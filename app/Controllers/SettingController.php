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

        $company = $pdo->query("SELECT * FROM company_profile LIMIT 1")->fetch();

        $pageTitle = 'Profil Perusahaan & Template';

        ob_start();
        require __DIR__ . '/../Views/settings/company.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function users(): void {
        AuthMiddleware::handle('settings.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_user') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('settings_users');
            }

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

        $users = $pdo->query("
            SELECT u.*, r.display_name as role_display 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.id ASC
        ")->fetchAll();

        $roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

        $pageTitle = 'Pengguna & Hak Akses';

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
