<?php
// app/Controllers/AiController.php - AI Assistant & Advisor Controller

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Services/AiAssistantService.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AiController {

    public function index(): void {
        AuthMiddleware::handle('ai.use');
        $pdo = getDbConnection();

        $response = null;
        $userPrompt = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('ai');
            }

            $userPrompt = trim($_POST['prompt'] ?? '');
            if (!empty($userPrompt)) {
                $userId = AuthService::user()['id'] ?? null;
                $response = AiAssistantService::ask($userPrompt, $userId);
            }
        }

        $pageTitle = 'AI Business & Financial Advisor';

        ob_start();
        require __DIR__ . '/../Views/ai/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
