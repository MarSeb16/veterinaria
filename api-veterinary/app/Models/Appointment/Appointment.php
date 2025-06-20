<?php

namespace App\Models\Appointment;

use App\Models\Pets\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "veterinarie_id",
        "pet_id",
        "day",
        "date_appointment",
        "reason",
        "reprogramar",
        "state",
        "user_id",
        "amount",
        "state_pay"
    ];
    public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }
    public function pet()
    {
        return $this->belongsTo(Pet::class, "pet_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function schedules()
    {
        return $this->hasMany(AppointmentSchedule::class);
    }
}
