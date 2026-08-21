<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#a24a63">
    <meta name="description" content="Hanova clinic, skincare products, appointments, and connected beauty care.">
    <title>Hanova | {{ $locale === 'ar' ? 'العناية التي تشبهك' : 'Care made personal' }}</title>
    <link rel="preload" href="{{ asset('fonts/Tajawal-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Tajawal-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>
    @php
        $isArabic = $locale === 'ar';
        $productName = fn ($product) => $isArabic ? $product->name_ar : $product->name_en;
        $productDescription = fn ($product) => $isArabic ? $product->description_ar : $product->description_en;
        $concernName = fn ($concern) => $isArabic ? $concern->name_ar : $concern->name_en;
        $offerTitle = $activeOffer ? ($isArabic ? $activeOffer->title_ar : $activeOffer->title_en) : null;
        $offerDescription = $activeOffer ? ($isArabic ? $activeOffer->description_ar : $activeOffer->description_en) : null;
    @endphp

    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('site.home') }}" aria-label="Hanova">
                <span class="brand-mark"><img src="{{ asset('images/hanova-mark.svg') }}" alt="" width="30" height="30"></span>
                <span><strong>Hanova</strong><small>Beauty · Clinic · Care</small></span>
            </a>

            <nav class="site-nav" aria-label="{{ $isArabic ? 'التنقل الرئيسي' : 'Main navigation' }}">
                <a href="#care">{{ $isArabic ? 'العناية' : 'Care' }}</a>
                <a href="#products">{{ $isArabic ? 'المنتجات' : 'Products' }}</a>
                <a href="#experience">{{ $isArabic ? 'التجربة' : 'Experience' }}</a>
            </nav>

            <div class="header-actions">
                <a class="language-link" href="{{ route('language.switch', $isArabic ? 'en' : 'ar') }}">{{ $isArabic ? 'EN' : 'عربي' }}</a>
                <a class="dashboard-link" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">
                    {{ $isArabic ? 'لوحة الإدارة' : 'Dashboard' }}
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero container">
            <div class="hero-copy reveal">
                <span class="eyebrow">HANOVA CONNECTED CARE</span>
                <h1>{{ $isArabic ? 'العناية التي' : 'Care that feels' }} <em>{{ $isArabic ? 'تشبهك.' : 'personal.' }}</em></h1>
                <p>{{ $isArabic
                    ? 'تجربة واحدة تجمع العيادة، الاستشارات، المواعيد، ومنتجات العناية المختارة لتكون رحلتك أوضح وأسهل.'
                    : 'One connected experience for clinic visits, consultations, appointments, and carefully selected skincare.' }}</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#products">{{ $isArabic ? 'اكتشفي المنتجات' : 'Explore products' }}</a>
                    <a class="button button-ghost" href="#care">{{ $isArabic ? 'كيف تعمل Hanova؟' : 'How Hanova works' }}</a>
                </div>
                <div class="hero-metrics" aria-label="Platform metrics">
                    <span><strong>{{ $productCount }}+</strong>{{ $isArabic ? 'منتج عناية' : 'care products' }}</span>
                    <span><strong>{{ $concernCount }}+</strong>{{ $isArabic ? 'مجال اهتمام' : 'care concerns' }}</span>
                    <span><strong>1</strong>{{ $isArabic ? 'ملف صحي متكامل' : 'connected profile' }}</span>
                </div>
            </div>

            <div class="hero-stage reveal reveal-delay">
                <div class="stage-orbit orbit-one"></div>
                <div class="stage-orbit orbit-two"></div>
                <div class="phone-frame">
                    <div class="phone-top">
                        <span class="mini-mark"><img src="{{ asset('images/hanova-mark.svg') }}" alt="" width="22" height="22"></span>
                        <span>Hanova</span>
                        <i></i>
                    </div>
                    <div class="phone-banner">
                        <small>{{ $isArabic ? 'استشارة البشرة' : 'Skin consultation' }}</small>
                        <strong>{{ $isArabic ? 'اختيارات أدق لروتينك' : 'A smarter routine for you' }}</strong>
                    </div>
                    <div class="phone-products">
                        @forelse($products->take(2) as $product)
                            <article>
                                <div class="phone-image">
                                    @if($product->image)
                                        <img src="{{ url(\Illuminate\Support\Facades\Storage::url($product->image)) }}" alt="{{ $productName($product) }}">
                                    @else
                                        <span>H</span>
                                    @endif
                                </div>
                                <strong>{{ $productName($product) }}</strong>
                                <small>${{ number_format($product->price, 2) }}</small>
                            </article>
                        @empty
                            <article class="phone-placeholder"><div>H</div><strong>Hanova Care</strong></article>
                            <article class="phone-placeholder"><div>H</div><strong>Clinic Pick</strong></article>
                        @endforelse
                    </div>
                    <div class="phone-nav"><span>⌂</span><span>✦</span><b>H</b><span>□</span><span>○</span></div>
                </div>
                <div class="floating-note note-top"><span>✓</span>{{ $isArabic ? 'مواعيد متاحة فعلياً' : 'Live availability' }}</div>
                <div class="floating-note note-bottom"><span>✦</span>{{ $isArabic ? 'اختيارات العيادة' : 'Clinic selected' }}</div>
            </div>
        </section>

        @if($activeOffer)
            <section class="offer-strip">
                <div class="container offer-inner">
                    <span class="offer-badge">{{ $activeOffer->discount_type === 'percentage' ? rtrim(rtrim(number_format($activeOffer->discount_value, 2), '0'), '.') . '%' : '$' . number_format($activeOffer->discount_value, 2) }}</span>
                    <div><small>{{ $isArabic ? 'عرض Hanova النشط' : 'Active Hanova offer' }}</small><strong>{{ $offerTitle }}</strong></div>
                    @if($offerDescription)<p>{{ $offerDescription }}</p>@endif
                    <a href="#products">{{ $isArabic ? 'شاهدي المنتجات' : 'View products' }} <span>→</span></a>
                </div>
            </section>
        @endif

        <section class="section container" id="care">
            <div class="section-heading">
                <span>{{ $isArabic ? 'عناية مترابطة' : 'CONNECTED CARE' }}</span>
                <h2>{{ $isArabic ? 'كل ما تحتاجينه، ضمن رحلة واحدة.' : 'Everything you need, in one clear journey.' }}</h2>
                <p>{{ $isArabic ? 'كل خطوة في التطبيق مرتبطة بالداشبورد وملفك، من الحجز حتى استلام الطلب.' : 'Every app step connects to the dashboard and your profile, from booking to order delivery.' }}</p>
            </div>
            <div class="care-grid">
                <article class="care-card featured"><span>01</span><div class="care-icon">✦</div><h3>{{ $isArabic ? 'العيادة والمواعيد' : 'Clinic & appointments' }}</h3><p>{{ $isArabic ? 'مواعيد حسب دوام الطبيبة والحجوزات الفعلية، مع متابعة نوع ومدة الجلسة.' : 'Real availability based on doctor schedules, bookings, visit type, and duration.' }}</p></article>
                <article class="care-card"><span>02</span><div class="care-icon">◌</div><h3>{{ $isArabic ? 'متجر العناية' : 'Care store' }}</h3><p>{{ $isArabic ? 'منتجات مرتبطة بالمشكلات والتصنيفات والعروض التي تديرها العيادة.' : 'Products connected to concerns, categories, and clinic-managed offers.' }}</p></article>
                <article class="care-card"><span>03</span><div class="care-icon">□</div><h3>{{ $isArabic ? 'المحادثة والمتابعة' : 'Chat & follow-up' }}</h3><p>{{ $isArabic ? 'تواصل مباشر مع فريق العيادة ومتابعة الطلبات والإشعارات من مكان واحد.' : 'Direct clinic chat, order tracking, and notifications in one place.' }}</p></article>
            </div>
        </section>

        <section class="concerns-section" id="experience">
            <div class="container concerns-inner">
                <div>
                    <span class="section-kicker">{{ $isArabic ? 'مصمم لاحتياجك' : 'BUILT AROUND YOU' }}</span>
                    <h2>{{ $isArabic ? 'ابدئي من المشكلة، وليس من اسم المنتج.' : 'Start with the concern, not the product name.' }}</h2>
                </div>
                <div class="concern-cloud">
                    @forelse($concerns as $index => $concern)
                        <span class="{{ $index % 4 === 0 ? 'accent' : '' }}">{{ $concernName($concern) }}</span>
                    @empty
                        <span class="accent">{{ $isArabic ? 'العناية بالبشرة' : 'Skin care' }}</span>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section container" id="products">
            <div class="section-heading split-heading">
                <div><span>{{ $isArabic ? 'مختارات Hanova' : 'HANOVA EDIT' }}</span><h2>{{ $isArabic ? 'منتجات العناية المتاحة.' : 'Care products available now.' }}</h2></div>
                <p>{{ $isArabic ? 'هذه البيانات تأتي مباشرة من الداشبورد نفسه.' : 'This catalog is loaded directly from the same dashboard data.' }}</p>
            </div>
            <div class="product-grid">
                @forelse($products as $product)
                    <article class="product-card">
                        <div class="product-image">
                            @if($product->image)
                                <img loading="lazy" src="{{ url(\Illuminate\Support\Facades\Storage::url($product->image)) }}" alt="{{ $productName($product) }}">
                            @else
                                <span class="product-fallback">H</span>
                            @endif
                            @if($product->concerns->isNotEmpty())<small>{{ $concernName($product->concerns->first()) }}</small>@endif
                        </div>
                        <div class="product-copy">
                            <h3>{{ $productName($product) }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($productDescription($product) ?: ($isArabic ? 'منتج عناية مختار من Hanova.' : 'A care product selected by Hanova.'), 88) }}</p>
                            <strong>${{ number_format($product->price, 2) }}</strong>
                        </div>
                    </article>
                @empty
                    <div class="catalog-empty">{{ $isArabic ? 'سيتم عرض المنتجات هنا فور إضافتها من الداشبورد.' : 'Products will appear here as soon as they are added in the dashboard.' }}</div>
                @endforelse
            </div>
        </section>

        <section class="final-cta container">
            <div><span>HANOVA</span><h2>{{ $isArabic ? 'إدارة واضحة. تجربة أهدأ. عناية أقرب.' : 'Clear management. Calmer experience. Closer care.' }}</h2></div>
            <a class="button button-light" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">{{ $isArabic ? 'فتح لوحة الإدارة' : 'Open dashboard' }} <span>↗</span></a>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="brand footer-brand"><span class="brand-mark"><img src="{{ asset('images/hanova-mark.svg') }}" alt="" width="28" height="28"></span><span><strong>Hanova</strong><small>Beauty · Clinic · Care</small></span></div>
            <p>© {{ date('Y') }} Hanova. {{ $isArabic ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</p>
        </div>
    </footer>
</body>
</html>
