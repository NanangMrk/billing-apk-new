<?php
// app/Services/AiAssistantService.php - Safe AI Business & Financial Advisor Engine

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';

class AiAssistantService {

    public static function ask(string $prompt, ?int $userId = null): array {
        $pdo = getDbConnection();
        $promptLower = strtolower($prompt);

        // Safe tool execution based on business intent
        if (str_contains($promptLower, 'olt') || str_contains($promptLower, 'beli') || str_contains($promptLower, 'mampu') || str_contains($promptLower, 'afford')) {
            return self::evaluateCapitalExpenditure($pdo, 35000000, '1 Unit OLT GPON 8 Port');
        } elseif (str_contains($promptLower, 'belum bayar') || str_contains($promptLower, 'overdue') || str_contains($promptLower, 'piutang')) {
            return self::analyzeOverdueReceivables($pdo);
        } elseif (str_contains($promptLower, 'stok') || str_contains($promptLower, 'habis') || str_contains($promptLower, 'menipis') || str_contains($promptLower, 'inventory')) {
            return self::analyzeInventoryAlerts($pdo);
        } elseif (str_contains($promptLower, 'laba') || str_contains($promptLower, 'profit') || str_contains($promptLower, 'pengeluaran') || str_contains($promptLower, 'keuangan') || str_contains($promptLower, 'kas')) {
            return self::analyzeFinancialHealth($pdo);
        }

        // Default holistic business summary
        return self::getExecutiveOverview($pdo);
    }

    private static function evaluateCapitalExpenditure(PDO $pdo, int $cost, string $itemName): array {
        // 1. Total Cash & Bank
        $cash = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();

        // 2. Expected Collections (Unpaid current invoices)
        $receivables = (int)$pdo->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE payment_status IN ('unpaid', 'partially_paid')")->fetchColumn();

        // 3. Upcoming Obligations (Payroll + Monthly Bandwidth)
        $payroll = (int)$pdo->query("SELECT COALESCE(SUM(basic_salary), 0) FROM employees WHERE status = 'active'")->fetchColumn();
        $bandwidthEst = 15000000; // Average bandwidth cost
        $reserveMinimum = 10000000; // Emergency buffer

        $totalObligations = $payroll + $bandwidthEst + $reserveMinimum;
        $availableForCapex = $cash + ($receivables * 0.8) - $totalObligations;
        $isFeasible = ($availableForCapex >= $cost);

        $html = "
            <div class='space-y-3 text-xs'>
                <p class='font-bold text-slate-800 text-sm'>
                    Analisis Kelayakan Belanja Modal: <span class='text-purple-700'>{$itemName} (" . Helper::formatRupiah($cost) . ")</span>
                </p>
                <div class='p-3.5 rounded-xl " . ($isFeasible ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-orange-50 border border-orange-200 text-orange-800') . "'>
                    <p class='font-bold mb-1'>" . ($isFeasible ? '✅ KEPUTUSAN: LAYAK DIBELI' : '⚠️ KEPUTUSAN: DITUNDA / PERLU RESTRUKTURISASI') . "</p>
                    <p class='text-2xs leading-relaxed'>
                        " . ($isFeasible 
                            ? "Kas operasional dan proyeksi penagihan mencukupi setelah memperhitungkan cadangan likuiditas darurat serta beban gaji dan bandwidth bulanan." 
                            : "Sisa likuiditas terlalu tipis jika dipaksakan beli tunai sekaligus. Disarankan menunggu penagihan piutang berjalan atau menggunakan skema termin vendor.") . "
                    </p>
                </div>

                <div class='p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5 font-mono text-2xs'>
                    <div class='flex justify-between'><span class='text-slate-500'>Saldo Kas & Bank Saat Ini:</span> <span class='font-bold text-slate-800'>" . Helper::formatRupiah($cash) . "</span></div>
                    <div class='flex justify-between'><span class='text-slate-500'>Estimasi Penagihan Piutang (80%):</span> <span class='text-green-600 font-bold'>+" . Helper::formatRupiah($receivables * 0.8) . "</span></div>
                    <div class='flex justify-between'><span class='text-slate-500'>Estimasi Beban Gaji (Payroll):</span> <span class='text-red-500'>-" . Helper::formatRupiah($payroll) . "</span></div>
                    <div class='flex justify-between'><span class='text-slate-500'>Beban Bandwidth Upstream:</span> <span class='text-red-500'>-" . Helper::formatRupiah($bandwidthEst) . "</span></div>
                    <div class='flex justify-between'><span class='text-slate-500'>Cadangan Kas Minimal (Buffer):</span> <span class='text-slate-600'>-" . Helper::formatRupiah($reserveMinimum) . "</span></div>
                    <div class='flex justify-between pt-1.5 border-t border-slate-300 font-bold text-xs'>
                        <span>Dana Bebas Capex:</span> <span class='text-purple-700'>" . Helper::formatRupiah($availableForCapex) . "</span>
                    </div>
                </div>
            </div>
        ";

        return ['role' => 'assistant', 'content' => $html];
    }

    private static function analyzeOverdueReceivables(PDO $pdo): array {
        $stmt = $pdo->query("
            SELECT i.invoice_no, i.balance_due, i.due_date, c.name as customer_name, c.phone 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            WHERE i.payment_status IN ('unpaid', 'partially_paid', 'overdue')
            ORDER BY i.due_date ASC
        ");
        $invoices = $stmt->fetchAll();
        $totalDue = array_sum(array_column($invoices, 'balance_due'));

        $html = "
            <div class='space-y-3 text-xs'>
                <p class='font-bold text-slate-800 text-sm'>
                    Laporan Tagihan Belum Terbayar: <span class='text-red-600'>" . count($invoices) . " Invoice (" . Helper::formatRupiah($totalDue) . ")</span>
                </p>
                <div class='space-y-2'>
        ";

        foreach ($invoices as $inv) {
            $html .= "
                <div class='p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex justify-between items-center text-2xs'>
                    <div>
                        <span class='font-bold text-slate-800 block'>{$inv['customer_name']} ({$inv['invoice_no']})</span>
                        <span class='text-slate-400'>Jatuh Tempo: " . Helper::formatDate($inv['due_date']) . " &bull; WA: {$inv['phone']}</span>
                    </div>
                    <span class='font-bold text-red-600 font-mono'>" . Helper::formatRupiah($inv['balance_due']) . "</span>
                </div>
            ";
        }

        $html .= "
                </div>
                <p class='text-2xs text-slate-500'>Rekomendasi AI: Kirimkan broadcast pengingat otomatis via WhatsApp untuk invoice yang sudah mendekati atau melewati jatuh tempo.</p>
            </div>
        ";

        return ['role' => 'assistant', 'content' => $html];
    }

    private static function analyzeInventoryAlerts(PDO $pdo): array {
        $stmt = $pdo->query("
            SELECT * FROM inventory_items 
            WHERE current_stock <= min_stock AND status = 'active'
            ORDER BY current_stock ASC
        ");
        $lowItems = $stmt->fetchAll();

        $html = "
            <div class='space-y-3 text-xs'>
                <p class='font-bold text-slate-800 text-sm'>
                    Peringatan Persediaan Barang: <span class='text-orange-600'>" . count($lowItems) . " Item Perlu Restock</span>
                </p>
        ";

        if (empty($lowItems)) {
            $html .= "<p class='text-2xs text-green-600 font-bold'>Semua item stok berada dalam batas aman.</p>";
        } else {
            $html .= "<div class='space-y-2'>";
            foreach ($lowItems as $it) {
                $html .= "
                    <div class='p-2.5 rounded-lg bg-orange-50 border border-orange-200 flex justify-between items-center text-2xs'>
                        <div>
                            <span class='font-bold text-slate-800 block'>{$it['name']} ({$it['sku']})</span>
                            <span class='text-slate-500'>Batas Minimum: {$it['min_stock']} {$it['unit']}</span>
                        </div>
                        <span class='font-bold text-red-600 font-mono'>Sisa {$it['current_stock']} {$it['unit']}</span>
                    </div>
                ";
            }
            $html .= "</div>";
        }

        $html .= "
                <p class='text-2xs text-slate-500'>Rekomendasi AI: Buat Purchase Order (PO) ke supplier terkait sebelum persediaan dropcore atau modem habis.</p>
            </div>
        ";

        return ['role' => 'assistant', 'content' => $html];
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
        $net = $fin['total_income'] - $fin['total_expense'];

        $html = "
            <div class='space-y-3 text-xs'>
                <p class='font-bold text-slate-800 text-sm'>
                    Ringkasan Kesehatan Finansial Periode <span class='text-purple-700'>" . date('F Y') . "</span>
                </p>
                <div class='grid grid-cols-2 gap-2 text-2xs font-mono'>
                    <div class='p-2.5 rounded-lg bg-slate-50 border border-slate-200'>
                        <span class='text-slate-400 block'>Total Pemasukan:</span>
                        <span class='font-bold text-green-600 text-xs'>" . Helper::formatRupiah($fin['total_income']) . "</span>
                    </div>
                    <div class='p-2.5 rounded-lg bg-slate-50 border border-slate-200'>
                        <span class='text-slate-400 block'>Total Pengeluaran:</span>
                        <span class='font-bold text-red-600 text-xs'>" . Helper::formatRupiah($fin['total_expense']) . "</span>
                    </div>
                </div>
                <div class='p-3 rounded-xl bg-purple-50 border border-purple-200 flex justify-between items-center text-xs font-bold'>
                    <span class='text-purple-900'>Laba Bersih Riil:</span>
                    <span class='" . ($net >= 0 ? 'text-green-700' : 'text-red-600') . " font-mono'>" . Helper::formatRupiah($net) . "</span>
                </div>
                <p class='text-2xs text-slate-500'>Kas likuid saat ini: <strong>" . Helper::formatRupiah($cash) . "</strong>.</p>
            </div>
        ";

        return ['role' => 'assistant', 'content' => $html];
    }

    private static function getExecutiveOverview(PDO $pdo): array {
        $customers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
        $invoices = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE billing_period = '" . date('Y-m') . "'")->fetchColumn();
        $cash = (int)$pdo->query("SELECT COALESCE(SUM(current_balance), 0) FROM finance_accounts WHERE status = 'active'")->fetchColumn();

        $html = "
            <div class='space-y-2 text-xs'>
                <p class='font-bold text-slate-800 text-sm'>Hai, saya Asisten AI NusantaraNet! 🤖</p>
                <p class='text-slate-600 leading-relaxed'>
                    Saat ini Anda memiliki <strong>{$customers} pelanggan aktif</strong> dengan <strong>{$invoices} tagihan</strong> rilis bulan ini dan total saldo kas <strong>" . Helper::formatRupiah($cash) . "</strong>.
                </p>
                <p class='text-slate-500 text-2xs'>Anda bisa menanyakan kelayakan pembelian alat (seperti OLT/Splicer), daftar piutang, status persediaan gudang, atau laporan laba rugi.</p>
            </div>
        ";

        return ['role' => 'assistant', 'content' => $html];
    }
}
