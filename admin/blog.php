<?php
$admin_page_title = 'Kelola Berita & Artikel';
require_once __DIR__ . '/layout-header.php';

$msg = $_GET['msg'] ?? '';

// Hapus artikel jika ada permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, 'Hapus Artikel', "Menghapus artikel ID: $id");
        header("Location: blog.php?msg=deleted");
        exit;
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

$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();
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
<?php endif; ?>

<!-- Search and Filter Bar -->
<div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari judul artikel, ringkasan, atau label topik..." class="flex-1 text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <select name="category" class="text-xs px-3 py-2.5 border border-slate-300 rounded-lg bg-white">
            <option value="0">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
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
