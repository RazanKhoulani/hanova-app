<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List user notifications.
     */
    public function index(Request $request)
    {
        $notifications = Notification::where(function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                ->orWhereNull('user_id');
        })->latest()->paginate(15);

        return NotificationResource::collection($notifications)
            ->additional(['message' => 'Notifications retrieved successfully']);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhereNull('user_id');
            })
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }
}
