<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index()
    {
        $consultations = Consultation::with('user', 'doctor')->latest()->paginate(15);
        return view('admin.consultations.index', compact('consultations'));
    }

    public function show($id)
    {
        $consultation = Consultation::with('user', 'doctor')->findOrFail($id);
        return view('admin.consultations.show', compact('consultation'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,completed,cancelled'
        ]);

        $consultation = Consultation::findOrFail($id);
        $consultation->status = $request->status;
        $consultation->save();

        return back()->with('success', __('admin.consultation_status_updated'));
    }
}
