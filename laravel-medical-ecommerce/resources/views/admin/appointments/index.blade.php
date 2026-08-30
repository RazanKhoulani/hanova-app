@extends('admin.layout.app')

@section('title', __('admin.appointments_management'))

@section('content')
<div class="page-header"><div><p class="eyebrow">{{ __('admin.clinic') }}</p><h1>{{ __('admin.appointments_management') }}</h1><p>{{ __('admin.appointments_management_hint') }}</p></div></div>
<section class="panel-card data-panel">
    <div class="panel-heading"><div><h3>{{ __('admin.appointments') }}</h3><p>{{ __('admin.appointments_table_hint') }}</p></div><span class="soft-count">{{ $appointments->total() }}</span></div>
    <div class="table-responsive"><table class="table align-middle mb-0 admin-data-table"><thead><tr><th>{{ __('admin.id') }}</th><th>{{ __('admin.patient') }}</th><th>{{ __('admin.date_time') }}</th><th>{{ __('admin.type') }}</th><th>{{ __('admin.status') }}</th><th class="text-end">{{ __('admin.action') }}</th></tr></thead><tbody>
        @forelse($appointments as $appointment)
            @php($typeKey = 'admin.type_' . ($appointment->type ?: 'clinic'))
            @php($statusKey = 'admin.status_' . $appointment->status)
            <tr class="clickable-row" data-href="{{ route('admin.appointments.show', $appointment->id) }}"><td class="fw-bold">#{{ $appointment->id }}</td><td><strong>{{ $appointment->patient?->name ?? __('admin.unknown') }}</strong><small class="d-block text-muted" dir="ltr">{{ $appointment->patient?->phone }}</small></td><td><strong>{{ $appointment->date }}</strong><small class="d-block text-muted">{{ $appointment->time }}</small></td><td><span class="status-pill info">{{ trans()->has($typeKey) ? __($typeKey) : ucfirst($appointment->type) }}</span></td><td><span class="status-pill {{ $appointment->status === 'completed' ? 'success' : (in_array($appointment->status, ['cancelled', 'canceled']) ? 'danger' : 'warning') }}">{{ trans()->has($statusKey) ? __($statusKey) : ucfirst($appointment->status) }}</span></td><td><div class="action-toolbar justify-content-end"><form action="{{ route('admin.appointments.updateStatus', $appointment->id) }}" method="POST">@csrf @method('PUT')<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">@foreach(['pending', 'confirmed', 'completed', 'cancelled'] as $status)<option value="{{ $status }}" @selected($appointment->status === $status)>{{ __('admin.status_' . $status) }}</option>@endforeach</select></form><a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-light" title="{{ __('admin.view_details') }}"><i class="fas fa-eye"></i></a></div></td></tr>
        @empty
            <tr><td colspan="6" class="empty-table"><i class="fas fa-calendar-times"></i><span>{{ __('admin.no_appointments_found') }}</span></td></tr>
        @endforelse
    </tbody></table></div>
    @if($appointments->hasPages())<div class="pt-3">{{ $appointments->links('pagination::bootstrap-5') }}</div>@endif
</section>
@endsection
