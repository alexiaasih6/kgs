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
    $type = trim($_POST['edit_type'] ?? '');

    // 1. Update Judul / Ringkasan / Isi Halaman (`pages`)
    if ($type === 'page_field') {
        $slug  = trim($_POST['slug'] ?? '');
        $field = trim($_POST['field'] ?? 'title');
        $rawVal = trim($_POST['value'] ?? '');
        $value = ($field === 'content') ? sanitize_rich_html($rawVal) : strip_tags($rawVal);

        if (!in_array($field, ['title', 'summary', 'content'], true) || empty($slug)) {
            throw new Exception('Parameter halaman tidak valid.');
        }

        ensure_pages_complete($pdo);
        $stmt = $pdo->prepare("UPDATE pages SET `{$field}` = ?, updated_at = NOW() WHERE slug = ?");
        $stmt->execute([$value, $slug]);
        mark_db_modified($pdo);
        clear_settings_cache();

        log_audit($pdo, 'Live Edit Halaman', "Mengubah {$field} pada halaman {$slug} langsung dari website");
        echo json_encode(['ok' => true, 'message' => 'Konten halaman berhasil diperbarui di Website & Admin CMS!']);
        exit;
    }

    // 2. Update Pengaturan Identitas / Statistik / Teks (`site_settings`)
    if ($type === 'site_setting') {
        $key   = trim($_POST['key_name'] ?? '');
        $value = trim($_POST['value'] ?? '');
        if (empty($key)) {
            throw new Exception('Key pengaturan tidak valid.');
        }

        save_setting($pdo, $key, $value, 'live_edit');

        log_audit($pdo, 'Live Edit Pengaturan', "Mengubah pengaturan {$key} langsung dari website");
        echo json_encode(['ok' => true, 'message' => 'Teks & Pengaturan berhasil disimpan ke Admin CMS!']);
        exit;
    }

    // 3. Update Record Tabel Database Langsung (`services`, `projects`, `pricing_packages`, `faqs`, `company_timelines`, `company_certifications`, `company_partners`, `posts`)
    if ($type === 'db_record') {
        $table = trim($_POST['table'] ?? '');
        $id    = (int)($_POST['id'] ?? 0);
        $field = trim($_POST['field'] ?? '');
        $value = trim($_POST['value'] ?? '');

        $allowed_tables = [
            'services'               => ['name', 'summary', 'description'],
            'service_categories'     => ['name', 'description'],
            'projects'               => ['title', 'client_or_location', 'description', 'category'],
            'clients'                => ['name', 'category', 'service_category', 'location'],
            'pricing_packages'       => ['name', 'tagline', 'price_label', 'period'],
            'faqs'                   => ['question', 'answer', 'category'],
            'posts'                  => ['title', 'summary', 'content'],
            'company_timelines'      => ['year_label', 'title', 'description'],
            'company_certifications' => ['badge_code', 'title', 'description'],
            'company_partners'       => ['brand_name', 'role_text'],
            'hero_sliders'           => ['title', 'subtitle', 'badge_text']
        ];

        if ($id <= 0 || !isset($allowed_tables[$table]) || !in_array($field, $allowed_tables[$table], true)) {
            throw new Exception('Parameter tabel atau kolom tidak diizinkan.');
        }

        $stmt = $pdo->prepare("UPDATE `{$table}` SET `{$field}` = ? WHERE id = ?");
        $stmt->execute([$value, $id]);

        log_audit($pdo, 'Live Edit Data CMS', "Mengubah {$table}.{$field} (ID #{$id}) langsung dari halaman website");
        echo json_encode(['ok' => true, 'message' => 'Data berhasil diperbarui di Website & Admin CMS!']);
        exit;
    }

    // 4. Upload / Ganti Gambar Slider, Logo, Layanan, Proyek, atau Artikel langsung dari Website
    if ($type === 'upload_image') {
        $target_kind = trim($_POST['target_kind'] ?? 'slider_new');
        $record_id   = (int)($_POST['record_id'] ?? ($_POST['slide_id'] ?? 0));

        $uploaded_path = '';
        if (!empty($_FILES['image_file']['name'])) {
            $uploaded_path = handle_image_upload('image_file', 'uploads/', $pdo);
        } elseif (!empty($_POST['image_url'])) {
            $uploaded_path = trim($_POST['image_url']);
        }

        if (empty($uploaded_path)) {
            throw new Exception('Gagal mengunggah atau menyimpan gambar.');
        }

        if ($target_kind === 'company_logo') {
            save_setting($pdo, 'company_logo', $uploaded_path, 'branding');
        } elseif ($target_kind === 'slider_replace' && $record_id > 0) {
            ensure_hero_sliders_table($pdo);
            $stmt = $pdo->prepare("UPDATE hero_sliders SET image_url = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif ($target_kind === 'service_image' && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE services SET main_image = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif ($target_kind === 'project_image' && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE projects SET image_url = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } elseif ($target_kind === 'post_image' && $record_id > 0) {
            $stmt = $pdo->prepare("UPDATE posts SET main_image = ? WHERE id = ?");
            $stmt->execute([$uploaded_path, $record_id]);
        } else {
            ensure_hero_sliders_table($pdo);
            $title = trim($_POST['title'] ?? 'Dokumentasi Infrastruktur PT. KGS');
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, badge_text, image_url, sort_order, is_active) VALUES (?, 'Solusi terintegrasi jaringan, keamanan & ISP KGSNet.', 'Dokumentasi KGS', ?, 99, 1)");
            $stmt->execute([$title, $uploaded_path]);
        }

        log_audit($pdo, 'Live Upload Gambar', "Mengunggah gambar ({$target_kind}) langsung dari halaman website");
        echo json_encode(['ok' => true, 'image_url' => $uploaded_path, 'message' => 'Gambar berhasil diperbarui di Website & Admin CMS!']);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Tipe aksi tidak dikenali.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
