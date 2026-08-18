<?php
// app/Controllers/SettingController.php - Company Profile, Users, and Audit Logs

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class SettingController {

    public function company(): void {
        AuthMiddleware::handle('settings.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('settings_company');
            }

            $action = $_POST['action'] ?? 'update_profile';

            if ($action === 'save_account') {
                $name = trim($_POST['account_name'] ?? '');
                $type = trim($_POST['account_type'] ?? 'bank');
                $bank = trim($_POST['bank_name'] ?? '');
                $accNo = trim($_POST['account_number'] ?? '');
                $balance = (int)str_replace(['.', ',', ' '], '', $_POST['opening_balance'] ?? '0');

                if (!empty($name)) {
                    $stmt = $pdo->prepare("INSERT INTO finance_accounts (account_name, account_type, bank_name, account_number, opening_balance, current_balance) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $type, $bank, $accNo, $balance, $balance]);
                    Helper::logActivity('SETTINGS', 'CREATE_ACCOUNT', $pdo->lastInsertId(), null, "Created account $name");
                    Helper::setFlash('success', 'Rekening baru berhasil ditambahkan.');
                }
                Helper::redirect('settings_company');
            }

            if ($action === 'update_account') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['account_name'] ?? '');
                $type = trim($_POST['account_type'] ?? 'bank');
                $bank = trim($_POST['bank_name'] ?? '');
                $accNo = trim($_POST['account_number'] ?? '');

                if ($id > 0 && !empty($name)) {
                    $stmt = $pdo->prepare("UPDATE finance_accounts SET account_name = ?, account_type = ?, bank_name = ?, account_number = ? WHERE id = ?");
                    $stmt->execute([$name, $type, $bank, $accNo, $id]);
                    Helper::logActivity('SETTINGS', 'UPDATE_ACCOUNT', (string)$id, null, "Updated account $name");
                    Helper::setFlash('success', 'Rekening berhasil diperbarui.');
                }
                Helper::redirect('settings_company');
            }

            if ($action === 'delete_account') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    // Check if used in transactions
                    $used = $pdo->query("SELECT COUNT(*) FROM finance_transactions WHERE account_id = $id")->fetchColumn();
                    if ($used > 0) {
                        Helper::setFlash('error', 'Rekening tidak bisa dihapus karena sudah ada transaksi.');
                    } else {
                        $pdo->prepare("DELETE FROM finance_accounts WHERE id = ?")->execute([$id]);
                        Helper::logActivity('SETTINGS', 'DELETE_ACCOUNT', (string)$id, null, "Deleted account #$id");
                        Helper::setFlash('success', 'Rekening berhasil dihapus.');
                    }
                }
                Helper::redirect('settings_company');
            }

            if ($action === 'update_profile') {
                $companyName = trim($_POST['company_name'] ?? '');
                $brandName = trim($_POST['brand_name'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $whatsapp = trim($_POST['whatsapp'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $bankInfo = trim($_POST['bank_account_info'] ?? '');
                $footer = trim($_POST['invoice_footer'] ?? '');

                $stmt = $pdo->prepare("
                    UPDATE company_profile 
                    SET company_name = ?, brand_name = ?, address = ?, phone = ?, whatsapp = ?, email = ?, bank_account_info = ?, invoice_footer = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = 1
                ");
                $stmt->execute([$companyName, $brandName, $address, $phone, $whatsapp, $email, $bankInfo, $footer]);

                Helper::logActivity('SETTINGS', 'UPDATE_COMPANY', '1', null, 'Updated company profile');
                Helper::setFlash('success', 'Profil perusahaan berhasil diperbarui.');
                Helper::redirect('settings_company');
            }
        }

        $company = $pdo->query("SELECT * FROM company_profile LIMIT 1")->fetch();
        $accounts = $pdo->query("SELECT * FROM finance_accounts ORDER BY id ASC")->fetchAll();

        $pageTitle = 'Profil Perusahaan & Rekening';

        ob_start();
        require __DIR__ . '/../Views/settings/company.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    private function ensurePermissionsExist(PDO $pdo): array {
        $permissionsList = [
            // Pelanggan
            ['customers.view', 'Pelanggan', 'Melihat data & detail pelanggan'],
            ['customers.create', 'Pelanggan', 'Menambah pelanggan baru'],
            ['customers.edit', 'Pelanggan', 'Mengubah data pelanggan'],
            ['customers.delete', 'Pelanggan', 'Menghapus data pelanggan'],
            ['customers.import', 'Pelanggan', 'Mengimpor data pelanggan (CSV)'],
            ['customers.export', 'Pelanggan', 'Mengekspor data pelanggan (CSV)'],
            ['customers.filter', 'Pelanggan', 'Menggunakan filter & pencarian pelanggan'],

            // Paket Internet
            ['packages.view', 'Paket Internet', 'Melihat daftar paket internet'],
            ['packages.create', 'Paket Internet', 'Menambah paket internet baru'],
            ['packages.edit', 'Paket Internet', 'Mengubah paket internet'],
            ['packages.delete', 'Paket Internet', 'Menghapus paket internet'],

            // Area Coverage
            ['locations.view', 'Area Coverage', 'Melihat area coverage & ODP'],
            ['locations.create', 'Area Coverage', 'Menambah area / ODP baru'],
            ['locations.edit', 'Area Coverage', 'Mengubah area coverage'],
            ['locations.delete', 'Area Coverage', 'Menghapus area coverage'],

            // PIC / Penanggung Jawab
            ['pics.view', 'PIC / Koordinator', 'Melihat daftar PIC'],
            ['pics.create', 'PIC / Koordinator', 'Menambah PIC baru'],
            ['pics.edit', 'PIC / Koordinator', 'Mengubah data PIC'],
            ['pics.delete', 'PIC / Koordinator', 'Menghapus data PIC'],

            // Billing & Tagihan
            ['billing.view', 'Billing & Tagihan', 'Melihat daftar tagihan & invoice'],
            ['billing.create', 'Billing & Tagihan', 'Membuat tagihan / invoice baru'],
            ['billing.edit', 'Billing & Tagihan', 'Mengubah invoice tagihan'],
            ['billing.delete', 'Billing & Tagihan', 'Menghapus invoice tagihan'],
            ['billing.payment', 'Billing & Tagihan', 'Mencatat pembayaran tagihan'],
            ['billing.export', 'Billing & Tagihan', 'Mengekspor data tagihan & piutang'],

            // Keuangan & Transaksi
            ['finance.view', 'Keuangan & Kas', 'Melihat arus kas & transaksi'],
            ['finance.create', 'Keuangan & Kas', 'Mencatat transaksi pemasukan/pengeluaran'],
            ['finance.edit', 'Keuangan & Kas', 'Mengubah transaksi keuangan'],
            ['finance.delete', 'Keuangan & Kas', 'Menghapus transaksi keuangan'],
            ['finance.account', 'Keuangan & Kas', 'Mengelola akun rekening bank/kas'],

            // Penggajian / Payroll
            ['payroll.view', 'Payroll & Gaji', 'Melihat data & slip gaji karyawan'],
            ['payroll.manage', 'Payroll & Gaji', 'Memproses & mengelola penggajian'],

            // RAB & Proyek
            ['rab.view', 'RAB & Proyek', 'Melihat data RAB proyek'],
            ['rab.create', 'RAB & Proyek', 'Membuat draf RAB baru'],
            ['rab.edit', 'RAB & Proyek', 'Mengubah draf RAB'],
            ['rab.delete', 'RAB & Proyek', 'Menghapus draf RAB'],
            ['rab.approve', 'RAB & Proyek', 'Menyetujui & menolak pengajuan RAB'],

            // Inventaris & Gudang
            ['inventory.view', 'Inventaris Gudang', 'Melihat stok barang gudang'],
            ['inventory.create', 'Inventaris Gudang', 'Menambah master item barang'],
            ['inventory.edit', 'Inventaris Gudang', 'Mengubah master item barang'],
            ['inventory.delete', 'Inventaris Gudang', 'Menghapus item barang'],
            ['inventory.goods_in', 'Inventaris Gudang', 'Mengelola barang masuk'],
            ['inventory.goods_out', 'Inventaris Gudang', 'Mengelola barang keluar'],
            ['inventory.suppliers', 'Inventaris Gudang', 'Mengelola data supplier'],

            // Aset Perusahaan
            ['assets.view', 'Aset Perusahaan', 'Melihat daftar aset'],
            ['assets.create', 'Aset Perusahaan', 'Menambah data aset baru'],
            ['assets.edit', 'Aset Perusahaan', 'Mengubah data aset'],
            ['assets.delete', 'Aset Perusahaan', 'Menghapus data aset'],
            ['assets.maintenance', 'Aset Perusahaan', 'Mencatat pemeliharaan & mutasi aset'],

            // Laba Rugi
            ['profit_loss.view', 'Laba Rugi', 'Melihat laporan laba rugi'],

            // Ticketing & Gangguan
            ['tickets.view', 'Ticketing Gangguan', 'Melihat tiket gangguan & aduan'],
            ['tickets.create', 'Ticketing Gangguan', 'Membuat tiket gangguan baru'],
            ['tickets.manage', 'Ticketing Gangguan', 'Memperbarui status & balasan tiket'],
            ['tickets.delete', 'Ticketing Gangguan', 'Menghapus tiket gangguan'],

            // Dashboard & AI
            ['dashboard.view', 'Dashboard', 'Melihat halaman dashboard utama'],
            ['ai.use', 'AI Advisor', 'Mengakses fitur AI Advisor'],

            // Pengaturan Sistem
            ['settings.company', 'Pengaturan Sistem', 'Mengelola profil perusahaan'],
            ['settings.users', 'Pengaturan Sistem', 'Mengelola akun pengguna'],
            ['settings.roles', 'Pengaturan Sistem', 'Mengelola role & hak akses'],
            ['settings.logs', 'Pengaturan Sistem', 'Melihat audit log aktivitas'],
            ['settings.manage', 'Pengaturan Sistem', 'Akses penuh pengaturan sistem'],
        ];

        $stmtCheck = $pdo->prepare("SELECT id FROM permissions WHERE name = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO permissions (name, category, description) VALUES (?, ?, ?)");

        foreach ($permissionsList as $p) {
            $stmtCheck->execute([$p[0]]);
            if (!$stmtCheck->fetch()) {
                $stmtInsert->execute($p);
            }
        }

        return $pdo->query("SELECT * FROM permissions ORDER BY category ASC, id ASC")->fetchAll();
    }

    public function users(): void {
        AuthMiddleware::handle('settings.manage');
        $pdo = getDbConnection();

        // Ensure granular permissions exist in database
        $allPermissions = $this->ensurePermissionsExist($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('settings_users');
            }

            $action = $_POST['action'] ?? '';

            // 1. Create User
            if ($action === 'save_user') {
                $name = trim($_POST['name'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $roleId = (int)($_POST['role_id'] ?? 3);
                $password = trim($_POST['password'] ?? 'admin123');

                if (!empty($name) && !empty($username) && !empty($email)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, role_id, password, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$name, $username, $email, $phone, $roleId, $hash]);

                    Helper::logActivity('SETTINGS', 'CREATE_USER', $username, null, "Added user $username ($name)");
                    Helper::setFlash('success', "Pengguna $name berhasil didaftarkan.");
                }
                Helper::redirect('settings_users');
            }

            // 2. Update User
            if ($action === 'update_user') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $roleId = (int)($_POST['role_id'] ?? 3);
                $status = trim($_POST['status'] ?? 'active');
                $password = trim($_POST['password'] ?? '');

                if ($id > 0 && !empty($name) && !empty($username)) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone = ?, role_id = ?, status = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $username, $email, $phone, $roleId, $status, $hash, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone = ?, role_id = ?, status = ? WHERE id = ?");
                        $stmt->execute([$name, $username, $email, $phone, $roleId, $status, $id]);
                    }

                    Helper::logActivity('SETTINGS', 'UPDATE_USER', (string)$id, null, "Updated user $username ($name)");
                    Helper::setFlash('success', "Data pengguna $name berhasil diperbarui.");
                }
                Helper::redirect('settings_users');
            }

            // 3. Delete User
            if ($action === 'delete_user') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $u = $pdo->query("SELECT * FROM users WHERE id = {$id}")->fetch();
                    if ($u) {
                        if ($u['id'] == 1 || $u['username'] === 'admin') {
                            Helper::setFlash('error', 'Akun Super Admin utama tidak dapat dihapus.');
                        } else {
                            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                            Helper::logActivity('SETTINGS', 'DELETE_USER', (string)$id, null, "Deleted user {$u['username']}");
                            Helper::setFlash('success', "User {$u['name']} berhasil dihapus.");
                        }
                    }
                }
                Helper::redirect('settings_users');
            }

            // 4. Create Role
            if ($action === 'save_role') {
                $displayName = trim($_POST['display_name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $selectedPerms = $_POST['permissions'] ?? [];

                if (!empty($displayName)) {
                    $nameSlug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $displayName)) . '_' . time();
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)");
                    $stmt->execute([$nameSlug, $displayName, $description]);
                    $roleId = (int)$pdo->lastInsertId();

                    if (!empty($selectedPerms) && is_array($selectedPerms)) {
                        $stmtRP = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                        foreach ($selectedPerms as $permId) {
                            $stmtRP->execute([$roleId, (int)$permId]);
                        }
                    }

                    $pdo->commit();

                    Helper::logActivity('SETTINGS', 'CREATE_ROLE', (string)$roleId, null, "Created role $displayName");
                    Helper::setFlash('success', "Role \"$displayName\" berhasil dibuat.");
                }
                Helper::redirect('settings_users', ['tab' => 'roles']);
            }

            // 5. Update Role
            if ($action === 'update_role') {
                $roleId = (int)($_POST['id'] ?? 0);
                $displayName = trim($_POST['display_name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $selectedPerms = $_POST['permissions'] ?? [];

                if ($roleId > 0 && !empty($displayName)) {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE roles SET display_name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$displayName, $description, $roleId]);

                    // Reset role permissions
                    $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

                    if (!empty($selectedPerms) && is_array($selectedPerms)) {
                        $stmtRP = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                        foreach ($selectedPerms as $permId) {
                            $stmtRP->execute([$roleId, (int)$permId]);
                        }
                    }

                    $pdo->commit();

                    Helper::logActivity('SETTINGS', 'UPDATE_ROLE', (string)$roleId, null, "Updated role $displayName");
                    Helper::setFlash('success', "Hak akses role \"$displayName\" berhasil diperbarui.");
                }
                Helper::redirect('settings_users', ['tab' => 'roles']);
            }

            // 6. Delete Role
            if ($action === 'delete_role') {
                $roleId = (int)($_POST['id'] ?? 0);
                if ($roleId > 0) {
                    if ($roleId === 1) {
                        Helper::setFlash('error', 'Role Super Admin bawaan sistem tidak dapat dihapus.');
                    } else {
                        // Check if users exist in this role
                        $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role_id = {$roleId}")->fetchColumn();
                        if ($userCount > 0) {
                            Helper::setFlash('error', "Role tidak dapat dihapus karena masih digunakan oleh $userCount pengguna.");
                        } else {
                            $pdo->beginTransaction();
                            $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);
                            $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$roleId]);
                            $pdo->commit();

                            Helper::logActivity('SETTINGS', 'DELETE_ROLE', (string)$roleId, null, "Deleted role #$roleId");
                            Helper::setFlash('success', "Role berhasil dihapus.");
                        }
                    }
                }
                Helper::redirect('settings_users', ['tab' => 'roles']);
            }
        }

        $users = $pdo->query("
            SELECT u.*, r.display_name as role_display, r.name as role_slug
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.id ASC
        ")->fetchAll();

        $roles = $pdo->query("
            SELECT r.*, 
                   (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) as user_count,
                   (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as permission_count
            FROM roles r
            ORDER BY r.id ASC
        ")->fetchAll();

        // Fetch role permission map
        $rolePermissionsMap = [];
        $rpRows = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll();
        foreach ($rpRows as $row) {
            $rolePermissionsMap[(int)$row['role_id']][] = (int)$row['permission_id'];
        }

        // Group all permissions by category
        $groupedPermissions = [];
        foreach ($allPermissions as $p) {
            $groupedPermissions[$p['category']][] = $p;
        }

        $activeTab = $_GET['tab'] ?? 'users';
        $pageTitle = 'Pengguna & Hak Akses (Roles)';

        ob_start();
        require __DIR__ . '/../Views/settings/users.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function auditLogs(): void {
        AuthMiddleware::handle('settings.manage');
        $pdo = getDbConnection();

        $logs = $pdo->query("
            SELECT l.*, u.name as user_name, u.username 
            FROM activity_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.id DESC LIMIT 100
        ")->fetchAll();

        $pageTitle = 'Audit & Activity Log';

        ob_start();
        require __DIR__ . '/../Views/settings/audit_logs.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
