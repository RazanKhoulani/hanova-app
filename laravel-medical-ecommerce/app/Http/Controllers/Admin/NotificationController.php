<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
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
        Notification::query()->where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
        $notifications = Notification::with('user')->latest()->paginate(15);
        $users = User::all();

        return view('admin.notifications.index', compact('notifications', 'users', 'inboxNotifications'));
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Notification::query()->where('user_id', auth()->id())->where('is_read', false)->count(),
        ]);
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

        return back()->with('success', 'Notification sent successfully');
    }

    public function destroy($id)
    {
        Notification::findOrFail($id)->delete();

        return back()->with('success', 'Notification deleted');
    }
}
