@extends('admin.layout.app')

@section('title', 'Manage Appointments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Appointments Management</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Patient</th>
                        <th class="border-0 px-4 py-3">Date & Time</th>
                        <th class="border-0 px-4 py-3">Type</th>
                        <th class="border-0 px-4 py-3">Status</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    <tr class="clickable-row" data-href="{{ route('admin.appointments.show', $appointment->id) }}">
                        <td class="align-middle px-4 fw-bold">#{{ $appointment->id }}</td>
                        <td class="align-middle px-4">
                            {{ $appointment->patient->name ?? 'Unknown' }}<br>
                            <small class="text-muted">{{ $appointment->patient->phone ?? '' }}</small>
                        </td>
                        <td class="align-middle px-4">
                            <span class="text-dark fw-medium">{{ $appointment->date }}</span><br>
                            <small class="text-muted">{{ $appointment->time }}</small>
                        </td>
                        <td class="align-middle px-4">
                            <span class="badge {{ $appointment->type == 'online' ? 'bg-info' : 'bg-primary' }}">
                                {{ ucfirst($appointment->type) }}
                            </span>
                        </td>
                        <td class="align-middle px-4">
                            @php
                                $badgeClass = match($appointment->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'confirmed' => 'bg-info',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">{{ ucfirst($appointment->status) }}</span>
                        </td>
                        <td class="align-middle px-4 text-end">
                            <form action="{{ route('admin.appointments.updateStatus', $appointment->id) }}" method="POST" class="d-flex justify-content-end align-items-center">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto me-2" onchange="this.form.submit()">
                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No appointments found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($appointments->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $appointments->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
