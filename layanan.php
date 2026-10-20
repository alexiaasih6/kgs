<?php
require_once __DIR__ . '/config/database.php';
$layanan_page = get_page_by_slug($pdo, 'layanan');
$page_title = !empty($layanan_page['meta_title']) ? $layanan_page['meta_title'] : ($layanan_page['title'] ?? "Katalog Layanan & Solusi Terintegrasi");
$meta_description = $layanan_page['meta_description'] ?? $layanan_page['summary'] ?? '';
require_once __DIR__ . '/includes/header.php';

$cat_code = isset($_GET['cat']) ? trim($_GET['cat']) : '';

if (!empty($cat_code)) {
    $stmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.code as category_code FROM services s JOIN service_categories c ON s.category_id = c.id WHERE c.code = ? AND s.is_published = 1 ORDER BY s.sort_order ASC");
    $stmt->execute([$cat_code]);
    $services = $stmt->fetchAll();
} else {
    $services = $pdo->query("SELECT s.*, c.name as category_name, c.code as category_code FROM services s JOIN service_categories c ON s.category_id = c.id WHERE s.is_published = 1 ORDER BY c.segment_number ASC, s.sort_order ASC")->fetchAll();
}

$all_categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY segment_number ASC")->fetchAll();
?>

<div class="bg-white border-b border-slate-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block mb-1">Katalog Solusi & Layanan B2B Terpadu</span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?= e($layanan_page['title'] ?: 'Katalog Layanan & Solusi Terintegrasi') ?></h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-3xl">
            <?= e($layanan_page['summary'] ?: 'Solusi teknologi informasi, telekomunikasi, keamanan CCTV, dan internet korporasi PT. Kreatif Global Solusindo.') ?>
        </p>
        <?php if (!empty($layanan_page['content'])): ?>
            <div class="mt-3 text-xs text-slate-600 leading-relaxed max-w-4xl prose" data-live-page-slug="layanan" data-live-page-field="content">
                <?= sanitize_rich_html($layanan_page['content']) ?>
            </div>
        <?php endif; ?>

        <!-- Segment Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 mt-6">
            <a href="layanan.php" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition <?= empty($cat_code) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                Semua Segmen
            </a>
            <?php foreach ($all_categories as $c): ?>
            <a href="layanan.php?cat=<?= urlencode($c['code']) ?>" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition <?= ($cat_code === $c['code']) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                Segmen <?= e($c['segment_number']) ?>: <?= e($c['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($services as $srv): ?>
        <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                    <span class="font-semibold text-blue-600"><?= e($srv['category_name']) ?></span>
                    <span class="text-emerald-700 font-medium text-[11px]">✓ Standar Teruji</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2 leading-snug"><?= e($srv['name']) ?></h3>
                <p class="text-xs text-slate-600 leading-relaxed mb-4"><?= e($srv['summary']) ?></p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="detail-layanan.php?slug=<?= urlencode($srv['slug']) ?>" class="font-semibold text-blue-600 hover:underline">
                    Detail Spesifikasi &rarr;
                </a>
                <a href="kontak.php?layanan=<?= urlencode($srv['name']) ?>#konsultasi" class="text-slate-600 hover:text-slate-900">
                    Minta Penawaran
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
