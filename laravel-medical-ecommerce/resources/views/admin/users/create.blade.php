@extends('admin.layout.app')

@section('title', __('admin.create_user'))

@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">{{ __('admin.users') }}</p>
        <h1>{{ __('admin.create_user') }}</h1>
        <p>{{ __('admin.create_user_hint') }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" class="panel-card form-panel">
    @csrf
    @include('admin.users._form', ['user' => null])
</form>
@endsection
