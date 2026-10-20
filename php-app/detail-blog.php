<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: blog.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.status = 'published'
    LIMIT 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Tambah jumlah tayangan secara aman menggunakan prepared statement
try {
    $updViews = $pdo->prepare("UPDATE posts SET views = COALESCE(views, 0) + 1 WHERE id = ?");
    $updViews->execute([(int)$post['id']]);
} catch (Exception $e) {}

$category_label = !empty($post['category_name']) ? $post['category_name'] : ($post['category'] ?? 'Teknologi');
$author_label   = !empty($post['author']) ? $post['author'] : ($post['author_name'] ?? 'Tim Teknis PT. KGS');
$summary_text   = !empty($post['summary']) ? $post['summary'] : ($post['excerpt'] ?? '');
$post_labels    = array_values(array_filter(array_map('trim', explode(',', (string)($post['labels'] ?? '')))));

$page_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'];
$page_desc  = !empty($post['meta_description']) ? $post['meta_description'] : ($summary_text ?: substr(strip_tags((string)$post['content']), 0, 155));

// Artikel lainnya dari tabel `posts`
$otherStmt = $pdo->prepare("
    SELECT p.id, p.title, p.slug, p.created_at, c.name AS category_name
    FROM posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE p.id != ? AND p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 4
");
$otherStmt->execute([(int)$post['id']]);
$otherPosts = $otherStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Artikel -->
<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <a href="index.php" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="blog.php" class="hover:text-white">Artikel & Insight</a>
            <span>/</span>
            <span class="text-blue-400"><?= e($category_label) ?></span>
        </div>
        <span class="px-3 py-1 bg-blue-500/20 text-blue-300 font-bold text-xs rounded-full border border-blue-500/30">
            <?= e($category_label) ?>
        </span>
        <h1 data-live-record="posts:<?= (int)$post['id'] ?>:title" class="text-2xl sm:text-4xl font-extrabold mt-4 leading-tight"><?= e($post['title']) ?></h1>
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 mt-4">
            <span>Oleh: <strong class="text-slate-200" data-live-record="posts:<?= (int)$post['id'] ?>:author"><?= e($author_label) ?></strong></span>
            <span>•</span>
            <span>Diterbitkan: <?= date('d M Y', strtotime($post['created_at'])) ?></span>
        </div>
    </div>
</section>

<!-- Isi Artikel -->
<section class="py-14 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-8 space-y-6">
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                        <img src="<?= e($post['featured_image']) ?>" alt="<?= e(!empty($post['alt_image']) ? $post['alt_image'] : $post['title']) ?>" data-live-img="post_image:<?= (int)$post['id'] ?>" class="w-full h-64 sm:h-80 object-cover">
                    </div>
                <?php endif; ?>

                <?php if (!empty($summary_text)): ?>
                    <div data-live-record="posts:<?= (int)$post['id'] ?>:summary" class="p-4 bg-blue-50/70 border-l-4 border-blue-600 rounded-r-xl text-xs sm:text-sm text-slate-700 font-medium italic leading-relaxed"><?= e($summary_text) ?></div>
                <?php endif; ?>

                <article data-live-record="posts:<?= (int)$post['id'] ?>:content" class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed space-y-4"><?= sanitize_rich_html($post['content'] ?? '') ?></article>

                <?php if (!empty($post_labels)): ?>
                    <div class="pt-4 border-t border-slate-200 flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase">Label Topik:</span>
                        <?php foreach ($post_labels as $lbl): ?>
                            <a href="blog.php?label=<?= urlencode($lbl) ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-semibold transition">#<?= e($lbl) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <?php if (!empty($otherPosts)): ?>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Artikel Terbaru Lainnya</h3>
                        <div class="space-y-3">
                            <?php foreach ($otherPosts as $op): ?>
                                <a href="detail-blog.php?slug=<?= e($op['slug']) ?>" class="block p-3 rounded-xl bg-white border border-slate-200 hover:border-blue-400 transition">
                                    <span class="text-[10px] font-bold text-blue-600 uppercase"><?= e($op['category_name'] ?: 'Teknologi') ?></span>
                                    <div class="text-xs font-bold text-slate-800 mt-0.5 line-clamp-2"><?= e($op['title']) ?></div>
                                    <div class="text-[10px] text-slate-400 mt-1"><?= date('d M Y', strtotime($op['created_at'])) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="bg-slate-900 text-white rounded-2xl p-6 space-y-3">
                    <h4 class="text-sm font-bold" data-live-setting="detail_blog_cta_title"><?= e(get_setting($pdo, 'detail_blog_cta_title', 'Butuh Implementasi Solusi Ini?')) ?></h4>
                    <p class="text-xs text-slate-300 leading-relaxed" data-live-setting="detail_blog_cta_desc"><?= e(get_setting($pdo, 'detail_blog_cta_desc', 'Hubungi tim engineer PT. KGS untuk diskusi teknis dan survei lokasi.')) ?></p>
                    <a href="kontak.php#konsultasi" class="block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition">
                        Hubungi Tim KGS
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
