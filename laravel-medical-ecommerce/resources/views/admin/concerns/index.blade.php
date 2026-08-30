@extends('admin.layout.app')

@section('title', __('admin.treatment_concerns'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.treatment_concerns') }}</h2>
    <a href="{{ route('admin.concerns.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('admin.add_concern') }}
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.arabic') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.english') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.slug') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.status') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($concerns as $concern)
                        <tr>
                            <td class="align-middle px-4" dir="rtl">{{ $concern->name_ar }}</td>
                            <td class="align-middle px-4">{{ $concern->name_en }}</td>
                            <td class="align-middle px-4"><code>{{ $concern->slug }}</code></td>
                            <td class="align-middle px-4">
                                <span class="badge bg-{{ $concern->is_active ? 'success' : 'secondary' }}">{{ $concern->is_active ? __('admin.active') : __('admin.hidden') }}</span>
                            </td>
                            <td class="align-middle px-4 text-end">
                                <a href="{{ route('admin.concerns.edit', $concern) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.concerns.destroy', $concern) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">{{ __('admin.no_concerns_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($concerns->hasPages())
        <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
            {{ $concerns->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
