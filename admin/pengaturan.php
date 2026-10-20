<?php
$admin_page_title = 'Pengaturan Identitas Perusahaan, Logo, Favicon & Google Maps';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        // 1. Proses Upload Logo Perusahaan (jika diunggah)
        $uploaded_logo = handle_image_upload('company_logo_file', 'uploads/', $pdo);
        if ($uploaded_logo) {
            $stmt_logo = $pdo->prepare("INSERT INTO site_settings (group_name, key_name, key_value) VALUES ('branding', 'company_logo', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt_logo->execute([$uploaded_logo]);
        } elseif (!empty($_POST['remove_logo'])) {
            $stmt_logo = $pdo->prepare("UPDATE site_settings SET key_value = '' WHERE key_name = 'company_logo'");
            $stmt_logo->execute();
        }

        // 2. Proses Upload Favicon Website (jika diunggah)
        $uploaded_favicon = handle_image_upload('favicon_file', 'uploads/', $pdo);
        if ($uploaded_favicon) {
            $stmt_fav = $pdo->prepare("INSERT INTO site_settings (group_name, key_name, key_value) VALUES ('branding', 'favicon', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt_fav->execute([$uploaded_favicon]);
        }

        // 3. Simpan pengaturan teks lainnya
        $fields = [
            'company_name'       => 'identity',
            'footer_brand_label' => 'identity',
            'short_name'         => 'identity',
            'tagline'            => 'identity',
            'company_profile'    => 'identity',
            'phone_palembang'    => 'contact',
            'whatsapp'           => 'contact',
            'email'              => 'contact',
            'operating_hours'    => 'contact',
            'address_palembang'  => 'contact',
            'google_map_input'   => 'contact',
            'vision'             => 'identity',
            'mission'            => 'identity',
            'copyright_text'     => 'footer'
        ];

        $stmt_save = $pdo->prepare("INSERT INTO site_settings (group_name, key_name, key_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        foreach ($fields as $key => $group) {
            if (isset($_POST[$key])) {
                $stmt_save->execute([$group, $key, trim($_POST[$key])]);
            }
        }
        if (isset($_POST['footer_brand_label'])) {
            $stmt_save->execute(['identity', 'short_name', trim($_POST['footer_brand_label'])]);
        }

        log_audit($pdo, 'Update Pengaturan', 'Memperbarui identitas perusahaan, logo, favicon, WhatsApp CS, dan Google Maps');
        $msg = 'Pengaturan identitas perusahaan, logo, favicon, WhatsApp CS & Google Maps berhasil disimpan!';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$settings = [];
$stmt_all = $pdo->query("SELECT key_name, key_value FROM site_settings");
while ($row = $stmt_all->fetch()) {
    $settings[$row['key_name']] = $row['key_value'];
}

$current_logo = $settings['company_logo'] ?? '';
$current_favicon = $settings['favicon'] ?? 'assets/images/favicon.svg';
$gmap_input = $settings['google_map_input'] ?? ($settings['address_palembang'] ?? 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
$gmap_embed_src = build_gmap_embed_url($gmap_input);
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Identitas Perusahaan, Logo, Favicon & Google Maps</h1>
        <p class="text-xs text-slate-500">Atur logo website, favicon, profil perusahaan, nomor WhatsApp CS untuk penawaran langsung, serta titik Google Maps.</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6 text-xs">
    <?= csrf_field() ?>

    <!-- 1. Upload Logo & Favicon -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 rounded-2xl bg-slate-50 border border-slate-200">
        <div class="space-y-2.5">
            <label class="block font-bold text-slate-900">1. Logo Website (Header & Footer)</label>
            <div class="flex items-center gap-4">
                <div class="w-28 h-16 bg-white rounded-xl border border-slate-300 flex items-center justify-center p-2 shrink-0 overflow-hidden">
                    <?php if (!empty($current_logo)): ?>
                        <img src="../<?= e($current_logo) ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                    <?php else: ?>
                        <span class="font-black text-blue-700 text-lg">KGS</span>
                    <?php endif; ?>
                </div>
                <div class="space-y-1.5 flex-1">
                    <input type="file" name="company_logo_file" accept=".png,.jpg,.jpeg,.webp,.svg" class="w-full text-xs text-slate-600">
                    <?php if (!empty($current_logo)): ?>
                        <label class="inline-flex items-center gap-1.5 text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1">
                            <span>Hapus logo kustom (gunakan badge default)</span>
                        </label>
                    <?php endif; ?>
                    <p class="text-[11px] text-slate-500">Format: PNG, JPG, WEBP, SVG (Maks. 2 MB).</p>
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
                    <input type="file" name="favicon_file" accept=".ico,.png,.svg" class="w-full text-xs text-slate-600">
                    <p class="text-[11px] text-slate-500">Format: ICO, PNG, atau SVG (Maks. 2 MB).</p>
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

    <div>
        <label class="block font-bold text-slate-800 mb-1">Deskripsi Singkat Profil Perusahaan (Footer)</label>
        <textarea name="company_profile" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5"><?= e($settings['company_profile'] ?? '') ?></textarea>
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
            <input type="text" name="operating_hours" value="<?= e($settings['operating_hours'] ?? 'Senin - Sabtu: 08:30 - 17:00 WIB') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
        </div>
    </div>

    <div>
        <label class="block font-semibold text-slate-700 mb-1">Alamat Kantor</label>
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

    <!-- 4. Visi, Misi & Hak Cipta -->
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

    <div>
        <label class="block font-semibold text-slate-700 mb-1">Teks Hak Cipta Footer</label>
        <input type="text" name="copyright_text" value="<?= e($settings['copyright_text'] ?? 'All Rights Reserved.') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
    </div>

    <div class="pt-4 border-t flex justify-end">
        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
            Simpan Pengaturan, Logo, Favicon & Maps
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
