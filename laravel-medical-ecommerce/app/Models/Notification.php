<?php

namespace App\Models;

use App\Services\FirebaseMessagingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            DB::afterCommit(function () use ($notification) {
                app(FirebaseMessagingService::class)->sendNotification($notification);
            });
        });
    }
}
