<?php
// app/Services/AiAssistantService.php - Enhanced Safe AI Advisor with Multi-Provider API Support

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/AuthService.php';

class AiAssistantService {

    public static function initTable(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider TEXT NOT NULL DEFAULT 'local',
                model TEXT NOT NULL DEFAULT 'local-engine',
                api_key TEXT DEFAULT NULL,
                base_url TEXT DEFAULT NULL,
                temperature REAL DEFAULT 0.7,
                max_tokens INTEGER DEFAULT 2048,
                system_prompt TEXT DEFAULT NULL,
                is_active INTEGER DEFAULT 1,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $exists = $pdo->query("SELECT COUNT(*) FROM ai_settings WHERE id = 1")->fetchColumn();
        if (!$exists) {
            $defaultSystemPrompt = "Anda adalah AI Business & Financial Advisor untuk ISP (Internet Service Provider) NusantaraNet. Analisis data riil tagihan, kas bank, RAB proyek, stok gudang, dan PIC koordinator wilayah dengan objektif, akurat, dan berikan rekomendasi operasional yang taktis dalam bahasa Indonesia.";
            $stmt = $pdo->prepare("
                INSERT INTO ai_settings (id, provider, model, api_key, base_url, temperature, max_tokens, system_prompt, is_active)
                VALUES (1, 'local', 'local-engine', '', '', 0.7, 2048, ?, 1)
            ");
            $stmt->execute([$defaultSystemPrompt]);
        }
    }

    public static function getSettings(PDO $pdo): array {
        self::initTable($pdo);
        $settings = $pdo->query("SELECT * FROM ai_settings WHERE id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            return [
                'id' => 1,
                'provider' => 'local',
                'model' => 'local-engine',
                'api_key' => '',
                'base_url' => '',
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'system_prompt' => 'Anda adalah AI Business Advisor ISP NusantaraNet.',
                'is_active' => 1
            ];
        }
        return $settings;
    }

    public static function saveSettings(PDO $pdo, array $data): bool {
        self::initTable($pdo);
        $provider = trim($data['provider'] ?? 'local');
        $model = trim($data['model'] ?? 'local-engine');
        $apiKey = trim($data['api_key'] ?? '');
        $baseUrl = trim($data['base_url'] ?? '');
        $temperature = max(0.0, min(2.0, (float)($data['temperature'] ?? 0.7)));
        $maxTokens = max(256, min(8192, (int)($data['max_tokens'] ?? 2048)));
        $systemPrompt = trim($data['system_prompt'] ?? '');
        $isActive = !empty($data['is_active']) ? 1 : 0;

        $stmt = $pdo->prepare("
            UPDATE ai_settings 
            SET provider = ?, model = ?, api_key = ?, base_url = ?, temperature = ?, max_tokens = ?, system_prompt = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = 1
        ");
        return $stmt->execute([$provider, $model, $apiKey, $baseUrl, $temperature, $maxTokens, $systemPrompt, $isActive]);
    }

    public static function getQuickStats(PDO $pdo): array {
        $stats = [];
        try {
            $stats['active_customers'] = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
            $stats['cash_balance'] = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
            
            $unpaidStmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(balance_due), 0) as total FROM invoices WHERE payment_status IN ('unpaid', 'partially_paid')");
            $unpaid = $unpaidStmt->fetch();
            $stats['unpaid_invoices_count'] = (int)($unpaid['count'] ?? 0);
            $stats['unpaid_invoices_total'] = (int)($unpaid['total'] ?? 0);

            $stats['low_stock_count'] = (int)$pdo->query("SELECT COUNT(*) FROM inventory_items WHERE current_stock <= min_stock AND status = 'active'")->fetchColumn();
            
            $rabStmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(budget_total), 0) as total FROM rabs WHERE status IN ('submitted', 'draft')");
            $rabRow = $rabStmt ? $rabStmt->fetch() : null;
            $stats['pending_rabs_count'] = (int)($rabRow['count'] ?? 0);
            $stats['pending_rabs_total'] = (int)($rabRow['total'] ?? 0);

            $stats['total_pics'] = (int)$pdo->query("SELECT COUNT(*) FROM customer_pics")->fetchColumn();
        } catch (Exception $e) {
            $stats = [
                'active_customers' => 0,
                'cash_balance' => 0,
                'unpaid_invoices_count' => 0,
                'unpaid_invoices_total' => 0,
                'low_stock_count' => 0,
                'pending_rabs_count' => 0,
                'pending_rabs_total' => 0,
                'total_pics' => 0
            ];
        }
        return $stats;
    }

    public static function buildLiveContextString(PDO $pdo, string $userPrompt = ''): string {
        $stats = self::getQuickStats($pdo);
        
        // 1. Financial & Bank Accounts Summary
        $accounts = $pdo->query("SELECT account_name, account_type, bank_name, account_number, current_balance FROM finance_accounts WHERE status = 'active' ORDER BY current_balance DESC")->fetchAll(PDO::FETCH_ASSOC);
        $accountsStr = [];
        foreach ($accounts as $a) {
            $accountsStr[] = "- {$a['bank_name']} ({$a['account_name']} / {$a['account_number']}): " . Helper::formatRupiah((int)$a['current_balance']);
        }

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
        $fin = $stmtFin->fetch();
        $netProfit = ($fin['total_income'] ?? 0) - ($fin['total_expense'] ?? 0);

        // 2. Customers & Internet Packages
        $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $activeCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
        $isolatedCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'isolated'")->fetchColumn();
        $inactiveCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('inactive', 'terminated')")->fetchColumn();

        $packages = $pdo->query("
            SELECT p.name, p.download_speed, p.price, COUNT(c.id) as user_count 
            FROM internet_packages p 
            LEFT JOIN customers c ON p.id = c.package_id 
            GROUP BY p.id 
            ORDER BY user_count DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $packagesStr = [];
        foreach ($packages as $pkg) {
            $packagesStr[] = "- {$pkg['name']} ({$pkg['download_speed']} Mbps @ " . Helper::formatRupiah((int)$pkg['price']) . "): {$pkg['user_count']} pelanggan";
        }

        // 3. Billing & Overdue Aging Invoices
        $invoicesCount = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
        $paidCount = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status = 'paid'")->fetchColumn();
        $unpaidInvoices = $pdo->query("
            SELECT i.invoice_no, i.balance_due, i.due_date, i.billing_period, c.name as customer_name, c.phone, p.name as pic_name
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            LEFT JOIN customer_pics p ON c.pic_id = p.id
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue')
            ORDER BY i.due_date ASC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        $unpaidStr = [];
        foreach ($unpaidInvoices as $u) {
            $unpaidStr[] = "- {$u['invoice_no']} | Pelanggan: {$u['customer_name']} (Telp: {$u['phone']}) | Sisa: " . Helper::formatRupiah((int)$u['balance_due']) . " | JT: {$u['due_date']} | PIC: " . ($u['pic_name'] ?: '-');
        }

        // 4. RAB (Rencana Anggaran Biaya) Projects
        $rabs = $pdo->query("
            SELECT r.rab_no, r.project_name, r.location, r.pic_name, r.budget_total, r.realized_total, r.status,
                   (SELECT COUNT(*) FROM rab_items ri WHERE ri.rab_id = r.id) as item_count
            FROM rabs r
            ORDER BY r.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        $rabsStr = [];
        foreach ($rabs as $r) {
            $rabsStr[] = "- {$r['rab_no']}: {$r['project_name']} (Lokasi: {$r['location']}, PIC: {$r['pic_name']}, Anggaran: " . Helper::formatRupiah((int)$r['budget_total']) . ", Realisasi: " . Helper::formatRupiah((int)$r['realized_total']) . ", Status: {$r['status']}, Items: {$r['item_count']})";
        }

        // 5. Inventory & Warehouse Items
        $inventory = $pdo->query("
            SELECT i.sku, i.name, i.current_stock, i.min_stock, i.unit, i.purchase_price, i.status, w.name as warehouse_name
            FROM inventory_items i
            LEFT JOIN warehouses w ON i.warehouse_id = w.id
            ORDER BY i.current_stock ASC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        $inventoryStr = [];
        foreach ($inventory as $inv) {
            $isLow = ($inv['current_stock'] <= $inv['min_stock']);
            $inventoryStr[] = "- SKU {$inv['sku']}: {$inv['name']} [Stok: {$inv['current_stock']} {$inv['unit']} / Min: {$inv['min_stock']} {$inv['unit']}] (Harga Beli: " . Helper::formatRupiah((int)$inv['purchase_price']) . ") " . ($isLow ? "[PERINGATAN: MENIPIS]" : "[AMAN]");
        }

        // 6. PIC / Mitra / Koordinator RT-RW
        $pics = $pdo->query("
            SELECT p.name, p.phone, p.position, p.company, COUNT(c.id) as customer_count,
                   COALESCE(SUM(CASE WHEN i.payment_status IN ('unpaid', 'partially_paid') THEN i.balance_due ELSE 0 END), 0) as uncollected
            FROM customer_pics p
            LEFT JOIN customers c ON p.id = c.pic_id
            LEFT JOIN invoices i ON c.id = i.customer_id
            GROUP BY p.id
        ")->fetchAll(PDO::FETCH_ASSOC);
        $picsStr = [];
        foreach ($pics as $p) {
            $picsStr[] = "- PIC {$p['name']} ({$p['position']} - {$p['company']}, Telp: {$p['phone']}): {$p['customer_count']} pelanggan, Piutang Belum Tertagih: " . Helper::formatRupiah((int)$p['uncollected']);
        }

        // 7. Employees & Staff Payroll
        $employees = $pdo->query("
            SELECT e.name, e.position, e.basic_salary, e.employment_status, d.name as dept_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.status = 'active'
        ")->fetchAll(PDO::FETCH_ASSOC);
        $employeesStr = [];
        $totalPayroll = 0;
        foreach ($employees as $emp) {
            $totalPayroll += (int)$emp['basic_salary'];
            $employeesStr[] = "- {$emp['name']} ({$emp['position']} - Dept: {$emp['dept_name']}): Gaji Pokok " . Helper::formatRupiah((int)$emp['basic_salary']);
        }

        // 8. Fixed Assets
        $assets = $pdo->query("SELECT asset_no, name, purchase_price, condition, location, status FROM assets LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        $assetsStr = [];
        foreach ($assets as $ast) {
            $assetsStr[] = "- {$ast['asset_no']}: {$ast['name']} (Nilai: " . Helper::formatRupiah((int)$ast['purchase_price']) . ", Kondisi: {$ast['condition']}, Lokasi: {$ast['location']})";
        }

        // 9. Helpdesk / Network Tickets
        $tickets = $pdo->query("
            SELECT t.ticket_no, t.title, t.category, t.priority, t.status, c.name as customer_name
            FROM tickets t
            LEFT JOIN customers c ON t.customer_id = c.id
            ORDER BY t.id DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);
        $ticketsStr = [];
        foreach ($tickets as $t) {
            $ticketsStr[] = "- {$t['ticket_no']}: {$t['title']} (Pelanggan: {$t['customer_name']}, Prioritas: {$t['priority']}, Status: {$t['status']})";
        }

        // 10. Dynamic Targeted Entity Search based on User Query
        $matchedEntitiesStr = self::searchEntityContext($pdo, $userPrompt);

        return "
=== DATA OPERASIONAL LENGKAP SISTEM ISP NUSANTARANET (Live Database Per " . date('d F Y H:i:s') . ") ===

1. RINGKASAN KEUANGAN & KAS BANK:
- Total Saldo Kas & Bank Likuid: " . Helper::formatRupiah($stats['cash_balance']) . "
" . implode("\n", $accountsStr) . "
- Rekap Finansial Bulan Ini (" . date('F Y') . "): Pemasukan " . Helper::formatRupiah($fin['total_income'] ?? 0) . ", Pengeluaran " . Helper::formatRupiah($fin['total_expense'] ?? 0) . ", Net Cashflow: " . Helper::formatRupiah($netProfit) . "
- Beban Rutin Gaji Karyawan Bulanan: " . Helper::formatRupiah($totalPayroll) . "

2. DATA PELANGGAN & PAKET INTERNET:
- Total Terdaftar: {$totalCustomers} pelanggan (Aktif: {$activeCustomers}, Terisolir: {$isolatedCustomers}, Nonaktif: {$inactiveCustomers})
- Distribusi Paket Internet:
" . implode("\n", $packagesStr) . "

3. BILLING, PENAGIHAN & AGING PIUTANG:
- Total Tagihan Diterbitkan: {$invoicesCount} (Lunas: {$paidCount}, Belum Lunas: {$stats['unpaid_invoices_count']})
- Total Nominal Piutang Belum Tertagih: " . Helper::formatRupiah($stats['unpaid_invoices_total']) . "
- Rincian Tagihan Tertunggak / Overdue Terbesar:
" . (empty($unpaidStr) ? "- Seluruh tagihan pelanggan lancar / lunas." : implode("\n", $unpaidStr)) . "

4. RENCANA ANGGARAN BIAYA (RAB) PROYEK:
- Jumlah RAB Pending / Submitted: {$stats['pending_rabs_count']} dokumen (Total Nilai: " . Helper::formatRupiah($stats['pending_rabs_total']) . ")
- Daftar Dokumen RAB Terbaru:
" . (empty($rabsStr) ? "- Belum ada dokumen RAB." : implode("\n", $rabsStr)) . "

5. INVENTARIS, GUDANG & MATERIAL JARINGAN:
- Peringatan Stok Menipis: {$stats['low_stock_count']} item
- Katalog Barang & Posisi Stok Saat Ini:
" . (empty($inventoryStr) ? "- Tidak ada data inventaris." : implode("\n", $inventoryStr)) . "

6. KOORDINATOR PIC & MITRA RT-RW:
- Total PIC Wilayah Terdaftar: {$stats['total_pics']} PIC
" . (empty($picsStr) ? "- Belum ada PIC terdaftar." : implode("\n", $picsStr)) . "

7. KARYAWAN & STRUKTUR GAJI (PAYROLL):
" . (empty($employeesStr) ? "- Tidak ada data karyawan aktif." : implode("\n", $employeesStr)) . "

8. ASET TETAP PERUSAHAAN:
" . (empty($assetsStr) ? "- Tidak ada data aset." : implode("\n", $assetsStr)) . "

9. TIKET GANGGUAN / HELPDESK PELANGGAN:
" . (empty($ticketsStr) ? "- Tidak ada tiket gangguan aktif." : implode("\n", $ticketsStr)) . "
" . (!empty($matchedEntitiesStr) ? "\n10. HASIL PENCARIAN DETAIL KHUSUS ENTITAS TERKAIT PERTANYAAN USER:\n" . $matchedEntitiesStr : "") . "
=== AKHIR DATA SISTEM ===";
    }

    public static function searchEntityContext(PDO $pdo, string $prompt): string {
        if (empty($prompt)) return '';
        $words = preg_split('/\s+/', trim($prompt));
        $matches = [];

        foreach ($words as $w) {
            $w = trim($w, " \t\n\r\0\x0B?,.!\"'()");
            if (strlen($w) < 3) continue;
            // skip common stop words
            if (in_array(strtolower($w), ['apa', 'berapa', 'siapa', 'bagaimana', 'apakah', 'yang', 'dan', 'dari', 'untuk', 'ini', 'itu', 'ada', 'pada', 'bisa', 'tolong', 'jelaskan', 'tampilkan', 'daftar', 'cek', 'lihat', 'saya'])) continue;

            // Search Customer
            try {
                $stmt = $pdo->prepare("
                    SELECT c.customer_no, c.name, c.phone, c.whatsapp, c.full_address, c.status, c.pppoe_username, c.ip_address,
                           p.name as package_name, p.download_speed, p.price as package_price, pic.name as pic_name
                    FROM customers c
                    LEFT JOIN internet_packages p ON c.package_id = p.id
                    LEFT JOIN customer_pics pic ON c.pic_id = pic.id
                    WHERE c.name LIKE ? OR c.customer_no LIKE ? OR c.phone LIKE ? OR c.pppoe_username LIKE ?
                    LIMIT 3
                ");
                $term = "%{$w}%";
                $stmt->execute([$term, $term, $term, $term]);
                $custs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($custs as $c) {
                    $matches[] = "[Detail Pelanggan Cocok] {$c['customer_no']} - {$c['name']} (Status: {$c['status']}, Paket: {$c['package_name']} {$c['download_speed']} Mbps @" . Helper::formatRupiah((int)$c['package_price']) . ", PIC: {$c['pic_name']}, Alamat: {$c['full_address']}, Telp: {$c['phone']}, PPPoE: {$c['pppoe_username']}, IP: {$c['ip_address']})";
                }
            } catch (Exception $e) {}

            // Search RAB Project items
            try {
                $stmt = $pdo->prepare("
                    SELECT r.rab_no, r.project_name, ri.item_name, ri.quantity, ri.unit, ri.unit_price, ri.subtotal
                    FROM rabs r
                    JOIN rab_items ri ON r.id = ri.rab_id
                    WHERE r.project_name LIKE ? OR r.rab_no LIKE ?
                    LIMIT 10
                ");
                $term = "%{$w}%";
                $stmt->execute([$term, $term]);
                $rabItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rabItems)) {
                    $itemSummary = [];
                    foreach ($rabItems as $ri) {
                        $itemSummary[] = "{$ri['item_name']} ({$ri['quantity']} {$ri['unit']} @ " . Helper::formatRupiah((int)$ri['unit_price']) . " = " . Helper::formatRupiah((int)$ri['subtotal']) . ")";
                    }
                    $matches[] = "[Detail Rincian Item RAB] {$rabItems[0]['rab_no']} - {$rabItems[0]['project_name']}: " . implode('; ', $itemSummary);
                }
            } catch (Exception $e) {}

            // Search Inventory Item
            try {
                $stmt = $pdo->prepare("
                    SELECT sku, name, brand, model, unit, current_stock, min_stock, purchase_price, selling_price
                    FROM inventory_items
                    WHERE name LIKE ? OR sku LIKE ? OR brand LIKE ?
                    LIMIT 3
                ");
                $term = "%{$w}%";
                $stmt->execute([$term, $term, $term]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($items as $it) {
                    $matches[] = "[Detail Material/Barang] SKU {$it['sku']}: {$it['name']} ({$it['brand']} {$it['model']}) - Sisa Stok: {$it['current_stock']} {$it['unit']} (Min: {$it['min_stock']}), Harga Beli: " . Helper::formatRupiah((int)$it['purchase_price']) . ", Harga Jual: " . Helper::formatRupiah((int)$it['selling_price']);
                }
            } catch (Exception $e) {}
        }

        return implode("\n", array_unique($matches));
    }

    public static function callLlmApi(string $prompt, array $settings, PDO $pdo): array {
        $provider = strtolower($settings['provider'] ?? 'openai');
        $model = $settings['model'] ?? 'gpt-4o-mini';
        $apiKey = $settings['api_key'] ?? '';
        $customBaseUrl = trim($settings['base_url'] ?? '');
        $temperature = (float)($settings['temperature'] ?? 0.7);
        $maxTokens = (int)($settings['max_tokens'] ?? 2048);
        $systemPrompt = $settings['system_prompt'] ?? 'Anda adalah AI Business Advisor NusantaraNet.';

        $liveContext = self::buildLiveContextString($pdo, $prompt);
        $fullSystemPrompt = $systemPrompt . "\n\n" . $liveContext . "\n\nInstruksi Tambahan: Anda memiliki akses menyeluruh ke seluruh data operasional, kas bank, tagihan, RAB, inventaris, dan pelanggan di atas. Jawab pertanyaan user dengan fakta akurat berdasarkan data di atas. Jika user menanyakan estimasi, berikan analisis finansial atau operasional yang logis. Format tanggapan Anda dengan rapi menggunakan Markdown tebal, list, dan tabel jika relevan.";

        $rawResponseText = '';

        if ($provider === 'gemini') {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $payload = [
                'system_instruction' => [
                    'parts' => [['text' => $fullSystemPrompt]]
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens
                ]
            ];

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) throw new Exception("Gemini Network Error: " . $err);
            $json = json_decode($res, true);
            if ($code >= 400 || !empty($json['error'])) {
                $errMsg = $json['error']['message'] ?? "HTTP Status $code";
                throw new Exception("Gemini Error: " . $errMsg);
            }
            $rawResponseText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

        } elseif ($provider === 'claude') {
            $endpoint = "https://api.anthropic.com/v1/messages";
            $payload = [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'system' => $fullSystemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ];

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'x-api-key: ' . $apiKey,
                    'anthropic-version: 2023-06-01',
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) throw new Exception("Claude Network Error: " . $err);
            $json = json_decode($res, true);
            if ($code >= 400 || !empty($json['error'])) {
                $errMsg = $json['error']['message'] ?? "HTTP Status $code";
                throw new Exception("Claude Error: " . $errMsg);
            }
            $rawResponseText = $json['content'][0]['text'] ?? '';

        } else {
            // OpenAI / DeepSeek / Ollama / Custom Endpoint
            $baseUrl = $customBaseUrl;
            if (empty($baseUrl)) {
                if ($provider === 'deepseek') {
                    $baseUrl = 'https://api.deepseek.com';
                } elseif ($provider === 'ollama') {
                    $baseUrl = 'http://localhost:11434/v1';
                } else {
                    $baseUrl = 'https://api.openai.com/v1';
                }
            }
            $endpoint = rtrim($baseUrl, '/') . '/chat/completions';

            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $fullSystemPrompt],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens
            ];

            $headers = ['Content-Type: application/json'];
            if (!empty($apiKey)) {
                $headers[] = 'Authorization: Bearer ' . $apiKey;
            }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 40,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) throw new Exception("OpenAI/LLM Network Error: " . $err);
            $json = json_decode($res, true);
            if ($code >= 400 || !empty($json['error'])) {
                $errMsg = $json['error']['message'] ?? "HTTP Status $code";
                throw new Exception("LLM API Error: " . $errMsg);
            }
            $rawResponseText = $json['choices'][0]['message']['content'] ?? '';
        }

        if (empty($rawResponseText)) {
            throw new Exception("Model tidak mengembalikan respon teks.");
        }

        $formattedHtml = self::formatMarkdownToHtml($rawResponseText);
        $providerLabel = strtoupper($provider) . " (" . Helper::e($model) . ")";

        return [
            'title' => "Tanggapan {$providerLabel}",
            'content' => "
                <div class='space-y-3 text-xs leading-relaxed'>
                    <div class='flex items-center gap-2 border-b border-slate-200/80 pb-2 text-3xs text-purple-700 font-extrabold uppercase tracking-wider'>
                        <i class='fa-solid fa-microchip'></i>
                        <span>Model: {$providerLabel} &bull; Konteks Data Riil Terhubung</span>
                    </div>
                    <div class='prose prose-sm max-w-none text-slate-800 space-y-2'>
                        {$formattedHtml}
                    </div>
                </div>
            "
        ];
    }

    public static function testConnection(PDO $pdo, array $config): array {
        try {
            $provider = strtolower($config['provider'] ?? 'local');
            if ($provider === 'local') {
                return [
                    'status' => 'success',
                    'message' => 'Engine lokal sistem database SQLite terhubung dan siap digunakan.'
                ];
            }

            $testPrompt = "Tes koneksi. Jawab singkat dalam 1 kalimat bahwa koneksi AI berhasil.";
            $response = self::callLlmApi($testPrompt, $config, $pdo);
            return [
                'status' => 'success',
                'message' => 'Koneksi ke provider ' . strtoupper($provider) . ' (' . ($config['model'] ?? '') . ') BERHASIL!'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal terhubung: ' . $e->getMessage()
            ];
        }
    }

    public static function formatMarkdownToHtml(string $markdown): string {
        // Simple and safe markdown parser for bold, italic, lists, code, and newlines
        $html = Helper::e($markdown);

        // Bold: **text** or __text__
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="font-bold text-slate-900">$1</strong>', $html);
        $html = preg_replace('/__(.*?)__/s', '<strong class="font-bold text-slate-900">$1</strong>', $html);

        // Italic: *text* or _text_
        $html = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em class="italic">$1</em>', $html);

        // Headings: ### H3, ## H2, # H1
        $html = preg_replace('/^### (.*?)$/m', '<h6 class="font-black text-slate-900 text-xs mt-3 mb-1">$1</h6>', $html);
        $html = preg_replace('/^## (.*?)$/m', '<h5 class="font-black text-slate-900 text-sm mt-3 mb-1 text-purple-800">$1</h5>', $html);
        $html = preg_replace('/^# (.*?)$/m', '<h4 class="font-black text-slate-900 text-base mt-4 mb-2 text-purple-900">$1</h4>', $html);

        // Unordered lists: - item or * item
        $html = preg_replace('/^[•\-\*] (.*?)$/m', '<li class="ml-4 list-disc text-2xs">$1</li>', $html);

        // Numbered lists: 1. item
        $html = preg_replace('/^\d+\. (.*?)$/m', '<li class="ml-4 list-decimal text-2xs">$1</li>', $html);

        // Line breaks
        $html = nl2br($html);

        return $html;
    }

    private static function evaluateCapitalExpenditure(PDO $pdo, string $prompt): array {
        // Extract amount from prompt if present
        $cost = 35000000; // default 35jt
        $itemName = 'Peralatan Jaringan (Capex)';

        if (preg_match('/(?:rp\.?|idr)\s*([\d\.,]+)/i', $prompt, $matches)) {
            $parsed = (int)str_replace(['.', ',', ' '], '', $matches[1]);
            if ($parsed > 0) $cost = $parsed;
        } elseif (preg_match('/(\d+)\s*(?:juta|jt)/i', $prompt, $matches)) {
            $cost = ((int)$matches[1]) * 1000000;
        } elseif (preg_match('/(\d{6,10})/', $prompt, $matches)) {
            $cost = (int)$matches[1];
        }

        if (stripos($prompt, 'olt') !== false) {
            $itemName = '1 Unit OLT GPON 8-Port';
        } elseif (stripos($prompt, 'splicer') !== false) {
            $itemName = '1 Unit Fusion Splicer Fiber Optic';
        } elseif (stripos($prompt, 'server') !== false) {
            $itemName = '1 Unit Server Core / Mikrotik CCR';
        } elseif (stripos($prompt, 'kabel') !== false || stripos($prompt, 'drum') !== false) {
            $itemName = 'Pengadaan Kabel Fiber Optic (Dropcore/ADSS)';
        }

        $cash = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
        $receivables = (int)$pdo->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE payment_status IN ('unpaid', 'partially_paid')")->fetchColumn();
        $payroll = (int)$pdo->query("SELECT COALESCE(SUM(basic_salary), 0) FROM employees WHERE status = 'active'")->fetchColumn();
        $bandwidthEst = 15000000;
        $reserveMinimum = 10000000;

        $totalObligations = $payroll + $bandwidthEst + $reserveMinimum;
        $expectedCollection = (int)($receivables * 0.8);
        $availableForCapex = $cash + $expectedCollection - $totalObligations;
        $isFeasible = ($availableForCapex >= $cost);
        $cashOnlyFeasible = ($cash - $totalObligations >= $cost);

        $badgeClass = $isFeasible 
            ? 'bg-emerald-500/10 text-emerald-700 border-emerald-300/60' 
            : 'bg-rose-500/10 text-rose-700 border-rose-300/60';
        $iconDecision = $isFeasible ? 'fa-circle-check text-emerald-600' : 'fa-triangle-exclamation text-rose-600';
        $decisionText = $isFeasible 
            ? ($cashOnlyFeasible ? 'SANGAT LAYAK (Kas Riil Mencukupi Penuh)' : 'LAYAK DENGAN CATATAN (Bergantung 80% Piutang Berjalan)') 
            : 'TIDAK DIREKOMENDASIKAN DIBELI SEKALIGUS (Kas & Likuiditas Terlalu Tipis)';

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-purple-700 block'>Analisis Kelayakan Belanja Modal (Capex)</span>
                        <h6 class='font-black text-slate-900 text-sm'>{$itemName} &mdash; <span class='text-purple-700'>" . Helper::formatRupiah($cost) . "</span></h6>
                    </div>
                    <span class='px-3 py-1 rounded-full text-2xs font-extrabold border {$badgeClass} inline-flex items-center gap-1.5'>
                        <i class='fa-solid {$iconDecision}'></i>
                        <span>" . ($isFeasible ? 'REKOMENDASI: LAYAK' : 'REKOMENDASI: TUNDA / TERMIN') . "</span>
                    </span>
                </div>

                <div class='p-3.5 rounded-2xl " . ($isFeasible ? 'bg-emerald-50/70 border border-emerald-200/70 text-emerald-900' : 'bg-rose-50/70 border border-rose-200/70 text-rose-900') . " leading-relaxed space-y-1'>
                    <p class='font-bold flex items-center gap-1.5 text-xs'>
                        <i class='fa-solid " . ($isFeasible ? 'fa-thumbs-up' : 'fa-hand') . "'></i>
                        <span>Keputusan Finansial: {$decisionText}</span>
                    </p>
                    <p class='text-2xs opacity-90'>
                        " . ($isFeasible 
                            ? "Setelah memperhitungkan seluruh kewajiban gaji tim (" . Helper::formatRupiah($payroll) . "), bandwidth upstream (" . Helper::formatRupiah($bandwidthEst) . "), dan cadangan kas darurat (" . Helper::formatRupiah($reserveMinimum) . "), Anda masih memiliki sisa likuiditas bebas sebesar <strong>" . Helper::formatRupiah($availableForCapex) . "</strong>." 
                            : "Dana bebas belanja saat ini hanya <strong>" . Helper::formatRupiah($availableForCapex) . "</strong>. Membeli tunai sekarang berisiko mengganggu pembayaran gaji atau tagihan upstream. Disarankan mengajukan skema termin vendor (DP 30-50%) atau menunggu penagihan piutang pelanggan bulan ini.") . "
                    </p>
                </div>

                <div class='rounded-2xl border border-slate-200/80 overflow-hidden bg-slate-50/60 font-mono text-2xs'>
                    <div class='p-2.5 bg-slate-100/80 font-bold font-sans text-slate-700 border-b border-slate-200 text-3xs uppercase tracking-wider'>
                        Kalkulasi Likuiditas & Arus Kas Tersedia
                    </div>
                    <div class='divide-y divide-slate-100 p-2'>
                        <div class='flex justify-between py-1.5 px-2'><span class='text-slate-500 font-sans'>Saldo Kas & Bank Likuid:</span> <span class='font-bold text-slate-800'>" . Helper::formatRupiah($cash) . "</span></div>
                        <div class='flex justify-between py-1.5 px-2'><span class='text-slate-500 font-sans'>Proyeksi Tagihan Masuk (80% Piutang):</span> <span class='text-emerald-600 font-bold'>+" . Helper::formatRupiah($expectedCollection) . "</span></div>
                        <div class='flex justify-between py-1.5 px-2'><span class='text-slate-500 font-sans'>Beban Gaji Karyawan (Payroll):</span> <span class='text-rose-500 font-bold'>-" . Helper::formatRupiah($payroll) . "</span></div>
                        <div class='flex justify-between py-1.5 px-2'><span class='text-slate-500 font-sans'>Beban Bandwidth Upstream (Est):</span> <span class='text-rose-500 font-bold'>-" . Helper::formatRupiah($bandwidthEst) . "</span></div>
                        <div class='flex justify-between py-1.5 px-2'><span class='text-slate-500 font-sans'>Buffer Cadangan Kas Minimum:</span> <span class='text-slate-600 font-bold'>-" . Helper::formatRupiah($reserveMinimum) . "</span></div>
                        <div class='flex justify-between py-2 px-2 border-t-2 border-slate-300 font-bold text-xs bg-purple-50/50 rounded-xl'>
                            <span class='font-sans text-purple-900'>Batas Maksimal Dana Bebas Capex:</span> 
                            <span class='text-purple-700'>" . Helper::formatRupiah($availableForCapex) . "</span>
                        </div>
                    </div>
                </div>
            </div>
        ";

        return ['title' => "Analisis Capex: {$itemName}", 'content' => $html];
    }

    private static function analyzeRabProjects(PDO $pdo): array {
        try {
            $stmt = $pdo->query("
                SELECT r.*, 
                       (SELECT COUNT(*) FROM rab_items ri WHERE ri.rab_id = r.id) as total_items
                FROM rabs r
                ORDER BY r.created_at DESC
                LIMIT 6
            ");
            $rabs = $stmt->fetchAll();

            $totalSubmitted = (int)$pdo->query("SELECT COUNT(*) FROM rabs WHERE status = 'submitted'")->fetchColumn();
            $totalBudgetSubmitted = (int)$pdo->query("SELECT COALESCE(SUM(budget_total), 0) FROM rabs WHERE status = 'submitted'")->fetchColumn();
            $totalApproved = (int)$pdo->query("SELECT COUNT(*) FROM rabs WHERE status = 'approved'")->fetchColumn();
            $totalBudgetApproved = (int)$pdo->query("SELECT COALESCE(SUM(budget_total), 0) FROM rabs WHERE status = 'approved'")->fetchColumn();
        } catch (Exception $e) {
            $rabs = [];
            $totalSubmitted = 0;
            $totalBudgetSubmitted = 0;
            $totalApproved = 0;
            $totalBudgetApproved = 0;
        }

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-purple-700 block'>Modul Operasional & Proyek</span>
                        <h6 class='font-black text-slate-900 text-sm'>Analisis Status & Anggaran RAB Proyek</h6>
                    </div>
                    <a href='" . Helper::url('rab') . "' class='px-3 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Buka Menu RAB</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>

                <div class='grid grid-cols-1 sm:grid-cols-2 gap-3'>
                    <div class='p-3 rounded-2xl bg-amber-50/80 border border-amber-200/70'>
                        <span class='text-3xs font-bold text-amber-700 uppercase tracking-wider block'>Menunggu Persetujuan (Submitted)</span>
                        <div class='flex items-baseline justify-between mt-1'>
                            <span class='text-base font-black text-amber-900'>{$totalSubmitted} Pengajuan</span>
                            <span class='font-mono font-bold text-amber-800 text-xs'>" . Helper::formatRupiah($totalBudgetSubmitted) . "</span>
                        </div>
                    </div>

                    <div class='p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200/70'>
                        <span class='text-3xs font-bold text-emerald-700 uppercase tracking-wider block'>Disetujui & Berjalan (Approved)</span>
                        <div class='flex items-baseline justify-between mt-1'>
                            <span class='text-base font-black text-emerald-900'>{$totalApproved} Proyek</span>
                            <span class='font-mono font-bold text-emerald-800 text-xs'>" . Helper::formatRupiah($totalBudgetApproved) . "</span>
                        </div>
                    </div>
                </div>
        ";

        if (empty($rabs)) {
            $html .= "<p class='text-2xs text-slate-400 italic p-3 bg-slate-50 rounded-xl'>Belum ada dokumen RAB yang dibuat di sistem.</p>";
        } else {
            $html .= "
                <div class='space-y-2'>
                    <span class='text-3xs font-extrabold uppercase tracking-wider text-slate-400 block'>Daftar RAB Terbaru:</span>
                    <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
            ";
            foreach ($rabs as $r) {
                $statusColors = [
                    'draft' => 'bg-slate-100 text-slate-700',
                    'submitted' => 'bg-amber-100 text-amber-800',
                    'approved' => 'bg-emerald-100 text-emerald-800',
                    'rejected' => 'bg-rose-100 text-rose-800',
                    'completed' => 'bg-blue-100 text-blue-800'
                ];
                $stClass = $statusColors[$r['status'] ?? 'draft'] ?? 'bg-slate-100 text-slate-700';

                $html .= "
                    <div class='p-3 flex flex-wrap items-center justify-between gap-2 hover:bg-slate-50 transition-colors text-2xs'>
                        <div>
                            <div class='flex items-center gap-2'>
                                <span class='font-mono font-bold text-purple-700'>{$r['rab_no']}</span>
                                <span class='px-2 py-0.5 rounded-full font-bold uppercase text-3xs {$stClass}'>" . strtoupper($r['status']) . "</span>
                            </div>
                            <span class='font-bold text-slate-800 text-xs block mt-0.5'>{$r['project_name']}</span>
                            <span class='text-slate-400 text-3xs'>Lokasi: " . Helper::e($r['location'] ?: '-') . " &bull; PIC: " . Helper::e($r['pic_name'] ?: '-') . " &bull; {$r['total_items']} Item</span>
                        </div>
                        <div class='text-right'>
                            <span class='text-3xs text-slate-400 block'>Total Anggaran:</span>
                            <span class='font-mono font-bold text-slate-900 text-xs'>" . Helper::formatRupiah((int)$r['budget_total']) . "</span>
                        </div>
                    </div>
                ";
            }
            $html .= "
                    </div>
                </div>
            ";
        }

        $html .= "
                <div class='p-3 bg-purple-50/60 rounded-2xl border border-purple-100 text-2xs text-purple-900'>
                    <strong>Saran AI:</strong> Pastikan pengajuan RAB yang berstatus <em>submitted</em> diperiksa ketersediaan stok di gudang terlebih dahulu agar tidak membeli ganda material yang sudah tersedia.
                </div>
            </div>
        ";

        return ['title' => 'Analisis RAB & Proyek', 'content' => $html];
    }

    private static function analyzePicPerformance(PDO $pdo): array {
        try {
            $stmt = $pdo->query("
                SELECT p.*, 
                       COUNT(c.id) as total_customers,
                       COALESCE(SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END), 0) as active_customers,
                       COALESCE(SUM(CASE WHEN i.payment_status IN ('unpaid', 'partially_paid') THEN i.balance_due ELSE 0 END), 0) as uncollected_dues
                FROM customer_pics p
                LEFT JOIN customers c ON p.id = c.pic_id
                LEFT JOIN invoices i ON c.id = i.customer_id
                GROUP BY p.id
                ORDER BY total_customers DESC
            ");
            $pics = $stmt->fetchAll();
        } catch (Exception $e) {
            $pics = [];
        }

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-purple-700 block'>Distribusi & Kinerja Wilayah</span>
                        <h6 class='font-black text-slate-900 text-sm'>Ringkasan PIC / Koordinator RT-RW</h6>
                    </div>
                    <a href='" . Helper::url('pics') . "' class='px-3 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Kelola PIC</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>
        ";

        if (empty($pics)) {
            $html .= "<p class='text-2xs text-slate-400 italic p-3 bg-slate-50 rounded-xl'>Belum ada data PIC yang terdaftar di sistem.</p>";
        } else {
            $html .= "
                <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
            ";
            foreach ($pics as $p) {
                $html .= "
                    <div class='p-3 flex flex-wrap items-center justify-between gap-3 hover:bg-slate-50 transition-colors text-2xs'>
                        <div class='flex items-center gap-3'>
                            <div class='w-8 h-8 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs font-black shrink-0'>
                                " . strtoupper(substr($p['name'], 0, 1)) . "
                            </div>
                            <div>
                                <span class='font-bold text-slate-900 block'>{$p['name']}</span>
                                <span class='text-3xs text-slate-400'>" . Helper::e($p['position'] ?: 'Koordinator') . " &bull; " . Helper::e($p['company'] ?: '-') . "</span>
                            </div>
                        </div>

                        <div class='flex items-center gap-4'>
                            <div class='text-center'>
                                <span class='text-3xs text-slate-400 block'>Pelanggan:</span>
                                <span class='font-bold text-slate-800 text-xs'>{$p['total_customers']}</span>
                            </div>
                            <div class='text-right'>
                                <span class='text-3xs text-slate-400 block'>Piutang Tertunda:</span>
                                <span class='font-mono font-bold " . ($p['uncollected_dues'] > 0 ? 'text-rose-600' : 'text-emerald-600') . " text-xs'>" . Helper::formatRupiah((int)$p['uncollected_dues']) . "</span>
                            </div>
                        </div>
                    </div>
                ";
            }
            $html .= "</div>";
        }

        $html .= "
                <div class='p-3 bg-slate-50 rounded-2xl border border-slate-200 text-2xs text-slate-600'>
                    <strong>Tips AI:</strong> Anda dapat membatasi data login setiap PIC agar hanya melihat pelanggan dan tagihan yang mereka kelola lewat menu <em>Pengaturan > Pengguna & Role</em>.
                </div>
            </div>
        ";

        return ['title' => 'Analisis Data PIC & Koordinator', 'content' => $html];
    }

    private static function analyzeOverdueReceivables(PDO $pdo): array {
        $stmt = $pdo->query("
            SELECT i.invoice_no, i.balance_due, i.due_date, i.billing_period, c.name as customer_name, c.phone, p.name as pic_name
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            LEFT JOIN customer_pics p ON c.pic_id = p.id
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue')
            ORDER BY i.due_date ASC
            LIMIT 10
        ");
        $invoices = $stmt->fetchAll();

        $totalUnpaidCount = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status IN ('unpaid', 'partially_paid')")->fetchColumn();
        $totalDue = (int)$pdo->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE payment_status IN ('unpaid', 'partially_paid')")->fetchColumn();

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-rose-600 block'>Penagihan & Aging Piutang</span>
                        <h6 class='font-black text-slate-900 text-sm'>Daftar Tagihan Belum Terbayar: <span class='text-rose-600'>" . Helper::formatRupiah($totalDue) . "</span></h6>
                    </div>
                    <a href='" . Helper::url('receivables') . "' class='px-3 py-1 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Aging Piutang</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>

                <div class='p-3 rounded-2xl bg-rose-50/70 border border-rose-200/70 flex items-center justify-between text-2xs'>
                    <div>
                        <span class='font-bold text-rose-900 block'>Total {$totalUnpaidCount} Tagihan Tertunda</span>
                        <span class='text-rose-700 text-3xs'>Perlu tindakan penagihan atau pengingat WhatsApp</span>
                    </div>
                    <span class='px-3 py-1 rounded-full bg-rose-600 text-white font-extrabold font-mono text-xs shadow-soft-xs'>
                        " . Helper::formatRupiah($totalDue) . "
                    </span>
                </div>
        ";

        if (empty($invoices)) {
            $html .= "<p class='text-2xs text-emerald-600 font-bold p-3 bg-emerald-50 rounded-xl'>Luar biasa! Tidak ada tagihan tertunggak saat ini.</p>";
        } else {
            $html .= "
                <div class='space-y-2'>
                    <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
            ";
            foreach ($invoices as $inv) {
                $isOverdue = strtotime($inv['due_date']) < time();
                $cleanPhone = preg_replace('/[^0-9]/', '', $inv['phone']);
                $waText = urlencode("Halo Bapak/Ibu " . $inv['customer_name'] . ", menginfokan tagihan internet NusantaraNet periode " . $inv['billing_period'] . " (" . $inv['invoice_no'] . ") sebesar " . Helper::formatRupiah($inv['balance_due']) . " sudah jatuh tempo. Mohon segera melakukan pembayaran. Terima kasih.");

                $html .= "
                    <div class='p-3 flex flex-wrap items-center justify-between gap-2 hover:bg-slate-50 transition-colors text-2xs'>
                        <div>
                            <div class='flex items-center gap-2'>
                                <span class='font-bold text-slate-900'>{$inv['customer_name']}</span>
                                " . ($isOverdue ? "<span class='px-1.5 py-0.5 rounded-md bg-rose-100 text-rose-700 font-bold text-3xs uppercase'>Lewat Jatuh Tempo</span>" : "<span class='px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 font-bold text-3xs uppercase'>Belum Bayar</span>") . "
                            </div>
                            <span class='text-slate-400 text-3xs block mt-0.5'>Inv: {$inv['invoice_no']} &bull; Jatuh Tempo: " . Helper::formatDate($inv['due_date']) . (!empty($inv['pic_name']) ? " &bull; PIC: " . Helper::e($inv['pic_name']) : '') . "</span>
                        </div>

                        <div class='flex items-center gap-3'>
                            <span class='font-mono font-bold text-rose-600 text-xs'>" . Helper::formatRupiah($inv['balance_due']) . "</span>
                            " . (!empty($cleanPhone) ? "
                            <a href='https://wa.me/{$cleanPhone}?text={$waText}' target='_blank' class='p-1.5 px-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-3xs transition-colors inline-flex items-center gap-1' title='Kirim WA Pengingat'>
                                <i class='fa-brands fa-whatsapp text-xs'></i>
                                <span>Ingatkan</span>
                            </a>" : "") . "
                        </div>
                    </div>
                ";
            }
            $html .= "
                    </div>
                </div>
            ";
        }

        $html .= "
                <p class='text-2xs text-slate-500'><strong>Rekomendasi AI:</strong> Lakukan isolir bertahap bagi pelanggan yang sudah melewati masa tenggang lebih dari 7 hari.</p>
            </div>
        ";

        return ['title' => 'Analisis Piutang & Overdue', 'content' => $html];
    }

    private static function analyzeInventoryAlerts(PDO $pdo): array {
        $stmt = $pdo->query("
            SELECT * FROM inventory_items 
            WHERE current_stock <= min_stock AND status = 'active'
            ORDER BY current_stock ASC
        ");
        $lowItems = $stmt->fetchAll();

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-amber-600 block'>Logistik & Persediaan Gudang</span>
                        <h6 class='font-black text-slate-900 text-sm'>Peringatan Stok Menipis: <span class='text-amber-600'>" . count($lowItems) . " Item</span></h6>
                    </div>
                    <a href='" . Helper::url('inventory') . "' class='px-3 py-1 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Katalog Stok</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>
        ";

        if (empty($lowItems)) {
            $html .= "
                <div class='p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-center space-y-1'>
                    <i class='fa-solid fa-boxes-stacked text-2xl text-emerald-500 mb-1 block'></i>
                    <p class='font-bold text-xs'>Semua Persediaan Aman</p>
                    <p class='text-2xs opacity-90'>Seluruh barang di gudang berada di atas batas minimum persediaan.</p>
                </div>
            ";
        } else {
            $html .= "
                <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
            ";
            foreach ($lowItems as $it) {
                $isCritical = ($it['current_stock'] <= 0);
                $html .= "
                    <div class='p-3 flex flex-wrap items-center justify-between gap-2 hover:bg-slate-50 transition-colors text-2xs'>
                        <div>
                            <div class='flex items-center gap-2'>
                                <span class='font-bold text-slate-900'>{$it['name']}</span>
                                " . ($isCritical ? "<span class='px-1.5 py-0.5 rounded-md bg-rose-100 text-rose-700 font-bold text-3xs uppercase'>HABIS (0)</span>" : "<span class='px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 font-bold text-3xs uppercase'>MENIPIS</span>") . "
                            </div>
                            <span class='text-slate-400 text-3xs'>SKU: {$it['sku']} &bull; Min Stock: {$it['min_stock']} {$it['unit']}</span>
                        </div>

                        <div class='text-right'>
                            <span class='text-3xs text-slate-400 block'>Sisa Stok:</span>
                            <span class='font-mono font-black " . ($isCritical ? 'text-rose-600' : 'text-amber-600') . " text-xs'>{$it['current_stock']} {$it['unit']}</span>
                        </div>
                    </div>
                ";
            }
            $html .= "</div>";
        }

        $html .= "
                <div class='p-3 bg-purple-50/60 rounded-2xl border border-purple-100 text-2xs text-purple-900'>
                    <strong>Saran AI:</strong> Segera terbitkan <em>Barang Masuk / PO</em> untuk modem ONT dan konektor dropcore agar tidak menghambat pemasangan pelanggan baru.
                </div>
            </div>
        ";

        return ['title' => 'Peringatan Stok Gudang', 'content' => $html];
    }

    private static function analyzeFinancialHealth(PDO $pdo): array {
        $cash = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();
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
        $fin = $stmtFin->fetch();
        $net = ($fin['total_income'] ?? 0) - ($fin['total_expense'] ?? 0);

        $accounts = $pdo->query("SELECT * FROM finance_accounts WHERE status = 'active' ORDER BY current_balance DESC")->fetchAll();

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-purple-700 block'>Kesehatan Finansial & Kas</span>
                        <h6 class='font-black text-slate-900 text-sm'>Periode Berjalan: <span class='text-purple-700'>" . date('F Y') . "</span></h6>
                    </div>
                    <a href='" . Helper::url('cashflow') . "' class='px-3 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Lihat Cashflow</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>

                <div class='grid grid-cols-1 sm:grid-cols-3 gap-3 text-2xs'>
                    <div class='p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200/70'>
                        <span class='text-3xs font-bold text-emerald-700 uppercase tracking-wider block'>Total Pemasukan</span>
                        <span class='font-mono font-black text-emerald-800 text-sm block mt-1'>" . Helper::formatRupiah($fin['total_income'] ?? 0) . "</span>
                    </div>
                    <div class='p-3 rounded-2xl bg-rose-50/80 border border-rose-200/70'>
                        <span class='text-3xs font-bold text-rose-700 uppercase tracking-wider block'>Total Pengeluaran</span>
                        <span class='font-mono font-black text-rose-800 text-sm block mt-1'>" . Helper::formatRupiah($fin['total_expense'] ?? 0) . "</span>
                    </div>
                    <div class='p-3 rounded-2xl bg-purple-50/80 border border-purple-200/70'>
                        <span class='text-3xs font-bold text-purple-700 uppercase tracking-wider block'>Net Laba / Kas Masuk</span>
                        <span class='font-mono font-black " . ($net >= 0 ? 'text-purple-900' : 'text-rose-700') . " text-sm block mt-1'>" . Helper::formatRupiah($net) . "</span>
                    </div>
                </div>

                <div class='space-y-2'>
                    <span class='text-3xs font-extrabold uppercase tracking-wider text-slate-400 block'>Rincian Saldo Rekening Kas & Bank:</span>
                    <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
        ";

        foreach ($accounts as $acc) {
            $accTitle = Helper::e($acc['account_name'] ?? $acc['bank_name'] ?? 'Akun Kas');
            $html .= "
                <div class='p-2.5 px-3 flex justify-between items-center text-2xs hover:bg-slate-50'>
                    <div class='flex items-center gap-2'>
                        <i class='fa-solid fa-building-columns text-slate-400'></i>
                        <span class='font-bold text-slate-800'>{$accTitle} (" . Helper::e($acc['account_number'] ?? '-') . ")</span>
                    </div>
                    <span class='font-mono font-bold text-slate-900'>" . Helper::formatRupiah((int)$acc['current_balance']) . "</span>
                </div>
            ";
        }

        $html .= "
                    </div>
                </div>
            </div>
        ";

        return ['title' => 'Analisis Kesehatan Finansial', 'content' => $html];
    }

    private static function analyzeCustomerInsights(PDO $pdo): array {
        $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $activeCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
        $isolatedCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'isolated'")->fetchColumn();

        $pkgStmt = $pdo->query("
            SELECT p.name, p.download_speed, p.price, COUNT(c.id) as total_users 
            FROM internet_packages p 
            LEFT JOIN customers c ON p.id = c.package_id 
            GROUP BY p.id 
            ORDER BY total_users DESC
        ");
        $packages = $pkgStmt->fetchAll();

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3'>
                    <div>
                        <span class='text-3xs font-extrabold tracking-wider uppercase text-purple-700 block'>Insight Pelanggan & Paket</span>
                        <h6 class='font-black text-slate-900 text-sm'>Total: {$totalCustomers} Pelanggan ({$activeCustomers} Aktif)</h6>
                    </div>
                    <a href='" . Helper::url('customers') . "' class='px-3 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-2xs transition-colors inline-flex items-center gap-1'>
                        <span>Data Pelanggan</span>
                        <i class='fa-solid fa-arrow-right text-3xs'></i>
                    </a>
                </div>

                <div class='grid grid-cols-3 gap-2 text-2xs text-center'>
                    <div class='p-2.5 rounded-xl bg-emerald-50 border border-emerald-200'>
                        <span class='text-3xs text-emerald-600 font-bold uppercase block'>Aktif</span>
                        <span class='text-sm font-black text-emerald-800'>{$activeCustomers}</span>
                    </div>
                    <div class='p-2.5 rounded-xl bg-amber-50 border border-amber-200'>
                        <span class='text-3xs text-amber-600 font-bold uppercase block'>Isolir</span>
                        <span class='text-sm font-black text-amber-800'>{$isolatedCustomers}</span>
                    </div>
                    <div class='p-2.5 rounded-xl bg-purple-50 border border-purple-200'>
                        <span class='text-3xs text-purple-600 font-bold uppercase block'>Total User</span>
                        <span class='text-sm font-black text-purple-900'>{$totalCustomers}</span>
                    </div>
                </div>

                <div class='space-y-2'>
                    <span class='text-3xs font-extrabold uppercase tracking-wider text-slate-400 block'>Distribusi Paket Terpopuler:</span>
                    <div class='divide-y divide-slate-100 rounded-2xl border border-slate-200/80 overflow-hidden bg-white'>
        ";

        foreach ($packages as $pkg) {
            $html .= "
                <div class='p-2.5 px-3 flex justify-between items-center text-2xs hover:bg-slate-50'>
                    <div>
                        <span class='font-bold text-slate-800 block'>{$pkg['name']} ({$pkg['download_speed']} Mbps)</span>
                        <span class='text-3xs text-slate-400'>" . Helper::formatRupiah($pkg['price']) . " / bulan</span>
                    </div>
                    <span class='px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-3xs'>
                        {$pkg['total_users']} User
                    </span>
                </div>
            ";
        }

        $html .= "
                    </div>
                </div>
            </div>
        ";

        return ['title' => 'Insight Pelanggan & Paket', 'content' => $html];
    }

    private static function getExecutiveOverview(PDO $pdo): array {
        $stats = self::getQuickStats($pdo);

        $html = "
            <div class='space-y-4 text-xs'>
                <div class='flex items-center gap-2.5 border-b border-slate-200/80 pb-3'>
                    <div class='w-8 h-8 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs font-black shrink-0'>
                        <i class='fa-solid fa-robot'></i>
                    </div>
                    <div>
                        <h6 class='font-black text-slate-900 text-sm'>Ringkasan Operasional & Keuangan Cerdas</h6>
                        <span class='text-3xs text-slate-400'>Analisis otomatis seluruh modul sistem</span>
                    </div>
                </div>

                <p class='text-slate-600 leading-relaxed text-2xs'>
                    Halo! Saya <strong>AI Advisor NusantaraNet</strong>. Sistem Anda saat ini mengelola <strong>{$stats['active_customers']} pelanggan aktif</strong>, total kas likuid <strong>" . Helper::formatRupiah($stats['cash_balance']) . "</strong>, dan " . ($stats['unpaid_invoices_count'] > 0 ? "<strong class='text-rose-600'>{$stats['unpaid_invoices_count']} tagihan belum bayar (" . Helper::formatRupiah($stats['unpaid_invoices_total']) . ")</strong>" : "<strong class='text-emerald-600'>seluruh tagihan lancar</strong>") . ".
                </p>

                <div class='grid grid-cols-2 sm:grid-cols-4 gap-2 text-2xs'>
                    <div class='p-2.5 rounded-xl bg-slate-50 border border-slate-200/80'>
                        <span class='text-3xs text-slate-400 block'>Pelanggan Aktif:</span>
                        <span class='font-black text-slate-900 text-xs'>{$stats['active_customers']}</span>
                    </div>
                    <div class='p-2.5 rounded-xl bg-slate-50 border border-slate-200/80'>
                        <span class='text-3xs text-slate-400 block'>Kas Likuid:</span>
                        <span class='font-mono font-bold text-purple-700 text-xs'>" . Helper::formatRupiah($stats['cash_balance']) . "</span>
                    </div>
                    <div class='p-2.5 rounded-xl bg-slate-50 border border-slate-200/80'>
                        <span class='text-3xs text-slate-400 block'>Piutang Berjalan:</span>
                        <span class='font-mono font-bold text-rose-600 text-xs'>" . Helper::formatRupiah($stats['unpaid_invoices_total']) . "</span>
                    </div>
                    <div class='p-2.5 rounded-xl bg-slate-50 border border-slate-200/80'>
                        <span class='text-3xs text-slate-400 block'>RAB Menunggu:</span>
                        <span class='font-bold text-amber-700 text-xs'>{$stats['pending_rabs_count']} Proyek</span>
                    </div>
                </div>

                <div class='p-3 bg-purple-50/70 rounded-2xl border border-purple-100 text-2xs text-purple-900'>
                    <strong>💡 Pertanyaan yang bisa Anda ajukan ke AI:</strong>
                    <ul class='list-disc list-inside mt-1 space-y-0.5 text-3xs text-purple-800'>
                        <li><em>\"Bisakah kita beli OLT GPON Rp 35.000.000 bulan ini?\"</em></li>
                        <li><em>\"Bagaimana status RAB proyek dan pengajuan yang belum disetujui?\"</em></li>
                        <li><em>\"Siapa saja pelanggan yang menunggak dan berapa total piutangnya?\"</em></li>
                        <li><em>\"Bagaimana performa dan data pelanggan per PIC / Koordinator?\"</em></li>
                        <li><em>\"Barang apa saja di gudang yang stoknya sudah menipis?\"</em></li>
                    </ul>
                </div>
            </div>
        ";

        return ['title' => 'Ikhtisar Eksekutif Bisnis', 'content' => $html];
    }
}
