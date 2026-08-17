<?php
// app/Middleware/AuthMiddleware.php - Route Protection and Access Gate

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/Helper.php';

class AuthMiddleware {

    public static function handle(?string $permission = null): void {
        if (!AuthService::check()) {
            Helper::setFlash('warning', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
            Helper::redirect('login');
        }

        if ($permission !== null && !AuthService::hasPermission($permission)) {
            Helper::setFlash('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
            Helper::redirect('dashboard');
        }
    }
}
