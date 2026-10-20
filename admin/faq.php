<?php
$admin_page_title = 'Kelola Tanya Jawab (FAQ)';
require_once __DIR__ . '/layout-header.php';

$msg = '';
$error = '';
$edit_id = (int)($_GET['edit_id'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt_edit = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $edit_data = $stmt_edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id         = (int)($_POST['id'] ?? 0);
        $category   = trim($_POST['category'] ?? 'Informasi Umum');
        $question   = trim($_POST['question'] ?? '');
        $answer     = trim($_POST['answer'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        if (empty($question) || empty($answer)) {
            $error = 'Pertanyaan dan jawaban wajib diisi.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO faqs (category, question, answer, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$category, $question, $answer, $sort_order, $is_active]);
                log_audit($pdo, 'Tambah FAQ', "Menambahkan FAQ: $question");
                $msg = 'Item FAQ baru berhasil ditambahkan!';
            } else {
                $stmt = $pdo->prepare("UPDATE faqs SET category = ?, question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$category, $question, $answer, $sort_order, $is_active, $id]);
                log_audit($pdo, 'Edit FAQ', "Memperbarui FAQ ID $id: $question");
                $msg = 'Item FAQ berhasil diperbarui!';
                $edit_data = null;
                $edit_id = 0;
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            log_audit($pdo, 'Hapus FAQ', "Menghapus FAQ ID: $id");
            $msg = 'Item FAQ berhasil dihapus.';
        }
    }
}

$faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Tanya Jawab (FAQ)</h1>
        <p class="text-xs text-slate-500">Tambah, perbarui, atau hapus daftar pertanyaan teknis yang tampil pada halaman FAQ publik.</p>
    </div>
    <a href="../faq.php" target="_blank" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
        Lihat Halaman FAQ &nearr;
    </a>
</div>

<?php if (!empty($msg)): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-3.5 rounded-xl font-medium">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Tambah / Edit FAQ -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit">
        <h2 class="font-bold text-slate-900 text-sm mb-4 pb-2 border-b">
            <?= $edit_data ? 'Edit Pertanyaan FAQ' : 'Tambah FAQ Baru' ?>
        </h2>
        <form method="POST" class="space-y-3.5 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Topik *</label>
                    <input type="text" name="category" required value="<?= e($edit_data['category'] ?? 'Layanan & Teknis') ?>" placeholder="Contoh: Legalitas & Lisensi" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="<?= (int)($edit_data['sort_order'] ?? 1) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Pertanyaan *</label>
                <input type="text" name="question" required value="<?= e($edit_data['question'] ?? '') ?>" placeholder="Masukkan kalimat pertanyaan..." class="w-full border border-slate-300 rounded-lg p-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Jawaban Lengkap *</label>
                <textarea name="answer" rows="5" required placeholder="Tuliskan jawaban teknis secara jelas..." class="w-full border border-slate-300 rounded-lg p-2"><?= e($edit_data['answer'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_data['is_active']) || $edit_data['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="font-semibold text-slate-700">Aktif Tampil di Website</span>
                </label>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition cursor-pointer">
                    <?= $edit_data ? 'Simpan Perubahan' : 'Tambah FAQ' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="faq.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Daftar FAQ -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs h-fit">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Daftar Pertanyaan FAQ (<?= count($faqs) ?>)</h3>
        </div>
        <div class="divide-y divide-slate-200">
            <?php foreach ($faqs as $f): ?>
            <div class="p-4 hover:bg-slate-50 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-bold rounded text-[10px]"><?= e($f['category']) ?></span>
                        <?php if (!$f['is_active']): ?>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm"><?= e($f['question']) ?></h4>
                    <p class="text-xs text-slate-600 leading-relaxed"><?= e($f['answer']) ?></p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 text-xs">
                    <a href="faq.php?edit_id=<?= (int)$f['id'] ?>" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-semibold">
                        Edit
                    </a>
                    <form method="POST" class="inline" onsubmit="return confirm('Hapus item FAQ ini?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold cursor-pointer">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
