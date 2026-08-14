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
        $notifications = Notification::with('user')->latest()->paginate(15);
        $users = User::all();

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:general,offer,clinic,chat_message,order_status,order_created,order_accepted,order_ready,order_delivered',
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
