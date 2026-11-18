import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

/**
 * Talk to the cart endpoints and always come back with JSON.
 */
window.cartRequest = async function cartRequest(url, method = 'POST', payload = {}) {
    const response = await fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: method === 'GET' ? undefined : JSON.stringify(payload),
    });

    if (response.status === 422) {
        const data = await response.json();
        const first = Object.values(data.errors ?? {}).flat().shift();
        throw new Error(first ?? 'Please check the quantity and try again.');
    }

    if (!response.ok) {
        throw new Error('Something went wrong. Please try again.');
    }

    return response.json();
};

/**
 * Download a CSV export with visible feedback. The native link is still a
 * normal anchor, so it keeps working if JavaScript is unavailable.
 */
window.downloadCsv = async function downloadCsv(url, fallbackName = 'export.csv') {
    const notify = (message) => {
        const store = window.Alpine?.store('cart');

        if (store) {
            store.notify(message);
        }
    };

    notify('Preparing your CSV…');

    try {
        const response = await fetch(url, {
            headers: { 'Accept': 'text/csv, text/plain, */*' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Export failed — please sign in again and retry.');
        }

        const contentType = response.headers.get('content-type') ?? '';

        if (!contentType.includes('csv') && !contentType.includes('text/plain')) {
            throw new Error('Export failed — the server did not return a CSV file.');
        }

        const blob = await response.blob();
        const disposition = response.headers.get('content-disposition') ?? '';
        const match = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(disposition);
        const filename = match ? decodeURIComponent(match[1]) : fallbackName;
        const objectUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = objectUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();

        window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1500);
        notify(`CSV downloaded — ${filename}`);
    } catch (error) {
        notify(error instanceof Error ? error.message : 'Could not export the CSV.');
    }
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-csv-export]');

    if (!link) {
        return;
    }

    event.preventDefault();
    window.downloadCsv(link.href, link.dataset.filename || 'export.csv');
});

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        count: Number(document.querySelector('[data-cart-count]')?.dataset.cartCount ?? 0),
        open: false,
        loading: false,
        html: '',
        toast: '',
        timer: null,

        ensureLoaded() {
            if (this.html === '') {
                this.refresh();
            }
        },

        async refresh() {
            try {
                const data = await window.cartRequest('/cart/mini', 'GET');
                this.html = data.html;
                this.count = data.count;
            } catch (error) {
                this.notify(error.message);
            }
        },

        apply(data) {
            this.count = data.count;
            this.html = data.html;
            this.notify(data.message);
            this.open = true;
        },

        notify(message) {
            if (!message) {
                return;
            }

            this.toast = message;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.toast = '';
            }, 4500);
        },

        async mutate(url, method = 'POST', payload = {}) {
            this.loading = true;

            try {
                const data = await window.cartRequest(url, method, payload);
                this.apply(data);

                return data;
            } catch (error) {
                this.notify(error.message);
                throw error;
            } finally {
                this.loading = false;
            }
        },
    });
});

/**
 * Any form marked with data-ajax-cart is submitted over fetch, with a normal
 * POST fallback when JavaScript is unavailable.
 */
document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-ajax-cart]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const payload = Object.fromEntries(new FormData(form).entries());
    const method = String(payload._method ?? 'POST').toUpperCase();
    delete payload._method;

    try {
        await Alpine.store('cart').mutate(form.getAttribute('action'), method, payload);
    } catch (error) {
        // The store already surfaced the message in the toast.
    }
});

Alpine.start();
