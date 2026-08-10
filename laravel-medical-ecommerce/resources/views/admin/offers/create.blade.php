@extends('admin.layout.app')

@section('title', 'Add Offer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Offer</h2>
    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.offers.form', ['offer' => null])

            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i> Save Offer
            </button>
        </form>
    </div>
</div>
@endsection
