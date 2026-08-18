<?php
// app/Controllers/BillingController.php - Billing, Invoices, Payments, Auto Billing, and Comprehensive Filtering

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class BillingController {

    public function invoices(): void {
        AuthMiddleware::handle('billing.view');
        $pdo = getDbConnection();

        // 1. Handle Batch Operations on Selected Invoices
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_update_invoices') {
            AuthMiddleware::handle('billing.manage');

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $batchAction = trim($_POST['batch_action'] ?? '');
            $invoiceIds = $_POST['invoice_ids'] ?? [];

            if (is_string($invoiceIds)) {
                $invoiceIds = json_decode($invoiceIds, true) ?: explode(',', $invoiceIds);
            }

            $invoiceIds = array_filter(array_map('intval', (array)$invoiceIds));

            if (empty($invoiceIds) || empty($batchAction)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Pilih minimal satu tagihan untuk diproses.']);
                    exit;
                }
                Helper::setFlash('error', 'Pilih minimal satu tagihan untuk diproses.');
                Helper::redirect('invoices');
            }

            $userId = AuthService::user()['id'] ?? null;
            $countUpdated = 0;

            $pdo->beginTransaction();
            try {
                foreach ($invoiceIds as $invId) {
                    $inv = $pdo->query("SELECT * FROM invoices WHERE id = {$invId}")->fetch();
                    if (!$inv) continue;

                    if ($batchAction === 'mark_paid') {
                        $diff = $inv['grand_total'] - $inv['paid_amount'];
                        $pdo->exec("UPDATE invoices SET paid_amount = grand_total, balance_due = 0, payment_status = 'paid', updated_at = CURRENT_TIMESTAMP WHERE id = {$invId}");

                        if ($diff > 0) {
                            $payCount = (int)$pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn() + 1;
                            $payNo = 'PAY-' . date('Ym') . '-' . str_pad((string)$payCount, 6, '0', STR_PAD_LEFT);
                            
                            $stmtPay = $pdo->prepare("
                                INSERT INTO payments (payment_no, invoice_id, customer_id, account_id, payment_date, amount, payment_method, reference_no, received_by, notes)
                                VALUES (?, ?, ?, 1, CURRENT_DATE, ?, 'cash', 'BATCH_MARK_PAID', ?, 'Pelunasan massal batch')
                            ");
                            $stmtPay->execute([$payNo, $invId, $inv['customer_id'], $diff, $userId]);

                            $txNo = 'TX-' . date('Ymd') . '-' . str_pad((string)$payCount, 5, '0', STR_PAD_LEFT);
                            $stmtTx = $pdo->prepare("
                                INSERT INTO finance_transactions (transaction_no, account_id, category_id, transaction_date, type, amount, description, reference_type, reference_id, created_by)
                                VALUES (?, 1, 1, CURRENT_DATE, 'income', ?, ?, 'invoice', ?, ?)
                            ");
                            $stmtTx->execute([$txNo, $diff, "Pelunasan Invoice {$inv['invoice_no']} (Batch Action)", $invId, $userId]);
                            $pdo->exec("UPDATE finance_accounts SET current_balance = current_balance + {$diff} WHERE id = 1");
                        }
                        $countUpdated++;
                    } elseif ($batchAction === 'mark_unpaid') {
                        $pdo->exec("UPDATE invoices SET paid_amount = 0, balance_due = grand_total, payment_status = 'unpaid', updated_at = CURRENT_TIMESTAMP WHERE id = {$invId}");
                        $countUpdated++;
                    } elseif ($batchAction === 'mark_cancelled') {
                        $pdo->exec("UPDATE invoices SET payment_status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = {$invId}");
                        $countUpdated++;
                    } elseif ($batchAction === 'delete') {
                        $pdo->exec("DELETE FROM invoice_items WHERE invoice_id = {$invId}");
                        $pdo->exec("DELETE FROM payments WHERE invoice_id = {$invId}");
                        $pdo->exec("DELETE FROM invoices WHERE id = {$invId}");
                        $countUpdated++;
                    }
                }

                $pdo->commit();
                Helper::logActivity('BILLING', 'BATCH_ACTION', $batchAction, null, "Executed $batchAction on " . count($invoiceIds) . " invoices");

                $msg = "Berhasil memproses $countUpdated tagihan secara massal.";
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $msg, 'count' => $countUpdated]);
                    exit;
                }
                Helper::setFlash('success', $msg);
            } catch (Exception $e) {
                $pdo->rollBack();
                $err = "Gagal memproses batch: " . $e->getMessage();
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $err]);
                    exit;
                }
                Helper::setFlash('error', $err);
            }

            Helper::redirect('invoices');
        }

        // 2. Handle Inline Status Change (AJAX & Form POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_invoice_status_inline') {
            AuthMiddleware::handle('billing.manage');
            
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $invoiceId = (int)($_POST['invoice_id'] ?? 0);
            $newStatus = trim($_POST['status'] ?? 'unpaid');

            if ($invoiceId <= 0 || !in_array($newStatus, ['paid', 'unpaid', 'partially_paid', 'overdue', 'cancelled'])) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Status atau invoice tidak valid.']);
                    exit;
                }
                Helper::setFlash('error', 'Status atau invoice tidak valid.');
                Helper::redirect('invoices');
            }

            $invoice = $pdo->query("SELECT * FROM invoices WHERE id = {$invoiceId}")->fetch();
            if (!$invoice) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
                    exit;
                }
                Helper::setFlash('error', 'Invoice tidak ditemukan.');
                Helper::redirect('invoices');
            }

            $userId = AuthService::user()['id'] ?? null;

            if ($newStatus === 'paid') {
                $paidAmt = $invoice['grand_total'];
                $dueAmt = 0;

                $stmtUp = $pdo->prepare("UPDATE invoices SET paid_amount = ?, balance_due = ?, payment_status = 'paid', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmtUp->execute([$paidAmt, $dueAmt, $invoiceId]);

                if ($invoice['paid_amount'] < $invoice['grand_total']) {
                    $diff = $invoice['grand_total'] - $invoice['paid_amount'];
                    $payCount = (int)$pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn() + 1;
                    $payNo = 'PAY-' . date('Ym') . '-' . str_pad((string)$payCount, 6, '0', STR_PAD_LEFT);
                    
                    $stmtPay = $pdo->prepare("
                        INSERT INTO payments (payment_no, invoice_id, customer_id, account_id, payment_date, amount, payment_method, reference_no, received_by, notes)
                        VALUES (?, ?, ?, 1, CURRENT_DATE, ?, 'cash', 'INLINE_SET_PAID', ?, 'Pelunasan status inline')
                    ");
                    $stmtPay->execute([$payNo, $invoiceId, $invoice['customer_id'], $diff, $userId]);

                    $txNo = 'TX-' . date('Ymd') . '-' . str_pad((string)$payCount, 5, '0', STR_PAD_LEFT);
                    $stmtTx = $pdo->prepare("
                        INSERT INTO finance_transactions (transaction_no, account_id, category_id, transaction_date, type, amount, description, reference_type, reference_id, created_by)
                        VALUES (?, 1, 1, CURRENT_DATE, 'income', ?, ?, 'invoice', ?, ?)
                    ");
                    $stmtTx->execute([$txNo, $diff, "Pelunasan Invoice {$invoice['invoice_no']} (Status Inline)", $invoiceId, $userId]);

                    $pdo->exec("UPDATE finance_accounts SET current_balance = current_balance + {$diff} WHERE id = 1");
                }
            } elseif ($newStatus === 'unpaid') {
                $stmtUp = $pdo->prepare("UPDATE invoices SET paid_amount = 0, balance_due = grand_total, payment_status = 'unpaid', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmtUp->execute([$invoiceId]);
            } else {
                $stmtUp = $pdo->prepare("UPDATE invoices SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmtUp->execute([$newStatus, $invoiceId]);
            }

            Helper::logActivity('BILLING', 'INLINE_STATUS_CHANGE', $invoice['invoice_no'], $invoice['payment_status'], "Changed status to $newStatus");

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'status' => $newStatus, 'message' => "Status invoice {$invoice['invoice_no']} berhasil diubah ke {$newStatus}."]);
                exit;
            }

            Helper::setFlash('success', "Status invoice {$invoice['invoice_no']} berhasil diubah.");
            Helper::redirect('invoices');
        }

        // 3. Handle Save Auto-Billing Configuration
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_auto_billing_config') {
            AuthMiddleware::handle('settings.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('invoices');
            }

            $status = trim($_POST['status'] ?? 'inactive');
            $daysBefore = max(1, (int)($_POST['days_before_due'] ?? 7));
            $defaultDueDay = max(1, min(28, (int)($_POST['default_due_day'] ?? 1)));

            $stmtSave = $pdo->prepare("
                UPDATE auto_billing_config 
                SET status = ?, days_before_due = ?, default_due_day = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = 1
            ");
            $stmtSave->execute([$status, $daysBefore, $defaultDueDay]);

            Helper::logActivity('BILLING', 'UPDATE_AUTO_CONFIG', '1', null, "Updated auto-billing: status=$status, days_before=$daysBefore, default_due_day=$defaultDueDay");
            Helper::setFlash('success', 'Pengaturan Auto-Generate Tagihan Bulanan berhasil disimpan.');
            Helper::redirect('invoices');
        }

        // 4. Handle Manual 1-Click Trigger
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manual_trigger_auto_billing') {
            AuthMiddleware::handle('billing.create');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('invoices');
            }

            $targetPeriod = trim($_POST['period'] ?? date('Y-m'));
            $autoBilling = $pdo->query("SELECT * FROM auto_billing_config WHERE id = 1")->fetch();
            $defaultDueDay = (int)($autoBilling['default_due_day'] ?? 1);

            $stmtCust = $pdo->prepare("
                SELECT c.*, p.name as package_name, p.price as package_price, p.tax_percent, b.due_day as cycle_due_day 
                FROM customers c 
                JOIN internet_packages p ON c.package_id = p.id 
                LEFT JOIN billing_cycles b ON c.billing_cycle_id = b.id
                WHERE c.status = 'active'
                AND c.id NOT IN (SELECT customer_id FROM invoices WHERE billing_period = ?)
            ");
            $stmtCust->execute([$targetPeriod]);
            $activeCustomers = $stmtCust->fetchAll();

            $generatedCount = 0;
            $userId = AuthService::user()['id'] ?? null;

            foreach ($activeCustomers as $cust) {
                $subtotal = (int)$cust['package_price'];
                $taxPercent = (int)($cust['tax_percent'] ?? 11);
                $taxAmount = (int)round(($subtotal * $taxPercent) / 100);
                $grandTotal = $subtotal + $taxAmount;

                $dueDay = (int)($cust['cycle_due_day'] ?: $defaultDueDay);
                $dueDate = $targetPeriod . '-' . str_pad((string)$dueDay, 2, '0', STR_PAD_LEFT);

                $count = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() + 1;
                $invNo = 'INV-' . str_replace('-', '', $targetPeriod) . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);

                $stmtInv = $pdo->prepare("
                    INSERT INTO invoices (invoice_no, customer_id, billing_period, issue_date, due_date, subtotal, discount, tax, grand_total, paid_amount, balance_due, payment_status, notes, package_name_snapshot, created_by)
                    VALUES (?, ?, ?, CURRENT_DATE, ?, ?, 0, ?, ?, 0, ?, 'unpaid', 'Tagihan otomatis internet bulanan', ?, ?)
                ");
                $stmtInv->execute([$invNo, $cust['id'], $targetPeriod, $dueDate, $subtotal, $taxAmount, $grandTotal, $grandTotal, $cust['package_name'], $userId]);
                $invId = (int)$pdo->lastInsertId();

                $stmtItem = $pdo->prepare("
                    INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, subtotal, notes)
                    VALUES (?, ?, 1, ?, ?, ?)
                ");
                $stmtItem->execute([$invId, "Langganan " . $cust['package_name'], $subtotal, $subtotal, "Periode " . $targetPeriod]);

                $generatedCount++;
            }

            Helper::logActivity('BILLING', 'TRIGGER_AUTO_BILLING', $targetPeriod, null, "Generated $generatedCount invoices for $targetPeriod");
            Helper::setFlash('success', "Berhasil menerbitkan $generatedCount tagihan untuk pelanggan aktif periode $targetPeriod.");
            Helper::redirect('invoices');
        }

        // Fetch Auto-Billing Configuration
        $autoBilling = $pdo->query("SELECT * FROM auto_billing_config WHERE id = 1")->fetch();
        if (!$autoBilling) {
            $pdo->exec("INSERT INTO auto_billing_config (id, status, days_before_due, default_due_day) VALUES (1, 'inactive', 7, 1)");
            $autoBilling = $pdo->query("SELECT * FROM auto_billing_config WHERE id = 1")->fetch();
        }

        // Fetch filter options (Packages, Locations, PICs)
        $packages = $pdo->query("SELECT id, name FROM internet_packages ORDER BY name ASC")->fetchAll();
        $locations = $pdo->query("SELECT id, area_name, city FROM locations ORDER BY area_name ASC")->fetchAll();
        $pics = $pdo->query("SELECT id, name, position, company FROM customer_pics ORDER BY name ASC")->fetchAll();

        $status = trim($_GET['status'] ?? '');
        $period = trim($_GET['period'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $packageId = (int)($_GET['package_id'] ?? 0);
        $locationId = (int)($_GET['location_id'] ?? 0);
        $picId = (int)($_GET['pic_id'] ?? 0);

        // RBAC: Force PIC Filter if logged in as PIC
        if (AuthService::isPic()) {
            $picId = AuthService::getPicId() ?: -1; // -1 ensures no data if pic_id is missing somehow
        }

        $whereSql = "";
        $params = [];

        if ($status !== '') {
            $whereSql .= " AND i.payment_status = ?";
            $params[] = $status;
        }

        if ($period !== '') {
            $whereSql .= " AND i.billing_period = ?";
            $params[] = $period;
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
        }

        if ($search !== '') {
            $whereSql .= " AND (i.invoice_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR l.area_name LIKE ? OR cp.name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        // Count total matching invoices
        $countSql = "
            SELECT COUNT(*) 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            WHERE 1=1 {$whereSql}
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalInvoices = (int)$countStmt->fetchColumn();

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
            $totalPages = max(1, (int)ceil($totalInvoices / $perPage));
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $perPage;
            $limitClause = " LIMIT {$perPage} OFFSET {$offset}";
        } else {
            $totalPages = 1;
            $offset = 0;
            $limitClause = "";
        }

        $sql = "
            SELECT i.*, c.name as customer_name, c.customer_no, c.phone, c.full_address, c.odp_point,
                   l.area_name, l.city, cp.name as pic_name, cp.phone as pic_phone
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            WHERE 1=1 {$whereSql}
            ORDER BY i.id DESC
            {$limitClause}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        $pageTitle = 'Tagihan & Invoice Pelanggan';

        ob_start();
        require __DIR__ . '/../Views/billing/invoices.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function createInvoice(): void {
        AuthMiddleware::handle('billing.create');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('create_invoice');
            }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $billingPeriod = trim($_POST['billing_period'] ?? date('Y-m'));
            $issueDate = trim($_POST['issue_date'] ?? date('Y-m-d'));
            $dueDate = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days')));
            $taxPercent = (int)($_POST['tax_percent'] ?? 11);
            $discount = (int)($_POST['discount_amount'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            $itemsDesc = $_POST['item_desc'] ?? [];
            $itemsPrice = $_POST['item_price'] ?? [];
            $itemsQty = $_POST['item_qty'] ?? [];

            if ($customerId <= 0 || empty($itemsDesc)) {
                Helper::setFlash('error', 'Pelanggan dan minimal satu rincian item wajib diisi.');
                Helper::redirect('create_invoice');
            }

            $cust = $pdo->query("SELECT c.*, p.name as package_name FROM customers c JOIN internet_packages p ON c.package_id = p.id WHERE c.id = {$customerId}")->fetch();

            $subtotal = 0;
            $itemsData = [];
            for ($i = 0; $i < count($itemsDesc); $i++) {
                $d = trim($itemsDesc[$i]);
                $p = (int)($itemsPrice[$i] ?? 0);
                $q = max(1, (int)($itemsQty[$i] ?? 1));
                if (!empty($d) && $p > 0) {
                    $tot = $p * $q;
                    $subtotal += $tot;
                    $itemsData[] = ['desc' => $d, 'price' => $p, 'qty' => $q, 'total' => $tot];
                }
            }

            $taxAmount = (int)round((($subtotal - $discount) * $taxPercent) / 100);
            $grandTotal = max(0, $subtotal - $discount + $taxAmount);

            $count = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() + 1;
            $invNo = 'INV-' . str_replace('-', '', $billingPeriod) . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
            $userId = AuthService::user()['id'] ?? null;

            $stmt = $pdo->prepare("
                INSERT INTO invoices (invoice_no, customer_id, billing_period, issue_date, due_date, subtotal, discount, tax, grand_total, paid_amount, balance_due, payment_status, notes, package_name_snapshot, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 'unpaid', ?, ?, ?)
            ");
            $stmt->execute([$invNo, $customerId, $billingPeriod, $issueDate, $dueDate, $subtotal, $discount, $taxAmount, $grandTotal, $grandTotal, $notes, $cust['package_name'], $userId]);
            $invId = (int)$pdo->lastInsertId();

            foreach ($itemsData as $it) {
                $stmtIt = $pdo->prepare("
                    INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, subtotal, notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtIt->execute([$invId, $it['desc'], $it['qty'], $it['price'], $it['total'], '']);
            }

            Helper::logActivity('BILLING', 'CREATE_INVOICE', $invNo, null, "Created invoice $invNo for {$cust['name']} ($grandTotal)");
            Helper::setFlash('success', "Invoice $invNo berhasil diterbitkan.");
            Helper::redirect('invoices');
        }

        $selectedCustId = (int)($_GET['customer_id'] ?? 0);
        $customers = $pdo->query("
            SELECT c.*, p.name as package_name, p.price as package_price, p.tax_percent 
            FROM customers c 
            JOIN internet_packages p ON c.package_id = p.id 
            WHERE c.status = 'active'
            ORDER BY c.name ASC
        ")->fetchAll();

        $pageTitle = 'Terbitkan Invoice Baru';

        ob_start();
        require __DIR__ . '/../Views/billing/create_invoice.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function showInvoice(): void {
        AuthMiddleware::handle('billing.view');
        $pdo = getDbConnection();

        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT i.*, c.name as customer_name, c.customer_no, c.phone, c.whatsapp, c.email, c.full_address, c.odp_point,
                   l.area_name, l.city, b.name as cycle_name
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id 
            JOIN billing_cycles b ON c.billing_cycle_id = b.id 
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            Helper::setFlash('error', 'Invoice tidak ditemukan.');
            Helper::redirect('invoices');
        }

        $items = $pdo->query("SELECT * FROM invoice_items WHERE invoice_id = {$id}")->fetchAll();
        $payments = $pdo->query("SELECT p.*, a.account_name, u.name as receiver_name FROM payments p JOIN finance_accounts a ON p.account_id = a.id LEFT JOIN users u ON p.received_by = u.id WHERE p.invoice_id = {$id} ORDER BY p.id ASC")->fetchAll();
        $company = $pdo->query("SELECT * FROM company_profile LIMIT 1")->fetch();
        $accounts = $pdo->query("SELECT * FROM finance_accounts WHERE status = 'active'")->fetchAll();

        $pageTitle = 'Invoice #' . $invoice['invoice_no'];

        ob_start();
        require __DIR__ . '/../Views/billing/show_invoice.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function recordPayment(): void {
        AuthMiddleware::handle('billing.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('invoices');
            }

            $invoiceId = (int)($_POST['invoice_id'] ?? 0);
            $accountId = (int)($_POST['account_id'] ?? 1);
            $amount = (int)($_POST['amount'] ?? 0);
            $method = trim($_POST['payment_method'] ?? 'bank_transfer');
            $refNo = trim($_POST['reference_no'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($invoiceId <= 0 || $amount <= 0) {
                Helper::setFlash('error', 'Invoice dan nominal pembayaran wajib diisi.');
                Helper::redirect('invoices');
            }

            $invoice = $pdo->query("SELECT * FROM invoices WHERE id = {$invoiceId}")->fetch();
            if (!$invoice) {
                Helper::setFlash('error', 'Invoice tidak ditemukan.');
                Helper::redirect('invoices');
            }

            $pdo->beginTransaction();
            try {
                $payCount = (int)$pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn() + 1;
                $payNo = 'PAY-' . date('Ym') . '-' . str_pad((string)$payCount, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;

                $stmtPay = $pdo->prepare("
                    INSERT INTO payments (payment_no, invoice_id, customer_id, account_id, payment_date, amount, payment_method, reference_no, received_by, notes)
                    VALUES (?, ?, ?, 1, CURRENT_DATE, ?, ?, ?, ?, ?)
                ");
                $stmtPay->execute([$payNo, $invoiceId, $invoice['customer_id'], $accountId, $amount, $method, $refNo, $userId, $notes]);

                $newPaid = $invoice['paid_amount'] + $amount;
                $newDue = max(0, $invoice['grand_total'] - $newPaid);
                $status = ($newDue <= 0) ? 'paid' : 'partially_paid';

                $stmtUpInv = $pdo->prepare("UPDATE invoices SET paid_amount = ?, balance_due = ?, payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmtUpInv->execute([$newPaid, $newDue, $status, $invoiceId]);

                $stmtTx = $pdo->prepare("
                    INSERT INTO finance_transactions (transaction_no, account_id, category_id, transaction_date, type, amount, description, reference_type, reference_id, created_by)
                    VALUES (?, 1, 1, CURRENT_DATE, 'income', ?, ?, 'invoice', ?, ?)
                ");
                $txNo = 'TX-' . date('Ymd') . '-' . str_pad((string)$payCount, 5, '0', STR_PAD_LEFT);
                $stmtTx->execute([$txNo, $diff, "Pembayaran Invoice {$invoice['invoice_no']} ($payNo)", $invoiceId, $userId]);

                $stmtUpAcc = $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?");
                $stmtUpAcc->execute([$amount, $accountId]);

                $pdo->commit();
                Helper::logActivity('FINANCE', 'RECORD_PAYMENT', $payNo, null, "Recorded payment of $amount for {$invoice['invoice_no']}");
                Helper::setFlash('success', "Pembayaran " . Helper::formatRupiah($amount) . " berhasil dicatat.");
            } catch (Exception $e) {
                $pdo->rollBack();
                Helper::setFlash('error', "Gagal mencatat pembayaran: " . $e->getMessage());
            }

            Helper::redirect('show_invoice', ['id' => $invoiceId]);
        }
    }

    public function payments(): void {
        AuthMiddleware::handle('billing.view');
        $pdo = getDbConnection();

        // Fetch options for filter modal
        $accounts = $pdo->query("SELECT id, account_name, account_number FROM finance_accounts WHERE status = 'active' ORDER BY account_name ASC")->fetchAll();
        $methods = $pdo->query("SELECT DISTINCT payment_method FROM payments WHERE payment_method IS NOT NULL AND payment_method != '' ORDER BY payment_method ASC")->fetchAll();
        $locations = $pdo->query("SELECT id, area_name, city FROM locations ORDER BY area_name ASC")->fetchAll();
        $pics = $pdo->query("SELECT id, name, position, company FROM customer_pics ORDER BY name ASC")->fetchAll();

        $search = trim($_GET['search'] ?? '');
        $method = trim($_GET['method'] ?? '');
        $accountId = (int)($_GET['account_id'] ?? 0);
        $locationId = (int)($_GET['location_id'] ?? 0);
        $picId = (int)($_GET['pic_id'] ?? 0);
        $period = trim($_GET['period'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');

        $sql = "
            SELECT p.*, i.invoice_no, i.billing_period, c.name as customer_name, c.customer_no, c.phone,
                   l.area_name, cp.name as pic_name, a.account_name, u.name as receiver_name 
            FROM payments p 
            JOIN invoices i ON p.invoice_id = i.id 
            JOIN customers c ON p.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            JOIN finance_accounts a ON p.account_id = a.id 
            LEFT JOIN users u ON p.received_by = u.id 
            WHERE 1=1
        ";
        $params = [];

        if ($method !== '') {
            $sql .= " AND p.payment_method = ?";
            $params[] = $method;
        }

        if ($accountId > 0) {
            $sql .= " AND p.account_id = ?";
            $params[] = $accountId;
        }

        if ($locationId > 0) {
            $sql .= " AND c.location_id = ?";
            $params[] = $locationId;
        }

        if ($picId > 0) {
            $sql .= " AND c.pic_id = ?";
            $params[] = $picId;
        }

        if ($period !== '') {
            $sql .= " AND (p.payment_date LIKE ? OR i.billing_period = ?)";
            $params[] = "{$period}%";
            $params[] = $period;
        }

        if ($startDate !== '') {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $endDate;
        }

        if ($search !== '') {
            $sql .= " AND (p.payment_no LIKE ? OR p.reference_no LIKE ? OR i.invoice_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR l.area_name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        // Calculate total amount collected from filtered results
        $totalCollected = 0;
        foreach ($payments as $pay) {
            $totalCollected += (int)$pay['amount'];
        }

        $pageTitle = 'Riwayat Pembayaran Pelanggan';

        ob_start();
        require __DIR__ . '/../Views/billing/payments.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function receivables(): void {
        AuthMiddleware::handle('billing.view');
        $pdo = getDbConnection();

        // 1. Calculate overall summary statistics for all unpaid invoices
        $allUnpaid = $pdo->query("
            SELECT i.balance_due,
                   CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) as days_overdue
            FROM invoices i 
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue')
        ")->fetchAll();

        $bucket1_7 = 0;
        $bucket8_30 = 0;
        $bucket31_60 = 0;
        $bucketOver60 = 0;
        $totalOverdue = 0;

        foreach ($allUnpaid as $inv) {
            $days = (int)$inv['days_overdue'];
            $due = (int)$inv['balance_due'];
            $totalOverdue += $due;

            if ($days >= 1 && $days <= 7) {
                $bucket1_7 += $due;
            } elseif ($days >= 8 && $days <= 30) {
                $bucket8_30 += $due;
            } elseif ($days >= 31 && $days <= 60) {
                $bucket31_60 += $due;
            } elseif ($days > 60) {
                $bucketOver60 += $due;
            }
        }

        // 2. Fetch master data for filter dropdowns
        $packages = $pdo->query("SELECT id, name FROM internet_packages WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $locations = $pdo->query("SELECT id, area_name, city FROM locations ORDER BY area_name ASC")->fetchAll();
        $pics = $pdo->query("SELECT id, name, position, company FROM customer_pics ORDER BY name ASC")->fetchAll();

        // 3. Process filter parameters
        $search = trim($_GET['search'] ?? '');
        $period = trim($_GET['period'] ?? '');
        $packageId = (int)($_GET['package_id'] ?? 0);
        $locationId = (int)($_GET['location_id'] ?? 0);
        $picId = (int)($_GET['pic_id'] ?? 0);
        $agingBucket = trim($_GET['aging_bucket'] ?? '');

        $whereSql = "";
        $params = [];

        if ($search !== '') {
            $whereSql .= " AND (i.invoice_no LIKE ? OR c.name LIKE ? OR c.customer_no LIKE ? OR c.phone LIKE ? OR l.area_name LIKE ? OR cp.name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if ($period !== '') {
            $whereSql .= " AND i.billing_period = ?";
            $params[] = $period;
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
        }

        if ($agingBucket !== '') {
            if ($agingBucket === 'not_due') {
                $whereSql .= " AND CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) <= 0";
            } elseif ($agingBucket === '1_7') {
                $whereSql .= " AND CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) BETWEEN 1 AND 7";
            } elseif ($agingBucket === '8_30') {
                $whereSql .= " AND CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) BETWEEN 8 AND 30";
            } elseif ($agingBucket === '31_60') {
                $whereSql .= " AND CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) BETWEEN 31 AND 60";
            } elseif ($agingBucket === 'over_60') {
                $whereSql .= " AND CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) > 60";
            }
        }

        // Count total matching unpaid invoices
        $countSql = "
            SELECT COUNT(*) 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue') {$whereSql}
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalUnpaidCount = (int)$countStmt->fetchColumn();

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
            $totalPages = max(1, (int)ceil($totalUnpaidCount / $perPage));
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $perPage;
            $limitClause = " LIMIT {$perPage} OFFSET {$offset}";
        } else {
            $totalPages = 1;
            $offset = 0;
            $limitClause = "";
        }

        $sql = "
            SELECT i.*, c.name as customer_name, c.customer_no, c.phone, c.whatsapp,
                   l.area_name, cp.name as pic_name,
                   CAST((julianday('now') - julianday(i.due_date)) AS INTEGER) as days_overdue
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN locations l ON c.location_id = l.id
            LEFT JOIN customer_pics cp ON c.pic_id = cp.id
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue') {$whereSql}
            ORDER BY days_overdue DESC, i.id DESC
            {$limitClause}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $unpaidInvoices = $stmt->fetchAll();

        $pageTitle = 'Aging Piutang & Collection Rate';

        ob_start();
        require __DIR__ . '/../Views/billing/receivables.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function cycles(): void {
        Helper::redirect('invoices');
    }
}
