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

    // Internet Packages
    $packages = [
        ['Home Fiber 20 Mbps', '20 Mbps', '10 Mbps', 175000, 11, 150000, 'monthly', 'Paket internet rumahan hemat tanpa FUP', 'active'],
        ['Home Fiber 50 Mbps', '50 Mbps', '25 Mbps', 275000, 11, 150000, 'monthly', 'Paket internet keluarga cepat untuk streaming 4K & gaming', 'active'],
        ['Office Fast 100 Mbps', '100 Mbps', '50 Mbps', 550000, 11, 250000, 'monthly', 'Koneksi bisnis stabil dengan prioritas bandwidth', 'active'],
        ['Dedicated Pro 200 Mbps', '200 Mbps', '200 Mbps', 1500000, 11, 500000, 'monthly', 'Bandwidth 1:1 Simetris dengan SLA 99.5% dan IP Publik Statis', 'active']
    ];
    $stmtPkg = $pdo->prepare("INSERT INTO internet_packages (name, download_speed, upload_speed, price, tax_percent, installation_fee, billing_cycle, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($packages as $pkg) {
        $stmtPkg->execute($pkg);
    }

    // Locations
    $locations = [
        ['Perum Grand Galaxy Blok A-D', 'Bekasi Selatan', 'Bekasi', 'POP-Galaxy-01', 'ODP-GLX-001', -6.2625, 106.9745, 'covered'],
        ['Kelurahan Sukamaju RW 05', 'Cilodong', 'Depok', 'POP-Depok-02', 'ODP-SKM-012', -6.4215, 106.8432, 'covered'],
        ['Komplek Griya Asri Blok F', 'Cibinong', 'Bogor', 'POP-Bogor-01', 'ODP-CIB-008', -6.4831, 106.8521, 'covered'],
        ['Kawasan Bisnis Sudirman Point', 'Setiabudi', 'Jakarta Selatan', 'POP-JKT-HQ', 'ODP-SDR-003', -6.2146, 106.8209, 'covered']
    ];
    $stmtLoc = $pdo->prepare("INSERT INTO locations (area_name, district, city, pop_name, odp_name, latitude, longitude, coverage_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($locations as $loc) {
        $stmtLoc->execute($loc);
    }

    // Billing Cycles
    $cycles = [
        ['Siklus 1 (Tgl 1 - 10)', 1, 10, 3, 5, 'Tagihan rilis tanggal 1, jatuh tempo tanggal 10'],
        ['Siklus 2 (Tgl 5 - 15)', 5, 15, 3, 5, 'Tagihan rilis tanggal 5, jatuh tempo tanggal 15'],
        ['Siklus 3 (Tgl 20 - 30)', 20, 30, 3, 5, 'Tagihan rilis tanggal 20, jatuh tempo tanggal 30']
    ];
    $stmtCyc = $pdo->prepare("INSERT INTO billing_cycles (name, generate_day, due_day, grace_period_days, auto_suspend_days, description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cycles as $c) {
        $stmtCyc->execute($c);
    }

    // Customer PICs
    $pics = [
        ['Hendra Wijaya', '081399881122', '081399881122', 'hendra@galaxy.id', 'Ketua RT 04', 'RT 04 RW 12 Galaxy', 'Koordinator wilayah perumahan'],
        ['Irvan Kurniawan', '081277665544', '081277665544', 'it@sudirmanpoint.com', 'IT Manager', 'PT Sudirman Point Kreatif', 'PIC Teknis Gedung']
    ];
    $stmtPic = $pdo->prepare("INSERT INTO customer_pics (name, phone, whatsapp, email, position, company, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($pics as $p) {
        $stmtPic->execute($p);
    }

    // Customers
    $customers = [
        ['CUST-000001', 'Anugrah Pratama', '081211112222', '081211112222', 'anugrah@gmail.com', 1, 2, 1, 1, 'Perum Grand Galaxy Blok A3 No. 12', -6.2625, 106.9745, 'ODP-GLX-001', 'POP-Galaxy-01', '192.168.10.15', 'anugrah_glx', 'pass123', '2025-01-10', '2025-01-11', '2025-02-01', 'active', 'Pelanggan loyal pembayaran lancar'],
        ['CUST-000002', 'Rian Hidayat', '081233334444', '081233334444', 'rian.h@gmail.com', null, 1, 2, 1, 'Jl. Mawar No. 45 Sukamaju', -6.4215, 106.8432, 'ODP-SKM-012', 'POP-Depok-02', '192.168.10.16', 'rian_skm', 'pass123', '2025-02-01', '2025-02-02', '2025-02-05', 'active', 'Home Fiber 20Mbps'],
        ['CUST-000003', 'PT Cipta Media Solusi', '081199887766', '081199887766', 'finance@ciptamedia.co.id', 2, 3, 4, 2, 'Gedung Sudirman Point Lt. 3', -6.2146, 106.8209, 'ODP-SDR-003', 'POP-JKT-HQ', '10.200.1.5', 'ciptamedia_corp', 'corpPass88', '2024-11-15', '2024-11-16', '2024-12-01', 'active', 'Paket Bisnis 100 Mbps Dedicated'],
        ['CUST-000004', 'Deni Setiawan', '085711223344', '085711223344', 'deni.set@yahoo.com', null, 1, 1, 1, 'Perum Grand Galaxy Blok C2 No. 8', -6.2629, 106.9750, 'ODP-GLX-001', 'POP-Galaxy-01', '192.168.10.18', 'deni_glx', 'pass123', '2025-03-01', '2025-03-02', '2025-03-05', 'suspended', 'Tagihan jatuh tempo belum dibayar 15 hari'],
        ['CUST-000005', 'Maya Kusuma', '087855667788', '087855667788', 'maya.kusuma@gmail.com', null, 2, 3, 1, 'Komplek Griya Asri Blok F1 No. 5', -6.4831, 106.8521, 'ODP-CIB-008', 'POP-Bogor-01', '192.168.10.22', 'maya_cib', 'pass123', '2025-05-10', '2025-05-11', '2025-06-01', 'active', 'Home Fiber 50 Mbps'],
        ['CUST-000006', 'Klinik Sehat Bersama', '081344556677', '081344556677', 'info@kliniksehat.id', null, 3, 1, 1, 'Ruko Galaxy Square No. 10', -6.2635, 106.9730, 'ODP-GLX-002', 'POP-Galaxy-01', '192.168.10.25', 'klinik_sehat', 'pass123', '2025-04-12', '2025-04-13', '2025-05-01', 'active', 'Office Fast 100Mbps']
    ];
    $stmtCust = $pdo->prepare("INSERT INTO customers (customer_no, name, phone, whatsapp, email, pic_id, package_id, location_id, billing_cycle_id, full_address, latitude, longitude, odp_point, pop_point, ip_address, pppoe_username, pppoe_password, installation_date, activation_date, billing_start_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($customers as $c) {
        $stmtCust->execute($c);
    }

    // Finance Accounts
    $accounts = [
        ['Kas Tunai Kantor', 'cash', 'Kas Operasional', 'CASH-01', 5000000, 8500000, 'active'],
        ['Bank BCA Operasional', 'bank', 'BCA', '1234567890', 25000000, 48750000, 'active'],
        ['Bank Mandiri Penerimaan', 'bank', 'Bank Mandiri', '9876543210', 10000000, 22400000, 'active'],
        ['QRIS Settlement', 'qris', 'QRIS Merchant', 'QRIS-NUSA-01', 2000000, 6300000, 'active']
    ];
    $stmtAcc = $pdo->prepare("INSERT INTO finance_accounts (account_name, account_type, bank_name, account_number, opening_balance, current_balance, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($accounts as $acc) {
        $stmtAcc->execute($acc);
    }

    // Finance Categories
    $finCats = [
        ['Pendapatan Langganan Internet', 'income', 'Penerimaan tagihan internet bulanan pelanggan'],
        ['Pendapatan Biaya Pasang Baru', 'income', 'Pemasukan dari registrasi dan instalasi pelanggan baru'],
        ['Pendapatan Jasa & Perangkat', 'income', 'Penjualan router, kabel, dan jasa perbaikan'],
        ['Beban Bandwidth / Upstream ISP', 'expense', 'Pembayaran bandwidth grosir ke penyedia upstream'],
        ['Beban Listrik & Sewa POP', 'expense', 'Tagihan PLN dan sewa lokasi OLT/POP'],
        ['Beban Gaji & Upah Karyawan', 'expense', 'Pembayaran payroll bulanan tim'],
        ['Beban Operasional & BBM Teknisi', 'expense', 'BBM armada motor/mobil teknisi dan konsumsi'],
        ['Beban Pembelian Stok & Alat', 'expense', 'Pembelian kabel dropcore, fast connector, ONU'],
        ['Beban Pemeliharaan Jaringan', 'expense', 'Perbaikan kabel putus, ganti perangkat rusak']
    ];
    $stmtFinCat = $pdo->prepare("INSERT INTO finance_categories (name, type, description) VALUES (?, ?, ?)");
    foreach ($finCats as $fc) {
        $stmtFinCat->execute($fc);
    }

    // Invoices
    $invoices = [
        ['INV-202608-000001', 1, '2026-08', 'Home Fiber 50 Mbps', 275000, 0, 30250, 0, 0, 305250, 305250, 0, '2026-08-01', '2026-08-10', 'paid', '2026-08-05 10:30:00', 'BCA Transfer', 'Lunas tepat waktu', 1],
        ['INV-202608-000002', 2, '2026-08', 'Home Fiber 20 Mbps', 175000, 0, 19250, 0, 0, 194250, 194250, 0, '2026-08-01', '2026-08-10', 'paid', '2026-08-07 14:15:00', 'QRIS', 'Lunas via QRIS', 1],
        ['INV-202608-000003', 3, '2026-08', 'Office Fast 100 Mbps', 550000, 0, 60500, 0, 0, 610500, 610500, 0, '2026-08-05', '2026-08-15', 'paid', '2026-08-12 09:00:00', 'Bank Mandiri', 'Lunas korporat', 1],
        ['INV-202608-000004', 4, '2026-08', 'Home Fiber 20 Mbps', 175000, 0, 19250, 0, 0, 194250, 0, 194250, '2026-08-01', '2026-08-10', 'overdue', null, null, 'Jatuh tempo terlewat - isolir otomatis', 1],
        ['INV-202608-000005', 5, '2026-08', 'Home Fiber 50 Mbps', 275000, 0, 30250, 0, 0, 305250, 0, 305250, '2026-08-01', '2026-08-10', 'unpaid', null, null, 'Menunggu pembayaran', 1],
        ['INV-202608-000006', 6, '2026-08', 'Office Fast 100 Mbps', 550000, 0, 60500, 0, 0, 610500, 610500, 0, '2026-08-01', '2026-08-10', 'paid', '2026-08-04 11:20:00', 'BCA Transfer', 'Lunas klinik', 1]
    ];
    $stmtInv = $pdo->prepare("INSERT INTO invoices (invoice_no, customer_id, billing_period, package_name_snapshot, subtotal, discount, tax, additional_fee, previous_balance, grand_total, paid_amount, balance_due, issue_date, due_date, payment_status, payment_date, payment_method, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($invoices as $inv) {
        $stmtInv->execute($inv);
    }

    // Invoice Items
    $stmtInvItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtInvItem->execute([1, 'Langganan Internet Home Fiber 50 Mbps (Agustus 2026)', 1, 275000, 275000, 'Periode 01/08/2026 - 31/08/2026']);
    $stmtInvItem->execute([2, 'Langganan Internet Home Fiber 20 Mbps (Agustus 2026)', 1, 175000, 175000, 'Periode 01/08/2026 - 31/08/2026']);
    $stmtInvItem->execute([3, 'Langganan Internet Office Fast 100 Mbps (Agustus 2026)', 1, 550000, 550000, 'Periode 01/08/2026 - 31/08/2026']);
    $stmtInvItem->execute([4, 'Langganan Internet Home Fiber 20 Mbps (Agustus 2026)', 1, 175000, 175000, 'Periode 01/08/2026 - 31/08/2026']);
    $stmtInvItem->execute([5, 'Langganan Internet Home Fiber 50 Mbps (Agustus 2026)', 1, 275000, 275000, 'Periode 01/08/2026 - 31/08/2026']);
    $stmtInvItem->execute([6, 'Langganan Internet Office Fast 100 Mbps (Agustus 2026)', 1, 550000, 550000, 'Periode 01/08/2026 - 31/08/2026']);

    // Payments
    $payments = [
        ['PAY-202608-000001', 1, 1, 2, 305250, '2026-08-05', 'BCA Transfer', 'TRX-BCA-88991', null, 'Pembayaran tagihan Anugrah Pratama via BCA', 1],
        ['PAY-202608-000002', 2, 2, 4, 194250, '2026-08-07', 'QRIS', 'QRIS-774411', null, 'Pembayaran tagihan Rian Hidayat via QRIS', 1],
        ['PAY-202608-000003', 3, 3, 3, 610500, '2026-08-12', 'Bank Mandiri', 'MDR-992211', null, 'Pembayaran tagihan PT Cipta Media Solusi', 1],
        ['PAY-202608-000004', 6, 6, 2, 610500, '2026-08-04', 'BCA Transfer', 'BCA-445566', null, 'Pembayaran tagihan Klinik Sehat Bersama', 1]
    ];
    $stmtPay = $pdo->prepare("INSERT INTO payments (payment_no, invoice_id, customer_id, account_id, amount, payment_date, payment_method, reference_no, proof_file, notes, received_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($payments as $pay) {
        $stmtPay->execute($pay);
    }

    // Finance Transactions
    $finTrx = [
        ['TRX-202608-000001', '2026-08-05', 2, 1, 'income', 305250, 'Penerimaan Pembayaran INV-202608-000001 (Anugrah Pratama)', 'invoice', 1, null, 1],
        ['TRX-202608-000002', '2026-08-07', 4, 1, 'income', 194250, 'Penerimaan Pembayaran INV-202608-000002 (Rian Hidayat)', 'invoice', 2, null, 1],
        ['TRX-202608-000003', '2026-08-12', 3, 1, 'income', 610500, 'Penerimaan Pembayaran INV-202608-000003 (PT Cipta Media)', 'invoice', 3, null, 1],
        ['TRX-202608-000004', '2026-08-04', 2, 1, 'income', 610500, 'Penerimaan Pembayaran INV-202608-000006 (Klinik Sehat)', 'invoice', 6, null, 1],
        ['TRX-202608-000005', '2026-08-02', 2, 4, 'expense', 15000000, 'Pembayaran Upstream Bandwidth 1 Gbps ke Telkom/Indosat', 'manual', null, null, 1],
        ['TRX-202608-000006', '2026-08-03', 1, 5, 'expense', 1850000, 'Tagihan Listrik PLN Ruang Server & POP Galaxy', 'manual', null, null, 1],
        ['TRX-202608-000007', '2026-08-06', 1, 7, 'expense', 450000, 'BBM & Uang Jalan Teknisi Instalasi Wilayah Depok', 'manual', null, null, 1]
    ];
    $stmtTrx = $pdo->prepare("INSERT INTO finance_transactions (transaction_no, transaction_date, account_id, category_id, type, amount, description, reference_type, reference_id, attachment, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($finTrx as $trx) {
        $stmtTrx->execute($trx);
    }

    // Departments & Employees
    $departments = [
        ['Management & Direksi', 'Struktur pimpinan dan pengambil keputusan'],
        ['Teknis & Jaringan', 'Divisi NOC, maintenance fiber optik, dan instalasi'],
        ['Finance & Billing', 'Divisi keuangan, penagihan, dan administrasi'],
        ['Customer Support', 'Layanan bantuan dan penanganan keluhan pelanggan']
    ];
    $stmtDept = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    foreach ($departments as $d) {
        $stmtDept->execute($d);
    }

    $employees = [
        ['EMP-001', 'Budi Santoso', 1, 'Direktur Operasional', '081234567891', 'owner@nusantaranet.id', 12000000, 'BCA', '1122334455', 'permanent', '2023-01-01', 'active'],
        ['EMP-002', 'Ahmad Fauzi', 2, 'Senior Network Engineer', '081234567894', 'teknisi@nusantaranet.id', 5500000, 'BCA', '5566778899', 'permanent', '2023-06-01', 'active'],
        ['EMP-003', 'Rahmat Dani', 2, 'Teknisi Lapangan / FO', '081299887711', 'rahmat@nusantaranet.id', 4000000, 'Mandiri', '9988776655', 'permanent', '2024-02-15', 'active'],
        ['EMP-004', 'Siti Rahma', 3, 'Finance & Accounting Lead', '081234567892', 'finance@nusantaranet.id', 5000000, 'BCA', '3344556677', 'permanent', '2023-08-01', 'active'],
        ['EMP-005', 'Dewi Lestari', 3, 'Billing Specialist', '081234567893', 'billing@nusantaranet.id', 3800000, 'BCA', '7788990011', 'permanent', '2024-01-10', 'active']
    ];
    $stmtEmp = $pdo->prepare("INSERT INTO employees (employee_no, name, department_id, position, phone, email, basic_salary, bank_name, bank_account, employment_status, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($employees as $emp) {
        $stmtEmp->execute($emp);
    }

    // Warehouses & Suppliers
    $warehouses = [
        ['Gudang Pusat Cyber HQ', 'Cyber City Jakarta Lt. Dasar', 'Ahmad Fauzi', 'Gudang perangkat utama, OLT, router, dan splicing'],
        ['Gudang POP Galaxy', 'Perum Grand Galaxy Blok A1', 'Rahmat Dani', 'Stok dropcore, ONU, fast connector, dan tiang']
    ];
    $stmtWh = $pdo->prepare("INSERT INTO warehouses (name, location, pic_name, notes) VALUES (?, ?, ?, ?)");
    foreach ($warehouses as $wh) {
        $stmtWh->execute($wh);
    }

    $suppliers = [
        ['PT Fiber Optik Nusantara', 'Fiber Corp ID', 'Bambang Sudiro', '081122334455', 'sales@fibernusantara.co.id', 'Jl. Industri Raya No. 12, Cikarang', 'active'],
        ['CV Routerindo Global', 'Routerindo', 'Yuliana', '081299008811', 'order@routerindo.com', 'Mangga Dua Mall Lt. 4 Blok B, Jakarta Pusat', 'active']
    ];
    $stmtSup = $pdo->prepare("INSERT INTO suppliers (name, company, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($suppliers as $sup) {
        $stmtSup->execute($sup);
    }

    // Inventory Categories & Items
    $invCats = [
        ['ONU / ONT Router', 'Perangkat modem pelanggan GPON/EPON'],
        ['Kabel Dropcore & Fiber', 'Kabel optik 1 core, 2 core, feeder'],
        ['Passive & Aksesoris FO', 'Fast connector, adapter, closure, pigtail, patchcord'],
        ['Perangkat Aktif & OLT', 'OLT GPON, SFP Module, Mikrotik Router']
    ];
    $stmtInvCat = $pdo->prepare("INSERT INTO inventory_categories (name, description) VALUES (?, ?)");
    foreach ($invCats as $ic) {
        $stmtInvCat->execute($ic);
    }

    $invItems = [
        ['SKU-ONU-ZTE-F609', 'ZTE F609 GPON ONT Dual Band', 1, 'ZTE', 'F609 v9', 'unit', 165000, 220000, 10, 35, 1, 2, null, 'Modem ONT GPON standard untuk instalasi pelanggan rumahan', 'active'],
        ['SKU-ONU-HUA-HG8245', 'Huawei HG8245H GPON ONU', 1, 'Huawei', 'HG8245H', 'unit', 175000, 235000, 10, 20, 1, 2, null, 'Modem WiFi 4 Port LAN GPON', 'active'],
        ['SKU-KBL-DC-1C-1000', 'Dropcore Fiber Optic 1 Core 3 Seling (1000m)', 2, 'Global Fiber', '1 Core 1000M', 'roll', 450000, 550000, 3, 8, 2, 1, null, 'Kabel optik outdoor kualitas tinggi 1 roll 1000 meter', 'active'],
        ['SKU-ACC-FC-SCUPC', 'Fast Connector SC UPC Biru', 3, 'Ilsintech', 'SC-UPC', 'pack', 65000, 85000, 5, 25, 2, 1, null, '1 pack isi 100 pcs fast connector SC UPC', 'active'],
        ['SKU-OLT-HSGQ-4P', 'HSGQ 4 Port EPON/GPON OLT', 4, 'HSGQ', 'E04P', 'unit', 3850000, 4500000, 1, 2, 1, 2, null, 'OLT Mini 4 PON port untuk perluasan coverage', 'active'],
        ['SKU-ACC-ODP-16P', 'ODP Pole 16 Port Solid', 3, 'PAZ', 'ODP-16P', 'unit', 135000, 175000, 5, 14, 2, 1, null, 'Box ODP 16 core tiang outdoor tahan cuaca', 'active']
    ];
    $stmtItem = $pdo->prepare("INSERT INTO inventory_items (sku, name, category_id, brand, model, unit, purchase_price, selling_price, min_stock, current_stock, warehouse_id, supplier_id, image, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($invItems as $it) {
        $stmtItem->execute($it);
    }

    // Company Assets (Splicer, OTDR, OLT, Tangga, Laptop)
    $assets = [
        ['AST-000001', 5, 'Fusion Splicer Fujikura 90S+', 'SN-FJK-99281', null, '2024-01-15', 38000000, 32000000, 'good', 'Gudang Pusat Cyber HQ', null, 'Ahmad Fauzi', 'in_use', 'Mesin penyambung kabel fiber optik utama tim teknis'],
        ['AST-000002', null, 'OTDR Anritsu MT9083 Access Master', 'SN-ANR-88123', null, '2024-03-20', 28000000, 24000000, 'good', 'Gudang Pusat Cyber HQ', null, 'Ahmad Fauzi', 'available', 'Alat ukur dan deteksi putus kabel fiber optik'],
        ['AST-000003', null, 'Mikrotik CCR1036-8G-2S+ Core Router', 'SN-MKT-44120', '48:8F:5A:11:22:33', '2023-11-10', 14500000, 11500000, 'good', 'Rack Server Cyber HQ', null, 'Budi Santoso', 'in_use', 'Router Gateway Utama ISP BGP & PPPoE Server'],
        ['AST-000004', 1, 'Modem ONT ZTE F609 (Anugrah Pratama)', 'SN-ZTE-774411', '70:2E:22:99:88:11', '2025-01-10', 165000, 150000, 'good', 'Rumah Pelanggan', 1, 'Anugrah Pratama', 'assigned_customer', 'Dipinjamkan sebagai CPE di rumah pelanggan']
    ];
    $stmtAst = $pdo->prepare("INSERT INTO assets (asset_no, item_id, name, serial_number, mac_address, purchase_date, purchase_price, current_value, condition, location, customer_id, pic_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($assets as $ast) {
        $stmtAst->execute($ast);
    }

    // RAB (Rencana Anggaran Biaya) Projects
    $rabCats = [
        ['Pembangunan ODP / Ekspansi Jaringan', 'Proyek penarikan kabel feeder dan pemasangan ODP baru'],
        ['Pemasangan Backbone Fiber', 'Koneksi fiber optik antar POP / Data Center'],
        ['Pengadaan Perangkat Core', 'Upgrade kapasitas router dan OLT']
    ];
    $stmtRabCat = $pdo->prepare("INSERT INTO rab_categories (name, description) VALUES (?, ?)");
    foreach ($rabCats as $rc) {
        $stmtRabCat->execute($rc);
    }

    $rabs = [
        ['RAB-202608-000001', 'Ekspansi Jaringan Fiber Optik Perum Grand Galaxy Blok G & H', 1, 'Perum Grand Galaxy Bekasi', 'Ahmad Fauzi', null, '2026-08-15', '2026-08-30', 8500000, 4200000, 'in_progress', 'Pemasangan 4 ODP baru dan penarikan kabel dropcore 2000m kapasitas 64 user baru', 1, 2],
        ['RAB-202608-000002', 'Upgrade Core Router & OLT POP Depok', 3, 'POP Depok Cilodong', 'Ahmad Fauzi', null, '2026-09-01', '2026-09-10', 12500000, 0, 'approved', 'Penggantian Switch Aggregation dan penambahan 1 modul OLT 8 Port', 1, 2]
    ];
    $stmtRab = $pdo->prepare("INSERT INTO rabs (rab_no, project_name, category_id, location, pic_name, customer_id, start_date, end_date, budget_total, realized_total, status, description, created_by, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($rabs as $rb) {
        $stmtRab->execute($rb);
    }

    // RAB Items
    $stmtRabItem = $pdo->prepare("INSERT INTO rab_items (rab_id, item_name, category, quantity, unit, unit_price, subtotal, realized_subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtRabItem->execute([1, 'Kabel Dropcore 2 Core (2 Roll)', 'Material', 2, 'roll', 450000, 900000, 900000, 'Dropcore outdoor']);
    $stmtRabItem->execute([1, 'Box ODP Solid 16 Port + Splitter 1:8', 'Material', 4, 'unit', 185000, 740000, 740000, 'ODP tiang']);
    $stmtRabItem->execute([1, 'Tiang Besi Galvanis 7 Meter + Pasang', 'Infrastruktur', 6, 'batang', 450000, 2700000, 2560000, 'Pondasi cor tiang']);
    $stmtRabItem->execute([1, 'Jasa Penarikan Kabel & Splicing FO', 'Jasa', 1, 'paket', 3500000, 3500000, 0, 'Subkon penarikan']);
    $stmtRabItem->execute([1, 'Biaya Tak Terduga & Perijinan RT/RW', 'Operasional', 1, 'paket', 660000, 660000, 0, 'Ijin lingkungan']);

    // Tickets
    $tickets = [
        ['TKT-202608-000001', 4, 'Internet Tidak Konek / LOS Merah', 'Lampu indikator PON/LOS di modem berkedip merah sejak pagi tadi', 'connection_down', 'high', 3, 'in_progress', '2026-08-16 08:30:00', null, 'Teknisi Rahmat sedang mengecek titik ODP GLX-001', 1],
        ['TKT-202608-000002', 1, 'Permintaan Relokasi Titik Router Lantai 2', 'Pelanggan meminta kabel dropcore diperpanjang dan router dipindah ke kamar kerja lantai 2', 'relocation', 'low', 3, 'resolved', '2026-08-14 13:00:00', '2026-08-14 16:30:00', 'Selesai dipindahkan dengan penambahan patchcord 5 meter. Redaman -19.4 dBm sangat baik.', 1],
        ['TKT-202608-000003', 3, 'Speed Test Lambat pada Jam Sibuk', 'Klien melaporkan bandwidth hanya mencapai 45 Mbps saat siang hari', 'slow_speed', 'medium', 2, 'open', '2026-08-17 10:15:00', null, 'Perlu cek alokasi queue di Mikrotik Core', 1]
    ];
    $stmtTkt = $pdo->prepare("INSERT INTO tickets (ticket_no, customer_id, title, description, category, priority, technician_id, status, reported_at, resolved_at, resolution_notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($tickets as $tkt) {
        $stmtTkt->execute($tkt);
    }

    // Activity Logs
    $logs = [
        [1, 'AUTH', 'LOGIN', null, null, 'User admin login successfully', '127.0.0.1', 'Mozilla/5.0'],
        [1, 'BILLING', 'CREATE_INVOICE', 'INV-202608-000001', null, 'Generated monthly invoice for customer Anugrah Pratama', '127.0.0.1', 'Mozilla/5.0'],
        [1, 'FINANCE', 'RECORD_PAYMENT', 'PAY-202608-000001', null, 'Payment Rp 305.250 received via BCA for INV-202608-000001', '127.0.0.1', 'Mozilla/5.0']
    ];
    $stmtLog = $pdo->prepare("INSERT INTO activity_logs (user_id, module, action, record_id, old_value, new_value, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($logs as $l) {
        $stmtLog->execute($l);
    }

    $pdo->commit();
    echo "Seed data inserted successfully!\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error seeding database: " . $e->getMessage() . "\n";
    exit(1);
}
