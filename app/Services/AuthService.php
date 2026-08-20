<?php
// app/Services/AuthService.php - User Authentication and Session Management

require_once __DIR__ . '/../../config/database.php';

class AuthService {
    private const SECRET_KEY = 'ISP_BILLING_APP_KEY_PERMANENT_SESSION_NANANGMRK_2026';
    private const REMEMBER_COOKIE = 'billing_remember_session';
    private const COOKIE_EXPIRY = 31536000 * 5; // 5 years

    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                ini_set('session.cookie_lifetime', (string)self::COOKIE_EXPIRY);
                ini_set('session.gc_maxlifetime', (string)self::COOKIE_EXPIRY);
                if (PHP_VERSION_ID >= 70300) {
                    session_set_cookie_params([
                        'lifetime' => self::COOKIE_EXPIRY,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                } else {
                    session_set_cookie_params(self::COOKIE_EXPIRY, '/');
                }
            }
            @session_start();
        }
    }

    public static function attempt(string $username, string $password, bool $remember = true): bool {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name, r.display_name as role_display 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = ? OR u.email = ?) AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            self::initSession();

            // Fetch user permissions
            $permStmt = $pdo->prepare("
                SELECT p.name 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
            ");
            $permStmt->execute([$user['role_id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

            $passwordHash = $user['password'];
            unset($user['password']);
            $_SESSION['user'] = $user;
            $_SESSION['permissions'] = $permissions;
            $_SESSION['last_activity'] = time();

            // Set Persistent Remember Me Cookie
            if ($remember) {
                self::setRememberToken((int)$user['id'], $user['username'], $passwordHash);
            }

            // Update last login
            $upd = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$user['id']]);

            Helper::logActivity('AUTH', 'LOGIN', (string)$user['id'], null, 'User logged in');
            return true;
        }

        return false;
    }

    private static function setRememberToken(int $userId, string $username, string $passwordHash): void {
        $signature = hash_hmac('sha256', "{$userId}:{$username}:{$passwordHash}", self::SECRET_KEY);
        $payload = base64_encode("{$userId}:{$username}:{$signature}");
        if (!headers_sent()) {
            setcookie(self::REMEMBER_COOKIE, $payload, time() + self::COOKIE_EXPIRY, '/', '', false, true);
        }
    }

    private static function tryAutoLoginWithToken(): ?array {
        if (empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return null;
        }

        $raw = base64_decode($_COOKIE[self::REMEMBER_COOKIE]);
        if (!$raw || !str_contains($raw, ':')) {
            return null;
        }

        $parts = explode(':', $raw, 3);
        if (count($parts) !== 3) {
            return null;
        }

        [$userId, $username, $signature] = $parts;

        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT u.*, r.name as role_name, r.display_name as role_display 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.status = 'active'
                LIMIT 1
            ");
            $stmt->execute([(int)$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                self::clearRememberToken();
                return null;
            }

            $expectedSignature = hash_hmac('sha256', "{$user['id']}:{$user['username']}:{$user['password']}", self::SECRET_KEY);
            if (!hash_equals($expectedSignature, $signature)) {
                self::clearRememberToken();
                return null;
            }

            // Fetch permissions
            $permStmt = $pdo->prepare("
                SELECT p.name 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
            ");
            $permStmt->execute([$user['role_id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

            unset($user['password']);
            $_SESSION['user'] = $user;
            $_SESSION['permissions'] = $permissions;
            $_SESSION['last_activity'] = time();

            return $user;
        } catch (Exception $e) {
            error_log("Auto-login failed: " . $e->getMessage());
            return null;
        }
    }

    public static function clearRememberToken(): void {
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            if (!headers_sent()) {
                setcookie(self::REMEMBER_COOKIE, '', time() - 360000, '/');
            }
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        }
    }

    public static function user(): ?array {
        self::initSession();
        if (!empty($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        return self::tryAutoLoginWithToken();
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function isPic(): bool {
        $user = self::user();
        return $user && ($user['role_name'] === 'pic' || !empty($user['pic_id']));
    }

    public static function getPicId(): ?int {
        $user = self::user();
        return ($user && !empty($user['pic_id'])) ? (int)$user['pic_id'] : null;
    }

    public static function hasPermission(string $permission): bool {
        self::initSession();
        $user = self::user();
        if (!$user) {
            return false;
        }
        $roleName = $user['role_name'] ?? '';
        if ($roleName === 'super_admin') {
            return true;
        }
        $perms = $_SESSION['permissions'] ?? [];
        return in_array($permission, $perms, true);
    }

    public static function logout(): void {
        self::initSession();
        if (isset($_SESSION['user']['id'])) {
            Helper::logActivity('AUTH', 'LOGOUT', (string)$_SESSION['user']['id'], null, 'User logged out');
        }
        self::clearRememberToken();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
