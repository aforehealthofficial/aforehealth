<?php
namespace Controllers;

use Models\Bundle;
use Models\Cart;
use Exception;

class BundleController {
    public function index(): void {
        $bundleModel = new Bundle();
        $bundles = $bundleModel->getActiveBundles();
        $activeBundle = $bundles[0] ?? null;

        view('storefront.bundle-builder', [
            'pageTitle' => 'Build Your Period Kit - Custom Bundles | AFOREHEALTH',
            'bundles' => $bundles,
            'activeBundle' => $activeBundle
        ]);
    }

    public function show(string $slug): void {
        $bundleModel = new Bundle();
        $bundle = $bundleModel->findBySlug($slug);

        if (!$bundle) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Bundle Not Found - AFOREHEALTH']);
            return;
        }

        view('storefront.bundle-builder', [
            'pageTitle' => e($bundle['name']) . ' - Custom Bundle Builder | AFOREHEALTH',
            'bundles' => $bundleModel->getActiveBundles(),
            'activeBundle' => $bundle
        ]);
    }

    public function calculate(): void {
        $bundleId = (int)($_POST['bundle_id'] ?? 0);
        $variantIds = $_POST['variant_ids'] ?? [];

        if (!$bundleId || !is_array($variantIds)) {
            json_response(['success' => false, 'message' => 'Invalid bundle request.'], 400);
        }

        try {
            $bundleModel = new Bundle();
            $result = $bundleModel->calculateBundle($bundleId, $variantIds);
            json_response(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function addToCart(): void {
        $bundleId = (int)($_POST['bundle_id'] ?? 0);
        $variantIds = $_POST['variant_ids'] ?? [];

        if (!$bundleId || !is_array($variantIds)) {
            json_response(['success' => false, 'message' => 'Invalid bundle submission.'], 400);
        }

        try {
            $bundleModel = new Bundle();
            $calc = $bundleModel->calculateBundle($bundleId, $variantIds);

            $cartModel = new Cart();
            foreach ($calc['items'] as $it) {
                $cartModel->addItem((int)$it['product_id'], (int)$it['variant_id'], 1);
            }

            $_SESSION['applied_bundle'] = [
                'bundle_id' => $bundleId,
                'bundle_name' => $calc['bundle']['name'],
                'discount_amount' => (float)$calc['discount_amount'],
                'variant_ids' => array_map('intval', $variantIds)
            ];

            // Set session flash / flag for bundle savings
            set_flash('success', "🎉 '{$calc['bundle']['name']}' added to your bag! You saved ₹" . number_format($calc['total_savings'], 0) . ".");

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                json_response([
                    'success' => true,
                    'message' => "Bundle added to bag with ₹" . number_format($calc['total_savings'], 0) . " savings!",
                    'cart_count' => get_cart_count()
                ]);
            }

            redirect('/cart');
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
