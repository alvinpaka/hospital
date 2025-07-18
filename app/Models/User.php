<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'clinic_id',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the appointments for this user (as patient)
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    /**
     * Get the appointments for this user (as doctor)
     */
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Get the lab results for this user (as patient)
     */
    public function labResults()
    {
        return $this->hasMany(LabResult::class, 'patient_id');
    }

    /**
     * Get the prescriptions for this user (as patient)
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    /**
     * Get the visits for this user (as patient)
     */
    public function visits()
    {
        return $this->hasMany(Visit::class, 'patient_id');
    }

    /**
     * Get the visits for this user (as doctor)
     */
    public function doctorVisits()
    {
        return $this->hasMany(Visit::class, 'doctor_id');
    }

    /**
     * Get the vitals for this user (as patient)
     */
    public function vitals()
    {
        return $this->hasMany(Vital::class, 'patient_id');
    }

    /**
     * Get the clinic for this user (for admin users)
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * The doctors/nurses assigned to this patient.
     */
    public function patientDoctors()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'patient_id', 'doctor_id')
            ->withPivot('role', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * The patients assigned to this doctor/nurse.
     */
    public function doctorPatients()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'doctor_id', 'patient_id')
            ->withPivot('role', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Messages sent by this user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by this user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
