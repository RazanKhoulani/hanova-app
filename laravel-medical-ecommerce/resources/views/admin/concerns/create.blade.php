@extends('admin.layout.app')

@section('title', __('admin.add_concern_page'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.add_concern_page') }}</h2>
    <a href="{{ route('admin.concerns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.concerns.store') }}" method="POST">
            @csrf

            @include('admin.concerns.form', ['concern' => null])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
    </div>
</div>
@endsection
