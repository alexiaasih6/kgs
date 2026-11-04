<?php
/**
 * Halaman Detail Slide Show Kategori (`slideshow-detail.php?slug=...`)
 * Menampilkan galeri multi-gambar interaktif (Carousel, Thumbnail Strip, Lightbox Fullscreen)
 * serta penjelasan teknis lengkap untuk setiap kategori infrastruktur PT. Kreatif Global Solusindo.
 */
require_once __DIR__ . '/config/database.php';

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$category = $slug !== '' ? get_slideshow_category_by_slug($pdo, $slug, true) : null;
$all_categories = get_slideshow_categories($pdo, true);

if (!$category) {
    http_response_code(404);
    $page_title = 'Kategori Slide Show Tidak Ditemukan (404) - PT. KGS';
    $meta_description = 'Halaman kategori Slide Show yang Anda cari tidak ditemukan atau sedang dinonaktifkan.';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <main id="main-content" class="bg-slate-50 dark:bg-slate-950 min-h-[70vh] py-16 lg:py-24 transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 sm:p-12 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-3xl mx-auto mb-5">
                    ⚠️
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs font-extrabold uppercase tracking-wider mb-3">
                    Error 404 • Halaman Tidak Ditemukan
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mb-3">
                    Kategori Slide Show Tidak Tersedia
                </h1>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base max-w-xl mx-auto mb-8 leading-relaxed">
                    Maaf, kategori Slide Show dengan tautan <code class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-red-600 dark:text-red-400 font-mono text-xs"><?= e($slug ?: '(kosong)') ?></code> tidak ditemukan atau sedang dinonaktifkan.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                    <a href="slideshow.php" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition">
                        ← Kembali ke Daftar Slide Show
                    </a>
                    <a href="index.php" class="px-5 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm transition">
                        Ke Beranda Utama
                    </a>
                </div>

                <?php if (!empty($all_categories)): ?>
                    <div class="pt-8 border-t border-slate-200 dark:border-slate-800 text-left">
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 text-center">
                            Pilih Kategori Slide Show yang Tersedia:
                        </h2>
                        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <?php foreach ($all_categories as $cat_item): ?>
                                <a href="slideshow-detail.php?slug=<?= rawurlencode((string)$cat_item['slug']) ?>"
                                   class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 hover:border-blue-500 text-xs font-bold text-slate-800 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center justify-between gap-2">
                                    <span class="truncate"><?= e($cat_item['title']) ?></span>
                                    <span class="text-blue-600 dark:text-blue-400 shrink-0">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Data Kategori Valid
$images = $category['images'] ?? [];
if (empty($images) && !empty($category['cover_image'])) {
    $images = [[
        'id'          => 0,
        'category_id' => (int)$category['id'],
        'image_path'  => (string)$category['cover_image'],
        'caption'     => (string)$category['title'],
        'alt_text'    => (string)$category['title'],
        'sort_order'  => 1,
        'is_primary'  => 1,
    ]];
}

$total_slides = count($images);
$first_image = $images[0] ?? [
    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
    'caption'    => $category['title'],
    'alt_text'   => $category['title'],
];

$page_title = $category['title'] . ' - Slide Show Infrastruktur PT. KGS';
$meta_description = !empty($category['short_description'])
    ? $category['short_description']
    : mb_substr(strip_tags((string)$category['full_description']), 0, 160);
$og_image_override = $first_image['image_path'];

// Kategori lainnya untuk navigasi bawah
$related_categories = array_values(array_filter($all_categories, function ($c) use ($category) {
    return (string)$c['slug'] !== (string)$category['slug'];
}));

// Siapkan payload JSON gambar yang aman untuk Carousel & Lightbox JS
$js_slides = [];
foreach ($images as $idx => $img) {
    $js_slides[] = [
        'index'   => $idx,
        'src'     => (string)($img['image_path'] ?? ''),
        'caption' => (string)($img['caption'] ?? $category['title']),
        'alt'     => (string)($img['alt_text'] ?? $category['title']),
    ];
}

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content" class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <!-- Top Banner & Breadcrumb -->
    <section class="relative bg-slate-900 text-white py-12 lg:py-16 overflow-hidden border-b border-slate-800">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <nav aria-label="Breadcrumb" class="text-xs sm:text-sm text-slate-400 flex flex-wrap items-center gap-2">
                    <a href="index.php" class="hover:text-white transition">Beranda</a>
                    <span>/</span>
                    <a href="slideshow.php" class="hover:text-white transition">Slide Show</a>
                    <span>/</span>
                    <span class="text-blue-400 font-semibold"><?= e($category['title']) ?></span>
                </nav>

                <a href="slideshow.php"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 text-xs font-bold border border-slate-700 transition">
                    <span>← Kembali ke Daftar Slide Show</span>
                </a>
            </div>

            <div class="max-w-4xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold uppercase tracking-wider mb-3">
                    <span>🖼️ Galeri Slide Show • <?= $total_slides ?> Gambar Tersedia</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight mb-3">
                    <?= e($category['title']) ?>
                </h1>
                <?php if (!empty($category['short_description'])): ?>
                    <p class="text-slate-300 text-sm sm:text-lg leading-relaxed">
                        <?= e($category['short_description']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Bar Pindah Cepat Antar Kategori -->
            <?php if (count($all_categories) > 1): ?>
                <div class="mt-7 pt-5 border-t border-slate-800/90 flex items-center gap-2 overflow-x-auto pb-1">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 shrink-0 mr-1">Kategori:</span>
                    <?php foreach ($all_categories as $cat_nav):
                        $is_curr = ((string)$cat_nav['slug'] === (string)$category['slug']);
                    ?>
                        <a href="slideshow-detail.php?slug=<?= rawurlencode((string)$cat_nav['slug']) ?>"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $is_curr ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/80' ?>">
                            <?= e($cat_nav['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Konten Utama: Carousel Multi-Gambar & Penjelasan Teknis -->
    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">

                <!-- Kolom Kiri (7 Kolom): Penampil Slide Show Utama + Thumbnail Strip -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-md">
                        <!-- Toolbar Atas Viewer -->
                        <div class="px-4 sm:px-6 py-3.5 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2 text-white">
                            <div class="flex items-center gap-2.5">
                                <span id="kgs-slide-counter" class="px-3 py-1 rounded-full bg-blue-600 text-white text-xs font-extrabold tracking-wide">
                                    Slide 1 / <?= $total_slides ?>
                                </span>
                                <span class="text-xs text-slate-300 font-semibold hidden sm:inline">
                                    Klik gambar untuk mode layar penuh (Lightbox)
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <?php if ($total_slides > 1): ?>
                                    <button type="button"
                                            id="kgs-slide-autoplay-btn"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition cursor-pointer">
                                        <span id="kgs-slide-autoplay-icon">▶</span>
                                        <span id="kgs-slide-autoplay-label">Putar Otomatis</span>
                                    </button>
                                <?php endif; ?>
                                <button type="button"
                                        id="kgs-slide-fullscreen-btn"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 text-xs font-bold border border-blue-500/30 transition cursor-pointer">
                                    <span>🔍 Perbesar</span>
                                </button>
                            </div>
                        </div>

                        <!-- Area Gambar Utama -->
                        <div class="relative aspect-16/10 bg-slate-950 overflow-hidden group select-none">
                            <img id="kgs-main-slide-img"
                                 src="<?= e($first_image['image_path']) ?>"
                                 alt="<?= e($first_image['alt_text'] ?? $category['title']) ?>"
                                 class="w-full h-full object-cover cursor-zoom-in transition-opacity duration-300">

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent pointer-events-none"></div>

                            <?php if ($total_slides > 1): ?>
                                <!-- Tombol Prev -->
                                <button type="button"
                                        id="kgs-slide-prev-btn"
                                        aria-label="Gambar Sebelumnya"
                                        class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                                    ❮
                                </button>
                                <!-- Tombol Next -->
                                <button type="button"
                                        id="kgs-slide-next-btn"
                                        aria-label="Gambar Berikutnya"
                                        class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                                    ❯
                                </button>
                            <?php endif; ?>

                            <!-- Caption Bar di Bawah Gambar Utama -->
                            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 text-white pointer-events-none">
                                <p id="kgs-main-slide-caption" class="text-sm sm:text-base font-bold text-white leading-snug drop-shadow">
                                    <?= e($first_image['caption'] ?: $category['title']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Strip Thumbnail Multi-Gambar -->
                        <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Daftar Gambar Pada Halaman Ini (<?= $total_slides ?> Slide)
                                </span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Klik thumbnail untuk memilih gambar
                                </span>
                            </div>

                            <div id="kgs-thumbnail-strip" class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2.5">
                                <?php foreach ($images as $idx => $img): ?>
                                    <button type="button"
                                            data-slide-index="<?= $idx ?>"
                                            title="<?= e($img['caption'] ?? $category['title']) ?>"
                                            class="kgs-thumb-btn relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none <?= $idx === 0 ? 'border-blue-600 ring-2 ring-blue-500/40 opacity-100' : 'border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100' ?>">
                                        <img src="<?= e($img['image_path']) ?>"
                                             alt="<?= e($img['alt_text'] ?? $category['title']) ?>"
                                             loading="lazy"
                                             class="w-full h-full object-cover">
                                        <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-bold">
                                            <?= $idx + 1 ?>
                                        </span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan (5 Kolom): Penjelasan Teknis Lengkap & Spesifikasi -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-3">
                            <span>📘 Penjelasan & Spesifikasi Teknis</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <?= e($category['title']) ?>
                        </h2>

                        <div class="prose prose-slate dark:prose-invert max-w-none">
                            <?= format_slideshow_description_html((string)$category['full_description']) ?>
                        </div>

                        <!-- Box Konsultasi Cepat -->
                        <div class="mt-8 pt-6 border-t border-slate-200/80 dark:border-slate-800">
                            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50/70 dark:from-slate-800/90 dark:to-slate-800/50 border border-blue-200/70 dark:border-slate-700">
                                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white mb-1.5">
                                    Tertarik Mengimplementasikan <?= e($category['title']) ?>?
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                    Hubungi tim engineer PT. Kreatif Global Solusindo untuk konsultasi desain sistem, survei lokasi, dan penyusunan Bill of Quantity (BoQ).
                                </p>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <a href="kontak.php?layanan=<?= rawurlencode((string)$category['title']) ?>"
                                       class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                                        Minta Penawaran <?= e($category['title']) ?>
                                    </a>
                                    <a href="layanan.php"
                                       class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition">
                                        Katalog Product & Service
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bagian Bawah: Kategori Slide Show Infrastruktur Lainnya -->
            <?php if (!empty($related_categories)): ?>
                <div class="mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">
                                Eksplorasi Solusi Lainnya
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Kategori Slide Show Infrastruktur Lainnya
                            </h2>
                        </div>
                        <a href="slideshow.php" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat Semua Kategori (<?= count($all_categories) ?>) →
                        </a>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($related_categories as $rel):
                            $rel_imgs = $rel['images'] ?? [];
                            $rel_count = count($rel_imgs);
                            $rel_cover = !empty($rel['cover_image']) ? $rel['cover_image'] : (!empty($rel_imgs[0]['image_path']) ? $rel_imgs[0]['image_path'] : '');
                        ?>
                            <a href="slideshow-detail.php?slug=<?= rawurlencode((string)$rel['slug']) ?>"
                               class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs hover:shadow-lg hover:border-blue-500/50 transition flex flex-col">
                                <div class="relative aspect-16/9 bg-slate-900 overflow-hidden">
                                    <img src="<?= e($rel_cover) ?>"
                                         alt="<?= e($rel['title']) ?>"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-transparent"></div>
                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-blue-600/95 text-white text-[11px] font-bold">
                                        🖼️ <?= $rel_count ?> Gambar
                                    </span>
                                    <div class="absolute bottom-3 left-3.5 right-3.5">
                                        <h3 class="text-base font-extrabold text-white group-hover:text-blue-300 transition">
                                            <?= e($rel['title']) ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex items-center justify-between gap-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
                                        <?= e($rel['short_description'] ?: $rel['title']) ?>
                                    </span>
                                    <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400 shrink-0">
                                        Buka →
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Modal Lightbox Fullscreen -->
<div id="kgs-lightbox-modal"
     class="fixed inset-0 z-50 hidden bg-slate-950/95 backdrop-blur-md flex-col justify-between p-4 sm:p-6 select-none"
     role="dialog"
     aria-modal="true"
     aria-label="Penampil Gambar Layar Penuh">
    <!-- Header Lightbox -->
    <div class="flex items-center justify-between gap-4 text-white max-w-6xl w-full mx-auto">
        <div class="flex items-center gap-3">
            <span id="kgs-lightbox-counter" class="px-3 py-1 rounded-full bg-blue-600 text-xs font-extrabold">
                1 / <?= $total_slides ?>
            </span>
            <span class="text-sm font-bold text-slate-200 truncate max-w-md">
                <?= e($category['title']) ?>
            </span>
        </div>
        <button type="button"
                id="kgs-lightbox-close-btn"
                aria-label="Tutup Layar Penuh"
                class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-red-600 text-white text-xs font-bold border border-slate-700 transition cursor-pointer">
            ✕ Tutup (ESC)
        </button>
    </div>

    <!-- Gambar Tengah Lightbox -->
    <div class="relative flex-1 flex items-center justify-center my-4 max-w-6xl w-full mx-auto overflow-hidden">
        <?php if ($total_slides > 1): ?>
            <button type="button"
                    id="kgs-lightbox-prev-btn"
                    aria-label="Slide Sebelumnya"
                    class="absolute left-2 sm:left-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                ❮
            </button>
        <?php endif; ?>

        <img id="kgs-lightbox-img"
             src="<?= e($first_image['image_path']) ?>"
             alt="<?= e($first_image['alt_text'] ?? $category['title']) ?>"
             class="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl">

        <?php if ($total_slides > 1): ?>
            <button type="button"
                    id="kgs-lightbox-next-btn"
                    aria-label="Slide Berikutnya"
                    class="absolute right-2 sm:right-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer">
                ❯
            </button>
        <?php endif; ?>
    </div>

    <!-- Caption Bawah Lightbox -->
    <div class="max-w-4xl w-full mx-auto text-center pb-2">
        <p id="kgs-lightbox-caption" class="text-sm sm:text-base font-semibold text-slate-200">
            <?= e($first_image['caption'] ?: $category['title']) ?>
        </p>
    </div>
</div>

<script>
(function () {
    var slides = <?= json_encode($js_slides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    if (!slides || !slides.length) return;

    var currentIndex = 0;
    var autoPlayTimer = null;

    var mainImg = document.getElementById('kgs-main-slide-img');
    var mainCaption = document.getElementById('kgs-main-slide-caption');
    var counterEl = document.getElementById('kgs-slide-counter');
    var prevBtn = document.getElementById('kgs-slide-prev-btn');
    var nextBtn = document.getElementById('kgs-slide-next-btn');
    var autoPlayBtn = document.getElementById('kgs-slide-autoplay-btn');
    var autoPlayIcon = document.getElementById('kgs-slide-autoplay-icon');
    var autoPlayLabel = document.getElementById('kgs-slide-autoplay-label');
    var fullscreenBtn = document.getElementById('kgs-slide-fullscreen-btn');
    var thumbBtns = document.querySelectorAll('.kgs-thumb-btn');

    var lightboxModal = document.getElementById('kgs-lightbox-modal');
    var lightboxImg = document.getElementById('kgs-lightbox-img');
    var lightboxCaption = document.getElementById('kgs-lightbox-caption');
    var lightboxCounter = document.getElementById('kgs-lightbox-counter');
    var lightboxCloseBtn = document.getElementById('kgs-lightbox-close-btn');
    var lightboxPrevBtn = document.getElementById('kgs-lightbox-prev-btn');
    var lightboxNextBtn = document.getElementById('kgs-lightbox-next-btn');

    function renderSlide(idx) {
        if (idx < 0) idx = slides.length - 1;
        if (idx >= slides.length) idx = 0;
        currentIndex = idx;
        var item = slides[currentIndex];

        if (mainImg) {
            mainImg.src = item.src;
            mainImg.alt = item.alt || item.caption || '';
        }
        if (mainCaption) {
            mainCaption.textContent = item.caption || '';
        }
        if (counterEl) {
            counterEl.textContent = 'Slide ' + (currentIndex + 1) + ' / ' + slides.length;
        }
        if (lightboxImg) {
            lightboxImg.src = item.src;
            lightboxImg.alt = item.alt || item.caption || '';
        }
        if (lightboxCaption) {
            lightboxCaption.textContent = item.caption || '';
        }
        if (lightboxCounter) {
            lightboxCounter.textContent = (currentIndex + 1) + ' / ' + slides.length;
        }

        thumbBtns.forEach(function (btn, bIdx) {
            if (bIdx === currentIndex) {
                btn.className = 'kgs-thumb-btn relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none border-blue-600 ring-2 ring-blue-500/40 opacity-100';
            } else {
                btn.className = 'kgs-thumb-btn relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100';
            }
        });
    }

    function toggleAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
            if (autoPlayIcon) autoPlayIcon.textContent = '▶';
            if (autoPlayLabel) autoPlayLabel.textContent = 'Putar Otomatis';
        } else {
            autoPlayTimer = setInterval(function () {
                renderSlide(currentIndex + 1);
            }, 4000);
            if (autoPlayIcon) autoPlayIcon.textContent = '⏸';
            if (autoPlayLabel) autoPlayLabel.textContent = 'Jeda Otomatis';
        }
    }

    function openLightbox() {
        if (!lightboxModal) return;
        lightboxModal.classList.remove('hidden');
        lightboxModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightboxModal) return;
        lightboxModal.classList.add('hidden');
        lightboxModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { renderSlide(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { renderSlide(currentIndex + 1); });
    if (autoPlayBtn) autoPlayBtn.addEventListener('click', toggleAutoPlay);
    if (fullscreenBtn) fullscreenBtn.addEventListener('click', openLightbox);
    if (mainImg) mainImg.addEventListener('click', openLightbox);

    thumbBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idx = parseInt(btn.getAttribute('data-slide-index') || '0', 10);
            renderSlide(idx);
        });
    });

    if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
    if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', function () { renderSlide(currentIndex - 1); });
    if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', function () { renderSlide(currentIndex + 1); });
    if (lightboxModal) {
        lightboxModal.addEventListener('click', function (e) {
            if (e.target === lightboxModal) closeLightbox();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!lightboxModal || lightboxModal.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') renderSlide(currentIndex - 1);
        if (e.key === 'ArrowRight') renderSlide(currentIndex + 1);
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
