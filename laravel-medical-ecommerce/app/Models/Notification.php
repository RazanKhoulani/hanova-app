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

    public function adminUrl(): string
    {
        $data = $this->data ?? [];

        if (($this->type === 'chat_message' || isset($data['conversation_id'])) && isset($data['conversation_id'])) {
            return route('admin.chats.show', $data['conversation_id']);
        }

        if ((str_starts_with($this->type, 'order_') || isset($data['order_id'])) && isset($data['order_id'])) {
            return route('admin.orders.show', $data['order_id']);
        }

        if ((str_contains($this->type, 'appointment') || isset($data['appointment_id'])) && isset($data['appointment_id'])) {
            return route('admin.appointments.show', $data['appointment_id']);
        }

        return route('admin.notifications.index');
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
