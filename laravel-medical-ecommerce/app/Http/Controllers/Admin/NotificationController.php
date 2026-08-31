<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $inboxNotifications = Notification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get();
        $notifications = Notification::with('user')->latest()->paginate(15);
        $users = User::all();

        return view('admin.notifications.index', compact('notifications', 'users', 'inboxNotifications'));
    }

    public function unreadCount()
    {
        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->localizedTitle(),
                'body' => $notification->localizedBody(),
                'is_read' => $notification->is_read,
                'url' => $notification->adminUrl(),
                'created_at' => $notification->created_at?->locale(app()->getLocale())->diffForHumans(),
            ]);

        return response()->json([
            'count' => Notification::query()->where('user_id', auth()->id())->where('is_read', false)->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);
        $notification->update(['is_read' => true]);

        return response()->json(['url' => $notification->adminUrl()]);
    }

    public function markAllAsRead()
    {
        Notification::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            ['user_id' => auth()->id(), 'platform' => 'web', 'last_used_at' => now()],
        );

        return response()->json(['message' => 'Browser notifications enabled.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|max:100',
        ]);

        Notification::create([
            'user_id' => $request->user_id, // Null for all (broadcast)
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type ?? 'general',
        ]);

        return back()->with('success', __('admin.notification_sent'));
    }

    public function destroy($id)
    {
        Notification::findOrFail($id)->delete();

        return back()->with('success', __('admin.notification_deleted'));
    }
}
