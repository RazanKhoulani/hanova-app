@extends('admin.layout.app')

@section('title', __('admin.edit_offer_page'))

@section('content')
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.offers_management') }}</p><h1>{{ __('admin.edit_offer_page') }}</h1><p>{{ app()->getLocale() === 'ar' ? $offer->title_ar : $offer->title_en }}</p></div>
    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<section class="panel-card form-panel">
        <form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.offers.form', ['offer' => $offer])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
</section>
@endsection
