<?php
/**
 * Render Meta Tag SEO Global, Verifikasi Kepemilikan Website (Google Search Console,
 * TXT Record, Bing, Yandex, Meta Domain), OpenGraph, Twitter Card, Geo-Tagging,
 * dan Schema.org JSON-LD.
 * Seluruh output di-escape dengan e() / JSON_HEX_* untuk mencegah Stored XSS.
 */
function render_seo_meta(PDO $pdo, string $page_title = '', string $page_desc = '', string $og_type_or_image = '', string $og_image = ''): void {
    $company_name  = get_setting($pdo, 'company_name', 'PT. Kreatif Global Solusindo');
    $default_title = get_setting($pdo, 'default_meta_title', $company_name . ' - System Integrator, Security & ISP KGSNet');
    $default_desc  = get_setting($pdo, 'default_meta_desc', 'Solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, dan Internet Service Provider (KGSNet) di Palembang.');
    $keywords      = get_setting($pdo, 'meta_keywords', 'ISP Palembang, KGSNet, Kontraktor Tower, Penangkal Petir, CCTV, Fiber Optik, PT Kreatif Global Solusindo');
    $robots        = get_setting($pdo, 'seo_robots', 'index, follow');
    $googlebot     = get_setting($pdo, 'seo_googlebot', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');
    $separator     = get_setting($pdo, 'seo_title_separator', '|');
    $author        = get_setting($pdo, 'seo_author', 'PT. Kreatif Global Solusindo (KGSNet)');

    // Token verifikasi kepemilikan website
    $google_verif    = trim(get_setting($pdo, 'seo_google_verification', ''));
    $sc_txt_record   = trim(get_setting($pdo, 'seo_search_console_txt', ''));
    $bing_verif      = trim(get_setting($pdo, 'seo_bing_verification', ''));
    $yandex_verif    = trim(get_setting($pdo, 'seo_yandex_verification', ''));
    $pinterest_verif = trim(get_setting($pdo, 'seo_pinterest_verification', ''));
    $fb_domain_verif = trim(get_setting($pdo, 'seo_fb_domain_verification', ''));

    // Jika admin mengisi TXT Search Console (misal: google-site-verification=XXXX) namun belum mengisi token HTML
    if ($google_verif === '' && $sc_txt_record !== '') {
        if (strpos($sc_txt_record, 'google-site-verification=') === 0) {
            $google_verif = trim(substr($sc_txt_record, strlen('google-site-verification=')));
        } else {
            $google_verif = $sc_txt_record;
        }
    }

    $final_title = $page_title !== '' ? ($page_title . ' ' . $separator . ' ' . $company_name) : $default_title;
    $final_desc  = $page_desc !== '' ? $page_desc : $default_desc;

    $canonical_base = rtrim(get_setting($pdo, 'seo_canonical_base', ''), '/');
    if ($canonical_base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $canonical_base = $scheme . '://' . $host;
    }
    $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $current_url = $canonical_base . $uri;

    $allowed_og_types = ['website', 'article', 'business.business', 'profile'];
    if (in_array($og_type_or_image, $allowed_og_types, true)) {
        $og_type = $og_type_or_image;
    } else {
        $og_type = get_setting($pdo, 'seo_og_type', 'website');
        if ($og_image === '' && $og_type_or_image !== '') {
            $og_image = $og_type_or_image;
        }
    }
    $og_site_name = get_setting($pdo, 'seo_og_site_name', $company_name);
    $saved_og_img = get_setting($pdo, 'seo_og_image', '');
    $final_og_img = $og_image !== '' ? $og_image : $saved_og_img;
    if ($final_og_img !== '' && !preg_match('/^https?:\/\//i', $final_og_img)) {
        $final_og_img = $canonical_base . '/' . ltrim($final_og_img, '/');
    }

    $twitter_card = get_setting($pdo, 'seo_twitter_card', 'summary_large_image');
    $twitter_site = get_setting($pdo, 'seo_twitter_site', '');

    $geo_region    = get_setting($pdo, 'seo_geo_region', 'ID-SS');
    $geo_placename = get_setting($pdo, 'seo_geo_placename', 'Palembang, Sumatera Selatan');
    $geo_position  = get_setting($pdo, 'seo_geo_position', '-2.9718;104.7644');

    echo '<title>' . e($final_title) . "</title>\n";
    echo '    <meta name="description" content="' . e($final_desc) . '">' . "\n";
    echo '    <meta name="keywords" content="' . e($keywords) . '">' . "\n";
    echo '    <meta name="author" content="' . e($author) . '">' . "\n";
    echo '    <meta name="robots" content="' . e($robots) . '">' . "\n";
    if ($googlebot !== '') {
        echo '    <meta name="googlebot" content="' . e($googlebot) . '">' . "\n";
    }
    echo '    <link rel="canonical" href="' . e($current_url) . '">' . "\n";

    // Website Ownership & Search Console Verification Tags
    if ($google_verif !== '') {
        echo '    <meta name="google-site-verification" content="' . e($google_verif) . '">' . "\n";
    }
    if ($sc_txt_record !== '') {
        echo '    <meta name="search-console-txt" content="' . e($sc_txt_record) . '">' . "\n";
    }
    if ($bing_verif !== '') {
        echo '    <meta name="msvalidate.01" content="' . e($bing_verif) . '">' . "\n";
    }
    if ($yandex_verif !== '') {
        echo '    <meta name="yandex-verification" content="' . e($yandex_verif) . '">' . "\n";
    }
    if ($pinterest_verif !== '') {
        echo '    <meta name="p:domain_verify" content="' . e($pinterest_verif) . '">' . "\n";
    }
    if ($fb_domain_verif !== '') {
        echo '    <meta name="facebook-domain-verification" content="' . e($fb_domain_verif) . '">' . "\n";
    }

    // Geo-Tagging Metadata
    if ($geo_region !== '') {
        echo '    <meta name="geo.region" content="' . e($geo_region) . '">' . "\n";
    }
    if ($geo_placename !== '') {
        echo '    <meta name="geo.placename" content="' . e($geo_placename) . '">' . "\n";
    }
    if ($geo_position !== '') {
        echo '    <meta name="geo.position" content="' . e($geo_position) . '">' . "\n";
        echo '    <meta name="ICBM" content="' . e(str_replace(';', ', ', $geo_position)) . '">' . "\n";
    }

    // OpenGraph Meta Tags
    echo '    <meta property="og:type" content="' . e($og_type) . '">' . "\n";
    echo '    <meta property="og:site_name" content="' . e($og_site_name) . '">' . "\n";
    echo '    <meta property="og:title" content="' . e($final_title) . '">' . "\n";
    echo '    <meta property="og:description" content="' . e($final_desc) . '">' . "\n";
    echo '    <meta property="og:url" content="' . e($current_url) . '">' . "\n";
    echo '    <meta property="og:locale" content="id_ID">' . "\n";
    if ($final_og_img !== '') {
        echo '    <meta property="og:image" content="' . e($final_og_img) . '">' . "\n";
    }

    // Twitter Card Meta Tags
    echo '    <meta name="twitter:card" content="' . e($twitter_card) . '">' . "\n";
    echo '    <meta name="twitter:title" content="' . e($final_title) . '">' . "\n";
    echo '    <meta name="twitter:description" content="' . e($final_desc) . '">' . "\n";
    if ($twitter_site !== '') {
        echo '    <meta name="twitter:site" content="' . e($twitter_site) . '">' . "\n";
    }
    if ($final_og_img !== '') {
        echo '    <meta name="twitter:image" content="' . e($final_og_img) . '">' . "\n";
    }

    // Schema.org JSON-LD Structured Data
    $schema_type = get_setting($pdo, 'seo_schema_type', 'Organization');
    $founding_yr = get_setting($pdo, 'seo_schema_founding_year', '1997');
    $price_range = get_setting($pdo, 'seo_schema_price_range', '$$$');
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $schema_type,
        'name' => $company_name,
        'alternateName' => get_setting($pdo, 'short_name', 'KGSNET'),
        'description' => $final_desc,
        'foundingDate' => $founding_yr,
        'priceRange' => $price_range,
        'url' => $canonical_base,
        'telephone' => get_setting($pdo, 'phone_palembang', '(0711) 316088'),
        'email' => get_setting($pdo, 'email', 'sales@creative.co.id'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Palembang'),
            'addressLocality' => 'Palembang',
            'addressRegion' => 'Sumatera Selatan',
            'addressCountry' => 'ID'
        ]
    ];
    echo '    <script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "</script>\n";

    // Google Analytics 4 (Hanya dirender apabila format ID valid)
    $ga_id = trim(get_setting($pdo, 'seo_google_analytics_id', ''));
    if ($ga_id !== '' && preg_match('/^(G|UA|AW)-[A-Z0-9\-]{4,25}$/i', $ga_id)) {
        echo '    <script async src="https://www.googletagmanager.com/gtag/js?id=' . rawurlencode($ga_id) . '"></script>' . "\n";
        echo "    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . e($ga_id) . "');</script>\n";
    }
}
