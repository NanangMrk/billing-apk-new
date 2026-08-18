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
}
