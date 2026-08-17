<?php
// app/Controllers/PayrollController.php - Employee Payroll Management

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class PayrollController {

    public function index(): void {
        AuthMiddleware::handle('payroll.view');
        $pdo = getDbConnection();

        $employees = $pdo->query("
            SELECT e.*, d.name as department_name 
            FROM employees e 
            LEFT JOIN departments d ON e.department_id = d.id 
            ORDER BY e.id ASC
        ")->fetchAll();

        $totalPayrollCost = array_sum(array_column($employees, 'basic_salary'));

        $pageTitle = 'Payroll & Penggajian Karyawan';

        ob_start();
        require __DIR__ . '/../Views/payroll/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
