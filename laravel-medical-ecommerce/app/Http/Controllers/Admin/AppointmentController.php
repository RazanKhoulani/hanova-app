<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
        private readonly AuditService $auditService,
    ) {
    }

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
        $appointment = Appointment::with('patient', 'user', 'doctor')->findOrFail($id);
        $doctors = User::role(['doctor', 'admin'])->orderBy('name')->get(['id', 'name']);
        return view('admin.appointments.show', compact('appointment', 'doctors'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'provider_type' => 'required|in:doctor,team',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'type' => 'required|in:clinic,online',
            'appointment_type' => 'required|in:consultation,session,treatment',
            'specialty' => 'required|in:skin,hair,nutrition',
            'internal_notes' => 'nullable|string|max:5000',
        ]);

        $appointment = Appointment::findOrFail($id);
        $before = $appointment->only(['doctor_id', 'provider_type', 'date', 'time', 'type', 'appointment_type', 'specialty']);
        $internalNotes = $data['internal_notes'] ?? null;
        unset($data['internal_notes']);
        $updated = $this->appointmentService->updateAppointment($id, $data);
        $updated->update(['internal_notes' => $internalNotes, 'assigned_by' => auth()->id()]);
        $this->auditService->record('appointment.updated', $updated, [
            'before' => $before,
            'after' => $updated->only(array_keys($before)),
        ]);

        return back()->with('success', __('admin.appointment_updated'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string|max:1000',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->cancellation_reason = $request->status === 'cancelled' ? $request->cancellation_reason : null;
        $appointment->cancelled_at = $request->status === 'cancelled' ? now() : null;
        $appointment->save();
        $this->auditService->record('appointment.status_updated', $appointment, [
            'status' => $appointment->status,
            'cancellation_reason' => $appointment->cancellation_reason,
        ]);

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
