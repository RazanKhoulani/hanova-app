@extends('admin.layout.app')

@section('title', 'Manage FAQs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>FAQ Management</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
        <i class="fas fa-plus me-2"></i> Add FAQ
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Question (EN)</th>
                        <th class="border-0 px-4 py-3">Question (AR)</th>
                        <th class="border-0 px-4 py-3">Keywords</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    <tr>
                        <td class="align-middle px-4">{{ $faq->id }}</td>
                        <td class="align-middle px-4 text-truncate" style="max-width: 300px;">{{ $faq->question_en }}</td>
                        <td class="align-middle px-4 text-truncate" style="max-width: 300px;">{{ $faq->question_ar }}</td>
                        <td class="align-middle px-4">
                            @foreach(array_filter(array_map('trim', explode(',', $faq->keywords ?? ''))) as $keyword)
                                <span class="badge bg-light text-dark border">{{ $keyword }}</span>
                            @endforeach
                        </td>
                        <td class="align-middle px-4 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit FAQ" data-bs-toggle="modal" data-bs-target="#editFaqModal{{ $faq->id }}">
                                <i class="fas fa-pen"></i>
                            </button>
                             <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="d-inline delete-confirm">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete FAQ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-question-circle fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No FAQs found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($faqs as $faq)
<div class="modal fade" id="editFaqModal{{ $faq->id }}" tabindex="-1" aria-labelledby="editFaqModalLabel{{ $faq->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editFaqModalLabel{{ $faq->id }}">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Question (English)</label>
                            <input type="text" class="form-control" name="question_en" value="{{ $faq->question_en }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Question (Arabic)</label>
                            <input type="text" class="form-control" name="question_ar" value="{{ $faq->question_ar }}" required dir="rtl">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Answer (English)</label>
                            <textarea class="form-control" name="answer_en" rows="4" required>{{ $faq->answer_en }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Answer (Arabic)</label>
                            <textarea class="form-control" name="answer_ar" rows="4" required dir="rtl">{{ $faq->answer_ar }}</textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keywords (Comma separated)</label>
                        <input type="text" class="form-control" name="keywords" value="{{ $faq->keywords }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.faqs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addFaqModalLabel">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Question (English)</label>
                            <input type="text" class="form-control" name="question_en" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Question (Arabic)</label>
                            <input type="text" class="form-control" name="question_ar" required dir="rtl">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Answer (English)</label>
                            <textarea class="form-control" name="answer_en" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Answer (Arabic)</label>
                            <textarea class="form-control" name="answer_ar" rows="3" required dir="rtl"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keywords (Comma separated)</label>
                        <input type="text" class="form-control" name="keywords" placeholder="e.g. acne, هرمونات, علاج">
                    </div>
                </div>
                <div class="modal-header d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
