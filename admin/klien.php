<?php
$admin_page_title = 'Kelola Portofolio & Daftar Klien';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

if (function_exists('ensure_clients_table')) {
    ensure_clients_table($pdo);
}

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
        $name = clean_plain_text((string)($_POST['name'] ?? ''), 150);
        $category = clean_plain_text((string)($_POST['category'] ?? ''), 100);
        $location = clean_plain_text((string)($_POST['location'] ?? ''), 150);
        $service_category = clean_plain_text((string)($_POST['service_category'] ?? ''), 255);
        $raw_website = trim((string)($_POST['website_url'] ?? ''));
        $website_url = ($raw_website !== '' && $raw_website !== '#') ? sanitize_nav_url($raw_website) : '';
        if ($website_url === '#') {
            $website_url = '';
        }
        $image_url = sanitize_image_url((string)($_POST['image_url'] ?? ''), '');
        $pdf_page = (int)($_POST['pdf_page'] ?? 8);

        if (!empty($_POST['remove_image'])) {
            $image_url = '';
        }

        if (!empty($_FILES['client_image_file']['name'])) {
            $up = secure_upload_image($_FILES['client_image_file'], 'portfolio', $pdo);
            if ($up['ok']) {
                $image_url = $up['path'];
            } else {
                $error = $up['error'];
            }
        } elseif (!empty($_POST['client_image_file_from_media'])) {
            $media_sel = sanitize_image_url((string)$_POST['client_image_file_from_media'], '');
            if ($media_sel !== '') {
                $image_url = $media_sel;
            }
        }

        if ($error === '') {
            if (empty($name) || empty($service_category)) {
                $error = 'Nama perusahaan klien dan kategori solusi pekerjaan wajib diisi.';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO clients (name, category, location, service_category, image_url, logo_url, website_url, pdf_page) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $name,
                            $category ?: 'Korporasi & Instansi',
                            $location ?: 'Palembang / Sumatera Selatan',
                            $service_category,
                            $image_url ?: null,
                            $image_url ?: null,
                            $website_url ?: null,
                            $pdf_page
                        ]);
                        invalidate_live_preview_json('clients');
                        log_audit($pdo, 'Tambah Klien', "Menambahkan klien baru: $name");
                        $msg = 'Data klien baru beserta foto/logo berhasil ditambahkan!';
                    } else {
                        $stmt = $pdo->prepare("UPDATE clients SET name = ?, category = ?, location = ?, service_category = ?, image_url = ?, logo_url = ?, website_url = ?, pdf_page = ? WHERE id = ?");
                        $stmt->execute([
                            $name,
                            $category ?: 'Korporasi & Instansi',
                            $location ?: 'Palembang / Sumatera Selatan',
                            $service_category,
                            $image_url ?: null,
                            $image_url ?: null,
                            $website_url ?: null,
                            $pdf_page,
                            $id
                        ]);
                        invalidate_live_preview_json('clients');
                        log_audit($pdo, 'Edit Klien', "Memperbarui data klien ID $id: $name");
                        $msg = 'Data klien berhasil diperbarui!';
                        $edit_data = null;
                        $edit_id = 0;
                    }
                } catch (Exception $e) {
                    error_log('[KGS Clients Save Error] ' . $e->getMessage());
                    $error = 'Gagal menyimpan data klien. Silakan periksa kembali input Anda.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->execute([$id]);
            invalidate_live_preview_json('clients');
            log_audit($pdo, 'Hapus Klien', "Menghapus data klien ID: $id");
            $msg = 'Data klien berhasil dihapus.';
        }
    }
}

$clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Manajemen Rekam Jejak B2B</span>
        <h1 class="text-xl font-extrabold text-slate-900">Daftar Klien &amp; Dokumentasi Mitra (<?= count($clients) ?> Entitas)</h1>
        <p class="text-xs text-slate-500">Kelola daftar klien korporasi, BUMN, rumah sakit, perbankan, instansi pemerintah, beserta upload foto/logo klien.</p>
    </div>
    <a href="../klien.php" target="_blank" class="px-4 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition shadow-xs">
        Lihat Halaman Klien Publik &nearr;
    </a>
</div>

<?php if (!empty($msg)): ?>
<div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-bold">
    ✓ <?= e($msg) ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-bold">
    ✕ <?= e($error) ?>
</div>
<?php endif; ?>

<!-- Form Tambah / Edit Klien (Mendukung Upload Foto / Logo Klien) -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
        <div>
            <h2 class="text-sm font-extrabold text-slate-900">
                <?= $edit_data ? 'Edit Data & Foto Klien: ' . e($edit_data['name']) : 'Tambah Klien Korporasi / Instansi Baru' ?>
            </h2>
            <p class="text-[11px] text-slate-500">Unggah logo instansi atau foto dokumentasi proyek klien agar tampil profesional di halaman Klien &amp; Portofolio.</p>
        </div>
        <?php if ($edit_data): ?>
            <a href="klien.php" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs">Batal Edit</a>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Perusahaan / Instansi Klien *</label>
                <input type="text" name="name" required value="<?= e($edit_data['name'] ?? '') ?>" placeholder="Contoh: PT. Bukit Asam / RSUD" class="w-full border border-slate-300 rounded-xl p-2.5 font-semibold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Bidang Industri / Sektor</label>
                <input type="text" name="category" value="<?= e($edit_data['category'] ?? '') ?>" placeholder="Contoh: BUMN / Perbankan / Rumah Sakit" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Solusi Pekerjaan KGS *</label>
                <input type="text" name="service_category" required value="<?= e($edit_data['service_category'] ?? '') ?>" placeholder="Contoh: Dedicated Internet / PABX / Tower / CCTV" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Lokasi / Wilayah Operasional</label>
                <input type="text" name="location" value="<?= e($edit_data['location'] ?? '') ?>" placeholder="Contoh: Palembang, Sumatera Selatan" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 pt-2 border-t border-slate-100">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Upload Foto / Logo Klien (PNG, JPG, WebP)</label>
                <input type="file" name="client_image_file" accept=".png,.jpg,.jpeg,.webp" class="w-full border border-slate-300 rounded-xl p-2 bg-white file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
                <?php $current_img = trim((string)($edit_data['image_url'] ?? ($edit_data['logo_url'] ?? ''))); ?>
                <?php if ($current_img !== ''): ?>
                    <div class="mt-2 flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <img src="../<?= e(ltrim($current_img, '/')) ?>" alt="Preview" class="h-10 w-14 object-contain rounded bg-white border p-0.5">
                        <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1">
                            <span>Hapus foto saat ini</span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Atau URL Foto / Logo Klien (Opsional)</label>
                <input type="text" name="image_url" value="<?= e($edit_data['image_url'] ?? ($edit_data['logo_url'] ?? '')) ?>" placeholder="uploads/portfolio/... atau https://..." class="w-full border border-slate-300 rounded-xl p-2.5 font-mono text-[11px]">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Link Website Klien / Referensi (Opsional)</label>
                <input type="text" name="website_url" value="<?= e($edit_data['website_url'] ?? '') ?>" placeholder="https://..." class="w-full border border-slate-300 rounded-xl p-2.5 font-mono text-[11px]">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <?php if ($edit_data): ?>
                <a href="klien.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</a>
            <?php endif; ?>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                <?= $edit_data ? 'Simpan Perubahan Klien' : '+ Tambah Klien & Upload Foto' ?>
            </button>
        </div>
    </form>
</div>

<!-- Tabel Direktori Klien -->
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 border-b uppercase font-bold text-[10px] text-slate-500">
                <tr>
                    <th class="p-3.5">Foto / Logo</th>
                    <th class="p-3.5">Nama Klien / Instansi</th>
                    <th class="p-3.5">Bidang Industri</th>
                    <th class="p-3.5">Solusi Pekerjaan KGS</th>
                    <th class="p-3.5">Lokasi</th>
                    <th class="p-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $c):
                    $c_img = trim((string)($c['image_url'] ?? ($c['logo_url'] ?? '')));
                    $c_web = trim((string)($c['website_url'] ?? ''));
                    $initials = strtoupper(substr(preg_replace('/^(PT\.|CV\.|RS\.|RSUD|BANK)\s*/i', '', trim((string)$c['name'])), 0, 2));
                    if ($initials === '') $initials = 'KG';
                ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-3.5 w-20">
                        <?php if ($c_img !== ''): ?>
                            <?php $img_src = preg_match('/^https?:\/\//i', $c_img) ? $c_img : '../' . ltrim($c_img, '/'); ?>
                            <img src="<?= e($img_src) ?>" alt="<?= e($c['name']) ?>" class="w-14 h-10 object-contain rounded-lg bg-white border border-slate-200 p-1 shadow-2xs">
                        <?php else: ?>
                            <div class="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[11px] flex items-center justify-center">
                                <?= e($initials) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="p-3.5">
                        <div class="font-bold text-slate-900"><?= e($c['name']) ?></div>
                        <?php if ($c_web !== ''): ?>
                            <a href="<?= e(sanitize_nav_url($c_web)) ?>" target="_blank" rel="noopener noreferrer" class="text-[10px] text-blue-600 hover:underline font-mono"><?= e($c_web) ?> &nearr;</a>
                        <?php endif; ?>
                    </td>
                    <td class="p-3.5">
                        <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px]"><?= e($c['category'] ?: 'Korporasi & Instansi') ?></span>
                    </td>
                    <td class="p-3.5 font-semibold text-blue-700"><?= e($c['service_category']) ?></td>
                    <td class="p-3.5 text-slate-500"><?= e($c['location'] ?: 'Sumatera Selatan') ?></td>
                    <td class="p-3.5 text-right whitespace-nowrap space-x-1.5">
                        <a href="klien.php?edit_id=<?= (int)$c['id'] ?>" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-bold text-[11px]">
                            Edit / Foto
                        </a>
                        <form method="POST" onsubmit="return confirm('Hapus data klien ini?');" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-[11px] cursor-pointer">
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
