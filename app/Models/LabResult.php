<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'date', 'type', 'result', 'doctor', 'notes', 'attachment_url'
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}