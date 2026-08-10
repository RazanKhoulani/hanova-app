@extends('admin.layout.app')

@section('title', 'Patient Details - ' . $patient->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Patient Details</h2>
    <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Basic Information</div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $patient->name }}</p>
                <p><strong>Age:</strong> {{ $patient->age ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $patient->phone }}</p>
                <p><strong>Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
                <p><strong>Linked User:</strong> {{ $patient->user->name ?? 'None' }}</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Medical Files</div>
            <div class="card-body">
                @if($patient->medical_file)
                    <a href="{{ Storage::url($patient->medical_file) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-2"></i> View Medical File
                    </a>
                @else
                    <p class="text-muted">No files uploaded</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Medical History & Notes</div>
            <div class="card-body">
                <p>{{ $patient->notes ?? 'No notes available' }}</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Suggested Medical Facts From Chat</div>
            <div class="card-body">
                @forelse($patient->medicalFacts as $fact)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary">{{ str_replace('_', ' ', $fact->key) }}</span>
                                <span class="badge bg-secondary">{{ ucfirst($fact->status) }}</span>
                                <span class="text-muted small">Confidence: {{ number_format($fact->confidence * 100, 0) }}%</span>
                            </div>
                            <small class="text-muted">{{ $fact->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                        <p class="mb-1 mt-2">{{ $fact->value }}</p>
                        @if($fact->sourceMessage)
                            <small class="text-muted">Source message #{{ $fact->sourceMessage->id }}</small>
                        @endif
                        <div class="d-flex gap-2 justify-content-end mt-2">
                            <form action="{{ route('admin.patients.medicalFacts.status', $fact) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="confirmed">
                                <button class="btn btn-sm btn-outline-success" type="submit">Confirm</button>
                            </form>
                            <form action="{{ route('admin.patients.medicalFacts.status', $fact) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="ignored">
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Ignore</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No suggested facts extracted yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Current Approved Progress</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <p class="text-muted fw-bold">BEFORE</p>
                        @if($patient->image_before)
                            <img src="{{ Storage::url($patient->image_before) }}" class="img-fluid rounded" style="max-height: 300px;">
                        @else
                            <div class="bg-light p-5 rounded">No Image</div>
                        @endif
                    </div>
                    <div class="col-6">
                        <p class="text-muted fw-bold">AFTER</p>
                        @if($patient->image_after)
                            <img src="{{ Storage::url($patient->image_after) }}" class="img-fluid rounded" style="max-height: 300px;">
                        @else
                            <div class="bg-light p-5 rounded">No Image</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Before / After Submissions</div>
            <div class="card-body">
                @forelse($patient->progressPhotos as $photo)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-{{ $photo->status === 'approved' ? 'success' : ($photo->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($photo->status) }}
                                </span>
                                @if($photo->consent_for_discount)
                                    <span class="badge bg-info text-dark">Discount consent</span>
                                @endif
                                @if($photo->coupon)
                                    <span class="badge bg-success">Coupon: {{ $photo->coupon->code }}</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $photo->created_at->format('Y-m-d H:i') }}</small>
                        </div>

                        <div class="row text-center">
                            <div class="col-6">
                                <p class="text-muted fw-bold">BEFORE</p>
                                <img src="{{ Storage::url($photo->before_image) }}" class="img-fluid rounded" style="max-height: 220px;">
                            </div>
                            <div class="col-6">
                                <p class="text-muted fw-bold">AFTER</p>
                                <img src="{{ Storage::url($photo->after_image) }}" class="img-fluid rounded" style="max-height: 220px;">
                            </div>
                        </div>

                        @if($photo->status === 'pending')
                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <form action="{{ route('admin.patients.progressPhotos.approve', $photo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i> Approve + Create Discount
                                    </button>
                                </form>
                                <form action="{{ route('admin.patients.progressPhotos.reject', $photo) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Optional reason">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times me-1"></i> Reject
                                    </button>
                                </form>
                            </div>
                        @elseif($photo->rejection_reason)
                            <div class="alert alert-danger mt-3 mb-0">{{ $photo->rejection_reason }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No progress photo submissions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
