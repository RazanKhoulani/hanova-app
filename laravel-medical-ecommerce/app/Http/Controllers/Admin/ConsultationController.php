<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index()
    {
        $consultations = Consultation::with('user', 'doctor')->latest()->paginate(15);
        return view('admin.consultations.index', compact('consultations'));
    }

    public function show($id)
    {
        $consultation = Consultation::with('user', 'doctor')->findOrFail($id);
        $doctors = User::role(['doctor', 'admin'])->orderBy('name')->get(['id', 'name']);
        return view('admin.consultations.show', compact('consultation', 'doctors'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'doctor_id' => 'nullable|exists:users,id',
            'type' => 'required|string|max:100',
            'notes' => 'nullable|string|max:5000',
            'internal_notes' => 'nullable|string|max:5000',
        ]);
        $consultation = Consultation::findOrFail($id);
        $before = $consultation->only(array_keys($data));
        $consultation->update($data);
        $consultation->conversation?->update(['doctor_id' => $consultation->doctor_id]);
        $this->auditService->record('consultation.updated', $consultation, [
            'before' => $before,
            'after' => $consultation->only(array_keys($data)),
        ]);

        return back()->with('success', __('admin.consultation_updated'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,completed,cancelled',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string|max:1000',
        ]);

        $consultation = Consultation::findOrFail($id);
        $consultation->status = $request->status;
        $consultation->cancellation_reason = $request->status === 'cancelled' ? $request->cancellation_reason : null;
        $consultation->cancelled_at = $request->status === 'cancelled' ? now() : null;
        $consultation->save();
        $this->auditService->record('consultation.status_updated', $consultation, [
            'status' => $consultation->status,
            'cancellation_reason' => $consultation->cancellation_reason,
        ]);

        return back()->with('success', __('admin.consultation_status_updated'));
    }
}
