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
            'company_name_color'      => 'branding',
            'logo_bg_color'           => 'branding',
            'logo_text_color'         => 'branding',
            'footer_brand_label'      => 'identity',
            'short_name'              => 'identity',
            'tagline'                 => 'identity',
            'tagline_color'           => 'branding',
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
            'footer_cert_heading'      => 'footer',
            'footer_cert_badges'       => 'footer',
            'footer_cert_card_title'   => 'footer',
            'footer_cert_card_status'  => 'footer',
            'footer_show_cert_section' => 'footer',
            'footer_show_cert_status'  => 'footer',
            'footer_show_cert_badges'  => 'footer',
            'copyright_text'           => 'footer',
            'website_corporate'        => 'contact',
            'website_isp'              => 'contact',
            // Sosial Media di Footer (Kolom 2: Alamat Kantor & Kontak)
            'social_facebook'          => 'social',
            'social_instagram'         => 'social',
            'social_linkedin'          => 'social',
            'social_youtube'           => 'social',
            'social_tiktok'            => 'social',
        ];

        $url_fields = [
            'footer_quick_1_url',
            'footer_quick_2_url',
            'footer_quick_3_url',
            'footer_quick_4_url',
            'footer_quick_5_url',
            'social_facebook',
            'social_instagram',
            'social_linkedin',
            'social_youtube',
            'social_tiktok',
        ];

        foreach ($fields as $key => $group) {
            if (isset($_POST[$key])) {
                $raw_val = trim((string)$_POST[$key]);
                if (in_array($key, $url_fields, true)) {
                    $clean_val = $raw_val === '' ? '' : sanitize_nav_url(clean_plain_text($raw_val, 300));
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

        // 3b. Kelola Daftar Akun Sosial Media (CRUD + Aktif/Nonaktif + Urutan Tampil)
        if (isset($_POST['social_item_platforms']) && is_array($_POST['social_item_platforms'])) {
            $updated_social_list = [];
            foreach ($_POST['social_item_platforms'] as $s_idx => $raw_plat) {
                if (!empty($_POST['social_item_deletes'][$s_idx])) {
                    continue; // Dihapus oleh admin
                }
                $plat_name = clean_plain_text((string)$raw_plat, 60);
                $raw_s_url = trim((string)($_POST['social_item_urls'][$s_idx] ?? ''));
                if ($plat_name === '') continue;
                // Validasi keamanan URL (tolak skema javascript:, data:, vbscript:)
                if ($raw_s_url !== '' && preg_match('/^\s*(javascript|data|vbscript):/i', $raw_s_url)) {
                    throw new Exception("URL sosial media untuk {$plat_name} tidak valid atau berbahaya.");
                }
                $clean_s_url = $raw_s_url === '' ? '' : sanitize_nav_url(clean_plain_text($raw_s_url, 300));
                $s_order = max(1, (int)($_POST['social_item_orders'][$s_idx] ?? ($s_idx + 1)));
                $s_active = !empty($_POST['social_item_actives'][$s_idx]) && $clean_s_url !== '';
                $updated_social_list[] = [
                    'id'         => 'soc-' . ($s_idx + 1),
                    'platform'   => $plat_name,
                    'url'        => $clean_s_url,
                    'is_active'  => $s_active,
                    'sort_order' => $s_order,
                ];

                // Sinkronkan juga ke key individual bila cocok
                $p_low = strtolower($plat_name);
                if (strpos($p_low, 'facebook') !== false) {
                    save_setting($pdo, 'social_facebook', $s_active ? $clean_s_url : '', 'social');
                } elseif (strpos($p_low, 'instagram') !== false) {
                    save_setting($pdo, 'social_instagram', $s_active ? $clean_s_url : '', 'social');
                } elseif (strpos($p_low, 'linkedin') !== false) {
                    save_setting($pdo, 'social_linkedin', $s_active ? $clean_s_url : '', 'social');
                } elseif (strpos($p_low, 'youtube') !== false) {
                    save_setting($pdo, 'social_youtube', $s_active ? $clean_s_url : '', 'social');
                } elseif (strpos($p_low, 'tiktok') !== false) {
                    save_setting($pdo, 'social_tiktok', $s_active ? $clean_s_url : '', 'social');
                } elseif (strpos($p_low, 'whatsapp') !== false) {
                    save_setting($pdo, 'social_whatsapp', $s_active ? $clean_s_url : '', 'social');
                }
            }

            // Tambah akun sosial media baru jika diisi
            $new_soc_plat = clean_plain_text((string)($_POST['new_social_platform'] ?? ''), 60);
            $new_soc_url_raw = trim((string)($_POST['new_social_url'] ?? ''));
            if ($new_soc_plat !== '' && $new_soc_url_raw !== '') {
                if (preg_match('/^\s*(javascript|data|vbscript):/i', $new_soc_url_raw)) {
                    throw new Exception("URL sosial media baru tidak valid.");
                }
                $new_soc_url = sanitize_nav_url(clean_plain_text($new_soc_url_raw, 300));
                $new_soc_order = max(1, (int)($_POST['new_social_order'] ?? (count($updated_social_list) + 1)));
                $updated_social_list[] = [
                    'id'         => 'soc-' . time(),
                    'platform'   => $new_soc_plat,
                    'url'        => $new_soc_url,
                    'is_active'  => true,
                    'sort_order' => $new_soc_order,
                ];
            }

            usort($updated_social_list, function ($a, $b) {
                return ($a['sort_order'] ?? 99) <=> ($b['sort_order'] ?? 99);
            });
            save_setting($pdo, 'social_links_json', json_encode($updated_social_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'social');
        }

        // 4. Update Daftar Menu Navigasi Halaman di Footer pada tabel `menus`
        if (!empty($_POST['footer_menu_ids']) && is_array($_POST['footer_menu_ids'])) {
            $stmt_upd_menu = $pdo->prepare("UPDATE menus SET title = ?, url = ?, page_slug = ? WHERE id = ? AND (menu_location = 'footer' OR location = 'footer')");
            foreach ($_POST['footer_menu_ids'] as $idx => $fm_id) {
                $fm_id_int = (int)$fm_id;
                $fm_title = clean_plain_text((string)($_POST['footer_menu_titles'][$idx] ?? ''), 100);
                $fm_url = sanitize_nav_url(trim((string)($_POST['footer_menu_urls'][$idx] ?? '')));
                $fm_slug = resolve_url_to_page_slug($fm_url);
                if ($fm_id_int > 0 && $fm_title !== '' && $fm_url !== '') {
                    $stmt_upd_menu->execute([$fm_title, $fm_url, $fm_slug, $fm_id_int]);
                }
            }
            invalidate_live_preview_json('menus');
        }

        // Tambah item baru ke Navigasi Halaman Footer jika diisi
        $new_fm_title = clean_plain_text((string)($_POST['new_footer_menu_title'] ?? ''), 100);
        $new_fm_url = sanitize_nav_url(trim((string)($_POST['new_footer_menu_url'] ?? '')));
        if ($new_fm_title !== '' && $new_fm_url !== '' && $new_fm_url !== '#') {
            $new_fm_slug = resolve_url_to_page_slug($new_fm_url);
            $stmt_ins_fm = $pdo->prepare("INSERT INTO menus (title, url, page_slug, location, menu_location, sort_order, target, is_active) VALUES (?, ?, ?, 'footer', 'footer', 99, '_self', 1)");
            $stmt_ins_fm->execute([$new_fm_title, $new_fm_url, $new_fm_slug]);
            invalidate_live_preview_json('menus');
        }

        clear_settings_cache();
        log_audit($pdo, 'Update Pengaturan & Footer', 'Memperbarui identitas perusahaan, logo, Google Maps, dan seluruh pengaturan Footer');
        $msg = 'Seluruh pengaturan identitas perusahaan, logo, Google Maps, dan pengaturan Footer lengkap berhasil disimpan ke database & langsung tampil di website publik!';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$settings = [];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `group_name` VARCHAR(50) NOT NULL DEFAULT 'general',
        `key_name` VARCHAR(60) NOT NULL UNIQUE,
        `key_value` LONGTEXT,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $stmt_all = $pdo->query("SELECT key_name, key_value FROM site_settings");
    while ($row = $stmt_all->fetch()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
} catch (Exception $e) {
    $settings = [];
}

$footer_menus_db = get_navigation_menus($pdo, 'footer', false);

$current_logo = trim((string)($settings['logo'] ?? ($settings['company_logo'] ?? '')));
if (strpos($current_logo, 'image_8b7e60bd') !== false || $current_logo === 'assets/images/logo-kgs.svg') {
    $current_logo = '';
}
$current_logo = ltrim($current_logo, '/');
$current_footer_logo = $settings['footer_logo'] ?? '';
$current_favicon = $settings['favicon'] ?? 'assets/images/favicon.svg';
$current_company_name = $settings['company_name'] ?? 'PT. KREATIF GLOBAL SOLUSINDO';
$current_company_name_color = sanitize_hex_color($settings['company_name_color'] ?? '#0f172a', '#0f172a');
$current_logo_bg_color = sanitize_hex_color($settings['logo_bg_color'] ?? '#2563eb', '#2563eb');
$current_logo_text_color = sanitize_hex_color($settings['logo_text_color'] ?? '#ffffff', '#ffffff');
$current_tagline = $settings['tagline'] ?? 'System Integrator, Security Solution & Internet Provider';
if ($current_tagline === 'Your Solution & Partner') {
    $current_tagline = 'System Integrator, Security Solution & Internet Provider';
}
$current_tagline_color = sanitize_hex_color($settings['tagline_color'] ?? '#2563eb', '#2563eb');
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
        <h1 class="text-xl font-bold text-slate-900">Identitas Perusahaan, Logo, Google Maps &amp; Pengaturan Seluruh Footer</h1>
        <p class="text-xs text-slate-500">Atur logo KGS, Nama PT atau Nama Website di samping logo (dengan warna custom), deskripsi di bawah logo &amp; nama, kontak WhatsApp CS, Google Maps, serta seluruh bagian Footer.</p>
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

    <!-- 1. Pengaturan Logo Utama & Favicon -->
    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">1. Pengaturan Logo, Nama PT / Website (Warna Custom) &amp; Deskripsi Bawah Logo</h2>
                <p class="text-[11px] text-slate-500">Logo tampil di sebelah kiri, sampingnya Nama PT atau Nama Website dengan warna yang dapat dicustom, dan di bawah Logo &amp; Nama tampil deskripsi &quot;System Integrator, Security Solution &amp; Internet Provider&quot;.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px] uppercase tracking-wider shrink-0">Aktif di Header &amp; Footer</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-center bg-white p-4 rounded-2xl border border-blue-200">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-slate-50 rounded-xl border border-slate-300 flex items-center justify-center p-2 shrink-0 overflow-hidden">
                    <?php if (!empty($current_logo)): ?>
                        <img src="../<?= e($current_logo) ?>" alt="Logo Perusahaan" class="max-h-full max-w-full object-contain">
                    <?php else: ?>
                        <div style="background-color: <?= e($current_logo_bg_color) ?>; color: <?= e($current_logo_text_color) ?>;" class="w-11 h-11 rounded-xl flex items-center justify-center font-extrabold text-sm shadow-xs">KGS</div>
                    <?php endif; ?>
                </div>
                <div class="space-y-1.5 flex-1">
                    <label class="block font-bold text-slate-900">Logo Utama Website (Di Sebelah Kiri PT. KREATIF GLOBAL SOLUSINDO)</label>
                    <input type="file" name="company_logo_file" accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full text-xs text-slate-600">
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <div class="flex items-center gap-1.5">
                            <label class="text-[11px] font-bold text-slate-700">Warna Latar Logo KGS:</label>
                            <input type="color" name="logo_bg_color" value="<?= e($current_logo_bg_color) ?>" class="w-8 h-7 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-[11px] font-bold text-slate-700">Warna Teks KGS:</label>
                            <input type="color" name="logo_text_color" value="<?= e($current_logo_text_color) ?>" class="w-8 h-7 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                        </div>
                    </div>
                    <?php if (!empty($current_logo)): ?>
                        <label class="inline-flex items-center gap-1.5 text-rose-600 font-semibold mt-1 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1">
                            <span>Hapus gambar &amp; kembalikan ke ikon KGS default</span>
                        </label>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white rounded-xl border border-slate-300 flex items-center justify-center p-2 shrink-0 overflow-hidden">
                    <img src="../<?= e($current_favicon ?: 'assets/images/favicon.svg') ?>" alt="Favicon" class="w-8 h-8 object-contain">
                </div>
                <div class="space-y-1 flex-1">
                    <label class="block font-bold text-slate-900">Favicon Website (Ikon Tab Browser)</label>
                    <input type="file" name="favicon_file" accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full text-xs text-slate-600">
                    <p class="text-[11px] text-slate-500">Ikon persegi 64x64px untuk tab browser.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Identitas, Warna Nama PT/Website & Deskripsi di Bawah Logo & Nama -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="space-y-2 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <label class="block font-bold text-slate-900">Nama PT atau Nama Website (Di Samping Logo) *</label>
            <input type="text" name="company_name" required value="<?= e($current_company_name) ?>" class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-bold">
            <div class="flex items-center gap-2 pt-1">
                <label class="text-[11px] font-bold text-slate-700 shrink-0">Warna Nama PT/Website:</label>
                <input type="color" name="company_name_color" value="<?= e($current_company_name_color) ?>" class="w-9 h-8 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                <span class="font-mono text-[11px] text-slate-600"><?= e($current_company_name_color) ?></span>
            </div>
        </div>
        <div class="space-y-2 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <label class="block font-bold text-slate-900">Deskripsi di Bawah Logo &amp; Nama *</label>
            <input type="text" name="tagline" required value="<?= e($current_tagline) ?>" class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-semibold">
            <div class="flex items-center gap-2 pt-1">
                <label class="text-[11px] font-bold text-slate-700 shrink-0">Warna Deskripsi:</label>
                <input type="color" name="tagline_color" value="<?= e($current_tagline_color) ?>" class="w-9 h-8 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                <span class="font-mono text-[11px] text-slate-600"><?= e($current_tagline_color) ?></span>
            </div>
        </div>
        <div class="space-y-2 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <label class="block font-bold text-slate-900">Label Brand di Footer (Default: KGSNET) *</label>
            <input type="text" name="footer_brand_label" required value="<?= e(($settings['footer_brand_label'] ?? ($settings['short_name'] ?? 'KGSNET')) === 'PT. KGS' ? 'KGSNET' : ($settings['footer_brand_label'] ?? ($settings['short_name'] ?? 'KGSNET'))) ?>" class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-bold">
            <p class="text-[10px] text-slate-500 pt-1">Ditampilkan pada badge brand dan copyright footer.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Telepon Kantor</label>
            <input type="text" name="phone_palembang" value="<?= e($settings['phone_palembang'] ?? '0711-316088') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
        <div>
            <label class="block font-bold text-emerald-700 mb-1">WhatsApp CS Resmi (Direct Penawaran) *</label>
            <input type="text" name="whatsapp" required value="<?= e($settings['whatsapp'] ?? '0811-782119') ?>" class="w-full border-2 border-emerald-400 bg-emerald-50/40 rounded-xl p-2.5 font-bold">
            <span class="text-[10px] text-emerald-700 block mt-1">Tujuan direct WA saat pengunjung mengirim formulir penawaran.</span>
        </div>
        <div>
            <label class="block font-semibold text-slate-700 mb-1">Email Korporat</label>
            <input type="email" name="email" value="<?= e($settings['email'] ?? 'info@creative.co.id') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
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
        <?php
            $p_show_cert_section = ($settings['footer_show_cert_section'] ?? '1') !== '0' ? '1' : '0';
            $p_show_cert_status  = ($settings['footer_show_cert_status'] ?? '1') !== '0' ? '1' : '0';
            $p_show_cert_badges  = ($settings['footer_show_cert_badges'] ?? '1') !== '0' ? '1' : '0';
        ?>
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">B. Lisensi, Sertifikasi &amp; Keanggotaan Resmi di Footer (Pilihan Ditampilkan / Tidak Ditampilkan)</h3>
                <a href="bagian-halaman.php?tab=sertifikasi_mitra" class="text-[11px] font-bold text-blue-400 hover:underline">+ Kelola Daftar Kartu Sertifikasi Lengkap &rarr;</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3.5 rounded-xl bg-slate-900/90 border border-slate-700">
                <div>
                    <label class="block font-bold text-emerald-300 mb-1">1. Baris "Sertifikasi &amp; Keanggotaan Resmi" di Footer</label>
                    <select name="footer_show_cert_section" class="w-full border border-slate-600 bg-slate-800 text-white rounded-xl p-2.5 font-bold">
                        <option value="1" <?= $p_show_cert_section === '1' ? 'selected' : '' ?>>✓ Ditampilkan di Footer</option>
                        <option value="0" <?= $p_show_cert_section === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-emerald-300 mb-1">2. Label "✓ Terverifikasi &amp; Berlisensi Resmi"</label>
                    <select name="footer_show_cert_status" class="w-full border border-slate-600 bg-slate-800 text-white rounded-xl p-2.5 font-bold">
                        <option value="1" <?= $p_show_cert_status === '1' ? 'selected' : '' ?>>✓ Ditampilkan</option>
                        <option value="0" <?= $p_show_cert_status === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-emerald-300 mb-1">3. Badge "Lisensi, Sertifikasi &amp; Keanggotaan" (Kolom 1)</label>
                    <select name="footer_show_cert_badges" class="w-full border border-slate-600 bg-slate-800 text-white rounded-xl p-2.5 font-bold">
                        <option value="1" <?= $p_show_cert_badges === '1' ? 'selected' : '' ?>>✓ Ditampilkan di Kolom 1 Footer</option>
                        <option value="0" <?= $p_show_cert_badges === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Bagian Lisensi &amp; Sertifikasi (Kolom 1)</label>
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
                    <label class="block font-bold text-slate-200 mb-1">Daftar Badge Lisensi, Sertifikasi &amp; Keanggotaan (Pisahkan dengan tanda <code class="text-amber-300">|</code>)</label>
                    <input type="text" name="footer_cert_badges" value="<?= e($settings['footer_cert_badges'] ?? 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5 font-semibold">
                    <span class="text-[10px] text-slate-400 block mt-1">Contoh: <code>APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified</code></span>
                </div>
            </div>
        </div>

        <!-- C. Alamat Kantor, Kontak & Sosial Media di Footer -->
        <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-400">C. Alamat Kantor, Kontak &amp; Icon Sosial Media (Kolom 2 Footer)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Judul Kolom Alamat &amp; Kontak</label>
                    <input type="text" name="footer_contact_heading" value="<?= e($settings['footer_contact_heading'] ?? 'Alamat Kantor & Kontak') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-200 mb-1">Label Sub-Judul Alamat</label>
                    <input type="text" name="footer_address_label" value="<?= e($settings['footer_address_label'] ?? 'Alamat Kantor:') ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-xl p-2.5">
                </div>
            </div>

            <?php
            $admin_social_list = [];
            if (!empty($settings['social_links_json'])) {
                $decoded_admin_soc = json_decode((string)$settings['social_links_json'], true);
                if (is_array($decoded_admin_soc)) {
                    $admin_social_list = $decoded_admin_soc;
                }
            }
            if (empty($admin_social_list)) {
                $wa_num_clean = format_wa_number($settings['whatsapp'] ?? '0811-782119');
                $admin_social_list = [
                    ['platform' => 'Facebook',  'url' => $settings['social_facebook'] ?? 'https://facebook.com/kgs.palembang', 'is_active' => !empty($settings['social_facebook'] ?? 'https://facebook.com/kgs.palembang'), 'sort_order' => 1],
                    ['platform' => 'Instagram', 'url' => $settings['social_instagram'] ?? 'https://instagram.com/kgs.palembang', 'is_active' => !empty($settings['social_instagram'] ?? 'https://instagram.com/kgs.palembang'), 'sort_order' => 2],
                    ['platform' => 'LinkedIn',  'url' => $settings['social_linkedin'] ?? 'https://linkedin.com/company/kreatif-global-solusindo', 'is_active' => !empty($settings['social_linkedin'] ?? 'https://linkedin.com/company/kreatif-global-solusindo'), 'sort_order' => 3],
                    ['platform' => 'YouTube',   'url' => $settings['social_youtube'] ?? 'https://youtube.com/@kreatifglobalsolusindo', 'is_active' => !empty($settings['social_youtube'] ?? 'https://youtube.com/@kreatifglobalsolusindo'), 'sort_order' => 4],
                    ['platform' => 'TikTok',    'url' => $settings['social_tiktok'] ?? 'https://tiktok.com/@kgs.palembang', 'is_active' => !empty($settings['social_tiktok'] ?? 'https://tiktok.com/@kgs.palembang'), 'sort_order' => 5],
                    ['platform' => 'WhatsApp',  'url' => $settings['social_whatsapp'] ?? ('https://wa.me/' . $wa_num_clean), 'is_active' => true, 'sort_order' => 6],
                ];
            }
            ?>
            <div id="pengaturan-sosmed" class="pt-3 border-t border-slate-700 space-y-3 scroll-mt-24">
                <div>
                    <div class="font-bold text-emerald-400 text-xs">🌐 Manajemen Akun Sosial Media di Footer (Tambah, Edit, Urutan Tampil, Aktif/Nonaktif &amp; Hapus)</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Hanya akun sosial media yang berstatus <strong>Aktif</strong> dan memiliki URL valid yang ditampilkan di Footer halaman publik.</p>
                </div>

                <div class="space-y-2">
                    <?php foreach ($admin_social_list as $s_idx => $soc_row):
                        $s_plat = (string)($soc_row['platform'] ?? ($soc_row['name'] ?? ''));
                        $s_url  = (string)($soc_row['url'] ?? '');
                        $s_ord  = (int)($soc_row['sort_order'] ?? ($s_idx + 1));
                        $s_act  = isset($soc_row['is_active']) ? (bool)$soc_row['is_active'] : true;
                    ?>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-slate-900/90 border border-slate-700 rounded-xl p-2.5">
                            <div class="sm:col-span-3">
                                <label class="block text-[10px] text-slate-400 mb-0.5">Nama Platform</label>
                                <input type="text" name="social_item_platforms[<?= $s_idx ?>]" value="<?= e($s_plat) ?>" class="w-full border border-slate-600 bg-slate-800 text-white font-bold rounded-lg p-2 text-xs">
                            </div>
                            <div class="sm:col-span-5">
                                <label class="block text-[10px] text-slate-400 mb-0.5">URL Lengkap (https://...)</label>
                                <input type="text" name="social_item_urls[<?= $s_idx ?>]" value="<?= e($s_url) ?>" placeholder="https://..." class="w-full border border-slate-600 bg-slate-800 text-slate-200 font-mono rounded-lg p-2 text-xs">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-[10px] text-slate-400 mb-0.5">Urutan</label>
                                <input type="number" name="social_item_orders[<?= $s_idx ?>]" value="<?= $s_ord ?>" min="1" max="99" class="w-full border border-slate-600 bg-slate-800 text-white text-center font-bold rounded-lg p-2 text-xs">
                            </div>
                            <div class="sm:col-span-2 flex items-center pt-3">
                                <label class="inline-flex items-center gap-1.5 text-emerald-400 font-bold cursor-pointer">
                                    <input type="checkbox" name="social_item_actives[<?= $s_idx ?>]" value="1" <?= $s_act ? 'checked' : '' ?> class="rounded">
                                    <span>Aktif di Footer</span>
                                </label>
                            </div>
                            <div class="sm:col-span-1 flex items-center pt-3">
                                <label class="inline-flex items-center gap-1 text-rose-400 hover:text-rose-300 font-semibold cursor-pointer" title="Centang untuk menghapus platform ini saat disimpan">
                                    <input type="checkbox" name="social_item_deletes[<?= $s_idx ?>]" value="1" class="rounded">
                                    <span>Hapus</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tambah Platform Sosial Media Baru -->
                <div class="p-3 rounded-xl bg-slate-900/60 border border-dashed border-slate-600 space-y-2">
                    <div class="text-[11px] font-bold text-blue-400">+ Tambah Akun Sosial Media Baru (Opsional):</div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                        <div class="sm:col-span-4">
                            <input type="text" name="new_social_platform" placeholder="Nama Platform (misal: Instagram / X / Telegram)" class="w-full border border-slate-600 bg-slate-900 text-white rounded-lg p-2 text-xs">
                        </div>
                        <div class="sm:col-span-6">
                            <input type="text" name="new_social_url" placeholder="URL Lengkap (contoh: https://instagram.com/kgs.palembang)" class="w-full border border-slate-600 bg-slate-900 text-slate-200 font-mono rounded-lg p-2 text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" name="new_social_order" placeholder="Urutan (7)" min="1" max="99" class="w-full border border-slate-600 bg-slate-900 text-white text-center rounded-lg p-2 text-xs">
                        </div>
                    </div>
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
                    2 => ['label' => '3 Segmen Paket Internet ISP', 'url' => 'harga.php'],
                    3 => ['label' => 'Pertanyaan Umum (FAQ)',       'url' => 'faq.php'],
                    4 => ['label' => 'Minta Penawaran / Survei',    'url' => 'kontak.php#konsultasi'],
                    5 => ['label' => 'Peta Lokasi Google Maps',     'url' => 'kontak.php#peta-lokasi'],
                ];
                foreach ($quick_defaults as $q_idx => $q_def):
                    $saved_q_url = trim((string)($settings["footer_quick_{$q_idx}_url"] ?? ''));
                    if ($saved_q_url === '' || $saved_q_url === '#') {
                        $saved_q_url = $q_def['url'];
                    }
                    $saved_q_lbl = trim((string)($settings["footer_quick_{$q_idx}_label"] ?? ''));
                    if ($saved_q_lbl === '' || ($q_idx === 2 && $saved_q_lbl === 'Katalog Paket & Harga B2B')) {
                        $saved_q_lbl = $q_def['label'];
                    }
                ?>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                        <div class="sm:col-span-6">
                            <input type="text" name="footer_quick_<?= $q_idx ?>_label" value="<?= e($saved_q_lbl) ?>" class="w-full border border-slate-600 bg-slate-900 text-white rounded-lg p-2">
                        </div>
                        <div class="sm:col-span-6">
                            <input type="text" name="footer_quick_<?= $q_idx ?>_url" value="<?= e($saved_q_url) ?>" class="w-full border border-slate-600 bg-slate-900 text-slate-300 font-mono rounded-lg p-2">
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
