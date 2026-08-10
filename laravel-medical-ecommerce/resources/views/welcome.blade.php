<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ca6d84">
    <meta name="description" content="Hanova beauty, clinic, and skincare platform.">
    <title>Hanova | Beauty, Clinic &amp; Care</title>

    <style>
        @font-face {
            font-family: "Tajawal";
            src: url("{{ asset('fonts/Tajawal-Regular.ttf') }}") format("truetype");
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: "Tajawal";
            src: url("{{ asset('fonts/Tajawal-Bold.ttf') }}") format("truetype");
            font-weight: 700;
            font-display: swap;
        }

        @font-face {
            font-family: "Tajawal";
            src: url("{{ asset('fonts/Tajawal-ExtraBold.ttf') }}") format("truetype");
            font-weight: 800;
            font-display: swap;
        }

        :root {
            color-scheme: light;
            --primary: #ca6d84;
            --primary-dark: #a24a63;
            --ink: #2b2320;
            --muted: #7b6860;
            --paper: #fffaf7;
            --line: #ebddd7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 10%, rgba(202, 109, 132, .18), transparent 31rem),
                radial-gradient(circle at 90% 88%, rgba(210, 138, 94, .13), transparent 28rem),
                var(--paper);
            font-family: "Tajawal", sans-serif;
        }

        .page {
            width: min(1180px, calc(100% - 40px));
            min-height: 100vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }

        header {
            min-height: 96px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 13px;
            color: inherit;
            text-decoration: none;
        }

        .mark {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(145deg, #dc8da2, var(--primary-dark));
            border-radius: 16px;
            box-shadow: 0 12px 28px rgba(162, 74, 99, .24);
        }

        .mark svg {
            width: 25px;
            height: 25px;
        }

        .brand-copy strong,
        .brand-copy small {
            display: block;
        }

        .brand-copy strong {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .brand-copy small {
            color: var(--muted);
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .admin-link,
        .primary-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #fff;
            background: var(--ink);
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            transition: transform .2s ease, background .2s ease;
        }

        .admin-link {
            min-height: 44px;
            padding: 0 20px;
            font-size: 14px;
        }

        .admin-link:hover,
        .primary-link:hover {
            color: #fff;
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        main {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(360px, .92fr);
            align-items: center;
            gap: clamp(50px, 8vw, 110px);
            padding: 70px 0 95px;
        }

        .eyebrow {
            display: inline-flex;
            padding: 8px 13px;
            color: var(--primary-dark);
            background: rgba(202, 109, 132, .11);
            border: 1px solid rgba(202, 109, 132, .17);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .14em;
        }

        h1 {
            max-width: 720px;
            margin: 24px 0 20px;
            font-size: clamp(3.2rem, 7vw, 6.6rem);
            line-height: .92;
            letter-spacing: -.055em;
        }

        h1 span {
            color: var(--primary);
        }

        .lead {
            max-width: 620px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(17px, 2vw, 21px);
            line-height: 1.8;
        }

        .lead-ar {
            max-width: 620px;
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.9;
        }

        .primary-link {
            min-height: 54px;
            margin-top: 32px;
            padding: 0 26px;
            background: var(--primary);
        }

        .showcase {
            position: relative;
            min-height: 510px;
        }

        .showcase::before {
            content: "";
            position: absolute;
            inset: 8% -8% -4% 14%;
            background: linear-gradient(145deg, rgba(202, 109, 132, .2), rgba(210, 138, 94, .08));
            border-radius: 44% 56% 46% 54% / 56% 42% 58% 44%;
            transform: rotate(-7deg);
        }

        .panel {
            position: relative;
            z-index: 1;
            padding: 28px;
            background: rgba(255, 255, 255, .83);
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 32px;
            box-shadow: 0 28px 80px rgba(78, 50, 45, .13);
            backdrop-filter: blur(16px);
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding-bottom: 23px;
            border-bottom: 1px solid var(--line);
        }

        .panel-head strong {
            font-size: 23px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #2f8f64;
            font-size: 12px;
            font-weight: 700;
        }

        .status::before {
            content: "";
            width: 8px;
            height: 8px;
            background: currentColor;
            border-radius: 50%;
            box-shadow: 0 0 0 5px rgba(47, 143, 100, .1);
        }

        .features {
            display: grid;
            gap: 13px;
            margin-top: 22px;
        }

        .feature {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 14px;
            align-items: center;
            padding: 17px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 19px;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: var(--primary-dark);
            background: #fbeaf0;
            border-radius: 15px;
            font-weight: 800;
        }

        .feature strong,
        .feature small {
            display: block;
        }

        .feature small {
            margin-top: 3px;
            color: var(--muted);
            line-height: 1.5;
        }

        footer {
            padding: 24px 0 30px;
            color: var(--muted);
            border-top: 1px solid rgba(235, 221, 215, .8);
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .page {
                width: min(100% - 28px, 620px);
            }

            header {
                min-height: 82px;
            }

            .admin-link {
                width: 44px;
                padding: 0;
                overflow: hidden;
                font-size: 0;
            }

            .admin-link::after {
                content: "↗";
                font-size: 18px;
            }

            main {
                grid-template-columns: 1fr;
                gap: 58px;
                padding: 62px 0 80px;
            }

            h1 {
                font-size: clamp(3.4rem, 17vw, 5rem);
            }

            .showcase {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header>
            <a class="brand" href="{{ url('/') }}" aria-label="Hanova home">
                <span class="mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 21c0-7 3.5-11 9-13-1 6-4.5 9-9 9"/>
                        <path d="M12 21c0-7-3.5-11-9-13 1 6 4.5 9 9 9"/>
                        <path d="M12 14c-3-3-3-7 0-11 3 4 3 8 0 11Z"/>
                    </svg>
                </span>
                <span class="brand-copy">
                    <strong>Hanova</strong>
                    <small>Beauty | Clinic | Care</small>
                </span>
            </a>

            <a class="admin-link" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">
                Admin Dashboard
                <span aria-hidden="true">→</span>
            </a>
        </header>

        <main>
            <section>
                <span class="eyebrow">CONNECTED BEAUTY CARE</span>
                <h1>Care that feels <span>personal.</span></h1>
                <p class="lead">Beauty care, clinic management, and skincare products in one connected Hanova experience.</p>
                <p class="lead-ar" dir="rtl">العناية بالجمال، إدارة العيادة، ومنتجات البشرة ضمن تجربة Hanova واحدة ومترابطة.</p>
                <a class="primary-link" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">
                    Open Hanova Dashboard
                    <span aria-hidden="true">→</span>
                </a>
            </section>

            <section class="showcase" aria-label="Hanova services">
                <div class="panel">
                    <div class="panel-head">
                        <strong>Hanova Platform</strong>
                        <span class="status">Unified</span>
                    </div>
                    <div class="features">
                        <div class="feature">
                            <span class="feature-icon">01</span>
                            <span><strong>Clinic</strong><small>Appointments, patient records, and consultations.</small></span>
                        </div>
                        <div class="feature">
                            <span class="feature-icon">02</span>
                            <span><strong>Store</strong><small>Products, offers, orders, and delivery workflow.</small></span>
                        </div>
                        <div class="feature">
                            <span class="feature-icon">03</span>
                            <span><strong>Care</strong><small>Live chat, beauty assistant, and notifications.</small></span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer>&copy; {{ date('Y') }} Hanova. Beauty, clinic, and care.</footer>
    </div>
</body>
</html>
