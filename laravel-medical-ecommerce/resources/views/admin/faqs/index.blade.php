@extends('admin.layout.app')

@section('title', __('admin.bot_knowledge_management'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">{{ __('admin.bot_knowledge_management') }}</h2>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTopicModal">
            <i class="fas fa-folder-plus me-2"></i> {{ __('admin.add_topic') }}
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal" @disabled($topics->isEmpty())>
            <i class="fas fa-plus me-2"></i> {{ __('admin.add_question') }}
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

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">{{ __('admin.consultation_topics') }}</h5>
        <span class="badge bg-light text-dark border">{{ __('admin.topics_count', ['count' => $topics->count()]) }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">{{ __('admin.order') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.topic_en') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.topic_ar') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.questions') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.status') }}</th>
                        <th class="border-0 px-4 py-3 text-end">{{ __('admin.action') }}</th>
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
                                    {{ $topic->is_active ? __('admin.active') : __('admin.inactive') }}
                                </span>
                            </td>
                            <td class="align-middle px-4 text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit_topic') }}" data-bs-toggle="modal" data-bs-target="#editTopicModal{{ $topic->id }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.faq-topics.destroy', $topic) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete_topic') }}" @disabled($topic->faqs_count > 0)>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('admin.add_first_topic') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">{{ __('admin.topic_questions_answers') }}</h5>
        <span class="text-muted small">{{ __('admin.question_order_hint') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">{{ __('admin.id') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.topic') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.order') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.question_en') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.question_ar') }}</th>
                        <th class="border-0 px-4 py-3">{{ __('admin.status') }}</th>
                        <th class="border-0 px-4 py-3 text-end">{{ __('admin.action') }}</th>
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
                                    <span class="badge bg-warning text-dark">{{ __('admin.unassigned') }}</span>
                                @endif
                            </td>
                            <td class="align-middle px-4">{{ $faq->sort_order }}</td>
                            <td class="align-middle px-4 text-truncate" style="max-width: 260px;" title="{{ $faq->question_en }}">{{ $faq->question_en }}</td>
                            <td class="align-middle px-4 text-truncate" style="max-width: 260px;" title="{{ $faq->question_ar }}" dir="rtl">{{ $faq->question_ar }}</td>
                            <td class="align-middle px-4">
                                <span class="badge {{ $faq->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $faq->is_active ? __('admin.active') : __('admin.inactive') }}
                                </span>
                            </td>
                            <td class="align-middle px-4 text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit_question') }}" data-bs-toggle="modal" data-bs-target="#editFaqModal{{ $faq->id }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete_question') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">{{ __('admin.no_questions_found') }}</td></tr>
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
                    <h5 class="modal-title">{{ __('admin.edit_consultation_topic') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.topic-fields', ['topic' => $topic])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.save_topic') }}</button>
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
                    <h5 class="modal-title">{{ __('admin.edit_question_answer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.faq-fields', ['faq' => $faq, 'topics' => $topics])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.save_question') }}</button>
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
                    <h5 class="modal-title">{{ __('admin.add_consultation_topic') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.topic-fields', ['topic' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.add_topic') }}</button>
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
                    <h5 class="modal-title">{{ __('admin.add_question_answer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.faqs.partials.faq-fields', ['faq' => null, 'topics' => $topics])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.add_question') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
