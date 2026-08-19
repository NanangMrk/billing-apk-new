<?php
// app/Controllers/AiController.php - AI Assistant & Multi-Provider Settings Controller

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
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || isset($_POST['ajax']) 
                  || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'chat';

            // 1. Save Settings Action
            if ($action === 'save_settings') {
                if (!Helper::verifyCsrf() && empty($_SESSION['user'])) {
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
                        exit;
                    }
                    Helper::setFlash('error', 'Token CSRF tidak valid.');
                    Helper::redirect('ai', ['tab' => 'settings']);
                }

                $saved = AiAssistantService::saveSettings($pdo, [
                    'provider' => $_POST['provider'] ?? 'local',
                    'model' => $_POST['model'] ?? 'local-engine',
                    'api_key' => $_POST['api_key'] ?? '',
                    'base_url' => $_POST['base_url'] ?? '',
                    'temperature' => $_POST['temperature'] ?? 0.7,
                    'max_tokens' => $_POST['max_tokens'] ?? 2048,
                    'system_prompt' => $_POST['system_prompt'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);

                if ($saved) {
                    Helper::logActivity('AI', 'UPDATE_SETTINGS', '1', null, "Updated AI Provider: " . ($_POST['provider'] ?? 'local'));
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Pengaturan AI & API berhasil disimpan.',
                            'csrf_token' => Helper::csrfToken()
                        ]);
                        exit;
                    }
                    Helper::setFlash('success', 'Pengaturan AI & API berhasil diperbarui.');
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengaturan.']);
                        exit;
                    }
                    Helper::setFlash('error', 'Gagal memperbarui pengaturan AI.');
                }
                Helper::redirect('ai', ['tab' => 'settings']);
            }

            // 2. Test Connection Action
            if ($action === 'test_connection') {
                $testConfig = [
                    'provider' => $_POST['provider'] ?? 'local',
                    'model' => $_POST['model'] ?? 'local-engine',
                    'api_key' => $_POST['api_key'] ?? '',
                    'base_url' => $_POST['base_url'] ?? '',
                    'temperature' => $_POST['temperature'] ?? 0.7,
                    'max_tokens' => 256,
                    'system_prompt' => $_POST['system_prompt'] ?? 'Tes singkat'
                ];

                $testResult = AiAssistantService::testConnection($pdo, $testConfig);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array_merge($testResult, ['csrf_token' => Helper::csrfToken()]));
                exit;
            }

            // 3. Regular Chat Action
            $isCsrfValid = Helper::verifyCsrf() || ($isAjax && !empty($_SESSION['user']));
            if (!$isCsrfValid) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Sesi login Anda telah kadaluarsa. Silakan muat ulang halaman.'
                    ]);
                    exit;
                }
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('ai');
            }

            $userPrompt = trim($_POST['prompt'] ?? '');
            if (!empty($userPrompt)) {
                $userId = AuthService::user()['id'] ?? null;
                $response = AiAssistantService::ask($userPrompt, $userId);

                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'success',
                        'prompt' => $userPrompt,
                        'content' => $response['content'],
                        'title' => $response['title'] ?? 'Tanggapan AI Advisor',
                        'csrf_token' => Helper::csrfToken(),
                        'timestamp' => date('H:i')
                    ]);
                    exit;
                }
            } else if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Pertanyaan tidak boleh kosong.'
                ]);
                exit;
            }
        }

        // Live stats & AI settings
        $stats = AiAssistantService::getQuickStats($pdo);
        $aiSettings = AiAssistantService::getSettings($pdo);
        $activeTab = $_GET['tab'] ?? 'chat';

        $pageTitle = 'AI Business & Financial Advisor';

        ob_start();
        require __DIR__ . '/../Views/ai/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
