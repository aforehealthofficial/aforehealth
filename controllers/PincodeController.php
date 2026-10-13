<?php
namespace Controllers;

use Models\Pincode;

class PincodeController {
    public function check(): void {
        $pincode = trim($_POST['pincode'] ?? $_GET['pincode'] ?? '');
        
        $pincodeModel = new Pincode();
        $result = $pincodeModel->checkServiceability($pincode);

        json_response($result, $result['serviceable'] ? 200 : 422);
    }
}
