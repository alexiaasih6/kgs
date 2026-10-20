<?php
/**
 * Dynamic XML Sitemap Generator - PT. Kreatif Global Solusindo (KGS)
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = defined('BASE_URL') ? BASE_URL : 'https://www.kgs.net.id';
$today = date('Y-m-d');

$static_routes = [
    'index.php'             => '1.0',
    'tentang.php'           => '0.9',
    'layanan.php'           => '0.9',
    'harga.php'             => '0.9',
    'faq.php'               => '0.8',
    'portofolio.php'        => '0.8',
    'klien.php'             => '0.8',
    'kontak.php'            => '0.9',
    'blog.php'              => '0.8',
    'kebijakan-privasi.php' => '0.5'
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static_routes as $route => $priority): ?>
    <url>
        <loc><?= e($base . '/' . $route) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority><?= $priority ?></priority>
    </url>
<?php endforeach; ?>
<?php
try {
    $stmt_srv = $pdo->query("SELECT slug, updated_at FROM services WHERE is_published = 1");
    while ($srv = $stmt_srv->fetch()):
        $lastmod = !empty($srv['updated_at']) ? date('Y-m-d', strtotime($srv['updated_at'])) : $today;
?>
    <url>
        <loc><?= e($base . '/detail-layanan.php?slug=' . urlencode($srv['slug'])) ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php
    endwhile;

    $stmt_post = $pdo->query("SELECT slug, updated_at FROM posts WHERE status = 'published'");
    while ($post = $stmt_post->fetch()):
        $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : $today;
?>
    <url>
        <loc><?= e($base . '/detail-blog.php?slug=' . urlencode($post['slug'])) ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php
    endwhile;
} catch (Exception $e) {
    // Abaikan jika tabel belum tersedia
}
?>
</urlset>
