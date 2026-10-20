<?php
$admin_page_title = 'Dashboard Admin CMS';
require_once __DIR__ . '/layout-header.php';

// Ambil statistik lengkap dari database
$total_pages = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$total_services = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$published_services = $pdo->query("SELECT COUNT(*) FROM services WHERE is_published = 1")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM service_categories")->fetchColumn();
$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();

$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$published_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$draft_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();

$total_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$new_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Baru'")->fetchColumn();
$processed_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Diproses'")->fetchColumn();
$done_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Selesai'")->fetchColumn();

// Ambil 5 Permintaan Terbaru
$recent_inquiries = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Ambil 6 Aktivitas Admin Terbaru
$recent_logs = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Cek status sistem
$php_version = phpversion();
$db_version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
$upload_dir = __DIR__ . '/../uploads';
$is_upload_writable = is_dir($upload_dir) ? is_writable($upload_dir) : is_writable(__DIR__ . '/..');
?>

<!-- Welcome Banner & Quick Action Buttons -->
<div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/30 text-blue-300 border border-blue-400/30">
                Sistem Terpadu PT. KGS
            </span>
            <span class="text-xs text-slate-400 font-mono">Panel v2.4 Native</span>
        </div>
        <h1 class="text-2xl font-black tracking-tight">Selamat Datang, <?= e($admin_name) ?>!</h1>
        <p class="text-xs text-slate-300 max-w-xl">
            Sistem manajemen konten website resmi PT. Kreatif Global Solusindo (System Integrator, Security Solution & ISP).
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="tambah-layanan.php" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition">
            <span>+ Tambah Layanan</span>
        </a>
        <a href="tambah-artikel.php" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition">
            <span>+ Tulis Artikel</span>
        </a>
        <a href="inquiries.php?status=Baru" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition">
            <span>💬 Leads Baru (<?= $new_inquiries ?>)</span>
        </a>
        <a href="pengaturan.php" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium rounded-xl transition">
            ⚙️ Pengaturan & Logo
        </a>
    </div>
</div>

<!-- KPI Cards Grid (8 Metrik Utama) -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Layanan -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-blue-300 transition">
        <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Layanan Terstandar</div>
        <div class="text-3xl font-black text-blue-700 mt-2"><?= $total_services ?></div>
        <div class="text-[11px] text-emerald-600 mt-1 font-semibold flex items-center gap-1">
            <span>✓ <?= $published_services ?> Terbit</span>
            <span class="text-slate-400 font-normal">/ <?= $total_categories ?> Segmen Utama</span>
        </div>
    </div>

    <!-- Leads / Permintaan -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-amber-300 transition">
        <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Permintaan Penawaran</div>
        <div class="text-3xl font-black text-amber-600 mt-2"><?= $new_inquiries ?> Baru</div>
        <div class="text-[11px] text-slate-500 mt-1 font-medium">
            Total <?= $total_inquiries ?> Masuk (<?= $done_inquiries ?> Deal)
        </div>
    </div>

    <!-- Klien Nyata -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-400 transition">
        <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Portofolio Klien</div>
        <div class="text-3xl font-black text-slate-900 mt-2"><?= $total_clients ?></div>
        <div class="text-[11px] text-slate-500 mt-1 font-medium">
            Klien BUMN, Pemerintah & Korporasi
        </div>
    </div>

    <!-- Artikel Wawasan -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
        <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Artikel & Wawasan</div>
        <div class="text-3xl font-black text-emerald-700 mt-2"><?= $published_posts ?> Terbit</div>
        <div class="text-[11px] text-slate-400 mt-1 font-medium">
            <?= $draft_posts ?> Draft | <?= $total_posts ?> Total Konten
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left 2 Cols: Inquiries & Activity -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Recent Inquiries Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4 border-b pb-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Permintaan Penawaran Terbaru</h3>
                    <?php if ($new_inquiries > 0): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            <?= $new_inquiries ?> Baru
                        </span>
                    <?php endif; ?>
                </div>
                <a href="inquiries.php" class="text-xs font-semibold text-blue-600 hover:underline">
                    Kelola Semua Leads &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 uppercase text-[10px] text-slate-500 border-b">
                        <tr>
                            <th class="p-3">Nama & Perusahaan</th>
                            <th class="p-3">Layanan</th>
                            <th class="p-3">Lokasi</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recent_inquiries)): ?>
                            <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada permintaan penawaran baru.</td></tr>
                        <?php else: foreach ($recent_inquiries as $inq): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900"><?= e($inq['name']) ?></div>
                                    <div class="text-slate-500 text-[11px]"><?= e($inq['company'] ?: 'Perorangan') ?></div>
                                </td>
                                <td class="p-3 font-semibold text-blue-700"><?= e($inq['service_name']) ?></td>
                                <td class="p-3 text-slate-500"><?= e($inq['location']) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $inq['status'] === 'Baru' ? 'bg-amber-100 text-amber-800' : ($inq['status'] === 'Diproses' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                        <?= e($inq['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <a href="detail-inquiry.php?id=<?= $inq['id'] ?>" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md font-semibold text-[11px] border border-blue-200">
                                        Proses &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Activity -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4 border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Aktivitas Admin & Keamanan Terkini</h3>
                <a href="audit-log.php" class="text-xs font-semibold text-blue-600 hover:underline">
                    Lihat Seluruh Log &rarr;
                </a>
            </div>

            <div class="space-y-3">
                <?php if (empty($recent_logs)): ?>
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada aktivitas tercatat.</p>
                <?php else: foreach ($recent_logs as $log): ?>
                    <div class="flex items-center justify-between text-xs py-2 border-b border-slate-50 last:border-none">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="font-bold text-slate-800"><?= e($log['action']) ?></span>
                            <span class="text-slate-500 text-[11px]"><?= e($log['details']) ?></span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400"><?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Col: System Status & PDF Matrix -->
    <div class="space-y-6">
        <!-- System Status Box -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Sistem & Lingkungan</h3>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Versi PHP</span>
                    <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">PHP <?= e($php_version) ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Database Driver</span>
                    <span class="font-mono font-bold text-blue-700">PDO MySQL (<?= e(substr($db_version, 0, 15)) ?>)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Folder Uploads</span>
                    <span class="font-semibold <?= $is_upload_writable ? 'text-emerald-600' : 'text-amber-600' ?>">
                        <?= $is_upload_writable ? '✓ Writable' : 'Read Only' ?>
                    </span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Waktu Server</span>
                    <span class="font-mono text-slate-700"><?= date('H:i:s T') ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-500">Enkripsi Kata Sandi</span>
                    <span class="font-bold text-slate-800">BCRYPT (Cost 12)</span>
                </div>
            </div>
        </div>

        <!-- Katalog Standar Summary -->
        <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Ringkasan Katalog KGS</span>
                <span class="text-xs text-emerald-400 font-bold">Aktif</span>
            </div>
            <h4 class="font-bold text-sm leading-snug">Katalog Solusi & Kemitraan Resmi</h4>
            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span>Layanan Terstandar:</span>
                    <strong class="text-white">22 Layanan Utama</strong>
                </div>
                <div class="flex justify-between">
                    <span>Mitra & ISP Support:</span>
                    <strong class="text-white">6 Rekanan Resmi</strong>
                </div>
                <div class="flex justify-between">
                    <span>Sertifikasi Resmi:</span>
                    <strong class="text-white">Sony, Panasonic, Netviel, MTCRE</strong>
                </div>
                <div class="flex justify-between">
                    <span>Klien Korporasi:</span>
                    <strong class="text-white">96+ Entitas Bisnis</strong>
                </div>
            </div>
            <a href="audit-pdf.php" class="block text-center py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                Buka Katalog 22 Standar &rarr;
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
