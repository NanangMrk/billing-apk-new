<?php
// app/Helpers/Helper.php - Global Utility and Formatting Helper

class Helper {

    public static function baseUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = dirname($scriptName);
        if ($dir === '/' || $dir === '\\') {
            $dir = '';
        }
        return rtrim($protocol . $host . $dir, '/');
    }

    public static function url(string $page = '', array $params = []): string {
        $base = self::baseUrl();
        $query = [];
        if ($page !== '') {
            $query['page'] = $page;
        }
        $query = array_merge($query, $params);
        $queryString = http_build_query($query);
        return $base . '/index.php' . ($queryString ? '?' . $queryString : '');
    }

    public static function asset(string $path): string {
        return self::baseUrl() . '/public/assets/' . ltrim($path, '/');
    }

    public static function uploadUrl(?string $path): string {
        if (empty($path)) return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return self::baseUrl() . '/public/' . ltrim($path, '/');
    }

    public static function redirect(string $page, array $params = []): void {
        header("Location: " . self::url($page, $params));
        exit;
    }

    public static function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function formatRupiah(int|float $amount, bool $withSymbol = true): string {
        $formatted = number_format((float)$amount, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }

    public static function formatDate(?string $dateString, string $format = 'd M Y'): string {
        if (!$dateString) return '-';
        $time = strtotime($dateString);
        if (!$time) return '-';

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $shortMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $d = date('j', $time);
        $m = (int)date('n', $time);
        $y = date('Y', $time);

        if ($format === 'd F Y') {
            return "$d {$months[$m]} $y";
        } elseif ($format === 'd M Y') {
            return "$d {$shortMonths[$m]} $y";
        } elseif ($format === 'd/m/Y H:i') {
            return date('d/m/Y H:i', $time);
        }

        return date($format, $time);
    }

    public static function statusBadge(string $status): string {
        $status = strtolower($status);
        $map = [
            'active' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Aktif'],
            'paid' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Lunas'],
            'completed' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Selesai'],
            'resolved' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Terselesaikan'],
            'approved' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Disetujui'],
            
            'unpaid' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Belum Bayar'],
            'partially_paid' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Bayar Sebagian'],
            'in_progress' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Proses Realisasi'],
            'assigned' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Ditugaskan'],
            'open' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Terbuka'],
            'submitted' => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'label' => 'Diajukan'],
            'draft' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Draft'],
            
            'suspended' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Isolir / Suspend'],
            'overdue' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Jatuh Tempo'],
            'inactive' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Nonaktif'],
            'terminated' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Berhenti'],
            'cancelled' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Dibatalkan'],
            'rejected' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Ditolak'],
            'low_stock' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Stok Menipis'],
            'out_of_stock' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Habis'],
        ];

        $badge = $map[$status] ?? ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => ucfirst($status)];

        return sprintf(
            '<span class="%s border text-3xs font-extrabold px-2.5 py-0.5 rounded-full inline-block tracking-tight text-center uppercase leading-tight">%s</span>',
            $badge['bg'],
            htmlspecialchars($badge['label'])
        );
    }

    public static function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type' => $type, // success, error, warning, info
            'message' => $message
        ];
    }

    public static function getFlash(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function csrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="_token" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function logActivity(string $module, string $action, ?string $recordId = null, ?string $oldVal = null, ?string $newVal = null): void {
        try {
            $pdo = getDbConnection();
            $userId = $_SESSION['user']['id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, module, action, record_id, old_value, new_value, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $module, $action, $recordId, $oldVal, $newVal, $ip, $ua]);
        } catch (Exception $e) {
            error_log("Failed to write activity log: " . $e->getMessage());
        }
    }
}
