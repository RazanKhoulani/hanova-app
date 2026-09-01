@extends('admin.layout.app')

@section('title', __('admin.edit_concern_page'))

@section('content')
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.treatment_concerns') }}</p><h1>{{ __('admin.edit_concern_page') }}</h1><p>{{ app()->getLocale() === 'ar' ? $concern->name_ar : $concern->name_en }}</p></div>
    <a href="{{ route('admin.concerns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<section class="panel-card form-panel">
        <form action="{{ route('admin.concerns.update', $concern) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.concerns.form', ['concern' => $concern])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
</section>
@endsection
