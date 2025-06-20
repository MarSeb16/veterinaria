<?php

namespace App\Models\Appointment;

use App\Models\Veterinarie\VeterinarieScheduleHour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        "appointment_id",
        "veterinarie_schedule_hour_id"
    ];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");
    }
    public function schedule_hour()
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_schedule_hour_id");
    }
}
