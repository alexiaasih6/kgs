<?php
$admin_page_title = 'Katalog Layanan (22 Standar Resmi)';
require_once __DIR__ . '/layout-header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            log_audit($pdo, 'Hapus Standar Layanan', "Menghapus standar layanan ID: $id");
            $msg = 'Item standar layanan berhasil dihapus dari katalog.';
        }
    }
}

$services = $pdo->query("SELECT s.*, c.name as category_name, c.segment_number FROM services s JOIN service_categories c ON s.category_id = c.id ORDER BY c.segment_number ASC, s.sort_order ASC, s.id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Katalog Layanan (<?= count($services) ?> Standar Resmi)</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola, edit spesifikasi, tambah, atau hapus daftar standar layanan PT. Kreatif Global Solusindo.</p>
    </div>
    <a href="tambah-layanan.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
        + Tambah Standar Layanan
    </a>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($msg) ?>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 border-b uppercase font-semibold text-[10px] text-slate-500">
                <tr>
                    <th class="p-3.5 w-12 text-center">No.</th>
                    <th class="p-3.5 w-48">Segmen Bisnis</th>
                    <th class="p-3.5 w-64">Nama Standar Layanan</th>
                    <th class="p-3.5">Ringkasan Spesifikasi</th>
                    <th class="p-3.5 w-36 text-center">Status Standar</th>
                    <th class="p-3.5 w-36 text-right">Aksi (Edit / Hapus)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $no = 1; foreach ($services as $s): ?>
                <tr class="hover:bg-slate-50">
                    <td class="p-3.5 text-center font-mono text-slate-500 font-bold"><?= $no++ ?></td>
                    <td class="p-3.5 font-semibold text-slate-900">Segmen <?= e($s['segment_number']) ?>: <?= e($s['category_name']) ?></td>
                    <td class="p-3.5 font-bold text-blue-700"><?= e($s['name']) ?></td>
                    <td class="p-3.5 text-slate-600 leading-relaxed"><?= e($s['summary']) ?></td>
                    <td class="p-3.5 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            <?= e($s['verification_status'] ?: 'Standar Resmi KGS') ?>
                        </span>
                    </td>
                    <td class="p-3.5 text-right whitespace-nowrap">
                        <a href="edit-layanan.php?id=<?= (int)$s['id'] ?>" class="inline-block px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold mr-1">
                            Edit
                        </a>
                        <form method="POST" class="inline" onsubmit="return confirm('Hapus standar layanan ini dari katalog?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
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

<?php require_once __DIR__ . '/layout-footer.php'; ?>
