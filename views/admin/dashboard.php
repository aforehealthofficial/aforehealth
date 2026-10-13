<?php require __DIR__ . '/layout-header.php'; ?>

<!-- 1. SaaS Dashboard Stat Cards -->
<div class="row g-3 mb-4">
    
    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value text-primary">₹<?= number_format($stats['total_sales'], 0) ?></div>
            <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
            <small class="text-success fw-bold"><i class="bi bi-graph-up-arrow"></i> Verified sales</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value">₹<?= number_format($stats['today_sales'], 0) ?></div>
            <div class="stat-icon"><i class="bi bi-calendar2-check"></i></div>
            <small class="text-muted">Live daily intake</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= (int)$stats['total_orders'] ?></div>
            <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
            <small class="text-warning fw-bold"><?= (int)$stats['pending_orders'] ?> Pending fulfillment</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Pending Reviews</div>
            <div class="stat-value text-danger"><?= (int)$stats['pending_reviews'] ?></div>
            <div class="stat-icon"><i class="bi bi-star"></i></div>
            <a href="<?= url('admin/reviews') ?>" class="small text-danger fw-bold text-decoration-underline">Moderate Now →</a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Registered Customers</div>
            <div class="stat-value"><?= (int)$stats['total_customers'] ?></div>
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <small class="text-muted">Mobile verified users</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Catalog Products</div>
            <div class="stat-value"><?= (int)$stats['total_products'] ?></div>
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <small class="text-muted">Active published SKUs</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Low Stock Alerts</div>
            <div class="stat-value text-warning"><?= (int)$stats['low_stock'] ?></div>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <a href="<?= url('admin/inventory') ?>" class="small text-warning fw-bold text-decoration-underline">Restock items →</a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Average Order (AOV)</div>
            <div class="stat-value text-success">₹<?= number_format($stats['aov'] ?? 0, 0) ?></div>
            <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
            <small class="text-muted">Net order average</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-metric-card">
            <div class="stat-label">Repeat Customer Rate</div>
            <div class="stat-value text-primary"><?= $stats['repeat_customer_pct'] ?? 0 ?>%</div>
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <small class="text-muted">Customers with 2+ orders</small>
        </div>
    </div>

</div>

<!-- 2. Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0 text-dark">Revenue Trend (Last 7 Days)</h6>
                <span class="badge bg-light text-primary">Live Data</span>
            </div>
            <div style="height: 280px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3 text-dark">Products by Category</h6>
            <div style="height: 280px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 2.5 Top Products & Payment Methods Analytics -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3 text-dark">Top Bestselling Products</h6>
            <?php if (empty($topProducts)): ?>
                <div class="text-muted small py-4 text-center">No product sales recorded yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0 small">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Units Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $tp): ?>
                                <tr>
                                    <td><strong class="text-dark"><?= e($tp['product_name']) ?></strong></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= (int)$tp['sold'] ?> units</span></td>
                                    <td class="text-end fw-bold text-primary">₹<?= number_format($tp['revenue'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3 text-dark">Payment Methods Breakdown</h6>
            <?php if (empty($paymentMethods)): ?>
                <div class="text-muted small py-4 text-center">No orders processed yet.</div>
            <?php else: ?>
                <div class="list-group list-group-flush small">
                    <?php foreach ($paymentMethods as $pm): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                            <div>
                                <strong class="text-dark"><?= strtoupper($pm['payment_method']) ?></strong>
                                <small class="text-muted d-block"><?= (int)$pm['count'] ?> orders</small>
                            </div>
                            <span class="fw-bold text-dark fs-6">₹<?= number_format($pm['revenue'], 0) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 3. Tables Row (Recent Orders & Low Stock) -->
<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="admin-table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">Recent Store Orders</h6>
                <a href="<?= url('admin/orders') ?>" class="small text-primary fw-bold">View All Orders →</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td><strong><?= e($ro['order_number']) ?></strong></td>
                                <td>
                                    <?= e($ro['customer_name']) ?><br>
                                    <small class="text-muted">+91 <?= e($ro['customer_mobile']) ?></small>
                                </td>
                                <td><?= date('d M, h:i A', strtotime($ro['created_at'])) ?></td>
                                <td class="fw-bold">₹<?= number_format($ro['grand_total'], 0) ?></td>
                                <td><span class="badge bg-light text-dark"><?= strtoupper($ro['payment_method']) ?></span></td>
                                <td>
                                    <span class="status-pill status-<?= strtolower($ro['order_status']) ?>"><?= e($ro['order_status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= url('admin/orders/' . $ro['order_number']) ?>" class="btn btn-sm btn-outline-dark">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-lg-4">
        <div class="admin-table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0 text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock Alerts</h6>
                <a href="<?= url('admin/inventory') ?>" class="small text-muted">All Inventory</a>
            </div>
            <div class="p-3">
                <?php if (empty($lowStockProducts)): ?>
                    <div class="text-center py-4 text-muted small">All items are sufficiently stocked!</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($lowStockProducts as $lp): ?>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                <div>
                                    <strong class="d-block text-dark small text-truncate" style="max-width: 170px;"><?= e($lp['name']) ?></strong>
                                    <small class="text-muted">SKU: <?= e($lp['sku']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">Only <?= (int)$lp['stock'] ?> left</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= $chartDates ?>,
            datasets: [{
                label: 'Gross Sales (INR)',
                data: <?= $chartSales ?>,
                borderColor: '#8B5E83',
                backgroundColor: 'rgba(139, 94, 131, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#8B5E83'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Category Share Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catLabels = <?= json_encode(array_column($catRevenue, 'name')) ?>;
    const catCounts = <?= json_encode(array_column($catRevenue, 'count')) ?>;
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catCounts,
                backgroundColor: ['#8B5E83', '#E8B7C8', '#4E8B68', '#D9822B', '#3A2436', '#693D62']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
        }
    });
});
</script>

<?php require __DIR__ . '/layout-footer.php'; ?>
