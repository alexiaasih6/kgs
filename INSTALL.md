# PANDUAN INSTALASI WEBSITE PT. KREATIF GLOBAL SOLUSINDO (KGS)
**Target Hosting:** cPanel · CyberPanel · Shared Hosting (`public_html`) · XAMPP · Laragon  
**Teknologi:** PHP Native (Min. PHP 7.4+ s/d PHP 8.3) + MySQL 5.7+ / MariaDB 10.3+  
**Tanpa Build di Server:** Tidak memerlukan `npm install`, `npm run build`, Composer, atau akses SSH/Terminal.

---

## Langkah 1 — Siapkan Database
1. Login ke panel kontrol hosting Anda (**cPanel** atau **CyberPanel**).
2. Buka menu **MySQL® Databases** (di cPanel) atau **Databases -> Create Database** (di CyberPanel).
3. Buat database MySQL baru (contoh: `demo_isp1`).
4. Buat user database baru beserta password yang kuat.
5. Hubungkan user ke database tersebut dan centang **ALL PRIVILEGES** (Semua Hak Akses).
6. Catat **Nama Database**, **Username Database**, dan **Password Database**.

---

## Langkah 2 — Import Database (`database.sql`)
1. Buka menu **phpMyAdmin** di panel hosting Anda.
2. Klik nama database yang telah Anda buat (misal: `demo_isp1`) pada daftar di sebelah kiri.
3. Klik tab **Import** di bagian atas halaman phpMyAdmin.
4. Klik tombol **Choose File / Pilih File**, lalu pilih berkas **`database.sql`** yang berada di direktori utama paket ZIP.
5. Klik tombol **Import / Go / Kirim** di bagian bawah.
6. Pastikan muncul pesan sukses (*Import has been successfully finished*) tanpa error.

---

## Langkah 3 — Upload Website ke `public_html`
1. Ekstrak file ZIP hasil unduhan di komputer Anda.
2. Buka folder hasil ekstraksi.
3. Masuk ke **File Manager** di cPanel / CyberPanel, lalu buka direktori **`public_html/`**.
4. Upload seluruh isi website ke dalam `public_html/` (Anda juga dapat mengunggah ZIP langsung ke `public_html/` lalu klik kanan -> **Extract**).
5. **Penting:** Pastikan file **`index.php`** berada langsung di dalam `public_html/index.php` (bukan di dalam subfolder bertingkat yang tidak diperlukan).
6. Pastikan file **`.htaccess`** ikut terunggah (aktifkan opsi *Show Hidden Files / Dotfiles* di Settings File Manager cPanel).

---

## Langkah 4 — Atur Koneksi Database
1. Di File Manager hosting, buka file **`config/database.php`**.
2. Sesuaikan konstanta koneksi database berikut dengan akun database hosting Anda:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'demo_isp1');                  // Nama database MySQL default
   define('DB_USER', 'demo_isp1');                  // Username database MySQL default
   define('DB_PASS', '<PASSWORD_DATABASE_ANDA>');   // Telah dikonfigurasi di config/database.php
   define('DB_CHARSET', 'utf8mb4');
   ```
3. Simpan perubahan file (`Save Changes`).
4. File konfigurasi ini telah dilindungi oleh `.htaccess` dan pengecekan script langsung sehingga tidak dapat diakses dari browser publik.

---

## Langkah 5 — Atur Akun Admin Pertama
1. Demi keamanan produksi, sistem tidak menyimpan password admin plaintext di dalam `database.sql`.
2. Buka browser dan akses halaman login admin:
   ```text
   https://domain-anda.com/admin/login.php
   ```
3. Saat tabel `users` masih kosong (instalasi pertama), sistem akan otomatis mengarahkan Anda ke halaman **Inisialisasi Superadmin Pertama** (`admin/register-admin.php`).
4. Buat akun Superadmin Anda dengan **Password Kuat** (minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka). Password akan langsung dienkripsi menggunakan `password_hash(..., PASSWORD_BCRYPT)`.
5. Setelah akun pertama dibuat, halaman registrasi otomatis terkunci secara permanen.

---

## Langkah 6 — Tes Website & Fitur Utama
1. **Buka Domain Utama:** Pastikan halaman Beranda tampil utuh dan responsif.
2. **Tes 7 Menu Publik:**
   - **Beranda** (`index.php`)
   - **Tentang Kami** (`tentang.php`)
   - **Layanan** (`layanan.php` & `detail-layanan.php`)
   - **Harga** (`harga.php`)
   - **FAQ** (`faq.php`)
   - **Portofolio** (`portofolio.php` & `klien.php`)
   - **Kontak** (`kontak.php` — lengkap dengan peta Google Maps & formulir penawaran direct WhatsApp CS)
3. **Login ke Admin CMS (`/admin/login.php`):**
   - Uji menu **Identitas, Logo & Maps** (`admin/pengaturan.php`) untuk mengganti logo, favicon, nomor WhatsApp CS, dan titik Google Maps.
   - Uji tambah, edit, dan hapus pada **Layanan**, **Katalog 22 Standar**, **Paket Harga B2B**, **FAQ**, **Dokumentasi Proyek**, **Daftar Klien**, dan **Artikel/Blog**.
   - Uji **Media & Upload Gambar** (`admin/media.php`).
4. **Tes Formulir Kontak & Direct WhatsApp:**
   - Isi formulir penawaran di halaman Kontak, pastikan data tersimpan di menu **Permintaan Penawaran** Admin CMS dan mengarahkan ke WhatsApp CS resmi.
5. **Aktifkan SSL (HTTPS):**
   - Aktifkan AutoSSL / Let's Encrypt di cPanel atau CyberPanel setelah domain terhubung.

---

## PANDUAN TROUBLESHOOTING (PEMECAHAN MASALAH)

### 1. Error Koneksi Database ("Koneksi Database Belum Terhubung")
- **Penyebab:** Nama database, username, atau password pada `config/database.php` belum sesuai dengan yang dibuat di cPanel/CyberPanel, atau user database belum diberi hak akses (`ALL PRIVILEGES`).
- **Solusi:** Periksa kembali ejaan `DB_NAME`, `DB_USER`, dan `DB_PASS` di `config/database.php`. Di cPanel, pastikan prefix username cPanel disertakan (contoh: `usercpanel_demo`). Pastikan `database.sql` sudah diimpor di phpMyAdmin.

### 2. Error HTTP 500 (Internal Server Error)
- **Penyebab:** Versi PHP di hosting terlalu lama (di bawah PHP 7.4) atau direktif `.htaccess` tertentu diblokir oleh konfigurasi Apache hosting.
- **Solusi:**
  1. Buka menu **MultiPHP Manager** atau **Select PHP Version** di cPanel, lalu pilih **PHP 8.1** atau **PHP 8.2** (minimal PHP 7.4) dan pastikan ekstensi `pdo` serta `pdo_mysql` aktif.
  2. Jika server menggunakan Apache versi khusus yang menolak blok `<IfModule mod_php.c>`, periksa file `.htaccess` di root dan `uploads/.htaccess`.

### 3. Halaman 404 (Not Found) Saat Membuka Menu
- **Penyebab:** File `.htaccess` belum terunggah ke `public_html`, atau file PHP berada di dalam subfolder yang salah.
- **Solusi:**
  1. Seluruh tautan menu utama sudah menggunakan panggilan langsung (`index.php`, `tentang.php`, `layanan.php`, `harga.php`, `faq.php`, `portofolio.php`, `kontak.php`) sehingga tetap berjalan meskipun `mod_rewrite` belum aktif.
  2. Pastikan seluruh file `.php` berada langsung di `public_html/` dan `.htaccess` sudah terunggah.

### 4. Gambar atau Logo Tidak Muncul Setelah Diunggah
- **Penyebab:** Path gambar berubah atau izin baca file (file permission) tidak sesuai.
- **Solusi:** Pastikan folder `uploads/` berada di root `public_html/uploads/` dengan permission folder **`755`** dan file gambar di dalamnya memiliki permission **`644`**.

### 5. Gagal Upload Gambar ("Folder uploads/ tidak dapat ditulis")
- **Penyebab:** Izin (permission) folder `uploads/` di server hosting belum mengizinkan penulisan oleh proses PHP, atau ekstensi `fileinfo` PHP belum aktif.
- **Solusi:**
  1. Di File Manager cPanel/CyberPanel, klik kanan pada folder **`uploads`** -> **Change Permissions**, lalu atur ke **`755`** (`rwxr-xr-x`).
  2. Di menu **Select PHP Version -> Extensions**, pastikan ekstensi **`fileinfo`** dicentang.
