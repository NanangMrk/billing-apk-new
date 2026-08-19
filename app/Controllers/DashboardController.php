<?php
// app/Controllers/DashboardController.php - Executive Dashboard Handler with Real Comprehensive Metrics

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class DashboardController {

    public function index(): void {
        AuthMiddleware::handle('dashboard.view');
        $pdo = getDbConnection();

        $isPic = AuthService::isPic();
        $picId = $isPic ? (AuthService::getPicId() ?: -1) : null;
        $currentMonth = date('Y-m');

        // 1. Customer KPIs (Scaped if PIC)
        if ($isPic) {
            $custTotal = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE pic_id = {$picId}")->fetchColumn();
            $custActive = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active' AND pic_id = {$picId}")->fetchColumn();
            $custSuspended = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('suspended', 'isolated') AND pic_id = {$picId}")->fetchColumn();
            $custInactive = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('inactive', 'terminated') AND pic_id = {$picId}")->fetchColumn();
            $picFilterInv = " AND i.customer_id IN (SELECT id FROM customers WHERE pic_id = {$picId}) ";
        } else {
            $custTotal = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
            $custActive = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
            $custSuspended = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('suspended', 'isolated')")->fetchColumn();
            $custInactive = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('inactive', 'terminated')")->fetchColumn();
            $picFilterInv = "";
        }

        // 2. Billing & Receivables KPIs (Current Period)
        $stmtBill = $pdo->prepare("
            SELECT 
                COUNT(*) as total_invoices,
                COALESCE(SUM(grand_total), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as paid_amount,
                COALESCE(SUM(balance_due), 0) as unpaid_amount,
                COALESCE(SUM(CASE WHEN payment_status = 'overdue' OR (payment_status IN ('unpaid', 'partially_paid') AND due_date < date('now')) THEN balance_due ELSE 0 END), 0) as overdue_amount,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END), 0) as paid_count,
                COALESCE(SUM(CASE WHEN payment_status IN ('unpaid', 'partially_paid') THEN 1 ELSE 0 END), 0) as unpaid_count
            FROM invoices i
            WHERE (i.billing_period = ? OR i.billing_period IS NULL) {$picFilterInv}
        ");
        $stmtBill->execute([$currentMonth]);
        $billKpi = $stmtBill->fetch(PDO::FETCH_ASSOC);

        // If no invoices for exact currentMonth, query latest period or all active
        if (($billKpi['total_invoices'] ?? 0) === 0) {
            $stmtBillAll = $pdo->query("
                SELECT 
                    COUNT(*) as total_invoices,
                    COALESCE(SUM(grand_total), 0) as total_amount,
                    COALESCE(SUM(paid_amount), 0) as paid_amount,
                    COALESCE(SUM(balance_due), 0) as unpaid_amount,
                    COALESCE(SUM(CASE WHEN payment_status = 'overdue' OR (payment_status IN ('unpaid', 'partially_paid') AND due_date < date('now')) THEN balance_due ELSE 0 END), 0) as overdue_amount,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END), 0) as paid_count,
                    COALESCE(SUM(CASE WHEN payment_status IN ('unpaid', 'partially_paid') THEN 1 ELSE 0 END), 0) as unpaid_count
                FROM invoices i
                WHERE 1=1 {$picFilterInv}
            ");
            $billKpi = $stmtBillAll->fetch(PDO::FETCH_ASSOC);
        }

        $collectionRate = ($billKpi['total_amount'] > 0) 
            ? round(($billKpi['paid_amount'] / $billKpi['total_amount']) * 100, 1) 
            : 0;

        // 3. Finance KPIs (Cash balance, monthly income, monthly expense)
        $cashBalance = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
        $accountCount = (int)$pdo->query("SELECT COUNT(*) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
        
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
        $finKpi = $stmtFin->fetch(PDO::FETCH_ASSOC);
        $netProfit = (int)$finKpi['total_income'] - (int)$finKpi['total_expense'];

        // 4. Financial Transactions by Category for Dynamic Chart
        $categoryTransactions = $pdo->query("
            SELECT 
                COALESCE(fc.name, 'Lainnya') as category_name,
                ft.type,
                SUM(ft.amount) as total_amount
            FROM finance_transactions ft
            LEFT JOIN finance_categories fc ON ft.category_id = fc.id
            GROUP BY ft.category_id, ft.type
            ORDER BY total_amount DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        $palette = [
            'rgba(124, 58, 237, 0.85)', // Purple
            'rgba(16, 185, 129, 0.85)', // Emerald
            'rgba(239, 68, 68, 0.85)',  // Rose
            'rgba(245, 158, 11, 0.85)', // Amber
            'rgba(59, 130, 246, 0.85)', // Blue
            'rgba(236, 72, 153, 0.85)'  // Pink
        ];
        $colorIdx = 0;

        foreach ($categoryTransactions as $cat) {
            $prefix = ($cat['type'] === 'income') ? '[Pemasukan] ' : '[Beban] ';
            $chartLabels[] = $prefix . $cat['category_name'];
            $chartData[] = (int)$cat['total_amount'];
            $chartColors[] = $palette[$colorIdx % count($palette)];
            $colorIdx++;
        }

        // If no transactions, provide default structure
        if (empty($chartData)) {
            $chartLabels = ['Pendapatan Langganan', 'Beban Bandwidth', 'Beban Operasional'];
            $chartData = [0, 0, 0];
            $chartColors = [$palette[0], $palette[2], $palette[3]];
        }

        // 5. Internet Package Distribution
        $packageDist = $pdo->query("
            SELECT 
                p.name as package_name,
                p.download_speed,
                p.price,
                COUNT(c.id) as user_count
            FROM internet_packages p
            LEFT JOIN customers c ON p.id = c.package_id " . ($isPic ? "AND c.pic_id = {$picId}" : "") . "
            GROUP BY p.id
            ORDER BY user_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 6. Inventory & Asset KPIs
        $inventoryValue = (int)$pdo->query("SELECT COALESCE(SUM(purchase_price * current_stock), 0) FROM inventory_items WHERE status = 'active'")->fetchColumn();
        $assetValue = (int)$pdo->query("SELECT COALESCE(SUM(current_value), 0) FROM assets WHERE status != 'disposed'")->fetchColumn();
        $lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM inventory_items WHERE current_stock <= min_stock AND status = 'active'")->fetchColumn();

        // 7. RAB Projects KPIs
        $rabPendingCount = (int)$pdo->query("SELECT COUNT(*) FROM rabs WHERE status IN ('submitted', 'draft')")->fetchColumn();
        $rabPendingTotal = (int)$pdo->query("SELECT COALESCE(SUM(budget_total), 0) FROM rabs WHERE status IN ('submitted', 'draft')")->fetchColumn();
        $rabActiveCount = (int)$pdo->query("SELECT COUNT(*) FROM rabs WHERE status IN ('approved', 'in_progress')")->fetchColumn();

        // 8. Operations & Tickets
        $openTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open', 'assigned', 'in_progress')")->fetchColumn();

        // 9. Recent Invoices
        $recentInvoices = $pdo->query("
            SELECT i.*, c.name as customer_name, c.customer_no 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            " . ($isPic ? "WHERE c.pic_id = {$picId}" : "") . "
            ORDER BY i.id DESC LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 10. Recent Activity Logs
        $recentLogs = $pdo->query("
            SELECT l.*, u.name as user_name 
            FROM activity_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.id DESC LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 11. AI Provider Status
        $aiSetting = $pdo->query("SELECT provider, model FROM ai_settings WHERE id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $aiProvider = $aiSetting['provider'] ?? 'local';

        $pageTitle = 'Dashboard Utama';

        ob_start();
        require __DIR__ . '/../Views/dashboard/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
