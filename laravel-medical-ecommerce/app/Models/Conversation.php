<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'doctor_id', 'care_scope', 'consultation_id'];

    public function canBeAccessedBy(User $user): bool
    {
        if ((int) $this->user_id === (int) $user->id || (int) $this->doctor_id === (int) $user->id) {
            return true;
        }

        return $this->care_scope === 'team' && $user->hasAnyRole(['admin', 'doctor', 'staff']);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('doctor_id', $user->id);

            if ($user->hasAnyRole(['admin', 'doctor', 'staff'])) {
                $query->orWhere('care_scope', 'team');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
