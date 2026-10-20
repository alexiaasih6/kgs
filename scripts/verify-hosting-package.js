import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');
const phpAppDir = path.join(__dirname, '../php-app');

const requiredRootFiles = [
  'index.php',
  'tentang.php',
  'layanan.php',
  'detail-layanan.php',
  'harga.php',
  'faq.php',
  'portofolio.php',
  'klien.php',
  'kontak.php',
  'blog.php',
  'detail-blog.php',
  'page.php',
  'kebijakan-privasi.php',
  '404.php',
  'sitemap.php',
  'robots.txt',
  '.htaccess',
  'database.sql',
  'INSTALL.md',
  'README.md',
  'config/app.php',
  'config/database.php',
  'config/database.example.php',
  'config/.htaccess',
  'includes/header.php',
  'includes/footer.php',
  'includes/functions.php',
  'includes/seo.php',
  'includes/auth.php',
  'includes/csrf.php',
  'includes/upload.php',
  'uploads/.htaccess',
  'uploads/.gitkeep',
  'assets/css/style.css',
  'assets/js/main.js',
  'assets/images/logo-kgs.svg',
  'assets/images/logo-kgs-white.svg',
  'assets/images/favicon.svg',
  'assets/fonts/fonts.css',
  'admin/index.php',
  'admin/login.php',
  'admin/register-admin.php',
  'admin/logout.php',
  'admin/pengaturan.php',
  'admin/layanan.php',
  'admin/tambah-layanan.php',
  'admin/edit-layanan.php',
  'admin/audit-pdf.php',
  'admin/kategori.php',
  'admin/harga.php',
  'admin/faq.php',
  'admin/portofolio.php',
  'admin/klien.php',
  'admin/blog.php',
  'admin/tambah-artikel.php',
  'admin/edit-artikel.php',
  'admin/halaman.php',
  'admin/media.php',
  'admin/inquiries.php',
  'admin/detail-inquiry.php',
  'admin/seo.php',
  'admin/menu.php',
  'admin/users.php',
  'admin/audit-log.php'
];

let errors = [];

// 1. Check required files in both php-app/ and workspace root /
for (const rel of requiredRootFiles) {
  if (!fs.existsSync(path.join(phpAppDir, rel))) {
    errors.push(`Missing in php-app/: ${rel}`);
  }
  if (!fs.existsSync(path.join(rootDir, rel))) {
    errors.push(`Missing in workspace root (/): ${rel}`);
  }
}

// 2. Check config/database.php credentials & ensure password is not leaked in docs or frontend src/
const dbConfigContent = fs.readFileSync(path.join(rootDir, 'config/database.php'), 'utf8');
if (!dbConfigContent.includes("define('DB_HOST', 'localhost');")) {
  errors.push('config/database.php missing DB_HOST = localhost');
}
if (!dbConfigContent.includes("define('DB_NAME', 'demo_isp1');")) {
  errors.push('config/database.php missing DB_NAME = demo_isp1');
}
if (!dbConfigContent.includes("define('DB_USER', 'demo_isp1');")) {
  errors.push('config/database.php missing DB_USER = demo_isp1');
}
if (!dbConfigContent.includes("define('DB_CHARSET', 'utf8mb4');")) {
  errors.push('config/database.php missing DB_CHARSET = utf8mb4');
}

const passMatch = dbConfigContent.match(/define\('DB_PASS',\s*'([^']+)'\);/);
if (!passMatch || !passMatch[1]) {
  errors.push('config/database.php missing configured DB_PASS');
} else {
  const configuredPass = passMatch[1];
  const installMd = fs.readFileSync(path.join(rootDir, 'INSTALL.md'), 'utf8');
  const readmeMd = fs.readFileSync(path.join(rootDir, 'README.md'), 'utf8');
  if (installMd.includes(configuredPass)) {
    errors.push('Password leaked in INSTALL.md');
  }
  if (readmeMd.includes(configuredPass)) {
    errors.push('Password leaked in README.md');
  }

  function checkDirForPassword(dir) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) {
        checkDirForPassword(full);
      } else if (entry.name.endsWith('.ts') || entry.name.endsWith('.tsx')) {
        const text = fs.readFileSync(full, 'utf8');
        if (text.includes(configuredPass)) {
          errors.push(`Password leaked in frontend source file: ${path.relative(rootDir, full)}`);
        }
      }
    }
  }
  checkDirForPassword(path.join(rootDir, 'src'));
}

// 3. Verify database.sql syntax & tables
const sql = fs.readFileSync(path.join(rootDir, 'database.sql'), 'utf8');
if (sql.includes('\\`')) {
  errors.push('database.sql contains escaped backticks (\\`)');
}
if (/CREATE\s+DATABASE/i.test(sql) || /^USE\s+/im.test(sql)) {
  errors.push('database.sql contains CREATE DATABASE or USE statement that may fail in shared hosting phpMyAdmin');
}
if (/INSERT\s+(?:IGNORE\s+)?INTO\s+`?users`?/i.test(sql)) {
  errors.push('database.sql should not contain default weak admin user in users table');
}

const requiredTables = [
  'users',
  'audit_logs',
  'site_settings',
  'pages',
  'menus',
  'service_categories',
  'services',
  'pricing_packages',
  'faqs',
  'clients',
  'projects',
  'blog_categories',
  'posts',
  'inquiries',
  'lead_notes',
  'media',
  'partners'
];
for (const tbl of requiredTables) {
  const regex = new RegExp(`CREATE TABLE IF NOT EXISTS \`${tbl}\``);
  if (!regex.test(sql)) {
    errors.push(`Missing table in database.sql: ${tbl}`);
  }
}

// 4. Verify all require_once / include paths in PHP files
function getPhpFiles(dir, list = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      getPhpFiles(full, list);
    } else if (entry.name.endsWith('.php')) {
      list.push(full);
    }
  }
  return list;
}

const phpFiles = getPhpFiles(phpAppDir);
for (const file of phpFiles) {
  const code = fs.readFileSync(file, 'utf8');
  const dir = path.dirname(file);
  const reqRegex = /(?:require|require_once|include|include_once)\s+__DIR__\s*\.\s*['"]([^'"]+)['"]/g;
  let match;
  while ((match = reqRegex.exec(code)) !== null) {
    const targetPath = path.resolve(dir, '.' + match[1]);
    if (!fs.existsSync(targetPath)) {
      errors.push(`Broken include in ${path.relative(phpAppDir, file)}: ${match[1]} -> ${targetPath}`);
    }
  }
}

// 5. Verify ZIP archives exist in public/
const zipPath = path.join(rootDir, 'public/KGS-WEBSITE-HOSTING.zip');
if (!fs.existsSync(zipPath) || fs.statSync(zipPath).size < 50000) {
  errors.push('public/KGS-WEBSITE-HOSTING.zip is missing or incomplete');
}

if (errors.length > 0) {
  console.error('VERIFICATION FAILED:', errors);
  process.exit(1);
} else {
  console.log(`VERIFICATION PASSED: ${phpFiles.length} PHP files, ${requiredTables.length} SQL tables, security rules, and ZIP package verified.`);
}
