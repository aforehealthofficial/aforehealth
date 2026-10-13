<?php
namespace Services;

class InvoiceService {
    public function renderInvoiceHtml(array $order): string {
        $invNumber = str_replace('AFH-', 'AFH-INV-', $order['order_number']);
        $companyName = get_setting('store_name', 'AFOREHEALTH Wellness Pvt. Ltd.');
        $companyAddress = nl2br(e(get_setting('store_address')));
        $gstin = get_setting('store_gstin', '27AAACA1234A1Z5');
        $phone = get_setting('store_phone', '+91 98765 43210');
        $email = get_setting('store_email', 'care@aforehealth.com');

        $itemsHtml = '';
        foreach ($order['items'] as $idx => $it) {
            $num = $idx + 1;
            $itemsHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$num}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                        <strong>" . e($it['product_name']) . "</strong><br>
                        <small style='color: #666;'>" . e($it['variant_details']) . " | SKU: " . e($it['sku']) . "</small>
                    </td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>" . (int)$it['quantity'] . "</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₹" . number_format($it['price'], 2) . "</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₹" . number_format($it['total'], 2) . "</td>
                </tr>
            ";
        }

        $addr = $order['shipping_address'];
        $addrStr = e($addr['full_name']) . "<br>" .
                   e($addr['house_flat']) . ", " . e($addr['area']) . "<br>" .
                   (!empty($addr['landmark']) ? "Landmark: " . e($addr['landmark']) . "<br>" : "") .
                   e($addr['city']) . ", " . e($addr['state']) . " - <strong>" . e($addr['pincode']) . "</strong><br>" .
                   "Phone: " . e($addr['mobile']);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Tax Invoice - {$invNumber}</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #252126; margin: 0; padding: 30px; background: #fff; }
                .invoice-box { max-width: 850px; margin: auto; padding: 30px; border: 1px solid #f0e6ed; border-radius: 8px; }
                .header-table { width: 100%; margin-bottom: 25px; }
                .brand-title { color: #8B5E83; font-size: 26px; font-weight: 800; letter-spacing: 1px; margin: 0; }
                .meta-table { width: 100%; margin-bottom: 25px; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                .items-table th { background: #FFF9FA; color: #8B5E83; padding: 10px; text-align: left; font-size: 13px; border-bottom: 2px solid #8B5E83; }
                .total-box { float: right; width: 320px; }
                .total-table { width: 100%; border-collapse: collapse; }
                .total-table td { padding: 6px 10px; }
                .grand-total { font-size: 17px; font-weight: bold; color: #8B5E83; border-top: 2px solid #eee; }
                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; padding-top: 15px; clear: both; }
                @media print {
                    body { padding: 0; }
                    .invoice-box { border: none; padding: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width: 850px; margin: 0 auto 15px; text-align: right;'>
                <button onclick='window.print()' style='background: #8B5E83; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;'>🖨️ Print / Save as PDF</button>
            </div>
            <div class='invoice-box'>
                <table class='header-table'>
                    <tr>
                        <td style='vertical-align: top;'>
                            <h1 class='brand-title'>AFOREHEALTH</h1>
                            <small style='color: #777; font-weight: 600;'>Everyday Comfort. Period Confidence.</small>
                            <div style='font-size: 12px; line-height: 1.5; margin-top: 10px;'>
                                {$companyAddress}<br>
                                <strong>GSTIN:</strong> {$gstin}<br>
                                <strong>Support:</strong> {$phone} | {$email}
                            </div>
                        </td>
                        <td style='vertical-align: top; text-align: right;'>
                            <h2 style='margin: 0; color: #252126; font-size: 20px;'>TAX INVOICE</h2>
                            <p style='margin: 5px 0 0; font-size: 13px;'>
                                <strong>Invoice No:</strong> {$invNumber}<br>
                                <strong>Order Ref:</strong> " . e($order['order_number']) . "<br>
                                <strong>Invoice Date:</strong> " . date('d M Y, h:i A', strtotime($order['created_at'])) . "<br>
                                <strong>Payment:</strong> " . strtoupper($order['payment_method']) . " (" . strtoupper($order['payment_status']) . ")
                            </p>
                        </td>
                    </tr>
                </table>

                <table class='meta-table'>
                    <tr>
                        <td style='width: 50%; vertical-align: top;'>
                            <strong style='color: #8B5E83; font-size: 13px;'>BILLED &amp; SHIPPED TO:</strong><br>
                            <div style='font-size: 13px; line-height: 1.6; margin-top: 5px;'>
                                {$addrStr}
                            </div>
                        </td>
                        <td style='width: 50%; vertical-align: top; text-align: right;'>
                            <strong style='color: #8B5E83; font-size: 13px;'>DISPATCH DETAILS:</strong><br>
                            <div style='font-size: 13px; line-height: 1.6; margin-top: 5px;'>
                                <strong>Courier:</strong> " . e($order['courier_name'] ?: 'Delhivery Express') . "<br>
                                <strong>Tracking AWB:</strong> " . e($order['tracking_number'] ?: 'Standard Dispatch') . "<br>
                                <strong>Packaging:</strong> 100% Sealed &amp; Discreet
                            </div>
                        </td>
                    </tr>
                </table>

                <table class='items-table'>
                    <thead>
                        <tr>
                            <th style='width: 40px; text-align: center;'>#</th>
                            <th>Description of Goods</th>
                            <th style='width: 70px; text-align: center;'>Qty</th>
                            <th style='width: 100px; text-align: right;'>Rate</th>
                            <th style='width: 110px; text-align: right;'>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHtml}
                    </tbody>
                </table>

                <div class='total-box'>
                    <table class='total-table'>
                        <tr>
                            <td>Subtotal:</td>
                            <td style='text-align: right;'>₹" . number_format($order['subtotal'], 2) . "</td>
                        </tr>
                        " . ($order['discount'] > 0 ? "
                        <tr>
                            <td style='color: #4E8B68;'>Coupon Discount:</td>
                            <td style='text-align: right; color: #4E8B68;'>-₹" . number_format($order['discount'], 2) . "</td>
                        </tr>
                        " : "") . "
                        <tr>
                            <td>Shipping Fee:</td>
                            <td style='text-align: right;'>" . ($order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'FREE') . "</td>
                        </tr>
                        " . ($order['cod_fee'] > 0 ? "
                        <tr>
                            <td>COD Handling:</td>
                            <td style='text-align: right;'>₹" . number_format($order['cod_fee'], 2) . "</td>
                        </tr>
                        " : "") . "
                        <tr class='grand-total'>
                            <td>Total (INR):</td>
                            <td style='text-align: right;'>₹" . number_format($order['grand_total'], 2) . "</td>
                        </tr>
                    </table>
                </div>

                <div class='footer'>
                    <p>This is a computer generated invoice and does not require a physical signature. Thank you for choosing AFOREHEALTH!</p>
                    <p>For support or returns regarding hygiene-eligible items, visit <strong>aforehealth.com</strong> or WhatsApp +91 98765 43210</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
