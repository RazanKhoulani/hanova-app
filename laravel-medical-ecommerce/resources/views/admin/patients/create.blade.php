@extends('admin.layout.app')

@section('title', __('admin.create_patient'))

@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">{{ __('admin.patients') }}</p>
        <h1>{{ __('admin.create_patient') }}</h1>
        <p>{{ __('admin.create_patient_hint') }}</p>
    </div>
    <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.patients.store') }}" class="panel-card form-panel">
    @csrf
    @include('admin.patients._form', ['patient' => null])
</form>
@endsection
