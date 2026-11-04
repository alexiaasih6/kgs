<?php
$admin_page_title = 'Manajemen Pengguna Admin';
require_once __DIR__ . '/layout-header.php';

$current_user_id = (int)($_SESSION['admin_id'] ?? 0);
$current_role = $_SESSION['admin_role'] ?? 'superadmin';

$msg = '';
$error = '';

// Proses Aksi: Tambah User, Edit User, Edit Password, atau Hapus User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        if ($current_role !== 'superadmin') {
            $error = 'Hanya Super Admin yang diizinkan menambahkan pengguna baru.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $name = clean_plain_text((string)($_POST['name'] ?? ''), 100);
            $email = trim($_POST['email'] ?? '');
            $role = ($_POST['role'] ?? '') === 'editor' ? 'editor' : 'superadmin';
            $password = (string)($_POST['password'] ?? '');

            if (empty($username) || empty($name) || empty($email) || strlen($password) < 8) {
                $error = 'Semua bidang wajib diisi dan password minimal 8 karakter.';
            } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $error = 'Password wajib mengandung kombinasi huruf dan angka (minimal 8 karakter).';
            } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
                $error = 'Username hanya boleh berisi huruf, angka, titik, garis bawah, atau strip (3-50 karakter).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format alamat email tidak valid.';
            } else {
                // Cek username/email unik
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt_check->execute([$username, $email]);
                if ($stmt_check->fetch()) {
                    $error = 'Username atau Email sudah terdaftar.';
                } else {
                    $hashed_pwd = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt_ins = $pdo->prepare("INSERT INTO users (username, password, name, email, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
                    $stmt_ins->execute([$username, $hashed_pwd, $name, $email, $role]);
                    log_audit($pdo, 'Tambah User', "Membuat akun admin baru: $username ($role)");
                    $msg = 'Pengguna administrator baru berhasil ditambahkan!';
                }
            }
        }
    } elseif ($action === 'edit_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $name    = clean_plain_text((string)($_POST['name'] ?? ''), 100);
        $email   = trim((string)($_POST['email'] ?? ''));
        $role    = ($_POST['role'] ?? '') === 'editor' ? 'editor' : 'superadmin';
        $status  = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($current_role !== 'superadmin' && $user_id !== $current_user_id) {
            $error = 'Anda tidak memiliki hak akses mengubah profil pengguna lain.';
        } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Nama lengkap dan alamat email yang valid wajib diisi.';
        } else {
            // Non-superadmin tidak boleh menaikkan role sendiri
            if ($current_role !== 'superadmin') {
                $role = 'editor';
                $status = 'active';
            }
            // Pastikan minimal tetap ada 1 superadmin aktif
            $stmt_cur = $pdo->prepare("SELECT role, status FROM users WHERE id = ? LIMIT 1");
            $stmt_cur->execute([$user_id]);
            $cur_u = $stmt_cur->fetch();
            if ($cur_u && $cur_u['role'] === 'superadmin' && ($role !== 'superadmin' || $status !== 'active')) {
                $cnt_super = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND status = 'active'")->fetchColumn();
                if ($cnt_super <= 1) {
                    $error = 'Tidak dapat menurunkan role atau menonaktifkan satu-satunya Super Admin aktif.';
                }
            }
            if ($error === '') {
                $stmt_dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                $stmt_dup->execute([$email, $user_id]);
                if ($stmt_dup->fetch()) {
                    $error = 'Alamat email tersebut sudah digunakan oleh akun lain.';
                } else {
                    $stmt_up = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                    $stmt_up->execute([$name, $email, $role, $status, $user_id]);
                    if ($user_id === $current_user_id) {
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['admin_role'] = $role;
                    }
                    log_audit($pdo, 'Edit User', "Memperbarui profil user ID $user_id ($name, role: $role, status: $status)");
                    $msg = 'Data profil dan hak akses pengguna berhasil diperbarui.';
                }
            }
        }
    } elseif ($action === 'change_password') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $new_pwd = (string)($_POST['new_password'] ?? '');

        if ($current_role !== 'superadmin' && $user_id !== $current_user_id) {
            $error = 'Anda tidak memiliki hak akses mengubah kata sandi pengguna lain.';
        } elseif (strlen($new_pwd) < 8 || !preg_match('/[A-Za-z]/', $new_pwd) || !preg_match('/[0-9]/', $new_pwd)) {
            $error = 'Password baru minimal 8 karakter dan wajib mengandung kombinasi huruf serta angka.';
        } else {
            $hashed = password_hash($new_pwd, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt_up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_up->execute([$hashed, $user_id]);
            log_audit($pdo, 'Ganti Password', "Mengubah kata sandi untuk user ID: $user_id");
            $msg = 'Kata sandi berhasil diperbarui dengan enkripsi BCRYPT yang aman!';
        }
    } elseif ($action === 'delete_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($current_role !== 'superadmin') {
            $error = 'Hanya Super Admin yang diizinkan menghapus pengguna.';
        } elseif ($user_id === $current_user_id) {
            $error = 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.';
        } else {
            $stmt_target = $pdo->prepare("SELECT username, role FROM users WHERE id = ? LIMIT 1");
            $stmt_target->execute([$user_id]);
            $target_user = $stmt_target->fetch();
            if (!$target_user) {
                $error = 'Akun pengguna tidak ditemukan.';
            } else {
                if ($target_user['role'] === 'superadmin') {
                    $cnt_super = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND status = 'active'")->fetchColumn();
                    if ($cnt_super <= 1) {
                        $error = 'Tidak dapat menghapus satu-satunya Super Admin aktif di sistem.';
                    }
                }
                if ($error === '') {
                    $stmt_del = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt_del->execute([$user_id]);
                    log_audit($pdo, 'Hapus User', "Menghapus akun admin ID: $user_id ({$target_user['username']})");
                    $msg = 'Pengguna berhasil dihapus.';
                }
            }
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Manajemen Pengguna Admin & Keamanan</h1>
        <p class="text-xs text-slate-500">Kelola hak akses role (Super Admin, Editor) dan keamanan kata sandi BCRYPT.</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3.5 rounded-xl font-medium">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Tambah User Baru -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs">
            <h2 class="text-sm font-bold text-slate-900 border-b pb-3">Tambah Pengguna Baru</h2>
            <form method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_user">

                <div>
                    <label class="block font-semibold mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required class="w-full border rounded-lg p-2.5">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Username *</label>
                    <input type="text" name="username" required class="w-full border rounded-lg p-2.5 font-mono">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Email Resmi *</label>
                    <input type="email" name="email" required class="w-full border rounded-lg p-2.5">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Role / Peran *</label>
                    <select name="role" class="w-full border rounded-lg p-2.5 bg-white">
                        <option value="superadmin">Super Admin (Akses Penuh)</option>
                        <option value="editor">Editor (Kelola Konten & Layanan)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Kata Sandi Awal *</label>
                    <input type="password" name="password" minlength="8" required placeholder="Min. 8 karakter (huruf & angka)" class="w-full border rounded-lg p-2.5 font-mono">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition">
                        + Daftarkan Administrator
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Pengguna -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Daftar Administrator Aktif</h3>
                <span class="text-xs text-slate-400">Total: <?= count($users) ?> Akun</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                <?php foreach ($users as $u): ?>
                    <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2.5">
                                <span class="font-extrabold text-slate-900 text-sm"><?= e($u['name']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $u['role'] === 'superadmin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= e($u['role']) ?>
                                </span>
                                <?php if (($u['status'] ?? 'active') !== 'active'): ?>
                                    <span class="text-[10px] bg-rose-100 text-rose-700 px-2 py-0.5 rounded font-bold">Nonaktif</span>
                                <?php endif; ?>
                                <?php if ($u['id'] == $current_user_id): ?>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.2 rounded font-bold">Anda</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-slate-500 font-mono text-[11px] flex gap-3">
                                <span>Username: <strong><?= e($u['username']) ?></strong></span>
                                <span>Email: <?= e($u['email']) ?></span>
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Login Terakhir: <?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Belum pernah login' ?>
                            </div>
                        </div>

                        <!-- Actions: Edit Profil, Ganti Password & Hapus -->
                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Popover Edit User -->
                            <details class="relative">
                                <summary class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded-lg border border-amber-200 cursor-pointer text-[11px] list-none">
                                    ✏️ Edit Profil
                                </summary>
                                <div class="absolute right-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-xl p-4 z-20 space-y-3">
                                    <span class="font-bold text-slate-900 block text-xs">Edit Profil &amp; Hak Akses</span>
                                    <form method="POST" class="space-y-2.5">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="edit_user">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Nama Lengkap</label>
                                            <input type="text" name="name" value="<?= e($u['name']) ?>" required class="w-full border rounded-lg p-2 text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Email</label>
                                            <input type="email" name="email" value="<?= e($u['email']) ?>" required class="w-full border rounded-lg p-2 text-xs">
                                        </div>
                                        <?php if ($current_role === 'superadmin'): ?>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Role</label>
                                                <select name="role" class="w-full border rounded-lg p-2 text-xs bg-white">
                                                    <option value="superadmin" <?= $u['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                                                    <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Status</label>
                                                <select name="status" class="w-full border rounded-lg p-2 text-xs bg-white">
                                                    <option value="active" <?= ($u['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="inactive" <?= ($u['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <button type="submit" class="w-full py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold">
                                            Simpan Perubahan
                                        </button>
                                    </form>
                                </div>
                            </details>

                            <!-- Popover Reset Password -->
                            <details class="relative">
                                <summary class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border cursor-pointer text-[11px] list-none">
                                    🔑 Ganti Sandi
                                </summary>
                                <div class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl p-4 z-20 space-y-3">
                                    <span class="font-bold text-slate-900 block text-xs">Ganti Kata Sandi</span>
                                    <form method="POST" class="space-y-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="change_password">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <input type="password" name="new_password" minlength="8" required placeholder="Min. 8 kar (huruf & angka)" class="w-full border rounded-lg p-2 text-xs">
                                        <button type="submit" class="w-full py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold">
                                            Simpan Sandi Baru
                                        </button>
                                    </form>
                                </div>
                            </details>

                            <?php if ($u['id'] != $current_user_id && $current_role === 'superadmin'): ?>
                                <form method="POST" onsubmit="return confirm('Hapus pengguna <?= e($u['username']) ?>?');" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-lg text-[11px] font-semibold">
                                        Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
