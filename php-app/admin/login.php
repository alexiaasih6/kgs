<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// Cek apakah tabel users ada dan sudah terisi
try {
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (Exception $e) {
    // Fallback jika database lama
    $user_count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
}

if ($user_count == 0) {
    header("Location: register-admin.php");
    exit;
}

$error = '';
$msg = $_GET['msg'] ?? '';
if ($msg === 'session_expired') {
    $error = 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali.';
} elseif ($msg === 'registered') {
    $success = 'Akun administrator berhasil dibuat. Silakan login.';
}

// Pembatasan percobaan login (Rate Limiting via Session)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

if (time() - $_SESSION['last_attempt_time'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($_SESSION['login_attempts'] >= 5) {
        $error = 'Terlalu banyak percobaan gagal. Akses terkunci selama 15 menit demi keamanan.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password)) {
            // Cek di tabel users
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
            } catch (Exception $e) {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                if ($user) $user['role'] = 'superadmin';
            }

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'] ?? 'superadmin';
                $_SESSION['last_activity'] = time();
                $_SESSION['login_attempts'] = 0;

                // Update last_login
                try {
                    $up = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $up->execute([$user['id']]);
                } catch (Exception $ignore) {}

                log_audit($pdo, 'LOGIN_SUCCESS', 'Administrator ' . $user['username'] . ' berhasil masuk.');

                header("Location: index.php");
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $sisa = 5 - $_SESSION['login_attempts'];
                log_audit($pdo, 'LOGIN_FAILED', 'Percobaan login gagal untuk username: ' . $username);
                $error = "Kredensial username atau kata sandi tidak cocok. (Sisa percobaan: {$sisa})";
            }
        } else {
            $error = 'Silakan isi seluruh bidang login.';
        }
    }
}

$company_name = get_setting($pdo, 'company_name') ?: 'PT. KREATIF GLOBAL SOLUSINDO';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin CMS - <?= e($company_name) ?></title>
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
            <h1 class="text-xl font-bold mt-3 text-white">Admin CMS Portal</h1>
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
                <label class="block text-xs font-semibold text-slate-300 mb-1">Username atau Email</label>
                <input type="text" name="username" required autocomplete="username"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-lg shadow-md transition cursor-pointer">
                Masuk ke Panel Kontrol
            </button>
        </form>

        <div class="pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
            <a href="../index.php" class="hover:text-white transition">&larr; Website Publik</a>
            <span class="text-[11px] font-mono">Bcrypt & RBAC Guard</span>
        </div>
    </div>
</body>
</html>
