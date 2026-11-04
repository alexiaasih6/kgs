<?php
$admin_page_title = 'Kelola Berita & Artikel';
require_once __DIR__ . '/layout-header.php';

$msg = $_GET['msg'] ?? '';
$cat_error = '';

// Proses POST: Hapus Artikel atau CRUD Kategori Blog (`blog_categories`)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $action = (string)$_POST['action'];

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            invalidate_live_preview_json('posts');
            log_audit($pdo, 'Hapus Artikel', "Menghapus artikel ID: $id");
            header("Location: blog.php?msg=deleted");
            exit;
        }
    } elseif ($action === 'add_category') {
        $cat_name = clean_plain_text((string)($_POST['name'] ?? ''), 100);
        $cat_slug = slugify($cat_name);
        if ($cat_name === '') {
            $cat_error = 'Nama kategori artikel wajib diisi.';
        } else {
            $stmt_chk = $pdo->prepare("SELECT id FROM blog_categories WHERE slug = ? LIMIT 1");
            $stmt_chk->execute([$cat_slug]);
            if ($stmt_chk->fetch()) {
                $cat_slug .= '-' . time();
            }
            $pdo->prepare("INSERT INTO blog_categories (name, slug) VALUES (?, ?)")->execute([$cat_name, $cat_slug]);
            log_audit($pdo, 'Tambah Kategori Artikel', "Menambahkan kategori artikel: $cat_name");
            $msg = 'cat_added';
        }
    } elseif ($action === 'edit_category') {
        $cat_id   = (int)($_POST['id'] ?? 0);
        $cat_name = clean_plain_text((string)($_POST['name'] ?? ''), 100);
        $cat_slug = slugify($cat_name);
        if ($cat_id > 0 && $cat_name !== '') {
            $pdo->prepare("UPDATE blog_categories SET name = ?, slug = ? WHERE id = ?")->execute([$cat_name, $cat_slug, $cat_id]);
            log_audit($pdo, 'Edit Kategori Artikel', "Memperbarui kategori artikel ID $cat_id: $cat_name");
            $msg = 'cat_updated';
        }
    } elseif ($action === 'delete_category') {
        $cat_id = (int)($_POST['id'] ?? 0);
        if ($cat_id > 0) {
            $stmt_cnt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
            $stmt_cnt->execute([$cat_id]);
            $post_cnt = (int)$stmt_cnt->fetchColumn();
            if ($post_cnt > 0) {
                $cat_error = "Kategori tidak dapat dihapus karena masih digunakan oleh $post_cnt artikel. Pindahkan kategori artikel tersebut terlebih dahulu.";
            } else {
                $pdo->prepare("DELETE FROM blog_categories WHERE id = ?")->execute([$cat_id]);
                log_audit($pdo, 'Hapus Kategori Artikel', "Menghapus kategori artikel ID: $cat_id");
                $msg = 'cat_deleted';
            }
        }
    }
}

// Filter pencarian dan kategori
$search = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);

$query = "SELECT p.*, c.name as category_name FROM posts p JOIN blog_categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.title LIKE ? OR p.summary LIKE ? OR p.labels LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS post_count FROM blog_categories c ORDER BY c.name ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Berita, Artikel & Wawasan Teknis</h1>
        <p class="text-xs text-slate-500">Publikasikan edukasi teknologi dilengkapi upload gambar sampul dan label topik.</p>
    </div>
    <a href="tambah-artikel.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition">
        <span>+ Tambah Artikel Baru</span>
    </a>
</div>

<?php if ($msg === 'created'): ?>
<div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
    Artikel baru berhasil dipublikasikan!
</div>
<?php elseif ($msg === 'updated'): ?>
<div class="bg-blue-100 border border-blue-300 text-blue-800 text-xs p-3.5 rounded-xl font-medium">
    Perubahan artikel berhasil disimpan!
</div>
<?php elseif ($msg === 'deleted'): ?>
<div class="bg-amber-100 border border-amber-300 text-amber-800 text-xs p-3.5 rounded-xl font-medium">
    Artikel berhasil dihapus dari sistem.
</div>
<?php elseif ($msg === 'cat_added' || $msg === 'cat_updated' || $msg === 'cat_deleted'): ?>
<div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
    Data kategori artikel berhasil diperbarui.
</div>
<?php endif; ?>

<?php if ($cat_error !== ''): ?>
<div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
    <?= e($cat_error) ?>
</div>
<?php endif; ?>

<!-- Search and Filter Bar + Kelola Kategori Blog -->
<div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari judul artikel, ringkasan, atau label topik..." class="flex-1 text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <select name="category" class="text-xs px-3 py-2.5 border border-slate-300 rounded-lg bg-white">
            <option value="0">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?> (<?= (int)($cat['post_count'] ?? 0) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg cursor-pointer">
            Filter
        </button>
        <?php if (!empty($search) || $category_id > 0): ?>
            <a href="blog.php" class="px-3 py-2 border border-slate-300 rounded-lg text-xs text-slate-500 hover:bg-slate-50 flex items-center justify-center">Reset</a>
        <?php endif; ?>
    </form>

    <details class="border-t border-slate-100 pt-2.5 text-xs">
        <summary class="font-bold text-slate-700 cursor-pointer hover:text-blue-700 select-none">
            🗂️ Kelola Kategori Artikel Blog (<?= count($categories) ?> Kategori)
        </summary>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
            <form method="POST" class="space-y-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_category">
                <label class="block font-bold text-slate-700 text-[11px]">Tambah Kategori Artikel Baru</label>
                <div class="flex gap-2">
                    <input type="text" name="name" required placeholder="Nama Kategori..." class="flex-1 border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white text-xs">
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs cursor-pointer">+ Tambah</button>
                </div>
            </form>
            <div class="md:col-span-2 space-y-1.5 max-h-44 overflow-y-auto">
                <?php foreach ($categories as $cat): ?>
                <div class="flex items-center justify-between gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                    <form method="POST" class="flex items-center gap-2 flex-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <input type="text" name="name" value="<?= e($cat['name']) ?>" required class="flex-1 border border-slate-200 rounded px-2 py-1 text-xs font-semibold">
                        <span class="text-[10px] text-slate-400 font-mono"><?= (int)($cat['post_count'] ?? 0) ?> artikel</span>
                        <button type="submit" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-[10px] font-bold cursor-pointer">Simpan</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Hapus kategori artikel ini?');" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded text-[10px] font-bold cursor-pointer">Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </details>
</div>

<!-- Table of Articles -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 uppercase text-[10px] text-slate-500 border-b">
                <tr>
                    <th class="p-3.5">Judul, Gambar & Label</th>
                    <th class="p-3.5">Penulis</th>
                    <th class="p-3.5 text-center">Status</th>
                    <th class="p-3.5 text-center">Featured</th>
                    <th class="p-3.5 text-center">Views</th>
                    <th class="p-3.5 text-center">Tanggal</th>
                    <th class="p-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            Tidak ada artikel yang cocok dengan filter atau belum ada artikel.
                        </td>
                    </tr>
                <?php else: foreach ($posts as $post):
                    $labels_arr = array_filter(array_map('trim', explode(',', (string)($post['labels'] ?? ''))));
                ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3.5 max-w-md">
                            <div class="flex items-start gap-3">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="../<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>" class="w-14 h-10 object-cover rounded-lg border border-slate-200 shrink-0 mt-0.5">
                                <?php endif; ?>
                                <div>
                                    <a href="../detail-blog.php?slug=<?= e($post['slug']) ?>" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block leading-snug">
                                        <?= e($post['title']) ?>
                                    </a>
                                    <div class="flex flex-wrap items-center gap-1 mt-1">
                                        <span class="inline-block text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                                            <?= e($post['category_name']) ?>
                                        </span>
                                        <?php foreach ($labels_arr as $lbl): ?>
                                            <span class="inline-block text-[10px] text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                                #<?= e($lbl) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 text-slate-600 whitespace-nowrap">
                            <?= e($post['author'] ?: 'Tim Teknis PT. KGS') ?>
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $post['status'] === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>">
                                <?= $post['status'] === 'published' ? 'Terbit' : 'Draft' ?>
                            </span>
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            <?= $post['is_featured'] ? '<span class="text-amber-500 font-bold">★ Ya</span>' : '<span class="text-slate-400">-</span>' ?>
                        </td>
                        <td class="p-3.5 text-center font-mono text-slate-600 whitespace-nowrap">
                            <?= number_format((int)$post['views']) ?>
                        </td>
                        <td class="p-3.5 text-center text-slate-500 font-mono text-[11px] whitespace-nowrap">
                            <?= date('d M Y', strtotime($post['created_at'])) ?>
                        </td>
                        <td class="p-3.5 text-right space-x-1.5 whitespace-nowrap">
                            <a href="edit-artikel.php?id=<?= (int)$post['id'] ?>" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold rounded-lg text-[11px] border border-amber-200">
                                Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus artikel ini secara permanen?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-[11px] border border-rose-200 cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
