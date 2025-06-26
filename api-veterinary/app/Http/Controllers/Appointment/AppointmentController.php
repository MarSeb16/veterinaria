<?php

namespace App\Http\Controllers\Appointment;

use App\Exports\DownloadAppointment;
use App\Http\Controllers\Controller;
use App\Http\Resources\Appointment\AppointmentCollection;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPayment;
use App\Models\Appointment\AppointmentSchedule;
use App\Models\Pets\Pet;
use App\Models\Veterinarie\VeterinarieScheduleDay;
use App\Models\Veterinarie\VeterinarieScheduleJoin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type_date =  $request->type_date;
        $start_date =  $request->start_date;
        $end_date =  $request->end_date;
        $state_pay =  $request->state_pay;
        $state =  $request->state_appointment;
        $specie =  $request->specie;
        $search_pets =  $request->search_pets;
        $appointments = Appointment::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets)
            ->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $appointments->lastPage(),
            "appointments" => AppointmentCollection::make($appointments),
        ]);
    }
    public function filter(Request $request)
    {
        $date_appointment = $request->date_appointment;
        $hour = $request->hour;
        date_default_timezone_set('America/La_Paz');
        Carbon::setLocale('es');
        $dayName = Carbon::parse($date_appointment)->dayName;
        $veterinarie_days = VeterinarieScheduleDay::where("day", "ilike", "%" . $dayName . "%")->orderBy("veterinarie_id", "asc")->get();
        $veterinarie_time_availability = collect([]);
        foreach ($veterinarie_days as $veterinarie_day) {
            $segment_time_formats = collect([]);
            $segment_time_joins = VeterinarieScheduleJoin::where("veterinarie_schedule_day_id", $veterinarie_day->id)
                ->where(function ($query) use ($hour) {
                    if ($hour) {
                        $query->whereHas("veterinarie_schedule_hour", function ($subquery) use ($hour) {
                            $hour_explode = explode(":", $hour);
                            $subquery->where("hour", $hour_explode[0]);
                        });
                    }
                })->get();
            foreach ($segment_time_joins as $segment_time_join) {
                $check = Appointment::whereDate("date_appointment", $date_appointment)
                    ->where("veterinarie_id", $veterinarie_day->veterinarie_id)
                    ->whereHas("schedules", function ($query) use ($segment_time_join) {
                        $query->where("veterinarie_schedule_hour_id", $segment_time_join->veterinarie_schedule_hour_id);
                    })->first();
                $segment_time_formats->push([
                    "id" => $segment_time_join->id,
                    "veterinarie_schedule_day_id" => $segment_time_join->veterinarie_schedule_day_id,
                    "veterinarie_schedule_hour_id" => $segment_time_join->veterinarie_schedule_hour_id,
                    "hour" => $segment_time_join->veterinarie_schedule_hour->hour,
                    "schedule_hour" => [
                        "hour_start" => $segment_time_join->veterinarie_schedule_hour->hour_start,
                        "hour_end" => $segment_time_join->veterinarie_schedule_hour->hour_end,
                        "hour" => $segment_time_join->veterinarie_schedule_hour->hour,
                        "hour_start_format" => Carbon::parse(date("Y-m-d") . ' ' . $segment_time_join->veterinarie_schedule_hour->hour_start)->format("h:i A"),
                        "hour_end_format" => Carbon::parse(date("Y-m-d") . ' ' . $segment_time_join->veterinarie_schedule_hour->hour_end)->format("h:i A"),
                    ],
                    "check" => $check ? true : false,
                ]);
            }
            $segment_time_groups = collect([]);
            foreach ($segment_time_formats->groupBy("hour") as $hourT => $segment_time_format) {
                $segment_time_groups->push([
                    "hour" => $hourT,
                    "hour_format" => Carbon::parse(date("Y-m-d") . ' ' . $hourT . ':00:00')->format("h:i A"),
                    "segment_times" => $segment_time_format,
                ]);
            }
            if ($segment_time_groups->count() != 0) {
                $veterinarie_time_availability->push([
                    "id" => $veterinarie_day->veterinarie_id,
                    "full_name" => $veterinarie_day->veterinarie->name . ' ' . $veterinarie_day->veterinarie->surname,
                    "segment_time_groups" => $segment_time_groups,
                ]);
            }
        }
        return response()->json([
            "veterinarie_time_availability" => $veterinarie_time_availability,
        ]);
    }

    public function downloadExcel(Request $request)
    {
        $type_date =  $request->get("type_date");
        $start_date =  $request->get("start_date");
        $end_date =  $request->get("end_date");
        $state_pay =  $request->get("state_pay");
        $state =  $request->get("state_appointment");
        $specie =  $request->get("specie");
        $search_pets =  $request->get("search_pets");
        $appointments = Appointment::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets)
            ->orderBy("id", "desc")->get();
        return Excel::download(new DownloadAppointment($appointments), "citas_medicas_reporte.xlsx");
    }
    public function searchPets($search)
    {
        $pets = Pet::whereHas("owner", function ($query) use ($search) {
            $query->where(DB::raw("pets.name || ' ' || owners.first_name || ' ' || COALESCE(owners.last_name,'')|| ' ' || owners.phone || ' ' || owners.n_document "), "ilike", "%" . $search . "%");
        })->get();
        return response()->json([
            "pets" => $pets->map(function ($pet) {
                return [
                    "id" => $pet->id,
                    "name" => $pet->name,
                    "specie" => $pet->specie,
                    "breed" => $pet->breed,
                    "owner" => [
                        "id" => $pet->owner->id,
                        "first_name" => $pet->owner->first_name,
                        "last_name" => $pet->owner->last_name,
                        "phone" => $pet->owner->phone,
                        "n_document" => $pet->owner->n_document
                    ]
                ];
            }),
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        date_default_timezone_set('America/La_Paz');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->date_appointment)->dayName;

        $appointment = Appointment::create([
            "veterinarie_id" => $request->veterinarie_id,
            "pet_id" => $request->pet_id,
            "day" => $dayName,
            "date_appointment" => $request->date_appointment,
            "reason" => $request->reason,
            "user_id" => auth('api')->user()->id,
            "amount" => $request->amount,
            "state_pay" => $request->state_pay
        ]);

        AppointmentPayment::create([
            "appointment_id" => $appointment->id,
            "method_payment" => $request->method_payment,
            "amount" => $request->adelanto
        ]);

        foreach ($request->selected_segment_times as $selected_segment_time) {
            AppointmentSchedule::create([
                "appointment_id" => $appointment->id,
                "veterinarie_schedule_hour_id" => $selected_segment_time["segment_time_id"]
            ]);
        }

        return response()->json([
            "message" => 200
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        return response()->json([
            "appointment" => AppointmentResource::make($appointment)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        date_default_timezone_set('America/La_Paz');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->date_appointment)->dayName;

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            "veterinarie_id" => $request->veterinarie_id,
            "pet_id" => $request->pet_id,
            "day" => $dayName,
            "date_appointment" => $request->date_appointment,
            "reason" => $request->reason,
            "amount" => $request->amount,
            "state_pay" => $request->state_pay
        ]);

        foreach ($appointment->schedules as $schedule) {
            $schedule->delete();
        }

        foreach ($request->selected_segment_times as $selected_segment_time) {
            AppointmentSchedule::create([
                "appointment_id" => $appointment->id,
                "veterinarie_schedule_hour_id" => $selected_segment_time["segment_time_id"]
            ]);
        }

        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        if ($appointment->state == 3) {
            return response()->json([
                "message" => 403
            ]);
        }
        $appointment->delete();
        return response()->json([
            "message" => 200
        ]);
    }
}
