<?php
require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header("Location: layanan.php");
    exit;
}

$stmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.code as category_code FROM services s JOIN service_categories c ON s.category_id = c.id WHERE s.slug = ? AND s.is_published = 1 LIMIT 1");
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: layanan.php");
    exit;
}

$page_title = $service['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white border-b border-slate-200 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="layanan.php?cat=<?= urlencode($service['category_code']) ?>" class="text-xs text-blue-600 font-semibold hover:underline mb-2 block">
            &larr; Kembali ke Segmen <?= e($service['category_name']) ?>
        </a>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
            <?= e($service['name']) ?>
        </h1>
        <div class="flex items-center gap-3 text-xs text-slate-500 mt-3">
            <span class="font-semibold text-blue-700"><?= e($service['category_name']) ?></span>
            <span>·</span>
            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold text-[11px]"><?= e($service['verification_status'] === 'Terverifikasi dari PDF' ? 'Standar Resmi KGS' : ($service['verification_status'] ?: 'Standar Resmi KGS')) ?></span>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Main Content (2 cols) -->
        <div class="md:col-span-2 space-y-8 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <div>
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Ringkasan Layanan</h3>
                <p class="text-base text-slate-800 font-medium">
                    <?= e($service['summary']) ?>
                </p>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Deskripsi & Cakupan Teknis</h3>
                <p class="text-slate-700 leading-relaxed">
                    <?= nl2br(e($service['description'])) ?>
                </p>
            </div>

            <div class="p-4 bg-blue-50/60 rounded-xl border border-blue-200 text-xs text-blue-900">
                <span class="font-bold block mb-1">Standar Layanan & Jaminan Mutu:</span>
                <span><?= e($service['admin_notes'] ?: 'Standar Layanan Resmi PT. KGS') ?></span>
            </div>
        </div>

        <!-- Sidebar CTA (1 col) -->
        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h4 class="text-sm font-bold text-slate-900">Konsultasikan Solusi Ini</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Dapatkan analisis teknis dan estimasi penawaran harga resmi dari tim sales engineering PT. Kreatif Global Solusindo.
                </p>
                <a href="kontak.php?layanan=<?= urlencode($service['name']) ?>#konsultasi" class="block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    Minta Penawaran Resmi
                </a>
                <div class="pt-3 border-t text-[11px] text-slate-500 space-y-1.5">
                    <div>📞 Hunting: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></span></div>
                    <div>💬 WhatsApp: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></div>
                    <div>📍 <span data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></span></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
