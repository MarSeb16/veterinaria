<?php

namespace App\Http\Resources\MedicalRecord\Calendar;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordCalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hour_start = $this->resource->schedules->first()->schedule_hour->hour_start;
        $hour_end = $this->resource->schedules->first()->schedule_hour->hour_end;
        return [
            "id" => $this->resource->id,
            "title" => $this->resource->pet->name . ' ' . Carbon::parse(date("Y-m-d") . ' ' . $hour_start)->format("H:i A")
                . ' ' . Carbon::parse(date("Y-m-d") . ' ' . $hour_end)->format("H:i A"),
            "start" => Carbon::parse(Carbon::parse($this->resource->date_appointment)->format("Y-m-d") . ' ' . $hour_start)->format("Y-m-d h:i:s"),
            "end" => Carbon::parse(Carbon::parse($this->resource->date_appointment)->format("Y-m-d") . ' ' . $hour_end)->format("Y-m-d h:i:s"),
            "allDay" => false,
            "url" => '',
            "extendedProps" => [
                "calendar" => 'Appointment',
                "description" => $this->resource->reason,
                "notes" => $this->resource->medical_record?->notes,
                "day" => $this->resource->day,
                "state" => $this->resource->state,
                "amount" => $this->resource->amount,
                "veterinarie" => [
                    "full_name" => $this->resource->veterinarie->name . ' ' . $this->resource->veterinarie->surname,
                    "role" => [
                        "name" => $this->resource->veterinarie->role->name,
                    ],
                ],
                "pet" => [
                    "id" => $this->resource->pet->id,
                    "name" => $this->resource->pet->name,
                    "specie" => $this->resource->pet->specie,
                    "breed" => $this->resource->pet->breed,
                    'photo' => $this->resource->pet->photo ? asset('storage/' . $this->resource->pet->photo) : null,
                    "owner" => [
                        "id" => $this->resource->pet->owner->id,
                        "first_name" => $this->resource->pet->owner->first_name,
                        "last_name" => $this->resource->pet->owner->last_name,
                        "phone" => $this->resource->pet->owner->phone,
                        "n_document" => $this->resource->pet->owner->n_document
                    ],
                ],
            ],
        ];
    }
}
