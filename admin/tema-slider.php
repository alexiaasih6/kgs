<?php
$admin_page_title = 'Pengaturan Tema Website & Gambar Slider Beranda';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

ensure_hero_sliders_table($pdo);

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form_action = $_POST['form_action'] ?? '';

    try {
        // 1. Simpan Pengaturan Tema Website
        if ($form_action === 'save_theme') {
            $preset_id = trim($_POST['theme_preset'] ?? 'corporate-blue');
            $presets = get_website_theme_presets();
            $chosen = $presets[$preset_id] ?? $presets['corporate-blue'];

            $primary_color = trim($_POST['primary_color'] ?? $chosen['primary_color']);
            $hero_bg_color = trim($_POST['hero_bg_color'] ?? $chosen['hero_bg_color']);
            $navbar_style  = trim($_POST['navbar_style'] ?? $chosen['navbar_style']);

            $stmt_save = $pdo->prepare("INSERT INTO site_settings (group_name, key_name, key_value) VALUES ('branding', ?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt_save->execute(['theme_preset', $preset_id]);
            $stmt_save->execute(['primary_color', $primary_color]);
            $stmt_save->execute(['hero_bg_color', $hero_bg_color]);
            $stmt_save->execute(['navbar_style', $navbar_style]);

            log_audit($pdo, 'Update Tema Website', "Mengubah tema website ke preset: {$preset_id} ({$primary_color})");
            $msg = 'Tema & warna website berhasil diperbarui dan langsung diterapkan ke seluruh halaman!';
        }

        // 2. Simpan Konfigurasi Tata Letak Slider Beranda (Sebelah / Background / Both / NOC)
        elseif ($form_action === 'save_slider_config') {
            $layout_mode = in_array($_POST['hero_slider_layout'] ?? 'side', ['side', 'background', 'both', 'noc'], true)
                ? $_POST['hero_slider_layout']
                : 'side';
            $autoplay = isset($_POST['hero_slider_autoplay']) ? '1' : '0';
            $interval = max(2, min(15, (int)($_POST['hero_slider_interval'] ?? 5)));
            $overlay  = max(35, min(92, (int)($_POST['hero_slider_overlay'] ?? 75)));

            $stmt_save = $pdo->prepare("INSERT INTO site_settings (group_name, key_name, key_value) VALUES ('slider', ?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt_save->execute(['hero_slider_layout', $layout_mode]);
            $stmt_save->execute(['hero_slider_autoplay', $autoplay]);
            $stmt_save->execute(['hero_slider_interval', (string)$interval]);
            $stmt_save->execute(['hero_slider_overlay', (string)$overlay]);

            log_audit($pdo, 'Update Konfigurasi Slider', "Mengubah posisi slider beranda ke: {$layout_mode}");
            $msg = 'Pengaturan posisi & animasi Slider Beranda berhasil disimpan!';
        }

        // 3. Tambah atau Edit Slide Gambar Beranda
        elseif ($form_action === 'save_slide') {
            $slide_id   = (int)($_POST['slide_id'] ?? 0);
            $title      = trim($_POST['title'] ?? 'Dokumentasi Infrastruktur PT. KGS');
            $subtitle   = trim($_POST['subtitle'] ?? '');
            $badge_text = trim($_POST['badge_text'] ?? 'Dokumentasi KGS');
            $sort_order = max(1, (int)($_POST['sort_order'] ?? 1));
            $is_active  = isset($_POST['is_active']) ? 1 : 0;
            $image_url  = trim($_POST['image_url'] ?? '');

            // Cek upload file gambar baru
            if (!empty($_FILES['slide_image_file']['name'])) {
                $uploaded = handle_image_upload('slide_image_file', 'uploads/', $pdo);
                if ($uploaded) {
                    $image_url = $uploaded;
                }
            }

            if (empty($image_url)) {
                throw new Exception('Silakan upload file foto atau masukkan URL gambar slide.');
            }

            if ($slide_id > 0) {
                $stmt = $pdo->prepare("UPDATE hero_sliders SET title = ?, subtitle = ?, badge_text = ?, image_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $badge_text, $image_url, $sort_order, $is_active, $slide_id]);
                log_audit($pdo, 'Edit Slide Beranda', "Memperbarui slide #{$slide_id}: {$title}");
                $msg = 'Slide gambar beranda berhasil diperbarui!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, badge_text, image_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $badge_text, $image_url, $sort_order, $is_active]);
                log_audit($pdo, 'Tambah Slide Beranda', "Menambahkan slide baru: {$title}");
                $msg = 'Slide foto baru berhasil di-upload dan ditambahkan ke Beranda!';
            }
        }

        // 4. Hapus Slide
        elseif ($form_action === 'delete_slide') {
            $slide_id = (int)($_POST['slide_id'] ?? 0);
            if ($slide_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?");
                $stmt->execute([$slide_id]);
                log_audit($pdo, 'Hapus Slide Beranda', "Menghapus slide #{$slide_id}");
                $msg = 'Slide gambar berhasil dihapus dari Beranda.';
            }
        }

        // 5. Toggle Aktif / Nonaktif Slide
        elseif ($form_action === 'toggle_slide') {
            $slide_id = (int)($_POST['slide_id'] ?? 0);
            if ($slide_id > 0) {
                $stmt = $pdo->prepare("UPDATE hero_sliders SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
                $stmt->execute([$slide_id]);
                $msg = 'Status tampil slide berhasil diperbarui.';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$theme_presets = get_website_theme_presets();
$active_theme  = get_active_website_theme($pdo);
$slider_cfg    = get_hero_slider_config($pdo);
$all_slides    = get_hero_sliders($pdo, false);

$edit_slide = null;
if (isset($_GET['edit_slide'])) {
    $edit_id = (int)$_GET['edit_slide'];
    foreach ($all_slides as $s) {
        if ((int)$s['id'] === $edit_id) {
            $edit_slide = $s;
            break;
        }
    }
}
?>

<div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
    <div class="space-y-1">
        <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 font-bold text-[11px]">
            TEMA WEBSITE & SLIDER BERANDA
        </span>
        <h1 class="text-xl font-extrabold">Pengaturan Tema Website & Gambar Slider Beranda</h1>
        <p class="text-xs text-slate-300 max-w-2xl">
            Atur foto slider halaman Beranda (tampil di <strong>sebelah kanan teks</strong> atau sebagai <strong>background beranda</strong>), ganti tema warna seluruh website, atau klik tombol di kanan untuk mengedit teks & gambar langsung di halaman website.
        </p>
    </div>
    <a href="../index.php?live_edit=1" class="px-4 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-md transition shrink-0">
        ✏️ Buka Website & Edit Langsung (Live Editor)
    </a>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-900 text-xs p-3.5 rounded-xl font-bold">
        ✓ <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-900 text-xs p-3.5 rounded-xl font-bold">
        ⚠️ <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- BAGIAN 1: PENGATURAN SLIDER GAMBAR BERANDA -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5 text-xs">
    <div class="border-b border-slate-200 pb-3">
        <h2 class="text-base font-extrabold text-slate-900">1. Pengaturan Posisi & Animasi Gambar Slider Beranda</h2>
        <p class="text-slate-500">Pilih apakah foto yang di-upload tampil di sebelah kanan teks utama Beranda, sebagai background penuh Beranda, atau keduanya.</p>
    </div>

    <form method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_slider_config">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $modes = [
                'side' => ['title' => 'Di Sebelah Kanan Teks', 'desc' => 'Teks di kiri, Slider Foto tampil di kolom sebelah kanan.'],
                'background' => ['title' => 'Sebagai Background Beranda', 'desc' => 'Foto slider menjadi latar belakang penuh di belakang teks Beranda.'],
                'both' => ['title' => 'Background + Sebelah Kanan', 'desc' => 'Gambar tampil di background sekaligus kartu slider di kanan.'],
                'noc' => ['title' => 'Panel NOC Default', 'desc' => 'Tampilan standar Beranda tanpa slider gambar.']
            ];
            foreach ($modes as $m_key => $m_info):
                $checked = ($slider_cfg['layout_mode'] === $m_key);
            ?>
            <label class="p-4 rounded-2xl border-2 cursor-pointer block transition <?= $checked ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200 hover:border-slate-300' ?>">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-extrabold text-slate-900"><?= e($m_info['title']) ?></span>
                    <input type="radio" name="hero_slider_layout" value="<?= e($m_key) ?>" <?= $checked ? 'checked' : '' ?> class="accent-blue-600">
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed"><?= e($m_info['desc']) ?></p>
            </label>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-100">
            <label class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between cursor-pointer">
                <span class="font-bold text-slate-800">Geser Otomatis (Auto-Slide)</span>
                <input type="checkbox" name="hero_slider_autoplay" value="1" <?= !empty($slider_cfg['auto_play']) ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
            </label>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <label class="block font-bold text-slate-800 mb-1">Durasi Pergantian (Detik)</label>
                <input type="number" name="hero_slider_interval" min="2" max="15" value="<?= (int)$slider_cfg['interval_seconds'] ?>" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white font-semibold">
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <label class="block font-bold text-slate-800 mb-1">Gelap Overlay Background (%)</label>
                <input type="number" name="hero_slider_overlay" min="35" max="92" value="<?= (int)$slider_cfg['overlay_opacity'] ?>" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white font-semibold">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                Simpan Pengaturan Posisi Slider
            </button>
        </div>
    </form>
</div>

<!-- BAGIAN 2: UPLOAD & KELOLA FOTO SLIDER BERANDA -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 text-xs">
    <!-- Form Tambah / Edit Foto Slide -->
    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 self-start">
        <h3 class="text-sm font-extrabold text-slate-900 border-b pb-3">
            <?= $edit_slide ? 'Edit Slide Gambar #' . (int)$edit_slide['id'] : '+ Upload / Tambah Foto Slider Beranda' ?>
        </h3>

        <form method="POST" enctype="multipart/form-data" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="save_slide">
            <input type="hidden" name="slide_id" value="<?= (int)($edit_slide['id'] ?? 0) ?>">

            <div>
                <label class="block font-bold text-slate-800 mb-1">Upload File Foto dari Perangkat (JPG/PNG/WEBP)</label>
                <input type="file" name="slide_image_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full border border-slate-300 rounded-xl p-2 bg-slate-50">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Atau URL Gambar Slide</label>
                <input type="text" name="image_url" value="<?= e($edit_slide['image_url'] ?? '') ?>" placeholder="https://... atau uploads/..." class="w-full border border-slate-300 rounded-xl p-2.5 font-mono text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Label Badge Kecil</label>
                    <input type="text" name="badge_text" value="<?= e($edit_slide['badge_text'] ?? 'Infrastruktur KGS') ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" min="1" value="<?= (int)($edit_slide['sort_order'] ?? (count($all_slides) + 1)) ?>" class="w-full border border-slate-300 rounded-xl p-2.5 font-bold">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-800 mb-1">Judul Slide / Caption Utama *</label>
                <input type="text" name="title" required value="<?= e($edit_slide['title'] ?? '') ?>" placeholder="Contoh: Jaringan Fiber Optic & Tower Telekomunikasi" class="w-full border border-slate-300 rounded-xl p-2.5 font-semibold">
            </div>

            <div>
                <label class="block font-bold text-slate-800 mb-1">Subjudul / Keterangan Slide</label>
                <textarea name="subtitle" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5"><?= e($edit_slide['subtitle'] ?? '') ?></textarea>
            </div>

            <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_slide) || !empty($edit_slide['is_active'])) ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                <span>Aktifkan slide ini di halaman Beranda</span>
            </label>

            <div class="pt-3 border-t flex items-center justify-end gap-2">
                <?php if ($edit_slide): ?>
                    <a href="tema-slider.php" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold">Batal</a>
                <?php endif; ?>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                    <?= $edit_slide ? 'Simpan Perubahan Slide' : '+ Upload & Simpan Slide' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Slide Beranda -->
    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-extrabold text-slate-900 border-b pb-3">
            Daftar Gambar Slider Beranda Saat Ini (<?= count($all_slides) ?> Slide)
        </h3>

        <div class="space-y-3">
            <?php foreach ($all_slides as $sl): ?>
                <?php
                $img_preview = $sl['image_url'];
                if (!preg_match('/^https?:\/\//i', $img_preview) && !str_starts_with($img_preview, 'data:')) {
                    $img_preview = '../' . ltrim($img_preview, '/');
                }
                ?>
                <div class="p-3.5 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 <?= !empty($sl['is_active']) ? 'bg-white' : 'bg-slate-50 opacity-70' ?>">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <img src="<?= e($img_preview) ?>" alt="<?= e($sl['title']) ?>" class="w-24 h-16 object-cover rounded-xl border border-slate-200 shrink-0 bg-slate-900">
                        <div class="min-w-0 space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-bold text-[10px]">Urutan #<?= (int)$sl['sort_order'] ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= !empty($sl['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                    <?= !empty($sl['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                            <div class="font-bold text-slate-900 truncate"><?= e($sl['title']) ?></div>
                            <div class="text-[11px] text-slate-500 line-clamp-1"><?= e($sl['subtitle']) ?></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="tema-slider.php?edit_slide=<?= (int)$sl['id'] ?>" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg">Edit</a>
                        <form method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="toggle_slide">
                            <input type="hidden" name="slide_id" value="<?= (int)$sl['id'] ?>">
                            <button type="submit" class="px-2.5 py-1.5 border border-slate-200 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
                                <?= !empty($sl['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </button>
                        </form>
                        <form method="POST" class="inline" onsubmit="return confirm('Hapus slide gambar ini dari Beranda?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete_slide">
                            <input type="hidden" name="slide_id" value="<?= (int)$sl['id'] ?>">
                            <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg cursor-pointer">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- BAGIAN 3: PENGATURAN TEMA & WARNA WEBSITE -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5 text-xs">
    <div class="border-b border-slate-200 pb-3">
        <h2 class="text-base font-extrabold text-slate-900">2. Pengaturan Tema & Warna Website</h2>
        <p class="text-slate-500">Pilih preset tema korporasi atau sesuaikan warna utama dan latar beranda.</p>
    </div>

    <form method="POST" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_theme">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($theme_presets as $p_key => $preset): ?>
                <?php $is_sel = ($active_theme['preset_id'] ?? 'corporate-blue') === $p_key; ?>
                <label class="p-4 rounded-2xl border-2 cursor-pointer block transition <?= $is_sel ? 'border-blue-600 bg-blue-50/40' : 'border-slate-200 hover:border-slate-300' ?>">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-extrabold text-slate-900"><?= e($preset['name']) ?></span>
                        <input type="radio" name="theme_preset" value="<?= e($p_key) ?>" <?= $is_sel ? 'checked' : '' ?>
                               onclick="document.getElementById('custom_primary_color').value='<?= e($preset['primary_color']) ?>'; document.getElementById('custom_hero_bg').value='<?= e($preset['hero_bg_color']) ?>';">
                    </div>
                    <p class="text-[11px] text-slate-600 mb-3"><?= e($preset['description']) ?></p>
                    <div class="h-10 rounded-xl p-2 flex items-center justify-between" style="background-color: <?= e($preset['hero_bg_color']) ?>;">
                        <span class="text-[10px] text-white font-mono"><?= e($preset['hero_bg_color']) ?></span>
                        <span class="px-2.5 py-1 rounded text-[10px] font-bold text-white" style="background-color: <?= e($preset['primary_color']) ?>;">
                            <?= e($preset['primary_color']) ?>
                        </span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-100">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Warna Utama (Hex)</label>
                <input type="text" id="custom_primary_color" name="primary_color" value="<?= e($active_theme['primary_color']) ?>" class="w-full border border-slate-300 rounded-xl p-2.5 font-mono">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Warna Latar Hero Beranda (Hex)</label>
                <input type="text" id="custom_hero_bg" name="hero_bg_color" value="<?= e($active_theme['hero_bg_color']) ?>" class="w-full border border-slate-300 rounded-xl p-2.5 font-mono">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Gaya Header / Navbar</label>
                <select name="navbar_style" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-semibold">
                    <option value="light" <?= ($active_theme['navbar_style'] ?? 'light') === 'light' ? 'selected' : '' ?>>Terang Bersih (White Corporate)</option>
                    <option value="dark" <?= ($active_theme['navbar_style'] ?? '') === 'dark' ? 'selected' : '' ?>>Gelap Eksekutif (Dark Navy)</option>
                    <option value="glass" <?= ($active_theme['navbar_style'] ?? '') === 'glass' ? 'selected' : '' ?>>Transparan Modern (Glass)</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                Simpan & Terapkan Tema Website
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
