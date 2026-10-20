<?php
$admin_page_title = 'Kelola Kategori Segmen Layanan';
require_once __DIR__ . '/layout-header.php';

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt_edit = $pdo->prepare("SELECT * FROM service_categories WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $edit_data = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $segment_number = (int)($_POST['segment_number'] ?? 1);
        $description = trim($_POST['description'] ?? '');
        $pdf_page = (int)($_POST['pdf_page'] ?? 4);

        if (empty($name) || empty($code)) {
            $error = 'Nama kategori dan kode unik wajib diisi.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO service_categories (code, segment_number, name, description, pdf_page) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $segment_number, $name, $description, $pdf_page]);
                log_audit($pdo, 'Tambah Kategori Layanan', "Menambahkan kategori: $name");
                $msg = 'Kategori segmen baru berhasil ditambahkan.';
            } else {
                $stmt = $pdo->prepare("UPDATE service_categories SET code = ?, segment_number = ?, name = ?, description = ?, pdf_page = ? WHERE id = ?");
                $stmt->execute([$code, $segment_number, $name, $description, $pdf_page, $id]);
                log_audit($pdo, 'Edit Kategori Layanan', "Memperbarui kategori ID $id: $name");
                $msg = 'Kategori segmen berhasil diperbarui.';
                $edit_data = null;
                $edit_id = 0;
            }
        }
    } elseif ($action === 'toggle' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE service_categories SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, 'Toggle Kategori Layanan', "Mengubah status kategori ID: $id");
        $msg = 'Status kategori berhasil diperbarui.';
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id) as service_count FROM service_categories c ORDER BY segment_number ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kategori Segmen Layanan (<?= count($categories) ?>)</h1>
        <p class="text-xs text-slate-500">Kelola 5 segmen utama bisnis PT. Kreatif Global Solusindo.</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
<div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
    <?= e($msg) ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
    <?= e($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Tambah / Edit Kategori -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit">
        <h2 class="font-bold text-slate-900 text-sm mb-4 pb-2 border-b">
            <?= $edit_data ? 'Edit Kategori Segmen' : 'Tambah Kategori Segmen' ?>
        </h2>
        <form method="POST" class="space-y-3.5 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold mb-1">Nomor Segmen *</label>
                    <input type="number" name="segment_number" required value="<?= (int)($edit_data['segment_number'] ?? (count($categories) + 1)) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Kode Unik *</label>
                    <input type="text" name="code" required value="<?= e($edit_data['code'] ?? '') ?>" placeholder="comp-net" class="w-full border border-slate-300 rounded-lg p-2 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold mb-1">Nama Kategori Segmen *</label>
                <input type="text" name="name" required value="<?= e($edit_data['name'] ?? '') ?>" placeholder="Computer & Networking" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Deskripsi Segmen</label>
                <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg p-2"><?= e($edit_data['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer">
                    <?= $edit_data ? 'Simpan Perubahan' : '+ Tambah Kategori' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="kategori.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Kategori -->
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs h-fit">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b uppercase font-semibold text-[10px] text-slate-500">
                    <tr>
                        <th class="p-3.5 text-center">Segmen</th>
                        <th class="p-3.5">Nama & Deskripsi</th>
                        <th class="p-3.5 text-center">Layanan</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($categories as $cat): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3.5 font-bold text-blue-700 text-center">#<?= (int)$cat['segment_number'] ?></td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900"><?= e($cat['name']) ?> <span class="font-mono text-[10px] text-slate-400">(<?= e($cat['code']) ?>)</span></div>
                            <div class="text-[11px] text-slate-500 mt-0.5"><?= e($cat['description']) ?></div>
                        </td>
                        <td class="p-3.5 text-center font-bold"><?= (int)$cat['service_count'] ?> item</td>
                        <td class="p-3.5 text-center">
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer <?= $cat['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= $cat['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td class="p-3.5 text-right">
                            <a href="kategori.php?edit_id=<?= (int)$cat['id'] ?>" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold text-[11px]">
                                Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
