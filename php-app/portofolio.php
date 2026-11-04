<?php
/**
 * Halaman Portofolio Proyek & Galeri Multi-Gambar (`portofolio.php`)
 * Tampilan dan fitur interaktif menyerupai `slideshow.php` & `slideshow-detail.php`:
 * - Mode Grid (`portofolio.php`): Kartu proyek dengan cover 16:10, badge jumlah gambar, pratinjau strip thumbnail, dan tombol Read More.
 * - Mode Detail Slide Proyek (`portofolio.php?id=...`): Carousel multi-gambar interaktif, Autoplay, Lightbox Fullscreen, Thumbnail Strip,
 *   serta fitur Tambah Banyak Gambar (Multi-File Upload & Multi-URL).
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/upload.php';

ensure_portfolio_images_table($pdo);

$flash_success = '';
$flash_error   = '';

// Proses Penambahan Banyak Gambar / Proyek Baru (Multi-Upload & Multi-URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['portfolio_action'])) {
    verify_csrf();
    $p_action = (string)$_POST['portfolio_action'];

    try {
        // 1. TAMBAH BANYAK GAMBAR KE PROYEK YANG SUDAH ADA
        if ($p_action === 'add_project_images') {
            $target_project_id = (int)($_POST['project_id'] ?? 0);
            $default_caption   = clean_plain_text((string)($_POST['default_caption'] ?? ''), 250);
            $urls_raw          = trim((string)($_POST['image_urls_text'] ?? ''));
            $make_primary      = isset($_POST['is_primary']) ? 1 : 0;

            $proj_check = get_portfolio_project_by_id($pdo, $target_project_id);
            if (!$proj_check) {
                throw new RuntimeException('Data proyek portofolio tidak ditemukan.');
            }

            $added_items = [];

            // a) Proses Multi-File Upload (`project_images[]`)
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
                        throw new RuntimeException($up['error'] ?? 'Gagal mengunggah salah satu file gambar proyek.');
                    }
                    $orig_label = pathinfo((string)$_FILES['project_images']['name'][$i], PATHINFO_FILENAME);
                    $orig_label = trim(preg_replace('/[-_]+/', ' ', $orig_label));
                    $cap = $default_caption !== ''
                        ? ($file_count > 1 ? $default_caption . ' (' . ($i + 1) . ')' : $default_caption)
                        : ($proj_check['title'] . ($orig_label !== '' ? ' — ' . $orig_label : ''));
                    $added_items[] = [
                        'path'    => $up['path'],
                        'caption' => $cap,
                    ];
                }
            }

            // b) Proses Multi-Line URL Gambar (1 baris = 1 URL, opsional format `URL :: Keterangan`)
            if ($urls_raw !== '') {
                $lines = preg_split('/\r\n|\r|\n/', $urls_raw);
                foreach ($lines as $l_idx => $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = explode('::', $line, 2);
                    $url_val = trim($parts[0]);
                    $custom_cap = isset($parts[1]) ? trim($parts[1]) : '';
                    if ($url_val !== '') {
                        $cap = $custom_cap !== ''
                            ? $custom_cap
                            : ($default_caption !== '' ? $default_caption : ($proj_check['title'] . ' — Slide Tambahan'));
                        $added_items[] = [
                            'path'    => $url_val,
                            'caption' => $cap,
                        ];
                    }
                }
            }

            if (empty($added_items)) {
                throw new RuntimeException('Pilih minimal 1 file gambar (bisa pilih banyak file sekaligus) atau masukkan daftar URL gambar.');
            }

            if ($make_primary === 1) {
                $pdo->prepare("UPDATE project_images SET is_primary = 0 WHERE project_id = ?")->execute([$target_project_id]);
            }

            $stmt_max = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM project_images WHERE project_id = ?");
            $stmt_max->execute([$target_project_id]);
            $base_sort = (int)$stmt_max->fetchColumn();

            $stmt_ins = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($added_items as $idx => $item) {
                $is_prim = ($make_primary === 1 && $idx === 0) ? 1 : 0;
                $stmt_ins->execute([
                    $target_project_id,
                    $item['path'],
                    $item['caption'],
                    $item['caption'],
                    $base_sort + $idx + 1,
                    $is_prim,
                ]);

                if ($is_prim === 1 || empty($proj_check['image_url'])) {
                    $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?")->execute([$item['path'], $target_project_id]);
                    $proj_check['image_url'] = $item['path'];
                }
            }

            invalidate_live_preview_json('portfolios');
            $flash_success = count($added_items) . ' gambar baru berhasil ditambahkan ke galeri proyek "' . $proj_check['title'] . '".';
        }

        // 2. TAMBAH PROYEK PORTOFOLIO BARU BESERTA BANYAK GAMBAR SEKALIGUS
        elseif ($p_action === 'add_project_with_images') {
            $title              = clean_plain_text((string)($_POST['title'] ?? ''), 200);
            $client_or_location = clean_plain_text((string)($_POST['client_or_location'] ?? ''), 150);
            $category           = clean_plain_text((string)($_POST['category'] ?? 'Solusi Terpadu'), 100);
            $description        = clean_plain_text((string)($_POST['description'] ?? ''), 2000);
            $urls_raw           = trim((string)($_POST['image_urls_text'] ?? ''));

            if ($title === '' || $client_or_location === '') {
                throw new RuntimeException('Judul proyek dan nama klien / lokasi wajib diisi.');
            }

            $added_items = [];

            // Multi-file upload
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
                        throw new RuntimeException($up['error'] ?? 'Gagal mengunggah salah satu file gambar proyek.');
                    }
                    $added_items[] = [
                        'path'    => $up['path'],
                        'caption' => $title . ' — ' . $client_or_location . ' (Slide ' . ($i + 1) . ')',
                    ];
                }
            }

            // Multi-URL
            if ($urls_raw !== '') {
                $lines = preg_split('/\r\n|\r|\n/', $urls_raw);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = explode('::', $line, 2);
                    $url_val = trim($parts[0]);
                    $custom_cap = isset($parts[1]) ? trim($parts[1]) : '';
                    if ($url_val !== '') {
                        $added_items[] = [
                            'path'    => $url_val,
                            'caption' => $custom_cap !== '' ? $custom_cap : ($title . ' — ' . $client_or_location),
                        ];
                    }
                }
            }

            if (empty($added_items)) {
                $added_items[] = [
                    'path'    => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => $title . ' — ' . $client_or_location,
                ];
            }

            $cover_url = $added_items[0]['path'];
            $stmt_proj = $pdo->prepare("INSERT INTO projects (title, client_or_location, category, description, image_url, pdf_page) VALUES (?, ?, ?, ?, ?, 18)");
            $stmt_proj->execute([$title, $client_or_location, $category, $description ?: $title, $cover_url]);
            $new_project_id = (int)$pdo->lastInsertId();

            if ($new_project_id > 0) {
                $stmt_ins = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($added_items as $idx => $item) {
                    $stmt_ins->execute([
                        $new_project_id,
                        $item['path'],
                        $item['caption'],
                        $item['caption'],
                        $idx + 1,
                        $idx === 0 ? 1 : 0,
                    ]);
                }
            }

            invalidate_live_preview_json('portfolios');
            $flash_success = 'Proyek portofolio baru "' . $title . '" beserta ' . count($added_items) . ' gambar dokumentasi berhasil ditambahkan.';
            $_GET['id'] = $new_project_id;
        }

        // 3. JADIKAN GAMBAR TERTENTU SEBAGAI SAMPUL UTAMA PROYEK
        elseif ($p_action === 'set_primary_project_image') {
            $target_project_id = (int)($_POST['project_id'] ?? 0);
            $image_id          = (int)($_POST['image_id'] ?? 0);

            $stmt_find = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ? AND project_id = ? LIMIT 1");
            $stmt_find->execute([$image_id, $target_project_id]);
            $img_row = $stmt_find->fetch();
            if ($img_row) {
                $pdo->prepare("UPDATE project_images SET is_primary = 0 WHERE project_id = ?")->execute([$target_project_id]);
                $pdo->prepare("UPDATE project_images SET is_primary = 1 WHERE id = ?")->execute([$image_id]);
                $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?")->execute([$img_row['image_path'], $target_project_id]);
                invalidate_live_preview_json('portfolios');
                $flash_success = 'Gambar sampul utama (Cover) proyek berhasil diperbarui.';
            }
        }

        // 4. HAPUS SATU GAMBAR DARI GALERI PROYEK
        elseif ($p_action === 'delete_project_image') {
            $target_project_id = (int)($_POST['project_id'] ?? 0);
            $image_id          = (int)($_POST['image_id'] ?? 0);

            $stmt_cnt = $pdo->prepare("SELECT COUNT(*) FROM project_images WHERE project_id = ?");
            $stmt_cnt->execute([$target_project_id]);
            if ((int)$stmt_cnt->fetchColumn() <= 1) {
                throw new RuntimeException('Minimal harus tersisa 1 gambar pada galeri proyek ini.');
            }

            $pdo->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?")->execute([$image_id, $target_project_id]);

            // Pastikan sampul tetap sinkron
            $stmt_first = $pdo->prepare("SELECT id, image_path FROM project_images WHERE project_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1");
            $stmt_first->execute([$target_project_id]);
            $first_row = $stmt_first->fetch();
            if ($first_row) {
                $pdo->prepare("UPDATE project_images SET is_primary = 1 WHERE id = ?")->execute([(int)$first_row['id']]);
                $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?")->execute([$first_row['image_path'], $target_project_id]);
            }

            invalidate_live_preview_json('portfolios');
            $flash_success = 'Slide gambar berhasil dihapus dari galeri proyek.';
        }
    } catch (Throwable $e) {
        $flash_error = $e->getMessage();
    }
}

$detail_project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cat_filter        = isset($_GET['kategori']) ? trim((string)$_GET['kategori']) : '';

$all_projects = get_portfolio_projects($pdo, '');
$projects     = $cat_filter !== '' ? get_portfolio_projects($pdo, $cat_filter) : $all_projects;

$categories = [];
foreach ($all_projects as $ap) {
    $c = trim((string)($ap['category'] ?? ''));
    if ($c !== '' && !in_array($c, $categories, true)) {
        $categories[] = $c;
    }
}
sort($categories);

// Total seluruh slide foto di seluruh proyek
$total_all_slides = 0;
foreach ($all_projects as $ap) {
    $total_all_slides += count($ap['images'] ?? []);
}

// Cek apakah sedang membuka halaman detail proyek (`portofolio.php?id=...`)
$active_project = $detail_project_id > 0 ? get_portfolio_project_by_id($pdo, $detail_project_id) : null;

$portofolio_page = get_page_by_slug($pdo, 'portofolio');
if ($active_project) {
    $page_title = $active_project['title'] . ' (' . $active_project['client_or_location'] . ') - Slide Show Portofolio PT. KGS';
    $meta_description = !empty($active_project['description'])
        ? mb_substr(strip_tags((string)$active_project['description']), 0, 160)
        : $active_project['title'];
    if (!empty($active_project['cover_image'])) {
        $og_image_override = $active_project['cover_image'];
    }
} else {
    $page_title = !empty($portofolio_page['meta_title'])
        ? $portofolio_page['meta_title']
        : ($portofolio_page['title'] ?? 'Portofolio Proyek & Galeri Multi-Gambar');
    $meta_description = $portofolio_page['meta_description'] ?? $portofolio_page['summary'] ?? '';
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($active_project):
    $p_images = $active_project['images'] ?? [];
    if (empty($p_images)) {
        $p_images = [[
            'id'         => 0,
            'project_id' => (int)$active_project['id'],
            'image_path' => (string)($active_project['cover_image'] ?? 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80'),
            'caption'    => $active_project['title'] . ' — ' . $active_project['client_or_location'],
            'alt_text'   => $active_project['title'],
            'sort_order' => 1,
            'is_primary' => 1,
        ]];
    }
    $total_slides = count($p_images);
    $first_image  = $p_images[0];

    $related_projects = array_values(array_filter($all_projects, function ($item) use ($active_project) {
        return (int)$item['id'] !== (int)$active_project['id'];
    }));

    $js_slides = [];
    foreach ($p_images as $idx => $img) {
        $js_slides[] = [
            'index'   => $idx,
            'src'     => (string)($img['image_path'] ?? ''),
            'caption' => (string)($img['caption'] ?? $active_project['title']),
            'alt'     => (string)($img['alt_text'] ?? $active_project['title']),
        ];
    }
?>
<!-- ========================================================================= -->
<!-- MODE 1: HALAMAN DETAIL SLIDE SHOW PROYEK MULTI-GAMBAR (`portofolio.php?id=...`) -->
<!-- ========================================================================= -->
<main id="main-content" class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <!-- Top Banner & Breadcrumb (Identik dengan slideshow-detail.php) -->
    <section class="relative bg-slate-900 text-white py-12 lg:py-16 overflow-hidden border-b border-slate-800">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <nav aria-label="Breadcrumb" class="text-xs sm:text-sm text-slate-400 flex flex-wrap items-center gap-2">
                    <a href="index.php" class="hover:text-white transition">Beranda</a>
                    <span>/</span>
                    <a href="portofolio.php" class="hover:text-white transition">Portofolio Proyek</a>
                    <span>/</span>
                    <span class="text-blue-400 font-semibold"><?= e($active_project['title']) ?></span>
                </nav>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="#tambah-gambar-proyek"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition">
                        <span>+ Tambah Banyak Gambar</span>
                    </a>
                    <a href="portofolio.php"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 text-xs font-bold border border-slate-700 transition">
                        <span>← Kembali ke Daftar Portofolio</span>
                    </a>
                </div>
            </div>

            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold uppercase tracking-wider">
                        🖼️ Galeri Slide Show Proyek • <?= $total_slides ?> Gambar Tersedia
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold">
                        <?= e($active_project['category']) ?>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight mb-3">
                    <?= e($active_project['title']) ?>
                </h1>
                <p class="text-blue-300 font-bold text-sm sm:text-base flex items-center gap-2">
                    <span>📍 Lokasi / Klien:</span>
                    <span><?= e($active_project['client_or_location']) ?></span>
                </p>
            </div>

            <!-- Bar Pindah Cepat Antar Proyek Portofolio -->
            <?php if (count($all_projects) > 1): ?>
                <div class="mt-7 pt-5 border-t border-slate-800/90 flex items-center gap-2 overflow-x-auto pb-1">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 shrink-0 mr-1">Proyek:</span>
                    <?php foreach ($all_projects as $p_nav):
                        $is_curr = ((int)$p_nav['id'] === (int)$active_project['id']);
                    ?>
                        <a href="portofolio.php?id=<?= (int)$p_nav['id'] ?>"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $is_curr ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/80' ?>">
                            <?= e($p_nav['title']) ?> (<?= count($p_nav['images'] ?? []) ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-10 lg:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <?php if ($flash_success !== ''): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-4">
                    <span>✅ <?= e($flash_success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flash_error !== ''): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-4">
                    <span>⚠️ <?= e($flash_error) ?></span>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                <!-- Kolom Kiri (7 Kolom): Penampil Slide Show Utama + Thumbnail Strip + Form Tambah Banyak Gambar -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-md">
                        <!-- Toolbar Atas Viewer -->
                        <div class="px-4 sm:px-6 py-3.5 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2 text-white">
                            <div class="flex items-center gap-2.5">
                                <span id="kgs-slide-counter" class="px-3 py-1 rounded-full bg-blue-600 text-white text-xs font-extrabold tracking-wide">
                                    Slide 1 / <?= $total_slides ?>
                                </span>
                                <span class="text-xs text-slate-300 font-semibold hidden sm:inline">
                                    Klik gambar untuk mode layar penuh (Lightbox)
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <?php if ($total_slides > 1): ?>
                                    <button type="button"
                                            id="kgs-slide-autoplay-btn"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition cursor-pointer">
                                        <span id="kgs-slide-autoplay-icon">▶</span>
                                        <span id="kgs-slide-autoplay-label">Putar Otomatis</span>
                                    </button>
                                <?php endif; ?>
                                <button type="button"
                                        id="kgs-slide-fullscreen-btn"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 text-xs font-bold border border-blue-500/30 transition cursor-pointer">
                                    <span>🔍 Perbesar</span>
                                </button>
                            </div>
                        </div>

                        <!-- Area Gambar Utama -->
                        <div class="relative aspect-16/10 bg-slate-950 overflow-hidden group select-none">
                            <img id="kgs-main-slide-img"
                                 src="<?= e($first_image['image_path']) ?>"
                                 alt="<?= e($first_image['alt_text'] ?? $active_project['title']) ?>"
                                 class="w-full h-full object-cover cursor-zoom-in transition-opacity duration-300">

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent pointer-events-none"></div>

                            <?php if ($total_slides > 1): ?>
                                <button type="button"
                                        id="kgs-slide-prev-btn"
                                        aria-label="Gambar Sebelumnya"
                                        class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                                    ❮
                                </button>
                                <button type="button"
                                        id="kgs-slide-next-btn"
                                        aria-label="Gambar Berikutnya"
                                        class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                                    ❯
                                </button>
                            <?php endif; ?>

                            <!-- Caption Bar di Bawah Gambar Utama -->
                            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 text-white pointer-events-none">
                                <p id="kgs-main-slide-caption" class="text-sm sm:text-base font-bold text-white leading-snug drop-shadow">
                                    <?= e($first_image['caption'] ?: $active_project['title']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Strip Thumbnail Multi-Gambar -->
                        <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Daftar Gambar Pada Proyek Ini (<?= $total_slides ?> Slide)
                                </span>
                                <a href="#tambah-gambar-proyek" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                    + Tambah Gambar Lagi
                                </a>
                            </div>

                            <div id="kgs-thumbnail-strip" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5">
                                <?php foreach ($p_images as $idx => $img): ?>
                                    <div class="relative group/thumb">
                                        <button type="button"
                                                data-slide-index="<?= $idx ?>"
                                                title="<?= e($img['caption'] ?? $active_project['title']) ?>"
                                                class="kgs-thumb-btn w-full relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none <?= $idx === 0 ? 'border-blue-600 ring-2 ring-blue-500/40 opacity-100' : 'border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100' ?>">
                                            <img src="<?= e($img['image_path']) ?>"
                                                 alt="<?= e($img['alt_text'] ?? $active_project['title']) ?>"
                                                 loading="lazy"
                                                 class="w-full h-full object-cover">
                                            <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-bold">
                                                <?= $idx + 1 ?>
                                            </span>
                                            <?php if (!empty($img['is_primary'])): ?>
                                                <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-emerald-600/90 text-white text-[9px] font-extrabold uppercase">
                                                    Sampul
                                                </span>
                                            <?php endif; ?>
                                        </button>

                                        <?php if (!empty($img['id'])): ?>
                                            <div class="mt-1 flex items-center justify-between gap-1 text-[10px]">
                                                <?php if (empty($img['is_primary'])): ?>
                                                    <form method="POST" class="inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="portfolio_action" value="set_primary_project_image">
                                                        <input type="hidden" name="project_id" value="<?= (int)$active_project['id'] ?>">
                                                        <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                                        <button type="submit" class="text-blue-600 hover:underline font-semibold cursor-pointer" title="Jadikan Sampul Utama">
                                                            ★ Sampul
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-emerald-600 font-bold">★ Cover</span>
                                                <?php endif; ?>

                                                <?php if ($total_slides > 1): ?>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Hapus gambar slide ini dari proyek?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="portfolio_action" value="delete_project_image">
                                                        <input type="hidden" name="project_id" value="<?= (int)$active_project['id'] ?>">
                                                        <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                                        <button type="submit" class="text-rose-600 hover:underline font-semibold cursor-pointer" title="Hapus Slide">
                                                            ✕ Hapus
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Tambah Banyak Gambar (Multi-File Upload & Multi-URL) ke Proyek Ini -->
                    <div id="tambah-gambar-proyek" class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-blue-500/30 p-6 sm:p-7 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 block">
                                    Galeri Multi-Gambar Proyek
                                </span>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">
                                    + Tambah Banyak Gambar ke Proyek "<?= e($active_project['title']) ?>"
                                </h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-[11px] font-bold">
                                Mendukung Banyak File Sekaligus
                            </span>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                            <?= csrf_field() ?>
                            <input type="hidden" name="portfolio_action" value="add_project_images">
                            <input type="hidden" name="project_id" value="<?= (int)$active_project['id'] ?>">

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                    <label class="block font-extrabold text-slate-800 dark:text-slate-200 mb-1">
                                        1. Upload Banyak File Gambar Sekaligus (Multi-Select)
                                    </label>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2.5">
                                        Tekan <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-900 border rounded text-[10px]">Ctrl</kbd> / <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-900 border rounded text-[10px]">Shift</kbd> saat memilih file untuk mengunggah beberapa foto dokumentasi sekaligus (JPG, PNG, WEBP, GIF).
                                    </p>
                                    <input type="file"
                                           name="project_images[]"
                                           multiple
                                           accept=".jpg,.jpeg,.png,.webp,.gif"
                                           class="w-full text-xs text-slate-700 dark:text-slate-200 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                    <label class="block font-extrabold text-slate-800 dark:text-slate-200 mb-1">
                                        2. Atau Tempel Banyak URL Gambar (1 Baris = 1 Gambar)
                                    </label>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                                        Opsional: tambahkan <code class="text-blue-600">:: Keterangan</code> di akhir URL.
                                    </p>
                                    <textarea name="image_urls_text"
                                              rows="3"
                                              placeholder="https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?w=1200 :: Dokumentasi tampak depan&#10;https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200 :: Pengujian perangkat"
                                              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 font-mono"></textarea>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Caption / Keterangan Default (Opsional)
                                    </label>
                                    <input type="text"
                                           name="default_caption"
                                           placeholder="Contoh: Dokumentasi Instalasi Lapangan <?= e($active_project['title']) ?>"
                                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100">
                                </div>
                                <div class="pt-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-700 dark:text-slate-300 font-semibold">
                                        <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span>Jadikan gambar pertama yang diunggah sebagai Sampul Utama (Cover)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
                                    + Simpan &amp; Tambahkan ke Slide Proyek
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kolom Kanan (5 Kolom): Rincian Teknis Pekerjaan & Spesifikasi Proyek -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-3">
                            <span>📘 Dokumentasi &amp; Rincian Teknis Proyek</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <?= e($active_project['title']) ?>
                        </h2>

                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/70 dark:border-slate-700">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi / Klien</span>
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white mt-0.5 block">
                                    📍 <?= e($active_project['client_or_location']) ?>
                                </span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/70 dark:border-slate-700">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kategori Pekerjaan</span>
                                <span class="text-xs sm:text-sm font-extrabold text-blue-600 dark:text-blue-400 mt-0.5 block">
                                    <?= e($active_project['category']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="prose prose-slate dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <?= format_slideshow_description_html((string)$active_project['description']) ?>
                        </div>

                        <!-- Ringkasan Daftar Slide Gambar -->
                        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                                Daftar Keterangan Slide Foto (<?= $total_slides ?> Gambar):
                            </h3>
                            <ol class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                                <?php foreach ($p_images as $idx => $img): ?>
                                    <li class="flex items-start gap-2.5">
                                        <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-extrabold text-[11px] flex items-center justify-center shrink-0 mt-0.5">
                                            <?= $idx + 1 ?>
                                        </span>
                                        <span><?= e($img['caption'] ?: $active_project['title']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                        <!-- Box Konsultasi Cepat -->
                        <div class="mt-8 pt-6 border-t border-slate-200/80 dark:border-slate-800">
                            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50/70 dark:from-slate-800/90 dark:to-slate-800/50 border border-blue-200/70 dark:border-slate-700">
                                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white mb-1.5">
                                    Butuh Implementasi Serupa seperti di <?= e($active_project['client_or_location']) ?>?
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                    Hubungi tim engineer PT. Kreatif Global Solusindo untuk survei lokasi, desain teknis, fabrikasi, maupun instalasi terpadu.
                                </p>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <a href="kontak.php?layanan=<?= rawurlencode((string)$active_project['title']) ?>#konsultasi"
                                       class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                                        Konsultasi Proyek Serupa →
                                    </a>
                                    <a href="admin/portofolio.php?edit_id=<?= (int)$active_project['id'] ?>"
                                       class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition">
                                        Kelola di Admin CMS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah: Proyek Portofolio Lainnya -->
            <?php if (!empty($related_projects)): ?>
                <div class="mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">
                                Dokumentasi Lapangan Lainnya
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Galeri Slide Show Proyek Portofolio Lainnya
                            </h2>
                        </div>
                        <a href="portofolio.php" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat Semua Proyek (<?= count($all_projects) ?>) →
                        </a>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach (array_slice($related_projects, 0, 6) as $rel):
                            $rel_imgs = $rel['images'] ?? [];
                            $rel_count = count($rel_imgs);
                            $rel_cover = !empty($rel['cover_image']) ? $rel['cover_image'] : (!empty($rel_imgs[0]['image_path']) ? $rel_imgs[0]['image_path'] : '');
                        ?>
                            <a href="portofolio.php?id=<?= (int)$rel['id'] ?>"
                               class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs hover:shadow-lg hover:border-blue-500/50 transition flex flex-col">
                                <div class="relative aspect-16/9 bg-slate-900 overflow-hidden">
                                    <img src="<?= e($rel_cover) ?>"
                                         alt="<?= e($rel['title']) ?>"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-transparent"></div>
                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-blue-600/95 text-white text-[11px] font-bold">
                                        🖼️ <?= $rel_count ?> Gambar
                                    </span>
                                    <div class="absolute bottom-3 left-3.5 right-3.5">
                                        <span class="text-[10px] font-bold text-blue-300 block">📍 <?= e($rel['client_or_location']) ?></span>
                                        <h3 class="text-base font-extrabold text-white group-hover:text-blue-300 transition">
                                            <?= e($rel['title']) ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex items-center justify-between gap-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
                                        <?= e($rel['description']) ?>
                                    </span>
                                    <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400 shrink-0">
                                        Read More →
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Modal Lightbox Fullscreen -->
<div id="kgs-lightbox-modal"
     class="fixed inset-0 z-50 hidden bg-slate-950/95 backdrop-blur-md flex-col justify-between p-4 sm:p-6 select-none"
     role="dialog"
     aria-modal="true"
     aria-label="Penampil Gambar Proyek Layar Penuh">
    <div class="flex items-center justify-between gap-4 text-white max-w-6xl w-full mx-auto">
        <div class="flex items-center gap-3">
            <span id="kgs-lightbox-counter" class="px-3 py-1 rounded-full bg-blue-600 text-xs font-extrabold">
                1 / <?= $total_slides ?>
            </span>
            <span class="text-sm font-bold text-slate-200 truncate max-w-md">
                <?= e($active_project['title']) ?> — <?= e($active_project['client_or_location']) ?>
            </span>
        </div>
        <button type="button"
                id="kgs-lightbox-close-btn"
                aria-label="Tutup Layar Penuh"
                class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-red-600 text-white text-xs font-bold border border-slate-700 transition cursor-pointer">
            ✕ Tutup (ESC)
        </button>
    </div>

    <div class="relative flex-1 flex items-center justify-center my-4 max-w-6xl w-full mx-auto overflow-hidden">
        <?php if ($total_slides > 1): ?>
            <button type="button"
                    id="kgs-lightbox-prev-btn"
                    aria-label="Slide Sebelumnya"
                    class="absolute left-2 sm:left-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                ❮
            </button>
        <?php endif; ?>

        <img id="kgs-lightbox-img"
             src="<?= e($first_image['image_path']) ?>"
             alt="<?= e($first_image['alt_text'] ?? $active_project['title']) ?>"
             class="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl">

        <?php if ($total_slides > 1): ?>
            <button type="button"
                    id="kgs-lightbox-next-btn"
                    aria-label="Slide Berikutnya"
                    class="absolute right-2 sm:right-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                ❯
            </button>
        <?php endif; ?>
    </div>

    <div class="max-w-4xl w-full mx-auto text-center pb-2">
        <p id="kgs-lightbox-caption" class="text-sm sm:text-base font-semibold text-slate-200">
            <?= e($first_image['caption'] ?: $active_project['title']) ?>
        </p>
    </div>
</div>

<script>
(function () {
    var slides = <?= json_encode($js_slides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    if (!slides || !slides.length) return;

    var currentIndex = 0;
    var autoPlayTimer = null;

    var mainImg = document.getElementById('kgs-main-slide-img');
    var mainCaption = document.getElementById('kgs-main-slide-caption');
    var counterEl = document.getElementById('kgs-slide-counter');
    var prevBtn = document.getElementById('kgs-slide-prev-btn');
    var nextBtn = document.getElementById('kgs-slide-next-btn');
    var autoPlayBtn = document.getElementById('kgs-slide-autoplay-btn');
    var autoPlayIcon = document.getElementById('kgs-slide-autoplay-icon');
    var autoPlayLabel = document.getElementById('kgs-slide-autoplay-label');
    var fullscreenBtn = document.getElementById('kgs-slide-fullscreen-btn');
    var thumbBtns = document.querySelectorAll('.kgs-thumb-btn');

    var lightboxModal = document.getElementById('kgs-lightbox-modal');
    var lightboxImg = document.getElementById('kgs-lightbox-img');
    var lightboxCaption = document.getElementById('kgs-lightbox-caption');
    var lightboxCounter = document.getElementById('kgs-lightbox-counter');
    var lightboxCloseBtn = document.getElementById('kgs-lightbox-close-btn');
    var lightboxPrevBtn = document.getElementById('kgs-lightbox-prev-btn');
    var lightboxNextBtn = document.getElementById('kgs-lightbox-next-btn');

    function renderSlide(idx) {
        if (idx < 0) idx = slides.length - 1;
        if (idx >= slides.length) idx = 0;
        currentIndex = idx;
        var item = slides[currentIndex];

        if (mainImg) {
            mainImg.src = item.src;
            mainImg.alt = item.alt || item.caption || '';
        }
        if (mainCaption) {
            mainCaption.textContent = item.caption || '';
        }
        if (counterEl) {
            counterEl.textContent = 'Slide ' + (currentIndex + 1) + ' / ' + slides.length;
        }
        if (lightboxImg) {
            lightboxImg.src = item.src;
            lightboxImg.alt = item.alt || item.caption || '';
        }
        if (lightboxCaption) {
            lightboxCaption.textContent = item.caption || '';
        }
        if (lightboxCounter) {
            lightboxCounter.textContent = (currentIndex + 1) + ' / ' + slides.length;
        }

        thumbBtns.forEach(function (btn, bIdx) {
            if (bIdx === currentIndex) {
                btn.className = 'kgs-thumb-btn w-full relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none border-blue-600 ring-2 ring-blue-500/40 opacity-100';
            } else {
                btn.className = 'kgs-thumb-btn w-full relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100';
            }
        });
    }

    function toggleAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
            if (autoPlayIcon) autoPlayIcon.textContent = '▶';
            if (autoPlayLabel) autoPlayLabel.textContent = 'Putar Otomatis';
        } else {
            autoPlayTimer = setInterval(function () {
                renderSlide(currentIndex + 1);
            }, 4000);
            if (autoPlayIcon) autoPlayIcon.textContent = '⏸';
            if (autoPlayLabel) autoPlayLabel.textContent = 'Jeda Otomatis';
        }
    }

    function openLightbox() {
        if (!lightboxModal) return;
        lightboxModal.classList.remove('hidden');
        lightboxModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightboxModal) return;
        lightboxModal.classList.add('hidden');
        lightboxModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { renderSlide(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { renderSlide(currentIndex + 1); });
    if (autoPlayBtn) autoPlayBtn.addEventListener('click', toggleAutoPlay);
    if (fullscreenBtn) fullscreenBtn.addEventListener('click', openLightbox);
    if (mainImg) mainImg.addEventListener('click', openLightbox);

    thumbBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idx = parseInt(btn.getAttribute('data-slide-index') || '0', 10);
            renderSlide(idx);
        });
    });

    if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
    if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', function () { renderSlide(currentIndex - 1); });
    if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', function () { renderSlide(currentIndex + 1); });
    if (lightboxModal) {
        lightboxModal.addEventListener('click', function (e) {
            if (e.target === lightboxModal) closeLightbox();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!lightboxModal || lightboxModal.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') renderSlide(currentIndex - 1);
        if (e.key === 'ArrowRight') renderSlide(currentIndex + 1);
    });
})();
</script>

<?php else: ?>
<!-- ========================================================================= -->
<!-- MODE 2: HALAMAN UTAMA GRID SLIDE SHOW PORTOFOLIO (`portofolio.php`)      -->
<!-- ========================================================================= -->
<main id="main-content" class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <!-- Hero Banner (Menyerupai slideshow.php) -->
    <section class="relative bg-slate-900 text-white py-16 lg:py-20 overflow-hidden border-b border-slate-800">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <nav aria-label="Breadcrumb" class="text-xs sm:text-sm text-slate-400 mb-4 flex items-center gap-2">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>/</span>
                <span class="text-blue-400 font-semibold">Portofolio Proyek &amp; Galeri Slide Show</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold uppercase tracking-wider" data-live-setting="portofolio_kicker">
                        <span>📸 <?= e(get_setting($pdo, 'portofolio_kicker', 'Slide Show Portofolio & Dokumentasi Lapangan')) ?></span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight" data-live-page-slug="portofolio" data-live-page-field="title">
                        <?= e($portofolio_page['title'] ?: 'Portofolio Proyek & Klien Korporasi') ?>
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed" data-live-page-slug="portofolio" data-live-page-field="summary">
                        <?= e($portofolio_page['summary'] ?: 'Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN di Sumatera Selatan hingga berbagai provinsi di Indonesia. Klik salah satu kartu proyek di bawah ini untuk membuka halaman detail Slide Show multi-gambar dan menambahkan banyak foto dokumentasi.') ?>
                    </p>
                </div>

                <!-- Statistik Ringkas & Tombol Tambah -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <div class="px-4 py-3 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-center min-w-[120px]">
                        <div class="text-2xl font-extrabold text-blue-400"><?= count($all_projects) ?></div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Proyek</div>
                    </div>
                    <div class="px-4 py-3 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-center min-w-[120px]">
                        <div class="text-2xl font-extrabold text-emerald-400"><?= $total_all_slides ?></div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Total Slide Foto</div>
                    </div>
                    <button type="button"
                            onclick="var el=document.getElementById('form-proyek-baru'); if(el){el.classList.toggle('hidden');}"
                            class="px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition cursor-pointer">
                        + Proyek &amp; Banyak Gambar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <?php if ($flash_success !== ''): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold">
                    ✅ <?= e($flash_success) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash_error !== ''): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold">
                    ⚠️ <?= e($flash_error) ?>
                </div>
            <?php endif; ?>

            <!-- Form Collapsible: Tambah Proyek Portofolio Baru Beserta Banyak Gambar -->
            <div id="form-proyek-baru" class="hidden bg-white dark:bg-slate-900 rounded-3xl border-2 border-blue-500/40 p-6 sm:p-8 shadow-lg">
                <div class="flex items-center justify-between gap-4 mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 block">
                            Dokumentasi Portofolio Multi-Gambar
                        </span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
                            Tambah Proyek Portofolio Baru &amp; Upload Banyak Gambar Sekaligus
                        </h2>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('form-proyek-baru').classList.add('hidden');"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold cursor-pointer">
                        ✕ Tutup
                    </button>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    <?= csrf_field() ?>
                    <input type="hidden" name="portfolio_action" value="add_project_with_images">

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Proyek / Instalasi *</label>
                            <input type="text" name="title" required placeholder="Contoh: Instalasi Tower & Fiber Optik" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Klien / Lokasi Site *</label>
                            <input type="text" name="client_or_location" required placeholder="Contoh: PT. Bukit Asam (Persero) Tbk" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori Pekerjaan *</label>
                            <input type="text" name="category" list="portfolio-category-list" required value="Fabrikasi Tower" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                            <datalist id="portfolio-category-list">
                                <?php foreach ($categories as $cat_opt): ?>
                                    <option value="<?= e($cat_opt) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Rincian Teknis Pekerjaan *</label>
                        <textarea name="description" rows="3" required placeholder="Jelaskan lingkup pekerjaan teknis, spesifikasi material, dan hasil implementasi di lokasi klien..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5"></textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <label class="block font-extrabold text-slate-800 dark:text-slate-200 mb-1">
                                Upload Banyak File Gambar Sekaligus (Multi-File)
                            </label>
                            <p class="text-[11px] text-slate-500 mb-2">Pilih beberapa file foto dokumentasi sekaligus.</p>
                            <input type="file" name="project_images[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full text-xs file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <label class="block font-extrabold text-slate-800 dark:text-slate-200 mb-1">
                                Atau Masukkan Banyak URL Gambar (1 Baris = 1 Gambar)
                            </label>
                            <textarea name="image_urls_text" rows="3" placeholder="https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?w=1200 :: Foto 1&#10;https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200 :: Foto 2" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 font-mono"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
                            Simpan Proyek &amp; Galeri Slide Show
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filter Kategori & Tautan Klien -->
            <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 mr-1">Kategori:</span>
                    <a href="portofolio.php"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition <?= empty($cat_filter) ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-800' ?>">
                        Semua Proyek (<?= count($all_projects) ?>)
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="portofolio.php?kategori=<?= urlencode($cat) ?>"
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition <?= $cat_filter === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-800' ?>">
                            <?= e($cat) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <a href="klien.php" class="inline-flex items-center gap-1.5 text-xs font-extrabold text-blue-600 dark:text-blue-400 hover:underline">
                    <span>Lihat Daftar 100+ Klien Resmi</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Grid Kartu Portofolio (Tampilan Seperti isi slideshow.php) -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7">
                <?php foreach ($projects as $idx => $p):
                    $p_id       = (int)$p['id'];
                    $images     = $p['images'] ?? [];
                    $img_count  = count($images);
                    $cover      = !empty($p['cover_image']) ? $p['cover_image'] : (!empty($p['image_url']) ? $p['image_url'] : 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80');
                    $detail_url = 'portofolio.php?id=' . $p_id;
                ?>
                    <article class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs hover:shadow-xl hover:border-blue-500/60 dark:hover:border-blue-500/50 transition-all duration-300 flex flex-col">
                        <!-- Bagian Gambar Sampul (Klik langsung ke halaman detail slide proyek) -->
                        <a href="<?= e($detail_url) ?>" class="relative block aspect-16/10 bg-slate-900 overflow-hidden">
                            <img src="<?= e($cover) ?>"
                                 alt="<?= e($p['title']) ?>"
                                 data-live-img="project_image:<?= $p_id ?>"
                                 loading="<?= $idx < 3 ? 'eager' : 'lazy' ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>

                            <!-- Nomor Urut & Badge Kategori -->
                            <div class="absolute top-3.5 left-3.5 flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-white text-xs font-extrabold border border-white/15">
                                    #<?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?>
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-950/85 backdrop-blur-md text-blue-200 text-[11px] font-bold border border-blue-400/20" data-live-record="projects:<?= $p_id ?>:category">
                                    <?= e($p['category']) ?>
                                </span>
                            </div>

                            <!-- Badge Jumlah Gambar -->
                            <span class="absolute top-3.5 right-3.5 px-3 py-1 rounded-full bg-blue-600/95 backdrop-blur-md text-white text-xs font-bold shadow-sm flex items-center gap-1.5">
                                <span>🖼️</span>
                                <span><?= $img_count ?> Gambar</span>
                            </span>

                            <!-- Lokasi & Judul di Atas Gradient Bawah -->
                            <div class="absolute bottom-3.5 left-4 right-4">
                                <div class="text-[11px] font-bold text-blue-300 flex items-center gap-1 mb-0.5">
                                    <span>📍</span>
                                    <span data-live-record="projects:<?= $p_id ?>:client_or_location"><?= e($p['client_or_location']) ?></span>
                                </div>
                                <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight group-hover:text-blue-300 transition leading-snug" data-live-record="projects:<?= $p_id ?>:title">
                                    <?= e($p['title']) ?>
                                </h2>
                            </div>
                        </a>

                        <!-- Bagian Deskripsi & Pratinjau Strip Thumbnail -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm leading-relaxed line-clamp-3 mb-5" data-live-record="projects:<?= $p_id ?>:description">
                                    <?= e($p['description']) ?>
                                </p>

                                <!-- Strip Pratinjau Thumbnail Mini (Menyerupai slideshow.php) -->
                                <?php if (!empty($images)): ?>
                                    <div class="mb-5">
                                        <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
                                            <span>Pratinjau Slide Foto:</span>
                                            <a href="<?= e($detail_url) ?>#tambah-gambar-proyek" class="text-blue-600 dark:text-blue-400 hover:underline normal-case font-extrabold">
                                                + Tambah Gambar
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <?php foreach (array_slice($images, 0, 4) as $t_idx => $thumb): ?>
                                                <a href="<?= e($detail_url) ?>"
                                                   title="<?= e($thumb['caption'] ?? $p['title']) ?>"
                                                   class="relative w-14 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-blue-500 transition shrink-0">
                                                    <img src="<?= e($thumb['image_path']) ?>"
                                                         alt="<?= e($thumb['alt_text'] ?? $p['title']) ?>"
                                                         loading="lazy"
                                                         class="w-full h-full object-cover">
                                                </a>
                                            <?php endforeach; ?>
                                            <?php if ($img_count > 4): ?>
                                                <a href="<?= e($detail_url) ?>"
                                                   class="w-14 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs font-extrabold text-blue-600 dark:text-blue-400 hover:bg-blue-50 transition shrink-0">
                                                    +<?= $img_count - 4 ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tombol Aksi ke Halaman Detail Proyek -->
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Klik untuk galeri &amp; tambah foto
                                </span>
                                <a href="<?= e($detail_url) ?>"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                                    <span>Read More</span>
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Workshop & Technical Capability Highlight -->
            <div class="bg-slate-900 text-white rounded-3xl p-8 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6 border border-slate-800">
                <div class="space-y-2 max-w-2xl">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-400" data-live-setting="workshop_kicker">
                        <?= e(get_setting($pdo, 'workshop_kicker', 'Workshop Mandiri di Palembang')) ?>
                    </span>
                    <h3 class="text-xl font-bold" data-live-setting="workshop_title">
                        <?= e(get_setting($pdo, 'workshop_title', 'Memiliki Workshop Fabrikasi Tower Sendiri')) ?>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-live-setting="workshop_desc">
                        <?= e(get_setting($pdo, 'workshop_desc', 'PT. Kreatif Global Solusindo memproduksi dan merakit besi rangka Guywire Tower dan Self Supporting Tower di workshop mandiri Palembang, memastikan kontrol kualitas material, pengelasan presisi, galvanis anti karat, dan pengujian kekuatan tarik seling sebelum instalasi di lokasi klien.')) ?>
                    </p>
                </div>
                <a href="kontak.php#konsultasi" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md transition whitespace-nowrap">
                    Minta Penawaran Fabrikasi &amp; Ereksi
                </a>
            </div>
        </div>
    </section>
</main>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
