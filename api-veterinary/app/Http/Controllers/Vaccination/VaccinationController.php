<?php

namespace App\Http\Controllers\Vaccination;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vaccination\VaccinationCollection;
use App\Http\Resources\Vaccination\VaccinationResource;
use App\Models\MedicalRecord;
use App\Models\Vaccination\Vaccination;
use App\Models\Vaccination\VaccinationPayment;
use App\Models\Vaccination\VaccinationSchedule;
use App\Models\Vaccination\VaccionationPayment;
use App\Models\Veterinarie\VeterinarieScheduleDay;
use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VaccinationController extends Controller
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
        $vaccinations = Vaccination::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets)
            ->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $vaccinations->lastPage(),
            "vaccinations" => VaccinationCollection::make($vaccinations),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        date_default_timezone_set('America/La_Paz');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->vaccination_date)->dayName;

        $vaccination = Vaccination::create([
            "veterinarie_id" => $request->veterinarie_id,
            "pet_id" => $request->pet_id,
            "day" => $dayName,
            "vaccination_date" => $request->vaccination_date,
            "reason" => $request->reason,
            "user_id" => auth('api')->user()->id,
            "amount" => $request->amount,
            "state_pay" => $request->state_pay,
            "outside" => $request->outside,
            "vaccine_names" => $request->vaccine_names,
            "nex_due_date" => $request->nex_due_date
        ]);

        MedicalRecord::create([
            "veterinarie_id" => $vaccination->veterinarie_id,
            "pet_id" => $vaccination->pet_id,
            "event_type" => 1,
            "event_date" => $vaccination->vaccination_date,
            "vaccination_id" => $vaccination->id
        ]);

        VaccinationPayment::create([
            "vaccination_id" => $vaccination->id,
            "method_payment" => $request->method_payment,
            "amount" => $request->adelanto
        ]);

        foreach ($request->selected_segment_times as $selected_segment_time) {
            $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
            VaccinationSchedule::create([
                "vaccination_id" => $vaccination->id,
                "hour" => $schedule_hour->hour,
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
        $vaccination = Vaccination::findOrFail($id);
        return response()->json([
            "vaccination" => VaccinationResource::make($vaccination)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        date_default_timezone_set('America/La_Paz');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->vaccination_date)->dayName;

        $vaccination = Vaccination::findOrFail($id);
        // if ($request->amount < $vaccination->payments->sum("amount")) {
        //     return response()->json([
        //         "message" => 403,
        //         "message_text" => "El costo de la cita medica no puede ser menor a lo cancelado (".$vaccination->payments->sum("amount"),")
        //     ])
        // }
        $vaccination->update([
            "veterinarie_id" => $request->veterinarie_id,
            "pet_id" => $request->pet_id,
            "day" => $dayName,
            "date_vaccination" => $request->date_vaccination,
            "reason" => $request->reason,
            "amount" => $request->amount,
            "state_pay" => $request->state_pay,
            "outside" => $request->outside,
            "vaccine_names" => $request->vaccine_names,
            "nex_due_date" => $request->nex_due_date
        ]);

        foreach ($vaccination->schedules as $schedule) {
            $schedule->delete();
        }

        foreach ($request->selected_segment_times as $selected_segment_time) {
            $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
            VaccinationSchedule::create([
                "vaccination_id" => $vaccination->id,
                "hour" => $schedule_hour->hour,
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
        $vaccination = Vaccination::findOrFail($id);
        if ($vaccination->state == 3) {
            return response()->json([
                "message" => 403
            ]);
        }
        $vaccination->delete();
        return response()->json([
            "message" => 200
        ]);
    }
}
