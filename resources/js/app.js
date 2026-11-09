/* Charlton Exclusive Club — small, progressive enhancements. */

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initCookieBanner();
    initLightbox();
    initBookingEstimate();
});

function initMobileMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const drawer = document.querySelector('[data-menu-drawer]');

    if (!toggle || !drawer) return;

    toggle.addEventListener('click', () => {
        const open = drawer.hasAttribute('hidden');
        if (open) {
            drawer.removeAttribute('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        } else {
            drawer.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
}

function initCookieBanner() {
    const banner = document.querySelector('[data-cookie-banner]');
    const key = 'cec_cookie_choice';

    if (!banner) return;

    const stored = readCookie(key);

    if (!stored) {
        banner.removeAttribute('hidden');
    }

    banner.querySelectorAll('[data-cookie-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const choice = button.getAttribute('data-cookie-choice');
            const value = JSON.stringify({
                necessary: true,
                preferences: choice === 'all',
                analytics: choice === 'all',
                marketing: false,
                at: new Date().toISOString(),
            });

            document.cookie = `${key}=${encodeURIComponent(value)}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;
            banner.setAttribute('hidden', '');

            const settings = document.querySelector('[data-cookie-settings]');
            if (settings) settings.focus();
        });
    });
}

function readCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}

function initLightbox() {
    const triggers = Array.from(document.querySelectorAll('[data-lightbox]'));
    if (!triggers.length) return;

    const overlay = document.createElement('div');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Photo viewer');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(11,20,38,.96);display:none;align-items:center;justify-content:center;z-index:80;padding:24px;';
    overlay.innerHTML = `
        <button type="button" data-close style="position:absolute;top:16px;right:20px;background:transparent;border:1px solid #C9A961;color:#F5EFE0;border-radius:4px;min-height:44px;padding:0 16px;font:inherit;cursor:pointer;">Close</button>
        <img alt="" style="max-width:min(1100px,92vw);max-height:86vh;object-fit:contain;border-radius:4px;">
    `;
    document.body.appendChild(overlay);

    const image = overlay.querySelector('img');

    const close = () => {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            image.src = trigger.getAttribute('data-lightbox');
            image.alt = trigger.getAttribute('data-alt') || '';
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            overlay.querySelector('[data-close]').focus();
        });
    });

    overlay.querySelector('[data-close]').addEventListener('click', close);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
}

/**
 * Live estimate for hall bookings: reads the hourly rate from the selected
 * date + start/end times and shows the member/standard estimate.
 */
function initBookingEstimate() {
    const form = document.querySelector('[data-booking-form]');
    if (!form) return;

    const startInput = form.querySelector('[name="start_time"]');
    const endInput = form.querySelector('[name="end_time"]');
    const output = document.querySelector('[data-estimate]');
    const rates = JSON.parse(form.getAttribute('data-rates') || '{}');

    if (!startInput || !endInput || !output) return;

    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const update = () => {
        if (!startInput.value || !endInput.value) return;

        const start = new Date(startInput.value);
        const end = new Date(endInput.value);

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end <= start) {
            output.textContent = '';
            return;
        }

        const key = weekdays[start.getDay()];
        const rate = rates[key];

        if (!rate) {
            output.textContent = 'No published rate for this day type yet.';
            return;
        }

        const hours = (end - start) / 3600000;
        const standardPence = rate.standard_price_pence ?? (rate.price != null ? Math.round(rate.price * 100) : 0);
        const totalStandardPence = Math.round(standardPence * hours);
        let totalMemberPence = totalStandardPence;
        if (rate.discount_type === 'fixed') {
            totalMemberPence = Math.max(0, totalStandardPence - Math.round((rate.discount_value || 0) * hours));
        } else {
            const pct = Math.min(100, Math.max(0, Number(rate.discount_value ?? rate.discount ?? 0)));
            totalMemberPence = Math.round(totalStandardPence * (1 - pct / 100));
        }

        output.textContent = `${hours.toFixed(1)} hours · standard estimate £${(totalStandardPence / 100).toFixed(2)} · member estimate £${(totalMemberPence / 100).toFixed(2)}`;
    };

    startInput.addEventListener('change', update);
    endInput.addEventListener('change', update);
}
