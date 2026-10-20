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

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

/**
 * Validasi sesi aktif admin (termasuk batas idle timeout 30 menit & konsistensi User-Agent)
 */
function is_admin_session_valid(): bool {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }

    $now = time();
    $idle_limit = 1800; // 30 menit
    if (isset($_SESSION['admin_last_activity']) && ($now - (int)$_SESSION['admin_last_activity']) > $idle_limit) {
        logout_admin();
        return false;
    }

    $current_ua_hash = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? 'kgs-ua'));
    if (!empty($_SESSION['admin_ua_hash']) && !hash_equals($_SESSION['admin_ua_hash'], $current_ua_hash)) {
        logout_admin();
        return false;
    }

    $_SESSION['admin_last_activity'] = $now;
    return true;
}

function is_admin_logged_in(): bool {
    return is_admin_session_valid();
}

function require_admin(): void {
    if (!is_admin_session_valid()) {
        header('Location: login.php');
        exit;
    }
}

function require_superadmin(): void {
    require_admin();
    if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        die('Akses Ditolak: Halaman atau tindakan ini memerlukan hak akses Super Admin.');
    }
}

/**
 * Hitung percobaan login gagal dari IP yang sama dalam 15 menit terakhir di database
 */
function count_recent_failed_logins(PDO $pdo, string $ip): int {
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM audit_logs 
             WHERE action = 'Login Gagal' 
               AND ip_address = ? 
               AND created_at >= (NOW() - INTERVAL 15 MINUTE)"
        );
        $stmt->execute([$ip]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function login_admin(PDO $pdo, string $username, string $password): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $username = trim($username);

    // Rate limiting sesi & database berbasis IP (Maks 5 gagal per 15 menit)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }

    if (time() - (int)($_SESSION['last_attempt_time'] ?? 0) > 900) {
        $_SESSION['login_attempts'] = 0;
    }

    $db_failed_count = count_recent_failed_logins($pdo, $ip);
    if ($_SESSION['login_attempts'] >= 5 || $db_failed_count >= 5) {
        return [
            'success' => false,
            'message' => 'Terlalu banyak percobaan login gagal. Akun/IP dikunci sementara selama 15 menit demi keamanan.'
        ];
    }

    if ($username === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Username dan kata sandi wajib diisi.'
        ];
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = (int)$user['id'];
        $_SESSION['admin_username'] = (string)$user['username'];
        $_SESSION['admin_name'] = (string)$user['name'];
        $_SESSION['admin_role'] = (string)$user['role'];
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_ua_hash'] = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? 'kgs-ua'));
        $_SESSION['login_attempts'] = 0;
        // Rotasi CSRF token setelah login berhasil
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Update last_login
        $up = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $up->execute([$user['id']]);

        log_audit($pdo, 'Login Berhasil', "Admin {$user['username']} berhasil masuk ke CMS.");
        return ['success' => true];
    }

    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    log_audit($pdo, 'Login Gagal', "Percobaan login gagal untuk username: " . mb_substr($username, 0, 50));

    $remaining = max(0, 5 - max((int)$_SESSION['login_attempts'], $db_failed_count + 1));
    return [
        'success' => false,
        'message' => "Username atau kata sandi salah. Sisa percobaan: {$remaining}"
    ];
}

function logout_admin(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
