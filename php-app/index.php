<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$home_page_data = get_page_by_slug($pdo, 'beranda');
$page_title = ($home_page_data['title'] ?: 'System Integrator, Security Solution & ISP') . ' - PT. Kreatif Global Solusindo';
$page_description = $home_page_data['summary'] ?: 'PT. Kreatif Global Solusindo (KGS) - Mitra B2B Infrastruktur Jaringan, Telekomunikasi, CCTV, Tower, Penangkal Petir, dan Layanan Internet ISP KGSNet sejak 1997.';

$slider_cfg = get_hero_slider_config($pdo);
$slides = $slider_cfg['slides'] ?? [];
$layout_mode = $slider_cfg['layout_mode'] ?? 'side';
$has_bg_slider = !empty($slides) && in_array($layout_mode, ['background', 'both'], true);
$has_side_slider = !empty($slides) && in_array($layout_mode, ['side', 'both'], true);

// Ambil kategori segmen & layanan terbit dari database MySQL
$categories_db = [];
$services_db = [];
try {
    $categories_db = $pdo->query("SELECT * FROM service_categories ORDER BY segment_number ASC, id ASC")->fetchAll();
    $services_db = $pdo->query("SELECT s.*, c.name as category_name, c.segment_number FROM services s JOIN service_categories c ON s.category_id = c.id WHERE s.is_published = 1 ORDER BY c.segment_number ASC, s.sort_order ASC, s.id ASC LIMIT 6")->fetchAll();
} catch (Exception $e) {
    $categories_db = [];
    $services_db = [];
}

// Ambil proyek portofolio terbaru dari database MySQL
$recent_projects = [];
try {
    $recent_projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 3")->fetchAll();
} catch (Exception $e) {
    $recent_projects = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-slate-900 text-white overflow-hidden">
    <?php if ($has_bg_slider): ?>
        <div class="absolute inset-0 z-0">
            <?php foreach ($slides as $idx => $sl): ?>
                <img src="<?= e($sl['image_url']) ?>" alt="<?= e($sl['title']) ?>" data-bg-slide="<?= $idx ?>" data-live-img="slider_replace:<?= (int)$sl['id'] ?>" class="w-full h-full object-cover absolute inset-0 transition-opacity duration-700 <?= $idx === 0 ? 'opacity-100' : 'opacity-0' ?>">
            <?php endforeach; ?>
            <div class="absolute inset-0 bg-slate-950" style="opacity: <?= round(($slider_cfg['overlay_opacity'] ?? 75) / 100, 2) ?>;"></div>
        </div>
    <?php else: ?>
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:24px_24px]"></div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/10 border border-blue-400/20 text-blue-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span data-live-setting="hero_badge_text"><?= e(get_setting($pdo, 'hero_badge_text', 'Berdiri Sejak 1997 • PT. Kreatif Global Solusindo')) ?></span>
                </div>
                <h1 data-live-page-slug="beranda" data-live-page-field="title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    <?= e($home_page_data['title'] ?: 'Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider') ?>
                </h1>
                <p data-live-page-slug="beranda" data-live-page-field="summary" class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                    <?= e($home_page_data['summary'] ?: 'Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.') ?>
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="kontak.php#konsultasi" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition text-sm">
                        <span data-live-setting="hero_cta_primary"><?= e(get_setting($pdo, 'hero_cta_primary', 'Konsultasi & Penawaran Gratis')) ?></span> &rarr;
                    </a>
                    <a href="layanan.php" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl transition text-sm">
                        <span data-live-setting="hero_cta_secondary"><?= e(get_setting($pdo, 'hero_cta_secondary', 'Jelajahi 5 Segmen Layanan')) ?></span>
                    </a>
                </div>

                <!-- 4 Poin Keunggulan Utama (Sinkron dengan Admin CMS) -->
                <div class="pt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-300 border-t border-slate-800/80">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span data-live-setting="hero_highlight_1"><?= e(get_setting($pdo, 'hero_highlight_1', 'ISP Resmi KGSNet & Anggota APJII')) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span data-live-setting="hero_highlight_2"><?= e(get_setting($pdo, 'hero_highlight_2', 'Pabrikasi & Instalasi Tower Guywire Mandiri')) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span data-live-setting="hero_highlight_3"><?= e(get_setting($pdo, 'hero_highlight_3', 'Garansi Uji Tahanan Grounding < 2 Ohm')) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span data-live-setting="hero_highlight_4"><?= e(get_setting($pdo, 'hero_highlight_4', 'Klien Korporasi, BUMN, Perbankan & Rumah Sakit')) ?></span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Slider Gambar Beranda ATAU Kartu NOC -->
            <div class="lg:col-span-5">
                <?php if ($has_side_slider): ?>
                    <div class="bg-slate-950/90 border border-slate-700/80 rounded-2xl p-3 shadow-2xl space-y-3">
                        <div class="relative h-64 sm:h-72 rounded-xl overflow-hidden bg-slate-900">
                            <?php foreach ($slides as $idx => $sl): ?>
                                <div data-side-slide="<?= $idx ?>" class="absolute inset-0 transition-opacity duration-700 <?= $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>">
                                    <img src="<?= e($sl['image_url']) ?>" alt="<?= e($sl['title']) ?>" data-live-img="slider_replace:<?= (int)$sl['id'] ?>" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/35 to-transparent"></div>
                                    <div class="absolute bottom-3 left-3 right-3">
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-600/90 text-white text-[10px] font-bold uppercase tracking-wider" data-live-record="hero_sliders:<?= (int)$sl['id'] ?>:badge_text">
                                            <?= e($sl['badge_text'] ?: 'Dokumentasi KGS') ?>
                                        </span>
                                        <h3 class="text-sm font-bold text-white mt-1.5" data-live-record="hero_sliders:<?= (int)$sl['id'] ?>:title"><?= e($sl['title']) ?></h3>
                                        <?php if (!empty($sl['subtitle'])): ?>
                                            <p class="text-[11px] text-slate-300 line-clamp-2 mt-0.5" data-live-record="hero_sliders:<?= (int)$sl['id'] ?>:subtitle"><?= e($sl['subtitle']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($slides) > 1): ?>
                            <div class="flex items-center justify-between px-2 py-1 text-[11px] text-slate-400">
                                <span>Dokumentasi Infrastruktur PT. KGS</span>
                                <div class="flex gap-1.5">
                                    <?php foreach ($slides as $idx => $_): ?>
                                        <button type="button" onclick="window.kgsGoSlide(<?= $idx ?>)" data-slide-dot="<?= $idx ?>" class="w-5 h-1.5 rounded-full transition cursor-pointer <?= $idx === 0 ? 'bg-blue-500' : 'bg-slate-700' ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 shadow-2xl space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-4">
                            <div>
                                <span class="text-xs text-blue-400 font-bold uppercase tracking-wider" data-live-setting="noc_kicker"><?= e(get_setting($pdo, 'noc_kicker', 'Infrastruktur Aktif')) ?></span>
                                <h2 class="text-lg font-bold text-white" data-live-setting="noc_card_title"><?= e(get_setting($pdo, 'noc_card_title', 'Network Operations Center (NOC)')) ?></h2>
                            </div>
                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-xs font-bold" data-live-setting="noc_status_badge"><?= e(get_setting($pdo, 'noc_status_badge', 'ONLINE 24/7')) ?></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-900/90 p-4 rounded-xl border border-slate-700/80">
                                <div class="text-xs text-slate-400" data-live-setting="noc_box1_label"><?= e(get_setting($pdo, 'noc_box1_label', 'Konektivitas ISP')) ?></div>
                                <div class="text-xl font-extrabold text-white mt-1" data-live-setting="noc_box1_val"><?= e(get_setting($pdo, 'noc_box1_val', 'KGSNet')) ?></div>
                                <div class="text-[11px] text-blue-400 mt-1" data-live-setting="noc_box1_sub"><?= e(get_setting($pdo, 'noc_box1_sub', 'Fiber Optic & Wireless PTP')) ?></div>
                            </div>
                            <div class="bg-slate-900/90 p-4 rounded-xl border border-slate-700/80">
                                <div class="text-xs text-slate-400" data-live-setting="noc_box2_label"><?= e(get_setting($pdo, 'noc_box2_label', 'Proteksi & Grounding')) ?></div>
                                <div class="text-xl font-extrabold text-emerald-400 mt-1" data-live-setting="noc_box2_val"><?= e(get_setting($pdo, 'noc_box2_val', '< 2 Ohm')) ?></div>
                                <div class="text-[11px] text-slate-400 mt-1" data-live-setting="noc_box2_sub"><?= e(get_setting($pdo, 'noc_box2_sub', 'Uji Earth Tester Terukur')) ?></div>
                            </div>
                            <div class="bg-slate-900/90 p-4 rounded-xl border border-slate-700/80">
                                <div class="text-xs text-slate-400" data-live-setting="noc_box3_label"><?= e(get_setting($pdo, 'noc_box3_label', 'Kemitraan Resmi')) ?></div>
                                <div class="text-xl font-extrabold text-white mt-1" data-live-setting="noc_box3_val"><?= e(get_setting($pdo, 'noc_box3_val', 'Panasonic')) ?></div>
                                <div class="text-[11px] text-slate-400 mt-1" data-live-setting="noc_box3_sub"><?= e(get_setting($pdo, 'noc_box3_sub', 'Authorized Dealer Sejak 2002')) ?></div>
                            </div>
                            <div class="bg-slate-900/90 p-4 rounded-xl border border-slate-700/80">
                                <div class="text-xs text-slate-400" data-live-setting="noc_box4_label"><?= e(get_setting($pdo, 'noc_box4_label', 'Sertifikasi Jaringan')) ?></div>
                                <div class="text-xl font-extrabold text-white mt-1" data-live-setting="noc_box4_val"><?= e(get_setting($pdo, 'noc_box4_val', 'NETVIEL')) ?></div>
                                <div class="text-[11px] text-slate-400 mt-1" data-live-setting="noc_box4_sub"><?= e(get_setting($pdo, 'noc_box4_sub', 'Structured Cabling System')) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (count($slides) > 1 && ($has_bg_slider || $has_side_slider)): ?>
    <script>
        (function() {
            let current = 0;
            const total = <?= count($slides) ?>;
            window.kgsGoSlide = function(idx) {
                current = idx;
                document.querySelectorAll('[data-side-slide]').forEach((el, i) => {
                    el.style.opacity = i === current ? '1' : '0';
                    el.style.zIndex = i === current ? '10' : '0';
                });
                document.querySelectorAll('[data-bg-slide]').forEach((el, i) => {
                    el.style.opacity = i === current ? '1' : '0';
                });
                document.querySelectorAll('[data-slide-dot]').forEach((el, i) => {
                    el.className = 'w-5 h-1.5 rounded-full transition cursor-pointer ' + (i === current ? 'bg-blue-500' : 'bg-slate-700');
                });
            };
            <?php if (!empty($slider_cfg['auto_play'])): ?>
            setInterval(function() {
                window.kgsGoSlide((current + 1) % total);
            }, <?= (int)($slider_cfg['interval_seconds'] ?? 5) * 1000 ?>);
            <?php endif; ?>
        })();
    </script>
    <?php endif; ?>
</section>

<!-- 4 Angka Statistik Utama (Sinkron Penuh dengan Admin CMS -> Bagian Halaman & Statistik) -->
<section class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            <div class="p-4">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_1_val"><?= e(get_setting($pdo, 'hero_stat_1_val', 'Sejak 1997')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_1_lbl"><?= e(get_setting($pdo, 'hero_stat_1_lbl', 'Pengalaman Lebih dari 27 Tahun')) ?></div>
            </div>
            <div class="p-4">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_2_val"><?= e(get_setting($pdo, 'hero_stat_2_val', '5 Segmen')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_2_lbl"><?= e(get_setting($pdo, 'hero_stat_2_lbl', 'Solusi Teknologi Terintegrasi')) ?></div>
            </div>
            <div class="p-4">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_3_val"><?= e(get_setting($pdo, 'hero_stat_3_val', 'KGSNet')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_3_lbl"><?= e(get_setting($pdo, 'hero_stat_3_lbl', 'Penyelenggara ISP & Anggota APJII')) ?></div>
            </div>
            <div class="p-4">
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600" data-live-setting="hero_stat_4_val"><?= e(get_setting($pdo, 'hero_stat_4_val', '< 2 Ohm')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_4_lbl"><?= e(get_setting($pdo, 'hero_stat_4_lbl', 'Standar Uji Tahanan Grounding')) ?></div>
            </div>
        </div>
    </div>
</section>

<!-- 5 Segmen Solusi Terintegrasi (Dinamis dari Database `service_categories` & `services`) -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-blue-600 font-bold text-xs uppercase tracking-widest" data-live-setting="home_segmen_kicker"><?= e(get_setting($pdo, 'home_segmen_kicker', 'Ruang Lingkup Bisnis')) ?></span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2" data-live-setting="home_segmen_title"><?= e(get_setting($pdo, 'home_segmen_title', '5 Segmen Solusi Terintegrasi')) ?></h2>
            <p class="text-sm text-slate-600 mt-3" data-live-setting="home_segmen_desc"><?= e(get_setting($pdo, 'home_segmen_desc', 'Seluruh layanan di bawah ini terhubung langsung dengan katalog database Admin CMS.')) ?></p>
        </div>

        <?php if (!empty($categories_db)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories_db as $cat): ?>
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-sm font-black">0<?= (int)$cat['segment_number'] ?></span>
                                <span class="text-xs font-extrabold text-slate-300">SEGMEN 0<?= (int)$cat['segment_number'] ?></span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 mb-2" data-live-record="service_categories:<?= (int)$cat['id'] ?>:name"><?= e($cat['name']) ?></h3>
                            <p class="text-xs text-slate-600 leading-relaxed" data-live-record="service_categories:<?= (int)$cat['id'] ?>:description"><?= e($cat['description']) ?></p>
                        </div>
                        <a href="layanan.php#segmen-<?= (int)$cat['segment_number'] ?>" class="inline-block mt-5 text-xs font-bold text-blue-600 hover:text-blue-800">Lihat Rincian Layanan &rarr;</a>
                    </div>
                <?php endforeach; ?>

                <div class="bg-blue-700 text-white rounded-2xl p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-200" data-live-setting="home_custom_kicker"><?= e(get_setting($pdo, 'home_custom_kicker', 'Konsultasi Proyek')) ?></span>
                        <h3 class="text-lg font-bold mt-2 mb-2" data-live-setting="home_custom_title"><?= e(get_setting($pdo, 'home_custom_title', 'Butuh Solusi Kustom untuk Perusahaan Anda?')) ?></h3>
                        <p class="text-xs text-blue-100 leading-relaxed" data-live-setting="home_custom_desc"><?= e(get_setting($pdo, 'home_custom_desc', 'Tim teknis kami siap melakukan survei lokasi, perancangan topologi jaringan, dan pengajuan RAB/BoQ sesuai kebutuhan operasional Anda.')) ?></p>
                    </div>
                    <a href="kontak.php#konsultasi" class="mt-6 inline-block text-center py-3 px-4 bg-white text-blue-700 font-bold text-xs rounded-xl hover:bg-blue-50 transition">Hubungi Tim Engineer Kami</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Katalog Layanan Unggulan dari Tabel `services` -->
        <?php if (!empty($services_db)): ?>
            <div class="mt-14">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900" data-live-setting="home_featured_srv_title"><?= e(get_setting($pdo, 'home_featured_srv_title', 'Layanan & Solusi Unggulan')) ?></h3>
                        <p class="text-xs text-slate-500" data-live-setting="home_featured_srv_desc"><?= e(get_setting($pdo, 'home_featured_srv_desc', 'Menampilkan layanan aktif dari menu Kelola Layanan di Admin CMS.')) ?></p>
                    </div>
                    <a href="layanan.php" class="text-xs font-bold text-blue-600 hover:underline">Lihat Seluruh Layanan &rarr;</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($services_db as $srv): 
                        $srv_img = $srv['image_url'] ?? $srv['main_image'] ?? '';
                    ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <?php if (!empty($srv_img)): ?>
                                    <div class="h-44 bg-slate-900 overflow-hidden">
                                        <img src="<?= e($srv_img) ?>" alt="<?= e($srv['name']) ?>" data-live-img="service_image:<?= (int)$srv['id'] ?>" class="w-full h-full object-cover">
                                    </div>
                                <?php endif; ?>
                                <div class="p-5">
                                    <span class="px-2.5 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[10px] uppercase">Segmen <?= (int)$srv['segment_number'] ?> • <?= e($srv['category_name']) ?></span>
                                    <h4 class="text-sm font-bold text-slate-900 mt-2" data-live-record="services:<?= (int)$srv['id'] ?>:name"><?= e($srv['name']) ?></h4>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed line-clamp-3" data-live-record="services:<?= (int)$srv['id'] ?>:summary"><?= e($srv['summary']) ?></p>
                                </div>
                            </div>
                            <div class="px-5 pb-5 pt-2 flex items-center justify-between border-t border-slate-100">
                                <a href="detail-layanan.php?slug=<?= e($srv['slug']) ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800">Detail Teknis &rarr;</a>
                                <a href="kontak.php?layanan=<?= urlencode($srv['name']) ?>#konsultasi" class="px-3 py-1.5 bg-slate-900 hover:bg-blue-600 text-white text-[11px] font-bold rounded-lg transition">Minta Penawaran</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Keunggulan Teknis & Operasional -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-5">
                <span class="text-blue-600 font-bold text-xs uppercase tracking-widest" data-live-setting="why_kgs_kicker"><?= e(get_setting($pdo, 'why_kgs_kicker', 'Mengapa Memilih PT. KGS?')) ?></span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight" data-live-setting="why_kgs_title">
                    <?= e(get_setting($pdo, 'why_kgs_title', 'Kemitraan Jangka Panjang dengan Standar Teknis Terukur')) ?>
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed" data-live-setting="why_kgs_desc">
                    <?= e(get_setting($pdo, 'why_kgs_desc', 'Kami tidak sekadar menjual perangkat. Tim engineer PT. KGS mendampingi klien mulai dari survei lokasi (on-site survey), perancangan topologi, fabrikasi di workshop internal, instalasi berstandar K3, hingga pemeliharaan berkelanjutan.')) ?>
                </p>
                
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-sm">1</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" data-live-setting="why_point_1_title"><?= e(get_setting($pdo, 'why_point_1_title', 'Workshop Fabrikasi Tower Mandiri')) ?></h3>
                            <p class="text-xs text-slate-600 mt-0.5" data-live-setting="why_point_1_desc"><?= e(get_setting($pdo, 'why_point_1_desc', 'Memiliki fasilitas pengelasan dan fabrikasi besi tower guywire sendiri untuk kendali mutu konstruksi dan kecepatan pengerjaan proyek.')) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-sm">2</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" data-live-setting="why_point_2_title"><?= e(get_setting($pdo, 'why_point_2_title', 'Pengujian Grounding Terverifikasi (< 2 Ohm)')) ?></h3>
                            <p class="text-xs text-slate-600 mt-0.5" data-live-setting="why_point_2_desc"><?= e(get_setting($pdo, 'why_point_2_desc', 'Setiap instalasi penangkal petir diuji menggunakan Digital Earth Tester hingga mencapai nilai resistansi di bawah 2 Ohm untuk keamanan perangkat sensitif.')) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-sm">3</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" data-live-setting="why_point_3_title"><?= e(get_setting($pdo, 'why_point_3_title', 'Dukungan Prinsipal & Lisensi Resmi')) ?></h3>
                            <p class="text-xs text-slate-600 mt-0.5" data-live-setting="why_point_3_desc"><?= e(get_setting($pdo, 'why_point_3_desc', 'Anggota resmi APJII untuk layanan ISP KGSNet, Authorized Dealer Panasonic sejak 2002, dan System Integrator bersertifikat NETVIEL.')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="bg-slate-900 text-white rounded-2xl p-8 space-y-6">
                    <h3 class="text-lg font-bold border-b border-slate-800 pb-3" data-live-setting="kgsnet_box_title"><?= e(get_setting($pdo, 'kgsnet_box_title', 'KGSNet Dedicated Internet')) ?></h3>
                    <p class="text-xs text-slate-300 leading-relaxed" data-live-setting="kgsnet_box_desc">
                        <?= e(get_setting($pdo, 'kgsnet_box_desc', 'Layanan akses internet dedicated fiber optic dan wireless point-to-point dengan rasio bandwidth simetris 1:1, garansi SLA 99.5%, serta dukungan teknis lokal yang responsif.')) ?>
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-800 p-3.5 rounded-xl border border-slate-700">
                            <strong class="text-blue-400 block mb-1" data-live-setting="kgsnet_feat1_title"><?= e(get_setting($pdo, 'kgsnet_feat1_title', 'Koneksi Simetris 1:1')) ?></strong>
                            <span class="text-slate-300" data-live-setting="kgsnet_feat1_desc"><?= e(get_setting($pdo, 'kgsnet_feat1_desc', 'Kecepatan upload dan download setara untuk kebutuhan server, ERP, dan video conference.')) ?></span>
                        </div>
                        <div class="bg-slate-800 p-3.5 rounded-xl border border-slate-700">
                            <strong class="text-blue-400 block mb-1" data-live-setting="kgsnet_feat2_title"><?= e(get_setting($pdo, 'kgsnet_feat2_title', 'Jangkauan Remote Area')) ?></strong>
                            <span class="text-slate-300" data-live-setting="kgsnet_feat2_desc"><?= e(get_setting($pdo, 'kgsnet_feat2_desc', 'Solusi link wireless untuk perkebunan sawit, pertambangan, dan pabrik di luar kota.')) ?></span>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="harga.php" class="inline-block px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition">Lihat Pilihan Paket B2B &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dokumentasi Proyek & Portofolio Nyata (Dinamis dari Tabel `projects`) -->
<section class="py-16 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-blue-400 font-bold text-xs uppercase tracking-widest" data-live-setting="home_portofolio_kicker"><?= e(get_setting($pdo, 'home_portofolio_kicker', 'Bukti Kinerja Nyata')) ?></span>
                <h2 class="text-2xl sm:text-3xl font-extrabold mt-2" data-live-setting="home_portofolio_title"><?= e(get_setting($pdo, 'home_portofolio_title', 'Dokumentasi Proyek & Pengalaman Lapangan')) ?></h2>
            </div>
            <a href="portofolio.php" class="text-xs font-bold text-blue-400 hover:text-blue-300">Lihat Semua Portofolio &rarr;</a>
        </div>

        <?php if (!empty($recent_projects)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <?php foreach ($recent_projects as $prj): ?>
                    <div class="bg-slate-800 rounded-2xl overflow-hidden border border-slate-700 flex flex-col justify-between">
                        <div>
                            <?php if (!empty($prj['image_url'])): ?>
                                <div class="h-44 bg-slate-950 overflow-hidden">
                                    <img src="<?= e($prj['image_url']) ?>" alt="<?= e($prj['title']) ?>" data-live-img="project_image:<?= (int)$prj['id'] ?>" class="w-full h-full object-cover">
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <span class="px-2.5 py-1 bg-blue-500/20 text-blue-300 rounded-md font-semibold" data-live-record="projects:<?= (int)$prj['id'] ?>:category"><?= e($prj['category'] ?: 'Proyek KGS') ?></span>
                                <h3 class="text-base font-bold text-white mt-3 mb-2" data-live-record="projects:<?= (int)$prj['id'] ?>:title"><?= e($prj['title']) ?></h3>
                                <p class="text-slate-300 leading-relaxed" data-live-record="projects:<?= (int)$prj['id'] ?>:description"><?= e($prj['description']) ?></p>
                            </div>
                        </div>
                        <div class="px-6 pb-5 text-[11px] text-slate-400 border-t border-slate-700/60 pt-3">
                            📍 Lokasi / Klien: <strong class="text-slate-200" data-live-record="projects:<?= (int)$prj['id'] ?>:client_or_location"><?= e($prj['client_or_location']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700">
                    <span class="px-2.5 py-1 bg-blue-500/20 text-blue-300 rounded-md font-semibold">Fabrikasi & Konstruksi</span>
                    <h3 class="text-base font-bold text-white mt-4 mb-2">Pembangunan Tower Guywire</h3>
                    <p class="text-slate-300 leading-relaxed">Fabrikasi mandiri di workshop internal KGS dan instalasi vertikal tower guywire beserta pengecatan anti-karat standar penerbangan.</p>
                </div>
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700">
                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 rounded-md font-semibold">Sertifikasi Keamanan</span>
                    <h3 class="text-base font-bold text-white mt-4 mb-2">Instalasi & Uji Grounding &lt; 2 Ohm</h3>
                    <p class="text-slate-300 leading-relaxed">Pemasangan grounding rod tembaga dan verifikasi nilai tahanan tanah menggunakan Digital Earth Tester mencapai 1.24 Ohm.</p>
                </div>
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700">
                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-300 rounded-md font-semibold">Maritim & Korporasi</span>
                    <h3 class="text-base font-bold text-white mt-4 mb-2">Integrasi AIS Kapal & Jaringan BUMN</h3>
                    <p class="text-slate-300 leading-relaxed">Implementasi Automatic Identification System (AIS) pelayaran serta infrastruktur jaringan untuk rumah sakit, perbankan, dan perkebunan.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Bottom -->
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-slate-50 border border-slate-200 rounded-3xl p-8 sm:p-12 shadow-xs">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900" data-live-setting="cta_bottom_title"><?= e(get_setting($pdo, 'cta_bottom_title', 'Siap Meningkatkan Infrastruktur Teknologi Perusahaan Anda?')) ?></h2>
            <p class="text-sm text-slate-600 mt-3 max-w-2xl mx-auto" data-live-setting="cta_bottom_desc"><?= e(get_setting($pdo, 'cta_bottom_desc', 'Hubungi kantor operasional kami untuk penjadwalan survei teknis atau permintaan penawaran harga resmi.')) ?></p>
            
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left text-xs max-w-3xl mx-auto mb-8">
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-1">Alamat Kantor</div>
                    <div class="font-bold text-slate-900" data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-1">Telepon Kantor</div>
                    <div class="font-bold text-slate-900" data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-1">WhatsApp & Email Resmi</div>
                    <div class="font-bold text-emerald-700">WA: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></div>
                    <div class="text-slate-600" data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?></div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="kontak.php#konsultasi" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">Kirim Permintaan Penawaran</a>
                <a href="https://wa.me/<?= e(format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'))) ?>?text=<?= rawurlencode('Halo Tim PT. KGS, saya ingin berkonsultasi mengenai kebutuhan layanan perusahaan.') ?>" target="_blank" rel="noopener noreferrer" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">Chat WhatsApp Sekarang</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
