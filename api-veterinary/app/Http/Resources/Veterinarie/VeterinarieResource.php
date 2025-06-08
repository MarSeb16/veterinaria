<?php

namespace App\Http\Resources\Veterinarie;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VeterinarieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $selected_segment_time = [];
        foreach ($this->resource->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                array_push($selected_segment_time, $schedule_join->veterinarie_schedule_hour_id . '-' . $schedule_day->day);
            }
        }
        $schedule_hours_veterinarie = [];
        foreach ($this->resource->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                array_push($schedule_hours_veterinarie, [
                    "id_seg" => $schedule_join->veterinarie_schedule_hour_id . '-' . $schedule_day->day,
                    "segment_time_id" => $schedule_join->veterinarie_schedule_hour_id,
                    "day" => $schedule_day->day
                ]);
            }
        }
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'gender' => $this->resource->gender,
            'password' => $this->resource->password,
            'surname' => $this->resource->surname,
            'full_name' => $this->resource->name . ' ' . $this->resource->surname,
            'role_id' => $this->resource->role_id,
            "role" => [
                "name" => $this->resource->role?->name,
            ],
            "role_name" => $this->resource->role?->name,
            'avatar' => $this->resource->avatar ? env("APP_URL") . "storage/" . $this->resource->avatar : null,
            'type_document' => $this->resource->type_document,
            'n_document' => $this->resource->n_document,
            'phone' => $this->resource->phone,
            'designation' => $this->resource->designation,
            'birthday' => $this->resource->birthday ? Carbon::parse($this->resource->birthday)->format("Y/m/d") : null,
            'selected_segment_time' => $selected_segment_time,
            'schedule_hours_veterinarie' => $schedule_hours_veterinarie,
        ];
    }
}
