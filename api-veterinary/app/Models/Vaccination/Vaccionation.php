<?php

namespace App\Models\Vaccination;

use App\Models\MedicalRecord;
use App\Models\Pets\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vaccionation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "veterinarie_id",
        "pet_id",
        "day",
        "vaccination_date",
        "vaccine_name",
        "nex_due_date",
        "outside",
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
    public function payments()
    {
        return $this->hasMany(VaccionationPayment::class);
    }
    public function medical_record()
    {
        return $this->hasOne(MedicalRecord::class, "vaccination_id");
    }
    public function schedules()
    {
        return $this->hasMany(VaccionationSchedule::class);
    }
    public function scopeFilterMultiple($query, $type_date, $start_date, $end_date, $state_pay, $specie, $state, $search_pets)
    {
        if ($start_date && $end_date) {
            if ($type_date == 1) {
                $query->whereBetween("vaccination_date", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            } else {
                $query->whereBetween("created_at", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            }
        }
        if ($state_pay) {
            $query->where("state_pay", $state_pay);
        }
        if ($state) {
            $query->where("state", $state);
        }
        if ($specie) {
            $query->whereHas("pet", function ($subquery) use ($specie) {
                $subquery->where("specie", $specie);
            });
        }
        if ($search_pets) {
            $query->whereHas("pet", function ($subquery) use ($search_pets) {
                $subquery->where("name", "ilike", "%" . $search_pets . "%");
            });
        }
        return $query;
    }
}
