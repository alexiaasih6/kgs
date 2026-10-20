<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: layanan.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.*, c.name AS category_name, c.segment_number 
    FROM services s 
    JOIN service_categories c ON s.category_id = c.id 
    WHERE s.slug = ? AND s.is_published = 1 
    LIMIT 1
");
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: layanan.php');
    exit;
}

$page_title = !empty($service['meta_title']) ? $service['meta_title'] : $service['name'];
$page_description = !empty($service['meta_description']) ? $service['meta_description'] : ($service['summary'] ?: substr(strip_tags($service['description']), 0, 155));

// Ambil layanan lain di segmen yang sama
$relatedStmt = $pdo->prepare("SELECT name, slug, summary FROM services WHERE category_id = ? AND id != ? AND is_published = 1 ORDER BY sort_order ASC LIMIT 4");
$relatedStmt->execute([$service['category_id'], $service['id']]);
$relatedServices = $relatedStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb & Hero -->
<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <a href="index.php" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="layanan.php" class="hover:text-white">Layanan</a>
            <span>/</span>
            <span class="text-blue-400"><?= e($service['category_name']) ?></span>
        </div>
        <span class="px-3 py-1 bg-blue-500/20 text-blue-300 font-bold text-xs rounded-full border border-blue-500/30">
            Segmen 0<?= (int)$service['segment_number'] ?> — <?= e($service['category_name']) ?>
        </span>
        <h1 data-live-record="services:<?= (int)$service['id'] ?>:name" class="text-3xl sm:text-4xl font-extrabold mt-4"><?= e($service['name']) ?></h1>
        <?php if (!empty($service['summary'])): ?>
            <p data-live-record="services:<?= (int)$service['id'] ?>:summary" class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl leading-relaxed"><?= e($service['summary']) ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Detail Content -->
<section class="py-14 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-8 space-y-6">
                <?php $service_img = !empty($service['main_image']) ? $service['main_image'] : ($service['image_url'] ?? ''); ?>
                <?php if (!empty($service_img)): ?>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                        <img src="<?= e($service_img) ?>" alt="<?= e($service['name']) ?>" data-live-img="service_image:<?= (int)$service['id'] ?>" class="w-full h-72 sm:h-96 object-cover">
                    </div>
                <?php endif; ?>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 mb-4" data-live-setting="detail_service_heading"><?= e(get_setting($pdo, 'detail_service_heading', 'Deskripsi & Ruang Lingkup Layanan')) ?></h2>
                        <div data-live-record="services:<?= (int)$service['id'] ?>:description" class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed space-y-3"><?= nl2br(e($service['description'])) ?></div>
                    </div>

                    <?php if (!empty($service['features'])):
                        $feat_items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$service['features']))));
                        if (!empty($feat_items)):
                    ?>
                        <div class="pt-5 border-t border-slate-200">
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-3">Spesifikasi & Fitur Unggulan</h3>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-700">
                                <?php foreach ($feat_items as $fi): ?>
                                    <li class="flex items-start gap-2 bg-white p-3 rounded-xl border border-slate-200">
                                        <span class="text-blue-600 font-bold">✓</span>
                                        <span><?= e($fi) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; endif; ?>

                    <?php if (!empty($service['brands']) || !empty($service['target_sectors'])): ?>
                        <div class="pt-5 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <?php if (!empty($service['brands'])): ?>
                                <div class="bg-white p-4 rounded-xl border border-slate-200">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Brand & Perangkat Pendukung</span>
                                    <span class="font-bold text-slate-800" data-live-record="services:<?= (int)$service['id'] ?>:brands"><?= e($service['brands']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($service['target_sectors'])): ?>
                                <div class="bg-white p-4 rounded-xl border border-slate-200">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Sektor Industri Sasaran</span>
                                    <span class="font-bold text-slate-800" data-live-record="services:<?= (int)$service['id'] ?>:target_sectors"><?= e($service['target_sectors']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar CTA & Related -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-slate-900 text-white rounded-2xl p-6 space-y-4">
                    <h3 class="text-base font-bold" data-live-setting="detail_service_cta_title"><?= e(get_setting($pdo, 'detail_service_cta_title', 'Konsultasikan Kebutuhan Proyek Ini')) ?></h3>
                    <p class="text-xs text-slate-300 leading-relaxed" data-live-setting="detail_service_cta_desc"><?= e(get_setting($pdo, 'detail_service_cta_desc', 'Tim teknis PT. Kreatif Global Solusindo siap membantu survei lapangan, perancangan topologi, dan estimasi RAB.')) ?></p>
                    <a href="https://wa.me/<?= e(format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'))) ?>?text=<?= rawurlencode('Halo Tim PT. KGS, saya ingin berkonsultasi mengenai layanan: ' . $service['name']) ?>" target="_blank" rel="noopener noreferrer" class="block w-full text-center py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition">
                        Chat WhatsApp Teknis
                    </a>
                    <a href="kontak.php#konsultasi" class="block w-full text-center py-3 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl transition">
                        Minta Penawaran Resmi
                    </a>
                </div>

                <?php if (!empty($relatedServices)): ?>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Layanan Terkait</h4>
                        <div class="space-y-2.5">
                            <?php foreach ($relatedServices as $rel): ?>
                                <a href="detail-layanan.php?slug=<?= e($rel['slug']) ?>" class="block p-3 rounded-xl bg-white border border-slate-200 hover:border-blue-400 transition">
                                    <div class="text-xs font-bold text-slate-800"><?= e($rel['name']) ?></div>
                                    <?php if (!empty($rel['summary'])): ?>
                                        <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5"><?= e($rel['summary']) ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
