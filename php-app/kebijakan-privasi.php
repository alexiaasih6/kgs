<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$privacy_page = get_page_by_slug($pdo, 'kebijakan-privasi');
$page_title = (!empty($privacy_page['meta_title']) ? $privacy_page['meta_title'] : ($privacy_page['title'] ?: 'Kebijakan Privasi, SLA & Ketentuan Layanan')) . ' - PT. Kreatif Global Solusindo';
$page_description = !empty($privacy_page['meta_description']) ? $privacy_page['meta_description'] : ($privacy_page['summary'] ?: 'Kebijakan privasi pengguna, komitmen Service Level Agreement (SLA) KGSNet, dan ketentuan layanan instalasi infrastruktur PT. Kreatif Global Solusindo.');

require_once __DIR__ . '/includes/header.php';
?>

<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-400 font-bold text-xs uppercase tracking-widest" data-live-setting="privacy_badge"><?= e(get_setting($pdo, 'privacy_badge', 'Kepatuhan & Standar Layanan')) ?></span>
        <h1 data-live-page-slug="kebijakan-privasi" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-2"><?= e($privacy_page['title'] ?: 'Kebijakan Privasi & Ketentuan Layanan (SLA)') ?></h1>
        <p data-live-page-slug="kebijakan-privasi" data-live-page-field="summary" class="text-slate-300 text-sm mt-3">
            <?= e($privacy_page['summary'] ?: 'Komitmen perlindungan data pelanggan korporasi dan standar operasional layanan PT. Kreatif Global Solusindo.') ?>
        </p>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-sm text-slate-600 leading-relaxed">
        <?php if (!empty($privacy_page['content'])): ?>
            <div data-live-page-slug="kebijakan-privasi" data-live-page-field="content" class="bg-slate-50 p-7 rounded-2xl border border-slate-200 prose prose-slate max-w-none text-sm text-slate-600 leading-relaxed"><?= sanitize_rich_html($privacy_page['content']) ?></div>
        <?php endif; ?>

        <div class="bg-slate-50 p-7 rounded-2xl border border-slate-200 space-y-3">
            <h2 class="text-lg font-extrabold text-slate-900" data-live-setting="privacy_sec1_title"><?= e(get_setting($pdo, 'privacy_sec1_title', '1. Perlindungan Data & Kerahasiaan Topologi Klien')) ?></h2>
            <p data-live-setting="privacy_sec1_desc">
                <?= e(get_setting($pdo, 'privacy_sec1_desc', 'PT. Kreatif Global Solusindo (KGS) menjamin kerahasiaan seluruh informasi teknis, skema topologi jaringan, alamat IP publik/privat, konfigurasi firewall, serta titik koordinat kamera pengawas (CCTV) milik klien. Data yang dikirimkan melalui formulir konsultasi hanya digunakan untuk keperluan survei teknis, penyusunan penawaran harga (Quotation/RAB), dan administrasi kontrak kerja resmi.')) ?>
            </p>
        </div>

        <div class="bg-slate-50 p-7 rounded-2xl border border-slate-200 space-y-3">
            <h2 class="text-lg font-extrabold text-slate-900" data-live-setting="privacy_sec2_title"><?= e(get_setting($pdo, 'privacy_sec2_title', '2. Komitmen Layanan Internet ISP KGSNet (SLA)')) ?></h2>
            <p data-live-setting="privacy_sec2_desc">
                <?= e(get_setting($pdo, 'privacy_sec2_desc', 'Sebagai Penyelenggara Jasa Internet (ISP) resmi anggota APJII sejak tahun 2020, layanan KGSNet Dedicated Internet dan Broadband Corporate didukung oleh pemantauan NOC dan tim teknis lokal di Palembang untuk memastikan ketersediaan jaringan dan waktu tanggap gangguan sesuai kesepakatan kontrak (Service Level Agreement).')) ?>
            </p>
        </div>

        <div class="bg-slate-50 p-7 rounded-2xl border border-slate-200 space-y-3">
            <h2 class="text-lg font-extrabold text-slate-900" data-live-setting="privacy_sec3_title"><?= e(get_setting($pdo, 'privacy_sec3_title', '3. Standar Keselamatan Konstruksi & Pengujian Teknis')) ?></h2>
            <p data-live-setting="privacy_sec3_desc">
                <?= e(get_setting($pdo, 'privacy_sec3_desc', 'Seluruh pekerjaan fisik yang meliputi fabrikasi dan instalasi tower antena (Guywire / Triangle / Fourangle) serta pemasangan sistem penangkal petir & grounding dilaksanakan sesuai prosedur keselamatan kerja dan diuji menggunakan instrumen ukur terkalibrasi (seperti Digital Earth Tester dengan target resistansi di bawah 2 Ohm).')) ?>
            </p>
        </div>

        <div class="bg-slate-50 p-7 rounded-2xl border border-slate-200 space-y-3">
            <h2 class="text-lg font-extrabold text-slate-900" data-live-setting="privacy_sec4_title"><?= e(get_setting($pdo, 'privacy_sec4_title', '4. Kontak Resmi Kepatuhan & Layanan Pelanggan')) ?></h2>
            <p data-live-setting="privacy_sec4_desc">
                <?= e(get_setting($pdo, 'privacy_sec4_desc', 'Apabila Anda memiliki pertanyaan terkait kebijakan privasi atau membutuhkan verifikasi dokumen legalitas perusahaan, silakan hubungi kantor resmi kami:')) ?>
            </p>
            <ul class="space-y-1 text-xs font-semibold text-slate-800 pt-2">
                <li>• Alamat: <span data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></span></li>
                <li>• Telepon: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></span></li>
                <li>• WhatsApp: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></li>
                <li>• Email: <span data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?></span></li>
            </ul>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
