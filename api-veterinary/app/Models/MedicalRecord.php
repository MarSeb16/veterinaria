<?php

namespace App\Models;

use App\Models\Appointment\Appointment;
use App\Models\Pets\Pet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "veterinarie_id",
        "pet_id",
        "event_type",
        "event_date",
        "appointment_id",
        "vaccination_id",
        "surgerie_id",
        "notes"
    ];
    public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }
    public function pet()
    {
        return $this->belongsTo(Pet::class, "pet_id");
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");
    }
}
