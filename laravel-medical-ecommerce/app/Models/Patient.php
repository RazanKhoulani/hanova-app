<?php

namespace App\Models;

use App\Support\SyrianPhoneNumber;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'age',
        'phone',
        'address',
        'notes',
        'image_before',
        'image_after',
        'medical_file',
    ];

    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => SyrianPhoneNumber::normalize($value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressPhotos(): HasMany
    {
        return $this->hasMany(PatientProgressPhoto::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalFacts(): HasMany
    {
        return $this->hasMany(PatientMedicalFact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function botConversations(): HasMany
    {
        return $this->hasMany(BotConversation::class);
    }
}
