<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
$layanan_page = get_page_by_slug($pdo, 'layanan');
$page_title = !empty($layanan_page['meta_title']) ? $layanan_page['meta_title'] : ($layanan_page['title'] ?? "Product & Service");
$meta_description = $layanan_page['meta_description'] ?? $layanan_page['summary'] ?? '';
require_once __DIR__ . '/includes/header.php';

$cat_code = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// Map friendly category slugs (security, networking, pbx, telecommunication, isp) to search terms
$cat_keyword_map = [
    'security' => 'security',
    'networking' => 'network',
    'pbx' => 'pbx',
    'telecommunication' => 'tele',
    'isp' => 'internet',
];

try {
    if (!empty($cat_code)) {
        $kw = $cat_keyword_map[strtolower($cat_code)] ?? $cat_code;
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as category_name, c.code as category_code 
            FROM services s 
            JOIN service_categories c ON s.category_id = c.id 
            WHERE (c.code = ? OR LOWER(c.name) LIKE ?) AND s.is_published = 1 
            ORDER BY s.sort_order ASC
        ");
        $stmt->execute([$cat_code, '%' . strtolower($kw) . '%']);
        $services = $stmt->fetchAll();
    } else {
        $services = $pdo->query("SELECT s.*, c.name as category_name, c.code as category_code FROM services s JOIN service_categories c ON s.category_id = c.id WHERE s.is_published = 1 ORDER BY c.segment_number ASC, s.sort_order ASC")->fetchAll();
    }
    $all_categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY segment_number ASC")->fetchAll();
} catch (Throwable $e) {
    $services = [];
    $all_categories = [];
}

if (empty($services) && function_exists('get_default_services_catalog')) {
    $default_srv = get_default_services_catalog();
    if (!empty($cat_code)) {
        $kw = strtolower($cat_keyword_map[strtolower($cat_code)] ?? $cat_code);
        $services = array_values(array_filter($default_srv, function ($item) use ($kw, $cat_code) {
            return stripos((string)($item['category_name'] ?? ''), $kw) !== false
                || stripos((string)($item['name'] ?? ''), $kw) !== false
                || stripos((string)($item['category_code'] ?? ''), $cat_code) !== false;
        }));
        if (empty($services)) {
            $services = $default_srv;
        }
    } else {
        $services = $default_srv;
    }
}
?>

<div class="bg-white border-b border-slate-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block mb-1" data-live-setting="layanan_kicker"><?= e(get_setting($pdo, 'layanan_kicker', 'Katalog Solusi Terintegrasi — Product & Service')) ?></span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight" data-live-page-slug="layanan" data-live-page-field="title"><?= e(($layanan_page['title'] && $layanan_page['title'] !== 'Katalog Layanan & Solusi Terintegrasi' && $layanan_page['title'] !== '5 Segmen Layanan & Solusi Terintegrasi') ? $layanan_page['title'] : 'Product & Service') ?></h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-3xl" data-live-page-slug="layanan" data-live-page-field="summary">
            <?= e($layanan_page['summary'] ?: 'Katalog lengkap Product & Service: Security, Networking, PBX, Telecommunication (LIGHTNING PROTECTOR & Tower), serta Internet Service Provider (Internet Dedicated, Internet Bisnis & Home Internet).') ?>
        </p>

        <!-- Ringkasan Terstruktur Product & Service (Dapat Diklik) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
            <!-- 1. Security -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 hover:border-rose-300 p-5 shadow-2xs transition flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=security#daftar-layanan" class="flex items-center justify-between gap-2.5 mb-3 group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 font-extrabold text-xs flex items-center justify-center">SEC</div>
                            <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-rose-600 uppercase tracking-wide transition-colors">Security</h3>
                        </div>
                        <span class="text-xs font-bold text-rose-600">&rarr;</span>
                    </a>
                    <ul class="space-y-1.5 text-xs font-semibold text-slate-700">
                        <li><a href="detail-layanan.php?slug=fire-alarm-security" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-rose-100/70 hover:text-rose-700 transition"><span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> FIRE &amp; ALARM SECURITY</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=cctv-security-system" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-rose-100/70 hover:text-rose-700 transition"><span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> CCTV</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=access-door-control" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-rose-100/70 hover:text-rose-700 transition"><span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> Access Door</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=video-door-phone" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-rose-100/70 hover:text-rose-700 transition"><span class="flex items-center gap-2"><span class="text-rose-600 font-bold">✓</span> Video Door Phone</span><span>&rsaquo;</span></a></li>
                    </ul>
                </div>
                <a href="layanan.php?cat=security#daftar-layanan" class="mt-4 pt-2.5 border-t border-slate-200 text-[11px] font-bold text-rose-600 hover:text-rose-700 flex items-center justify-between">
                    <span>Filter Layanan Security</span><span>&rarr;</span>
                </a>
            </div>

            <!-- 2. Networking -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 hover:border-blue-300 p-5 shadow-2xs transition flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=networking#daftar-layanan" class="flex items-center justify-between gap-2.5 mb-3 group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 font-extrabold text-xs flex items-center justify-center">NET</div>
                            <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-blue-600 uppercase tracking-wide transition-colors">Networking</h3>
                        </div>
                        <span class="text-xs font-bold text-blue-600">&rarr;</span>
                    </a>
                    <ul class="space-y-1.5 text-xs font-semibold text-slate-700">
                        <li><a href="detail-layanan.php?slug=router-networking" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-blue-100/70 hover:text-blue-700 transition"><span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Router</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=wireless-networking" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-blue-100/70 hover:text-blue-700 transition"><span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Wireless</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=fiber-optik-backbone" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-blue-100/70 hover:text-blue-700 transition"><span class="flex items-center gap-2"><span class="text-blue-600 font-bold">✓</span> Fiber Optik &amp; Cabling</span><span>&rsaquo;</span></a></li>
                    </ul>
                </div>
                <a href="layanan.php?cat=networking#daftar-layanan" class="mt-4 pt-2.5 border-t border-slate-200 text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center justify-between">
                    <span>Filter Layanan Networking</span><span>&rarr;</span>
                </a>
            </div>

            <!-- 3. PBX -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 hover:border-indigo-300 p-5 shadow-2xs transition flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=pbx#daftar-layanan" class="flex items-center justify-between gap-2.5 mb-3 group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center">PBX</div>
                            <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 uppercase tracking-wide transition-colors">PBX</h3>
                        </div>
                        <span class="text-xs font-bold text-indigo-600">&rarr;</span>
                    </a>
                    <ul class="space-y-1.5 text-xs font-semibold text-slate-700">
                        <li><a href="detail-layanan.php?slug=analogue-pbx" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-indigo-100/70 hover:text-indigo-700 transition"><span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> ANALOGUE PBX</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=hybrid-pbx" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-indigo-100/70 hover:text-indigo-700 transition"><span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> HYBRID PBX</span><span>&rsaquo;</span></a></li>
                        <li><a href="detail-layanan.php?slug=ip-pbx-system" class="flex items-center justify-between gap-2 py-1 px-2 -mx-2 rounded-lg hover:bg-indigo-100/70 hover:text-indigo-700 transition"><span class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> IP – PBX</span><span>&rsaquo;</span></a></li>
                    </ul>
                </div>
                <a href="layanan.php?cat=pbx#daftar-layanan" class="mt-4 pt-2.5 border-t border-slate-200 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center justify-between">
                    <span>Filter Layanan PBX</span><span>&rarr;</span>
                </a>
            </div>

            <!-- 4. Telecommunication -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 hover:border-amber-300 p-5 shadow-2xs transition flex flex-col justify-between">
                <div>
                    <a href="layanan.php?cat=telecommunication#daftar-layanan" class="flex items-center justify-between gap-2.5 mb-3 group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-extrabold text-xs flex items-center justify-center">TEL</div>
                            <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-amber-600 uppercase tracking-wide transition-colors">Telecommunication</h3>
                        </div>
                        <span class="text-xs font-bold text-amber-600">&rarr;</span>
                    </a>
                    <div class="space-y-2.5 text-xs">
                        <div>
                            <a href="detail-layanan.php?slug=lightning-protector" class="font-extrabold text-slate-900 hover:text-amber-600 block mb-1 transition">LIGHTNING PROTECTOR &nearr;</a>
                            <div class="flex flex-wrap gap-1.5">
                                <a href="detail-layanan.php?slug=handy-talky" class="px-2 py-0.5 rounded bg-white border border-slate-200 hover:border-amber-400 text-slate-700 font-medium transition">Handy Talky</a>
                                <a href="detail-layanan.php?slug=conference-system" class="px-2 py-0.5 rounded bg-white border border-slate-200 hover:border-amber-400 text-slate-700 font-medium transition">CONFERENCE SYSTEM</a>
                                <a href="detail-layanan.php?slug=lightning-protector" class="px-2 py-0.5 rounded bg-white border border-slate-200 hover:border-amber-400 text-slate-700 font-medium transition">LIGHTNING PROTECTOR</a>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200">
                            <a href="detail-layanan.php?slug=self-supporting-tower-monopole" class="font-extrabold text-slate-900 hover:text-amber-600 block mb-1 transition">Tower &nearr;</a>
                            <div class="flex flex-wrap gap-1.5">
                                <a href="detail-layanan.php?slug=guy-wire-tower" class="px-2 py-0.5 rounded bg-white border border-slate-200 hover:border-amber-400 text-slate-700 font-medium transition">Guy Wire Tower</a>
                                <a href="detail-layanan.php?slug=self-supporting-tower-monopole" class="px-2 py-0.5 rounded bg-white border border-slate-200 hover:border-amber-400 text-slate-700 font-medium transition">SELF SUPPORTING TOWER MONOPOLE</a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="layanan.php?cat=telecommunication#daftar-layanan" class="mt-4 pt-2.5 border-t border-slate-200 text-[11px] font-bold text-amber-600 hover:text-amber-700 flex items-center justify-between">
                    <span>Filter Layanan Telecom</span><span>&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Internet Service Provider: Legalitas & 3 Segmen -->
        <div class="mt-6 bg-slate-900 text-white rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-lg space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6 pb-6 border-b border-slate-800">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                        <span>Internet Service Provider</span>
                    </div>
                    <h3 class="text-2xl font-extrabold tracking-tight">Layanan Internet Resmi Berlisensi Kominfo RI &amp; APJII</h3>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Solusi akses internet berkecepatan tinggi melalui jaringan Fiber Optic dan Wireless Point-to-Point untuk 3 segmen utama: Internet Dedicated, Internet Bisnis, dan Home Internet.
                    </p>
                </div>

                <!-- Legalitas ISP -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-5 lg:max-w-xl w-full space-y-2.5">
                    <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-amber-300">
                        <span>📜 Legalitas Internet Service Provider:</span>
                    </div>
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

            <!-- 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet) -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-extrabold uppercase tracking-wider text-blue-300">
                        3 Segmen Layanan (Internet Dedicated, Internet Bisnis, Home Internet)
                    </h4>
                    <a href="harga.php" class="text-xs font-bold text-emerald-400 hover:text-emerald-300">Lihat Halaman Paket Harga &rarr;</a>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Segmen 1: Internet Dedicated -->
                    <div class="bg-slate-800/75 border border-slate-700 rounded-2xl p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-blue-500/20 text-blue-300 text-[11px] font-bold">Segmen 1</span>
                                <span class="text-[11px] text-emerald-400 font-semibold">Dedicated 1:1</span>
                            </div>
                            <h5 class="text-lg font-extrabold text-white mb-1"><a href="detail-layanan.php?slug=internet-dedicated" class="hover:text-blue-400 transition">Internet Dedicated &nearr;</a></h5>
                            <p class="text-xs text-slate-400 mb-4">Koneksi dedicated simetris untuk UMKM, Sekolah, dan Corporate.</p>
                            <div class="space-y-2">
                                <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <span class="font-extrabold text-white block">10 Mbps</span>
                                        <span class="text-[11px] text-emerald-300">Kebutuhan UMKM</span>
                                    </div>
                                    <div class="text-right font-bold text-blue-300">Harga Perbulan mulai 3.500.000</div>
                                </div>
                                <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <span class="font-extrabold text-white block">10 Mbps</span>
                                        <span class="text-[11px] text-emerald-300">Kebutuhan Sekolah</span>
                                    </div>
                                    <div class="text-right font-bold text-blue-300">Harga Perbulan mulai 1.750.000</div>
                                </div>
                                <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <span class="font-extrabold text-white block">10 Mbps</span>
                                        <span class="text-[11px] text-emerald-300">Kebutuhan Corporate</span>
                                    </div>
                                    <div class="text-right font-bold text-blue-300">Harga Perbulan mulai 1.750.000</div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <a href="detail-layanan.php?slug=internet-dedicated" class="py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition text-center block">Detail Layanan</a>
                            <a href="kontak.php?layanan=Internet+Dedicated#konsultasi" class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition text-center block">Pesan Sekarang &rarr;</a>
                        </div>
                    </div>

                    <!-- Segmen 2: Internet Bisnis -->
                    <div class="bg-slate-800/75 border border-slate-700 rounded-2xl p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-blue-500/20 text-blue-300 text-[11px] font-bold">Segmen 2</span>
                                <span class="text-[11px] text-emerald-400 font-semibold">10 – 200 Mbps</span>
                            </div>
                            <h5 class="text-lg font-extrabold text-white mb-1"><a href="detail-layanan.php?slug=internet-bisnis" class="hover:text-blue-400 transition">Internet Bisnis &nearr;</a></h5>
                            <p class="text-xs text-slate-400 mb-4">Koneksi internet bisnis stabil dan cepat untuk kantor &amp; tempat usaha.</p>
                            <div class="space-y-2">
                                <?php
                                $bisnis_tiers = [
                                    ['10 Mbps', 'Rp. 407.000 / bulan'],
                                    ['20 Mbps', 'Rp. 431.000 / bulan'],
                                    ['30 Mbps', 'Rp. 453.000 / bulan'],
                                    ['40 Mbps', 'Rp. 477.000 / bulan'],
                                    ['50 Mbps', 'Rp. 511.000 / bulan'],
                                    ['100 Mbps', 'Rp. 1.050.000 / bulan'],
                                    ['200 Mbps', 'Rp. 1.560.000 / bulan'],
                                ];
                                foreach ($bisnis_tiers as $bt): ?>
                                <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 flex items-center justify-between gap-2 text-xs">
                                    <span class="font-extrabold text-white"><?= e($bt[0]) ?></span>
                                    <span class="font-bold text-blue-300"><?= e($bt[1]) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <a href="detail-layanan.php?slug=internet-bisnis" class="py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition text-center block">Detail Layanan</a>
                            <a href="kontak.php?layanan=Internet+Bisnis#konsultasi" class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition text-center block">Pesan Sekarang &rarr;</a>
                        </div>
                    </div>

                    <!-- Segmen 3: Home Internet -->
                    <div class="bg-slate-800/75 border border-slate-700 rounded-2xl p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-blue-500/20 text-blue-300 text-[11px] font-bold">Segmen 3</span>
                                <span class="text-[11px] text-emerald-400 font-semibold">Home Fiber</span>
                            </div>
                            <h5 class="text-lg font-extrabold text-white mb-1"><a href="detail-layanan.php?slug=home-internet" class="hover:text-blue-400 transition">Home Internet &nearr;</a></h5>
                            <p class="text-xs text-slate-400 mb-4">Internet rumah tangga nyaman untuk keluarga dari 5 hingga 20 perangkat.</p>
                            <div class="space-y-2">
                                <?php
                                $home_tiers = [
                                    ['10 Mbps', 'Rp. 252.000 / bulan', 'Optiman di 5 Perangkat'],
                                    ['20 Mbps', 'Rp. 276.000 / bulan', 'Optiman di 7 Perangkat'],
                                    ['30 Mbps', 'Rp. 298.000 / bulan', 'Optiman di 9 Perangkat'],
                                    ['50 Mbps', 'Rp. 450.000 / bulan', 'Optiman di 12 Perangkat'],
                                    ['100 Mbps', 'Rp. 710.000 / bulan', 'Optiman di 15 Perangkat'],
                                    ['200 Mbps', 'Rp. 1.100.000 / bulan', 'Optiman di 20 Perangkat'],
                                ];
                                foreach ($home_tiers as $ht): ?>
                                <div class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-2.5 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <span class="font-extrabold text-white block"><?= e($ht[0]) ?></span>
                                        <span class="text-[11px] text-emerald-300"><?= e($ht[2]) ?></span>
                                    </div>
                                    <span class="font-bold text-blue-300"><?= e($ht[1]) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <a href="detail-layanan.php?slug=home-internet" class="py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition text-center block">Detail Layanan</a>
                            <a href="kontak.php?layanan=Home+Internet#konsultasi" class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition text-center block">Pesan Sekarang &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segment Filter Tabs -->
        <div id="daftar-layanan" class="flex flex-wrap items-center gap-1.5 mt-8 scroll-mt-24">
            <a href="layanan.php#daftar-layanan" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition <?= empty($cat_code) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                Semua Product &amp; Service
            </a>
            <?php foreach ($all_categories as $c): ?>
            <a href="layanan.php?cat=<?= urlencode($c['code']) ?>#daftar-layanan" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition <?= ($cat_code === $c['code']) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                <span data-live-record="service_categories:<?= (int)$c['id'] ?>:name"><?= e($c['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($services as $srv):
            $srv_img = !empty($srv['main_image']) ? $srv['main_image'] : ($srv['image_url'] ?? '');
            $srv_slug = (string)($srv['slug'] ?? '');
        ?>
        <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md hover:border-blue-300 transition group">
            <div>
                <?php if (!empty($srv_img)): ?>
                    <a href="detail-layanan.php?slug=<?= urlencode($srv_slug) ?>" class="block aspect-16/9 -mx-6 -mt-6 mb-4 bg-slate-900 overflow-hidden rounded-t-xl">
                        <img src="<?= e($srv_img) ?>" alt="<?= e($srv['name']) ?>" width="640" height="360" loading="lazy" decoding="async" data-live-img="service_image:<?= (int)($srv['id'] ?? 0) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </a>
                <?php endif; ?>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                    <span class="font-semibold text-blue-600"><?= e($srv['category_name'] ?? 'Solusi Terintegrasi') ?></span>
                    <span class="text-emerald-700 font-medium text-[11px]">✓ Standar Teruji</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2 leading-snug">
                    <a href="detail-layanan.php?slug=<?= urlencode($srv_slug) ?>" class="hover:text-blue-600 transition-colors" data-live-record="services:<?= (int)($srv['id'] ?? 0) ?>:name"><?= e($srv['name']) ?></a>
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed mb-4" data-live-record="services:<?= (int)($srv['id'] ?? 0) ?>:summary"><?= e($srv['summary'] ?? '') ?></p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="detail-layanan.php?slug=<?= urlencode($srv_slug) ?>" class="font-semibold text-blue-600 hover:underline">
                    Detail Spesifikasi &rarr;
                </a>
                <a href="kontak.php?layanan=<?= urlencode($srv['name']) ?>#konsultasi" class="text-slate-600 hover:text-slate-900 font-medium">
                    Minta Penawaran
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
