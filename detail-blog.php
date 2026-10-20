<?php
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM posts p JOIN blog_categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: blog.php");
    exit;
}

// Increment view count
$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

$page_title = $post['meta_title'] ?: $post['title'];
$meta_description = $post['meta_description'] ?: $post['summary'];
require_once __DIR__ . '/includes/header.php';

// Artikel terkait di kategori yang sama
$stmt_related = $pdo->prepare("SELECT * FROM posts WHERE category_id = ? AND id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
$stmt_related->execute([$post['category_id'], $post['id']]);
$related_posts = $stmt_related->fetchAll();

$post_labels = array_filter(array_map('trim', explode(',', (string)($post['labels'] ?? ''))));
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
$wa_share = "https://wa.me/?text=" . urlencode($post['title'] . " - " . $current_url);
?>

<div class="bg-slate-900 text-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="index.php" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="blog.php" class="hover:text-white">Artikel</a>
            <span>/</span>
            <a href="blog.php?kategori=<?= urlencode($post['category_slug']) ?>" class="text-blue-400 hover:text-blue-300"><?= e($post['category_name']) ?></a>
        </nav>

        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white leading-tight">
            <?= e($post['title']) ?>
        </h1>

        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 pt-2 border-t border-slate-800">
            <span>✍️ Penulis: <strong><?= e($post['author'] ?: 'Tim Teknis PT. KGS') ?></strong></span>
            <span>📅 Tanggal: <?= date('d F Y', strtotime($post['created_at'])) ?></span>
            <span>👁️ <?= number_format((int)$post['views'] + 1) ?> Pembaca</span>
            <span class="bg-blue-600/40 text-blue-300 px-2.5 py-0.5 rounded font-semibold border border-blue-500/30">
                <?= e($post['category_name']) ?>
            </span>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    <!-- Main Content Container -->
    <article class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xs space-y-6">
        <?php if (!empty($post['featured_image'])): ?>
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 max-h-96">
            <img src="<?= e($post['featured_image']) ?>" alt="<?= e($post['alt_image'] ?: $post['title']) ?>" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>

        <!-- Lead summary -->
        <div class="p-4 bg-slate-50 border-l-4 border-blue-600 rounded-r-xl text-slate-700 text-sm font-medium leading-relaxed italic">
            <?= e($post['summary']) ?>
        </div>

        <!-- Rich Body -->
        <div class="prose-kgs max-w-none text-slate-700 text-sm leading-relaxed space-y-4">
            <?= sanitize_rich_html($post['content']) ?>
        </div>

        <?php if (!empty($post_labels)): ?>
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-bold text-slate-600">Label Topik:</span>
            <?php foreach ($post_labels as $lbl): ?>
                <a href="blog.php?label=<?= urlencode($lbl) ?>" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 rounded-lg font-medium transition">
                    #<?= e($lbl) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Social Share Bar -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <span class="font-bold text-slate-700">Bagikan artikel wawasan ini:</span>
            <div class="flex items-center gap-2">
                <a href="<?= e($wa_share) ?>" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition flex items-center gap-1.5">
                    <span>💬 WhatsApp</span>
                </a>
                <button type="button" data-copy-text="<?= e($current_url) ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl border transition cursor-pointer">
                    🔗 Salin Tautan
                </button>
            </div>
        </div>
    </article>

    <!-- Consultation CTA Box -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white rounded-3xl p-8 shadow-md flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center sm:text-left">
            <h3 class="text-xl font-bold">Butuh Konsultasi Teknis Terkait Topik Ini?</h3>
            <p class="text-xs text-blue-100 max-w-lg leading-relaxed">
                Tim engineer PT. Kreatif Global Solusindo siap melakukan survei lokasi dan memberikan rancangan sistem terintegrasi yang tepat guna.
            </p>
        </div>
        <a href="kontak.php#konsultasi" class="px-6 py-3 bg-white text-blue-800 hover:bg-blue-50 font-extrabold text-xs rounded-xl shadow-md transition whitespace-nowrap">
            Minta Penawaran Resmi
        </a>
    </div>

    <!-- Related Articles -->
    <?php if (!empty($related_posts)): ?>
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-900">Artikel Terkait Lainnya</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php foreach ($related_posts as $r): ?>
                <a href="detail-blog.php?slug=<?= e($r['slug']) ?>" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-300 hover:shadow-xs transition block group">
                    <span class="text-[10px] text-slate-400 font-mono"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                    <h4 class="font-bold text-slate-900 group-hover:text-blue-600 text-xs mt-1 leading-snug line-clamp-2">
                        <?= e($r['title']) ?>
                    </h4>
                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-1"><?= e($r['summary']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
