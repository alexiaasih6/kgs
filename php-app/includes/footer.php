<?php
// Proteksi akses langsung ke file include
if (basename($_SERVER['PHP_SELF'] ?? '') === 'footer.php') {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

require_once __DIR__ . '/auth.php';

// Ambil menu navigasi lokasi 'footer' yang aktif dari tabel `menus` database
$footer_menus = get_navigation_menus($pdo, 'footer', true);
$footer_certs = [];

try {
    $footer_certs = get_company_certifications($pdo);
} catch (Exception $e) {
    $footer_certs = [];
}

$footer_logo_path = trim(get_setting($pdo, 'footer_logo', ''));
if ($footer_logo_path === '') {
    $footer_logo_path = trim(get_setting($pdo, 'logo', ''));
}
if ($footer_logo_path === '') {
    $footer_logo_path = trim(get_setting($pdo, 'company_logo', ''));
}

$raw_cert_badges = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
$cert_badges_list = array_filter(array_map('trim', preg_split('/[|\n]+/', (string)$raw_cert_badges)));
if (empty($cert_badges_list)) {
    $cert_badges_list = ['APJII Official Member', 'KGSNet ISP Resmi', 'Panasonic Authorized', 'NETVIEL Certified'];
}

$default_copyright = '© ' . date('Y') . ' ' . get_setting($pdo, 'footer_brand_label', 'KGSNET') . ' — Creative.co.id. All Rights Reserved.';
$copyright_full = get_setting($pdo, 'copyright_text', $default_copyright);
if (trim($copyright_full) === 'All Rights Reserved.') {
    $copyright_full = $default_copyright;
}
?>
</main>

<!-- Footer Korporat Utama (Seluruh Bagian Dinamis & Dapat Diatur dari Admin CMS / Mode Edit Langsung) -->
<footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <?php if (!empty($_SESSION['admin_logged_in'])): ?>
            <div class="mb-8 p-3.5 rounded-2xl bg-slate-800/90 border border-slate-700 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-emerald-400 font-bold">
                    <span>⚙️ Panel Kendali Footer Aktif:</span>
                    <span class="text-slate-300 font-normal">Anda dapat mengklik teks/logo di bawah pada Mode Edit Langsung atau mengatur seluruh bagian Footer di Admin CMS.</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="admin/pengaturan.php#pengaturan-footer" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition">Edit Seluruh Pengaturan Footer &rarr;</a>
                    <a href="admin/navigasi.php" class="px-3 py-1.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold transition">Atur Menu Navigasi Footer</a>
                    <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="px-3 py-1.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold transition">Atur Sertifikasi & Lisensi</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
            <!-- Kolom 1: Identitas, Logo, Tentang Perusahaan & Lisensi Sertifikasi (4 cols) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center gap-3">
                    <?php if (!empty($footer_logo_path) && file_exists(__DIR__ . '/../' . $footer_logo_path)): ?>
                        <img src="<?= e($footer_logo_path) ?>?v=<?= time() ?>" alt="Logo Footer" data-live-img="logo" class="h-10 w-auto bg-white p-1 rounded-lg object-contain">
                    <?php else: ?>
                        <div data-live-img="logo" title="Klik pada Mode Edit Langsung untuk mengunggah Logo" class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-base cursor-pointer">KGS</div>
                    <?php endif; ?>
                    <div>
                        <span class="font-extrabold text-white text-sm block" data-live-setting="footer_brand_label"><?= e(get_setting($pdo, 'footer_brand_label', get_setting($pdo, 'short_name', 'KGSNET'))) ?></span>
                        <span class="text-[11px] text-blue-400 font-medium block" data-live-setting="company_name"><?= e(get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO')) ?></span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed" data-live-setting="footer_description">
                    <?= e(get_setting($pdo, 'footer_description', get_setting($pdo, 'company_profile', 'Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.'))) ?>
                </p>

                <!-- Lisensi, Sertifikasi & Keanggotaan di Footer -->
                <div class="pt-2 space-y-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400" data-live-setting="footer_cert_heading">
                        <?= e(get_setting($pdo, 'footer_cert_heading', 'Lisensi, Sertifikasi & Keanggotaan')) ?>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px]" data-live-setting="footer_cert_badges">
                        <?php foreach ($cert_badges_list as $idx => $badge_txt): ?>
                            <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 <?= $idx === 1 ? 'text-emerald-400' : 'text-slate-300' ?> font-semibold"><?= e($badge_txt) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom 2: Alamat Kantor & Kontak (4 cols) -->
            <div class="lg:col-span-4 space-y-3 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-3" data-live-setting="footer_contact_heading">
                    <?= e(get_setting($pdo, 'footer_contact_heading', 'Alamat Kantor & Kontak')) ?>
                </h3>
                <div class="p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/70 space-y-1.5">
                    <div class="text-blue-400 font-bold" data-live-setting="footer_address_label"><?= e(get_setting($pdo, 'footer_address_label', 'Alamat Kantor:')) ?></div>
                    <p class="text-slate-300 leading-relaxed" data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></p>
                    <p class="text-slate-400 pt-1">Telp: <strong class="text-white" data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></strong></p>
                </div>
                <div class="pt-1 space-y-1 text-slate-300">
                    <div>💬 WhatsApp Resmi: <a href="https://wa.me/<?= e(format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'))) ?>" target="_blank" rel="noopener noreferrer" class="text-emerald-400 font-bold hover:underline" data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></a></div>
                    <div>✉️ Email Korporat: <a href="mailto:<?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?>" class="text-blue-400 hover:underline" data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?></a></div>
                </div>
            </div>

            <!-- Kolom 3: Navigasi Halaman Dinamis dari Database (2 cols) -->
            <div class="lg:col-span-2 space-y-2.5 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-3" data-live-setting="footer_nav_heading">
                    <?= e(get_setting($pdo, 'footer_nav_heading', 'Navigasi Halaman')) ?>
                </h3>
                <ul class="space-y-2 text-slate-400">
                    <?php foreach ($footer_menus as $fm): 
                        $fm_url = sanitize_nav_url((string)($fm['url'] ?? '#'));
                        $fm_target = ($fm['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        $fm_live_attr = !empty($fm['id']) ? ' data-live-record="menus:' . (int)$fm['id'] . ':title"' : '';
                    ?>
                        <li>
                            <a href="<?= e($fm_url) ?>"<?= $fm_target ?><?= $fm_live_attr ?> class="hover:text-white transition">
                                <?= e($fm['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Kolom 4: Informasi & Akses Cepat (2 cols) -->
            <div class="lg:col-span-2 space-y-2.5 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-3" data-live-setting="footer_quick_heading">
                    <?= e(get_setting($pdo, 'footer_quick_heading', 'Informasi & Akses Cepat')) ?>
                </h3>
                <ul class="space-y-2 text-slate-400">
                    <li>
                        <a href="<?= e(sanitize_nav_url(get_setting($pdo, 'footer_quick_1_url', 'kebijakan-privasi.php'))) ?>" class="hover:text-white transition" data-live-setting="footer_quick_1_label">
                            <?= e(get_setting($pdo, 'footer_quick_1_label', 'Kebijakan Privasi & SLA')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(sanitize_nav_url(get_setting($pdo, 'footer_quick_2_url', 'harga.php'))) ?>" class="hover:text-white transition" data-live-setting="footer_quick_2_label">
                            <?= e(get_setting($pdo, 'footer_quick_2_label', 'Katalog Paket & Harga B2B')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(sanitize_nav_url(get_setting($pdo, 'footer_quick_3_url', 'faq.php'))) ?>" class="hover:text-white transition" data-live-setting="footer_quick_3_label">
                            <?= e(get_setting($pdo, 'footer_quick_3_label', 'Pertanyaan Umum (FAQ)')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(sanitize_nav_url(get_setting($pdo, 'footer_quick_4_url', 'kontak.php#konsultasi'))) ?>" class="hover:text-white transition" data-live-setting="footer_quick_4_label">
                            <?= e(get_setting($pdo, 'footer_quick_4_label', 'Minta Penawaran / Survei')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(sanitize_nav_url(get_setting($pdo, 'footer_quick_5_url', 'kontak.php#peta-lokasi'))) ?>" class="hover:text-white transition" data-live-setting="footer_quick_5_label">
                            <?= e(get_setting($pdo, 'footer_quick_5_label', 'Peta Lokasi Google Maps')) ?>
                        </a>
                    </li>
                    <li class="pt-2 border-t border-slate-800">
                        <a href="admin/login.php" class="text-slate-400 hover:text-blue-400 font-semibold transition flex items-center gap-1.5">
                            <span>🔒</span>
                            <span data-live-setting="footer_admin_link_label"><?= e(get_setting($pdo, 'footer_admin_link_label', 'Login Administrator CMS')) ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Baris Sertifikasi & Keanggotaan Resmi di Footer (Opsional / Dinamis dari Tabel `company_certifications`) -->
        <?php if (!empty($footer_certs)): ?>
            <div class="mt-10 pt-8 border-t border-slate-800/80">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400" data-live-setting="footer_cert_card_title">
                        <?= e(get_setting($pdo, 'footer_cert_card_title', 'Sertifikasi & Keanggotaan Resmi')) ?>
                    </span>
                    <span class="text-[11px] text-emerald-400 font-semibold" data-live-setting="footer_cert_card_status">
                        <?= e(get_setting($pdo, 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi')) ?>
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <?php foreach (array_slice($footer_certs, 0, 4) as $fc): ?>
                        <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/70 flex items-start gap-2.5">
                            <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-300 font-extrabold text-[10px] shrink-0" data-live-record="company_certifications:<?= (int)$fc['id'] ?>:badge_code"><?= e($fc['badge_code']) ?></span>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-200 truncate" data-live-record="company_certifications:<?= (int)$fc['id'] ?>:title"><?= e($fc['title']) ?></div>
                                <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5" data-live-record="company_certifications:<?= (int)$fc['id'] ?>:description"><?= e($fc['description']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Baris Hak Cipta & Domain Bawah -->
        <div class="border-t border-slate-800 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <div data-live-setting="copyright_text" class="text-slate-400 font-medium">
                <?= e($copyright_full) ?>
            </div>
            <div class="flex gap-4">
                <span data-live-setting="website_corporate"><?= e(get_setting($pdo, 'website_corporate', 'www.creative.co.id')) ?></span>
                <span>|</span>
                <span data-live-setting="website_isp"><?= e(get_setting($pdo, 'website_isp', 'www.kgs.net.id')) ?></span>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<?php $wa_num = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119')); ?>
<a href="https://wa.me/<?= e($wa_num) ?>?text=<?= rawurlencode('Halo Tim PT. Kreatif Global Solusindo (KGS), saya ingin berkonsultasi mengenai layanan teknologi / KGSNet.') ?>"
   target="_blank" rel="noopener noreferrer"
   class="fixed bottom-5 right-5 z-40 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-3 rounded-full shadow-xl flex items-center gap-2 text-xs font-bold transition transform hover:scale-105">
    <span>💬 Hubungi CS KGS</span>
</a>

<?php if (!empty($_SESSION['admin_logged_in'])): 
    require_once __DIR__ . '/csrf.php';
    $live_csrf = generate_csrf_token();
?>
<!-- ========================================================================
     ADMIN LIVE VISUAL WEBSITE EDITOR BAR (HANYA TAMPIL JIKA ADMIN LOGIN)
     Aktif di seluruh halaman menu: Beranda, Tentang, Layanan, Harga,
     Portofolio, Klien, Artikel, FAQ, Kontak, serta seluruh bagian Footer.
     ======================================================================== -->
<style id="kgs-live-edit-styles"></style>

<div id="kgs-live-bar" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 max-w-[96vw]">
    <div class="bg-slate-900/95 backdrop-blur-md text-white border border-slate-700 rounded-2xl px-3.5 py-2.5 shadow-2xl flex flex-wrap items-center gap-2 text-xs">
        <button type="button" id="btn-toggle-live-edit" class="px-3.5 py-2 rounded-xl font-extrabold bg-blue-600 hover:bg-blue-500 text-white transition cursor-pointer">
            ✏️ Aktifkan Mode Edit Langsung Website
        </button>
        <a href="admin/bagian-halaman.php" class="px-3 py-2 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition">
            📑 Semua Bagian Halaman
        </a>
        <a href="admin/pengaturan.php#pengaturan-footer" class="px-3 py-2 rounded-xl font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition">
            🦶 Pengaturan Footer
        </a>
        <a href="admin/tema-slider.php" class="px-3 py-2 rounded-xl font-bold bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700 transition">
            🎨 Tema & Slider
        </a>
        <a href="admin/index.php" class="px-3 py-2 rounded-xl font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
            ⚙️ Dashboard CMS
        </a>
    </div>
</div>

<!-- Modal Edit Teks / Gambar Langsung -->
<div id="kgs-live-modal" class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs text-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div>
                <h3 id="kgs-modal-title" class="font-extrabold text-sm text-slate-900">Edit Konten Website</h3>
                <p class="text-[11px] text-slate-500">Tersimpan langsung ke database MySQL & Admin CMS</p>
            </div>
            <button type="button" id="btn-close-live-modal" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold cursor-pointer">✕</button>
        </div>

        <form id="form-live-edit" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e($live_csrf) ?>">
            <input type="hidden" name="action" id="live-input-action" value="">
            <input type="hidden" name="key" id="live-input-key" value="">
            <input type="hidden" name="slug" id="live-input-slug" value="">
            <input type="hidden" name="field" id="live-input-field" value="">
            <input type="hidden" name="table" id="live-input-table" value="">
            <input type="hidden" name="id" id="live-input-id" value="">
            <input type="hidden" name="target" id="live-input-target" value="">

            <div id="live-text-group">
                <label class="block font-bold text-slate-700 mb-1">Isi Teks</label>
                <textarea name="value" id="live-input-value" rows="4" class="w-full border border-slate-300 rounded-xl p-3 text-slate-900 font-medium focus:outline-none focus:border-blue-600"></textarea>
            </div>

            <div id="live-img-group" class="hidden space-y-3">
                <label class="block font-bold text-slate-700 mb-1">Pilih Berkas Gambar Baru (JPG, PNG, WebP)</label>
                <input type="file" name="image" id="live-input-file" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
            </div>

            <div id="live-status-msg" class="hidden p-3 rounded-xl font-bold"></div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" id="btn-cancel-live-modal" class="px-4 py-2 rounded-xl border border-slate-300 font-semibold cursor-pointer">Batal</button>
                <button type="submit" id="btn-save-live-modal" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold cursor-pointer">Simpan ke Database</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('live_edit') === '1') {
        try { sessionStorage.setItem('kgs_live_edit_mode', '1'); } catch (e) {}
    } else if (urlParams.get('live_edit') === '0') {
        try { sessionStorage.setItem('kgs_live_edit_mode', '0'); } catch (e) {}
    }

    var liveActive = false;
    try {
        liveActive = sessionStorage.getItem('kgs_live_edit_mode') === '1';
    } catch (e) {
        liveActive = <?= (isset($_GET['live_edit']) && $_GET['live_edit'] === '1') ? 'true' : 'false' ?>;
    }

    var styleEl = document.getElementById('kgs-live-edit-styles');
    var btnToggle = document.getElementById('btn-toggle-live-edit');
    var modal = document.getElementById('kgs-live-modal');
    var form = document.getElementById('form-live-edit');
    var activeDomEl = null;

    function updateLiveUI() {
        try { sessionStorage.setItem('kgs_live_edit_mode', liveActive ? '1' : '0'); } catch (e) {}
        if (liveActive) {
            btnToggle.textContent = '✅ Mode Edit Langsung: AKTIF (Klik Teks / Gambar di Halaman Ini)';
            btnToggle.className = 'px-3.5 py-2 rounded-xl font-extrabold bg-emerald-500 text-slate-950 transition cursor-pointer';
            styleEl.textContent = '[data-live-setting]:hover, [data-live-page-slug]:hover, [data-live-record]:hover, [data-live-img]:hover { outline: 2px dashed #10b981 !important; outline-offset: 4px !important; cursor: pointer !important; }';
        } else {
            btnToggle.textContent = '✏️ Aktifkan Mode Edit Langsung Website';
            btnToggle.className = 'px-3.5 py-2 rounded-xl font-extrabold bg-blue-600 hover:bg-blue-500 text-white transition cursor-pointer';
            styleEl.textContent = '';
        }
    }

    btnToggle.addEventListener('click', function() {
        liveActive = !liveActive;
        updateLiveUI();
    });

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('live-status-msg').classList.add('hidden');
    }

    document.getElementById('btn-close-live-modal').addEventListener('click', closeModal);
    document.getElementById('btn-cancel-live-modal').addEventListener('click', closeModal);

    document.addEventListener('click', function(ev) {
        if (!liveActive) return;
        if (ev.target.closest('#kgs-live-bar') || ev.target.closest('#kgs-live-modal')) return;

        var el = ev.target.closest('[data-live-setting], [data-live-page-slug], [data-live-record], [data-live-img]');
        if (!el) return;

        ev.preventDefault();
        ev.stopPropagation();
        activeDomEl = el;

        var settingKey = el.getAttribute('data-live-setting');
        var pageSlug = el.getAttribute('data-live-page-slug');
        var pageField = el.getAttribute('data-live-page-field') || 'title';
        var recordSpec = el.getAttribute('data-live-record');
        var imgTarget = el.getAttribute('data-live-img');

        document.getElementById('live-input-file').value = '';
        document.getElementById('live-status-msg').classList.add('hidden');

        if (imgTarget) {
            document.getElementById('kgs-modal-title').textContent = 'Upload / Ganti Gambar (' + imgTarget + ')';
            document.getElementById('live-input-action').value = 'upload_image';
            document.getElementById('live-input-target').value = imgTarget;
            document.getElementById('live-text-group').classList.add('hidden');
            document.getElementById('live-img-group').classList.remove('hidden');
        } else if (settingKey) {
            document.getElementById('kgs-modal-title').textContent = 'Edit Pengaturan Website (' + settingKey + ')';
            document.getElementById('live-input-action').value = 'site_setting';
            document.getElementById('live-input-key').value = settingKey;
            document.getElementById('live-input-value').value = el.innerText.trim();
            document.getElementById('live-text-group').classList.remove('hidden');
            document.getElementById('live-img-group').classList.add('hidden');
        } else if (pageSlug) {
            document.getElementById('kgs-modal-title').textContent = 'Edit Konten Halaman (' + pageSlug + ' — ' + pageField + ')';
            document.getElementById('live-input-action').value = 'page_field';
            document.getElementById('live-input-slug').value = pageSlug;
            document.getElementById('live-input-field').value = pageField;
            document.getElementById('live-input-value').value = el.innerText.trim();
            document.getElementById('live-text-group').classList.remove('hidden');
            document.getElementById('live-img-group').classList.add('hidden');
        } else if (recordSpec) {
            var parts = recordSpec.split(':');
            if (parts.length === 3) {
                document.getElementById('kgs-modal-title').textContent = 'Edit Data (' + parts[0] + ' #' + parts[1] + ' — ' + parts[2] + ')';
                document.getElementById('live-input-action').value = 'db_record';
                document.getElementById('live-input-table').value = parts[0];
                document.getElementById('live-input-id').value = parts[1];
                document.getElementById('live-input-field').value = parts[2];
                document.getElementById('live-input-value').value = el.innerText.trim();
                document.getElementById('live-text-group').classList.remove('hidden');
                document.getElementById('live-img-group').classList.add('hidden');
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }, true);

    form.addEventListener('submit', function(ev) {
        ev.preventDefault();
        var btnSave = document.getElementById('btn-save-live-modal');
        var statusBox = document.getElementById('live-status-msg');
        btnSave.disabled = true;
        btnSave.textContent = 'Menyimpan...';

        var fd = new FormData(form);
        fetch('admin/api-live-edit.php', {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan ke Database';
            statusBox.classList.remove('hidden');
            if (res.ok) {
                statusBox.className = 'p-3 rounded-xl font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                statusBox.textContent = '✓ ' + res.message;
                if (activeDomEl) {
                    if (res.image_url) {
                        if (activeDomEl.tagName === 'IMG') {
                            activeDomEl.src = res.image_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        var newVal = document.getElementById('live-input-value').value;
                        activeDomEl.textContent = newVal;
                        var sKey = activeDomEl.getAttribute('data-live-setting');
                        if (sKey) {
                            document.querySelectorAll('[data-live-setting="' + sKey + '"]').forEach(function(node) {
                                node.textContent = newVal;
                            });
                        }
                    }
                }
                setTimeout(closeModal, 700);
            } else {
                statusBox.className = 'p-3 rounded-xl font-bold bg-rose-50 text-rose-700 border border-rose-200';
                statusBox.textContent = '✕ ' + (res.message || 'Gagal menyimpan perubahan.');
            }
        })
        .catch(function() {
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan ke Database';
            statusBox.classList.remove('hidden');
            statusBox.className = 'p-3 rounded-xl font-bold bg-rose-50 text-rose-700 border border-rose-200';
            statusBox.textContent = '✕ Terjadi kesalahan koneksi ke server.';
        });
    });

    updateLiveUI();
})();
</script>
<?php endif; ?>

</body>
</html>
