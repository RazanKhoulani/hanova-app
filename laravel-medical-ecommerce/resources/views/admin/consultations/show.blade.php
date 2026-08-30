@extends('admin.layout.app')

@section('title', __('admin.consultation_details') . ' #' . $consultation->id)

@section('content')
@php
    $whatsappPhone = preg_replace('/\D+/', '', (string) ($consultation->user->phone ?? ''));
    if (str_starts_with($whatsappPhone, '00')) $whatsappPhone = substr($whatsappPhone, 2);
    $typeKey = 'admin.type_' . ($consultation->type ?: 'consultation');
    $statusKey = 'admin.status_' . $consultation->status;
@endphp
<div class="page-header"><div><p class="eyebrow">{{ __('admin.consultations') }} · #{{ $consultation->id }}</p><h1>{{ __('admin.consultation_details') }}</h1><p>{{ $consultation->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</p></div><a href="{{ route('admin.consultations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}</a></div>
<div class="row g-4">
    <div class="col-xl-7"><section class="panel-card mb-4"><div class="panel-heading"><div><h3>{{ __('admin.consultation_info') }}</h3><p>{{ __('admin.consultation_details_hint') }}</p></div><span class="status-pill {{ $consultation->status === 'completed' ? 'success' : (in_array($consultation->status, ['cancelled', 'canceled']) ? 'danger' : 'warning') }}">{{ trans()->has($statusKey) ? __($statusKey) : ucfirst($consultation->status) }}</span></div><div class="detail-stat-grid"><div><span>{{ __('admin.type') }}</span><strong>{{ trans()->has($typeKey) ? __($typeKey) : ucfirst(str_replace('_', ' ', $consultation->type)) }}</strong></div><div><span>{{ __('admin.requested_at') }}</span><strong>{{ $consultation->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</strong></div><div><span>{{ __('admin.user') }}</span><strong>{{ $consultation->user?->name ?? __('admin.unknown') }}</strong></div><div><span>{{ __('admin.assigned_doctor') }}</span><strong>{{ $consultation->doctor?->name ?? __('admin.unassigned') }}</strong></div></div>@if($whatsappPhone !== '')<a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success mt-4"><i class="fab fa-whatsapp me-1"></i>{{ __('admin.open_whatsapp') }}</a>@endif</section><section class="panel-card"><div class="panel-heading"><div><h3>{{ __('admin.change_status') }}</h3><p>{{ __('admin.status_update_hint') }}</p></div></div><form action="{{ route('admin.consultations.updateStatus', $consultation->id) }}" method="POST" class="d-flex flex-wrap gap-3 align-items-end">@csrf @method('PUT')<div class="flex-grow-1"><label for="consultation_status" class="form-label">{{ __('admin.status') }}</label><select id="consultation_status" name="status" class="form-select">@foreach(['pending', 'active', 'completed', 'cancelled'] as $status)<option value="{{ $status }}" @selected($consultation->status === $status)>{{ __('admin.status_' . $status) }}</option>@endforeach</select></div><button type="submit" class="btn btn-primary">{{ __('admin.update_consultation') }}</button></form></section></div>
    <div class="col-xl-5"><section class="panel-card h-100"><div class="panel-heading"><div><h3>{{ __('admin.clinical_notes') }}</h3><p>{{ __('admin.notes_hint') }}</p></div></div><p class="text-readable mb-0">{{ $consultation->notes ?: __('admin.no_notes') }}</p></section></div>
</div>
@endsection
