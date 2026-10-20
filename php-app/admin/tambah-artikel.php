<?php
$admin_page_title = 'Tambah Artikel Baru';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

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
            $featured_image = handle_image_upload('featured_image_file', 'uploads/', $pdo);
            if (!$featured_image && !empty($_POST['featured_image_url'])) {
                $featured_image = trim($_POST['featured_image_url']);
            }

            $slug = create_slug($title);
            $stmt_check = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $stmt_check->execute([$slug]);
            if ($stmt_check->fetch()) {
                $slug .= '-' . time();
            }

            $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt_insert = $pdo->prepare("INSERT INTO posts (category_id, title, slug, summary, content, featured_image, alt_image, labels, author, is_featured, status, meta_title, meta_description, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->execute([
                $category_id,
                $title,
                $slug,
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
                $published_at
            ]);

            log_audit($pdo, 'Tambah Artikel', "Membuat artikel baru: $title");
            header("Location: blog.php?msg=created");
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
            <h1 class="text-xl font-bold text-slate-900">Tambah Artikel / Berita Baru</h1>
            <p class="text-xs text-slate-500">Buat artikel wawasan teknologi dilengkapi gambar sampul dan label topik.</p>
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
            <input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" required placeholder="Contoh: Standar Nilai Tahanan Pembumian Grounding di Bawah 2 Ohm" class="w-full text-sm font-semibold p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-1">Kategori Artikel *</label>
                <select name="category_id" required class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Penulis (Author)</label>
                <input type="text" name="author" value="<?= e($_POST['author'] ?? 'Tim Teknis PT. KGS') ?>" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block font-semibold mb-1">Status Publikasi</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                    <option value="published">Langsung Terbitkan (Published)</option>
                    <option value="draft">Simpan Sebagai Draft</option>
                </select>
            </div>
        </div>

        <!-- Upload Gambar Sampul & Label Topik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="space-y-2">
                <label class="block font-bold text-slate-800">Upload Gambar Sampul Artikel (JPG, PNG, WEBP maks. 2 MB)</label>
                <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                <input type="text" name="alt_image" value="<?= e($_POST['alt_image'] ?? '') ?>" placeholder="Teks alternatif (Alt Image SEO)" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="space-y-2">
                <label class="block font-bold text-slate-800">Label / Tag Topik Artikel (Pisahkan dengan koma)</label>
                <input type="text" name="labels" value="<?= e($_POST['labels'] ?? 'Fiber Optik, Infrastruktur, KGSNet') ?>" placeholder="Contoh: Fiber Optik, Grounding, CCTV, KGSNet" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                <p class="text-[11px] text-slate-500">Label ini akan tampil sebagai badge topik di kartu artikel dan halaman baca.</p>
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1">Ringkasan Artikel (Excerpt) *</label>
            <textarea name="summary" rows="3" required placeholder="Tuliskan 1-2 paragraf ringkasan penting untuk cuplikan kartu dan meta description..." class="w-full border border-slate-300 rounded-lg p-3"><?= e($_POST['summary'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Konten Lengkap Artikel (Mendukung tag HTML &lt;p&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;) *</label>
            <textarea name="content" rows="12" required placeholder="Tulis artikel lengkap di sini..." class="w-full border border-slate-300 rounded-lg p-3 font-mono text-xs leading-relaxed"><?= e($_POST['content'] ?? '') ?></textarea>
        </div>

        <!-- SEO Metadata Card -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <div class="font-bold text-slate-800 text-xs">Optimasi Mesin Pencari (SEO)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-600 mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" placeholder="Kosongkan untuk memakai judul artikel" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
                <div>
                    <label class="block text-slate-600 mb-1">Custom Meta Description</label>
                    <input type="text" name="meta_description" placeholder="Kosongkan untuk memakai ringkasan artikel" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" class="rounded text-blue-600">
                <span class="font-semibold text-slate-800">Tandai sebagai Artikel Unggulan (Featured Post)</span>
            </label>

            <div class="flex gap-3">
                <a href="blog.php" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
                    Simpan & Publikasikan
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
