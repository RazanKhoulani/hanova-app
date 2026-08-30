@extends('admin.layout.app')

@section('title', __('admin.consultations_management'))

@section('content')
<div class="page-header"><div><p class="eyebrow">{{ __('admin.clinic') }}</p><h1>{{ __('admin.consultations_management') }}</h1><p>{{ __('admin.consultations_management_hint') }}</p></div></div>
<section class="panel-card data-panel"><div class="panel-heading"><div><h3>{{ __('admin.consultations') }}</h3><p>{{ __('admin.consultations_table_hint') }}</p></div><span class="soft-count">{{ $consultations->total() }}</span></div><div class="table-responsive"><table class="table align-middle mb-0 admin-data-table"><thead><tr><th>{{ __('admin.id') }}</th><th>{{ __('admin.patient') }}</th><th>{{ __('admin.type') }}</th><th>{{ __('admin.date_requested') }}</th><th>{{ __('admin.status') }}</th><th class="text-end">{{ __('admin.action') }}</th></tr></thead><tbody>
    @forelse($consultations as $consultation)
        @php($typeKey = 'admin.type_' . ($consultation->type ?: 'consultation')) @php($statusKey = 'admin.status_' . $consultation->status)
        <tr class="clickable-row" data-href="{{ route('admin.consultations.show', $consultation->id) }}"><td class="fw-bold">#{{ $consultation->id }}</td><td class="fw-bold">{{ $consultation->user?->name ?? __('admin.unknown') }}</td><td><span class="status-pill info">{{ trans()->has($typeKey) ? __($typeKey) : ucfirst(str_replace('_', ' ', $consultation->type)) }}</span></td><td class="text-muted">{{ $consultation->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</td><td><span class="status-pill {{ $consultation->status === 'completed' ? 'success' : (in_array($consultation->status, ['cancelled', 'canceled']) ? 'danger' : 'warning') }}">{{ trans()->has($statusKey) ? __($statusKey) : ucfirst($consultation->status) }}</span></td><td><div class="action-toolbar justify-content-end"><form action="{{ route('admin.consultations.updateStatus', $consultation->id) }}" method="POST">@csrf @method('PUT')<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">@foreach(['pending', 'active', 'completed', 'cancelled'] as $status)<option value="{{ $status }}" @selected($consultation->status === $status)>{{ __('admin.status_' . $status) }}</option>@endforeach</select></form><a href="{{ route('admin.consultations.show', $consultation->id) }}" class="btn btn-sm btn-light" title="{{ __('admin.view_details') }}"><i class="fas fa-eye"></i></a></div></td></tr>
    @empty
        <tr><td colspan="6" class="empty-table"><i class="fas fa-comments"></i><span>{{ __('admin.no_consultations_found') }}</span></td></tr>
    @endforelse
    </tbody></table></div>@if($consultations->hasPages())<div class="pt-3">{{ $consultations->links('pagination::bootstrap-5') }}</div>@endif</section>
@endsection
