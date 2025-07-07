<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'medication', 'dosage', 'instructions', 'doctor', 'start_date', 'end_date'
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Models\User::class, 'patient_id');
    }
}