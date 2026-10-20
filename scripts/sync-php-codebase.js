import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const phpAppDir = path.join(__dirname, '../php-app');
const rootDir = path.join(__dirname, '..');
const outputFile = path.join(__dirname, '../src/data/phpCodebase.ts');

function getAllFiles(dirPath, arrayOfFiles = []) {
  const files = fs.readdirSync(dirPath);
  files.forEach((file) => {
    const fullPath = path.join(dirPath, file);
    if (fs.statSync(fullPath).isDirectory()) {
      arrayOfFiles = getAllFiles(fullPath, arrayOfFiles);
    } else {
      arrayOfFiles.push(fullPath);
    }
  });
  return arrayOfFiles;
}

function getCategory(relPath) {
  if (relPath === 'database.sql') return 'database';
  if (relPath.startsWith('config/') || relPath === '.htaccess' || relPath.startsWith('uploads/')) return 'config';
  if (relPath.startsWith('admin/')) return 'admin';
  if (relPath.endsWith('.md') || relPath === 'robots.txt') return 'docs';
  return 'public';
}

function getDescription(relPath) {
  if (relPath === 'database.sql') return 'Master Schema MySQL / MariaDB Siap Import phpMyAdmin';
  if (relPath === 'INSTALL.md') return 'Panduan Instalasi 6 Langkah (cPanel, CyberPanel, XAMPP)';
  if (relPath === 'README.md') return 'Dokumentasi Arsitektur & Spesifikasi Sistem';
  if (relPath === 'config/database.php') return 'Konfigurasi Koneksi PDO MySQL (Default: demo_isp1)';
  if (relPath === 'config/app.php') return 'Konfigurasi Environment Produksi & Auto Base URL';
  if (relPath === '.htaccess') return 'Aturan Keamanan Apache, Proteksi File & URL Bersih';
  if (relPath.startsWith('admin/')) return 'Modul Panel Admin CMS PT. KGS';
  if (relPath.startsWith('includes/')) return 'Komponen Modular Header, Footer, SEO, Auth, CSRF & Upload';
  if (relPath.startsWith('assets/')) return 'Aset Lokal CSS, JavaScript, Font & Logo Vektor';
  return 'Halaman Website Publik PT. KGS';
}

const allFiles = getAllFiles(phpAppDir);
const codebaseEntries = [];

allFiles.forEach((fullPath) => {
  const relPath = path.relative(phpAppDir, fullPath).replace(/\\/g, '/');
  const rawContent = fs.readFileSync(fullPath, 'utf8');

  // Mirror exact file (including real config/database.php) to workspace root (/)
  // so Code -> Export -> Download as ZIP has the full production-ready PHP files at root.
  const targetRootPath = path.join(rootDir, relPath);
  const targetDir = path.dirname(targetRootPath);
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }
  fs.copyFileSync(fullPath, targetRootPath);

  // Mask DB_PASS in frontend TypeScript preview bundle so password is never exposed in frontend source code
  let frontendPreviewCode = rawContent;
  if (relPath === 'config/database.php') {
    frontendPreviewCode = rawContent.replace(
      /define\('DB_PASS',\s*'[^']*'\);/,
      "define('DB_PASS', '****************'); // Dikonfigurasi otomatis di paket ZIP & config/database.php"
    );
  }

  codebaseEntries.push({
    filename: path.basename(relPath),
    path: relPath,
    category: getCategory(relPath),
    description: getDescription(relPath),
    code: frontendPreviewCode
  });
});

const tsContent = `// AUTO-GENERATED FROM /php-app DIRECTORY
export interface PhpSourceFile {
  filename: string;
  path: string;
  category: 'config' | 'database' | 'public' | 'admin' | 'docs';
  description: string;
  code: string;
}

export const PHP_CODEBASE: PhpSourceFile[] = ${JSON.stringify(codebaseEntries, null, 2)};
`;

fs.writeFileSync(outputFile, tsContent, 'utf8');
console.log('Successfully synced ' + codebaseEntries.length + ' files to src/data/phpCodebase.ts (with masked frontend preview) and mirrored to workspace root (/)');
