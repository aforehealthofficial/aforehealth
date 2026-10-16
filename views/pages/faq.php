<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F5E8F0 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container text-center" style="max-width: 820px;">
        <span class="badge bg-secondary text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Help Center</span>
        <h1 class="display-5 fw-bold text-dark mb-2">Frequently Asked Questions</h1>
        <p class="lead text-muted mb-0">
            Answers to all your questions about absorbency, washing, sizing, returns, and discreet packaging.
        </p>
    </div>
</section>

<div class="container py-5" style="max-width: 860px;">
    <?php foreach ($groupedFaqs as $categoryName => $faqs): ?>
        <div class="mb-5">
            <h3 class="fw-bold mb-3 text-dark pb-2 border-bottom"><?= e($categoryName) ?></h3>
            <div class="accordion accordion-flush" id="faqGroup<?= md5($categoryName) ?>">
                <?php foreach ($faqs as $fIdx => $fq): ?>
                    <div class="accordion-item border rounded-3 mb-2 overflow-hidden shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fqItem<?= $fq['id'] ?>">
                                <?= e($fq['question']) ?>
                            </button>
                        </h2>
                        <div id="fqItem<?= $fq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqGroup<?= md5($categoryName) ?>">
                            <div class="accordion-body text-muted" style="line-height: 1.7;">
                                <?= e($fq['answer']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Still have questions? -->
    <div class="p-4 rounded-4 bg-white border text-center shadow-sm">
        <h4 class="fw-bold text-dark mb-2">Still have questions?</h4>
        <p class="text-muted small mb-3">Our customer care and intimacy wellness specialists are always here for you.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= url('contact-us') ?>" class="btn btn-outline-dark fw-bold">Contact Support</a>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', '919654504052')) ?>" target="_blank" class="btn btn-success fw-bold">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
