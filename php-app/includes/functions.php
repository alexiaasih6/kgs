<?php
/**
 * Fungsi Helper Global, Sanitasi Keamanan & Sinkronisasi Database CMS
 * PT. Kreatif Global Solusindo (KGS)
 */

// Escape output HTML untuk mencegah XSS pada teks biasa & atribut
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Bersihkan input teks biasa (plain text) dari karakter kontrol berbahaya
 * tanpa merusak karakter teks sah (spasi, tanda baca, UTF-8).
 */
/**
 * Bersihkan input teks biasa (plain text) dari tag HTML dan karakter kontrol berbahaya
 * tanpa merusak karakter teks sah (spasi, tanda baca, UTF-8).
 */
function clean_plain_text(?string $str, int $max_len = 0): string {
    $raw = (string)$str;
    // Hapus blok script/style/iframe beserta isinya sebelum strip_tags
    $raw = preg_replace('#<(script|style|iframe|object|embed|svg)\b[^>]*>.*?</\1>#is', '', $raw) ?? $raw;
    $clean = trim(strip_tags($raw));
    // Hapus null byte dan karakter kontrol non-printable (kecuali newline/tab)
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean) ?? '';
    if ($max_len > 0 && function_exists('mb_substr')) {
        $clean = mb_substr($clean, 0, $max_len, 'UTF-8');
    } elseif ($max_len > 0) {
        $clean = substr($clean, 0, $max_len);
    }
    return $clean;
}

/**
 * Validasi warna HEX yang aman untuk mencegah CSS Injection pada inline style
 */
function sanitize_hex_color(?string $color, string $default = '#1d4ed8'): string {
    $c = trim((string)$color);
    if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $c)) {
        return $c;
    }
    return $default;
}

/**
 * Validasi URL/Path Gambar untuk atribut src/href gambar.
 * Hanya mengizinkan protokol https:// atau http:// yang valid,
 * atau path internal relatif di dalam folder uploads/ atau assets/.
 * Menolak javascript:, data:, vbscript:, file:, //, dan path traversal (..).
 */
function sanitize_image_url(?string $url, string $default = ''): string {
    $trimmed = trim((string)$url);
    if ($trimmed === '') {
        return $default;
    }

    // Tolak karakter kontrol, kutip, kurung sudut, backslash, atau upaya traversal
    if (preg_match('/[\x00-\x1F\x7F<>"\'\\\\]/', $trimmed) || strpos($trimmed, '..') !== false) {
        return $default;
    }

    $decoded = html_entity_decode($trimmed, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($decoded)) ?? '';
    if (
        strpos($normalized, 'javascript:') === 0 ||
        strpos($normalized, 'data:') === 0 ||
        strpos($normalized, 'vbscript:') === 0 ||
        strpos($normalized, 'file:') === 0 ||
        strpos($normalized, '//') === 0
    ) {
        return $default;
    }

    if (preg_match('#^https?://#i', $trimmed)) {
        return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : $default;
    }

    $clean_rel = ltrim($trimmed, '/');
    if (preg_match('#^(uploads|assets)/[a-zA-Z0-9_\-\/\.]+$#', $clean_rel)) {
        // Pastikan tidak berakhiran ekstensi script berbahaya
        if (preg_match('/\.(php\d*|phtml|phar|pl|py|cgi|sh|exe|svg|html?|js)$/i', $clean_rel)) {
            return $default;
        }
        return $clean_rel;
    }

    return $default;
}

/**
 * Sanitasi HTML kaya (Rich Text) untuk halaman statis & artikel blog.
 * Menghapus tag berbahaya (<script>, <iframe>, <object>, <svg>, <form>, <style>, dll),
 * menghapus seluruh atribut event handler (onerror, onload, onclick, dll),
 * dan memblokir protokol berbahaya (javascript:, vbscript:, data:text/html).
 */
function sanitize_rich_html(?string $html): string {
    $raw = trim((string)$html);
    if ($raw === '') {
        return '';
    }

    // 1. Hapus elemen blok berbahaya beserta isinya terlebih dahulu
    $cleaned = preg_replace(
        '#<(script|style|iframe|object|embed|applet|svg|math|form|input|button|select|textarea|meta|link|base|frame|frameset|xss|marquee)\b[^>]*>.*?</\1>#is',
        '',
        $raw
    );
    // Hapus self-closing atau unclosed dangerous tags
    $cleaned = preg_replace(
        '#</?(script|style|iframe|object|embed|applet|svg|math|form|input|button|select|textarea|meta|link|base|frame|frameset|xss|marquee)\b[^>]*>#is',
        '',
        (string)$cleaned
    );

    // 2. Izinkan hanya tag tipografi dan struktur konten yang aman
    $allowed_tags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><h5><h6><blockquote><code><pre><span><div><a><img><table><thead><tbody><tr><th><td><hr><figure><figcaption><small><sub><sup>';
    $cleaned = strip_tags((string)$cleaned, $allowed_tags);

    // 3. Gunakan DOMDocument jika tersedia untuk membersihkan atribut secara presisi
    if (class_exists('DOMDocument')) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="__kgs_sanitize_root">' . $cleaned . '</div></body></html>';
        if (@$dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//*');
            // Catatan: atribut 'id' dan 'style' sengaja tidak diizinkan secara global untuk mencegah DOM Clobbering & CSS Defacement/Hidden Spam
            $allowed_attrs_global = ['class', 'title', 'alt', 'colspan', 'rowspan', 'align'];
            if ($nodes) {
                foreach ($nodes as $node) {
                    if (!($node instanceof DOMElement)) {
                        continue;
                    }
                    if ($node->getAttribute('id') === '__kgs_sanitize_root') {
                        continue;
                    }
                    $tag = strtolower($node->tagName);
                    $remove_attrs = [];
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            $attr_name = strtolower($attr->nodeName);
                            $attr_val  = trim($attr->nodeValue);

                            // Blokir semua event handler on* (onclick, onerror, onload, onmouseover, dll)
                            if (strpos($attr_name, 'on') === 0) {
                                $remove_attrs[] = $attr->nodeName;
                                continue;
                            }

                            if ($tag === 'a' && in_array($attr_name, ['href', 'target', 'rel'], true)) {
                                if ($attr_name === 'href') {
                                    $safe_href = sanitize_nav_url($attr_val);
                                    $node->setAttribute('href', $safe_href);
                                } elseif ($attr_name === 'target') {
                                    $node->setAttribute('target', $attr_val === '_blank' ? '_blank' : '_self');
                                    if ($attr_val === '_blank') {
                                        $node->setAttribute('rel', 'noopener noreferrer');
                                    }
                                }
                                continue;
                            }

                            if ($tag === 'img' && in_array($attr_name, ['src', 'alt', 'width', 'height', 'loading'], true)) {
                                if ($attr_name === 'src') {
                                    $safe_src = sanitize_image_url($attr_val, '');
                                    if ($safe_src === '') {
                                        $remove_attrs[] = $attr->nodeName;
                                    } else {
                                        $node->setAttribute('src', $safe_src);
                                    }
                                }
                                continue;
                            }

                            if (!in_array($attr_name, $allowed_attrs_global, true)) {
                                $remove_attrs[] = $attr->nodeName;
                            }
                        }
                    }
                    foreach ($remove_attrs as $r_attr) {
                        $node->removeAttribute($r_attr);
                    }
                }
            }

            $root = $dom->getElementById('__kgs_sanitize_root');
            if ($root) {
                $output = '';
                foreach ($root->childNodes as $child) {
                    $output .= $dom->saveHTML($child);
                }
                libxml_clear_errors();
                libxml_use_internal_errors($prev);
                return $output;
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
    }

    // 4. Fallback regex pembersihan atribut on*, style, dan protokol javascript:/data: jika DOMDocument gagal
    $cleaned = preg_replace('/\s+(?:on[a-zA-Z]+|style|id)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string)$cleaned);
    $cleaned = preg_replace('/(href|src|action)\s*=\s*["\']?\s*(?:javascript|vbscript|data)\s*:[^"\'>\s]*/i', '$1="#"', (string)$cleaned);

    return (string)$cleaned;
}

/**
 * Reset cache pengaturan di memori dalam siklus request saat ini
 */
function clear_settings_cache(): void {
    unset($GLOBALS['__kgs_settings_cache']);
    unset($GLOBALS['__kgs_live_preview_state']);
}

/**
 * Hapus / perbarui bagian tertentu pada file config/live-preview-state.json
 * ketika Admin CMS menyimpan perubahan ke database MySQL agar tidak menimpa data DB terbaru.
 */
function invalidate_live_preview_json(?string $section = null, ?string $key = null): void {
    clear_settings_cache();
    $json_file = dirname(__DIR__) . '/config/live-preview-state.json';
    if (!is_file($json_file) || !is_writable($json_file)) {
        return;
    }
    try {
        $raw = @file_get_contents($json_file);
        if (!$raw) {
            return;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return;
        }
        if ($section === null) {
            @unlink($json_file);
            return;
        }
        if ($key !== null && isset($decoded[$section]) && is_array($decoded[$section])) {
            unset($decoded[$section][$key]);
        } else {
            unset($decoded[$section]);
        }
        @file_put_contents($json_file, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        // Abaikan jika gagal menulis file
    }
}

/**
 * Pastikan tabel `site_settings` tersedia di database
 */
function ensure_site_settings_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `group_name` VARCHAR(50) NOT NULL DEFAULT 'general',
            `key_name` VARCHAR(60) NOT NULL UNIQUE,
            `key_value` LONGTEXT,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Exception $e) {
        // Abaikan jika user DB tidak memiliki izin CREATE
    }
}

/**
 * Ambil pengaturan situs dari database dengan caching per-request yang dapat di-reset.
 * Database (`site_settings`) adalah sumber kebenaran utama (Single Source of Truth).
 */
function get_setting(PDO $pdo, string $key, string $default = ''): string {
    if (!isset($GLOBALS['__kgs_settings_cache']) || !is_array($GLOBALS['__kgs_settings_cache'])) {
        $GLOBALS['__kgs_settings_cache'] = [];
        try {
            ensure_site_settings_table($pdo);
            $stmt = $pdo->query("SELECT key_name, key_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $GLOBALS['__kgs_settings_cache'][$row['key_name']] = $row['key_value'];
            }
        } catch (Exception $e) {
            // Tabel mungkin belum diimpor
        }
    }

    $cache = $GLOBALS['__kgs_settings_cache'];

    // Normalisasi otomatis email lama ke info@creative.co.id, nomor telepon tanpa (Hunting), & topbar_badge ke alamat kantor
    if (($key === 'phone_palembang' || $key === 'phone_hunting')) {
        $val_phone = (string)($cache[$key] ?? ($cache['phone_palembang'] ?? ($cache['phone_hunting'] ?? '')));
        if ($val_phone !== '') {
            return trim(str_ireplace('(Hunting)', '', $val_phone));
        }
    }
    if (($key === 'email' || $key === 'email_general')) {
        $val_email = (string)($cache[$key] ?? ($cache['email'] ?? ($cache['email_general'] ?? '')));
        if ($val_email === 'info@kgs.net.id') {
            return 'info@creative.co.id';
        }
    }
    if ($key === 'topbar_badge' && isset($cache['topbar_badge']) && $cache['topbar_badge'] === 'Sejak 1997 (Creative Com)') {
        return (string)($cache['address_palembang'] ?? 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
    }
    if ($key === 'company_name') {
        $val_cname = trim((string)($cache['company_name'] ?? ''));
        if ($val_cname === 'Creatve.co.id' || $val_cname === 'Creative.co.id') {
            return 'PT. KREATIF GLOBAL SOLUSINDO';
        }
    }

    // Alias kompatibilitas otomatis antar key pengaturan
    if (array_key_exists($key, $cache) && $cache[$key] !== null && $cache[$key] !== '') {
        $cached_val = trim((string)$cache[$key]);
        // Pulihkan URL akses cepat footer jika sebelumnya tersimpan sebagai '#' atau '#anchor'
        if (in_array($key, ['footer_quick_1_url', 'footer_quick_2_url', 'footer_quick_3_url', 'footer_quick_4_url', 'footer_quick_5_url'], true)) {
            if ($cached_val === '#' || strpos($cached_val, '#') === 0) {
                return $default;
            }
        }
        return (string)$cache[$key];
    }

    if ($key === 'phone_hunting' && !empty($cache['phone_palembang'])) {
        return (string)$cache['phone_palembang'];
    }
    if ($key === 'phone_palembang' && !empty($cache['phone_hunting'])) {
        return (string)$cache['phone_hunting'];
    }
    if ($key === 'email_general' && !empty($cache['email'])) {
        return (string)$cache['email'];
    }
    if ($key === 'email' && !empty($cache['email_general'])) {
        return (string)$cache['email_general'];
    }
    if ($key === 'operational_hours' && !empty($cache['operating_hours'])) {
        return (string)$cache['operating_hours'];
    }
    if ($key === 'operating_hours' && !empty($cache['operational_hours'])) {
        return (string)$cache['operational_hours'];
    }
    if ($key === 'footer_description' && !empty($cache['company_profile'])) {
        return (string)$cache['company_profile'];
    }
    if ($key === 'logo' && !empty($cache['company_logo'])) {
        return (string)$cache['company_logo'];
    }
    if ($key === 'company_logo' && !empty($cache['logo'])) {
        return (string)$cache['logo'];
    }

    // Fallback ke state Live Preview JSON hanya jika key belum ada di database
    $live = load_live_preview_state();
    if (!empty($live['settings']) && array_key_exists($key, $live['settings'])) {
        $liveVal = trim((string)$live['settings'][$key]);
        if ($liveVal !== '') {
            if (in_array($key, ['footer_quick_1_url', 'footer_quick_2_url', 'footer_quick_3_url', 'footer_quick_4_url', 'footer_quick_5_url'], true)) {
                if ($liveVal === '#' || strpos($liveVal, '#') === 0) {
                    return $default;
                }
            }
            return $liveVal;
        }
    }

    return $default;
}

/**
 * Simpan satu pengaturan ke `site_settings` dan langsung perbarui cache memori & alias terkait.
 */
function save_setting(PDO $pdo, string $key, string $value, string $group = 'general'): bool {
    ensure_site_settings_table($pdo);
    $key = clean_plain_text($key, 60);
    $group = clean_plain_text($group, 50);
    // Jika bukan key JSON terstruktur, pastikan nilai bersih dari tag HTML/script berbahaya
    if (substr($key, -5) !== '_json') {
        $value = clean_plain_text($value);
    } else {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', trim($value)) ?? '';
    }
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (group_name, key_name, key_value) 
         VALUES (?, ?, ?) 
         ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)"
    );
    $ok = $stmt->execute([$group, $key, $value]);

    // Sinkronisasi key pasangan agar Header, Footer, Halaman Kontak, dan Tentang Kami selalu konsisten
    $paired_keys = [
        'phone_palembang'    => ['group' => 'contact',  'alias' => 'phone_hunting'],
        'phone_hunting'      => ['group' => 'contact',  'alias' => 'phone_palembang'],
        'email'              => ['group' => 'contact',  'alias' => 'email_general'],
        'email_general'      => ['group' => 'contact',  'alias' => 'email'],
        'operational_hours'  => ['group' => 'contact',  'alias' => 'operating_hours'],
        'operating_hours'    => ['group' => 'contact',  'alias' => 'operational_hours'],
        'footer_description' => ['group' => 'identity', 'alias' => 'company_profile'],
        'company_profile'    => ['group' => 'footer',   'alias' => 'footer_description'],
        'footer_brand_label' => ['group' => 'identity', 'alias' => 'short_name'],
        'short_name'         => ['group' => 'identity', 'alias' => 'footer_brand_label'],
        'logo'               => ['group' => 'branding', 'alias' => 'company_logo'],
        'company_logo'       => ['group' => 'branding', 'alias' => 'logo'],
    ];

    if (isset($paired_keys[$key])) {
        $alias_info = $paired_keys[$key];
        $stmt->execute([$alias_info['group'], $alias_info['alias'], $value]);
    }

    invalidate_live_preview_json('settings', $key);
    return $ok;
}

/**
 * Muat snapshot live preview dari config/live-preview-state.json jika tersedia
 * dan sinkronisasikan ke database sekali jika memiliki build_id baru.
 */
function load_live_preview_state(): array {
    if (isset($GLOBALS['__kgs_live_preview_state']) && is_array($GLOBALS['__kgs_live_preview_state'])) {
        return $GLOBALS['__kgs_live_preview_state'];
    }
    $GLOBALS['__kgs_live_preview_state'] = [];
    $json_file = dirname(__DIR__) . '/config/live-preview-state.json';
    if (is_file($json_file) && is_readable($json_file)) {
        $raw = @file_get_contents($json_file);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (isset($decoded['liveJson']) && is_string($decoded['liveJson'])) {
                    $inner = json_decode($decoded['liveJson'], true);
                    if (is_array($inner)) {
                        $decoded = array_merge($decoded, $inner);
                    }
                }
                $GLOBALS['__kgs_live_preview_state'] = $decoded;
            }
        }
    }
    return $GLOBALS['__kgs_live_preview_state'];
}

/**
 * Format nomor telepon / WhatsApp menjadi format internasional 628...
 */
function format_wa_number(string $phone): string {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($clean, '0') === 0) {
        $clean = '62' . substr($clean, 1);
    }
    return $clean ?: '62811782119';
}

/**
 * Buat URL Google Maps Embed Iframe otomatis dari Alamat, Koordinat, atau objek PDO
 * @param PDO|string|null $map_input_or_pdo
 */
function get_google_map_embed_url($map_input_or_pdo): string {
    if ($map_input_or_pdo instanceof PDO) {
        $raw_input = get_setting(
            $map_input_or_pdo,
            'google_map_input',
            get_setting($map_input_or_pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')
        );
    } else {
        $raw_input = (string)$map_input_or_pdo;
    }
    $trimmed = trim($raw_input);
    if (empty($trimmed)) {
        $trimmed = 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113';
    }
    if (stripos($trimmed, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $trimmed, $m)) {
        $src = $m[1];
        if (preg_match('#^https://(www\.)?google\.(com|co\.id)/maps#i', $src) || preg_match('#^https://maps\.google\.(com|co\.id)/#i', $src)) {
            return $src;
        }
    }
    if (preg_match('#^https://((www\.)?google\.(com|co\.id)/maps|maps\.google\.(com|co\.id))/#i', $trimmed) && stripos($trimmed, 'output=embed') !== false) {
        return $trimmed;
    }
    return 'https://maps.google.com/maps?q=' . rawurlencode($trimmed) . '&t=&z=16&ie=UTF8&iwloc=&output=embed';
}

function build_gmap_embed_url(?string $map_input): string {
    return get_google_map_embed_url((string)$map_input);
}

function parse_google_map_embed_src(?string $map_input): string {
    return get_google_map_embed_url((string)$map_input);
}

/**
 * Buat URL Tautan Langsung Buka Google Maps
 */
function get_google_map_direct_url(string $map_input): string {
    $trimmed = trim($map_input);
    if (empty($trimmed)) {
        $trimmed = 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113';
    }
    if (preg_match('#^https?://#i', $trimmed) && stripos($trimmed, 'output=embed') === false) {
        return $trimmed;
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($trimmed);
}

/**
 * Buat slug URL-friendly dari judul
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $converted = @iconv('utf-8', 'us-ascii//TRANSLIT', (string)$text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = preg_replace('~[^-\w]+~', '', (string)$text);
    $text = trim((string)$text, '-');
    $text = preg_replace('~-+~', '-', (string)$text);
    $text = strtolower((string)$text);
    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Alias untuk slugify() yang dipanggil oleh admin/halaman.php, admin/menu.php, dan admin/tambah-layanan.php
 */
function create_slug(string $text): string {
    return slugify($text);
}

/**
 * Catat aktivitas penting ke tabel audit_logs
 */
function log_audit(PDO $pdo, string $action, string $details = ''): void {
    try {
        $user_id = $_SESSION['admin_id'] ?? null;
        $username = $_SESSION['admin_username'] ?? 'Guest/Public';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, username, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $username, $action, $details, $ip, $ua]);
    } catch (Exception $e) {
        // Jangan hentikan alur utama jika logging gagal
    }
}

/**
 * Daftar halaman standar wajib (Default Pages) untuk seluruh menu website publik
 */
function get_default_system_pages(): array {
    return [
        'beranda' => [
            'title' => 'System Integrator, Security Solution & Internet Provider',
            'slug' => 'beranda',
            'summary' => 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.',
            'content' => '<p>PT. Kreatif Global Solusindo (KGS) menghadirkan Product & Service terintegrasi: Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), serta Internet Service Provider resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet).</p>',
            'meta_title' => 'Beranda - System Integrator, Security Solution & Internet Provider',
            'meta_description' => 'Creative Com / PT. Kreatif Global Solusindo: System Integrator, Security Solution & Internet Provider di Palembang.',
            'sort_order' => 1
        ],
        'tentang-kami' => [
            'title' => 'Tentang PT. Kreatif Global Solusindo',
            'slug' => 'tentang-kami',
            'summary' => 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.',
            'content' => '<p>Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.</p>',
            'meta_title' => 'Tentang Kami - Profile PT. Kreatif Global Solusindo',
            'meta_description' => 'Profile, Visi, Budaya, dan Komitmen PT. Kreatif Global Solusindo.',
            'sort_order' => 2
        ],
        'layanan' => [
            'title' => 'Produk & Layanan',
            'slug' => 'layanan',
            'summary' => 'Security, Networking, PBX, Telecommunication (LIGHTNING PROTECTOR & Tower), serta Internet Service Provider berizin resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet).',
            'content' => '<p>Katalog lengkap Produk & Layanan PT. Kreatif Global Solusindo meliputi Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), dan Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet).</p>',
            'meta_title' => 'Produk & Layanan - PT. Kreatif Global Solusindo',
            'meta_description' => 'Produk & Layanan PT. Kreatif Global Solusindo untuk Security, Networking, PBX, Telecommunication, dan Internet Service Provider.',
            'sort_order' => 3
        ],
        'promo' => [
            'title' => 'Promo & Penawaran Spesial Layanan',
            'slug' => 'promo',
            'summary' => 'Manfaatkan program promo instalasi jaringan Fiber Optik KGSNet, paket bundling CCTV & Access Door korporasi, serta inspeksi sistem proteksi petir & grounding gratis untuk perusahaan Anda.',
            'content' => '<p>PT. Kreatif Global Solusindo menghadirkan program promo berkala untuk instalasi Internet Dedicated & Internet Bisnis KGSNet, paket sistem keamanan CCTV & Access Door terintegrasi, modernisasi IP-PBX kantor, serta audit tahanan grounding & penangkal petir (&lt; 2 Ohm).</p>',
            'meta_title' => 'Promo & Penawaran Spesial - PT. Kreatif Global Solusindo',
            'meta_description' => 'Promo spesial layanan Internet ISP KGSNet, CCTV, IP-PBX, dan Lightning Protector PT. Kreatif Global Solusindo.',
            'sort_order' => 4
        ],
        'kemitraan' => [
            'title' => 'Kemitraan & Aliansi Teknologi Strategis',
            'slug' => 'kemitraan',
            'summary' => 'PT. Kreatif Global Solusindo menjalin kemitraan resmi dengan prinsipal teknologi global dan asosiasi penyelenggara telekomunikasi nasional serta membuka peluang kolaborasi proyek B2B & B2G.',
            'content' => '<p>Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Kami bekerjasama dengan berbagai vendor prinsipal perangkat telekomunikasi, jaringan, security system, dan penyelenggara infrastruktur nasional.</p>',
            'meta_title' => 'Kemitraan & Partner Teknologi - PT. Kreatif Global Solusindo',
            'meta_description' => 'Kemitraan strategis dan partner prinsipal teknologi PT. Kreatif Global Solusindo.',
            'sort_order' => 5
        ],
        'testimoni' => [
            'title' => 'Testimoni & Kepercayaan Pelanggan',
            'slug' => 'testimoni',
            'summary' => 'Pengalaman nyata dari jajaran manajemen IT, operasional pabrik, rumah sakit, perhotelan, dan instansi pemerintahan yang mempercayakan infrastruktur teknologinya kepada PT. Kreatif Global Solusindo.',
            'content' => '<p>Kepuasan Pelanggan adalah target utama dan promosi yang efektif. Berikut adalah ulasan dan testimoni langsung dari para pelanggan korporasi, instansi pemerintah, BUMN, dan sektor swasta yang telah merasakan kualitas layanan dan dukungan teknis PT. Kreatif Global Solusindo sejak 1997.</p>',
            'meta_title' => 'Testimoni Pelanggan - PT. Kreatif Global Solusindo',
            'meta_description' => 'Ulasan dan testimoni kepuasan pelanggan korporasi, BUMN, dan instansi terhadap layanan PT. Kreatif Global Solusindo.',
            'sort_order' => 6
        ],
        'harga' => [
            'title' => 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya',
            'slug' => 'harga',
            'summary' => 'Daftar paket berlangganan 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, dan Home Internet) berizin resmi Kominfo RI, serta solusi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication) berbasis konsultasi teknis & survei lokasi.',
            'content' => '<p>Layanan Internet Service Provider PT. Kreatif Global Solusindo (Izin Kominfo RI Nomor: 674/TEL.02.02/2020, SKLO Nomor: 375/TEL.04.02/2020, NIB: 9120202100598) tersedia dalam 3 Segmen utama: Internet Dedicated, Internet Bisnis, dan Home Internet, dilengkapi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication).</p>',
            'meta_title' => 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya - PT. KGS',
            'meta_description' => 'Daftar paket 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet) serta solusi segmen Security, Networking, PBX, dan Telecommunication PT. Kreatif Global Solusindo.',
            'sort_order' => 7
        ],
        'faq' => [
            'title' => 'Pertanyaan Umum & Jawaban Teknis',
            'slug' => 'faq',
            'summary' => 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.',
            'content' => '<p>Temukan jawaban teknis mengenai jangkauan layanan operasional Sumatera hingga nasional, lisensi resmi ISP KGSNet Kominfo RI & APJII, prosedur survei lapangan, serta garansi pengujian tahanan pembumian di bawah 2 Ohm.</p>',
            'meta_title' => 'Frequently Asked Questions (FAQ) - PT. KGS',
            'meta_description' => 'Pertanyaan umum & jawaban teknis seputar layanan ISP KGSNet, grounding, tower, dan tender B2B.',
            'sort_order' => 8
        ],
        'portofolio' => [
            'title' => 'Client / Pelanggan & Portofolio Proyek',
            'slug' => 'portofolio',
            'summary' => 'Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.',
            'content' => '<p>Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.</p><p>Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;</p>',
            'meta_title' => 'Client / Pelanggan & Portofolio Proyek - PT. KGS',
            'meta_description' => 'Rekam jejak proyek lapangan dan daftar klien terverifikasi PT. Kreatif Global Solusindo.',
            'sort_order' => 9
        ],
        'kontak' => [
            'title' => 'Hubungi Kami — PT. Kreatif Global Solusindo',
            'slug' => 'kontak',
            'summary' => 'Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.',
            'content' => '<p>Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, penjadwalan survei lokasi (on-site survey), serta pengajuan proposal penawaran harga resmi.</p>',
            'meta_title' => 'Hubungi Kami & Permintaan Penawaran - PT. KGS',
            'meta_description' => 'Hubungi Alamat Kantor PT. Kreatif Global Solusindo.',
            'sort_order' => 10
        ],
        'blog' => [
            'title' => 'Artikel Solusi Teknologi & Praktik Terbaik Industri',
            'slug' => 'blog',
            'summary' => 'Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.',
            'content' => '<p>Pusat edukasi dan publikasi artikel teknis dari tim engineer PT. Kreatif Global Solusindo seputar infrastruktur jaringan serat optik, proteksi petir industri, telekomunikasi radio, dan navigasi maritim.</p>',
            'meta_title' => 'Artikel & Wawasan Teknologi - PT. KGS',
            'meta_description' => 'Kumpulan artikel edukasi teknologi informasi dan telekomunikasi industri PT. KGS.',
            'sort_order' => 11
        ],
        'profil-perusahaan' => [
            'title' => 'Profile Perusahaan',
            'slug' => 'profil-perusahaan',
            'summary' => 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo.',
            'content' => '<h2>Profile</h2><p>Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.</p>',
            'meta_title' => 'Profile Perusahaan - PT. KGS',
            'meta_description' => 'Profile resmi PT. Kreatif Global Solusindo dari tahun 1997 hingga kini.',
            'sort_order' => 12
        ],
        'visi-misi-budaya' => [
            'title' => 'Visi, Budaya & Komitmen',
            'slug' => 'visi-misi-budaya',
            'summary' => 'Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan PT. Kreatif Global Solusindo.',
            'content' => '<h2>VISI</h2><p>Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.</p><h2>BUDAYA</h2><p>Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.</p><h2>KOMITMEN</h2><p>Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.</p>',
            'meta_title' => 'Visi, Budaya & Komitmen - PT. KGS',
            'meta_description' => 'Visi, Budaya kerja fungsional, dan Komitmen kepuasan pelanggan PT. KGS.',
            'sort_order' => 13
        ],
        'kebijakan-privasi' => [
            'title' => 'Kebijakan Privasi Pengguna',
            'slug' => 'kebijakan-privasi',
            'summary' => 'Komitmen PT. Kreatif Global Solusindo dalam menjaga kerahasiaan dan keamanan data pelanggan, rekanan, dan calon klien.',
            'content' => '<h2>1. Pengumpulan Informasi</h2><p>PT. Kreatif Global Solusindo (KGS) menghormati dan melindungi privasi setiap pengunjung situs dan pelanggan kami. Informasi yang dikumpulkan melalui formulir konsultasi, permintaan penawaran harga, atau korespondensi langsung (seperti nama, nama perusahaan, nomor telepon, alamat email, dan lokasi proyek) hanya digunakan untuk kepentingan verifikasi teknis, penyusunan proposal solusi, dan komunikasi resmi perusahaan.</p><h2>2. Keamanan Data Pelanggan</h2><p>Kami menerapkan prosedur teknis dan manajemen yang ketat untuk mengamankan data Anda dari akses tanpa izin, perubahan, atau pengungkapan yang tidak sah. Seluruh data penawaran harga disimpan dalam server yang aman dengan enkripsi standar industri.</p><h2>3. Tidak Ada Pembagian Data kepada Pihak Ketiga</h2><p>Kami tidak akan menjual, menyewakan, atau membagikan informasi kontak pribadi atau data korporasi Anda kepada pihak ketiga manapun untuk tujuan pemasaran komersial tanpa persetujuan eksplisit dari Anda, kecuali jika diwajibkan oleh peraturan hukum yang berlaku di Republik Indonesia.</p><h2>4. Hubungi Kami</h2><p>Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: <strong>info@creative.co.id</strong> atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.</p>',
            'meta_title' => 'Kebijakan Privasi - PT. KGS',
            'meta_description' => 'Kebijakan privasi perlindungan data pengguna PT. Kreatif Global Solusindo.',
            'sort_order' => 14
        ],
        'klien' => [
            'title' => 'Client / Pelanggan & Portofolio Proyek',
            'slug' => 'klien',
            'summary' => 'Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.',
            'content' => '<p>Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;</p>',
            'meta_title' => 'Client / Pelanggan & Portofolio Proyek - PT. KGS',
            'meta_description' => 'Daftar klien dan mitra industri PT. Kreatif Global Solusindo (KGS) sejak 1997.',
            'sort_order' => 15
        ],
        'slideshow' => [
            'title' => 'Slide Show Infrastruktur & Solusi Teknologi',
            'slug' => 'slideshow',
            'summary' => 'Galeri interaktif dan penjelasan teknis mendalam mengenai infrastruktur telekomunikasi, jaringan serat optik, menara komunikasi, proteksi petir, IP-PBX, wireless, dan sistem keamanan akses PT. Kreatif Global Solusindo.',
            'content' => '<p>Jelajahi setiap kategori infrastruktur di bawah ini untuk melihat dokumentasi multi-gambar (Slide Show / Galeri Foto) beserta spesifikasi dan keunggulan teknis dari solusi yang dihadirkan oleh PT. Kreatif Global Solusindo (Creative.co.id).</p>',
            'meta_title' => 'Slide Show Infrastruktur & Teknologi - PT. Kreatif Global Solusindo',
            'meta_description' => 'Galeri Slide Show dokumentasi dan spesifikasi teknis Self Supporting Tower Monopole, Fiber Optik, Guy Wire Tower, Lightning Protector, IP-PBX, Wireless, dan Access Door.',
            'sort_order' => 16
        ]
    ];
}

/**
 * Pastikan seluruh halaman sistem tersedia di tabel `pages` beserta kolom lengkapnya
 */
function ensure_pages_complete(PDO $pdo): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `pages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(200) NOT NULL,
            `slug` VARCHAR(220) NOT NULL UNIQUE,
            `summary` TEXT DEFAULT NULL,
            `content` LONGTEXT NOT NULL,
            `featured_image` VARCHAR(255) DEFAULT NULL,
            `meta_title` VARCHAR(255) DEFAULT NULL,
            `meta_description` TEXT DEFAULT NULL,
            `og_image` VARCHAR(255) DEFAULT NULL,
            `is_published` TINYINT(1) DEFAULT 1,
            `sort_order` INT DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `pages`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'summary'          => "ADD COLUMN `summary` TEXT DEFAULT NULL AFTER `slug`",
            'featured_image'   => "ADD COLUMN `featured_image` VARCHAR(255) DEFAULT NULL",
            'meta_title'       => "ADD COLUMN `meta_title` VARCHAR(255) DEFAULT NULL",
            'meta_description' => "ADD COLUMN `meta_description` TEXT DEFAULT NULL",
            'og_image'         => "ADD COLUMN `og_image` VARCHAR(255) DEFAULT NULL",
            'is_published'     => "ADD COLUMN `is_published` TINYINT(1) DEFAULT 1",
            'sort_order'       => "ADD COLUMN `sort_order` INT DEFAULT 0",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `pages` " . $ddl);
                } catch (Exception $e) {}
            }
        }

        $defaults = get_default_system_pages();
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
        $stmt_ins = $pdo->prepare("INSERT INTO pages (title, slug, summary, content, meta_title, meta_description, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");

        foreach ($defaults as $slug => $p) {
            $stmt_check->execute([$slug]);
            if ((int)$stmt_check->fetchColumn() === 0) {
                $stmt_ins->execute([
                    $p['title'],
                    $p['slug'],
                    $p['summary'],
                    $p['content'],
                    $p['meta_title'],
                    $p['meta_description'],
                    $p['sort_order']
                ]);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika tabel pages belum dibuat
    }
}

function ensure_default_pages(PDO $pdo): void {
    ensure_pages_complete($pdo);
}

function mark_db_modified(PDO $pdo): void {
    clear_settings_cache();
}

/**
 * Ambil data halaman dari tabel `pages` berdasarkan slug (dengan fallback aman).
 * Database (`pages`) adalah sumber utama sehingga edit di Admin CMS langsung tampil di website.
 */
function get_page_by_slug(PDO $pdo, string $slug, array $fallback = []): array {
    $defaults = get_default_system_pages();
    $base_fallback = $defaults[$slug] ?? [
        'title' => $fallback['title'] ?? ucfirst(str_replace('-', ' ', $slug)),
        'slug' => $slug,
        'summary' => $fallback['summary'] ?? '',
        'content' => $fallback['content'] ?? '',
        'meta_title' => $fallback['meta_title'] ?? '',
        'meta_description' => $fallback['meta_description'] ?? '',
        'is_published' => 1
    ];

    try {
        ensure_pages_complete($pdo);
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if ($row) {
            return [
                'id'               => $row['id'],
                'title'            => !empty($row['title']) ? $row['title'] : $base_fallback['title'],
                'slug'             => $row['slug'],
                'summary'          => ($row['summary'] !== null && $row['summary'] !== '') ? $row['summary'] : $base_fallback['summary'],
                'content'          => ($row['content'] !== null && $row['content'] !== '') ? $row['content'] : $base_fallback['content'],
                'meta_title'       => !empty($row['meta_title']) ? $row['meta_title'] : $base_fallback['meta_title'],
                'meta_description' => !empty($row['meta_description']) ? $row['meta_description'] : $base_fallback['meta_description'],
                'is_published'     => (int)($row['is_published'] ?? 1)
            ];
        }
    } catch (Exception $e) {
        // Fallback jika query gagal
    }

    // Fallback ke state Live Preview JSON jika DB belum tersedia
    $live = load_live_preview_state();
    if (!empty($live['pages'][$slug]) && is_array($live['pages'][$slug])) {
        $lp = $live['pages'][$slug];
        return [
            'id'               => $lp['id'] ?? 0,
            'title'            => !empty($lp['title']) ? $lp['title'] : $base_fallback['title'],
            'slug'             => $slug,
            'summary'          => !empty($lp['summary']) ? $lp['summary'] : $base_fallback['summary'],
            'content'          => !empty($lp['content']) ? $lp['content'] : $base_fallback['content'],
            'meta_title'       => !empty($lp['meta_title']) ? $lp['meta_title'] : $base_fallback['meta_title'],
            'meta_description' => !empty($lp['meta_description']) ? $lp['meta_description'] : $base_fallback['meta_description'],
            'is_published'     => (int)($lp['is_published'] ?? 1)
        ];
    }

    return $base_fallback;
}

/**
 * Daftar menu navigasi default untuk Header & Footer
 * Urutan 7 Menu Header: Beranda, Produk & Layanan, Promo, Client / Pelanggan, Kemitraan, Testimoni, Hubungi Kami
 */
function get_default_navigation_menus(): array {
    return [
        ['id' => 1,  'title' => 'Beranda',            'url' => 'index.php',                        'location' => 'header', 'sort_order' => 1, 'target' => '_self', 'is_active' => 1],
        ['id' => 2,  'title' => 'Produk & Layanan',   'url' => 'layanan.php',                      'location' => 'header', 'sort_order' => 2, 'target' => '_self', 'is_active' => 1],
        ['id' => 3,  'title' => 'Promo',              'url' => 'promo.php',                        'location' => 'header', 'sort_order' => 3, 'target' => '_self', 'is_active' => 1],
        ['id' => 4,  'title' => 'Client / Pelanggan', 'url' => 'klien.php',                        'location' => 'header', 'sort_order' => 4, 'target' => '_self', 'is_active' => 1],
        ['id' => 5,  'title' => 'Kemitraan',          'url' => 'kemitraan.php',                    'location' => 'header', 'sort_order' => 5, 'target' => '_self', 'is_active' => 1],
        ['id' => 6,  'title' => 'Testimoni',          'url' => 'testimoni.php',                    'location' => 'header', 'sort_order' => 6, 'target' => '_self', 'is_active' => 1],
        ['id' => 7,  'title' => 'Hubungi Kami',       'url' => 'kontak.php',                       'location' => 'header', 'sort_order' => 7, 'target' => '_self', 'is_active' => 1],
        ['id' => 8,  'title' => 'Profil Perusahaan',  'url' => 'page.php?slug=profil-perusahaan',  'location' => 'footer', 'sort_order' => 1, 'target' => '_self', 'is_active' => 1],
        ['id' => 9,  'title' => 'Visi & Misi',        'url' => 'page.php?slug=visi-misi-budaya',   'location' => 'footer', 'sort_order' => 2, 'target' => '_self', 'is_active' => 1],
        ['id' => 10, 'title' => 'Product & Service',  'url' => 'layanan.php',                      'location' => 'footer', 'sort_order' => 3, 'target' => '_self', 'is_active' => 1],
        ['id' => 11, 'title' => 'Artikel Edukasi',    'url' => 'blog.php',                         'location' => 'footer', 'sort_order' => 4, 'target' => '_self', 'is_active' => 1],
        ['id' => 12, 'title' => 'Kebijakan Privasi',  'url' => 'kebijakan-privasi.php',            'location' => 'footer', 'sort_order' => 5, 'target' => '_self', 'is_active' => 1],
    ];
}

/**
 * Pastikan tabel `menus` tersedia dan memiliki seluruh kolom kompatibel (`location`, `menu_location`, `page_slug`)
 */
function ensure_menus_table(PDO $pdo): void {
    static $table_checked = false;
    if (!$table_checked) {
        $table_checked = true;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `menus` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(100) NOT NULL,
                `url` VARCHAR(255) NOT NULL,
                `page_slug` VARCHAR(120) DEFAULT NULL,
                `location` ENUM('header', 'footer') DEFAULT 'header',
                `menu_location` VARCHAR(30) DEFAULT 'header',
                `parent_id` INT DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `target` ENUM('_self', '_blank') DEFAULT '_self',
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $cols = [];
            $stmt_cols = $pdo->query("SHOW COLUMNS FROM `menus`");
            while ($c = $stmt_cols->fetch()) {
                $cols[strtolower($c['Field'])] = true;
            }
            $alter_map = [
                'title'         => "ADD COLUMN `title` VARCHAR(100) NOT NULL DEFAULT ''",
                'url'           => "ADD COLUMN `url` VARCHAR(255) NOT NULL DEFAULT 'index.php'",
                'page_slug'     => "ADD COLUMN `page_slug` VARCHAR(120) DEFAULT NULL",
                'location'      => "ADD COLUMN `location` VARCHAR(30) DEFAULT 'header'",
                'menu_location' => "ADD COLUMN `menu_location` VARCHAR(30) DEFAULT 'header'",
                'parent_id'     => "ADD COLUMN `parent_id` INT DEFAULT NULL",
                'sort_order'    => "ADD COLUMN `sort_order` INT DEFAULT 0",
                'target'        => "ADD COLUMN `target` VARCHAR(20) DEFAULT '_self'",
                'is_active'     => "ADD COLUMN `is_active` TINYINT(1) DEFAULT 1",
            ];
            foreach ($alter_map as $col_name => $ddl) {
                if (!isset($cols[$col_name])) {
                    try {
                        $pdo->exec("ALTER TABLE `menus` " . $ddl);
                    } catch (Exception $e) {}
                }
            }
            if (isset($cols['label'])) {
                try {
                    $pdo->exec("UPDATE `menus` SET `title` = `label` WHERE (`title` IS NULL OR TRIM(`title`) = '') AND `label` IS NOT NULL AND TRIM(`label`) != ''");
                } catch (Exception $e) {}
            }
            // Sinkronkan nilai kolom location & menu_location secara aman dan hapus duplikat menu
            try {
                $pdo->exec("UPDATE `menus` SET `menu_location` = `location` WHERE (`menu_location` IS NULL OR `menu_location` = '') AND `location` IN ('header', 'footer')");
            } catch (Exception $e) {}
            try {
                $pdo->exec("UPDATE `menus` SET `location` = `menu_location` WHERE `menu_location` IN ('header', 'footer')");
            } catch (Exception $e) {}
            try {
                // Hapus baris menu kosong dan tautan login Admin CMS / Slide Show dari menu navigasi publik
                $pdo->exec("DELETE FROM `menus` WHERE `title` IS NULL OR TRIM(`title`) = '' OR LOWER(TRIM(`title`)) IN ('admin cms', 'login admin', 'portal admin', 'slide show', 'slideshow') OR `url` LIKE '%admin/login.php%' OR `url` LIKE '%slideshow.php%' OR `page_slug` = 'slideshow'");

                // Jika menu Header masih menggunakan skema lama (belum ada Promo / Kemitraan / Testimoni), perbarui menjadi 7 menu Header baru
                $has_promo_menu = (int)$pdo->query("SELECT COUNT(*) FROM `menus` WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) = 'promo'")->fetchColumn();
                if ($has_promo_menu === 0) {
                    $pdo->exec("DELETE FROM `menus` WHERE `location` = 'header' OR `menu_location` = 'header'");
                }

                // Pastikan URL untuk 7 menu Header selalu mengarah ke file .php yang benar dan aktif (tanpa #)
                $pdo->exec("UPDATE `menus` SET `url` = 'index.php', `page_slug` = 'beranda', `location` = 'header', `menu_location` = 'header', `sort_order` = 1, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) = 'beranda'");
                $pdo->exec("UPDATE `menus` SET `title` = 'Produk & Layanan', `url` = 'layanan.php', `page_slug` = 'layanan', `sort_order` = 2, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) IN ('produk & layanan', 'product & service', 'layanan', 'katalog layanan')");
                $pdo->exec("UPDATE `menus` SET `url` = 'promo.php', `page_slug` = 'promo', `location` = 'header', `menu_location` = 'header', `sort_order` = 3, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) = 'promo'");
                $pdo->exec("UPDATE `menus` SET `title` = 'Client / Pelanggan', `url` = 'klien.php', `page_slug` = 'klien', `location` = 'header', `menu_location` = 'header', `sort_order` = 4, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) IN ('client / pelanggan', 'klien', 'pelanggan', 'portofolio', 'portfolio')");
                $pdo->exec("UPDATE `menus` SET `url` = 'kemitraan.php', `page_slug` = 'kemitraan', `location` = 'header', `menu_location` = 'header', `sort_order` = 5, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) = 'kemitraan'");
                $pdo->exec("UPDATE `menus` SET `url` = 'testimoni.php', `page_slug` = 'testimoni', `location` = 'header', `menu_location` = 'header', `sort_order` = 6, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) = 'testimoni'");
                $pdo->exec("UPDATE `menus` SET `title` = 'Hubungi Kami', `url` = 'kontak.php', `page_slug` = 'kontak', `location` = 'header', `menu_location` = 'header', `sort_order` = 7, `is_active` = 1 WHERE (`location` = 'header' OR `menu_location` = 'header') AND LOWER(TRIM(`title`)) IN ('hubungi kami', 'kontak')");

                // Pastikan URL untuk 5 menu Footer selalu mengarah ke halaman yang benar dan aktif (tanpa #)
                $pdo->exec("UPDATE `menus` SET `url` = 'page.php?slug=profil-perusahaan', `page_slug` = 'profil-perusahaan', `location` = 'footer', `menu_location` = 'footer', `sort_order` = 1, `is_active` = 1 WHERE LOWER(TRIM(`title`)) IN ('profil perusahaan', 'profile perusahaan')");
                $pdo->exec("UPDATE `menus` SET `url` = 'page.php?slug=visi-misi-budaya', `page_slug` = 'visi-misi-budaya', `location` = 'footer', `menu_location` = 'footer', `sort_order` = 2, `is_active` = 1 WHERE LOWER(TRIM(`title`)) IN ('visi & misi', 'visi dan misi', 'visi, budaya & komitmen')");
                $pdo->exec("UPDATE `menus` SET `url` = 'layanan.php', `page_slug` = 'layanan', `location` = 'footer', `menu_location` = 'footer', `sort_order` = 3, `is_active` = 1 WHERE (`location` = 'footer' OR `menu_location` = 'footer') AND LOWER(TRIM(`title`)) IN ('product & service', 'produk & layanan', 'layanan')");
                $pdo->exec("UPDATE `menus` SET `url` = 'blog.php', `page_slug` = 'blog', `location` = 'footer', `menu_location` = 'footer', `sort_order` = 4, `is_active` = 1 WHERE LOWER(TRIM(`title`)) IN ('artikel edukasi', 'artikel', 'blog')");
                $pdo->exec("UPDATE `menus` SET `url` = 'kebijakan-privasi.php', `page_slug` = 'kebijakan-privasi', `location` = 'footer', `menu_location` = 'footer', `sort_order` = 5, `is_active` = 1 WHERE LOWER(TRIM(`title`)) IN ('kebijakan privasi', 'kebijakan privasi & sla')");

                // Hapus baris menu yang duplikat pada lokasi yang sama
                $all_menu_rows = $pdo->query("SELECT `id`, `title`, `location`, `menu_location` FROM `menus` ORDER BY `id` ASC")->fetchAll();
                $seen_menu_keys = [];
                $dup_ids = [];
                foreach ($all_menu_rows as $mr) {
                    $m_loc = (!empty($mr['location']) && in_array($mr['location'], ['header', 'footer'], true))
                        ? $mr['location']
                        : ((!empty($mr['menu_location']) && in_array($mr['menu_location'], ['header', 'footer'], true)) ? $mr['menu_location'] : 'header');
                    $m_key = $m_loc . ':' . strtolower(trim((string)($mr['title'] ?? '')));
                    if (isset($seen_menu_keys[$m_key])) {
                        $dup_ids[] = (int)$mr['id'];
                    } else {
                        $seen_menu_keys[$m_key] = true;
                    }
                }
                if (!empty($dup_ids)) {
                    $in_placeholders = implode(',', array_map('intval', $dup_ids));
                    $pdo->exec("DELETE FROM `menus` WHERE `id` IN ($in_placeholders)");
                }
            } catch (Exception $e) {}
        } catch (Exception $e) {
            // Abaikan jika gagal membuat tabel
        }
    }
    ensure_menus_seeded($pdo);
}

/**
 * Pastikan tabel `menus` terisi dengan seluruh menu utama Header & Footer jika belum lengkap
 */
function ensure_menus_seeded(PDO $pdo): void {
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    try {
        $existing_rows = $pdo->query("SELECT `id`, `title`, `url`, `location`, `menu_location` FROM `menus`")->fetchAll();
        $existing_keys = [];
        $upd_url_stmt = $pdo->prepare("UPDATE `menus` SET `url` = ?, `page_slug` = ?, `is_active` = 1 WHERE `id` = ?");
        foreach ($existing_rows as $er) {
            $loc = (!empty($er['location']) && in_array($er['location'], ['header', 'footer'], true))
                ? $er['location']
                : ((!empty($er['menu_location']) && in_array($er['menu_location'], ['header', 'footer'], true)) ? $er['menu_location'] : 'header');
            $t_str = trim((string)($er['title'] ?? ''));
            $u_str = trim((string)($er['url'] ?? ''));
            $norm_u = normalize_core_menu_url($t_str, $u_str);
            if ($u_str === '' || strpos($u_str, '#') === 0 || $u_str !== $norm_u) {
                $upd_url_stmt->execute([$norm_u, resolve_url_to_page_slug($norm_u), (int)$er['id']]);
            }
            $existing_keys[$loc . ':' . strtolower($t_str)] = true;
        }

        $stmt = $pdo->prepare("INSERT INTO menus (title, url, page_slug, location, menu_location, sort_order, target, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach (get_default_navigation_menus() as $m) {
            $key = $m['location'] . ':' . strtolower(trim($m['title']));
            if (!isset($existing_keys[$key])) {
                $slug = resolve_url_to_page_slug($m['url']);
                $stmt->execute([$m['title'], $m['url'], $slug, $m['location'], $m['location'], $m['sort_order'], $m['target'], $m['is_active']]);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika tabel menus belum siap
    }
}

/**
 * Normalisasi URL untuk seluruh halaman utama Header & Footer
 * agar selalu mengarah ke file .php yang valid dan dapat diklik.
 */
function normalize_core_menu_url(string $title, string $url, ?string $page_slug = null): string {
    $t = strtolower(trim($title));
    $u = strtolower(trim($url));
    $s = strtolower(trim((string)$page_slug));

    if ($t === 'beranda' || $t === 'home' || $s === 'beranda' || in_array($u, ['index.php', 'index', 'beranda', 'beranda.php', '#beranda', 'page.php?slug=beranda'], true)) {
        return 'index.php';
    }
    if ($t === 'tentang kami' || $t === 'tentang' || $s === 'tentang-kami' || in_array($u, ['tentang.php', 'tentang', 'tentang-kami', 'tentang-kami.php', '#tentang', '#tentang-kami', 'page.php?slug=tentang-kami', 'page.php?slug=tentang'], true)) {
        return 'tentang.php';
    }
    if (in_array($t, ['produk & layanan', 'produk dan layanan', 'product & service', 'product and service', 'layanan', 'katalog layanan'], true) || in_array($s, ['layanan', 'product-service'], true) || in_array($u, ['layanan.php', 'layanan', 'product-service', 'product-service.php', '#layanan', 'page.php?slug=layanan', 'page.php?slug=product-service'], true)) {
        return 'layanan.php';
    }
    if ($t === 'promo' || $t === 'promosi' || $s === 'promo' || in_array($u, ['promo.php', 'promo', '#promo', 'page.php?slug=promo'], true)) {
        return 'promo.php';
    }
    if (in_array($t, ['client / pelanggan', 'client & pelanggan', 'klien', 'pelanggan'], true) || $s === 'klien' || in_array($u, ['klien.php', 'klien', '#klien', 'page.php?slug=klien'], true)) {
        return 'klien.php';
    }
    if ($t === 'kemitraan' || $t === 'partner' || $t === 'mitra' || $s === 'kemitraan' || in_array($u, ['kemitraan.php', 'kemitraan', '#kemitraan', 'page.php?slug=kemitraan'], true)) {
        return 'kemitraan.php';
    }
    if ($t === 'testimoni' || $t === 'testimonial' || $s === 'testimoni' || in_array($u, ['testimoni.php', 'testimoni', '#testimoni', 'page.php?slug=testimoni'], true)) {
        return 'testimoni.php';
    }
    if ($t === 'harga' || $t === 'paket harga' || strpos($t, '3 segmen paket internet') !== false || $s === 'harga' || in_array($u, ['harga.php', 'harga', '#harga', 'page.php?slug=harga'], true)) {
        return 'harga.php';
    }
    if ($t === 'faq' || strpos($t, 'pertanyaan umum') !== false || $s === 'faq' || in_array($u, ['faq.php', 'faq', '#faq', 'page.php?slug=faq'], true)) {
        return 'faq.php';
    }
    if ($t === 'portofolio' || $t === 'portfolio' || $s === 'portofolio' || in_array($u, ['portofolio.php', 'portofolio', 'portfolio', 'portfolio.php', '#portofolio', 'page.php?slug=portofolio'], true)) {
        return 'portofolio.php';
    }
    if ($t === 'kontak' || $t === 'hubungi kami' || $s === 'kontak' || in_array($u, ['kontak.php', 'kontak', '#kontak', 'page.php?slug=kontak'], true)) {
        return 'kontak.php';
    }
    if ($t === 'profil perusahaan' || $t === 'profile perusahaan' || $s === 'profil-perusahaan' || strpos($u, 'profil-perusahaan') !== false) {
        return 'page.php?slug=profil-perusahaan';
    }
    if ($t === 'visi & misi' || $t === 'visi dan misi' || strpos($t, 'visi') !== false || $s === 'visi-misi-budaya' || strpos($u, 'visi-misi-budaya') !== false) {
        return 'page.php?slug=visi-misi-budaya';
    }
    if ($t === 'artikel edukasi' || $t === 'artikel' || $t === 'blog' || $s === 'blog' || in_array($u, ['blog.php', 'blog', '#artikel', '#blog', 'page.php?slug=blog'], true)) {
        return 'blog.php';
    }
    if ($t === 'kebijakan privasi' || $t === 'kebijakan privasi & sla' || strpos($t, 'kebijakan privasi') !== false || $s === 'kebijakan-privasi' || in_array($u, ['kebijakan-privasi.php', 'kebijakan-privasi', 'page.php?slug=kebijakan-privasi'], true)) {
        return 'kebijakan-privasi.php';
    }
    if (strpos($t, 'minta penawaran') !== false || strpos($t, 'survei') !== false || $u === '#konsultasi' || $u === 'kontak.php#konsultasi') {
        return 'kontak.php#konsultasi';
    }
    if (strpos($t, 'peta lokasi') !== false || strpos($t, 'google maps') !== false || $u === '#peta-lokasi' || $u === 'kontak.php#peta-lokasi') {
        return 'kontak.php#peta-lokasi';
    }

    return trim($url) !== '' && trim($url) !== '#' ? trim($url) : 'index.php';
}

/**
 * Konversi slug halaman menjadi URL rute publik yang sesuai
 */
function resolve_page_slug_to_url(string $slug): string {
    $clean = strtolower(trim($slug));
    $map = [
        'beranda'           => 'index.php',
        'tentang-kami'      => 'tentang.php',
        'tentang'           => 'tentang.php',
        'layanan'           => 'layanan.php',
        'product-service'   => 'layanan.php',
        'promo'             => 'promo.php',
        'klien'             => 'klien.php',
        'kemitraan'         => 'kemitraan.php',
        'testimoni'         => 'testimoni.php',
        'slideshow'         => 'slideshow.php',
        'harga'             => 'harga.php',
        'faq'               => 'faq.php',
        'portofolio'        => 'portofolio.php',
        'portfolio'         => 'portofolio.php',
        'kontak'            => 'kontak.php',
        'blog'              => 'blog.php',
        'kebijakan-privasi' => 'kebijakan-privasi.php',
    ];
    if (isset($map[$clean])) {
        return $map[$clean];
    }
    return 'page.php?slug=' . rawurlencode($clean);
}

/**
 * Konversi URL menu menjadi slug halaman di tabel `pages` (jika cocok)
 */
function resolve_url_to_page_slug(string $url): ?string {
    $clean = trim($url);
    $reverse_map = [
        'index.php'             => 'beranda',
        'tentang.php'           => 'tentang-kami',
        'layanan.php'           => 'layanan',
        'promo.php'             => 'promo',
        'klien.php'             => 'klien',
        'kemitraan.php'         => 'kemitraan',
        'testimoni.php'         => 'testimoni',
        'slideshow.php'         => 'slideshow',
        'harga.php'             => 'harga',
        'faq.php'               => 'faq',
        'portofolio.php'        => 'portofolio',
        'kontak.php'            => 'kontak',
        'blog.php'              => 'blog',
        'kebijakan-privasi.php' => 'kebijakan-privasi',
    ];
    if (isset($reverse_map[$clean])) {
        return $reverse_map[$clean];
    }
    if (preg_match('/page\.php\?slug=([a-zA-Z0-9\-_]+)/', $clean, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Ambil daftar menu navigasi berdasarkan lokasi ('header', 'footer', atau '' untuk semua).
 * Membaca dari database `menus` terlebih dahulu agar perubahan di Admin CMS langsung tampil,
 * serta otomatis memfilter duplikat agar tidak pernah tampil double.
 */
function get_navigation_menus(PDO $pdo, string $location = 'header', bool $only_active = true): array {
    $loc = ($location === 'footer') ? 'footer' : (($location === '') ? '' : 'header');

    try {
        ensure_menus_table($pdo);
        if ($loc === '') {
            $sql = "SELECT * FROM menus";
            if ($only_active) {
                $sql .= " WHERE is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC, id ASC";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT * FROM menus WHERE (location = ? OR menu_location = ?)";
            if ($only_active) {
                $sql .= " AND is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC, id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$loc, $loc]);
        }
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            $filtered = [];
            $seen_keys = [];
            foreach ($rows as $r) {
                $t_lower = strtolower(trim((string)($r['title'] ?? '')));
                $u_lower = strtolower(trim((string)($r['url'] ?? '')));
                if (
                    in_array($t_lower, ['admin cms', 'login admin', 'portal admin', 'slide show', 'slideshow'], true) ||
                    strpos($u_lower, 'admin/login.php') !== false ||
                    strpos($u_lower, 'slideshow.php') !== false
                ) {
                    continue;
                }
                $resolved_loc = !empty($r['location']) ? $r['location'] : (!empty($r['menu_location']) ? $r['menu_location'] : 'header');
                if ($resolved_loc === 'header' && in_array(trim((string)($r['title'] ?? '')), ['Layanan', 'Katalog Layanan', 'Product & Service'], true)) {
                    $r['title'] = 'Produk & Layanan';
                }
                $dedup_key = $resolved_loc . ':' . strtolower(trim((string)$r['title']));
                if (isset($seen_keys[$dedup_key])) {
                    continue;
                }
                $seen_keys[$dedup_key] = true;

                $r['location'] = $resolved_loc;
                $r['menu_location'] = $resolved_loc;
                $r['url'] = normalize_core_menu_url((string)($r['title'] ?? ''), (string)($r['url'] ?? ''), $r['page_slug'] ?? null);
                if (empty($r['page_slug'])) {
                    $r['page_slug'] = resolve_url_to_page_slug((string)($r['url'] ?? ''));
                }
                $filtered[] = $r;
            }
            if (!empty($filtered)) {
                return $filtered;
            }
        }
    } catch (Exception $e) {
        // Gunakan fallback default
    }

    // Fallback ke Live Preview JSON atau default jika DB tidak tersedia
    $live = load_live_preview_state();
    if (!empty($live['menus']) && is_array($live['menus'])) {
        $raw_list = ($loc !== '' && isset($live['menus'][$loc]) && is_array($live['menus'][$loc]))
            ? $live['menus'][$loc]
            : $live['menus'];

        $items = [];
        $seen_live = [];
        foreach ($raw_list as $m) {
            if (!is_array($m)) {
                continue;
            }
            $m_loc = (string)($m['location'] ?? ($m['menu_location'] ?? 'header'));
            if ($loc !== '' && $m_loc !== $loc) {
                continue;
            }
            $t_lower = strtolower(trim((string)($m['title'] ?? '')));
            $u_lower = strtolower(trim((string)($m['url'] ?? '')));
            if (
                in_array($t_lower, ['admin cms', 'login admin', 'portal admin', 'slide show', 'slideshow'], true) ||
                strpos($u_lower, 'admin/login.php') !== false ||
                strpos($u_lower, 'slideshow.php') !== false
            ) {
                continue;
            }
            $is_act = isset($m['is_active']) ? (bool)$m['is_active'] : (isset($m['isActive']) ? (bool)$m['isActive'] : true);
            if ($only_active && !$is_act) {
                continue;
            }
            $title = (string)($m['title'] ?? '');
            if (in_array(trim($title), ['Layanan', 'Katalog Layanan'], true)) {
                $title = 'Product & Service';
            }
            $dedup_key = $m_loc . ':' . strtolower(trim($title));
            if (isset($seen_live[$dedup_key])) {
                continue;
            }
            $seen_live[$dedup_key] = true;

            $norm_url = normalize_core_menu_url($title, (string)($m['url'] ?? ''), $m['page_slug'] ?? null);
            $items[] = [
                'id'            => (int)($m['id'] ?? 0),
                'title'         => $title,
                'url'           => $norm_url,
                'page_slug'     => resolve_url_to_page_slug($norm_url),
                'location'      => $m_loc,
                'menu_location' => $m_loc,
                'sort_order'    => (int)($m['sort_order'] ?? ($m['sortOrder'] ?? 1)),
                'target'        => (string)($m['target'] ?? '_self'),
                'is_active'     => $is_act ? 1 : 0,
            ];
        }
        usort($items, function ($a, $b) {
            return ((int)($a['sort_order'] ?? 0)) <=> ((int)($b['sort_order'] ?? 0));
        });
        if (!empty($items)) {
            return $items;
        }
    }

    $defaults = array_filter(get_default_navigation_menus(), function ($m) use ($loc, $only_active) {
        if ($loc !== '' && $m['location'] !== $loc) {
            return false;
        }
        if ($only_active && empty($m['is_active'])) {
            return false;
        }
        return true;
    });

    return array_values($defaults);
}

/**
 * Sanitasi href menu / tautan untuk mencegah XSS & Open Redirect berbahaya.
 * Hanya mengizinkan protokol https://, http://, mailto:, tel:, anchor (#), atau path relatif internal.
 */
function sanitize_nav_url(string $url): string {
    $trimmed = trim($url);
    if ($trimmed === '' || $trimmed === '#') {
        return '#';
    }

    // Tolak karakter kontrol, kutip, kurung sudut, backslash, atau upaya traversal
    if (preg_match('/[\x00-\x1F\x7F<>"\'\\\\]/', $trimmed) || strpos($trimmed, '..') !== false) {
        return '#';
    }

    $decoded = html_entity_decode($trimmed, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($decoded)) ?? '';
    if (
        strpos($normalized, 'javascript:') === 0 ||
        strpos($normalized, 'data:') === 0 ||
        strpos($normalized, 'vbscript:') === 0 ||
        strpos($normalized, 'file:') === 0 ||
        strpos($normalized, '//') === 0
    ) {
        return '#';
    }

    if (preg_match('/^#[a-zA-Z0-9\-_]*$/', $trimmed)) {
        return $trimmed;
    }

    if (preg_match('#^https?://#i', $trimmed)) {
        return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : '#';
    }

    if (preg_match('#^(mailto|tel):[a-zA-Z0-9_\-\.\+@\?=&%]+$#i', $trimmed)) {
        return $trimmed;
    }

    // Path relatif internal (tidak mengandung titik dua ':')
    if (strpos($trimmed, ':') === false && preg_match('~^/?[a-zA-Z0-9_\-\/\.\?=&%#]+$~', $trimmed)) {
        return $trimmed;
    }

    return '#';
}

function sanitize_menu_url(?string $url): string {
    return sanitize_nav_url((string)$url);
}

/**
 * Daftar preset tema warna website resmi PT. KGS
 */
function get_website_theme_presets(): array {
    return [
        'corporate-blue' => [
            'id' => 'corporate-blue',
            'name' => 'Corporate Blue (Standar KGS)',
            'description' => 'Tema biru korporasi resmi PT. Kreatif Global Solusindo (Default).',
            'primary_color' => '#1d4ed8',
            'primary_hover' => '#1e40af',
            'hero_bg_color' => '#0b1329',
            'navbar_style' => 'light'
        ],
        'emerald-isp' => [
            'id' => 'emerald-isp',
            'name' => 'KGSNet Emerald Green',
            'description' => 'Nuansa hijau modern khas layanan jaringan ISP KGSNet & Fiber Optic.',
            'primary_color' => '#059669',
            'primary_hover' => '#047857',
            'hero_bg_color' => '#062019',
            'navbar_style' => 'light'
        ],
        'cyber-indigo' => [
            'id' => 'cyber-indigo',
            'name' => 'Cyber Security Indigo',
            'description' => 'Tema teknikal indigo untuk sistem integrasi keamanan, CCTV AI & Data Center.',
            'primary_color' => '#4f46e5',
            'primary_hover' => '#4338ca',
            'hero_bg_color' => '#0f1026',
            'navbar_style' => 'dark'
        ],
        'industrial-amber' => [
            'id' => 'industrial-amber',
            'name' => 'Tower & Mining Amber',
            'description' => 'Aksen tegas untuk infrastruktur pertambangan, perkebunan, dan tower.',
            'primary_color' => '#d97706',
            'primary_hover' => '#b45309',
            'hero_bg_color' => '#18130a',
            'navbar_style' => 'dark'
        ],
        'executive-teal' => [
            'id' => 'executive-teal',
            'name' => 'Executive Telecom Teal',
            'description' => 'Tampilan profesional biru-hijau untuk tender BUMN dan instansi pemerintah.',
            'primary_color' => '#0d9488',
            'primary_hover' => '#0f766e',
            'hero_bg_color' => '#071c1f',
            'navbar_style' => 'light'
        ]
    ];
}

/**
 * Ambil konfigurasi tema aktif website dari `site_settings`
 */
function get_active_website_theme(PDO $pdo): array {
    $presets = get_website_theme_presets();
    $preset_id = get_setting($pdo, 'theme_preset', 'corporate-blue');
    $base = $presets[$preset_id] ?? $presets['corporate-blue'];

    $primary_color = sanitize_hex_color(get_setting($pdo, 'primary_color', $base['primary_color']), $base['primary_color']);
    $hero_bg_color = sanitize_hex_color(get_setting($pdo, 'hero_bg_color', $base['hero_bg_color']), $base['hero_bg_color']);
    $navbar_style  = get_setting($pdo, 'navbar_style', $base['navbar_style']);

    return [
        'preset_id'     => $preset_id,
        'primary_color' => $primary_color,
        'primary_hover' => $base['primary_hover'],
        'hero_bg_color' => $hero_bg_color,
        'navbar_style'  => in_array($navbar_style, ['light', 'dark'], true) ? $navbar_style : 'light'
    ];
}

/**
 * Ambil pengaturan tata letak Slider Beranda (Di Sebelah Teks / Background / Both / NOC)
 * beserta daftar slide aktif dari tabel `hero_sliders` (Default: Daftar Gambar Slider Beranda Saat Ini)
 */
function get_hero_slider_config(PDO $pdo): array {
    $enabled = get_setting($pdo, 'hero_slider_enabled', '1') === '1';
    $layout_mode = get_setting($pdo, 'hero_slider_layout', 'side');
    if (!in_array($layout_mode, ['side', 'background', 'both', 'noc'], true)) {
        $layout_mode = 'side';
    }
    $autoplay = get_setting($pdo, 'hero_slider_autoplay', '1') === '1';
    $interval = max(2, min(15, (int)get_setting($pdo, 'hero_slider_interval', '5')));
    $overlay  = max(20, min(95, (int)get_setting($pdo, 'hero_slider_overlay', '75')));
    $slides   = get_hero_sliders($pdo, true);

    // Jika terdapat slide aktif di Daftar Gambar Slider Beranda Saat Ini, pastikan slider tampil aktif secara default
    if (!empty($slides) && get_setting($pdo, 'hero_slider_enabled', '') === '') {
        $enabled = true;
    }

    return [
        'enabled'          => $enabled,
        'layout'           => $layout_mode,
        'layout_mode'      => $layout_mode,
        'autoplay'         => $autoplay,
        'auto_play'        => $autoplay,
        'interval'         => $interval,
        'interval_seconds' => $interval,
        'overlay_opacity'  => $overlay,
        'slides'           => $slides,
    ];
}

/**
 * Daftar slide default untuk halaman Beranda — menggunakan 8 Kategori dari Halaman Slide Show Infrastruktur (slideshow.php)
 */
function get_default_hero_sliders(): array {
    $live = load_live_preview_state();
    if (!empty($live['hero_sliders']) && is_array($live['hero_sliders']) && count($live['hero_sliders']) > 3) {
        $has_legacy = false;
        foreach ($live['hero_sliders'] as $ls) {
            if (($ls['title'] ?? '') === 'Backbone Fiber Optic & Data Center KGSNet') {
                $has_legacy = true;
                break;
            }
        }
        if (!$has_legacy) {
            $mapped = [];
            foreach ($live['hero_sliders'] as $idx => $s) {
                if (!empty($s['image_url']) || !empty($s['imageUrl'])) {
                    $mapped[] = [
                        'id'          => (int)($s['id'] ?? ($idx + 1)),
                        'title'       => (string)($s['title'] ?? 'Dokumentasi Proyek & Infrastruktur PT. KGS'),
                        'subtitle'    => (string)($s['subtitle'] ?? ''),
                        'badge_text'  => (string)($s['badge_text'] ?? $s['badgeText'] ?? 'Slide Show Infrastruktur'),
                        'image_url'   => (string)($s['image_url'] ?? $s['imageUrl'] ?? ''),
                        'slug'        => (string)($s['slug'] ?? ''),
                        'image_count' => (int)($s['image_count'] ?? $s['imageCount'] ?? 1),
                        'sort_order'  => (int)($s['sort_order'] ?? $s['sortOrder'] ?? ($idx + 1)),
                        'is_active'   => isset($s['is_active']) ? (int)$s['is_active'] : (!empty($s['isActive']) ? 1 : 1),
                    ];
                }
            }
            if (!empty($mapped)) {
                return $mapped;
            }
        }
    }

    $slideshow_defaults = get_default_slideshow_categories();
    $slides = [];
    foreach ($slideshow_defaults as $idx => $cat) {
        $img_count = isset($cat['images']) && is_array($cat['images']) ? count($cat['images']) : 1;
        $slides[] = [
            'id'          => (int)($cat['id'] ?? ($idx + 1)),
            'title'       => (string)$cat['title'],
            'subtitle'    => (string)$cat['short_description'],
            'badge_text'  => 'Slide Show Infrastruktur · ' . $img_count . ' Gambar',
            'image_url'   => (string)$cat['cover_image'],
            'slug'        => (string)$cat['slug'],
            'image_count' => $img_count,
            'sort_order'  => (int)($cat['sort_order'] ?? ($idx + 1)),
            'is_active'   => (int)($cat['is_active'] ?? 1),
        ];
    }
    return $slides;
}

/**
 * Sinkronkan isi tabel `hero_sliders` langsung dari Halaman Slide Show Infrastruktur (`slideshow.php` / `slideshow_categories`)
 */
function sync_hero_sliders_from_slideshow(PDO $pdo): int {
    $categories = get_slideshow_categories($pdo, false);
    if (empty($categories)) {
        $categories = get_default_slideshow_categories();
    }

    $pdo->exec("DELETE FROM hero_sliders");
    $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, badge_text, image_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $inserted = 0;
    foreach ($categories as $idx => $cat) {
        $img_count = (int)($cat['image_count'] ?? (isset($cat['images']) && is_array($cat['images']) ? count($cat['images']) : 1));
        $cover = !empty($cat['cover_image'])
            ? $cat['cover_image']
            : (!empty($cat['images'][0]['image_path']) ? $cat['images'][0]['image_path'] : '');
        $badge = 'Slide Show Infrastruktur · ' . $img_count . ' Gambar';
        $stmt->execute([
            (string)$cat['title'],
            (string)($cat['short_description'] ?? ''),
            $badge,
            (string)$cover,
            (int)($cat['sort_order'] ?? ($idx + 1)),
            isset($cat['is_active']) ? (int)$cat['is_active'] : 1
        ]);
        $inserted++;
    }
    invalidate_live_preview_json('hero_sliders');
    return $inserted;
}

/**
 * Pastikan tabel `hero_sliders` tersedia dan terisi dari Halaman Slide Show Infrastruktur (`slideshow.php`)
 */
function ensure_hero_sliders_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `hero_sliders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(180) NOT NULL,
            `subtitle` TEXT DEFAULT NULL,
            `badge_text` VARCHAR(100) DEFAULT 'Slide Show Infrastruktur',
            `image_url` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $count = (int)$pdo->query("SELECT COUNT(*) FROM hero_sliders")->fetchColumn();
        $legacy_count = (int)$pdo->query("SELECT COUNT(*) FROM hero_sliders WHERE title = 'Backbone Fiber Optic & Data Center KGSNet'")->fetchColumn();
        if ($count === 0 || $legacy_count > 0) {
            sync_hero_sliders_from_slideshow($pdo);
        }
    } catch (Exception $e) {
        // Abaikan jika user DB tidak memiliki izin CREATE
    }
}

/**
 * Ambil daftar slide beranda dari database `hero_sliders` (diperkaya dengan informasi kategori `slideshow.php`)
 */
function get_hero_sliders(PDO $pdo, bool $only_active = true): array {
    $cats = [];
    try {
        foreach (get_slideshow_categories($pdo, false) as $c) {
            $cats[strtolower(trim((string)$c['title']))] = $c;
        }
    } catch (Exception $e) {}

    try {
        ensure_hero_sliders_table($pdo);
        $sql = "SELECT * FROM hero_sliders";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
            foreach ($rows as &$r) {
                $key = strtolower(trim((string)$r['title']));
                if (isset($cats[$key])) {
                    $r['slug'] = $cats[$key]['slug'] ?? '';
                    $r['slideshow_category_id'] = (int)($cats[$key]['id'] ?? 0);
                    $r['image_count'] = (int)($cats[$key]['image_count'] ?? count($cats[$key]['images'] ?? []));
                } else {
                    $r['slug'] = $r['slug'] ?? '';
                    $r['slideshow_category_id'] = 0;
                    $r['image_count'] = 1;
                }
            }
            unset($r);
            return $rows;
        }
    } catch (Exception $e) {
        // Gunakan default jika tabel belum tersedia
    }
    return get_default_hero_sliders();
}

/**
 * Pastikan tabel `company_timelines` tersedia beserta kolom `is_highlighted`
 */
function ensure_company_timelines_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `company_timelines` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `year_label` VARCHAR(30) NOT NULL,
            `title` VARCHAR(180) NOT NULL,
            `description` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_highlighted` TINYINT(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `company_timelines`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        if (!isset($cols['is_highlighted'])) {
            try {
                $pdo->exec("ALTER TABLE `company_timelines` ADD COLUMN `is_highlighted` TINYINT(1) NOT NULL DEFAULT 0");
            } catch (Exception $e) {}
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM company_timelines")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO company_timelines (year_label, title, description, sort_order, is_highlighted) VALUES (?, ?, ?, ?, ?)");
            $defaults = [
                ['1997', 'Pendirian CV. CREATIVE (Creative Com)', 'Berdiri di Palembang dengan fokus layanan perbaikan perangkat komputer dan instalasi jaringan lokal.', 1, 0],
                ['1998', 'Distributor Perangkat Wartel QPHONE', 'Merambah bidang penjualan dan distribusi perangkat telekomunikasi wartel merk QPHONE.', 2, 0],
                ['2002', 'Authorized Dealer PABX Panasonic', 'Panasonic mempercayakan Creative Com sebagai salah satu dealer resmi sistem PABX di Palembang.', 3, 0],
                ['2004', 'Jaringan Komputer, Radio, PABX & Tower', 'Melayani instalasi jaringan kabel/wireless, radio komunikasi, PABX, serta fabrikasi & ereksi tower antena.', 4, 0],
                ['2013', 'Transformasi PT. KREATIF GLOBAL SOLUSINDO', 'Peningkatan badan hukum menjadi Perseroan Terbatas untuk melayani tender korporasi dan BUMN.', 5, 1],
                ['2020', 'Lisensi Resmi ISP KGSNet & Anggota APJII', 'Resmi mengantongi izin Penyelenggara Jasa Internet (ISP) dengan produk KGSNet dan tergabung di APJII.', 6, 1],
            ];
            foreach ($defaults as $d) {
                $stmt->execute($d);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Pastikan tabel `company_certifications` tersedia beserta kolom `subtitle`, `logo_url`, `link_url`, `sort_order`
 */
function ensure_company_certifications_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `company_certifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `badge_code` VARCHAR(50) NOT NULL DEFAULT 'CERT',
            `title` VARCHAR(180) NOT NULL DEFAULT '',
            `subtitle` VARCHAR(180) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `logo_url` VARCHAR(255) DEFAULT NULL,
            `link_url` VARCHAR(255) DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `company_certifications`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'badge_code'  => "ADD COLUMN `badge_code` VARCHAR(50) NOT NULL DEFAULT 'CERT'",
            'title'       => "ADD COLUMN `title` VARCHAR(180) NOT NULL DEFAULT ''",
            'subtitle'    => "ADD COLUMN `subtitle` VARCHAR(180) DEFAULT NULL",
            'description' => "ADD COLUMN `description` TEXT DEFAULT NULL",
            'logo_url'    => "ADD COLUMN `logo_url` VARCHAR(255) DEFAULT NULL",
            'link_url'    => "ADD COLUMN `link_url` VARCHAR(255) DEFAULT NULL",
            'sort_order'  => "ADD COLUMN `sort_order` INT NOT NULL DEFAULT 1",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `company_certifications` " . $ddl);
                } catch (Exception $e) {}
            }
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM company_certifications")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO company_certifications (badge_code, title, subtitle, description, logo_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $certs = [
                ['SONY', 'Certificate of Appointment SONY System Integrator', 'Lisensi Resmi Surveillance', 'Penunjukan resmi sebagai System Integrator untuk produk Video Security & IP Camera Sony.', '', '', 1],
                ['PANA', 'Authorized Dealer PABX Panasonic', 'Kemitraan Sejak 2002', 'Kemitraan resmi instalasi, pemrograman, dan purnajual sistem telekomunikasi PABX Panasonic sejak 2002.', '', '', 2],
                ['FO', 'NETVIEL InfiTe™ Fiber Optic Connectivity', 'Sertifikasi Infrastruktur Serat Optik', 'Sertifikasi lisensi instalasi dan terminasi jaringan serat optik (Fiber Optic) berstandar industri.', '', '', 3],
                ['MTC', 'MikroTik Certified Routing Engineer (MTCRE)', 'Sertifikasi Engineer Jaringan ISP', 'Tenaga ahli tersertifikasi internasional untuk manajemen routing, bandwidth, dan keamanan jaringan ISP.', '', '', 4],
            ];
            foreach ($certs as $c) {
                $stmt->execute($c);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Pastikan tabel `client_sectors` tersedia di database beserta seluruh kolom kompatibel
 */
function ensure_client_sectors_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `client_sectors` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `badge_code` VARCHAR(30) NOT NULL DEFAULT 'BUMN',
            `badge_label` VARCHAR(30) DEFAULT 'BUMN',
            `badge_color` VARCHAR(30) DEFAULT 'blue',
            `name` VARCHAR(150) NOT NULL DEFAULT '',
            `sector_name` VARCHAR(150) DEFAULT '',
            `sector_title` VARCHAR(150) DEFAULT '',
            `description` TEXT NOT NULL,
            `summary` TEXT DEFAULT NULL,
            `highlight_clients` TEXT DEFAULT NULL,
            `client_list` TEXT DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `client_sectors`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'badge_code'        => "ADD COLUMN `badge_code` VARCHAR(30) NOT NULL DEFAULT 'BUMN'",
            'badge_label'       => "ADD COLUMN `badge_label` VARCHAR(30) DEFAULT 'BUMN'",
            'badge_color'       => "ADD COLUMN `badge_color` VARCHAR(30) DEFAULT 'blue'",
            'name'              => "ADD COLUMN `name` VARCHAR(150) NOT NULL DEFAULT ''",
            'sector_name'       => "ADD COLUMN `sector_name` VARCHAR(150) DEFAULT ''",
            'sector_title'      => "ADD COLUMN `sector_title` VARCHAR(150) DEFAULT ''",
            'description'       => "ADD COLUMN `description` TEXT DEFAULT NULL",
            'summary'           => "ADD COLUMN `summary` TEXT DEFAULT NULL",
            'highlight_clients' => "ADD COLUMN `highlight_clients` TEXT DEFAULT NULL",
            'client_list'       => "ADD COLUMN `client_list` TEXT DEFAULT NULL",
            'sort_order'        => "ADD COLUMN `sort_order` INT NOT NULL DEFAULT 1",
            'is_active'         => "ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `client_sectors` " . $ddl);
                } catch (Exception $e) {}
            }
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM client_sectors")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO client_sectors (badge_code, badge_label, badge_color, name, sector_name, sector_title, description, summary, highlight_clients, client_list, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $sectors = [
                ['BUMN', 'BUMN', 'blue', 'BUMN, Energi & Kepelabuhanan', 'BUMN, Energi & Kepelabuhanan', 'BUMN, Energi & Kepelabuhanan', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 1],
                ['AGRO', 'AGRO', 'emerald', 'Perkebunan Sawit, Pulp & Tambang', 'Perkebunan Sawit, Pulp & Tambang', 'Perkebunan Sawit, Pulp & Tambang', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 2],
                ['GOV', 'GOV', 'indigo', 'Pemerintahan, Perbankan & Medis', 'Pemerintahan, Perbankan & Medis', 'Pemerintahan, Perbankan & Medis', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, RS M. Husin', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 3],
            ];
            foreach ($sectors as $sec) {
                $stmt->execute($sec);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Ambil daftar ringkasan sektor industri klien dari tabel `client_sectors`
 * dengan normalisasi nama kolom agar kompatibel di `klien.php` dan `admin/bagian-halaman.php`.
 */
function get_client_sectors(PDO $pdo, bool $only_active = false): array {
    try {
        ensure_client_sectors_table($pdo);
        $sql = "SELECT * FROM client_sectors";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
            foreach ($rows as &$r) {
                $badge = !empty($r['badge_code']) ? $r['badge_code'] : (!empty($r['badge_label']) ? $r['badge_label'] : 'BUMN');
                $title = !empty($r['sector_name']) ? $r['sector_name'] : (!empty($r['name']) ? $r['name'] : (!empty($r['sector_title']) ? $r['sector_title'] : ''));
                $desc  = !empty($r['summary']) ? $r['summary'] : (!empty($r['description']) ? $r['description'] : (!empty($r['client_list']) ? $r['client_list'] : ''));
                $hl    = !empty($r['highlight_clients']) ? $r['highlight_clients'] : $desc;
                $color = !empty($r['badge_color']) ? $r['badge_color'] : 'blue';

                $r['badge_code']        = $badge;
                $r['badge_label']       = $badge;
                $r['badge_color']       = $color;
                $r['name']              = $title;
                $r['sector_name']       = $title;
                $r['sector_title']      = $title;
                $r['description']       = $desc;
                $r['summary']           = $desc;
                $r['highlight_clients'] = $hl;
                $r['client_list']       = $desc;
                $r['is_active']         = isset($r['is_active']) ? (int)$r['is_active'] : 1;
            }
            unset($r);
            return $rows;
        }
    } catch (Exception $e) {}

    return [
        ['id' => 1, 'badge_code' => 'BUMN', 'badge_label' => 'BUMN', 'badge_color' => 'blue', 'name' => 'BUMN, Energi & Kepelabuhanan', 'sector_name' => 'BUMN, Energi & Kepelabuhanan', 'sector_title' => 'BUMN, Energi & Kepelabuhanan', 'description' => 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'summary' => 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'highlight_clients' => 'Pelindo, Semen Tonasa, Pusri, PLN, Pertamina', 'client_list' => 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'sort_order' => 1, 'is_active' => 1],
        ['id' => 2, 'badge_code' => 'AGRO', 'badge_label' => 'AGRO', 'badge_color' => 'emerald', 'name' => 'Perkebunan Sawit, Pulp & Tambang', 'sector_name' => 'Perkebunan Sawit, Pulp & Tambang', 'sector_title' => 'Perkebunan Sawit, Pulp & Tambang', 'description' => 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'summary' => 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'highlight_clients' => 'OKI Pulp & Paper, Sampoerna Agro, Lonsum, Wilmar', 'client_list' => 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'sort_order' => 2, 'is_active' => 1],
        ['id' => 3, 'badge_code' => 'GOV', 'badge_label' => 'GOV', 'badge_color' => 'indigo', 'name' => 'Pemerintahan, Perbankan & Medis', 'sector_name' => 'Pemerintahan, Perbankan & Medis', 'sector_title' => 'Pemerintahan, Perbankan & Medis', 'description' => 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'summary' => 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'highlight_clients' => 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK', 'client_list' => 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'sort_order' => 3, 'is_active' => 1],
    ];
}

/**
 * Pastikan tabel `company_partners` tersedia di database beserta kolom `logo_url`, `link_url`, `description`
 */
function ensure_company_partners_table(PDO $pdo): void {
    static $partners_ready = false;
    if ($partners_ready) {
        return;
    }
    $partners_ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `company_partners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `brand_name` VARCHAR(150) NOT NULL DEFAULT '',
            `role_text` VARCHAR(200) NOT NULL DEFAULT '',
            `description` TEXT DEFAULT NULL,
            `logo_url` VARCHAR(255) DEFAULT NULL,
            `link_url` VARCHAR(255) DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `company_partners`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'brand_name'  => "ADD COLUMN `brand_name` VARCHAR(150) NOT NULL DEFAULT ''",
            'role_text'   => "ADD COLUMN `role_text` VARCHAR(200) NOT NULL DEFAULT ''",
            'description' => "ADD COLUMN `description` TEXT DEFAULT NULL",
            'logo_url'    => "ADD COLUMN `logo_url` VARCHAR(255) DEFAULT NULL",
            'link_url'    => "ADD COLUMN `link_url` VARCHAR(255) DEFAULT NULL",
            'sort_order'  => "ADD COLUMN `sort_order` INT NOT NULL DEFAULT 1",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `company_partners` " . $ddl);
                } catch (Exception $e) {}
            }
        }

        if (isset($cols['name'])) {
            try {
                $pdo->exec("UPDATE `company_partners` SET `brand_name` = `name` WHERE (`brand_name` = '' OR `brand_name` IS NULL) AND `name` IS NOT NULL");
            } catch (Exception $e) {}
        }
        if (isset($cols['role'])) {
            try {
                $pdo->exec("UPDATE `company_partners` SET `role_text` = `role` WHERE (`role_text` = '' OR `role_text` IS NULL) AND `role` IS NOT NULL");
            } catch (Exception $e) {}
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM company_partners")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO company_partners (brand_name, role_text, description, logo_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $partners = [
                ['PT. Telkom Indonesia', 'Lastmile Support for ISP', 'Dukungan jaringan backbone dan lastmile telekomunikasi nasional.', '', '', 1],
                ['B.NET', 'Guywire Tower Support for ISP', 'Kemitraan infrastruktur menara telekomunikasi dan link wireless ISP.', '', '', 2],
                ['Inti.Net', 'Guywire Tower Support for ISP', 'Dukungan jaringan menara guywire dan transmisi data regional.', '', '', 3],
                ['Elnus.Net', 'Guywire Tower Support for ISP', 'Kemitraan strategis infrastruktur menara dan jaringan komunikasi.', '', '', 4],
                ['PT. LintasArta', 'Guywire Tower Support for ISP', 'Kemitraan jaringan komunikasi data korporasi dan infrastruktur tower.', '', '', 5],
                ['PT. IES Nusantara', 'Guywire Tower Support for ISP', 'Dukungan teknis ereksi menara dan konektivitas jaringan ISP.', '', '', 6],
            ];
            foreach ($partners as $p) {
                $stmt->execute($p);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Pastikan tabel `clients` memiliki kolom `image_url`, `logo_url`, `website_url`, `description`, `pdf_page`
 */
function ensure_clients_table(PDO $pdo): void {
    static $clients_ready = false;
    if ($clients_ready) {
        return;
    }
    $clients_ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `clients` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `location` VARCHAR(100) DEFAULT NULL,
            `service_category` VARCHAR(100) NOT NULL,
            `image_url` VARCHAR(255) DEFAULT NULL,
            `logo_url` VARCHAR(255) DEFAULT NULL,
            `website_url` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `pdf_page` INT NOT NULL DEFAULT 8,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `clients`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'image_url'   => "ADD COLUMN `image_url` VARCHAR(255) DEFAULT NULL",
            'logo_url'    => "ADD COLUMN `logo_url` VARCHAR(255) DEFAULT NULL",
            'website_url' => "ADD COLUMN `website_url` VARCHAR(255) DEFAULT NULL",
            'description' => "ADD COLUMN `description` TEXT DEFAULT NULL",
            'pdf_page'    => "ADD COLUMN `pdf_page` INT NOT NULL DEFAULT 8",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `clients` " . $ddl);
                } catch (Exception $e) {}
            }
        }
    } catch (Exception $e) {}
}

/**
 * Pastikan tabel `company_timelines`, `company_certifications`, `company_partners`,
 * `client_sectors`, dan `clients` tersedia di database beserta seluruh kolom yang dibutuhkan.
 */
function ensure_company_profile_tables(PDO $pdo): void {
    ensure_company_timelines_table($pdo);
    ensure_company_certifications_table($pdo);
    ensure_client_sectors_table($pdo);
    ensure_company_partners_table($pdo);
    ensure_clients_table($pdo);
}

/**
 * Parse string `footer_cert_badges` menjadi array terstruktur:
 * Mendukung format:
 *   "APJII Official Member"
 *   "APJII Official Member :: https://apjii.or.id"
 *   "APJII Official Member :: https://apjii.or.id :: uploads/general/logo-apjii.png"
 * Dipisahkan dengan karakter "|" atau baris baru.
 */
function parse_footer_cert_badges(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }
    $chunks = preg_split('/[\|\r\n]+/', $raw);
    $result = [];
    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') {
            continue;
        }
        $parts = array_map('trim', explode('::', $chunk));
        $label = $parts[0] ?? '';
        $url   = $parts[1] ?? '';
        $logo  = $parts[2] ?? '';
        if ($label !== '') {
            $result[] = [
                'label' => $label,
                'url'   => $url,
                'logo'  => $logo,
            ];
        }
    }
    return $result;
}

/**
 * Pastikan tabel `pricing_packages` memiliki seluruh kolom kompatibel (`tagline`, `period`, `cta_text`, dll)
 */
function ensure_pricing_packages_table(PDO $pdo): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `pricing_packages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `code_label` VARCHAR(30) NOT NULL DEFAULT 'ISP',
            `name` VARCHAR(150) NOT NULL,
            `target_sector` VARCHAR(120) DEFAULT 'Perkantoran & Industri',
            `tagline` VARCHAR(255) DEFAULT NULL,
            `subtitle` VARCHAR(255) DEFAULT NULL,
            `price_label` VARCHAR(120) NOT NULL DEFAULT 'Sesuai Kapasitas & BoQ',
            `period` VARCHAR(150) DEFAULT 'SLA 99.5% Uptime Terjamin',
            `sla_badge` VARCHAR(150) DEFAULT NULL,
            `features` TEXT NOT NULL,
            `badge_text` VARCHAR(100) DEFAULT NULL,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `cta_text` VARCHAR(120) DEFAULT 'Minta Penawaran Paket Ini',
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Pastikan kolom tambahan ada jika tabel dibuat dari skema lama
        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `pricing_packages`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        $alter_map = [
            'code_label'    => "ADD COLUMN `code_label` VARCHAR(30) NOT NULL DEFAULT 'ISP'",
            'target_sector' => "ADD COLUMN `target_sector` VARCHAR(120) DEFAULT 'Perkantoran & Industri'",
            'tagline'       => "ADD COLUMN `tagline` VARCHAR(255) DEFAULT NULL",
            'subtitle'      => "ADD COLUMN `subtitle` VARCHAR(255) DEFAULT NULL",
            'period'        => "ADD COLUMN `period` VARCHAR(150) DEFAULT 'SLA 99.5% Uptime Terjamin'",
            'sla_badge'     => "ADD COLUMN `sla_badge` VARCHAR(150) DEFAULT NULL",
            'badge_text'    => "ADD COLUMN `badge_text` VARCHAR(100) DEFAULT NULL",
            'cta_text'      => "ADD COLUMN `cta_text` VARCHAR(120) DEFAULT 'Minta Penawaran Paket Ini'",
            'is_active'     => "ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1",
        ];
        foreach ($alter_map as $col_name => $ddl) {
            if (!isset($cols[$col_name])) {
                try {
                    $pdo->exec("ALTER TABLE `pricing_packages` " . $ddl);
                } catch (Exception $e) {
                    // Abaikan jika kolom sudah ada
                }
            }
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM pricing_packages")->fetchColumn() === 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO pricing_packages (code_label, name, target_sector, tagline, subtitle, price_label, period, sla_badge, features, badge_text, is_featured, cta_text, sort_order, is_active) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $plans = [
                [
                    'DEDICATED',
                    'Internet Dedicated',
                    'Kebutuhan UMKM, Sekolah & Corporate',
                    'Koneksi Internet Dedicated simetris berizin resmi Kominfo RI & APJII untuk UMKM, Sekolah, dan Corporate.',
                    'Koneksi Internet Dedicated simetris berizin resmi Kominfo RI & APJII untuk UMKM, Sekolah, dan Corporate.',
                    'Mulai Rp. 1.750.000 / bulan',
                    'Bandwidth Dedicated 1:1 & SLA 99.5%',
                    'Bandwidth Dedicated 1:1 & SLA 99.5%',
                    "10 Mbps Harga Perbulan mulai 3.500.000 Kebutuhan UMKM\n10 Mbps Harga Perbulan mulai 1.750.000 Kebutuhan Sekolah\n10 Mbps Harga Perbulan mulai 1.750.000 Kebutuhan Corporate\nIzin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020\nSurat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020\nNIB (Nomor Induk Berusaha) ; 9120202100598",
                    'Segmen 1 • Dedicated',
                    1,
                    'Pesan Internet Dedicated',
                    1
                ],
                [
                    'BISNIS',
                    'Internet Bisnis',
                    'Segmen Internet Bisnis (10 Mbps – 200 Mbps)',
                    'Pilihan paket kecepatan 10 Mbps hingga 200 Mbps untuk menunjang produktivitas bisnis dan kantor.',
                    'Pilihan paket kecepatan 10 Mbps hingga 200 Mbps untuk menunjang produktivitas bisnis dan kantor.',
                    'Mulai Rp. 407.000 / bulan',
                    'Koneksi Bisnis Stabil & Responsif',
                    'Koneksi Bisnis Stabil & Responsif',
                    "10 Mbps Rp. 407.000 / bulan\n20 Mbps Rp. 431.000 / bulan\n30 Mbps Rp. 453.000 / bulan\n40 Mbps Rp. 477.000 / bulan\n50 Mbps Rp. 511.000 / bulan\n100 Mbps Rp. 1.050.000 / bulan\n200 Mbps Rp. 1.560.000 / bulan",
                    'Segmen 2 • Internet Bisnis',
                    0,
                    'Pesan Internet Bisnis',
                    2
                ],
                [
                    'HOME',
                    'Home Internet',
                    'Segmen Home Internet (5 – 20 Perangkat)',
                    'Koneksi internet rumah tangga cepat dan stabil mulai dari 10 Mbps hingga 200 Mbps.',
                    'Koneksi internet rumah tangga cepat dan stabil mulai dari 10 Mbps hingga 200 Mbps.',
                    'Mulai Rp. 252.000 / bulan',
                    'Optimal 5 hingga 20 Perangkat',
                    'Optimal 5 hingga 20 Perangkat',
                    "10 Mbps Rp. 252.000 / bulan Optiman di 5 Perangkat\n20 Mbps Rp. 276.000 / bulan Optiman di 7 Perangkat\n30 Mbps Rp. 298.000 / bulan Optiman di 9 Perangkat\n50 Mbps Rp. 450.000 / bulan Optiman di 12 Perangkat\n100 Mbps Rp. 710.000 / bulan Optiman di 15 Perangkat\n200 Mbps Rp. 1.100.000 / bulan Optiman di 20 Perangkat",
                    'Segmen 3 • Home Internet',
                    0,
                    'Pesan Home Internet',
                    3
                ],
            ];
            foreach ($plans as $pl) {
                $stmt->execute($pl);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika gagal
    }
}

/**
 * Pastikan tabel `faqs` tersedia di database
 */
function ensure_faqs_table(PDO $pdo): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `faqs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category` VARCHAR(100) NOT NULL DEFAULT 'Umum',
            `question` VARCHAR(255) NOT NULL,
            `answer` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if ((int)$pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO faqs (category, question, answer, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
            $faqs = [
                ['Umum', 'Bagaimana sejarah berdirinya PT. Kreatif Global Solusindo (Creative Com)?', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.', 1],
                ['Umum', 'Di mana saja jangkauan wilayah pengerjaan proyek PT. KGS?', 'Sejak 1997, ruang layanan tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga ke provinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.', 2],
                ['Internet ISP', 'Apa saja legalitas resmi layanan Internet Service Provider PT. KGS?', 'Layanan Internet Service Provider PT. Kreatif Global Solusindo dilengkapi legalitas resmi: Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020, Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020, serta NIB (Nomor Induk Berusaha) ; 9120202100598.', 3],
                ['Internet ISP', 'Apa saja 3 segmen paket Internet dan harganya di PT. KGS?', 'Terdapat 3 Segmen layanan internet: (1) Internet Dedicated 10 Mbps mulai Rp 3.500.000/bulan untuk UMKM, Rp 1.750.000/bulan untuk Sekolah, dan Rp 1.750.000/bulan untuk Corporate; (2) Internet Bisnis mulai 10 Mbps (Rp 407.000/bulan) hingga 200 Mbps (Rp 1.560.000/bulan); serta (3) Home Internet mulai 10 Mbps (Rp 252.000/bulan Optiman di 5 Perangkat) hingga 200 Mbps (Rp 1.100.000/bulan Optiman di 20 Perangkat).', 4],
                ['Keamanan & Tower', 'Apa saja Product & Service yang disediakan oleh PT. KGS?', 'Product & Service kami mencakup: Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), serta Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet).', 5],
                ['Keamanan & Tower', 'Berapa standar nilai tahanan pembumian (grounding) pada instalasi Penangkal Petir?', 'Dalam pelaksanaan instalasi Lightning Protection System (Penangkal Petir), tim teknisi kami melakukan pengujian menggunakan alat ukur digital (Earth Resistance Tester) untuk memastikan nilai resistansi grounding mencapai standar aman di bawah 2 Ohm.', 6],
                ['Kontrak & Legalitas', 'Bagaimana prosedur pengajuan survei lokasi dan penawaran harga (Quotation)?', 'Calon klien dapat menghubungi kantor kami di Palembang (0711-316088 / WhatsApp 0811-782119) atau mengisi formulir Permintaan Penawaran di website ini. Tim teknis kami akan menjadwalkan survei lapangan guna menghitung Bill of Quantity (BoQ) dan spesifikasi perangkat yang paling tepat dan efisien.', 7],
                ['Kontrak & Legalitas', 'Bagaimana komitmen pelayanan purnajual PT. KGS kepada pelanggan?', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.', 8],
            ];
            foreach ($faqs as $f) {
                $stmt->execute($f);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika gagal
    }
}

function get_company_timelines(PDO $pdo): array {
    try {
        ensure_company_profile_tables($pdo);
        $rows = $pdo->query("SELECT * FROM company_timelines ORDER BY sort_order ASC, id ASC")->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    $live = load_live_preview_state();
    if (!empty($live['timelines']) && is_array($live['timelines'])) {
        return $live['timelines'];
    }
    return [];
}

function get_company_certifications(PDO $pdo): array {
    $rows = [];
    try {
        ensure_company_profile_tables($pdo);
        $rows = $pdo->query("SELECT * FROM company_certifications ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {
        try {
            $rows = $pdo->query("SELECT * FROM company_certifications")->fetchAll();
        } catch (Exception $e2) {}
    }

    if (empty($rows)) {
        $live = load_live_preview_state();
        if (!empty($live['certifications']) && is_array($live['certifications'])) {
            $rows = $live['certifications'];
        }
    }

    $normalized = [];
    foreach ($rows as $idx => $r) {
        if (!is_array($r)) continue;
        $title = trim((string)($r['title'] ?? $r['name'] ?? 'Sertifikasi Resmi'));
        $badge = trim((string)($r['badge_code'] ?? $r['issuerOrBrand'] ?? 'CERT'));
        if (strlen($badge) > 12) {
            $badge = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $badge), 0, 6)) ?: 'CERT';
        }
        $subtitle = trim((string)($r['subtitle'] ?? $r['issuerOrBrand'] ?? ''));
        $desc = trim((string)($r['description'] ?? $r['yearOrValidity'] ?? $r['holder'] ?? ''));
        $normalized[] = [
            'id'          => isset($r['id']) && is_numeric($r['id']) ? (int)$r['id'] : ($idx + 1),
            'badge_code'  => $badge !== '' ? $badge : 'CERT',
            'title'       => $title,
            'subtitle'    => $subtitle,
            'description' => $desc,
            'logo_url'    => trim((string)($r['logo_url'] ?? $r['logoUrl'] ?? '')),
            'link_url'    => trim((string)($r['link_url'] ?? $r['linkUrl'] ?? '')),
            'sort_order'  => isset($r['sort_order']) ? (int)$r['sort_order'] : ($idx + 1),
        ];
    }
    return $normalized;
}

function get_company_partners(PDO $pdo): array {
    $rows = [];
    try {
        ensure_company_profile_tables($pdo);
        $rows = $pdo->query("SELECT * FROM company_partners ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {
        try {
            $rows = $pdo->query("SELECT * FROM company_partners")->fetchAll();
        } catch (Exception $e2) {}
    }

    if (empty($rows)) {
        $live = load_live_preview_state();
        if (!empty($live['partners']) && is_array($live['partners'])) {
            $rows = $live['partners'];
        }
    }

    $normalized = [];
    foreach ($rows as $idx => $r) {
        if (!is_array($r)) continue;
        $brand = trim((string)($r['brand_name'] ?? $r['name'] ?? 'Mitra KGS'));
        $role  = trim((string)($r['role_text'] ?? $r['role'] ?? 'Mitra Resmi KGS'));
        $desc  = trim((string)($r['description'] ?? $r['serviceType'] ?? $r['service_type'] ?? ''));
        $normalized[] = [
            'id'          => isset($r['id']) && is_numeric($r['id']) ? (int)$r['id'] : ($idx + 1),
            'brand_name'  => $brand,
            'role_text'   => $role,
            'description' => $desc,
            'logo_url'    => trim((string)($r['logo_url'] ?? $r['logoUrl'] ?? '')),
            'link_url'    => trim((string)($r['link_url'] ?? $r['linkUrl'] ?? '')),
            'sort_order'  => isset($r['sort_order']) ? (int)$r['sort_order'] : ($idx + 1),
        ];
    }
    return $normalized;
}

function get_pricing_packages(PDO $pdo, bool $only_active = true): array {
    try {
        ensure_pricing_packages_table($pdo);
        $sql = "SELECT * FROM pricing_packages";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
            // Normalisasi kolom antara subtitle/tagline dan sla_badge/period (prioritaskan subtitle & sla_badge dari Admin CMS)
            foreach ($rows as &$r) {
                $r['subtitle'] = !empty($r['subtitle']) ? $r['subtitle'] : ($r['tagline'] ?? '');
                $r['tagline']  = $r['subtitle'];
                $r['sla_badge'] = !empty($r['sla_badge']) ? $r['sla_badge'] : ($r['period'] ?? '');
                $r['period']    = $r['sla_badge'];
                $r['cta_text']  = !empty($r['cta_text']) ? $r['cta_text'] : 'Minta Penawaran Paket Ini';
            }
            unset($r);
            return $rows;
        }
    } catch (Exception $e) {}

    $live = load_live_preview_state();
    if (!empty($live['pricing_packages']) && is_array($live['pricing_packages'])) {
        return $live['pricing_packages'];
    }
    return [];
}

function get_faqs_list(PDO $pdo, bool $only_active = true): array {
    try {
        ensure_faqs_table($pdo);
        $sql = "SELECT * FROM faqs";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    $live = load_live_preview_state();
    if (!empty($live['faqs']) && is_array($live['faqs'])) {
        return $live['faqs'];
    }
    return [];
}

/**
 * Daftar 8 Kategori Default Slide Show Infrastruktur beserta multi-image seed data
 */
function get_default_slideshow_categories(): array {
    return [
        [
            'id' => 1,
            'title' => 'Fiber Optik',
            'slug' => 'fiber-optik',
            'short_description' => 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari...',
            'full_description' => "Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari sehelai rambut, dan dapat digunakan untuk mentransmisikan sinyal cahaya dari suatu tempat ke tempat lain. Sumber cahaya yang digunakan biasanya adalah dari sinar laser atau LED.\n\nKabel ini berdiameter lebih kurang 120 mikrometer. Cahaya yang ada di dalam serat optik tidak keluar karena indeks bias dari kaca lebih besar daripada indeks bias dari udara, karena laser mempunyai spektrum yang sangat sempit. Kecepatan transmisi fiber optik sangat tinggi sehingga sangat bagus digunakan sebagai saluran komunikasi.\n\nPerkembangan teknologi fiber optik saat ini, telah dapat menghasilkan pelemahan (attenuation) kurang dari 20 decibels (dB)/km. Dengan lebar jalur (bandwidth) yang besar, maka mampu dalam mentransmisikan data menjadi lebih banyak dan cepat dibandingan dengan penggunaan kabel konvensional. Dengan demikian fiber optik sangat cocok digunakan terutama dalam aplikasi sistem telekomunikasi.\n\nSekitar 20 tahun yang lalu, kabel fiber optik telah memngambil alih dan mengubah wajah teknologi industri telepon jarak jauh maupun industri automasi dengan pengontrolan jarak jauh. Serat optik juga memberikan peranan besar membuat Internet dapat digunakan di seluruh dunia. Pada tahun 1997 fiber optik menghubungkan seluruh dunia, Fiber-Optic Link Around the Globe (FLAG) menjadi jaringan kabel terpanjang di seluruh dunia yang menyediakan infrastruktur untuk generasi internet terbaru.\n\n### Kelebihan Fiber Optik\n- Bandwidth sangat besar dengan kecepatan transmisi mencapai gigabit-per detik dan menghantarkan informasi jarak jauh tanpa pengulangan\n- Biaya pemasangan dan pengoperasian yang rendah serta tingkat keamanan yang lebih tinggi\n- Ukuran kecil dan ringan, sehingga hemat pemakaian ruang\n- Kebal terhadap gangguan elektromagnetik dan gangguan gelombang radio\n- Tidak ada tenaga listrik dan percikan api\n- Tidak berkarat",
            'cover_image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 1,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 201,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Terminasi dan manajemen jaringan backbone Fiber Optik pada rak server Optical Distribution Frame (ODF).',
                    'alt_text' => 'Backbone Fiber Optik KGS',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 202,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Transmisi sinyal cahaya laser kecepatan tinggi melalui inti serat kaca berdiameter 120 mikrometer.',
                    'alt_text' => 'Serat Kaca Fiber Optik',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 203,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konektor patch cord LC/SC Singlemode & Multimode untuk interkoneksi switch optik.',
                    'alt_text' => 'Patch Cord Fiber Optik',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 204,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian redaman (attenuation < 20 dB/km) menggunakan Optical Time Domain Reflectometer (OTDR).',
                    'alt_text' => 'Pengujian OTDR Fiber Optik',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 205,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Penyambungan core serat optik presisi tinggi dengan Fusion Splicer bersertifikasi NETVIEL InfiTe.',
                    'alt_text' => 'Fusion Splicing Fiber Optik',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
                [
                    'id' => 206,
                    'category_id' => 1,
                    'image_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Jaringan Fiber Optik antar gedung dan kawasan industri yang kebal terhadap interferensi elektromagnetik.',
                    'alt_text' => 'Infrastruktur Jaringan Fiber Optik',
                    'sort_order' => 6,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 2,
            'title' => 'Guy Wire Tower',
            'slug' => 'guy-wire-tower',
            'short_description' => 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah...',
            'full_description' => "Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah (Guywire), dimana skur ini merupakan tumpuan utama agar tower tersebut dapat berdiri.\n\nMenara ini juga disusun atas pola batang besi sebagaimana dengan self-supporting tower, akan tetapi menara jenis guyed wire tower memiliki jenis dimensi batang yang lebih kecil dari pada jenis self-supporting tower.\n\nNamun demikian jenis guyed wire tower tidak kalah kuat dari segi kekuatan menopang beban, jenis menara ini mampu menerima beban antena dan perangkat lainnya yang terpasang pada tower.\n\nKarena sifatnya yang ditopang oleh kawat baja (wire) dan memiliki dimensi batang yang lebih kecil, menara jenis ini memiliki keuntungan antara lain dari segi biaya dan ruang, menara ini jauh lebih hemat biaya pembuatan dan membutuhkan ruang yang tidak terlalu luas dibanding dengan jenis self-supporting tower.",
            'cover_image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 2,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 301,
                    'category_id' => 2,
                    'image_path' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pembangunan dan ereksi Guy Wire Tower (Triangle Tower) dengan penopang tali baja (guywire).',
                    'alt_text' => 'Guy Wire Tower Utama',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 302,
                    'category_id' => 2,
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Struktur segmen batang besi triangle yang presisi dari workshop pabrikasi internal PT. KGS.',
                    'alt_text' => 'Struktur Batang Guy Wire Tower',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 303,
                    'category_id' => 2,
                    'image_path' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Sistem angkur tanah dan bentangan kawat skur baja anti-karat untuk stabilitas beban angin tinggi.',
                    'alt_text' => 'Angkur Skur Kawat Baja Guy Wire Tower',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 304,
                    'category_id' => 2,
                    'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi Guy Wire Tower untuk link radio komunikasi VHF/UHF dan Wireless Point-to-Point perkebunan.',
                    'alt_text' => 'Instalasi Guy Wire Tower Lapangan',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 3,
            'title' => 'SELF SUPPORTING TOWER MONOPOLE',
            'slug' => 'self-supporting-tower-monopole',
            'short_description' => 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara...',
            'full_description' => "Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara menolak berbagai kondisi lingkungan. Monopole menara tidak memerlukan bracing dari kabel tegangan tinggi atau bingkai logam, yang menawarkan banyak manfaat dan keuntungan.",
            'cover_image' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
            'sort_order' => 3,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 101,
                    'category_id' => 3,
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
                    'caption' => 'Instalasi Self Supporting Tower Monopole untuk infrastruktur telekomunikasi dan transmisi radio.',
                    'alt_text' => 'Self Supporting Tower Monopole Utama',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 102,
                    'category_id' => 3,
                    'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konstruksi tiang tunggal baja galvanis berintegritas tinggi tanpa bentangan kawat skur.',
                    'alt_text' => 'Struktur Tiang Tunggal Monopole',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 103,
                    'category_id' => 3,
                    'image_path' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan perangkat antena sektoral dan microwave link pada puncak menara Monopole.',
                    'alt_text' => 'Antena Microwave pada Tower Monopole',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 104,
                    'category_id' => 3,
                    'image_path' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Fondasi beton bertulang dan sistem grounding terintegrasi untuk ketahanan cuaca ekstrem.',
                    'alt_text' => 'Infrastruktur Menara Telekomunikasi KGS',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 4,
            'title' => 'LIGHTNING PROTECTOR',
            'slug' => 'lightning-protector',
            'short_description' => 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada...',
            'full_description' => "Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada 3 bagian utama pada penyalur petir:\n\n### 1. Batang penyalur petir\nBatang penyalur petir berupa batang tembaga yang ujungnya runcing. Buat batang tembaga yang runcing ini karena muatan listrik mempunyai sifat mudah berkumpul dan lepas pada ujung logam yang runcing. Dengan demikian dapat memperlancar proses tarik menarik dengan muatan listrik yang ada di awan. Batang runcing ini dipasang pada bagian puncak suatu bangunan/gedung.\n\n### 2. Kabel konduktor\nKabel konduktorv terbuat dari jalinan kawat tembaga. Diameter jalinan kabel konduktor sekitar 1 cm hingga 2 cm . Kabel konduktor berfungsi meneruskan aliran muatan listrik dari batang muatan listrik ke tanah. Kabel konduktor tersebut dipasang pada dinding di bagian luar bangunan/gedung.\n\n### 3. Tempat pembumian\nTempat pembumian (grounding) berfungsi mengalirkan muatan listrik dari kabel konduktor ke batang pembumian (ground rod) yang tertanam di tanah. Batang pembumian terbuat dari bahan tembaga berlapis baja, dengan diameter 1,5 cm dan panjang sekitar 1,8 – 3 m .",
            'cover_image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 4,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 401,
                    'category_id' => 4,
                    'image_path' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi kepala penangkal petir elektrostatis & konvensional pada puncak gedung dan menara.',
                    'alt_text' => 'Batang Penyalur Petir Utama',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 402,
                    'category_id' => 4,
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian resistansi tempat pembumian (grounding) menggunakan Earth Tester digital dengan target < 2 Ohm.',
                    'alt_text' => 'Pengujian Grounding Earth Tester',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 403,
                    'category_id' => 4,
                    'image_path' => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Jalur kabel konduktor tembaga (BC / NYY / Coaxial HV) dengan pipa pelindung di dinding luar gedung.',
                    'alt_text' => 'Kabel Konduktor Penyalur Petir',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 404,
                    'category_id' => 4,
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Bak kontrol grounding dan penanaman batang pembumian (ground rod) tembaga berlapis baja 1,8 - 3 meter.',
                    'alt_text' => 'Tempat Pembumian Ground Rod',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 405,
                    'category_id' => 4,
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Perlindungan Surge Arrester internal untuk menjaga perangkat server, PABX, dan switch dari induksi petir.',
                    'alt_text' => 'Surge Arrester & Proteksi Internal',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 5,
            'title' => 'IP – PBX',
            'slug' => 'ip-pbx',
            'short_description' => 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis...',
            'full_description' => "IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX merupakan perangkat switching komunikasi telepon dan data berbasis teknologi Internet Protocol (IP) yang mengendalikan ekstension telepon analog (TDM) maupun ekstension IP Phone.\n\nFungsi-fungsi yang dapat dilakukan antara lain penyambungan, pengendalian, dan pemutusan hubungan telepon; translasi protokol komunikasi; serta pengaturan jalur dan media komunikasi (suara, data, dan video).\n\nApabila di lingkungan anda telah terdapat perangkat router, maka perangkat IP PBX dapat berbagi jaringan dengan jaringan komputer yang sudah ada. Artinya tidak diperlukan jaringan khusus untuk mentransmisikan sinyal suara maupun video, cukup menumpangkannya pada jaringan data yang ada.\n\nSebuah IP PBX dapat mengubah panggilan telepon melalui jaringan data dengan menggunakan teknologi VoIP (Voice over Internet Protocol), dan mengkonversi panggilan VoIP ke jaringan telepon lokal (PSTN). Dengan demikian pengguna dalam satu perusahaan dapat berkomunikasi secara internal melalui jaringan IP dan juga tetap terhubung dengan jaringan telepon publik eksternal, baik melalui saluran analog konvensional maupun ISDN.\n\nDengan hadirnya IP PBX, maka jaringan data dan jaringan suara dapat digabungkan menjadi satu infrastruktur terpadu yang lebih efisien dan fleksibel untuk pengembangan di masa depan.",
            'cover_image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 5,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 501,
                    'category_id' => 5,
                    'image_path' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Perangkat IP Phone eksekutif dan konsol operator terintegrasi dengan server IP-PBX korporasi.',
                    'alt_text' => 'Sistem IP-PBX dan IP Phone',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 502,
                    'category_id' => 5,
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Unit main-frame IP-PBX dan Hybrid PBX Panasonic / SIP Server di ruang telekomunikasi.',
                    'alt_text' => 'Server IP-PBX Rackmount',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 503,
                    'category_id' => 5,
                    'image_path' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Integrasi komunikasi suara (VoIP) antar kantor cabang tanpa biaya interlokal melalui jaringan IP.',
                    'alt_text' => 'Komunikasi VoIP Korporasi',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 504,
                    'category_id' => 5,
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konvergensi jaringan data LAN dan ekstensi telepon analog/digital dalam satu infrastruktur terpadu.',
                    'alt_text' => 'Topologi Jaringan Terpadu IP-PBX',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 505,
                    'category_id' => 5,
                    'image_path' => 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi, pemrograman IVR/Auto-Attendant, dan billing system oleh teknisi resmi PT. KGS.',
                    'alt_text' => 'Pemrograman Sistem PABX & IP-PBX',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 6,
            'title' => 'Wireless',
            'slug' => 'wireless',
            'short_description' => 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah  hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik...',
            'full_description' => "Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik sebagai pengganti media kabel.\n\nTeknologi Wireless LAN adalah teknologi jaringan lokal yang memanfaatkan gelombang radio sebagai media transmisi data, sehingga tidak lagi mengandalkan kabel tembaga atau serat optik untuk menghubungkan perangkat akhir (end-user).\n\nJaringan wireless bekerja menggunakan gelombang elektromagnetik pada frekuensi tertentu (umumnya 2.4 GHz dan 5 GHz) untuk mengirim dan menerima paket data melalui perangkat Access Point (AP) dan Wireless Adapter.\n\n### Kelebihan\n- Mobilitas Tinggi: Pengguna dapat mengakses jaringan dan informasi dari mana saja selama masih berada dalam jangkauan sinyal (coverage area) Access Point.\n- Instalasi Mudah dan Cepat: Tidak memerlukan penarikan kabel yang rumit ke setiap meja kerja atau ruangan, sehingga meminimalkan perubahan fisik pada bangunan.\n- Fleksibilitas Tempat: Sangat ideal untuk area yang sulit dijangkau kabel, ruang rapat terbuka, gudang logistik, maupun gedung bersejarah.\n- Pengurangan Biaya Jangka Panjang: Menghemat biaya pengadaan kabel, pemeliharaan jalur kabel, serta biaya relokasi ketika terjadi perubahan tata letak kantor.\n- Kemudahan Ekspansi (Scalability): Penambahan pengguna baru atau perluasan area cakupan dapat dilakukan dengan cepat hanya dengan menambahkan unit Access Point baru.",
            'cover_image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 6,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 601,
                    'category_id' => 6,
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Implementasi Enterprise Wireless LAN & Access Point Dual-Band (2.4 GHz & 5 GHz) untuk perkantoran.',
                    'alt_text' => 'Enterprise Wireless Access Point',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 602,
                    'category_id' => 6,
                    'image_path' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Radio Wireless Point-to-Point (PTP) & Point-to-Multipoint (PTMP) outdoor jarak jauh antar site.',
                    'alt_text' => 'Antena Wireless Outdoor PTP',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 603,
                    'category_id' => 6,
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Manajemen terpusat bandwidth, roaming seamless, dan keamanan pengguna melalui Router/Controller MikroTik.',
                    'alt_text' => 'Manajemen Jaringan Wireless & Router',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 604,
                    'category_id' => 6,
                    'image_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemetaan sinyal (Heatmapping Survey) untuk memastikan cakupan merata tanpa blind-spot di seluruh ruangan.',
                    'alt_text' => 'Cakupan Sinyal Wireless Korporasi',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 605,
                    'category_id' => 6,
                    'image_path' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Enkripsi keamanan WPA3-Enterprise dan segmentasi VLAN untuk tamu serta operasional internal.',
                    'alt_text' => 'Keamanan Jaringan Nirkabel',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 7,
            'title' => 'Access Door',
            'slug' => 'access-door',
            'short_description' => 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID...',
            'full_description' => "Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID, PIN, maupun biometrik Sidik Jari dan Pengenalan Wajah untuk membatasi hak akses masuk dan keluar pada suatu ruangan.\n\nDalam penerapannya, sistem Access Door menggantikan kunci mekanis konvensional dengan metode otentikasi digital seperti Kartu RFID (Proximity/Mifare), kode PIN angka, maupun pemindaian biometrik (Sidik Jari / Fingerprint dan Pengenalan Wajah / Face Recognition).\n\nSistem ini bekerja menggunakan rangkaian kunci elektronik seperti Magnetic Lock (Maglock) atau Dropbolt yang dikendalikan oleh unit kontroler akses. Saat pengguna menempelkan kartu atau sidik jari yang terdaftar, kontroler akan memverifikasi data dan melepaskan pengunci magnetik selama beberapa detik agar pintu dapat dibuka.\n\nSelain meningkatkan keamanan fisik ruangan krusial (seperti ruang Server, ruang Direksi, Gudang, dan Laboratorium), sistem Access Door modern juga dapat diintegrasikan dengan perangkat lunak pencatatan waktu kehadiran (Time Attendance) serta sistem alarm kebakaran darurat (Emergency Breakglass) untuk keselamatan kerja.",
            'cover_image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 7,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 701,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Terminal Access Door pintar dengan verifikasi Kartu RFID, PIN, dan Biometrik Sidik Jari/Wajah.',
                    'alt_text' => 'Terminal Access Door Biometrik & RFID',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 702,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengamanan pintu ruang Data Center dan Server menggunakan Electromagnetic Lock (Maglock) 600 lbs.',
                    'alt_text' => 'Access Control Ruang Server',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 703,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan Exit Button Touchless (No-Touch Sensor) dan Emergency Breakglass di sisi dalam ruangan.',
                    'alt_text' => 'Sensor Exit Button & Emergency Breakglass',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 704,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Integrasi sistem Access Door dengan kamera pengawas CCTV dan Video Door Phone.',
                    'alt_text' => 'Integrasi Keamanan Access Door & CCTV',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 705,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Penerapan sistem kontrol akses pintu kaca dan kayu pada gedung perkantoran korporasi.',
                    'alt_text' => 'Pintu Kantor dengan Sistem Access Control',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
                [
                    'id' => 706,
                    'category_id' => 7,
                    'image_path' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Perangkat lunak log aktivitas keluar-masuk personel dan laporan absensi real-time.',
                    'alt_text' => 'Monitoring Log Akses Pintu',
                    'sort_order' => 6,
                    'is_primary' => 0,
                ],
            ],
        ],
        [
            'id' => 8,
            'title' => 'CCTV',
            'slug' => 'cctv',
            'short_description' => 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal...',
            'full_description' => "CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal ini tidak seperti televisi siaran di mana setiap penerima yang disetel dengan benar dapat mengambil sinyal dari udara; pada sistem CCTV sinyal video hanya ditransmisikan secara eksklusif ke monitor dan perekam (DVR/NVR) yang terhubung di dalam jaringan keamanan internal.\n\nSistem CCTV modern oleh PT. Kreatif Global Solusindo mengintegrasikan kamera IP resolusi tinggi (Full HD hingga 4K Ultra HD), fitur Night Vision / ColorVu untuk pemantauan 24 jam penuh dalam kondisi gelap, serta analitik cerdas berbasis AI (Motion Detection, Line Crossing, Intrusion Detection, dan License Plate Recognition).\n\nSeluruh rekaman disimpan secara terpusat pada Network Video Recorder (NVR) atau Digital Video Recorder (DVR) berkapasitas besar dan dapat dipantau secara real-time melalui layar Command Center maupun aplikasi smartphone dari mana saja.\n\n### Keunggulan Solusi CCTV PT. Kreatif Global Solusindo\n- Kamera IP & Analog HD berkualitas industri (Hikvision, Dahua, Panasonic) tahan cuaca ekstrem (IP67 Weatherproof)\n- Pemantauan jarak jauh (Remote Viewing) real-time melalui smartphone, tablet, dan PC\n- Perekaman 24/7 nonstop dengan kompresi H.265+ yang menghemat ruang penyimpanan harddisk\n- Integrasi penuh dengan sistem alarm keamanan, Access Door, dan jaringan Fiber Optik kawasan industri",
            'cover_image' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 8,
            'is_active' => 1,
            'images' => [
                [
                    'id' => 801,
                    'category_id' => 8,
                    'image_path' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi kamera pengawas CCTV IP Outdoor Weatherproof IP67 untuk keamanan perimeter gedung dan pabrik.',
                    'alt_text' => 'Kamera CCTV Outdoor Industri',
                    'sort_order' => 1,
                    'is_primary' => 1,
                ],
                [
                    'id' => 802,
                    'category_id' => 8,
                    'image_path' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pusat kendali pemantauan (Security Command Center) dengan multi-monitor dan sistem NVR terpusat.',
                    'alt_text' => 'Ruang Monitoring CCTV Command Center',
                    'sort_order' => 2,
                    'is_primary' => 0,
                ],
                [
                    'id' => 803,
                    'category_id' => 8,
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Kamera PTZ (Pan-Tilt-Zoom) jarak jauh untuk pengawasan area pelabuhan, dermaga, dan kawasan perkebunan.',
                    'alt_text' => 'Kamera CCTV PTZ High Speed Dome',
                    'sort_order' => 3,
                    'is_primary' => 0,
                ],
                [
                    'id' => 804,
                    'category_id' => 8,
                    'image_path' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Kamera Dome Indoor dengan fitur AI Smart Detection untuk area lobi kantor, perbankan, dan rumah sakit.',
                    'alt_text' => 'Kamera CCTV Dome Indoor',
                    'sort_order' => 4,
                    'is_primary' => 0,
                ],
                [
                    'id' => 805,
                    'category_id' => 8,
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Penyimpanan rekaman NVR/DVR di rak server dengan proteksi UPS dan akses pemantauan mobile 24/7.',
                    'alt_text' => 'Sistem Perekam NVR CCTV',
                    'sort_order' => 5,
                    'is_primary' => 0,
                ],
            ],
        ],
    ];
}

/**
 * Konten resmi "Why Choose Us?" di Beranda & halaman Slide Show
 */
function get_why_choose_us_content(): array {
    return [
        'title' => 'Why Choose Us?',
        'paragraphs' => [
            'Perkembangan dunia IT yang begitu cepat, memberikan efek yang tidak kecil terhadap segala sudut kehidupan. Era internet pun datang merambah seluruh ruang lingkup aktivitas kita. Laju perkembangan IT berimbas pada semakin meningkatnya kebutuhan akan penerapan IT dalam dunia bisnis. Persaingan bisnis yang semakin keras, menuntut  untuk bisa berinovasi dan menerapkan tool-tool pendukung untuk selalu bisa bertahan atau minimal tidak tertinggal jauh dan selalu terhubung. Salah satu tool pendukung itu adalah pemberdayaan teknologi informasi (TI).Untuk memenuhi kebutuhan itu, Kami hadir membawa solusi dalam bidang IT.',
            'Creative Com, merupakan perusahaan yang bergerak di bidang IT, dengan menyediakan berbagai produk untuk mendukung infrastruktur anda dalam berbagai bidang khususnya teknologi informasi. Prinsip kami adalah memberikan solusi terbaik dengan produk yang berkualitas. Kepuasan pelanggan merupakan tujuan utama kami dalam memberikan pelayanan.'
        ]
    ];
}

/**
 * Pastikan tabel `slideshow_categories` dan `slideshow_images` tersedia di database
 * dan terisi dengan 8 kategori default beserta multi-image seed data apabila masih kosong.
 */
function ensure_slideshow_tables(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `slideshow_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(180) NOT NULL,
            `slug` VARCHAR(200) NOT NULL UNIQUE,
            `short_description` TEXT DEFAULT NULL,
            `full_description` LONGTEXT NOT NULL,
            `cover_image` VARCHAR(255) DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `slideshow_images` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT NOT NULL,
            `image_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(255) DEFAULT NULL,
            `alt_text` VARCHAR(180) DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_slideshow_category` (`category_id`),
            CONSTRAINT `fk_slideshow_images_category` FOREIGN KEY (`category_id`) REFERENCES `slideshow_categories` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $defaults = get_default_slideshow_categories();
        $count = (int)$pdo->query("SELECT COUNT(*) FROM slideshow_categories")->fetchColumn();
        if ($count === 0) {
            $stmt_cat = $pdo->prepare("INSERT INTO slideshow_categories (title, slug, short_description, full_description, cover_image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_img = $pdo->prepare("INSERT INTO slideshow_images (category_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($defaults as $cat) {
                $stmt_cat->execute([
                    $cat['title'],
                    $cat['slug'],
                    $cat['short_description'],
                    $cat['full_description'],
                    $cat['cover_image'],
                    (int)$cat['sort_order'],
                    (int)$cat['is_active'],
                ]);
                $new_cat_id = (int)$pdo->lastInsertId();
                if ($new_cat_id > 0 && !empty($cat['images'])) {
                    foreach ($cat['images'] as $img) {
                        $stmt_img->execute([
                            $new_cat_id,
                            $img['image_path'],
                            $img['caption'],
                            $img['alt_text'],
                            (int)$img['sort_order'],
                            (int)$img['is_primary'],
                        ]);
                    }
                }
            }
        } else {
            // Pastikan kategori ke-8 (CCTV) dan urutan/snippet terbaru tersinkronisasi
            $stmt_check = $pdo->prepare("SELECT id FROM slideshow_categories WHERE slug = ? LIMIT 1");
            $stmt_upd = $pdo->prepare("UPDATE slideshow_categories SET title = ?, short_description = ?, sort_order = ? WHERE id = ?");
            $stmt_ins_cat = $pdo->prepare("INSERT INTO slideshow_categories (title, slug, short_description, full_description, cover_image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_ins_img = $pdo->prepare("INSERT INTO slideshow_images (category_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($defaults as $cat) {
                $stmt_check->execute([$cat['slug']]);
                $existing_id = (int)$stmt_check->fetchColumn();
                if ($existing_id > 0) {
                    $stmt_upd->execute([$cat['title'], $cat['short_description'], (int)$cat['sort_order'], $existing_id]);
                } else {
                    $stmt_ins_cat->execute([
                        $cat['title'],
                        $cat['slug'],
                        $cat['short_description'],
                        $cat['full_description'],
                        $cat['cover_image'],
                        (int)$cat['sort_order'],
                        (int)$cat['is_active'],
                    ]);
                    $new_cat_id = (int)$pdo->lastInsertId();
                    if ($new_cat_id > 0 && !empty($cat['images'])) {
                        foreach ($cat['images'] as $img) {
                            $stmt_ins_img->execute([
                                $new_cat_id,
                                $img['image_path'],
                                $img['caption'],
                                $img['alt_text'],
                                (int)$img['sort_order'],
                                (int)$img['is_primary'],
                            ]);
                        }
                    }
                }
            }
            try {
                $old_monopole_url = 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80';
                $new_monopole_url = 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80';
                $pdo->prepare("UPDATE slideshow_categories SET cover_image = ? WHERE cover_image = ?")->execute([$new_monopole_url, $old_monopole_url]);
                $pdo->prepare("UPDATE slideshow_images SET image_path = ? WHERE image_path = ?")->execute([$new_monopole_url, $old_monopole_url]);
                $pdo->prepare("UPDATE hero_sliders SET image_url = ? WHERE image_url = ?")->execute([$new_monopole_url, $old_monopole_url]);
            } catch (Exception $e) {}
        }
    } catch (Exception $e) {
        // Abaikan jika user DB tidak memiliki izin DDL
    }
}

/**
 * Ambil seluruh gambar milik suatu kategori Slide Show berdasarkan `category_id`
 */
function get_slideshow_images(PDO $pdo, int $category_id): array {
    try {
        ensure_slideshow_tables($pdo);
        $stmt = $pdo->prepare("SELECT * FROM slideshow_images WHERE category_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$category_id]);
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    foreach (get_default_slideshow_categories() as $cat) {
        if ((int)$cat['id'] === $category_id) {
            return $cat['images'] ?? [];
        }
    }
    return [];
}

/**
 * Ambil daftar kategori Slide Show beserta array `images` dan `image_count`
 */
function get_slideshow_categories(PDO $pdo, bool $only_active = true): array {
    try {
        ensure_slideshow_tables($pdo);
        $sql = "SELECT * FROM slideshow_categories";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
            $stmt_imgs = $pdo->prepare("SELECT * FROM slideshow_images WHERE category_id = ? ORDER BY sort_order ASC, id ASC");
            foreach ($rows as &$r) {
                $stmt_imgs->execute([(int)$r['id']]);
                $imgs = $stmt_imgs->fetchAll();
                $r['images'] = is_array($imgs) ? $imgs : [];
                $r['image_count'] = count($r['images']);
                if (empty($r['cover_image']) && !empty($r['images'][0]['image_path'])) {
                    $r['cover_image'] = $r['images'][0]['image_path'];
                }
            }
            unset($r);
            return $rows;
        }
    } catch (Exception $e) {}

    $defaults = get_default_slideshow_categories();
    if ($only_active) {
        $defaults = array_values(array_filter($defaults, function ($c) {
            return !empty($c['is_active']);
        }));
    }
    foreach ($defaults as &$d) {
        $d['image_count'] = isset($d['images']) && is_array($d['images']) ? count($d['images']) : 0;
    }
    unset($d);
    return $defaults;
}

/**
 * Ambil 1 kategori Slide Show berdasarkan `slug` beserta seluruh daftar gambarnya (`images`)
 */
function get_slideshow_category_by_slug(PDO $pdo, string $slug, bool $only_active = true): ?array {
    $clean_slug = trim($slug);
    if ($clean_slug === '') {
        return null;
    }

    try {
        ensure_slideshow_tables($pdo);
        $sql = "SELECT * FROM slideshow_categories WHERE slug = ?";
        if ($only_active) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$clean_slug]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt_imgs = $pdo->prepare("SELECT * FROM slideshow_images WHERE category_id = ? ORDER BY sort_order ASC, id ASC");
            $stmt_imgs->execute([(int)$row['id']]);
            $imgs = $stmt_imgs->fetchAll();
            $row['images'] = is_array($imgs) ? $imgs : [];
            $row['image_count'] = count($row['images']);
            if (empty($row['cover_image']) && !empty($row['images'][0]['image_path'])) {
                $row['cover_image'] = $row['images'][0]['image_path'];
            }
            return $row;
        }

        // Jika tabel tersedia namun slug tidak ditemukan atau nonaktif, pastikan cek apakah tabel sudah terisi
        $total_in_db = (int)$pdo->query("SELECT COUNT(*) FROM slideshow_categories")->fetchColumn();
        if ($total_in_db > 0) {
            return null;
        }
    } catch (Exception $e) {}

    foreach (get_default_slideshow_categories() as $cat) {
        if ($cat['slug'] === $clean_slug) {
            if ($only_active && empty($cat['is_active'])) {
                return null;
            }
            $cat['image_count'] = isset($cat['images']) && is_array($cat['images']) ? count($cat['images']) : 0;
            return $cat;
        }
    }
    return null;
}

/**
 * Format teks deskripsi teknis Slide Show (mendukung sub-heading `### `, bullet list `- `, dan paragraf)
 * menjadi HTML terstruktur yang aman dari XSS.
 */
function format_slideshow_description_html(string $text): string {
    $blocks = preg_split("/\r?\n\r?\n+/", trim($text));
    if (!$blocks) {
        return '';
    }
    $html = '';
    foreach ($blocks as $block) {
        $trimmed = trim($block);
        if ($trimmed === '') {
            continue;
        }
        $lines = array_values(array_filter(array_map('trim', preg_split("/\r?\n/", $trimmed)), function ($l) {
            return $l !== '';
        }));
        if (empty($lines)) {
            continue;
        }

        $first_line = $lines[0];
        $has_heading = strpos($first_line, '### ') === 0;
        $heading_text = $has_heading ? trim(substr($first_line, 4)) : '';
        $remaining = $has_heading ? array_slice($lines, 1) : $lines;

        $all_bullets = !empty($remaining) && count(array_filter($remaining, function ($l) {
            return strpos($l, '- ') === 0 || strpos($l, '* ') === 0;
        })) === count($remaining);

        if ($has_heading && empty($remaining)) {
            $html .= '<h3 class="text-lg font-extrabold text-slate-900 dark:text-white mt-6 mb-2 flex items-center gap-2">'
                   . '<span class="w-2 h-5 rounded-full bg-blue-600 inline-block"></span>'
                   . e($heading_text)
                   . '</h3>';
        } elseif ($all_bullets) {
            if ($has_heading) {
                $html .= '<div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-slate-800">'
                       . '<h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white mb-3.5 flex items-center gap-2.5">'
                       . '<span class="w-2 h-5 rounded-full bg-blue-600 inline-block"></span>'
                       . e($heading_text)
                       . '</h3>';
            } else {
                $html .= '<div class="mt-4">';
            }
            $html .= '<ul class="grid sm:grid-cols-2 gap-3">';
            foreach ($remaining as $item) {
                $clean_item = trim(preg_replace('/^[-*]\s+/', '', $item));
                $parts = explode(':', $clean_item, 2);
                $html .= '<li class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-sm text-slate-700 dark:text-slate-200 leading-relaxed">';
                $html .= '<span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">✓</span>';
                if (count($parts) === 2 && strlen($parts[0]) < 45) {
                    $html .= '<span><strong class="font-bold text-slate-900 dark:text-white">' . e($parts[0]) . ':</strong>' . e($parts[1]) . '</span>';
                } else {
                    $html .= '<span>' . e($clean_item) . '</span>';
                }
                $html .= '</li>';
            }
            $html .= '</ul></div>';
        } elseif ($has_heading) {
            $html .= '<div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/70 mt-4">';
            $html .= '<h3 class="text-base font-extrabold text-blue-700 dark:text-blue-400 mb-2 flex items-center gap-2">'
                   . '<span class="w-2 h-2 rounded-full bg-blue-600"></span>'
                   . e($heading_text)
                   . '</h3>';
            foreach ($remaining as $para) {
                $html .= '<p class="text-sm sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed mt-1.5">' . e($para) . '</p>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px] sm:text-base mb-4">'
                   . nl2br(e(implode("\n", $lines)))
                   . '</p>';
        }
    }
    return $html;
}

/**
 * Daftar proyek portofolio default beserta galeri multi-gambar (`images`) untuk masing-masing proyek
 */
function get_default_portfolio_projects_with_images(): array {
    return [
        [
            'id' => 1,
            'title' => 'Pabrikasi Guywire Tower',
            'client_or_location' => 'Workshop Creative Com Palembang',
            'description' => 'Pemotongan, perakitan dan pengelasan modul guywire tower oleh tim teknisi internal di workshop resmi Creative Com.',
            'category' => 'Fabrikasi Tower',
            'image_url' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pabrikasi dan perakitan struktur segitiga Guywire Tower di Workshop Creative Com Palembang.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Proses pengelasan presisi dan inspeksi sambungan batang pipa baja menara.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Persiapan pengiriman modul Guywire Tower beserta aksesori kawat seling baja galvanis ke lokasi proyek.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'External Lightning Protect & Grounding Installation',
            'client_or_location' => 'Lokasi Proyek Industri',
            'description' => 'Pemasangan proteksi penangkal petir eksternal dan pengeboran grounding elektroda untuk proteksi sambaran petir.',
            'category' => 'Penangkal Petir',
            'image_url' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan batang penyalur petir eksternal pada titik tertinggi fasilitas industri.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Penarikan jalur kabel konduktor tembaga menuju sumur tempat pembumian (grounding).',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi bak kontrol dan batang pembumian (ground rod) tembaga berlapis baja.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'Pengujian Nilai Tahanan Tanah (Earth Resistance Tester)',
            'client_or_location' => 'Area Pembumian Proyek',
            'description' => 'Pengukuran resistansi pembumian menggunakan Earth Resistance Tester digital untuk menjamin nilai tanah di bawah 2 Ohm.',
            'category' => 'Penangkal Petir',
            'image_url' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian nilai resistansi tanah menggunakan Digital Earth Resistance Tester (< 2 Ohm).',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Verifikasi koneksi busbar tembaga pada bak kontrol grounding bersama tim pengawas.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Dokumentasi hasil ukur tahanan pembumian sesuai standar keselamatan kelistrikan.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'Arrester Panel MDP',
            'client_or_location' => 'RSUD Kayu Agung',
            'description' => 'Instalasi surge arrester panel Main Distribution Panel (MDP) untuk melindungi trafo dan instrumen medis dari lonjakan daya petir.',
            'category' => 'Penangkal Petir',
            'image_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan Surge Protection Device (Arrester) pada Main Distribution Panel (MDP) RSUD Kayu Agung.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Integrasi jalur grounding internal panel distribusi untuk proteksi peralatan medis rumah sakit.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemeriksaan indikator status modul arrester pasca instalasi oleh teknisi PT. KGS.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'Arrester CCTV',
            'client_or_location' => 'PLTU Talang Duku PT. PLN',
            'description' => 'Pemasangan video & power arrester pada kamera CCTV outdoor di area pembangkit tenaga listrik.',
            'category' => 'CCTV & Security',
            'image_url' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan unit kamera CCTV outdoor beserta proteksi Video & Power Arrester di PLTU Talang Duku.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1580894894513-541e068a3e2b?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Box panel pelindung cuaca (weatherproof enclosure) dan modul arrester kamera pengawas.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian tampilan video monitoring CCTV di ruang kontrol pembangkit listrik.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 6,
            'title' => 'Instalasi Tower & Jaringan Networking BBIP Group',
            'client_or_location' => 'Site STO Telkom Jambi & Mill Jambi',
            'description' => 'Ereksi tower transmisi dan terminasi kabel jaringan komunikasi data dari Site STO Telkom menuju pabrik kelapa sawit.',
            'category' => 'Networking & Tower',
            'image_url' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Ereksi menara transmisi dan pointing antena Point-to-Point di Site STO Telkom Jambi.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan perangkat radio wireless outdoor pada menara komunikasi Mill Jambi BBIP Group.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Terminasi jaringan kabel data dan konfigurasi router penghubung antar site perkebunan.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 7,
            'title' => 'Door Phone & Audio Intercom',
            'client_or_location' => 'Hotel Arya Duta Palembang',
            'description' => 'Instalasi interkom komunikasi internal door phone pada kamar dan pintu layanan hotel bintang 5.',
            'category' => 'Telekomunikasi',
            'image_url' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 18,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi sistem Door Phone & Audio Intercom di area layanan Hotel Arya Duta Palembang.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan unit panel interkom dinding dan integrasi kunci elektrik pintu akses.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian kualitas suara dua arah antara unit kamar dan resepsionis utama.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 8,
            'title' => 'Pemasangan CCTV Security System Terintegrasi',
            'client_or_location' => 'PT. OKI Pulp & Paper',
            'description' => 'Pemasangan kamera CCTV keamanan, tiang pemantau dan kabel link di kawasan industri pabrik bubur kertas terbesar.',
            'category' => 'CCTV & Security',
            'image_url' => 'https://images.unsplash.com/photo-1580894894513-541e068a3e2b?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 19,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1580894894513-541e068a3e2b?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi kamera CCTV perimeter dan tiang pantau di kawasan industri PT. OKI Pulp & Paper.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan IP Camera beresolusi tinggi untuk pemantauan area produksi dan logistik 24 jam.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konfigurasi NVR dan layar Central Monitoring System (CMS) di ruang sekuriti pabrik.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 9,
            'title' => 'Pemasangan Jaringan Intranet Link',
            'client_or_location' => 'ADR Group Jambi & Sumsel',
            'description' => 'Pemasangan link radio komunikasi data dan router intranet antar lokasi kebun sawit dengan pusat kendali operasional.',
            'category' => 'Networking & Tower',
            'image_url' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 19,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konfigurasi Router dan switch distribusi untuk jaringan Intranet Link ADR Group Jambi & Sumsel.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi antena radio Point-to-Point penghubung jaringan data antar kebun sawit.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian throughput bandwidth dan kestabilan latency interkoneksi intranet korporat.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 10,
            'title' => 'Pemasangan Panel Solar Sel Tenaga Surya',
            'client_or_location' => 'PT. OKI Pulp & Paper',
            'description' => 'Instalasi modul panel sel surya mandiri untuk suplai energi perangkat pengawas dan sensor transmisi di area terbuka.',
            'category' => 'Solusi Terintegrasi',
            'image_url' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 19,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan modul Panel Solar Sel Tenaga Surya mandiri di area terbuka PT. OKI Pulp & Paper.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi baterai penyimpanan daya (deep cycle battery) dan solar charge controller pada tiang perangkat.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Suplai daya otonom 24 jam untuk perangkat kamera pengawas dan radio transmisi di titik remote.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 11,
            'title' => 'Pemasangan Repeater HT MUBA Electric Power',
            'client_or_location' => 'Sungai Lalan, Kab. Musi Banyuasin',
            'description' => 'Pemasangan perangkat stasiun pengulang sinyal (repeater) Handy Talky frekuensi VHF untuk koordinasi operasional tenaga listrik daerah.',
            'category' => 'Radio Komunikasi',
            'image_url' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 20,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan antena omni VHF dan kabel heliax low-loss pada menara Repeater HT MUBA Electric Power.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Konfigurasi unit Repeater VHF, cavity duplexer, dan power supply cadangan di Sungai Lalan.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengukuran nilai VSWR antena dan uji jangkauan komunikasi Handy Talky lintas rayon.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 12,
            'title' => 'Pemasangan Sistem Penangkal Petir Terpadu',
            'client_or_location' => 'PT. PISS Langkat, Sumatera Utara',
            'description' => 'Pengeboran sumur grounding, pemasangan head terminal petir radius proteksi luas dan pengkabelan tembaga konduktor.',
            'category' => 'Penangkal Petir',
            'image_url' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 20,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pemasangan head terminal penangkal petir elektrostatis radius luas di PT. PISS Langkat, Sumatera Utara.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengeboran sumur grounding dalam dan penanaman elektroda tembaga masif.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Pengujian tahanan tanah (< 2 Ohm) dan instalasi Lightning Counter pencatat sambaran petir.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 13,
            'title' => 'Pemasangan Jaringan PT. JAPFA COMFEED Tbk',
            'client_or_location' => 'Site Jambi dan Padang',
            'description' => 'Pemasangan infrastruktur jaringan komputer, penarikan kabel data dan konfigurasi perangkat jaringan di fasilitas pakan ternak.',
            'category' => 'Networking & Tower',
            'image_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 20,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi rack server, patch panel, dan switch gigabit di fasilitas PT. JAPFA COMFEED Tbk Site Jambi & Padang.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Penarikan kabel backbone Fiber Optik & UTP Cat6 antar gedung operasional dan gudang.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Fluke test dan pelabelan terstruktur seluruh titik jaringan data kantor dan pabrik.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ],
        [
            'id' => 14,
            'title' => 'Pemasangan AIS (Automatic Identification System) Maritim',
            'client_or_location' => 'Tulung Selapan OKI / Perairan Sungai Musi',
            'description' => 'Instalasi AIS Receiver terintegrasi kamera Hikvision pada ketinggian menara 28 meter untuk pemantauan lalu lintas kapal secara otomatis.',
            'category' => 'AIS & Navigasi',
            'image_url' => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80',
            'pdf_page' => 15,
            'images' => [
                [
                    'image_path' => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Instalasi antena AIS Receiver VHF dan kamera PTZ Hikvision pada ketinggian menara 28 meter di Tulung Selapan OKI.',
                    'sort_order' => 1,
                    'is_primary' => 1
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Tampilan layar pemantauan posisi kapal tongkang dan tugboat secara real-time di alur perairan.',
                    'sort_order' => 2,
                    'is_primary' => 0
                ],
                [
                    'image_path' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                    'caption' => 'Infrastruktur menara komunikasi dan transmisi data dari pos pantau dermaga menuju pusat kontrol.',
                    'sort_order' => 3,
                    'is_primary' => 0
                ]
            ]
        ]
    ];
}

/**
 * Pastikan tabel `projects` dan `project_images` tersedia dan memiliki galeri multi-gambar awal
 */
function ensure_portfolio_images_table(PDO $pdo): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `projects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(200) NOT NULL,
            `client_or_location` VARCHAR(150) NOT NULL,
            `description` TEXT NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `image_url` VARCHAR(255) DEFAULT NULL,
            `pdf_page` INT NOT NULL DEFAULT 18,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `project_images` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` INT NOT NULL,
            `image_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(255) DEFAULT NULL,
            `alt_text` VARCHAR(200) DEFAULT NULL,
            `sort_order` INT DEFAULT 1,
            `is_primary` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_project_img` (`project_id`, `sort_order`),
            CONSTRAINT `fk_project_images_proj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $defaults = get_default_portfolio_projects_with_images();
        $default_by_title = [];
        foreach ($defaults as $d) {
            $default_by_title[strtolower(trim($d['title']))] = $d;
        }

        $proj_count = (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        if ($proj_count === 0) {
            $ins_proj = $pdo->prepare("INSERT INTO projects (title, client_or_location, description, category, image_url, pdf_page) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($defaults as $d) {
                $ins_proj->execute([
                    $d['title'],
                    $d['client_or_location'],
                    $d['description'],
                    $d['category'],
                    $d['image_url'],
                    $d['pdf_page']
                ]);
            }
        }

        // Pastikan setiap proyek di `projects` memiliki gambar cover & minimal slide di `project_images`
        $all_projs = $pdo->query("SELECT * FROM projects ORDER BY id ASC")->fetchAll();
        $cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM project_images WHERE project_id = ?");
        $ins_img = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption, alt_text, sort_order, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
        $upd_cover = $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?");

        foreach ($all_projs as $p) {
            $pid = (int)$p['id'];
            $t_key = strtolower(trim((string)$p['title']));
            $def = $default_by_title[$t_key] ?? null;

            if (empty($p['image_url']) && $def && !empty($def['image_url'])) {
                $upd_cover->execute([$def['image_url'], $pid]);
                $p['image_url'] = $def['image_url'];
            }

            $cnt_stmt->execute([$pid]);
            $img_cnt = (int)$cnt_stmt->fetchColumn();
            if ($img_cnt === 0) {
                if ($def && !empty($def['images'])) {
                    foreach ($def['images'] as $img) {
                        $ins_img->execute([
                            $pid,
                            $img['image_path'],
                            $img['caption'],
                            $p['title'],
                            $img['sort_order'],
                            $img['is_primary']
                        ]);
                    }
                } elseif (!empty($p['image_url'])) {
                    $ins_img->execute([
                        $pid,
                        $p['image_url'],
                        $p['title'] . ' — ' . $p['client_or_location'],
                        $p['title'],
                        1,
                        1
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        // Abaikan jika gagal membuat tabel
    }
}

/**
 * Ambil seluruh proyek portofolio beserta daftar gambar (`images`) untuk masing-masing proyek
 */
function get_portfolio_projects(PDO $pdo, string $category_filter = ''): array {
    $cat = trim($category_filter);
    try {
        ensure_portfolio_images_table($pdo);
        $sql = "SELECT * FROM projects WHERE 1=1";
        $params = [];
        if ($cat !== '') {
            $sql .= " AND category = ?";
            $params[] = $cat;
        }
        $sql .= " ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if (!empty($rows)) {
            $img_stmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
            foreach ($rows as &$row) {
                $img_stmt->execute([(int)$row['id']]);
                $imgs = $img_stmt->fetchAll();
                if (empty($imgs) && !empty($row['image_url'])) {
                    $imgs = [[
                        'id' => 0,
                        'project_id' => (int)$row['id'],
                        'image_path' => $row['image_url'],
                        'caption' => $row['title'] . ' — ' . $row['client_or_location'],
                        'alt_text' => $row['title'],
                        'sort_order' => 1,
                        'is_primary' => 1
                    ]];
                }
                $row['images'] = $imgs;
                $row['image_count'] = count($imgs);
                if (empty($row['image_url']) && !empty($imgs[0]['image_path'])) {
                    $row['image_url'] = $imgs[0]['image_path'];
                }
            }
            unset($row);
            return $rows;
        }
    } catch (Exception $e) {}

    $defaults = get_default_portfolio_projects_with_images();
    if ($cat !== '') {
        $defaults = array_values(array_filter($defaults, function ($d) use ($cat) {
            return $d['category'] === $cat;
        }));
    }
    foreach ($defaults as &$d) {
        $d['image_count'] = count($d['images'] ?? []);
    }
    unset($d);
    return $defaults;
}

/**
 * Ambil 1 proyek portofolio berdasarkan ID beserta seluruh gambarnya
 */
function get_portfolio_project_by_id(PDO $pdo, int $project_id): ?array {
    if ($project_id <= 0) {
        return null;
    }
    try {
        ensure_portfolio_images_table($pdo);
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
        $stmt->execute([$project_id]);
        $row = $stmt->fetch();
        if ($row) {
            $img_stmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
            $img_stmt->execute([$project_id]);
            $imgs = $img_stmt->fetchAll();
            if (empty($imgs) && !empty($row['image_url'])) {
                $imgs = [[
                    'id' => 0,
                    'project_id' => $project_id,
                    'image_path' => $row['image_url'],
                    'caption' => $row['title'] . ' — ' . $row['client_or_location'],
                    'alt_text' => $row['title'],
                    'sort_order' => 1,
                    'is_primary' => 1
                ]];
            }
            $row['images'] = $imgs;
            $row['image_count'] = count($imgs);
            if (empty($row['image_url']) && !empty($imgs[0]['image_path'])) {
                $row['image_url'] = $imgs[0]['image_path'];
            }
            return $row;
        }
    } catch (Exception $e) {}

    foreach (get_default_portfolio_projects_with_images() as $d) {
        if ((int)$d['id'] === $project_id) {
            $d['image_count'] = count($d['images'] ?? []);
            return $d;
        }
    }
    return null;
}

/**
 * Ambil daftar lengkap gambar dari pustaka "Media & Upload Gambar" (tabel `media`, folder `/uploads/`,
 * kategori `slideshow.php`, `hero_sliders`, dan `projects`) untuk modal pemilih gambar di Admin CMS.
 */
function get_uploaded_media_library(PDO $pdo): array {
    $items = [];
    $seen  = [];

    $add_item = function (string $raw_path, string $title, string $category, string $source) use (&$items, &$seen): void {
        $path = trim($raw_path);
        if ($path === '' || $path === '#') {
            return;
        }
        if (!preg_match('#^https?://#i', $path) && strpos($path, 'data:') !== 0) {
            $path = ltrim($path, '/');
        }
        if (isset($seen[$path])) {
            return;
        }
        $seen[$path] = true;

        $preview = $path;
        if (!preg_match('#^https?://#i', $preview) && strpos($preview, 'data:') !== 0) {
            $preview = '../' . $preview;
        }

        $items[] = [
            'url'         => $path,
            'preview_url' => $preview,
            'title'       => $title !== '' ? $title : basename($path),
            'category'    => $category !== '' ? $category : 'Media Terunggah',
            'source'      => $source,
        ];
    };

    // 1. Dari tabel `media` (halaman admin/media.php — Media & Upload Gambar)
    try {
        $stmt = $pdo->query("SELECT filename, original_name, filepath, created_at FROM media ORDER BY created_at DESC LIMIT 200");
        while ($row = $stmt->fetch()) {
            $fp = trim((string)($row['filepath'] ?? ''));
            if ($fp === '' && !empty($row['filename'])) {
                $fp = 'uploads/' . ltrim((string)$row['filename'], '/');
            }
            $orig = trim((string)($row['original_name'] ?? $row['filename'] ?? 'File Media'));
            $add_item($fp, $orig, 'Media & Upload Gambar', 'media');
        }
    } catch (Exception $e) {
        // Tabel media mungkin belum ada
    }

    // 2. Pindai file fisik di direktori /uploads/ (termasuk /uploads/slideshow/ dan /uploads/portfolio/)
    $uploads_root = dirname(__DIR__) . '/uploads';
    $scan_dirs = [
        ''          => 'Media & Upload Gambar',
        'slideshow' => 'Upload Slide Show',
        'portfolio' => 'Upload Portofolio',
    ];
    foreach ($scan_dirs as $sub => $label) {
        $dir_path = $sub === '' ? $uploads_root : ($uploads_root . '/' . $sub);
        if (!is_dir($dir_path)) {
            continue;
        }
        $files = @scandir($dir_path);
        if (!is_array($files)) {
            continue;
        }
        foreach ($files as $f) {
            if ($f === '.' || $f === '..' || $f === '.htaccess' || $f === 'index.php') {
                continue;
            }
            $full = $dir_path . '/' . $f;
            if (!is_file($full)) {
                continue;
            }
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }
            $rel = 'uploads/' . ($sub !== '' ? $sub . '/' : '') . $f;
            $add_item($rel, $f, $label, 'media');
        }
    }

    // 3. Dari kategori & galeri Slide Show Infrastruktur (`slideshow.php`)
    try {
        $ss_cats = get_slideshow_categories($pdo, false);
        foreach ($ss_cats as $cat) {
            $cat_title = (string)($cat['title'] ?? 'Slide Show');
            if (!empty($cat['cover_image'])) {
                $add_item((string)$cat['cover_image'], $cat_title . ' (Cover)', $cat_title, 'slideshow');
            }
            if (!empty($cat['images']) && is_array($cat['images'])) {
                foreach ($cat['images'] as $idx => $img) {
                    $img_p = (string)($img['image_path'] ?? '');
                    $cap   = trim((string)($img['caption'] ?? ''));
                    $add_item($img_p, $cap !== '' ? $cap : ($cat_title . ' #' . ($idx + 1)), $cat_title, 'slideshow');
                }
            }
        }
    } catch (Exception $e) {}

    // 4. Dari Slider Beranda (`hero_sliders`)
    try {
        $sliders = get_hero_sliders($pdo, false);
        foreach ($sliders as $sl) {
            if (!empty($sl['image_url'])) {
                $add_item((string)$sl['image_url'], (string)($sl['title'] ?? 'Slide Beranda'), (string)($sl['badge_text'] ?? 'Slider Beranda'), 'slider');
            }
        }
    } catch (Exception $e) {}

    // 5. Dari Portofolio Proyek (`projects` & `project_images`)
    try {
        $projs = get_portfolio_projects($pdo);
        foreach ($projs as $pr) {
            $pr_title = (string)($pr['title'] ?? 'Proyek');
            $pr_cat   = (string)($pr['category'] ?? 'Portofolio');
            if (!empty($pr['image_url'])) {
                $add_item((string)$pr['image_url'], $pr_title, $pr_cat, 'portfolio');
            }
            if (!empty($pr['images']) && is_array($pr['images'])) {
                foreach ($pr['images'] as $p_img) {
                    $add_item((string)($p_img['image_path'] ?? ''), (string)($p_img['caption'] ?? $pr_title), $pr_cat, 'portfolio');
                }
            }
        }
    } catch (Exception $e) {}

    return $items;
}



