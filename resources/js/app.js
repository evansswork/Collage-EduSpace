document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-auth-nav]');

    if (!link) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (link.target && link.target !== '_self') return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#')) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const targetUrl = new URL(href, window.location.href);
    if (targetUrl.href === window.location.href) return;

    event.preventDefault();

    const body = document.body;
    body.classList.add('is-leaving', `auth-leave-${link.dataset.authNav || 'next'}`);

    window.setTimeout(() => {
        window.location.href = targetUrl.href;
    }, 180);
});
