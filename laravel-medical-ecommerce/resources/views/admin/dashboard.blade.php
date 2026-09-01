@extends('admin.layout.app')

@section('title', __('admin.dashboard'))

@section('content')
@php
    $statusClass = static fn (string $status): string => match ($status) {
        'completed', 'delivered', 'paid', 'confirmed', 'ready' => 'success',
        'cancelled', 'canceled', 'rejected' => 'danger',
        'pending', 'processing', 'shipped' => 'warning',
        default => '',
    };

    $statusLabel = static fn (string $status): string => trans()->has('admin.status_' . $status)
        ? __('admin.status_' . $status)
        : ucfirst(str_replace('_', ' ', $status));

    $money = static fn ($amount): string => number_format((float) $amount, 0) . ' ل.س';
@endphp

<section class="dashboard-hero">
    <div class="hero-copy">
        <div class="hero-kicker">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            {{ now()->locale(app()->getLocale())->translatedFormat('l، d F Y') }}
        </div>
        <h1>{{ __('admin.hello_name', ['name' => auth()->user()->name]) }}</h1>
        <p>{{ __('admin.dashboard_intro') }}</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('admin.orders.index') }}" class="hero-action">
            <i class="fa-solid fa-bag-shopping"></i>
            {{ __('admin.view_orders') }}
        </a>
        <a href="{{ route('admin.products.create') }}" class="hero-action primary">
            <i class="fa-solid fa-plus"></i>
            {{ __('admin.add_product') }}
        </a>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="metric-card h-100">
            <div class="metric-top">
                <div class="metric-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <span class="metric-note">+{{ $stats['orders_today'] }} {{ __('admin.today') }}</span>
            </div>
            <div class="metric-value">{{ number_format($stats['orders']) }}</div>
            <div class="metric-label">{{ __('admin.total_orders') }}</div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="metric-card h-100" style="--metric-color: #2f8f64; --metric-soft: #e8f6ef;">
            <div class="metric-top">
                <div class="metric-icon"><i class="fa-solid fa-user-doctor"></i></div>
                <span class="metric-note">+{{ $stats['new_patients'] }} {{ __('admin.this_month') }}</span>
            </div>
            <div class="metric-value">{{ number_format($stats['patients']) }}</div>
            <div class="metric-label">{{ __('admin.total_patients') }}</div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="metric-card h-100" style="--metric-color: #c78636; --metric-soft: #fff3df;">
            <div class="metric-top">
                <div class="metric-icon"><i class="fa-regular fa-calendar-check"></i></div>
                <span class="metric-note">{{ $stats['appointments_today'] }} {{ __('admin.today') }}</span>
            </div>
            <div class="metric-value">{{ number_format($stats['upcoming_appointments']) }}</div>
            <div class="metric-label">{{ __('admin.upcoming_appointments') }}</div>
        </article>
    </div>

    <div class="col-sm-6 col-xl-3">
        <article class="metric-card h-100" style="--metric-color: #a24a63; --metric-soft: #fbeaf0;">
            <div class="metric-top">
                <div class="metric-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span class="metric-note">{{ __('admin.completed_orders') }}</span>
            </div>
            <div class="metric-value">{{ $money($stats['revenue']) }}</div>
            <div class="metric-label">{{ __('admin.total_revenue') }}</div>
        </article>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <section class="panel-card">
            <div class="panel-heading">
                <div>
                    <h3>{{ __('admin.recent_orders') }}</h3>
                    <p>{{ __('admin.recent_orders_hint') }}</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="panel-link">
                    {{ __('admin.view_all') }} <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} ms-1"></i>
                </a>
            </div>

            <div class="order-list">
                @forelse($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="dashboard-list-item">
                        <span class="list-icon"><i class="fa-solid fa-receipt"></i></span>
                        <span class="list-copy">
                            <strong>{{ __('admin.order_number', ['number' => $order->id]) }} · {{ $order->user->name ?? __('admin.unknown_customer') }}</strong>
                            <small>{{ $order->created_at->locale(app()->getLocale())->translatedFormat('d M Y، h:i A') }}</small>
                        </span>
                        <strong class="d-none d-sm-block" style="font-size: 11px; white-space: nowrap;">{{ $money($order->total_amount) }}</strong>
                        <span class="status-pill {{ $statusClass($order->status) }}">{{ $statusLabel($order->status) }}</span>
                    </a>
                @empty
                    <div class="empty-panel">
                        <div><i class="fa-solid fa-bag-shopping"></i>{{ __('admin.no_orders_yet') }}</div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="panel-card">
            <div class="panel-heading">
                <div>
                    <h3>{{ __('admin.next_appointments') }}</h3>
                    <p>{{ __('admin.next_appointments_hint') }}</p>
                </div>
                <a href="{{ route('admin.appointments.index') }}" class="panel-link">{{ __('admin.view_all') }}</a>
            </div>

            <div class="appointment-list">
                @forelse($upcomingAppointments as $appointment)
                    @php
                        $appointmentDate = \Carbon\Carbon::parse($appointment->date);
                        $typeKey = 'admin.type_' . ($appointment->type ?? 'clinic');
                        $typeLabel = trans()->has($typeKey) ? __($typeKey) : ucfirst($appointment->type ?? 'clinic');
                    @endphp
                    <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="dashboard-list-item">
                        <span class="list-date">
                            <span>
                                <strong>{{ $appointmentDate->format('d') }}</strong>
                                <small>{{ $appointmentDate->locale(app()->getLocale())->translatedFormat('M') }}</small>
                            </span>
                        </span>
                        <span class="list-copy">
                            <strong>{{ $appointment->patient->name ?? __('admin.unknown_patient') }}</strong>
                            <small>{{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }} · {{ $typeLabel }}</small>
                        </span>
                        <span class="status-pill {{ $statusClass($appointment->status) }}">{{ $statusLabel($appointment->status) }}</span>
                    </a>
                @empty
                    <div class="empty-panel">
                        <div><i class="fa-regular fa-calendar-xmark"></i>{{ __('admin.no_upcoming_appointments') }}</div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="d-flex justify-content-between align-items-end mb-3">
    <div>
        <h3 class="mb-1" style="font-size: 16px;">{{ __('admin.quick_actions') }}</h3>
        <p class="text-muted mb-0" style="font-size: 10px;">{{ __('admin.quick_actions_hint') }}</p>
    </div>
</div>

<div class="quick-actions">
    <a href="{{ route('admin.products.create') }}" class="quick-action-card">
        <i class="fa-solid fa-pump-soap"></i>
        <span><strong>{{ __('admin.add_product') }}</strong><small>{{ $stats['products'] }} {{ __('admin.products_available') }}</small></span>
    </a>
    <a href="{{ route('admin.notifications.index') }}" class="quick-action-card">
        <i class="fa-regular fa-bell"></i>
        <span><strong>{{ __('admin.send_notification') }}</strong><small>{{ __('admin.notify_customers') }}</small></span>
    </a>
    <a href="{{ route('admin.chats.index') }}" class="quick-action-card">
        <i class="fa-regular fa-message"></i>
        <span><strong>{{ __('admin.open_chats') }}</strong><small>{{ $stats['conversations'] }} {{ __('admin.conversations') }}</small></span>
    </a>
    <a href="{{ route('admin.consultations.index') }}" class="quick-action-card">
        <i class="fa-solid fa-stethoscope"></i>
        <span><strong>{{ __('admin.consultations') }}</strong><small>{{ $stats['consultations'] }} {{ __('admin.consultation_records') }}</small></span>
    </a>
</div>
@endsection
