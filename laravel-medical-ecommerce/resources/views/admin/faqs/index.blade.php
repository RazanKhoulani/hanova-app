@extends('admin.layout.app')

@section('title', 'Manage Bot Knowledge')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Bot Knowledge Management</h2>
        <p class="text-muted mb-0">Flow: consultation topics → topic questions → answer → remaining questions.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTopicModal">
            <i class="fas fa-folder-plus me-2"></i> Add Topic
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal" @disabled($topics->isEmpty())>
            <i class="fas fa-plus me-2"></i> Add Question
        </button>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-light border d-flex align-items-start gap-3 mb-4">
    <i class="fas fa-sitemap text-primary mt-1"></i>
    <div>
        <strong>How the app uses this data</strong>
        <div class="text-muted">Only active topics containing active questions appear in the bot. Question order controls the sequence shown after a topic is selected and after each answer.</div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">Consultation Topics</h5>
        <span class="badge bg-light text-dark border">{{ $topics->count() }} topics</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">Order</th>
                        <th class="border-0 px-4 py-3">Topic (EN)</th>
                        <th class="border-0 px-4 py-3">Topic (AR)</th>
                        <th class="border-0 px-4 py-3">Questions</th>
                        <th class="border-0 px-4 py-3">Status</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topics as $topic)
                        <tr>
                            <td class="align-middle px-4">{{ $topic->sort_order }}</td>
                            <td class="align-middle px-4">
                                <div class="fw-semibold">{{ $topic->name_en }}</div>
                                <small class="text-muted">{{ $topic->description_en }}</small>
                            </td>
                            <td class="align-middle px-4" dir="rtl">
                                <div class="fw-semibold">{{ $topic->name_ar }}</div>
                                <small class="text-muted">{{ $topic->description_ar }}</small>
                            </td>
                            <td class="align-middle px-4"><span class="badge bg-primary">{{ $topic->faqs_count }}</span></td>
                            <td class="align-middle px-4">
                                <span class="badge {{ $topic->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $topic->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="align-middle px-4 text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Edit topic" data-bs-toggle="modal" data-bs-target="#editTopicModal{{ $topic->id }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.faq-topics.destroy', $topic) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete topic" @disabled($topic->faqs_count > 0)>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Add the first consultation topic before adding questions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">Topic Questions & Answers</h5>
        <span class="text-muted small">Drag-free ordering: edit the order number for each question.</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Topic</th>
                        <th class="border-0 px-4 py-3">Order</th>
                        <th class="border-0 px-4 py-3">Question (EN)</th>
                        <th class="border-0 px-4 py-3">Question (AR)</th>
                        <th class="border-0 px-4 py-3">Status</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                        <tr>
                            <td class="align-middle px-4">{{ $faq->id }}</td>
                            <td class="align-middle px-4">
                                @if($faq->topic)
                                    <span class="badge bg-light text-dark border">{{ $faq->topic->name_en }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">Unassigned</span>
                                @endif
                            </td>
                            <td class="align-middle px-4">{{ $faq->sort_order }}</td>
                            <td class="align-middle px-4 text-truncate" style="max-width: 260px;" title="{{ $faq->question_en }}">{{ $faq->question_en }}</td>
                            <td class="align-middle px-4 text-truncate" style="max-width: 260px;" title="{{ $faq->question_ar }}" dir="rtl">{{ $faq->question_ar }}</td>
                            <td class="align-middle px-4">
                                <span class="badge {{ $faq->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $faq->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="align-middle px-4 text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Edit question" data-bs-toggle="modal" data-bs-target="#editFaqModal{{ $faq->id }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete question">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No questions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($faqs->hasPages())
        <div class="card-footer bg-white py-3">{{ $faqs->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@foreach($topics as $topic)
<div class="modal fade" id="editTopicModal{{ $topic->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.faq-topics.update', $topic) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Consultation Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.topic-fields', ['topic' => $topic])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Topic</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($faqs as $faq)
<div class="modal fade" id="editFaqModal{{ $faq->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Question & Answer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.faq-fields', ['faq' => $faq, 'topics' => $topics])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="addTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.faq-topics.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Consultation Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.topic-fields', ['topic' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Topic</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('admin.faqs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Question & Answer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.faq-fields', ['faq' => null, 'topics' => $topics])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
