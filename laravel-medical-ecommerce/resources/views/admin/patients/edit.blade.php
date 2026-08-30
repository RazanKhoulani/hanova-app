@extends('admin.layout.app')

@section('title', __('admin.edit_patient'))

@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">{{ __('admin.patients') }}</p>
        <h1>{{ __('admin.edit_patient') }}</h1>
        <p>{{ __('admin.edit_patient_hint') }}</p>
    </div>
    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_file') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.patients.update', $patient->id) }}" class="panel-card form-panel">
    @csrf
    @method('PUT')
    @include('admin.patients._form', ['patient' => $patient])
</form>
@endsection
