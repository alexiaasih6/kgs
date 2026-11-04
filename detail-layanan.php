<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: layanan.php');
    exit;
}

// Alias mapping for sub-items in the 4 Pillars & ISP segments
$slug_aliases = [
    'fire-alarm-security' => 'fire-alarm-security',
    'cctv-security-system' => 'cctv-security-system',
    'access-door-control' => 'access-door-control',
    'video-door-phone' => 'video-door-phone',
    'router-networking' => 'router-enterprise-mikrotik-cisco',
    'wireless-networking' => 'wireless-point-to-point-outdoor',
    'fiber-optik-backbone' => 'instalasi-kabel-fiber-optik-lan',
    'analogue-pbx' => 'analogue-pbx-panasonic',
    'hybrid-pbx' => 'hybrid-pbx-system',
    'ip-pbx-system' => 'ip-pbx-unified-communication',
    'lightning-protector' => 'penangkal-petir-grounding-system',
    'handy-talky' => 'radio-komunikasi-ht-rig-repeater',
    'conference-system' => 'audio-video-conference-system',
    'guy-wire-tower' => 'pembangunan-tower-triangle-guy-wire',
    'self-supporting-tower-monopole' => 'self-supporting-tower-sst-monopole',
    'internet-dedicated' => 'internet-dedicated-korporat-1-1',
    'internet-bisnis' => 'internet-broadband-bisnis-soho',
    'home-internet' => 'internet-fiber-perumahan-home',
];
$lookup_slug = $slug_aliases[$slug] ?? $slug;

$service = false;
try {
    $stmt = $pdo->prepare("
        SELECT s.*, c.name AS category_name, c.segment_number 
        FROM services s 
        JOIN service_categories c ON s.category_id = c.id 
        WHERE (s.slug = ? OR s.slug = ?) AND s.is_published = 1 
        LIMIT 1
    ");
    $stmt->execute([$slug, $lookup_slug]);
    $service = $stmt->fetch();
} catch (Throwable $e) {
    $service = false;
}

// Fallback ke katalog default jika belum ada di tabel MySQL
$default_catalog = function_exists('get_default_services_catalog') ? get_default_services_catalog() : [];
if (!$service && !empty($default_catalog)) {
    foreach ($default_catalog as $idx => $cat_item) {
        $item_slug = (string)($cat_item['slug'] ?? '');
        if ($item_slug === $slug || $item_slug === $lookup_slug) {
            $service = array_merge([
                'id' => $idx + 1,
                'category_id' => (int)($cat_item['segment_number'] ?? 1),
                'category_name' => (string)($cat_item['category_name'] ?? 'Solusi Terintegrasi'),
                'segment_number' => (int)($cat_item['segment_number'] ?? 1),
                'meta_title' => '',
                'meta_description' => '',
                'target_customer' => $cat_item['target_customer'] ?? 'Perusahaan, Instansi Pemerintah, BUMN, Pendidikan, Perhotelan & Industri',
                'benefits' => $cat_item['benefits'] ?? 'Survei lokasi gratis, jaminan garansi perangkat resmi, instalasi rapi berstandar K3, dan dukungan maintenance engineer berpengalaman.',
                'specifications' => $cat_item['specifications'] ?? 'Standar industri enterprise dengan dukungan dokumentasi topologi dan serah terima UAT.',
                'main_image' => $cat_item['image_url'] ?? '',
            ], $cat_item);
            break;
        }
    }
}

// Fallback deskriptif untuk sub-item spesifik bila belum ditemukan
if (!$service) {
    $pillar_fallbacks = [
        'fire-alarm-security' => ['name' => 'Fire & Alarm Security System', 'cat' => 'Security System', 'seg' => 1, 'summary' => 'Sistem deteksi dini kebakaran (Conventional & Addressable Fire Alarm) dan integrasi sirine keselamatan gedung.', 'img' => 'uploads/slideshow/security.jpg'],
        'cctv-security-system' => ['name' => 'CCTV Surveillance System (IP & Analog HD)', 'cat' => 'Security System', 'seg' => 1, 'summary' => 'Sistem pengawasan kamera CCTV resolusi tinggi dengan pemantauan jarak jauh, NVR/DVR, dan analitik keamanan.', 'img' => 'uploads/slideshow/security.jpg'],
        'access-door-control' => ['name' => 'Access Door Control & Biometric', 'cat' => 'Security System', 'seg' => 1, 'summary' => 'Sistem kontrol akses pintu menggunakan kartu RFID, sidik jari, pengenalan wajah, dan magnetic lock.', 'img' => 'uploads/slideshow/security.jpg'],
        'video-door-phone' => ['name' => 'Video Door Phone & Interkom Gedung', 'cat' => 'Security System', 'seg' => 1, 'summary' => 'Komunikasi visual dua arah antara tamu dan penghuni/resepsionis terintegrasi kunci pintu elektrik.', 'img' => 'uploads/slideshow/security.jpg'],
        'router-networking' => ['name' => 'Enterprise Router & Core Switching', 'cat' => 'Networking', 'seg' => 2, 'summary' => 'Manajemen bandwidth, load balancing multi-ISP, firewall, VPN antar cabang, dan routing skala korporasi.', 'img' => 'uploads/slideshow/networking.jpg'],
        'wireless-networking' => ['name' => 'Wireless Point-to-Point & Indoor Wi-Fi', 'cat' => 'Networking', 'seg' => 2, 'summary' => 'Jaringan wireless outdoor jarak jauh antar gedung/site dan distribusi Wi-Fi seamless berkapasitas tinggi.', 'img' => 'uploads/slideshow/networking.jpg'],
        'fiber-optik-backbone' => ['name' => 'Instalasi Jaringan Fiber Optik & Structured Cabling', 'cat' => 'Networking', 'seg' => 2, 'summary' => 'Penarikan kabel Fiber Optik (Singlemode/Multimode), splicing fusion, terminasi OTB, dan pengujian OTDR.', 'img' => 'uploads/slideshow/fiber-optik.jpg'],
        'analogue-pbx' => ['name' => 'Analogue PBX System', 'cat' => 'PBX Telecommunication', 'seg' => 3, 'summary' => 'Sistem komunikasi telepon internal kantor yang stabil dan hemat biaya untuk distribusi ekstensi analog.', 'img' => 'uploads/slideshow/pbx.jpg'],
        'hybrid-pbx' => ['name' => 'Hybrid PBX System', 'cat' => 'PBX Telecommunication', 'seg' => 3, 'summary' => 'Perpaduan fleksibel sistem telepon analog, digital, dan IP untuk mendukung ekspansi bertahap perusahaan.', 'img' => 'uploads/slideshow/pbx.jpg'],
        'ip-pbx-system' => ['name' => 'IP-PBX & Unified Communication', 'cat' => 'PBX Telecommunication', 'seg' => 3, 'summary' => 'Sistem telepon berbasis jaringan IP modern dengan fitur IVR, call recording, SIP trunk, dan integrasi cabang.', 'img' => 'uploads/slideshow/pbx.jpg'],
        'lightning-protector' => ['name' => 'Lightning Protector & Grounding System', 'cat' => 'Telecommunication & Tower', 'seg' => 4, 'summary' => 'Sistem proteksi petir elektrostatis/konvensional, arrester panel, dan pembumian (grounding) resistansi rendah.', 'img' => 'uploads/slideshow/tower.jpg'],
        'handy-talky' => ['name' => 'Radio Komunikasi Handy Talky (HT), RIG & Repeater', 'cat' => 'Telecommunication & Tower', 'seg' => 4, 'summary' => 'Perangkat komunikasi radio dua arah VHF/UHF, base station RIG, dan repeater untuk operasional lapangan.', 'img' => 'uploads/slideshow/telecommunication.jpg'],
        'conference-system' => ['name' => 'Audio & Video Conference System', 'cat' => 'Telecommunication & Tower', 'seg' => 4, 'summary' => 'Solusi ruang rapat modern dengan delegate/chairman microphone, kamera PTZ, dan sistem tata suara presisi.', 'img' => 'uploads/slideshow/telecommunication.jpg'],
        'guy-wire-tower' => ['name' => 'Guy Wire Tower (Tower Triangle)', 'cat' => 'Telecommunication & Tower', 'seg' => 4, 'summary' => 'Pembangunan dan perawatan tower triangle dengan kawat seling baja (guy wire) untuk antena wireless & radio.', 'img' => 'uploads/slideshow/tower.jpg'],
        'self-supporting-tower-monopole' => ['name' => 'Self Supporting Tower (SST) & Monopole', 'cat' => 'Telecommunication & Tower', 'seg' => 4, 'summary' => 'Konstruksi menara telekomunikasi mandiri berkekuatan tinggi (3/4 kaki & monopole) untuk backbone korporasi.', 'img' => 'uploads/slideshow/tower.jpg'],
        'internet-dedicated' => ['name' => 'Layanan Internet Dedicated Korporat (Rasio 1:1)', 'cat' => 'Internet Service Provider', 'seg' => 5, 'summary' => 'Koneksi internet dedicated simetris 1:1 dengan jaminan SLA tinggi untuk korporasi, BUMN, sekolah, dan instansi.', 'img' => 'uploads/slideshow/isp.jpg'],
        'internet-bisnis' => ['name' => 'Layanan Internet Bisnis & UMKM', 'cat' => 'Internet Service Provider', 'seg' => 5, 'summary' => 'Paket koneksi internet berkecepatan tinggi dan stabil untuk menunjang produktivitas kantor, ruko, dan bisnis.', 'img' => 'uploads/slideshow/isp.jpg'],
        'home-internet' => ['name' => 'Home Internet Fiber (Perumahan & Residensial)', 'cat' => 'Internet Service Provider', 'seg' => 5, 'summary' => 'Layanan internet rumah tanpa batasan kuota (unlimited) untuk kebutuhan keluarga, WFH, dan hiburan digital.', 'img' => 'uploads/slideshow/isp.jpg'],
    ];
    if (isset($pillar_fallbacks[$slug])) {
        $fb = $pillar_fallbacks[$slug];
        $service = [
            'id' => 900,
            'category_id' => $fb['seg'],
            'category_name' => $fb['cat'],
            'segment_number' => $fb['seg'],
            'name' => $fb['name'],
            'slug' => $slug,
            'summary' => $fb['summary'],
            'description' => $fb['summary'] . "\n\nPT. Kreatif Global Solusindo menyediakan layanan konsultasi, survei lokasi, pengadaan perangkat bergaransi resmi, instalasi berstandar keselamatan kerja, hingga pemeliharaan berkala untuk memastikan sistem " . $fb['name'] . " berjalan optimal mendukung operasional organisasi Anda.",
            'specifications' => "1. Perangkat orisinal bergaransi resmi pabrikan\n2. Instalasi rapi sesuai standar teknis & K3\n3. Pengujian fungsi (Commissioning & UAT)\n4. Dukungan teknis dan pemeliharaan purna jual",
            'target_customer' => 'Korporasi, Instansi Pemerintah, BUMN, Rumah Sakit, Kampus/Sekolah, Perhotelan, Pabrik & Gudang',
            'benefits' => 'Didukung tim teknisi berpengalaman sejak 1997 dengan cakupan layanan Sumatera Selatan dan nasional.',
            'main_image' => $fb['img'],
            'image_url' => $fb['img'],
            'meta_title' => $fb['name'] . ' — PT. Kreatif Global Solusindo',
            'meta_description' => $fb['summary'],
        ];
    }
}

if (!$service) {
    http_response_code(404);
    $page_title = 'Layanan Tidak Ditemukan';
    $page_description = 'Layanan yang Anda cari tidak ditemukan. Silakan jelajahi katalog produk dan layanan PT. Kreatif Global Solusindo.';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="py-24 bg-slate-50 text-center">
        <div class="max-w-xl mx-auto px-4">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 font-bold text-xs rounded-full uppercase">Katalog Solusi Terintegrasi</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-4">Halaman Layanan Tidak Ditemukan</h1>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed">Maaf, tautan produk atau layanan yang Anda tuju sedang diperbarui atau tidak tersedia. Silakan buka daftar lengkap produk dan layanan kami.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="layanan.php" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition">Lihat Katalog Produk &amp; Layanan &rarr;</a>
                <a href="kontak.php#konsultasi" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition">Hubungi Kami</a>
            </div>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = !empty($service['meta_title']) ? $service['meta_title'] : $service['name'];
$page_description = !empty($service['meta_description']) ? $service['meta_description'] : ($service['summary'] ?: substr(strip_tags($service['description']), 0, 155));

// Ambil layanan lain di segmen yang sama
$relatedServices = [];
try {
    $relatedStmt = $pdo->prepare("SELECT name, slug, summary FROM services WHERE category_id = ? AND id != ? AND is_published = 1 ORDER BY sort_order ASC LIMIT 4");
    $relatedStmt->execute([$service['category_id'], $service['id']]);
    $relatedServices = $relatedStmt->fetchAll();
} catch (Throwable $e) {
    $relatedServices = [];
}
if (empty($relatedServices) && !empty($default_catalog)) {
    foreach ($default_catalog as $cat_item) {
        if (($cat_item['slug'] ?? '') !== ($service['slug'] ?? '') && count($relatedServices) < 4) {
            $relatedServices[] = [
                'name' => $cat_item['name'],
                'slug' => $cat_item['slug'],
                'summary' => $cat_item['summary'] ?? '',
            ];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb & Hero -->
<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <a href="index.php" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="layanan.php" class="hover:text-white">Product &amp; Service</a>
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
                    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm aspect-16/9 bg-slate-950">
                        <img src="<?= e($service_img) ?>" alt="<?= e($service['name']) ?>" width="1280" height="720" loading="eager" decoding="async" data-live-img="service_image:<?= (int)$service['id'] ?>" class="w-full h-full object-cover">
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
