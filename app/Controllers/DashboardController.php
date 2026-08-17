<?php
// app/Controllers/DashboardController.php - Executive Dashboard Handler

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class DashboardController {

    public function index(): void {
        AuthMiddleware::handle('dashboard.view');
        $pdo = getDbConnection();

        // 1. Customer KPIs
        $custTotal = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $custActive = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
        $custSuspended = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'suspended'")->fetchColumn();

        // 2. Billing KPIs (Current Month)
        $currentMonth = date('Y-m');
        $stmtBill = $pdo->prepare("
            SELECT 
                COUNT(*) as total_invoices,
                COALESCE(SUM(grand_total), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as paid_amount,
                COALESCE(SUM(balance_due), 0) as unpaid_amount,
                COALESCE(SUM(CASE WHEN payment_status = 'overdue' THEN balance_due ELSE 0 END), 0) as overdue_amount
            FROM invoices 
            WHERE billing_period = ?
        ");
        $stmtBill->execute([$currentMonth]);
        $billKpi = $stmtBill->fetch();

        // 3. Finance KPIs (Cash balance, monthly income, monthly expense)
        $cashBalance = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
        
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $stmtFin = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            FROM finance_transactions
            WHERE transaction_date BETWEEN ? AND ?
        ");
        $stmtFin->execute([$monthStart, $monthEnd]);
        $finKpi = $stmtFin->fetch();
        $netProfit = $finKpi['total_income'] - $finKpi['total_expense'];

        // 4. Inventory & Asset KPIs
        $inventoryValue = (int)$pdo->query("SELECT COALESCE(SUM(purchase_price * current_stock), 0) FROM inventory_items WHERE status = 'active'")->fetchColumn();
        $assetValue = (int)$pdo->query("SELECT COALESCE(SUM(current_value), 0) FROM assets WHERE status != 'disposed'")->fetchColumn();
        $lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM inventory_items WHERE current_stock <= min_stock AND status = 'active'")->fetchColumn();

        // 5. Operations & Tickets
        $openTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open', 'assigned', 'in_progress')")->fetchColumn();

        // 6. Recent Invoices
        $recentInvoices = $pdo->query("
            SELECT i.*, c.name as customer_name, c.customer_no 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            ORDER BY i.id DESC LIMIT 5
        ")->fetchAll();

        // 7. Recent Activity Logs
        $recentLogs = $pdo->query("
            SELECT l.*, u.name as user_name 
            FROM activity_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.id DESC LIMIT 6
        ")->fetchAll();

        $pageTitle = 'Dashboard Utama';

        ob_start();
        require __DIR__ . '/../Views/dashboard/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
