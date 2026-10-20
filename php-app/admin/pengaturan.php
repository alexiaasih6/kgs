<?php
$admin_page_title = 'Pengaturan Identitas, Logo, Google Maps & Seluruh Bagian Footer';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        ensure_menus_table($pdo);

        // 1. Proses Upload Logo Utama Perusahaan (Header & Footer)
        $uploaded_logo = handle_image_upload('company_logo_file', 'uploads/', $pdo);
        if ($uploaded_logo) {
            save_setting($pdo, 'logo', $uploaded_logo, 'branding');
            save_setting($pdo, 'company_logo', $uploaded_logo, 'branding');
        } elseif (!empty($_POST['remove_logo'])) {
            save_setting($pdo, 'logo', '', 'branding');
            save_setting($pdo, 'company_logo', '', 'branding');
        }

        // 1b. Proses Upload Logo Khusus Footer (opsional)
        $uploaded_footer_logo = handle_image_upload('footer_logo_file', 'uploads/', $pdo);
        if ($uploaded_footer_logo) {
            save_setting($pdo, 'footer_logo', $uploaded_footer_logo, 'branding');
        } elseif (!empty($_POST['remove_footer_logo'])) {
            save_setting($pdo, 'footer_logo', '', 'branding');
        }

        // 2. Proses Upload Favicon Website (jika diunggah)
        $uploaded_favicon = handle_image_upload('favicon_file', 'uploads/', $pdo);
        if ($uploaded_favicon) {
            save_setting($pdo, 'favicon', $uploaded_favicon, 'branding');
        }

        // 3. Simpan pengaturan teks Identitas, Kontak, Maps & Seluruh Footer
        $fields = [
            'company_name'            => 'identity',
            'footer_brand_label'      => 'identity',
            'short_name'              => 'identity',
            'tagline'                 => 'identity',
            'company_profile'         => 'identity',
            'footer_description'      => 'footer',
            'phone_palembang'         => 'contact',
            'whatsapp'                => 'contact',
            'email'                   => 'contact',
            'operating_hours'         => 'contact',
            'operational_hours'       => 'contact',
            'address_palembang'       => 'contact',
            'google_map_input'        => 'contact',
            'vision'                  => 'identity',
            'mission'                 => 'identity',
            // Pengaturan Seluruh Footer
            'footer_contact_heading'  => 'footer',
            'footer_address_label'    => 'footer',
            'footer_nav_heading'      => 'footer',
            'footer_quick_heading'    => 'footer',
            'footer_quick_1_label'    => 'footer',
            'footer_quick_1_url'      => 'footer',
            'footer_quick_2_label'    => 'footer',
            'footer_quick_2_url'      => 'footer',
            'footer_quick_3_label'    => 'footer',
            'footer_quick_3_url'      => 'footer',
            'footer_quick_4_label'    => 'footer',
            'footer_quick_4_url'      => 'footer',
            'footer_quick_5_label'    => 'footer',
            'footer_quick_5_url'      => 'footer',
            'footer_admin_link_label' => 'footer',
            'footer_cert_heading'     => 'footer',
            'footer_cert_badges'      => 'footer',
            'footer_cert_card_title'  => 'footer',
            'footer_cert_card_status' => 'footer',
            'copyright_text'          => 'footer',
            'website_corporate'       => 'contact',
            'website_isp'             => 'contact',
        ];

        $url_fields = [
            'footer_quick_1_url',
            'footer_quick_2_url',
            'footer_quick_3_url',
            'footer_quick_4_url',
            'footer_quick_5_url',
        ];

        foreach ($fields as $key => $group) {
            if (isset($_POST[$key])) {
                $raw_val = trim((string)$_POST[$key]);
                if (in_array($key, $url_fields, true)) {
                    $clean_val = sanitize_nav_url(clean_plain_text($raw_val, 300));
                } elseif ($key === 'google_map_input') {
                    // Jika admin menempelkan <iframe src="...">, ekstrak URL peta resminya
                    if (stripos($raw_val, '<iframe') !== false) {
                        $clean_val = get_google_map_embed_url($raw_val);
                    } else {
                        $clean_val = clean_plain_text($raw_val, 500);
                    }
                } else {
                    $clean_val = clean_plain_text($raw_val, 2000);
                }
                save_setting($pdo, $key, $clean_val, $group);
            }
        }

        // 4. Update Daftar Menu Navigasi Halaman di Footer pada tabel `menus`
        if (!empty($_POST['footer_menu_ids']) && is_array($_POST['footer_menu_ids'])) {
            $stmt_upd_menu = $pdo->prepare("UPDATE menus SET title = ?, url = ?, page_slug = ? WHERE id = ? AND menu_location = 'footer'");
            foreach ($_POST['footer_menu_ids'] as $idx => $fm_id) {
                $fm_id_int = (int)$fm_id;
                $fm_title = clean_plain_text((string)($_POST['footer_menu_titles'][$idx] ?? ''), 100);
                $fm_url = sanitize_nav_url(trim((string)($_POST['footer_menu_urls'][$idx] ?? '')));
                $fm_slug = resolve_url_to_page_slug($fm_url);
                if ($fm_id_int > 0 && $fm_title !== '' && $fm_url !== '') {
                    $stmt_upd_menu->execute([$fm_title, $fm_url, $fm_slug, $fm_id_int]);
                }
            }
        }

        // Tambah item baru ke Navigasi Halaman Footer jika diisi
        $new_fm_title = clean_plain_text((string)($_POST['new_footer_menu_title'] ?? ''), 100);
        $new_fm_url = sanitize_nav_url(trim((string)($_POST['new_footer_menu_url'] ?? '')));
        if ($new_fm_title !== '' && $new_fm_url !== '' && $new_fm_url !== '#') {
            $new_fm_slug = resolve_url_to_page_slug($new_fm_url);
            $stmt_ins_fm = $pdo->prepare("INSERT INTO menus (title, url, page_slug, menu_location, sort_order, target, is_active) VALUES (?, ?, ?, 'footer', 99, '_self', 1)");
            $stmt_ins_fm->execute([$new_fm_title, $new_fm_url, $new_fm_slug]);
        }

        clear_settings_cache();
        log_audit($pdo, 'Update Pengaturan & Footer', 'Memperbarui identitas perusahaan, logo, Google Maps, dan seluruh pengaturan Footer');
        $msg = 'Seluruh pengaturan identitas perusahaan, logo, Google Maps, dan pengaturan Footer lengkap berhasil disimpan ke database & langsung tampil di website publik!';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$settings = [];
$stmt_all = $pdo->query("SELECT key_name, key_value FROM site_settings");
while ($row = $stmt_all->fetch()) {
    $settings[$row['key_name']] = $row['key_value'];
}

$footer_menus_db = get_navigation_menus($pdo, 'footer', false);

$current_logo = $settings['logo'] ?? ($settings['company_logo'] ?? '');
$current_footer_logo = $settings['footer_logo'] ?? '';
$current_favicon = $settings['favicon'] ?? 'assets/images/favicon.svg';
$gmap_input = $settings['google_map_input'] ?? ($settings['address_palembang'] ?? 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
$gmap_embed_src = get_google_map_embed_url($gmap_input);

$default_copyright_val = '© ' . date('Y') . ' ' . ($settings['footer_brand_label'] ?? 'KGSNET') . ' — Creative.co.id. All Rights Reserved.';
$current_copyright = $settings['copyright_text'] ?? $default_copyright_val;
if (trim($current_copyright) === 'All Rights Reserved.') {
    $current_copyright = $default_copyright_val;
}
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Identitas Perusahaan, Logo, Google Maps & Pengaturan Seluruh Footer</h1>
        <p class="text-xs text-slate-500">Atur logo website, kontak WhatsApp CS, Google Maps, serta seluruh bagian Footer (Logo, Tentang Perusahaan, Alamat & Kontak, Navigasi Halaman, Informasi & Akses Cepat, Lisensi Sertifikasi & Keanggotaan, dan Hak Cipta).</p>
    </div>
    <a href="#pengaturan-footer" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0">
        🦶 Lompat ke Pengaturan Seluruh Footer &darr;
    </a>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        ✓ <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
        ✕ <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6 text-xs">
    <?= csrf_field() ?>

    <!-- 1. Upload Logo & Favicon -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 rounded-2xl bg-slate-50 border border-slate-200">
        <div class="space-y-2.5">
            <label class="block font-bold text-slate-900">1. Logo Utama Website (Header & Default Footer)</label>
            <div class="flex items-center gap-4">
                <div class="w-28 h-16 bg-white rounded-xl border border-slate-300 flex items-center justify-center p-2 shrink-0 overflow-hidden">
                    <?php if (!empty($current_logo)): ?>
                        <img src="../<?= e($current_logo) ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                    <?php else: ?>
                        <span class="font-black text-blue-700 text-lg">KGS</span>
                    <?php endif; ?>
                </div>
                <div class="space-y-1.5 flex-1">
                    <input type="file" name="company_logo_file" accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full text-xs text-slate-600">
                    <?php if (!empty($current_logo)): ?>
                        <label class="inline-flex items-center gap-1.5 text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1">
                            <span>Hapus logo kustom (gunakan badge default)</span>
                        </label>
                    <?php endif; ?>
                    <p class="text-[11px] text-slate-500">Format: PNG, JPG, WEBP, GIF (Maks. 3 MB).</p>
                </div>
            </div>
        </div>

        <div class="space-y-2.5">
            <label class="block font-bold text-slate-900">2. Favicon Website (Ikon Tab Browser)</label>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white rounded-xl border border-slate-300 flex items-center justify-center p-2 shrink-0 overflow-hidden">
                    <img src="../<?= e($current_favicon ?: 'assets/images/favicon.svg') ?>" alt="Favicon" class="w-8 h-8 object-contain">
                </div>
                <div class="space-y-1.5 flex-1">
                    <input type="file" name="favicon_file" accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full text-xs text-slate-600">
                    <p class="text-[11px] text-slate-500">Format: PNG, JPG, WEBP, atau GIF (Maks. 3 MB).</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Identitas & Kontak Utama -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block font-bold text-slate-800 mb-1">Nama Legalitas Perusahaan *</label>
            <input type="text" name="company_name" required value="<?= e($settings['company_name'] ?? 'PT. KREATIF GLOBAL SOLUSINDO') ?>" class="w-full border border-slate-300 rounded-xl p-2.5 font-semibold">
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Label Brand di Footer (Default: KGSNET) *</label>
            <input type="text" name="footer_brand_label" required value="<?= e(($settings['footer_brand_label'] ?? ($settings['short_name'] ?? 'KGSNET')) === 'PT. KGS' ? 'KGSNET' : ($settings['footer_brand_label'] ?? ($settings['short_name'] ?? 'KGSNET'))) ?>" class="w-full border border-slate-300 rounded-xl p-2.5 font-bold">
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Slogan / Tagline Resmi *</label>
            <input type="text" name="tagline" required value="<?= e($settings['tagline'] ?? 'Your Solution & Partner') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Telepon Kantor (Hunting)</label>
            <input type="text" name="phone_palembang" value="<?= e($settings['phone_palembang'] ?? '0711-316088 (Hunting)') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
        <div>
            <label class="block font-bold text-emerald-700 mb-1">WhatsApp CS Resmi (Direct Penawaran) *</label>
            <input type="text" name="whatsapp" required value="<?= e($settings['whatsapp'] ?? '0811-782119') ?>" class="w-full border-2 border-emerald-400 bg-emerald-50/40 rounded-xl p-2.5 font-bold">
            <span class="text-[10px] text-emerald-700 block mt-1">Tujuan direct WA saat pengunjung mengirim formulir penawaran.</span>
        </div>
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Email Korporat</label>
            <input type="email" name="email" value="<?= e($settings['email'] ?? 'info@kgs.net.id') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Jam Operasional</label>
            <input type="text" name="operating_hours" value="<?= e($settings['operational_hours'] ?? ($settings['operating_hours'] ?? 'Senin - Sabtu: 08:30 - 17:00 WIB')) ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
    </div>

    <div>
        <label class="block font-semibold text-slate-700 mb-1">Alamat Kantor (Tampil di Header, Kontak & Footer)</label>
        <textarea name="address_palembang" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5"><?= e($settings['address_palembang'] ?? 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113') ?></textarea>
    </div>

    <!-- 3. Pengaturan Peta Google Maps -->
    <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200 space-y-4">
        <div>
            <label class="block font-bold text-slate-900 text-xs mb-1">
                3. Konfigurasi Peta Google Maps Kontak Website (Link Embed / Nama Tempat / Koordinat Lat,Long)
            </label>
            <p class="text-[11px] text-slate-600 mb-2">
                Masukkan <strong>Nama Tempat / Alamat</strong>, <strong>Koordinat Latitude, Longitude</strong> (contoh: <code>-2.9718, 104.7644</code>), atau <strong>Link Embed Google Maps</strong>.
            </p>
            <input type="text" id="google_map_input" name="google_map_input" value="<?= e($gmap_input) ?>" placeholder="Contoh: Jln. Mayor Ruslan No.175C Palembang atau -2.9718, 104.7644" class="w-full border border-blue-300 rounded-xl p-2.5 bg-white font-mono text-xs">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] text-slate-500 font-semibold">Preset Cepat:</span>
            <button type="button" onclick="document.getElementById('google_map_input').value='Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113'" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg text-[11px] font-semibold cursor-pointer">
                Alamat Kantor
            </button>
            <button type="button" onclick="document.getElementById('google_map_input').value='-2.9718, 104.7644'" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg text-[11px] font-semibold cursor-pointer">
                Koordinat Kantor (-2.9718, 104.7644)
            </button>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-300 bg-slate-100 h-52">
            <iframe title="Preview Google Maps" src="<?= e($gmap_embed_src) ?>" class="w-full h-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <!-- 4. Visi & Misi -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Visi Perusahaan</label>
            <textarea name="vision" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5"><?= e($settings['vision'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Misi Perusahaan</label>
            <textarea name="mission" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5"><?= e($settings['mission'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- =======================================================================
         5. PENGATURAN SELURUH FOOTER WEBSITE LENGKAP
         Logo Footer, Tentang Perusahaan, Alamat Kantor & Kontak, Navigasi Halaman,
         Informasi & Akses Cepat, Lisensi Sertifikasi & Keanggotaan, Hak Cipta
         ======================================================================= -->
    <div id="pengaturan-footer" class="p-6 rounded-2xl bg-slate-900 text-slate-200 border border-slate-800 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold text-[10px] uppercase tracking-wider">Pengaturan Footer Terpadu</span>
                <h2 class="text-base font-extrabold text-white mt-2">4. Pengaturan Seluruh Bagian Footer Website</h2>
                <p class="text-[11px] text-slate-400">Kelola Logo Footer, Tentang Perusahaan, Alamat Kantor & Kontak, Navigasi Halaman, Informasi & Akses Cepat, Lisensi Sertifikasi & Keanggotaan, serta Hak Cipta (© 2026 KGSNET — Creative.co.id. All Rights Reserved.).</p>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow transition cursor-pointer shrink-0">
                ✓ Simpan Seluruh Pengaturan Footer
            </button>
        </div>

        <!-- A. Logo Footer & Tentang Perusahaan -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-400">A. Logo Footer & Tentang Perusahaan (Kolom 1 Footer)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div class="space-y-2">
                    <label class="block font-bold text-slate-200">Logo Khusus Footer (Opsional — Kosongkan untuk menggunakan Logo Utama)</label>
                    <div class="flex items-center gap-3">
                        <div class="w-24 h-14 bg-white rounded-xl border border-slate-600 flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                            <?php $preview_f_logo = !empty($current_footer_logo) ? $current_footer_logo : $current_logo; ?>
                            <?php if (!empty($preview_f_logo)): ?>
                                <img src="../<?= e($preview_f_logo) ?>" alt="Logo Footer" class="max-h-full max-w-full object-contain">
                            <?php else: ?>
                                <span class="font-black text-blue-700 text-base">KGS</span>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-1 flex-1">
                            <input type="file" name="footer_logo_file" accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full text-xs text-slate-300">
                            <?php if (!empty($current_footer_logo)): ?>
                                <label class="inline-flex items-center gap-1.5 text-rose-400 font-semibold cursor-pointer">
                                    <input type="checkbox" name="remove_footer_logo" value="1">
                                    <span>Hapus logo khusus footer (gunakan logo utama)</span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Tentang Perusahaan / Deskripsi Singkat di Footer</label>
                    <textarea name="footer_description" rows="3" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5"><?= e($settings['footer_description'] ?? ($settings['company_profile'] ?? 'Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- B. Lisensi, Sertifikasi & Keanggotaan di Footer -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">B. Lisensi, Sertifikasi & Keanggotaan di Footer</h3>
                <a href="bagian-halaman.php?tab=sertifikasi_mitra" class="text-[11px] font-bold text-blue-400 hover:underline">+ Kelola Daftar Kartu Sertifikasi Lengkap &rarr;</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Bagian Lisensi & Sertifikasi (Kolom 1)</label>
                    <input type="text" name="footer_cert_heading" value="<?= e($settings['footer_cert_heading'] ?? 'Lisensi, Sertifikasi & Keanggotaan') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Baris Kartu Sertifikasi Footer</label>
                    <input type="text" name="footer_cert_card_title" value="<?= e($settings['footer_cert_card_title'] ?? 'Sertifikasi & Keanggotaan Resmi') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Label Status Verifikasi Sertifikasi</label>
                    <input type="text" name="footer_cert_card_status" value="<?= e($settings['footer_cert_card_status'] ?? '✓ Terverifikasi & Berlisensi Resmi') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div class="md:col-span-3">
                    <label class="block font-bold text-slate-200 mb-1">Daftar Badge Lisensi, Sertifikasi & Keanggotaan (Pisahkan dengan tanda <code class="text-amber-300">|</code>)</label>
                    <input type="text" name="footer_cert_badges" value="<?= e($settings['footer_cert_badges'] ?? 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5 font-semibold">
                    <span class="text-[10px] text-slate-400 block mt-1">Contoh: <code>APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified</code></span>
                </div>
            </div>
        </div>

        <!-- C. Alamat Kantor & Kontak di Footer -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-400">C. Alamat Kantor & Kontak (Kolom 2 Footer)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Kolom Alamat & Kontak</label>
                    <input type="text" name="footer_contact_heading" value="<?= e($settings['footer_contact_heading'] ?? 'Alamat Kantor & Kontak') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Label Sub-Judul Alamat</label>
                    <input type="text" name="footer_address_label" value="<?= e($settings['footer_address_label'] ?? 'Alamat Kantor:') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
            </div>
            <p class="text-[11px] text-slate-400">Catatan: Isi alamat kantor, nomor telepon, WhatsApp, dan email otomatis tersinkronisasi dengan kolom Kontak Utama di atas.</p>
        </div>

        <!-- D. Navigasi Halaman di Footer -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-400">D. Navigasi Halaman (Kolom 3 Footer)</h3>
                <a href="navigasi.php" class="text-[11px] font-bold text-blue-400 hover:underline">Buka Manajemen Navigasi Lengkap &rarr;</a>
            </div>
            <div>
                <label class="block font-bold text-slate-200 mb-1">Judul Kolom Navigasi Halaman</label>
                <input type="text" name="footer_nav_heading" value="<?= e($settings['footer_nav_heading'] ?? 'Navigasi Halaman') ?>" class="w-full md:w-1/2 border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
            </div>

            <?php if (!empty($footer_menus_db)): ?>
                <div class="space-y-2">
                    <label class="block font-bold text-slate-300">Daftar Link pada Kolom Navigasi Halaman Footer:</label>
                    <?php foreach ($footer_menus_db as $fm): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="hidden" name="footer_menu_ids[]" value="<?= (int)$fm['id'] ?>">
                            <div class="sm:col-span-6">
                                <input type="text" name="footer_menu_titles[]" value="<?= e($fm['title']) ?>" placeholder="Label Menu Footer" class="w-full border border-slate-600 bg-slate-900 text-white rounded-lg p-2">
                            </div>
                            <div class="sm:col-span-6">
                                <input type="text" name="footer_menu_urls[]" value="<?= e($fm['url']) ?>" placeholder="Target URL (misal: layanan.php)" class="w-full border border-slate-600 bg-slate-900 text-slate-300 font-mono rounded-lg p-2">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="pt-2 border-t border-slate-700">
                <label class="block font-bold text-emerald-400 mb-1.5">+ Tambah Link Baru ke Navigasi Halaman Footer (Opsional):</label>
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                    <div class="sm:col-span-6">
                        <input type="text" name="new_footer_menu_title" placeholder="Contoh: Katalog Layanan B2B" class="w-full border border-slate-600 bg-slate-900 text-white rounded-lg p-2">
                    </div>
                    <div class="sm:col-span-6">
                        <input type="text" name="new_footer_menu_url" placeholder="Contoh: layanan.php" class="w-full border border-slate-600 bg-slate-900 text-slate-300 font-mono rounded-lg p-2">
                    </div>
                </div>
            </div>
        </div>

        <!-- E. Informasi & Akses Cepat di Footer -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-400">E. Informasi & Akses Cepat (Kolom 4 Footer)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Kolom Informasi & Akses Cepat</label>
                    <input type="text" name="footer_quick_heading" value="<?= e($settings['footer_quick_heading'] ?? 'Informasi & Akses Cepat') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Label Tautan Login Admin di Bawah Kolom 4</label>
                    <input type="text" name="footer_admin_link_label" value="<?= e($settings['footer_admin_link_label'] ?? 'Login Administrator CMS') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-300">Daftar 5 Tautan Informasi & Akses Cepat (Label & URL Tujuan):</label>
                <?php
                $quick_defaults = [
                    1 => ['label' => 'Kebijakan Privasi & SLA',     'url' => 'kebijakan-privasi.php'],
                    2 => ['label' => 'Katalog Paket & Harga B2B',   'url' => 'harga.php'],
                    3 => ['label' => 'Pertanyaan Umum (FAQ)',       'url' => 'faq.php'],
                    4 => ['label' => 'Minta Penawaran / Survei',    'url' => 'kontak.php#konsultasi'],
                    5 => ['label' => 'Peta Lokasi Google Maps',     'url' => 'kontak.php#peta-lokasi'],
                ];
                foreach ($quick_defaults as $q_idx => $q_def):
                ?>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                        <div class="sm:col-span-6">
                            <input type="text" name="footer_quick_<?= $q_idx ?>_label" value="<?= e($settings["footer_quick_{$q_idx}_label"] ?? $q_def['label']) ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-lg p-2">
                        </div>
                        <div class="sm:col-span-6">
                            <input type="text" name="footer_quick_<?= $q_idx ?>_url" value="<?= e($settings["footer_quick_{$q_idx}_url"] ?? $q_def['url']) ?>" class="w-full border border-slate-600 bg-slate-900 text-slate-300 font-mono rounded-lg p-2">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- F. Hak Cipta & Domain Bawah (© 2026 KGSNET — Creative.co.id. All Rights Reserved.) -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-amber-400">F. Baris Hak Cipta & Domain Bawah (© 2026 KGSNET — Creative.co.id. All Rights Reserved.)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label class="block font-bold text-slate-200 mb-1">Teks Hak Cipta Lengkap (Copyright Footer Kiri Bawah)</label>
                    <input type="text" name="copyright_text" value="<?= e($current_copyright) ?>" placeholder="© 2026 KGSNET — Creative.co.id. All Rights Reserved." class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5 font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Domain Portal Korporat (Kanan Bawah)</label>
                    <input type="text" name="website_corporate" value="<?= e($settings['website_corporate'] ?? 'www.creative.co.id') ?>" class="w-full border border-slate-600 bg-slate-900 text-white font-mono rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Domain Portal ISP KGSNet (Kanan Bawah)</label>
                    <input type="text" name="website_isp" value="<?= e($settings['website_isp'] ?? 'www.kgs.net.id') ?>" class="w-full border border-slate-600 bg-slate-900 text-white font-mono rounded-xl p-2.5">
                </div>
            </div>
        </div>
    </div>

    <div class="pt-4 border-t flex justify-end">
        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
            Simpan Pengaturan, Logo, Google Maps & Seluruh Footer
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
