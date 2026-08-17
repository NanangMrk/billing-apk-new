<?php
// app/Controllers/InventoryController.php - Inventory Catalog, Goods In, Goods Out, and Suppliers

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class InventoryController {

    public function index(): void {
        AuthMiddleware::handle('inventory.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_item') {
            AuthMiddleware::handle('inventory.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('inventory');
            }

            $name = trim($_POST['name'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 1);
            $brand = trim($_POST['brand'] ?? '');
            $unit = trim($_POST['unit'] ?? 'unit');
            $price = (int)str_replace(['.', ',', ' '], '', $_POST['purchase_price'] ?? '0');
            $minStock = (int)($_POST['min_stock'] ?? 5);
            $initialStock = (int)($_POST['current_stock'] ?? 0);

            if (!empty($name)) {
                $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4)) . '-' . rand(100, 999);
                $stmt = $pdo->prepare("
                    INSERT INTO inventory_items (sku, name, category_id, brand, unit, purchase_price, min_stock, current_stock, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$sku, $name, $categoryId, $brand, $unit, $price, $minStock, $initialStock]);
                Helper::setFlash('success', "Item $name ($sku) berhasil ditambahkan.");
            }
            Helper::redirect('inventory');
        }

        $items = $pdo->query("
            SELECT i.*, c.name as category_name, w.name as warehouse_name 
            FROM inventory_items i 
            LEFT JOIN inventory_categories c ON i.category_id = c.id 
            LEFT JOIN warehouses w ON i.warehouse_id = w.id 
            ORDER BY i.id DESC
        ")->fetchAll();

        $categories = $pdo->query("SELECT * FROM inventory_categories")->fetchAll();

        $pageTitle = 'Katalog Stok Barang';

        ob_start();
        require __DIR__ . '/../Views/inventory/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function goodsIn(): void {
        AuthMiddleware::handle('inventory.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('goods_in');
            }

            $itemId = (int)($_POST['item_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            $unitPrice = (int)str_replace(['.', ',', ' '], '', $_POST['unit_price'] ?? '0');
            $referenceNo = trim($_POST['reference_no'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($itemId > 0 && $quantity > 0) {
                $pdo->beginTransaction();

                $count = (int)$pdo->query("SELECT COUNT(*) FROM inventory_transactions")->fetchColumn() + 1;
                $trxNo = 'GIN-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;
                $totalAmount = $quantity * $unitPrice;

                $stmt = $pdo->prepare("
                    INSERT INTO inventory_transactions (transaction_no, transaction_date, type, item_id, quantity, unit_price, total_amount, reference_no, notes, created_by)
                    VALUES (?, CURRENT_DATE, 'in', ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$trxNo, $itemId, $quantity, $unitPrice, $totalAmount, $referenceNo, $notes, $userId]);

                // Update item stock
                $stmtUpd = $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?");
                $stmtUpd->execute([$quantity, $itemId]);

                $pdo->commit();

                Helper::logActivity('INVENTORY', 'GOODS_IN', $trxNo, null, "Barang Masuk: +$quantity unit (Item ID $itemId)");
                Helper::setFlash('success', "Penerimaan barang masuk $trxNo berhasil dicatat.");
            }
            Helper::redirect('inventory');
        }

        $items = $pdo->query("SELECT * FROM inventory_items WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $history = $pdo->query("
            SELECT t.*, i.name as item_name, i.unit, u.name as creator_name 
            FROM inventory_transactions t 
            JOIN inventory_items i ON t.item_id = i.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE t.type = 'in' 
            ORDER BY t.id DESC LIMIT 20
        ")->fetchAll();

        $pageTitle = 'Penerimaan Barang Masuk (Goods In)';

        ob_start();
        require __DIR__ . '/../Views/inventory/goods_in.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function goodsOut(): void {
        AuthMiddleware::handle('inventory.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('goods_out');
            }

            $itemId = (int)($_POST['item_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            $destType = trim($_POST['destination_type'] ?? 'customer');
            $notes = trim($_POST['notes'] ?? '');

            if ($itemId > 0 && $quantity > 0) {
                $item = $pdo->query("SELECT * FROM inventory_items WHERE id = {$itemId}")->fetch();
                if ($item && $item['current_stock'] >= $quantity) {
                    $pdo->beginTransaction();

                    $count = (int)$pdo->query("SELECT COUNT(*) FROM inventory_transactions")->fetchColumn() + 1;
                    $trxNo = 'GOUT-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                    $userId = AuthService::user()['id'] ?? null;

                    $stmt = $pdo->prepare("
                        INSERT INTO inventory_transactions (transaction_no, transaction_date, type, item_id, quantity, unit_price, destination_type, notes, created_by)
                        VALUES (?, CURRENT_DATE, 'out', ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$trxNo, $itemId, $quantity, $item['purchase_price'], $destType, $notes, $userId]);

                    // Reduce stock
                    $stmtUpd = $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?");
                    $stmtUpd->execute([$quantity, $itemId]);

                    $pdo->commit();

                    Helper::logActivity('INVENTORY', 'GOODS_OUT', $trxNo, null, "Barang Keluar: -$quantity unit {$item['name']}");
                    Helper::setFlash('success', "Pengeluaran barang $trxNo berhasil dicatat.");
                } else {
                    Helper::setFlash('error', 'Stok tidak mencukupi untuk dikeluarkan.');
                }
            }
            Helper::redirect('inventory');
        }

        $items = $pdo->query("SELECT * FROM inventory_items WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $history = $pdo->query("
            SELECT t.*, i.name as item_name, i.unit, u.name as creator_name 
            FROM inventory_transactions t 
            JOIN inventory_items i ON t.item_id = i.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE t.type = 'out' 
            ORDER BY t.id DESC LIMIT 20
        ")->fetchAll();

        $pageTitle = 'Pengeluaran Barang (Goods Out)';

        ob_start();
        require __DIR__ . '/../Views/inventory/goods_out.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function suppliers(): void {
        AuthMiddleware::handle('inventory.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_supplier') {
            AuthMiddleware::handle('inventory.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('suppliers');
            }

            $name = trim($_POST['name'] ?? '');
            $contact = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, address, status) VALUES (?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $contact, $phone, $address]);
                Helper::setFlash('success', "Supplier $name berhasil didaftarkan.");
            }
            Helper::redirect('suppliers');
        }

        $suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();

        $pageTitle = 'Data Supplier & Vendor Logistik';

        ob_start();
        require __DIR__ . '/../Views/inventory/suppliers.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
