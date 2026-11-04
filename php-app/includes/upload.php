<?php
/**
 * Helper Upload Gambar Aman (Hardened)
 * Memvalidasi MIME asli, ekstensi, dimensi gambar raster (getimagesize),
 * memblokir SVG & polyglot script, serta menamai ulang secara acak.
 */

function handle_image_upload($file_input_name, string $target_subdir = 'uploads/', ?PDO $pdo = null) {
    // Jika pemanggil mengirimkan array $_FILES['field'] secara langsung (bukan string nama field),
    // kembalikan array hasil terstruktur ['success' => bool, 'filepath' => string, 'error' => string]
    if (is_array($file_input_name)) {
        $normalized_subdir = trim($target_subdir, '/');
        if ($normalized_subdir === '' || $normalized_subdir === 'uploads') {
            $normalized_subdir = 'uploads/';
        } elseif (strpos($normalized_subdir, 'uploads/') !== 0) {
            $normalized_subdir = 'uploads/' . $normalized_subdir;
        }
        return secure_upload_image($file_input_name, $normalized_subdir, $pdo);
    }

    if (!is_string($file_input_name)) {
        return null;
    }

    // Jika tidak ada file yang diunggah lewat Choose File, cek apakah admin memilih dari "Media & Upload Gambar"
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] === UPLOAD_ERR_NO_FILE) {
        $from_media_key = $file_input_name . '_from_media';
        $selected_media = trim((string)($_POST[$from_media_key] ?? ''));
        if ($selected_media !== '') {
            $safe_media = function_exists('sanitize_image_url')
                ? sanitize_image_url($selected_media, '')
                : ((strpos($selected_media, '..') === false && preg_match('#^(https?://|uploads/|assets/)#i', ltrim($selected_media, '/'))) ? ltrim($selected_media, '/') : '');
            if ($safe_media !== '') {
                return $safe_media;
            }
        }
        return null;
    }

    $file = $_FILES[$file_input_name];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Gagal mengunggah file (Kode Error: ' . (int)$file['error'] . ').');
    }

    // Batas ukuran 3 MB
    $max_size = 3 * 1024 * 1024;
    if ($file['size'] <= 0 || $file['size'] > $max_size) {
        throw new Exception('Ukuran file gambar tidak valid atau melebihi batas maksimal 3 MB.');
    }

    // Validasi ekstensi nama file asli (Hanya format gambar raster aman; SVG diblokir untuk mencegah Stored XSS)
    $orig_name = basename((string)$file['name']);
    $orig_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($orig_ext, $allowed_exts, true)) {
        throw new Exception('Ekstensi file tidak diizinkan. Hanya menerima JPG, JPEG, PNG, WEBP, atau GIF.');
    }

    // Validasi ekstensi ganda berbahaya (misal: shell.php.jpg)
    if (preg_match('/\.(php\d*|phtml|phar|pl|py|cgi|sh|exe|bat|cmd|svg|html?|js)\b/i', $orig_name)) {
        throw new Exception('Nama file mengandung ekstensi berisiko yang ditolak oleh sistem keamanan.');
    }

    // Validasi MIME Type nyata menggunakan finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    if (!isset($allowed_mimes[$mime])) {
        throw new Exception('Format file tidak diizinkan. File harus berupa gambar asli (JPG, PNG, WEBP, GIF).');
    }

    // Validasi struktur header & dimensi gambar raster menggunakan getimagesize (dan cegah Image Bomb > 6000px)
    $img_info = @getimagesize($file['tmp_name']);
    if ($img_info === false || empty($img_info[0]) || empty($img_info[1])) {
        throw new Exception('File yang diunggah bukan gambar valid atau file gambar rusak.');
    }
    if ((int)$img_info[0] > 6000 || (int)$img_info[1] > 6000) {
        throw new Exception('Dimensi resolusi gambar melebihi batas aman (maksimal 6000 x 6000 piksel).');
    }

    // Deteksi payload kode berbahaya (Polyglot PHP/HTML/JS di dalam metadata gambar)
    $raw_sample = @file_get_contents($file['tmp_name'], false, null, 0, 65536);
    if ($raw_sample !== false && preg_match('/(<\?php|<\?=|<script\b|javascript\s*:)/i', $raw_sample)) {
        throw new Exception('File ditolak karena terdeteksi mengandung sisipan skrip berbahaya.');
    }

    $ext = $allowed_mimes[$mime];
    $new_filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

    $clean_subdir = trim($target_subdir, '/');
    // Cegah directory traversal pada target_subdir
    $clean_subdir = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $clean_subdir) ?? 'uploads';
    if ($clean_subdir === '' || strpos($clean_subdir, '..') !== false) {
        $clean_subdir = 'uploads';
    } elseif (strpos($clean_subdir, 'uploads') !== 0) {
        $clean_subdir = 'uploads/' . $clean_subdir;
    }

    $base_dir = dirname(__DIR__) . '/' . $clean_subdir . '/';
    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0755, true);
    }

    $destination = $base_dir . $new_filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Gagal memindahkan file ke direktori uploads.');
    }
    @chmod($destination, 0644);

    $relative_path = $clean_subdir . '/' . $new_filename;

    // Catat ke tabel media jika koneksi PDO diberikan
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, filepath, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $new_filename,
                mb_substr($orig_name, 0, 250),
                $relative_path,
                $mime,
                (int)$file['size'],
                $_SESSION['admin_id'] ?? null
            ]);
        } catch (Throwable $e) {
            // Abaikan jika tabel media belum siap
        }
    }

    return $relative_path;
}

/**
 * Helper kompatibilitas upload gambar dengan return array lengkap:
 * ['success' => bool, 'ok' => bool, 'filename' => string, 'filepath' => string, 'path' => string, 'error' => string]
 */
function secure_upload_image(array $file_arr, string $subdir = 'uploads/', ?PDO $pdo = null): array {
    try {
        $target_dir = trim($subdir, '/');
        if ($target_dir === '' || $target_dir === 'img') {
            $target_dir = 'uploads';
        } elseif (strpos($target_dir, 'uploads') !== 0) {
            $target_dir = 'uploads/' . $target_dir;
        }
        $_FILES['__temp_secure_upload'] = $file_arr;
        $rel = handle_image_upload('__temp_secure_upload', $target_dir, $pdo);
        unset($_FILES['__temp_secure_upload']);
        if (is_string($rel) && $rel !== '') {
            return [
                'success'  => true,
                'ok'       => true,
                'filename' => $rel,
                'filepath' => $rel,
                'path'     => $rel,
                'error'    => ''
            ];
        }
        return [
            'success'  => false,
            'ok'       => false,
            'filename' => '',
            'filepath' => '',
            'path'     => '',
            'error'    => 'Tidak ada file yang diunggah.'
        ];
    } catch (Throwable $e) {
        unset($_FILES['__temp_secure_upload']);
        return [
            'success'  => false,
            'ok'       => false,
            'filename' => '',
            'filepath' => '',
            'path'     => '',
            'error'    => $e->getMessage()
        ];
    }
}

/**
 * Hapus file fisik di dalam direktori uploads/ secara aman dengan proteksi anti-Path Traversal.
 * Tidak akan pernah menghapus file di luar folder uploads/ (seperti assets/, config/, atau includes/).
 */
function safe_delete_uploaded_file(?string $filepath): bool {
    $rel = ltrim(trim((string)$filepath), '/');
    if ($rel === '' || strpos($rel, '..') !== false || strpos($rel, 'uploads/') !== 0) {
        return false;
    }
    $uploads_root = realpath(dirname(__DIR__) . '/uploads');
    $candidate = realpath(dirname(__DIR__) . '/' . $rel);
    if ($uploads_root && $candidate && strpos($candidate, $uploads_root . DIRECTORY_SEPARATOR) === 0 && is_file($candidate)) {
        return @unlink($candidate);
    }
    return false;
}


