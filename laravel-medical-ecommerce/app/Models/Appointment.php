<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'date',
        'time',
        'type',
        'appointment_type',
        'duration_minutes',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Patient::class, 'id', 'id', 'patient_id', 'user_id');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }
}
