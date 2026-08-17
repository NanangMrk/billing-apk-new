<?php
// app/Controllers/RabController.php - RAB Project Budgeting and Realization

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class RabController {

    public function index(): void {
        AuthMiddleware::handle('rab.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_rab') {
            AuthMiddleware::handle('rab.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('rab');
            }

            $projectName = trim($_POST['project_name'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 1);
            $location = trim($_POST['location'] ?? '');
            $picName = trim($_POST['pic_name'] ?? '');
            $budgetTotal = (int)str_replace(['.', ',', ' '], '', $_POST['budget_total'] ?? '0');
            $startDate = trim($_POST['start_date'] ?? date('Y-m-d'));
            $endDate = trim($_POST['end_date'] ?? date('Y-m-d', strtotime('+14 days')));
            $desc = trim($_POST['description'] ?? '');

            if (!empty($projectName) && $budgetTotal > 0) {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM rabs")->fetchColumn() + 1;
                $rabNo = 'RAB-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO rabs (rab_no, project_name, category_id, location, pic_name, start_date, end_date, budget_total, realized_total, status, description, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'submitted', ?, ?)
                ");
                $stmt->execute([$rabNo, $projectName, $categoryId, $location, $picName, $startDate, $endDate, $budgetTotal, $desc, $userId]);

                Helper::logActivity('RAB', 'CREATE_RAB', $rabNo, null, "Created RAB $rabNo: $projectName");
                Helper::setFlash('success', "RAB $rabNo ($projectName) berhasil diajukan.");
            }
            Helper::redirect('rab');
        }

        $rabs = $pdo->query("
            SELECT r.*, c.name as category_name, u.name as creator_name 
            FROM rabs r 
            LEFT JOIN rab_categories c ON r.category_id = c.id 
            LEFT JOIN users u ON r.created_by = u.id 
            ORDER BY r.id DESC
        ")->fetchAll();

        $categories = $pdo->query("SELECT * FROM rab_categories")->fetchAll();

        $pageTitle = 'RAB (Rencana Anggaran Biaya) Proyek';

        ob_start();
        require __DIR__ . '/../Views/rab/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
