<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'testimoni', [
    'title' => 'Formulir & Ulasan Testimoni Pelanggan',
    'summary' => 'Berikan penilaian dan testimoni Anda atas layanan maupun pengerjaan proyek PT. Kreatif Global Solusindo melalui formulir di bawah ini.',
]);
$page_title = !empty($page_data['meta_title']) ? $page_data['meta_title'] : $page_data['title'];
$meta_description = !empty($page_data['meta_description']) ? $page_data['meta_description'] : $page_data['summary'];

$testimonials = [
    [
        'client_name' => 'Kepala Divisi Infrastruktur TI & Jaringan',
        'role' => 'Manajemen Operasional TI',
        'company' => 'BUMN Sektor Pupuk & Industri Kimia — Palembang',
        'sector' => 'BUMN & Industri',
        'service_used' => 'Data Networking',
        'quote' => 'Tim teknisi PT. Kreatif Global Solusindo bekerja sangat disiplin menerapkan standar K3 di area pabrik kami. Hasil terminasi Fiber Optik OTDR sangat presisi dan jaringan berjalan sangat stabil.',
    ],
    [
        'client_name' => 'Koordinator Sistem Informasi & Komunikasi',
        'role' => 'Pejabat Teknis Instansi',
        'company' => 'Instansi Pemerintah Daerah Provinsi Sumatera Selatan',
        'sector' => 'Pemerintahan',
        'service_used' => 'Internet Service Provider',
        'quote' => 'Koneksi Internet Dedicated KGSNet memiliki latensi yang sangat rendah dan stabil untuk menopang layanan publik harian kami. Saat dibutuhkan dukungan teknis, tim NOC dan engineer lapangan di Palembang merespons dengan sangat cepat.',
    ],
    [
        'client_name' => 'General Manager Operational',
        'role' => 'Hospitality Management',
        'company' => 'Hotel Bintang 4 & Convention Center — Palembang',
        'sector' => 'Perhotelan & Komersial',
        'service_used' => 'PABX & CCTV',
        'quote' => 'Sejak migrasi sistem PABX kamar dan Wi-Fi ballroom ditangani oleh PT. Kreatif Global Solusindo, komunikasi internal sangat lancar. Integrasi kamera CCTV dan Access Door juga sangat rapi.',
    ],
    [
        'client_name' => 'Kepala Bagian Sarana & Prasarana Medis',
        'role' => 'Manajemen Fasilitas Rumah Sakit',
        'company' => 'Rumah Sakit Rujukan Utama — Sumatera Selatan',
        'sector' => 'Kesehatan & RS',
        'service_used' => 'Fire Alarm, Public Address & Nursecall',
        'quote' => 'Instalasi sistem Fire Alarm, Public Address, dan Nursecall serta jaringan LAN SIMRS diselesaikan tepat waktu tanpa mengganggu operasional pelayanan pasien.',
    ],
];

$testi_submitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    $person_name = trim($_POST['person_name'] ?? '');
    $role = trim($_POST['role'] ?? 'Pelanggan');
    $company = trim($_POST['company'] ?? '');
    $sector = trim($_POST['sector'] ?? 'BUMN & Korporasi');
    $service_used = trim($_POST['service_used'] ?? 'Data Networking');
    $quote = trim($_POST['quote'] ?? '');

    if ($person_name !== '' && $company !== '' && $quote !== '') {
        array_unshift($testimonials, [
            'client_name' => $person_name,
            'role' => $role,
            'company' => $company,
            'sector' => $sector,
            'service_used' => $service_used,
            'quote' => $quote,
        ]);
        $testi_submitted = true;
    }
}

$bidang_options = [
    'Data Networking',
    'Radio Komunikasi (Repeater, HT, RIG)',
    'PABX',
    'CCTV',
    'Access Door',
    'Fire Alarm, Public Address & Nursecall',
    'Internet Service Provider',
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold uppercase tracking-wider mb-3">
            <span>★ Form Testimoni & Kepuasan Pelanggan</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3"><?= e($page_data['title']) ?></h1>
        <p class="text-slate-300 max-w-3xl text-sm sm:text-base leading-relaxed"><?= e($page_data['summary']) ?></p>
    </div>
</section>

<section class="py-16 bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- FORM UNTUK PELANGGAN MEMBERIKAN TESTIMONI -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-blue-500/70 p-6 sm:p-10 shadow-xl max-w-3xl mx-auto">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mb-2">Form Testimoni Pelanggan</h2>
            <p class="text-xs sm:text-sm text-slate-500 mb-6">Bagikan pengalaman kerjasama Anda bersama tim teknis PT. Kreatif Global Solusindo (KGSNet).</p>

            <?php if ($testi_submitted): ?>
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs sm:text-sm font-bold">
                    ✓ Terima kasih! Testimoni Anda berhasil dikirim dan telah ditampilkan di bawah ini.
                </div>
            <?php endif; ?>

            <form method="post" action="testimoni.php" class="space-y-4">
                <input type="hidden" name="submit_testimonial" value="1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Pelanggan *</label>
                        <input type="text" name="person_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Nama Lengkap Anda">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jabatan / Divisi</label>
                        <input type="text" name="role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Contoh: IT Manager / Kepala Bagian">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Perusahaan / Instansi / Pelanggan *</label>
                        <input type="text" name="company" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Nama Perusahaan / Instansi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bidang Layanan yang Digunakan *</label>
                        <select name="service_used" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm">
                            <?php foreach ($bidang_options as $b): ?>
                                <option value="<?= e($b) ?>"><?= e($b) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Isi Testimoni / Ulasan Pelanggan *</label>
                    <textarea name="quote" rows="3" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Tuliskan testimoni Anda..."></textarea>
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-extrabold transition cursor-pointer">
                    Kirim Testimoni Sekarang &rarr;
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($testimonials as $t): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <div class="text-amber-400 text-sm tracking-widest">★★★★★</div>
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-[10px] font-bold uppercase tracking-wider"><?= e($t['sector']) ?></span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed italic mb-5">"<?= e($t['quote']) ?>"</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 mb-2">✓ Layanan: <?= e($t['service_used']) ?></div>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white"><?= e($t['client_name']) ?></div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($t['role']) ?> · <?= e($t['company']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
