/**
 * Main Public JavaScript
 * Handles: navigation, animations, scroll effects, toast notifications, lazy loading
 */

'use strict';

// ── Utility ───────────────────────────────────────────────────────────────────

/**
 * Create and show a toast notification
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {number} duration in ms
 */
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `<i class="fas ${icons[type]}" aria-hidden="true"></i><span>${message}</span>`;

    container.appendChild(toast);

    // Auto-remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 350);
    }, duration);
}

// ── Navigation ─────────────────────────────────────────────────────────────────

(function initNav() {
    const header   = document.getElementById('site-header');
    const toggle   = document.getElementById('nav-toggle');
    const menu     = document.getElementById('nav-menu');

    if (!header) return;

    // Add overlay element for mobile
    const overlay = document.createElement('div');
    overlay.className = 'nav-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    document.body.appendChild(overlay);

    // Sticky header style on scroll
    function onScroll() {
        header.classList.toggle('scrolled', window.scrollY > 50);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Mobile hamburger toggle
    if (toggle && menu) {
        function openMenu() {
            menu.classList.add('open');
            overlay.classList.add('visible');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            menu.classList.remove('open');
            overlay.classList.remove('visible');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', () => {
            const isOpen = menu.classList.contains('open');
            isOpen ? closeMenu() : openMenu();
        });

        overlay.addEventListener('click', closeMenu);

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && menu.classList.contains('open')) closeMenu();
        });

        // Set active nav link
        const currentPath = window.location.pathname.split('/').pop() || 'index.php';
        menu.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href')?.split('/').pop() || '';
            if (href === currentPath || (currentPath === '' && href === 'index.php')) {
                link.classList.add('active');
            }
        });
    }
})();

// ── Smooth Scroll ──────────────────────────────────────────────────────────────

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ── Scroll-Triggered Animations ────────────────────────────────────────────────

(function initScrollAnimations() {
    // Add anim-ready class to animatable elements
    const targets = document.querySelectorAll(
        '.glass-card, .why-card, .video-card, .song-card, .stat-item, .cta-feature'
    );

    targets.forEach(el => {
        if (!el.classList.contains('animate-slide-up') &&
            !el.classList.contains('animate-fade-in') &&
            !el.classList.contains('animate-slide-right')) {
            el.classList.add('anim-ready');
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.anim-ready').forEach(el => observer.observe(el));
})();

// ── Hero Particles ─────────────────────────────────────────────────────────────

(function initParticles() {
    const container = document.getElementById('hero-particles');
    if (!container) return;

    const colors = ['#ff6b35', '#e94560', '#ffffff'];
    const count  = window.innerWidth < 768 ? 20 : 40;

    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        const size     = Math.random() * 4 + 1;
        const color    = colors[Math.floor(Math.random() * colors.length)];
        const left     = Math.random() * 100;
        const delay    = Math.random() * 8;
        const duration = Math.random() * 10 + 8;

        particle.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: 50%;
            left: ${left}%;
            top: ${Math.random() * 100}%;
            opacity: ${Math.random() * 0.5 + 0.1};
            animation: float ${duration}s ${delay}s ease-in-out infinite alternate;
        `;
        container.appendChild(particle);
    }

    // Add particle animation keyframes if not already present
    if (!document.getElementById('particle-styles')) {
        const style = document.createElement('style');
        style.id = 'particle-styles';
        style.textContent = `
            @keyframes float {
                from { transform: translateY(0) translateX(0) scale(1); }
                to   { transform: translateY(-40px) translateX(20px) scale(1.2); }
            }
        `;
        document.head.appendChild(style);
    }
})();

// ── Lazy Loading ───────────────────────────────────────────────────────────────

(function initLazyLoading() {
    if ('loading' in HTMLImageElement.prototype) return; // Native support

    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
})();

// ── Flash Message Auto-dismiss ─────────────────────────────────────────────────

document.querySelectorAll('.flash-close').forEach(btn => {
    btn.addEventListener('click', function () {
        const msg = this.closest('.flash-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
        }
    });
});

// ── Expose showToast globally ──────────────────────────────────────────────────

window.showToast = showToast;
