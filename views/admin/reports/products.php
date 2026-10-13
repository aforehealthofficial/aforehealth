<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Top Performing Products</h5>
                    <p class="text-muted small mb-0">Ranking of best selling items across all completed &amp; active customer orders.</p>
                </div>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold m-0 text-dark">Top 25 Bestselling SKUs &amp; Products</h6>
                <span class="badge bg-light text-dark border"><?= count($topProducts) ?> Products</span>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Units Sold</th>
                            <th class="text-end">Total Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No completed order line items yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php $rank = 1; foreach ($topProducts as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= $rank <= 3 ? 'warning text-dark' : 'light text-dark border' ?> px-2 py-1">
                                            #<?= $rank++ ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark"><?= e($item['product_name']) ?></td>
                                    <td><code><?= e($item['sku'] ?? 'N/A') ?></code></td>
                                    <td><span class="badge bg-primary px-3 py-2 fs-7"><?= number_format($item['total_sold']) ?> units</span></td>
                                    <td class="text-end fw-bold text-success fs-6">₹<?= number_format($item['total_revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
