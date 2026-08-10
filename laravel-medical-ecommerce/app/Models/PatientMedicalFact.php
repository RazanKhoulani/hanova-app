<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalFact extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'source_message_id',
        'key',
        'value',
        'confidence',
        'status',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'source_message_id');
    }
}
