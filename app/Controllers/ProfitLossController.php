<?php
// app/Controllers/ProfitLossController.php - Real Accounting Profit & Loss Report

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class ProfitLossController {

    public function index(): void {
        AuthMiddleware::handle('profit_loss.view');
        $pdo = getDbConnection();

        $month = trim($_GET['month'] ?? date('Y-m'));
        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // 1. Revenue
        $stmtRev = $pdo->prepare("
            SELECT c.name as category_name, COALESCE(SUM(t.amount), 0) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'income' AND t.transaction_date BETWEEN ? AND ?
            GROUP BY c.id
        ");
        $stmtRev->execute([$monthStart, $monthEnd]);
        $revenues = $stmtRev->fetchAll();
        $totalRevenue = array_sum(array_column($revenues, 'total_amount'));

        // 2. Direct Bandwidth / Upstream Costs (COGS)
        $stmtCogs = $pdo->prepare("
            SELECT c.name as category_name, COALESCE(SUM(t.amount), 0) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'expense' AND (c.name LIKE '%Bandwidth%' OR c.name LIKE '%Upstream%') AND t.transaction_date BETWEEN ? AND ?
            GROUP BY c.id
        ");
        $stmtCogs->execute([$monthStart, $monthEnd]);
        $cogsItems = $stmtCogs->fetchAll();
        $totalCogs = array_sum(array_column($cogsItems, 'total_amount'));
        $grossProfit = $totalRevenue - $totalCogs;

        // 3. Operating Expenses (Electricity, Maintenance, Fuel, etc.)
        $stmtOpex = $pdo->prepare("
            SELECT c.name as category_name, COALESCE(SUM(t.amount), 0) as total_amount 
            FROM finance_transactions t 
            JOIN finance_categories c ON t.category_id = c.id 
            WHERE t.type = 'expense' AND c.name NOT LIKE '%Bandwidth%' AND c.name NOT LIKE '%Upstream%' AND t.transaction_date BETWEEN ? AND ?
            GROUP BY c.id
        ");
        $stmtOpex->execute([$monthStart, $monthEnd]);
        $opexItems = $stmtOpex->fetchAll();
        $totalOpex = array_sum(array_column($opexItems, 'total_amount'));

        // 4. Net Profit
        $netProfit = $grossProfit - $totalOpex;
        $profitMargin = ($totalRevenue > 0) ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        $pageTitle = 'Laporan Laba Rugi (Profit & Loss)';

        ob_start();
        require __DIR__ . '/../Views/profit_loss/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
