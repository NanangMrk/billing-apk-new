<?php
// app/Services/AuthService.php - User Authentication and Session Management

require_once __DIR__ . '/../../config/database.php';

class AuthService {

    public static function attempt(string $username, string $password): bool {
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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Fetch user permissions
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

            // Update last login
            $upd = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$user['id']]);

            Helper::logActivity('AUTH', 'LOGIN', (string)$user['id'], null, 'User logged in');
            return true;
        }

        return false;
    }

    public static function user(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function isPic(): bool {
        $user = self::user();
        return $user && ($user['role_name'] === 'pic');
    }

    public static function getPicId(): ?int {
        $user = self::user();
        return ($user && $user['pic_id']) ? (int)$user['pic_id'] : null;
    }

    public static function hasPermission(string $permission): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $roleName = $_SESSION['user']['role_name'] ?? '';
        if ($roleName === 'super_admin') {
            return true;
        }
        $perms = $_SESSION['permissions'] ?? [];
        return in_array($permission, $perms, true);
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user']['id'])) {
            Helper::logActivity('AUTH', 'LOGOUT', (string)$_SESSION['user']['id'], null, 'User logged out');
        }
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
