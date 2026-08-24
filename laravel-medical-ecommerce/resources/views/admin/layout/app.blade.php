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
                <span class="brand-mark"><img src="{{ asset('images/hanova-mark.svg') }}" alt="" width="30" height="30"></span>
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
                    <a href="{{ route('site.home') }}" class="topbar-icon" target="_blank" rel="noopener" title="Hanova website">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    @unless($isDelivery)
                        @php($adminUnreadNotifications = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count())
                        @php($adminRecentNotifications = \App\Models\Notification::where('user_id', auth()->id())->latest()->limit(8)->get())
                        <div class="dropdown">
                            <button class="topbar-icon position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('admin.notifications') }}">
                                <i class="fa-regular fa-bell"></i>
                                <span id="adminNotificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $adminUnreadNotifications ? '' : 'd-none' }}">{{ $adminUnreadNotifications }}</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="adminNotificationDropdown">
                                <div class="notification-dropdown-header">
                                    <strong>{{ app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications' }}</strong>
                                    <button type="button" id="markAllNotificationsRead">{{ app()->getLocale() === 'ar' ? 'تعيين الكل كمقروء' : 'Mark all as read' }}</button>
                                </div>
                                <div id="adminNotificationItems">
                                    @forelse($adminRecentNotifications as $alert)
                                        <a href="{{ $alert->adminUrl() }}" class="notification-dropdown-item {{ $alert->is_read ? '' : 'unread' }}" data-notification-id="{{ $alert->id }}">
                                            <span class="notification-type-icon"><i class="fa-solid {{ $alert->type === 'chat_message' ? 'fa-comment' : (str_contains($alert->type, 'appointment') ? 'fa-calendar-check' : (str_starts_with($alert->type, 'order_') ? 'fa-bag-shopping' : 'fa-bell')) }}"></i></span>
                                            <span><strong>{{ $alert->title }}</strong><small>{{ \Illuminate\Support\Str::limit($alert->body, 75) }}</small><time>{{ $alert->created_at?->diffForHumans() }}</time></span>
                                        </a>
                                    @empty
                                        <div class="notification-dropdown-empty">{{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات' : 'No notifications' }}</div>
                                    @endforelse
                                </div>
                                <a href="{{ route('admin.notifications.index') }}" class="notification-dropdown-footer">{{ app()->getLocale() === 'ar' ? 'عرض كل الإشعارات' : 'View all notifications' }}</a>
                            </div>
                        </div>
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        {{ session('error') }}
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

    @unless($isDelivery)
    <script>
        (() => {
            const badge = document.getElementById('adminNotificationBadge');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const items = document.getElementById('adminNotificationItems');
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
            const iconFor = (notification) => notification.url.includes('/chats/') ? 'fa-comment' : (notification.url.includes('/appointments/') ? 'fa-calendar-check' : (notification.url.includes('/orders/') ? 'fa-bag-shopping' : 'fa-bell'));
            const renderItems = (notifications) => {
                if (!notifications.length) {
                    items.innerHTML = '<div class="notification-dropdown-empty">{{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات' : 'No notifications' }}</div>';
                    return;
                }
                items.innerHTML = notifications.map(notification => `<a href="${escapeHtml(notification.url)}" class="notification-dropdown-item ${notification.is_read ? '' : 'unread'}" data-notification-id="${notification.id}"><span class="notification-type-icon"><i class="fa-solid ${iconFor(notification)}"></i></span><span><strong>${escapeHtml(notification.title)}</strong><small>${escapeHtml(notification.body)}</small><time>${escapeHtml(notification.created_at)}</time></span></a>`).join('');
            };
            const refreshNotifications = async () => {
                try {
                    const response = await fetch(@json(route('admin.notifications.unreadCount')), {headers: {'Accept': 'application/json'}});
                    if (!response.ok) return;
                    const data = await response.json();
                    const count = Number(data.count || 0);
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count === 0);
                    renderItems(data.notifications || []);
                } catch (_) {}
            };
            document.addEventListener('click', async (event) => {
                const link = event.target.closest('[data-notification-id]');
                if (!link) return;
                event.preventDefault();
                try {
                    await fetch(`{{ url('/admin/notifications') }}/${link.dataset.notificationId}/read`, {method: 'PUT', headers: {'Accept':'application/json', 'X-CSRF-TOKEN':csrf}});
                } finally {
                    window.location.href = link.href;
                }
            });
            document.getElementById('markAllNotificationsRead')?.addEventListener('click', async (event) => {
                event.stopPropagation();
                await fetch(@json(route('admin.notifications.readAll')), {method:'PUT', headers:{'Accept':'application/json', 'X-CSRF-TOKEN':csrf}});
                await refreshNotifications();
            });
            window.setInterval(refreshNotifications, 15000);

            const firebaseConfig = @json([
                'apiKey' => config('services.firebase.web_api_key'),
                'appId' => config('services.firebase.web_app_id'),
                'messagingSenderId' => config('services.firebase.messaging_sender_id'),
                'projectId' => config('services.firebase.project_id', 'hanva-app'),
            ]);
            const vapidKey = @json(config('services.firebase.web_vapid_key'));
            if (vapidKey && 'serviceWorker' in navigator && 'Notification' in window) {
                import('https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js').then(async ({initializeApp}) => {
                    const {getMessaging, getToken, onMessage} = await import('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging.js');
                    const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission();
                    if (permission !== 'granted') return;
                    const messaging = getMessaging(initializeApp(firebaseConfig));
                    const token = await getToken(messaging, {vapidKey, serviceWorkerRegistration: registration});
                    if (token) await fetch(@json(route('admin.notifications.deviceToken')), {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({token})});
                    onMessage(messaging, () => refreshNotifications());
                }).catch(error => console.warn('Firebase web notifications are unavailable.', error));
            }
        })();
    </script>
    @endunless

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered delete-confirm-dialog">
            <div class="modal-content">
                <div class="modal-body text-center p-4 p-md-5">
                    <div class="delete-confirm-icon" aria-hidden="true">
                        <i class="fa-regular fa-trash-can"></i>
                    </div>
                    <h5 class="mt-3 mb-2" id="deleteConfirmTitle">{{ __('admin.confirm_delete') }}</h5>
                    <p class="text-muted mb-4">{{ __('admin.delete_confirm') }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            {{ __('admin.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteButton">
                            <span class="delete-button-label">{{ __('admin.delete') }}</span>
                            <span class="delete-button-loading d-none">
                                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                {{ __('admin.deleting') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

            const deleteModalElement = document.getElementById('deleteConfirmModal');
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            const deleteModal = deleteModalElement
                ? bootstrap.Modal.getOrCreateInstance(deleteModalElement)
                : null;
            let pendingDeleteForm = null;

            document.querySelectorAll('.delete-confirm').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    if (!deleteModal || form.dataset.submitting === 'true') return;

                    pendingDeleteForm = form;
                    deleteModal.show();
                });
            });

            confirmDeleteButton?.addEventListener('click', () => {
                if (!pendingDeleteForm || pendingDeleteForm.dataset.submitting === 'true') return;

                pendingDeleteForm.dataset.submitting = 'true';
                confirmDeleteButton.disabled = true;
                confirmDeleteButton.querySelector('.delete-button-label')?.classList.add('d-none');
                confirmDeleteButton.querySelector('.delete-button-loading')?.classList.remove('d-none');
                HTMLFormElement.prototype.submit.call(pendingDeleteForm);
            });

            deleteModalElement?.addEventListener('hidden.bs.modal', () => {
                if (pendingDeleteForm?.dataset.submitting === 'true') return;

                pendingDeleteForm = null;
                if (confirmDeleteButton) {
                    confirmDeleteButton.disabled = false;
                    confirmDeleteButton.querySelector('.delete-button-label')?.classList.remove('d-none');
                    confirmDeleteButton.querySelector('.delete-button-loading')?.classList.add('d-none');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
