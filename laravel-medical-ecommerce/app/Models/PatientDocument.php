<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDocument extends Model
{
    protected $fillable = [
        'patient_id',
        'user_id',
        'consultation_id',
        'conversation_id',
        'message_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
