<?php
/**
 * Helper Upload Gambar Aman (Hardened)
 * Memvalidasi MIME asli, ekstensi, dimensi gambar raster (getimagesize),
 * memblokir SVG & polyglot script, serta menamai ulang secara acak.
 */

function handle_image_upload(string $file_input_name, string $target_subdir = 'uploads/', ?PDO $pdo = null): ?string {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] === UPLOAD_ERR_NO_FILE) {
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

    // Validasi struktur header & dimensi gambar raster menggunakan getimagesize
    $img_info = @getimagesize($file['tmp_name']);
    if ($img_info === false || empty($img_info[0]) || empty($img_info[1])) {
        throw new Exception('File yang diunggah bukan gambar valid atau file gambar rusak.');
    }

    // Deteksi payload kode berbahaya (Polyglot PHP/HTML/JS di dalam metadata gambar)
    $raw_sample = @file_get_contents($file['tmp_name'], false, null, 0, 65536);
    if ($raw_sample !== false && preg_match('/(<\?php|<\?=|<script\b|javascript\s*:)/i', $raw_sample)) {
        throw new Exception('File ditolak karena terdeteksi mengandung sisipan skrip berbahaya.');
    }

    $ext = $allowed_mimes[$mime];
    $new_filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

    $base_dir = dirname(__DIR__) . '/' . trim($target_subdir, '/') . '/';
    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0755, true);
    }

    $destination = $base_dir . $new_filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Gagal memindahkan file ke direktori uploads.');
    }
    @chmod($destination, 0644);

    $relative_path = trim($target_subdir, '/') . '/' . $new_filename;

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
        } catch (Exception $e) {
            // Abaikan jika tabel media belum siap
        }
    }

    return $relative_path;
}
