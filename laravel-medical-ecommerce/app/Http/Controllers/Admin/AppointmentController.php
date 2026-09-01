<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'type' => 'nullable|in:clinic,online',
            'date' => 'nullable|date',
        ]);
        $query = Appointment::with('patient', 'user')->latest();

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->whereHas('patient', fn ($patient) => $patient
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }
        foreach (['status', 'type', 'date'] as $filter) {
            if (($filters[$filter] ?? null) !== null) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $appointments = $query->paginate(15)->withQueryString();
        return view('admin.appointments.index', compact('appointments', 'filters'));
    }

    public function show($id)
    {
        $appointment = Appointment::with('patient', 'user')->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();

        $appointment->loadMissing('patient');
        $userId = $appointment->patient?->user_id;
        if ($userId) {
            $labels = [
                'pending' => ['بانتظار المراجعة', 'Pending'],
                'confirmed' => ['مؤكد', 'Confirmed'],
                'completed' => ['مكتمل', 'Completed'],
                'cancelled' => ['ملغي', 'Cancelled'],
            ];
            [$statusAr, $statusEn] = $labels[$appointment->status];
            Notification::create([
                'user_id' => $userId,
                'title' => 'تحديث حالة الموعد',
                'body' => "أصبحت حالة موعدك بتاريخ {$appointment->date}: {$statusAr}.",
                'type' => 'appointment_status_updated',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'status' => $appointment->status,
                    'title_en' => 'Appointment status updated',
                    'body_en' => "Your appointment on {$appointment->date} is now {$statusEn}.",
                ],
            ]);
        }

        return back()->with('success', __('admin.appointment_status_updated'));
    }
}
