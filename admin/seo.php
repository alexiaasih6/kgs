<?php
$admin_page_title = 'Pengaturan Global SEO, Search Console & Metadata';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

$msg = '';
$error_msg = '';

/**
 * Ekstrak kode verifikasi bersih apabila admin menempelkan tag <meta name="google-site-verification" content="..."> penuh
 * maupun kode token langsung.
 */
function extract_verification_token(string $raw): string {
    $val = trim($raw);
    if ($val === '') {
        return '';
    }
    if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $val, $m)) {
        $val = trim($m[1]);
    }
    // Hanya izinkan karakter token verifikasi standar yang aman
    $val = preg_replace('/[^a-zA-Z0-9_\-\.=+:\/]/', '', $val) ?? '';
    return substr($val, 0, 180);
}

/**
 * Sanitasi nama file verifikasi kepemilikan situs secara ketat:
 * Hanya mengizinkan pola resmi Google (google[a-f0-9].html), Yandex (yandex_[a-f0-9].html),
 * BingSiteAuth.xml, atau berkas teks verifikasi (.txt). Mencegah pembuatan halaman HTML sembarang.
 */
function sanitize_verification_filename(string $raw): string {
    $name = basename(trim($raw));
    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $name) ?? '';
    if ($name === '') {
        return '';
    }
    $reserved = ['index.php', 'index.html', 'robots.txt', 'sitemap.xml', '.htaccess'];
    if (in_array(strtolower($name), $reserved, true)) {
        return '';
    }
    if (
        preg_match('/^google[a-f0-9]{10,32}\.html$/i', $name) ||
        preg_match('/^yandex_[a-f0-9]{10,32}\.html$/i', $name) ||
        preg_match('/^BingSiteAuth\.xml$/i', $name) ||
        preg_match('/^[a-zA-Z0-9_\-]{6,64}\.txt$/i', $name)
    ) {
        return substr($name, 0, 80);
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['form_action'] ?? 'save_seo';

    // 1. Upload OG Image / Social Share Image
    if (!empty($_FILES['og_image_file']['name'])) {
        $up = secure_upload_image($_FILES['og_image_file'], 'seo', $pdo);
        if ($up['success']) {
            save_setting($pdo, 'seo_og_image', $up['filename'], 'seo');
        } else {
            $error_msg = $up['error'];
        }
    } elseif (!empty($_POST['og_image_file_from_media'])) {
        $safe_og = sanitize_image_url((string)$_POST['og_image_file_from_media'], '');
        if ($safe_og !== '') {
            save_setting($pdo, 'seo_og_image', $safe_og, 'seo');
        }
    } elseif (isset($_POST['seo_og_image_url']) && trim((string)$_POST['seo_og_image_url']) !== '') {
        $safe_og = sanitize_image_url((string)$_POST['seo_og_image_url'], '');
        if ($safe_og !== '') {
            save_setting($pdo, 'seo_og_image', $safe_og, 'seo');
        }
    }

    // 2. Ekstrak & Bersihkan Token Kepemilikan Website (Search Console, Bing, Yandex, Facebook, Pinterest)
    $google_verif    = extract_verification_token((string)($_POST['seo_google_verification'] ?? ''));
    $bing_verif      = extract_verification_token((string)($_POST['seo_bing_verification'] ?? ''));
    $yandex_verif    = extract_verification_token((string)($_POST['seo_yandex_verification'] ?? ''));
    $pinterest_verif = extract_verification_token((string)($_POST['seo_pinterest_verification'] ?? ''));
    $fb_domain_verif = extract_verification_token((string)($_POST['seo_fb_domain_verification'] ?? ''));

    save_setting($pdo, 'seo_google_verification', $google_verif, 'seo');
    save_setting($pdo, 'seo_bing_verification', $bing_verif, 'seo');
    save_setting($pdo, 'seo_yandex_verification', $yandex_verif, 'seo');
    save_setting($pdo, 'seo_pinterest_verification', $pinterest_verif, 'seo');
    save_setting($pdo, 'seo_fb_domain_verification', $fb_domain_verif, 'seo');

    // 3. Simpan Input TXT Search Console (DNS TXT Record & File Verifikasi Fisik di Root Hosting)
    $search_console_txt = clean_plain_text(strip_tags((string)($_POST['seo_search_console_txt'] ?? '')), 500);
    $verif_file_name    = sanitize_verification_filename((string)($_POST['seo_verification_file_name'] ?? ''));
    // Bersihkan isi file verifikasi dari tag HTML/script/PHP agar tidak dapat disalahgunakan untuk defacement
    $raw_verif_content  = str_replace(['<', '>', '<?', '?>'], '', (string)($_POST['seo_verification_file_content'] ?? ''));
    $verif_file_content = clean_plain_text($raw_verif_content, 300);

    save_setting($pdo, 'seo_search_console_txt', $search_console_txt, 'seo');
    save_setting($pdo, 'seo_verification_file_name', $verif_file_name, 'seo');
    save_setting($pdo, 'seo_verification_file_content', $verif_file_content, 'seo');

    // Tulis file verifikasi fisik di root website hanya jika nama file lolos allowlist ketat
    if ($verif_file_name !== '' && $verif_file_content !== '') {
        $target_verif_path = __DIR__ . '/../' . $verif_file_name;
        @file_put_contents($target_verif_path, $verif_file_content);
    }

    // 4. Simpan Field SEO Global, OpenGraph, Twitter Card, Schema JSON-LD, Analytics & Indexing
    $seo_fields = [
        'default_meta_title',
        'default_meta_desc',
        'meta_keywords',
        'seo_title_separator',
        'seo_canonical_base',
        'seo_robots',
        'seo_googlebot',
        'seo_author',
        'seo_geo_region',
        'seo_geo_placename',
        'seo_geo_position',
        'seo_og_type',
        'seo_og_site_name',
        'seo_twitter_card',
        'seo_twitter_site',
        'seo_schema_type',
        'seo_schema_price_range',
        'seo_schema_founding_year',
        'seo_custom_robots_txt',
    ];

    foreach ($seo_fields as $field) {
        if (isset($_POST[$field])) {
            $max_len = ($field === 'seo_custom_robots_txt') ? 3000 : 500;
            $clean_field_val = clean_plain_text(strip_tags((string)$_POST[$field]), $max_len);
            save_setting($pdo, $field, $clean_field_val, 'seo');
        }
    }

    // 5. Validasi ketat ID Analytics / Tag Manager / Pixel (hanya format ID resmi, cegah XSS)
    $ga_id = trim((string)($_POST['seo_google_analytics_id'] ?? ''));
    if ($ga_id !== '' && !preg_match('/^(G|UA|AW)-[A-Z0-9\-]{4,25}$/i', $ga_id)) {
        $ga_id = '';
    }
    save_setting($pdo, 'seo_google_analytics_id', strtoupper($ga_id), 'seo');

    $gtm_id = trim((string)($_POST['seo_gtm_id'] ?? ''));
    if ($gtm_id !== '' && !preg_match('/^GTM-[A-Z0-9]{4,20}$/i', $gtm_id)) {
        $gtm_id = '';
    }
    save_setting($pdo, 'seo_gtm_id', strtoupper($gtm_id), 'seo');

    $fb_pixel_id = trim((string)($_POST['seo_fb_pixel_id'] ?? ''));
    if ($fb_pixel_id !== '' && !preg_match('/^[0-9]{8,22}$/', $fb_pixel_id)) {
        $fb_pixel_id = '';
    }
    save_setting($pdo, 'seo_fb_pixel_id', $fb_pixel_id, 'seo');

    // 6. Sinkronkan file robots.txt fisik apabila diisi (tanpa tag HTML/PHP)
    $custom_robots = clean_plain_text(str_replace(['<', '>', '<?', '?>'], '', (string)($_POST['seo_custom_robots_txt'] ?? '')), 3000);
    if ($custom_robots !== '') {
        @file_put_contents(__DIR__ . '/../robots.txt', $custom_robots . "\n");
    }

    if ($error_msg === '') {
        log_audit($pdo, 'Update Global SEO & Search Console', 'Memperbarui Pengaturan Global SEO, Verifikasi Kepemilikan Website, TXT Search Console & Schema.org');
        $msg = 'Pengaturan Global SEO, Kepemilikan Website, TXT Search Console & Metadata berhasil disimpan dan disinkronkan ke seluruh halaman publik!';
    }
}

$default_robots_txt = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /config/\nDisallow: /includes/\n\nSitemap: " . e(get_setting($pdo, 'seo_canonical_base', 'https://www.creative.co.id')) . "/sitemap.php";
$current_og_image = get_setting($pdo, 'seo_og_image', '');
$current_verif_file = get_setting($pdo, 'seo_verification_file_name', '');
?>

<!-- Header Banner SEO & Search Console -->
<div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
    <div class="space-y-1.5">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 font-bold text-[11px]">
            <span>🔍 TECHNICAL SEO, SEARCH CONSOLE & OWNERSHIP VERIFICATION</span>
        </div>
        <h1 class="text-lg sm:text-xl font-extrabold">
            Pengaturan Global SEO, Kepemilikan Website, Search Console TXT & Metadata
        </h1>
        <p class="text-slate-300 text-xs max-w-3xl leading-relaxed">
            Kelola meta title & description global, verifikasi kepemilikan website (Google Search Console HTML Tag & TXT Record, Bing Webmaster, Yandex, Meta Domain), OpenGraph Social Card, Schema.org JSON-LD, hingga <code>robots.txt</code> dan <code>sitemap.xml</code>.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2 shrink-0 text-xs">
        <a href="../sitemap.php" target="_blank" class="px-3.5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold rounded-xl transition">
            🗺️ Buka Sitemap XML &nearr;
        </a>
        <a href="../robots.txt" target="_blank" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold rounded-xl transition">
            🤖 Cek robots.txt &nearr;
        </a>
    </div>
</div>

<?php if ($msg !== ''): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-900 text-xs p-4 rounded-xl font-bold flex items-center justify-between">
        <span>✓ <?= e($msg) ?></span>
        <a href="../index.php" target="_blank" class="underline font-extrabold">Cek Source Halaman Publik &nearr;</a>
    </div>
<?php endif; ?>

<?php if ($error_msg !== ''): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-900 text-xs p-4 rounded-xl font-bold">
        ⚠️ <?= e($error_msg) ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="save_seo">

    <!-- =====================================================================
         BAGIAN 1: VERIFIKASI KEPEMILIKAN WEBSITE & INPUT TXT SEARCH CONSOLE
         ===================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-4">
            <div>
                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-extrabold text-[10px] uppercase tracking-wider">
                    Fitur Utama Search Console
                </span>
                <h2 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1">
                    1. Verifikasi Kepemilikan Website & Input TXT Google Search Console
                </h2>
                <p class="text-slate-500 text-xs">
                    Masukkan kode verifikasi atau tempel langsung tag meta dari Google Search Console, input record TXT Search Console, atau buat file verifikasi HTML/TXT otomatis di root hosting.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Kolom Kiri: Meta Tag Verification -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                    <span>🏷️ Metode 1: Tag HTML Meta Verification (Otomatis di &lt;head&gt;)</span>
                </h3>

                <div>
                    <label class="block font-bold mb-1 text-slate-800">
                        Google Search Console Verification (<code>google-site-verification</code>)
                    </label>
                    <input
                        type="text"
                        name="seo_google_verification"
                        value="<?= e(get_setting($pdo, 'seo_google_verification')) ?>"
                        placeholder="Tempel kode token atau seluruh tag <meta name=&quot;google-site-verification&quot; content=&quot;...&quot;>"
                        class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                    >
                    <span class="text-[11px] text-slate-500 block mt-1">
                        💡 Anda dapat menempelkan kode token saja maupun seluruh tag <code>&lt;meta name="google-site-verification" ...&gt;</code> (sistem otomatis mengekstrak kode).
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">
                            Bing Webmaster Tools (<code>msvalidate.01</code>)
                        </label>
                        <input
                            type="text"
                            name="seo_bing_verification"
                            value="<?= e(get_setting($pdo, 'seo_bing_verification')) ?>"
                            placeholder="Kode Bing msvalidate.01"
                            class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                        >
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">
                            Yandex Webmaster (<code>yandex-verification</code>)
                        </label>
                        <input
                            type="text"
                            name="seo_yandex_verification"
                            value="<?= e(get_setting($pdo, 'seo_yandex_verification')) ?>"
                            placeholder="Kode Yandex Verification"
                            class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">
                            Meta / Facebook Domain Verification
                        </label>
                        <input
                            type="text"
                            name="seo_fb_domain_verification"
                            value="<?= e(get_setting($pdo, 'seo_fb_domain_verification')) ?>"
                            placeholder="facebook-domain-verification"
                            class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                        >
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">
                            Pinterest Site Verification (<code>p:domain_verify</code>)
                        </label>
                        <input
                            type="text"
                            name="seo_pinterest_verification"
                            value="<?= e(get_setting($pdo, 'seo_pinterest_verification')) ?>"
                            placeholder="Kode Pinterest"
                            class="w-full border border-slate-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                        >
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Input TXT Search Console & File Verifikasi Root -->
            <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200 space-y-4">
                <h3 class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                    <span>📄 Metode 2: Input TXT Search Console & File Verifikasi Root Hosting</span>
                </h3>

                <div>
                    <label class="block font-bold mb-1 text-slate-800">
                        Input TXT Google Search Console (DNS TXT Record / Token Verifikasi Domain)
                    </label>
                    <textarea
                        name="seo_search_console_txt"
                        rows="2"
                        placeholder="Contoh: google-site-verification=rXOxyZounnZasA8Z7oaD3c14JdjS9aKSWeb..."
                        class="w-full border border-blue-300 bg-white rounded-xl p-2.5 font-mono text-xs"
                    ><?= e(get_setting($pdo, 'seo_search_console_txt')) ?></textarea>
                    <span class="text-[11px] text-slate-600 block mt-1">
                        ✓ Menyimpan nilai TXT Search Console (<code>google-site-verification=...</code>) dan otomatis menyertakannya pada meta verification halaman publik.
                    </span>
                </div>

                <div class="pt-2 border-t border-blue-200/80 space-y-3">
                    <div class="font-bold text-slate-800">
                        Generator File Verifikasi Fisik (HTML / TXT) di Root Website
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700">
                                Nama File Verifikasi (.html / .txt)
                            </label>
                            <input
                                type="text"
                                name="seo_verification_file_name"
                                value="<?= e($current_verif_file) ?>"
                                placeholder="Contoh: google1234567890abcdef.html"
                                class="w-full border border-slate-300 bg-white rounded-xl p-2 font-mono text-xs"
                            >
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700">
                                Isi File Verifikasi (Teks / Token)
                            </label>
                            <input
                                type="text"
                                name="seo_verification_file_content"
                                value="<?= e(get_setting($pdo, 'seo_verification_file_content')) ?>"
                                placeholder="google-site-verification: google1234567890abcdef.html"
                                class="w-full border border-slate-300 bg-white rounded-xl p-2 font-mono text-xs"
                            >
                        </div>
                    </div>
                    <?php if ($current_verif_file !== '' && file_exists(__DIR__ . '/../' . $current_verif_file)): ?>
                        <div class="p-2.5 rounded-xl bg-emerald-100/80 border border-emerald-300 text-emerald-900 flex items-center justify-between text-[11px] font-semibold">
                            <span>✓ File verifikasi aktif di root website: <code>/<?= e($current_verif_file) ?></code></span>
                            <a href="../<?= e($current_verif_file) ?>" target="_blank" class="underline font-bold">Buka File &nearr;</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         BAGIAN 2: PENGATURAN META TITLE, DESCRIPTION, KEYWORDS & SERP PREVIEW
         ===================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-sm sm:text-base font-extrabold text-slate-900">
                2. Meta Title, Meta Description, Keywords & Pratinjau Hasil Pencarian Google (SERP)
            </h2>
            <p class="text-slate-500 text-xs">
                Digunakan sebagai judul dan deskripsi utama di Google apabila halaman tidak menentukan meta khusus.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block font-bold mb-1 text-slate-800">Default Meta Title Website *</label>
                        <input
                            type="text"
                            id="inpMetaTitle"
                            name="default_meta_title"
                            required
                            value="<?= e(get_setting($pdo, 'default_meta_title', 'PT. Kreatif Global Solusindo - System Integrator, Security & ISP KGSNet')) ?>"
                            oninput="updateSerpPreview()"
                            class="w-full border border-slate-300 rounded-xl p-2.5 font-semibold"
                        >
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-800">Pemisah Judul</label>
                        <select name="seo_title_separator" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-bold">
                            <?php
                            $cur_sep = get_setting($pdo, 'seo_title_separator', '|');
                            foreach (['|', '-', '—', '·', '»'] as $sep):
                            ?>
                                <option value="<?= e($sep) ?>" <?= $cur_sep === $sep ? 'selected' : '' ?>><?= e($sep) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-slate-800">Default Meta Description *</label>
                    <textarea
                        id="inpMetaDesc"
                        name="default_meta_desc"
                        rows="3"
                        required
                        oninput="updateSerpPreview()"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    ><?= e(get_setting($pdo, 'default_meta_desc', 'Solusi terintegrasi IT, Telekomunikasi, CCTV, Fabrikasi Tower, Penangkal Petir < 2 Ohm, dan layanan Internet Dedicated ISP KGSNet di Palembang & Sumatera Selatan.')) ?></textarea>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-slate-800">Meta Keywords (Kata Kunci Utama Dipisahkan Koma)</label>
                    <input
                        type="text"
                        name="meta_keywords"
                        value="<?= e(get_setting($pdo, 'meta_keywords', 'ISP Palembang, KGSNet, PT Kreatif Global Solusindo, Creative Com, Kontraktor Tower Palembang, Penangkal Petir, Grounding System, Instalasi Fiber Optik, CCTV AI')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">Canonical Base URL (Domain Utama)</label>
                        <input
                            type="url"
                            id="inpCanonicalUrl"
                            name="seo_canonical_base"
                            value="<?= e(get_setting($pdo, 'seo_canonical_base', 'https://www.creative.co.id')) ?>"
                            oninput="updateSerpPreview()"
                            placeholder="https://www.creative.co.id"
                            class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                        >
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-700">Meta Author / Publisher</label>
                        <input
                            type="text"
                            name="seo_author"
                            value="<?= e(get_setting($pdo, 'seo_author', 'PT. Kreatif Global Solusindo (KGSNet)')) ?>"
                            class="w-full border border-slate-300 rounded-xl p-2.5"
                        >
                    </div>
                </div>
            </div>

            <!-- Live Google SERP Preview Card -->
            <div class="lg:col-span-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                        Pratinjau Tampilan di Google Search (SERP)
                    </span>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold">Live Preview</span>
                </div>
                <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-1.5">
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-[10px] flex items-center justify-center">KGS</span>
                        <div class="truncate">
                            <div class="font-bold text-slate-800 text-[11px] leading-tight"><?= e(get_setting($pdo, 'company_name', 'PT. Kreatif Global Solusindo')) ?></div>
                            <div id="serpUrlPreview" class="text-[11px] text-emerald-700 truncate"><?= e(get_setting($pdo, 'seo_canonical_base', 'https://www.creative.co.id')) ?></div>
                        </div>
                    </div>
                    <div id="serpTitlePreview" class="text-base font-bold text-blue-700 hover:underline leading-snug line-clamp-2">
                        <?= e(get_setting($pdo, 'default_meta_title', 'PT. Kreatif Global Solusindo - System Integrator, Security & ISP KGSNet')) ?>
                    </div>
                    <p id="serpDescPreview" class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                        <?= e(get_setting($pdo, 'default_meta_desc', 'Solusi terintegrasi IT, Telekomunikasi, CCTV, Fabrikasi Tower, Penangkal Petir < 2 Ohm, dan layanan Internet Dedicated ISP KGSNet di Palembang & Sumatera Selatan.')) ?>
                    </p>
                </div>
                <p class="text-[11px] text-slate-500">
                     Panjang ideal: <strong>Meta Title 50–60 karakter</strong> dan <strong>Meta Description 120–160 karakter</strong> agar tidak terpotong di hasil pencarian.
                </p>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         BAGIAN 3: OPENGRAPH SOCIAL SHARE, TWITTER CARD & SCHEMA.ORG JSON-LD
         ===================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- OpenGraph & Social Media Preview -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 border-b pb-3">
                3. OpenGraph (WhatsApp / Facebook / LinkedIn) & Twitter Card
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">OpenGraph Type</label>
                    <select name="seo_og_type" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                        <?php
                        $cur_og_type = get_setting($pdo, 'seo_og_type', 'website');
                        foreach (['website' => 'website (Standar Korporasi)', 'business.business' => 'business.business', 'article' => 'article'] as $k => $lbl):
                        ?>
                            <option value="<?= e($k) ?>" <?= $cur_og_type === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">OpenGraph Site Name</label>
                    <input
                        type="text"
                        name="seo_og_site_name"
                        value="<?= e(get_setting($pdo, 'seo_og_site_name', 'PT. Kreatif Global Solusindo (KGSNet)')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>
            </div>

            <div>
                <label class="block font-bold mb-1 text-slate-700">Gambar Thumbnail Share Sosial Media (OG Image 1200x630px)</label>
                <div class="space-y-2">
                    <input
                        type="text"
                        name="seo_og_image_url"
                        value="<?= e($current_og_image) ?>"
                        placeholder="URL gambar atau upload file gambar di bawah..."
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                    <input
                        type="file"
                        name="og_image_file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Tipe Twitter Card</label>
                    <select name="seo_twitter_card" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                        <?php
                        $cur_tw = get_setting($pdo, 'seo_twitter_card', 'summary_large_image');
                        foreach (['summary_large_image' => 'Summary Large Image', 'summary' => 'Summary Card'] as $k => $lbl):
                        ?>
                            <option value="<?= e($k) ?>" <?= $cur_tw === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Akun Twitter / X (@username)</label>
                    <input
                        type="text"
                        name="seo_twitter_site"
                        value="<?= e(get_setting($pdo, 'seo_twitter_site', '@kgsnet_id')) ?>"
                        placeholder="@kgsnet_id"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>
            </div>
        </div>

        <!-- Schema.org JSON-LD, Geo Tagging & Analytics -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 border-b pb-3">
                4. Schema.org JSON-LD, Geo-Tagging Lokal & ID Analytics
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Tipe Schema.org</label>
                    <select name="seo_schema_type" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                        <?php
                        $cur_schema = get_setting($pdo, 'seo_schema_type', 'Organization');
                        foreach (['Organization', 'LocalBusiness', 'InternetServiceProvider', 'ProfessionalService'] as $sc):
                        ?>
                            <option value="<?= e($sc) ?>" <?= $cur_schema === $sc ? 'selected' : '' ?>><?= e($sc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Tahun Berdiri</label>
                    <input
                        type="text"
                        name="seo_schema_founding_year"
                        value="<?= e(get_setting($pdo, 'seo_schema_founding_year', '1997')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Rentang Harga</label>
                    <input
                        type="text"
                        name="seo_schema_price_range"
                        value="<?= e(get_setting($pdo, 'seo_schema_price_range', '$$$')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Geo Region</label>
                    <input
                        type="text"
                        name="seo_geo_region"
                        value="<?= e(get_setting($pdo, 'seo_geo_region', 'ID-SS')) ?>"
                        placeholder="ID-SS"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Geo Placename (Kota)</label>
                    <input
                        type="text"
                        name="seo_geo_placename"
                        value="<?= e(get_setting($pdo, 'seo_geo_placename', 'Palembang, Sumatera Selatan')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5"
                    >
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Koordinat Geo (Lat;Long)</label>
                    <input
                        type="text"
                        name="seo_geo_position"
                        value="<?= e(get_setting($pdo, 'seo_geo_position', '-2.9718;104.7644')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Google Analytics 4 ID</label>
                    <input
                        type="text"
                        name="seo_google_analytics_id"
                        value="<?= e(get_setting($pdo, 'seo_google_analytics_id')) ?>"
                        placeholder="G-XXXXXXXXXX"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Google Tag Manager ID</label>
                    <input
                        type="text"
                        name="seo_gtm_id"
                        value="<?= e(get_setting($pdo, 'seo_gtm_id')) ?>"
                        placeholder="GTM-XXXXXXX"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Meta / FB Pixel ID</label>
                    <input
                        type="text"
                        name="seo_fb_pixel_id"
                        value="<?= e(get_setting($pdo, 'seo_fb_pixel_id')) ?>"
                        placeholder="123456789012345"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         BAGIAN 4: ROBOTS META DIRECTIVES & ROBOTS.TXT EDITOR
         ===================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 border-b pb-3">
            5. Kebijakan Indexing Mesin Pencari (Robots Meta & Editor <code>robots.txt</code>)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="space-y-4">
                <div>
                    <label class="block font-bold mb-1 text-slate-700">Direktif Meta Robots Global</label>
                    <?php $cur_robots = get_setting($pdo, 'seo_robots', 'index, follow'); ?>
                    <select name="seo_robots" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-semibold">
                        <option value="index, follow" <?= $cur_robots === 'index, follow' ? 'selected' : '' ?>>index, follow (Direkomendasikan — Tampil di Google)</option>
                        <option value="index, follow, max-image-preview:large" <?= $cur_robots === 'index, follow, max-image-preview:large' ? 'selected' : '' ?>>index, follow, max-image-preview:large (Google Discover)</option>
                        <option value="noindex, follow" <?= $cur_robots === 'noindex, follow' ? 'selected' : '' ?>>noindex, follow</option>
                        <option value="noindex, nofollow" <?= $cur_robots === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow (Mode Staging / Maintenance)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-slate-700">Direktif Khusus Googlebot</label>
                    <input
                        type="text"
                        name="seo_googlebot"
                        value="<?= e(get_setting($pdo, 'seo_googlebot', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')) ?>"
                        class="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                    >
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold mb-1 text-slate-700">Isi File <code>robots.txt</code> (Otomatis Disinkronkan ke Root Website)</label>
                <textarea
                    name="seo_custom_robots_txt"
                    rows="5"
                    class="w-full border border-slate-300 rounded-xl p-3 font-mono text-xs bg-slate-50"
                ><?= e(get_setting($pdo, 'seo_custom_robots_txt', $default_robots_txt)) ?></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl shadow-md transition cursor-pointer">
                💾 Simpan Seluruh Pengaturan Global SEO & Search Console
            </button>
        </div>
    </div>
</form>

<script>
function updateSerpPreview() {
    var titleEl = document.getElementById('inpMetaTitle');
    var descEl = document.getElementById('inpMetaDesc');
    var urlEl = document.getElementById('inpCanonicalUrl');
    if (titleEl && document.getElementById('serpTitlePreview')) {
        document.getElementById('serpTitlePreview').textContent = titleEl.value || 'PT. Kreatif Global Solusindo';
    }
    if (descEl && document.getElementById('serpDescPreview')) {
        document.getElementById('serpDescPreview').textContent = descEl.value || '';
    }
    if (urlEl && document.getElementById('serpUrlPreview')) {
        document.getElementById('serpUrlPreview').textContent = urlEl.value || 'https://www.creative.co.id';
    }
}
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
