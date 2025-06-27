<?php

namespace App\Models\Vaccination;

use App\Models\Veterinarie\VeterinarieScheduleHour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccinationSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        "vaccination_id",
        "veterinarie_schedule_hour_id",
        "hour"
    ];
    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, "vaccination_id");
    }
    public function schedule_hour()
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_schedule_hour_id");
    }
}
