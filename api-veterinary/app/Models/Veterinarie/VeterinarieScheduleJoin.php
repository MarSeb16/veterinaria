<?php

namespace App\Models\Veterinarie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarieScheduleJoin extends Model
{
    use HasFactory;

    protected $fillable = [
        "veterinarie_schedule_day_id",
        "veterinarie_schedule_hour_id"
    ];

    public function veterinarie_schedule_day()
    {
        return $this->belongsTo(VeterinarieScheduleDay::class, "veterinarie_schedule_day_id");
    }

    public function veterinarie_schedule_hour()
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_schedule_hour_id");
    }
}
