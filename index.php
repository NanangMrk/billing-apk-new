<?php
// index.php - Main Application Router & Entry Point

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Core Services & Helpers
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/Helper.php';
require_once __DIR__ . '/app/Services/AuthService.php';
require_once __DIR__ . '/app/Middleware/AuthMiddleware.php';

// Controllers
require_once __DIR__ . '/app/Controllers/LandingController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/CustomerController.php';
require_once __DIR__ . '/app/Controllers/BillingController.php';
require_once __DIR__ . '/app/Controllers/FinanceController.php';
require_once __DIR__ . '/app/Controllers/PayrollController.php';
require_once __DIR__ . '/app/Controllers/RabController.php';
require_once __DIR__ . '/app/Controllers/InventoryController.php';
require_once __DIR__ . '/app/Controllers/AssetController.php';
require_once __DIR__ . '/app/Controllers/ProfitLossController.php';
require_once __DIR__ . '/app/Controllers/AiController.php';
require_once __DIR__ . '/app/Controllers/TicketController.php';
require_once __DIR__ . '/app/Controllers/SettingController.php';

// Route Dispatcher
$page = $_GET['page'] ?? 'landing';

switch ($page) {
    // 1. Landing & Auth
    case 'landing':
        (new LandingController())->index();
        break;
    case 'login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    // 2. Executive Dashboard
    case 'dashboard':
        (new DashboardController())->index();
        break;

    // 3. Customers, Packages, Locations, PIC
    case 'customers':
        (new CustomerController())->index();
        break;
    case 'customers_create':
        (new CustomerController())->create();
        break;
    case 'customers_export_csv':
        (new CustomerController())->exportCsv();
        break;
    case 'customers_import_csv':
        (new CustomerController())->importCsv();
        break;
    case 'customers_download_template':
        (new CustomerController())->downloadTemplateCsv();
        break;
    case 'packages':
        (new CustomerController())->packages();
        break;
    case 'locations':
        (new CustomerController())->locations();
        break;
    case 'pics':
        (new CustomerController())->pics();
        break;

    // 4. Billing, Invoices, Payments, Aging
    case 'invoices':
        (new BillingController())->invoices();
        break;
    case 'create_invoice':
        (new BillingController())->createInvoice();
        break;
    case 'show_invoice':
        (new BillingController())->showInvoice();
        break;
    case 'record_payment':
        (new BillingController())->recordPayment();
        break;
    case 'payments':
        (new BillingController())->payments();
        break;
    case 'receivables':
        (new BillingController())->receivables();
        break;
    case 'billing_cycles':
        (new BillingController())->cycles();
        break;

    // 5. Finance & Cashflow
    case 'finance':
        (new FinanceController())->index();
        break;
    case 'transactions':
        (new FinanceController())->transactions();
        break;
    case 'transactions_export_csv':
        (new FinanceController())->exportTransactionsCsv();
        break;
    case 'transactions_import_csv':
        (new FinanceController())->importTransactionsCsv();
        break;
    case 'transactions_download_template':
        (new FinanceController())->downloadTransactionsTemplateCsv();
        break;
    case 'cashflow':
        (new FinanceController())->cashflow();
        break;

    // 6. Payroll & Employees
    case 'payroll':
        (new PayrollController())->index();
        break;

    // 7. RAB Proyek
    case 'rab':
        (new RabController())->index();
        break;

    // 8. Inventory & Assets
    case 'inventory':
        (new InventoryController())->index();
        break;
    case 'goods_in':
        (new InventoryController())->goodsIn();
        break;
    case 'goods_out':
        (new InventoryController())->goodsOut();
        break;
    case 'suppliers':
        (new InventoryController())->suppliers();
        break;
    case 'assets':
        (new AssetController())->index();
        break;
    case 'assets_export_csv':
        (new AssetController())->exportCsv();
        break;
    case 'assets_export_pdf':
        (new AssetController())->exportPdf();
        break;
    case 'assets_download_template':
        (new AssetController())->downloadTemplateCsv();
        break;
    case 'assets_import_csv':
        (new AssetController())->importCsv();
        break;

    // 9. Profit & Loss
    case 'profit_loss':
        (new ProfitLossController())->index();
        break;

    // 10. AI Assistant
    case 'ai':
        (new AiController())->index();
        break;

    // 11. Ticketing
    case 'tickets':
        (new TicketController())->index();
        break;

    // 12. Settings & Logs
    case 'settings_company':
        (new SettingController())->company();
        break;
    case 'settings_users':
        (new SettingController())->users();
        break;
    case 'settings_logs':
        (new SettingController())->auditLogs();
        break;

    default:
        (new LandingController())->index();
        break;
}
