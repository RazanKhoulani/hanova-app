@extends('admin.layout.app')

@section('title', __('admin.add_concern_page'))

@section('content')
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.treatment_concerns') }}</p><h1>{{ __('admin.add_concern_page') }}</h1><p>{{ __('admin.arabic') }} / {{ __('admin.english') }}</p></div>
    <a href="{{ route('admin.concerns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<section class="panel-card form-panel">
        <form action="{{ route('admin.concerns.store') }}" method="POST">
            @csrf

            @include('admin.concerns.form', ['concern' => null])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
</section>
@endsection
