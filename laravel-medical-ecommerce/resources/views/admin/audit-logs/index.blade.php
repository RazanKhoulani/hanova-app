@extends('admin.layout.app')

@section('title', __('admin.audit_log'))

@section('content')
<div class="page-header">
    <div>
        <span class="page-eyebrow">HANOVA SECURITY</span>
        <h2>{{ __('admin.audit_log') }}</h2>
        <p>{{ __('admin.audit_log_hint') }}</p>
    </div>
</div>

<section class="panel-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">{{ __('admin.action') }}</label>
            <select name="action" class="form-select">
                <option value="">{{ __('admin.all') }}</option>
                @foreach($actions as $action)
                    @php($actionKey = 'admin.audit_action_' . str_replace('.', '_', $action))
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ trans()->has($actionKey) ? __($actionKey) : $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">{{ __('admin.staff_member') }}</label>
            <select name="user_id" class="form-select">
                <option value="">{{ __('admin.all') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('admin.filter') }}</button></div>
    </form>
</section>

<section class="panel-card data-panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0 admin-data-table">
            <thead><tr><th>{{ __('admin.date') }}</th><th>{{ __('admin.staff_member') }}</th><th>{{ __('admin.action') }}</th><th>{{ __('admin.record') }}</th><th>{{ __('admin.ip_address') }}</th><th>{{ __('admin.details') }}</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    @php($actionKey = 'admin.audit_action_' . str_replace('.', '_', $log->action))
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at?->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</td>
                        <td>{{ $log->user?->name ?? __('admin.system') }}</td>
                        <td><span class="badge bg-light text-dark">{{ trans()->has($actionKey) ? __($actionKey) : $log->action }}</span></td>
                        <td dir="ltr">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        <td dir="ltr">{{ $log->ip_address ?: '-' }}</td>
                        <td><small class="text-muted">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '-' }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">{{ __('admin.no_audit_logs') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
