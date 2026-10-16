<?php
$addr = $order['shipping_address'] ?? [];
$isCod = ($order['payment_method'] === 'cod');
$pincode = $addr['pincode'] ?? '';
$city = strtoupper($addr['city'] ?? '');
$state = strtoupper($addr['state'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - <?= e($order['order_number']) ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #000;
        }
        body {
            background: #F4F4F6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .no-print-toolbar {
            width: 4in;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #8B5E83;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-close-win {
            background: #e2e8f0;
            color: #333;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        /* 4x6 Thermal Label Container */
        .shipping-label {
            width: 4in;
            min-height: 6in;
            background: #fff;
            border: 2px solid #000;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .brand-name {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .brand-sub {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .courier-box {
            text-align: right;
            font-size: 12px;
            font-weight: 800;
        }
        /* Barcode Area */
        .barcode-section {
            text-align: center;
            padding: 8px 0;
            border-bottom: 2px solid #000;
        }
        .barcode-bars {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 48px;
            gap: 2px;
            margin: 0 auto 4px auto;
        }
        .bar {
            background: #000;
            height: 100%;
        }
        .barcode-number {
            font-family: monospace;
            font-weight: 900;
            font-size: 14px;
            letter-spacing: 2px;
        }
        /* Routing Block */
        .routing-block {
            display: flex;
            border-bottom: 2px solid #000;
        }
        .routing-cell {
            padding: 6px;
            border-right: 1px solid #000;
            flex: 1;
        }
        .routing-cell:last-child {
            border-right: none;
        }
        .pincode-giant {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 2px;
            line-height: 1;
        }
        /* Payment banner */
        .payment-banner {
            border-bottom: 2px solid #000;
            padding: 6px 8px;
            font-size: 14px;
            font-weight: 900;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-banner.cod {
            background: #000;
            color: #fff;
        }
        .payment-banner.cod * {
            color: #fff;
        }
        /* Destination / Deliver To */
        .deliver-to-section {
            padding: 8px 6px;
            border-bottom: 1px solid #000;
            font-size: 12px;
            line-height: 1.35;
        }
        .section-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
            color: #444;
        }
        .recipient-name {
            font-size: 15px;
            font-weight: 900;
            margin-bottom: 3px;
        }
        .recipient-phone {
            font-size: 13px;
            font-weight: 800;
            margin-top: 4px;
        }
        /* Items manifest */
        .items-section {
            padding: 6px;
            border-bottom: 1px solid #000;
            font-size: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table td {
            padding: 2px 0;
        }
        /* Return Address */
        .return-section {
            padding-top: 6px;
            font-size: 9px;
            color: #222;
            line-height: 1.25;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .shipping-label {
                border: 2px solid #000;
                margin: 0;
                width: 100%;
                height: 100%;
                page-break-after: avoid;
            }
            @page {
                size: 4in 6in;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <a href="<?= url('admin/orders/' . $order['order_number']) ?>" class="btn-close-win">← Back to Order</a>
        <button class="btn-print" onclick="window.print()">🖨️ Print Label (4×6)</button>
    </div>

    <div class="shipping-label">
        <div>
            <!-- Header -->
            <div class="label-header">
                <div>
                    <div class="brand-name">AFOREHEALTH</div>
                    <div class="brand-sub">Discreet Packaging Guaranteed</div>
                </div>
                <div class="courier-box">
                    <div><?= e($order['courier_name'] ?: 'STANDARD EXPRESS') ?></div>
                    <div style="font-size: 9px; font-weight: normal;">AIR / SURFACE</div>
                </div>
            </div>

            <!-- Barcode Area -->
            <div class="barcode-section">
                <div class="barcode-bars">
                    <?php 
                    // Render simulated Code-128 barcode pattern
                    $codeDigits = preg_replace('/[^A-Za-z0-9]/', '', $order['order_number']);
                    $bars = [3,1,2,4,1,3,2,1,4,2,1,3,1,2,4,2,3,1,2,3,1,4,1,2,3,1,4,2,1,3,2,1,4,1,3,2,4,1,2,3,1,2,4,3,1,2,1,4,2,3,1];
                    foreach ($bars as $bWidth): 
                    ?>
                        <div class="bar" style="width: <?= $bWidth * 1.5 ?>px;"></div>
                        <div style="width: 2px;"></div>
                    <?php endforeach; ?>
                </div>
                <div class="barcode-number">*<?= e($order['order_number']) ?>*</div>
            </div>

            <!-- Routing & Pincode -->
            <div class="routing-block">
                <div class="routing-cell" style="flex: 1.3;">
                    <div class="section-label">Destination PIN Code</div>
                    <div class="pincode-giant"><?= e($pincode) ?></div>
                </div>
                <div class="routing-cell">
                    <div class="section-label">City / Hub</div>
                    <div style="font-weight: 800; font-size: 13px;"><?= e($city) ?></div>
                    <div style="font-size: 10px; font-weight: 600;"><?= e($state) ?></div>
                </div>
                <div class="routing-cell">
                    <div class="section-label">Date</div>
                    <div style="font-weight: 700; font-size: 11px;"><?= date('d-m-Y', strtotime($order['created_at'])) ?></div>
                    <div style="font-size: 9px; color: #555;">WT: ~350g</div>
                </div>
            </div>

            <!-- Payment Status Box -->
            <div class="payment-banner <?= $isCod ? 'cod' : '' ?>">
                <?php if ($isCod): ?>
                    <span>CASH ON DELIVERY (COD)</span>
                    <span style="font-size: 16px;">COLLECT: ₹<?= number_format((float)$order['grand_total'], 0) ?></span>
                <?php else: ?>
                    <span style="color: #000;">PREPAID - DO NOT COLLECT CASH</span>
                    <span style="color: #000; font-size: 15px;">PAID ₹<?= number_format((float)$order['grand_total'], 0) ?></span>
                <?php endif; ?>
            </div>

            <!-- Deliver To Section -->
            <div class="deliver-to-section">
                <div class="section-label">DELIVER TO:</div>
                <div class="recipient-name"><?= e($addr['full_name'] ?? $order['customer_name']) ?></div>
                <div><?= e($addr['house_flat'] ?? '') ?>, <?= e($addr['area'] ?? '') ?></div>
                <?php if (!empty($addr['landmark'])): ?>
                    <div>Landmark: <?= e($addr['landmark']) ?></div>
                <?php endif; ?>
                <div><strong><?= e($city) ?>, <?= e($state) ?> - <?= e($pincode) ?></strong></div>
                <div class="recipient-phone">📞 Phone: +91 <?= e($addr['mobile'] ?? $order['customer_mobile']) ?></div>
            </div>

            <!-- Item Manifest -->
            <div class="items-section">
                <div class="section-label">PACKAGE CONTENTS (<?= count($order['items'] ?? []) ?> Items):</div>
                <table class="items-table">
                    <?php foreach ($order['items'] ?? [] as $item): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= e($item['product_name']) ?></td>
                            <td style="text-align: right;"><?= e($item['variant_details'] ?: $item['sku']) ?> &times; <?= (int)$item['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- Return Address (Bottom) -->
        <div class="return-section">
            <div class="section-label">IF UNDELIVERED, RETURN TO:</div>
            <div><strong>AFH Fulfillment Center (Discreet Logistics)</strong></div>
            <div>Plot 14-B, Industrial Area, Okhla Phase III, New Delhi, DL - 110020</div>
            <div>Support: care@aforehealth.com | Helpline: +91 98765 43210</div>
        </div>
    </div>

</body>
</html>
