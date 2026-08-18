<?php
// app/Controllers/InventoryController.php - Inventory Catalog, Goods In, Goods Out, and Suppliers

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class InventoryController {

    public function index(): void {
        AuthMiddleware::handle('inventory.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            // 1. Create New Item
            if ($action === 'save_item') {
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

                    Helper::logActivity('INVENTORY', 'CREATE_ITEM', $sku, null, "Created item $sku: $name");
                    Helper::setFlash('success', "Item $name ($sku) berhasil ditambahkan.");
                }
                Helper::redirect('inventory');
            }

            // 2. Update Item
            if ($action === 'update_item') {
                AuthMiddleware::handle('inventory.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('inventory');
                }

                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $categoryId = (int)($_POST['category_id'] ?? 1);
                $brand = trim($_POST['brand'] ?? '');
                $unit = trim($_POST['unit'] ?? 'unit');
                $price = (int)str_replace(['.', ',', ' '], '', $_POST['purchase_price'] ?? '0');
                $minStock = (int)($_POST['min_stock'] ?? 5);
                $currentStock = (int)($_POST['current_stock'] ?? 0);
                $status = trim($_POST['status'] ?? 'active');

                if ($id > 0 && !empty($name)) {
                    $stmt = $pdo->prepare("
                        UPDATE inventory_items 
                        SET name = ?, category_id = ?, brand = ?, unit = ?, purchase_price = ?, min_stock = ?, current_stock = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $categoryId, $brand, $unit, $price, $minStock, $currentStock, $status, $id]);

                    Helper::logActivity('INVENTORY', 'UPDATE_ITEM', (string)$id, null, "Updated item #$id: $name");
                    Helper::setFlash('success', "Data barang $name berhasil diperbarui.");
                }
                Helper::redirect('inventory');
            }

            // 3. Delete Item
            if ($action === 'delete_item') {
                AuthMiddleware::handle('inventory.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('inventory');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $checkTrx = $pdo->prepare("SELECT COUNT(*) FROM inventory_transactions WHERE item_id = ?");
                    $checkTrx->execute([$id]);
                    $trxCount = (int)$checkTrx->fetchColumn();

                    if ($trxCount > 0) {
                        Helper::setFlash('error', "Gagal menghapus: Barang ini memiliki {$trxCount} riwayat mutasi stok. Anda dapat mengubah statusnya menjadi nonaktif.");
                    } else {
                        $itemStmt = $pdo->prepare("SELECT name FROM inventory_items WHERE id = ?");
                        $itemStmt->execute([$id]);
                        $itemName = $itemStmt->fetchColumn() ?: "ID #$id";

                        $delStmt = $pdo->prepare("DELETE FROM inventory_items WHERE id = ?");
                        $delStmt->execute([$id]);

                        Helper::logActivity('INVENTORY', 'DELETE_ITEM', (string)$id, null, "Deleted item #$id: $itemName");
                        Helper::setFlash('success', "Barang {$itemName} berhasil dihapus.");
                    }
                }
                Helper::redirect('inventory');
            }
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

            $action = $_POST['action'] ?? 'save_goods_in';

            // 1. Create Goods In
            if ($action === 'save_goods_in') {
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
                Helper::redirect('goods_in');
            }

            // 2. Update Goods In
            if ($action === 'update_goods_in') {
                $id = (int)($_POST['id'] ?? 0);
                $itemId = (int)($_POST['item_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                $unitPrice = (int)str_replace(['.', ',', ' '], '', $_POST['unit_price'] ?? '0');
                $referenceNo = trim($_POST['reference_no'] ?? '');
                $notes = trim($_POST['notes'] ?? '');

                if ($id > 0 && $itemId > 0 && $quantity > 0) {
                    $oldTrx = $pdo->query("SELECT * FROM inventory_transactions WHERE id = {$id} AND type = 'in'")->fetch();
                    if ($oldTrx) {
                        $pdo->beginTransaction();

                        // Revert previous stock addition
                        $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?")
                            ->execute([(int)$oldTrx['quantity'], (int)$oldTrx['item_id']]);

                        // Apply new stock addition
                        $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?")
                            ->execute([$quantity, $itemId]);

                        $totalAmount = $quantity * $unitPrice;
                        $stmt = $pdo->prepare("
                            UPDATE inventory_transactions 
                            SET item_id = ?, quantity = ?, unit_price = ?, total_amount = ?, reference_no = ?, notes = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$itemId, $quantity, $unitPrice, $totalAmount, $referenceNo, $notes, $id]);

                        $pdo->commit();

                        Helper::logActivity('INVENTORY', 'UPDATE_GOODS_IN', (string)$id, null, "Updated Goods In #$id ({$oldTrx['transaction_no']})");
                        Helper::setFlash('success', "Transaksi barang masuk {$oldTrx['transaction_no']} berhasil diperbarui.");
                    }
                }
                Helper::redirect('goods_in');
            }

            // 3. Delete Goods In
            if ($action === 'delete_goods_in') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $trx = $pdo->query("SELECT * FROM inventory_transactions WHERE id = {$id} AND type = 'in'")->fetch();
                    if ($trx) {
                        $pdo->beginTransaction();

                        // Revert stock
                        $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?")
                            ->execute([(int)$trx['quantity'], (int)$trx['item_id']]);

                        $pdo->prepare("DELETE FROM inventory_transactions WHERE id = ?")->execute([$id]);

                        $pdo->commit();

                        Helper::logActivity('INVENTORY', 'DELETE_GOODS_IN', (string)$id, null, "Deleted Goods In {$trx['transaction_no']}");
                        Helper::setFlash('success', "Transaksi barang masuk {$trx['transaction_no']} berhasil dihapus dan stok telah disesuaikan.");
                    }
                }
                Helper::redirect('goods_in');
            }
        }

        $items = $pdo->query("SELECT * FROM inventory_items WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        
        // Filter logic
        $filterItem = $_GET['item_id'] ?? '';
        $filterMonth = $_GET['month'] ?? date('Y-m');

        $whereClause = "t.type = 'in'";
        $params = [];

        if ($filterItem !== '') {
            $whereClause .= " AND t.item_id = ?";
            $params[] = $filterItem;
        }

        if ($filterMonth !== '') {
            $whereClause .= " AND strftime('%Y-%m', t.transaction_date) = ?";
            $params[] = $filterMonth;
        }

        $stmt = $pdo->prepare("
            SELECT t.*, i.name as item_name, i.unit, u.name as creator_name 
            FROM inventory_transactions t 
            JOIN inventory_items i ON t.item_id = i.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE $whereClause 
            ORDER BY t.id DESC LIMIT 200
        ");
        $stmt->execute($params);
        $history = $stmt->fetchAll();

        $pageTitle = 'Penerimaan Barang Masuk (Goods In)';

        ob_start();
        require __DIR__ . '/../Views/inventory/goods_in.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function goodsOut(): void {
        AuthMiddleware::handle('inventory.manage');
        $pdo = getDbConnection();

        // Ensure columns exist in database
        try { $pdo->exec("ALTER TABLE inventory_transactions ADD COLUMN recipient_name TEXT"); } catch (\PDOException $e) {}
        try { $pdo->exec("ALTER TABLE inventory_transactions ADD COLUMN photo TEXT"); } catch (\PDOException $e) {}

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('goods_out');
            }

            $action = $_POST['action'] ?? 'save_goods_out';

            // 1. Create Goods Out
            if ($action === 'save_goods_out') {
                $itemId = (int)($_POST['item_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                $destType = trim($_POST['destination_type'] ?? 'customer');
                $notes = trim($_POST['notes'] ?? '');
                $recipientName = trim($_POST['recipient_name'] ?? '');

                // Photo upload
                $photoPath = null;
                if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $ftype = mime_content_type($_FILES['photo']['tmp_name']);
                    if (in_array($ftype, $allowedTypes) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                        $filename = 'gout_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                        $uploadDir = __DIR__ . '/../../public/uploads/inventory/';
                        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                            $photoPath = 'uploads/inventory/' . $filename;
                        }
                    }
                }

                if ($itemId > 0 && $quantity > 0) {
                    $item = $pdo->query("SELECT * FROM inventory_items WHERE id = {$itemId}")->fetch();
                    if ($item && $item['current_stock'] >= $quantity) {
                        $pdo->beginTransaction();

                        $count = (int)$pdo->query("SELECT COUNT(*) FROM inventory_transactions")->fetchColumn() + 1;
                        $trxNo = 'GOUT-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                        $userId = AuthService::user()['id'] ?? null;

                        $stmt = $pdo->prepare("
                            INSERT INTO inventory_transactions (transaction_no, transaction_date, type, item_id, quantity, unit_price, destination_type, notes, recipient_name, photo, created_by)
                            VALUES (?, CURRENT_DATE, 'out', ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$trxNo, $itemId, $quantity, $item['purchase_price'], $destType, $notes, $recipientName, $photoPath, $userId]);

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
                Helper::redirect('goods_out');
            }

            // 2. Update Goods Out
            if ($action === 'update_goods_out') {
                $id = (int)($_POST['id'] ?? 0);
                $itemId = (int)($_POST['item_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                $destType = trim($_POST['destination_type'] ?? 'customer');
                $notes = trim($_POST['notes'] ?? '');
                $recipientName = trim($_POST['recipient_name'] ?? '');

                if ($id > 0 && $itemId > 0 && $quantity > 0) {
                    $oldTrx = $pdo->query("SELECT * FROM inventory_transactions WHERE id = {$id} AND type = 'out'")->fetch();
                    if ($oldTrx) {
                        $pdo->beginTransaction();

                        // Revert old stock deduction
                        $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?")
                            ->execute([(int)$oldTrx['quantity'], (int)$oldTrx['item_id']]);

                        // Check new available stock
                        $item = $pdo->query("SELECT * FROM inventory_items WHERE id = {$itemId}")->fetch();
                        if ($item && $item['current_stock'] >= $quantity) {
                            // Deduct new quantity
                            $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?")
                                ->execute([$quantity, $itemId]);

                            // Handle photo update
                            $photoPath = $oldTrx['photo'] ?? null;
                            if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                                $ftype = mime_content_type($_FILES['photo']['tmp_name']);
                                if (in_array($ftype, $allowedTypes) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                                    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                                    $filename = 'gout_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                                    $uploadDir = __DIR__ . '/../../public/uploads/inventory/';
                                    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                                        if (!empty($oldTrx['photo'])) {
                                            $oldFile = __DIR__ . '/../../public/' . $oldTrx['photo'];
                                            if (file_exists($oldFile)) { @unlink($oldFile); }
                                        }
                                        $photoPath = 'uploads/inventory/' . $filename;
                                    }
                                }
                            }

                            $stmt = $pdo->prepare("
                                UPDATE inventory_transactions 
                                SET item_id = ?, quantity = ?, destination_type = ?, notes = ?, recipient_name = ?, photo = ?
                                WHERE id = ?
                            ");
                            $stmt->execute([$itemId, $quantity, $destType, $notes, $recipientName, $photoPath, $id]);

                            $pdo->commit();

                            Helper::logActivity('INVENTORY', 'UPDATE_GOODS_OUT', (string)$id, null, "Updated Goods Out #$id ({$oldTrx['transaction_no']})");
                            Helper::setFlash('success', "Transaksi barang keluar {$oldTrx['transaction_no']} berhasil diperbarui.");
                        } else {
                            $pdo->rollBack();
                            Helper::setFlash('error', 'Stok tidak mencukupi untuk memperbarui transaksi.');
                        }
                    }
                }
                Helper::redirect('goods_out');
            }

            // 3. Delete Goods Out
            if ($action === 'delete_goods_out') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $trx = $pdo->query("SELECT * FROM inventory_transactions WHERE id = {$id} AND type = 'out'")->fetch();
                    if ($trx) {
                        $pdo->beginTransaction();

                        // Restore deducted stock
                        $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?")
                            ->execute([(int)$trx['quantity'], (int)$trx['item_id']]);

                        // Delete physical photo file if present
                        if (!empty($trx['photo'])) {
                            $oldFile = __DIR__ . '/../../public/' . $trx['photo'];
                            if (file_exists($oldFile)) { @unlink($oldFile); }
                        }

                        $pdo->prepare("DELETE FROM inventory_transactions WHERE id = ?")->execute([$id]);

                        $pdo->commit();

                        Helper::logActivity('INVENTORY', 'DELETE_GOODS_OUT', (string)$id, null, "Deleted Goods Out {$trx['transaction_no']}");
                        Helper::setFlash('success', "Transaksi barang keluar {$trx['transaction_no']} berhasil dihapus dan stok telah dikembalikan.");
                    }
                }
                Helper::redirect('goods_out');
            }
        }

        $items = $pdo->query("SELECT * FROM inventory_items WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        
        // Filter logic
        $filterItem = $_GET['item_id'] ?? '';
        $filterMonth = $_GET['month'] ?? date('Y-m');

        $whereClause = "t.type = 'out'";
        $params = [];

        if ($filterItem !== '') {
            $whereClause .= " AND t.item_id = ?";
            $params[] = $filterItem;
        }

        if ($filterMonth !== '') {
            $whereClause .= " AND strftime('%Y-%m', t.transaction_date) = ?";
            $params[] = $filterMonth;
        }

        $stmt = $pdo->prepare("
            SELECT t.*, i.name as item_name, i.unit, u.name as creator_name 
            FROM inventory_transactions t 
            JOIN inventory_items i ON t.item_id = i.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE $whereClause 
            ORDER BY t.id DESC LIMIT 200
        ");
        $stmt->execute($params);
        $history = $stmt->fetchAll();

        $pageTitle = 'Pengeluaran Barang (Goods Out)';

        ob_start();
        require __DIR__ . '/../Views/inventory/goods_out.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function suppliers(): void {
        AuthMiddleware::handle('inventory.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            AuthMiddleware::handle('inventory.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('suppliers');
            }

            $action = $_POST['action'];

            // 1. Create Supplier
            if ($action === 'save_supplier') {
                $name = trim($_POST['name'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $contact = trim($_POST['contact_person'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if (!empty($name)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO suppliers (name, company, contact_person, phone, email, address, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $company, $contact, $phone, $email, $address, $status]);

                    Helper::logActivity('INVENTORY', 'CREATE_SUPPLIER', $name, null, "Created supplier: $name ($company)");
                    Helper::setFlash('success', "Supplier $name berhasil didaftarkan.");
                }
                Helper::redirect('suppliers');
            }

            // 2. Update Supplier
            if ($action === 'update_supplier') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $contact = trim($_POST['contact_person'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if ($id > 0 && !empty($name)) {
                    $stmt = $pdo->prepare("
                        UPDATE suppliers 
                        SET name = ?, company = ?, contact_person = ?, phone = ?, email = ?, address = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $company, $contact, $phone, $email, $address, $status, $id]);

                    Helper::logActivity('INVENTORY', 'UPDATE_SUPPLIER', (string)$id, null, "Updated supplier #$id: $name");
                    Helper::setFlash('success', "Data supplier $name berhasil diperbarui.");
                }
                Helper::redirect('suppliers');
            }

            // 3. Delete Supplier
            if ($action === 'delete_supplier') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $checkItems = $pdo->prepare("SELECT COUNT(*) FROM inventory_items WHERE supplier_id = ?");
                    $checkItems->execute([$id]);
                    $itemCount = (int)$checkItems->fetchColumn();

                    $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
                    $stmt->execute([$id]);
                    $supName = $stmt->fetchColumn() ?: "ID #$id";

                    if ($itemCount > 0) {
                        Helper::setFlash('error', "Gagal menghapus: Supplier ini terhubung dengan {$itemCount} master barang inventaris. Anda dapat menonaktifkan statusnya.");
                    } else {
                        $del = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
                        $del->execute([$id]);

                        Helper::logActivity('INVENTORY', 'DELETE_SUPPLIER', (string)$id, null, "Deleted supplier #$id: $supName");
                        Helper::setFlash('success', "Supplier $supName berhasil dihapus.");
                    }
                }
                Helper::redirect('suppliers');
            }
        }

        $suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();

        $pageTitle = 'Data Supplier & Vendor Logistik';

        ob_start();
        require __DIR__ . '/../Views/inventory/suppliers.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
