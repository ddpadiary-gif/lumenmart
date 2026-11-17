import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') ?? '';

/**
 * Store global keranjang: badge navbar reaktif + mini cart drawer (slide-over).
 */
Alpine.store('cart', {
    count: Number(meta('cart-count') || 0),
    summary: null,
    drawer: false,
    url: meta('cart-url'),
    checkoutUrl: meta('checkout-url'),
    set(data) {
        if (typeof data.cartCount === 'number') this.count = data.cartCount;
        if (data.summary) this.summary = data.summary;
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
    },
    open() {
        if (!this.summary) {
            window.location.href = this.url;
            return;
        }
        this.drawer = true;
    },
});

/**
 * Helper AJAX LUMEN MART (Add-to-cart, Bundling, Wishlist) tanpa reload halaman.
 * Jika terjadi kegagalan non-validasi (mis. sesi kedaluwarsa), fallback ke submit form biasa.
 */
window.lumen = {
    rupiah(value) {
        return 'Rp ' + Math.round(Number(value) || 0).toLocaleString('id-ID');
    },

    notify(message, options = {}) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message, type: options.type ?? 'success', action: options.action ?? null },
        }));
    },

    async send(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': meta('csrf-token'),
            },
            body,
        });

        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            data = {};
        }

        if (!response.ok) {
            const error = new Error(data.message || 'Terjadi kesalahan.');
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    },

    async addToCart(form, extraQuantity = null) {
        const button = form.querySelector('[type="submit"]');
        const formData = new FormData(form);
        if (extraQuantity !== null) formData.set('quantity', extraQuantity);

        if (button) button.disabled = true;
        try {
            const data = await this.send(form.action, formData);
            Alpine.store('cart').set(data);
            this.notify('✓ ' + data.message, { action: { label: 'Lihat Keranjang', drawer: true } });
            return true;
        } catch (error) {
            if (error.status === 422) {
                const first = error.data?.errors ? Object.values(error.data.errors)[0]?.[0] : null;
                this.notify(first || error.message, { type: 'error' });
                if (typeof error.data?.cartCount === 'number') Alpine.store('cart').count = error.data.cartCount;
                return false;
            }
            form.submit();
            return false;
        } finally {
            if (button) button.disabled = false;
        }
    },

    async toggleWishlist(form, productId, productName = 'Produk') {
        try {
            const data = await this.send(form.action, new FormData(form));
            window.dispatchEvent(new CustomEvent('wishlist-updated', {
                detail: { id: productId, wishlisted: data.wishlisted },
            }));
            this.notify(data.wishlisted
                ? `♥ ${productName} disimpan ke Favorit`
                : `${productName} dihapus dari Favorit`, { type: data.wishlisted ? 'wishlist' : 'info' });
            return data.wishlisted;
        } catch (error) {
            form.submit();
            return null;
        }
    },

    async copy(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            const area = document.createElement('textarea');
            area.value = text;
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            area.remove();
        }
        return true;
    },
};

Alpine.start();
