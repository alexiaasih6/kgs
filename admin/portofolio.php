<?php
$admin_page_title = 'Kelola Dokumentasi Proyek & Galeri Multi-Gambar Portofolio';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

ensure_portfolio_images_table($pdo);

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' || $action === 'edit') {
            $id                 = (int)($_POST['id'] ?? 0);
            $title              = clean_plain_text((string)($_POST['title'] ?? ''), 200);
            $client_or_location = clean_plain_text((string)($_POST['client_or_location'] ?? ''), 150);
            $category           = clean_plain_text((string)($_POST['category'] ?? ''), 100);
            $description        = clean_plain_text((string)($_POST['description'] ?? ''), 2000);
            $urls_raw           = trim((string)($_POST['image_urls_text'] ?? ''));
            $default_caption    = clean_plain_text((string)($_POST['default_caption'] ?? ''), 250);

            if (empty($title) || empty($client_or_location)) {
                throw new Exception('Judul dan lokasi/klien proyek wajib diisi.');
            }

            // Kumpulkan daftar gambar baru dari upload tunggal (`project_image`), multi-upload (`project_images[]`), dan multi-URL (`image_urls_text`)
            $new_images = [];

            // 1. Kompatibilitas input file tunggal `project_image`
            if (!empty($_FILES['project_image']['name']) && !is_array($_FILES['project_image']['name']) && $_FILES['project_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploaded_single = handle_image_upload('project_image', 'uploads/portfolio', $pdo);
                if ($uploaded_single) {
                    $new_images[] = [
                        'path'    => $uploaded_single,
                        'caption' => $default_caption !== '' ? $default_caption : ($title . ' — ' . $client_or_location),
                    ];
                }
            }

            // 2. Multi-file upload `project_images[]`
            if (!empty($_FILES['project_images']['name']) && is_array($_FILES['project_images']['name'])) {
                $file_count = count($_FILES['project_images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['project_images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $single_file = [
                        'name'     => $_FILES['project_images']['name'][$i],
                        'type'     => $_FILES['project_images']['type'][$i],
                        'tmp_name' => $_FILES['project_images']['tmp_name'][$i],
                        'error'    => $_FILES['project_images']['error'][$i],
                        'size'     => $_FILES['project_images']['size'][$i],
                    ];
                    $up = secure_upload_image($single_file, 'uploads/portfolio', $pdo);
                    if (!$up['ok']) {
                        throw new Exception($up['error'] ?? 'Gagal mengunggah salah satu file gambar.');
                    }
                    $orig_label = pathinfo((string)$_FILES['project_images']['name'][$i], PATHINFO_FILENAME);
                    $orig_label = trim(preg_replace('/[-_]+/', ' ', $orig_label));
                    $cap = $default_caption !== ''
                        ? ($file_count > 1 ? $default_caption . ' (' . ($i + 1) . ')' : $default_caption)
                        : ($title . ($orig_label !== '' ? ' — ' . $orig_label : ' — ' . $client_or_location));
                    $new_images[] = [
                        'path'    => $up['path'],
                        'caption' => $cap,
                    ];
                }
            }

            // 3. Multi-line URL input (`image_urls_text`) atau pilihan dari Media & Upload Gambar (`project_images_from_media`)
            if ($urls_raw === '' && !empty($_POST['project_images_from_media'])) {
                $urls_raw = trim((string)$_POST['project_images_from_media']);
            }
            if ($urls_raw !== '') {
                $lines = preg_split('/\r\n|\r|\n/', $urls_raw);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = explode('::', $line, 2);
                    $url_val = sanitize_image_url($parts[0] ?? '', '');
                    $custom_cap = isset($parts[1]) ? clean_plain_text($parts[1], 250) : '';
                    if ($url_val !== '') {
                        $new_images[] = [
                            'path'    => $url_val,
                            'caption' => $custom_cap !== '' ? $custom_cap : ($default_caption !== '' ? $default_caption : ($title . ' — ' . $client_or_location)),
                        ];
                    }
                }
            }

            if ($action === 'add') {
                $cover_img = !empty($new_images[0]['path']) ? $new_images[0]['path'] : 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80';
                $stmt = $pdo->prepare("INSERT INTO projects (title, client_or_location, category, description, image_url, pdf_page) VALUES (?, ?, ?, ?, ?, 18)");
                $stmt->execute([$title, $client_or_location, $category, $description, $cover_img]);
                $new_proj_id = (int)$pdo->lastInsertId();

                if ($new_proj_id > 0) {
                    if (empty($new_images)) {
                        $new_images[] = [
                            'path'    => $cover_img,
                            'caption' => $title . ' — ' . $client_or_location,
                        ];
                    }
                    $stmt_img = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($new_images as $idx => $img_item) {
                        $stmt_img->execute([
                            $new_proj_id,
                            $img_item['path'],
                            $img_item['caption'],
                            $img_item['caption'],
                            $idx + 1,
                            $idx === 0 ? 1 : 0,
                        ]);
                    }
                }

                invalidate_live_preview_json('portfolios');
                log_audit($pdo, 'Tambah Proyek', "Menambahkan dokumentasi proyek: $title (" . count($new_images) . " gambar)");
                $msg = 'Dokumentasi proyek baru beserta ' . count($new_images) . ' gambar slide berhasil ditambahkan!';
                $edit_id = $new_proj_id;
            } else {
                if ($id <= 0) {
                    throw new Exception('ID proyek tidak valid.');
                }
                if (!empty($new_images[0]['path']) && isset($_POST['make_first_primary'])) {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, client_or_location = ?, category = ?, description = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$title, $client_or_location, $category, $description, $new_images[0]['path'], $id]);
                    $pdo->prepare("UPDATE project_images SET is_primary = 0 WHERE project_id = ?")->execute([$id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, client_or_location = ?, category = ?, description = ? WHERE id = ?");
                    $stmt->execute([$title, $client_or_location, $category, $description, $id]);
                }

                if (!empty($new_images)) {
                    $stmt_max = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM project_images WHERE project_id = ?");
                    $stmt_max->execute([$id]);
                    $base_sort = (int)$stmt_max->fetchColumn();

                    $stmt_img = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($new_images as $idx => $img_item) {
                        $is_prim = (isset($_POST['make_first_primary']) && $idx === 0) ? 1 : 0;
                        $stmt_img->execute([
                            $id,
                            $img_item['path'],
                            $img_item['caption'],
                            $img_item['caption'],
                            $base_sort + $idx + 1,
                            $is_prim,
                        ]);
                    }
                }

                invalidate_live_preview_json('portfolios');
                log_audit($pdo, 'Edit Proyek', "Memperbarui dokumentasi proyek ID $id: $title");
                $msg = 'Dokumentasi proyek berhasil diperbarui' . (!empty($new_images) ? ' dan ditambahkan ' . count($new_images) . ' gambar baru!' : '!');
                $edit_id = $id;
            }
        } elseif ($action === 'update_image') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            $image_id   = (int)($_POST['image_id'] ?? 0);
            $caption    = clean_plain_text((string)($_POST['caption'] ?? ''), 250);
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            $pdo->prepare("UPDATE project_images SET caption = ?, alt_text = ?, sort_order = ? WHERE id = ? AND project_id = ?")
                ->execute([$caption, $caption, $sort_order, $image_id, $project_id]);
            invalidate_live_preview_json('portfolios');
            $msg = 'Keterangan (caption) dan urutan gambar proyek berhasil diperbarui.';
            $edit_id = $project_id;
        } elseif ($action === 'set_primary_image') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            $image_id   = (int)($_POST['image_id'] ?? 0);
            $stmt_find  = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ? AND project_id = ? LIMIT 1");
            $stmt_find->execute([$image_id, $project_id]);
            $row = $stmt_find->fetch();
            if ($row) {
                $pdo->prepare("UPDATE project_images SET is_primary = 0 WHERE project_id = ?")->execute([$project_id]);
                $pdo->prepare("UPDATE project_images SET is_primary = 1 WHERE id = ?")->execute([$image_id]);
                $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?")->execute([$row['image_path'], $project_id]);
                invalidate_live_preview_json('portfolios');
                $msg = 'Gambar sampul utama proyek berhasil diperbarui.';
                $edit_id = $project_id;
            }
        } elseif ($action === 'delete_image') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            $image_id   = (int)($_POST['image_id'] ?? 0);
            $stmt_cnt   = $pdo->prepare("SELECT COUNT(*) FROM project_images WHERE project_id = ?");
            $stmt_cnt->execute([$project_id]);
            if ((int)$stmt_cnt->fetchColumn() <= 1) {
                throw new Exception('Minimal harus tersisa 1 gambar pada proyek ini.');
            }
            $pdo->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?")->execute([$image_id, $project_id]);
            $stmt_first = $pdo->prepare("SELECT id, image_path FROM project_images WHERE project_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1");
            $stmt_first->execute([$project_id]);
            $first = $stmt_first->fetch();
            if ($first) {
                $pdo->prepare("UPDATE project_images SET is_primary = 1 WHERE id = ?")->execute([(int)$first['id']]);
                $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?")->execute([$first['image_path'], $project_id]);
            }
            invalidate_live_preview_json('portfolios');
            $msg = 'Slide gambar berhasil dihapus dari galeri proyek.';
            $edit_id = $project_id;
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM project_images WHERE project_id = ?")->execute([$id]);
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                invalidate_live_preview_json('portfolios');
                log_audit($pdo, 'Hapus Proyek', "Menghapus dokumentasi proyek ID: $id");
                $msg = 'Dokumentasi proyek beserta galeri gambarnya berhasil dihapus.';
                if ($edit_id === $id) {
                    $edit_id = 0;
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($edit_id > 0) {
    $edit_data = get_portfolio_project_by_id($pdo, $edit_id);
}

$category_filter = $_GET['cat'] ?? '';
$projects = get_portfolio_projects($pdo, (string)$category_filter);

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
        <h1 class="text-xl font-bold text-slate-900">Kelola Dokumentasi Proyek &amp; Galeri Slide Multi-Gambar</h1>
        <p class="text-xs text-slate-500">Tambah proyek, edit deskripsi teknis, dan unggah banyak gambar dokumentasi sekaligus untuk setiap proyek portofolio.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Tambah / Edit Proyek + Multi-Gambar -->
    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit space-y-5">
        <div class="flex items-center justify-between pb-2 border-b">
            <h2 class="font-bold text-slate-900 text-sm">
                <?= $edit_data ? 'Edit Proyek & Tambah Banyak Gambar' : 'Tambah Proyek & Banyak Gambar' ?>
            </h2>
            <?php if ($edit_data): ?>
                <a href="../portofolio.php?id=<?= (int)$edit_data['id'] ?>" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline">
                    Lihat Slide Proyek &nearr;
                </a>
            <?php endif; ?>
        </div>

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
                <label class="block font-semibold mb-1 text-slate-700">Rincian Teknis Pekerjaan *</label>
                <textarea name="description" rows="4" required placeholder="Jelaskan lingkup pekerjaan teknis..." class="w-full border border-slate-300 rounded-lg p-2"><?= e($edit_data['description'] ?? '') ?></textarea>
            </div>

            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 space-y-3">
                <div class="font-bold text-blue-900">
                    🖼️ Upload Banyak Gambar Sekaligus (Multi-Image)
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Pilih Beberapa File Gambar Sekaligus (JPG/PNG/WEBP)</label>
                    <input type="file" name="project_images[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full border border-slate-300 rounded-lg p-1.5 bg-white">
                    <p class="text-[11px] text-slate-500 mt-1">Tekan Ctrl / Shift saat memilih file untuk mengunggah banyak foto sekaligus.</p>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Atau Masukkan Banyak URL Gambar (1 Baris = 1 Gambar)</label>
                    <textarea name="image_urls_text" rows="3" placeholder="https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?w=1200 :: Keterangan slide 1&#10;https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200 :: Keterangan slide 2" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono text-[11px]"></textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-slate-700">Caption Default untuk Gambar Baru (Opsional)</label>
                    <input type="text" name="default_caption" placeholder="Contoh: Dokumentasi Instalasi Lapangan" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>

                <?php if ($edit_data): ?>
                    <label class="inline-flex items-center gap-2 text-slate-700 font-semibold cursor-pointer">
                        <input type="checkbox" name="make_first_primary" value="1" class="rounded border-slate-300">
                        <span>Jadikan gambar baru pertama sebagai Sampul Utama (Cover)</span>
                    </label>
                <?php endif; ?>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
                    <?= $edit_data ? 'Simpan Perubahan & Tambah Gambar' : 'Simpan Proyek & Galeri' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="portofolio.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Daftar Slide Gambar Saat Ini (Jika sedang mode Edit Proyek) -->
        <?php if ($edit_data && !empty($edit_data['images'])): ?>
            <div class="pt-4 border-t border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs text-slate-800">
                        Galeri Slide Proyek Ini (<?= count($edit_data['images']) ?> Gambar)
                    </h3>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <?php foreach ($edit_data['images'] as $idx => $img):
                        $img_src = (string)($img['image_path'] ?? '');
                        if ($img_src !== '' && !preg_match('#^https?://#i', $img_src)) {
                            $img_src = '../' . ltrim($img_src, '/');
                        }
                    ?>
                        <div class="border border-slate-200 rounded-xl p-2 bg-slate-50 flex flex-col justify-between gap-2">
                            <div class="relative aspect-16/10 rounded-lg overflow-hidden bg-slate-900">
                                <img src="<?= e($img_src) ?>" alt="" class="w-full h-full object-cover">
                                <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-bold">
                                    #<?= $idx + 1 ?>
                                </span>
                                <?php if (!empty($img['is_primary'])): ?>
                                    <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[9px] font-bold uppercase">
                                        Sampul Utama
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-[11px] text-slate-600 line-clamp-2">
                                <?= e($img['caption'] ?? $edit_data['title']) ?>
                            </div>
                            <?php if (!empty($img['id'])): ?>
                                <div class="flex items-center justify-between gap-1 pt-1 border-t border-slate-200 text-[11px]">
                                    <?php if (empty($img['is_primary'])): ?>
                                        <form method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_primary_image">
                                            <input type="hidden" name="project_id" value="<?= (int)$edit_data['id'] ?>">
                                            <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                            <button type="submit" class="text-blue-600 hover:underline font-bold cursor-pointer">★ Jadikan Sampul</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-emerald-700 font-bold">★ Cover Aktif</span>
                                    <?php endif; ?>

                                    <?php if (count($edit_data['images']) > 1): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('Hapus gambar slide ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_image">
                                            <input type="hidden" name="project_id" value="<?= (int)$edit_data['id'] ?>">
                                            <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                            <button type="submit" class="text-rose-600 hover:underline font-bold cursor-pointer">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Daftar Proyek -->
    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs h-fit">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <span class="font-bold text-xs text-slate-700">Daftar Proyek Terdokumentasi (<?= count($projects) ?> Proyek)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <th class="p-3.5">Proyek, Lokasi &amp; Pratinjau Slide</th>
                        <th class="p-3.5">Kategori &amp; Jumlah Slide</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($projects as $p):
                        $p_imgs = $p['images'] ?? [];
                        $cover_src = (string)($p['cover_image'] ?? $p['image_url'] ?? '');
                        if ($cover_src !== '' && !preg_match('#^https?://#i', $cover_src)) {
                            $cover_src = '../' . ltrim($cover_src, '/');
                        }
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5">
                                <div class="flex items-start gap-3">
                                    <?php if ($cover_src !== ''): ?>
                                        <img src="<?= e($cover_src) ?>" alt="" class="w-16 h-12 rounded-lg object-cover border shrink-0">
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-bold text-slate-900"><?= e($p['title']) ?></div>
                                        <div class="text-blue-600 font-semibold mt-0.5">📍 <?= e($p['client_or_location']) ?></div>
                                        <p class="text-slate-500 mt-1 leading-relaxed line-clamp-2"><?= e($p['description']) ?></p>
                                        <?php if (!empty($p_imgs)): ?>
                                            <div class="flex items-center gap-1.5 mt-2">
                                                <?php foreach (array_slice($p_imgs, 0, 5) as $thumb):
                                                    $t_src = (string)($thumb['image_path'] ?? '');
                                                    if ($t_src !== '' && !preg_match('#^https?://#i', $t_src)) {
                                                        $t_src = '../' . ltrim($t_src, '/');
                                                    }
                                                ?>
                                                    <img src="<?= e($t_src) ?>" alt="" class="w-9 h-6 rounded object-cover border border-slate-200">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 space-y-1.5">
                                <div>
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= e($p['category']) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-[11px]">
                                        🖼️ <?= count($p_imgs) ?> Slide Gambar
                                    </span>
                                </div>
                            </td>
                            <td class="p-3.5 text-right whitespace-nowrap space-y-1">
                                <div>
                                    <a href="portofolio.php?edit_id=<?= (int)$p['id'] ?>" class="inline-block px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg font-semibold mr-1">
                                        Kelola &amp; +Gambar
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Hapus dokumentasi proyek ini beserta seluruh gambarnya?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <a href="../portofolio.php?id=<?= (int)$p['id'] ?>" target="_blank" class="text-[11px] font-bold text-slate-500 hover:text-blue-600">
                                        Lihat Slide Proyek &nearr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
