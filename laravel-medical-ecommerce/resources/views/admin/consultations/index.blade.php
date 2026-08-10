@extends('admin.layout.app')

@section('title', 'Manage Consultations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Consultations Management</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Patient</th>
                        <th class="border-0 px-4 py-3">Type</th>
                        <th class="border-0 px-4 py-3">Date Requested</th>
                        <th class="border-0 px-4 py-3">Status</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                    <tr class="clickable-row" data-href="{{ route('admin.consultations.show', $consultation->id) }}">
                        <td class="align-middle px-4 fw-bold">#{{ $consultation->id }}</td>
                        <td class="align-middle px-4">
                            {{ $consultation->user->name ?? 'Unknown' }}
                        </td>
                        <td class="align-middle px-4">
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $consultation->type)) }}
                            </span>
                        </td>
                        <td class="align-middle px-4 text-muted">{{ $consultation->created_at->format('Y-m-d H:i') }}</td>
                        <td class="align-middle px-4">
                            @php
                                $badgeClass = match($consultation->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'active' => 'bg-primary',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">{{ ucfirst($consultation->status) }}</span>
                        </td>
                        <td class="align-middle px-4 text-end">
                            <form action="{{ route('admin.consultations.updateStatus', $consultation->id) }}" method="POST" class="d-flex justify-content-end align-items-center">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto me-2" onchange="this.form.submit()">
                                    <option value="pending" {{ $consultation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ $consultation->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ $consultation->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $consultation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <a href="{{ route('admin.consultations.show', $consultation->id) }}" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No consultations found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($consultations->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $consultations->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
