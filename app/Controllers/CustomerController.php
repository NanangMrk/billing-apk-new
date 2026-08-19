<?php
// app/Controllers/CustomerController.php - Customer, Packages, Locations, and PIC Management

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class CustomerController {

    public function index(): void {
        AuthMiddleware::handle('customers.view');
        $pdo = getDbConnection();

        // Handle Customer Registration from Modal
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            // 1. AJAX Inline Status Updater
            if ($action === 'update_customer_status_inline') {
                AuthMiddleware::handle('customers.edit');
                header('Content-Type: application/json');

                if (!Helper::verifyCsrf()) {
                    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
                    exit;
                }

                $customerId = (int)($_POST['customer_id'] ?? 0);
                $newStatus = trim($_POST['status'] ?? '');
                $validStatuses = ['active', 'suspended', 'inactive'];

                if ($customerId <= 0 || !in_array($newStatus, $validStatuses, true)) {
                    echo json_encode(['success' => false, 'message' => 'Parameter status tidak valid.']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE customers SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $customerId]);

                Helper::logActivity('CUSTOMER', 'UPDATE_STATUS', (string)$customerId, null, "Updated customer #{$customerId} status to {$newStatus}");
                echo json_encode(['success' => true, 'message' => 'Status pelanggan berhasil diperbarui.']);
                exit;
            }

            // 2. Save New Customer
            if ($action === 'save_customer') {
                AuthMiddleware::handle('customers.create');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('customers');
                }

                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $whatsapp = trim($_POST['whatsapp'] ?? $phone);
                $email = trim($_POST['email'] ?? '');
                $packageId = (int)($_POST['package_id'] ?? 0);
                $locationId = (int)($_POST['location_id'] ?? 0) ?: null;
                $billingCycleId = (int)($_POST['billing_cycle_id'] ?? 1);
                $picId = (int)($_POST['pic_id'] ?? 0) ?: null;
                $fullAddress = trim($_POST['full_address'] ?? '');
                $odpPoint = trim($_POST['odp_point'] ?? '');
                $pppoeUser = trim($_POST['pppoe_username'] ?? '');
                $pppoePass = trim($_POST['pppoe_password'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if (empty($name) || empty($phone) || empty($packageId)) {
                    Helper::setFlash('error', 'Nama pelanggan, No. HP, dan Paket Internet wajib diisi.');
                } else {
                    $count = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() + 1;
                    $customerNo = 'CUST-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("
                        INSERT INTO customers (customer_no, name, phone, whatsapp, email, pic_id, package_id, location_id, billing_cycle_id, full_address, odp_point, pppoe_username, pppoe_password, installation_date, activation_date, billing_start_date, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, ?)
                    ");
                    $stmt->execute([$customerNo, $name, $phone, $whatsapp, $email, $picId, $packageId, $locationId, $billingCycleId, $fullAddress, $odpPoint, $pppoeUser, $pppoePass, $status]);

                    Helper::logActivity('CUSTOMER', 'CREATE', $customerNo, null, "Added new customer: $name");
                    Helper::setFlash('success', "Pelanggan $name ($customerNo) berhasil didaftarkan.");
                }
                Helper::redirect('customers');
            }

            // 3. Update Existing Customer
            if ($action === 'update_customer') {
                AuthMiddleware::handle('customers.edit');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('customers');
                }

                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $whatsapp = trim($_POST['whatsapp'] ?? $phone);
                $email = trim($_POST['email'] ?? '');
                $packageId = (int)($_POST['package_id'] ?? 0);
                $locationId = (int)($_POST['location_id'] ?? 0) ?: null;
                $billingCycleId = (int)($_POST['billing_cycle_id'] ?? 1);
                $picId = (int)($_POST['pic_id'] ?? 0) ?: null;
                $fullAddress = trim($_POST['full_address'] ?? '');
                $odpPoint = trim($_POST['odp_point'] ?? '');
                $pppoeUser = trim($_POST['pppoe_username'] ?? '');
                $pppoePass = trim($_POST['pppoe_password'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if ($id <= 0 || empty($name) || empty($phone) || empty($packageId)) {
                    Helper::setFlash('error', 'Data tidak lengkap untuk memperbarui pelanggan.');
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE customers 
                        SET name = ?, phone = ?, whatsapp = ?, email = ?, pic_id = ?, package_id = ?, 
                            location_id = ?, billing_cycle_id = ?, full_address = ?, odp_point = ?, 
                            pppoe_username = ?, pppoe_password = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $phone, $whatsapp, $email, $picId, $packageId, $locationId, $billingCycleId, $fullAddress, $odpPoint, $pppoeUser, $pppoePass, $status, $id]);

                    Helper::logActivity('CUSTOMER', 'UPDATE', (string)$id, null, "Updated customer #{$id}: {$name}");
                    Helper::setFlash('success', "Data pelanggan {$name} berhasil diperbarui.");
                }
                Helper::redirect('customers');
            }

            // 4. Delete Customer
            if ($action === 'delete_customer') {
                AuthMiddleware::handle('customers.delete');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('customers');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $cust = $pdo->query("SELECT name, customer_no FROM customers WHERE id = {$id}")->fetch();
                    $name = $cust['name'] ?? 'Pelanggan';

                    // Delete related invoices or safely delete
                    $pdo->prepare("DELETE FROM invoices WHERE customer_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM payments WHERE customer_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM tickets WHERE customer_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);

                    Helper::logActivity('CUSTOMER', 'DELETE', (string)$id, null, "Deleted customer #{$id}: {$name}");
                    Helper::setFlash('success', "Pelanggan {$name} berhasil dihapus.");
                }
                Helper::redirect('customers');
            }
        }

        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $packageId = (int)($_GET['package_id'] ?? 0);
        $locationId = (int)($_GET['location_id'] ?? 0);
        $picId = (int)($_GET['pic_id'] ?? 0);

        // RBAC: Force PIC filter if logged in as PIC / linked to a specific PIC
        if (AuthService::isPic() || AuthService::getPicId()) {
            $picId = AuthService::getPicId() ?: -1;
        }

        $whereSql = "";
        $params = [];

        if ($search !== '') {
            $whereSql .= " AND (c.name LIKE ? OR c.customer_no LIKE ? OR c.phone LIKE ? OR c.full_address LIKE ? OR c.pppoe_username LIKE ? OR l.area_name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if ($status !== '') {
            $whereSql .= " AND c.status = ?";
            $params[] = $status;
        }

        if ($packageId > 0) {
            $whereSql .= " AND c.package_id = ?";
            $params[] = $packageId;
        }

        if ($locationId > 0) {
            $whereSql .= " AND c.location_id = ?";
            $params[] = $locationId;
        }

        if ($picId > 0) {
            $whereSql .= " AND c.pic_id = ?";
            $params[] = $picId;
        } elseif ($picId === -1) {
            $whereSql .= " AND c.pic_id = -1";
        }

        // Count total matching customers
        $countSql = "
            SELECT COUNT(*) 
            FROM customers c 
            JOIN internet_packages p ON c.package_id = p.id 
            LEFT JOIN locations l ON c.location_id = l.id 
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            JOIN billing_cycles b ON c.billing_cycle_id = b.id 
            WHERE 1=1 {$whereSql}
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCustomers = (int)$countStmt->fetchColumn();

        // Limit / Per-Page Handling (10, 25, 50, 100, all - default 10)
        $rawLimit = strtolower(trim($_GET['limit'] ?? '10'));
        if ($rawLimit === 'all' || $rawLimit === 'semua' || $rawLimit === '-1') {
            $perPage = 0; // Show all
            $limitValue = 'all';
        } else {
            $perPage = (int)$rawLimit;
            if (!in_array($perPage, [10, 25, 50, 100], true)) {
                $perPage = 10;
            }
            $limitValue = (string)$perPage;
        }

        $currentPage = max(1, (int)($_GET['p'] ?? 1));
        if ($perPage > 0) {
            $totalPages = max(1, (int)ceil($totalCustomers / $perPage));
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $perPage;
            $limitClause = " LIMIT {$perPage} OFFSET {$offset}";
        } else {
            $totalPages = 1;
            $offset = 0;
            $limitClause = "";
        }

        $sql = "
            SELECT c.*, p.name as package_name, p.price as package_price, l.area_name, l.odp_name as loc_odp, b.name as cycle_name,
                   cp.name as pic_name
            FROM customers c 
            JOIN internet_packages p ON c.package_id = p.id 
            LEFT JOIN locations l ON c.location_id = l.id 
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            JOIN billing_cycles b ON c.billing_cycle_id = b.id 
            WHERE 1=1 {$whereSql}
            ORDER BY c.id DESC
            {$limitClause}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        // Master data for filters & modal
        $packages = $pdo->query("SELECT * FROM internet_packages WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $locations = $pdo->query("SELECT * FROM locations ORDER BY area_name ASC")->fetchAll();
        $cycles = $pdo->query("SELECT * FROM billing_cycles ORDER BY id ASC")->fetchAll();
        $pics = $pdo->query("SELECT id, name, position, company FROM customer_pics ORDER BY name ASC")->fetchAll();

        $pageTitle = 'Data Pelanggan';

        ob_start();
        require __DIR__ . '/../Views/customers/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function create(): void {
        // Redirect direct create requests to customers index
        Helper::redirect('customers');
    }

    public function packages(): void {
        AuthMiddleware::handle('customers.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'save_package') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('packages');
                }

                $name = trim($_POST['name'] ?? '');
                $dl = trim($_POST['download_speed'] ?? '');
                $ul = trim($_POST['upload_speed'] ?? '');
                $price = (int)($_POST['price'] ?? 0);
                $tax = (int)($_POST['tax_percent'] ?? 11);
                $fee = (int)($_POST['installation_fee'] ?? 0);
                $desc = trim($_POST['description'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if (!empty($name) && $price > 0) {
                    $stmt = $pdo->prepare("
                        INSERT INTO internet_packages (name, download_speed, upload_speed, price, tax_percent, installation_fee, description, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $dl, $ul, $price, $tax, $fee, $desc, $status]);

                    Helper::logActivity('PACKAGE', 'CREATE', $name, null, "Created package: $name ($price)");
                    Helper::setFlash('success', "Paket internet $name berhasil ditambahkan.");
                }
                Helper::redirect('packages');
            }

            if ($action === 'update_package') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('packages');
                }

                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $dl = trim($_POST['download_speed'] ?? '');
                $ul = trim($_POST['upload_speed'] ?? '');
                $price = (int)($_POST['price'] ?? 0);
                $tax = (int)($_POST['tax_percent'] ?? 0);
                $fee = (int)($_POST['installation_fee'] ?? 0);
                $desc = trim($_POST['description'] ?? '');
                $status = trim($_POST['status'] ?? 'active');

                if ($id > 0 && !empty($name) && $price > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE internet_packages 
                        SET name = ?, download_speed = ?, upload_speed = ?, price = ?, tax_percent = ?, installation_fee = ?, description = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $dl, $ul, $price, $tax, $fee, $desc, $status, $id]);

                    Helper::logActivity('PACKAGE', 'UPDATE', (string)$id, null, "Updated package #$id: $name ($price)");
                    Helper::setFlash('success', "Paket internet $name berhasil diperbarui.");
                }
                Helper::redirect('packages');
            }

            if ($action === 'delete_package') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('packages');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE package_id = ?");
                    $checkStmt->execute([$id]);
                    $userCount = (int)$checkStmt->fetchColumn();

                    if ($userCount > 0) {
                        Helper::setFlash('error', "Gagal menghapus: Paket masih digunakan oleh {$userCount} pelanggan. Pindahkan pelanggan ke paket lain terlebih dahulu atau ubah status menjadi Nonaktif.");
                    } else {
                        $pkgStmt = $pdo->prepare("SELECT name FROM internet_packages WHERE id = ?");
                        $pkgStmt->execute([$id]);
                        $pkgName = $pkgStmt->fetchColumn() ?: "ID #$id";

                        $delStmt = $pdo->prepare("DELETE FROM internet_packages WHERE id = ?");
                        $delStmt->execute([$id]);

                        Helper::logActivity('PACKAGE', 'DELETE', (string)$id, null, "Deleted package #$id: $pkgName");
                        Helper::setFlash('success', "Paket internet {$pkgName} berhasil dihapus.");
                    }
                }
                Helper::redirect('packages');
            }
        }

        $packages = $pdo->query("
            SELECT p.*, COUNT(c.id) as total_users 
            FROM internet_packages p 
            LEFT JOIN customers c ON p.id = c.package_id AND c.status = 'active'
            GROUP BY p.id
            ORDER BY p.price ASC
        ")->fetchAll();

        $pageTitle = 'Paket Layanan Internet';

        ob_start();
        require __DIR__ . '/../Views/customers/packages.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function locations(): void {
        AuthMiddleware::handle('customers.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'save_location') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('locations');
                }

                $areaName = trim($_POST['area_name'] ?? '');
                $district = trim($_POST['district'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $popName = trim($_POST['pop_name'] ?? '');
                $odpName = trim($_POST['odp_name'] ?? '');
                $coverageStatus = trim($_POST['coverage_status'] ?? 'covered');

                if (!empty($areaName)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO locations (area_name, district, city, pop_name, odp_name, coverage_status)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$areaName, $district, $city, $popName, $odpName, $coverageStatus]);

                    Helper::logActivity('LOCATION', 'CREATE', $areaName, null, "Created location: $areaName ($odpName)");
                    Helper::setFlash('success', "Area $areaName berhasil didaftarkan.");
                }
                Helper::redirect('locations');
            }

            if ($action === 'update_location') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('locations');
                }

                $id = (int)($_POST['id'] ?? 0);
                $areaName = trim($_POST['area_name'] ?? '');
                $district = trim($_POST['district'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $popName = trim($_POST['pop_name'] ?? '');
                $odpName = trim($_POST['odp_name'] ?? '');
                $coverageStatus = trim($_POST['coverage_status'] ?? 'covered');

                if ($id > 0 && !empty($areaName)) {
                    $stmt = $pdo->prepare("
                        UPDATE locations 
                        SET area_name = ?, district = ?, city = ?, pop_name = ?, odp_name = ?, coverage_status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$areaName, $district, $city, $popName, $odpName, $coverageStatus, $id]);

                    Helper::logActivity('LOCATION', 'UPDATE', (string)$id, null, "Updated location #$id: $areaName");
                    Helper::setFlash('success', "Area $areaName berhasil diperbarui.");
                }
                Helper::redirect('locations');
            }

            if ($action === 'delete_location') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('locations');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE location_id = ?");
                    $checkStmt->execute([$id]);
                    $customerCount = (int)$checkStmt->fetchColumn();

                    if ($customerCount > 0) {
                        Helper::setFlash('error', "Gagal menghapus: Area masih digunakan oleh {$customerCount} pelanggan. Harap pindahkan pelanggan ke area lain terlebih dahulu.");
                    } else {
                        $locStmt = $pdo->prepare("SELECT area_name FROM locations WHERE id = ?");
                        $locStmt->execute([$id]);
                        $locName = $locStmt->fetchColumn() ?: "ID #$id";

                        $delStmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
                        $delStmt->execute([$id]);

                        Helper::logActivity('LOCATION', 'DELETE', (string)$id, null, "Deleted location #$id: $locName");
                        Helper::setFlash('success', "Area {$locName} berhasil dihapus.");
                    }
                }
                Helper::redirect('locations');
            }
        }

        $locations = $pdo->query("
            SELECT l.*, COUNT(c.id) as total_customers 
            FROM locations l 
            LEFT JOIN customers c ON l.id = c.location_id 
            GROUP BY l.id
            ORDER BY l.area_name ASC
        ")->fetchAll();

        $pageTitle = 'Area Coverage & Titik ODP';

        ob_start();
        require __DIR__ . '/../Views/customers/locations.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function pics(): void {
        AuthMiddleware::handle('customers.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'save_pic') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('pics');
                }

                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $position = trim($_POST['position'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $notes = trim($_POST['notes'] ?? '');

                if (!empty($name) && !empty($phone)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO customer_pics (name, phone, position, company, notes)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $phone, $position, $company, $notes]);

                    Helper::logActivity('PIC', 'CREATE', $name, null, "Created PIC: $name");
                    Helper::setFlash('success', "Data PIC $name berhasil disimpan. Untuk membuat akun login atau hak aksesnya, silakan atur di menu Pengaturan Pengguna.");
                }
                Helper::redirect('pics');
            }

            if ($action === 'update_pic') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('pics');
                }

                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $position = trim($_POST['position'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $notes = trim($_POST['notes'] ?? '');

                if ($id > 0 && !empty($name) && !empty($phone)) {
                    $stmt = $pdo->prepare("
                        UPDATE customer_pics 
                        SET name = ?, phone = ?, position = ?, company = ?, notes = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $phone, $position, $company, $notes, $id]);

                    Helper::logActivity('PIC', 'UPDATE', (string)$id, null, "Updated PIC #$id: $name");
                    Helper::setFlash('success', "Data PIC $name berhasil diperbarui.");
                }
                Helper::redirect('pics');
            }

            if ($action === 'delete_pic') {
                AuthMiddleware::handle('settings.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('pics');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE pic_id = ?");
                    $checkStmt->execute([$id]);
                    $customerCount = (int)$checkStmt->fetchColumn();

                    if ($customerCount > 0) {
                        Helper::setFlash('error', "Gagal menghapus: PIC masih terhubung dengan {$customerCount} pelanggan. Harap ubah data PIC pelanggan terlebih dahulu.");
                    } else {
                        $picStmt = $pdo->prepare("SELECT name FROM customer_pics WHERE id = ?");
                        $picStmt->execute([$id]);
                        $picName = $picStmt->fetchColumn() ?: "ID #$id";

                        // Unlink any users tied to this PIC
                        $pdo->prepare("UPDATE users SET pic_id = NULL WHERE pic_id = ?")->execute([$id]);

                        $delStmt = $pdo->prepare("DELETE FROM customer_pics WHERE id = ?");
                        $delStmt->execute([$id]);

                        Helper::logActivity('PIC', 'DELETE', (string)$id, null, "Deleted PIC #$id: $picName");
                        Helper::setFlash('success', "Data PIC {$picName} berhasil dihapus.");
                    }
                }
                Helper::redirect('pics');
            }
        }

        $pics = $pdo->query("
            SELECT p.*, COUNT(c.id) as total_customers, u.username
            FROM customer_pics p 
            LEFT JOIN customers c ON p.id = c.pic_id 
            LEFT JOIN users u ON p.id = u.pic_id
            GROUP BY p.id
            ORDER BY p.name ASC
        ")->fetchAll();

        $pageTitle = 'Data PIC & Koordinator RT/RW';

        ob_start();
        require __DIR__ . '/../Views/customers/pics.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function exportCsv(): void {
        AuthMiddleware::handle('customers.view');
        $pdo = getDbConnection();

        $whereSql = "";
        $params = [];
        if (AuthService::isPic() || AuthService::getPicId()) {
            $userPicId = AuthService::getPicId() ?: -1;
            $whereSql = " WHERE c.pic_id = ? ";
            $params[] = $userPicId;
        }

        $stmt = $pdo->prepare("
            SELECT c.customer_no, c.name, c.phone, c.whatsapp, c.email, p.name as package_name, p.price as package_price, l.area_name, c.full_address, c.status, c.installation_date 
            FROM customers c 
            JOIN internet_packages p ON c.package_id = p.id 
            LEFT JOIN locations l ON c.location_id = l.id 
            {$whereSql}
            ORDER BY c.id ASC
        ");
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Data_Pelanggan_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['No. Pelanggan', 'Nama Lengkap', 'Telepon', 'WhatsApp', 'Email', 'Paket Internet', 'Tarif Bulanan', 'Wilayah / Area', 'Alamat Lengkap', 'Status', 'Tanggal Pasang']);

        foreach ($customers as $c) {
            fputcsv($out, [
                $c['customer_no'],
                $c['name'],
                $c['phone'],
                $c['whatsapp'],
                $c['email'],
                $c['package_name'],
                $c['package_price'],
                $c['area_name'] ?? '-',
                $c['full_address'],
                $c['status'],
                $c['installation_date']
            ]);
        }
        fclose($out);
        exit;
    }

    public function downloadTemplateCsv(): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_Import_Pelanggan_Lengkap.csv');

        $out = fopen('php://output', 'w');
        // All parameters from Form Registrasi Pelanggan Baru
        fputcsv($out, [
            'ID Pelanggan', 'Nama Lengkap', 'No Handphone', 'WhatsApp', 'Email', 
            'PIC Wilayah', 'Alamat Pemasangan', 'Area Coverage', 'Paket Internet', 'Tarif Bulanan', 
            'Siklus Penagihan', 'Kode Port ODP', 'PPPoE Username', 'PPPoE Password', 'Status Langganan'
        ], ',', '"', '\\');

        // Dummy 1: Full active customer with PIC & ODP
        fputcsv($out, [
            'CUST-001', 'VERY PRASETYO', '081234567891', '081234567891', 'very.prasetyo@gmail.com', 
            'Ahmad Fauzi', 'Jl. Sekembang Raya No. 12 RT 01/RW 02', 'SEKEMBANG', 'PAKET HEMAT', '100000', 
            'Tagihan Tgl 10', 'ODP-SKB-001 Port 2', 'very_net', 'pass1234', 'Aktif'
        ], ',', '"', '\\');

        // Dummy 2: Full active customer with different PIC & Area
        fputcsv($out, [
            'CUST-002', 'NURUL HIDAYAH', '085712345678', '085712345678', 'nurul.hidayah@gmail.com', 
            'Rudi Hartono', 'Dusun Prangkokan RT 03/RW 01', 'PRANGKOKAN', 'PAKET HEMAT', '100000', 
            'Tagihan Tgl 10', 'ODP-PRK-002 Port 4', 'nurul_net', 'pass5678', 'Aktif'
        ], ',', '"', '\\');

        // Dummy 3: Suspended customer with fiber package
        fputcsv($out, [
            'CUST-003', 'SRIMULYANI', '087811223344', '087811223344', 'srimulyani@gmail.com', 
            'Ahmad Fauzi', 'Perum Grand Galaxy Blok A5 No. 8', 'GRAND GALAXY', 'HOME FIBER 20M', '150000', 
            'Tagihan Tgl 15', 'ODP-GLX-001 Port 1', 'sri_galaxy', 'pass9012', 'Isolir'
        ], ',', '"', '\\');

        fclose($out);
        exit;
    }

    public function importCsv(): void {
        AuthMiddleware::handle('customers.create');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('customers');
        }

        if (!Helper::verifyCsrf()) {
            Helper::setFlash('error', 'Token CSRF tidak valid.');
            Helper::redirect('customers');
        }

        $filepath = null;

        // Check if user uploaded a file or chose the built-in data-pelanggan .csv
        if (isset($_FILES['csv_file']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            $filepath = $_FILES['csv_file']['tmp_name'];
        } elseif (!empty($_POST['use_default_sample'])) {
            // Built-in data-pelanggan .csv in project root
            $defaultFile = dirname(__DIR__, 2) . '/data-pelanggan .csv';
            if (file_exists($defaultFile)) {
                $filepath = $defaultFile;
            }
        }

        if (!$filepath || !file_exists($filepath)) {
            Helper::setFlash('error', 'Silakan pilih file CSV yang valid untuk diimpor.');
            Helper::redirect('customers');
        }

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            Helper::setFlash('error', 'Gagal membaca file CSV.');
            Helper::redirect('customers');
        }

        // Read header row and handle UTF-8 BOM (PHP 8.4+ compliant)
        $header = fgetcsv($handle, 0, ',', '"', '\\');
        if (!$header) {
            fclose($handle);
            Helper::setFlash('error', 'File CSV kosong atau format tidak sesuai.');
            Helper::redirect('customers');
        }

        if (isset($header[0])) {
            $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        }

        // Normalize header columns for all Registration Form Parameters
        $colMap = [];
        foreach ($header as $idx => $col) {
            $cleaned = strtolower(trim($col));
            if (in_array($cleaned, ['id', 'no', 'customer_no', 'no_pelanggan', 'no. pelanggan', 'id pelanggan'])) {
                $colMap['id'] = $idx;
            } elseif (in_array($cleaned, ['nama', 'name', 'nama pelanggan', 'nama lengkap'])) {
                $colMap['name'] = $idx;
            } elseif (in_array($cleaned, ['alamat', 'address', 'alamat lengkap', 'alamat pemasangan'])) {
                $colMap['address'] = $idx;
            } elseif (in_array($cleaned, ['area', 'wilayah', 'lokasi', 'area coverage', 'coverage', 'area / coverage'])) {
                $colMap['area'] = $idx;
            } elseif (in_array($cleaned, ['paket', 'package', 'paket internet', 'layanan'])) {
                $colMap['package'] = $idx;
            } elseif (in_array($cleaned, ['harga', 'price', 'tarif', 'biaya', 'tarif bulanan', 'harga paket'])) {
                $colMap['price'] = $idx;
            } elseif (in_array($cleaned, ['status', 'status langganan', 'status layanan'])) {
                $colMap['status'] = $idx;
            } elseif (in_array($cleaned, ['tgl penagihan', 'tanggal penagihan', 'tgl', 'billing_day', 'jatuh tempo', 'tgl jatuh tempo', 'siklus penagihan', 'siklus'])) {
                $colMap['billing_day'] = $idx;
            } elseif (in_array($cleaned, ['telepon', 'phone', 'hp', 'no hp', 'no. hp', 'no handphone', 'no. handphone'])) {
                $colMap['phone'] = $idx;
            } elseif (in_array($cleaned, ['whatsapp', 'wa', 'no wa', 'no whatsapp'])) {
                $colMap['whatsapp'] = $idx;
            } elseif (in_array($cleaned, ['email'])) {
                $colMap['email'] = $idx;
            } elseif (in_array($cleaned, ['pic', 'pic wilayah', 'pic / mitra', 'koordinator', 'koordinator wilayah'])) {
                $colMap['pic'] = $idx;
            } elseif (in_array($cleaned, ['odp', 'kode odp', 'odp_point', 'titik odp', 'port odp', 'kode port odp'])) {
                $colMap['odp'] = $idx;
            } elseif (in_array($cleaned, ['pppoe', 'pppoe username', 'pppoe_username', 'user pppoe', 'username pppoe'])) {
                $colMap['pppoe_user'] = $idx;
            } elseif (in_array($cleaned, ['pppoe password', 'pppoe_password', 'pass pppoe', 'password pppoe'])) {
                $colMap['pppoe_pass'] = $idx;
            }
        }

        // Fallback default index positions if headers are compact: ID,Nama,Alamat,Paket,Harga,Status,Tgl Penagihan
        if (!isset($colMap['name'])) $colMap['name'] = 1;
        if (!isset($colMap['id'])) $colMap['id'] = 0;
        if (!isset($colMap['address'])) $colMap['address'] = 2;
        if (!isset($colMap['package'])) $colMap['package'] = 3;
        if (!isset($colMap['price'])) $colMap['price'] = 4;
        if (!isset($colMap['status'])) $colMap['status'] = 5;
        if (!isset($colMap['billing_day'])) $colMap['billing_day'] = 6;

        $insertedCount = 0;
        $duplicatesCount = 0;
        $newPackagesCount = 0;
        $newLocationsCount = 0;
        $newPicsCount = 0;
        $rowNum = 1;

        // Cache packages, locations & PICs to minimize queries
        $packageCache = [];
        $locCache = [];
        $picCache = [];

        $allPkgs = $pdo->query("SELECT id, UPPER(TRIM(name)) as uname, price FROM internet_packages")->fetchAll();
        foreach ($allPkgs as $p) {
            $packageCache[$p['uname']] = (int)$p['id'];
        }

        $allLocs = $pdo->query("SELECT id, UPPER(TRIM(area_name)) as uname FROM locations")->fetchAll();
        foreach ($allLocs as $l) {
            $locCache[$l['uname']] = (int)$l['id'];
        }

        $allPics = $pdo->query("SELECT id, UPPER(TRIM(name)) as uname FROM customer_pics")->fetchAll();
        foreach ($allPics as $pc) {
            $picCache[$pc['uname']] = (int)$pc['id'];
        }

        $defaultCycleId = (int)$pdo->query("SELECT id FROM billing_cycles LIMIT 1")->fetchColumn() ?: 1;

        $pdo->beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $rowNum++;
                if (empty($row) || (count($row) === 1 && empty($row[0]))) continue;

                $rawId = trim($row[$colMap['id']] ?? '');
                $rawName = trim($row[$colMap['name']] ?? '');
                $rawAddress = trim($row[$colMap['address']] ?? '');
                $rawArea = trim($row[$colMap['area'] ?? -1] ?? '');
                $rawPackage = trim($row[$colMap['package']] ?? '');
                $rawPrice = trim($row[$colMap['price']] ?? '0');
                $rawStatus = strtolower(trim($row[$colMap['status']] ?? 'active'));
                $rawDay = (int)preg_replace('/[^0-9]/', '', $row[$colMap['billing_day']] ?? '1');
                $rawPhone = trim($row[$colMap['phone'] ?? -1] ?? '');
                $rawWhatsapp = trim($row[$colMap['whatsapp'] ?? -1] ?? '');
                $rawEmail = trim($row[$colMap['email'] ?? -1] ?? '');
                $rawPic = trim($row[$colMap['pic'] ?? -1] ?? '');
                $rawOdp = trim($row[$colMap['odp'] ?? -1] ?? '');
                $rawPppoeUser = trim($row[$colMap['pppoe_user'] ?? -1] ?? '');
                $rawPppoePass = trim($row[$colMap['pppoe_pass'] ?? -1] ?? '');

                if (empty($rawName)) continue;

                // Phone & WA formatting
                if (empty($rawPhone)) {
                    $rawPhone = '-';
                }
                if (empty($rawWhatsapp)) {
                    $rawWhatsapp = ($rawPhone !== '-') ? $rawPhone : '';
                }

                // 1. Check if customer already exists (by customer_no, pppoe_username, or name+phone / name+address)
                $isDuplicate = false;

                // Check by customer_no if provided
                if (!empty($rawId)) {
                    $chkNo = $pdo->prepare("SELECT id FROM customers WHERE customer_no = ? LIMIT 1");
                    $chkNo->execute([$rawId]);
                    if ($chkNo->fetchColumn()) {
                        $isDuplicate = true;
                    }
                }

                // Check by PPPoE Username if provided
                if (!$isDuplicate && !empty($rawPppoeUser)) {
                    $chkPppoe = $pdo->prepare("SELECT id FROM customers WHERE LOWER(pppoe_username) = LOWER(?) LIMIT 1");
                    $chkPppoe->execute([$rawPppoeUser]);
                    if ($chkPppoe->fetchColumn()) {
                        $isDuplicate = true;
                    }
                }

                // Check by Name + Phone (or Name + Address)
                if (!$isDuplicate) {
                    if (!empty($rawPhone) && $rawPhone !== '-') {
                        $chkNamePhone = $pdo->prepare("SELECT id FROM customers WHERE LOWER(TRIM(name)) = LOWER(?) AND phone = ? LIMIT 1");
                        $chkNamePhone->execute([$rawName, $rawPhone]);
                        if ($chkNamePhone->fetchColumn()) {
                            $isDuplicate = true;
                        }
                    } elseif (!empty($rawAddress)) {
                        $chkNameAddr = $pdo->prepare("SELECT id FROM customers WHERE LOWER(TRIM(name)) = LOWER(?) AND LOWER(TRIM(full_address)) = LOWER(?) LIMIT 1");
                        $chkNameAddr->execute([$rawName, $rawAddress]);
                        if ($chkNameAddr->fetchColumn()) {
                            $isDuplicate = true;
                        }
                    }
                }

                // If identical/duplicate record found, SKIP without modifying or deleting anything
                if ($isDuplicate) {
                    $duplicatesCount++;
                    continue;
                }

                // 2. Resolve / Create Package
                $pkgNameUpper = strtoupper($rawPackage ?: 'PAKET INTERNET');
                $cleanPrice = (int)preg_replace('/[^0-9]/', '', $rawPrice);
                if ($cleanPrice === 0) $cleanPrice = 100000;

                if (!isset($packageCache[$pkgNameUpper])) {
                    $insPkg = $pdo->prepare("INSERT INTO internet_packages (name, download_speed, upload_speed, price, tax_percent, status) VALUES (?, '20 Mbps', '10 Mbps', ?, 0, 'active')");
                    $insPkg->execute([$rawPackage ?: 'Paket Standar', $cleanPrice]);
                    $pkgId = (int)$pdo->lastInsertId();
                    $packageCache[$pkgNameUpper] = $pkgId;
                    $newPackagesCount++;
                } else {
                    $pkgId = $packageCache[$pkgNameUpper];
                }

                // 3. Resolve / Create Location Area
                $targetArea = $rawArea ?: $rawAddress;
                $locId = null;
                if (!empty($targetArea)) {
                    $locNameUpper = strtoupper($targetArea);
                    if (!isset($locCache[$locNameUpper])) {
                        $insLoc = $pdo->prepare("INSERT INTO locations (area_name, city) VALUES (?, 'Area Pelanggan')");
                        $insLoc->execute([$targetArea]);
                        $locId = (int)$pdo->lastInsertId();
                        $locCache[$locNameUpper] = $locId;
                        $newLocationsCount++;
                    } else {
                        $locId = $locCache[$locNameUpper];
                    }
                }

                // 4. Resolve / Create PIC
                $picId = null;
                if (!empty($rawPic)) {
                    $picUpper = strtoupper($rawPic);
                    if (!isset($picCache[$picUpper])) {
                        $insPic = $pdo->prepare("INSERT INTO customer_pics (name, position) VALUES (?, 'Koordinator Wilayah')");
                        $insPic->execute([$rawPic]);
                        $picId = (int)$pdo->lastInsertId();
                        $picCache[$picUpper] = $picId;
                        $newPicsCount++;
                    } else {
                        $picId = $picCache[$picUpper];
                    }
                }

                // 5. Resolve Status
                $status = match($rawStatus) {
                    'aktif', 'active', 'lunas' => 'active',
                    'isolir', 'suspended', 'terisolir' => 'suspended',
                    'nonaktif', 'inactive', 'off', 'berhenti' => 'inactive',
                    default => 'active'
                };

                // 6. Resolve Customer Number for New Customer
                $customerNo = $rawId;
                if (empty($customerNo)) {
                    $count = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() + 1 + $insertedCount;
                    $customerNo = 'CUST-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                }

                // Check collision if customerNo was autogenerated
                $chkCol = $pdo->prepare("SELECT id FROM customers WHERE customer_no = ?");
                $chkCol->execute([$customerNo]);
                if ($chkCol->fetchColumn()) {
                    $count = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() + 1 + $insertedCount + rand(10, 99);
                    $customerNo = 'CUST-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                }

                // 7. Insert new customer
                $ins = $pdo->prepare("
                    INSERT INTO customers (
                        customer_no, name, phone, whatsapp, email, pic_id, package_id, location_id, 
                        billing_cycle_id, full_address, odp_point, pppoe_username, pppoe_password,
                        status, installation_date, activation_date, billing_start_date
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, ?,
                        ?, CURRENT_DATE, CURRENT_DATE, CURRENT_DATE
                    )
                ");
                $ins->execute([
                    $customerNo, $rawName, $rawPhone, $rawWhatsapp, $rawEmail, $picId, $pkgId, $locId,
                    $defaultCycleId, $rawAddress, $rawOdp, $rawPppoeUser, $rawPppoePass,
                    $status
                ]);
                $insertedCount++;
            }

            $pdo->commit();
            fclose($handle);

            Helper::logActivity('CUSTOMER', 'IMPORT_CSV', "Imported {$insertedCount} new customers, {$duplicatesCount} duplicates skipped");
            
            if ($insertedCount > 0) {
                $msg = "Impor CSV Sukses: {$insertedCount} data pelanggan baru berhasil ditambahkan.";
                if ($duplicatesCount > 0) {
                    $msg .= " ({$duplicatesCount} data pelanggan yang sudah ada dilewati/skip agar tidak duplikat).";
                }
                if ($newPackagesCount > 0 || $newLocationsCount > 0 || $newPicsCount > 0) {
                    $msg .= " ({$newPackagesCount} Paket, {$newLocationsCount} Area, {$newPicsCount} PIC otomatis didaftarkan).";
                }
                Helper::setFlash('success', $msg);
            } elseif ($duplicatesCount > 0) {
                Helper::setFlash('info', "Semua data pelanggan ({$duplicatesCount} data) sudah ada di sistem dan dilewati tanpa mengubah data yang ada.");
            } else {
                Helper::setFlash('warning', "Tidak ada data pelanggan valid yang dapat diimpor.");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            fclose($handle);
            Helper::setFlash('error', 'Terjadi kesalahan saat mengimpor CSV: ' . $e->getMessage());
        }

        Helper::redirect('customers');
    }
}
