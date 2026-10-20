<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
$blog_page = get_page_by_slug($pdo, 'blog');
$page_title = !empty($blog_page['meta_title']) ? $blog_page['meta_title'] : ($blog_page['title'] ?? 'Artikel & Wawasan Teknologi');
$meta_description = $blog_page['meta_description'] ?? $blog_page['summary'] ?? '';
require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$cat_slug = trim($_GET['kategori'] ?? '');
$label_filter = trim($_GET['label'] ?? '');

$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM posts p JOIN blog_categories c ON p.category_id = c.id WHERE p.status = 'published'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.summary LIKE ? OR p.content LIKE ? OR p.labels LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($cat_slug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $cat_slug;
}

if (!empty($label_filter)) {
    $sql .= " AND p.labels LIKE ?";
    $params[] = "%$label_filter%";
}

$sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();
?>

<div class="bg-gradient-to-b from-slate-900 to-blue-950 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <span class="px-3 py-1 bg-blue-500/30 text-blue-300 text-xs font-bold rounded-full border border-blue-400/30 uppercase tracking-wider" data-live-setting="blog_kicker">
                <?= e(get_setting($pdo, 'blog_kicker', 'Edukasi & Artikel Industri')) ?>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight" data-live-page-slug="blog" data-live-page-field="title"><?= e($blog_page['title'] ?: 'Wawasan Teknologi & Panduan Solusi IT') ?></h1>
            <p class="text-slate-300 text-sm leading-relaxed" data-live-page-slug="blog" data-live-page-field="summary">
                <?= e($blog_page['summary'] ?: 'Kumpulan artikel teknis, tips implementasi jaringan fiber optik, standar proteksi petir kelistrikan industri, telekomunikasi lapangan, dan solusi sistem integrator dari praktisi berpengalaman PT. KGS.') ?>
            </p>
            <?php if (!empty($blog_page['content'])): ?>
                <div class="text-xs sm:text-sm text-slate-300/90 leading-relaxed prose prose-invert" data-live-page-slug="blog" data-live-page-field="content">
                    <?= sanitize_rich_html($blog_page['content']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Categories -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="blog.php" class="px-3.5 py-2 rounded-xl font-semibold transition <?= (empty($cat_slug) && empty($label_filter)) ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' ?>">
                Semua Topik
            </a>
            <?php foreach ($categories as $c): ?>
                <a href="blog.php?kategori=<?= urlencode($c['slug']) ?>" class="px-3.5 py-2 rounded-xl font-semibold transition <?= $cat_slug === $c['slug'] ? 'bg-blue-600 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' ?>">
                    <?= e($c['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Form -->
        <form method="GET" class="w-full md:w-72 flex gap-2">
            <?php if (!empty($cat_slug)): ?>
                <input type="hidden" name="kategori" value="<?= e($cat_slug) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari judul atau label..." class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl cursor-pointer">
                Cari
            </button>
        </form>
    </div>

    <?php if (!empty($label_filter)): ?>
    <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-xs">
        <span class="text-blue-900">Menampilkan artikel dengan label: <strong>#<?= e($label_filter) ?></strong></span>
        <a href="blog.php" class="text-blue-700 font-bold hover:underline">Hapus Filter Label &times;</a>
    </div>
    <?php endif; ?>

    <!-- Articles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($posts)): ?>
            <div class="col-span-full bg-white p-12 rounded-2xl border text-center text-slate-400 text-sm">
                Belum ada artikel yang cocok dengan pencarian Anda. <br>
                <a href="blog.php" class="text-blue-600 font-semibold underline mt-2 inline-block">Reset Pencarian</a>
            </div>
        <?php else: foreach ($posts as $p):
            $post_labels = array_filter(array_map('trim', explode(',', (string)($p['labels'] ?? ''))));
        ?>
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <?php if (!empty($p['featured_image'])): ?>
                        <a href="detail-blog.php?slug=<?= e($p['slug']) ?>" class="block h-44 bg-slate-100 overflow-hidden border-b border-slate-100">
                            <img src="<?= e($p['featured_image']) ?>" alt="<?= e($p['alt_image'] ?: $p['title']) ?>" data-live-img="post_image:<?= (int)$p['id'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </a>
                    <?php endif; ?>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded">
                                <?= e($p['category_name']) ?>
                            </span>
                            <span class="text-slate-400 font-mono">
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                            </span>
                        </div>

                        <h2 class="text-base font-extrabold text-slate-900 group-hover:text-blue-600 transition leading-snug">
                            <a href="detail-blog.php?slug=<?= e($p['slug']) ?>" data-live-record="posts:<?= (int)$p['id'] ?>:title">
                                <?= e($p['title']) ?>
                            </a>
                        </h2>

                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3" data-live-record="posts:<?= (int)$p['id'] ?>:summary">
                            <?= e($p['summary']) ?>
                        </p>

                        <?php if (!empty($post_labels)): ?>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <?php foreach ($post_labels as $lbl): ?>
                                <a href="blog.php?label=<?= urlencode($lbl) ?>" class="px-2 py-0.5 bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-700 rounded text-[10px] font-medium transition">
                                    #<?= e($lbl) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 text-[11px]">
                        Oleh <strong data-live-record="posts:<?= (int)$p['id'] ?>:author"><?= e($p['author'] ?: 'Tim Teknis PT. KGS') ?></strong>
                    </span>
                    <a href="detail-blog.php?slug=<?= e($p['slug']) ?>" class="font-bold text-blue-600 hover:text-blue-800 transition">
                        Baca Selengkapnya &rarr;
                    </a>
                </div>
            </article>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
