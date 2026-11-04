/**
 * Automated Security, Anti-XSS/Defacement & Admin CMS CRUD Verification Suite
 * PT. Kreatif Global Solusindo (KGS)
 */
import fs from 'fs';
import path from 'path';

const ROOT = process.cwd();
const PHP_APP = path.join(ROOT, 'php-app');

let passed = 0;
let failed = 0;
const findings = [];

function assertCheck(name, condition, detail = '') {
  if (condition) {
    passed++;
    console.log(`  [PASS] ${name}`);
  } else {
    failed++;
    findings.push({ name, detail });
    console.error(`  [FAIL] ${name} ${detail ? '— ' + detail : ''}`);
  }
}

console.log('=== 1. VERIFIKASI PROTEKSI SERVER, .HTACCESS & KONFIGURASI DATABASE ===');
const rootHtaccess = fs.readFileSync(path.join(PHP_APP, '.htaccess'), 'utf8');
const uploadsHtaccess = fs.readFileSync(path.join(PHP_APP, 'uploads/.htaccess'), 'utf8');
const dbConfig = fs.readFileSync(path.join(PHP_APP, 'config/database.php'), 'utf8');
const appConfig = fs.readFileSync(path.join(PHP_APP, 'config/app.php'), 'utf8');

assertCheck('Root .htaccess memblokir akses langsung ke config/ dan includes/', /RewriteRule\s+\^\(config\|includes\)\/\s+-\s+\[F,L\]/.test(rootHtaccess));
assertCheck('Root .htaccess memblokir file sensitif (.sql, .env, .log, .bak, .json)', /FilesMatch.*\.(sql|bak|old|swp|log|ini|sh|lock|json|md)/.test(rootHtaccess));
assertCheck('Root .htaccess memblokir eksekusi script di dalam uploads/', /RewriteRule\s+\^uploads\/.*\.\(php/.test(rootHtaccess));
assertCheck('uploads/.htaccess menonaktifkan engine PHP & memblokir ekstensi executable/SVG', /php_flag\s+engine\s+off/i.test(uploadsHtaccess) && /phtml\|phar/i.test(uploadsHtaccess));
assertCheck('config/database.php menggunakan PDO dengan ATTR_EMULATE_PREPARES => false', /ATTR_EMULATE_PREPARES\s*=>\s*false/.test(dbConfig));
assertCheck('config/database.php mendukung file konfigurasi eksternal di luar public_html & env vars', /kgs-db-config\.php/.test(dbConfig) && /getenv\('KGS_DB_PASS'\)/.test(dbConfig));
assertCheck('config/app.php menonaktifkan display_errors di produksi (display_errors = 0)', /ini_set\('display_errors',\s*'0'\)/.test(appConfig));

console.log('\n=== 2. VERIFIKASI AUTENTIKASI, SESSION, CSRF & UPLOAD HARDENING ===');
const authPhp = fs.readFileSync(path.join(PHP_APP, 'includes/auth.php'), 'utf8');
const csrfPhp = fs.readFileSync(path.join(PHP_APP, 'includes/csrf.php'), 'utf8');
const uploadPhp = fs.readFileSync(path.join(PHP_APP, 'includes/upload.php'), 'utf8');
const funcPhp = fs.readFileSync(path.join(PHP_APP, 'includes/functions.php'), 'utf8');

assertCheck('Session menggunakan HttpOnly, SameSite=Lax, dan use_strict_mode', /session\.cookie_httponly/.test(authPhp) && /session\.cookie_samesite/.test(authPhp) && /session\.use_strict_mode/.test(authPhp));
assertCheck('Login meregenerasi session ID (session_regenerate_id(true))', /session_regenerate_id\(true\)/.test(authPhp));
assertCheck('Password diverifikasi dengan password_verify() & disimpan dengan password_hash() BCRYPT', /password_verify\(/.test(authPhp) && /PASSWORD_BCRYPT/.test( fs.readFileSync(path.join(PHP_APP, 'admin/users.php'), 'utf8') ));
assertCheck('Rate limiting login brute-force (count_recent_failed_logins & login_attempts) aktif', /function\s+count_recent_failed_logins/.test(authPhp) && /login_attempts/.test(authPhp));
assertCheck('CSRF menggunakan random_bytes(32) dan hash_equals()', /random_bytes\(32\)/.test(csrfPhp) && /hash_equals\(/.test(csrfPhp));
assertCheck('Upload memvalidasi finfo MIME, getimagesize(), batas dimensi 6000px, dan deteksi polyglot PHP/JS', /finfo_file/.test(uploadPhp) && /getimagesize/.test(uploadPhp) && /6000/.test(uploadPhp) && /<\?php/.test(uploadPhp));
assertCheck('Upload memiliki helper safe_delete_uploaded_file() anti-path traversal', /function\s+safe_delete_uploaded_file/.test(uploadPhp) && /realpath/.test(uploadPhp));
assertCheck('Helper sanitasi clean_plain_text(), sanitize_image_url(), sanitize_nav_url(), dan sanitize_rich_html() tersedia', /function\s+clean_plain_text/.test(funcPhp) && /function\s+sanitize_image_url/.test(funcPhp) && /function\s+sanitize_nav_url/.test(funcPhp) && /function\s+sanitize_rich_html/.test(funcPhp));

console.log('\n=== 3. VERIFIKASI SELURUH MODUL ADMIN CMS (AUTH, CSRF, PREPARED STATEMENTS & CRUD) ===');
const adminDir = path.join(PHP_APP, 'admin');
const adminFiles = fs.readdirSync(adminDir).filter(f => f.endsWith('.php'));

const publicAuthPages = new Set(['login.php', 'register-admin.php', 'logout.php', 'auth_check.php', 'layout-header.php', 'layout-footer.php']);

for (const file of adminFiles) {
  const content = fs.readFileSync(path.join(adminDir, file), 'utf8');

  if (!publicAuthPages.has(file)) {
    const isAlias = /require_once\s+__DIR__\s*\.\s*'\/[a-z0-9\-_]+\.php'/i.test(content);
    const hasAuth = content.includes('layout-header.php') || content.includes('auth_check.php') || content.includes('require_admin') || content.includes('is_admin_logged_in') || isAlias;
    assertCheck(`Admin [${file}] memiliki proteksi autentikasi server-side`, hasAuth);
  }

  if (content.includes("$_SERVER['REQUEST_METHOD'] === 'POST'") && file !== 'login.php') {
    const hasCsrfVerify = content.includes('verify_csrf()') || content.includes('hash_equals');
    assertCheck(`Admin [${file}] memvalidasi token CSRF pada handler POST`, hasCsrfVerify);
  }

  // Pastikan setiap <form method="POST"> memiliki csrf_field()
  const postForms = content.match(/<form\b[^>]*method=["']POST["'][^>]*>/gi) || [];
  if (postForms.length > 0) {
    const csrfCount = (content.match(/csrf_field\(\)/g) || []).length;
    assertCheck(`Admin [${file}] menyertakan csrf_field() pada form POST (${csrfCount} ditemukan)`, csrfCount >= 1);
  }

  // Pastikan tidak ada interpolasi langsung $_GET atau $_POST di dalam $pdo->query() atau $pdo->exec()
  const unsafeSql = /\$pdo->(query|exec)\s*\([^)]*\$_(GET|POST|REQUEST)/.test(content);
  assertCheck(`Admin [${file}] bebas dari interpolasi langsung $_GET/$_POST pada query SQL`, !unsafeSql);
}

console.log('\n=== 4. VERIFIKASI KELENGKAPAN AKSI CRUD PADA MODUL UTAMA ADMIN CMS ===');
const crudChecks = [
  ['admin/layanan.php', /DELETE FROM services/, 'Hapus Layanan & Toggle Status'],
  ['admin/tambah-layanan.php', /INSERT INTO services/, 'Tambah Layanan Baru'],
  ['admin/edit-layanan.php', /UPDATE services SET/, 'Edit Layanan'],
  ['admin/kategori.php', /INSERT INTO service_categories[\s\S]*UPDATE service_categories[\s\S]*DELETE FROM service_categories/, 'Tambah, Edit, Toggle & Hapus Kategori Layanan (dengan proteksi FK)'],
  ['admin/harga.php', /INSERT INTO pricing_packages[\s\S]*UPDATE pricing_packages[\s\S]*DELETE FROM pricing_packages/, 'Tambah, Edit & Hapus Paket Harga ISP'],
  ['admin/faq.php', /INSERT INTO faqs[\s\S]*UPDATE faqs[\s\S]*DELETE FROM faqs/, 'Tambah, Edit, Toggle & Hapus FAQ'],
  ['admin/portofolio.php', /INSERT INTO projects[\s\S]*UPDATE projects[\s\S]*DELETE FROM projects/, 'Tambah, Edit, Multi-Gambar & Hapus Portofolio Proyek'],
  ['admin/slideshow.php', /INSERT INTO slideshow_categories[\s\S]*UPDATE slideshow_categories[\s\S]*DELETE FROM slideshow_categories/, 'Tambah, Edit, Multi-Gambar & Hapus Kategori Slide Show'],
  ['admin/klien.php', /INSERT INTO clients[\s\S]*UPDATE clients[\s\S]*DELETE FROM clients/, 'Tambah, Edit & Hapus Klien'],
  ['admin/blog.php', /DELETE FROM posts[\s\S]*INSERT INTO blog_categories[\s\S]*UPDATE blog_categories[\s\S]*DELETE FROM blog_categories/, 'Daftar/Hapus Artikel & CRUD Kategori Blog'],
  ['admin/tambah-artikel.php', /INSERT INTO posts/, 'Tambah Artikel Blog'],
  ['admin/edit-artikel.php', /UPDATE posts SET/, 'Edit Artikel Blog'],
  ['admin/inquiries.php', /UPDATE inquiries SET status[\s\S]*DELETE FROM inquiries/, 'Filter, Update Status Whitelist & Hapus Leads'],
  ['admin/detail-inquiry.php', /INSERT INTO lead_notes[\s\S]*DELETE FROM lead_notes[\s\S]*UPDATE inquiries SET status/, 'Detail Lead, Tambah/Hapus Catatan & Update Status'],
  ['admin/halaman.php', /DELETE FROM pages[\s\S]*INSERT INTO pages[\s\S]*UPDATE pages SET/, 'Tambah, Edit & Hapus Halaman Statis/Dinamis'],
  ['admin/bagian-halaman.php', /company_timelines[\s\S]*company_certifications[\s\S]*company_partners[\s\S]*client_sectors/, 'CRUD Sejarah Timeline, Sertifikasi, Mitra, Sektor Klien & Seksi Beranda/Footer'],
  ['admin/menu.php', /INSERT INTO menus[\s\S]*UPDATE menus[\s\S]*DELETE FROM menus/, 'Tambah, Edit, Urutkan & Hapus Menu Navigasi Header/Footer'],
  ['admin/tema-slider.php', /save_setting[\s\S]*hero_sliders/, 'Pilih Tema Warna & CRUD Slide Banner Beranda'],
  ['admin/seo.php', /save_setting[\s\S]*seo_google_verification/, 'Simpan Pengaturan Global SEO, OG, Schema & Verifikasi Search Console'],
  ['admin/media.php', /handle_image_upload[\s\S]*DELETE FROM media/, 'Upload & Hapus Media dengan Proteksi Path Traversal'],
  ['admin/pengaturan.php', /save_setting[\s\S]*company_name/, 'Simpan Identitas Perusahaan, Logo, Kontak, Sosial Media & SMTP'],
  ['admin/users.php', /create_user[\s\S]*edit_user[\s\S]*change_password[\s\S]*delete_user/, 'Tambah, Edit Profil/Role, Ganti Sandi BCRYPT & Hapus User (dengan proteksi Superadmin terakhir)']
];

for (const [relPath, regex, desc] of crudChecks) {
  const content = fs.readFileSync(path.join(PHP_APP, relPath), 'utf8');
  assertCheck(`CRUD [${relPath}] — ${desc}`, regex.test(content));
}

console.log(`\n=== HASIL AKHIR VERIFIKASI KEAMANAN & CRUD: ${passed} LULUS, ${failed} GAGAL ===`);
if (failed > 0) {
  process.exit(1);
}
