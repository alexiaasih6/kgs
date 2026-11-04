<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (function_exists('ensure_clients_table')) {
    ensure_clients_table($pdo);
}

$klien_page = get_page_by_slug($pdo, 'klien');
$page_title = (!empty($klien_page['meta_title']) ? $klien_page['meta_title'] : ($klien_page['title'] ?: 'Klien & Sektor Industri Mitra')) . ' - PT. Kreatif Global Solusindo';
$page_description = !empty($klien_page['meta_description']) ? $klien_page['meta_description'] : ($klien_page['summary'] ?: 'Daftar klien dan mitra industri PT. Kreatif Global Solusindo (KGS) dari sektor BUMN, perbankan, rumah sakit, perhotelan, pendidikan, pertambangan, dan maritim.');

$sectors = get_client_sectors($pdo);
$clients_db = [];
try {
    $clients_db = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $clients_db = [];
}

$solution_categories = [];
foreach ($clients_db as $row) {
    $sc = trim((string)($row['service_category'] ?? ''));
    if ($sc !== '' && !in_array($sc, $solution_categories, true)) {
        $solution_categories[] = $sc;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 font-bold text-xs uppercase tracking-widest inline-block" data-live-setting="klien_badge"><?= e(get_setting($pdo, 'klien_badge', 'CLIENT Portofolio & Pengalaman Lapangan')) ?></span>
                    <span class="text-slate-500">·</span>
                    <span class="text-xs font-semibold text-emerald-300">Rekam Jejak Nyata &amp; Klien Terdaftar</span>
                </div>
                <h1 data-live-page-slug="klien" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-3 tracking-tight"><?= e($klien_page['title'] ?: 'Portofolio Proyek & Klien Korporasi') ?></h1>
            </div>
            <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                <div class="flex flex-wrap gap-2">
                    <a href="admin/klien.php" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow transition">+ Kelola Klien &amp; Upload Foto</a>
                    <a href="admin/bagian-halaman.php?tab=klien_sektor" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition">+ Kelola 6 Sektor Industri</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="space-y-3 text-slate-300 text-sm sm:text-base mt-4 max-w-4xl leading-relaxed">
            <p data-live-page-slug="klien" data-live-page-field="summary">
                <?= e($klien_page['summary'] ?: 'Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.') ?>
            </p>
            <p>
                Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;
            </p>
        </div>

        <!-- Executive B2B Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-10 pt-8 border-t border-slate-800/90">
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/70">
                <div class="text-2xl font-extrabold text-white"><?= count($clients_db) > 0 ? count($clients_db) . '+' : '100+' ?></div>
                <div class="text-xs text-slate-400 mt-0.5">Entitas Klien BUMN, Swasta &amp; Instansi</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/70">
                <div class="text-2xl font-extrabold text-blue-400"><?= count($sectors) ?: 6 ?> Sektor</div>
                <div class="text-xs text-slate-400 mt-0.5">Industri Strategis Terlayani</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/70">
                <div class="text-2xl font-extrabold text-emerald-400">Sejak 1997</div>
                <div class="text-xs text-slate-400 mt-0.5">Rekam Jejak &amp; Dedikasi Teknis</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/70">
                <div class="text-2xl font-extrabold text-amber-400">SLA 99.5%</div>
                <div class="text-xs text-slate-400 mt-0.5">Dukungan NOC &amp; Teknisi Bersertifikasi</div>
            </div>
        </div>
    </div>
</section>

<!-- Direktori Klien B2B Profesional dengan Foto/Logo & Filter Interaktif -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">

        <?php if (!empty($clients_db)): ?>
        <div class="space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600">Portofolio Klien Terverifikasi</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Daftar Klien &amp; Dokumentasi Mitra B2B</h2>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">Filter berdasarkan solusi teknis atau cari nama perusahaan/instansi yang telah bekerja sama dengan PT. KGS.</p>
                </div>

                <!-- Pencarian Cepat Klien -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
                    <div class="relative w-full sm:w-72">
                        <input type="search" id="clientSearchInput" placeholder="Cari nama klien, sektor, lokasi..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs text-slate-800 focus:outline-none focus:border-blue-600 shadow-2xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Kategori Solusi -->
            <div class="flex flex-wrap items-center gap-2" id="clientCategoryFilters">
                <button type="button" data-filter-cat="all" class="client-filter-btn px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-xs transition cursor-pointer">
                    Semua Klien (<?= count($clients_db) ?>)
                </button>
                <?php foreach ($solution_categories as $sol_cat): ?>
                    <button type="button" data-filter-cat="<?= e($sol_cat) ?>" class="client-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:border-blue-400 transition cursor-pointer">
                        <?= e($sol_cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Grid Kartu Klien Profesional -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" id="clientCardsGrid">
                <?php foreach ($clients_db as $cl):
                    $cl_img = trim((string)($cl['image_url'] ?? ($cl['logo_url'] ?? '')));
                    $cl_web = trim((string)($cl['website_url'] ?? ''));
                    $cl_cat = trim((string)($cl['category'] ?: 'Korporasi & Instansi'));
                    $cl_sol = trim((string)($cl['service_category'] ?: 'Infrastruktur IT & Telekomunikasi'));
                    $cl_loc = trim((string)($cl['location'] ?: 'Sumatera Selatan'));
                    $clean_title = preg_replace('/^(PT\.|CV\.|RS\.|RSUD|BANK|PERUMDA)\s*/i', '', trim((string)$cl['name']));
                    $words = preg_split('/\s+/', trim((string)$clean_title));
                    $monogram = strtoupper(substr($words[0] ?? 'K', 0, 1) . substr($words[1] ?? ($words[0] ?? 'G'), isset($words[1]) ? 0 : 1, 1));
                ?>
                <div class="client-card-item group bg-white rounded-2xl border border-slate-200 hover:border-blue-400 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between"
                     data-client-cat="<?= e($cl_sol) ?>"
                     data-client-search="<?= e(strtolower($cl['name'] . ' ' . $cl_cat . ' ' . $cl_sol . ' ' . $cl_loc)) ?>">

                    <?php if ($cl_img !== ''): ?>
                        <div class="relative h-44 w-full bg-slate-100 border-b border-slate-100 flex items-center justify-center p-4 overflow-hidden">
                            <img src="<?= e($cl_img) ?>" alt="<?= e($cl['name']) ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300">
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500/95 text-slate-950 font-extrabold text-[10px] shadow-xs">
                                ✓ Mitra Terverifikasi
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="flex items-start gap-3.5">
                            <?php if ($cl_img === ''): ?>
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-slate-900 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs">
                                    <?= e($monogram) ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[11px] truncate" data-live-record="clients:<?= (int)$cl['id'] ?>:category">
                                        <?= e($cl_cat) ?>
                                    </span>
                                    <?php if ($cl_img === ''): ?>
                                        <span class="text-[11px] font-bold text-emerald-600 shrink-0">✓ Terverifikasi</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-base font-extrabold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors" data-live-record="clients:<?= (int)$cl['id'] ?>:name">
                                    <?= e($cl['name']) ?>
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                                    <span>📍</span>
                                    <span data-live-record="clients:<?= (int)$cl['id'] ?>:location"><?= e($cl_loc) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 block">Implementasi Solusi KGS</span>
                                <span class="font-bold text-slate-800 truncate block" data-live-record="clients:<?= (int)$cl['id'] ?>:service_category"><?= e($cl_sol) ?></span>
                            </div>
                            <?php if ($cl_web !== ''): ?>
                                <a href="<?= e(sanitize_nav_url($cl_web)) ?>" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-blue-600 font-bold text-[11px] shrink-0 transition">
                                    Detail &nearr;
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="clientEmptyState" class="hidden p-10 text-center bg-white rounded-2xl border border-dashed border-slate-300 text-sm text-slate-500">
                Tidak ditemukan data klien yang cocok dengan kata kunci pencarian Anda.
            </div>
        </div>
        <?php endif; ?>

        <!-- Daftar Klien Berdasarkan 6 Sektor Industri (Dinamis dari Tabel `client_sectors`) -->
        <div class="space-y-6 pt-6 border-t border-slate-200">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600">Cakupan Lintas Sektor</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Pengelompokan Klien Berdasarkan 6 Sektor Industri Utama</h2>
            </div>
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
                        <h3 class="text-lg font-extrabold text-slate-900 mt-3 mb-3" data-live-record="client_sectors:<?= (int)$sec['id'] ?>:sector_title"><?= e($sec['sector_title']) ?></h3>
                        <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line" data-live-record="client_sectors:<?= (int)$sec['id'] ?>:client_list"><?= e(implode("\n", array_map(function($cl) { return '• ' . ltrim($cl, '•- '); }, $sector_clients))) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

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

<script>
(function() {
    var searchInp = document.getElementById('clientSearchInput');
    var filterBtns = document.querySelectorAll('.client-filter-btn');
    var cards = document.querySelectorAll('.client-card-item');
    var emptyState = document.getElementById('clientEmptyState');
    var activeCat = 'all';

    function filterClients() {
        var q = searchInp ? searchInp.value.trim().toLowerCase() : '';
        var visibleCount = 0;
        cards.forEach(function(card) {
            var cat = card.getAttribute('data-client-cat') || '';
            var hay = card.getAttribute('data-client-search') || '';
            var matchCat = (activeCat === 'all' || cat === activeCat);
            var matchQ = (q === '' || hay.indexOf(q) !== -1);
            if (matchCat && matchQ) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });
        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount > 0);
        }
    }

    if (searchInp) {
        searchInp.addEventListener('input', filterClients);
    }

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            activeCat = btn.getAttribute('data-filter-cat') || 'all';
            filterBtns.forEach(function(b) {
                var isAct = (b === btn);
                b.className = isAct
                    ? 'client-filter-btn px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-xs transition cursor-pointer'
                    : 'client-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:border-blue-400 transition cursor-pointer';
            });
            filterClients();
        });
    });
})();
</script>

<?php if (!empty($klien_page['content'])): ?>
<section class="py-12 bg-white border-t border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-live-page-slug="klien" data-live-page-field="content" class="prose prose-slate max-w-none text-sm text-slate-600 leading-relaxed"><?= sanitize_rich_html($klien_page['content']) ?></div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
