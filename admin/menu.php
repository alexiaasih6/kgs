<?php
$admin_page_title = 'Pengaturan Navigasi & Isi Halaman (Header & Footer)';
require_once __DIR__ . '/layout-header.php';

ensure_menus_table($pdo);
ensure_pages_complete($pdo);

$msg = '';
$error_msg = '';
$active_tab = $_GET['tab'] ?? 'all';
if (!in_array($active_tab, ['all', 'header', 'footer'], true)) {
    $active_tab = 'all';
}

// Ambil seluruh data halaman untuk diintegrasikan dengan setiap item navigasi
$pages_by_slug = [];
$static_pages_list = [];
try {
    $all_pages = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id ASC")->fetchAll();
    foreach ($all_pages as $pg) {
        $pages_by_slug[$pg['slug']] = $pg;
        $static_pages_list[] = $pg;
    }
} catch (Exception $e) {
    $pages_by_slug = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $redirect_tab = $_POST['current_tab'] ?? $active_tab;
    if (in_array($redirect_tab, ['all', 'header', 'footer'], true)) {
        $active_tab = $redirect_tab;
    }

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $raw_url = trim($_POST['url'] ?? '');
        $location = ($_POST['location'] ?? 'header') === 'footer' ? 'footer' : 'header';
        $sort_order = (int)($_POST['sort_order'] ?? 1);
        $target = ($_POST['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
        $is_active = isset($_POST['is_active']) ? ((int)$_POST['is_active'] === 1 ? 1 : 0) : 1;

        // Data isi halaman (jika diisi saat membuat menu baru)
        $page_title_inp = trim($_POST['page_title'] ?? '') ?: $title;
        $page_slug_inp = create_slug(trim($_POST['page_slug'] ?? '') ?: $title);
        $page_summary_inp = trim($_POST['page_summary'] ?? '');
        $page_content_inp = trim($_POST['page_content'] ?? '');

        if ($raw_url === '' && $page_slug_inp !== '') {
            $raw_url = resolve_page_slug_to_url($page_slug_inp);
        }
        $url = sanitize_nav_url($raw_url);

        if ($title === '' || $raw_url === '') {
            $error_msg = 'Label Menu dan Target URL / Slug wajib diisi.';
        } elseif ($url === '#' && strtolower($raw_url) !== '#') {
            $error_msg = 'Format Target URL tidak diizinkan demi keamanan.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO menus (title, url, location, sort_order, target, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $url, $location, $sort_order, $target, $is_active]);

            // Simpan atau perbarui juga isi konten halaman di tabel pages
            $resolved_slug = resolve_url_to_page_slug($url);
            if ($resolved_slug !== '' && ($page_content_inp !== '' || $page_summary_inp !== '' || !isset($pages_by_slug[$resolved_slug]))) {
                $final_content = $page_content_inp !== '' ? $page_content_inp : ('<h2>' . e($page_title_inp) . '</h2><p>' . e($page_summary_inp ?: "Informasi lengkap mengenai $page_title_inp PT. Kreatif Global Solusindo.") . '</p>');
                $final_summary = $page_summary_inp !== '' ? $page_summary_inp : "Informasi resmi mengenai $page_title_inp PT. Kreatif Global Solusindo.";
                $stmt_pg = $pdo->prepare("INSERT INTO pages (title, slug, summary, content, meta_title, meta_description, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), content = VALUES(content), updated_at = NOW()");
                $stmt_pg->execute([
                    $page_title_inp,
                    $resolved_slug,
                    $final_summary,
                    $final_content,
                    $page_title_inp . ' - PT. KGS',
                    $final_summary,
                    count($pages_by_slug) + 1
                ]);
            }

            log_audit($pdo, 'Tambah Menu & Halaman', "Menambahkan menu [$location]: $title -> $url beserta konten halaman");
            $msg = "Menu \"$title\" dan isi konten halamannya berhasil ditambahkan ke Navigasi " . ($location === 'header' ? 'Header (Atas)' : 'Footer (Bawah)') . ".";
        }
    } elseif ($action === 'edit_single') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $raw_url = trim($_POST['url'] ?? '');
        $url = sanitize_nav_url($raw_url);
        $location = ($_POST['location'] ?? 'header') === 'footer' ? 'footer' : 'header';
        $sort_order = (int)($_POST['sort_order'] ?? 1);
        $target = ($_POST['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
        $is_active = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;

        // Data isi konten halaman yang diedit bersamaan
        $page_slug = trim($_POST['page_slug'] ?? '');
        $page_title = trim($_POST['page_title'] ?? '');
        $page_summary = trim($_POST['page_summary'] ?? '');
        $page_content = trim($_POST['page_content'] ?? '');
        $page_meta_title = trim($_POST['page_meta_title'] ?? '');
        $page_meta_desc = trim($_POST['page_meta_description'] ?? '');

        if ($id <= 0) {
            $error_msg = 'ID menu tidak valid.';
        } elseif ($title === '' || $raw_url === '') {
            $error_msg = 'Label Menu dan Target URL tidak boleh kosong.';
        } elseif ($url === '#' && strtolower($raw_url) !== '#') {
            $error_msg = 'Skema URL berbahaya ditolak oleh sistem keamanan.';
        } else {
            $stmt = $pdo->prepare("UPDATE menus SET title = ?, url = ?, location = ?, sort_order = ?, target = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $url, $location, $sort_order, $target, $is_active, $id]);

            $target_slug = $page_slug !== '' ? create_slug($page_slug) : resolve_url_to_page_slug($url);
            if ($target_slug !== '' && $page_title !== '' && $page_content !== '') {
                $stmt_pg = $pdo->prepare("INSERT INTO pages (title, slug, summary, content, meta_title, meta_description, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), content = VALUES(content), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description), updated_at = NOW()");
                $stmt_pg->execute([
                    $page_title,
                    $target_slug,
                    $page_summary,
                    $page_content,
                    $page_meta_title ?: ($page_title . ' - PT. KGS'),
                    $page_meta_desc ?: $page_summary,
                    $sort_order
                ]);
            }

            log_audit($pdo, 'Edit Menu & Isi Halaman', "Memperbarui menu ID #$id [$location]: $title ($url) serta isi halaman [$target_slug]");
            $msg = "Perubahan menu \"$title\" dan isi konten halaman website berhasil disimpan!";
        }
    } elseif ($action === 'bulk_save') {
        $items = $_POST['items'] ?? [];
        if (is_array($items) && count($items) > 0) {
            $updated_count = 0;
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE menus SET sort_order = ?, title = ?, url = ?, target = ?, is_active = ? WHERE id = ?");
                foreach ($items as $id_key => $data) {
                    $menu_id = (int)$id_key;
                    if ($menu_id <= 0 || !is_array($data)) {
                        continue;
                    }
                    $m_title = trim((string)($data['title'] ?? ''));
                    $m_raw_url = trim((string)($data['url'] ?? ''));
                    $m_url = sanitize_nav_url($m_raw_url);
                    $m_sort = (int)($data['sort_order'] ?? 1);
                    $m_target = (($data['target'] ?? '_self') === '_blank') ? '_blank' : '_self';
                    $m_active = ((int)($data['is_active'] ?? 0) === 1) ? 1 : 0;

                    if ($m_title !== '' && $m_raw_url !== '') {
                        $stmt->execute([$m_sort, $m_title, $m_url, $m_target, $m_active, $menu_id]);
                        $updated_count++;
                    }
                }
                $pdo->commit();
                log_audit($pdo, 'Simpan Perubahan Navigasi', "Memperbarui $updated_count item navigasi sekaligus");
                $msg = "Berhasil menyimpan perubahan pada $updated_count item navigasi.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = 'Gagal menyimpan perubahan massal: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT title, is_active FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                $new_status = ((int)$row['is_active'] === 1) ? 0 : 1;
                $upd = $pdo->prepare("UPDATE menus SET is_active = ? WHERE id = ?");
                $upd->execute([$new_status, $id]);
                $status_label = $new_status === 1 ? 'Aktif' : 'Nonaktif';
                log_audit($pdo, 'Ubah Status Menu', "Mengubah status menu \"{$row['title']}\" menjadi $status_label");
                $msg = "Status menu \"{$row['title']}\" berhasil diubah menjadi $status_label.";
            }
        }
    } elseif ($action === 'move_order') {
        $id = (int)($_POST['id'] ?? 0);
        $direction = $_POST['direction'] ?? 'up';
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT id, location, sort_order FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            if ($current) {
                $loc = $current['location'];
                $cur_order = (int)$current['sort_order'];
                if ($direction === 'up') {
                    $neighbor_stmt = $pdo->prepare("SELECT id, sort_order FROM menus WHERE location = ? AND (sort_order < ? OR (sort_order = ? AND id < ?)) ORDER BY sort_order DESC, id DESC LIMIT 1");
                    $neighbor_stmt->execute([$loc, $cur_order, $cur_order, $id]);
                } else {
                    $neighbor_stmt = $pdo->prepare("SELECT id, sort_order FROM menus WHERE location = ? AND (sort_order > ? OR (sort_order = ? AND id > ?)) ORDER BY sort_order ASC, id ASC LIMIT 1");
                    $neighbor_stmt->execute([$loc, $cur_order, $cur_order, $id]);
                }
                $neighbor = $neighbor_stmt->fetch();
                if ($neighbor) {
                    $n_order = (int)$neighbor['sort_order'];
                    if ($n_order === $cur_order) {
                        $n_order = $direction === 'up' ? max(1, $cur_order - 1) : $cur_order + 1;
                    }
                    $upd = $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?");
                    $upd->execute([$n_order, $id]);
                    $upd->execute([$cur_order, (int)$neighbor['id']]);
                    $msg = 'Urutan menu berhasil diperbarui.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT title, location FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            $del = $pdo->prepare("DELETE FROM menus WHERE id = ?");
            $del->execute([$id]);
            $deleted_title = $row['title'] ?? "ID #$id";
            log_audit($pdo, 'Hapus Menu Navigasi', "Menghapus item menu: $deleted_title");
            $msg = "Menu \"$deleted_title\" berhasil dihapus.";
        }
    } elseif ($action === 'reset_default') {
        $pdo->beginTransaction();
        try {
            $pdo->exec("DELETE FROM menus");
            $stmt = $pdo->prepare("INSERT INTO menus (id, title, url, location, parent_id, sort_order, target, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach (get_default_navigation_menus() as $m) {
                $stmt->execute([$m['id'], $m['title'], $m['url'], $m['location'], $m['parent_id'], $m['sort_order'], $m['target'], $m['is_active']]);
            }
            $pdo->commit();
            log_audit($pdo, 'Reset Navigasi Default', 'Mengembalikan seluruh menu Header & Footer ke konfigurasi standar awal');
            $msg = 'Seluruh navigasi Header (7 menu) dan Footer (5 menu) berhasil dikembalikan ke pengaturan awal.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = 'Gagal mereset navigasi: ' . $e->getMessage();
        }
    }

    // Muat ulang daftar halaman setelah update
    $pages_by_slug = [];
    $all_pages = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id ASC")->fetchAll();
    foreach ($all_pages as $pg) {
        $pages_by_slug[$pg['slug']] = $pg;
    }
}

// Cek apakah sedang membuka mode edit 1 item beserta konten halamannya
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_item = null;
$edit_linked_page = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_item = $stmt->fetch();
    if ($edit_item) {
        $linked_slug = resolve_url_to_page_slug($edit_item['url']);
        if ($linked_slug !== '') {
            $edit_linked_page = get_page_by_slug($pdo, $linked_slug);
        }
    }
}

$header_menus = get_navigation_menus($pdo, 'header', false);
$footer_menus = get_navigation_menus($pdo, 'footer', false);
$active_header_count = count(array_filter($header_menus, fn($m) => (int)$m['is_active'] === 1));
$active_footer_count = count(array_filter($footer_menus, fn($m) => (int)$m['is_active'] === 1));
?>

<!-- Header Halaman Pengaturan Navigasi & Isi Halaman -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-blue-600 mb-1">
            <span>🧭 CMS Navigation & Page Content Manager</span>
            <span>·</span>
            <span>Edit Menu, Slug, Posisi & Isi Halaman</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Pengaturan Navigasi (Header & Footer) & Isi Konten Halaman</h1>
        <p class="text-xs text-slate-500 mt-1">
            Setiap halaman di Navigasi Header (Atas) dan Navigasi Footer (Bawah) dapat ditambah serta diedit langsung <strong>isi konten halamannya</strong> (Judul H1, Ringkasan Hero, dan Isi Konten Lengkap), bukan hanya nama menu, slug, dan urutannya saja.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="#formTambahMenu" onclick="document.getElementById('formTambahMenu').classList.remove('hidden');" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5">
            <span>+ Tambah Menu & Halaman Baru</span>
        </a>
        <a href="halaman.php" class="px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 transition">
            📄 Semua Halaman (<?= count($pages_by_slug) ?>)
        </a>
        <form method="POST" onsubmit="return confirm('Kembalikan seluruh menu Header dan Footer ke data awal standar PT. KGS?');" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reset_default">
            <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">
            <button type="submit" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                Pulihkan Data Awal
            </button>
        </form>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-4 rounded-xl font-semibold flex items-center justify-between">
        <span>✅ <?= e($msg) ?></span>
        <a href="../index.php" target="_blank" class="text-emerald-700 underline hover:text-emerald-900 font-bold">Lihat Hasil di Website &nearr;</a>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-4 rounded-xl font-semibold">
        ⚠️ <?= e($error_msg) ?>
    </div>
<?php endif; ?>

<!-- Statistik & Tab Filter Lokasi Navigasi -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="inline-flex bg-white p-1.5 rounded-xl border border-slate-200 shadow-xs text-xs font-bold">
        <a href="menu.php?tab=all" class="px-4 py-2 rounded-lg transition <?= $active_tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">
            Semua Bagian (<?= count($header_menus) + count($footer_menus) ?>)
        </a>
        <a href="menu.php?tab=header" class="px-4 py-2 rounded-lg transition <?= $active_tab === 'header' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">
            A. Navigasi Header (Atas) (<?= $active_header_count ?>/<?= count($header_menus) ?> Aktif)
        </a>
        <a href="menu.php?tab=footer" class="px-4 py-2 rounded-lg transition <?= $active_tab === 'footer' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">
            B. Navigasi Footer (Bawah) (<?= $active_footer_count ?>/<?= count($footer_menus) ?> Aktif)
        </a>
    </div>

    <div class="text-xs text-slate-500">
        Klik tombol <strong>📝 Edit Menu & Isi Halaman</strong> pada baris mana pun untuk mengedit konten lengkap halaman tersebut.
    </div>
</div>

<!-- Modal / Panel Edit Menu + Isi Konten Halaman Lengkap -->
<?php if ($edit_item): ?>
<div class="bg-amber-50/90 border-2 border-amber-300 rounded-2xl p-6 shadow-sm space-y-5 text-xs">
    <div class="flex items-center justify-between border-b border-amber-200 pb-3">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 block">Editor Terpadu: Navigasi & Konten Halaman</span>
            <h2 class="text-base font-bold text-slate-900">Edit Menu & Isi Halaman: <?= e($edit_item['title']) ?> (<?= e($edit_item['url']) ?>)</h2>
        </div>
        <a href="menu.php?tab=<?= e($active_tab) ?>" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg border border-slate-300 font-semibold">
            ✕ Tutup Editor
        </a>
    </div>
    <form method="POST" action="menu.php?tab=<?= e($active_tab) ?>" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="edit_single">
        <input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>">
        <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">

        <!-- Bagian 1: Pengaturan Menu, Posisi & Target URL -->
        <div class="bg-white p-4 rounded-xl border border-amber-200 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-700">1. Pengaturan Posisi, Label Menu & Target URL</div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Lokasi Navigasi *</label>
                    <select name="location" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-semibold">
                        <option value="header" <?= $edit_item['location'] === 'header' ? 'selected' : '' ?>>Header (Atas)</option>
                        <option value="footer" <?= $edit_item['location'] === 'footer' ? 'selected' : '' ?>>Footer (Bawah)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Urutan Tampil *</label>
                    <input type="number" name="sort_order" value="<?= (int)$edit_item['sort_order'] ?>" required min="1" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono">
                </div>

                <div class="lg:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Label Menu *</label>
                    <input type="text" name="title" value="<?= e($edit_item['title']) ?>" required class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target URL *</label>
                    <input type="text" name="url" value="<?= e($edit_item['url']) ?>" required class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Status & Target Tab</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <select name="is_active" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-semibold">
                            <option value="1" <?= (int)$edit_item['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (int)$edit_item['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <select name="target" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                            <option value="_self" <?= ($edit_item['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>_self</option>
                            <option value="_blank" <?= ($edit_item['target'] ?? '_self') === '_blank' ? 'selected' : '' ?>>_blank</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Edit Isi Konten Halaman Website -->
        <?php if ($edit_linked_page): ?>
        <div class="bg-white p-5 rounded-xl border border-amber-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">2. Isi Konten Halaman Website (Slug: <?= e($edit_linked_page['slug']) ?>)</span>
                    <p class="text-[11px] text-slate-500">Perubahan judul, subjudul hero, dan isi konten di bawah ini langsung mengubah tampilan di halaman <strong><?= e($edit_item['url']) ?></strong>.</p>
                </div>
                <a href="../<?= e($edit_item['url']) ?>" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline">Buka Halaman Publik &nearr;</a>
            </div>

            <input type="hidden" name="page_slug" value="<?= e($edit_linked_page['slug']) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Judul Utama Halaman (Hero Title / H1) *</label>
                    <input type="text" name="page_title" value="<?= e($edit_linked_page['title']) ?>" required class="w-full border border-slate-300 rounded-lg p-2.5 font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Title SEO</label>
                    <input type="text" name="page_meta_title" value="<?= e($edit_linked_page['meta_title'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg p-2.5">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Ringkasan / Subjudul Halaman (Tampil di Bagian Atas / Hero Halaman) *</label>
                <textarea name="page_summary" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e($edit_linked_page['summary']) ?></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Isi Konten Lengkap Halaman (Format HTML / Paragraf) *</label>
                <textarea name="page_content" rows="10" required class="w-full border border-slate-300 rounded-lg p-3 font-mono text-xs leading-relaxed"><?= e($edit_linked_page['content']) ?></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan tag HTML seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt; untuk memformat isi halaman.</p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Meta Description SEO</label>
                <input type="text" name="page_meta_description" value="<?= e($edit_linked_page['meta_description'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
        </div>
        <?php endif; ?>

        <div class="flex items-center justify-end gap-3">
            <a href="menu.php?tab=<?= e($active_tab) ?>" class="px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-300 font-semibold">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition">
                Simpan Menu & Isi Halaman
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Form Tambah Menu & Halaman Baru -->
<div id="formTambahMenu" class="<?= (!empty($error_msg) && ($_POST['action'] ?? '') === 'add') ? '' : 'hidden' ?> bg-white rounded-2xl border-2 border-blue-200 p-6 shadow-xs space-y-4 text-xs">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Tambah Navigasi & Halaman Website Baru</span>
            <h2 class="text-sm font-bold text-slate-900">Buat Menu Baru Beserta Isi Konten Halamannya</h2>
        </div>
        <button type="button" onclick="document.getElementById('formTambahMenu').classList.add('hidden');" class="text-slate-400 hover:text-slate-700 font-bold text-sm">✕</button>
    </div>

    <form method="POST" action="menu.php?tab=<?= e($active_tab) ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Lokasi Menu *</label>
                <select name="location" class="w-full border border-slate-300 rounded-lg p-2.5 bg-slate-50 font-semibold">
                    <option value="header" <?= $active_tab === 'footer' ? '' : 'selected' ?>>Header (Atas)</option>
                    <option value="footer" <?= $active_tab === 'footer' ? 'selected' : '' ?>>Footer (Bawah)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Urutan Tampil *</label>
                <input type="number" name="sort_order" value="<?= $active_tab === 'footer' ? count($footer_menus) + 1 : count($header_menus) + 1 ?>" required min="1" class="w-full border border-slate-300 rounded-lg p-2.5 font-mono">
            </div>

            <div class="lg:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Label Menu *</label>
                <input type="text" name="title" required placeholder="Contoh: Karir / Kemitraan" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Target URL / Slug</label>
                <input type="text" name="url" id="addMenuUrlInput" placeholder="Otomatis jika buat halaman baru" class="w-full border border-slate-300 rounded-lg p-2.5 font-mono">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Status *</label>
                <select name="is_active" class="w-full border border-slate-300 rounded-lg p-2.5 bg-slate-50 font-semibold">
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- Isi Konten Halaman untuk Menu Baru -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Isi Konten Halaman Website (Untuk Halaman Baru / Halaman Terkait)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Judul Utama Halaman (H1)</label>
                    <input type="text" name="page_title" placeholder="Kosongkan jika sama dengan Label Menu" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Slug Halaman Baru</label>
                    <input type="text" name="page_slug" placeholder="Contoh: karir-kemitraan (otomatis menghasilkan page.php?slug=...)" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Ringkasan / Subjudul Halaman (Hero Subtitle)</label>
                <textarea name="page_summary" rows="2" placeholder="Ringkasan singkat yang tampil di bagian atas halaman..." class="w-full border border-slate-300 rounded-lg p-2 bg-white"></textarea>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Isi Konten Lengkap Halaman (Format HTML / Paragraf)</label>
                <textarea name="page_content" rows="6" placeholder="<h2>Judul Sub-Bagian</h2><p>Tuliskan isi konten halaman secara lengkap...</p>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono"></textarea>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] text-slate-500 font-semibold">Pilih Cepat URL Halaman:</span>
                <?php
                $quick_urls = [
                    'Beranda' => 'index.php',
                    'Tentang Kami' => 'tentang.php',
                    'Layanan' => 'layanan.php',
                    'Harga' => 'harga.php',
                    'FAQ' => 'faq.php',
                    'Portofolio' => 'portofolio.php',
                    'Kontak' => 'kontak.php',
                    'Artikel Edukasi' => 'blog.php',
                    'Kebijakan Privasi' => 'kebijakan-privasi.php',
                ];
                foreach ($quick_urls as $q_label => $q_url): ?>
                    <button type="button" onclick="document.getElementById('addMenuUrlInput').value='<?= e($q_url) ?>';" class="px-2 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-600 rounded text-[10px] font-mono border border-slate-200 transition">
                        <?= e($q_label) ?> (<?= e($q_url) ?>)
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <input type="hidden" name="target" value="_self">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition">
                    + Simpan Menu & Halaman
                </button>
            </div>
        </div>
    </form>
</div>

<?php
/**
 * Fungsi render tabel navigasi untuk masing-masing lokasi (Header / Footer) beserta preview & tombol edit isi halaman
 */
function render_navigation_section_table(string $section_title, string $section_subtitle, string $loc_key, array $menus_data, string $active_tab, array $pages_by_slug): void {
    $active_total = count(array_filter($menus_data, fn($i) => (int)$i['is_active'] === 1));
    ?>
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full <?= $loc_key === 'header' ? 'bg-blue-600' : 'bg-indigo-600' ?>"></span>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider"><?= e($section_title) ?></h2>
                    <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-blue-100 text-blue-800">
                        <?= $active_total ?> Aktif dari <?= count($menus_data) ?> Menu
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1"><?= e($section_subtitle) ?></p>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" form="bulkForm_<?= e($loc_key) ?>" class="px-4 py-2 bg-slate-900 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                    💾 Simpan Perubahan <?= $loc_key === 'header' ? 'Header' : 'Footer' ?>
                </button>
            </div>
        </div>

        <form id="bulkForm_<?= e($loc_key) ?>" method="POST" action="menu.php?tab=<?= e($active_tab) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="bulk_save">
            <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/80 uppercase text-[10px] text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-3.5 w-24 text-center">Urutan</th>
                        <th class="p-3.5 w-48">Label Menu</th>
                        <th class="p-3.5 w-56">Target URL</th>
                        <th class="p-3.5">Isi Konten Halaman Terkait (Judul & Ringkasan)</th>
                        <th class="p-3.5 w-28 text-center">Status</th>
                        <th class="p-3.5 w-64 text-right">Aksi (Menu & Isi Halaman)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($menus_data)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada item navigasi pada bagian ini. Klik tombol <strong>+ Tambah Menu & Halaman Baru</strong> di atas.
                            </td>
                        </tr>
                    <?php else:
                        $total_rows = count($menus_data);
                        foreach ($menus_data as $idx => $m):
                            $id = (int)$m['id'];
                            $is_active = (int)$m['is_active'] === 1;
                            $form_id = 'bulkForm_' . $loc_key;
                            $linked_slug = resolve_url_to_page_slug($m['url']);
                            $linked_page = ($linked_slug !== '' && isset($pages_by_slug[$linked_slug])) ? $pages_by_slug[$linked_slug] : null;
                    ?>
                        <tr class="hover:bg-slate-50/90 transition <?= !$is_active ? 'bg-slate-50/50 opacity-75' : '' ?>">
                            <!-- Kolom 1: Urutan -->
                            <td class="p-3.5 text-center">
                                <input
                                    type="number"
                                    form="<?= e($form_id) ?>"
                                    name="items[<?= $id ?>][sort_order]"
                                    value="<?= (int)$m['sort_order'] ?>"
                                    min="1"
                                    class="w-16 text-center font-mono font-bold text-slate-900 border border-slate-300 rounded-lg py-1.5 px-2 bg-white focus:border-blue-600 focus:outline-none"
                                >
                            </td>

                            <!-- Kolom 2: Label Menu -->
                            <td class="p-3.5">
                                <input
                                    type="text"
                                    form="<?= e($form_id) ?>"
                                    name="items[<?= $id ?>][title]"
                                    value="<?= e($m['title']) ?>"
                                    required
                                    class="w-full font-bold text-slate-900 border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white focus:border-blue-600 focus:outline-none"
                                >
                            </td>

                            <!-- Kolom 3: Target URL -->
                            <td class="p-3.5">
                                <input
                                    type="text"
                                    form="<?= e($form_id) ?>"
                                    name="items[<?= $id ?>][url]"
                                    value="<?= e($m['url']) ?>"
                                    required
                                    class="w-full font-mono text-[11px] text-blue-700 border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white focus:border-blue-600 focus:outline-none"
                                >
                                <input type="hidden" form="<?= e($form_id) ?>" name="items[<?= $id ?>][target]" value="<?= e($m['target'] ?? '_self') ?>">
                            </td>

                            <!-- Kolom 4: Ringkasan Isi Halaman Website -->
                            <td class="p-3.5">
                                <?php if ($linked_page): ?>
                                    <div class="font-bold text-slate-800 line-clamp-1"><?= e($linked_page['title']) ?></div>
                                    <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5"><?= e($linked_page['summary']) ?></div>
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-400 italic">Klik "Edit Isi Halaman" untuk mengatur konten</span>
                                <?php endif; ?>
                            </td>

                            <!-- Kolom 5: Status (Aktif / Nonaktif) -->
                            <td class="p-3.5 text-center">
                                <select
                                    form="<?= e($form_id) ?>"
                                    name="items[<?= $id ?>][is_active]"
                                    class="text-[11px] font-bold rounded-lg px-2.5 py-1.5 border <?= $is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' ?>"
                                >
                                    <option value="1" <?= $is_active ? 'selected' : '' ?>>● Aktif</option>
                                    <option value="0" <?= !$is_active ? 'selected' : '' ?>>○ Nonaktif</option>
                                </select>
                            </td>

                            <!-- Kolom 6: Aksi -->
                            <td class="p-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <!-- Tombol Naik -->
                                    <form method="POST" action="menu.php?tab=<?= e($active_tab) ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="move_order">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">
                                        <button type="submit" title="Geser Naik" <?= $idx === 0 ? 'disabled' : '' ?> class="px-2 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 disabled:opacity-30 cursor-pointer">
                                            &uarr;
                                        </button>
                                    </form>

                                    <!-- Tombol Turun -->
                                    <form method="POST" action="menu.php?tab=<?= e($active_tab) ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="move_order">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">
                                        <button type="submit" title="Geser Turun" <?= $idx === $total_rows - 1 ? 'disabled' : '' ?> class="px-2 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 disabled:opacity-30 cursor-pointer">
                                            &darr;
                                        </button>
                                    </form>

                                    <!-- Tombol Edit Menu & Isi Halaman Lengkap -->
                                    <a href="menu.php?tab=<?= e($active_tab) ?>&edit=<?= $id ?>" class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] shadow-2xs">
                                        📝 Edit Isi Halaman
                                    </a>

                                    <!-- Tombol Hapus dengan Konfirmasi -->
                                    <form method="POST" action="menu.php?tab=<?= e($active_tab) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu \'<?= e(addslashes($m['title'])) ?>\' dari navigasi <?= $loc_key === 'header' ? 'Header' : 'Footer' ?>?');" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <input type="hidden" name="current_tab" value="<?= e($active_tab) ?>">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] cursor-pointer">
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

        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <span class="text-slate-500">
                Ubah urutan, label, atau URL langsung pada tabel, atau klik <strong>📝 Edit Isi Halaman</strong> untuk mengubah judul & isi konten halaman.
            </span>
            <button type="submit" form="bulkForm_<?= e($loc_key) ?>" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
                Simpan Perubahan <?= $loc_key === 'header' ? 'Header (Atas)' : 'Footer (Bawah)' ?>
            </button>
        </div>
    </div>
    <?php
}
?>

<!-- Bagian Tabel Navigasi Header & Footer -->
<div class="space-y-8">
    <?php if ($active_tab === 'all' || $active_tab === 'header'): ?>
        <?php render_navigation_section_table(
            'A. Navigasi Header (Atas)',
            'Ditampilkan pada bilah navigasi utama bagian atas (Desktop & Mobile Drawer) di seluruh halaman publik.',
            'header',
            $header_menus,
            $active_tab,
            $pages_by_slug
        ); ?>
    <?php endif; ?>

    <?php if ($active_tab === 'all' || $active_tab === 'footer'): ?>
        <?php render_navigation_section_table(
            'B. Navigasi Footer (Bawah)',
            'Ditampilkan pada kolom tautan informasi & legalitas di bagian bawah (Footer) di seluruh halaman publik.',
            'footer',
            $footer_menus,
            $active_tab,
            $pages_by_slug
        ); ?>
    <?php endif; ?>
</div>

<!-- E. Pratinjau Langsung Navigasi Header & Footer -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Pratinjau Langsung (Live Preview)</span>
            <h3 class="text-base font-bold text-slate-900">Simulasi Tampilan Navigasi Header & Footer</h3>
            <p class="text-xs text-slate-500">Membedakan tampilan menu berstatus <strong>Aktif</strong> (ditampilkan ke pengunjung) dan <strong>Nonaktif</strong> (disembunyikan dari pengunjung).</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Menu Aktif (Tampil)
            </span>
            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Menu Nonaktif (Tersembunyi)
            </span>
        </div>
    </div>

    <!-- Preview Header -->
    <div class="space-y-2">
        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">1. Pratinjau Navigasi Header (Atas)</div>
        <div class="bg-slate-900 text-white p-4 rounded-xl flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-black text-xs">KGS</div>
                <div>
                    <div class="text-xs font-bold leading-none">PT. KREATIF GLOBAL SOLUSINDO</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Header Navigation Bar</div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <?php foreach ($header_menus as $hm):
                    $hm_active = (int)$hm['is_active'] === 1;
                ?>
                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1.5 <?= $hm_active ? 'bg-slate-800 text-white border border-slate-700' : 'bg-slate-950/60 text-slate-500 line-through border border-dashed border-slate-800' ?>">
                        <span class="text-[10px] font-mono text-blue-400">#<?= (int)$hm['sort_order'] ?></span>
                        <span><?= e($hm['title']) ?></span>
                        <span class="text-[10px] font-mono text-slate-400">(<?= e($hm['url']) ?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Preview Footer -->
    <div class="space-y-2">
        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">2. Pratinjau Navigasi Footer (Bawah)</div>
        <div class="bg-slate-950 text-slate-300 p-5 rounded-xl border border-slate-800">
            <div class="text-xs font-bold text-white uppercase tracking-wider mb-3">Tautan Informasi & Legalitas (Footer)</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
                <?php foreach ($footer_menus as $fm):
                    $fm_active = (int)$fm['is_active'] === 1;
                ?>
                    <div class="p-2.5 rounded-lg border text-xs flex flex-col justify-between <?= $fm_active ? 'bg-slate-900 border-slate-800 text-slate-200' : 'bg-slate-950 border-dashed border-slate-800/70 text-slate-600 line-through' ?>">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="font-bold"><?= e($fm['title']) ?></span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded <?= $fm_active ? 'bg-emerald-950 text-emerald-400' : 'bg-slate-900 text-slate-500' ?>">
                                <?= $fm_active ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </div>
                        <div class="text-[10px] font-mono text-slate-400 truncate">
                            Urutan #<?= (int)$fm['sort_order'] ?> &middot; <?= e($fm['url']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
