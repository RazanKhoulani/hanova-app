@extends('admin.layout.app')

@section('title', __('admin.patients_management'))

@section('content')
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.clinic') }}</p><h1>{{ __('admin.patients_management') }}</h1><p>{{ __('admin.patients_management_hint') }}</p></div>
    <div class="action-toolbar"><a href="{{ route('admin.patients.export') }}" class="btn btn-outline-primary"><i class="fas fa-file-export me-1"></i>{{ __('admin.export_excel') }}</a><a href="{{ route('admin.patients.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>{{ __('admin.create_patient') }}</a></div>
</div>
<form method="GET" action="{{ route('admin.patients.index') }}" class="panel-card search-panel mb-4"><div class="input-group"><input type="search" name="query" class="form-control" value="{{ $search }}" placeholder="{{ __('admin.search_patients') }}"><button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>{{ __('admin.search') }}</button></div></form>
<section class="panel-card data-panel">
    <div class="panel-heading"><div><h3>{{ __('admin.patients') }}</h3><p>{{ __('admin.patient_table_hint') }}</p></div><span class="soft-count">{{ $patients->total() }}</span></div>
    <div class="table-responsive"><table class="table align-middle mb-0 admin-data-table"><thead><tr><th>{{ __('admin.record_code') }}</th><th>{{ __('admin.full_name') }}</th><th>{{ __('admin.age') }}</th><th>{{ __('admin.phone_number') }}</th><th>{{ __('admin.linked_user') }}</th><th>{{ __('admin.registered_at') }}</th><th class="text-end">{{ __('admin.action') }}</th></tr></thead><tbody>
        @forelse($patients as $patient)
            <tr class="clickable-row" data-href="{{ route('admin.patients.show', $patient->id) }}"><td><span class="record-chip">{{ $patient->record_code ?: 'HNV-' . str_pad($patient->id, 6, '0', STR_PAD_LEFT) }}</span></td><td class="fw-bold">{{ $patient->name }}</td><td>{{ $patient->age ?? '—' }}</td><td dir="ltr">{{ $patient->phone }}</td><td>{{ $patient->user?->name ?? __('admin.no_linked_user') }}</td><td class="text-muted">{{ $patient->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td><td><div class="action-toolbar justify-content-end"><a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-sm btn-light" title="{{ __('admin.view_patient_file') }}" aria-label="{{ __('admin.view_patient_file') }}"><i class="fas fa-folder-open"></i></a><a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit') }}" aria-label="{{ __('admin.edit') }}"><i class="fas fa-pen"></i></a><form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" class="delete-confirm d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete') }}" aria-label="{{ __('admin.delete') }}"><i class="fas fa-trash"></i></button></form></div></td></tr>
        @empty
            <tr><td colspan="7" class="empty-table"><i class="fas fa-user-injured"></i><span>{{ __('admin.no_patients_found') }}</span></td></tr>
        @endforelse
    </tbody></table></div>
    @if($patients->hasPages())<div class="pt-3">{{ $patients->links('pagination::bootstrap-5') }}</div>@endif
</section>
@endsection
