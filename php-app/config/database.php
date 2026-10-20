<?php
declare(strict_types=1);

/**
 * Konfigurasi Database MySQL / MariaDB - PT. Kreatif Global Solusindo (KGS)
 * 
 * PETUNJUK HOSTING (cPanel / CyberPanel / Shared Hosting / XAMPP):
 * Cukup sesuaikan nilai DB_HOST, DB_NAME, DB_USER, dan DB_PASS di bawah ini
 * sesuai dengan database MySQL yang telah Anda buat di panel hosting.
 */

if (basename($_SERVER['PHP_SELF'] ?? '') === 'database.php') {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/app.php';

// Kredensial Database (Sesuaikan dengan cPanel / CyberPanel / Lokal Anda)
define('DB_HOST', 'localhost');
define('DB_NAME', 'demo_isp1');
define('DB_USER', 'demo_isp1');
define('DB_PASS', 'UC%WqVedIKVCPfme');
define('DB_CHARSET', 'utf8mb4');

$db_config = [
    'host'     => DB_HOST,
    'database' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset'  => DB_CHARSET,
];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db_config['charset']}"
    ];
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
} catch (PDOException $e) {
    // Catat detail teknis ke error log server, jangan pernah menampilkan password/detail DB ke pengunjung
    error_log('[KGS Database Error] ' . $e->getMessage());
    http_response_code(503);
    die("<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Layanan Sedang Dalam Konfigurasi - PT. Kreatif Global Solusindo</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { max-width: 520px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        .badge { display: inline-block; padding: 4px 10px; background: #fee2e2; color: #991b1b; border-radius: 9999px; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
        h1 { font-size: 20px; margin: 0 0 10px; color: #0f172a; }
        p { font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 16px; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 13px; color: #0f172a; }
    </style>
</head>
<body>
    <div class='card'>
        <span class='badge'>Koneksi Database Belum Terhubung</span>
        <h1>Sistem Sedang Menunggu Konfigurasi Database</h1>
        <p>Pastikan berkas <code>database.sql</code> telah diimpor melalui <strong>phpMyAdmin</strong> dan kredensial pada <code>config/database.php</code> telah disesuaikan dengan akun MySQL hosting Anda.</p>
    </div>
</body>
</html>");
}

require_once __DIR__ . '/../includes/functions.php';
