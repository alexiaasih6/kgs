<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: index.php');
    exit;
}

ensure_default_pages($pdo);
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1 LIMIT 1");
$stmt->execute([$slug]);
$page_item = $stmt->fetch();

if (!$page_item) {
    http_response_code(404);
    $page_title = 'Halaman Tidak Ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="py-24 text-center max-w-xl mx-auto px-4"><h1 class="text-2xl font-extrabold text-slate-900 mb-2">404 — Halaman Tidak Ditemukan</h1><p class="text-xs text-slate-500 mb-6">Maaf, halaman yang Anda cari belum tersedia atau telah dipindahkan.</p><a href="index.php" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold">Kembali ke Beranda</a></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = !empty($page_item['meta_title']) ? $page_item['meta_title'] : $page_item['title'];
$page_desc  = !empty($page_item['meta_description']) ? $page_item['meta_description'] : ($page_item['summary'] ?: substr(strip_tags((string)$page_item['content']), 0, 155));

require_once __DIR__ . '/includes/header.php';
?>

<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-400 font-bold text-xs uppercase tracking-widest" data-live-setting="page_badge"><?= e(get_setting($pdo, 'page_badge', 'Informasi Korporasi')) ?></span>
        <h1 data-live-page-slug="<?= e($page_item['slug']) ?>" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-2"><?= e($page_item['title']) ?></h1>
        <?php if (!empty($page_item['summary'])): ?>
            <p data-live-page-slug="<?= e($page_item['slug']) ?>" data-live-page-field="summary" class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl leading-relaxed"><?= e($page_item['summary']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="py-14 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php $page_img = !empty($page_item['featured_image']) ? $page_item['featured_image'] : ($page_item['hero_image'] ?? ''); ?>
        <?php if (!empty($page_img)): ?>
            <div class="mb-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                <img src="<?= e($page_img) ?>" alt="<?= e($page_item['title']) ?>" data-live-img="page_hero:<?= (int)$page_item['id'] ?>" class="w-full h-72 sm:h-96 object-cover">
            </div>
        <?php endif; ?>

        <div data-live-page-slug="<?= e($page_item['slug']) ?>" data-live-page-field="content" class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed space-y-4"><?= sanitize_rich_html($page_item['content']) ?></div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
