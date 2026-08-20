<?php
// database/seed.php - Database Initializer & Seeder for ISP / RT-RW Net Management System

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();

    // 1. Execute Schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);
    echo "Schema created successfully.\n";

    // 2. Check if already seeded
    $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
    if ($stmt->fetchColumn() > 0) {
        echo "Database already seeded.\n";
        exit(0);
    }

    $pdo->beginTransaction();

    // Roles
    $roles = [
        ['super_admin', 'Super Admin', 'Full access to all system features and configurations'],
        ['owner', 'Owner / Direktur', 'Executive access to analytics, financial reports, and approvals'],
        ['admin', 'Administrator', 'Administrative operational access across modules'],
        ['finance', 'Finance & Akuntansi', 'Access to billing, payments, cashflow, and P&L'],
        ['billing', 'Billing Officer', 'Manages customer invoicing, payment records, and receivables'],
        ['technician', 'Teknisi Lapangan', 'Manages tickets, installations, and assigned assets'],
        ['staff', 'Staff Operasional', 'General operational view and basic customer support']
    ];
    $stmtRole = $pdo->prepare("INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmtRole->execute($r);
    }

    // Permissions
    $permissions = [
        ['dashboard.view', 'Dashboard', 'Melihat halaman dashboard'],
        ['customers.view', 'Pelanggan', 'Melihat data pelanggan'],
        ['customers.create', 'Pelanggan', 'Menambah pelanggan baru'],
        ['customers.edit', 'Pelanggan', 'Mengubah data pelanggan'],
        ['customers.delete', 'Pelanggan', 'Menghapus data pelanggan'],
        ['billing.view', 'Billing', 'Melihat tagihan dan invoice'],
        ['billing.create', 'Billing', 'Membuat tagihan baru'],
        ['billing.payment', 'Billing', 'Mencatat pembayaran tagihan'],
        ['finance.view', 'Keuangan', 'Melihat data keuangan'],
        ['finance.create', 'Keuangan', 'Mencatat transaksi keuangan'],
        ['payroll.view', 'Payroll', 'Melihat data gaji karyawan'],
        ['payroll.manage', 'Payroll', 'Memproses penggajian'],
        ['rab.view', 'RAB & Proyek', 'Melihat data & rincian RAB'],
        ['rab.create', 'RAB & Proyek', 'Membuat & mengajukan RAB baru'],
        ['rab.edit', 'RAB & Proyek', 'Mengubah rincian / realisasi RAB'],
        ['rab.approve', 'RAB & Proyek', 'Menyetujui & menolak pengajuan RAB'],
        ['rab.delete', 'RAB & Proyek', 'Menghapus pengajuan RAB'],
        ['inventory.view', 'Inventory', 'Melihat data stok barang'],
        ['inventory.manage', 'Inventory', 'Mengelola barang masuk/keluar'],
        ['assets.view', 'Aset', 'Melihat daftar aset'],
        ['assets.manage', 'Aset', 'Mengelola dan mutasi aset'],
        ['profit_loss.view', 'Laba Rugi', 'Melihat laporan laba rugi'],
        ['tickets.view', 'Ticketing', 'Melihat tiket gangguan'],
        ['tickets.manage', 'Ticketing', 'Memperbarui dan menyelesaikan tiket'],
        ['ai.use', 'AI Assistant', 'Menggunakan fitur asisten AI'],
        ['settings.manage', 'Pengaturan', 'Mengelola pengaturan sistem dan pengguna']
    ];
    $stmtPerm = $pdo->prepare("INSERT INTO permissions (name, category, description) VALUES (?, ?, ?)");
    foreach ($permissions as $p) {
        $stmtPerm->execute($p);
    }

    // Assign all permissions to Super Admin (Role 1) and Owner (Role 2)
    $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $stmtRolePerm = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ([1, 2] as $rId) {
        foreach ($allPerms as $pId) {
            $stmtRolePerm->execute([$rId, $pId]);
        }
    }

    // Default Super Admin User
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    $users = [
        [1, 'Super Administrator', 'admin@email.com', 'admin@email.com', '081234567890', $passwordHash, 'active']
    ];
    $stmtUser = $pdo->prepare("INSERT INTO users (role_id, name, username, email, phone, password, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmtUser->execute($u);
    }

    // Default Company Profile
    $pdo->prepare("
        INSERT INTO company_profile (id, company_name, brand_name, address, phone, whatsapp, email, website)
        VALUES (1, 'PT Nusantara Net Mandiri', 'NusantaraNet ISP', 'Jl. Fiber Optik No. 88, Cyber City, Jakarta', '021-88997700', '081234567890', 'info@nusantaranet.id', 'https://nusantaranet.id')
    ")->execute();

    // Default AI Settings
    $defaultSystemPrompt = "Anda adalah AI Business & Financial Advisor untuk ISP (Internet Service Provider) NusantaraNet. Analisis data riil tagihan, kas bank, RAB proyek, stok gudang, dan PIC koordinator wilayah dengan objektif, akurat, dan berikan rekomendasi operasional yang taktis dalam bahasa Indonesia.";
    $pdo->prepare("
        INSERT INTO ai_settings (id, provider, model, api_key, base_url, temperature, max_tokens, system_prompt, is_active)
        VALUES (1, 'local', 'local-engine', '', '', 0.7, 2048, ?, 1)
    ")->execute([$defaultSystemPrompt]);

    // Default Auto-Billing Config
    $pdo->prepare("
        INSERT INTO auto_billing_config (id, status, days_before_due, default_due_day)
        VALUES (1, 'inactive', 7, 1)
    ")->execute();

    $pdo->commit();
    echo "Fresh database initialized with 0 dummy records and 1 Super Admin (admin@email.com).\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error seeding database: " . $e->getMessage() . "\n";
    exit(1);
}
