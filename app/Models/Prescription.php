<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'drug_id', 'dosage', 'instructions', 'doctor', 'start_date', 'end_date'
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Models\User::class, 'patient_id');
    }

    public function drug()
    {
        return $this->belongsTo(\App\Models\Drug::class, 'drug_id');
    }
}