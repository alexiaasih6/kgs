<?php
$admin_page_title = 'Pengaturan SEO & Metadata';
require_once __DIR__ . '/layout-header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $seo_keys = [
        'meta_title_default' => trim($_POST['meta_title_default'] ?? ''),
        'meta_desc_default' => trim($_POST['meta_desc_default'] ?? ''),
        'canonical_url' => trim($_POST['canonical_url'] ?? ''),
        'robots_directive' => trim($_POST['robots_directive'] ?? 'index, follow')
    ];

    foreach ($seo_keys as $k => $v) {
        save_setting($pdo, $k, $v, 'seo');
    }
    clear_settings_cache();

    log_audit($pdo, 'Update SEO', 'Memperbarui konfigurasi SEO dan metadata global');
    $msg = 'Pengaturan SEO dan Metadata berhasil disimpan.';
}

$meta_title = get_setting($pdo, 'meta_title_default') ?: 'PT. Kreatif Global Solusindo - System Integrator, Security Solution & ISP';
$meta_desc = get_setting($pdo, 'meta_desc_default') ?: 'Portal resmi PT. Kreatif Global Solusindo: Solusi jaringan komputer, fiber optik, PABX, CCTV, penangkal petir, dan ISP KGSNet resmi APJII sejak 1997 di Palembang.';
$canonical = get_setting($pdo, 'canonical_url') ?: 'https://www.kgs.net.id';
$robots = get_setting($pdo, 'robots_directive') ?: 'index, follow';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Pengaturan Global SEO & Metadata</h1>
            <p class="text-xs text-slate-500">Konfigurasi OpenGraph, Meta Tags, dan arahan Robot untuk perayapan Google.</p>
        </div>
        <a href="index.php" class="text-xs text-slate-500 hover:text-slate-800">&larr; Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5 text-xs">
        <?= csrf_field() ?>

        <div>
            <label class="block font-semibold mb-1">Default Meta Title (Judul Browser Global) *</label>
            <input type="text" name="meta_title_default" value="<?= e($meta_title) ?>" required class="w-full border rounded-lg p-2.5">
            <p class="text-[11px] text-slate-400 mt-1">Disarankan antara 50 - 60 karakter untuk keterbacaan optimal di hasil pencarian Google.</p>
        </div>

        <div>
            <label class="block font-semibold mb-1">Default Meta Description (Deskripsi Cuplikan Snippet) *</label>
            <textarea name="meta_desc_default" rows="3" required class="w-full border rounded-lg p-2.5"><?= e($meta_desc) ?></textarea>
            <p class="text-[11px] text-slate-400 mt-1">Disarankan antara 120 - 160 karakter.</p>
        </div>

        <div>
            <label class="block font-semibold mb-1">Canonical URL Domain Utama</label>
            <input type="url" name="canonical_url" value="<?= e($canonical) ?>" required class="w-full border rounded-lg p-2.5 font-mono">
            <p class="text-[11px] text-slate-400 mt-1">Mencegah duplikasi konten antar protokol HTTP/HTTPS atau WWW.</p>
        </div>

        <div>
            <label class="block font-semibold mb-1">Robots.txt Directive Indexing</label>
            <select name="robots_directive" class="w-full border rounded-lg p-2.5 bg-white">
                <option value="index, follow" <?= $robots === 'index, follow' ? 'selected' : '' ?>>index, follow (Direkomendasikan: Izinkan Google Mengindeks)</option>
                <option value="noindex, nofollow" <?= $robots === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow (Blokir Mesin Pencari / Mode Uji Coba)</option>
            </select>
        </div>

        <div class="pt-4 border-t flex justify-end gap-3">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition">
                Simpan Konfigurasi SEO
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
