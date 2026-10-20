<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$klien_page = get_page_by_slug($pdo, 'klien');
$page_title = (!empty($klien_page['meta_title']) ? $klien_page['meta_title'] : ($klien_page['title'] ?: 'Klien & Sektor Industri Mitra')) . ' - PT. Kreatif Global Solusindo';
$page_description = !empty($klien_page['meta_description']) ? $klien_page['meta_description'] : ($klien_page['summary'] ?: 'Daftar klien dan mitra industri PT. Kreatif Global Solusindo (KGS) dari sektor BUMN, perbankan, rumah sakit, perhotelan, pendidikan, pertambangan, dan maritim.');

$sectors = get_client_sectors($pdo);
$clients_db = [];
try {
    $clients_db = $pdo->query("SELECT * FROM clients ORDER BY pdf_page ASC, id ASC")->fetchAll();
} catch (Exception $e) {
    $clients_db = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-blue-400 font-bold text-xs uppercase tracking-widest" data-live-setting="klien_badge"><?= e(get_setting($pdo, 'klien_badge', 'Kepercayaan Lintas Industri')) ?></span>
                <h1 data-live-page-slug="klien" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-2"><?= e($klien_page['title'] ?: 'Klien & Sektor Industri yang Kami Layani') ?></h1>
            </div>
            <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                <div class="flex flex-wrap gap-2">
                    <a href="admin/klien.php" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow transition">+ Kelola Daftar Klien Proyek</a>
                    <a href="admin/bagian-halaman.php?tab=klien_sektor" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition">+ Kelola 6 Sektor Industri</a>
                </div>
            <?php endif; ?>
        </div>
        <p data-live-page-slug="klien" data-live-page-field="summary" class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl leading-relaxed">
            <?= e($klien_page['summary'] ?: 'Sejak 1997, PT. Kreatif Global Solusindo dipercaya menangani infrastruktur jaringan, telekomunikasi, keamanan, dan konektivitas internet untuk berbagai institusi di Sumatera Selatan dan Indonesia.') ?>
        </p>
    </div>
</section>

<!-- Daftar Klien Berdasarkan Sektor (Dinamis dari Tabel `client_sectors`) -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($sectors as $sec): 
                $badge_bg = 'bg-blue-50 text-blue-700';
                if ($sec['badge_color'] === 'emerald') $badge_bg = 'bg-emerald-50 text-emerald-700';
                elseif ($sec['badge_color'] === 'amber') $badge_bg = 'bg-amber-50 text-amber-700';
                elseif ($sec['badge_color'] === 'purple') $badge_bg = 'bg-purple-50 text-purple-700';
                elseif ($sec['badge_color'] === 'cyan') $badge_bg = 'bg-cyan-50 text-cyan-700';
                elseif ($sec['badge_color'] === 'rose') $badge_bg = 'bg-rose-50 text-rose-700';

                $sector_clients = array_filter(array_map('trim', explode("\n", (string)$sec['client_list'])));
            ?>
                <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-xs">
                    <span class="px-3 py-1 <?= $badge_bg ?> font-bold text-xs rounded-md" data-live-record="client_sectors:<?= (int)$sec['id'] ?>:badge_label"><?= e($sec['badge_label']) ?></span>
                    <h2 class="text-lg font-extrabold text-slate-900 mt-3 mb-3" data-live-record="client_sectors:<?= (int)$sec['id'] ?>:sector_title"><?= e($sec['sector_title']) ?></h2>
                    <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line" data-live-record="client_sectors:<?= (int)$sec['id'] ?>:client_list"><?= e(implode("\n", array_map(function($cl) { return '• ' . ltrim($cl, '•- '); }, $sector_clients))) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Direktori Klien Terverifikasi Berdasarkan Proyek (Sinkron dengan Admin CMS -> Kelola Klien `clients`) -->
        <?php if (!empty($clients_db)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Rekam Jejak Implementasi</span>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-0.5">Daftar Klien Proyek Terverifikasi (<?= count($clients_db) ?> Entitas)</h2>
                </div>
                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                    <a href="admin/klien.php" class="text-xs font-bold text-blue-600 hover:underline">+ Tambah / Edit Data Klien &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 uppercase text-[10px] font-bold text-slate-500">
                        <tr>
                            <th class="p-3.5">Nama Perusahaan / Instansi Klien</th>
                            <th class="p-3.5">Bidang Industri</th>
                            <th class="p-3.5">Solusi Pekerjaan KGS</th>
                            <th class="p-3.5">Wilayah / Lokasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($clients_db as $cl): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-bold text-slate-900" data-live-record="clients:<?= (int)$cl['id'] ?>:name"><?= e($cl['name']) ?></td>
                            <td class="p-3.5 text-slate-600" data-live-record="clients:<?= (int)$cl['id'] ?>:category"><?= e($cl['category'] ?: 'Korporasi & Instansi') ?></td>
                            <td class="p-3.5 font-semibold text-blue-700" data-live-record="clients:<?= (int)$cl['id'] ?>:service_category"><?= e($cl['service_category']) ?></td>
                            <td class="p-3.5 text-slate-500" data-live-record="clients:<?= (int)$cl['id'] ?>:location"><?= e($cl['location'] ?: 'Sumatera Selatan') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ringkasan Portofolio Terkait -->
        <div class="bg-slate-900 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-extrabold" data-live-setting="klien_cta_title"><?= e(get_setting($pdo, 'klien_cta_title', 'Ingin Melihat Bukti Dokumentasi Pekerjaan Lapangan Kami?')) ?></h3>
                <p class="text-xs text-slate-300 mt-1" data-live-setting="klien_cta_desc"><?= e(get_setting($pdo, 'klien_cta_desc', 'Lihat foto fabrikasi tower guywire, hasil uji Digital Earth Tester grounding < 2 Ohm, dan instalasi perangkat di halaman Portofolio.')) ?></p>
            </div>
            <a href="portofolio.php" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shrink-0 transition">Buka Halaman Portofolio &rarr;</a>
        </div>
    </div>
</section>

<?php if (!empty($klien_page['content'])): ?>
<section class="py-12 bg-white border-t border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-live-page-slug="klien" data-live-page-field="content" class="prose prose-slate max-w-none text-sm text-slate-600 leading-relaxed"><?= sanitize_rich_html($klien_page['content']) ?></div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
