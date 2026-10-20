<?php
$admin_page_title = 'Kelola Halaman Website & Konten';
require_once __DIR__ . '/layout-header.php';

ensure_pages_complete($pdo);
ensure_menus_table($pdo);

$msg = '';
$error_msg = '';
$action = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);
$edit_slug = trim($_GET['slug'] ?? '');

// Proses tambah, update, atau hapus halaman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $post_action = $_POST['post_action'] ?? 'save';

    if ($post_action === 'delete') {
        $del_id = (int)($_POST['page_id'] ?? 0);
        if ($del_id > 0) {
            $stmt = $pdo->prepare("SELECT title, slug FROM pages WHERE id = ?");
            $stmt->execute([$del_id]);
            $row = $stmt->fetch();
            if ($row) {
                $del = $pdo->prepare("DELETE FROM pages WHERE id = ?");
                $del->execute([$del_id]);
                log_audit($pdo, 'Hapus Halaman', "Menghapus halaman: {$row['title']} ({$row['slug']})");
                $msg = "Halaman \"{$row['title']}\" berhasil dihapus.";
            }
        }
    } elseif ($post_action === 'add') {
        $title = clean_plain_text(strip_tags($_POST['title'] ?? ''), 200);
        $raw_slug = trim($_POST['slug'] ?? '');
        $slug = create_slug($raw_slug !== '' ? $raw_slug : $title);
        $summary = clean_plain_text(strip_tags($_POST['summary'] ?? ''), 1000);
        $content = sanitize_rich_html($_POST['content'] ?? '');
        $meta_title = clean_plain_text(strip_tags($_POST['meta_title'] ?? ''), 255);
        $meta_description = clean_plain_text(strip_tags($_POST['meta_description'] ?? ''), 500);
        $sort_order = (int)($_POST['sort_order'] ?? 12);
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $nav_location = $_POST['nav_location'] ?? 'none';
        $nav_label = clean_plain_text(strip_tags($_POST['nav_label'] ?? ''), 100) ?: $title;
        $nav_order = (int)($_POST['nav_order'] ?? 8);

        if ($title === '' || $content === '') {
            $error_msg = 'Judul Halaman dan Isi Konten wajib diisi.';
            $action = 'add';
        } else {
            try {
                $stmt_ins = $pdo->prepare("INSERT INTO pages (title, slug, summary, content, meta_title, meta_description, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), content = VALUES(content), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description), is_published = VALUES(is_published), sort_order = VALUES(sort_order)");
                $stmt_ins->execute([
                    $title,
                    $slug,
                    $summary,
                    $content,
                    $meta_title ?: ($title . ' - PT. KGS'),
                    $meta_description ?: $summary,
                    $is_published,
                    $sort_order
                ]);

                if (in_array($nav_location, ['header', 'footer'], true)) {
                    $target_url = resolve_page_slug_to_url($slug);
                    $stmt_nav = $pdo->prepare("INSERT INTO menus (title, url, location, sort_order, target, is_active) VALUES (?, ?, ?, ?, '_self', 1)");
                    $stmt_nav->execute([$nav_label, $target_url, $nav_location, $nav_order]);
                    invalidate_live_preview_json('menus');
                }

                invalidate_live_preview_json('pages', $slug);
                log_audit($pdo, 'Tambah Halaman', "Menambahkan halaman baru: $title ($slug)");
                $msg = "Halaman \"$title\" berhasil ditambahkan" . (in_array($nav_location, ['header', 'footer'], true) ? " dan ditautkan ke Navigasi " . ucfirst($nav_location) : "") . "!";
                $action = 'list';
            } catch (Exception $e) {
                $error_msg = 'Gagal menyimpan halaman baru: ' . $e->getMessage();
                $action = 'add';
            }
        }
    } else {
        $page_id = (int)($_POST['page_id'] ?? 0);
        $title = clean_plain_text(strip_tags($_POST['title'] ?? ''), 200);
        $raw_slug = trim($_POST['slug'] ?? '');
        $slug = create_slug($raw_slug !== '' ? $raw_slug : $title);
        $summary = clean_plain_text(strip_tags($_POST['summary'] ?? ''), 1000);
        $content = sanitize_rich_html($_POST['content'] ?? '');
        $meta_title = clean_plain_text(strip_tags($_POST['meta_title'] ?? ''), 255);
        $meta_description = clean_plain_text(strip_tags($_POST['meta_description'] ?? ''), 500);
        $sort_order = (int)($_POST['sort_order'] ?? 1);
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        if ($page_id > 0 && $title !== '' && $content !== '') {
            $stmt_update = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, summary = ?, content = ?, meta_title = ?, meta_description = ?, is_published = ?, sort_order = ?, updated_at = NOW() WHERE id = ?");
            $stmt_update->execute([$title, $slug, $summary, $content, $meta_title ?: $title, $meta_description ?: $summary, $is_published, $sort_order, $page_id]);
            invalidate_live_preview_json('pages', $slug);
            log_audit($pdo, 'Edit Halaman', "Memperbarui konten halaman ID #$page_id ($slug): $title");
            $msg = "Konten halaman \"$title\" berhasil diperbarui dan langsung aktif di website!";
            $action = 'list';
        } else {
            $error_msg = 'Judul Halaman dan Isi Konten Halaman wajib diisi.';
        }
    }
}

// Mode Edit (berdasarkan ID atau Slug)
$page_to_edit = null;
if ($action === 'edit') {
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$edit_id]);
        $page_to_edit = $stmt->fetch();
    } elseif ($edit_slug !== '') {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
        $stmt->execute([$edit_slug]);
        $page_to_edit = $stmt->fetch();
    }
}

$pages = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id ASC")->fetchAll();
$core_slugs = ['beranda', 'tentang-kami', 'layanan', 'harga', 'faq', 'portofolio', 'kontak', 'profil-perusahaan', 'visi-misi-budaya', 'blog', 'kebijakan-privasi'];
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-blue-600 mb-1">
            <span>📄 CMS Page & Content Manager</span>
            <span>·</span>
            <span>Seluruh Halaman Header & Footer</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Halaman & Isi Konten Website</h1>
        <p class="text-xs text-slate-500 mt-1">
            Tambah halaman baru atau edit judul utama, ringkasan subjudul, dan isi konten lengkap untuk setiap halaman di Navigasi Header maupun Footer.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="halaman.php?action=add" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
            + Tambah Halaman Baru
        </a>
        <a href="menu.php" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
            🧭 Pengaturan Navigasi
        </a>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-4 rounded-xl font-semibold flex items-center justify-between">
        <span>✅ <?= e($msg) ?></span>
        <a href="../index.php" target="_blank" class="text-emerald-700 underline font-bold">Lihat Website &nearr;</a>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-4 rounded-xl font-semibold">
        ⚠️ <?= e($error_msg) ?>
    </div>
<?php endif; ?>

<?php if ($action === 'add'): ?>
<!-- Form Tambah Halaman Baru -->
<div class="bg-white rounded-2xl border-2 border-blue-200 p-6 shadow-sm space-y-5 text-xs">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Buat Halaman Baru</span>
            <h2 class="text-base font-bold text-slate-900">Tambah Halaman Website & Isi Konten</h2>
        </div>
        <a href="halaman.php" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold">&larr; Batal</a>
    </div>

    <form method="POST" action="halaman.php" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="post_action" value="add">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Judul Utama Halaman (H1) *</label>
                <input type="text" name="title" required placeholder="Contoh: Standar K3 & Keselamatan Kerja" class="w-full border border-slate-300 rounded-lg p-2.5 font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Slug URL (Kosongkan untuk otomatis)</label>
                <input type="text" name="slug" placeholder="standar-k3-keselamatan" class="w-full border border-slate-300 rounded-lg p-2.5 font-mono">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Ringkasan / Subjudul Hero Halaman *</label>
            <textarea name="summary" rows="2" required placeholder="Deskripsi singkat yang tampil di bagian atas (Hero) halaman..." class="w-full border border-slate-300 rounded-lg p-2.5"></textarea>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Isi Konten Lengkap Halaman (Format HTML / Paragraf) *</label>
            <textarea name="content" rows="10" required placeholder="<h2>Judul Bagian</h2><p>Tuliskan isi konten lengkap halaman di sini...</p>" class="w-full border border-slate-300 rounded-lg p-3 font-mono text-xs leading-relaxed"></textarea>
            <p class="text-[11px] text-slate-400 mt-1">Mendukung tag HTML seperti &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-blue-50/60 p-4 rounded-xl border border-blue-200">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tampilkan di Menu Navigasi?</label>
                <select name="nav_location" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-semibold">
                    <option value="none">Jangan Tambahkan Otomatis</option>
                    <option value="header">Tambahkan ke Navigasi Header (Atas)</option>
                    <option value="footer" selected>Tambahkan ke Navigasi Footer (Bawah)</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Label Menu di Navigasi</label>
                <input type="text" name="nav_label" placeholder="Kosongkan jika sama dengan Judul" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Urutan Posisi Menu</label>
                <input type="number" name="nav_order" value="6" min="1" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom Meta Title (SEO)</label>
                <input type="text" name="meta_title" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom Meta Description (SEO)</label>
                <input type="text" name="meta_description" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Urutan Daftar Halaman</label>
                <input type="number" name="sort_order" value="<?= count($pages) + 1 ?>" min="1" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" checked class="rounded text-blue-600">
                <span class="font-semibold text-slate-800">Status Aktif / Ditampilkan ke Publik</span>
            </label>

            <div class="flex gap-3">
                <a href="halaman.php" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm">
                    + Simpan & Terbitkan Halaman
                </button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($page_to_edit):
    $public_url = resolve_page_slug_to_url($page_to_edit['slug']);
?>
<!-- Form Edit Halaman -->
<div class="bg-white rounded-2xl border-2 border-amber-300 p-6 shadow-sm space-y-5 text-xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Editor Konten Halaman Website</span>
            <h2 class="text-base font-bold text-slate-900">Edit Isi Halaman: <?= e($page_to_edit['title']) ?></h2>
            <p class="text-[11px] text-slate-500">URL Halaman: <code class="text-blue-600 font-mono"><?= e($public_url) ?></code></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="../<?= e($public_url) ?>" target="_blank" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-semibold">
                Lihat Halaman &nearr;
            </a>
            <a href="halaman.php" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold">&larr; Kembali</a>
        </div>
    </div>

    <form method="POST" action="halaman.php" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="post_action" value="save">
        <input type="hidden" name="page_id" value="<?= (int)$page_to_edit['id'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Judul Utama Halaman (Tampil di Header/Hero Halaman) *</label>
                <input type="text" name="title" value="<?= e($page_to_edit['title']) ?>" required class="w-full border border-slate-300 rounded-lg p-2.5 font-bold">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Slug Halaman *</label>
                <input type="text" name="slug" value="<?= e($page_to_edit['slug']) ?>" <?= in_array($page_to_edit['slug'], $core_slugs, true) ? 'readonly class="w-full border border-slate-200 rounded-lg p-2.5 bg-slate-100 text-slate-500 font-mono"' : 'required class="w-full border border-slate-300 rounded-lg p-2.5 font-mono"' ?>>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Ringkasan / Subjudul Halaman (Tampil di Bawah Judul Utama) *</label>
            <textarea name="summary" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e($page_to_edit['summary']) ?></textarea>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Isi Konten Lengkap Halaman (Format HTML / Teks) *</label>
            <textarea name="content" rows="12" required class="w-full border border-slate-300 rounded-lg p-3 font-mono text-xs leading-relaxed"><?= e($page_to_edit['content']) ?></textarea>
            <p class="text-[11px] text-slate-400 mt-1">Gunakan tag HTML standar seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;. Perubahan langsung tampil di halaman website.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom Meta Title (SEO)</label>
                <input type="text" name="meta_title" value="<?= e($page_to_edit['meta_title']) ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom Meta Description (SEO)</label>
                <input type="text" name="meta_description" value="<?= e($page_to_edit['meta_description']) ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Urutan Halaman</label>
                <input type="number" name="sort_order" value="<?= (int)$page_to_edit['sort_order'] ?>" min="1" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" <?= $page_to_edit['is_published'] ? 'checked' : '' ?> class="rounded text-blue-600">
                <span class="font-semibold text-slate-800">Status Aktif / Ditampilkan ke Publik</span>
            </label>

            <div class="flex gap-3">
                <a href="halaman.php" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm">
                    Simpan Perubahan Konten
                </button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Daftar Seluruh Halaman Website -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Daftar Seluruh Halaman Website (<?= count($pages) ?> Halaman)</h3>
            <p class="text-[11px] text-slate-500">Mencakup seluruh halaman pada Navigasi Header (Atas), Navigasi Footer (Bawah), serta halaman tambahan.</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 uppercase text-[10px] text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-3.5 w-14 text-center">Urutan</th>
                    <th class="p-3.5">Judul Halaman & Target URL</th>
                    <th class="p-3.5">Ringkasan & Cuplikan Isi Konten</th>
                    <th class="p-3.5 text-center">Status</th>
                    <th class="p-3.5 text-center">Diperbarui</th>
                    <th class="p-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($pages as $p):
                    $pub_url = resolve_page_slug_to_url($p['slug']);
                    $is_core = in_array($p['slug'], $core_slugs, true);
                ?>
                    <tr class="hover:bg-slate-50/80">
                        <td class="p-3.5 text-center font-mono font-bold text-slate-500">
                            <?= (int)$p['sort_order'] ?>
                        </td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900"><?= e($p['title']) ?></div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono text-[11px] text-blue-600"><?= e($pub_url) ?></span>
                                <span class="text-[10px] text-slate-400">· slug: <?= e($p['slug']) ?></span>
                            </div>
                        </td>
                        <td class="p-3.5 text-slate-600 max-w-md">
                            <div class="font-medium text-slate-800 line-clamp-1"><?= e($p['summary']) ?></div>
                            <div class="text-[11px] text-slate-400 truncate mt-0.5"><?= e(strip_tags((string)$p['content'])) ?></div>
                        </td>
                        <td class="p-3.5 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $p['is_published'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>">
                                <?= $p['is_published'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="p-3.5 text-center text-slate-400 font-mono text-[11px]">
                            <?= !empty($p['updated_at']) ? date('d M Y, H:i', strtotime($p['updated_at'])) : '-' ?>
                        </td>
                        <td class="p-3.5 text-right whitespace-nowrap space-x-1.5">
                            <a href="../<?= e($pub_url) ?>" target="_blank" class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg border border-slate-200 text-[11px] font-semibold">
                                Lihat &nearr;
                            </a>
                            <a href="halaman.php?action=edit&id=<?= (int)$p['id'] ?>" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-[11px] shadow-2xs">
                                📝 Edit Isi Halaman
                            </a>
                            <?php if (!$is_core): ?>
                                <form method="POST" action="halaman.php" onsubmit="return confirm('Hapus halaman <?= e(addslashes($p['title'])) ?>?');" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="post_action" value="delete">
                                    <input type="hidden" name="page_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg border border-rose-200 text-[11px]">
                                        Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
