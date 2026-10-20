<?php
$admin_page_title = 'Media Manager & Upload Gambar';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        try {
            $uploaded_path = handle_image_upload('media_file', 'uploads/', $pdo);
            if ($uploaded_path) {
                log_audit($pdo, 'Upload Media', "Mengunggah berkas media baru: $uploaded_path");
                $msg = "Berkas berhasil diunggah ke: $uploaded_path";
            } else {
                $error = 'Pilih berkas gambar terlebih dahulu.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
            $stmt->execute([$id]);
            $media_row = $stmt->fetch();
            if ($media_row) {
                $full_path = __DIR__ . '/../' . ltrim($media_row['filepath'], '/');
                if (is_file($full_path) && strpos(realpath($full_path) ?: '', realpath(__DIR__ . '/../uploads') ?: '') === 0) {
                    @unlink($full_path);
                }
                $del = $pdo->prepare("DELETE FROM media WHERE id = ?");
                $del->execute([$id]);
                log_audit($pdo, 'Hapus Media', "Menghapus berkas media: " . $media_row['filepath']);
                $msg = 'Berkas media berhasil dihapus.';
            }
        }
    }
}

$media_list = [];
try {
    $media_list = $pdo->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $media_list = [];
}
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Media Manager & Pustaka Gambar</h1>
        <p class="text-xs text-slate-500">Unggah gambar (JPG, PNG, WEBP, SVG, ICO maks. 2 MB) untuk digunakan pada halaman, artikel, layanan, atau portofolio.</p>
    </div>
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

<!-- Upload Card -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
    <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-end gap-4 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <div class="flex-1 w-full">
            <label class="block font-bold text-slate-800 mb-1.5">Pilih Berkas Gambar Baru (Maks. 2 MB — JPG, PNG, WEBP, SVG, ICO)</label>
            <input type="file" name="media_file" required accept=".jpg,.jpeg,.png,.webp,.svg,.ico" class="w-full border border-slate-300 rounded-xl p-2 bg-slate-50 text-slate-700">
        </div>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer shrink-0">
            Unggah ke Server
        </button>
    </form>
</div>

<!-- Daftar Gambar Terunggah -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
    <h2 class="font-bold text-slate-900 text-sm mb-4 pb-2 border-b">Pustaka Berkas Terunggah (<?= count($media_list) ?> File)</h2>

    <?php if (empty($media_list)): ?>
        <div class="py-12 text-center text-xs text-slate-400">
            Belum ada berkas gambar yang diunggah melalui Media Manager.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($media_list as $m): ?>
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col justify-between">
                    <div class="h-36 bg-white flex items-center justify-center p-2 border-b border-slate-200 overflow-hidden">
                        <img src="../<?= e($m['filepath']) ?>" alt="<?= e($m['original_name']) ?>" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="p-3 space-y-2 text-xs">
                        <div class="font-bold text-slate-800 truncate" title="<?= e($m['original_name']) ?>">
                            <?= e($m['original_name']) ?>
                        </div>
                        <input type="text" readonly value="<?= e($m['filepath']) ?>" class="w-full bg-white border border-slate-200 rounded px-2 py-1 font-mono text-[10px] text-slate-600">
                        <div class="flex items-center justify-between gap-2 pt-1">
                            <button type="button" data-copy-text="<?= e($m['filepath']) ?>" class="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-semibold text-[11px] cursor-pointer">
                                Salin Path
                            </button>
                            <form method="POST" onsubmit="return confirm('Hapus berkas gambar ini dari server?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-[11px] cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
