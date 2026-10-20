<?php
$admin_page_title = 'Permintaan Penawaran & Leads';
require_once __DIR__ . '/layout-header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'status' && $id > 0) {
        $status = $_POST['status'] ?? 'Baru';
        $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        log_audit($pdo, 'Update Status Lead', "Mengubah status permintaan penawaran ID $id menjadi: $status");
        $msg = 'Status tindak lanjut penawaran berhasil diubah.';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        log_audit($pdo, 'Hapus Lead', "Menghapus permintaan penawaran ID: $id");
        $msg = 'Data permintaan penawaran berhasil dihapus.';
    }
}

// Filter pencarian dan status
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM inquiries WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

// Hitung total per status
$count_all = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$count_baru = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Baru'")->fetchColumn();
$count_diproses = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Diproses'")->fetchColumn();
$count_selesai = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Selesai'")->fetchColumn();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Manajemen Permintaan Penawaran (Leads)</h1>
        <p class="text-xs text-slate-500">Kelola formulir konsultasi masuk, tindak lanjut via WhatsApp, dan catat histori penawaran.</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($msg) ?>
    </div>
<?php endif; ?>

<!-- Quick Filter Tabs -->
<div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
    <a href="inquiries.php" class="px-3.5 py-2 rounded-lg border transition <?= empty($status_filter) ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-300' ?>">
        Semua (<?= $count_all ?>)
    </a>
    <a href="inquiries.php?status=Baru" class="px-3.5 py-2 rounded-lg border transition <?= $status_filter === 'Baru' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-amber-700 hover:bg-amber-50 border-slate-300' ?>">
        Baru (<?= $count_baru ?>)
    </a>
    <a href="inquiries.php?status=Diproses" class="px-3.5 py-2 rounded-lg border transition <?= $status_filter === 'Diproses' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-700 hover:bg-blue-50 border-slate-300' ?>">
        Diproses (<?= $count_diproses ?>)
    </a>
    <a href="inquiries.php?status=Selesai" class="px-3.5 py-2 rounded-lg border transition <?= $status_filter === 'Selesai' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-emerald-700 hover:bg-emerald-50 border-slate-300' ?>">
        Selesai (<?= $count_selesai ?>)
    </a>
</div>

<!-- Search Bar -->
<div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <?php if (!empty($status_filter)): ?>
            <input type="hidden" name="status" value="<?= e($status_filter) ?>">
        <?php endif; ?>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama pemohon, perusahaan, email, atau telepon..." class="flex-1 text-xs px-3.5 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm">
            Cari
        </button>
        <?php if (!empty($search)): ?>
            <a href="inquiries.php<?= !empty($status_filter) ? '?status=' . e($status_filter) : '' ?>" class="px-3 py-2 border rounded-lg text-xs text-slate-500 hover:bg-slate-50 flex items-center justify-center">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Inquiries Cards / List -->
<div class="space-y-4">
    <?php if (empty($inquiries)): ?>
        <div class="bg-white p-12 rounded-2xl border text-center text-xs text-slate-400">
            Tidak ada permintaan penawaran pada filter ini.
        </div>
    <?php else: foreach ($inquiries as $inq): 
        $clean_phone = preg_replace('/[^0-9]/', '', $inq['phone']);
        if (substr($clean_phone, 0, 1) === '0') {
            $clean_phone = '62' . substr($clean_phone, 1);
        }
        $wa_quick = "https://wa.me/" . $clean_phone . "?text=" . urlencode("Halo Bapak/Ibu " . $inq['name'] . ", salam dari PT. Kreatif Global Solusindo terkait penawaran " . $inq['service_name']);
    ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col md:flex-row justify-between gap-5 hover:border-slate-300 transition">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-3">
                    <span class="font-extrabold text-sm text-slate-900"><?= e($inq['name']) ?></span>
                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded"><?= e($inq['company'] ?: 'Perorangan / Instansi') ?></span>
                    <span class="text-[11px] text-slate-400 font-mono"><?= date('d M Y, H:i', strtotime($inq['created_at'])) ?></span>
                </div>
                <div class="text-xs text-slate-600 flex flex-wrap gap-4">
                    <span>📧 <?= e($inq['email']) ?></span>
                    <span>📞 <?= e($inq['phone']) ?></span>
                    <span>📍 Lokasi: <strong><?= e($inq['location']) ?></strong></span>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-100 text-xs mt-2">
                    <div class="font-bold text-blue-800 mb-1">Kebutuhan: <?= e($inq['service_name']) ?></div>
                    <p class="text-slate-700 line-clamp-2"><?= nl2br(e($inq['message'])) ?></p>
                </div>
            </div>

            <div class="flex md:flex-col items-center justify-between md:justify-center gap-2.5 shrink-0 md:border-l md:pl-5 border-slate-100">
                <a href="detail-inquiry.php?id=<?= $inq['id'] ?>" class="w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-xs transition">
                    Lihat Detail & Tindak Lanjut &rarr;
                </a>

                <div class="flex items-center gap-2 w-full">
                    <a href="<?= $wa_quick ?>" target="_blank" class="flex-1 text-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold transition">
                        💬 WA
                    </a>

                    <form method="POST" onsubmit="return confirm('Hapus permintaan penawaran ini secara permanen?');" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                        <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-lg text-xs">
                            Hapus
                        </button>
                    </form>
                </div>

                <form method="POST" class="w-full">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                    <select name="status" onchange="this.form.submit()" class="w-full text-[11px] font-bold px-2 py-1 rounded-md border cursor-pointer <?= $inq['status'] === 'Baru' ? 'bg-amber-50 text-amber-800 border-amber-300' : ($inq['status'] === 'Diproses' ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-emerald-50 text-emerald-800 border-emerald-300') ?>">
                        <option value="Baru" <?= $inq['status'] === 'Baru' ? 'selected' : '' ?>>Status: Baru</option>
                        <option value="Diproses" <?= $inq['status'] === 'Diproses' ? 'selected' : '' ?>>Status: Diproses</option>
                        <option value="Selesai" <?= $inq['status'] === 'Selesai' ? 'selected' : '' ?>>Status: Selesai</option>
                    </select>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
