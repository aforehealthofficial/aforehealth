    <!-- Footer Component -->
    <footer class="footer-main">
        <div class="container">
            <div class="row g-4">
                
                <!-- Col 1: Brand & Mission -->
                <div class="col-lg-4 col-md-6">
                    <img src="<?= asset('images/logo-white.svg') ?>" alt="AFOREHEALTH" height="42" class="mb-3">
                    <p class="text-white-50 small mb-3">
                        AFOREHEALTH is an India-focused women's intimate-care and period-care brand. We engineer thoughtfully designed leakproof underwear, rash-free organic pads, and pH-balanced essentials so you can move with complete comfort and period confidence every single day.
                    </p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-light text-dark p-2"><i class="bi bi-shield-check text-success me-1"></i> 100% Discreet Packaging</span>
                        <span class="badge bg-light text-dark p-2"><i class="bi bi-truck text-primary me-1"></i> Fast India Delivery</span>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="https://instagram.com" target="_blank" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="https://facebook.com" target="_blank" class="text-white-50 fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="https://youtube.com" target="_blank" class="text-white-50 fs-5"><i class="bi bi-youtube"></i></a>
                        <a href="<?= e(get_setting('whatsapp_group_url')) ?>" target="_blank" class="text-success fs-5" title="Join WhatsApp Community"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Col 2: Shop & Categories -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">SHOP</h6>
                    <ul class="footer-links">
                        <li><a href="<?= url('category/period-panties') ?>">Period Panties</a></li>
                        <li><a href="<?= url('category/everyday-panties') ?>">Everyday Underwear</a></li>
                        <li><a href="<?= url('category/period-kits') ?>">Period Kits &amp; Boxes</a></li>
                        <li><a href="<?= url('category/pads-and-liners') ?>">Organic Cotton Pads</a></li>
                        <li><a href="<?= url('category/intimate-care') ?>">Intimate Care pH 3.8</a></li>
                        <li><a href="<?= url('combos') ?>">Combos &amp; Bundles</a></li>
                        <li><a href="<?= url('offers') ?>">Offers &amp; Discounts</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care & Policies -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="footer-heading">CUSTOMER CARE</h6>
                    <ul class="footer-links">
                        <li><a href="<?= url('track-order') ?>"><i class="bi bi-geo-alt me-1"></i> Track Your Order</a></li>
                        <li><a href="<?= url('about-us') ?>">About AFOREHEALTH</a></li>
                        <li><a href="<?= url('contact-us') ?>">Contact &amp; WhatsApp Support</a></li>
                        <li><a href="<?= url('faq') ?>">Frequently Asked Questions</a></li>
                        <li><a href="<?= url('shipping-policy') ?>">Shipping &amp; Delivery Policy</a></li>
                        <li><a href="<?= url('return-refund-policy') ?>">Return &amp; Refund Policy</a></li>
                        <li><a href="<?= url('cancellation-policy') ?>">Cancellation Policy</a></li>
                        <li><a href="<?= url('privacy-policy') ?>">Privacy &amp; Data Security</a></li>
                        <li><a href="<?= url('terms-conditions') ?>">Terms &amp; Conditions</a></li>
                    </ul>
                </div>

                <!-- Col 4: WhatsApp Sisterhood & Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-heading">JOIN OUR SISTERHOOD</h6>
                    <p class="text-white-50 small mb-3">
                        Connect with 25,000+ Indian women for period wellness tips, doctor Q&amp;As, and secret subscriber discounts.
                    </p>
                    
                    <a href="<?= e(get_setting('whatsapp_group_url')) ?>" target="_blank" class="btn btn-success w-100 fw-bold mb-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp fs-5"></i> Join WhatsApp Community
                    </a>

                    <form id="newsletter-form" class="mt-2">
                        <label class="form-label text-white-50 small mb-1">Get 10% Off Your First Order:</label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="Enter your email" required>
                            <button class="btn btn-afh-primary btn-sm" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    &copy; <?= date('Y') ?> <strong>AFOREHEALTH Wellness Pvt. Ltd.</strong> All rights reserved. Made with ❤️ for Indian Women.
                </div>
                <div class="d-flex align-items-center gap-3 text-white-50 small">
                    <span>100% Secure Payments:</span>
                    <span>UPI • Razorpay • RuPay • Cards • NetBanking • COD</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- First Order Offer Modal (Mobile-Friendly & Non-Aggressive) -->
    <?php if ((bool)get_setting('first_order_offer_enabled', 1)): ?>
        <div class="modal fade" id="firstOrderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 text-center p-4 position-relative" style="background: linear-gradient(180deg, #FFF9FA 0%, #FFFFFF 100%);">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" onclick="sessionStorage.setItem('first_order_dismissed', '1')"></button>
                    <div class="py-2">
                        <span class="badge rounded-pill px-3 py-2 text-uppercase mb-2" style="background-color: var(--afh-primary) !important; color: white;">
                            Welcome to AFOREHEALTH
                        </span>
                        <h3 class="fw-bold text-dark mt-2 mb-2">Get 10% OFF Your First Order</h3>
                        <p class="text-muted small mb-3">Experience medical-grade leakproof comfort. Use coupon code at checkout:</p>
                        
                        <div class="p-3 bg-light rounded-3 border d-inline-flex align-items-center gap-3 mb-3">
                            <span class="fs-4 fw-bold font-monospace" style="color: var(--afh-primary); letter-spacing: 2px;">
                                <?= e(get_setting('first_order_coupon_code', 'WELCOME10')) ?>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="navigator.clipboard.writeText('<?= e(get_setting('first_order_coupon_code', 'WELCOME10')) ?>'); showToast('Coupon code copied!', 'success');">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>

                        <div>
                            <a href="<?= url('shop') ?>" class="btn btn-afh-primary rounded-pill px-5 fw-bold w-100 py-3" data-bs-dismiss="modal">
                                Shop First-Period Essentials
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mobile Bottom Navigation Bar -->
    <?php require __DIR__ . '/../components/mobile-nav.php'; ?>

    <!-- Cart Drawer Modal / Offcanvas -->
    <?php require __DIR__ . '/../components/cart-drawer.php'; ?>

    <!-- Custom Confirmation Modal Component -->
    <?php require __DIR__ . '/../components/confirm-modal.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/confirm-modal.js') ?>"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/cart.js') ?>"></script>

    <script>
    // PWA Service Worker Registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    // First order polite offer popup (once per session, delayed 3.5s)
    window.addEventListener('load', () => {
        if (!sessionStorage.getItem('first_order_dismissed')) {
            setTimeout(() => {
                const el = document.getElementById('firstOrderModal');
                if (el) {
                    const modal = new bootstrap.Modal(el);
                    modal.show();
                }
            }, 3500);
        }
    });

    // Smart Search AJAX Suggestions
    let searchTimeout = null;
    function handleHeaderSearch(q) {
        clearTimeout(searchTimeout);
        const box = document.getElementById('headerSearchSuggestions');
        if (!box) return;

        if (!q || q.trim().length < 2) {
            box.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('/api/search/suggestions?q=' + encodeURIComponent(q.trim()))
                .then(res => res.json())
                .then(data => {
                    if ((data.categories && data.categories.length > 0) || (data.products && data.products.length > 0)) {
                        let html = '';
                        if (data.categories.length > 0) {
                            html += '<div class="small fw-bold text-muted text-uppercase mb-1">Categories:</div><div class="d-flex flex-wrap gap-1 mb-3">';
                            data.categories.forEach(c => {
                                html += `<a href="/category/${c.slug}" class="badge bg-light text-dark border text-decoration-none py-1 px-2">${c.name}</a>`;
                            });
                            html += '</div>';
                        }
                        if (data.products.length > 0) {
                            html += '<div class="small fw-bold text-muted text-uppercase mb-2">Matching Products:</div><div class="d-flex flex-column gap-2">';
                            data.products.forEach(p => {
                                html += `
                                    <a href="${p.url}" class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none border-bottom">
                                        <img src="${p.image_url}" style="width: 45px; height: 45px; object-fit: contain;" class="rounded border p-1" alt="">
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold text-dark text-truncate" style="max-width: 250px;">${p.name}</div>
                                            <div class="small fw-bold" style="color: var(--afh-primary);">${p.formatted_price}</div>
                                        </div>
                                    </a>
                                `;
                            });
                            html += '</div>';
                        }
                        box.innerHTML = html;
                        box.style.display = 'block';
                    } else {
                        box.innerHTML = '<div class="text-muted small py-2 text-center">No instant suggestions. Press Enter to search all.</div>';
                        box.style.display = 'block';
                    }
                })
                .catch(() => { box.style.display = 'none'; });
        }, 250);
    }

    document.addEventListener('click', (e) => {
        const box = document.getElementById('headerSearchSuggestions');
        const input = document.getElementById('headerSearchInput');
        if (box && input && !box.contains(e.target) && !input.contains(e.target)) {
            box.style.display = 'none';
        }
    });
    </script>
</body>
</html>
