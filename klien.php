<?php
require_once __DIR__ . '/config/database.php';
$page_title = "Daftar Klien & Portofolio Proyek";
require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE name LIKE ? OR service_category LIKE ? OR location LIKE ? ORDER BY pdf_page ASC, id ASC");
    $term = "%{$search}%";
    $stmt->execute([$term, $term, $term]);
    $clients = $stmt->fetchAll();
} else {
    $clients = $pdo->query("SELECT * FROM clients ORDER BY pdf_page ASC, id ASC")->fetchAll();
}
?>

<div class="bg-white border-b border-slate-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block mb-1">Portofolio & Pengalaman Kerja Nyata</span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Klien Berdasarkan Solusi Pekerjaan</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-3xl leading-relaxed">
            Lebih dari 96 instansi BUMN, pemerintah daerah, perkebunan kelapa sawit, pertambangan batubara, rumah sakit, dan perbankan yang telah mempercayakan proyeknya kepada PT. KGS.
        </p>

        <!-- Search Box -->
        <form method="GET" action="klien.php" class="mt-6 max-w-md flex gap-2">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama klien atau lokasi..." class="flex-1 bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition">
                Cari
            </button>
            <?php if (!empty($search)): ?>
                <a href="klien.php" class="px-3 py-2 border rounded-lg text-xs text-slate-600 hover:bg-slate-100 flex items-center">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($clients as $c): ?>
        <div class="p-4 rounded-xl border border-slate-200 bg-white hover:shadow-xs transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                    <span class="font-medium text-blue-600"><?= e($c['service_category']) ?></span>
                    <span class="text-emerald-600 font-medium">✓ Terverifikasi</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900 leading-snug"><?= e($c['name']) ?></h4>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px] text-slate-600"><?= e($c['category']) ?></span>
                <?php if (!empty($c['location'])): ?>
                    <span class="text-[11px] text-slate-400">📍 <?= e($c['location']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($clients)): ?>
    <div class="text-center py-12 bg-white rounded-xl border border-dashed text-xs text-slate-500">
        Tidak ditemukan klien dengan kata kunci "<?= e($search) ?>". Silakan coba kata kunci lain.
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
