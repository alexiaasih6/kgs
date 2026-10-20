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
function clean_plain_text(?string $str, int $max_len = 0): string {
    $clean = trim((string)$str);
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
        '#<(script|style|iframe|object|embed|applet|svg|math|form|input|button|select|textarea|meta|link|base|frame|frameset|xss)\b[^>]*>.*?</\1>#is',
        '',
        $raw
    );
    // Hapus self-closing atau unclosed dangerous tags
    $cleaned = preg_replace(
        '#</?(script|style|iframe|object|embed|applet|svg|math|form|input|button|select|textarea|meta|link|base|frame|frameset|xss)\b[^>]*>#is',
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
            $allowed_attrs_global = ['class', 'id', 'title', 'alt', 'colspan', 'rowspan', 'align'];
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
                                    $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($attr_val));
                                    if (
                                        strpos($normalized, 'javascript:') === 0 ||
                                        strpos($normalized, 'vbscript:') === 0 ||
                                        strpos($normalized, 'data:text/') === 0 ||
                                        strpos($normalized, 'data:image/svg') === 0
                                    ) {
                                        $remove_attrs[] = $attr->nodeName;
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

    // 4. Fallback regex pembersihan atribut on* dan protokol javascript:/data: jika DOMDocument gagal
    $cleaned = preg_replace('/\s+on[a-zA-Z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string)$cleaned);
    $cleaned = preg_replace('/(href|src|action)\s*=\s*["\']?\s*(?:javascript|vbscript|data\s*:\s*text\/html)[^"\'>\s]*/i', '$1="#"', (string)$cleaned);

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
 * Ambil pengaturan situs dari database dengan caching per-request yang dapat di-reset.
 * Database (`site_settings`) adalah sumber kebenaran utama (Single Source of Truth).
 */
function get_setting(PDO $pdo, string $key, string $default = ''): string {
    if (!isset($GLOBALS['__kgs_settings_cache']) || !is_array($GLOBALS['__kgs_settings_cache'])) {
        $GLOBALS['__kgs_settings_cache'] = [];
        try {
            $stmt = $pdo->query("SELECT key_name, key_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $GLOBALS['__kgs_settings_cache'][$row['key_name']] = $row['key_value'];
            }
        } catch (Exception $e) {
            // Tabel mungkin belum diimpor
        }
    }

    $cache = $GLOBALS['__kgs_settings_cache'];

    // Alias kompatibilitas otomatis antar key pengaturan
    if (array_key_exists($key, $cache) && $cache[$key] !== null && $cache[$key] !== '') {
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
        $liveVal = $live['settings'][$key];
        if ($liveVal !== null && $liveVal !== '') {
            return (string)$liveVal;
        }
    }

    return $default;
}

/**
 * Simpan satu pengaturan ke `site_settings` dan langsung perbarui cache memori & alias terkait.
 */
function save_setting(PDO $pdo, string $key, string $value, string $group = 'general'): bool {
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
            'title' => 'Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider',
            'slug' => 'beranda',
            'summary' => 'Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.',
            'content' => '<p>PT. Kreatif Global Solusindo (KGS) menghadirkan infrastruktur jaringan komputer, fiber optik NETVIEL, sentral telepon PABX Panasonic, radio komunikasi 2-arah, pengawasan CCTV Sony & Hikvision, proteksi penangkal petir, serta koneksi internet KGSNet resmi APJII dengan dukungan NOC 24/7 dan SLA 99.5%.</p>',
            'meta_title' => 'Beranda - System Integrator, Security Solution & ISP',
            'meta_description' => 'Solusi jaringan komputer, fiber optik, PABX, CCTV, penangkal petir, dan ISP KGSNet resmi APJII.',
            'sort_order' => 1
        ],
        'tentang-kami' => [
            'title' => 'Tentang PT. Kreatif Global Solusindo',
            'slug' => 'tentang-kami',
            'summary' => 'Perjalanan dedikasi teknologi sejak didirikan dengan nama Creative Com pada tahun 1997 di Palembang hingga bertransformasi menjadi System Integrator & ISP berlisensi resmi APJII.',
            'content' => '<p>Berawal dari CV. CREATIVE pada tahun 1997 di Palembang, kami terus bertumbuh menjadi distributor perangkat telekomunikasi, dealer resmi PABX Panasonic (2002), kontraktor jaringan & menara tower (2004), bertransformasi menjadi PT. Kreatif Global Solusindo (2013), hingga resmi memegang izin penyelenggara jasa internet (ISP) KGSNet dan anggota APJII (2020).</p>',
            'meta_title' => 'Tentang Kami - Profil Perusahaan PT. KGS',
            'meta_description' => 'Identitas, sejarah perjalanan, visi misi, dan mitra kerja resmi PT. Kreatif Global Solusindo.',
            'sort_order' => 2
        ],
        'layanan' => [
            'title' => '5 Segmen Layanan & Solusi Terintegrasi',
            'slug' => 'layanan',
            'summary' => 'Infrastruktur komputasi, telekomunikasi radio & PABX, surveillance keamanan CCTV, internet ISP KGSNet, hingga fabrikasi tower dan sistem proteksi petir.',
            'content' => '<p>Katalog lengkap solusi teknologi informasi, telekomunikasi, kamera pengawas CCTV, konektivitas ISP KGSNet, pabrikasi menara guywire, sistem penangkal petir grounding < 2 Ohm, serta sistem AIS maritim untuk korporasi, perkebunan, pertambangan, perbankan, dan instansi pemerintah.</p>',
            'meta_title' => 'Katalog Layanan & Solusi Terintegrasi - PT. KGS',
            'meta_description' => '5 segmen layanan utama PT. Kreatif Global Solusindo untuk bisnis dan instansi.',
            'sort_order' => 3
        ],
        'harga' => [
            'title' => 'Pilihan Paket Solusi & Layanan B2B',
            'slug' => 'harga',
            'summary' => 'Struktur paket transparan yang dirancang khusus untuk kebutuhan konektivitas bisnis, keamanan terpadu, infrastruktur remote site, dan pengadaan proyek pemerintah/BUMN dengan SLA terjamin.',
            'content' => '<p>Setiap paket solusi bisnis dirancang sesuai standar reliabilitas industri dengan dukungan survei lokasi (on-site survey) dan penyusunan Bill of Quantity (BoQ) yang presisi sesuai kebutuhan RKS perusahaan atau instansi Anda.</p>',
            'meta_title' => 'Pilihan Paket & Solusi Bisnis (B2B) - PT. KGS',
            'meta_description' => 'Daftar paket Dedicated Internet KGSNet, CCTV Surveillance, dan Tower Guywire PT. KGS.',
            'sort_order' => 4
        ],
        'faq' => [
            'title' => 'Pertanyaan Umum & Jawaban Teknis',
            'slug' => 'faq',
            'summary' => 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.',
            'content' => '<p>Temukan jawaban teknis mengenai jangkauan layanan operasional Sumatera hingga nasional, lisensi resmi ISP KGSNet APJII, prosedur survei lapangan, serta garansi pengujian tahanan pembumian di bawah 2 Ohm.</p>',
            'meta_title' => 'Tanya Jawab (FAQ) & Bantuan Teknis - PT. KGS',
            'meta_description' => 'Pertanyaan umum seputar layanan ISP KGSNet, grounding, tower, dan tender B2B.',
            'sort_order' => 5
        ],
        'portofolio' => [
            'title' => 'Portofolio Proyek & Klien Korporasi',
            'slug' => 'portofolio',
            'summary' => 'Bukti nyata dedikasi teknis PT. KGS: dari pabrikasi mandiri tower guywire dan pengujian grounding di bawah 2 Ohm, hingga integrasi jaringan dan sistem keamanan untuk 100+ mitra BUMN dan korporasi.',
            'content' => '<p>Dokumentasi pengerjaan instalasi lapangan oleh tim teknisi bersertifikasi PT. Kreatif Global Solusindo di berbagai sektor strategis: BUMN, perbankan, rumah sakit, perkebunan kelapa sawit, pertambangan batubara, dan navigasi pelabuhan.</p>',
            'meta_title' => 'Dokumentasi Proyek & Portofolio Nyata - PT. KGS',
            'meta_description' => 'Rekam jejak proyek lapangan dan daftar klien terverifikasi PT. Kreatif Global Solusindo.',
            'sort_order' => 6
        ],
        'kontak' => [
            'title' => 'Hubungi Tim PT. Kreatif Global Solusindo',
            'slug' => 'kontak',
            'summary' => 'Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.',
            'content' => '<p>Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, penjadwalan survei lokasi (on-site survey), serta pengajuan proposal penawaran harga resmi.</p>',
            'meta_title' => 'Kontak & Permintaan Penawaran - PT. KGS',
            'meta_description' => 'Hubungi Alamat Kantor PT. Kreatif Global Solusindo.',
            'sort_order' => 7
        ],
        'blog' => [
            'title' => 'Artikel Solusi Teknologi & Praktik Terbaik Industri',
            'slug' => 'blog',
            'summary' => 'Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.',
            'content' => '<p>Pusat edukasi dan publikasi artikel teknis dari tim engineer PT. Kreatif Global Solusindo seputar infrastruktur jaringan serat optik, proteksi petir industri, telekomunikasi radio, dan navigasi maritim.</p>',
            'meta_title' => 'Artikel & Wawasan Teknologi - PT. KGS',
            'meta_description' => 'Kumpulan artikel edukasi teknologi informasi dan telekomunikasi industri PT. KGS.',
            'sort_order' => 8
        ],
        'profil-perusahaan' => [
            'title' => 'Profil & Sejarah Perusahaan',
            'slug' => 'profil-perusahaan',
            'summary' => 'Sejarah Creative Com sejak 1997 hingga menjadi PT. Kreatif Global Solusindo dan ISP berizin APJII.',
            'content' => '<h2>Sejarah Berdiri</h2><p>Creative Com adalah nama tempat usaha yang menjual produk IT dengan legalitas CV. CREATIVE, berdomisili di Palembang sejak 1997 dan sejak 2013 menjadi PT. KREATIF GLOBAL SOLUSINDO. Pada awal berdirinya Creative Com bergerak dalam bidang jasa perbaikan perangkat computer dan jaringan. Kemudian di 1998, merambah kebidang penjualan perangkat wartel sebagai distributor perangkat wartel merk QPHONE. Lalu pada 2002, Panasonic mempercayakan CREATIVE COM sebagai salah satu dealernya di Palembang. Dengan mengamati perkembangan teknologi dan kebutuhan masyarakat, maka sejak 2004 melayani permintaan pemasangan jaringan komputer (Cable / Wireless), radio komunikasi, pabx serta tower antenna. Dan saat ini, sejak tahun 2020, PT. KGS juga telah resmi memiliki izin sebagai perusahaan penyelenggara jasa internet (ISP) dengan nama produk KGSNet dan tergabung menjadi anggota APJII.</p>',
            'meta_title' => 'Profil & Sejarah Perusahaan - PT. KGS',
            'meta_description' => 'Sejarah resmi perjalanan PT. Kreatif Global Solusindo dari tahun 1997 hingga kini.',
            'sort_order' => 9
        ],
        'visi-misi-budaya' => [
            'title' => 'Visi, Budaya & Komitmen',
            'slug' => 'visi-misi-budaya',
            'summary' => 'Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan.',
            'content' => '<h2>Visi & Misi</h2><p><strong>Visi:</strong> Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.</p><p><strong>Misi:</strong> Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.</p><h2>Budaya Organisasi</h2><p>Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif.</p>',
            'meta_title' => 'Visi, Misi & Budaya Kerja - PT. KGS',
            'meta_description' => 'Visi terdepan, misi kemandirian, dan komitmen kepuasan pelanggan PT. KGS.',
            'sort_order' => 10
        ],
        'kebijakan-privasi' => [
            'title' => 'Kebijakan Privasi Pengguna',
            'slug' => 'kebijakan-privasi',
            'summary' => 'Komitmen PT. Kreatif Global Solusindo dalam menjaga kerahasiaan dan keamanan data pelanggan, rekanan, dan calon klien.',
            'content' => '<h2>1. Pengumpulan Informasi</h2><p>PT. Kreatif Global Solusindo (KGS) menghormati dan melindungi privasi setiap pengunjung situs dan pelanggan kami. Informasi yang dikumpulkan melalui formulir konsultasi, permintaan penawaran harga, atau korespondensi langsung (seperti nama, nama perusahaan, nomor telepon, alamat email, dan lokasi proyek) hanya digunakan untuk kepentingan verifikasi teknis, penyusunan proposal solusi, dan komunikasi resmi perusahaan.</p><h2>2. Keamanan Data Pelanggan</h2><p>Kami menerapkan prosedur teknis dan manajemen yang ketat untuk mengamankan data Anda dari akses tanpa izin, perubahan, atau pengungkapan yang tidak sah. Seluruh data penawaran harga disimpan dalam server yang aman dengan enkripsi standar industri.</p><h2>3. Tidak Ada Pembagian Data kepada Pihak Ketiga</h2><p>Kami tidak akan menjual, menyewakan, atau membagikan informasi kontak pribadi atau data korporasi Anda kepada pihak ketiga manapun untuk tujuan pemasaran komersial tanpa persetujuan eksplisit dari Anda, kecuali jika diwajibkan oleh peraturan hukum yang berlaku di Republik Indonesia.</p><h2>4. Hubungi Kami</h2><p>Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: <strong>info@kgs.net.id</strong> atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.</p>',
            'meta_title' => 'Kebijakan Privasi - PT. KGS',
            'meta_description' => 'Kebijakan privasi perlindungan data pengguna PT. Kreatif Global Solusindo.',
            'sort_order' => 11
        ],
        'klien' => [
            'title' => 'Klien & Sektor Industri yang Kami Layani',
            'slug' => 'klien',
            'summary' => 'Sejak 1997, PT. Kreatif Global Solusindo dipercaya menangani infrastruktur jaringan, telekomunikasi, keamanan, dan konektivitas internet untuk berbagai institusi di Sumatera Selatan dan Indonesia.',
            'content' => '<p>Daftar klien korporasi, BUMN, perbankan, rumah sakit, perhotelan, pendidikan, pertambangan, perkebunan, dan pelayaran maritim yang mempercayakan infrastruktur IT & telekomunikasi kepada PT. Kreatif Global Solusindo.</p>',
            'meta_title' => 'Klien & Sektor Industri Mitra - PT. KGS',
            'meta_description' => 'Daftar klien dan mitra industri PT. Kreatif Global Solusindo (KGS) dari sektor BUMN, perbankan, rumah sakit, dan korporasi.',
            'sort_order' => 12
        ]
    ];
}

/**
 * Pastikan seluruh halaman sistem tersedia di tabel `pages`
 */
function ensure_pages_complete(PDO $pdo): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
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
 */
function get_default_navigation_menus(): array {
    return [
        ['id' => 1,  'title' => 'Beranda',           'url' => 'index.php',                        'location' => 'header', 'sort_order' => 1, 'target' => '_self', 'is_active' => 1],
        ['id' => 2,  'title' => 'Tentang Kami',      'url' => 'tentang.php',                      'location' => 'header', 'sort_order' => 2, 'target' => '_self', 'is_active' => 1],
        ['id' => 3,  'title' => 'Layanan',           'url' => 'layanan.php',                      'location' => 'header', 'sort_order' => 3, 'target' => '_self', 'is_active' => 1],
        ['id' => 4,  'title' => 'Harga',             'url' => 'harga.php',                        'location' => 'header', 'sort_order' => 4, 'target' => '_self', 'is_active' => 1],
        ['id' => 5,  'title' => 'FAQ',               'url' => 'faq.php',                          'location' => 'header', 'sort_order' => 5, 'target' => '_self', 'is_active' => 1],
        ['id' => 6,  'title' => 'Portofolio',        'url' => 'portofolio.php',                   'location' => 'header', 'sort_order' => 6, 'target' => '_self', 'is_active' => 1],
        ['id' => 7,  'title' => 'Kontak',            'url' => 'kontak.php',                       'location' => 'header', 'sort_order' => 7, 'target' => '_self', 'is_active' => 1],
        ['id' => 8,  'title' => 'Profil Perusahaan', 'url' => 'page.php?slug=profil-perusahaan',  'location' => 'footer', 'sort_order' => 1, 'target' => '_self', 'is_active' => 1],
        ['id' => 9,  'title' => 'Visi & Misi',       'url' => 'page.php?slug=visi-misi-budaya',   'location' => 'footer', 'sort_order' => 2, 'target' => '_self', 'is_active' => 1],
        ['id' => 10, 'title' => 'Katalog Layanan',   'url' => 'layanan.php',                      'location' => 'footer', 'sort_order' => 3, 'target' => '_self', 'is_active' => 1],
        ['id' => 11, 'title' => 'Artikel Edukasi',   'url' => 'blog.php',                         'location' => 'footer', 'sort_order' => 4, 'target' => '_self', 'is_active' => 1],
        ['id' => 12, 'title' => 'Kebijakan Privasi', 'url' => 'kebijakan-privasi.php',            'location' => 'footer', 'sort_order' => 5, 'target' => '_self', 'is_active' => 1],
    ];
}

/**
 * Pastikan tabel `menus` tersedia dan terisi dengan menu default apabila masih kosong
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
                `location` ENUM('header', 'footer') DEFAULT 'header',
                `parent_id` INT DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `target` ENUM('_self', '_blank') DEFAULT '_self',
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Abaikan jika gagal membuat tabel
        }
    }
    ensure_menus_seeded($pdo);
}

/**
 * Pastikan tabel `menus` terisi dengan menu default apabila masih kosong
 */
function ensure_menus_seeded(PDO $pdo): void {
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare("INSERT INTO menus (title, url, location, sort_order, target, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            foreach (get_default_navigation_menus() as $m) {
                $stmt->execute([$m['title'], $m['url'], $m['location'], $m['sort_order'], $m['target'], $m['is_active']]);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika tabel menus belum siap
    }
}

/**
 * Konversi slug halaman menjadi URL rute publik yang sesuai
 */
function resolve_page_slug_to_url(string $slug): string {
    $clean = trim($slug);
    $map = [
        'beranda'           => 'index.php',
        'tentang-kami'      => 'tentang.php',
        'layanan'           => 'layanan.php',
        'harga'             => 'harga.php',
        'faq'               => 'faq.php',
        'portofolio'        => 'portofolio.php',
        'klien'             => 'klien.php',
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
 * Ambil daftar menu navigasi berdasarkan lokasi ('header' atau 'footer').
 * Membaca dari database `menus` terlebih dahulu agar perubahan di Admin CMS langsung tampil.
 */
function get_navigation_menus(PDO $pdo, string $location = 'header', bool $only_active = true): array {
    $loc = ($location === 'footer') ? 'footer' : 'header';

    try {
        ensure_menus_seeded($pdo);
        $sql = "SELECT * FROM menus WHERE location = ?";
        if ($only_active) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$loc]);
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {
        // Gunakan fallback default
    }

    // Fallback ke Live Preview JSON atau default jika DB tidak tersedia
    $live = load_live_preview_state();
    if (!empty($live['menus'][$loc]) && is_array($live['menus'][$loc])) {
        $items = $live['menus'][$loc];
        if ($only_active) {
            $items = array_values(array_filter($items, function ($m) {
                return !empty($m['is_active']);
            }));
        }
        usort($items, function ($a, $b) {
            return ((int)($a['sort_order'] ?? 0)) <=> ((int)($b['sort_order'] ?? 0));
        });
        if (!empty($items)) {
            return $items;
        }
    }

    $defaults = array_filter(get_default_navigation_menus(), function ($m) use ($loc, $only_active) {
        if ($m['location'] !== $loc) {
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
 * Sanitasi href menu untuk mencegah XSS via protokol berbahaya (javascript:, data:, vbscript:)
 */
function sanitize_nav_url(string $url): string {
    $trimmed = trim($url);
    if ($trimmed === '') {
        return '#';
    }
    $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($trimmed));
    if (
        strpos($normalized, 'javascript:') === 0 ||
        strpos($normalized, 'data:') === 0 ||
        strpos($normalized, 'vbscript:') === 0
    ) {
        return '#';
    }
    return $trimmed;
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
            'primary_color' => '#1d4ed8',
            'primary_hover' => '#1e40af',
            'hero_bg_color' => '#0b1329',
            'navbar_style' => 'light'
        ],
        'emerald-isp' => [
            'id' => 'emerald-isp',
            'name' => 'KGSNet Emerald Green',
            'primary_color' => '#059669',
            'primary_hover' => '#047857',
            'hero_bg_color' => '#062019',
            'navbar_style' => 'light'
        ],
        'cyber-indigo' => [
            'id' => 'cyber-indigo',
            'name' => 'Cyber Security Indigo',
            'primary_color' => '#4f46e5',
            'primary_hover' => '#4338ca',
            'hero_bg_color' => '#0f1026',
            'navbar_style' => 'dark'
        ],
        'industrial-amber' => [
            'id' => 'industrial-amber',
            'name' => 'Tower & Mining Amber',
            'primary_color' => '#d97706',
            'primary_hover' => '#b45309',
            'hero_bg_color' => '#18130a',
            'navbar_style' => 'dark'
        ],
        'executive-teal' => [
            'id' => 'executive-teal',
            'name' => 'Executive Telecom Teal',
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
 * beserta daftar slide aktif dari tabel `hero_sliders`
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
 * Daftar slide default untuk halaman Beranda
 */
function get_default_hero_sliders(): array {
    return [
        [
            'id' => 1,
            'title' => 'Backbone Fiber Optic & Data Center KGSNet',
            'subtitle' => 'Koneksi Dedicated Internet B2B SLA 99.5% terhubung langsung ke BGP Peering Neucentrix.',
            'badge_text' => 'ISP Resmi & Anggota APJII',
            'image_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 1,
            'is_active' => 1
        ],
        [
            'id' => 2,
            'title' => 'Pabrikasi & Instalasi Tower Guywire Mandiri',
            'subtitle' => 'Workshop internal untuk pembangunan tower komunikasi radio & wireless PTP perkebunan dan tambang.',
            'badge_text' => 'Infrastruktur Telekomunikasi',
            'image_url' => 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 2,
            'is_active' => 1
        ],
        [
            'id' => 3,
            'title' => 'Sistem CCTV AI, Access Control & Proteksi Petir < 2 Ohm',
            'subtitle' => 'Keamanan perimeter industri 24/7 dengan uji grounding bersertifikasi Earth Tester.',
            'badge_text' => 'Security & Grounding System',
            'image_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 3,
            'is_active' => 1
        ]
    ];
}

/**
 * Pastikan tabel `hero_sliders` tersedia dan memiliki data awal
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
            `badge_text` VARCHAR(100) DEFAULT 'Dokumentasi KGS',
            `image_url` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $count = (int)$pdo->query("SELECT COUNT(*) FROM hero_sliders")->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, badge_text, image_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            foreach (get_default_hero_sliders() as $s) {
                $stmt->execute([$s['title'], $s['subtitle'], $s['badge_text'], $s['image_url'], $s['sort_order'], $s['is_active']]);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika user DB tidak memiliki izin CREATE
    }
}

/**
 * Ambil daftar slide beranda dari database `hero_sliders`
 */
function get_hero_sliders(PDO $pdo, bool $only_active = true): array {
    try {
        ensure_hero_sliders_table($pdo);
        $sql = "SELECT * FROM hero_sliders";
        if ($only_active) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $rows = $pdo->query($sql)->fetchAll();
        if (!empty($rows)) {
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
 * Pastikan tabel `company_certifications` tersedia beserta kolom `subtitle`
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
            `badge_code` VARCHAR(30) NOT NULL,
            `title` VARCHAR(180) NOT NULL,
            `subtitle` VARCHAR(180) DEFAULT NULL,
            `description` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = [];
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM `company_certifications`");
        while ($c = $stmt_cols->fetch()) {
            $cols[strtolower($c['Field'])] = true;
        }
        if (!isset($cols['subtitle'])) {
            try {
                $pdo->exec("ALTER TABLE `company_certifications` ADD COLUMN `subtitle` VARCHAR(180) DEFAULT NULL AFTER `title`");
            } catch (Exception $e) {}
        }

        if ((int)$pdo->query("SELECT COUNT(*) FROM company_certifications")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO company_certifications (badge_code, title, subtitle, description, sort_order) VALUES (?, ?, ?, ?, ?)");
            $certs = [
                ['SONY', 'Certificate of Appointment SONY System Integrator', 'Lisensi Resmi Surveillance', 'Penunjukan resmi sebagai System Integrator untuk produk Video Security & IP Camera Sony.', 1],
                ['PANA', 'Authorized Dealer PABX Panasonic', 'Kemitraan Sejak 2002', 'Kemitraan resmi instalasi, pemrograman, dan purnajual sistem telekomunikasi PABX Panasonic sejak 2002.', 2],
                ['FO', 'NETVIEL InfiTe™ Fiber Optic Connectivity', 'Sertifikasi Infrastruktur Serat Optik', 'Sertifikasi lisensi instalasi dan terminasi jaringan serat optik (Fiber Optic) berstandar industri.', 3],
                ['MTC', 'MikroTik Certified Routing Engineer (MTCRE)', 'Sertifikasi Engineer Jaringan ISP', 'Tenaga ahli tersertifikasi internasional untuk manajemen routing, bandwidth, dan keamanan jaringan ISP.', 4],
            ];
            foreach ($certs as $c) {
                $stmt->execute($c);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Pastikan tabel `client_sectors` tersedia di database
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
            `badge_code` VARCHAR(30) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `description` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if ((int)$pdo->query("SELECT COUNT(*) FROM client_sectors")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO client_sectors (badge_code, name, description, sort_order) VALUES (?, ?, ?, ?)");
            $sectors = [
                ['BUMN', 'BUMN, Energi & Kepelabuhanan', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 1],
                ['AGRO', 'Perkebunan Sawit, Pulp & Tambang', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 2],
                ['GOV', 'Pemerintahan, Perbankan & Medis', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 3],
            ];
            foreach ($sectors as $sec) {
                $stmt->execute($sec);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Ambil daftar ringkasan sektor industri klien dari tabel `client_sectors`
 */
function get_client_sectors(PDO $pdo): array {
    try {
        ensure_client_sectors_table($pdo);
        $rows = $pdo->query("SELECT * FROM client_sectors ORDER BY sort_order ASC, id ASC")->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    return [
        ['id' => 1, 'badge_code' => 'BUMN', 'name' => 'BUMN, Energi & Kepelabuhanan', 'description' => 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'sort_order' => 1],
        ['id' => 2, 'badge_code' => 'AGRO', 'name' => 'Perkebunan Sawit, Pulp & Tambang', 'description' => 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'sort_order' => 2],
        ['id' => 3, 'badge_code' => 'GOV', 'name' => 'Pemerintahan, Perbankan & Medis', 'description' => 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'sort_order' => 3],
    ];
}

/**
 * Pastikan tabel `company_timelines`, `company_certifications`, `company_partners`,
 * `pricing_packages`, dan `faqs` tersedia di database beserta seluruh kolom yang dibutuhkan.
 */
function ensure_company_profile_tables(PDO $pdo): void {
    ensure_company_timelines_table($pdo);
    ensure_company_certifications_table($pdo);
    ensure_client_sectors_table($pdo);

    static $partners_ready = false;
    if ($partners_ready) {
        return;
    }
    $partners_ready = true;

    try {
        // 3. Mitra Kerja & Brand
        $pdo->exec("CREATE TABLE IF NOT EXISTS `company_partners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `brand_name` VARCHAR(150) NOT NULL,
            `role_text` VARCHAR(200) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if ((int)$pdo->query("SELECT COUNT(*) FROM company_partners")->fetchColumn() === 0) {
            $stmt = $pdo->prepare("INSERT INTO company_partners (brand_name, role_text, sort_order) VALUES (?, ?, ?)");
            $partners = [
                ['PT. Telkom Indonesia', 'Lastmile Support for ISP', 1],
                ['B.NET', 'Guywire Tower Support for ISP', 2],
                ['Inti.Net', 'Guywire Tower Support for ISP', 3],
                ['Elnus.Net', 'Guywire Tower Support for ISP', 4],
                ['PT. LintasArta', 'Guywire Tower Support for ISP', 5],
                ['PT. IES Nusantara', 'Guywire Tower Support for ISP', 6],
            ];
            foreach ($partners as $p) {
                $stmt->execute($p);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika gagal membuat tabel
    }
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
                    'ISP',
                    'Dedicated Internet B2B (KGSNet)',
                    'Perkantoran & Industri',
                    'Koneksi simetris tanpa FUP dengan izin resmi Kemenkominfo & APJII.',
                    'Koneksi simetris tanpa FUP dengan izin resmi Kemenkominfo & APJII.',
                    'Sesuai Kapasitas Bandwidth',
                    'SLA 99.5% Uptime Terjamin',
                    'SLA 99.5% Uptime Terjamin',
                    "Bandwidth 1:1 CIR Simetris (Upload & Download setara)\nRedundant Fiber Optic & Wireless Link Backup\nDedicated IP Public Static /29\nPeering Router BGP Neucentrix Data Center\nPemantauan MRTG Real-Time & NOC Siaga 24/7",
                    'Solusi Utama Terlaris',
                    1,
                    'Minta Penawaran Paket ISP',
                    1
                ],
                [
                    'CCTV',
                    'Integrated Security & Surveillance',
                    'Pelabuhan & Gudang',
                    'Pengawasan cerdas berbasis IP AI, NVR terpusat & kontrol akses fisik.',
                    'Pengawasan cerdas berbasis IP AI, NVR terpusat & kontrol akses fisik.',
                    'Sesuai Titik Kamera & BoQ',
                    'Garansi Hardware & On-Site Support',
                    'Garansi Hardware & On-Site Support',
                    "IP Camera AI Human & Vehicle Detection\nNVR Enterprise dengan RAID Storage Redundant\nAccess Control Biometrik Wajah / Fingerprint\nIntegrasi Barrier Gate & Metal Detector\nInstalasi Kabel FO Armor & UTP Berkualitas",
                    '',
                    0,
                    'Konsultasi Paket Security',
                    2
                ],
                [
                    'TOW',
                    'Tower Guywire & Remote Site',
                    'Tambang & Sawit',
                    'Infrastruktur telekomunikasi remote area dan sertifikasi penangkal petir.',
                    'Infrastruktur telekomunikasi remote area dan sertifikasi penangkal petir.',
                    'Sesuai Ketinggian & Lokasi Site',
                    'Sertifikasi Grounding < 2 Ohm',
                    'Sertifikasi Grounding < 2 Ohm',
                    "Pabrikasi Guywire Tower 20m - 40m Workshop KGS\nGrounding System Teruji Digital Earth Tester < 2 Ohm\nRepeater Radio VHF/UHF & RIG Jarak Jauh\nWireless Point-to-Point hingga 30+ KM\nTim Bersertifikasi K3 Bekerja di Ketinggian",
                    '',
                    0,
                    'Konsultasi Pembangunan Site',
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
            `category` VARCHAR(100) NOT NULL DEFAULT 'Informasi Teknis',
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
                ['Cakupan Wilayah', 'Apa cakupan wilayah layanan operasional PT. Kreatif Global Solusindo?', 'PT. KGS berkantor pusat di Palembang dan memiliki rekam jejak operasional luas di seluruh Sumatera Selatan, Kepulauan Bangka Belitung, Jambi, Riau, Sumatera Utara, hingga wilayah Kalimantan Barat, Kalimantan Timur, dan kawasan industri Cikarang (Jawa Barat). Kami siap mengirimkan tim teknisi ke lokasi remote di seluruh Indonesia.', 1],
                ['Legalitas & Lisensi', 'Apakah KGS memiliki izin resmi sebagai Penyelenggara Jasa Internet (ISP)?', 'Ya. PT. Kreatif Global Solusindo mengoperasikan layanan KGSNet dengan Izin Penyelenggaraan Jasa Akses Internet resmi dari Kementerian Komunikasi dan Informatika (Kemenkominfo) RI serta merupakan anggota resmi Asosiasi Penyelenggara Jasa Internet Indonesia (APJII) sejak tahun 2020. Kami memiliki koneksi BGP Peering langsung dengan Neucentrix Data Center.', 2],
                ['Metodologi Survei', 'Bagaimana prosedur survei lokasi sebelum penawaran teknis diterbitkan?', 'Untuk proyek jaringan fiber optic, tower, grounding, maupun surveillance, tim engineer KGS melakukan survei lokasi on-site. Survei mencakup pengukuran Line-of-Sight (LOS) untuk wireless link, pengukuran tahanan jenis tanah dengan Earth Tester untuk grounding, dan audit topologi kabel. Hasil survei menjadi dasar Bill of Quantity (BoQ) yang transparan dan akurat tanpa biaya tersembunyi.', 3],
                ['Standar Grounding', 'Berapa nilai resistansi tanah yang dijamin pada instalasi grounding?', 'PT. KGS secara ketat menerapkan standar tahanan pembumian di bawah 2 Ohm (< 2 Ω). Kami menggunakan teknik pembumian mendalam dengan elektroda tembaga masif dan bahan penurun resistansi bila diperlukan. Nilai ini diuji dan diverifikasi langsung bersama klien menggunakan alat ukur Digital Earth Resistance Tester sebelum serah terima pekerjaan.', 4],
                ['Garansi & SLA', 'Berapa Service Level Agreement (SLA) dan bagaimana dukungan teknis 24/7?', 'Kami memberikan garansi Service Level Agreement (SLA) hingga 99.5% untuk koneksi Dedicated Internet B2B. Network Operation Center (NOC) KGS beroperasi 24 jam sehari, 7 hari seminggu dengan waktu tanggap (response time) remote maksimal 15 menit dan penanganan on-site teknisi darurat maksimal 2-4 jam untuk wilayah operasional utama.', 5],
                ['Tender & Pengadaan', 'Apakah PT. KGS melayani tender pengadaan resmi pemerintah dan BUMN?', 'Ya, kami memiliki kelengkapan legalitas dan perizinan usaha yang lengkap (NIB, SIUP, NPWP, Pengusaha Kena Pajak / PKP, Akta Perusahaan, Surat Izin Kominfo) dan telah berpengalaman mengerjakan pengadaan untuk BUMN ternama seperti PT Semen Tonasa, PT Pelindo, PT Pupuk Sriwidjaja (Pusri), perbankan nasional, serta rumah sakit dan instansi pemerintah daerah.', 6],
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
    try {
        ensure_company_profile_tables($pdo);
        $rows = $pdo->query("SELECT * FROM company_certifications ORDER BY sort_order ASC, id ASC")->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    $live = load_live_preview_state();
    if (!empty($live['certifications']) && is_array($live['certifications'])) {
        return $live['certifications'];
    }
    return [];
}

function get_company_partners(PDO $pdo): array {
    try {
        ensure_company_profile_tables($pdo);
        $rows = $pdo->query("SELECT * FROM company_partners ORDER BY sort_order ASC, id ASC")->fetchAll();
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Exception $e) {}

    $live = load_live_preview_state();
    if (!empty($live['partners']) && is_array($live['partners'])) {
        return $live['partners'];
    }
    return [];
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
