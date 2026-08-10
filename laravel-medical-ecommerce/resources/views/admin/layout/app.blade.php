<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard')) | {{ __('admin.brand') }}</title>

    <link rel="preload" href="{{ asset('fonts/Tajawal-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Tajawal-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>
    @php
        $currentUser = auth()->user();
        $roleName = $currentUser->getRoleNames()->first() ?? 'staff';
        $roleKey = 'admin.role_' . $roleName;
        $roleLabel = trans()->has($roleKey) ? __($roleKey) : ucfirst($roleName);
        $userInitial = mb_strtoupper(mb_substr($currentUser->name ?? 'A', 0, 1));
        $isDelivery = $currentUser->hasRole('delivery');
    @endphp

    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="{{ __('admin.main_navigation') }}">
            <a href="{{ $isDelivery ? route('admin.orders.index') : route('admin.dashboard') }}" class="sidebar-brand">
                <span class="brand-mark"><i class="fa-solid fa-spa"></i></span>
                <span class="brand-copy">
                    <strong>{{ __('admin.brand') }}</strong>
                    <small>{{ __('admin.clinic_management') }}</small>
                </span>
            </a>

            <div class="sidebar-scroll">
                <div class="nav-section-label">{{ __('admin.overview') }}</div>
                <nav class="sidebar-nav">
                    @unless($isDelivery)
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-table-cells-large"></i>
                            <span>{{ __('admin.dashboard') }}</span>
                        </a>
                    @endunless
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>{{ __('admin.orders') }}</span>
                    </a>
                    @unless($isDelivery)
                        <a href="{{ route('admin.appointments.index') }}" class="sidebar-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>{{ __('admin.appointments') }}</span>
                        </a>
                    @endunless
                </nav>

                @unless($isDelivery)
                    <div class="nav-section-label">{{ __('admin.clinic') }}</div>
                    <nav class="sidebar-nav">
                        <a href="{{ route('admin.patients.index') }}" class="sidebar-link {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-doctor"></i>
                            <span>{{ __('admin.patients') }}</span>
                        </a>
                        <a href="{{ route('admin.consultations.index') }}" class="sidebar-link {{ request()->routeIs('admin.consultations.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-stethoscope"></i>
                            <span>{{ __('admin.consultations') }}</span>
                        </a>
                        <a href="{{ route('admin.chats.index') }}" class="sidebar-link {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}">
                            <i class="fa-regular fa-message"></i>
                            <span>{{ __('admin.chats') }}</span>
                        </a>
                    </nav>

                    <div class="nav-section-label">{{ __('admin.store') }}</div>
                    <nav class="sidebar-nav">
                        <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-pump-soap"></i>
                            <span>{{ __('admin.products') }}</span>
                        </a>
                        <a href="{{ route('admin.concerns.index') }}" class="sidebar-link {{ request()->routeIs('admin.concerns.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>{{ __('admin.concerns') }}</span>
                        </a>
                        <a href="{{ route('admin.offers.index') }}" class="sidebar-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-tags"></i>
                            <span>{{ __('admin.offers') }}</span>
                        </a>
                    </nav>

                    <div class="nav-section-label">{{ __('admin.system') }}</div>
                    <nav class="sidebar-nav">
                        <a href="{{ route('admin.notifications.index') }}" class="sidebar-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="fa-regular fa-bell"></i>
                            <span>{{ __('admin.notifications') }}</span>
                        </a>
                        <a href="{{ route('admin.faqs.index') }}" class="sidebar-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                            <i class="fa-regular fa-circle-question"></i>
                            <span>{{ __('admin.faqs') }}</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>{{ __('admin.users') }}</span>
                        </a>
                    </nav>
                @endunless
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar">{{ $userInitial }}</div>
                    <div class="sidebar-user-copy">
                        <strong>{{ $currentUser->name }}</strong>
                        <small>{{ $roleLabel }}</small>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>
                        {{ __('admin.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <button type="button" class="sidebar-overlay" id="sidebarOverlay" aria-label="{{ __('admin.close_menu') }}"></button>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-start">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-controls="adminSidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    <div class="topbar-title">
                        <small>{{ __('admin.admin_dashboard') }}</small>
                        <strong>@yield('title', __('admin.dashboard'))</strong>
                    </div>
                </div>

                <div class="topbar-actions">
                    @unless($isDelivery)
                        <a href="{{ route('admin.notifications.index') }}" class="topbar-icon" title="{{ __('admin.notifications') }}">
                            <i class="fa-regular fa-bell"></i>
                            <span class="notification-dot"></span>
                        </a>
                    @endunless

                    <div class="dropdown">
                        <button class="language-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-globe"></i>
                            <span>{{ app()->getLocale() === 'ar' ? __('admin.arabic') : __('admin.english') }}</span>
                            <i class="fa-solid fa-chevron-down fa-2xs"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('language.switch', 'ar') }}">{{ __('admin.arabic') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">{{ __('admin.english') }}</a></li>
                        </ul>
                    </div>

                    <div class="topbar-profile">
                        <div class="topbar-avatar">{{ $userInitial }}</div>
                        <div class="topbar-profile-copy">
                            <strong>{{ $currentUser->name }}</strong>
                            <small>{{ $roleLabel }}</small>
                        </div>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-regular fa-circle-check me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.close') }}"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>{{ __('admin.validation_errors') }}:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.close') }}"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const toggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            const closeSidebar = () => {
                body.classList.remove('sidebar-open');
                toggle?.setAttribute('aria-expanded', 'false');
            };

            toggle?.addEventListener('click', () => {
                const isOpen = body.classList.toggle('sidebar-open');
                toggle.setAttribute('aria-expanded', String(isOpen));
            });
            overlay?.addEventListener('click', closeSidebar);
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) closeSidebar();
            });

            document.querySelectorAll('.clickable-row').forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, input, select, textarea, form, label')) return;
                    const url = row.dataset.href;
                    if (url) window.location.href = url;
                });
            });

            document.querySelectorAll('.delete-confirm').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    if (window.confirm(@json(__('admin.delete_confirm')))) form.submit();
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
