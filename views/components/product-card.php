<?php
/**
 * Reusable Product Card Component
 * Expected parameter: $product (array)
 */
$discountPct = $product['discount_percent'] ?? 0;
if (!$discountPct && $product['mrp'] > $product['price']) {
    $discountPct = round((($product['mrp'] - $product['price']) / $product['mrp']) * 100);
}
$isLowStock = ($product['stock'] <= ($product['low_stock_threshold'] ?? 5) && $product['stock'] > 0);
?>
<div class="product-card">
    <!-- Image Box with Badges & Wishlist -->
    <div class="product-image-box">
        <a href="<?= url('product/' . $product['slug']) ?>">
            <img src="<?= upload_url('products/' . ($product['primary_image'] ?: 'cat_period_panties.svg')) ?>" 
                 alt="<?= e($product['name']) ?>" 
                 loading="lazy">
        </a>

        <!-- Badges -->
        <div class="badge-tag-wrap">
            <?php if (!empty($product['is_bestseller'])): ?>
                <span class="badge-pill-afh badge-bestseller">BESTSELLER</span>
            <?php elseif (!empty($product['is_new_arrival'])): ?>
                <span class="badge-pill-afh badge-new">NEW</span>
            <?php endif; ?>

            <?php if ($product['category_id'] == 1): ?>
                <span class="badge-pill-afh badge-leakproof">LEAKPROOF</span>
            <?php endif; ?>

            <?php if ($isLowStock): ?>
                <span class="badge-pill-afh bg-warning text-dark">ONLY <?= $product['stock'] ?> LEFT</span>
            <?php endif; ?>
        </div>

        <!-- Float Wishlist Heart -->
        <button type="button" 
                class="wishlist-btn-float btn-wishlist-toggle" 
                data-product-id="<?= $product['id'] ?>" 
                title="Add to Wishlist" 
                aria-label="Wishlist">
            <i class="bi bi-heart"></i>
        </button>
    </div>

    <!-- Product Info Details -->
    <div class="product-info-box">
        <?php if (!empty($product['category_name'])): ?>
            <div class="product-category-crumb"><?= e($product['category_name']) ?></div>
        <?php endif; ?>

        <h3 class="product-title">
            <a href="<?= url('product/' . $product['slug']) ?>" title="<?= e($product['name']) ?>">
                <?= e($product['name']) ?>
            </a>
        </h3>

        <!-- Rating & Count -->
        <div class="rating-stars-row">
            <span class="stars-solid">★★★★★</span>
            <span class="rating-num"><?= number_format((float)($product['rating_avg'] ?? 4.9), 1) ?></span>
            <span class="reviews-cnt">(<?= (int)($product['rating_count'] ?? 120) ?>)</span>
        </div>

        <!-- Price & Discount -->
        <div class="product-price-row">
            <span class="selling-price">₹<?= number_format((float)$product['price'], 0) ?></span>
            <?php if ($product['mrp'] > $product['price']): ?>
                <span class="mrp-strikethrough">₹<?= number_format((float)$product['mrp'], 0) ?></span>
                <span class="discount-badge"><?= $discountPct ?>% OFF</span>
            <?php endif; ?>
        </div>

        <!-- Quick Add Action -->
        <?php if ($product['stock'] > 0): ?>
            <button type="button" 
                    class="btn btn-quick-add btn-quick-add-action" 
                    data-product-id="<?= $product['id'] ?>">
                <i class="bi bi-bag-plus me-1"></i> Quick Add to Bag
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-quick-add text-muted" disabled>
                Out of Stock
            </button>
        <?php endif; ?>
    </div>
</div>
