@extends('admin.layout.app')

@section('title', __('admin.add_offer_page'))

@section('content')
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.offers_management') }}</p><h1>{{ __('admin.add_offer_page') }}</h1><p>{{ __('admin.dates') }}</p></div>
    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<section class="panel-card form-panel">
        <form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.offers.form', ['offer' => null])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
</section>
@endsection
