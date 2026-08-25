document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('[data-site-header]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 20);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    const closeMenu = () => {
        if (!menuToggle || !mobileMenu) return;
        menuToggle.setAttribute('aria-expanded', 'false');
        mobileMenu.hidden = true;
        document.body.classList.remove('menu-open');
    };

    menuToggle?.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') !== 'true';
        menuToggle.setAttribute('aria-expanded', String(open));
        mobileMenu.hidden = !open;
        document.body.classList.toggle('menu-open', open);
    });

    mobileMenu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1120) closeMenu();
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealItems = document.querySelectorAll('.reveal');
    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px' }
    );

    revealItems.forEach((item) => observer.observe(item));
});
