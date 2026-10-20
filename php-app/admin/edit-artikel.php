<?php
$admin_page_title = 'Edit Artikel';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs'>Artikel tidak ditemukan. <a href='blog.php' class='underline font-bold'>Kembali ke daftar</a></div>";
    require_once __DIR__ . '/layout-footer.php';
    exit;
}

$error = '';
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = clean_plain_text(strip_tags($_POST['title'] ?? ''), 255);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $summary = clean_plain_text(strip_tags($_POST['summary'] ?? ''), 1000);
    $content = sanitize_rich_html($_POST['content'] ?? '');
    $labels = clean_plain_text(strip_tags($_POST['labels'] ?? ''), 255);
    $alt_image = clean_plain_text(strip_tags($_POST['alt_image'] ?? ''), 255);
    $author = clean_plain_text(strip_tags($_POST['author'] ?? 'Tim Teknis PT. KGS'), 100);
    $status = ($_POST['status'] ?? 'published') === 'published' ? 'published' : 'draft';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $meta_title = clean_plain_text(strip_tags($_POST['meta_title'] ?? ''), 255);
    $meta_description = clean_plain_text(strip_tags($_POST['meta_description'] ?? ''), 500);

    if (empty($title) || empty($summary) || empty($content) || $category_id === 0) {
        $error = 'Judul, kategori, ringkasan, dan isi artikel wajib diisi.';
    } else {
        try {
            $featured_image = $post['featured_image'] ?? null;
            $uploaded = handle_image_upload('featured_image_file', 'uploads/', $pdo);
            if ($uploaded) {
                $featured_image = $uploaded;
            } elseif (!empty($_POST['remove_image'])) {
                $featured_image = null;
            }

            $stmt_update = $pdo->prepare("UPDATE posts SET category_id = ?, title = ?, summary = ?, content = ?, featured_image = ?, alt_image = ?, labels = ?, author = ?, is_featured = ?, status = ?, meta_title = ?, meta_description = ? WHERE id = ?");
            $stmt_update->execute([
                $category_id,
                $title,
                $summary,
                $content,
                $featured_image,
                $alt_image ?: $title,
                $labels,
                $author,
                $is_featured,
                $status,
                $meta_title ?: $title,
                $meta_description ?: $summary,
                $id
            ]);

            log_audit($pdo, 'Edit Artikel', "Memperbarui artikel ID $id: $title");
            header("Location: blog.php?msg=updated");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Edit Artikel: <?= e($post['title']) ?></h1>
            <p class="text-xs text-slate-500">Perbarui konten artikel, gambar sampul, label topik, atau informasi optimasi SEO.</p>
        </div>
        <a href="blog.php" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">&larr; Kembali ke Daftar</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3.5 rounded-xl font-medium">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5 text-xs">
        <?= csrf_field() ?>

        <div class="space-y-1">
            <label class="block font-bold text-slate-700">Judul Artikel *</label>
            <input type="text" name="title" value="<?= e($post['title']) ?>" required class="w-full text-sm font-semibold p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-1">Kategori Artikel *</label>
                <select name="category_id" required class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Penulis (Author)</label>
                <input type="text" name="author" value="<?= e($post['author']) ?>" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block font-semibold mb-1">Status Publikasi</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Terbitkan (Published)</option>
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Simpan Sebagai Draft</option>
                </select>
            </div>
        </div>

        <!-- Upload Gambar Sampul & Label Topik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="space-y-2">
                <label class="block font-bold text-slate-800">Gambar Sampul Artikel (JPG, PNG, WEBP maks. 2 MB)</label>
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="flex items-center gap-3 mb-2">
                        <img src="../<?= e($post['featured_image']) ?>" alt="Preview" class="w-16 h-12 object-cover rounded border border-slate-300">
                        <label class="inline-flex items-center gap-1.5 text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1">
                            <span>Hapus gambar saat ini</span>
                        </label>
                    </div>
                <?php endif; ?>
                <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                <input type="text" name="alt_image" value="<?= e($post['alt_image'] ?? '') ?>" placeholder="Teks alternatif (Alt Image SEO)" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="space-y-2">
                <label class="block font-bold text-slate-800">Label / Tag Topik Artikel (Pisahkan dengan koma)</label>
                <input type="text" name="labels" value="<?= e($post['labels'] ?? '') ?>" placeholder="Contoh: Fiber Optik, Grounding, CCTV, KGSNet" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                <p class="text-[11px] text-slate-500">Label ini akan tampil sebagai badge topik di kartu artikel dan halaman baca.</p>
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1">Ringkasan Artikel (Excerpt) *</label>
            <textarea name="summary" rows="3" required class="w-full border border-slate-300 rounded-lg p-3"><?= e($post['summary']) ?></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Konten Lengkap Artikel (Format HTML didukung) *</label>
            <textarea name="content" rows="14" required class="w-full border border-slate-300 rounded-lg p-3 font-mono text-xs leading-relaxed"><?= e($post['content']) ?></textarea>
        </div>

        <!-- SEO Metadata Card -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <div class="font-bold text-slate-800 text-xs">Optimasi Mesin Pencari (SEO)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-600 mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" value="<?= e($post['meta_title']) ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
                <div>
                    <label class="block text-slate-600 mb-1">Custom Meta Description</label>
                    <input type="text" name="meta_description" value="<?= e($post['meta_description']) ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" <?= $post['is_featured'] ? 'checked' : '' ?> class="rounded text-blue-600">
                <span class="font-semibold text-slate-800">Tandai sebagai Artikel Unggulan (Featured Post)</span>
            </label>

            <div class="flex gap-3">
                <a href="blog.php" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
