<?php
// app/Controllers/TicketController.php - Customer Complaint and Field Ticketing

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class TicketController {

    public function index(): void {
        AuthMiddleware::handle('tickets.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_ticket') {
            AuthMiddleware::handle('tickets.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('tickets');
            }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? 'connection_down');
            $priority = trim($_POST['priority'] ?? 'medium');
            $techId = (int)($_POST['technician_id'] ?? 0) ?: null;
            $desc = trim($_POST['description'] ?? '');

            if ($customerId > 0 && !empty($title)) {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn() + 1;
                $tktNo = 'TKT-' . date('Ym') . '-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);
                $userId = AuthService::user()['id'] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO tickets (ticket_no, customer_id, title, description, category, priority, technician_id, status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?)
                ");
                $stmt->execute([$tktNo, $customerId, $title, $desc, $category, $priority, $techId, $userId]);

                Helper::logActivity('TICKETING', 'CREATE_TICKET', $tktNo, null, "Opened ticket $tktNo: $title");
                Helper::setFlash('success', "Tiket gangguan $tktNo berhasil dibuat.");
            }
            Helper::redirect('tickets');
        }

        $tickets = $pdo->query("
            SELECT t.*, c.name as customer_name, c.customer_no, c.phone, u.name as tech_name 
            FROM tickets t 
            JOIN customers c ON t.customer_id = c.id 
            LEFT JOIN users u ON t.technician_id = u.id 
            ORDER BY t.id DESC
        ")->fetchAll();

        $customers = $pdo->query("SELECT id, name, customer_no FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $technicians = $pdo->query("SELECT id, name FROM users WHERE role_id IN (1, 6) ORDER BY name ASC")->fetchAll();

        $pageTitle = 'Tiket Gangguan & Layanan Pelanggan';

        ob_start();
        require __DIR__ . '/../Views/tickets/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
