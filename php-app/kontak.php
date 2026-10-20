<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/database.php';

$page_title = "Kontak & Permintaan Penawaran";
$success = false;
$error = '';
$wa_redirect_url = '';

$prefilled_layanan = trim($_GET['layanan'] ?? '');
$company_name = get_setting($pdo, 'company_name') ?: 'PT. KREATIF GLOBAL SOLUSINDO';
$cs_whatsapp = get_setting($pdo, 'whatsapp') ?: '0811-782119';
$clean_cs_wa = preg_replace('/[^0-9]/', '', $cs_whatsapp);
if (substr($clean_cs_wa, 0, 1) === '0') {
    $clean_cs_wa = '62' . substr($clean_cs_wa, 1);
} elseif (substr($clean_cs_wa, 0, 1) === '8') {
    $clean_cs_wa = '62' . $clean_cs_wa;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Anti-spam Honeypot & Rate Limiting (Session + IP)
    $honeypot = trim($_POST['website_url_hp'] ?? ($_POST['website_url'] ?? ''));
    $last_inq_time = (int)($_SESSION['last_inquiry_ts'] ?? 0);
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $ip_recent_count = 0;
    try {
        $stmtRate = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE action = 'PUBLIC_INQUIRY_SUBMIT' AND ip_address = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
        $stmtRate->execute([$client_ip]);
        $ip_recent_count = (int)$stmtRate->fetchColumn();
    } catch (Exception $e) {
        $ip_recent_count = 0;
    }

    $name         = clean_plain_text(strip_tags($_POST['name'] ?? ''), 100);
    $company      = clean_plain_text(strip_tags($_POST['company'] ?? ''), 150);
    $email        = clean_plain_text($_POST['email'] ?? '', 100);
    $phone        = clean_plain_text(strip_tags($_POST['phone'] ?? ''), 50);
    $service_name = clean_plain_text(strip_tags($_POST['service_name'] ?? ''), 150);
    $location     = clean_plain_text(strip_tags($_POST['location'] ?? 'Palembang / Sumatera Selatan'), 100);
    $message      = clean_plain_text(strip_tags($_POST['message'] ?? ''), 2000);

    if ($honeypot !== '') {
        $error = 'Permintaan terdeteksi sebagai spam otomatis.';
    } elseif (time() - $last_inq_time < 15 || $ip_recent_count >= 5) {
        $error = 'Terlalu banyak pengiriman dalam waktu singkat. Mohon tunggu beberapa saat sebelum mengirim kembali.';
    } elseif ($name === '' || $company === '' || $email === '' || $phone === '' || $service_name === '') {
        $error = 'Silakan lengkapi seluruh kolom wajib.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (!preg_match('/^[0-9+\-\s()]{7,30}$/', $phone)) {
        $error = 'Format nomor telepon / WhatsApp tidak valid (gunakan angka 7-30 digit).';
    } else {
        $stmt = $pdo->prepare("INSERT INTO inquiries (name, company, email, phone, service_name, location, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$name, $company, $email, $phone, $service_name, $location ?: 'Palembang / Sumatera Selatan', $message]);
        if ($ok) {
            $_SESSION['last_inquiry_ts'] = time();
            log_audit($pdo, 'PUBLIC_INQUIRY_SUBMIT', "Permintaan penawaran baru dari {$name} ({$company}) - Layanan: {$service_name}");
            $success = true;

            $wa_msg = "Halo CS *{$company_name}*,\n"
                . "Saya mengajukan *Permintaan Penawaran & Konsultasi* melalui website resmi:\n\n"
                . "*Nama Lengkap:* {$name}\n"
                . "*Perusahaan / Instansi:* {$company}\n"
                . "*Email Resmi:* {$email}\n"
                . "*No. Telepon / WA:* {$phone}\n"
                . "*Kategori Layanan:* {$service_name}\n"
                . "*Lokasi Proyek / Site:* " . ($location ?: 'Palembang / Sumatera Selatan') . "\n"
                . "*Catatan Kebutuhan Teknis:* " . ($message ?: '-') . "\n\n"
                . "Mohon informasi dan tindak lanjut penawarannya. Terima kasih.";

            $wa_redirect_url = "https://wa.me/{$clean_cs_wa}?text=" . rawurlencode($wa_msg);
        } else {
            $error = 'Gagal menyimpan permintaan penawaran. Silakan coba lagi.';
        }
    }
}

$services = $pdo->query("SELECT name FROM services WHERE is_published = 1 ORDER BY sort_order ASC")->fetchAll();
$google_map_raw = trim(get_setting($pdo, 'google_map_input') ?: 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
$map_embed_url = get_google_map_embed_url($google_map_raw);
$map_direct_url = get_google_map_direct_url($google_map_raw);

$kontak_page = get_page_by_slug($pdo, 'kontak');
$page_title = !empty($kontak_page['meta_title']) ? $kontak_page['meta_title'] : ($kontak_page['title'] ?? "Kontak & Permintaan Penawaran");
$page_desc = $kontak_page['meta_description'] ?? $kontak_page['summary'] ?? '';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white border-b border-slate-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block mb-1">Kontak & Kantor Operasional Resmi</span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight" data-live-page-slug="kontak" data-live-page-field="title"><?= e($kontak_page['title'] ?: ('Hubungi ' . $company_name)) ?></h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-3xl leading-relaxed" data-live-page-slug="kontak" data-live-page-field="summary">
            <?= e($kontak_page['summary'] ?: 'Alamat kantor operasional resmi kami siap melayani konsultasi teknis infrastruktur IT dan telekomunikasi perusahaan Anda. Permintaan penawaran langsung terhubung ke WhatsApp CS resmi kami.') ?>
        </p>
        <?php if (!empty($kontak_page['content'])): ?>
            <div class="mt-3 text-xs text-slate-600 leading-relaxed max-w-4xl prose" data-live-page-slug="kontak" data-live-page-field="content">
                <?= sanitize_rich_html($kontak_page['content']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Left: Office Contacts & Google Maps (5 cols) -->
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <span class="text-xs font-bold text-blue-700 uppercase tracking-wide block mb-3">Alamat Kantor</span>
                <p class="text-xs text-slate-700 leading-relaxed space-y-2">
                    <span class="block">📍 <span data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang') ?: 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113') ?></span></span>
                    <span class="block">📞 Telp: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang') ?: '0711-316088 (Hunting)') ?></span></span>
                    <span class="block text-emerald-600 font-semibold">💬 WhatsApp CS: <span data-live-setting="whatsapp"><?= e($cs_whatsapp) ?></span></span>
                    <span class="block">✉️ Email: <span data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?></span></span>
                </p>
            </div>

            <!-- Interactive Google Maps Card -->
            <div id="peta-lokasi" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <span class="text-xs font-bold text-slate-900">🗺️ Peta Lokasi Google Maps</span>
                    <a href="<?= e($map_direct_url) ?>" target="_blank" rel="noopener noreferrer" class="text-[11px] font-semibold text-blue-600 hover:underline">
                        Buka Maps &nearr;
                    </a>
                </div>
                <div class="w-full h-64 bg-slate-100">
                    <iframe
                        title="Peta Lokasi Kantor PT. KGS"
                        src="<?= e($map_embed_url) ?>"
                        class="w-full h-full border-0"
                        loading="lazy"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>

            <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-xs text-xs space-y-2">
                <span class="text-blue-400 font-bold block mb-1">Layanan Daring & Legalitas:</span>
                <div>Website: <span data-live-setting="website_corporate"><?= e(get_setting($pdo, 'website_corporate', 'www.creative.co.id')) ?></span> / <span data-live-setting="website_isp"><?= e(get_setting($pdo, 'website_isp', 'www.kgs.net.id')) ?></span></div>
                <div data-live-setting="business_focus">Status: <?= e(get_setting($pdo, 'business_focus', 'Penyelenggara Jasa Internet Resmi (KGSNet) & Anggota APJII')) ?></div>
            </div>
        </div>

        <!-- Right: Inquiry / Consultation Form (7 cols) -->
        <div id="konsultasi" class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                    <h3 class="text-lg font-bold text-slate-900">Formulir Permintaan Penawaran Resmi</h3>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold">
                        💬 Direct WhatsApp CS (<?= e($cs_whatsapp) ?>)
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-6">Silakan isi formulir di bawah ini. Data Anda tercatat di sistem dan langsung mengarahkan ke WhatsApp CS resmi kami.</p>

                <?php if ($success): ?>
                <div class="p-6 bg-emerald-50 border border-emerald-200 rounded-xl text-center space-y-4">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto font-bold text-lg">✓</div>
                    <h4 class="text-base font-bold text-emerald-950">Permintaan Penawaran Diteruskan ke WhatsApp CS!</h4>
                    <p class="text-xs text-emerald-800">Data penawaran Anda telah tersimpan dan diarahkan langsung ke WhatsApp CS resmi kami (<?= e($cs_whatsapp) ?>).</p>
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                        <a href="<?= e($wa_redirect_url) ?>" target="_blank" rel="noopener noreferrer" id="autoWaBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                            💬 Buka Chat WhatsApp CS (<?= e($cs_whatsapp) ?>)
                        </a>
                        <a href="kontak.php" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl">
                            Kirim Permintaan Lain
                        </a>
                    </div>
                </div>
                <script>
                    window.addEventListener('DOMContentLoaded', function() {
                        var btn = document.getElementById('autoWaBtn');
                        if (btn) btn.click();
                    });
                </script>
                <?php else: ?>

                <?php if (!empty($error)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-lg mb-4">
                    <?= e($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="kontak.php#konsultasi" class="space-y-4 text-xs">
                    <?= csrf_field() ?>
                    <!-- Honeypot anti-spam -->
                    <div class="hidden" aria-hidden="true">
                        <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" required placeholder="Ir. Budi Santoso" class="w-full border rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Nama Perusahaan / Instansi *</label>
                            <input type="text" name="company" required placeholder="PT. Sawit Makmur Jaya" class="w-full border rounded-lg p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Alamat Email Perusahaan *</label>
                            <input type="email" name="email" required placeholder="budi@sawitmakmur.co.id" class="w-full border rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Nomor Telepon / WhatsApp *</label>
                            <input type="tel" name="phone" required placeholder="0812-xxxx-xxxx" class="w-full border rounded-lg p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Kategori Layanan Yang Dibutuhkan *</label>
                            <select name="service_name" required class="w-full border rounded-lg p-2.5">
                                <?php foreach ($services as $srv): ?>
                                    <option value="<?= e($srv['name']) ?>" <?= ($prefilled_layanan === $srv['name']) ? 'selected' : '' ?>>
                                        <?= e($srv['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Lokasi Proyek / Site</label>
                            <input type="text" name="location" placeholder="Kab. Musi Banyuasin, Sumsel" class="w-full border rounded-lg p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Catatan Kebutuhan Spesifikasi</label>
                        <textarea name="message" rows="3" placeholder="Sebutkan kebutuhan perkiraan (misal: panjang penarikan fiber optik, kapasitas ekstensi PABX, atau tinggi tower)..." class="w-full border rounded-lg p-2.5"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Kirim Permintaan Penawaran (Direct WhatsApp CS)
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
