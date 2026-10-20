<?php
/**
 * Middleware Proteksi Autentikasi Admin CMS
 * Memastikan hanya pengguna terautentikasi yang dapat mengakses halaman panel admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Wajib login sebagai admin dengan sesi yang valid
require_admin();

// Variabel sesi pengguna aktif untuk kemudahan di seluruh halaman admin
$admin_id       = (int)($_SESSION['admin_id'] ?? 0);
$admin_username = (string)($_SESSION['admin_username'] ?? 'admin_kgs');
$admin_name     = (string)($_SESSION['admin_name'] ?? 'Administrator');
$admin_role     = (string)($_SESSION['admin_role'] ?? 'superadmin');
