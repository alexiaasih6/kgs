<?php
require_once __DIR__ . '/config/database.php';

ensure_pages_complete($pdo);

$slug = trim($_GET['slug'] ?? 'profil-perusahaan');
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    header("Location: index.php");
    exit;
}

$page_title = !empty($page['meta_title']) ? $page['meta_title'] : $page['title'];
$meta_description = !empty($page['meta_description']) ? $page['meta_description'] : ($page['summary'] ?? '');
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gradient-to-b from-slate-900 to-blue-950 text-white py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
        <span class="px-3 py-1 bg-blue-500/30 text-blue-300 text-xs font-bold rounded-full border border-blue-400/30 uppercase tracking-wider">
            Informasi Resmi Perusahaan
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight"><?= e($page['title']) ?></h1>
        <?php if (!empty($page['summary'])): ?>
            <p class="text-slate-300 text-sm leading-relaxed"><?= e($page['summary']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 shadow-xs space-y-6 text-slate-700 leading-relaxed text-sm">
        <div class="prose max-w-none space-y-4">
            <?= sanitize_rich_html($page['content']) ?>
        </div>

        <div class="pt-8 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>Terakhir diperbarui: <?= !empty($page['updated_at']) ? date('d F Y', strtotime($page['updated_at'])) : date('d F Y') ?></span>
            <a href="kontak.php#konsultasi" class="text-blue-600 font-bold hover:underline">Hubungi Tim Kami &rarr;</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
