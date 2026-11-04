<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Jika sesi admin sudah aktif dan valid, langsung ke dashboard
if (is_admin_logged_in()) {
    header("Location: index.php");
    exit;
}

// Cek apakah tabel users ada dan sudah memiliki akun (Fail-Closed jika terjadi gangguan DB)
$user_count = 1;
try {
    $user_count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (Exception $e) {
    try {
        $user_count = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    } catch (Exception $e2) {
        $user_count = 1;
    }
}

if ($user_count === 0) {
    header("Location: register-admin.php");
    exit;
}

$error = '';
$success = '';
$msg = $_GET['msg'] ?? '';
if ($msg === 'session_expired') {
    $error = 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali.';
} elseif ($msg === 'registered') {
    $success = 'Akun administrator berhasil diinisialisasi. Silakan masuk.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $auth_result = login_admin($pdo, $username, $password);
    if (!empty($auth_result['success'])) {
        header("Location: index.php");
        exit;
    } else {
        $error = $auth_result['message'] ?? 'Kredensial yang Anda masukkan tidak valid.';
    }
}

$company_name = get_setting($pdo, 'company_name') ?: 'PT. KREATIF GLOBAL SOLUSINDO';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Portal Manajemen Internal - <?= e($company_name) ?></title>
    <link rel="icon" href="../assets/images/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-2xl space-y-6">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white font-extrabold text-2xl rounded-xl shadow-lg">
                KGS
            </div>
            <h1 class="text-xl font-bold mt-3 text-white">Portal Manajemen Internal</h1>
            <p class="text-xs text-slate-400"><?= e($company_name) ?></p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-rose-900/50 border border-rose-600 text-rose-200 text-xs p-3.5 rounded-xl">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="bg-emerald-900/50 border border-emerald-600 text-emerald-200 text-xs p-3.5 rounded-xl">
            <?= e($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">ID Pengguna / Email</label>
                <input type="text" name="username" required autocomplete="username"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Kata Sandi</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-lg shadow-md transition cursor-pointer">
                Masuk ke Panel Kendali
            </button>
        </form>

        <div class="pt-4 border-t border-slate-700/60 flex items-center justify-center text-xs text-slate-400">
            <a href="../index.php" class="hover:text-white transition">&larr; Kembali ke Website Utama</a>
        </div>
    </div>
</body>
</html>
