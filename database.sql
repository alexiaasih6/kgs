-- ==========================================================
-- MASTER DATABASE SCHEMA: PT. KREATIF GLOBAL SOLUSINDO (KGS)
-- Professional Corporate Website + Complete Admin CMS
-- Kompatibel: MySQL 5.7+, MySQL 8.x, MariaDB 10.3+, cPanel, CyberPanel, XAMPP, Laragon
-- Karakter: utf8mb4_unicode_ci
-- ==========================================================
-- CATATAN IMPORT PHPMYADMIN:
-- Pilih database yang telah Anda buat di cPanel/CyberPanel (misal: demo_isp1)
-- pada panel kiri phpMyAdmin, lalu buka tab "Import" dan pilih file database.sql ini.
-- File ini tidak menggunakan perintah destruktif (DROP DATABASE) atau perintah yang
-- memerlukan hak akses root/superadmin MySQL sehingga aman di semua shared hosting.
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Tabel Pengguna & Hak Akses (users)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('superadmin', 'editor') DEFAULT 'superadmin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabel Audit Log Aktivitas Keamanan (audit_logs)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabel Pengaturan Website & Identitas (site_settings)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_name` VARCHAR(50) NOT NULL DEFAULT 'general',
  `key_name` VARCHAR(60) NOT NULL UNIQUE,
  `key_value` LONGTEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`group_name`, `key_name`, `key_value`) VALUES
('identity', 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO'),
('identity', 'short_name', 'KGSNET'),
('identity', 'footer_brand_label', 'KGSNET'),
('identity', 'brand_name', 'Creative Com / KGS'),
('identity', 'tagline', 'Your Solution & Partner'),
('identity', 'business_focus', 'System Integrator, Security Solution & Internet Service Provider'),
('identity', 'company_profile', 'PT. Kreatif Global Solusindo adalah perusahaan teknologi terkemuka yang berbasis di Palembang, melayani integrasi sistem, solusi keamanan, dan layanan penyedia internet berlisensi resmi APJII sejak 1997.'),
('identity', 'established_year', '1997'),
('identity', 'pt_year', '2013'),
('identity', 'isp_year', '2020'),
('identity', 'isp_brand', 'KGSNet'),
('identity', 'vision', 'Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.'),
('identity', 'mission', 'Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.'),
('identity', 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi.'),
('identity', 'commitment', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.'),
('contact', 'office_palembang_label', 'Alamat Kantor'),
('contact', 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113'),
('contact', 'city_province', 'Palembang, Sumatera Selatan'),
('contact', 'postal_code', '30113'),
('contact', 'phone_hunting', '0711-316088 (Hunting)'),
('contact', 'phone_palembang', '0711-316088 (Hunting)'),
('contact', 'whatsapp', '0811-782119'),
('contact', 'email', 'info@kgs.net.id'),
('contact', 'email_general', 'info@kgs.net.id'),
('contact', 'email_sales', 'sales@kgs.net.id'),
('contact', 'email_support', 'noc@kgs.net.id'),
('contact', 'operating_hours', 'Senin - Sabtu: 08:30 - 17:00 WIB'),
('contact', 'official_website', 'www.creative.co.id / www.kgs.net.id'),
('contact', 'google_map_input', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113'),
('contact', 'wa_default_msg', 'Halo PT. Kreatif Global Solusindo, saya tertarik untuk berkonsultasi mengenai solusi teknologi.'),
('branding', 'company_logo', ''),
('branding', 'logo_main', 'assets/images/logo-kgs.svg'),
('branding', 'logo_dark', 'assets/images/logo-kgs-white.svg'),
('branding', 'favicon', 'assets/images/favicon.svg'),
('branding', 'primary_color', '#1d4ed8'),
('branding', 'secondary_color', '#0f172a'),
('branding', 'font_family', 'Plus Jakarta Sans'),
('seo', 'meta_title_default', 'PT. Kreatif Global Solusindo - System Integrator, Security Solution & ISP'),
('seo', 'meta_desc_default', 'Portal resmi PT. Kreatif Global Solusindo: Solusi jaringan komputer, fiber optik, PABX, CCTV, penangkal petir, dan ISP KGSNet resmi APJII sejak 1997 di Palembang.'),
('seo', 'canonical_url', 'https://www.kgs.net.id'),
('seo', 'robots_directive', 'index, follow'),
('footer', 'copyright_text', 'All Rights Reserved.'),
('footer', 'privacy_policy_link', 'kebijakan-privasi.php')
ON DUPLICATE KEY UPDATE `key_value` = VALUES(`key_value`);

-- --------------------------------------------------------
-- 4. Tabel Halaman Statis / Pages (pages)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `summary` TEXT,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `title`, `slug`, `summary`, `content`, `meta_title`, `meta_description`, `is_published`, `sort_order`) VALUES
(1, 'Profil & Sejarah Perusahaan', 'profil-perusahaan', 'Sejarah Creative Com sejak 1997 hingga menjadi PT. Kreatif Global Solusindo dan ISP berizin APJII.', '<h2>Sejarah Berdiri</h2><p>Creative Com adalah nama tempat usaha yang menjual produk IT dengan legalitas CV. CREATIVE, berdomisili di Palembang sejak 1997 dan sejak 2013 menjadi PT. KREATIF GLOBAL SOLUSINDO. Pada awal berdirinya Creative Com bergerak dalam bidang jasa perbaikan perangkat computer dan jaringan. Kemudian di 1998, merambah kebidang penjualan perangkat wartel sebagai distributor perangkat wartel merk QPHONE. Lalu pada 2002, Panasonic mempercayakan CREATIVE COM sebagai salah satu dealernya di Palembang. Dengan mengamati perkembangan teknologi dan kebutuhan masyarakat, maka sejak 2004 melayani permintaan pemasangan jaringan komputer (Cable / Wireless), radio komunikasi, pabx serta tower antenna. Dan saat ini, sejak tahun 2020, PT. KGS juga telah resmi memiliki izin sebagai perusahaan penyelenggara jasa internet (ISP) dengan nama produk KGSNet dan tergabung menjadi anggota APJII.</p>', 'Profil & Sejarah Perusahaan - PT. KGS', 'Sejarah resmi perjalanan PT. Kreatif Global Solusindo dari tahun 1997 hingga kini.', 1, 1),
(2, 'Visi, Budaya & Komitmen', 'visi-misi-budaya', 'Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan.', '<h2>Visi & Misi</h2><p><strong>Visi:</strong> Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.</p><p><strong>Misi:</strong> Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.</p><h2>Budaya Organisasi</h2><p>Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif.</p>', 'Visi, Misi & Budaya Kerja - PT. KGS', 'Visi terdepan, misi kemandirian, dan komitmen kepuasan pelanggan PT. KGS.', 1, 2),
(3, 'Kebijakan Privasi Pengguna', 'kebijakan-privasi', 'Komitmen PT. Kreatif Global Solusindo dalam menjaga kerahasiaan dan keamanan data pelanggan, rekanan, dan calon klien.', '<h2>1. Pengumpulan Informasi</h2><p>PT. Kreatif Global Solusindo (KGS) menghormati dan melindungi privasi setiap pengunjung situs dan pelanggan kami. Informasi yang dikumpulkan melalui formulir konsultasi, permintaan penawaran harga, atau korespondensi langsung (seperti nama, nama perusahaan, nomor telepon, alamat email, dan lokasi proyek) hanya digunakan untuk kepentingan verifikasi teknis, penyusunan proposal solusi, dan komunikasi resmi perusahaan.</p><h2>2. Keamanan Data Pelanggan</h2><p>Kami menerapkan prosedur teknis dan manajemen yang ketat untuk mengamankan data Anda dari akses tanpa izin, perubahan, atau pengungkapan yang tidak sah. Seluruh data penawaran harga disimpan dalam server yang aman dengan enkripsi standar industri.</p><h2>3. Tidak Ada Pembagian Data kepada Pihak Ketiga</h2><p>Kami tidak akan menjual, menyewakan, atau membagikan informasi kontak pribadi atau data korporasi Anda kepada pihak ketiga manapun untuk tujuan pemasaran komersial tanpa persetujuan eksplisit dari Anda, kecuali jika diwajibkan oleh peraturan hukum yang berlaku di Republik Indonesia.</p><h2>4. Hubungi Kami</h2><p>Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: <strong>info@kgs.net.id</strong> atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.</p>', 'Kebijakan Privasi - PT. KGS', 'Kebijakan privasi perlindungan data pengguna PT. Kreatif Global Solusindo.', 1, 3),
(4, 'Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider', 'beranda', 'Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.', '<p>PT. Kreatif Global Solusindo (KGS) menghadirkan infrastruktur jaringan komputer, fiber optik NETVIEL, sentral telepon PABX Panasonic, radio komunikasi 2-arah, pengawasan CCTV Sony & Hikvision, proteksi penangkal petir, serta koneksi internet KGSNet resmi APJII dengan dukungan NOC 24/7 dan SLA 99.5%.</p>', 'Beranda - System Integrator, Security Solution & ISP', 'Solusi jaringan komputer, fiber optik, PABX, CCTV, penangkal petir, dan ISP KGSNet resmi APJII.', 1, 4),
(5, 'Tentang PT. Kreatif Global Solusindo', 'tentang-kami', 'Perjalanan dedikasi teknologi sejak didirikan dengan nama Creative Com pada tahun 1997 di Palembang hingga bertransformasi menjadi System Integrator & ISP berlisensi resmi APJII.', '<p>Berawal dari CV. CREATIVE pada tahun 1997 di Palembang, kami terus bertumbuh menjadi distributor perangkat telekomunikasi, dealer resmi PABX Panasonic (2002), kontraktor jaringan & menara tower (2004), bertransformasi menjadi PT. Kreatif Global Solusindo (2013), hingga resmi memegang izin penyelenggara jasa internet (ISP) KGSNet dan anggota APJII (2020).</p>', 'Tentang Kami - Profil Perusahaan PT. KGS', 'Identitas, sejarah perjalanan, visi misi, dan mitra kerja resmi PT. Kreatif Global Solusindo.', 1, 5),
(6, '5 Segmen Layanan & Solusi Terintegrasi', 'layanan', 'Infrastruktur komputasi, telekomunikasi radio & PABX, surveillance keamanan CCTV, internet ISP KGSNet, hingga fabrikasi tower dan sistem proteksi petir.', '<p>Katalog lengkap solusi teknologi informasi, telekomunikasi, kamera pengawas CCTV, konektivitas ISP KGSNet, pabrikasi menara guywire, sistem penangkal petir grounding < 2 Ohm, serta sistem AIS maritim untuk korporasi, perkebunan, pertambangan, perbankan, dan instansi pemerintah.</p>', 'Katalog Layanan & Solusi Terintegrasi - PT. KGS', '5 segmen layanan utama PT. Kreatif Global Solusindo untuk bisnis dan instansi.', 1, 6),
(7, 'Pilihan Paket Solusi & Layanan B2B', 'harga', 'Struktur paket transparan yang dirancang khusus untuk kebutuhan konektivitas bisnis, keamanan terpadu, infrastruktur remote site, dan pengadaan proyek pemerintah/BUMN dengan SLA terjamin.', '<p>Setiap paket solusi bisnis dirancang sesuai standar reliabilitas industri dengan dukungan survei lokasi (on-site survey) dan penyusunan Bill of Quantity (BoQ) yang presisi sesuai kebutuhan RKS perusahaan atau instansi Anda.</p>', 'Pilihan Paket & Solusi Bisnis (B2B) - PT. KGS', 'Daftar paket Dedicated Internet KGSNet, CCTV Surveillance, dan Tower Guywire PT. KGS.', 1, 7),
(8, 'Pertanyaan Umum & Jawaban Teknis', 'faq', 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.', '<p>Temukan jawaban teknis mengenai jangkauan layanan operasional Sumatera hingga nasional, lisensi resmi ISP KGSNet APJII, prosedur survei lapangan, serta garansi pengujian tahanan pembumian di bawah 2 Ohm.</p>', 'Tanya Jawab (FAQ) & Bantuan Teknis - PT. KGS', 'Pertanyaan umum seputar layanan ISP KGSNet, grounding, tower, dan tender B2B.', 1, 8),
(9, 'Portofolio Proyek & Klien Korporasi', 'portofolio', 'Bukti nyata dedikasi teknis PT. KGS: dari pabrikasi mandiri tower guywire dan pengujian grounding di bawah 2 Ohm, hingga integrasi jaringan dan sistem keamanan untuk 100+ mitra BUMN dan korporasi.', '<p>Dokumentasi pengerjaan instalasi lapangan oleh tim teknisi bersertifikasi PT. Kreatif Global Solusindo di berbagai sektor strategis: BUMN, perbankan, rumah sakit, perkebunan kelapa sawit, pertambangan batubara, dan navigasi pelabuhan.</p>', 'Dokumentasi Proyek & Portofolio Nyata - PT. KGS', 'Rekam jejak proyek lapangan dan daftar klien terverifikasi PT. Kreatif Global Solusindo.', 1, 9),
(10, 'Hubungi Tim PT. Kreatif Global Solusindo', 'kontak', 'Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.', '<p>Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, penjadwalan survei lokasi (on-site survey), serta pengajuan proposal penawaran harga resmi.</p>', 'Kontak & Permintaan Penawaran - PT. KGS', 'Hubungi Alamat Kantor PT. Kreatif Global Solusindo.', 1, 10),
(11, 'Artikel Solusi Teknologi & Praktik Terbaik Industri', 'blog', 'Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.', '<p>Pusat edukasi dan publikasi artikel teknis dari tim engineer PT. Kreatif Global Solusindo seputar infrastruktur jaringan serat optik, proteksi petir industri, telekomunikasi radio, dan navigasi maritim.</p>', 'Artikel & Wawasan Teknologi - PT. KGS', 'Kumpulan artikel edukasi teknologi informasi dan telekomunikasi industri PT. KGS.', 1, 11)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `summary` = VALUES(`summary`), `content` = VALUES(`content`);

-- --------------------------------------------------------
-- 5. Tabel Menu Navigasi (menus)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `location` ENUM('header', 'footer') DEFAULT 'header',
  `parent_id` INT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `target` ENUM('_self', '_blank') DEFAULT '_self',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `menus` (`id`, `title`, `url`, `location`, `parent_id`, `sort_order`, `target`, `is_active`) VALUES
(1, 'Beranda', 'index.php', 'header', NULL, 1, '_self', 1),
(2, 'Tentang Kami', 'tentang.php', 'header', NULL, 2, '_self', 1),
(3, 'Layanan', 'layanan.php', 'header', NULL, 3, '_self', 1),
(4, 'Harga', 'harga.php', 'header', NULL, 4, '_self', 1),
(5, 'FAQ', 'faq.php', 'header', NULL, 5, '_self', 1),
(6, 'Portofolio', 'portofolio.php', 'header', NULL, 6, '_self', 1),
(7, 'Kontak', 'kontak.php', 'header', NULL, 7, '_self', 1),
(8, 'Profil Perusahaan', 'page.php?slug=profil-perusahaan', 'footer', NULL, 1, '_self', 1),
(9, 'Visi & Misi', 'page.php?slug=visi-misi-budaya', 'footer', NULL, 2, '_self', 1),
(10, 'Katalog Layanan', 'layanan.php', 'footer', NULL, 3, '_self', 1),
(11, 'Artikel Edukasi', 'blog.php', 'footer', NULL, 4, '_self', 1),
(12, 'Kebijakan Privasi', 'kebijakan-privasi.php', 'footer', NULL, 5, '_self', 1);

-- --------------------------------------------------------
-- 6. Tabel Kategori Layanan (service_categories)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `segment_number` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `pdf_page` INT NOT NULL DEFAULT 4,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_categories` (`id`, `code`, `segment_number`, `name`, `description`, `pdf_page`) VALUES
(1, 'comp-net', 1, 'Computer & Networking', 'Pengadaan PC/laptop, instalasi LAN/WLAN/Hotspot, Fiber Optic dan reparasi komputer.', 4),
(2, 'telecom', 2, 'Telecommunication', 'PABX/IP-PBX, Radio SSB/HT/RIG/Marine, Radiolink Single Channel, VOIP dan NurseCall.', 4),
(3, 'cctv-sec', 3, 'Video Surveillance / CCTV', 'CCTV Analog dan IP Network Camera terintegrasi serta sistem Door Access Control.', 4),
(4, 'isp', 4, 'Internet Service Provider', 'Layanan ISP resmi KGSNet (APJII), Wireless & Fiber Optic, Hosting, Server Colo & Cloud.', 4),
(5, 'another', 5, 'Another Service', 'Video Conference, Tower Erection (workshop sendiri), Penangkal Petir, Alarm Kebakaran, dan AIS.', 4)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- --------------------------------------------------------
-- 7. Tabel Master Layanan (services - 22 Standar Layanan KGS)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `summary` TEXT NOT NULL,
  `description` LONGTEXT NOT NULL,
  `main_image` VARCHAR(255) DEFAULT NULL,
  `features` TEXT,
  `brands` TEXT,
  `target_sectors` TEXT,
  `pdf_page` INT NOT NULL DEFAULT 4,
  `verification_status` ENUM('Standar Resmi KGS', 'Terverifikasi dari PDF', 'Perlu Konfirmasi', 'Tambahan Disetujui Admin') DEFAULT 'Standar Resmi KGS',
  `admin_notes` TEXT,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `is_published` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `service_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `category_id`, `name`, `slug`, `summary`, `description`, `pdf_page`, `verification_status`, `admin_notes`, `is_published`, `sort_order`) VALUES
(1, 1, 'PC Desktop, Notebook, Printer, Scanner dan Accesoris Komputer lainnya', 'pc-desktop-notebook-printer-scanner', 'Pengadaan komputer desktop, laptop/notebook, printer, scanner, dan aksesoris komputer bergaransi resmi.', 'Layanan pengadaan unit PC Desktop, laptop / notebook, printer, scanner serta berbagai perlengkapan aksesoris komputer untuk kebutuhan perkantoran, instansi pemerintah, BUMN, perbankan, dan sektor industri.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 1),
(2, 1, 'Installasi LAN, Wireless LAN, Internet Connection, HotSpot Area', 'instalasi-lan-wireless-lan-hotspot', 'Perancangan dan penarikan kabel LAN UTP/STP, konfigurasi Wireless LAN, koneksi internet dan hotspot area.', 'Layanan instalasi infrastruktur jaringan lokal kabel (LAN) dan nirkabel (Wireless LAN), konfigurasi routing & switching, koneksi internet terintegrasi, serta pembuatan hotspot area untuk gedung, pabrik, perkebunan, dan instansi.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 2),
(3, 1, 'Fibre Optic Cable Networking', 'fibre-optic-cable-networking', 'Instalasi kabel fiber optik, splicing, OTDR termination, dan perancangan backbone throughput tinggi.', 'Pemasangan jaringan kabel serat optik (Fiber Optic) untuk backbone antar gedung, kawasan industri, perkebunan, maupun link jarak jauh dengan kapasitas transfer data kecepatan tinggi. Didukung lisensi resmi NETVIEL InfiTe Fiber Optic Connectivity.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 3),
(4, 1, 'Komputer Service & Repair', 'komputer-service-and-repair', 'Jasa perbaikan perangkat komputer, troubleshooting hardware/software, dan maintenance sistem.', 'Layanan pemeliharaan berkala, diagnosis kerusakan hardware, perbaikan motherboard/power supply, penggantian suku cadang, dan perbaikan software sistem komputer yang telah dirintis sejak 1997.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 4),
(5, 2, 'PABX / IP PBX', 'pabx-ip-pbx-system', 'Sistem sentral telepon kantor analog, digital, maupun IP-PBX modern untuk efisiensi komunikasi suara perusahaan.', 'Pemasangan, pemrograman, dan integrasi perangkat Private Automatic Branch Exchange (PABX) serta IP PBX untuk interkoneksi ekstensi internal kantor, hotel, rumah sakit, dan industri. Creative Com telah dipercaya sebagai dealer resmi Panasonic di Palembang sejak tahun 2002.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 5),
(6, 2, 'SSB, Handy Talky, Repeater, RIG, Marine', 'ssb-handy-talky-repeater-rig-marine', 'Solusi radio komunikasi 2 arah jarak jauh untuk operasional lapangan, tambang, perkebunan, dan maritim.', 'Penyediaan dan instalasi perangkat radio komunikasi High Frequency (SSB), VHF/UHF, Handy Talky (HT), stasiun pangkalan (RIG), stasiun repeater penguat sinyal, serta sistem radio perkapalan (Marine Radio). Brand: Motorola, ICOM, Alinco.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 6),
(7, 2, 'Radiolink Telephone Single Channel & Data', 'radiolink-telephone-single-channel-data', 'Transmisi telepon dan data point-to-point nirkabel frekuensi radio ke lokasi terpencil / remote area.', 'Solusi penghubung saluran telepon dan jalur data nirkabel berbasis tautan radio (Radiolink Single Channel) untuk menjangkau lokasi perkebunan, dermaga, basecamp pertambangan, dan stasiun kepanduan yang belum terjangkau jaringan kabel konvensional.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 7),
(8, 2, 'Telephone Billing System', 'telephone-billing-system', 'Sistem pencatatan, kalkulasi tarif pemakaian telepon, dan pelaporan biaya komunikasi hotel/kantor.', 'Perangkat lunak dan hardware antarmuka pencatat data panggilan telepon (Station Message Detail Recording / SMDR) untuk kontrol anggaran telepon kantor serta kalkulasi tagihan kamar hotel secara otomatis.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 8),
(9, 2, 'VOIP Gateway / IP Telephone', 'voip-gateway-ip-telephone', 'Integrasi Voice over IP untuk transmisi percakapan telepon melalui jaringan data internet/intranet.', 'Penggunaan gateway VOIP dan pesawat IP Phone untuk mentransmisikan percakapan suara melalui protokol internet (IP), memungkinkan komunikasi gratis antar kantor cabang melalui jaringan data internal tanpa tarif interlokal.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 9),
(10, 2, 'NurseCall', 'nursecall-system', 'Sistem pemanggil perawat terintegrasi untuk ruang rawat inap rumah sakit dan klinik bersalin.', 'Instalasi perangkat sistem pemanggil perawat (Nurse Call) dengan tombol darurat di sisi tempat tidur pasien dan kamar mandi yang terhubung langsung ke master station di pos perawat (nurse station). Brand: Commax, Aiphone.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 10),
(11, 3, 'CCTV, baik Analog maupun IP (Network) Camera', 'cctv-analog-ip-network-camera', 'Sistem pengawasan kamera keamanan resolusi tinggi Analog maupun IP Camera berbasis jaringan.', 'Perancangan, instalasi kabel coaxial / UTP, konfigurasi Network Video Recorder (NVR) / Digital Video Recorder (DVR), serta integrasi pemantauan kamera pengawas CCTV multi-site. Brand: Sony, Samsung, Panasonic, BOSCH, Honeywell, Avtech, Keeper, G-Lenz, Hikvision, Dahua.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 11),
(12, 3, 'Door Access Control System', 'door-access-control-system', 'Sistem pengaman pintu elektronik berbasis kartu RFID, PIN, dan biometrik untuk ruang penting.', 'Pemasangan sistem pembatas dan pengaman akses pintu masuk (Access Control) menggunakan magnetic lock / drop bolt dengan autentikasi kartu RFID, smart key, atau biometrik untuk ruang server, brankas, kantor manajemen, dan area sensitif. Brand: Rosslare, HID, EntryPass.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 12),
(13, 4, 'Koneksi via Wireless & Fiber Optic (KGSNet)', 'koneksi-internet-kgsnet-wireless-fiber-optic', 'Layanan sambungan internet dedicated dan broadband berizin resmi KGSNet melalui radio wireless dan fiber optik.', 'Layanan penyedia jasa internet (ISP) resmi berizin tahun 2020 dengan brand KGSNet dan anggota resmi APJII. Menghubungkan kawasan industri, perkebunan, pelabuhan, dan perkantoran melalui media transmisi Wireless Point-to-Point maupun kabel Fiber Optic berkecepatan tinggi dengan peering Neucentrix.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 13),
(14, 4, 'Web & Mail Hosting, Server Colo & Cloud', 'web-mail-hosting-server-colo-cloud', 'Layanan hosting website perusahaan, email bisnis korporasi, serta colocation dan cloud server.', 'Penyediaan infrastruktur hosting website domain perusahaan, email hosting korporasi yang stabil dan aman, fasilitas rak colocation server di data center, serta komputasi awan (Cloud) untuk operasional aplikasi bisnis terpusat.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 14),
(15, 4, 'Network Managed Service', 'network-managed-service', 'Layanan pengelolaan, pemeliharaan proaktif, dan monitoring performa infrastruktur jaringan pelanggan.', 'Pengelolaan menyeluruh jaringan komputer dan internet perusahaan, meliputi pemeliharaan perangkat router/switch, monitoring trafik transmisi, penanganan kendala teknis cepat tanggap, serta optimalisasi bandwidth.', 14, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 15),
(16, 5, 'Audio – Video Conference System', 'audio-video-conference-system', 'Sistem konferensi audio dan video interaktif untuk ruang rapat direksi, aula sidang, dan ruang seminar.', 'Instalasi perangkat sistem telekonferensi video dan tata suara konferensi audio interaktif dengan mikrofon meja delegate/chairman, kamera PTZ tracking, dan sound processor berkualitas tinggi. Brand: BOSCH, TOA, POLYCOM.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 16),
(17, 5, 'Biometric Attendant (Mesin Absensi)', 'biometric-attendant', 'Mesin pencatat kehadiran pegawai menggunakan sidik jari (fingerprint) dan pengenalan biometrik.', 'Penyediaan dan konfigurasi mesin absensi biometrik untuk mencatat waktu kehadiran karyawan secara akurat, terhubung ke software payroll dan manajemen SDM perusahaan. Brand: Magic, Solution, I-Bio.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 17),
(18, 5, 'Tower Erection (Guywire Tower & Self Supporting Tower)', 'tower-erection-guywire-self-supporting', 'Fabrikasi, ereksi, dan pemeliharaan menara telekomunikasi (Guywire Tower dan Self Supporting Tower).', 'Layanan pabrikasi besi rangka tower di Workshop Creative Com sendiri, dilanjutkan proses ereksi (pemasangan berdiri) tower jenis Guywire (tarikan kawat seling) maupun Self Supporting Tower (SST 3/4 kaki) untuk antena radio, internet wireless, dan penangkal petir.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 18),
(19, 5, 'Lightning Protection System (Penangkal Petir)', 'lightning-protection-system', 'Instalasi sistem penangkal petir elektrostatis/konvensional, grounding pembumian, dan arrester panel.', 'Perancangan dan pemasangan sistem proteksi petir eksternal dan internal untuk melindungi gedung, pembangkit listrik, pabrik sawit, dan menara telekomunikasi dari sambaran petir. Meliputi head terminal (KURN, ERICO, VIKING, THOMAS), grounding < 2 Ohm teruji Earth Resistance Tester, dan surge arrester.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 19),
(20, 5, 'AIC / AIS (Automatic Identification System)', 'automatic-identification-system-ais', 'Sistem identifikasi dan pelacakan kapal otomatis berbasis AIS Receiver dan integrasi kamera pengawas.', 'Pemasangan dan integrasi sistem identifikasi kapal maritim otomatis (Automatic Identification System / AIS) yang memadukan AIS Receiver VHF dengan kamera pengawas Hikvision resolusi tinggi dan antena menara untuk pemantauan lalu lintas perairan sungai dan pelabuhan.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 20),
(21, 5, 'Fire & Intrusion Alarm System', 'fire-intrusion-alarm-system', 'Sistem deteksi dini bahaya kebakaran dan alarm keamanan intrusi pencurian untuk gedung dan perbankan.', 'Pemasangan Master Control Fire Alarm (MCFA), detektor asap (smoke detector), detektor panas (heat detector), manual call point, serta sirine evakuasi (BOSCH, Notifier, Honeywell, Hooseki) dipadukan dengan sensor gerak dan door contact alarm intrusi.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 21),
(22, 5, 'Pengadaan Khusus: Sound System, Mesin Antri, Labor Bahasa & Solar Panel', 'pengadaan-khusus-sound-mesin-antri-labor-bahasa-solar', 'Solusi pengadaan sound system gedung, mesin antrian Quenetic, laboratorium bahasa, dan panel surya.', 'Layanan proyek pengadaan spesifik yang telah sukses diselesaikan oleh Creative Com / PT. KGS, mencakup tata suara Public Address / Sound System aula, Mesin Antrian Quenetic System di kantor imigrasi dan perbankan, perangkat laboratorium bahasa sekolah, serta instalasi sistem panel tenaga surya di area industri.', 13, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 22)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `summary` = VALUES(`summary`);

-- --------------------------------------------------------
-- 8. Tabel Paket Harga & Solusi Bisnis B2B (pricing_packages)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pricing_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code_label` VARCHAR(30) NOT NULL DEFAULT 'ISP',
  `name` VARCHAR(150) NOT NULL,
  `target_sector` VARCHAR(120) DEFAULT NULL,
  `tagline` VARCHAR(255) DEFAULT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `price_label` VARCHAR(120) DEFAULT 'Sesuai Kapasitas & BoQ',
  `period` VARCHAR(100) DEFAULT 'Survei Lokasi Gratis',
  `sla_badge` VARCHAR(150) DEFAULT NULL,
  `features` TEXT NOT NULL,
  `cta_text` VARCHAR(100) DEFAULT 'Minta Penawaran Paket Ini',
  `badge_text` VARCHAR(100) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pricing_packages` (`id`, `code_label`, `name`, `target_sector`, `subtitle`, `price_label`, `sla_badge`, `features`, `badge_text`, `is_featured`, `is_active`, `sort_order`) VALUES
(1, 'ISP', 'Dedicated Internet B2B (KGSNet)', 'Perkantoran & Industri', 'Koneksi simetris tanpa FUP dengan izin resmi Kemenkominfo & APJII.', 'Sesuai Kapasitas Bandwidth', 'SLA 99.5% Uptime Terjamin', 'Bandwidth 1:1 CIR Simetris (Upload & Download setara)\nRedundant Fiber Optic & Wireless Link Backup\nDedicated IP Public Static /29\nPeering Router BGP Neucentrix Data Center\nPemantauan MRTG Real-Time & NOC Siaga 24/7', 'Solusi Utama Terlaris', 1, 1, 1),
(2, 'CCTV', 'Integrated Security & Surveillance', 'Pelabuhan & Gudang', 'Pengawasan cerdas berbasis IP AI, NVR terpusat & kontrol akses fisik.', 'Sesuai Titik Kamera & BoQ', 'Garansi Hardware & On-Site Support', 'IP Camera AI Human & Vehicle Detection\nNVR Enterprise dengan RAID Storage Redundant\nAccess Control Biometrik Wajah / Fingerprint\nIntegrasi Barrier Gate & Metal Detector\nInstalasi Kabel FO Armor & UTP Berkualitas', '', 0, 1, 2),
(3, 'TOW', 'Tower Guywire & Remote Site', 'Tambang & Sawit', 'Infrastruktur telekomunikasi remote area dan sertifikasi penangkal petir.', 'Sesuai Ketinggian & Lokasi Site', 'Sertifikasi Grounding < 2 Ohm', 'Pabrikasi Guywire Tower 20m - 40m Workshop KGS\nGrounding System Teruji Digital Earth Tester < 2 Ohm\nRepeater Radio VHF/UHF & RIG Jarak Jauh\nWireless Point-to-Point hingga 30+ KM\nTim Bersertifikasi K3 Bekerja di Ketinggian', '', 0, 1, 3);

-- --------------------------------------------------------
-- 9. Tabel Tanya Jawab / FAQ (faqs)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Informasi Teknis',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `faqs` (`id`, `category`, `question`, `answer`, `sort_order`, `is_active`) VALUES
(1, 'Cakupan Wilayah', 'Apa cakupan wilayah layanan operasional PT. Kreatif Global Solusindo?', 'PT. KGS berkantor pusat di Palembang dan memiliki rekam jejak operasional luas di seluruh Sumatera Selatan, Kepulauan Bangka Belitung, Jambi, Riau, Sumatera Utara, hingga wilayah Kalimantan Barat, Kalimantan Timur, dan kawasan industri Cikarang (Jawa Barat). Kami siap mengirimkan tim teknisi ke lokasi remote di seluruh Indonesia.', 1, 1),
(2, 'Legalitas & Lisensi', 'Apakah KGS memiliki izin resmi sebagai Penyelenggara Jasa Internet (ISP)?', 'Ya. PT. Kreatif Global Solusindo mengoperasikan layanan KGSNet dengan Izin Penyelenggaraan Jasa Akses Internet resmi dari Kementerian Komunikasi dan Informatika (Kemenkominfo) RI serta merupakan anggota resmi Asosiasi Penyelenggara Jasa Internet Indonesia (APJII) sejak tahun 2020. Kami memiliki koneksi BGP Peering langsung dengan Neucentrix Data Center.', 2, 1),
(3, 'Metodologi Survei', 'Bagaimana prosedur survei lokasi sebelum penawaran teknis diterbitkan?', 'Untuk proyek jaringan fiber optic, tower, grounding, maupun surveillance, tim engineer KGS melakukan survei lokasi on-site. Survei mencakup pengukuran Line-of-Sight (LOS) untuk wireless link, pengukuran tahanan jenis tanah dengan Earth Tester untuk grounding, dan audit topologi kabel. Hasil survei menjadi dasar Bill of Quantity (BoQ) yang transparan dan akurat tanpa biaya tersembunyi.', 3, 1),
(4, 'Standar Grounding', 'Berapa nilai resistansi tanah yang dijamin pada instalasi grounding?', 'PT. KGS secara ketat menerapkan standar tahanan pembumian di bawah 2 Ohm (< 2 Ω). Kami menggunakan teknik pembumian mendalam dengan elektroda tembaga masif dan bahan penurun resistansi bila diperlukan. Nilai ini diuji dan diverifikasi langsung bersama klien menggunakan alat ukur Digital Earth Resistance Tester sebelum serah terima pekerjaan.', 4, 1),
(5, 'Garansi & SLA', 'Berapa Service Level Agreement (SLA) dan bagaimana dukungan teknis 24/7?', 'Kami memberikan garansi Service Level Agreement (SLA) hingga 99.5% untuk koneksi Dedicated Internet B2B. Network Operation Center (NOC) KGS beroperasi 24 jam sehari, 7 hari seminggu dengan waktu tanggap (response time) remote maksimal 15 menit dan penanganan on-site teknisi darurat maksimal 2-4 jam untuk wilayah operasional utama.', 5, 1),
(6, 'Tender & Pengadaan', 'Apakah PT. KGS melayani tender pengadaan resmi pemerintah dan BUMN?', 'Ya, kami memiliki kelengkapan legalitas dan perizinan usaha yang lengkap (NIB, SIUP, NPWP, Pengusaha Kena Pajak / PKP, Akta Perusahaan, Surat Izin Kominfo) dan telah berpengalaman mengerjakan pengadaan untuk BUMN ternama seperti PT Semen Tonasa, PT Pelindo, PT Pupuk Sriwidjaja (Pusri), perbankan nasional, serta rumah sakit dan instansi pemerintah daerah.', 6, 1);

-- --------------------------------------------------------
-- 10. Tabel Daftar Klien Terverifikasi (clients)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `service_category` VARCHAR(100) NOT NULL,
  `pdf_page` INT NOT NULL DEFAULT 8,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `clients` (`id`, `name`, `category`, `location`, `service_category`, `pdf_page`) VALUES
(1, 'PT. Multrada Multi Maju', 'Perkebunan & Industri', NULL, 'Radiolink & Radio Komunikasi', 8),
(2, 'PT. Karya Sawit Lestari', 'Kelapa Sawit', NULL, 'Radiolink & Radio Komunikasi', 8),
(3, 'PT. Bumi Sawindo Permai', 'Kelapa Sawit', 'Tj. Enim', 'Radiolink & Radio Komunikasi', 8),
(4, 'PT. Hexindo Adi Perkasa', 'Alat Berat & Industri', NULL, 'Radiolink & Radio Komunikasi', 8),
(5, 'PT. Pembangkit Jawa Bali - Talang Duku', 'Energi & Pembangkit Listrik', 'Talang Duku', 'Radiolink & Radio Komunikasi', 8),
(6, 'PT. Harapan Lima Roda, ConocoPhillips', 'Minyak & Gas', NULL, 'Radiolink & Radio Komunikasi', 8),
(7, 'BPK Perwakilan Palembang', 'Instansi Pemerintah', 'Palembang', 'Radiolink & Radio Komunikasi', 8),
(8, 'General Electric Stasiun Kepanduan Tj. Buyut', 'Pelayaran & Industri', 'Tj. Buyut', 'Radiolink & Radio Komunikasi', 8),
(9, 'PT. Pelindo II Cab. Palembang', 'BUMN & Kepelabuhanan', 'Palembang', 'Radiolink & Radio Komunikasi', 8),
(10, 'PT. Kirana Permata', 'Industri Karet', NULL, 'Radiolink & Radio Komunikasi', 8),
(11, 'PT. Tri Aryani', 'Pertambangan Batubara', 'Kab. MUBA', 'Radiolink & Radio Komunikasi', 8),
(12, 'PT. Servo Lintas Raya', 'Logistik & Tambang', NULL, 'Radiolink & Radio Komunikasi', 8),
(13, 'RS M. Husin Palembang', 'Rumah Sakit', 'Palembang', 'Radiolink & Radio Komunikasi', 8),
(14, 'PT. Puma Energy', 'Minyak & Energi', 'Samarinda', 'Radiolink & Radio Komunikasi', 8),
(15, 'Dinas Perikanan Prov. Sumatera Selatan', 'Pemerintah Daerah', 'Sumsel', 'Radiolink & Radio Komunikasi', 8),
(16, 'PT. Pinang Witmas', 'Perkebunan', NULL, 'Radiolink & Radio Komunikasi', 8),
(17, 'PT. Makin Group', 'Agro Industri', NULL, 'Radiolink & Radio Komunikasi', 8),
(18, 'PT. Selapan Jaya', 'Perkebunan', NULL, 'Radiolink & Radio Komunikasi', 8),
(19, 'PT. Medco LPG Kaji', 'Minyak & Gas', 'Kaji', 'Radiolink & Radio Komunikasi', 8),
(20, 'PT. Wiradaya Lintas Sukses', 'Logistik', NULL, 'Radiolink & Radio Komunikasi', 8),
(21, 'PT. Proteksindo Utama Mulia', 'Medco Group', NULL, 'Radiolink & Radio Komunikasi', 8),
(22, 'TAC Pertamina Retco Prima Energi', 'BUMN & Energi', 'Prabumulih', 'Radiolink & Radio Komunikasi', 8),
(23, 'PT. London Sumatera', 'Perkebunan Sawit', NULL, 'Radiolink & Radio Komunikasi', 8),
(24, 'PT. Minanga Ogan', 'Perkebunan Sawit', NULL, 'Radiolink & Radio Komunikasi', 8),
(25, 'PT. ABP Palembang & Jambi', 'Perkebunan', 'Palembang & Jambi', 'Radiolink & Radio Komunikasi', 8),
(26, 'PT. OKI Pulp & Paper', 'SinarMas Group / Pulp & Paper', 'OKI', 'Radiolink & Radio Komunikasi', 8),
(27, 'PT. SAMPOERNA AGRO GROUP', 'Agro Bisnis', NULL, 'Radiolink & Radio Komunikasi', 8),
(28, 'PT. Pulau Hijau Multi Lestari', 'Perkebunan', NULL, 'Radiolink & Radio Komunikasi', 8),
(29, 'Indofood Group (PT. Serikat Putra, PT. Pelangi Inti Pertiwi, PT. Gunung Mas Raya)', 'Makanan & Perkebunan', NULL, 'Radiolink & Radio Komunikasi', 8),
(30, 'Rumah Sakit Islam Siti Khadijah', 'Rumah Sakit', 'Palembang', 'PABX & Billing System', 9),
(31, 'BPN Prov. Sumsel', 'Instansi Pemerintah', 'Sumsel', 'PABX & Billing System', 9),
(32, 'BPN Kota Palembang', 'Instansi Pemerintah', 'Palembang', 'PABX & Billing System', 9),
(33, 'Hotel Swarna Dwipa', 'Perhotelan', 'Palembang', 'PABX & Billing System', 9),
(34, 'Hypermart Palembang', 'Retail & Mall', 'Palembang', 'PABX & Billing System', 9),
(35, 'Hotel Ranggonang Sekayu', 'Perhotelan', 'Sekayu', 'PABX & Billing System', 9),
(36, 'U-Finance / Mandala Finance / WOM Finance', 'Lembaga Keuangan', NULL, 'PABX & Billing System', 9),
(37, 'Balitek Unsri & Program Diploma Komputer Unsri', 'Perguruan Tinggi', 'Palembang / Indralaya', 'PABX & Billing System', 9),
(38, 'PT. BRI Cab. Baturaja, Prabumulih & Lahat', 'Perbankan BUMN', 'Baturaja, Prabumulih, Lahat', 'PABX & Billing System', 9),
(39, 'Kantor Pelayanan Pajak Pratama Baturaja & Prabumulih', 'Instansi Pemerintah', NULL, 'PABX & Billing System', 9),
(40, 'PT. Datascrip Cab. Palembang & ACSC PT. Acer Indonesia', 'Distributor IT', NULL, 'PABX & Billing System', 9),
(41, 'Pengadilan Tinggi Prov. Bangka Belitung & Pengadilan Agama Babel', 'Yudisial', 'Bangka Belitung', 'PABX & Billing System', 9),
(42, 'Dinas Perhubungan Prov. Sumsel', 'Pemerintah Daerah', 'Palembang', 'PABX & Billing System', 9),
(43, 'PT. SAP (Wilmar Group)', 'Agro Industri Wilmar', NULL, 'PABX & Billing System', 9),
(44, 'Bank SumselBabel', 'Perbankan Daerah', 'Sumsel & Babel', 'PABX & Billing System', 9),
(45, 'Laboratorium Klinik Pramita', 'Laboratorium Medis', NULL, 'PABX & Billing System', 9),
(46, 'Universitas Negeri Bangka Belitung', 'Perguruan Tinggi', 'Bangka Belitung', 'Networking & Tower', 10),
(47, 'Project Jardiknas Kabupaten/Kota se-Prov. Sumsel', 'Proyek Pendidikan Nasional', 'Sumatera Selatan', 'Networking & Tower', 10),
(48, 'Jaringan eGov Kab. OKI & Kab. Bangka Induk', 'Pemerintah Daerah', 'OKI & Bangka', 'Networking & Tower', 10),
(49, 'PT. Indosat Cab. Palembang', 'Telekomunikasi', 'Palembang', 'Networking & Tower', 10),
(50, 'Sampoerna Telkom Palembang', 'Telekomunikasi', 'Palembang', 'Networking & Tower', 10),
(51, 'Site BBIP Group', 'Perkebunan', 'Sumsel, Jambi, Riau & Sumut', 'Networking & Tower', 10),
(52, 'Site PT. JAPFA COMFEED Tbk', 'Agri-Food Industri', 'Lampung, Jambi & Padang', 'Networking & Tower', 10),
(53, 'PT. Cipta Tumbuh Berkembang (CTB)', 'Perkebunan', 'Kalimantan Barat', 'Networking & Tower', 10),
(54, 'PT. ADR Group', 'Agro Industri', 'Jambi & Sumsel', 'Networking & Tower', 10),
(55, 'PT. VALMET Indonesia', 'Manufaktur Multinasional', 'S. Baung OKI & Cikarang Bekasi', 'Networking & Tower', 10),
(56, 'Rumah Sakit Siloam Sriwijaya', 'Rumah Sakit Swasta', 'Palembang', 'CCTV & Security', 11),
(57, 'Rumah Sakit Bunda Lubuk Linggau', 'Rumah Sakit', 'Lubuk Linggau', 'CCTV & Security', 11),
(58, 'BNI Syari''ah Palembang', 'Perbankan', 'Palembang', 'CCTV & Security', 11),
(59, 'Pabrik Sawit BBIP Group', 'Pabrik Sawit', 'Sumsel', 'CCTV & Security', 11),
(60, 'Jaringan Minimarket Alfamart', 'Retail Berjaringan', 'Sumatera Selatan', 'CCTV & Security', 11),
(61, 'Pabrik Gula PT. Laju Perdana Indah (Indofood Group)', 'Industri Gula', NULL, 'CCTV & Security', 11),
(62, 'Universitas Sriwijaya (UNSRI)', 'Perguruan Tinggi Negeri', 'Palembang & Indralaya', 'CCTV & Security', 11),
(63, 'Bank Danamon Palembang', 'Perbankan Swasta', 'Palembang', 'Door Access Control', 11),
(64, 'PT. RIFAN Financindo Berjangka', 'Finansial', 'Palembang', 'Door Access Control', 11),
(65, 'Universitas Kader Bangsa', 'Perguruan Tinggi', 'Palembang', 'Door Access Control', 11),
(66, 'PLTG Talang Duku PT. PLN Wil IV Sumsel', 'PLN / Pembangkit Listrik', 'Talang Duku', 'Penangkal Petir', 12),
(67, 'PLTG Borang PT. PLN Wil IV Sumsel', 'PLN / Pembangkit Listrik', 'Borang', 'Penangkal Petir', 12),
(68, 'Site Camp / Stockpile PT. Tri Aryani', 'Tambang Batubara', 'Kab. MUBA', 'Penangkal Petir', 12),
(69, 'RSUD Kayu Agung', 'Rumah Sakit Daerah', 'Kayu Agung', 'Penangkal Petir', 12),
(70, 'PT. Pelangi Inti Pertiwi', 'Perkebunan', 'Langkat - Sumut', 'Penangkal Petir', 12),
(71, 'PT. PINAGO & PT. Manambang Muara Enim', 'Tambang & Perkebunan', 'Muara Enim', 'Penangkal Petir', 12),
(72, 'Bank Yudha Cab. Palembang & Bank Mandiri KCP Betung', 'Perbankan', 'Palembang & Betung', 'Fire & Intrusion Alarm', 12),
(73, 'RS AR-Rasyid & RS Muhammadiyah Palembang', 'Rumah Sakit', 'Palembang', 'Fire & Intrusion Alarm', 12),
(74, 'PT. Telkom Indonesia', 'BUMN Telekomunikasi', 'Palembang / Regional', 'Fire & Intrusion Alarm', 12),
(75, 'RSUD Siti Fatimah', 'Rumah Sakit Provinsi', 'Palembang', 'Fire & Intrusion Alarm', 12),
(76, 'Kanwil Kemenag Prov. Sumsel', 'Kementerian Agama', 'Palembang', 'Pengadaan Komputer & Sound', 13),
(77, 'Kantor Imigrasi Palembang', 'Kemenkumham / Imigrasi', 'Palembang', 'Quenetic System (Mesin Antri)', 13),
(78, 'Kantor Otoritas Jasa Keuangan (OJK)', 'Lembaga Negara', 'Jakarta', 'Quenetic System (Mesin Antri)', 13),
(79, 'Yayasan Pendidikan Islam Pagaralam', 'Pendidikan', 'Pagaralam', 'Perangkat Labor Bahasa', 13),
(80, 'PT. Kasih Agro Mandiri', 'Perkebunan Sawit', 'Banyuasin', 'ISP KGSNet & Managed Service', 14),
(81, 'PT. Wahana Lestari Makmur Sukses', 'Perkebunan', 'Musi Banyuasin', 'ISP KGSNet & Managed Service', 14),
(82, 'PT. Global Makara Teknik', 'Pertambangan', 'Sungai Lilin', 'ISP KGSNet & Managed Service', 14),
(83, 'PT. Trimata Benua', 'Pertambangan Batubara', 'Sungai Lilin', 'ISP KGSNet & Managed Service', 14),
(84, 'PT. Baramutiara Prima PORT & Tambang', 'Pelabuhan & Tambang', 'Sungai Lilin & Sri Gunung', 'ISP KGSNet & Managed Service', 14),
(85, 'PT. Global Alam Lestari', 'Kehutanan / HTI', 'Bayung Lencir', 'ISP KGSNet & Managed Service', 14),
(86, 'PT. IKI', 'Industri Perkayuan', 'Sembilang & Merang', 'ISP KGSNet & Managed Service', 14),
(87, 'Distrik Navigasi Type A Kelas I Palembang', 'Kementerian Perhubungan', 'Palembang', 'ISP KGSNet & Managed Service', 14),
(88, 'PT. Bara Pagmer Jaya', 'Tambang Batubara', 'Muara Enim & Lahat', 'ISP KGSNet & Managed Service', 14),
(89, 'KSOP & BKP Satker Wilker Tanjung Api-Api', 'Kepelabuhanan & Karantina', 'Tanjung Api-Api', 'ISP KGSNet & Managed Service', 14),
(90, 'PT. Swarnadwipa Dermaga Jaya', 'Dermaga Khusus Batubara', 'Muara Enim', 'ISP KGSNet & Managed Service', 14),
(91, 'PT. Trans Energy Indonesia', 'Energi & Logistik', 'Keramasan & Pulau Rimau', 'ISP KGSNet & Managed Service', 14),
(92, 'PT. Bumi Khatulistiwa Mandiri', 'Logistik Maritim', NULL, 'AIS (Automatic Identification System)', 15),
(93, 'PT. Daya Anugrah Utama', 'Transportasi Perairan', NULL, 'AIS (Automatic Identification System)', 15),
(94, 'PT. Hok Tong', 'Industri Karet / Pengapalan', NULL, 'AIS (Automatic Identification System)', 15),
(95, 'PT. Hijau Lestari Raya Fiberboard', 'Industri Fiberboard', NULL, 'AIS (Automatic Identification System)', 15),
(96, 'DISNAV (Distrik Navigasi)', 'Pemerintah / Navigasi Laut', 'Sumatera Selatan', 'AIS (Automatic Identification System)', 15);

-- --------------------------------------------------------
-- 11. Tabel Dokumentasi Proyek Nyata (projects)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `client_or_location` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(100) NOT NULL,
  `pdf_page` INT NOT NULL DEFAULT 18,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `projects` (`id`, `title`, `client_or_location`, `description`, `category`, `pdf_page`) VALUES
(1, 'Pabrikasi Guywire Tower Mandiri', 'Workshop Creative Com Palembang', 'Pabrikasi, perakitan modul dan pengelasan besi tiang guywire tower di workshop internal.', 'Fabrikasi Tower', 18),
(2, 'Grounding Installation & External Lightning Protect', 'Area Site Industri', 'Pengeboran elektroda pembumian dan instalasi head terminal proteksi petir eksternal.', 'Penangkal Petir', 18),
(3, 'Pengujian Nilai Tahanan Tanah Earth Resistance Tester', 'Area Pembumian Proyek', 'Pengujian nilai hambatan tanah dengan alat digital terkalibrasi untuk menjamin nilai tanah di bawah 2 Ohm.', 'Penangkal Petir', 18),
(4, 'Arrester Panel MDP', 'RSUD Kayu Agung', 'Pemasangan surge arrester pada Main Distribution Panel (MDP) rumah sakit.', 'Penangkal Petir', 18),
(5, 'Arrester CCTV Outdoor', 'PLTU Talang Duku PT. PLN', 'Pemasangan arrester video dan power pada tiang kamera CCTV kawasan pembangkit listrik.', 'CCTV & Security', 18),
(6, 'Instalasi Jaringan & Tower BBIP Group', 'Site STO Telkom Jambi & Mill', 'Ereksi menara transmisi data dari STO Telkom menuju pabrik kelapa sawit.', 'Networking & Tower', 18),
(7, 'Pemasangan CCTV Terintegrasi', 'PT. OKI Pulp & Paper', 'Pemasangan sistem kamera keamanan pengawas kawasan industri pulp and paper.', 'CCTV & Security', 19),
(8, 'Pemasangan Jaringan Intranet Link', 'ADR Group Jambi & Sumsel', 'Instalasi link komunikasi intranet point-to-point antar site kebun kelapa sawit.', 'Networking & Tower', 19),
(9, 'Pemasangan Modul Panel Solar Sel', 'PT. OKI Pulp & Paper', 'Pemasangan panel tenaga surya mandiri untuk suplai daya perangkat transmisi.', 'Solusi Terpadu', 19),
(10, 'Pemasangan Repeater HT MUBA Electric Power', 'Sungai Lalan, Musi Banyuasin', 'Instalasi stasiun pengulang radio komunikasi 2-arah untuk tim operasional lapangan.', 'Radio Komunikasi', 20),
(11, 'Pemasangan Penangkal Petir Terpadu', 'PT. PISS Langkat, Sumut', 'Pemasangan sistem penangkal petir elektrostatis radius luas dan instalasi konduktor.', 'Penangkal Petir', 20),
(12, 'Pemasangan Jaringan PT. JAPFA COMFEED Tbk', 'Site Jambi & Padang', 'Pemasangan infrastruktur kabel data jaringan komputer pabrik pakan ternak.', 'Networking & Tower', 20),
(13, 'Pemasangan Sistem AIS Monitoring Kapal', 'Tulung Selapan OKI / Sungai Musi', 'Instalasi AIS Receiver terintegrasi kamera Hikvision pada menara 28 meter.', 'AIS & Navigasi', 15);

-- --------------------------------------------------------
-- 12. Tabel Blog, Kategori & Artikel (blog_categories & posts)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Infrastruktur Jaringan & Fiber Optic', 'jaringan-fiber-optic', 'Artikel teknis mengenai perancangan jaringan komputer, serat optik, dan konektivitas nirkabel.'),
(2, 'Proteksi Petir & Kelistrikan', 'proteksi-petir', 'Wawasan standar instalasi penangkal petir, uji tahanan tanah, dan proteksi surge arrester.'),
(3, 'Telekomunikasi & Radio Komunikasi', 'telekomunikasi-radio', 'Panduan sistem PABX, IP-PBX, radio komunikasi HT/RIG/SSB di perkebunan dan tambang.'),
(4, 'Solusi Keamanan CCTV & Maritim', 'cctv-keamanan-maritim', 'Sistem pengawasan kamera keamanan resolusi tinggi dan pelacakan alur kapal AIS.');

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `summary` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `alt_image` VARCHAR(255) DEFAULT NULL,
  `labels` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(100) DEFAULT 'Tim Teknis PT. KGS',
  `is_featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `views` INT DEFAULT 0,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `posts` (`id`, `category_id`, `title`, `slug`, `summary`, `content`, `labels`, `is_featured`, `status`, `views`) VALUES
(1, 1, 'Pentingnya Backbone Fiber Optik untuk Kawasan Pabrik Kelapa Sawit & Pulp Paper', 'pentingnya-backbone-fiber-optik-industri', 'Bagaimana kabel serat optik dengan terminasi OTDR terstandar menjamin konektivitas data antar divisi di area pabrik tanpa gangguan interferensi elektromagnetik.', '<p>Di kawasan industri berskala besar seperti Pabrik Kelapa Sawit (PKS) dan pabrik bubur kertas (Pulp & Paper), jarak antar fasilitas seperti kantor timbang, pabrik pengolahan, workshop mekanik, dan kantor administrasi kerap mencapai ratusan meter bahkan beberapa kilometer.</p><p>Kabel tembaga konvensional memiliki batasan jarak efektif maksimum 100 meter dan sangat rentan terhadap gangguan interferensi elektromagnetik dari motor listrik tegangan tinggi dan generator pabrik. Penggunaan jaringan <strong>Fibre Optic Cable Networking</strong> dengan sertifikasi resmi seperti <em>NETVIEL InfiTe™</em> menjadi standar wajib.</p><h3>Keunggulan Fiber Optik di Area Industri</h3><ul><li>Bandwidth gigabit simetris tanpa degradasi sinyal jarak jauh.</li><li>Kebal terhadap induksi petir dan radiasi elektromagnetik mesin pabrik.</li><li>Uji redaman akurat menggunakan alat uji Optical Time-Domain Reflectometer (OTDR).</li></ul>', 'Fiber Optik, Infrastruktur, Netviel, Solusi B2B', 1, 'published', 142),
(2, 2, 'Standar Nilai Tahanan Pembumian (Grounding) di Bawah 2 Ohm untuk Fasilitas Vital', 'standar-nilai-tahanan-pembumian-grounding', 'Mengapa pengukuran dengan Earth Resistance Tester sangat penting dalam melindungi gedung, trafo listrik, dan perangkat CCTV dari lonjakan tegangan petir.', '<p>Sumatera Selatan dan sekitarnya dikenal memiliki hari guruh tahunan yang tinggi. Fasilitas vital seperti Pembangkit Listrik Tenaga Gas (PLTG), Rumah Sakit Daerah, dan menara telekomunikasi memerlukan sistem proteksi petir elektrostatis yang terintegrasi penuh.</p><h3>Uji Ketahanan Tanah dengan Earth Resistance Tester</h3><p>Penangkal petir di atas gedung tidak akan bekerja optimal tanpa pembumian (grounding) yang tepat. Standar teknis yang diterapkan teknisi PT. KGS mewajibkan pengujian resistansi tanah dengan <em>Earth Resistance Tester</em> hingga mencapai nilai tahanan di bawah 2 Ohm.</p><p>Selain grounding eksternal, perlindungan internal berupa surge arrester pada panel Main Distribution Panel (MDP) dan arrester CCTV wajib dipasang untuk memotong lonjakan arus induksi sebelum merusak motherboard perangkat.</p>', 'Penangkal Petir, Grounding, Arrester, Kelistrikan', 1, 'published', 215),
(3, 3, 'Implementasi Automatic Identification System (AIS) untuk Monitoring Alur Sungai & Pelabuhan', 'implementasi-ais-monitoring-alur-sungai', 'Integrasi perangkat AIS Receiver VHF dengan kamera pengawas resolusi tinggi untuk pelacakan kapal tongkang batubara dan kayu di perairan sungai Musi.', '<p>Lalu lintas kapal tongkang batubara, kapal penarik (tugboat), dan angkutan logistik di jalur sungai Sumatera Selatan membutuhkan sistem monitoring real-time untuk mencegah tabrakan dan memantau antrean dermaga.</p><p>Melalui sistem <strong>Automatic Identification System (AIS)</strong> yang dipadukan dengan antena menara elevasi tinggi dan kamera Hikvision, Distrik Navigasi dan pengelola pelabuhan dermaga dapat melacak koordinat GPS, nama kapal, kecepatan laju, dan status navigasi kapal secara otomatis.</p>', 'AIS, Navigasi Maritim, CCTV, Pelabuhan', 0, 'published', 98);

-- --------------------------------------------------------
-- 13. Tabel Permintaan Penawaran & Pesan Kontak (inquiries & lead_notes)
--     (Kosong secara default — tanpa data pelanggan fiktif)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `company` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `service_name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Baru', 'Diproses', 'Selesai') DEFAULT 'Baru',
  `admin_notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lead_notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `note` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 14. Tabel Media Terunggah (media)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT NOT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 15. Tabel Mitra Kerja Resmi (partners)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `partners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(150) NOT NULL,
  `service_type` VARCHAR(100) NOT NULL,
  `pdf_page` INT NOT NULL DEFAULT 16
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `partners` (`id`, `name`, `role`, `service_type`, `pdf_page`) VALUES
(1, 'PT. Telkom Indonesia', 'Lastmile support for ISP', 'Internet Service Provider', 16),
(2, 'B.NET', 'Guywire Tower support for ISP', 'Tower & Backbone', 16),
(3, 'Inti.Net', 'Guywire Tower support for ISP', 'Tower & Backbone', 16),
(4, 'Elnus.Net', 'Guywire Tower support for ISP', 'Tower & Backbone', 16),
(5, 'PT. LintasArta', 'Guywire Tower support for ISP', 'Tower & Backbone', 16),
(6, 'PT. IES Nusantara', 'Guywire Tower support for ISP', 'Tower & Backbone', 16);

-- --------------------------------------------------------
-- 16. Tabel Slider Gambar Halaman Beranda (hero_sliders)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hero_sliders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(180) NOT NULL,
  `subtitle` TEXT DEFAULT NULL,
  `badge_text` VARCHAR(100) DEFAULT 'Dokumentasi KGS',
  `image_url` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `hero_sliders` (`id`, `title`, `subtitle`, `badge_text`, `image_url`, `sort_order`, `is_active`) VALUES
(1, 'Backbone Fiber Optic & Data Center KGSNet', 'Koneksi Dedicated Internet B2B SLA 99.5% terhubung langsung ke BGP Peering Neucentrix.', 'ISP Resmi & Anggota APJII', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 1, 1),
(2, 'Pabrikasi & Instalasi Tower Guywire Mandiri', 'Workshop internal untuk pembangunan tower komunikasi radio & wireless PTP perkebunan dan tambang.', 'Infrastruktur Telekomunikasi', 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80', 2, 1),
(3, 'Sistem CCTV AI, Access Control & Proteksi Petir < 2 Ohm', 'Keamanan perimeter industri 24/7 dengan uji grounding bersertifikasi Earth Tester.', 'Security & Grounding System', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80', 3, 1);

INSERT IGNORE INTO `site_settings` (`group_name`, `key_name`, `key_value`) VALUES
('branding', 'theme_preset', 'corporate-blue'),
('branding', 'hero_bg_color', '#0b1329'),
('branding', 'navbar_style', 'light'),
('slider', 'hero_slider_layout', 'side'),
('slider', 'hero_slider_autoplay', '1'),
('slider', 'hero_slider_interval', '5'),
('slider', 'hero_slider_overlay', '75');

SET FOREIGN_KEY_CHECKS = 1;

