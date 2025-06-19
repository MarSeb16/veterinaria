<?php

namespace App\Models\Pets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'specie',
        'breed',
        'dirth_date',
        'gender',
        'color',
        'weight',
        'photo',
        'medical_notes',
        'owner_id',

    ];
    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
