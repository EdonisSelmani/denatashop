import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('productSearch', (endpoint, shopUrl, initialQuery = '') => ({
    query: initialQuery || '',
    suggestions: [],
    hasMore: false,
    loading: false,
    open: false,
    activeIndex: -1,
    abortController: null,

    get trimmedQuery() {
        return this.query.trim();
    },

    get allResultsUrl() {
        const url = new URL(shopUrl || '/shop', window.location.origin);

        if (this.trimmedQuery.length > 0) {
            url.searchParams.set('search', this.trimmedQuery);
        }

        return url.pathname + url.search;
    },

    search() {
        const term = this.trimmedQuery;

        this.activeIndex = -1;

        if (term.length === 0) {
            this.reset();
            return;
        }

        this.abortController?.abort();

        const controller = new AbortController();
        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('q', term);

        this.abortController = controller;
        this.loading = true;
        this.open = true;

        fetch(url.toString(), {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Search failed');
                }

                return response.json();
            })
            .then((data) => {
                if (controller.signal.aborted) {
                    return;
                }

                this.suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
                this.hasMore = Boolean(data.has_more);
                this.open = true;
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    this.suggestions = [];
                    this.hasMore = false;
                    this.open = true;
                }
            })
            .finally(() => {
                if (this.abortController === controller) {
                    this.loading = false;
                    this.abortController = null;
                }
            });
    },

    openSuggestions() {
        if (this.trimmedQuery.length === 0) {
            return;
        }

        this.open = true;

        if (this.suggestions.length === 0) {
            this.search();
        }
    },

    close() {
        this.open = false;
        this.activeIndex = -1;
    },

    reset() {
        this.suggestions = [];
        this.hasMore = false;
        this.loading = false;
        this.open = false;
        this.activeIndex = -1;
    },

    move(step) {
        if (!this.open) {
            this.openSuggestions();
        }

        if (this.suggestions.length === 0) {
            return;
        }

        this.activeIndex = (this.activeIndex + step + this.suggestions.length) % this.suggestions.length;
    },

    chooseActive() {
        if (this.activeIndex >= 0 && this.suggestions[this.activeIndex]) {
            this.go(this.suggestions[this.activeIndex].url);
            return;
        }

        this.$root.requestSubmit();
    },

    go(url) {
        window.location.href = url;
    },

    submitSearch() {
        this.close();
    },
}));

Alpine.start();

// Prevent multiple form submissions
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function(event) {
            if (form.dataset.submitted === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitted = 'true';
        });
    });

    if (document.querySelector('.heroSwiper')) {
        Promise.all([
            import('swiper/bundle'),
            import('swiper/css/bundle'),
        ]).then(([{ default: Swiper }]) => {
            new Swiper('.heroSwiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    }
});

document.addEventListener('click', async function(event) {
    const button = event.target.closest('.add-to-cart');

    if (!button) {
        return;
    }

    event.preventDefault();

    if (button.disabled) {
        return;
    }

    const formData = new FormData();
    formData.append('product_id', button.dataset.productId);
    formData.append('quantity', button.dataset.quantity || '1');
    const originalHtml = button.innerHTML;
    button.disabled = true;

    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            window.showToast?.(data.message, 'success');
            button.innerHTML = '<span>Shtuar</span>';
            if (data.cart_count !== undefined) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
            }
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.showToast?.(data.message || 'Nuk u shtua ne shporte', 'error');
        }
    } catch (error) {
        window.showToast?.('Ndodhi nje gabim', 'error');
    } finally {
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.disabled = false;
        }, 900);
    }
});

document.addEventListener('click', async function(event) {
    const button = event.target.closest('.add-to-wishlist');

    if (!button) {
        return;
    }

    event.preventDefault();

    const formData = new FormData();
    formData.append('product_id', button.dataset.productId);

    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            window.showToast?.(data.message, 'success');

            const wishlistCount = document.getElementById('wishlist-count');
            if (wishlistCount && data.wishlist_count !== undefined) {
                wishlistCount.textContent = data.wishlist_count;
            }

            button.classList.toggle('is-favorited', Boolean(data.is_favorited));
            button.classList.toggle('text-[#C9473D]', Boolean(data.is_favorited));
            const icon = button.querySelector('svg');
            if (icon) {
                icon.classList.toggle('fill-current', Boolean(data.is_favorited));
            }
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.showToast?.(data.message || 'Lista e deshirave nuk u perditesua', 'error');
        }
    } catch (error) {
        window.showToast?.('Ndodhi nje gabim', 'error');
    }
});
