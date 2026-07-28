import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
