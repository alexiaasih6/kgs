<?php
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if ($is_https) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) < 32) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $session_token = $_SESSION['csrf_token'] ?? '';
        if (empty($token) || empty($session_token) || !hash_equals((string)$session_token, (string)$token)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            $is_ajax = (
                (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                (!empty($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], 'api-') !== false) ||
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            );
            if ($is_ajax) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                die(json_encode([
                    'ok' => false,
                    'error' => 'Validasi Keamanan Gagal (CSRF Token Invalid atau Sesi Kedaluwarsa). Silakan muat ulang halaman.'
                ]));
            }
            die('Validasi Keamanan Gagal (CSRF Token Invalid). Silakan muat ulang halaman dan coba lagi.');
        }
    }
}
