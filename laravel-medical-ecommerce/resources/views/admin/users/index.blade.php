@extends('admin.layout.app')

@section('title', __('admin.users_management'))

@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">{{ __('admin.system') }}</p>
        <h1>{{ __('admin.users_management') }}</h1>
        <p>{{ __('admin.users_management_hint') }}</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>{{ __('admin.create_user') }}</a>
</div>

<section class="panel-card data-panel">
    <div class="panel-heading">
        <div><h3>{{ __('admin.users') }}</h3><p>{{ __('admin.users_table_hint') }}</p></div>
        <span class="soft-count">{{ $users->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 admin-data-table">
            <thead><tr><th>{{ __('admin.id') }}</th><th>{{ __('admin.full_name') }}</th><th>{{ __('admin.phone_number') }}</th><th>{{ __('admin.registered_at') }}</th><th>{{ __('admin.role') }}</th><th class="text-end">{{ __('admin.action') }}</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                    @php($roleName = $user->roles->first()?->name ?? 'user')
                    <tr class="clickable-row" data-href="{{ route('admin.users.show', $user->id) }}">
                        <td class="fw-bold">#{{ $user->id }}</td>
                        <td><strong>{{ $user->name }}</strong>@if($user->email)<small class="d-block text-muted" dir="ltr">{{ $user->email }}</small>@endif</td>
                        <td dir="ltr">{{ $user->phone }}</td>
                        <td class="text-muted">{{ $user->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                        <td><span class="status-pill info">{{ __('admin.role_' . $roleName) }}</span></td>
                        <td>
                            <div class="action-toolbar justify-content-end">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-light" title="{{ __('admin.view_details') }}" aria-label="{{ __('admin.view_details') }}"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit') }}" aria-label="{{ __('admin.edit') }}"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-confirm d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete') }}" aria-label="{{ __('admin.delete') }}"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-table"><i class="fas fa-users"></i><span>{{ __('admin.no_users_found') }}</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="pt-3">{{ $users->links('pagination::bootstrap-5') }}</div>@endif
</section>
@endsection
