<!-- Mobile Fixed Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <a href="<?= url() ?>" class="mobile-nav-item <?= empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">
        <i class="bi bi-house"></i>
        <span>Home</span>
    </a>
    <a href="<?= url('shop') ?>" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/shop') ? 'active' : '' ?>">
        <i class="bi bi-grid"></i>
        <span>Shop</span>
    </a>
    <a href="<?= url('search') ?>" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/search') ? 'active' : '' ?>">
        <i class="bi bi-search"></i>
        <span>Search</span>
    </a>
    <a href="<?= url('account/wishlist') ?>" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/wishlist') ? 'active' : '' ?>">
        <i class="bi bi-heart"></i>
        <span>Wishlist</span>
        <?php $wCount = get_wishlist_count(); ?>
        <?php if ($wCount > 0): ?>
            <span class="badge-count wishlist-badge position-absolute top-0 end-0 translate-middle-y" style="font-size: 0.6rem; padding: 2px 5px;"><?= $wCount ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= url('account') ?>" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/account') ? 'active' : '' ?>">
        <i class="bi bi-person"></i>
        <span>Account</span>
    </a>
</nav>
