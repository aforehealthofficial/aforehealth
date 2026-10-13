<?php
namespace Controllers;

use Models\Product;

class CompareController {
    public function index(): void {
        if (!isset($_SESSION['compare_ids'])) {
            $_SESSION['compare_ids'] = [];
        }

        $ids = array_slice($_SESSION['compare_ids'], 0, 4);
        $products = [];

        if (!empty($ids)) {
            $prodModel = new Product();
            $pdo = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.id IN ({$placeholders}) AND p.status = 'published'
            ");
            $stmt->execute($ids);
            $rawProds = $stmt->fetchAll();

            foreach ($rawProds as $p) {
                $p['sizes'] = $prodModel->getDistinctSizes($p['id']);
                $p['colors'] = $prodModel->getDistinctColors($p['id']);
                $products[] = $p;
            }
        }

        view('storefront.compare', [
            'pageTitle' => 'Compare Intimate & Period Care Essentials - AFOREHEALTH',
            'products' => $products
        ]);
    }

    public function add(): void {
        $id = (int)($_POST['product_id'] ?? 0);
        if (!$id) {
            json_response(['success' => false, 'message' => 'Invalid product.'], 400);
        }

        if (!isset($_SESSION['compare_ids'])) {
            $_SESSION['compare_ids'] = [];
        }

        if (count($_SESSION['compare_ids']) >= 4 && !in_array($id, $_SESSION['compare_ids'])) {
            json_response([
                'success' => false,
                'message' => 'You can compare a maximum of 4 products at once.',
                'count' => count($_SESSION['compare_ids'])
            ], 422);
        }

        if (!in_array($id, $_SESSION['compare_ids'])) {
            $_SESSION['compare_ids'][] = $id;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            json_response([
                'success' => true,
                'message' => 'Product added to comparison.',
                'count' => count($_SESSION['compare_ids'])
            ]);
        }

        set_flash('success', 'Product added to comparison table.');
        redirect('/compare');
    }

    public function remove(): void {
        $id = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['compare_ids'])) {
            $_SESSION['compare_ids'] = array_values(array_diff($_SESSION['compare_ids'], [$id]));
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            json_response([
                'success' => true,
                'message' => 'Product removed from comparison.',
                'count' => count($_SESSION['compare_ids'] ?? [])
            ]);
        }

        redirect('/compare');
    }

    public function clear(): void {
        $_SESSION['compare_ids'] = [];
        set_flash('info', 'Comparison cleared.');
        redirect('/compare');
    }
}
