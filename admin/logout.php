<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['admin_logged_in'])) {
    $uname = (string)($_SESSION['admin_username'] ?? 'admin');
    log_audit($pdo, 'Logout Admin', "User '$uname' keluar dari sesi Admin CMS.");
}

logout_admin();

header("Location: login.php");
exit;

