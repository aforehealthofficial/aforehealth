<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 960px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Contact Us</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
            <span class="badge bg-light text-primary fw-bold text-uppercase mb-2">We're Here For You</span>
            <h1 class="h2 fw-bold text-dark mb-3">Get in Touch</h1>
            <p class="text-muted small mb-4" style="line-height: 1.6;">
                Have questions about our leakproof underwear, need sizing guidance, or want help tracking your order? Our all-women support team is ready to assist.
            </p>

            <div class="d-flex flex-column gap-3 mb-4">
                <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-white border">
                    <i class="bi bi-whatsapp text-success fs-3"></i>
                    <div>
                        <strong class="d-block text-dark">WhatsApp Support</strong>
                        <small class="text-muted">Fastest response (Mon-Sat, 9AM-8PM)</small><br>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>" target="_blank" class="fw-bold text-success small">Chat on WhatsApp →</a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-white border">
                    <i class="bi bi-envelope text-primary fs-3"></i>
                    <div>
                        <strong class="d-block text-dark">Email Support</strong>
                        <small class="text-muted"><?= e($email) ?></small><br>
                        <span class="small text-muted">Response within 24 hours</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-white border">
                    <i class="bi bi-geo-alt text-dark fs-3"></i>
                    <div>
                        <strong class="d-block text-dark">Headquarters</strong>
                        <small class="text-muted"><?= nl2br(e($address)) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="fw-bold text-dark mb-4">Send Us a Message</h4>
                <form action="<?= url('contact-us/submit') ?>" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Your Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Priya Sharma" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Number *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+91</span>
                                <input type="tel" name="mobile" class="form-control" maxlength="10" placeholder="9876543210" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="care@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="Sizing Help">Sizing &amp; Fit Assistance</option>
                                <option value="Order Tracking">Order Tracking &amp; Delivery</option>
                                <option value="Hygienic Exchange">Hygienic Exchange Inquiry</option>
                                <option value="Product Feedback">Product Feedback</option>
                                <option value="Other">Other Query</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Your Message *</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="How can we help you feel more comfortable?" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-afh-primary w-100 py-3 fw-bold">Send Message →</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
