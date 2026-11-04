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
if (strpos($footer_logo_path, 'image_8b7e60bd') !== false || $footer_logo_path === 'assets/images/logo-kgs.svg') {
    $footer_logo_path = '';
}
$footer_logo_path = ltrim($footer_logo_path, '/');

$footer_show_cert_section = get_setting($pdo, 'footer_show_cert_section', '1') !== '0';
$footer_show_cert_status  = get_setting($pdo, 'footer_show_cert_status', '1') !== '0';
$footer_show_cert_badges  = get_setting($pdo, 'footer_show_cert_badges', '1') !== '0';

$raw_cert_badges = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
$parsed_cert_badges = function_exists('parse_footer_cert_badges')
    ? parse_footer_cert_badges((string)$raw_cert_badges)
    : array_map(function($t) { return ['label' => trim($t), 'url' => '', 'logo' => '']; }, array_filter(preg_split('/[|\n]+/', (string)$raw_cert_badges)));
if (empty($parsed_cert_badges)) {
    $parsed_cert_badges = [
        ['label' => 'APJII Official Member', 'url' => 'https://apjii.or.id', 'logo' => ''],
        ['label' => 'KGSNet ISP Resmi', 'url' => 'https://www.kgs.net.id', 'logo' => ''],
        ['label' => 'Panasonic Authorized', 'url' => '', 'logo' => ''],
        ['label' => 'NETVIEL Certified', 'url' => '', 'logo' => ''],
    ];
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
                <?php
                $f_logo_bg    = sanitize_hex_color(get_setting($pdo, 'logo_bg_color', '#2563eb'), '#2563eb');
                $f_logo_text  = sanitize_hex_color(get_setting($pdo, 'logo_text_color', '#ffffff'), '#ffffff');
                $f_cname_col  = sanitize_hex_color(get_setting($pdo, 'company_name_color', '#ffffff'), '#ffffff');
                if (strtolower($f_cname_col) === '#0f172a') {
                    $f_cname_col = '#ffffff';
                }
                $f_tagline_col = sanitize_hex_color(get_setting($pdo, 'tagline_color', '#60a5fa'), '#60a5fa');
                if (strtolower($f_tagline_col) === '#2563eb') {
                    $f_tagline_col = '#60a5fa';
                }
                $f_raw_tagline = get_setting($pdo, 'tagline', 'System Integrator, Security Solution & Internet Provider');
                if ($f_raw_tagline === 'Your Solution & Partner' || strcasecmp($f_raw_tagline, 'System Integrator, Security Solution & Internet Service Provider') === 0) {
                    $f_raw_tagline = 'System Integrator, Security Solution & Internet Provider';
                }
                ?>
                <div class="flex flex-col justify-center min-w-0 text-left">
                    <div class="flex flex-row items-center gap-2 sm:gap-2.5 min-w-0">
                        <?php if (!empty($footer_logo_path)): ?>
                            <img src="<?= e($footer_logo_path) ?>" alt="Logo PT. Kreatif Global Solusindo" data-live-img="logo" class="h-7 sm:h-9 w-auto max-w-[36px] sm:max-w-[120px] bg-white p-0.5 rounded-lg border border-slate-700 object-contain shrink-0">
                        <?php else: ?>
                            <div data-live-img="logo" title="Klik pada Mode Edit Langsung untuk mengunggah Logo" style="background-color: <?= e($f_logo_bg) ?>; color: <?= e($f_logo_text) ?>;" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center font-black text-[10px] sm:text-sm cursor-pointer shrink-0 shadow-xs">KGS</div>
                        <?php endif; ?>
                        <span style="color: <?= e($f_cname_col) ?>;" class="font-extrabold text-[13.5px] sm:text-base md:text-lg block tracking-tight leading-none whitespace-nowrap" data-live-setting="company_name"><?= e(get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO')) ?></span>
                    </div>
                    <span style="color: <?= e($f_tagline_col) ?>;" class="text-[10.5px] sm:text-xs font-semibold tracking-tight sm:tracking-wide block leading-tight mt-1 whitespace-nowrap" data-live-setting="tagline"><?= e($f_raw_tagline) ?></span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed" data-live-setting="footer_description">
                    <?= e(get_setting($pdo, 'footer_description', get_setting($pdo, 'company_profile', 'Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.'))) ?>
                </p>

                <!-- Lisensi, Sertifikasi & Keanggotaan di Footer (Dapat Ditampilkan / Disembunyikan dari Admin CMS) -->
                <?php if ($footer_show_cert_badges): ?>
                <div class="pt-2 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400" data-live-setting="footer_cert_heading">
                            <?= e(get_setting($pdo, 'footer_cert_heading', 'Lisensi, Sertifikasi & Keanggotaan')) ?>
                        </div>
                        <?php if ($footer_show_cert_status && !$footer_show_cert_section): ?>
                            <span class="text-[11px] text-emerald-400 font-semibold" data-live-setting="footer_cert_card_status">
                                <?= e(get_setting($pdo, 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi')) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                            <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-[10px] font-bold text-blue-400 hover:underline">+ Atur Tampil/Sembunyi &amp; Link</a>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <?php foreach ($parsed_cert_badges as $idx => $badge_item):
                            $b_label = trim((string)($badge_item['label'] ?? ''));
                            $b_url   = trim((string)($badge_item['url'] ?? ''));
                            $b_logo  = trim((string)($badge_item['logo'] ?? ''));
                            if ($b_label === '') continue;
                            $has_link = ($b_url !== '' && $b_url !== '#');
                        ?>
                            <?php if ($has_link): ?>
                                <a href="<?= e(sanitize_nav_url($b_url)) ?>" target="<?= preg_match('/^https?:\/\//i', $b_url) ? '_blank' : '_self' ?>" rel="noopener noreferrer" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-blue-500 <?= $idx === 1 ? 'text-emerald-400' : 'text-slate-200' ?> font-semibold inline-flex items-center gap-1.5 transition">
                                    <?php if ($b_logo !== ''): ?>
                                        <img src="<?= e($b_logo) ?>" alt="<?= e($b_label) ?>" class="w-3.5 h-3.5 object-contain rounded bg-white p-0.5">
                                    <?php endif; ?>
                                    <span><?= e($b_label) ?></span>
                                    <span class="text-[9px] text-blue-400">&nearr;</span>
                                </a>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 <?= $idx === 1 ? 'text-emerald-400' : 'text-slate-300' ?> font-semibold inline-flex items-center gap-1.5">
                                    <?php if ($b_logo !== ''): ?>
                                        <img src="<?= e($b_logo) ?>" alt="<?= e($b_label) ?>" class="w-3.5 h-3.5 object-contain rounded bg-white p-0.5">
                                    <?php endif; ?>
                                    <span><?= e($b_label) ?></span>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Kolom 2: Alamat Kantor & Kontak (4 cols) -->
            <div class="lg:col-span-4 space-y-3 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-3" data-live-setting="footer_contact_heading">
                    <?= e(get_setting($pdo, 'footer_contact_heading', 'Alamat Kantor & Kontak')) ?>
                </h3>
                <div class="p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/70 space-y-1.5">
                    <div class="text-blue-400 font-bold" data-live-setting="footer_address_label"><?= e(get_setting($pdo, 'footer_address_label', 'Alamat Kantor:')) ?></div>
                    <p class="text-slate-300 leading-relaxed" data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></p>
                    <p class="text-slate-400 pt-1">Telp: <strong class="text-white" data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088')) ?></strong></p>
                </div>
                <div class="pt-1 space-y-1.5 text-slate-300">
                    <div>💬 WhatsApp Resmi: <a href="https://wa.me/<?= e(format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'))) ?>" target="_blank" rel="noopener noreferrer" class="text-emerald-400 font-bold hover:underline"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></a></div>
                    <div>✉️ Email Korporat: <a href="mailto:<?= e(get_setting($pdo, 'email', 'info@creative.co.id')) ?>" class="text-blue-400 hover:underline"><?= e(get_setting($pdo, 'email', 'info@creative.co.id')) ?></a></div>
                    <div>🕒 Jam Kerja: <span class="text-slate-200 font-medium" data-live-setting="operating_hours"><?= e(get_setting($pdo, 'operating_hours', 'Senin - Jumat (08.00 - 17.00) | Sabtu (08.00 - 16.00)')) ?></span></div>
                </div>

                <?php
                $default_social_map = [
                    'facebook'  => ['key' => 'social_facebook',  'url' => 'https://facebook.com/kgs.palembang', 'order' => 1],
                    'instagram' => ['key' => 'social_instagram', 'url' => 'https://instagram.com/kgs.palembang', 'order' => 2],
                    'linkedin'  => ['key' => 'social_linkedin',  'url' => 'https://linkedin.com/company/kreatif-global-solusindo', 'order' => 3],
                    'youtube'   => ['key' => 'social_youtube',   'url' => 'https://youtube.com/@kreatifglobalsolusindo', 'order' => 4],
                    'tiktok'    => ['key' => 'social_tiktok',    'url' => 'https://tiktok.com/@kgs.palembang', 'order' => 5],
                    'whatsapp'  => ['key' => 'social_whatsapp',  'url' => 'https://wa.me/' . format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119')), 'order' => 6],
                ];

                $social_fb = trim((string)get_setting($pdo, 'social_facebook', $default_social_map['facebook']['url']));
                if ($social_fb === '' || $social_fb === '#') $social_fb = $default_social_map['facebook']['url'];

                $social_ig = trim((string)get_setting($pdo, 'social_instagram', $default_social_map['instagram']['url']));
                if ($social_ig === '' || $social_ig === '#') $social_ig = $default_social_map['instagram']['url'];

                $social_in = trim((string)get_setting($pdo, 'social_linkedin', $default_social_map['linkedin']['url']));
                if ($social_in === '' || $social_in === '#') $social_in = $default_social_map['linkedin']['url'];

                $social_yt = trim((string)get_setting($pdo, 'social_youtube', $default_social_map['youtube']['url']));
                if ($social_yt === '' || $social_yt === '#') $social_yt = $default_social_map['youtube']['url'];

                $social_tt = trim((string)get_setting($pdo, 'social_tiktok', $default_social_map['tiktok']['url']));
                if ($social_tt === '' || $social_tt === '#') $social_tt = $default_social_map['tiktok']['url'];

                $social_wa = trim((string)get_setting($pdo, 'social_whatsapp', $default_social_map['whatsapp']['url']));
                if ($social_wa === '' || $social_wa === '#') $social_wa = $default_social_map['whatsapp']['url'];

                $individual_social_urls = [
                    'facebook'  => $social_fb,
                    'instagram' => $social_ig,
                    'linkedin'  => $social_in,
                    'youtube'   => $social_yt,
                    'tiktok'    => $social_tt,
                    'whatsapp'  => $social_wa,
                ];

                $raw_social_json = trim((string)get_setting($pdo, 'social_links_json', ''));
                $parsed_social_list = [];
                if ($raw_social_json !== '') {
                    $decoded_soc = json_decode($raw_social_json, true);
                    if (is_array($decoded_soc)) {
                        $parsed_social_list = $decoded_soc;
                    }
                }
                if (empty($parsed_social_list)) {
                    $parsed_social_list = [
                        ['platform' => 'Facebook',  'url' => $social_fb, 'is_active' => get_setting($pdo, 'social_facebook_active', '1') !== '0',  'sort_order' => (int)get_setting($pdo, 'social_facebook_order', '1'), 'setting_key' => 'social_facebook'],
                        ['platform' => 'Instagram', 'url' => $social_ig, 'is_active' => get_setting($pdo, 'social_instagram_active', '1') !== '0', 'sort_order' => (int)get_setting($pdo, 'social_instagram_order', '2'), 'setting_key' => 'social_instagram'],
                        ['platform' => 'LinkedIn',  'url' => $social_in, 'is_active' => get_setting($pdo, 'social_linkedin_active', '1') !== '0',  'sort_order' => (int)get_setting($pdo, 'social_linkedin_order', '3'), 'setting_key' => 'social_linkedin'],
                        ['platform' => 'YouTube',   'url' => $social_yt, 'is_active' => get_setting($pdo, 'social_youtube_active', '1') !== '0',   'sort_order' => (int)get_setting($pdo, 'social_youtube_order', '4'), 'setting_key' => 'social_youtube'],
                        ['platform' => 'TikTok',    'url' => $social_tt, 'is_active' => get_setting($pdo, 'social_tiktok_active', '1') !== '0',    'sort_order' => (int)get_setting($pdo, 'social_tiktok_order', '5'), 'setting_key' => 'social_tiktok'],
                        ['platform' => 'WhatsApp',  'url' => $social_wa, 'is_active' => get_setting($pdo, 'social_whatsapp_active', '1') !== '0',  'sort_order' => (int)get_setting($pdo, 'social_whatsapp_order', '6'), 'setting_key' => 'social_whatsapp'],
                    ];
                }

                // Filter akun yang aktif dan pastikan URL valid (gunakan fallback jika URL kosong/#)
                $active_socials = [];
                foreach ($parsed_social_list as $s_item) {
                    $s_name = trim((string)($s_item['platform'] ?? ($s_item['name'] ?? '')));
                    if ($s_name === '') continue;
                    $s_key_norm = strtolower($s_name);
                    $s_url  = trim((string)($s_item['url'] ?? ''));
                    if (($s_url === '' || $s_url === '#') && isset($individual_social_urls[$s_key_norm])) {
                        $s_url = $individual_social_urls[$s_key_norm];
                    }
                    $s_act  = array_key_exists('is_active', $s_item) ? (bool)$s_item['is_active'] : (array_key_exists('isActive', $s_item) ? (bool)$s_item['isActive'] : true);
                    if (!$s_act || $s_url === '' || $s_url === '#') continue;
                    if (preg_match('/^\s*(javascript|data|vbscript):/i', $s_url)) continue;
                    $setting_key = $s_item['setting_key'] ?? ($default_social_map[$s_key_norm]['key'] ?? '');
                    $active_socials[] = [
                        'name'        => $s_name,
                        'url'         => $s_url,
                        'sort_order'  => (int)($s_item['sort_order'] ?? ($s_item['sortOrder'] ?? 99)),
                        'setting_key' => $setting_key,
                    ];
                }
                usort($active_socials, function ($a, $b) {
                    return $a['sort_order'] <=> $b['sort_order'];
                });

                function get_social_meta_style(string $platform_name): array {
                    $key = strtolower(trim($platform_name));
                    if (strpos($key, 'facebook') !== false || $key === 'fb') {
                        return [
                            'hover' => 'hover:bg-blue-600 hover:border-blue-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>',
                        ];
                    }
                    if (strpos($key, 'instagram') !== false || $key === 'ig') {
                        return [
                            'hover' => 'hover:bg-pink-600 hover:border-pink-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
                        ];
                    }
                    if (strpos($key, 'linkedin') !== false) {
                        return [
                            'hover' => 'hover:bg-sky-600 hover:border-sky-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>',
                        ];
                    }
                    if (strpos($key, 'youtube') !== false || $key === 'yt') {
                        return [
                            'hover' => 'hover:bg-red-600 hover:border-red-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
                        ];
                    }
                    if (strpos($key, 'tiktok') !== false) {
                        return [
                            'hover' => 'hover:bg-slate-700 hover:border-slate-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>',
                        ];
                    }
                    if (strpos($key, 'whatsapp') !== false || $key === 'wa') {
                        return [
                            'hover' => 'hover:bg-emerald-600 hover:border-emerald-500 hover:text-white',
                            'svg'   => '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.126.556 4.122 1.529 5.854l-1.529 5.586 5.719-1.5c1.693.924 3.633 1.46 5.701 1.46 6.627 0 12-5.373 12-12s-5.373-12-12-12z"/></svg>',
                        ];
                    }
                    return [
                        'hover' => 'hover:bg-blue-600 hover:border-blue-500 hover:text-white',
                        'svg'   => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
                    ];
                }
                ?>
                <?php if (!empty($active_socials)): ?>
                <div class="pt-2.5 mt-1 border-t border-slate-800/80">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Sosial Media Resmi:</span>
                        <?php if (is_admin_logged_in()): ?>
                            <a href="admin/pengaturan.php#pengaturan-sosmed" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-800 border border-slate-700 text-blue-400 hover:text-white hover:bg-blue-600 transition font-semibold">⚙️ Admin CMS / Live Edit</a>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <?php foreach ($active_socials as $soc):
                            $soc_raw = trim((string)$soc['url']);
                            $soc_href = sanitize_nav_url($soc_raw);
                            if ($soc_href === '' || $soc_href === '#') continue;
                            $meta = get_social_meta_style($soc['name']);
                            $soc_live_attr = !empty($soc['setting_key']) ? ' data-live-setting="' . e($soc['setting_key']) . '" data-live-href="1"' : '';
                        ?>
                            <a href="<?= e($soc_href) ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="<?= e($soc['name']) ?> PT. Kreatif Global Solusindo"
                               aria-label="<?= e($soc['name']) ?> PT. Kreatif Global Solusindo"<?= $soc_live_attr ?>
                               class="w-9 h-9 rounded-xl bg-slate-800/90 border border-slate-700/80 text-slate-200 flex items-center justify-center transition-all duration-200 shadow-xs <?= $meta['hover'] ?>">
                                <?= $meta['svg'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Kolom 3: Navigasi Halaman Dinamis dari Database (2 cols) -->
            <div class="lg:col-span-2 space-y-2.5 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-3" data-live-setting="footer_nav_heading">
                    <?= e(get_setting($pdo, 'footer_nav_heading', 'Navigasi Halaman')) ?>
                </h3>
                <ul class="space-y-2 text-slate-400">
                    <?php
                    $seen_footer_titles = [];
                    foreach ($footer_menus as $fm):
                        $fm_title = trim((string)($fm['title'] ?? ''));
                        if ($fm_title === '') continue;
                        $fm_key = strtolower($fm_title);
                        if (isset($seen_footer_titles[$fm_key])) continue;
                        $seen_footer_titles[$fm_key] = true;
                        $fm_url = sanitize_nav_url(normalize_core_menu_url($fm_title, (string)($fm['url'] ?? '#'), $fm['page_slug'] ?? null));
                        $fm_target = ($fm['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                    ?>
                        <li>
                            <a href="<?= e($fm_url) ?>"<?= $fm_target ?> class="hover:text-white transition inline-flex items-center gap-1.5">
                                <span><?= e($fm_title) ?></span>
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
                <?php
                $fq1_label = get_setting($pdo, 'footer_quick_1_label', 'Kebijakan Privasi & SLA');
                $fq2_label = get_setting($pdo, 'footer_quick_2_label', '3 Segmen Paket Internet ISP');
                if ($fq2_label === 'Katalog Paket & Harga B2B') {
                    $fq2_label = '3 Segmen Paket Internet ISP';
                }
                $fq3_label = get_setting($pdo, 'footer_quick_3_label', 'Pertanyaan Umum (FAQ)');
                $fq4_label = get_setting($pdo, 'footer_quick_4_label', 'Minta Penawaran / Survei');
                $fq5_label = get_setting($pdo, 'footer_quick_5_label', 'Peta Lokasi Google Maps');

                $fq1_url = sanitize_nav_url(normalize_core_menu_url($fq1_label, get_setting($pdo, 'footer_quick_1_url', 'kebijakan-privasi.php')));
                $fq2_url = sanitize_nav_url(normalize_core_menu_url($fq2_label, get_setting($pdo, 'footer_quick_2_url', 'harga.php')));
                $fq3_url = sanitize_nav_url(normalize_core_menu_url($fq3_label, get_setting($pdo, 'footer_quick_3_url', 'faq.php')));
                $fq4_url = sanitize_nav_url(normalize_core_menu_url($fq4_label, get_setting($pdo, 'footer_quick_4_url', 'kontak.php#konsultasi')));
                $fq5_url = sanitize_nav_url(normalize_core_menu_url($fq5_label, get_setting($pdo, 'footer_quick_5_url', 'kontak.php#peta-lokasi')));
                ?>
                <ul class="space-y-2 text-slate-400">
                    <li>
                        <a href="<?= e($fq1_url) ?>" class="hover:text-white transition inline-flex items-center gap-1.5">
                            <span><?= e($fq1_label) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e($fq2_url) ?>" class="hover:text-white transition inline-flex items-center gap-1.5">
                            <span><?= e($fq2_label) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e($fq3_url) ?>" class="hover:text-white transition inline-flex items-center gap-1.5">
                            <span><?= e($fq3_label) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e($fq4_url) ?>" class="hover:text-white transition inline-flex items-center gap-1.5">
                            <span><?= e($fq4_label) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e($fq5_url) ?>" class="hover:text-white transition inline-flex items-center gap-1.5">
                            <span><?= e($fq5_label) ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Baris Sertifikasi & Keanggotaan Resmi di Footer (Dapat Ditampilkan atau Disembunyikan dari Admin CMS) -->
        <?php if ($footer_show_cert_section && !empty($footer_certs)): ?>
            <div class="mt-10 pt-8 border-t border-slate-800/80">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400" data-live-setting="footer_cert_card_title">
                        <?= e(get_setting($pdo, 'footer_cert_card_title', 'Sertifikasi & Keanggotaan Resmi')) ?>
                    </span>
                    <div class="flex items-center gap-3">
                        <?php if ($footer_show_cert_status): ?>
                            <span class="text-[11px] text-emerald-400 font-semibold" data-live-setting="footer_cert_card_status">
                                <?= e(get_setting($pdo, 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi')) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (is_admin_logged_in()): ?>
                            <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-[10px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 font-bold transition">
                                👁️ Atur Tampil / Sembunyikan
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <?php foreach (array_slice($footer_certs, 0, 4) as $fc):
                        $fc_logo = trim((string)($fc['logo_url'] ?? ''));
                        $fc_link = trim((string)($fc['link_url'] ?? ''));
                    ?>
                        <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/70 flex items-start gap-2.5">
                            <?php if ($fc_logo !== ''): ?>
                                <img src="<?= e($fc_logo) ?>" alt="<?= e($fc['title']) ?>" class="w-8 h-8 rounded bg-white p-0.5 object-contain shrink-0">
                            <?php else: ?>
                                <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-300 font-extrabold text-[10px] shrink-0" data-live-record="company_certifications:<?= (int)$fc['id'] ?>:badge_code"><?= e($fc['badge_code']) ?></span>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <div class="font-bold text-slate-200 truncate" data-live-record="company_certifications:<?= (int)$fc['id'] ?>:title"><?= e($fc['title']) ?></div>
                                    <?php if ($fc_link !== ''): ?>
                                        <a href="<?= e(sanitize_nav_url($fc_link)) ?>" target="<?= preg_match('/^https?:\/\//i', $fc_link) ? '_blank' : '_self' ?>" rel="noopener noreferrer" class="text-[10px] text-blue-400 hover:underline shrink-0">&nearr;</a>
                                    <?php endif; ?>
                                </div>
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

<?php if (is_admin_logged_in()): 
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

        // Jangan blokir navigasi pada link <a> yang memiliki href valid kecuali klik sambil menahan tombol Alt
        var anchorEl = ev.target.closest('a[href]');
        if (anchorEl && !ev.altKey) {
            var hrefVal = (anchorEl.getAttribute('href') || '').trim();
            if (hrefVal !== '' && hrefVal !== '#' && hrefVal.indexOf('javascript:') !== 0) {
                return;
            }
        }

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
            var isHrefEdit = el.getAttribute('data-live-href') === '1';
            document.getElementById('kgs-modal-title').textContent = isHrefEdit
                ? 'Edit Link Sosial Media (' + settingKey + ')'
                : 'Edit Pengaturan Website (' + settingKey + ')';
            document.getElementById('live-input-action').value = 'site_setting';
            document.getElementById('live-input-key').value = settingKey;
            document.getElementById('live-input-value').value = isHrefEdit
                ? (el.getAttribute('href') || '')
                : el.innerText.trim();
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
                        var sKey = activeDomEl.getAttribute('data-live-setting');
                        if (activeDomEl.getAttribute('data-live-href') === '1') {
                            activeDomEl.setAttribute('href', newVal);
                            if (sKey) {
                                document.querySelectorAll('[data-live-setting="' + sKey + '"][data-live-href="1"]').forEach(function(node) {
                                    node.setAttribute('href', newVal);
                                });
                            }
                        } else {
                            activeDomEl.textContent = newVal;
                            if (sKey) {
                                document.querySelectorAll('[data-live-setting="' + sKey + '"]').forEach(function(node) {
                                    if (node.getAttribute('data-live-href') !== '1') {
                                        node.textContent = newVal;
                                    }
                                });
                            }
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
