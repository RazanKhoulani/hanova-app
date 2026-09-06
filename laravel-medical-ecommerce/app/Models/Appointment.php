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
        'provider_type',
        'date',
        'time',
        'type',
        'appointment_type',
        'specialty',
        'duration_minutes',
        'status',
        'internal_notes',
        'cancellation_reason',
        'cancelled_at',
        'assigned_by',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
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

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
