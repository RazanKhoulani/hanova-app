<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('patient', 'user')->latest()->paginate(15);
        return view('admin.appointments.index', compact('appointments'));
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

        return back()->with('success', 'Appointment status updated successfully');
    }
}
