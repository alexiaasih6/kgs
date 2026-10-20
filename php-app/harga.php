<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'harga', [
    'title' => 'Pilihan Paket Solusi & Layanan B2B',
    'summary' => 'Struktur paket transparan yang dirancang khusus untuk kebutuhan konektivitas bisnis, keamanan terpadu, infrastruktur remote site, dan pengadaan proyek pemerintah/BUMN dengan SLA terjamin.',
    'meta_title' => 'Pilihan Paket & Solusi Bisnis (B2B) - PT. KGS',
    'meta_description' => 'Daftar paket solusi Dedicated Internet KGSNet, CCTV Surveillance, Tower Guywire, dan infrastruktur IT PT. Kreatif Global Solusindo.'
]);

$page_title = $page_data['meta_title'] ?: $page_data['title'];
$page_desc = $page_data['meta_description'] ?: $page_data['summary'];
require_once __DIR__ . '/includes/header.php';

// Ambil daftar paket harga dinamis dari tabel database `pricing_packages`
$pricing_packages = get_pricing_packages($pdo, true);
$wa_clean = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'));
?>

<!-- Hero Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <span class="px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider" data-live-setting="pricing_hero_kicker">
                <?= e(get_setting($pdo, 'pricing_hero_kicker', 'Paket Solusi Korporasi & Instansi')) ?>
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight" data-live-page-slug="harga" data-live-page-field="title"><?= e($page_data['title']) ?></h1>
            <p class="text-slate-300 text-base leading-relaxed" data-live-page-slug="harga" data-live-page-field="summary">
                <?= e($page_data['summary']) ?>
            </p>
            <?php if (!empty($page_data['content'])): ?>
                <div class="pt-2 text-xs text-slate-400 leading-relaxed prose prose-invert max-w-none" data-live-page-slug="harga" data-live-page-field="content">
                    <?= sanitize_rich_html($page_data['content']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Pricing Tiers (Dinamis dari Tabel `pricing_packages`) -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            <?php foreach ($pricing_packages as $idx => $pkg):
                $pkg_id = (int)($pkg['id'] ?? ($idx + 1));
                $is_featured = !empty($pkg['is_featured']);
                $code_label = trim((string)($pkg['code_label'] ?? 'KGS'));
                if ($code_label === '') {
                    $code_label = $idx === 0 ? 'ISP' : ($idx === 1 ? 'CCTV' : 'TOW');
                }
                $target_sector = trim((string)($pkg['target_sector'] ?? 'Perkantoran & Industri'));
                $tagline = trim((string)(!empty($pkg['subtitle']) ? $pkg['subtitle'] : ($pkg['tagline'] ?? '')));
                $period_badge = trim((string)(!empty($pkg['sla_badge']) ? $pkg['sla_badge'] : ($pkg['period'] ?? 'SLA & Garansi Resmi KGS')));
                $badge_text = trim((string)($pkg['badge_text'] ?? ($is_featured ? 'Solusi Utama Terlaris' : '')));
                $cta_text = trim((string)($pkg['cta_text'] ?? 'Minta Penawaran Paket Ini'));
                $features_raw = (string)($pkg['features'] ?? '');
                $feature_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $features_raw))));
            ?>
                <?php if ($is_featured): ?>
                    <div class="bg-slate-900 text-white rounded-3xl border-2 border-blue-500 p-8 shadow-xl flex flex-col justify-between relative overflow-hidden">
                        <?php if (!empty($badge_text)): ?>
                            <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-extrabold uppercase tracking-widest px-3.5 py-1 rounded-bl-xl" data-live-record="pricing_packages:<?= $pkg_id ?>:badge_text">
                                <?= e($badge_text) ?>
                            </div>
                        <?php endif; ?>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between">
                                <span class="w-11 h-11 rounded-2xl bg-blue-600 text-white font-black flex items-center justify-center text-sm" data-live-record="pricing_packages:<?= $pkg_id ?>:code_label"><?= e($code_label) ?></span>
                                <span class="text-[11px] font-bold text-blue-300 bg-blue-500/20 px-3 py-1 rounded-full border border-blue-400/30" data-live-record="pricing_packages:<?= $pkg_id ?>:target_sector"><?= e($target_sector) ?></span>
                            </div>
                            <div>
                                <h2 class="text-xl font-extrabold text-white" data-live-record="pricing_packages:<?= $pkg_id ?>:name"><?= e($pkg['name']) ?></h2>
                                <p class="text-xs text-slate-400 mt-1" data-live-record="pricing_packages:<?= $pkg_id ?>:tagline"><?= e($tagline) ?></p>
                            </div>
                            <div class="py-4 border-y border-slate-800">
                                <div class="text-xs text-slate-400 uppercase font-semibold">Investasi Berlangganan / Proyek</div>
                                <div class="text-2xl font-extrabold text-white mt-0.5" data-live-record="pricing_packages:<?= $pkg_id ?>:price_label"><?= e($pkg['price_label']) ?></div>
                                <div class="text-[11px] text-emerald-400 font-semibold mt-1" data-live-record="pricing_packages:<?= $pkg_id ?>:period">✓ <?= e($period_badge) ?></div>
                            </div>
                            <ul class="space-y-3 text-xs text-slate-300">
                                <?php foreach ($feature_lines as $f_line): ?>
                                    <li class="flex items-start gap-2.5"><span class="text-blue-400 font-bold">✓</span> <span><?= e($f_line) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="pt-8">
                            <a href="kontak.php?layanan=<?= urlencode($pkg['name']) ?>" class="block text-center w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                                <?= e($cta_text) ?> &rarr;
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-5">
                            <div class="flex items-center justify-between">
                                <span class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-800 font-black flex items-center justify-center text-sm" data-live-record="pricing_packages:<?= $pkg_id ?>:code_label"><?= e($code_label) ?></span>
                                <span class="text-[11px] font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full" data-live-record="pricing_packages:<?= $pkg_id ?>:target_sector"><?= e($target_sector) ?></span>
                            </div>
                            <div>
                                <h2 class="text-xl font-extrabold text-slate-900" data-live-record="pricing_packages:<?= $pkg_id ?>:name"><?= e($pkg['name']) ?></h2>
                                <p class="text-xs text-slate-500 mt-1" data-live-record="pricing_packages:<?= $pkg_id ?>:tagline"><?= e($tagline) ?></p>
                            </div>
                            <div class="py-4 border-y border-slate-100">
                                <div class="text-xs text-slate-400 uppercase font-semibold">Investasi Proyek</div>
                                <div class="text-2xl font-extrabold text-slate-900 mt-0.5" data-live-record="pricing_packages:<?= $pkg_id ?>:price_label"><?= e($pkg['price_label']) ?></div>
                                <div class="text-[11px] text-blue-600 font-semibold mt-1" data-live-record="pricing_packages:<?= $pkg_id ?>:period">✓ <?= e($period_badge) ?></div>
                            </div>
                            <ul class="space-y-3 text-xs text-slate-600">
                                <?php foreach ($feature_lines as $f_line): ?>
                                    <li class="flex items-start gap-2.5"><span class="text-blue-600 font-bold">✓</span> <span><?= e($f_line) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="pt-8">
                            <a href="kontak.php?layanan=<?= urlencode($pkg['name']) ?>" class="block text-center w-full py-3.5 bg-slate-900 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition">
                                <?= e($cta_text) ?> &rarr;
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Custom Project / Tender BoQ CTA -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600" data-live-setting="pricing_banner_kicker"><?= e(get_setting($pdo, 'pricing_banner_kicker', 'Pengadaan Kustom & Tender Pemerintah / BUMN')) ?></span>
                <h3 class="text-2xl font-extrabold text-slate-900" data-live-setting="pricing_banner_title"><?= e(get_setting($pdo, 'pricing_banner_title', 'Butuh Bill of Quantity (BoQ) Sesuai Spesifikasi RKS Anda?')) ?></h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="pricing_banner_desc">
                    <?= e(get_setting($pdo, 'pricing_banner_desc', 'Tim pre-sales engineer PT. Kreatif Global Solusindo siap melakukan survei lokasi (on-site survey) dan menyusun RAB/BoQ komprehensif untuk PABX Panasonic, NurseCall Rumah Sakit, AIS Maritim, Mesin Antrian Quenetic, hingga Solar Panel.')) ?>
                </p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="kontak.php?layanan=Survei+dan+Penyusunan+BoQ+Proyek" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                    Jadwalkan Survei & BoQ Gratis &rarr;
                </a>
                <a href="https://wa.me/<?= e($wa_clean) ?>?text=Halo%20PT.%20KGS,%20saya%20ingin%20berkonsultasi%20mengenai%20penyusunan%20BoQ%20dan%20penawaran%20harga." target="_blank" rel="noopener noreferrer" class="px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition">
                    💬 Chat Sales Engineer
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
