<?php

namespace App\Http\Controllers\Veterinarie;

use App\Http\Controllers\Controller;
use App\Http\Resources\Veterinarie\VeterinarieCollection;
use App\Http\Resources\Veterinarie\VeterinarieResource;
use App\Models\User;
use App\Models\Veterinarie\VeterinarieScheduleDay;
use App\Models\Veterinarie\VeterinarieScheduleHour;
use App\Models\Veterinarie\VeterinarieScheduleJoin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class VeterinarieController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get("search");
        $veterinaries = User::where(DB::raw("users.name || ' ' || COALESCE(users.surname,'')|| ' ' || users.email"), "ilike", "%" . $search . "%")
            ->whereHas("roles", function ($query) {
                $query->where("name", "ilike", "%veterinario%");
            })
            ->orderBy("id", "asc")->get();

        return response()->json([
            "veterinaries" => VeterinarieCollection::make($veterinaries),
        ]);
    }

    public function config()
    {
        $roles = Role::where("name", "ilike", "%veterinario%")->get();
        $schedule_hours = VeterinarieScheduleHour::all();
        $schedule_hours_groups = collect([]);

        foreach ($schedule_hours->groupBy("hour") as $key => $schedule_hours) {
            $schedule_hours_groups->push([
                "hour" => $key,
                "hour_format" => Carbon::parse(date("Y-m-d") . ' ' . $key . ':00:00')->format("h:i A"),
                "segments_time" => $schedule_hours->map(function ($schedule_h) {
                    return [
                        "id" => $schedule_h->id,
                        "hour_start" => $schedule_h->hour_start,
                        "hour_end" => $schedule_h->hour_end,
                        "hour" => $schedule_h->hour,
                        "hour_start_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule_h->hour_start)->format("h:i A"),
                        "hour_end_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule_h->hour_end)->format("h:i A"),
                    ];
                }),
            ]);
        }

        return response()->json([
            "roles" => $roles,
            "schedule_hours_groups" => $schedule_hours_groups,
        ]);
    }

    public function store(Request $request)
    {
        $is_user_exists = User::where("email", $request->email)->first();
        if ($is_user_exists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El acceso ya existe"
            ]);
        }

        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("veterinaries", $request->file("imagen"));
            $request->merge(["avatar" => $path]);
        }

        if ($request->password) {
            $request->merge(["password" => bcrypt($request->password)]);
        }

        $veterinarie = User::create($request->all());
        $role = Role::findOrFail($request->role_id);
        $veterinarie->assignRole($role);

        // REGISTRO DE HORARIOS
        $schedule_hours_veterinarie = collect(json_decode($request->schedule_hours_veterinarie, true));
        foreach ($schedule_hours_veterinarie->groupBy("day") as $day => $schedules) {
            $schedule_day = VeterinarieScheduleDay::create([
                "veterinarie_id" => $veterinarie->id,
                "day" => $day
            ]);

            foreach ($schedules as $segment) {
                VeterinarieScheduleJoin::create([
                    "veterinarie_schedule_day_id" => $schedule_day->id, // CORREGIDO
                    "veterinarie_schedule_hour_id" => $segment["segment_time_id"]
                ]);
            }
        }

        return response()->json([
            "message" => 200,
        ]);
    }

    public function show(string $id)
    {
        $veterinarie = User::findOrFail($id);
        return response()->json([
            "veterinarie" => VeterinarieResource::make($veterinarie),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $is_user_exists = User::where("email", $request->email)->where("id", "<>", $id)->first();
        if ($is_user_exists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El acceso ya existe"
            ]);
        }

        $veterinarie = User::findOrFail($id);

        if ($request->hasFile("imagen")) {
            if ($veterinarie->avatar) {
                Storage::delete($veterinarie->avatar);
            }
            $path = Storage::putFile("veterinaries", $request->file("imagen"));
            $request->merge(["avatar" => $path]);
        }

        if ($request->password) {
            $request->merge(["password" => bcrypt($request->password)]);
        }

        $veterinarie->update($request->all());

        // Cambio de rol si es necesario
        if ($request->role_id && $request->role_id != $veterinarie->roles->first()?->id) {
            $veterinarie->syncRoles([Role::findOrFail($request->role_id)]);
        }

        // Borrar horarios anteriores
        foreach ($veterinarie->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                $schedule_join->delete();
            }
            $schedule_day->delete();
        }

        // Registrar nuevos horarios
        $schedule_hours_veterinarie = collect(json_decode($request->schedule_hours_veterinarie, true));
        foreach ($schedule_hours_veterinarie->groupBy("day") as $day => $schedules) {
            $schedule_day = VeterinarieScheduleDay::create([
                "veterinarie_id" => $veterinarie->id,
                "day" => $day
            ]);

            foreach ($schedules as $segment) {
                VeterinarieScheduleJoin::create([
                    "veterinarie_schedule_day_id" => $schedule_day->id,
                    "veterinarie_schedule_hour_id" => $segment["segment_time_id"]
                ]);
            }
        }

        return response()->json([
            "message" => 200,
        ]);
    }

    public function destroy(string $id)
    {
        $veterinarie = User::findOrFail($id);

        // Borrar imagen
        if ($veterinarie->avatar) {
            Storage::delete($veterinarie->avatar);
        }

        // Borrar horarios
        foreach ($veterinarie->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                $schedule_join->delete();
            }
            $schedule_day->delete();
        }

        $veterinarie->delete();

        return response()->json([
            "message" => 200,
            "message_text" => "Veterinario eliminado correctamente"
        ]);
    }
}
