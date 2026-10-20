import fs from 'fs';
import path from 'path';
import JSZip from 'jszip';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const phpAppDir = path.join(__dirname, '../php-app');
const publicZipPath1 = path.join(__dirname, '../public/kgs-portal-php-mysql.zip');
const publicZipPath2 = path.join(__dirname, '../public/KGS-WEBSITE-HOSTING.zip');

function addDirectoryToZip(zip, dirPath, rootDir) {
  const entries = fs.readdirSync(dirPath, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dirPath, entry.name);
    const relPath = path.relative(rootDir, fullPath).replace(/\\/g, '/');
    if (entry.isDirectory()) {
      addDirectoryToZip(zip, fullPath, rootDir);
    } else {
      const content = fs.readFileSync(fullPath);
      zip.file(relPath, content);
      console.log(`+ Added to ZIP: ${relPath}`);
    }
  }
}

async function buildZip() {
  console.log(`Starting ZIP archive generation from: ${phpAppDir}`);
  const zip = new JSZip();
  addDirectoryToZip(zip, phpAppDir, phpAppDir);

  const publicDir = path.dirname(publicZipPath1);
  if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
  }

  const buffer = await zip.generateAsync({
    type: 'nodebuffer',
    compression: 'DEFLATE',
    compressionOptions: { level: 9 }
  });

  fs.writeFileSync(publicZipPath1, buffer);
  fs.writeFileSync(publicZipPath2, buffer);
  console.log(`SUCCESS: ZIP created at ${publicZipPath2} (${(buffer.length / 1024).toFixed(2)} KB)`);
}

buildZip().catch((err) => {
  console.error('Error generating ZIP:', err);
  process.exit(1);
});
