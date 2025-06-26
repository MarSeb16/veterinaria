<?php

namespace App\Models\Vaccination;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccionationPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "vaccination_id",
        "method_payment",
        "amount"
    ];
    public function vaccination()
    {
        return $this->belongsTo(Vaccionation::class, "vaccination_id");
    }
}
