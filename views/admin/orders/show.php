<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/orders') ?>" class="small text-muted text-decoration-none">← Back to Orders</a>
        <h4 class="fw-bold m-0 text-dark">Order #<?= e($order['order_number']) ?></h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/orders/' . $order['order_number'] . '/shipping-label') ?>" target="_blank" class="btn btn-outline-primary fw-bold">
            <i class="bi bi-tag me-1"></i> Print 4×6 Shipping Label
        </a>
        <a href="<?= url('order/invoice/' . $order['order_number']) ?>" target="_blank" class="btn btn-outline-dark fw-bold">
            <i class="bi bi-printer me-1"></i> Print Tax Invoice
        </a>
    </div>
</div>

<div class="row g-4">
    
    <!-- Left: Order Items & Customer Details -->
    <div class="col-lg-8">
        
        <!-- Items in Order -->
        <div class="admin-table-card mb-4">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold m-0">Order Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['product_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($item['variant_details']) ?></small>
                                </td>
                                <td><code><?= e($item['sku']) ?></code></td>
                                <td>₹<?= number_format($item['price'], 0) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['total'], 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-top bg-light">
                <div class="row justify-content-end">
                    <div class="col-md-5 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal:</span>
                            <span class="fw-bold">₹<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-1 text-success">
                                <span>Discount:</span>
                                <span class="fw-bold">-₹<?= number_format($order['discount'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Shipping:</span>
                            <span class="fw-bold">₹<?= number_format($order['shipping_fee'], 2) ?></span>
                        </div>
                        <?php if ($order['cod_fee'] > 0): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span>COD Fee:</span>
                                <span class="fw-bold">₹<?= number_format($order['cod_fee'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between border-top pt-2 mt-1">
                            <strong class="fs-6">Grand Total:</strong>
                            <strong class="fs-5 text-primary">₹<?= number_format($order['grand_total'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status History Timeline -->
        <div class="admin-table-card">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold m-0">Status Timeline &amp; Activity Log</h6>
            </div>
            <div class="p-4">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($order['history'] as $h): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="badge bg-secondary text-dark text-uppercase me-2"><?= e($h['status']) ?></span>
                                <span><?= e($h['notes']) ?></span>
                                <small class="text-muted ms-2">(By: <?= e($h['created_by']) ?>)</small>
                            </div>
                            <small class="text-muted"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>

    <!-- Right: Update Status & Address -->
    <div class="col-lg-4">
        
        <!-- Status & Courier Update Box -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h6 class="fw-bold mb-3 text-dark pb-2 border-bottom">Fulfillment &amp; Status</h6>
            
            <form action="<?= url('admin/orders/update-status') ?>" method="POST">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <input type="hidden" name="order_number" value="<?= e($order['order_number']) ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Order Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['CONFIRMED', 'PROCESSING', 'PACKED', 'SHIPPED', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED', 'RETURNED'] as $st): ?>
                            <option value="<?= $st ?>" <?= $order['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Courier Partner</label>
                    <input type="text" name="courier_name" class="form-control" value="<?= e($order['courier_name'] ?? 'Delhivery Express') ?>" placeholder="e.g. Delhivery, Blue Dart">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tracking AWB Number</label>
                    <input type="text" name="tracking_number" class="form-control" value="<?= e($order['tracking_number'] ?? '') ?>" placeholder="e.g. DEL987654321IN">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Status Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional internal note or customer notice"></textarea>
                </div>

                <button type="submit" class="btn btn-afh-primary w-100 fw-bold">Update Order</button>
            </form>
        </div>

        <!-- Customer & Shipping Address Info -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3">
                <h6 class="fw-bold m-0 text-dark">Customer &amp; Shipping</h6>
                <?php if (!empty($order['user_id'])): ?>
                    <a href="<?= url('admin/customers/' . $order['user_id']) ?>" class="btn btn-outline-dark btn-sm fw-bold">
                        <i class="bi bi-person-badge me-1"></i> Profile
                    </a>
                <?php endif; ?>
            </div>
            <div class="small mb-3">
                <strong class="text-dark fs-6"><?= e($order['customer_name']) ?></strong><br>
                <span>Phone: +91 <?= e($order['customer_mobile']) ?></span><br>
                <span>Email: <?= e($order['customer_email'] ?: 'Not provided') ?></span><br>
                <div class="mt-2 pt-2 border-top">
                    <span class="text-muted small fw-bold">WhatsApp Community Consent:</span><br>
                    <?php if (!empty($order['whatsapp_opt_in'])): ?>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold px-2 py-1 mt-1">
                            <i class="bi bi-whatsapp me-1"></i> OPTED-IN (CONSENT GIVEN)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-light text-muted border fw-normal px-2 py-1 mt-1">
                            <i class="bi bi-x-circle me-1"></i> Not Opted In
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <h6 class="fw-bold text-uppercase small text-muted mb-2">Delivery Address:</h6>
            <div class="p-3 rounded-3 bg-light border small" style="line-height: 1.5;">
                <?= e($order['shipping_address']['house_flat']) ?>, <?= e($order['shipping_address']['area']) ?><br>
                <?= !empty($order['shipping_address']['landmark']) ? 'Landmark: ' . e($order['shipping_address']['landmark']) . '<br>' : '' ?>
                <?= e($order['shipping_address']['city']) ?>, <?= e($order['shipping_address']['state']) ?> - <strong><?= e($order['shipping_address']['pincode']) ?></strong>
            </div>
        </div>

    </div>

</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
