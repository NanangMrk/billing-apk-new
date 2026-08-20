<?php
// app/Controllers/LandingController.php - Public Landing Page Handler

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Services/AuthService.php';

class LandingController {

    public function index(): void {
        if (AuthService::check()) {
            Helper::redirect('dashboard');
        }

        $pdo = getDbConnection();

        // Fetch active internet packages
        $stmtPkg = $pdo->query("SELECT * FROM internet_packages WHERE status = 'active' ORDER BY price ASC");
        $packages = $stmtPkg->fetchAll();

        // Fetch covered locations
        $stmtLoc = $pdo->query("SELECT * FROM locations ORDER BY city ASC, area_name ASC LIMIT 6");
        $locations = $stmtLoc->fetchAll();

        // Render landing view inside landing layout
        ob_start();
        require __DIR__ . '/../Views/landing/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/landing.php';
    }
}
