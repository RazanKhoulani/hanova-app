<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('admin.login_title') }} | {{ __('admin.brand') }}</title>

    <link rel="preload" href="{{ asset('fonts/Tajawal-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Tajawal-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <main class="login-page">
        <div class="login-language d-flex gap-2">
            <a href="{{ route('language.switch', 'ar') }}" class="btn {{ app()->getLocale() === 'ar' ? 'btn-primary' : 'btn-light border' }}">العربية</a>
            <a href="{{ route('language.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-light border' }}">EN</a>
        </div>

        <section class="login-shell">
            <div class="login-visual">
                <div class="login-brand">
                    <span class="brand-mark"><i class="fa-solid fa-spa"></i></span>
                    <span>
                        <strong>{{ __('admin.brand') }}</strong>
                        <small>{{ __('admin.clinic_management') }}</small>
                    </span>
                </div>

                <div class="login-visual-copy">
                    <h1>{{ __('admin.login_visual_title') }}</h1>
                    <p>{{ __('admin.login_visual_text') }}</p>
                </div>
            </div>

            <div class="login-form-side">
                <div class="login-form-wrap">
                    <h2>{{ __('admin.login_heading') }}</h2>
                    <p class="login-subtitle">{{ __('admin.login_subtitle') }}</p>

                    @if($errors->any())
                        <div class="alert alert-danger py-3 mb-4">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('admin.phone_number') }}</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-phone"></i>
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="{{ __('admin.phone_placeholder') }}"
                                    autocomplete="tel"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('admin.password') }}</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-lock"></i>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="{{ __('admin.password_placeholder') }}"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">{{ __('admin.remember_me') }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary login-submit w-100">
                            {{ __('admin.login') }}
                            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
