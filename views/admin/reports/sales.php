<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Sales &amp; Revenue Analytics</h5>
                    <p class="text-muted small mb-0">View daily gross revenue, discounts, shipping fees, and net realized sales.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= url('admin/reports/sales?start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate) . '&export=csv') ?>" class="btn btn-outline-success fw-bold d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                    </a>
                </div>
            </div>

            <form action="<?= url('admin/reports/sales') ?>" method="GET" class="row g-3 align-items-end mt-2">
                <div class="col-md-4 col-sm-6">
                    <label class="form-label small fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>">
                </div>
                <div class="col-md-4 col-sm-6">
                    <label class="form-label small fw-bold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($endDate) ?>">
                </div>
                <div class="col-md-4 col-sm-12">
                    <button type="submit" class="btn btn-afh-primary w-100 fw-bold">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <?php
        $sumOrders = 0;
        $sumGross = 0;
        $sumDiscount = 0;
        $sumNet = 0;
        foreach ($reportData as $row) {
            $sumOrders += $row['total_orders'];
            $sumGross += $row['gross_sales'];
            $sumDiscount += $row['total_discount'];
            $sumNet += $row['net_sales'];
        }
        ?>

        <!-- Summary KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <span class="text-muted small fw-bold">TOTAL ORDERS</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0"><?= number_format($sumOrders) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <span class="text-muted small fw-bold">GROSS SALES</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">₹<?= number_format($sumGross, 2) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <span class="text-muted small fw-bold">DISCOUNTS GIVEN</span>
                    <h4 class="fw-bold text-danger mt-1 mb-0">-₹<?= number_format($sumDiscount, 2) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center border-start border-4 border-success">
                    <span class="text-muted small fw-bold">NET REALIZED REVENUE</span>
                    <h4 class="fw-bold text-success mt-1 mb-0">₹<?= number_format($sumNet, 2) ?></h4>
                </div>
            </div>
        </div>

        <!-- Daily Table -->
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold m-0 text-dark">Daily Sales Breakdown</h6>
                <span class="badge bg-light text-dark border"><?= count($reportData) ?> Days Listed</span>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Orders</th>
                            <th>Gross Subtotal</th>
                            <th>Discounts</th>
                            <th>Shipping</th>
                            <th class="text-end">Net Sales (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reportData)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No sales orders found for this selected range.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <td class="fw-bold"><?= date('d M Y (D)', strtotime($row['order_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= $row['total_orders'] ?> orders</span></td>
                                    <td>₹<?= number_format($row['gross_sales'], 2) ?></td>
                                    <td class="text-danger">-₹<?= number_format($row['total_discount'], 2) ?></td>
                                    <td>₹<?= number_format($row['total_shipping'], 2) ?></td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format($row['net_sales'], 2) ?></td>
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
