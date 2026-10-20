<?php
$admin_page_title = 'Kelola Portofolio Klien';
require_once __DIR__ . '/layout-header.php';

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt_edit = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $edit_data = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $service_category = trim($_POST['service_category'] ?? '');
        $pdf_page = (int)($_POST['pdf_page'] ?? 8);

        if (empty($name) || empty($service_category)) {
            $error = 'Nama perusahaan klien dan kategori solusi pekerjaan wajib diisi.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO clients (name, category, location, service_category, pdf_page) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $category ?: 'Korporasi & Instansi', $location ?: null, $service_category, $pdf_page]);
                log_audit($pdo, 'Tambah Klien', "Menambahkan klien baru: $name");
                $msg = 'Data klien baru berhasil ditambahkan!';
            } else {
                $stmt = $pdo->prepare("UPDATE clients SET name = ?, category = ?, location = ?, service_category = ?, pdf_page = ? WHERE id = ?");
                $stmt->execute([$name, $category ?: 'Korporasi & Instansi', $location ?: null, $service_category, $pdf_page, $id]);
                log_audit($pdo, 'Edit Klien', "Memperbarui data klien ID $id: $name");
                $msg = 'Data klien berhasil diperbarui!';
                $edit_data = null;
                $edit_id = 0;
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->execute([$id]);
            log_audit($pdo, 'Hapus Klien', "Menghapus data klien ID: $id");
            $msg = 'Data klien berhasil dihapus.';
        }
    }
}

$clients = $pdo->query("SELECT * FROM clients ORDER BY pdf_page ASC, id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Daftar Klien Berdasarkan Proyek (<?= count($clients) ?>)</h1>
        <p class="text-xs text-slate-500">Kelola daftar klien korporasi, BUMN, rumah sakit, perbankan, dan instansi pemerintah.</p>
    </div>
    <a href="../klien.php" target="_blank" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
        Lihat Halaman Klien &nearr;
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

<!-- Form Tambah / Edit Klien -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <h2 class="text-sm font-bold text-slate-900 mb-3 pb-2 border-b">
        <?= $edit_data ? 'Edit Data Klien: ' . e($edit_data['name']) : 'Tambah Klien Proyek Baru' ?>
    </h2>
    <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block font-semibold mb-1">Nama Perusahaan / Klien *</label>
            <input type="text" name="name" required value="<?= e($edit_data['name'] ?? '') ?>" placeholder="PT. Sawit Jaya" class="w-full border border-slate-300 rounded-lg p-2">
        </div>
        <div>
            <label class="block font-semibold mb-1">Bidang Usaha</label>
            <input type="text" name="category" value="<?= e($edit_data['category'] ?? '') ?>" placeholder="Perkebunan Sawit" class="w-full border border-slate-300 rounded-lg p-2">
        </div>
        <div>
            <label class="block font-semibold mb-1">Solusi Pekerjaan *</label>
            <input type="text" name="service_category" required value="<?= e($edit_data['service_category'] ?? '') ?>" placeholder="Radiolink / PABX / Tower / ISP" class="w-full border border-slate-300 rounded-lg p-2">
        </div>
        <div>
            <label class="block font-semibold mb-1">Lokasi</label>
            <input type="text" name="location" value="<?= e($edit_data['location'] ?? '') ?>" placeholder="Palembang / Sumsel" class="w-full border border-slate-300 rounded-lg p-2">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">
                <?= $edit_data ? 'Simpan' : '+ Tambah Klien' ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="klien.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel Klien -->
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 border-b uppercase font-semibold text-[10px] text-slate-500">
                <tr>
                    <th class="p-3.5">Nama Klien / Perusahaan</th>
                    <th class="p-3.5">Bidang Usaha</th>
                    <th class="p-3.5">Solusi Pekerjaan</th>
                    <th class="p-3.5">Lokasi</th>
                    <th class="p-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $c): ?>
                <tr class="hover:bg-slate-50">
                    <td class="p-3.5 font-bold text-slate-900"><?= e($c['name']) ?></td>
                    <td class="p-3.5 text-slate-600"><?= e($c['category']) ?></td>
                    <td class="p-3.5 font-medium text-blue-700"><?= e($c['service_category']) ?></td>
                    <td class="p-3.5 text-slate-500"><?= e($c['location'] ?: '-') ?></td>
                    <td class="p-3.5 text-right whitespace-nowrap space-x-1.5">
                        <a href="klien.php?edit_id=<?= (int)$c['id'] ?>" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold text-[11px]">
                            Edit
                        </a>
                        <form method="POST" onsubmit="return confirm('Hapus data klien ini?');" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-[11px] cursor-pointer">
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
