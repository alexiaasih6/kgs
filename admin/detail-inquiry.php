<?php
$admin_page_title = 'Detail Permintaan Penawaran';
require_once __DIR__ . '/layout-header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->execute([$id]);
$inquiry = $stmt->fetch();

if (!$inquiry) {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs'>Permintaan penawaran tidak ditemukan. <a href='inquiries.php' class='underline font-bold'>Kembali</a></div>";
    require_once __DIR__ . '/layout-footer.php';
    exit;
}

$msg = '';

// Tambah catatan tindak lanjut atau perbarui status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'add_note') {
        $note = trim($_POST['note'] ?? '');
        if (!empty($note)) {
            $stmt_note = $pdo->prepare("INSERT INTO lead_notes (inquiry_id, note, created_at) VALUES (?, ?, NOW())");
            $stmt_note->execute([$id, $note]);
            log_audit($pdo, 'Catatan Leads', "Menambah catatan tindak lanjut untuk lead #$id: " . substr($note, 0, 50));
            $msg = 'Catatan tindak lanjut berhasil ditambahkan.';
        }
    } elseif ($action === 'update_status') {
        $status = $_POST['status'] ?? 'Baru';
        $stmt_status = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt_status->execute([$status, $id]);
        $inquiry['status'] = $status;
        log_audit($pdo, 'Status Leads', "Memperbarui status lead #$id menjadi: $status");
        $msg = "Status penawaran berhasil diubah menjadi: $status.";
    }
}

// Ambil riwayat catatan tindak lanjut
$notes = $pdo->prepare("SELECT * FROM lead_notes WHERE inquiry_id = ? ORDER BY created_at DESC");
$notes->execute([$id]);
$lead_notes = $notes->fetchAll();

// Siapkan tautan WhatsApp
$clean_phone = preg_replace('/[^0-9]/', '', $inquiry['phone']);
if (substr($clean_phone, 0, 1) === '0') {
    $clean_phone = '62' . substr($clean_phone, 1);
}
$wa_text = urlencode("Halo Bapak/Ibu " . $inquiry['name'] . " (" . $inquiry['company'] . "),\n\nTerima kasih telah menghubungi PT. KREATIF GLOBAL SOLUSINDO (KGS). Kami telah menerima permintaan informasi Anda mengenai layanan: *" . $inquiry['service_name'] . "* untuk lokasi *" . $inquiry['location'] . "*.\n\nApakah ada waktu luang hari ini agar tim engineer kami dapat berdiskusi lebih lanjut terkait kebutuhan teknis Anda?\n\nSalam,\nTim Sales & Engineering PT. KGS\nPalembang - www.kgs.net.id");
$wa_link = "https://wa.me/" . $clean_phone . "?text=" . $wa_text;

// Siapkan tautan Email
$email_subject = urlencode("Tindak Lanjut Penawaran Solusi: " . $inquiry['service_name'] . " - PT. Kreatif Global Solusindo");
$email_body = urlencode("Yth. Bapak/Ibu " . $inquiry['name'] . ",\n\nTerima kasih atas kepercayaan Anda menghubungi PT. Kreatif Global Solusindo.\n\nBerikut kami lampirkan informasi dan penawaran teknis terkait solusi " . $inquiry['service_name'] . ".");
$email_link = "mailto:" . urlencode($inquiry['email']) . "?subject=" . $email_subject . "&body=" . $email_body;
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Detail Permintaan Penawaran #<?= $inquiry['id'] ?></h1>
            <p class="text-xs text-slate-500">Masuk pada: <?= date('d F Y, H:i', strtotime($inquiry['created_at'])) ?> WIB</p>
        </div>
        <a href="inquiries.php" class="text-xs text-slate-500 hover:text-slate-800">&larr; Kembali ke Daftar Leads</a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Client Info Card & Quick Actions -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">
                    <?= e($inquiry['company'] ?: 'Perorangan / Instansi') ?>
                </span>
                <h2 class="text-2xl font-black text-slate-900 mt-2"><?= e($inquiry['name']) ?></h2>
                <p class="text-xs text-slate-500 mt-0.5">📍 Lokasi Proyek: <strong><?= e($inquiry['location']) ?></strong></p>
            </div>

            <!-- Status Changer -->
            <form method="POST" class="flex items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <span class="text-xs font-bold text-slate-600">Status:</span>
                <select name="status" onchange="this.form.submit()" class="text-xs font-bold px-3 py-2 rounded-lg border cursor-pointer <?= $inquiry['status'] === 'Baru' ? 'bg-amber-100 text-amber-900 border-amber-300' : ($inquiry['status'] === 'Diproses' ? 'bg-blue-100 text-blue-900 border-blue-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300') ?>">
                    <option value="Baru" <?= $inquiry['status'] === 'Baru' ? 'selected' : '' ?>>Baru</option>
                    <option value="Diproses" <?= $inquiry['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses / Dihubungi</option>
                    <option value="Selesai" <?= $inquiry['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai Deal</option>
                </select>
            </form>
        </div>

        <!-- Quick Communication Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="<?= $wa_link ?>" target="_blank" class="flex items-center justify-center gap-2.5 p-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                <span>💬 Balas Langsung via WhatsApp</span>
                <span class="font-normal text-emerald-100">(<?= e($inquiry['phone']) ?>)</span>
            </a>
            <a href="<?= $email_link ?>" class="flex items-center justify-center gap-2.5 p-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                <span>📧 Kirim Penawaran via Email</span>
                <span class="font-normal text-blue-100">(<?= e($inquiry['email']) ?>)</span>
            </a>
        </div>

        <!-- Project & Requirement In-Depth -->
        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 space-y-3 text-xs">
            <div class="flex items-center justify-between">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Layanan yang Dikonsultasikan:</span>
                <span class="font-extrabold text-blue-700 bg-white px-3 py-1 rounded-md border border-slate-200"><?= e($inquiry['service_name']) ?></span>
            </div>
            <div class="pt-3 border-t border-slate-200">
                <span class="font-bold text-slate-700 block mb-1">Rincian Kebutuhan & Pesan Klien:</span>
                <p class="text-slate-800 leading-relaxed bg-white p-4 rounded-lg border border-slate-200 text-xs whitespace-pre-line"><?= e($inquiry['message']) ?></p>
            </div>
        </div>
    </div>

    <!-- Lead Follow-up History (Catatan Tindak Lanjut) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-3">Riwayat Tindak Lanjut & Catatan Internal Admin</h3>

        <!-- Form Tambah Catatan -->
        <form method="POST" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_note">
            <label class="block text-xs font-semibold text-slate-700">Tambah Catatan Tindak Lanjut Baru:</label>
            <textarea name="note" rows="3" required placeholder="Contoh: Sudah ditelepon pada 17 Okt pk 10:00, PIC meminta penawaran dikirim ke email finance@... dengan breakdown biaya instalasi fiber optic 500m." class="w-full text-xs p-3 border rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm">
                + Simpan Catatan Tindak Lanjut
            </button>
        </form>

        <!-- List Catatan -->
        <div class="space-y-3 pt-3">
            <?php if (empty($lead_notes)): ?>
                <div class="text-center py-6 text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed">
                    Belum ada catatan tindak lanjut. Tambahkan catatan pertama di atas untuk melacak proses komunikasi dengan klien.
                </div>
            <?php else: foreach ($lead_notes as $n): ?>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span class="font-semibold text-slate-600">Catatan Internal</span>
                        <span><?= date('d M Y H:i', strtotime($n['created_at'])) ?> WIB</span>
                    </div>
                    <p class="text-slate-800 whitespace-pre-line"><?= e($n['note']) ?></p>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
