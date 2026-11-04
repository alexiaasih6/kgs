<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$home_page_data = get_page_by_slug($pdo, 'beranda');
$page_title = ($home_page_data['title'] ?: 'System Integrator, Security Solution & Internet Provider') . ' - PT. Kreatif Global Solusindo';
$page_description = $home_page_data['summary'] ?: 'PT. Kreatif Global Solusindo (KGS) - System Integrator, Security Solution & Internet Provider sejak 1997.';

$slider_cfg = get_hero_slider_config($pdo);
$slides = $slider_cfg['slides'] ?? [];
$layout_mode = $slider_cfg['layout_mode'] ?? 'side';
$has_bg_slider = !empty($slides) && in_array($layout_mode, ['background', 'both'], true);

// Ambil 8 Kategori Slideshow Infrastruktur & Konten Why Choose Us
$slideshow_categories = get_slideshow_categories($pdo, true);
$why_choose_us = get_why_choose_us_content();

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

// Catatan: Bagian FAQ tidak ditampilkan di Beranda (tetap tersedia di halaman khusus faq.php dan Admin CMS)

$wa_clean = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section: Teks Utama di Kiri & Interactive Slideshows di Samping Kanan -->
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <!-- Kolom Kiri (7 Cols): PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-extrabold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span data-live-setting="hero_badge_text"><?= e(get_setting($pdo, 'hero_badge_text', 'PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP')) ?></span>
                </div>
                <h1 data-live-page-slug="beranda" data-live-page-field="title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    <?= e($home_page_data['title'] ?: 'System Integrator, Security Solution & Internet Provider') ?>
                </h1>
                <p data-live-page-slug="beranda" data-live-page-field="summary" class="text-slate-200 text-base sm:text-lg leading-relaxed max-w-2xl">
                    <?= e($home_page_data['summary'] ?: 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.') ?>
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="kontak.php#konsultasi" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition text-sm">
                        <span data-live-setting="hero_cta_primary"><?= e(get_setting($pdo, 'hero_cta_primary', 'Konsultasi & Penawaran Gratis')) ?></span> &rarr;
                    </a>
                    <a href="layanan.php" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl transition text-sm">
                        <span data-live-setting="hero_cta_secondary"><?= e(get_setting($pdo, 'hero_cta_secondary', 'Jelajahi Product & Service')) ?></span>
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

            <!-- Kolom Kanan (5 Cols): Slideshows tampil di samping teks utama Beranda (Rasio Landscape 16:9 Stabil + Portrait Blur Detection) -->
            <div class="lg:col-span-5">
                <?php if (!empty($slideshow_categories)): ?>
                    <div class="bg-slate-900/95 border border-slate-700/80 rounded-2xl overflow-hidden shadow-2xl">
                        <div class="hero-slide relative w-full bg-slate-950 overflow-hidden" style="aspect-ratio: 16 / 9; min-height: 240px;">
                            <?php foreach ($slideshow_categories as $idx => $cat): ?>
                                <?php
                                $cover = !empty($cat['cover_image']) ? $cat['cover_image'] : ($cat['images'][0]['image_path'] ?? 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80');
                                ?>
                                <a href="slideshow-detail.php?slug=<?= rawurlencode($cat['slug']) ?>" data-hero-ss-img="<?= $idx ?>" class="hero-slide-frame block absolute inset-0 w-full h-full transition-opacity duration-700 <?= $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' ?>">
                                    <img src="<?= e($cover) ?>" alt="" aria-hidden="true" decoding="async" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" class="hero-slide-bg">
                                    <img src="<?= e($cover) ?>" alt="<?= e($cat['title']) ?>" width="1280" height="720" decoding="async" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" fetchpriority="<?= $idx === 0 ? 'high' : 'auto' ?>" class="hero-slide-image w-full h-full object-cover">
                                    <div class="absolute inset-0 z-20 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent pointer-events-none"></div>
                                </a>
                            <?php endforeach; ?>

                            <!-- Top Badge & Counter -->
                            <div class="absolute top-3.5 left-3.5 right-3.5 flex items-center justify-between gap-2 z-30 pointer-events-none">
                                <span id="hero-ss-badge" class="px-3 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold shadow-md">
                                    Slideshow • <?= (int)($slideshow_categories[0]['image_count'] ?? count($slideshow_categories[0]['images'] ?? [])) ?> Gambar
                                </span>
                                <span id="hero-ss-counter" class="px-2.5 py-1 rounded-lg bg-slate-950/85 border border-slate-700 text-[11px] font-mono text-slate-200">
                                    1 / <?= count($slideshow_categories) ?>
                                </span>
                            </div>

                            <!-- Prev / Next Buttons -->
                            <?php if (count($slideshow_categories) > 1): ?>
                                <button type="button" onclick="window.kgsHeroSlideshowPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/80 hover:bg-blue-600 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg z-30" aria-label="Slide Sebelumnya">&#10094;</button>
                                <button type="button" onclick="window.kgsHeroSlideshowNext()" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/80 hover:bg-blue-600 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg z-30" aria-label="Slide Selanjutnya">&#10095;</button>
                            <?php endif; ?>
                        </div>

                        <!-- Slide Title, Snippet & Read More -->
                        <div class="p-5 sm:p-6 space-y-4">
                            <?php foreach ($slideshow_categories as $idx => $cat): ?>
                                <div data-hero-ss-body="<?= $idx ?>" class="<?= $idx === 0 ? 'block' : 'hidden' ?>">
                                    <a href="slideshow-detail.php?slug=<?= rawurlencode($cat['slug']) ?>" title="<?= e($cat['title']) ?>" class="text-base sm:text-lg font-extrabold text-white hover:text-blue-400 transition-colors block leading-snug line-clamp-1 min-h-[1.75rem]">
                                        <?= e($cat['title']) ?>
                                    </a>
                                    <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed line-clamp-3 min-h-[3.75rem]">
                                        <?= e($cat['short_description']) ?>
                                    </p>
                                    <div class="pt-4 mt-4 border-t border-slate-800 flex items-center justify-between gap-3">
                                        <a href="slideshow-detail.php?slug=<?= rawurlencode($cat['slug']) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm transition shadow-md">
                                            <span>Read More</span>
                                            <span>&rarr;</span>
                                        </a>
                                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                            <?php foreach ($slideshow_categories as $dot_idx => $dot_cat): ?>
                                                <button type="button" onclick="window.kgsHeroSlideshowGo(<?= $dot_idx ?>)" title="<?= e($dot_cat['title']) ?>" class="h-2 rounded-full transition-all cursor-pointer <?= $dot_idx === $idx ? 'w-6 bg-blue-400' : 'w-2 bg-slate-700 hover:bg-slate-500' ?>"></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (count($slideshow_categories) > 1): ?>
    <script>
        (function() {
            let currentIdx = 0;
            const total = <?= count($slideshow_categories) ?>;
            const imgCounts = <?= json_encode(array_map(function($c) { return (int)($c['image_count'] ?? count($c['images'] ?? [])); }, $slideshow_categories)) ?>;
            const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            window.kgsHeroSlideshowGo = function(idx) {
                currentIdx = (idx + total) % total;
                document.querySelectorAll('[data-hero-ss-img]').forEach((el, i) => {
                    if (i === currentIdx) {
                        el.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
                        el.classList.add('opacity-100', 'z-10');
                    } else {
                        el.classList.remove('opacity-100', 'z-10');
                        el.classList.add('opacity-0', 'z-0', 'pointer-events-none');
                    }
                });
                document.querySelectorAll('[data-hero-ss-body]').forEach((el, i) => {
                    el.className = (i === currentIdx) ? 'block' : 'hidden';
                });
                const badgeEl = document.getElementById('hero-ss-badge');
                if (badgeEl) {
                    badgeEl.textContent = 'Slideshow • ' + (imgCounts[currentIdx] || 1) + ' Gambar';
                }
                const counterEl = document.getElementById('hero-ss-counter');
                if (counterEl) {
                    counterEl.textContent = (currentIdx + 1) + ' / ' + total;
                }
            };
            window.kgsHeroSlideshowPrev = function() {
                window.kgsHeroSlideshowGo(currentIdx - 1);
            };
            window.kgsHeroSlideshowNext = function() {
                window.kgsHeroSlideshowGo(currentIdx + 1);
            };
            if (!prefersReducedMotion) {
                setInterval(function() {
                    window.kgsHeroSlideshowNext();
                }, 5000);
            }
        })();
    </script>
    <?php endif; ?>
</section>

<!-- 4 Angka Statistik Utama -->
<section class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            <div class="p-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_1_val"><?= e(get_setting($pdo, 'hero_stat_1_val', 'Sejak 1997')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_1_lbl"><?= e(get_setting($pdo, 'hero_stat_1_lbl', 'Pengalaman Lebih dari 27 Tahun')) ?></div>
            </div>
            <div class="p-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_2_val"><?= e(get_setting($pdo, 'hero_stat_2_val', 'Product & Service')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_2_lbl"><?= e(get_setting($pdo, 'hero_stat_2_lbl', 'Security, Networking, PBX, Telecom & ISP')) ?></div>
            </div>
            <div class="p-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-700" data-live-setting="hero_stat_3_val"><?= e(get_setting($pdo, 'hero_stat_3_val', 'KGSNet')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_3_lbl"><?= e(get_setting($pdo, 'hero_stat_3_lbl', 'Penyelenggara ISP & Anggota APJII')) ?></div>
            </div>
            <div class="p-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600" data-live-setting="hero_stat_4_val"><?= e(get_setting($pdo, 'hero_stat_4_val', '< 2 Ohm')) ?></div>
                <div class="text-xs text-slate-500 font-medium mt-1" data-live-setting="hero_stat_4_lbl"><?= e(get_setting($pdo, 'hero_stat_4_lbl', 'Standar Uji Tahanan Grounding')) ?></div>
            </div>
        </div>
    </div>
</section>

<!-- 8 Gambar Kategori Infrastruktur di Bawah Header + Why Choose Us? -->
<section id="slideshow" class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- 8 Kartu Kategori Slideshow dengan Frame Landscape 16:9 Stabil & Deteksi Portrait -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
            <?php foreach ($slideshow_categories as $cat): ?>
                <?php
                $cat_images = isset($cat['images']) && is_array($cat['images']) ? $cat['images'] : [];
                $cover = !empty($cat['cover_image']) ? $cat['cover_image'] : ($cat_images[0]['image_path'] ?? 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80');
                $img_count = (int)($cat['image_count'] ?? count($cat_images));
                if ($img_count <= 0) {
                    $img_count = 3;
                }
                $mini_thumbs = array_slice($cat_images, 0, 3);
                if (empty($mini_thumbs)) {
                    $mini_thumbs = [
                        ['image_path' => $cover, 'caption' => $cat['title']],
                    ];
                }
                ?>
                <article class="kgs-slideshow-card group bg-white rounded-2xl border border-slate-200 hover:border-blue-500 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                    <a href="slideshow-detail.php?slug=<?= rawurlencode($cat['slug']) ?>" class="hero-slide relative w-full bg-slate-900 overflow-hidden block shrink-0" style="aspect-ratio: 16 / 9;">
                        <img src="<?= e($cover) ?>" alt="" aria-hidden="true" decoding="async" loading="lazy" class="hero-slide-bg">
                        <img src="<?= e($cover) ?>" alt="<?= e($cat['title']) ?>" width="640" height="360" decoding="async" loading="lazy" class="hero-slide-image group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 z-20 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent pointer-events-none"></div>
                        <div class="absolute top-3 right-3 z-30">
                            <span class="kgs-slideshow-badge px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/15 inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                <span><?= $img_count ?> Gambar</span>
                            </span>
                        </div>
                        <div class="absolute bottom-3 left-4 right-4 z-30">
                            <h3 class="kgs-slideshow-title text-base sm:text-lg font-extrabold text-white group-hover:text-blue-300 transition-colors leading-snug line-clamp-1" title="<?= e($cat['title']) ?>">
                                <?= e($cat['title']) ?>
                            </h3>
                        </div>
                    </a>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <p class="kgs-slideshow-desc text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3 min-h-[3.75rem]">
                            <?= e($cat['short_description']) ?>
                        </p>
                        <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="slideshow-detail.php?slug=<?= rawurlencode($cat['slug']) ?>" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-blue-600 hover:text-blue-800 transition-colors">
                                <span>Read More</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </a>
                            <div class="flex -space-x-1.5 overflow-hidden shrink-0">
                                <?php foreach ($mini_thumbs as $m_idx => $m_img):
                                    $m_src = !empty($m_img['image_path']) ? $m_img['image_path'] : $cover;
                                ?>
                                    <img src="<?= e($m_src) ?>" alt="<?= e($m_img['caption'] ?? ($cat['title'] . ' ' . ($m_idx + 1))) ?>" width="24" height="24" decoding="async" loading="lazy" class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover bg-slate-200">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Why Choose Us? -->
        <div class="bg-white rounded-3xl border border-slate-200 p-7 sm:p-10 lg:p-12 shadow-xs">
            <div class="max-w-4xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-extrabold uppercase tracking-wider mb-3">
                    PT. Kreatif Global Solusindo • Creative Com
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-5">
                    <?= e($why_choose_us['title']) ?>
                </h2>
                <div class="space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
                    <?php foreach ($why_choose_us['paragraphs'] as $para): ?>
                        <p><?= e($para) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Profile, VISI, BUDAYA & KOMITMEN Section -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5 space-y-3">
                <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Tentang Perusahaan</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Profile</h2>
                <p class="text-sm text-slate-600 leading-relaxed" data-live-setting="company_profile">
                    <?= e(get_setting($pdo, 'company_profile', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.')) ?>
                </p>
                <div class="pt-2">
                    <a href="tentang.php" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-800">
                        Selengkapnya tentang Profile Perusahaan &rarr;
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 gap-4">
                <!-- VISI -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-extrabold text-[11px] rounded uppercase tracking-wider">VISI</span>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed mt-2.5" data-live-setting="vision">
                        <?= e(get_setting($pdo, 'vision', 'Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.')) ?>
                    </p>
                </div>

                <!-- BUDAYA & KOMITMEN -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 font-extrabold text-[11px] rounded uppercase tracking-wider">BUDAYA</span>
                        <p class="text-xs text-slate-700 leading-relaxed mt-2.5" data-live-setting="culture">
                            <?= e(get_setting($pdo, 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.')) ?>
                        </p>
                    </div>
                    <div class="bg-slate-900 text-white rounded-2xl p-6 border border-slate-800">
                        <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 font-extrabold text-[11px] rounded uppercase tracking-wider">KOMITMEN</span>
                        <p class="text-xs text-slate-300 leading-relaxed mt-2.5" data-live-setting="commitment">
                            <?= e(get_setting($pdo, 'commitment', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product & Service Section (Katalog Solusi Terintegrasi — Seluruh Kartu & Item Dapat Diklik) -->
<section id="layanan" class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <a href="layanan.php" class="text-blue-600 hover:text-blue-700 font-bold text-xs uppercase tracking-widest inline-flex items-center gap-1.5 group" data-live-setting="home_segmen_kicker">
                    <span><?= e(get_setting($pdo, 'home_segmen_kicker', 'Katalog Solusi Terintegrasi')) ?></span>
                    <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </a>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" data-live-setting="home_segmen_title">
                    <a href="layanan.php" class="hover:text-blue-600 transition-colors">Product &amp; Service</a>
                </h2>
                <p class="text-sm text-slate-600 mt-2 max-w-2xl" data-live-setting="home_segmen_desc">
                    Security, Networking, PBX, Telecommunication (LIGHTNING PROTECTOR &amp; Tower), dan Internet Service Provider. Klik pada kategori atau item di bawah untuk membuka detail layanan.
                </p>
            </div>
            <div class="shrink-0">
                <a href="layanan.php" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm shadow-sm transition">
                    <span>Lihat Semua Produk &amp; Layanan</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <!-- 4 Pilar Product & Service (Seluruh Kartu & Sub-Item Dapat Diklik) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- 1. Security -->
            <div class="bg-white hover:bg-rose-50/20 rounded-2xl p-6 border border-slate-200 hover:border-rose-400 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=SEC" class="flex items-center justify-between gap-2 group">
                        <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 font-extrabold text-xs uppercase">Security</span>
                        <span class="text-xs font-bold text-slate-400 group-hover:text-rose-600">&nearr;</span>
                    </a>
                    <h3 class="text-base font-extrabold text-slate-900 mt-3 mb-3">
                        <a href="layanan.php?cat=SEC" class="hover:text-rose-600 transition-colors">Security Solution</a>
                    </h3>
                    <ul class="space-y-2 text-xs text-slate-700 font-semibold">
                        <li>
                            <a href="detail-layanan.php?slug=fire-alarm-security" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-rose-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-rose-600 transition">
                                <span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> FIRE &amp; ALARM SECURITY</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=cctv-security-system" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-rose-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-rose-600 transition">
                                <span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> CCTV</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=access-door-control" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-rose-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-rose-600 transition">
                                <span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> Access Door</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=video-door-phone" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-rose-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-rose-600 transition">
                                <span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> Video Door Phone</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="layanan.php?cat=SEC" class="flex items-center justify-between text-[11px] font-bold text-rose-600 hover:underline">
                        <span>Lihat Kategori Security</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 2. Networking -->
            <div class="bg-white hover:bg-blue-50/20 rounded-2xl p-6 border border-slate-200 hover:border-blue-500 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=INFRA" class="flex items-center justify-between gap-2 group">
                        <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 font-extrabold text-xs uppercase">Networking</span>
                        <span class="text-xs font-bold text-slate-400 group-hover:text-blue-600">&nearr;</span>
                    </a>
                    <h3 class="text-base font-extrabold text-slate-900 mt-3 mb-3">
                        <a href="layanan.php?cat=INFRA" class="hover:text-blue-600 transition-colors">Networking</a>
                    </h3>
                    <ul class="space-y-2 text-xs text-slate-700 font-semibold">
                        <li>
                            <a href="detail-layanan.php?slug=router-networking" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-blue-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-blue-600 transition">
                                <span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Router</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=wireless-networking" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-blue-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-blue-600 transition">
                                <span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Wireless</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=fiber-optik-backbone" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-blue-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-blue-600 transition">
                                <span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Fiber Optik &amp; Cabling</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="layanan.php?cat=INFRA" class="flex items-center justify-between text-[11px] font-bold text-blue-600 hover:underline">
                        <span>Lihat Kategori Networking</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 3. PBX -->
            <div class="bg-white hover:bg-indigo-50/20 rounded-2xl p-6 border border-slate-200 hover:border-indigo-500 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=PABX" class="flex items-center justify-between gap-2 group">
                        <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-extrabold text-xs uppercase">PBX</span>
                        <span class="text-xs font-bold text-slate-400 group-hover:text-indigo-600">&nearr;</span>
                    </a>
                    <h3 class="text-base font-extrabold text-slate-900 mt-3 mb-3">
                        <a href="layanan.php?cat=PABX" class="hover:text-indigo-600 transition-colors">PBX System</a>
                    </h3>
                    <ul class="space-y-2 text-xs text-slate-700 font-semibold">
                        <li>
                            <a href="detail-layanan.php?slug=analogue-pbx" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-indigo-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-indigo-600 transition">
                                <span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> ANALOGUE PBX</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=hybrid-pbx" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-indigo-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-indigo-600 transition">
                                <span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> HYBRID PBX</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=ip-pbx-system" class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-50 hover:bg-indigo-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-indigo-600 transition">
                                <span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> IP – PBX</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="layanan.php?cat=PABX" class="flex items-center justify-between text-[11px] font-bold text-indigo-600 hover:underline">
                        <span>Lihat Kategori PBX</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 4. Telecommunication -->
            <div class="bg-white hover:bg-amber-50/20 rounded-2xl p-6 border border-slate-200 hover:border-amber-500 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=TOWER" class="flex items-center justify-between gap-2 group">
                        <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 font-extrabold text-xs uppercase">Telecommunication</span>
                        <span class="text-xs font-bold text-slate-400 group-hover:text-amber-600">&nearr;</span>
                    </a>
                    <h3 class="text-base font-extrabold text-slate-900 mt-3 mb-3">
                        <a href="layanan.php?cat=TOWER" class="hover:text-amber-600 transition-colors">Telecommunication</a>
                    </h3>
                    <ul class="space-y-1.5 text-xs text-slate-700 font-semibold">
                        <li>
                            <a href="detail-layanan.php?slug=lightning-protector" class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-slate-50 hover:bg-amber-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-amber-600 transition">
                                <span class="truncate">✓ LIGHTNING PROTECTOR</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=handy-talky" class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-slate-50 hover:bg-amber-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-amber-600 transition">
                                <span class="truncate">✓ Handy Talky</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=conference-system" class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-slate-50 hover:bg-amber-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-amber-600 transition">
                                <span class="truncate">✓ CONFERENCE SYSTEM</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=guy-wire-tower" class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-slate-50 hover:bg-amber-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-amber-600 transition">
                                <span class="truncate">✓ Guy Wire Tower</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                        <li>
                            <a href="detail-layanan.php?slug=self-supporting-tower-monopole" class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-slate-50 hover:bg-amber-600 text-slate-800 hover:text-white border border-slate-200/80 hover:border-amber-600 transition">
                                <span class="truncate">✓ SELF SUPPORTING TOWER MONOPOLE</span>
                                <span>&nearr;</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="layanan.php?cat=TOWER" class="flex items-center justify-between text-[11px] font-bold text-amber-600 hover:underline">
                        <span>Lihat Telecommunication</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Internet Service Provider: Legalitas & 3 Segmen (Dapat Diklik) -->
        <div class="bg-slate-900 text-white rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-lg space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6 pb-6 border-b border-slate-800">
                <div class="space-y-2 max-w-xl">
                    <a href="layanan.php?cat=ISP" class="px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase tracking-wider inline-block hover:bg-emerald-500/25 transition">
                        Internet Service Provider &nearr;
                    </a>
                    <h3 class="text-2xl font-extrabold tracking-tight">
                        <a href="layanan.php?cat=ISP" class="hover:text-blue-400 transition-colors">Internet Service Provider (ISP) Resmi PT. KGS</a>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Koneksi internet berkecepatan tinggi melalui jaringan Fiber Optic dan Wireless Point-to-Point untuk 3 segmen utama: Internet Dedicated, Internet Bisnis, dan Home Internet.
                    </p>
                </div>

                <!-- Legalitas -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-5 lg:max-w-xl w-full space-y-2">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-amber-300">📜 Legalitas :</div>
                    <ul class="space-y-1.5 text-xs text-slate-200 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold shrink-0">–</span>
                            <span>Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020,</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold shrink-0">–</span>
                            <span>Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020,</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold shrink-0">–</span>
                            <span>NIB (Nomor Induk Berusaha) ; 9120202100598.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 3 Segmen -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Internet Dedicated -->
                <div class="bg-slate-800/80 border border-slate-700 hover:border-blue-500 rounded-2xl p-5 flex flex-col justify-between transition">
                    <div>
                        <span class="text-[11px] font-bold text-blue-300 uppercase">Segmen 1</span>
                        <h4 class="text-lg font-extrabold text-white mt-0.5 mb-3">
                            <a href="detail-layanan.php?slug=internet-dedicated" class="hover:text-blue-400 transition-colors">Internet Dedicated &nearr;</a>
                        </h4>
                        <div class="space-y-2 text-xs">
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-extrabold text-white block">10 Mbps</span>
                                    <span class="text-[11px] text-emerald-300">Kebutuhan UMKM</span>
                                </div>
                                <span class="font-bold text-blue-300 text-right">Harga Perbulan mulai 3.500.000</span>
                            </div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-extrabold text-white block">10 Mbps</span>
                                    <span class="text-[11px] text-emerald-300">Kebutuhan Sekolah</span>
                                </div>
                                <span class="font-bold text-blue-300 text-right">Harga Perbulan mulai 1.750.000</span>
                            </div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-extrabold text-white block">10 Mbps</span>
                                    <span class="text-[11px] text-emerald-300">Kebutuhan Corporate</span>
                                </div>
                                <span class="font-bold text-blue-300 text-right">Harga Perbulan mulai 1.750.000</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <a href="detail-layanan.php?slug=internet-dedicated" class="block text-center py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition">Detail Layanan</a>
                        <a href="kontak.php?layanan=Internet+Dedicated#konsultasi" class="block text-center py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition">Pesan Sekarang &rarr;</a>
                    </div>
                </div>

                <!-- Internet Bisnis -->
                <div class="bg-slate-800/80 border border-slate-700 hover:border-blue-500 rounded-2xl p-5 flex flex-col justify-between transition">
                    <div>
                        <span class="text-[11px] font-bold text-blue-300 uppercase">Segmen 2</span>
                        <h4 class="text-lg font-extrabold text-white mt-0.5 mb-3">
                            <a href="detail-layanan.php?slug=internet-bisnis" class="hover:text-blue-400 transition-colors">Internet Bisnis &nearr;</a>
                        </h4>
                        <div class="space-y-1.5 text-xs">
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">10 Mbps</span><span class="font-bold text-blue-300">Rp. 407.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">20 Mbps</span><span class="font-bold text-blue-300">Rp. 431.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">30 Mbps</span><span class="font-bold text-blue-300">Rp. 453.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">40 Mbps</span><span class="font-bold text-blue-300">Rp. 477.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">50 Mbps</span><span class="font-bold text-blue-300">Rp. 511.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">100 Mbps</span><span class="font-bold text-blue-300">Rp. 1.050.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex justify-between"><span class="font-bold text-white">200 Mbps</span><span class="font-bold text-blue-300">Rp. 1.560.000 / bulan</span></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <a href="detail-layanan.php?slug=internet-bisnis" class="block text-center py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition">Detail Layanan</a>
                        <a href="kontak.php?layanan=Internet+Bisnis#konsultasi" class="block text-center py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition">Pesan Sekarang &rarr;</a>
                    </div>
                </div>

                <!-- Home Internet -->
                <div class="bg-slate-800/80 border border-slate-700 hover:border-blue-500 rounded-2xl p-5 flex flex-col justify-between transition">
                    <div>
                        <span class="text-[11px] font-bold text-blue-300 uppercase">Segmen 3</span>
                        <h4 class="text-lg font-extrabold text-white mt-0.5 mb-3">
                            <a href="detail-layanan.php?slug=home-internet" class="hover:text-blue-400 transition-colors">Home Internet &nearr;</a>
                        </h4>
                        <div class="space-y-1.5 text-xs">
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">10 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 5 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 252.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">20 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 7 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 276.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">30 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 9 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 298.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">50 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 12 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 450.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">100 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 15 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 710.000 / bulan</span></div>
                            <div class="bg-slate-900/90 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center justify-between gap-2"><div><span class="font-bold text-white block">200 Mbps</span><span class="text-[10px] text-emerald-300">Optiman di 20 Perangkat</span></div><span class="font-bold text-blue-300">Rp. 1.100.000 / bulan</span></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <a href="detail-layanan.php?slug=home-internet" class="block text-center py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition">Detail Layanan</a>
                        <a href="kontak.php?layanan=Home+Internet#konsultasi" class="block text-center py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition">Pesan Sekarang &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CLIENT Portofolio & Pengalaman Lapangan -->
<section class="py-16 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
            <div class="max-w-4xl space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-blue-400 font-bold text-xs uppercase tracking-widest" data-live-setting="home_portofolio_kicker"><?= e(get_setting($pdo, 'home_portofolio_kicker', 'CLIENT Portofolio & Pengalaman Lapangan')) ?></span>
                    <span class="text-slate-500">·</span>
                    <span class="text-xs font-semibold text-emerald-400">Rekam Jejak Nyata &amp; Klien Terdaftar</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold" data-live-setting="home_portofolio_title"><?= e(get_setting($pdo, 'home_portofolio_title', 'Portofolio Proyek & Klien Korporasi')) ?></h2>
                <div class="space-y-2.5 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    <p>
                        Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.
                    </p>
                    <p>
                        Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="portofolio.php" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition">Lihat Portofolio &rarr;</a>
                <a href="klien.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-bold transition">Daftar Klien &rarr;</a>
            </div>
        </div>

        <?php if (!empty($recent_projects)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <?php foreach ($recent_projects as $prj): ?>
                    <div class="bg-slate-800 rounded-2xl overflow-hidden border border-slate-700 flex flex-col justify-between">
                        <div>
                            <?php if (!empty($prj['image_url'])): ?>
                                <div class="hero-slide aspect-16/9 bg-slate-950 overflow-hidden">
                                    <img src="<?= e($prj['image_url']) ?>" alt="" aria-hidden="true" class="hero-slide-bg hidden" loading="lazy" decoding="async">
                                    <img src="<?= e($prj['image_url']) ?>" alt="<?= e($prj['title']) ?>" width="800" height="450" loading="lazy" decoding="async" data-live-img="project_image:<?= (int)$prj['id'] ?>" class="hero-slide-image w-full h-full object-cover object-center">
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
                    <div class="font-bold text-slate-900" data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088')) ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-1">WhatsApp &amp; Email Resmi</div>
                    <div class="font-bold text-emerald-700">WA: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></div>
                    <div class="text-slate-600" data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@creative.co.id')) ?></div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="kontak.php#konsultasi" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">Kirim Permintaan Penawaran</a>
                <a href="https://wa.me/<?= e($wa_clean) ?>?text=<?= rawurlencode('Halo Tim PT. KGS, saya ingin berkonsultasi mengenai kebutuhan layanan perusahaan.') ?>" target="_blank" rel="noopener noreferrer" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">Chat WhatsApp Sekarang</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-slide {
    position: relative;
    width: 100%;
    overflow: hidden;
    background: #0f172a;
    isolation: isolate;
}
.hero-slide-image {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    object-position: center;
}
.hero-slide.is-portrait .hero-slide-bg {
    display: block !important;
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: blur(18px);
    transform: scale(1.1);
    opacity: 0.72;
    z-index: 1;
}
.hero-slide.is-portrait .hero-slide-image {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    object-fit: contain !important;
    object-position: center !important;
}
</style>

<script>
(function() {
    // 1. Deteksi Otomatis Gambar Portrait di dalam Frame Landscape (.hero-slide)
    function evaluateSlideImages() {
        var slides = document.querySelectorAll('.hero-slide');
        slides.forEach(function(container) {
            var mainImg = container.querySelector('.hero-slide-image');
            if (!mainImg) return;
            function checkRatio() {
                if (mainImg.naturalWidth > 0 && mainImg.naturalHeight > 0) {
                    if (mainImg.naturalHeight > mainImg.naturalWidth * 1.05) {
                        container.classList.add('is-portrait');
                    } else {
                        container.classList.remove('is-portrait');
                    }
                }
            }
            if (mainImg.complete) {
                checkRatio();
            } else {
                mainImg.addEventListener('load', checkRatio);
            }
        });
    }
    evaluateSlideImages();
    window.addEventListener('load', evaluateSlideImages);

    // 2. Featured Landscape Slider di atas "Why Choose Us?"
    var featItems = document.querySelectorAll('.featured-infra-slide-item');
    var featDots = document.querySelectorAll('.featured-infra-dot');
    var featPrev = document.getElementById('featured-infra-prev');
    var featNext = document.getElementById('featured-infra-next');
    if (featItems.length > 1) {
        var featIdx = 0;
        var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function showFeatSlide(index) {
            featIdx = (index + featItems.length) % featItems.length;
            featItems.forEach(function(el, i) {
                if (i === featIdx) {
                    el.classList.remove('opacity-0', 'pointer-events-none', 'z-0');
                    el.classList.add('opacity-100', 'z-10');
                } else {
                    el.classList.remove('opacity-100', 'z-10');
                    el.classList.add('opacity-0', 'pointer-events-none', 'z-0');
                }
            });
            featDots.forEach(function(dot, i) {
                if (i === featIdx) {
                    dot.className = 'featured-infra-dot h-2 rounded-full transition-all duration-300 cursor-pointer w-7 bg-blue-500';
                } else {
                    dot.className = 'featured-infra-dot h-2 rounded-full transition-all duration-300 cursor-pointer w-2 bg-white/40 hover:bg-white/70';
                }
            });
        }
        if (featPrev) featPrev.addEventListener('click', function() { showFeatSlide(featIdx - 1); });
        if (featNext) featNext.addEventListener('click', function() { showFeatSlide(featIdx + 1); });
        featDots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var idx = parseInt(dot.getAttribute('data-feat-dot') || '0', 10);
                showFeatSlide(idx);
            });
        });
        if (!prefersReducedMotion) {
            setInterval(function() {
                showFeatSlide(featIdx + 1);
            }, 6000);
        }
    }
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
