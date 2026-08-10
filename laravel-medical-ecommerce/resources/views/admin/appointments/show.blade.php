@extends('admin.layout.app')

@section('title', 'Appointment Details #' . $appointment->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Appointment Details: #{{ $appointment->id }}</h2>
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-header bg-white fw-bold py-3">Schedule Information</div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold d-block">Date & Time</label>
                    <div class="fs-4 fw-bold text-primary">{{ $appointment->date }}</div>
                    <div class="fs-5 text-muted">{{ $appointment->time }}</div>
                </div>
                
                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold d-block">Appointment Type</label>
                    <span class="badge {{ $appointment->type == 'online' ? 'bg-info' : 'bg-primary' }} fs-6">
                        <i class="fas {{ $appointment->type == 'online' ? 'fa-video' : 'fa-hospital' }} me-2"></i> {{ ucfirst($appointment->type) }}
                    </span>
                </div>

                <div>
                    <label class="text-muted small text-uppercase fw-bold d-block">Status</label>
                    <form action="{{ route('admin.appointments.updateStatus', $appointment->id) }}" method="POST" class="mt-2">
                        @csrf
                        @method('PUT')
                        <select name="status" class="form-select mb-3">
                            <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-header bg-white fw-bold py-3">Patient Information</div>
            <div class="card-body text-center py-5">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i class="fas fa-user-injured fa-3x text-secondary"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $appointment->patient->name ?? 'Unknown' }}</h4>
                <p class="text-muted mb-4">{{ $appointment->patient->phone ?? 'N/A' }}</p>
                
                <div class="d-grid gap-2 col-8 mx-auto">
                    <a href="{{ route('admin.patients.show', $appointment->patient_id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-folder-open me-2"></i> View Patient History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
