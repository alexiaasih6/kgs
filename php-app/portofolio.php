<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
$portofolio_page = get_page_by_slug($pdo, 'portofolio');
$page_title = !empty($portofolio_page['meta_title']) ? $portofolio_page['meta_title'] : ($portofolio_page['title'] ?? 'Dokumentasi Proyek & Portofolio Nyata');
$meta_description = $portofolio_page['meta_description'] ?? $portofolio_page['summary'] ?? '';
require_once __DIR__ . '/includes/header.php';

$cat_filter = $_GET['kategori'] ?? '';

$sql = "SELECT * FROM projects WHERE 1=1";
$params = [];
if (!empty($cat_filter)) {
    $sql .= " AND category = ?";
    $params[] = $cat_filter;
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM projects ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="bg-gradient-to-b from-slate-900 to-blue-950 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="max-w-3xl space-y-4">
            <span class="px-3 py-1 bg-blue-500/30 text-blue-300 text-xs font-bold rounded-full border border-blue-400/30 uppercase tracking-wider" data-live-setting="portofolio_kicker">
                <?= e(get_setting($pdo, 'portofolio_kicker', 'Rekam Jejak Pekerjaan Lapangan')) ?>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight" data-live-page-slug="portofolio" data-live-page-field="title"><?= e($portofolio_page['title'] ?: 'Dokumentasi Proyek & Portofolio Teknis') ?></h1>
            <p class="text-slate-300 text-sm leading-relaxed" data-live-page-slug="portofolio" data-live-page-field="summary">
                <?= e($portofolio_page['summary'] ?: 'Bukti pengerjaan instalasi lapangan oleh tim teknisi bersertifikasi PT. Kreatif Global Solusindo: mulai dari pabrikasi mandiri tiang menara, grounding pembumian teruji Earth Resistance Tester, penangkal petir, modul solar cell, hingga instalasi AIS pelacakan kapal.') ?>
            </p>
            <?php if (!empty($portofolio_page['content'])): ?>
                <div class="text-xs sm:text-sm text-slate-300/90 leading-relaxed prose prose-invert" data-live-page-slug="portofolio" data-live-page-field="content">
                    <?= sanitize_rich_html($portofolio_page['content']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="shrink-0">
            <a href="klien.php" class="inline-flex items-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl text-xs font-bold transition">
                <span>Lihat Daftar 100+ Klien Resmi &rarr;</span>
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    <!-- Category Filter Bar -->
    <div class="flex flex-wrap items-center gap-2 pb-4 border-b border-slate-200">
        <span class="text-xs font-bold text-slate-500 mr-2">Pilih Kategori Pekerjaan:</span>
        <a href="portofolio.php" class="px-4 py-2 rounded-xl text-xs font-semibold transition <?= empty($cat_filter) ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' ?>">
            Semua Dokumentasi (<?= count($projects) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="portofolio.php?kategori=<?= urlencode($cat) ?>" class="px-4 py-2 rounded-xl text-xs font-semibold transition <?= $cat_filter === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' ?>">
                <?= e($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grid of Projects -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($projects as $p): ?>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <?php if (!empty($p['image_url'])): ?>
                        <div class="h-48 w-full bg-slate-100 overflow-hidden border-b border-slate-100">
                            <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['title']) ?>" data-live-img="project_image:<?= (int)$p['id'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    <?php endif; ?>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md" data-live-record="projects:<?= (int)$p['id'] ?>:category">
                                <?= e($p['category']) ?>
                            </span>
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded">
                                Proyek Terverifikasi
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-blue-600 transition leading-snug" data-live-record="projects:<?= (int)$p['id'] ?>:title">
                            <?= e($p['title']) ?>
                        </h3>

                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                            <span>📍</span>
                            <span data-live-record="projects:<?= (int)$p['id'] ?>:client_or_location"><?= e($p['client_or_location']) ?></span>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed pt-2 border-t border-slate-100" data-live-record="projects:<?= (int)$p['id'] ?>:description">
                            <?= e($p['description']) ?>
                        </p>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-emerald-700 font-semibold flex items-center gap-1">
                        <span>✓</span> Dokumentasi Instalasi KGS
                    </span>
                    <a href="kontak.php?layanan=<?= urlencode($p['title']) ?>#konsultasi" class="text-blue-600 hover:text-blue-800 font-bold transition">
                        Konsultasi Proyek &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Workshop & Technical Capability Highlight -->
    <div class="bg-slate-900 text-white rounded-2xl p-8 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400" data-live-setting="workshop_kicker"><?= e(get_setting($pdo, 'workshop_kicker', 'Workshop Mandiri di Palembang')) ?></span>
            <h3 class="text-xl font-bold" data-live-setting="workshop_title"><?= e(get_setting($pdo, 'workshop_title', 'Memiliki Workshop Fabrikasi Tower Sendiri')) ?></h3>
            <p class="text-xs text-slate-300 leading-relaxed" data-live-setting="workshop_desc">
                <?= e(get_setting($pdo, 'workshop_desc', 'PT. Kreatif Global Solusindo memproduksi dan merakit besi rangka Guywire Tower dan Self Supporting Tower di workshop mandiri Palembang, memastikan kontrol kualitas material, pengelasan presisi, galvanis anti karat, dan pengujian kekuatan tarik seling sebelum instalasi di lokasi klien.')) ?>
            </p>
        </div>
        <a href="kontak.php#konsultasi" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md transition whitespace-nowrap">
            Minta Penawaran Fabrikasi & Ereksi
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
