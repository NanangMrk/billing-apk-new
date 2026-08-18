<?php
// app/Controllers/RabController.php - RAB Project Budgeting, Itemization & Realization

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class RabController {

    public function index(): void {
        AuthMiddleware::handle('rab.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            // 1. Submit New RAB with Items
            if ($action === 'save_rab') {
                AuthMiddleware::handle('rab.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('rab');
                }

                $projectName = trim($_POST['project_name'] ?? '');
                $categoryId = (int)($_POST['category_id'] ?? 1);
                $location = trim($_POST['location'] ?? '');
                $picName = trim($_POST['pic_name'] ?? '');
                $startDate = trim($_POST['start_date'] ?? date('Y-m-d'));
                $endDate = trim($_POST['end_date'] ?? date('Y-m-d', strtotime('+14 days')));
                $desc = trim($_POST['description'] ?? '');

                // Process item lines
                $items = $_POST['items'] ?? [];
                $validItems = [];
                $calculatedBudget = 0;

                if (is_array($items)) {
                    foreach ($items as $it) {
                        $name = trim($it['name'] ?? '');
                        $cat = trim($it['category'] ?? 'Material');
                        $qty = max(1, (int)($it['quantity'] ?? 1));
                        $unit = trim($it['unit'] ?? 'pcs') ?: 'pcs';
                        $price = max(0, (int)str_replace(['.', ',', ' '], '', $it['unit_price'] ?? '0'));
                        $subtotal = $qty * $price;
                        $notes = trim($it['notes'] ?? '');

                        if (!empty($name)) {
                            $validItems[] = [
                                'item_name' => $name,
                                'category' => $cat,
                                'quantity' => $qty,
                                'unit' => $unit,
                                'unit_price' => $price,
                                'subtotal' => $subtotal,
                                'notes' => $notes
                            ];
                            $calculatedBudget += $subtotal;
                        }
                    }
                }

                $budgetTotal = $calculatedBudget > 0 ? $calculatedBudget : (int)str_replace(['.', ',', ' '], '', $_POST['budget_total'] ?? '0');

                if (!empty($projectName) && $budgetTotal > 0) {
                    $count = (int)$pdo->query("SELECT COUNT(*) FROM rabs")->fetchColumn() + 1;
                    $rabNo = 'RAB-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                    $userId = AuthService::user()['id'] ?? null;

                    $stmt = $pdo->prepare("
                        INSERT INTO rabs (rab_no, project_name, category_id, location, pic_name, start_date, end_date, budget_total, realized_total, status, description, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'submitted', ?, ?)
                    ");
                    $stmt->execute([$rabNo, $projectName, $categoryId, $location, $picName, $startDate, $endDate, $budgetTotal, $desc, $userId]);
                    $rabId = (int)$pdo->lastInsertId();

                    // Insert RAB Item rows
                    if (!empty($validItems) && $rabId > 0) {
                        $stmtItem = $pdo->prepare("
                            INSERT INTO rab_items (rab_id, item_name, category, quantity, unit, unit_price, subtotal, realized_subtotal, notes)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)
                        ");
                        foreach ($validItems as $v) {
                            $stmtItem->execute([
                                $rabId,
                                $v['item_name'],
                                $v['category'],
                                $v['quantity'],
                                $v['unit'],
                                $v['unit_price'],
                                $v['subtotal'],
                                $v['notes']
                            ]);
                        }
                    }

                    Helper::logActivity('RAB', 'CREATE_RAB', $rabNo, null, "Created RAB $rabNo ($projectName) with " . count($validItems) . " items");
                    Helper::setFlash('success', "RAB $rabNo ($projectName) berhasil diajukan dengan total anggaran " . Helper::formatRupiah($budgetTotal) . ".");
                }
                Helper::redirect('rab');
            }

            // 2. Approve RAB
            if ($action === 'approve_rab') {
                AuthMiddleware::handle('rab.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('rab');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $userId = AuthService::user()['id'] ?? null;
                    $stmt = $pdo->prepare("
                        UPDATE rabs 
                        SET status = 'approved', approved_by = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ");
                    $stmt->execute([$userId, $id]);

                    $rabNo = $pdo->query("SELECT rab_no FROM rabs WHERE id = {$id}")->fetchColumn();
                    Helper::logActivity('RAB', 'APPROVE_RAB', (string)$id, null, "Approved RAB $rabNo");
                    Helper::setFlash('success', "RAB {$rabNo} berhasil disetujui! Anda sekarang dapat menginput realisasi biaya.");
                }
                Helper::redirect('rab');
            }

            // 3. Reject RAB
            if ($action === 'reject_rab') {
                AuthMiddleware::handle('rab.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('rab');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE rabs 
                        SET status = 'rejected', updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ");
                    $stmt->execute([$id]);

                    $rabNo = $pdo->query("SELECT rab_no FROM rabs WHERE id = {$id}")->fetchColumn();
                    Helper::logActivity('RAB', 'REJECT_RAB', (string)$id, null, "Rejected RAB $rabNo");
                    Helper::setFlash('warning', "RAB {$rabNo} telah ditolak.");
                }
                Helper::redirect('rab');
            }

            // 4. Save Realization Input
            if ($action === 'save_realization') {
                AuthMiddleware::handle('rab.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('rab');
                }

                $id = (int)($_POST['id'] ?? 0);
                $status = trim($_POST['status'] ?? 'completed');
                $realizedItems = $_POST['realized_items'] ?? [];
                $totalRealized = 0;

                if ($id > 0) {
                    if (is_array($realizedItems)) {
                        $stmtUpdateItem = $pdo->prepare("UPDATE rab_items SET realized_subtotal = ? WHERE id = ? AND rab_id = ?");
                        foreach ($realizedItems as $itemId => $amount) {
                            $itemId = (int)$itemId;
                            $amount = (int)str_replace(['.', ',', ' '], '', $amount);
                            $totalRealized += $amount;
                            $stmtUpdateItem->execute([$amount, $itemId, $id]);
                        }
                    } else {
                        $totalRealized = (int)str_replace(['.', ',', ' '], '', $_POST['realized_total'] ?? '0');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE rabs 
                        SET realized_total = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ");
                    $stmt->execute([$totalRealized, $status, $id]);

                    $rabNo = $pdo->query("SELECT rab_no FROM rabs WHERE id = {$id}")->fetchColumn();
                    Helper::logActivity('RAB', 'SAVE_REALIZATION', (string)$id, null, "Recorded realization for RAB $rabNo: " . Helper::formatRupiah($totalRealized));
                    Helper::setFlash('success', "Realisasi RAB {$rabNo} berhasil disimpan sebesar " . Helper::formatRupiah($totalRealized) . ".");
                }
                Helper::redirect('rab');
            }

            // 5. Delete RAB
            if ($action === 'delete_rab') {
                AuthMiddleware::handle('rab.manage');
                if (!Helper::verifyCsrf()) {
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('rab');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $rabNo = $pdo->query("SELECT rab_no FROM rabs WHERE id = {$id}")->fetchColumn();
                    $pdo->prepare("DELETE FROM rab_items WHERE rab_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM rabs WHERE id = ?")->execute([$id]);

                    Helper::logActivity('RAB', 'DELETE_RAB', (string)$id, null, "Deleted RAB $rabNo");
                    Helper::setFlash('success', "RAB {$rabNo} berhasil dihapus.");
                }
                Helper::redirect('rab');
            }
        }

        // 6. Filter Query Parameters
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $month = trim($_GET['month'] ?? '');

        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(r.project_name LIKE ? OR r.rab_no LIKE ? OR r.location LIKE ? OR r.pic_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($status)) {
            $where[] = "r.status = ?";
            $params[] = $status;
        }

        if ($categoryId > 0) {
            $where[] = "r.category_id = ?";
            $params[] = $categoryId;
        }

        if (!empty($month)) {
            $where[] = "(strftime('%Y-%m', r.start_date) = ? OR strftime('%Y-%m', r.created_at) = ?)";
            $params[] = $month;
            $params[] = $month;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Fetch Filtered RABs
        $stmt = $pdo->prepare("
            SELECT r.*, c.name as category_name, u.name as creator_name, app.name as approver_name
            FROM rabs r 
            LEFT JOIN rab_categories c ON r.category_id = c.id 
            LEFT JOIN users u ON r.created_by = u.id 
            LEFT JOIN users app ON r.approved_by = app.id
            {$whereSql}
            ORDER BY r.id DESC
        ");
        $stmt->execute($params);
        $rabs = $stmt->fetchAll();

        // Overall stats (unfiltered for KPI summary)
        $summary = $pdo->query("
            SELECT 
                COALESCE(SUM(budget_total), 0) as total_budget,
                COALESCE(SUM(realized_total), 0) as total_realized,
                COUNT(CASE WHEN status = 'submitted' THEN 1 END) as count_submitted,
                COUNT(CASE WHEN status IN ('approved', 'in_progress') THEN 1 END) as count_approved,
                COUNT(id) as total_count
            FROM rabs
        ")->fetch();

        // Attach items to each RAB
        $allItems = $pdo->query("SELECT * FROM rab_items ORDER BY id ASC")->fetchAll();
        $itemsByRab = [];
        foreach ($allItems as $it) {
            $itemsByRab[$it['rab_id']][] = $it;
        }

        foreach ($rabs as &$rab) {
            $rab['items'] = $itemsByRab[$rab['id']] ?? [];
        }
        unset($rab);

        $categories = $pdo->query("SELECT * FROM rab_categories")->fetchAll();
        $inventoryItems = $pdo->query("SELECT id, name, unit, purchase_price FROM inventory_items WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        $pageTitle = 'RAB (Rencana Anggaran Biaya) Proyek & Realisasi';

        ob_start();
        require __DIR__ . '/../Views/rab/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
