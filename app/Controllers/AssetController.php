<?php
// app/Controllers/AssetController.php - Company Fixed Assets and CPE Equipment Tracking

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AssetController {

    public function index(): void {
        AuthMiddleware::handle('assets.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_asset') {
            AuthMiddleware::handle('assets.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('assets');
            }

            $name = trim($_POST['name'] ?? '');
            $sn = trim($_POST['serial_number'] ?? '');
            $mac = trim($_POST['mac_address'] ?? '');
            $purchaseDate = trim($_POST['purchase_date'] ?? date('Y-m-d'));
            $price = (int)str_replace(['.', ',', ' '], '', $_POST['purchase_price'] ?? '0');
            $location = trim($_POST['location'] ?? '');
            $pic = trim($_POST['pic_name'] ?? '');
            $status = trim($_POST['status'] ?? 'available');

            if (!empty($name)) {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn() + 1;
                $assetNo = 'AST-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);

                $stmt = $pdo->prepare("
                    INSERT INTO assets (asset_no, name, serial_number, mac_address, purchase_date, purchase_price, current_value, location, pic_name, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$assetNo, $name, $sn, $mac, $purchaseDate, $price, $price, $location, $pic, $status]);

                Helper::logActivity('ASSET', 'CREATE_ASSET', $assetNo, null, "Added asset $name ($assetNo)");
                Helper::setFlash('success', "Aset $name ($assetNo) berhasil didaftarkan.");
            }
            Helper::redirect('assets');
        }

        $assets = $pdo->query("
            SELECT a.*, c.name as customer_name 
            FROM assets a 
            LEFT JOIN customers c ON a.customer_id = c.id 
            ORDER BY a.id DESC
        ")->fetchAll();

        $pageTitle = 'Manajemen Aset & Alat Kerja Perusahaan';

        ob_start();
        require __DIR__ . '/../Views/assets/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
