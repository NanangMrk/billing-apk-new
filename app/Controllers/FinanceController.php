<?php
// app/Controllers/FinanceController.php - Cash & Bank, Transactions, Cashflow, and Ledger

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class FinanceController {

    public function index(): void {
        AuthMiddleware::handle('finance.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_transaction') {
            AuthMiddleware::handle('finance.create');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('finance');
            }

            $accountId = (int)($_POST['account_id'] ?? 0);
            $categoryText = trim($_POST['category_id'] ?? '');
            $type = trim($_POST['type'] ?? 'expense');
            $amount = (int)str_replace(['.', ',', ' '], '', $_POST['amount'] ?? '0');
            $date = trim($_POST['transaction_date'] ?? date('Y-m-d'));
            $desc = trim($_POST['description'] ?? '');

            // Resolve or auto-create category
            $categoryId = 0;
            if (!empty($categoryText)) {
                $catStmt = $pdo->prepare("SELECT id FROM finance_categories WHERE name = ? AND type = ? LIMIT 1");
                $catStmt->execute([$categoryText, $type]);
                $catRow = $catStmt->fetchColumn();
                if ($catRow) {
                    $categoryId = (int)$catRow;
                } else {
                    $insStmt = $pdo->prepare("INSERT INTO finance_categories (name, type) VALUES (?, ?)");
                    $insStmt->execute([$categoryText, $type]);
                    $categoryId = (int)$pdo->lastInsertId();
                }
            }

            if ($accountId <= 0 || $amount <= 0) {
                Helper::setFlash('error', 'Rekening dan Nominal wajib diisi.');
            } else {
                // Handle image upload
                $attachmentPath = null;
                if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $ftype = mime_content_type($_FILES['attachment']['tmp_name']);
                    if (in_array($ftype, $allowedTypes) && $_FILES['attachment']['size'] <= 5 * 1024 * 1024) {
                        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                        $filename = 'trx_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                        $uploadDir = __DIR__ . '/../../public/uploads/transactions/';
                        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                            $attachmentPath = 'uploads/transactions/' . $filename;
                        }
                    }
                }

                $pdo->beginTransaction();

                $count = (int)$pdo->query("SELECT COUNT(*) FROM finance_transactions")->fetchColumn() + 1;
                $trxNo = 'TRX-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO finance_transactions (transaction_no, transaction_date, account_id, category_id, type, amount, description, attachment, reference_type, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'manual', ?)
                ");
                $stmt->execute([$trxNo, $date, $accountId, $categoryId ?: null, $type, $amount, $desc, $attachmentPath, $userId]);

                // Update account balance
                if (in_array($type, ['income', 'debt'])) {
                    $stmtAcc = $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?");
                } else {
                    $stmtAcc = $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?");
                }
                $stmtAcc->execute([$amount, $accountId]);

                $pdo->commit();

                Helper::logActivity('FINANCE', 'RECORD_TRX', $trxNo, null, "Recorded $type Rp " . number_format($amount, 0, ',', '.') . " ($desc)");
                Helper::setFlash('success', "Transaksi $trxNo berhasil dicatat.");
                Helper::redirect('transactions');
            }
        }

        $accounts = $pdo->query("SELECT * FROM finance_accounts ORDER BY id ASC")->fetchAll();
        $categories = $pdo->query("SELECT * FROM finance_categories ORDER BY type ASC, name ASC")->fetchAll();

        $pageTitle = 'Kas & Rekening Bank';

        ob_start();
        require __DIR__ . '/../Views/finance/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function transactions(): void {
        AuthMiddleware::handle('finance.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            AuthMiddleware::handle('finance.create'); // or manage
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('transactions');
            }

            $action = $_POST['action'];

            if ($action === 'update_transaction') {
                $id = (int)($_POST['id'] ?? 0);
                $accountId = (int)($_POST['account_id'] ?? 0);
                $categoryText = trim($_POST['category_id'] ?? '');
                $amount = (int)str_replace(['.', ',', ' '], '', $_POST['amount'] ?? '0');
                $date = trim($_POST['transaction_date'] ?? date('Y-m-d'));
                $desc = trim($_POST['description'] ?? '');

                if ($id > 0 && $accountId > 0 && $amount > 0) {
                    $oldTrx = $pdo->query("SELECT * FROM finance_transactions WHERE id = {$id}")->fetch();
                    if ($oldTrx) {
                        // Resolve or auto-create category
                        $categoryId = $oldTrx['category_id']; // default: keep existing
                        if (!empty($categoryText)) {
                            $type = $oldTrx['type'];
                            $catStmt = $pdo->prepare("SELECT id FROM finance_categories WHERE name = ? AND type = ? LIMIT 1");
                            $catStmt->execute([$categoryText, $type]);
                            $catRow = $catStmt->fetchColumn();
                            if ($catRow) {
                                $categoryId = (int)$catRow;
                            } else {
                                $insStmt = $pdo->prepare("INSERT INTO finance_categories (name, type) VALUES (?, ?)");
                                $insStmt->execute([$categoryText, $type]);
                                $categoryId = (int)$pdo->lastInsertId();
                            }
                        }

                        $pdo->beginTransaction();

                        // Revert old transaction
                        if (in_array($oldTrx['type'], ['income', 'debt'])) {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        } else {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        }

                        // Apply new transaction (keep same type)
                        $type = $oldTrx['type'];
                        if (in_array($type, ['income', 'debt'])) {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$amount, $accountId]);
                        } else {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$amount, $accountId]);
                        }

                        $stmt = $pdo->prepare("UPDATE finance_transactions SET account_id = ?, category_id = ?, amount = ?, transaction_date = ?, description = ? WHERE id = ?");
                        $stmt->execute([$accountId, $categoryId, $amount, $date, $desc, $id]);

                        $pdo->commit();
                        Helper::logActivity('FINANCE', 'UPDATE_TRX', (string)$id, null, "Updated transaction #$id");
                        Helper::setFlash('success', "Transaksi berhasil diperbarui.");
                    }
                }
                Helper::redirect('transactions');
            }

            if ($action === 'batch_delete_transactions') {
                $ids = $_POST['trx_ids'] ?? [];
                if (!is_array($ids)) {
                    $ids = explode(',', (string)$ids);
                }
                $ids = array_filter(array_map('intval', $ids));
                if (!empty($ids)) {
                    $pdo->beginTransaction();
                    $inClause = implode(',', array_fill(0, count($ids), '?'));
                    $stmtTrx = $pdo->prepare("SELECT * FROM finance_transactions WHERE id IN ($inClause)");
                    $stmtTrx->execute($ids);
                    $records = $stmtTrx->fetchAll();

                    foreach ($records as $oldTrx) {
                        if (in_array($oldTrx['type'], ['income', 'debt'])) {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        } else {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        }
                    }

                    $stmtDel = $pdo->prepare("DELETE FROM finance_transactions WHERE id IN ($inClause)");
                    $stmtDel->execute($ids);
                    $pdo->commit();

                    Helper::logActivity('FINANCE', 'BATCH_DELETE_TRX', implode(',', $ids), null, "Batch deleted " . count($ids) . " transactions");
                    Helper::setFlash('success', count($ids) . " transaksi berhasil dihapus.");
                }
                Helper::redirect('transactions');
            }

            if ($action === 'delete_transaction') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $oldTrx = $pdo->query("SELECT * FROM finance_transactions WHERE id = {$id}")->fetch();
                    if ($oldTrx) {
                        $pdo->beginTransaction();
                        // Revert balance
                        if (in_array($oldTrx['type'], ['income', 'debt'])) {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        } else {
                            $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$oldTrx['amount'], $oldTrx['account_id']]);
                        }
                        
                        $pdo->prepare("DELETE FROM finance_transactions WHERE id = ?")->execute([$id]);
                        $pdo->commit();
                        Helper::logActivity('FINANCE', 'DELETE_TRX', (string)$id, null, "Deleted transaction #$id");
                        Helper::setFlash('success', "Transaksi berhasil dihapus.");
                    }
                }
                Helper::redirect('transactions');
            }
        }

        $filterMonth = trim($_GET['month'] ?? date('Y-m'));
        $accountId = (int)($_GET['account_id'] ?? 0);
        $searchQuery = trim($_GET['search'] ?? '');

        $sql = "
            SELECT t.*, a.account_name, c.name as category_name, u.name as creator_name 
            FROM finance_transactions t 
            JOIN finance_accounts a ON t.account_id = a.id 
            LEFT JOIN finance_categories c ON t.category_id = c.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE strftime('%Y-%m', t.transaction_date) = ?
        ";
        $params = [$filterMonth];

        if ($accountId > 0) {
            $sql .= " AND t.account_id = ?";
            $params[] = $accountId;
        }

        if ($searchQuery !== '') {
            $sql .= " AND (t.description LIKE ? OR t.transaction_no LIKE ? OR c.name LIKE ?)";
            $searchLike = "%{$searchQuery}%";
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        $accounts = $pdo->query("SELECT * FROM finance_accounts")->fetchAll();
        $categories = $pdo->query("SELECT * FROM finance_categories ORDER BY type ASC, name ASC")->fetchAll();


        $pageTitle = 'Jurnal Transaksi Kas & Bank';

        ob_start();
        require __DIR__ . '/../Views/finance/transactions.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function cashflow(): void {
        AuthMiddleware::handle('finance.view');
        $pdo = getDbConnection();

        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate = trim($_GET['end_date'] ?? date('Y-m-t'));

        // Income by category
        $stmtInc = $pdo->prepare("
            SELECT c.name as category_name, SUM(t.amount) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'income' AND t.transaction_date BETWEEN ? AND ? 
            GROUP BY c.id 
            ORDER BY total_amount DESC
        ");
        $stmtInc->execute([$startDate, $endDate]);
        $incomes = $stmtInc->fetchAll();

        // Expense by category
        $stmtExp = $pdo->prepare("
            SELECT c.name as category_name, SUM(t.amount) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'expense' AND t.transaction_date BETWEEN ? AND ? 
            GROUP BY c.id 
            ORDER BY total_amount DESC
        ");
        $stmtExp->execute([$startDate, $endDate]);
        $expenses = $stmtExp->fetchAll();

        // Totals
        $totalIncome = array_sum(array_column($incomes, 'total_amount'));
        $totalExpense = array_sum(array_column($expenses, 'total_amount'));
        $netCashflow = $totalIncome - $totalExpense;

        // 1. Total Saldo (All Accounts)
        $totalSaldo = (int)$pdo->query("SELECT SUM(current_balance) FROM finance_accounts")->fetchColumn();

        // 2. Total Hutang (Filtered by month, or all time? Let's do month as requested, or all unpaid. For now, month filter)
        $stmtDebt = $pdo->prepare("SELECT SUM(amount) FROM finance_transactions WHERE type = 'debt' AND transaction_date BETWEEN ? AND ?");
        $stmtDebt->execute([$startDate, $endDate]);
        $totalDebt = (int)$stmtDebt->fetchColumn();

        // 3. Barang Masuk (Inventory IN value for the month)
        $stmtInv = $pdo->prepare("SELECT SUM(total_amount) FROM inventory_transactions WHERE type = 'in' AND transaction_date BETWEEN ? AND ?");
        $stmtInv->execute([$startDate, $endDate]);
        $totalInventoryIn = (int)$stmtInv->fetchColumn();

        // 4. RAB Budget & Realisasi (For projects active or overlapping in the month)
        $stmtRab = $pdo->prepare("SELECT SUM(budget_total) as total_budget, SUM(realized_total) as total_realized FROM rabs WHERE DATE(start_date) BETWEEN ? AND ?");
        $stmtRab->execute([$startDate, $endDate]);
        $rabData = $stmtRab->fetch();
        $totalRabBudget = (int)($rabData['total_budget'] ?? 0);
        $totalRabRealized = (int)($rabData['total_realized'] ?? 0);

        // 5. Customer Statistics
        $customerStats = $pdo->query("SELECT status, COUNT(*) as count FROM customers GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $totalCustomers = array_sum($customerStats);
        $activeCustomers = $customerStats['active'] ?? 0;
        $suspendedCustomers = $customerStats['suspended'] ?? 0;

        // 6. Payroll Total
        $stmtPayroll = $pdo->prepare("SELECT SUM(total_amount) FROM payroll_runs WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmtPayroll->execute([$startDate, $endDate]);
        $totalPayroll = (int)$stmtPayroll->fetchColumn();

        // 7. Receivables (Piutang)
        $stmtRecv = $pdo->prepare("SELECT SUM(amount) FROM finance_transactions WHERE type = 'receivable' AND transaction_date BETWEEN ? AND ?");
        $stmtRecv->execute([$startDate, $endDate]);
        $totalReceivables = (int)$stmtRecv->fetchColumn();

        // Details for Tables
        $stmtIncDetail = $pdo->prepare("
            SELECT t.*, c.name as category_name, a.account_name 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            JOIN finance_accounts a ON t.account_id = a.id
            WHERE t.type = 'income' AND t.transaction_date BETWEEN ? AND ? 
            ORDER BY t.transaction_date DESC
        ");
        $stmtIncDetail->execute([$startDate, $endDate]);
        $incomesDetail = $stmtIncDetail->fetchAll();

        $stmtExpDetail = $pdo->prepare("
            SELECT t.*, c.name as category_name, a.account_name 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            JOIN finance_accounts a ON t.account_id = a.id
            WHERE t.type = 'expense' AND t.transaction_date BETWEEN ? AND ? 
            ORDER BY t.transaction_date DESC
        ");
        $stmtExpDetail->execute([$startDate, $endDate]);
        $expensesDetail = $stmtExpDetail->fetchAll();

        $stmtDebtDetail = $pdo->prepare("
            SELECT t.*, c.name as category_name, a.account_name 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            JOIN finance_accounts a ON t.account_id = a.id
            WHERE t.type = 'debt' AND t.transaction_date BETWEEN ? AND ? 
            ORDER BY t.transaction_date DESC
        ");
        $stmtDebtDetail->execute([$startDate, $endDate]);
        $debtsDetail = $stmtDebtDetail->fetchAll();


        $pageTitle = 'Dashboard Keuangan & Operasional';

        ob_start();
        require __DIR__ . '/../Views/finance/cashflow.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Export Transactions to CSV Format
     */
    public function exportTransactionsCsv(): void {
        AuthMiddleware::handle('finance.view');
        $pdo = getDbConnection();

        $filterMonth = trim($_GET['month'] ?? '');
        $accountId = (int)($_GET['account_id'] ?? 0);
        $searchQuery = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');

        $idsParam = trim($_GET['ids'] ?? '');
        if (!empty($idsParam)) {
            $idsArr = array_filter(array_map('intval', explode(',', $idsParam)));
            if (!empty($idsArr)) {
                $inClause = implode(',', array_fill(0, count($idsArr), '?'));
                $sql = "
                    SELECT t.*, a.account_name, a.bank_name, c.name as category_name, u.name as creator_name 
                    FROM finance_transactions t 
                    JOIN finance_accounts a ON t.account_id = a.id 
                    LEFT JOIN finance_categories c ON t.category_id = c.id 
                    LEFT JOIN users u ON t.created_by = u.id 
                    WHERE t.id IN ($inClause)
                    ORDER BY t.transaction_date DESC, t.id DESC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($idsArr);
                $transactions = $stmt->fetchAll();
            } else {
                $transactions = [];
            }
        } else {
            $sql = "
                SELECT t.*, a.account_name, a.bank_name, c.name as category_name, u.name as creator_name 
                FROM finance_transactions t 
                JOIN finance_accounts a ON t.account_id = a.id 
                LEFT JOIN finance_categories c ON t.category_id = c.id 
                LEFT JOIN users u ON t.created_by = u.id 
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filterMonth)) {
                $sql .= " AND strftime('%Y-%m', t.transaction_date) = ?";
                $params[] = $filterMonth;
            }

            if ($accountId > 0) {
                $sql .= " AND t.account_id = ?";
                $params[] = $accountId;
            }

            if (!empty($type)) {
                $sql .= " AND t.type = ?";
                $params[] = $type;
            }

            if ($searchQuery !== '') {
                $sql .= " AND (t.description LIKE ? OR t.transaction_no LIKE ? OR c.name LIKE ?)";
                $searchLike = "%{$searchQuery}%";
                $params[] = $searchLike;
                $params[] = $searchLike;
                $params[] = $searchLike;
            }

            $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll();
        }

        $filename = 'Jurnal_Transaksi_Kas_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'No. Transaksi',
            'Tanggal',
            'Tipe Transaksi',
            'Rekening Kas / Bank',
            'Kategori',
            'Nominal (Rp)',
            'Keterangan / Deskripsi',
            'Petugas (Created By)',
            'Tipe Referensi',
            'Waktu Input'
        ]);

        $typeLabels = [
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            'debt' => 'Catatan Hutang',
            'receivable' => 'Catatan Piutang'
        ];

        foreach ($transactions as $t) {
            $typeText = $typeLabels[$t['type']] ?? ucfirst($t['type']);
            fputcsv($out, [
                $t['transaction_no'],
                $t['transaction_date'],
                $typeText,
                $t['account_name'] . (!empty($t['bank_name']) ? " ({$t['bank_name']})" : ''),
                $t['category_name'] ?: '-',
                $t['amount'],
                $t['description'] ?: '-',
                $t['creator_name'] ?: 'System',
                $t['reference_type'] ?: 'manual',
                $t['created_at'] ?: '-'
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Download Sample CSV Template for Importing Transactions (with 5 sample rows)
     */
    public function downloadTransactionsTemplateCsv(): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Template_Import_Transaksi_Kas.csv"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Tanggal (YYYY-MM-DD)',
            'Tipe',
            'Rekening Kas / Bank',
            'Kategori',
            'Nominal (Rp)',
            'Keterangan'
        ]);

        // Sample 1: Pemasukan Langganan
        fputcsv($out, [
            '2026-08-10',
            'Pemasukan',
            'Bank BCA Operasional',
            'Pendapatan Langganan Internet',
            '2500000',
            'Pembayaran langganan internet dedicated 5 pelanggan corporate'
        ]);

        // Sample 2: Pengeluaran Operasional & BBM
        fputcsv($out, [
            '2026-08-11',
            'Pengeluaran',
            'Kas Tunai Kantor',
            'Beban Operasional & BBM Teknisi',
            '350000',
            'BBM operasional teknisi penarikan kabel FO wilayah Depok'
        ]);

        // Sample 3: Pengeluaran Upstream Bandwidth
        fputcsv($out, [
            '2026-08-12',
            'Pengeluaran',
            'Bank Mandiri Penerimaan',
            'Beban Bandwidth / Upstream ISP',
            '12500000',
            'Pembayaran tagihan upstream bandwidth IP Transit 1 Gbps'
        ]);

        // Sample 4: Catatan Hutang Pengadaan Alat
        fputcsv($out, [
            '2026-08-13',
            'Catatan Hutang',
            'Bank Mandiri Penerimaan',
            'Hutang Supplier',
            '8500000',
            'Hutang pengadaan 50 unit modem ONT GPON ke Supplier Multi Data'
        ]);

        // Sample 5: Catatan Piutang Instalasi Pelanggan
        fputcsv($out, [
            '2026-08-14',
            'Catatan Piutang',
            'Bank BCA Operasional',
            'Catatan Piutang Pelanggan',
            '4500000',
            'Tagihan instalasi fiber optic PT Maju Berkarya (termin 2)'
        ]);

        fclose($out);
        exit;
    }

    /**
     * Import Transactions from CSV File
     */
    public function importTransactionsCsv(): void {
        AuthMiddleware::handle('finance.create');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('transactions');
        }

        if (!Helper::verifyCsrf()) {
            Helper::setFlash('error', 'Token CSRF tidak valid.');
            Helper::redirect('transactions');
        }

        if (!isset($_FILES['csv_file']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            Helper::setFlash('error', 'Silakan pilih file CSV yang valid untuk diimpor.');
            Helper::redirect('transactions');
        }

        $filepath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            Helper::setFlash('error', 'Gagal membaca file CSV.');
            Helper::redirect('transactions');
        }

        // Read header (and handle BOM if present)
        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            Helper::setFlash('error', 'File CSV kosong atau format tidak sesuai.');
            Helper::redirect('transactions');
        }

        // Cache accounts for quick lookup
        $allAccounts = $pdo->query("SELECT id, account_name, bank_name FROM finance_accounts")->fetchAll();
        $defaultAccountId = $allAccounts[0]['id'] ?? 1;

        $imported = 0;
        $skipped = 0;
        $duplicates = 0;
        $userId = AuthService::user()['id'] ?? null;

        $pdo->beginTransaction();

        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM finance_transactions");
            $lastCount = (int)$countStmt->fetchColumn();

            // Prepare duplicate check statement
            $dupStmt = $pdo->prepare("
                SELECT id FROM finance_transactions 
                WHERE transaction_date = ? 
                  AND account_id = ? 
                  AND type = ? 
                  AND amount = ? 
                  AND (description = ? OR (description = '' AND ? = ''))
                LIMIT 1
            ");

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (empty(array_filter($row))) continue; // skip blank rows

                $rawDate = trim($row[0] ?? '');
                $rawType = trim(strtolower($row[1] ?? ''));
                $rawAccount = trim($row[2] ?? '');
                $rawCategory = trim($row[3] ?? '');
                $rawAmount = trim($row[4] ?? '0');
                $rawDesc = trim($row[5] ?? '');

                // 1. Clean & Validate Amount
                $amount = (int)str_replace(['Rp', 'rp', 'RP', '.', ',', ' '], '', $rawAmount);
                if ($amount <= 0) {
                    $skipped++;
                    continue;
                }

                // 2. Validate & Format Date
                $date = date('Y-m-d');
                if (!empty($rawDate)) {
                    $timestamp = strtotime(str_replace('/', '-', $rawDate));
                    if ($timestamp !== false) {
                        $date = date('Y-m-d', $timestamp);
                    }
                }

                // 3. Normalize Type
                $type = 'expense';
                if (str_contains($rawType, 'pemasukan') || str_contains($rawType, 'income') || str_contains($rawType, 'masuk') || str_contains($rawType, 'terima')) {
                    $type = 'income';
                } elseif (str_contains($rawType, 'hutang') || str_contains($rawType, 'debt') || str_contains($rawType, 'utang')) {
                    $type = 'debt';
                } elseif (str_contains($rawType, 'piutang') || str_contains($rawType, 'receivable') || str_contains($rawType, 'tagih')) {
                    $type = 'receivable';
                } elseif (str_contains($rawType, 'pengeluaran') || str_contains($rawType, 'expense') || str_contains($rawType, 'beban') || str_contains($rawType, 'keluar') || str_contains($rawType, 'biaya')) {
                    $type = 'expense';
                }

                // 4. Resolve Account
                $accountId = $defaultAccountId;
                if (!empty($rawAccount)) {
                    if (is_numeric($rawAccount)) {
                        $matchId = (int)$rawAccount;
                        foreach ($allAccounts as $acc) {
                            if ((int)$acc['id'] === $matchId) {
                                $accountId = $matchId;
                                break;
                            }
                        }
                    } else {
                        foreach ($allAccounts as $acc) {
                            if (stripos($acc['account_name'], $rawAccount) !== false || (!empty($acc['bank_name']) && stripos($acc['bank_name'], $rawAccount) !== false)) {
                                $accountId = $acc['id'];
                                break;
                            }
                        }
                    }
                }

                // 5. Check Duplicate Transaction (Skip if identical data already exists)
                $dupStmt->execute([$date, $accountId, $type, $amount, $rawDesc, $rawDesc]);
                $isDuplicate = $dupStmt->fetch();
                if ($isDuplicate) {
                    $duplicates++;
                    continue; // Skip this record, do not insert and do not modify existing data
                }

                // 6. Resolve or Auto-Create Category
                $categoryId = null;
                if (!empty($rawCategory)) {
                    $catStmt = $pdo->prepare("SELECT id FROM finance_categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
                    $catStmt->execute([$rawCategory]);
                    $catId = $catStmt->fetchColumn();
                    if ($catId) {
                        $categoryId = (int)$catId;
                    } else {
                        $insCat = $pdo->prepare("INSERT INTO finance_categories (name, type) VALUES (?, ?)");
                        $insCat->execute([$rawCategory, $type]);
                        $categoryId = (int)$pdo->lastInsertId();
                    }
                }

                // 7. Generate Transaction Number
                $lastCount++;
                $trxNo = 'TRX-' . date('Ym', strtotime($date)) . '-' . str_pad((string)$lastCount, 6, '0', STR_PAD_LEFT);

                // Check collision if any
                $chk = $pdo->prepare("SELECT id FROM finance_transactions WHERE transaction_no = ?");
                $chk->execute([$trxNo]);
                if ($chk->fetch()) {
                    $trxNo = 'TRX-' . date('Ym', strtotime($date)) . '-' . str_pad((string)($lastCount + rand(10, 99)), 6, '0', STR_PAD_LEFT);
                }

                // 8. Insert Transaction
                $stmt = $pdo->prepare("
                    INSERT INTO finance_transactions (transaction_no, transaction_date, account_id, category_id, type, amount, description, reference_type, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'manual', ?)
                ");
                $stmt->execute([$trxNo, $date, $accountId, $categoryId, $type, $amount, $rawDesc ?: "Import CSV {$type}", $userId]);

                // 9. Update Account Balance
                if (in_array($type, ['income', 'debt'])) {
                    $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$amount, $accountId]);
                } else {
                    $pdo->prepare("UPDATE finance_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$amount, $accountId]);
                }

                $imported++;
            }

            $pdo->commit();
            fclose($handle);

            Helper::logActivity('FINANCE', 'IMPORT_CSV', 'BULK', null, "Imported {$imported} transactions, {$duplicates} duplicates skipped");

            if ($imported > 0) {
                $msg = "Berhasil mengimpor {$imported} data transaksi baru.";
                if ($duplicates > 0) {
                    $msg .= " ({$duplicates} transaksi yang sama dilewati agar tidak duplikat).";
                }
                if ($skipped > 0) {
                    $msg .= " ({$skipped} baris kosong/tidak valid dilewati).";
                }
                Helper::setFlash('success', $msg);
            } elseif ($duplicates > 0) {
                Helper::setFlash('info', "Semua data ({$duplicates} transaksi) sudah ada di sistem dan dilewati tanpa mengubah data yang ada.");
            } else {
                Helper::setFlash('warning', "Tidak ada transaksi valid yang dapat diimpor.");
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            Helper::setFlash('error', 'Terjadi kesalahan saat mengimpor CSV: ' . $e->getMessage());
        }

        Helper::redirect('transactions');
    }
}

