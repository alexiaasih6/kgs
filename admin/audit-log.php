<?php
$admin_page_title = 'Audit Log Aktivitas';
require_once __DIR__ . '/layout-header.php';

$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM audit_logs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR action LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Log Aktivitas & Jejak Audit Keamanan</h1>
        <p class="text-xs text-slate-500">Mencatat riwayat aktivitas login, penambahan konten, pengeditan, dan penghapusan data.</p>
    </div>
</div>

<!-- Search -->
<div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari aksi, username, atau detail perubahan..." class="flex-1 text-xs px-3.5 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm">
            Cari Log
        </button>
        <?php if (!empty($search)): ?>
            <a href="audit-log.php" class="px-3 py-2 border rounded-lg text-xs text-slate-500 hover:bg-slate-50 flex items-center justify-center">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Log Table -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 uppercase text-[10px] text-slate-500 border-b">
                <tr>
                    <th class="p-3.5 w-16">ID</th>
                    <th class="p-3.5 w-32">Pengguna</th>
                    <th class="p-3.5 w-40">Aktivitas / Event</th>
                    <th class="p-3.5">Detail Rincian</th>
                    <th class="p-3.5 w-32 font-mono">IP Address</th>
                    <th class="p-3.5 w-40 text-right">Waktu (WIB)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">Belum ada riwayat aktivitas yang tercatat.</td>
                    </tr>
                <?php else: foreach ($logs as $l): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3.5 font-mono text-slate-400">#<?= $l['id'] ?></td>
                        <td class="p-3.5 font-bold text-slate-900"><?= e($l['username'] ?? 'Sistem') ?></td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <?= e($l['action']) ?>
                            </span>
                        </td>
                        <td class="p-3.5 text-slate-600 leading-relaxed"><?= e($l['details']) ?></td>
                        <td class="p-3.5 font-mono text-[11px] text-slate-500"><?= e($l['ip_address'] ?? '127.0.0.1') ?></td>
                        <td class="p-3.5 text-right font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            <?= date('d M Y H:i:s', strtotime($l['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
