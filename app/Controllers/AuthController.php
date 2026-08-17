<?php
// app/Controllers/AuthController.php - Authentication Controller

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/Helper.php';

class AuthController {

    public function login(): void {
        if (AuthService::check()) {
            Helper::redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token keamanan CSRF tidak valid. Silakan coba lagi.');
                Helper::redirect('login');
            }

            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                Helper::setFlash('error', 'Username dan password wajib diisi.');
            } else {
                if (AuthService::attempt($username, $password)) {
                    Helper::setFlash('success', 'Selamat datang kembali, ' . AuthService::user()['name'] . '!');
                    Helper::redirect('dashboard');
                } else {
                    Helper::setFlash('error', 'Username atau password yang Anda masukkan salah.');
                }
            }
        }

        $pageTitle = 'Masuk Portal ISP';
        ob_start();
        require __DIR__ . '/../Views/auth/login.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/auth.php';
    }

    public function logout(): void {
        AuthService::logout();
        Helper::setFlash('info', 'Anda telah berhasil keluar dari sistem.');
        Helper::redirect('landing');
    }
}
