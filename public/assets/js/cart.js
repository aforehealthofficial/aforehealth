/**
 * AFOREHEALTH - AJAX Cart Drawer System
 */

const CartDrawer = {
    isOpen: false,

    init() {
        this.bindEvents();
    },

    bindEvents() {
        // Drawer toggle buttons
        document.querySelectorAll('.btn-open-cart-drawer').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
            });
        });

        const closeBtn = document.querySelector('.cart-drawer-close');
        if (closeBtn) closeBtn.addEventListener('click', () => this.close());

        const backdrop = document.querySelector('.cart-drawer-backdrop');
        if (backdrop) backdrop.addEventListener('click', () => this.close());

        // Global Add To Cart Form or Button
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.ajax-add-to-cart-form')) {
                e.preventDefault();
                this.handleAddToCartForm(e.target);
            }
        });

        document.addEventListener('click', (e) => {
            const quickBtn = e.target.closest('.btn-quick-add-action');
            if (quickBtn) {
                e.preventDefault();
                const prodId = quickBtn.dataset.productId;
                const varId = quickBtn.dataset.variantId || '';
                this.addItem(prodId, varId, 1);
            }
        });
    },

    open() {
        this.fetchData(() => {
            const drawer = document.querySelector('.cart-drawer');
            const backdrop = document.querySelector('.cart-drawer-backdrop');
            if (drawer && backdrop) {
                drawer.classList.add('open');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
                this.isOpen = true;
            }
        });
    },

    close() {
        const drawer = document.querySelector('.cart-drawer');
        const backdrop = document.querySelector('.cart-drawer-backdrop');
        if (drawer && backdrop) {
            drawer.classList.remove('open');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
            this.isOpen = false;
        }
    },

    fetchData(callback) {
        fetch('/cart/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.cart) {
                this.render(data.cart);
                if (callback) callback();
            }
        });
    },

    addItem(productId, variantId, quantity = 1) {
        const formData = new FormData();
        formData.append('product_id', productId);
        if (variantId) formData.append('variant_id', variantId);
        formData.append('quantity', quantity);

        fetch('/cart/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.render(data.cart);
                this.open();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Could not add to bag', 'danger');
            }
        })
        .catch(() => showToast('Error adding product to bag.', 'danger'));
    },

    handleAddToCartForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch('/cart/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.render(data.cart);
                this.open();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Could not add to bag', 'danger');
            }
        })
        .catch(() => showToast('Error adding product to bag.', 'danger'))
        .finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
    },

    updateQty(itemId, quantity) {
        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('quantity', quantity);

        fetch('/cart/update', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.render(data.cart);
            }
        });
    },

    removeItem(itemId, callback) {
        const doRemove = () => {
            const formData = new FormData();
            formData.append('item_id', itemId);

            fetch('/cart/remove', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.render(data.cart);
                    showToast('Item removed from your bag.', 'info');
                    if (typeof callback === 'function') callback();
                }
            });
        };

        if (window.AforeConfirm) {
            window.AforeConfirm.ask({
                type: 'delete',
                title: 'Remove Item from Bag?',
                message: 'Are you sure you want to remove this item from your shopping bag?',
                confirmText: 'Remove Item',
                onConfirm: doRemove
            });
        } else {
            doRemove();
        }
    },

    applyCoupon(code) {
        if (!code) return;
        const formData = new FormData();
        formData.append('code', code);

        fetch('/cart/coupon', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.render(data.cart);
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        });
    },

    removeCoupon() {
        fetch('/cart/remove-coupon', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.render(data.cart);
                showToast('Coupon removed.', 'info');
            }
        });
    },

    render(cart) {
        // 1. Update Header & Mobile Badges
        document.querySelectorAll('.cart-badge-count').forEach(el => {
            el.textContent = cart.item_count;
            el.style.display = cart.item_count > 0 ? 'flex' : 'none';
        });

        // 2. Free shipping meter
        const meterText = document.querySelector('.free-shipping-meter-text');
        const meterFill = document.querySelector('.meter-bar-fill');
        if (meterText && meterFill) {
            if (cart.needed_for_free_shipping > 0) {
                meterText.innerHTML = `Add <strong>₹${Math.round(cart.needed_for_free_shipping)}</strong> more for <strong>FREE SHIPPING!</strong>`;
                meterFill.style.width = `${cart.free_shipping_percent}%`;
            } else {
                meterText.innerHTML = `<span style="color:#2E7D52; font-weight:700;">🎉 Congratulations! You have unlocked FREE Shipping!</span>`;
                meterFill.style.width = '100%';
            }
        }

        // 3. Render Items
        const container = document.querySelector('.cart-items-scrollable');
        const emptyState = document.querySelector('.cart-drawer-empty');
        const footer = document.querySelector('.cart-drawer-footer');

        if (!container) return;

        if (cart.items.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; padding: 40px 20px;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">🛍️</div>
                    <h5 style="font-weight:700; color:#252126;">Your Bag is Empty</h5>
                    <p style="color:#777; font-size: 0.875rem;">Discover our leakproof period wear and comfort essentials.</p>
                    <a href="/shop" class="btn btn-afh-primary" onclick="CartDrawer.close()" style="margin-top:10px;">Explore Collection</a>
                </div>
            `;
            if (footer) footer.style.display = 'none';
            return;
        }

        if (footer) footer.style.display = 'block';

        let html = '';
        cart.items.forEach(item => {
            const variantText = (item.color ? `Color: ${item.color} ` : '') + (item.size ? `| Size: ${item.size}` : '');
            html += `
                <div class="cart-drawer-item">
                    <div class="cart-item-thumb">
                        <img src="/uploads/products/${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-title">${item.name}</div>
                        <div class="cart-item-variant">${variantText}</div>
                        <div class="cart-item-price-qty">
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="CartDrawer.updateQty(${item.item_id}, ${item.quantity - 1})">-</button>
                                <span class="qty-display">${item.quantity}</span>
                                <button type="button" class="qty-btn" onclick="CartDrawer.updateQty(${item.item_id}, ${item.quantity + 1})">+</button>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:800; color:#252126;">₹${Math.round(item.line_total)}</div>
                                <button type="button" onclick="CartDrawer.removeItem(${item.item_id})" style="background:none; border:none; color:#999; font-size:0.75rem; text-decoration:underline; cursor:pointer; padding:0;">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;

        // 4. Update Totals in Footer
        const subtotalEl = document.querySelector('.cart-subtotal-val');
        const grandTotalEl = document.querySelector('.cart-grand-total-val');
        const discountRow = document.querySelector('.cart-discount-row');
        const discountVal = document.querySelector('.cart-discount-val');
        const shippingVal = document.querySelector('.cart-shipping-val');

        if (subtotalEl) subtotalEl.textContent = `₹${Math.round(cart.subtotal)}`;
        if (grandTotalEl) grandTotalEl.textContent = `₹${Math.round(cart.grand_total)}`;
        if (shippingVal) shippingVal.textContent = cart.shipping_fee > 0 ? `₹${Math.round(cart.shipping_fee)}` : 'FREE';

        if (discountRow && discountVal) {
            if (cart.coupon_discount > 0) {
                discountRow.style.display = 'flex';
                discountVal.textContent = `-₹${Math.round(cart.coupon_discount)}`;
            } else {
                discountRow.style.display = 'none';
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', () => CartDrawer.init());
