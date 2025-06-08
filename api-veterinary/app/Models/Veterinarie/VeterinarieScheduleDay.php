<?php

namespace App\Models\Veterinarie;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarieScheduleDay extends Model
{
    use HasFactory;
    protected $fillable = [
        "veterinarie_id",
        "day"
    ];

    public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }
    public function schedule_joins()
    {
        return $this->hasMany(VeterinarieScheduleJoin::class, 'veterinarie_schedule_day_id');
    }
}
