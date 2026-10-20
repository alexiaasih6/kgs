<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Hanya diizinkan jika belum ada user sama sekali di database
try {
    $user_count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (Exception $e) {
    $user_count = 0;
}

if ($user_count > 0) {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $weak_passwords = ['password', 'password123', 'admin123', '12345678', '123456789', 'qwerty123', 'superadmin', 'kgs12345'];

    if (empty($name) || empty($email) || empty($username)) {
        $error = 'Seluruh bidang wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]{4,32}$/', $username)) {
        $error = 'Username harus terdiri dari 4–32 karakter huruf, angka, titik, atau garis bawah.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal harus 8 karakter.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Kata sandi wajib mengandung kombinasi huruf besar, huruf kecil, dan angka.';
    } elseif (in_array(strtolower($password), $weak_passwords, true) || strtolower($password) === strtolower($username)) {
        $error = 'Kata sandi terlalu mudah ditebak. Gunakan kata sandi unik yang kuat.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role, status) VALUES (?, ?, ?, ?, 'superadmin', 'active')");
        $stmt->execute([$username, $hashed_password, $name, $email]);

        log_audit($pdo, 'ADMIN_INITIALIZED', "Super Administrator $username berhasil diinisialisasi.");

        header("Location: login.php?msg=registered");
        exit;
    }
}

$company_name = get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO');
$favicon_path = get_setting($pdo, 'favicon', 'assets/images/favicon.svg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Super Admin Pertama - <?= e($company_name) ?></title>
    <link rel="icon" href="../<?= e($favicon_path) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-2xl space-y-6">
        <div class="text-center">
            <span class="inline-block px-3 py-1 bg-amber-950 border border-amber-600 text-amber-300 text-[11px] font-semibold rounded-full mb-3">
                Setup Awal Sistem Aman
            </span>
            <h1 class="text-xl font-bold text-white">Buat Akun Super Administrator</h1>
            <p class="text-xs text-slate-400 mt-1">
                Tentukan kredensial administrator mandiri Anda secara aman. Halaman ini otomatis terkunci permanen setelah akun pertama dibuat.
            </p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-rose-900/50 border border-rose-600 text-rose-200 text-xs p-3 rounded-lg">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 text-xs">
            <?= csrf_field() ?>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nama Lengkap Administrator *</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Contoh: Administrator Utama"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Alamat Email Resmi *</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="admin@kgs.net.id"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Username Admin (4–32 karakter) *</label>
                <input type="text" name="username" required value="<?= e($_POST['username'] ?? '') ?>" placeholder="admin_kgs"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Password Kuat (Min. 8 Karakter, Huruf Besar, Kecil & Angka) *</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Ulangi Password *</label>
                <input type="password" name="password_confirm" required minlength="8"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg shadow-md transition cursor-pointer mt-2">
                Simpan & Aktifkan Super Admin
            </button>
        </form>
    </div>
</body>
</html>
