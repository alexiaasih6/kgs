<?php
require_once __DIR__ . '/config/database.php';

$raw_query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$q = clean_plain_text($raw_query, 120);

$page_title = $q !== '' ? 'Hasil Pencarian: ' . $q : 'Pencarian Layanan, Artikel & Informasi';
$page_desc  = 'Cari layanan infrastruktur IT, jaringan KGSNet, fabrikasi tower, CCTV, paket harga B2B, portofolio proyek, dan artikel teknis PT. Kreatif Global Solusindo.';

$results_services = [];
$results_posts    = [];
$results_pricing  = [];
$results_projects = [];
$results_faqs     = [];
$results_pages    = [];
$total_results    = 0;

if ($q !== '' && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';

    // 1. Cari di Layanan (services)
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, c.name AS category_name
            FROM services s
            LEFT JOIN service_categories c ON s.category_id = c.id
            WHERE s.is_published = 1
              AND (s.name LIKE ? OR s.summary LIKE ? OR s.description LIKE ?)
            ORDER BY s.sort_order ASC, s.id ASC
            LIMIT 12
        ");
        $stmt->execute([$like, $like, $like]);
        $results_services = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_services = [];
    }

    // 2. Cari di Artikel & Berita (posts)
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name
            FROM posts p
            LEFT JOIN blog_categories c ON p.category_id = c.id
            WHERE p.status = 'published'
              AND (p.title LIKE ? OR p.summary LIKE ? OR p.content LIKE ? OR p.labels LIKE ?)
            ORDER BY p.created_at DESC
            LIMIT 9
        ");
        $stmt->execute([$like, $like, $like, $like]);
        $results_posts = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_posts = [];
    }

    // 3. Cari di Paket Harga B2B (pricing_packages)
    try {
        ensure_pricing_packages_table($pdo);
        $stmt = $pdo->prepare("
            SELECT *
            FROM pricing_packages
            WHERE is_active = 1
              AND (name LIKE ? OR subtitle LIKE ? OR features LIKE ? OR target_sector LIKE ?)
            ORDER BY sort_order ASC, id ASC
            LIMIT 6
        ");
        $stmt->execute([$like, $like, $like, $like]);
        $results_pricing = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_pricing = [];
    }

    // 4. Cari di Portofolio Proyek (projects)
    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM projects
            WHERE title LIKE ? OR client_or_location LIKE ? OR category LIKE ? OR description LIKE ?
            ORDER BY id DESC
            LIMIT 9
        ");
        $stmt->execute([$like, $like, $like, $like]);
        $results_projects = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_projects = [];
    }

    // 5. Cari di Tanya Jawab / FAQ (faqs)
    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM faqs
            WHERE is_active = 1
              AND (question LIKE ? OR answer LIKE ? OR category LIKE ?)
            ORDER BY sort_order ASC, id ASC
            LIMIT 8
        ");
        $stmt->execute([$like, $like, $like]);
        $results_faqs = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_faqs = [];
    }

    // 6. Cari di Halaman Website (pages)
    try {
        ensure_pages_complete($pdo);
        $stmt = $pdo->prepare("
            SELECT *
            FROM pages
            WHERE is_published = 1
              AND (title LIKE ? OR summary LIKE ? OR content LIKE ?)
            ORDER BY sort_order ASC, id ASC
            LIMIT 6
        ");
        $stmt->execute([$like, $like, $like]);
        $results_pages = $stmt->fetchAll();
    } catch (Exception $e) {
        $results_pages = [];
    }

    $total_results = count($results_services)
        + count($results_posts)
        + count($results_pricing)
        + count($results_projects)
        + count($results_faqs)
        + count($results_pages);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Search Hero Section -->
<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-400 font-mono text-xs uppercase tracking-widest block mb-2">Pencarian Cepat Website PT. KGS</span>
        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight mb-4">
            <?= $q !== '' ? 'Hasil Pencarian untuk "' . e($q) . '"' : 'Cari Layanan, Solusi Teknis & Informasi' ?>
        </h1>

        <form action="cari.php" method="GET" class="mt-5 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    value="<?= e($q) ?>"
                    placeholder="Ketik kata kunci: Fiber Optik, KGSNet, Tower, Penangkal Petir, CCTV, SLA..."
                    required
                    minlength="2"
                    class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-slate-800/90 border border-slate-700 text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <button type="submit" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition cursor-pointer shrink-0">
                Cari Sekarang
            </button>
        </form>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-400">
            <span>Kata kunci populer:</span>
            <?php foreach (['Fiber Optik', 'KGSNet', 'Tower Triangle', 'Penangkal Petir', 'CCTV', 'Grounding', 'AIS Kapal'] as $kw): ?>
                <a href="cari.php?q=<?= urlencode($kw) ?>" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                    <?= e($kw) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-12 bg-slate-50 dark:bg-slate-950 flex-1">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <?php if ($q === ''): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center">
                <p class="text-slate-600 dark:text-slate-300 text-sm">
                    Silakan masukkan minimal 2 karakter pada kolom pencarian di atas untuk mencari layanan, paket harga B2B, portofolio proyek, FAQ, atau artikel teknis.
                </p>
            </div>
        <?php elseif (mb_strlen($q) < 2): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-6 text-sm font-medium">
                Kata kunci pencarian minimal terdiri dari 2 karakter.
            </div>
        <?php elseif ($total_results === 0): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-10 text-center space-y-4">
                <div class="text-lg font-bold text-slate-900 dark:text-white">
                    Tidak ditemukan hasil untuk kata kunci "<?= e($q) ?>"
                </div>
                <p class="text-xs text-slate-500 max-w-lg mx-auto">
                    Coba gunakan kata kunci yang lebih umum seperti <strong>Internet</strong>, <strong>Tower</strong>, <strong>CCTV</strong>, <strong>Petir</strong>, atau hubungi tim engineer kami untuk konsultasi langsung.
                </p>
                <div class="pt-2 flex justify-center gap-3">
                    <a href="layanan.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition">Lihat Katalog Layanan</a>
                    <a href="kontak.php" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">Hubungi Tim KGS</a>
                </div>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Ditemukan <span class="text-blue-600 font-extrabold"><?= (int)$total_results ?></span> hasil pencarian yang relevan untuk <span class="font-bold">"<?= e($q) ?>"</span>
                </p>
                <a href="cari.php" class="text-xs text-slate-500 hover:text-blue-600 font-semibold">Reset Pencarian</a>
            </div>

            <!-- 1. Hasil Layanan & Solusi -->
            <?php if (!empty($results_services)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-md text-xs"><?= count($results_services) ?></span>
                    <span>Katalog Layanan & Solusi Teknis</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($results_services as $srv): ?>
                        <a href="detail-layanan.php?slug=<?= e($srv['slug']) ?>" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block mb-1"><?= e($srv['category_name'] ?? 'Layanan KGS') ?></span>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1.5"><?= e($srv['name']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2"><?= e($srv['summary']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. Hasil Paket Harga B2B -->
            <?php if (!empty($results_pricing)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-md text-xs"><?= count($results_pricing) ?></span>
                    <span>Paket Harga & Solusi Bisnis (B2B)</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($results_pricing as $pkg): ?>
                        <a href="harga.php" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded"><?= e($pkg['code_label'] ?? 'B2B') ?></span>
                                <span class="text-xs font-extrabold text-blue-600"><?= e($pkg['price_label'] ?? '') ?></span>
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1"><?= e($pkg['name']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2"><?= e($pkg['subtitle'] ?? $pkg['tagline'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. Hasil Artikel & Berita -->
            <?php if (!empty($results_posts)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 rounded-md text-xs"><?= count($results_posts) ?></span>
                    <span>Artikel & Wawasan Teknis</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($results_posts as $post): ?>
                        <a href="detail-blog.php?slug=<?= e($post['slug']) ?>" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <span class="text-[11px] font-bold text-indigo-600 block mb-1"><?= e($post['category_name'] ?? 'Artikel Teknis') ?></span>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1.5"><?= e($post['title']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2"><?= e($post['summary']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4. Hasil Portofolio Proyek -->
            <?php if (!empty($results_projects)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-md text-xs"><?= count($results_projects) ?></span>
                    <span>Dokumentasi Proyek & Portofolio</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($results_projects as $proj): ?>
                        <a href="portofolio.php" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <span class="text-[11px] font-bold text-amber-700 block mb-1"><?= e($proj['category'] ?? 'Proyek KGS') ?> — <?= e($proj['client_or_location'] ?? '') ?></span>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1"><?= e($proj['title']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2"><?= e($proj['description'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 5. Hasil FAQ -->
            <?php if (!empty($results_faqs)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 rounded-md text-xs"><?= count($results_faqs) ?></span>
                    <span>Tanya Jawab Teknis (FAQ)</span>
                </h2>
                <div class="space-y-3">
                    <?php foreach ($results_faqs as $faq): ?>
                        <a href="faq.php" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1"><?= e($faq['question']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400"><?= e($faq['answer']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 6. Hasil Halaman Website -->
            <?php if (!empty($results_pages)): ?>
            <div class="space-y-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-slate-200 text-slate-800 rounded-md text-xs"><?= count($results_pages) ?></span>
                    <span>Halaman Informasi Website</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($results_pages as $pg):
                        $pg_url = resolve_page_slug_to_url((string)$pg['slug']);
                    ?>
                        <a href="<?= e($pg_url) ?>" class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition shadow-xs block">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1"><?= e($pg['title']) ?></h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2"><?= e($pg['summary'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
