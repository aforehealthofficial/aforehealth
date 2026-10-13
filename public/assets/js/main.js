/**
 * AFOREHEALTH - Global Storefront JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Toast Notification Helper
    window.showToast = function (message, type = 'success') {
        let container = document.getElementById('afh-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'afh-toast-container';
            container.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 1090; display: flex; flex-direction: column; gap: 10px; max-width: 360px; pointer-events: none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bg = type === 'success' ? '#2E7D52' : (type === 'danger' ? '#C0392B' : '#8B5E83');
        toast.style.cssText = `background: ${bg}; color: #FFFFFF; padding: 14px 20px; border-radius: 10px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 8px 24px rgba(0,0,0,0.18); transform: translateY(20px); opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); pointer-events: auto; display: flex; align-items: center; justify-content: space-between; gap: 12px;`;
        
        toast.innerHTML = `<span>${message}</span><button style="background:none; border:none; color:#fff; font-size:1.1rem; cursor:pointer; line-height:1;" onclick="this.parentElement.remove()">&times;</button>`;
        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        // Auto dismiss
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    // 2. Wishlist Toggle AJAX
    document.querySelectorAll('.btn-wishlist-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            if (!productId) return;

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('/account/wishlist/toggle', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.login_required) {
                    window.location.href = '/login';
                    return;
                }
                if (data.success) {
                    this.classList.toggle('active', data.added);
                    const heartIcon = this.querySelector('i');
                    if (heartIcon) {
                        heartIcon.className = data.added ? 'bi bi-heart-fill' : 'bi bi-heart';
                    }
                    showToast(data.message, data.added ? 'success' : 'info');
                    
                    // Update header wishlist badge
                    const badge = document.querySelector('.wishlist-badge');
                    if (badge) {
                        badge.textContent = data.wishlist_count;
                        badge.style.display = data.wishlist_count > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(() => showToast('Could not update wishlist. Please try again.', 'danger'));
        });
    });

    // 3. Newsletter Subscription AJAX
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[name="email"]');
            const submitBtn = this.querySelector('button[type="submit"]');
            const formData = new FormData(this);

            if (submitBtn) submitBtn.disabled = true;

            fetch('/newsletter/subscribe', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'danger');
                if (data.success) emailInput.value = '';
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

    // 4. Mobile Sticky Add-to-Cart visibility observer
    const mainAddToCartBtn = document.getElementById('main-add-to-cart-btn');
    const stickyMobileBar = document.querySelector('.sticky-mobile-cart-bar');
    if (mainAddToCartBtn && stickyMobileBar) {
        window.addEventListener('scroll', () => {
            const rect = mainAddToCartBtn.getBoundingClientRect();
            if (rect.bottom < 0) {
                stickyMobileBar.classList.add('visible');
            } else {
                stickyMobileBar.classList.remove('visible');
            }
        });
    }
});
