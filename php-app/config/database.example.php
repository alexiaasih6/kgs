<?php
declare(strict_types=1);

/**
 * CONTOH TEMPLATE KONFIGURASI DATABASE (TANPA KREDENSIAL RAHASIA)
 * Salin file ini menjadi 'config/database.php' lalu sesuaikan dengan kredensial MySQL Anda.
 */

if (basename($_SERVER['PHP_SELF'] ?? '') === 'database.example.php') {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

require_once __DIR__ . '/app.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'nama_database_anda');
define('DB_USER', 'username_database_anda');
define('DB_PASS', 'password_database_anda');
define('DB_CHARSET', 'utf8mb4');
