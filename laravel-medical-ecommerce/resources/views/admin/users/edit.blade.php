@extends('admin.layout.app')

@section('title', __('admin.edit_user'))

@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">{{ __('admin.users') }}</p>
        <h1>{{ __('admin.edit_user') }}</h1>
        <p>{{ __('admin.edit_user_hint') }}</p>
    </div>
    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_profile') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="panel-card form-panel">
    @csrf
    @method('PUT')
    @include('admin.users._form', ['user' => $user])
</form>
@endsection
