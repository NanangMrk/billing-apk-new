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
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $type = trim($_POST['type'] ?? 'expense');
            $amount = (int)str_replace(['.', ',', ' '], '', $_POST['amount'] ?? '0');
            $date = trim($_POST['transaction_date'] ?? date('Y-m-d'));
            $desc = trim($_POST['description'] ?? '');

            if ($accountId <= 0 || $categoryId <= 0 || $amount <= 0 || empty($desc)) {
                Helper::setFlash('error', 'Semua isian transaksi wajib diisi.');
            } else {
                $pdo->beginTransaction();

                $count = (int)$pdo->query("SELECT COUNT(*) FROM finance_transactions")->fetchColumn() + 1;
                $trxNo = 'TRX-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO finance_transactions (transaction_no, transaction_date, account_id, category_id, type, amount, description, reference_type, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'manual', ?)
                ");
                $stmt->execute([$trxNo, $date, $accountId, $categoryId, $type, $amount, $desc, $userId]);

                // Update account balance
                if ($type === 'income') {
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

        $type = trim($_GET['type'] ?? '');
        $accountId = (int)($_GET['account_id'] ?? 0);

        $sql = "
            SELECT t.*, a.account_name, c.name as category_name, u.name as creator_name 
            FROM finance_transactions t 
            JOIN finance_accounts a ON t.account_id = a.id 
            JOIN finance_categories c ON t.category_id = c.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE 1=1
        ";
        $params = [];

        if ($type !== '') {
            $sql .= " AND t.type = ?";
            $params[] = $type;
        }

        if ($accountId > 0) {
            $sql .= " AND t.account_id = ?";
            $params[] = $accountId;
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        $accounts = $pdo->query("SELECT * FROM finance_accounts")->fetchAll();

        $pageTitle = 'Jurnal Transaksi Kas & Bank';

        ob_start();
        require __DIR__ . '/../Views/finance/transactions.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function cashflow(): void {
        AuthMiddleware::handle('finance.view');
        $pdo = getDbConnection();

        $month = trim($_GET['month'] ?? date('Y-m'));
        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // Income by category
        $stmtInc = $pdo->prepare("
            SELECT c.name as category_name, SUM(t.amount) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'income' AND t.transaction_date BETWEEN ? AND ? 
            GROUP BY c.id 
            ORDER BY total_amount DESC
        ");
        $stmtInc->execute([$monthStart, $monthEnd]);
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
        $stmtExp->execute([$monthStart, $monthEnd]);
        $expenses = $stmtExp->fetchAll();

        $totalIncome = array_sum(array_column($incomes, 'total_amount'));
        $totalExpense = array_sum(array_column($expenses, 'total_amount'));
        $netCashflow = $totalIncome - $totalExpense;

        $pageTitle = 'Laporan Arus Kas (Cashflow)';

        ob_start();
        require __DIR__ . '/../Views/finance/cashflow.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
