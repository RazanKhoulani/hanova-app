@extends('admin.layout.app')

@section('title', __('admin.add_offer_page'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.add_offer_page') }}</h2>
    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.offers.form', ['offer' => null])

            <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
            </button>
        </form>
    </div>
</div>
@endsection
