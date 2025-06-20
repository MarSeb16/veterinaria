<?php

namespace App\Models\Appointment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "appointment_id",
        "method_payment",
        "amount"
    ];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");  
    }
}
