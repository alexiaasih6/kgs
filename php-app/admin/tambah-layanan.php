<?php
$admin_page_title = 'Tambah Layanan Baru';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$error = '';
$categories = $pdo->query("SELECT * FROM service_categories ORDER BY segment_number ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $summary = trim($_POST['summary'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pdf_page = (int)($_POST['pdf_page'] ?? 4);
    $verification_status = $_POST['verification_status'] ?? 'Standar Resmi KGS';
    $admin_notes = trim($_POST['admin_notes'] ?? 'Standar Layanan Resmi PT. KGS');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 1);

    if (empty($name) || empty($summary) || $category_id === 0) {
        $error = 'Nama layanan, kategori segmen, dan ringkasan wajib diisi.';
    } else {
        try {
            $main_image = handle_image_upload('main_image_file', 'uploads/', $pdo);
            $slug = create_slug($name);
            $stmt_check = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
            $stmt_check->execute([$slug]);
            if ($stmt_check->fetch()) {
                $slug .= '-' . time();
            }

            $stmt = $pdo->prepare("INSERT INTO services (category_id, name, slug, summary, description, main_image, pdf_page, verification_status, admin_notes, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $slug, $summary, $description ?: $summary, $main_image, $pdf_page, $verification_status, $admin_notes, $is_published, $sort_order]);

            log_audit($pdo, 'Tambah Layanan', "Menambahkan layanan baru: $name");
            header("Location: layanan.php?msg=added");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Tambah Layanan Baru</h1>
            <p class="text-xs text-slate-500">Tambahkan solusi layanan baru ke katalog website PT. KGS.</p>
        </div>
        <a href="layanan.php" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">&larr; Kembali ke Daftar</a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3.5 rounded-xl font-medium">
        <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Nama Layanan *</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Contoh: Instalasi Fiber Optic Backbone" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block font-semibold mb-1">Kategori Segmen Resmi *</label>
                <select name="category_id" required class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>">Segmen <?= (int)$c['segment_number'] ?>: <?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-1">Status Katalog *</label>
                <select name="verification_status" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <option value="Standar Resmi KGS">Standar Resmi KGS</option>
                    <option value="Tambahan Disetujui Admin">Tambahan Disetujui Admin</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Urutan Tampil</label>
                <input type="number" name="sort_order" value="1" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block font-semibold mb-1">Gambar Ilustrasi (Opsional)</label>
                <input type="file" name="main_image_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full border border-slate-300 rounded-lg p-2 bg-slate-50">
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1">Ringkasan Layanan *</label>
            <textarea name="summary" rows="2" required class="w-full border border-slate-300 rounded-lg p-2.5"><?= e($_POST['summary'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Deskripsi Lengkap & Spesifikasi Teknis</label>
            <textarea name="description" rows="4" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Catatan Katalog</label>
            <input type="text" name="admin_notes" value="Standar Layanan Resmi PT. KGS" class="w-full border border-slate-300 rounded-lg p-2.5">
        </div>

        <div class="pt-2">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300">
                <span class="font-semibold">Terbitkan langsung ke website publik</span>
            </label>
        </div>

        <div class="pt-4 border-t flex justify-end gap-3">
            <a href="layanan.php" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-semibold">Batal</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer">
                Simpan Layanan
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
