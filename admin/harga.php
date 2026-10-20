<?php
$admin_page_title = 'Kelola Paket Harga & Solusi B2B';
require_once __DIR__ . '/layout-header.php';

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt_edit = $pdo->prepare("SELECT * FROM pricing_packages WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $edit_data = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id            = (int)($_POST['id'] ?? 0);
        $code_label    = trim($_POST['code_label'] ?? 'ISP');
        $name          = trim($_POST['name'] ?? '');
        $target_sector = trim($_POST['target_sector'] ?? '');
        $subtitle      = trim($_POST['subtitle'] ?? '');
        $price_label   = trim($_POST['price_label'] ?? '');
        $sla_badge     = trim($_POST['sla_badge'] ?? '');
        $features      = trim($_POST['features'] ?? '');
        $badge_text    = trim($_POST['badge_text'] ?? '');
        $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
        $is_active     = isset($_POST['is_active']) ? 1 : 0;
        $sort_order    = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || empty($features)) {
            $error = 'Nama paket dan daftar fitur utama wajib diisi.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO pricing_packages (code_label, name, target_sector, subtitle, price_label, sla_badge, features, badge_text, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code_label, $name, $target_sector, $subtitle, $price_label, $sla_badge, $features, $badge_text, $is_featured, $is_active, $sort_order]);
                log_audit($pdo, 'Tambah Paket Harga', "Menambahkan paket harga: $name");
                $msg = 'Paket harga B2B baru berhasil ditambahkan!';
            } else {
                $stmt = $pdo->prepare("UPDATE pricing_packages SET code_label = ?, name = ?, target_sector = ?, subtitle = ?, price_label = ?, sla_badge = ?, features = ?, badge_text = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$code_label, $name, $target_sector, $subtitle, $price_label, $sla_badge, $features, $badge_text, $is_featured, $is_active, $sort_order, $id]);
                log_audit($pdo, 'Edit Paket Harga', "Memperbarui paket harga ID $id: $name");
                $msg = 'Paket harga B2B berhasil diperbarui!';
                $edit_data = null;
                $edit_id = 0;
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM pricing_packages WHERE id = ?");
            $stmt->execute([$id]);
            log_audit($pdo, 'Hapus Paket Harga', "Menghapus paket harga ID: $id");
            $msg = 'Paket harga berhasil dihapus.';
        }
    }
}

$packages = $pdo->query("SELECT * FROM pricing_packages ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Paket Harga & Solusi Bisnis (B2B)</h1>
        <p class="text-xs text-slate-500">Tambah, edit, atau hapus paket harga yang tampil pada menu Harga di website publik.</p>
    </div>
    <a href="../harga.php" target="_blank" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
        Lihat Halaman Harga &nearr;
    </a>
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
    <!-- Form Tambah / Edit -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit">
        <h2 class="font-bold text-slate-900 text-sm mb-4 pb-2 border-b">
            <?= $edit_data ? 'Edit Paket Harga' : 'Tambah Paket Harga Baru' ?>
        </h2>
        <form method="POST" class="space-y-3.5 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Singkat *</label>
                    <input type="text" name="code_label" required value="<?= e($edit_data['code_label'] ?? 'ISP') ?>" placeholder="ISP / CCTV / TOW" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="<?= (int)($edit_data['sort_order'] ?? 1) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Paket Solusi *</label>
                <input type="text" name="name" required value="<?= e($edit_data['name'] ?? '') ?>" placeholder="Contoh: Dedicated Internet B2B (KGSNet)" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Target Sektor</label>
                <input type="text" name="target_sector" value="<?= e($edit_data['target_sector'] ?? '') ?>" placeholder="Contoh: Perkantoran & Industri" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Ringkasan Singkat</label>
                <input type="text" name="subtitle" value="<?= e($edit_data['subtitle'] ?? '') ?>" placeholder="Deskripsi singkat 1 kalimat" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Label Estimasi Harga / Tarif</label>
                <input type="text" name="price_label" value="<?= e($edit_data['price_label'] ?? 'Sesuai Kapasitas & BoQ') ?>" placeholder="Contoh: Mulai Rp 3.500.000 / bln atau Sesuai BoQ" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Jaminan / SLA Badge</label>
                <input type="text" name="sla_badge" value="<?= e($edit_data['sla_badge'] ?? 'SLA 99.5% Uptime Terjamin') ?>" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Daftar Fitur (1 baris per fitur) *</label>
                <textarea name="features" rows="5" required placeholder="Bandwidth 1:1 CIR Simetris&#10;Dedicated IP Public Static" class="w-full border border-slate-300 rounded-lg p-2"><?= e($edit_data['features'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Teks Pita Unggulan (Opsional)</label>
                <input type="text" name="badge_text" value="<?= e($edit_data['badge_text'] ?? '') ?>" placeholder="Contoh: Solusi Utama Terlaris" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div class="flex items-center gap-4 pt-1">
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" <?= (!empty($edit_data['is_featured'])) ? 'checked' : '' ?>>
                    <span class="font-semibold text-slate-700">Paket Unggulan</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_data['is_active']) || $edit_data['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="font-semibold text-slate-700">Aktif Tampil</span>
                </label>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition cursor-pointer">
                    <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Paket' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="harga.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Paket Harga -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs h-fit">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Daftar Paket Harga B2B (<?= count($packages) ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 bg-slate-50/50">
                        <th class="p-3.5">Paket & Sektor</th>
                        <th class="p-3.5">Harga / SLA</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($packages as $pkg): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-bold rounded text-[10px]"><?= e($pkg['code_label']) ?></span>
                                <span class="font-bold text-slate-900"><?= e($pkg['name']) ?></span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5"><?= e($pkg['target_sector']) ?> — <?= e($pkg['subtitle']) ?></div>
                        </td>
                        <td class="p-3.5">
                            <div class="font-semibold text-slate-800"><?= e($pkg['price_label'] ?: '-') ?></div>
                            <div class="text-[11px] text-emerald-700"><?= e($pkg['sla_badge']) ?></div>
                        </td>
                        <td class="p-3.5">
                            <?php if ($pkg['is_active']): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Draft</span>
                            <?php endif; ?>
                            <?php if ($pkg['is_featured']): ?>
                                <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Unggulan</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3.5 text-right whitespace-nowrap">
                            <a href="harga.php?edit_id=<?= (int)$pkg['id'] ?>" class="inline-block px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold mr-1">
                                Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus paket harga ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$pkg['id'] ?>">
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
