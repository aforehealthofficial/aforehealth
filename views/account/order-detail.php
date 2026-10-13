<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 880px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account/orders') ?>">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?= e($order['order_number']) ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 pb-3 mb-4 border-bottom">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Order #<?= e($order['order_number']) ?></h1>
                <small class="text-muted">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="status-pill status-<?= strtolower($order['order_status']) ?>"><?= e($order['order_status']) ?></span>
                <form method="POST" action="<?= url('account/reorder') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_number" value="<?= e($order['order_number']) ?>">
                    <button type="submit" class="btn btn-sm btn-afh-primary fw-bold"><i class="bi bi-arrow-repeat me-1"></i> Buy Again</button>
                </form>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', '919876543210')) ?>?text=<?= urlencode('Hi AFOREHEALTH, I need help with Order ' . $order['order_number'] . '.') ?>" target="_blank" class="btn btn-sm btn-outline-success fw-bold">
                    <i class="bi bi-whatsapp me-1"></i> Need Help?
                </a>
                <a href="<?= url('order/invoice/' . $order['order_number']) ?>" target="_blank" class="btn btn-sm btn-outline-dark fw-bold">
                    <i class="bi bi-printer me-1"></i> Tax Invoice
                </a>
            </div>
        </div>

        <!-- 7-Step Delivery Status Progression (Feature 24) -->
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
        <div class="p-3 p-md-4 rounded-4 bg-light border mb-4">
            <h6 class="fw-bold text-dark small text-uppercase mb-3"><i class="bi bi-truck me-2 text-primary"></i>Delivery Status Journey</h6>
            <div class="row g-2 text-center small">
                <?php $i = 0; foreach ($flowSteps as $key => $label): ?>
                    <div class="col">
                        <div class="p-2 rounded-3 <?= ($curIndex >= $i && $curIndex !== -1) ? 'bg-success text-white' : 'bg-white text-muted border' ?>" style="font-size: 0.75rem; min-height: 52px; display: flex; flex-direction: column; justify-content: center;">
                            <i class="bi <?= ($curIndex >= $i && $curIndex !== -1) ? 'bi-check-circle-fill' : 'bi-circle' ?> mb-1"></i>
                            <span class="fw-bold"><?= $label ?></span>
                        </div>
                    </div>
                <?php $i++; endforeach; ?>
            </div>

            <!-- Courier Details & Estimated Delivery -->
            <div class="row g-2 small mt-3 pt-3 border-top">
                <div class="col-sm-4">
                    <span class="text-muted">Courier:</span>
                    <strong class="text-dark d-block"><?= e($order['courier_name'] ?: 'Delhivery Express') ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted">Tracking AWB:</span>
                    <strong class="font-monospace text-primary d-block"><?= !empty($order['tracking_number']) ? e($order['tracking_number']) : 'Assigned upon dispatch' ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted">Estimated Delivery:</span>
                    <strong class="text-success d-block">
                        <?php 
                        $pincode = $order['shipping_address']['pincode'] ?? '';
                        $deliverySvc = new \Services\DeliveryEstimationService();
                        $est = $deliverySvc->getEstimateForPincode($pincode);
                        echo e($est['date_range_formatted']);
                        ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <h5 class="fw-bold mb-3">Items in this Order</h5>
        <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($order['items'] as $item): ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong class="text-dark"><?= e($item['product_name']) ?></strong><br>
                        <small class="text-muted"><?= e($item['variant_details']) ?> | SKU: <?= e($item['sku']) ?> | Qty: <?= (int)$item['quantity'] ?></small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-dark">₹<?= number_format($item['total'], 0) ?></div>
                        <small class="text-muted">₹<?= number_format($item['price'], 0) ?> each</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Address and Price Breakdown -->
        <div class="row g-4 pt-2">
            <div class="col-md-6">
                <h6 class="fw-bold text-uppercase small text-muted">Shipping Address:</h6>
                <div class="p-3 rounded-3 bg-light border small" style="line-height: 1.6;">
                    <strong><?= e($order['shipping_address']['full_name']) ?></strong><br>
                    <?= e($order['shipping_address']['house_flat']) ?>, <?= e($order['shipping_address']['area']) ?><br>
                    <?= e($order['shipping_address']['city']) ?>, <?= e($order['shipping_address']['state']) ?> - <strong><?= e($order['shipping_address']['pincode']) ?></strong><br>
                    Phone: <?= e($order['shipping_address']['mobile']) ?>
                </div>
            </div>

            <div class="col-md-6">
                <h6 class="fw-bold text-uppercase small text-muted">Payment &amp; Totals:</h6>
                <div class="p-3 rounded-3 bg-light border small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <span class="fw-bold">₹<?= number_format($order['subtotal'], 0) ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>Coupon Discount:</span>
                            <span class="fw-bold">-₹<?= number_format($order['discount'], 0) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Shipping:</span>
                        <span class="fw-bold"><?= $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 0) : 'FREE' ?></span>
                    </div>
                    <?php if ($order['cod_fee'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span>COD Fee:</span>
                            <span class="fw-bold">₹<?= number_format($order['cod_fee'], 0) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between border-top pt-2 mt-1">
                        <strong class="fs-6">Grand Total:</strong>
                        <strong class="fs-5 text-primary">₹<?= number_format($order['grand_total'], 0) ?></strong>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        Payment Mode: <strong><?= strtoupper($order['payment_method']) ?></strong> (<?= strtoupper($order['payment_status']) ?>)
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
