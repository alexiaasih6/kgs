<?php
/**
 * Admin CMS: Kelola Slide Show / Galeri Multi-Gambar (`admin/slideshow.php`)
 * Mendukung CRUD Kategori Slide Show serta Upload & Manajemen Banyak Gambar (Multi-Image) di setiap halaman kategori.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/upload.php';

ensure_slideshow_tables($pdo);

$admin_page_title = 'Kelola Slide Show & Galeri Multi-Gambar';
$success_msg = '';
$error_msg = '';

$selected_cat_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$edit_cat_id     = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        // 1. SIMPAN KATEGORI BARU ATAU UPDATE KATEGORI
        if ($action === 'save_category') {
            $cat_id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title      = clean_plain_text((string)($_POST['title'] ?? ''), 200);
            $raw_slug   = trim((string)($_POST['slug'] ?? ''));
            $slug       = slugify($raw_slug !== '' ? $raw_slug : $title);
            $short_desc = clean_plain_text((string)($_POST['short_description'] ?? ''), 500);
            $full_desc  = clean_plain_text((string)($_POST['full_description'] ?? ''), 5000);
            $cover_url  = sanitize_image_url((string)($_POST['cover_image'] ?? ''), '');
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
            $is_active  = isset($_POST['is_active']) ? 1 : 0;

            if ($title === '' || $full_desc === '') {
                throw new RuntimeException('Judul kategori dan deskripsi teknis lengkap wajib diisi.');
            }

            // Cek upload file cover baru atau pilihan dari Media & Upload Gambar
            $up = handle_image_upload('cover_file', 'slideshow', $pdo);
            if (is_string($up) && $up !== '') {
                $cover_url = $up;
            } elseif (is_array($up) && !empty($up['filepath'])) {
                $cover_url = $up['filepath'];
            }

            // Pastikan slug unik
            $stmt_slug = $pdo->prepare("SELECT id FROM slideshow_categories WHERE slug = ? AND id != ? LIMIT 1");
            $stmt_slug->execute([$slug, $cat_id]);
            if ($stmt_slug->fetch()) {
                $slug .= '-' . time();
            }

            if ($cat_id > 0) {
                $stmt = $pdo->prepare("UPDATE slideshow_categories SET title = ?, slug = ?, short_description = ?, full_description = ?, cover_image = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $short_desc, $full_desc, $cover_url, $sort_order, $is_active, $cat_id]);
                mark_db_modified($pdo);
                $success_msg = 'Kategori Slide Show "' . $title . '" berhasil diperbarui.';
                $selected_cat_id = $cat_id;
            } else {
                $stmt = $pdo->prepare("INSERT INTO slideshow_categories (title, slug, short_description, full_description, cover_image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $short_desc, $full_desc, $cover_url, $sort_order, $is_active]);
                $new_id = (int)$pdo->lastInsertId();

                // Jika cover_url diisi saat buat kategori baru, tambahkan juga sebagai gambar pertama
                if ($new_id > 0 && $cover_url !== '') {
                    $stmt_img = $pdo->prepare("INSERT INTO slideshow_images (category_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, 1, 1)");
                    $stmt_img->execute([$new_id, $cover_url, $title, $title]);
                }

                mark_db_modified($pdo);
                $success_msg = 'Kategori Slide Show baru "' . $title . '" berhasil ditambahkan.';
                $selected_cat_id = $new_id;
            }
        }

        // 2. TOGGLE STATUS AKTIF / NONAKTIF KATEGORI
        elseif ($action === 'toggle_category_status') {
            $cat_id = (int)($_POST['id'] ?? 0);
            if ($cat_id > 0) {
                $pdo->prepare("UPDATE slideshow_categories SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?")->execute([$cat_id]);
                mark_db_modified($pdo);
                $success_msg = 'Status aktif kategori Slide Show berhasil diperbarui.';
                $selected_cat_id = $cat_id;
            }
        }

        // 3. HAPUS KATEGORI BESERTA GAMBARNYA
        elseif ($action === 'delete_category') {
            $cat_id = (int)($_POST['id'] ?? 0);
            if ($cat_id > 0) {
                $pdo->prepare("DELETE FROM slideshow_images WHERE category_id = ?")->execute([$cat_id]);
                $pdo->prepare("DELETE FROM slideshow_categories WHERE id = ?")->execute([$cat_id]);
                mark_db_modified($pdo);
                $success_msg = 'Kategori Slide Show beserta seluruh gambarnya berhasil dihapus.';
                if ($selected_cat_id === $cat_id) {
                    $selected_cat_id = 0;
                }
            }
        }

        // 4. TAMBAH GAMBAR (MULTI-UPLOAD FILE DAN/ATAU INPUT URL) KE KATEGORI
        elseif ($action === 'add_images') {
            $cat_id        = (int)($_POST['category_id'] ?? 0);
            $default_cap   = clean_plain_text((string)($_POST['caption'] ?? ''), 250);
            $default_alt   = clean_plain_text((string)($_POST['alt_text'] ?? ''), 250);
            $base_sort     = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
            $make_primary  = isset($_POST['is_primary']) ? 1 : 0;
            $url_input     = sanitize_image_url((string)($_POST['image_url_input'] ?? ''), '');

            if ($cat_id <= 0) {
                throw new RuntimeException('Pilih kategori Slide Show terlebih dahulu.');
            }

            $selected_cat_id = $cat_id;
            $stmt_cat_info = $pdo->prepare("SELECT title, cover_image FROM slideshow_categories WHERE id = ? LIMIT 1");
            $stmt_cat_info->execute([$cat_id]);
            $cat_info = $stmt_cat_info->fetch();
            if (!$cat_info) {
                throw new RuntimeException('Kategori Slide Show tidak ditemukan.');
            }

            $added_paths = [];

            // Proses Multi-File Upload (`gallery_files[]`)
            if (!empty($_FILES['gallery_files']['name']) && is_array($_FILES['gallery_files']['name'])) {
                $file_count = count($_FILES['gallery_files']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['gallery_files']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $single_file = [
                        'name'     => $_FILES['gallery_files']['name'][$i],
                        'type'     => $_FILES['gallery_files']['type'][$i],
                        'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i],
                        'error'    => $_FILES['gallery_files']['error'][$i],
                        'size'     => $_FILES['gallery_files']['size'][$i],
                    ];
                    $up = secure_upload_image($single_file, 'uploads/slideshow', $pdo);
                    if (!$up['ok']) {
                        throw new RuntimeException($up['error'] ?? 'Gagal mengunggah salah satu file gambar.');
                    }
                    $added_paths[] = $up['path'];
                }
            }

            // Proses Input URL Gambar atau Pilihan dari Media & Upload Gambar jika diisi
            if ($url_input !== '') {
                $added_paths[] = $url_input;
            } elseif (!empty($_POST['gallery_files_from_media'])) {
                $from_media_val = sanitize_image_url((string)$_POST['gallery_files_from_media'], '');
                if ($from_media_val !== '') {
                    $added_paths[] = $from_media_val;
                }
            }

            if (empty($added_paths)) {
                throw new RuntimeException('Pilih minimal 1 file gambar untuk diunggah atau masukkan URL gambar yang valid.');
            }

            if ($make_primary === 1) {
                $pdo->prepare("UPDATE slideshow_images SET is_primary = 0 WHERE category_id = ?")->execute([$cat_id]);
            }

            $stmt_ins = $pdo->prepare("INSERT INTO slideshow_images (category_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($added_paths as $idx => $path) {
                $is_prim = ($make_primary === 1 && $idx === 0) ? 1 : 0;
                $cap = $default_cap !== '' ? $default_cap : $cat_info['title'];
                $alt = $default_alt !== '' ? $default_alt : $cap;
                $stmt_ins->execute([$cat_id, $path, $cap, $alt, $base_sort + $idx, $is_prim]);

                if ($is_prim === 1 || empty($cat_info['cover_image'])) {
                    $pdo->prepare("UPDATE slideshow_categories SET cover_image = ? WHERE id = ?")->execute([$path, $cat_id]);
                    $cat_info['cover_image'] = $path;
                }
            }

            mark_db_modified($pdo);
            $success_msg = count($added_paths) . ' gambar berhasil ditambahkan ke kategori "' . $cat_info['title'] . '".';
        }

        // 5. UPDATE CAPTION / ALT / URUTAN / FILE GAMBAR TERTENTU
        elseif ($action === 'update_image') {
            $img_id     = (int)($_POST['image_id'] ?? 0);
            $cat_id     = (int)($_POST['category_id'] ?? 0);
            $caption    = clean_plain_text((string)($_POST['caption'] ?? ''), 250);
            $alt_text   = clean_plain_text((string)($_POST['alt_text'] ?? ''), 250);
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            $image_path = sanitize_image_url((string)($_POST['image_path'] ?? ''), '');

            $selected_cat_id = $cat_id;

            if ($img_id <= 0) {
                throw new RuntimeException('ID gambar tidak valid.');
            }

            // Cek jika admin mengunggah file pengganti atau memilih dari Media & Upload Gambar untuk gambar ini
            $up = handle_image_upload('replace_image_file', 'slideshow', $pdo);
            if (is_string($up) && $up !== '') {
                $image_path = $up;
            } elseif (is_array($up) && !empty($up['filepath'])) {
                $image_path = $up['filepath'];
            }

            if ($image_path === '') {
                throw new RuntimeException('Path atau URL gambar tidak boleh kosong atau menggunakan protokol yang tidak diizinkan.');
            }

            $stmt = $pdo->prepare("UPDATE slideshow_images SET image_path = ?, caption = ?, alt_text = ?, sort_order = ? WHERE id = ? AND category_id = ?");
            $stmt->execute([$image_path, $caption, $alt_text, $sort_order, $img_id, $cat_id]);

            // Jika gambar ini adalah primary, perbarui juga cover_image kategori
            $stmt_chk = $pdo->prepare("SELECT is_primary FROM slideshow_images WHERE id = ? LIMIT 1");
            $stmt_chk->execute([$img_id]);
            if ((int)$stmt_chk->fetchColumn() === 1) {
                $pdo->prepare("UPDATE slideshow_categories SET cover_image = ? WHERE id = ?")->execute([$image_path, $cat_id]);
            }

            mark_db_modified($pdo);
            $success_msg = 'Detail gambar slide berhasil diperbarui.';
        }

        // 6. JADIKAN GAMBAR SEBAGAI SAMPUL UTAMA (PRIMARY COVER)
        elseif ($action === 'set_primary_image') {
            $img_id = (int)($_POST['image_id'] ?? 0);
            $cat_id = (int)($_POST['category_id'] ?? 0);
            $selected_cat_id = $cat_id;

            $stmt_find = $pdo->prepare("SELECT image_path FROM slideshow_images WHERE id = ? AND category_id = ? LIMIT 1");
            $stmt_find->execute([$img_id, $cat_id]);
            $img_row = $stmt_find->fetch();
            if ($img_row) {
                $pdo->prepare("UPDATE slideshow_images SET is_primary = 0 WHERE category_id = ?")->execute([$cat_id]);
                $pdo->prepare("UPDATE slideshow_images SET is_primary = 1 WHERE id = ?")->execute([$img_id]);
                $pdo->prepare("UPDATE slideshow_categories SET cover_image = ? WHERE id = ?")->execute([$img_row['image_path'], $cat_id]);
                mark_db_modified($pdo);
                $success_msg = 'Gambar berhasil ditetapkan sebagai Sampul Utama (Cover) kategori.';
            }
        }

        // 7. HAPUS 1 GAMBAR DARI KATEGORI
        elseif ($action === 'delete_image') {
            $img_id = (int)($_POST['image_id'] ?? 0);
            $cat_id = (int)($_POST['category_id'] ?? 0);
            $selected_cat_id = $cat_id;

            if ($img_id > 0 && $cat_id > 0) {
                $pdo->prepare("DELETE FROM slideshow_images WHERE id = ? AND category_id = ?")->execute([$img_id, $cat_id]);
                mark_db_modified($pdo);
                $success_msg = 'Gambar berhasil dihapus dari galeri kategori.';
            }
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// Ambil seluruh kategori Slide Show (termasuk yang nonaktif)
$categories = get_slideshow_categories($pdo, false);
if ($selected_cat_id <= 0 && !empty($categories)) {
    $selected_cat_id = (int)$categories[0]['id'];
}

$active_category = null;
$edit_category   = null;
foreach ($categories as $c) {
    if ((int)$c['id'] === $selected_cat_id) {
        $active_category = $c;
    }
    if ($edit_cat_id > 0 && (int)$c['id'] === $edit_cat_id) {
        $edit_category = $c;
    }
}

require_once __DIR__ . '/layout-header.php';
?>

<div class="flex-1 space-y-8">
    <!-- Header Banner Admin Slide Show -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                🖼️ Modul Slide Show & Galeri Multi-Gambar
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                Kelola Halaman Slide Show Infrastruktur
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola kategori halaman Slide Show (Self Supporting Tower Monopole, Fiber Optik, Guy Wire Tower, Lightning Protector, IP-PBX, Wireless, Access Door) serta tambahkan banyak gambar di setiap halaman.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="../slideshow.php" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-blue-600 hover:bg-slate-800 dark:hover:bg-blue-500 text-white text-xs font-bold transition shadow-xs">
                Lihat Halaman Slide Show Publik &nearr;
            </a>
        </div>
    </div>

    <?php if ($success_msg !== ''): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between">
            <span>✅ <?= e($success_msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_msg !== ''): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold flex items-center justify-between">
            <span>⚠️ <?= e($error_msg) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
        <!-- KOLOM KIRI (5 KOLOM): DAFTAR KATEGORI + FORM TAMBAH/EDIT KATEGORI -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Daftar Kategori Slide Show -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                        Daftar Halaman Kategori (<?= count($categories) ?>)
                    </h2>
                    <?php if ($edit_category): ?>
                        <a href="slideshow.php?category_id=<?= (int)$selected_cat_id ?>" class="text-xs font-bold text-blue-600 hover:underline">
                            + Buat Kategori Baru
                        </a>
                    <?php endif; ?>
                </div>

                <div class="space-y-2.5">
                    <?php foreach ($categories as $cat):
                        $is_selected = ((int)$cat['id'] === $selected_cat_id);
                        $img_count = count($cat['images'] ?? []);
                    ?>
                        <div class="p-3.5 rounded-xl border transition <?= $is_selected ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/30' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300' ?>">
                            <div class="flex items-start gap-3">
                                <img src="<?= e($cat['cover_image'] ?: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=400&q=80') ?>"
                                     alt="<?= e($cat['title']) ?>"
                                     class="w-14 h-11 rounded-lg object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="slideshow.php?category_id=<?= (int)$cat['id'] ?>"
                                           class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white hover:text-blue-600 truncate">
                                            <?= e($cat['title']) ?>
                                        </a>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= !empty($cat['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                            <?= !empty($cat['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>🖼️ <?= $img_count ?> Gambar</span>
                                        <span>•</span>
                                        <span>Urutan #<?= (int)$cat['sort_order'] ?></span>
                                    </div>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                        <a href="slideshow.php?category_id=<?= (int)$cat['id'] ?>"
                                           class="px-2.5 py-1 rounded-lg text-[11px] font-bold <?= $is_selected ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-blue-600 hover:text-white' ?> transition">
                                            Kelola Gambar (<?= $img_count ?>)
                                        </a>
                                        <a href="slideshow.php?category_id=<?= (int)$cat['id'] ?>&edit_id=<?= (int)$cat['id'] ?>#form-kategori"
                                           class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 text-[11px] font-bold border border-amber-200 transition">
                                            Edit Teks
                                        </a>
                                        <a href="../slideshow-detail.php?slug=<?= rawurlencode((string)$cat['slug']) ?>"
                                           target="_blank"
                                           class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:text-blue-600 transition">
                                            Lihat &nearr;
                                        </a>

                                        <form method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_category_status">
                                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold cursor-pointer">
                                                <?= !empty($cat['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>

                                        <form method="post" class="inline" onsubmit="return confirm('Hapus kategori Slide Show ini beserta seluruh gambar di dalamnya?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Form Tambah / Edit Kategori Slide Show -->
            <div id="form-kategori" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white mb-1">
                    <?= $edit_category ? '✏️ Edit Kategori: ' . e($edit_category['title']) : '➕ Tambah Kategori Halaman Slide Show Baru' ?>
                </h2>
                <p class="text-xs text-slate-500 mb-4">
                    Gunakan awalan <code>### Judul Bagian</code> untuk sub-judul dan <code>- Item</code> untuk daftar poin keunggulan.
                </p>

                <form method="post" enctype="multipart/form-data" class="space-y-4 text-xs sm:text-sm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save_category">
                    <input type="hidden" name="id" value="<?= (int)($edit_category['id'] ?? 0) ?>">

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Judul Kategori Slide Show *</label>
                        <input type="text" name="title" required
                               value="<?= e($edit_category['title'] ?? '') ?>"
                               placeholder="Contoh: SELF SUPPORTING TOWER MONOPOLE"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Slug URL (Opsional)</label>
                            <input type="text" name="slug"
                                   value="<?= e($edit_category['slug'] ?? '') ?>"
                                   placeholder="Otomatis dari judul"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Urutan Tampil</label>
                            <input type="number" name="sort_order"
                                   value="<?= (int)($edit_category['sort_order'] ?? (count($categories) + 1)) ?>"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Ringkasan Singkat (Tampil di Kartu Depan)</label>
                        <textarea name="short_description" rows="2"
                                  placeholder="Ringkasan 1-2 kalimat untuk kartu kategori..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"><?= e($edit_category['short_description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Deskripsi Teknis Lengkap *</label>
                        <textarea name="full_description" rows="7" required
                                  placeholder="Tulis penjelasan lengkap kategori di sini..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"><?= e($edit_category['full_description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Upload Gambar Sampul (Cover) atau Isi URL</label>
                        <input type="file" name="cover_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold mb-2">
                        <input type="text" name="cover_image"
                               value="<?= e($edit_category['cover_image'] ?? '') ?>"
                               placeholder="Atau tempel URL gambar sampul (https://... atau uploads/slideshow/...)"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_category) || !empty($edit_category['is_active'])) ? 'checked' : '' ?> class="rounded text-blue-600">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Tampilkan Kategori Ini di Website Publik</span>
                    </label>

                    <div class="pt-2 flex items-center gap-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs transition cursor-pointer">
                            <?= $edit_category ? 'Simpan Perubahan Kategori' : 'Tambah Kategori Slide Show' ?>
                        </button>
                        <?php if ($edit_category): ?>
                            <a href="slideshow.php?category_id=<?= (int)$selected_cat_id ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN (7 KOLOM): KELOLA BANYAK GAMBAR (MULTI-IMAGE) UNTUK KATEGORI TERPILIH -->
        <div class="lg:col-span-7 space-y-6">
            <?php if (!$active_category): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-10 text-center">
                    <p class="text-sm text-slate-500">Pilih salah satu kategori di sebelah kiri untuk mengelola galeri gambar.</p>
                </div>
            <?php else:
                $cat_images = $active_category['images'] ?? [];
            ?>
                <!-- Box Tambah Banyak Gambar Sekaligus ke Kategori Ini -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 block">
                                Galeri Multi-Gambar Halaman:
                            </span>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
                                <?= e($active_category['title']) ?> (<?= count($cat_images) ?> Gambar)
                            </h2>
                        </div>
                        <a href="../slideshow-detail.php?slug=<?= rawurlencode((string)$active_category['slug']) ?>"
                           target="_blank"
                           class="px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition">
                            Pratinjau Halaman Detail &nearr;
                        </a>
                    </div>

                    <form method="post" enctype="multipart/form-data" class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 space-y-4 text-xs sm:text-sm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_images">
                        <input type="hidden" name="category_id" value="<?= (int)$active_category['id'] ?>">

                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <span>📤 Tambah Beberapa Gambar Baru ke Halaman "<?= e($active_category['title']) ?>"</span>
                        </h3>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">
                                    Pilih File Gambar (Bisa Pilih Banyak File Sekaligus)
                                </label>
                                <input type="file" name="gallery_files[]" multiple accept=".jpg,.jpeg,.png,.webp,.svg"
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:font-bold">
                                <span class="text-[11px] text-slate-400 block mt-1">Format: JPG, PNG, WEBP, SVG (Maks 5MB/file).</span>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">
                                    Atau Masukkan URL Gambar Eksternal
                                </label>
                                <input type="text" name="image_url_input"
                                       placeholder="https://images.unsplash.com/..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Caption / Keterangan Gambar</label>
                                <input type="text" name="caption"
                                       placeholder="Contoh: Instalasi perangkat <?= e($active_category['title']) ?> oleh tim teknis KGS"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Nomor Urut Awal</label>
                                <input type="number" name="sort_order"
                                       value="<?= count($cat_images) + 1 ?>"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_primary" value="1" class="rounded text-blue-600">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Jadikan sebagai Gambar Sampul Utama (Cover)</span>
                            </label>

                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                                + Tambahkan ke Slide Show
                            </button>
                        </div>
                    </form>

                    <!-- Daftar Gambar Saat Ini di Kategori Terpilih -->
                    <div class="mt-6 space-y-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Daftar Slide Gambar Saat Ini (<?= count($cat_images) ?> Item)
                        </h3>

                        <?php if (empty($cat_images)): ?>
                            <div class="p-8 rounded-2xl border border-dashed border-slate-300 text-center text-xs text-slate-500">
                                Belum ada gambar pada kategori ini. Gunakan formulir di atas untuk mengunggah gambar.
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($cat_images as $idx => $img): ?>
                                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex flex-col sm:flex-row gap-4 items-start">
                                        <div class="relative w-full sm:w-40 aspect-16/10 rounded-xl overflow-hidden bg-slate-900 shrink-0 border border-slate-200 dark:border-slate-700">
                                            <img src="<?= e($img['image_path']) ?>"
                                                 alt="<?= e($img['alt_text'] ?? $active_category['title']) ?>"
                                                 class="w-full h-full object-cover">
                                            <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-bold">
                                                Slide #<?= $idx + 1 ?>
                                            </span>
                                            <?php if (!empty($img['is_primary'])): ?>
                                                <span class="absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-extrabold">
                                                    ★ Cover Utama
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex-1 w-full space-y-2.5">
                                            <form method="post" enctype="multipart/form-data" class="space-y-2.5 text-xs">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="update_image">
                                                <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                                <input type="hidden" name="category_id" value="<?= (int)$active_category['id'] ?>">

                                                <div class="grid sm:grid-cols-4 gap-2">
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Caption / Keterangan Slide</label>
                                                        <input type="text" name="caption"
                                                               value="<?= e($img['caption'] ?? '') ?>"
                                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Urutan</label>
                                                        <input type="number" name="sort_order"
                                                               value="<?= (int)($img['sort_order'] ?? ($idx + 1)) ?>"
                                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                                    </div>
                                                </div>

                                                <div class="grid sm:grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Path / URL Gambar</label>
                                                        <input type="text" name="image_path"
                                                               value="<?= e($img['image_path']) ?>"
                                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Ganti File Gambar (Opsional)</label>
                                                        <input type="file" name="replace_image_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                                                               class="w-full text-[11px] text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold">
                                                    </div>
                                                </div>

                                                <input type="hidden" name="alt_text" value="<?= e($img['alt_text'] ?? $img['caption'] ?? $active_category['title']) ?>">

                                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">
                                                        Simpan Slide
                                                    </button>
                                                </div>
                                            </form>

                                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                                                <?php if (empty($img['is_primary'])): ?>
                                                    <form method="post" class="inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="set_primary_image">
                                                        <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                                        <input type="hidden" name="category_id" value="<?= (int)$active_category['id'] ?>">
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold cursor-pointer">
                                                            ★ Jadikan Cover Utama
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="post" class="inline" onsubmit="return confirm('Hapus gambar slide ini dari galeri?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_image">
                                                    <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                                    <input type="hidden" name="category_id" value="<?= (int)$active_category['id'] ?>">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold cursor-pointer">
                                                        🗑️ Hapus Gambar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
