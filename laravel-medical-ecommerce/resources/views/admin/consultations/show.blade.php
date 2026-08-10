@extends('admin.layout.app')

@section('title', 'Consultation #' . $consultation->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Consultation Details</h2>
    <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Consultation Info</div>
            <div class="card-body">
                <p><strong>Status:</strong> <span class="badge bg-primary">{{ ucfirst($consultation->status) }}</span></p>
                <p><strong>Type:</strong> {{ ucfirst($consultation->type) }}</p>
                <p><strong>Requested At:</strong> {{ $consultation->created_at }}</p>
                <p><strong>User:</strong> {{ $consultation->user->name ?? 'Unknown' }} ({{ $consultation->user->phone ?? 'N/A' }})</p>
                <p><strong>Assigned Doctor:</strong> {{ $consultation->doctor->name ?? 'Unassigned' }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Clinical Notes</div>
            <div class="card-body">
                <p>{{ $consultation->notes ?? 'No notes recorded.' }}</p>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Quick Actions</div>
            <div class="card-body">
                <form action="{{ route('admin.consultations.updateStatus', $consultation->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Change Status</label>
                        <select name="status" class="form-select mb-3">
                            <option value="pending" {{ $consultation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $consultation->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ $consultation->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $consultation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Consultation</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
