<?php
namespace Controllers\Admin;

use Models\Pincode;
use PDO;

class AdminPincodeController {
    public function index(): void {
        $q = trim($_GET['q'] ?? '');
        $state = trim($_GET['state'] ?? '');
        $cod = isset($_GET['cod']) && $_GET['cod'] !== '' ? $_GET['cod'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));

        $pincodeModel = new Pincode();
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;
        $results = $pincodeModel->searchPincodes($q, $state ?: null, $cod, $page, $perPage);

        // Fetch distinct states for filter dropdown
        $pdo = getDbConnection();
        $states = $pdo->query("SELECT DISTINCT state FROM pincodes ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);

        view('admin.shipping.pincodes', [
            'pageTitle' => 'Pincode Serviceability Management - AFOREHEALTH Admin',
            'pincodes' => $results['data'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages'],
            'q' => $q,
            'stateFilter' => $state,
            'codFilter' => $cod,
            'states' => $states
        ]);
    }

    public function store(): void {
        $pincode = trim($_POST['pincode'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $deliveryDays = max(1, (int)($_POST['delivery_days'] ?? 4));
        $codAvailable = isset($_POST['cod_available']) ? 1 : 0;
        $prepaidAvailable = isset($_POST['prepaid_available']) ? 1 : 0;
        $shippingCharge = (float)($_POST['shipping_charge'] ?? 0.00);

        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode) || empty($city) || empty($state)) {
            set_flash('danger', 'Please enter a valid 6-digit Indian PIN code, city, and state.');
            redirect('/admin/shipping/pincodes');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO pincodes (pincode, city, state, delivery_days, cod_available, prepaid_available, shipping_charge, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE 
                city = VALUES(city), state = VALUES(state), delivery_days = VALUES(delivery_days),
                cod_available = VALUES(cod_available), prepaid_available = VALUES(prepaid_available),
                shipping_charge = VALUES(shipping_charge), status = 'active'
        ");
        $stmt->execute([$pincode, $city, $state, $deliveryDays, $codAvailable, $prepaidAvailable, $shippingCharge]);

        set_flash('success', "PIN code {$pincode} ({$city}, {$state}) saved successfully.");
        redirect('/admin/shipping/pincodes');
    }

    public function toggle(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE pincodes SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Pincode status updated.');
        redirect('/admin/shipping/pincodes');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM pincodes WHERE id = ?");
        $stmt->execute([$id]);

        set_flash('success', 'Pincode removed.');
        redirect('/admin/shipping/pincodes');
    }

    public function exportCsv(): void {
        $pdo = getDbConnection();
        $pincodes = $pdo->query("SELECT pincode, city, state, delivery_days, cod_available, prepaid_available, shipping_charge, status FROM pincodes ORDER BY pincode ASC")->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="aforehealth_serviceable_pincodes_' . date('Y_m_d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Pincode', 'City', 'State', 'Delivery Days', 'COD Available (1/0)', 'Prepaid Available (1/0)', 'Shipping Charge (INR)', 'Status']);
        foreach ($pincodes as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public function importCsv(): void {
        if (empty($_FILES['csv_file']['tmp_name'])) {
            set_flash('danger', 'Please choose a CSV file to upload.');
            redirect('/admin/shipping/pincodes');
        }

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$file) {
            set_flash('danger', 'Failed to open uploaded file.');
            redirect('/admin/shipping/pincodes');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO pincodes (pincode, city, state, delivery_days, cod_available, prepaid_available, shipping_charge, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE 
                city = VALUES(city), state = VALUES(state), delivery_days = VALUES(delivery_days),
                cod_available = VALUES(cod_available), prepaid_available = VALUES(prepaid_available),
                shipping_charge = VALUES(shipping_charge)
        ");

        $header = fgetcsv($file);
        $imported = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 3) {
                $pin = trim($row[0]);
                if (preg_match('/^[1-9][0-9]{5}$/', $pin)) {
                    $city = trim($row[1]);
                    $state = trim($row[2]);
                    $days = isset($row[3]) ? max(1, (int)$row[3]) : 4;
                    $cod = isset($row[4]) ? (int)$row[4] : 1;
                    $prepaid = isset($row[5]) ? (int)$row[5] : 1;
                    $charge = isset($row[6]) ? (float)$row[6] : 0.00;

                    $stmt->execute([$pin, $city, $state, $days, $cod, $prepaid, $charge]);
                    $imported++;
                }
            }
        }
        fclose($file);

        set_flash('success', "Imported {$imported} serviceable PIN codes successfully.");
        redirect('/admin/shipping/pincodes');
    }
}
