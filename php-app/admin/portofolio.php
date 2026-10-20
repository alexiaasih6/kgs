<?php
$admin_page_title = 'Kelola Dokumentasi Proyek & Portofolio';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt_edit = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $edit_data = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $client_or_location = trim($_POST['client_or_location'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $uploaded_img = handle_image_upload('project_image', 'uploads/', $pdo);

            if (!empty($title) && !empty($client_or_location)) {
                $stmt = $pdo->prepare("INSERT INTO projects (title, client_or_location, category, description, image_url, pdf_page) VALUES (?, ?, ?, ?, ?, 18)");
                $stmt->execute([$title, $client_or_location, $category, $description, $uploaded_img]);
                log_audit($pdo, 'Tambah Proyek', "Menambahkan dokumentasi proyek: $title");
                $msg = 'Dokumentasi proyek baru berhasil ditambahkan!';
            } else {
                $error = 'Judul dan lokasi/klien proyek wajib diisi.';
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $client_or_location = trim($_POST['client_or_location'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $uploaded_img = handle_image_upload('project_image', 'uploads/', $pdo);

            if ($id > 0 && !empty($title) && !empty($client_or_location)) {
                if ($uploaded_img) {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, client_or_location = ?, category = ?, description = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$title, $client_or_location, $category, $description, $uploaded_img, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, client_or_location = ?, category = ?, description = ? WHERE id = ?");
                    $stmt->execute([$title, $client_or_location, $category, $description, $id]);
                }
                log_audit($pdo, 'Edit Proyek', "Memperbarui dokumentasi proyek ID $id: $title");
                $msg = 'Dokumentasi proyek berhasil diperbarui!';
                $edit_data = null;
                $edit_id = 0;
            } else {
                $error = 'Judul dan lokasi/klien proyek tidak boleh kosong.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                log_audit($pdo, 'Hapus Proyek', "Menghapus dokumentasi proyek ID: $id");
                $msg = 'Dokumentasi proyek berhasil dihapus.';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$category_filter = $_GET['cat'] ?? '';
$sql = "SELECT * FROM projects WHERE 1=1";
$params = [];
if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = [
    'Fabrikasi Tower',
    'Penangkal Petir',
    'CCTV & Security',
    'Networking & Tower',
    'Radio Komunikasi',
    'AIS & Navigasi',
    'Solusi Terpadu'
];
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Dokumentasi Proyek & Portofolio</h1>
        <p class="text-xs text-slate-500">Tambah, edit deskripsi teknis, unggah foto dokumentasi lapangan, atau hapus proyek portofolio.</p>
    </div>
    <a href="../portofolio.php" target="_blank" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
        Lihat Halaman Portofolio &nearr;
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
    <!-- Form Tambah / Edit Proyek -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit">
        <h2 class="font-bold text-slate-900 text-sm mb-4 pb-2 border-b">
            <?= $edit_data ? 'Edit Dokumentasi Proyek' : 'Tambah Dokumentasi Proyek' ?>
        </h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
            <?php endif; ?>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Judul Proyek / Instalasi *</label>
                <input type="text" name="title" required value="<?= e($edit_data['title'] ?? '') ?>" placeholder="Contoh: Pabrikasi Guywire Tower Mandiri" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Nama Klien / Lokasi Site *</label>
                <input type="text" name="client_or_location" required value="<?= e($edit_data['client_or_location'] ?? '') ?>" placeholder="Contoh: PT. OKI Pulp & Paper" class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Kategori Pekerjaan *</label>
                <select name="category" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= ($edit_data && $edit_data['category'] === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Foto Dokumentasi Proyek (Opsional, Maks. 2 MB)</label>
                <?php if (!empty($edit_data['image_url'])): ?>
                    <div class="mb-2">
                        <img src="../<?= e($edit_data['image_url']) ?>" alt="Preview" class="h-24 rounded-lg border object-cover">
                    </div>
                <?php endif; ?>
                <input type="file" name="project_image" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-slate-300 rounded-lg p-1.5 bg-slate-50">
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Rincian Teknis Pekerjaan *</label>
                <textarea name="description" rows="4" required placeholder="Jelaskan lingkup pekerjaan teknis..." class="w-full border border-slate-300 rounded-lg p-2"><?= e($edit_data['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
                    <?= $edit_data ? 'Simpan Perubahan' : 'Simpan Proyek' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="portofolio.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Proyek -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <span class="font-bold text-xs text-slate-700">Daftar Proyek Terdokumentasi (<?= count($projects) ?>)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <th class="p-3.5">Proyek & Lokasi</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($projects as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5">
                                <div class="flex items-start gap-3">
                                    <?php if (!empty($p['image_url'])): ?>
                                        <img src="../<?= e($p['image_url']) ?>" alt="" class="w-14 h-12 rounded-lg object-cover border shrink-0">
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-bold text-slate-900"><?= e($p['title']) ?></div>
                                        <div class="text-blue-600 font-semibold mt-0.5">📍 <?= e($p['client_or_location']) ?></div>
                                        <p class="text-slate-500 mt-1 leading-relaxed"><?= e($p['description']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                    <?= e($p['category']) ?>
                                </span>
                            </td>
                            <td class="p-3.5 text-right whitespace-nowrap">
                                <a href="portofolio.php?edit_id=<?= (int)$p['id'] ?>" class="inline-block px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold mr-1">
                                    Edit
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Hapus dokumentasi proyek ini?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
