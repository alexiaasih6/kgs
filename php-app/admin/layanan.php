<?php
$admin_page_title = 'Kelola Layanan & Solusi';
require_once __DIR__ . '/layout-header.php';

$msg = $_GET['msg'] ?? '';

// Toggle Publish / Unpublish via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $action = $_POST['action'];
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE services SET is_published = NOT is_published WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, 'Toggle Layanan', "Mengubah status publikasi layanan ID: $id");
        header("Location: layanan.php?msg=updated");
        exit;
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, 'Hapus Layanan', "Menghapus layanan ID: $id");
        header("Location: layanan.php?msg=deleted");
        exit;
    }
}

$services = $pdo->query("SELECT s.*, c.name as category_name, c.segment_number FROM services s JOIN service_categories c ON s.category_id = c.id ORDER BY c.segment_number ASC, s.sort_order ASC, s.id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Daftar Layanan & Solusi Terintegrasi (<?= count($services) ?>)</h1>
        <p class="text-xs text-slate-500">Kelola seluruh katalog layanan pada 5 Segmen Bisnis PT. Kreatif Global Solusindo.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="audit-pdf.php" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold rounded-xl transition">
            📋 Mode Katalog 22 Standar
        </a>
        <a href="tambah-layanan.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
            + Tambah Layanan Baru
        </a>
    </div>
</div>

<?php if ($msg === 'added'): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        Layanan baru berhasil ditambahkan ke katalog!
    </div>
<?php elseif ($msg === 'updated'): ?>
    <div class="bg-blue-100 border border-blue-300 text-blue-800 text-xs p-3.5 rounded-xl font-medium">
        Perubahan data layanan berhasil disimpan!
    </div>
<?php elseif ($msg === 'deleted'): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
        Layanan berhasil dihapus dari database.
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-[10px] text-slate-500">
                <tr>
                    <th class="p-3.5">Nama Layanan</th>
                    <th class="p-3.5">Kategori Segmen</th>
                    <th class="p-3.5">Status Standar</th>
                    <th class="p-3.5 text-center">Publikasi</th>
                    <th class="p-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">Belum ada data layanan.</td>
                    </tr>
                <?php else: foreach ($services as $s): ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-3.5">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($s['main_image'])): ?>
                                <img src="../<?= e($s['main_image']) ?>" alt="<?= e($s['name']) ?>" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                            <?php endif; ?>
                            <div>
                                <a href="../detail-layanan.php?slug=<?= e($s['slug']) ?>" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block">
                                    <?= e($s['name']) ?>
                                </a>
                                <div class="text-[11px] text-slate-400 line-clamp-1"><?= e($s['summary']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="p-3.5 text-slate-600 font-medium whitespace-nowrap">
                        <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold mr-1">Segmen <?= (int)$s['segment_number'] ?></span>
                        <?= e($s['category_name']) ?>
                    </td>
                    <td class="p-3.5 whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            <?= e($s['verification_status'] ?: 'Standar Resmi KGS') ?>
                        </span>
                    </td>
                    <td class="p-3.5 text-center whitespace-nowrap">
                        <form method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="px-2.5 py-0.5 rounded-full font-bold text-[10px] cursor-pointer <?= $s['is_published'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' ?>">
                                <?= $s['is_published'] ? 'Terbit' : 'Draft' ?>
                            </button>
                        </form>
                    </td>
                    <td class="p-3.5 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="edit-layanan.php?id=<?= (int)$s['id'] ?>" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold text-[11px]">
                                Edit
                            </a>
                            <form method="POST" action="layanan.php" onsubmit="return confirm('Hapus layanan ini secara permanen?');" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-[11px] cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
