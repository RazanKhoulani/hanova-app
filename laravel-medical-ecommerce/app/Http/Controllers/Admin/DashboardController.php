<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user?->hasAnyRole(['admin', 'doctor', 'delivery'])) {
            abort(403);
        }

        if ($user->hasRole('delivery')) {
            return redirect()->route('admin.orders.index');
        }

        $today = today();
        $completedOrderStatuses = ['paid', 'shipped', 'delivered', 'completed'];

        $stats = [
            'orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'patients' => Patient::count(),
            'new_patients' => Patient::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'upcoming_appointments' => Appointment::whereDate('date', '>=', $today)
                ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
                ->count(),
            'appointments_today' => Appointment::whereDate('date', $today)
                ->whereNotIn('status', ['cancelled', 'canceled'])
                ->count(),
            'revenue' => Order::whereIn('status', $completedOrderStatuses)->sum('total_amount'),
            'products' => Product::count(),
            'consultations' => Consultation::count(),
            'conversations' => Conversation::count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $upcomingAppointments = Appointment::with('patient.user')
            ->whereDate('date', '>=', $today)
            ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'upcomingAppointments'));
    }
}
