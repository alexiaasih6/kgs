<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Akses ditolak. Silakan login sebagai Admin CMS terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metode harus POST.']);
    exit;
}

verify_csrf();

try {
    $type = trim($_POST['action'] ?? ($_POST['edit_type'] ?? ''));

    // 1. Update Judul / Ringkasan / Isi Halaman (`pages`)
    if ($type === 'page_field' || $type === 'page') {
        $slug   = trim($_POST['slug'] ?? '');
        $field  = trim($_POST['field'] ?? 'title');
        $rawVal = trim($_POST['value'] ?? '');
        $value  = ($field === 'content') ? sanitize_rich_html($rawVal) : clean_plain_text(strip_tags($rawVal), 2000);

        if (!in_array($field, ['title', 'summary', 'content', 'meta_title', 'meta_description'], true) || empty($slug)) {
            throw new Exception('Parameter halaman tidak valid.');
        }

        ensure_pages_complete($pdo);
        $stmt = $pdo->prepare("UPDATE pages SET `{$field}` = ?, updated_at = NOW() WHERE slug = ?");
        $stmt->execute([$value, $slug]);
        invalidate_live_preview_json('pages', $slug);
        mark_db_modified($pdo);

        log_audit($pdo, 'Live Edit Halaman', "Mengubah {$field} pada halaman {$slug} langsung dari website");
        echo json_encode(['ok' => true, 'message' => 'Konten halaman berhasil diperbarui di Website & Admin CMS!', 'value' => $value]);
        exit;
    }

    // 2. Update Pengaturan Identitas / Statistik / Teks (`site_settings`)
    if ($type === 'site_setting' || $type === 'setting') {
        $key   = trim($_POST['key'] ?? ($_POST['key_name'] ?? ''));
        $value = clean_plain_text(strip_tags($_POST['value'] ?? ''), 2000);
        if (empty($key) || !preg_match('/^[a-zA-Z0-9_]{2,60}$/', $key)) {
            throw new Exception('Key pengaturan tidak valid.');
        }

        save_setting($pdo, $key, $value, 'live_edit');
        mark_db_modified($pdo);

        log_audit($pdo, 'Live Edit Pengaturan', "Mengubah pengaturan {$key} langsung dari website");
        echo json_encode(['ok' => true, 'message' => 'Teks & Pengaturan berhasil disimpan ke Admin CMS!', 'value' => $value]);
        exit;
    }

    // 3. Update Record Tabel Database Langsung (`services`, `projects`, `pricing_packages`, `faqs`, `company_timelines`, `company_certifications`, `company_partners`, `client_sectors`, `posts`, `menus`)
    if ($type === 'db_record' || $type === 'record') {
        $table = trim($_POST['table'] ?? '');
        $id    = (int)($_POST['id'] ?? 0);
        $field = trim($_POST['field'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if ($table === 'navigation_menus') {
            $table = 'menus';
        } elseif ($table === 'blog_posts') {
            $table = 'posts';
        }

        $allowed_tables = [
            'services'               => ['name', 'summary', 'description', 'features', 'admin_notes', 'brands', 'target_sectors'],
            'service_categories'     => ['name', 'description'],
            'projects'               => ['title', 'client_or_location', 'description', 'category'],
            'clients'                => ['name', 'category', 'service_category', 'location'],
            'client_sectors'         => ['badge_label', 'sector_title', 'client_list', 'badge_code', 'name', 'description'],
            'pricing_packages'       => ['name', 'tagline', 'subtitle', 'price_label', 'period', 'sla_badge', 'code_label', 'target_sector', 'badge_text', 'cta_text'],
            'faqs'                   => ['question', 'answer', 'category'],
            'posts'                  => ['title', 'excerpt', 'summary', 'content', 'author'],
            'company_timelines'      => ['year_label', 'title', 'description'],
            'company_certifications' => ['badge_code', 'title', 'subtitle', 'description'],
            'company_partners'       => ['brand_name', 'role_text'],
            'hero_sliders'           => ['title', 'subtitle', 'badge_text'],
            'menus'                  => ['title', 'url']
        ];

        if ($id <= 0 || !isset($allowed_tables[$table]) || !in_array($field, $allowed_tables[$table], true)) {
            throw new Exception('Parameter tabel atau kolom tidak diizinkan.');
        }

        if ($table === 'menus' && $field === 'url') {
            $clean_val = sanitize_nav_url($value);
        } elseif ($table === 'posts' && $field === 'content') {
            $clean_val = sanitize_rich_html($value);
        } else {
            $clean_val = clean_plain_text(strip_tags($value), 4000);
        }

        if ($table === 'pricing_packages' && in_array($field, ['subtitle', 'tagline'], true)) {
            ensure_pricing_packages_table($pdo);
            $stmt = $pdo->prepare("UPDATE pricing_packages SET subtitle = ?, tagline = ? WHERE id = ?");
            $stmt->execute([$clean_val, $clean_val, $id]);
        } elseif ($table === 'pricing_packages' && in_array($field, ['sla_badge', 'period'], true)) {
            ensure_pricing_packages_table($pdo);
            $stmt = $pdo->prepare("UPDATE pricing_packages SET sla_badge = ?, period = ? WHERE id = ?");
            $stmt->execute([$clean_val, $clean_val, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET `{$field}` = ? WHERE id = ?");
            $stmt->execute([$clean_val, $id]);
        }

        invalidate_live_preview_json($table);
        mark_db_modified($pdo);

        log_audit($pdo, 'Live Edit Data CMS', "Mengubah {$table}.{$field} (ID #{$id}) langsung dari halaman website");
        echo json_encode(['ok' => true, 'message' => 'Data berhasil diperbarui di Website & Admin CMS!', 'value' => $clean_val]);
        exit;
    }

    // 4. Upload / Ganti Gambar Slider, Logo, Layanan, Proyek, atau Artikel langsung dari Website
    if ($type === 'upload_image' || $type === 'image') {
        $raw_target  = trim($_POST['target'] ?? ($_POST['target_kind'] ?? 'slider_new'));
        $target_kind = $raw_target;
        $record_id   = (int)($_POST['record_id'] ?? ($_POST['slide_id'] ?? ($_POST['id'] ?? 0)));

        if (strpos($raw_target, ':') !== false) {
            $parts = explode(':', $raw_target, 2);
            $target_kind = $parts[0];
            $record_id   = (int)$parts[1];
        }

        $uploaded_path = '';
        if (!empty($_FILES['image']['name'])) {
            $uploaded_path = handle_image_upload('image', 'uploads/', $pdo);
        } elseif (!empty($_FILES['image_file']['name'])) {
            $uploaded_path = handle_image_upload('image_file', 'uploads/', $pdo);
        } elseif (!empty($_POST['image_url'])) {
            $uploaded_path = sanitize_nav_url(trim($_POST['image_url']));
        }

        if (empty($uploaded_path)) {
            throw new Exception('Gagal mengunggah atau menyimpan gambar.');
        }

        if ($target_kind === 'logo' || $target_kind === 'company_logo') {
            save_setting($pdo, 'logo', $uploaded_path, 'branding');
            save_setting($pdo, 'company_logo', $uploaded_path, 'branding');
        } elseif ($target_kind === 'footer_logo') {
            save_setting($pdo, 'footer_logo', $uploaded_path, 'branding');
        } elseif (($target_kind === 'slider_replace' || $target_kind === 'hero_slide') && $record_id > 0) {
            ensure_hero_sliders_table($pdo);
            $stmt = $pdo->prepare("UPDATE hero_sliders SET image_url = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif ($target_kind === 'service_image' && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE services SET main_image = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif ($target_kind === 'project_image' && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif (($target_kind === 'post_image' || $target_kind === 'blog_image') && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE posts SET featured_image = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif (($target_kind === 'page_hero' || $target_kind === 'page_image') && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE pages SET featured_image = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } else {
            ensure_hero_sliders_table($pdo);
            $title = clean_plain_text(strip_tags($_POST['title'] ?? 'Dokumentasi Infrastruktur PT. KGS'), 180);
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, badge_text, image_url, sort_order, is_active) VALUES (?, 'Solusi terintegrasi jaringan, keamanan & ISP KGSNet.', 'Dokumentasi KGS', ?, 99, 1)");
            $stmt->execute([$title, $uploaded_path]);
        }

        invalidate_live_preview_json();
        mark_db_modified($pdo);
        log_audit($pdo, 'Live Upload Gambar', "Mengunggah gambar ({$target_kind}) langsung dari halaman website");
        echo json_encode(['ok' => true, 'image_url' => $uploaded_path, 'message' => 'Gambar berhasil diperbarui di Website & Admin CMS!']);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Tipe aksi tidak dikenali.', 'message' => 'Tipe aksi tidak dikenali.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
}
