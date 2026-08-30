@extends('admin.layout.app')

@section('title', __('admin.patient_details') . ' - ' . $patient->name)

@section('content')
@php
    $whatsappPhone = preg_replace('/\D+/', '', (string) ($patient->phone ?? ''));
    if (str_starts_with($whatsappPhone, '00')) $whatsappPhone = substr($whatsappPhone, 2);
    $recordCode = $patient->record_code ?: 'HNV-' . str_pad($patient->id, 6, '0', STR_PAD_LEFT);
@endphp
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.patients') }} · {{ $recordCode }}</p><h1>{{ __('admin.patient_details') }}</h1><p>{{ $patient->name }}</p></div>
    <div class="action-toolbar">
        <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}</a>
        <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i>{{ __('admin.edit') }}</a>
        <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" class="delete-confirm d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i>{{ __('admin.delete') }}</button></form>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <section class="panel-card detail-profile-card mb-4">
            <div class="profile-avatar"><i class="fas fa-user-injured"></i></div>
            <h2>{{ $patient->name }}</h2>
            <span class="record-chip">{{ $recordCode }}</span>
            <div class="info-list text-start mt-4">
                <div><span>{{ __('admin.phone_number') }}</span><strong dir="ltr">{{ $patient->phone }}</strong></div>
                <div><span>{{ __('admin.age') }}</span><strong>{{ $patient->age ?? '-' }}</strong></div>
                <div><span>{{ __('admin.linked_user') }}</span><strong>{{ $patient->user?->name ?? __('admin.no_linked_user') }}</strong></div>
                <div><span>{{ __('admin.address') }}</span><strong>{{ $patient->address ?: __('admin.no_address') }}</strong></div>
            </div>
            @if($whatsappPhone !== '')
                <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success w-100 mt-4"><i class="fab fa-whatsapp me-1"></i>{{ __('admin.open_whatsapp') }}</a>
            @endif
        </section>

        <section class="panel-card mb-4">
            <div class="panel-heading"><div><h3>{{ __('admin.medical_files') }}</h3><p>{{ __('admin.upload_file_hint') }}</p></div></div>
            @if($patient->medical_file)
                <a href="{{ Storage::url($patient->medical_file) }}" target="_blank" class="file-row"><i class="fas fa-file-medical"></i><span>{{ __('admin.primary_medical_file') }}</span><i class="fas fa-external-link-alt"></i></a>
            @endif
            @forelse($patient->documents as $document)
                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="file-row"><i class="fas {{ str_starts_with((string) $document->mime_type, 'image/') ? 'fa-image' : 'fa-file-medical' }}"></i><span class="text-truncate">{{ $document->original_name ?: __('admin.medical_attachment') }}<small class="d-block text-muted">{{ $document->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</small></span><i class="fas fa-external-link-alt"></i></a>
            @empty
                @if(!$patient->medical_file)<p class="empty-copy">{{ __('admin.no_documents') }}</p>@endif
            @endforelse
            <form action="{{ route('admin.patients.documents.store', $patient) }}" method="POST" enctype="multipart/form-data" class="upload-box mt-3">
                @csrf
                <label for="patient_file" class="form-label">{{ __('admin.upload_new_file') }}</label>
                <input id="patient_file" type="file" name="file" class="form-control mb-2" accept="image/*,.pdf" capture="environment" required>
                <input type="text" name="notes" class="form-control mb-2" placeholder="{{ __('admin.optional_note') }}">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-camera me-1"></i>{{ __('admin.upload_file') }}</button>
                <div class="form-text">{{ __('admin.direct_photo_hint') }}</div>
            </form>
        </section>
    </div>

    <div class="col-xl-8">
        <section class="panel-card mb-4">
            <div class="panel-heading"><div><h3>{{ __('admin.medical_history') }}</h3><p>{{ __('admin.patient_notes_hint') }}</p></div></div>
            <p class="mb-0 text-readable">{{ $patient->notes ?: __('admin.no_notes') }}</p>
        </section>

        <section class="panel-card mb-4">
            <div class="panel-heading"><div><h3>{{ __('admin.medical_facts') }}</h3><p>{{ __('admin.medical_facts_hint') }}</p></div></div>
            @forelse($patient->medicalFacts as $fact)
                @php($factStatusKey = trans()->has('admin.fact_' . $fact->status) ? 'admin.fact_' . $fact->status : null)
                <div class="record-row">
                    <div><div class="d-flex flex-wrap gap-2 align-items-center"><span class="record-chip">{{ str_replace('_', ' ', $fact->key) }}</span><span class="status-pill {{ $fact->status === 'confirmed' ? 'success' : ($fact->status === 'ignored' ? 'danger' : 'warning') }}">{{ $factStatusKey ? __($factStatusKey) : ucfirst($fact->status) }}</span><small class="text-muted">{{ __('admin.confidence') }} {{ number_format($fact->confidence * 100, 0) }}%</small></div><p class="mb-1 mt-2">{{ $fact->value }}</p>@if($fact->sourceMessage)<small class="text-muted">{{ __('admin.source_message') }} #{{ $fact->sourceMessage->id }}</small>@endif</div>
                    <div class="action-toolbar"><form action="{{ route('admin.patients.medicalFacts.status', $fact) }}" method="POST">@csrf<input type="hidden" name="status" value="confirmed"><button class="btn btn-sm btn-outline-success" type="submit">{{ __('admin.confirm') }}</button></form><form action="{{ route('admin.patients.medicalFacts.status', $fact) }}" method="POST">@csrf<input type="hidden" name="status" value="ignored"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ __('admin.ignore') }}</button></form></div>
                </div>
            @empty
                <p class="empty-copy">{{ __('admin.no_suggested_facts') }}</p>
            @endforelse
        </section>

        <section class="panel-card mb-4">
            <div class="panel-heading"><div><h3>{{ __('admin.current_approved_progress') }}</h3><p>{{ __('admin.progress_hint') }}</p></div></div>
            <div class="detail-image-grid"><div><h4>{{ __('admin.before') }}</h4>@if($patient->image_before)<img src="{{ Storage::url($patient->image_before) }}" alt="{{ __('admin.before') }}">@else<div class="empty-image">{{ __('admin.no_image') }}</div>@endif</div><div><h4>{{ __('admin.after') }}</h4>@if($patient->image_after)<img src="{{ Storage::url($patient->image_after) }}" alt="{{ __('admin.after') }}">@else<div class="empty-image">{{ __('admin.no_image') }}</div>@endif</div></div>
        </section>

        <section class="panel-card">
            <div class="panel-heading"><div><h3>{{ __('admin.before_after_submissions') }}</h3><p>{{ __('admin.progress_submissions_hint') }}</p></div></div>
            @forelse($patient->progressPhotos as $photo)
                @php($photoStatusKey = trans()->has('admin.photo_' . $photo->status) ? 'admin.photo_' . $photo->status : null)
                <div class="submission-card">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div class="d-flex flex-wrap gap-2"><span class="status-pill {{ $photo->status === 'approved' ? 'success' : ($photo->status === 'rejected' ? 'danger' : 'warning') }}">{{ $photoStatusKey ? __($photoStatusKey) : ucfirst($photo->status) }}</span>@if($photo->consent_for_discount)<span class="record-chip">{{ __('admin.discount_consent') }}</span>@endif @if($photo->coupon)<span class="record-chip">{{ $photo->coupon->code }}</span>@endif</div><small class="text-muted">{{ $photo->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</small></div>
                    <div class="detail-image-grid compact"><div><h4>{{ __('admin.before') }}</h4><img src="{{ Storage::url($photo->before_image) }}" alt="{{ __('admin.before') }}"></div><div><h4>{{ __('admin.after') }}</h4><img src="{{ Storage::url($photo->after_image) }}" alt="{{ __('admin.after') }}"></div></div>
                    @if($photo->status === 'pending')<div class="d-flex flex-wrap gap-2 justify-content-end mt-3"><form action="{{ route('admin.patients.progressPhotos.approve', $photo) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>{{ __('admin.approve_create_discount') }}</button></form><form action="{{ route('admin.patients.progressPhotos.reject', $photo) }}" method="POST" class="d-flex gap-2"><input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="{{ __('admin.rejection_reason') }}"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>{{ __('admin.reject') }}</button></form></div>@elseif($photo->rejection_reason)<div class="alert alert-danger mt-3 mb-0">{{ $photo->rejection_reason }}</div>@endif
                </div>
            @empty
                <p class="empty-copy">{{ __('admin.no_progress_submissions') }}</p>
            @endforelse
        </section>
    </div>
</div>
@endsection
