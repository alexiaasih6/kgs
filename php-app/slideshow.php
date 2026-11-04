<?php
/**
 * Halaman Publik Utama: Slide Show Infrastruktur & Solusi Teknologi
 * Menampilkan grid kartu kategori Slide Show yang dapat diklik menuju halaman detail (`slideshow-detail.php?slug=...`).
 */
require_once __DIR__ . '/config/database.php';

$page_data = get_page_by_slug($pdo, 'slideshow', [
    'title'            => 'Slide Show Infrastruktur & Solusi Teknologi',
    'summary'          => 'Galeri interaktif dan penjelasan teknis mendalam mengenai infrastruktur telekomunikasi, jaringan serat optik, menara komunikasi, proteksi petir, IP-PBX, wireless, dan sistem keamanan akses PT. Kreatif Global Solusindo.',
    'content'          => '',
    'meta_title'       => 'Slide Show Infrastruktur & Teknologi - PT. Kreatif Global Solusindo',
    'meta_description' => 'Galeri Slide Show dokumentasi dan spesifikasi teknis Self Supporting Tower Monopole, Fiber Optik, Guy Wire Tower, Lightning Protector, IP-PBX, Wireless, dan Access Door.'
]);

$page_title = $page_data['meta_title'] ?: $page_data['title'];
$meta_description = $page_data['meta_description'] ?: $page_data['summary'];

$categories = get_slideshow_categories($pdo, true);
$total_images = 0;
foreach ($categories as $cat) {
    $total_images += (int)($cat['image_count'] ?? count($cat['images'] ?? []));
}

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content" class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors">
    <!-- Hero Header Slide Show -->
    <section class="relative bg-slate-900 text-white py-16 lg:py-20 overflow-hidden border-b border-slate-800">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <nav aria-label="Breadcrumb" class="mb-5 text-xs sm:text-sm text-slate-400 flex items-center gap-2">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>/</span>
                <span class="text-blue-400 font-semibold">Slide Show Infrastruktur</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-bold uppercase tracking-wider mb-4">
                        <span>🖼️ Galeri Multi-Gambar & Edukasi Teknis</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white mb-4">
                        <?= e($page_data['title']) ?>
                    </h1>
                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                        <?= e($page_data['summary']) ?>
                    </p>
                </div>

                <!-- Ringkasan Statistik Kategori & Foto -->
                <div class="flex items-center gap-3 shrink-0">
                    <div class="px-4 py-3 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-center min-w-[115px]">
                        <div class="text-2xl font-extrabold text-white"><?= count($categories) ?></div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Kategori</div>
                    </div>
                    <div class="px-4 py-3 rounded-2xl bg-blue-600/20 border border-blue-500/40 text-center min-w-[115px]">
                        <div class="text-2xl font-extrabold text-blue-300"><?= $total_images ?></div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-blue-200/80 mt-0.5">Total Slide Foto</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Daftar Kartu Kategori Slide Show -->
    <section class="py-14 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (empty($categories)): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-sm">
                    <div class="text-4xl mb-3">🖼️</div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Belum Ada Kategori Slide Show Aktif</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        Silakan tambahkan atau aktifkan kategori Slide Show melalui panel Admin CMS pada menu <strong>Slide Show / Galeri</strong>.
                    </p>
                </div>
            <?php else: ?>
                <!-- Bar Navigasi Cepat Kategori -->
                <div class="mb-8 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1">Pilih Cepat Halaman:</span>
                    <?php foreach ($categories as $idx => $cat): ?>
                        <a href="slideshow-detail.php?slug=<?= rawurlencode((string)$cat['slug']) ?>"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500 text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 shadow-xs transition">
                            <span class="w-4 h-4 rounded-full bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 text-[10px] font-extrabold flex items-center justify-center"><?= $idx + 1 ?></span>
                            <span><?= e($cat['title']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Grid Kartu Kategori -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7">
                    <?php foreach ($categories as $idx => $cat):
                        $images = $cat['images'] ?? [];
                        $img_count = count($images);
                        $cover = !empty($cat['cover_image']) ? $cat['cover_image'] : (!empty($images[0]['image_path']) ? $images[0]['image_path'] : 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80');
                        $detail_url = 'slideshow-detail.php?slug=' . rawurlencode((string)$cat['slug']);
                    ?>
                        <article class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs hover:shadow-xl hover:border-blue-500/50 dark:hover:border-blue-500/50 transition-all duration-300 flex flex-col">
                            <!-- Cover Image Link -->
                            <a href="<?= e($detail_url) ?>" class="relative aspect-16/10 bg-slate-900 overflow-hidden block">
                                <img src="<?= e($cover) ?>"
                                     alt="<?= e($cat['title']) ?>"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>

                                <!-- Badge Nomor Urut -->
                                <div class="absolute top-3.5 left-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/15">
                                        #<?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </div>

                                <!-- Badge Jumlah Slide Gambar -->
                                <div class="absolute top-3.5 right-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-600/95 backdrop-blur-md text-white text-xs font-extrabold shadow-md">
                                        🖼️ <?= $img_count ?> Gambar
                                    </span>
                                </div>

                                <!-- Judul Kategori di Atas Cover -->
                                <div class="absolute bottom-3.5 left-4 right-4">
                                    <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight leading-snug group-hover:text-blue-300 transition-colors">
                                        <?= e($cat['title']) ?>
                                    </h2>
                                </div>
                            </a>

                            <!-- Konten Kartu -->
                            <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3 mb-4">
                                        <?= e($cat['short_description'] ?: mb_substr(strip_tags((string)$cat['full_description']), 0, 160) . '...') ?>
                                    </p>

                                    <?php if (!empty($images)): ?>
                                        <!-- Preview Mini Thumbnail Strip -->
                                        <div class="mb-5">
                                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
                                                Pratinjau Slide Foto:
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <?php foreach (array_slice($images, 0, 4) as $thumb): ?>
                                                    <a href="<?= e($detail_url) ?>"
                                                       title="<?= e($thumb['caption'] ?? $cat['title']) ?>"
                                                       class="w-12 h-9 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 hover:opacity-90 transition">
                                                        <img src="<?= e($thumb['image_path']) ?>"
                                                             alt="<?= e($thumb['alt_text'] ?? $cat['title']) ?>"
                                                             loading="lazy"
                                                             class="w-full h-full object-cover">
                                                    </a>
                                                <?php endforeach; ?>
                                                <?php if ($img_count > 4): ?>
                                                    <a href="<?= e($detail_url) ?>"
                                                       class="w-12 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs font-extrabold text-blue-600 dark:text-blue-400 shrink-0">
                                                        +<?= $img_count - 4 ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tombol Read More Halaman Slide Show -->
                                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        Halaman Detail & Galeri
                                    </span>
                                    <a href="<?= e($detail_url) ?>"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                                        <span>Read More</span>
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php $why_choose_us = get_why_choose_us_content(); ?>
            <div class="mt-14 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-7 sm:p-10 lg:p-12 shadow-xs">
                <div class="max-w-4xl">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-3">
                        PT. Kreatif Global Solusindo • Creative Com
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-5">
                        <?= e($why_choose_us['title']) ?>
                    </h2>
                    <div class="space-y-4 text-sm sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed">
                        <?php foreach ($why_choose_us['paragraphs'] as $para): ?>
                            <p><?= e($para) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- CTA Konsultasi & Survei Teknis -->
            <div class="mt-14 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-8 sm:p-10 text-white border border-blue-500/20 shadow-xl flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold uppercase tracking-wider mb-3">
                        System Integrator, Security Solution & Internet Provider
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
                        Butuh Spesifikasi, BoQ, atau Survei Lokasi Infrastruktur?
                    </h3>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Tim teknisi berpengalaman PT. Kreatif Global Solusindo (Creative.co.id) siap membantu perencanaan dan implementasi Tower Monopole, Guy Wire Tower, Fiber Optik, Penangkal Petir, IP-PBX, Wireless, dan Access Door.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="kontak.php" class="px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg transition">
                        Minta Penawaran Resmi
                    </a>
                    <a href="layanan.php" class="px-5 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-sm border border-white/15 transition">
                        Lihat Product & Service
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
