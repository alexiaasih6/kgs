# PORTAL WEBSITE & ADMIN CMS — PT. KREATIF GLOBAL SOLUSINDO (KGS)
**System Integrator · Security Solution · Internet Service Provider (KGSNet)**

Paket aplikasi web perusahaan siap pakai (*production-ready*) berbasis **PHP Native + MySQL/MariaDB (PDO)** yang dirancang khusus agar dapat langsung dijalankan pada **cPanel, CyberPanel, Shared Hosting (`public_html`), XAMPP, dan Laragon** tanpa proses build (`npm`/`composer`) atau akses terminal di server.

---

## 1. SPESIFIKASI TEKNIS & KOMPATIBILITAS SERVER

- **Versi PHP Minimum:** PHP **7.4+** (Kompatibel penuh dengan PHP 7.4, 8.0, 8.1, 8.2, dan 8.3)
- **Database:** MySQL 5.7+ / MySQL 8.x atau MariaDB 10.3+ (`utf8mb4_unicode_ci`, Engine `InnoDB`)
- **Ekstensi PHP Standar Hosting:**
  - `pdo` & `pdo_mysql` (Koneksi database dengan Prepared Statements)
  - `fileinfo` (Validasi MIME type gambar saat upload)
  - `session` (Manajemen sesi autentikasi admin & CSRF)
  - `mbstring` & `json`
- **Web Server:** Apache (`mod_rewrite` opsional, dilengkapi fallback `.php` langsung), LiteSpeed / OpenLiteSpeed, atau Nginx.

---

## 2. STRUKTUR DIREKTORI PAKET HOSTING

```text
├── index.php                  # 1. Halaman Utama (Beranda)
├── tentang.php                # 2. Halaman Tentang Kami, Sejarah, Visi & Misi
├── layanan.php                # 3. Katalog 5 Segmen Bisnis & 22 Standar Layanan
├── detail-layanan.php         #    Detail Spesifikasi Layanan
├── harga.php                  # 4. Halaman Paket Harga & Solusi Bisnis (B2B)
├── faq.php                    # 5. Halaman Tanya Jawab Teknis (FAQ)
├── portofolio.php             # 6. Halaman Dokumentasi Proyek Lapangan
├── klien.php                  #    Daftar 96+ Klien Resmi BUMN, Swasta & Instansi
├── kontak.php                 # 7. Halaman Kontak, Google Maps & Form Direct WA CS
├── blog.php                   #    Daftar Artikel & Wawasan Teknologi
├── detail-blog.php            #    Detail Artikel & Label Topik
├── page.php                   #    Halaman Statis Dinamis
├── kebijakan-privasi.php      #    Halaman Kebijakan Privasi
├── 404.php                    #    Halaman Error 404 Kustom
├── sitemap.php                #    Generator XML Sitemap Dinamis
├── robots.txt                 #    Aturan Crawler Mesin Pencari
├── .htaccess                  #    Konfigurasi Keamanan Apache & URL Bersih
├── database.sql               #    Skema & Data Awal MySQL/MariaDB Siap Import phpMyAdmin
├── INSTALL.md                 #    Panduan Instalasi Hosting Langkah demi Langkah
├── README.md                  #    Dokumentasi Arsitektur Proyek
│
├── config/
│   ├── app.php                #    Konfigurasi Environment, Base URL & Mode Produksi
│   ├── database.php           #    Konfigurasi Koneksi PDO MySQL (Default: demo_isp1)
│   ├── database.example.php   #    Contoh Template Konfigurasi Tanpa Password
│   └── .htaccess              #    Proteksi Akses HTTP ke Folder Konfigurasi
│
├── includes/
│   ├── header.php             #    Header Publik, Logo/Favicon Dinamis & 7 Menu Utama
│   ├── footer.php             #    Footer Dinamis & Informasi Kantor Palembang/Jakarta
│   ├── functions.php          #    Fungsi Sanitasi XSS e(), get_setting(), Google Maps & WA
│   ├── seo.php                #    Generator Meta Tag SEO & OpenGraph
│   ├── auth.php               #    Autentikasi Bcrypt, Session Regeneration & Audit Log
│   ├── csrf.php               #    Proteksi Token Anti-CSRF
│   └── upload.php             #    Validasi Upload Gambar Aman (MIME, Ekstensi, Ukuran)
│
├── pages/                     #    Alias Halaman Publik (beranda, tentang-kami, layanan, dll.)
│
├── admin/                     #    Panel Admin CMS Lengkap (Sidebar Kiri)
│   ├── index.php              #    Dashboard Statistik & Leads Terbaru
│   ├── login.php              #    Halaman Login Admin (Dilindungi Throttling & CSRF)
│   ├── register-admin.php     #    Inisialisasi Akun Superadmin Pertama (Otomatis Terkunci)
│   ├── logout.php             #    Logout & Penghancuran Sesi Aman
│   ├── pengaturan.php         #    Pengaturan Profil, Upload Logo/Favicon, WA CS & Maps
│   ├── layanan.php            #    CRUD Layanan (+ tambah-layanan.php, edit-layanan.php)
│   ├── audit-pdf.php          #    CRUD Katalog 22 Standar Layanan Resmi
│   ├── kategori.php           #    CRUD 5 Segmen Kategori Layanan
│   ├── harga.php              #    CRUD Paket Harga & Solusi B2B
│   ├── faq.php                #    CRUD Tanya Jawab (FAQ)
│   ├── portofolio.php         #    CRUD Dokumentasi Proyek & Upload Foto Lapangan
│   ├── klien.php              #    CRUD Daftar Klien Perusahaan
│   ├── blog.php               #    CRUD Artikel, Upload Gambar & Label (+ tambah/edit)
│   ├── halaman.php            #    CRUD Halaman Statis Website
│   ├── media.php              #    Media Manager & Pustaka Gambar Terunggah
│   ├── inquiries.php          #    Manajemen Pesan Kontak & Permintaan Penawaran
│   ├── menu.php               #    Pengaturan Menu Navigasi Header & Footer
│   ├── seo.php                #    Pengaturan Metadata SEO Global
│   ├── users.php              #    Manajemen Akun Admin & Role (Superadmin / Editor)
│   └── audit-log.php          #    Catatan Riwayat Aktivitas Admin
│
├── assets/
│   ├── css/style.css          #    Stylesheet Lokal & Aturan Tipografi
│   ├── js/main.js             #    Script Navigasi Mobile & Utilitas Clipboard
│   ├── images/                #    Aset Logo Vektor KGS & Favicon Default
│   └── fonts/fonts.css        #    Konfigurasi Font Stack
│
└── uploads/
    ├── .htaccess              #    Blokir Eksekusi Script PHP di Dalam Folder Upload
    └── .gitkeep               #    Penjaga Direktori Upload
```

---

## 3. PANDUAN CEPAT INSTALASI

Silakan baca berkas **`INSTALL.md`** untuk langkah-langkah instalasi lengkap di cPanel, CyberPanel, maupun XAMPP/Laragon beserta panduan *troubleshooting*.
