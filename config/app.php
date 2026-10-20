<?php
/**
 * Konfigurasi Aplikasi Global - PT. Kreatif Global Solusindo (KGS)
 * File ini mendeteksi protokol dan direktori secara otomatis sehingga aman dijalankan
 * di root domain (public_html) maupun di dalam subfolder tanpa mengubah kode.
 */

if (!defined('APP_NAME')) {
    define('APP_NAME', 'PT. KREATIF GLOBAL SOLUSINDO');
    define('APP_TAGLINE', 'System Integrator, Security Solution & Internet Service Provider');
    define('APP_VERSION', '2.5.0-production');
}

// Mode produksi: matikan tampilan error teknis ke pengunjung publik
define('APP_DEBUG', false);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Zona Waktu Standar Indonesia Barat (Palembang / Jakarta)
date_default_timezone_set('Asia/Jakarta');

// Kirim HTTP Security Headers standar produksi jika dijalankan via web server
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Deteksi Base URL Otomatis (Kompatibel dengan HTTPS, Cloudflare, cPanel, CyberPanel, & Subfolder)
if (!defined('BASE_URL')) {
    $is_https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    $protocol = $is_https ? 'https://' : 'http://';
    $raw_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Sanitasi host header untuk mencegah Host Header Injection
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $raw_host) ?: 'localhost';

    // Hitung path relatif terhadap document root
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Jika dipanggil dari dalam folder /admin, naik satu level ke root aplikasi
    if (substr($script_dir, -6) === '/admin') {
        $script_dir = substr($script_dir, 0, -6);
    }
    $base_path = rtrim($script_dir, '/');

    define('BASE_URL', $protocol . $host . $base_path);
}
