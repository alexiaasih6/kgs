<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'kemitraan', [
    'title' => 'Membuka Kerjasama Kemitraan Reseller Internet KGSNet & Aliansi Teknologi',
    'summary' => 'KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia. PT. Kreatif Global Solusindo membuka peluang kemitraan strategis ISP & infrastruktur IT.',
]);
$page_title = !empty($page_data['meta_title']) ? $page_data['meta_title'] : $page_data['title'];
$meta_description = !empty($page_data['meta_description']) ? $page_data['meta_description'] : $page_data['summary'];

$partners = [
    ['name' => 'APJII & Kominfo RI', 'category' => 'Penyelenggara Resmi Jasa Akses Internet (ISP)', 'role' => 'Licensed Member & Regulated Operator'],
    ['name' => 'MikroTik & Cisco Systems', 'category' => 'Data Networking, Routing & Core Switching', 'role' => 'Network Infrastructure Partner'],
    ['name' => 'Motorola, Icom & Hytera', 'category' => 'Radio Komunikasi (Repeater, HT, RIG)', 'role' => 'Industrial Radio Partner'],
    ['name' => 'Panasonic & Yealink', 'category' => 'PABX, IP-PBX & SIP Telephony', 'role' => 'Unified Communication Partner'],
    ['name' => 'Hikvision & Dahua Enterprise', 'category' => 'CCTV IP Surveillance & NVR System', 'role' => 'Security Systems Integrator'],
    ['name' => 'ZKTeco & Suprema', 'category' => 'Access Door Biometric, RFID & Turnstile', 'role' => 'Access Control Partner'],
    ['name' => 'TOA, Hoiki & Nohmi', 'category' => 'Fire Alarm, Public Address & Nursecall', 'role' => 'Building Safety Partner'],
    ['name' => 'Ubiquiti & Cambium Networks', 'category' => 'Wireless Point-to-Point & Broadband ISP', 'role' => 'Broadband Wireless Partner'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold uppercase tracking-wider mb-3">
            <span>Program Kemitraan Reseller ISP & Sistem Integrator</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3"><?= e($page_data['title']) ?></h1>
        <p class="text-slate-300 max-w-3xl text-sm sm:text-base leading-relaxed"><?= e($page_data['summary']) ?></p>
    </div>
</section>

<section class="py-16 bg-white dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Highlight Utama Kemitraan Reseller KGSNet -->
        <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white p-6 sm:p-10 border-2 border-amber-400/60 shadow-2xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <div class="max-w-3xl">
                    <span class="inline-block px-3.5 py-1.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/40 text-xs font-extrabold uppercase tracking-wider mb-4">
                        Peluang Kemitraan Reseller Resmi ISP APJII
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-amber-300 leading-tight mb-4">
                        &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;
                    </h2>
                    <p class="text-sm sm:text-base text-slate-200 leading-relaxed mb-6">
                        Kami membuka kerjasama kemitraan bagi pengusaha jaringan lokal, pengelola RT/RW Net, pengelola kawasan bisnis/perumahan, maupun badan usaha di seluruh wilayah Indonesia yang ingin mengembangkan bisnis layanan internet dengan dukungan legalitas ISP resmi APJII, suplai bandwidth dedicated Fiber Optic / Wireless Point-to-Point, serta pendampingan teknis NOC 24/7 dari PT. Kreatif Global Solusindo.
                    </p>
                </div>
                <div class="flex flex-col gap-3 shrink-0 lg:w-72 bg-slate-900/90 p-6 rounded-2xl border border-slate-700">
                    <a href="https://wa.me/628117899002?text=<?= rawurlencode('Halo PT. Kreatif Global Solusindo (KGSNet), saya tertarik mendaftar kerjasama kemitraan reseller layanan internet KGSNet.') ?>" target="_blank" rel="noopener noreferrer" class="w-full py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs sm:text-sm text-center transition">
                        Daftar Kemitraan via WhatsApp &rarr;
                    </a>
                    <a href="promo.php?layanan=<?= rawurlencode('Kemitraan Reseller Layanan Internet KGSNet') ?>#form-permintaan-penawaran" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm text-center transition">
                        Isi Form Pengajuan Mitra
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($partners as $p): ?>
                <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900 hover:border-blue-500 transition flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-[10px] font-bold uppercase tracking-wider mb-2"><?= e($p['role']) ?></span>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white mb-1"><?= e($p['name']) ?></h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed"><?= e($p['category']) ?></p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                        <span>✓ Mitra Terverifikasi</span>
                        <span>Original Guarantee</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
