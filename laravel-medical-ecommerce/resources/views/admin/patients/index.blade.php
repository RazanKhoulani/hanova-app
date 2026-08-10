@extends('admin.layout.app')

@section('title', 'Manage Patients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Patients Management</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Patient Name</th>
                        <th class="border-0 px-4 py-3">Age</th>
                        <th class="border-0 px-4 py-3">Phone</th>
                        <th class="border-0 px-4 py-3">Linked User</th>
                        <th class="border-0 px-4 py-3">Registered At</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr class="clickable-row" data-href="{{ route('admin.patients.show', $patient->id) }}">
                        <td class="align-middle px-4">{{ $patient->id }}</td>
                        <td class="align-middle px-4 fw-medium">{{ $patient->name }}</td>
                        <td class="align-middle px-4">{{ $patient->age }}</td>
                        <td class="align-middle px-4">{{ $patient->phone }}</td>
                        <td class="align-middle px-4">
                            @if($patient->user)
                                <span class="badge bg-info text-dark"><i class="fas fa-user me-1"></i> {{ $patient->user->name }}</span>
                            @else
                                <span class="text-muted"><i class="fas fa-user-times"></i> None</span>
                            @endif
                        </td>
                        <td class="align-middle px-4 text-muted">{{ $patient->created_at->format('Y-m-d') }}</td>
                        <td class="align-middle px-4 text-end">
                            <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-sm btn-outline-primary" title="View Patient File">
                                <i class="fas fa-folder-open"></i> File
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-user-injured fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No patients found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($patients->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $patients->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
