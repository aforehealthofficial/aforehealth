<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 820px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Track Order</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
        <div class="text-center mb-4">
            <i class="bi bi-geo-alt-fill text-primary display-4 mb-2 d-block"></i>
            <h1 class="h3 fw-bold text-dark">Track Your AFOREHEALTH Order</h1>
            <p class="text-muted small">Enter your order ID and 10-digit mobile number to view live courier status.</p>
        </div>

        <form method="GET" action="<?= url('track-order') ?>" class="row g-3 justify-content-center">
            <div class="col-md-5">
                <input type="text" name="order_number" class="form-control" placeholder="Order Number (e.g. AFH-20261001-000101)" value="<?= e($orderNumber) ?>" required>
            </div>
            <div class="col-md-4">
                <input type="tel" name="mobile" class="form-control" placeholder="Mobile Number (Optional)" value="<?= e($mobile) ?>" maxlength="10">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-afh-primary w-100 fw-bold">Track Now</button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small mt-4 mb-0 text-center rounded-3">
                <i class="bi bi-exclamation-circle me-1"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($order)): ?>
        <?php
            $flowSteps = [
                'PENDING' => 'Order Placed',
                'CONFIRMED' => 'Confirmed',
                'PROCESSING' => 'Processing',
                'PACKED' => 'Packed',
                'SHIPPED' => 'Shipped',
                'OUT_FOR_DELIVERY' => 'Out for Delivery',
                'DELIVERED' => 'Delivered'
            ];
            $flowKeys = array_keys($flowSteps);
            $curIndex = array_search(strtoupper($order['order_status']), $flowKeys);
            if ($curIndex === false) {
                if (in_array(strtoupper($order['order_status']), ['CANCELLED', 'RETURNED'])) {
                    $curIndex = -1;
                } else {
                    $curIndex = 1;
                }
            }
        ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold m-0">Order #<?= e($order['order_number']) ?></h5>
                    <small class="text-muted">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></small>
                </div>
                <span class="status-pill status-<?= strtolower($order['order_status']) ?>"><?= e($order['order_status']) ?></span>
            </div>

            <!-- Status Timeline (7 Steps) -->
            <div class="row text-center mb-4 g-2">
                <?php $idx = 0; foreach ($flowSteps as $stKey => $stLabel): ?>
                    <div class="col">
                        <div class="p-2 rounded-3 <?= ($curIndex >= $idx && $curIndex !== -1) ? 'bg-success text-white' : 'bg-light text-muted border' ?>" style="font-size: 0.72rem; min-height: 50px; display: flex; flex-direction: column; justify-content: center;">
                            <i class="bi <?= ($curIndex >= $idx && $curIndex !== -1) ? 'bi-check-circle-fill' : 'bi-circle' ?> mb-1"></i>
                            <span class="fw-bold"><?= $stLabel ?></span>
                        </div>
                    </div>
                <?php $idx++; endforeach; ?>
            </div>

            <!-- Courier Details -->
            <?php if (!empty($order['tracking_number'])): ?>
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase fw-bold">Courier Partner:</small>
                            <div class="fw-bold text-dark"><?= e($order['courier_name'] ?: 'Delhivery Express') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase fw-bold">Tracking Number (AWB):</small>
                            <div class="fw-bold text-primary font-monospace"><?= e($order['tracking_number']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Items -->
            <h6 class="fw-bold mb-2">Package Contents:</h6>
            <div class="d-flex flex-column gap-2 mb-4">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                        <div>
                            <strong><?= e($item['product_name']) ?></strong><br>
                            <span class="text-muted"><?= e($item['variant_details']) ?> | Qty: <?= $item['quantity'] ?></span>
                        </div>
                        <span class="fw-bold">₹<?= number_format($item['total'], 0) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end">
                <a href="<?= url('order/invoice/' . $order['order_number']) ?>" target="_blank" class="btn btn-outline-dark btn-sm fw-bold">
                    <i class="bi bi-printer me-1"></i> Print Tax Invoice
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
