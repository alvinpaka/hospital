<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'temperature',
        'blood_pressure',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'fasting',
        'recorded_by',
        'recorded_at',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
