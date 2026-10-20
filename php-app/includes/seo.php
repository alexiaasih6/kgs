<?php
/**
 * Generator Meta SEO, OpenGraph, Twitter Card & Schema.org JSON-LD
 * PT. Kreatif Global Solusindo (KGS)
 */

function render_seo_meta(PDO $pdo, string $page_title = '', string $page_desc = '', string $og_type = 'website', string $og_image = ''): void {
    $company_name = get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO');
    $default_title = get_setting($pdo, 'meta_title_default', $company_name . ' - System Integrator, Security Solution & ISP');
    $default_desc = get_setting($pdo, 'meta_desc_default', 'Solusi Jaringan Komputer, Telekomunikasi, CCTV, Penangkal Petir, Tower, dan ISP KGSNet resmi APJII sejak 1997 di Palembang.');
    $robots = get_setting($pdo, 'robots_directive', 'index, follow');

    $final_title = !empty($page_title) ? ($page_title . ' | ' . $company_name) : $default_title;
    $final_desc = !empty($page_desc) ? $page_desc : $default_desc;

    $protocol = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ) ? "https://" : "http://";
    $raw_host = $_SERVER['HTTP_HOST'] ?? 'www.kgs.net.id';
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $raw_host) ?: 'www.kgs.net.id';
    $req_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    $current_url = $protocol . $host . $req_uri;

    $logo_setting = get_setting($pdo, 'company_logo', get_setting($pdo, 'logo', get_setting($pdo, 'logo_main', 'assets/images/logo-kgs.svg')));
    if (!empty($og_image)) {
        $image_url = preg_match('#^https?://#i', $og_image) ? $og_image : ($protocol . $host . '/' . ltrim($og_image, '/'));
    } else {
        $image_url = preg_match('#^https?://#i', $logo_setting) ? $logo_setting : ($protocol . $host . '/' . ltrim($logo_setting, '/'));
    }
    $phone = get_setting($pdo, 'phone_palembang', get_setting($pdo, 'phone_hunting', '0711-316088'));
    $email = get_setting($pdo, 'email', get_setting($pdo, 'email_general', 'info@kgs.net.id'));
    $address = get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
    ?>
    <title><?= e($final_title) ?></title>
    <meta name="description" content="<?= e($final_desc) ?>">
    <meta name="robots" content="<?= e($robots) ?>">
    <link rel="canonical" href="<?= e($current_url) ?>">

    <!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
    <meta property="og:type" content="<?= e($og_type) ?>">
    <meta property="og:title" content="<?= e($final_title) ?>">
    <meta property="og:description" content="<?= e($final_desc) ?>">
    <meta property="og:url" content="<?= e($current_url) ?>">
    <meta property="og:site_name" content="<?= e($company_name) ?>">
    <meta property="og:image" content="<?= e($image_url) ?>">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($final_title) ?>">
    <meta name="twitter:description" content="<?= e($final_desc) ?>">
    <meta name="twitter:image" content="<?= e($image_url) ?>">

    <!-- Schema.org JSON-LD Structured Data (LocalBusiness / IT Service Provider) -->
    <script type="application/ld+json">
    <?= json_encode([
        "@context" => "https://schema.org",
        "@type" => "TelecommunicationsService",
        "name" => $company_name,
        "alternateName" => ["Creative Com", "KGS", "KGSNet"],
        "foundingDate" => "1997",
        "url" => $protocol . $host,
        "telephone" => $phone,
        "email" => $email,
        "description" => $default_desc,
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $address,
            "addressLocality" => "Palembang",
            "addressRegion" => "Sumatera Selatan",
            "postalCode" => "30113",
            "addressCountry" => "ID"
        ],
        "areaServed" => "Indonesia"
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <?php
}
