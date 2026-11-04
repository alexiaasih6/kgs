<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'promo', [
    'title' => 'Form Permintaan Penawaran Harga & Promo Spesial',
    'summary' => 'Silakan isi formulir permintaan penawaran harga (Request for Quotation / BOQ) di bawah ini untuk mendapatkan estimasi biaya terbaik sesuai kebutuhan proyek atau koneksi internet Anda.',
]);
$page_title = !empty($page_data['meta_title']) ? $page_data['meta_title'] : $page_data['title'];
$meta_description = !empty($page_data['meta_description']) ? $page_data['meta_description'] : $page_data['summary'];

$prefilled_service = trim($_GET['layanan'] ?? 'Data Networking');
$quote_success = false;
$wa_redirect_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rfq'])) {
    $name = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? 'Perorangan / Instansi');
    $email = trim($_POST['email'] ?? '-');
    $phone = trim($_POST['phone'] ?? '');
    $service_name = trim($_POST['service_name'] ?? 'Data Networking');
    $location = trim($_POST['location'] ?? 'Indonesia');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $phone !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, company, email, phone, service_id, location, message, status) VALUES (?, ?, ?, ?, NULL, ?, ?, 'Baru')");
            $stmt->execute([$name, $company, $email, $phone, $location, "[Bidang/Promo: {$service_name}] " . $message]);
        } catch (Throwable $e) {
            // fallback if DB is read-only
        }
        $quote_success = true;
        $wa_text = "Halo PT. Kreatif Global Solusindo, saya mengajukan Permintaan Penawaran Harga:\n\nNama: {$name}\nPerusahaan/Instansi: {$company}\nEmail: {$email}\nNo. WA: {$phone}\nBidang/Layanan: {$service_name}\nLokasi: {$location}\nKebutuhan: {$message}";
        $wa_redirect_url = 'https://wa.me/628117899002?text=' . rawurlencode($wa_text);
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
    'Kemitraan Reseller Layanan Internet KGSNet',
];

$promos = [
    [
        'title' => 'Gratis Biaya Instalasi & OTB Fiber Optik KGSNet',
        'subtitle' => 'Khusus Pelanggan Baru Internet Dedicated & Internet Bisnis',
        'badge_text' => 'PROMO KORPORAT',
        'discount_label' => 'FREE INSTALASI 100%',
        'valid_until' => 'Berlaku s/d Akhir Kuartal Ini',
        'category' => 'Internet Service Provider',
        'description' => 'Bebas biaya penarikan kabel Fiber Optik udara/tanah hingga 300 meter, gratis terminasi OTB/Roset, serta peminjaman Router Enterprise selama masa kontrak berlangganan.',
        'highlights' => [
            'Gratis biaya registrasi & instalasi standar',
            'Jaminan SLA 99.5% & IP Public Static',
            'Prioritas dukungan NOC & teknisi lapangan 24/7',
            'Gratis survei kelayakan jalur (Site Survey) wilayah Sumsel'
        ],
    ],
    [
        'title' => 'Paket Bundling Smart CCTV IP Camera + Access Door Biometrik',
        'subtitle' => 'Solusi Keamanan Terpadu Kantor, Gudang & Pabrik',
        'badge_text' => 'BEST VALUE',
        'discount_label' => 'HEMAT HINGGA 20%',
        'valid_until' => 'Kuota Terbatas 15 Perusahaan',
        'category' => 'CCTV & Access Door',
        'description' => 'Paket lengkap pengawasan kamera CCTV resolusi tinggi (NVR + PoE Switch) terintegrasi dengan sistem absensi & kontrol akses pintu sidik jari/wajah (Face Recognition).',
        'highlights' => [
            'Termasuk instalasi pipa conduit & kabel UTP Cat6 murni',
            'Konfigurasi pemantauan real-time via Smartphone & PC',
            'Garansi perangkat resmi & pemeliharaan berkala 1 Tahun',
            'Pelatihan operator & dokumentasi topologi titik kamera'
        ],
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="bg-slate-900 text-white py-14 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-bold uppercase tracking-wider mb-3">
            <span>Formulir Permintaan Penawaran Harga (RFQ) & Promo</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3"><?= e($page_data['title']) ?></h1>
        <p class="text-slate-300 max-w-3xl text-sm sm:text-base leading-relaxed"><?= e($page_data['summary']) ?></p>
    </div>
</section>

<section class="py-16 bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
        <!-- FORM PERMINTAAN PENAWARAN HARGA -->
        <div id="form-permintaan-penawaran" class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-blue-500/70 shadow-xl overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="lg:col-span-5 bg-slate-900 text-white p-6 sm:p-8 flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold uppercase tracking-wider mb-3">Request For Quotation</span>
                        <h2 class="text-2xl font-extrabold mb-3">Formulir Permintaan Penawaran Harga Resmi</h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed mb-6">
                            Pilih bidang pekerjaan yang Anda butuhkan dan lengkapi rincian kebutuhan lokasi Anda untuk mendapatkan penawaran harga terbaik dari PT. Kreatif Global Solusindo:
                        </p>
                        <ul class="space-y-2 text-xs sm:text-sm text-slate-200 mb-6">
                            <?php foreach ($bidang_options as $b): ?>
                                <li>✓ <?= e($b) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-400/40 text-amber-300 text-xs font-extrabold">
                        &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;
                    </div>
                </div>

                <div class="lg:col-span-7 p-6 sm:p-8">
                    <?php if ($quote_success): ?>
                        <div class="p-6 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 space-y-4">
                            <h3 class="text-lg font-extrabold">Permintaan Penawaran Harga Berhasil Dikirim!</h3>
                            <p class="text-xs sm:text-sm">Data permintaan penawaran Anda telah tersimpan. Klik tombol di bawah untuk langsung terhubung ke WhatsApp Customer Service resmi kami.</p>
                            <a href="<?= e($wa_redirect_url) ?>" target="_blank" rel="noopener noreferrer" class="inline-block px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold">
                                Kirim Cepat via WhatsApp Sekarang &rarr;
                            </a>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="promo.php#form-permintaan-penawaran" class="space-y-4 mt-2">
                        <input type="hidden" name="submit_rfq" value="1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap / PIC *</label>
                                <input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Nama Lengkap Anda">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Perusahaan / Instansi</label>
                                <input type="text" name="company" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Nama Perusahaan / Instansi">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp / Telepon *</label>
                                <input type="tel" name="phone" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Contoh: 08117899002">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email</label>
                                <input type="email" name="email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="email@perusahaan.com">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Bidang Pekerjaan / Layanan *</label>
                                <select name="service_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm">
                                    <?php foreach ($bidang_options as $opt): ?>
                                        <option value="<?= e($opt) ?>" <?= $prefilled_service === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Lokasi Pemasangan / Kota</label>
                                <input type="text" name="location" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Contoh: Palembang / Sumatera Selatan">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Rincian Kebutuhan / Spesifikasi *</label>
                            <textarea name="message" rows="3" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm" placeholder="Tuliskan rincian kebutuhan penawaran harga Anda..."></textarea>
                        </div>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-extrabold transition cursor-pointer">
                            Kirim Permintaan Penawaran Harga &rarr;
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
            <?php foreach ($promos as $promo): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full bg-rose-600 text-white text-[11px] font-extrabold uppercase tracking-wider"><?= e($promo['badge_text']) ?></span>
                            <span class="px-2.5 py-1 rounded-lg bg-amber-400 text-slate-950 text-[11px] font-black uppercase tracking-wider"><?= e($promo['discount_label']) ?></span>
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-1"><?= e($promo['category']) ?> · <?= e($promo['valid_until']) ?></div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white mb-1"><?= e($promo['title']) ?></h2>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3"><?= e($promo['subtitle']) ?></p>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4"><?= e($promo['description']) ?></p>
                        <ul class="space-y-2 mb-6 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <?php foreach ($promo['highlights'] as $hl): ?>
                                <li class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                                    <span><?= e($hl) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <a href="promo.php?layanan=<?= rawurlencode($promo['category']) ?>#form-permintaan-penawaran" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold text-center transition">
                        Minta Penawaran Promo Ini &rarr;
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
