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
('identity', 'tagline', 'System Integrator, Security Solution & Internet Provider'),
('identity', 'business_focus', 'System Integrator, Security Solution & Internet Provider'),
('identity', 'company_profile', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.'),
('identity', 'established_year', '1997'),
('identity', 'pt_year', '2013'),
('identity', 'isp_year', '2020'),
('identity', 'isp_brand', 'KGSNet'),
('identity', 'vision', 'Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.'),
('identity', 'mission', 'Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.'),
('identity', 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.'),
('identity', 'commitment', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.'),
('contact', 'office_palembang_label', 'Alamat Kantor'),
('contact', 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113'),
('contact', 'city_province', 'Palembang, Sumatera Selatan'),
('contact', 'postal_code', '30113'),
('contact', 'phone_hunting', '0711-316088'),
('contact', 'phone_palembang', '0711-316088'),
('contact', 'whatsapp', '0811-782119'),
('contact', 'email', 'info@creative.co.id'),
('contact', 'email_general', 'info@creative.co.id'),
('contact', 'email_sales', 'sales@kgs.net.id'),
('contact', 'email_support', 'noc@kgs.net.id'),
('contact', 'operating_hours', 'Senin - Sabtu: 08:30 - 17:00 WIB'),
('contact', 'official_website', 'www.creative.co.id / www.kgs.net.id'),
('contact', 'google_map_input', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113'),
('contact', 'wa_default_msg', 'Halo PT. Kreatif Global Solusindo, saya tertarik untuk berkonsultasi mengenai solusi teknologi.'),
('branding', 'company_logo', ''),
('branding', 'company_name_color', '#0f172a'),
('branding', 'tagline_color', '#2563eb'),
('branding', 'logo_main', 'assets/images/logo-kgs.svg'),
('branding', 'logo_dark', 'assets/images/logo-kgs-white.svg'),
('branding', 'favicon', 'assets/images/favicon.svg'),
('branding', 'primary_color', '#1d4ed8'),
('branding', 'secondary_color', '#0f172a'),
('branding', 'font_family', 'Plus Jakarta Sans'),
('seo', 'meta_title_default', 'PT. Kreatif Global Solusindo - System Integrator, Security Solution & Internet Provider'),
('seo', 'meta_desc_default', 'Portal resmi PT. Kreatif Global Solusindo: System Integrator, Security Solution & Internet Provider sejak 1997 di Palembang.'),
('seo', 'canonical_url', 'https://www.kgs.net.id'),
('seo', 'robots_directive', 'index, follow'),
('footer', 'footer_description', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.'),
('footer', 'footer_contact_heading', 'Alamat Kantor & Kontak'),
('footer', 'footer_address_label', 'Alamat Kantor:'),
('footer', 'footer_nav_heading', 'Navigasi Halaman'),
('footer', 'footer_quick_heading', 'Informasi & Akses Cepat'),
('footer', 'footer_quick_1_label', 'Kebijakan Privasi & SLA'),
('footer', 'footer_quick_1_url', 'kebijakan-privasi.php'),
('footer', 'footer_quick_2_label', '3 Segmen Paket Internet ISP'),
('footer', 'footer_quick_2_url', 'harga.php'),
('footer', 'footer_quick_3_label', 'Pertanyaan Umum (FAQ)'),
('footer', 'footer_quick_3_url', 'faq.php'),
('footer', 'footer_quick_4_label', 'Minta Penawaran / Survei'),
('footer', 'footer_quick_4_url', 'kontak.php#konsultasi'),
('footer', 'footer_quick_5_label', 'Peta Lokasi Google Maps'),
('footer', 'footer_quick_5_url', 'kontak.php#peta-lokasi'),
('footer', 'footer_admin_link_label', 'Login Administrator CMS'),
('footer', 'footer_cert_heading', 'Lisensi, Sertifikasi & Keanggotaan'),
('footer', 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified'),
('footer', 'footer_cert_card_title', 'Sertifikasi & Keanggotaan Resmi'),
('footer', 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi'),
('footer', 'copyright_text', '© 2026 KGSNET — Creative.co.id. All Rights Reserved.'),
('contact', 'website_corporate', 'www.creative.co.id'),
('contact', 'website_isp', 'www.kgs.net.id'),
('contact', 'social_facebook', 'https://facebook.com/kgsnet.id'),
('contact', 'social_instagram', 'https://instagram.com/kgsnet.id'),
('contact', 'social_linkedin', 'https://linkedin.com/company/kreatif-global-solusindo'),
('contact', 'social_youtube', 'https://youtube.com/@kgsnet'),
('contact', 'social_tiktok', 'https://tiktok.com/@kgsnet.id'),
('contact', 'social_whatsapp', 'https://wa.me/628117879695'),
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
(1, 'Profile Perusahaan', 'profil-perusahaan', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo.', '<h2>Profile</h2><p>Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.</p>', 'Profile Perusahaan - PT. KGS', 'Profile resmi PT. Kreatif Global Solusindo dari tahun 1997 hingga kini.', 1, 1),
(2, 'Visi, Budaya & Komitmen', 'visi-misi-budaya', 'Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan PT. Kreatif Global Solusindo.', '<h2>VISI</h2><p>Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.</p><h2>BUDAYA</h2><p>Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.</p><h2>KOMITMEN</h2><p>Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.</p>', 'Visi, Budaya & Komitmen - PT. KGS', 'Visi, Budaya kerja fungsional, dan Komitmen kepuasan pelanggan PT. KGS.', 1, 2),
(3, 'Kebijakan Privasi Pengguna', 'kebijakan-privasi', 'Komitmen PT. Kreatif Global Solusindo dalam menjaga kerahasiaan dan keamanan data pelanggan, rekanan, dan calon klien.', '<h2>1. Pengumpulan Informasi</h2><p>PT. Kreatif Global Solusindo (KGS) menghormati dan melindungi privasi setiap pengunjung situs dan pelanggan kami. Informasi yang dikumpulkan melalui formulir konsultasi, permintaan penawaran harga, atau korespondensi langsung (seperti nama, nama perusahaan, nomor telepon, alamat email, dan lokasi proyek) hanya digunakan untuk kepentingan verifikasi teknis, penyusunan proposal solusi, dan komunikasi resmi perusahaan.</p><h2>2. Keamanan Data Pelanggan</h2><p>Kami menerapkan prosedur teknis dan manajemen yang ketat untuk mengamankan data Anda dari akses tanpa izin, perubahan, atau pengungkapan yang tidak sah. Seluruh data penawaran harga disimpan dalam server yang aman dengan enkripsi standar industri.</p><h2>3. Tidak Ada Pembagian Data kepada Pihak Ketiga</h2><p>Kami tidak akan menjual, menyewakan, atau membagikan informasi kontak pribadi atau data korporasi Anda kepada pihak ketiga manapun untuk tujuan pemasaran komersial tanpa persetujuan eksplisit dari Anda, kecuali jika diwajibkan oleh peraturan hukum yang berlaku di Republik Indonesia.</p><h2>4. Hubungi Kami</h2><p>Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: <strong>info@creative.co.id</strong> atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.</p>', 'Kebijakan Privasi - PT. KGS', 'Kebijakan privasi perlindungan data pengguna PT. Kreatif Global Solusindo.', 1, 3),
(4, 'System Integrator, Security Solution & Internet Provider', 'beranda', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.', '<p>PT. Kreatif Global Solusindo menyediakan solusi lengkap Product & Service: Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), serta layanan Internet Service Provider resmi (Internet Dedicated, Internet Bisnis, dan Home Internet).</p>', 'Beranda - System Integrator, Security Solution & Internet Provider', 'System Integrator, Security Solution & Internet Provider PT. Kreatif Global Solusindo sejak 1997.', 1, 4),
(5, 'Profile PT. Kreatif Global Solusindo', 'tentang-kami', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.', '<p>Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.</p>', 'Tentang Kami - Profile PT. KGS', 'Profile, Visi, Budaya, dan Komitmen PT. Kreatif Global Solusindo.', 1, 5),
(6, 'Product & Service', 'layanan', 'Katalog lengkap Product & Service: Security, Networking, PBX, Telecommunication (LIGHTNING PROTECTOR & Tower), serta Internet Service Provider (Internet Dedicated, Internet Bisnis & Home Internet).', '<p>Product & Service PT. Kreatif Global Solusindo mencakup Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), dan Internet Service Provider berizin resmi Kominfo RI.</p>', 'Product & Service - PT. KGS', 'Product & Service PT. Kreatif Global Solusindo: Security, Networking, PBX, Telecommunication, dan ISP.', 1, 6),
(7, 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya', 'harga', 'Daftar paket berlangganan 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, dan Home Internet) berizin resmi Kominfo RI, serta solusi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication) berbasis konsultasi teknis & survei lokasi.', '<p>Legalitas ISP: Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020, Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020, NIB (Nomor Induk Berusaha) ; 9120202100598.</p>', 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya - PT. KGS', 'Daftar paket 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet) serta solusi segmen Security, Networking, PBX, dan Telecommunication PT. Kreatif Global Solusindo.', 1, 7),
(8, 'Pertanyaan Umum & Jawaban Teknis', 'faq', 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.', '<p>Temukan jawaban teknis mengenai jangkauan layanan operasional Sumatera hingga nasional, lisensi resmi ISP KGSNet Kominfo RI & APJII, prosedur survei lapangan, serta garansi pengujian tahanan pembumian di bawah 2 Ohm.</p>', 'Frequently Asked Questions (FAQ) - PT. KGS', 'Pertanyaan umum & jawaban teknis seputar layanan ISP KGSNet, grounding, tower, dan tender B2B.', 1, 8),
(9, 'Portofolio Proyek & Klien Korporasi', 'portofolio', 'Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.', '<p>Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.</p><p>Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;</p>', 'Portofolio Proyek & Klien Korporasi - PT. KGS', 'Rekam jejak proyek lapangan dan daftar klien terverifikasi PT. Kreatif Global Solusindo.', 1, 9),
(10, 'Hubungi Tim PT. Kreatif Global Solusindo', 'kontak', 'Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.', '<p>Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, penjadwalan survei lokasi (on-site survey), serta pengajuan proposal penawaran harga resmi.</p>', 'Kontak & Permintaan Penawaran - PT. KGS', 'Hubungi Alamat Kantor PT. Kreatif Global Solusindo.', 1, 10),
(11, 'Artikel Solusi Teknologi & Praktik Terbaik Industri', 'blog', 'Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.', '<p>Pusat edukasi dan publikasi artikel teknis dari tim engineer PT. Kreatif Global Solusindo seputar infrastruktur jaringan serat optik, proteksi petir industri, telekomunikasi radio, dan navigasi maritim.</p>', 'Artikel & Wawasan Teknologi - PT. KGS', 'Kumpulan artikel edukasi teknologi informasi dan telekomunikasi industri PT. KGS.', 1, 11),
(12, 'Slide Show Infrastruktur & Solusi Teknologi', 'slideshow', 'Galeri interaktif dan penjelasan teknis 7 infrastruktur utama PT. Kreatif Global Solusindo (Creative.co.id): Self Supporting Tower Monopole, Fiber Optik, Guy Wire Tower, Lightning Protector, IP – PBX, Wireless, dan Access Door.', '<p>Jelajahi galeri multi-gambar dan spesifikasi teknis dari setiap kategori solusi infrastruktur telekomunikasi, jaringan, keamanan, dan menara yang dikerjakan oleh tim engineer PT. Kreatif Global Solusindo sejak 1997.</p>', 'Slide Show Infrastruktur & Solusi Teknologi - PT. KGS', 'Galeri Slide Show multi-gambar infrastruktur telekomunikasi, jaringan fiber optik, tower, penangkal petir, IP-PBX, wireless, dan access door PT. KGS.', 1, 12)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `summary` = VALUES(`summary`), `content` = VALUES(`content`);

-- --------------------------------------------------------
-- 5. Tabel Menu Navigasi (menus)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `page_slug` VARCHAR(120) DEFAULT NULL,
  `location` ENUM('header', 'footer') DEFAULT 'header',
  `menu_location` VARCHAR(30) DEFAULT 'header',
  `parent_id` INT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `target` ENUM('_self', '_blank') DEFAULT '_self',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `menus`;
INSERT INTO `menus` (`id`, `title`, `url`, `page_slug`, `location`, `menu_location`, `parent_id`, `sort_order`, `target`, `is_active`) VALUES
(1, 'Beranda', 'index.php', 'beranda', 'header', 'header', NULL, 1, '_self', 1),
(2, 'Produk & Layanan', 'layanan.php', 'layanan', 'header', 'header', NULL, 2, '_self', 1),
(3, 'Promo', 'promo.php', 'promo', 'header', 'header', NULL, 3, '_self', 1),
(4, 'Client / Pelanggan', 'klien.php', 'klien', 'header', 'header', NULL, 4, '_self', 1),
(5, 'Kemitraan', 'kemitraan.php', 'kemitraan', 'header', 'header', NULL, 5, '_self', 1),
(6, 'Testimoni', 'testimoni.php', 'testimoni', 'header', 'header', NULL, 6, '_self', 1),
(7, 'Hubungi Kami', 'kontak.php', 'kontak', 'header', 'header', NULL, 7, '_self', 1),
(8, 'Profil Perusahaan', 'page.php?slug=profil-perusahaan', 'profil-perusahaan', 'footer', 'footer', NULL, 1, '_self', 1),
(9, 'Visi & Misi', 'page.php?slug=visi-misi-budaya', 'visi-misi-budaya', 'footer', 'footer', NULL, 2, '_self', 1),
(10, 'Product & Service', 'layanan.php', 'layanan', 'footer', 'footer', NULL, 3, '_self', 1),
(11, 'Artikel Edukasi', 'blog.php', 'blog', 'footer', 'footer', NULL, 4, '_self', 1),
(12, 'Kebijakan Privasi', 'kebijakan-privasi.php', 'kebijakan-privasi', 'footer', 'footer', NULL, 5, '_self', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `url` = VALUES(`url`), `page_slug` = VALUES(`page_slug`), `location` = VALUES(`location`), `menu_location` = VALUES(`menu_location`), `sort_order` = VALUES(`sort_order`), `is_active` = VALUES(`is_active`);

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
(1, 'security', 1, 'Security', 'FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone', 4),
(2, 'networking', 2, 'Networking', 'Router, Wireless', 4),
(3, 'pbx', 3, 'PBX', 'ANALOGUE PBX, HYBRID PBX, IP – PBX', 4),
(4, 'telecom', 4, 'Telecommunication', 'LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR | Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE', 4),
(5, 'isp', 5, 'Internet Service Provider', 'Internet Dedicated, Internet Bisnis, dan Home Internet berizin resmi Kominfo RI & APJII.', 4)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- --------------------------------------------------------
-- 7. Tabel Master Layanan (services - Product & Service KGS)
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
(1, 1, 'FIRE & ALARM SECURITY', 'fire-alarm-security', 'Sistem deteksi dini bahaya kebakaran (Fire Alarm) dan alarm keamanan intrusi untuk gedung, pabrik, dan perbankan.', 'Pemasangan Master Control Fire Alarm (MCFA), detektor asap (smoke detector), detektor panas (heat detector), manual call point, serta sirine evakuasi dipadukan dengan sensor gerak dan door contact alarm keamanan.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 1),
(2, 1, 'CCTV', 'cctv-surveillance-system', 'Sistem pengawasan kamera keamanan resolusi tinggi Analog maupun IP Camera berbasis jaringan.', 'Perancangan, instalasi kabel coaxial / UTP / Fiber Optic, konfigurasi NVR/DVR, serta integrasi pemantauan kamera pengawas CCTV multi-site.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 2),
(3, 1, 'Access Door', 'access-door-control', 'Sistem pengaman akses pintu elektronik berbasis kartu RFID, PIN, dan biometrik untuk ruang penting.', 'Pemasangan sistem pembatas dan pengaman akses pintu masuk (Access Door) menggunakan magnetic lock / drop bolt dengan autentikasi kartu RFID, PIN, atau biometrik.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 3),
(4, 1, 'Video Door Phone', 'video-door-phone', 'Sistem interkom video pintu masuk terintegrasi untuk verifikasi tamu secara visual dan audio.', 'Instalasi perangkat Video Door Phone dengan layar monitor indoor dan kamera outdoor berfitur night vision yang terhubung langsung ke sistem pengunci pintu elektronik (Access Door).', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 4),
(5, 2, 'Router', 'networking-router', 'Konfigurasi dan pengadaan Router enterprise untuk manajemen bandwidth, firewall, VPN, dan routing jaringan.', 'Layanan pengadaan, instalasi, dan manajemen Router jaringan (MikroTik, Cisco, Ubiquiti) untuk pengaturan trafik data, load balancing multi-ISP, manajemen QoS/bandwidth, serta keamanan jaringan perusahaan.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 5),
(6, 2, 'Wireless', 'networking-wireless', 'Instalasi jaringan Wireless LAN (Wi-Fi Enterprise) dan radio Wireless Point-to-Point / Point-to-Multipoint.', 'Perancangan dan pemasangan infrastruktur jaringan nirkabel (Wireless) baik untuk cakupan Wi-Fi indoor/outdoor area perkantoran maupun link transmisi Wireless jarak jauh antar gedung dan remote site.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 6),
(7, 3, 'ANALOGUE PBX', 'analogue-pbx', 'Sistem sentral telepon Analogue PBX yang handal untuk komunikasi internal dan eksternal kantor.', 'Pemasangan dan pemrograman perangkat Analogue PBX untuk kebutuhan distribusi saluran telepon (CO Line) dan ekstensi analog di perkantoran, ruko, dan instansi.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 7),
(8, 3, 'HYBRID PBX', 'hybrid-pbx', 'Sistem sentral telepon Hybrid PBX yang memadukan fleksibilitas saluran analog, digital, dan IP.', 'Solusi komunikasi suara Hybrid PBX yang memungkinkan perusahaan menggabungkan pesawat telepon analog, digital keyphone, dan ekstensi IP secara bertahap.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 8),
(9, 3, 'IP – PBX', 'ip-pbx-system', 'Sistem komunikasi suara modern berbasis protokol IP (IP – PBX) untuk interkoneksi multi-cabang.', 'Implementasi penuh sistem IP – PBX dan SIP Trunking untuk komunikasi suara terpadu melalui jaringan LAN/WAN/Internet.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 9),
(10, 4, 'Handy Talky', 'handy-talky-radio', 'Perangkat komunikasi radio dua arah (Handy Talky, RIG, SSB & Repeater) untuk koordinasi operasional lapangan.', 'Penyediaan dan instalasi perangkat radio komunikasi Handy Talky (HT), stasiun pangkalan (RIG), radio SSB, dan repeater penguat sinyal.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 10),
(11, 4, 'CONFERENCE SYSTEM', 'conference-system', 'Sistem konferensi audio dan video interaktif untuk ruang rapat direksi, aula sidang, dan ruang komando.', 'Instalasi perangkat CONFERENCE SYSTEM (Audio & Video Conference) dengan mikrofon meja Chairman/Delegate, kamera PTZ tracking, dan tata suara jernih.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 11),
(12, 4, 'LIGHTNING PROTECTOR', 'lightning-protector', 'Sistem proteksi penangkal petir eksternal & internal dengan standar tahanan pembumian (grounding) < 2 Ohm.', 'Perancangan dan pemasangan LIGHTNING PROTECTOR (Sistem Penangkal Petir) elektrostatis maupun konvensional beserta sistem pembumian (grounding) tembaga teruji Digital Earth Resistance Tester < 2 Ohm.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 12),
(13, 4, 'Guy Wire Tower', 'guy-wire-tower', 'Fabrikasi mandiri dan ereksi menara telekomunikasi jenis Guy Wire Tower dengan tarikan seling baja.', 'Layanan pabrikasi besi rangka Guy Wire Tower di workshop mandiri PT. KGS Palembang serta instalasi ereksi vertikal lengkap dengan kawat seling baja (guyed wire) dan grounding.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 13),
(14, 4, 'SELF SUPPORTING TOWER MONOPOLE', 'self-supporting-tower-monopole', 'Konstruksi dan pemasangan Self Supporting Tower (SST) dan Monopole kokoh untuk beban antena tinggi.', 'Perancangan fondasi sipil, pabrikasi struktur baja, dan ereksi SELF SUPPORTING TOWER (SST 3/4 kaki) serta menara MONOPOLE mandiri tanpa kawat seling.', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 14),
(15, 5, 'Internet Dedicated (UMKM, Sekolah & Corporate)', 'internet-dedicated', 'Layanan Internet Dedicated 10 Mbps: mulai Rp 3.500.000/bulan (UMKM), Rp 1.750.000/bulan (Sekolah & Corporate).', 'Layanan Internet Dedicated berizin resmi Kominfo RI (Nomor 674/TEL.02.02/2020 & SKLO Nomor 375/TEL.04.02/2020, NIB 9120202100598). Tersedia untuk 3 kebutuhan: 10 Mbps Harga Perbulan mulai 3.500.000 (Kebutuhan UMKM), 10 Mbps Harga Perbulan mulai 1.750.000 (Kebutuhan Sekolah), dan 10 Mbps Harga Perbulan mulai 1.750.000 (Kebutuhan Corporate).', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 15),
(16, 5, 'Internet Bisnis (10 Mbps s/d 200 Mbps)', 'internet-bisnis', 'Paket Internet Bisnis kecepatan 10 Mbps hingga 200 Mbps mulai dari Rp. 407.000 / bulan.', 'Pilihan paket Internet Bisnis berkecepatan tinggi: 10 Mbps (Rp. 407.000/bln), 20 Mbps (Rp. 431.000/bln), 30 Mbps (Rp. 453.000/bln), 40 Mbps (Rp. 477.000/bln), 50 Mbps (Rp. 511.000/bln), 100 Mbps (Rp. 1.050.000/bln), dan 200 Mbps (Rp. 1.560.000/bln).', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 16),
(17, 5, 'Home Internet (10 Mbps s/d 200 Mbps)', 'home-internet', 'Paket Home Internet 10 Mbps s/d 200 Mbps mulai Rp. 252.000 / bulan (Optimal 5 hingga 20 Perangkat).', 'Layanan Home Internet nyaman dan stabil untuk keluarga: 10 Mbps Rp. 252.000/bln (Optiman di 5 Perangkat), 20 Mbps Rp. 276.000/bln (Optiman di 7 Perangkat), 30 Mbps Rp. 298.000/bln (Optiman di 9 Perangkat), 50 Mbps Rp. 450.000/bln (Optiman di 12 Perangkat), 100 Mbps Rp. 710.000/bln (Optiman di 15 Perangkat), dan 200 Mbps Rp. 1.100.000/bln (Optiman di 20 Perangkat).', 4, 'Standar Resmi KGS', 'Standar Layanan Resmi PT. KGS', 1, 17)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `summary` = VALUES(`summary`), `description` = VALUES(`description`);

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
(1, 'DEDICATED', 'Internet Dedicated', 'Kebutuhan UMKM, Sekolah & Corporate', 'Koneksi Internet Dedicated simetris berizin resmi Kominfo RI & APJII untuk UMKM, Sekolah, dan Corporate.', 'Mulai Rp. 1.750.000 / bulan', 'Bandwidth Dedicated 1:1 & SLA 99.5%', '10 Mbps Harga Perbulan mulai 3.500.000 Kebutuhan UMKM\n10 Mbps Harga Perbulan mulai 1.750.000 Kebutuhan Sekolah\n10 Mbps Harga Perbulan mulai 1.750.000 Kebutuhan Corporate\nIzin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020\nSurat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020\nNIB (Nomor Induk Berusaha) ; 9120202100598', 'Segmen 1 • Dedicated', 1, 1, 1),
(2, 'BISNIS', 'Internet Bisnis', 'Segmen Internet Bisnis (10 Mbps – 200 Mbps)', 'Pilihan paket kecepatan 10 Mbps hingga 200 Mbps untuk menunjang produktivitas bisnis dan kantor.', 'Mulai Rp. 407.000 / bulan', 'Koneksi Bisnis Stabil & Responsif', '10 Mbps Rp. 407.000 / bulan\n20 Mbps Rp. 431.000 / bulan\n30 Mbps Rp. 453.000 / bulan\n40 Mbps Rp. 477.000 / bulan\n50 Mbps Rp. 511.000 / bulan\n100 Mbps Rp. 1.050.000 / bulan\n200 Mbps Rp. 1.560.000 / bulan', 'Segmen 2 • Internet Bisnis', 0, 1, 2),
(3, 'HOME', 'Home Internet', 'Segmen Home Internet (5 – 20 Perangkat)', 'Koneksi internet rumah tangga cepat dan stabil mulai dari 10 Mbps hingga 200 Mbps.', 'Mulai Rp. 252.000 / bulan', 'Optimal 5 hingga 20 Perangkat', '10 Mbps Rp. 252.000 / bulan Optiman di 5 Perangkat\n20 Mbps Rp. 276.000 / bulan Optiman di 7 Perangkat\n30 Mbps Rp. 298.000 / bulan Optiman di 9 Perangkat\n50 Mbps Rp. 450.000 / bulan Optiman di 12 Perangkat\n100 Mbps Rp. 710.000 / bulan Optiman di 15 Perangkat\n200 Mbps Rp. 1.100.000 / bulan Optiman di 20 Perangkat', 'Segmen 3 • Home Internet', 0, 1, 3);

-- --------------------------------------------------------
-- 9. Tabel Tanya Jawab / FAQ (faqs)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Umum',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `faqs` (`id`, `category`, `question`, `answer`, `sort_order`, `is_active`) VALUES
(1, 'Umum', 'Bagaimana sejarah berdirinya PT. Kreatif Global Solusindo (Creative Com)?', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.', 1, 1),
(2, 'Umum', 'Di mana saja jangkauan wilayah pengerjaan proyek PT. KGS?', 'Sejak 1997, ruang layanan tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga ke provinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.', 2, 1),
(3, 'Internet ISP', 'Apa saja legalitas resmi layanan Internet Service Provider PT. KGS?', 'Layanan Internet Service Provider PT. Kreatif Global Solusindo dilengkapi legalitas resmi: Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020, Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020, serta NIB (Nomor Induk Berusaha) ; 9120202100598.', 3, 1),
(4, 'Internet ISP', 'Apa saja 3 segmen paket Internet dan harganya di PT. KGS?', 'Terdapat 3 Segmen layanan internet: (1) Internet Dedicated 10 Mbps mulai Rp 3.500.000/bulan untuk UMKM, Rp 1.750.000/bulan untuk Sekolah, dan Rp 1.750.000/bulan untuk Corporate; (2) Internet Bisnis mulai 10 Mbps (Rp 407.000/bulan) hingga 200 Mbps (Rp 1.560.000/bulan); serta (3) Home Internet mulai 10 Mbps (Rp 252.000/bulan Optiman di 5 Perangkat) hingga 200 Mbps (Rp 1.100.000/bulan Optiman di 20 Perangkat).', 4, 1),
(5, 'Keamanan & Tower', 'Apa saja Product & Service yang disediakan oleh PT. KGS?', 'Product & Service kami mencakup: Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), serta Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet).', 5, 1),
(6, 'Keamanan & Tower', 'Berapa standar nilai tahanan pembumian (grounding) pada instalasi Penangkal Petir?', 'Dalam pelaksanaan instalasi Lightning Protection System (Penangkal Petir), tim teknisi kami melakukan pengujian menggunakan alat ukur digital (Earth Resistance Tester) untuk memastikan nilai resistansi grounding mencapai standar aman di bawah 2 Ohm.', 6, 1),
(7, 'Kontrak & Legalitas', 'Bagaimana prosedur pengajuan survei lokasi dan penawaran harga (Quotation)?', 'Calon klien dapat menghubungi kantor kami di Palembang (0711-316088 / WhatsApp 0811-782119) atau mengisi formulir Permintaan Penawaran di website ini. Tim teknis kami akan menjadwalkan survei lapangan guna menghitung Bill of Quantity (BoQ) dan spesifikasi perangkat yang paling tepat dan efisien.', 7, 1),
(8, 'Kontrak & Legalitas', 'Bagaimana komitmen pelayanan purnajual PT. KGS kepada pelanggan?', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.', 8, 1);

-- --------------------------------------------------------
-- 10. Tabel Daftar Klien Terverifikasi (clients)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `service_category` VARCHAR(100) NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `website_url` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
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
(1, 'Fiber Optik', 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari...', 'Slide Show Infrastruktur · 6 Gambar', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 1, 1),
(2, 'Guy Wire Tower', 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah...', 'Slide Show Infrastruktur · 4 Gambar', 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80', 2, 1),
(3, 'SELF SUPPORTING TOWER MONOPOLE', 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara...', 'Slide Show Infrastruktur · 4 Gambar', 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80', 3, 1),
(4, 'LIGHTNING PROTECTOR', 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada...', 'Slide Show Infrastruktur · 5 Gambar', 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80', 4, 1),
(5, 'IP – PBX', 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis...', 'Slide Show Infrastruktur · 5 Gambar', 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80', 5, 1),
(6, 'Wireless', 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah  hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik...', 'Slide Show Infrastruktur · 5 Gambar', 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80', 6, 1),
(7, 'Access Door', 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID...', 'Slide Show Infrastruktur · 5 Gambar', 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80', 7, 1),
(8, 'CCTV', 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal...', 'Slide Show Infrastruktur · 5 Gambar', 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80', 8, 1);

INSERT IGNORE INTO `site_settings` (`group_name`, `key_name`, `key_value`) VALUES
('branding', 'theme_preset', 'corporate-blue'),
('branding', 'hero_bg_color', '#0b1329'),
('branding', 'navbar_style', 'light'),
('branding', 'logo_bg_color', '#0f172a'),
('branding', 'logo_text_color', '#ffffff'),
('branding', 'company_name_color', '#0f172a'),
('branding', 'tagline_color', '#2563eb'),
('slider', 'hero_slider_enabled', '1'),
('slider', 'hero_slider_layout', 'side'),
('slider', 'hero_slider_autoplay', '1'),
('slider', 'hero_slider_interval', '5'),
('slider', 'hero_slider_overlay', '75');

-- --------------------------------------------------------
-- 17. Tabel Timeline Sejarah Perusahaan (company_timelines)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `company_timelines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year_label` VARCHAR(30) NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `description` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_highlighted` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `company_timelines` (`id`, `year_label`, `title`, `description`, `sort_order`, `is_highlighted`) VALUES
(1, '1997', 'Pendirian CV. CREATIVE (Creative Com)', 'Berdiri di Palembang dengan fokus layanan perbaikan perangkat komputer dan instalasi jaringan lokal.', 1, 0),
(2, '1998', 'Distributor Perangkat Wartel QPHONE', 'Merambah bidang penjualan dan distribusi perangkat telekomunikasi wartel merk QPHONE.', 2, 0),
(3, '2002', 'Authorized Dealer PABX Panasonic', 'Panasonic mempercayakan Creative Com sebagai salah satu dealer resmi sistem PABX di Palembang.', 3, 0),
(4, '2004', 'Jaringan Komputer, Radio, PABX & Tower', 'Melayani instalasi jaringan kabel/wireless, radio komunikasi, PABX, serta fabrikasi & ereksi tower antena.', 4, 0),
(5, '2013', 'Transformasi PT. KREATIF GLOBAL SOLUSINDO', 'Peningkatan badan hukum menjadi Perseroan Terbatas untuk melayani tender korporasi dan BUMN.', 5, 1),
(6, '2020', 'Lisensi Resmi ISP KGSNet & Anggota APJII', 'Resmi mengantongi izin Penyelenggara Jasa Internet (ISP) dengan produk KGSNet dan tergabung di APJII.', 6, 1);

-- --------------------------------------------------------
-- 18. Tabel Sertifikasi & Lisensi Resmi (company_certifications)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `company_certifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `badge_code` VARCHAR(50) NOT NULL DEFAULT 'CERT',
  `title` VARCHAR(180) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(180) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `link_url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `company_certifications` (`id`, `badge_code`, `title`, `subtitle`, `description`, `logo_url`, `link_url`, `sort_order`) VALUES
(1, 'SONY', 'Certificate of Appointment SONY System Integrator', 'Lisensi Resmi Surveillance', 'Penunjukan resmi sebagai System Integrator untuk produk Video Security & IP Camera Sony.', '', '', 1),
(2, 'PANA', 'Authorized Dealer PABX Panasonic', 'Kemitraan Sejak 2002', 'Kemitraan resmi instalasi, pemrograman, dan purnajual sistem telekomunikasi PABX Panasonic sejak 2002.', '', '', 2),
(3, 'FO', 'NETVIEL InfiTe™ Fiber Optic Connectivity', 'Sertifikasi Infrastruktur Serat Optik', 'Sertifikasi lisensi instalasi dan terminasi jaringan serat optik (Fiber Optic) berstandar industri.', '', '', 3),
(4, 'MTC', 'MikroTik Certified Routing Engineer (MTCRE)', 'Sertifikasi Engineer Jaringan ISP', 'Tenaga ahli tersertifikasi internasional untuk manajemen routing, bandwidth, dan keamanan jaringan ISP.', '', '', 4);

-- --------------------------------------------------------
-- 19. Tabel Mitra Kerja & Brand (company_partners)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `company_partners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_name` VARCHAR(150) NOT NULL DEFAULT '',
  `role_text` VARCHAR(200) NOT NULL DEFAULT '',
  `description` TEXT DEFAULT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `link_url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `company_partners` (`id`, `brand_name`, `role_text`, `description`, `logo_url`, `link_url`, `sort_order`) VALUES
(1, 'PT. Telkom Indonesia', 'Lastmile Support for ISP', 'Dukungan jaringan backbone dan lastmile telekomunikasi nasional.', '', '', 1),
(2, 'B.NET', 'Guywire Tower Support for ISP', 'Kemitraan infrastruktur menara telekomunikasi dan link wireless ISP.', '', '', 2),
(3, 'Inti.Net', 'Guywire Tower Support for ISP', 'Dukungan jaringan menara guywire dan transmisi data regional.', '', '', 3),
(4, 'Elnus.Net', 'Guywire Tower Support for ISP', 'Kemitraan strategis infrastruktur menara dan jaringan komunikasi.', '', '', 4),
(5, 'PT. LintasArta', 'Guywire Tower Support for ISP', 'Kemitraan jaringan komunikasi data korporasi dan infrastruktur tower.', '', '', 5),
(6, 'PT. IES Nusantara', 'Guywire Tower Support for ISP', 'Dukungan teknis ereksi menara dan konektivitas jaringan ISP.', '', '', 6);

-- --------------------------------------------------------
-- 20. Tabel Ringkasan Sektor Industri Klien (client_sectors)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `client_sectors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `badge_code` VARCHAR(30) NOT NULL DEFAULT 'BUMN',
  `badge_label` VARCHAR(30) DEFAULT 'BUMN',
  `badge_color` VARCHAR(30) DEFAULT 'blue',
  `name` VARCHAR(150) NOT NULL DEFAULT '',
  `sector_name` VARCHAR(150) DEFAULT NULL,
  `sector_title` VARCHAR(150) DEFAULT NULL,
  `description` TEXT NOT NULL,
  `summary` TEXT DEFAULT NULL,
  `highlight_clients` TEXT DEFAULT NULL,
  `client_list` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `client_sectors` (`id`, `badge_code`, `badge_label`, `badge_color`, `name`, `sector_name`, `sector_title`, `description`, `summary`, `highlight_clients`, `client_list`, `sort_order`, `is_active`) VALUES
(1, 'BUMN', 'BUMN', 'blue', 'BUMN, Energi & Kepelabuhanan', 'BUMN, Energi & Kepelabuhanan', 'BUMN, Energi & Kepelabuhanan', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 'Pelindo, Semen Tonasa, Pupuk Sriwidjaja (Pusri), PLN / PJB, Pertamina, dan Telkom Indonesia.', 1, 1),
(2, 'AGRO', 'AGRO', 'emerald', 'Perkebunan Sawit, Pulp & Tambang', 'Perkebunan Sawit, Pulp & Tambang', 'Perkebunan Sawit, Pulp & Tambang', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 'OKI Pulp & Paper, Sampoerna Agro, London Sumatera, Wilmar Group, Indofood, dan pertambangan batubara.', 2, 1),
(3, 'GOV', 'GOV', 'amber', 'Pemerintahan, Perbankan & Medis', 'Pemerintahan, Perbankan & Medis', 'Pemerintahan, Perbankan & Medis', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 'Bank SumselBabel, BRI, BPK, BPN, Imigrasi, OJK, Pengadilan Tinggi, RS M. Husin, dan RS Siti Khadijah.', 3, 1);

-- --------------------------------------------------------
-- --------------------------------------------------------
-- 21. Tabel Kategori Slide Show Infrastruktur (slideshow_categories)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `slideshow_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `short_description` TEXT DEFAULT NULL,
  `full_description` LONGTEXT NOT NULL,
  `cover_image` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `slideshow_categories` (`id`, `title`, `slug`, `short_description`, `full_description`, `cover_image`, `sort_order`, `is_active`) VALUES
(1, 'Fiber Optik', 'fiber-optik', 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari...', 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari sehelai rambut, dan dapat digunakan untuk mentransmisikan sinyal cahaya dari suatu tempat ke tempat lain. Sumber cahaya yang digunakan biasanya adalah dari sinar laser atau LED.\n\nKabel ini berdiameter lebih kurang 120 mikrometer. Cahaya yang ada di dalam serat optik tidak keluar karena indeks bias dari kaca lebih besar daripada indeks bias dari udara, karena laser mempunyai spektrum yang sangat sempit. Kecepatan transmisi fiber optik sangat tinggi sehingga sangat bagus digunakan sebagai saluran komunikasi.\n\nPerkembangan teknologi fiber optik saat ini, telah dapat menghasilkan pelemahan (attenuation) kurang dari 20 decibels (dB)/km. Dengan lebar jalur (bandwidth) yang besar, maka mampu dalam mentransmisikan data menjadi lebih banyak dan cepat dibandingan dengan penggunaan kabel konvensional. Dengan demikian fiber optik sangat cocok digunakan terutama dalam aplikasi sistem telekomunikasi.\n\nSekitar 20 tahun yang lalu, kabel fiber optik telah memngambil alih dan mengubah wajah teknologi industri telepon jarak jauh maupun industri automasi dengan pengontrolan jarak jauh. Serat optik juga memberikan peranan besar membuat Internet dapat digunakan di seluruh dunia. Pada tahun 1997 fiber optik menghubungkan seluruh dunia, Fiber-Optic Link Around the Globe (FLAG) menjadi jaringan kabel terpanjang di seluruh dunia yang menyediakan infrastruktur untuk generasi internet terbaru.\n\n### Kelebihan Fiber Optik\n- Bandwidth sangat besar dengan kecepatan transmisi mencapai gigabit-per detik dan menghantarkan informasi jarak jauh tanpa pengulangan\n- Biaya pemasangan dan pengoperasian yang rendah serta tingkat keamanan yang lebih tinggi\n- Ukuran kecil dan ringan, sehingga hemat pemakaian ruang\n- Kebal terhadap gangguan elektromagnetik dan gangguan gelombang radio\n- Tidak ada tenaga listrik dan percikan api\n- Tidak berkarat', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 1, 1),
(2, 'Guy Wire Tower', 'guy-wire-tower', 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah...', 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah (Guywire), dimana skur ini merupakan tumpuan utama agar tower tersebut dapat berdiri.\n\nMenara ini juga disusun atas pola batang besi sebagaimana dengan self-supporting tower, akan tetapi menara jenis guyed wire tower memiliki jenis dimensi batang yang lebih kecil dari pada jenis self-supporting tower.\n\nNamun demikian jenis guyed wire tower tidak kalah kuat dari segi kekuatan menopang beban, jenis menara ini mampu menerima beban antena dan perangkat lainnya yang terpasang pada tower.\n\nKarena sifatnya yang ditopang oleh kawat baja (wire) dan memiliki dimensi batang yang lebih kecil, menara jenis ini memiliki keuntungan antara lain dari segi biaya dan ruang, menara ini jauh lebih hemat biaya pembuatan dan membutuhkan ruang yang tidak terlalu luas dibanding dengan jenis self-supporting tower.', 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80', 2, 1),
(3, 'SELF SUPPORTING TOWER MONOPOLE', 'self-supporting-tower-monopole', 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara...', 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara menolak berbagai kondisi lingkungan. Monopole menara tidak memerlukan bracing dari kabel tegangan tinggi atau bingkai logam, yang menawarkan banyak manfaat dan keuntungan.', 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80', 3, 1),
(4, 'LIGHTNING PROTECTOR', 'lightning-protector', 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada...', 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada 3 bagian utama pada penyalur petir:\n\n### 1. Batang penyalur petir\nBatang penyalur petir berupa batang tembaga yang ujungnya runcing. Buat batang tembaga yang runcing ini karena muatan listrik mempunyai sifat mudah berkumpul dan lepas pada ujung logam yang runcing. Dengan demikian dapat memperlancar proses tarik menarik dengan muatan listrik yang ada di awan. Batang runcing ini dipasang pada bagian puncak suatu bangunan/gedung.\n\n### 2. Kabel konduktor\nKabel konduktorv terbuat dari jalinan kawat tembaga. Diameter jalinan kabel konduktor sekitar 1 cm hingga 2 cm . Kabel konduktor berfungsi meneruskan aliran muatan listrik dari batang muatan listrik ke tanah. Kabel konduktor tersebut dipasang pada dinding di bagian luar bangunan/gedung.\n\n### 3. Tempat pembumian\nTempat pembumian (grounding) berfungsi mengalirkan muatan listrik dari kabel konduktor ke batang pembumian (ground rod) yang tertanam di tanah. Batang pembumian terbuat dari bahan tembaga berlapis baja, dengan diameter 1,5 cm dan panjang sekitar 1,8 – 3 m .', 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=80', 4, 1),
(5, 'IP – PBX', 'ip-pbx', 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis...', 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX merupakan perangkat switching komunikasi telepon dan data berbasis teknologi Internet Protocol (IP) yang mengendalikan ekstension telepon analog (TDM) maupun ekstension IP Phone.\n\nFungsi-fungsi yang dapat dilakukan antara lain penyambungan, pengendalian, dan pemutusan hubungan telepon; translasi protokol komunikasi; serta pengaturan jalur dan media komunikasi (suara, data, dan video).\n\nApabila di lingkungan anda telah terdapat perangkat router, maka perangkat IP PBX dapat berbagi jaringan dengan jaringan komputer yang sudah ada. Artinya tidak diperlukan jaringan khusus untuk mentransmisikan sinyal suara maupun video, cukup menumpangkannya pada jaringan data yang ada.\n\nSebuah IP PBX dapat mengubah panggilan telepon melalui jaringan data dengan menggunakan teknologi VoIP (Voice over Internet Protocol), dan mengkonversi panggilan VoIP ke jaringan telepon lokal (PSTN). Dengan demikian pengguna dalam satu perusahaan dapat berkomunikasi secara internal melalui jaringan IP dan juga tetap terhubung dengan jaringan telepon publik eksternal, baik melalui saluran analog konvensional maupun ISDN.\n\nDengan hadirnya IP PBX, maka jaringan data dan jaringan suara dapat digabungkan menjadi satu infrastruktur terpadu yang lebih efisien dan fleksibel untuk pengembangan di masa depan.', 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80', 5, 1),
(6, 'Wireless', 'wireless', 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah  hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik...', 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik sebagai pengganti media kabel.\n\nTeknologi Wireless LAN adalah teknologi jaringan lokal yang memanfaatkan gelombang radio sebagai media transmisi data, sehingga tidak lagi mengandalkan kabel tembaga atau serat optik untuk menghubungkan perangkat akhir (end-user).\n\nJaringan wireless bekerja menggunakan gelombang elektromagnetik pada frekuensi tertentu (umumnya 2.4 GHz dan 5 GHz) untuk mengirim dan menerima paket data melalui perangkat Access Point (AP) dan Wireless Adapter.\n\n### Kelebihan\n- Mobilitas Tinggi: Pengguna dapat mengakses jaringan dan informasi dari mana saja selama masih berada dalam jangkauan sinyal (coverage area) Access Point.\n- Instalasi Mudah dan Cepat: Tidak memerlukan penarikan kabel yang rumit ke setiap meja kerja atau ruangan, sehingga meminimalkan perubahan fisik pada bangunan.\n- Fleksibilitas Tempat: Sangat ideal untuk area yang sulit dijangkau kabel, ruang rapat terbuka, gudang logistik, maupun gedung bersejarah.\n- Pengurangan Biaya Jangka Panjang: Menghemat biaya pengadaan kabel, pemeliharaan jalur kabel, serta biaya relokasi ketika terjadi perubahan tata letak kantor.\n- Kemudahan Ekspansi (Scalability): Penambahan pengguna baru atau perluasan area cakupan dapat dilakukan dengan cepat hanya dengan menambahkan unit Access Point baru.', 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80', 6, 1),
(7, 'Access Door', 'access-door', 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID...', 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID, PIN, maupun biometrik Sidik Jari dan Pengenalan Wajah untuk membatasi hak akses masuk dan keluar pada suatu ruangan.\n\nDalam penerapannya, sistem Access Door menggantikan kunci mekanis konvensional dengan metode otentikasi digital seperti Kartu RFID (Proximity/Mifare), kode PIN angka, maupun pemindaian biometrik (Sidik Jari / Fingerprint dan Pengenalan Wajah / Face Recognition).\n\nSistem ini bekerja menggunakan rangkaian kunci elektronik seperti Magnetic Lock (Maglock) atau Dropbolt yang dikendalikan oleh unit kontroler akses. Saat pengguna menempelkan kartu atau sidik jari yang terdaftar, kontroler akan memverifikasi data dan melepaskan pengunci magnetik selama beberapa detik agar pintu dapat dibuka.\n\nSelain meningkatkan keamanan fisik ruangan krusial (seperti ruang Server, ruang Direksi, Gudang, dan Laboratorium), sistem Access Door modern juga dapat diintegrasikan dengan perangkat lunak pencatatan waktu kehadiran (Time Attendance) serta sistem alarm kebakaran darurat (Emergency Breakglass) untuk keselamatan kerja.', 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80', 7, 1),
(8, 'CCTV', 'cctv', 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal...', 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal ini tidak seperti televisi siaran di mana setiap penerima yang disetel dengan benar dapat mengambil sinyal dari udara; pada sistem CCTV sinyal video hanya ditransmisikan secara eksklusif ke monitor dan perekam (DVR/NVR) yang terhubung di dalam jaringan keamanan internal.\n\nSistem CCTV modern oleh PT. Kreatif Global Solusindo mengintegrasikan kamera IP resolusi tinggi (Full HD hingga 4K Ultra HD), fitur Night Vision / ColorVu untuk pemantauan 24 jam penuh dalam kondisi gelap, serta analitik cerdas berbasis AI (Motion Detection, Line Crossing, Intrusion Detection, dan License Plate Recognition).\n\nSeluruh rekaman disimpan secara terpusat pada Network Video Recorder (NVR) atau Digital Video Recorder (DVR) berkapasitas besar dan dapat dipantau secara real-time melalui layar Command Center maupun aplikasi smartphone dari mana saja.\n\n### Keunggulan Solusi CCTV PT. Kreatif Global Solusindo\n- Kamera IP & Analog HD berkualitas industri (Hikvision, Dahua, Panasonic) tahan cuaca ekstrem (IP67 Weatherproof)\n- Pemantauan jarak jauh (Remote Viewing) real-time melalui smartphone, tablet, dan PC\n- Perekaman 24/7 nonstop dengan kompresi H.265+ yang menghemat ruang penyimpanan harddisk\n- Integrasi penuh dengan sistem alarm keamanan, Access Door, dan jaringan Fiber Optik kawasan industri', 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80', 8, 1);

-- --------------------------------------------------------
-- 22. Tabel Multi-Gambar Kategori Slide Show (slideshow_images)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `slideshow_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `caption` VARCHAR(300) DEFAULT NULL,
  `alt_text` VARCHAR(200) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `slideshow_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `slideshow_images` (`id`, `category_id`, `image_path`, `caption`, `alt_text`, `sort_order`, `is_primary`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80', 'Kabel fiber optik berinti serat kaca murni untuk transmisi data cahaya berkecepatan tinggi', 'Kabel fiber optik', 1, 1),
(2, 1, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 'Kabel dan patchcord fiber optik LC/SC/FC Singlemode & Multimode di rak server', 'Kabel dan patchcord fiber optik', 2, 0),
(3, 1, 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?auto=format&fit=crop&w=1200&q=80', 'Optical Distribution Frame (ODF) dan Optical Distribution Box (ODB/OTB) terintegrasi', 'ODF dan optical distribution box', 3, 0),
(4, 1, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80', 'Proses fusion splicing dan terminasi presisi inti serat optik oleh teknisi KGS', 'Proses splicing dan terminasi', 4, 0),
(5, 1, 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80', 'Perangkat pengujian redaman saluran fiber optik menggunakan OTDR & Optical Power Meter', 'Perangkat pengujian fiber optik', 5, 0),
(6, 1, 'https://images.unsplash.com/photo-1520869562399-e772f042f422?auto=format&fit=crop&w=1200&q=80', 'Instalasi penarikan jaringan backbone fiber optik udara dan bawah tanah di lapangan', 'Instalasi jaringan fiber optik di lapangan', 6, 0),
(7, 2, 'https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80', 'Tampilan penuh Guy Wire Tower vertikal dengan rentangan kawat seling penopang', 'Tampilan penuh guy wire tower', 1, 1),
(8, 2, 'https://images.unsplash.com/photo-1513828583688-c52646db42da?auto=format&fit=crop&w=1200&q=80', 'Detail tali penyangga baja galvanis (guy wire), spandscrew, dan titik angkur fondasi tanah', 'Detail tali penyangga dan titik angkur', 2, 0),
(9, 2, 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=80', 'Struktur rangka modul segitiga hasil fabrikasi presisi di workshop internal PT. KGS', 'Struktur rangka menara', 3, 0),
(10, 2, 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80', 'Contoh instalasi menara telekomunikasi Guy Wire Tower untuk antena radio & wireless ISP di area site', 'Contoh instalasi menara telekomunikasi', 4, 0),
(11, 3, 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80', 'Gambar utama menara monopole telekomunikasi berstruktur tiang tunggal mandiri', 'Gambar utama menara monopole', 1, 1),
(12, 3, 'https://images.unsplash.com/photo-1513828583688-c52646db42da?auto=format&fit=crop&w=1200&q=80', 'Detail struktur baja galvanis dan sambungan flange baut kekuatan tinggi pada menara monopole', 'Detail struktur dan sambungan menara', 2, 0),
(13, 3, 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=80', 'Contoh instalasi fondasi beton bertulang dan ereksi menara monopole di lapangan', 'Contoh instalasi di lapangan', 3, 0),
(14, 3, 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80', 'Contoh penerapan menara monopole pada infrastruktur telekomunikasi dan antena wireless perkotaan', 'Contoh penerapan pada infrastruktur telekomunikasi', 4, 0),
(15, 4, 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80', 'Batang penangkap petir (Air Terminal) di puncak menara/gedung untuk menerima sambaran petir', 'Batang penangkap petir', 1, 1),
(16, 4, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80', 'Kabel konduktor jalinan kawat tembaga (BC / NYY) penyalur muatan listrik menuju pembumian', 'Kabel konduktor', 2, 0),
(17, 4, 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80', 'Sistem grounding (pembumian) batang tembaga beserta pengujian Earth Resistance Tester < 2 Ohm', 'Sistem grounding', 3, 0),
(18, 4, 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80', 'Instalasi sistem proteksi petir eksternal pada puncak gedung bertingkat dan kawasan industri', 'Instalasi proteksi petir di gedung', 4, 0),
(19, 4, 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80', 'Perangkat proteksi petir dan Surge Arrester panel MDP untuk fasilitas telekomunikasi & pembangkit', 'Perangkat proteksi petir untuk fasilitas telekomunikasi', 5, 0),
(20, 5, 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80', 'Perangkat sentral komunikasi IP-PBX enterprise untuk manajemen ratusan ekstensi telepon', 'Perangkat IP-PBX', 1, 1),
(21, 5, 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80', 'Pesawat telepon meja IP Phone HD Voice dengan layar status ekstensi dan PoE', 'IP Phone', 2, 0),
(22, 5, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 'Server komunikasi SIP Trunking dan gateway PSTN/VoIP pada rak komunikasi perusahaan', 'Server komunikasi', 3, 0),
(23, 5, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80', 'Topologi dan arsitektur jaringan komunikasi suara & data perusahaan lintas kantor cabang', 'Diagram jaringan komunikasi perusahaan', 4, 0),
(24, 5, 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 'Contoh instalasi sistem telepon kantor, resepsionis, dan ruang operasional BUMN/Rumah Sakit', 'Contoh instalasi sistem telepon kantor', 5, 0),
(25, 6, 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80', 'Access Point Wi-Fi Enterprise dual-band untuk cakupan jaringan lokal nirkabel berkapasitas tinggi', 'Access point Wi-Fi', 1, 1),
(26, 6, 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80', 'Perangkat radio wireless outdoor tahan cuaca untuk link Point-to-Point & Point-to-Multipoint', 'Perangkat wireless outdoor', 2, 0),
(27, 6, 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80', 'Antena dish radio komunikasi gelombang mikro pada menara transmisi jarak jauh', 'Antena radio komunikasi', 3, 0),
(28, 6, 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 'Instalasi jaringan Wireless LAN (WLAN) terpusat di lingkungan gedung perkantoran & kampus', 'Instalasi jaringan wireless di kantor', 4, 0),
(29, 6, 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80', 'Infrastruktur konektivitas nirkabel antargedung dan interkoneksi site perkebunan/tambang', 'Infrastruktur konektivitas nirkabel antargedung', 5, 0),
(30, 7, 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80', 'Mesin reader akses kartu pintar RFID dan keypad PIN untuk otorisasi pintu ruang terbatas', 'Mesin akses kartu RFID', 1, 1),
(31, 7, 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=1200&q=80', 'Perangkat pemindai biometrik Fingerprint Access Control terintegrasi log absensi kehadiran', 'Fingerprint access control', 2, 0),
(32, 7, 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80', 'Sistem pengunci pintu elektromagnetik (Magnetic Lock / Drop Bolt) dan tombol Exit Push Button', 'Electronic door lock', 3, 0),
(33, 7, 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80', 'Terminal Face Recognition (pengenalan wajah) tanpa sentuh dengan layar verifikasi cepat', 'Face recognition terminal', 4, 0),
(34, 7, 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 'Instalasi perangkat Access Door pada pintu kaca ruang server dan kantor perbankan', 'Instalasi access door di kantor', 5, 0),
(35, 7, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80', 'Sistem perangkat lunak pengelolaan hak akses pintu dan pemantauan log keluar-masuk terpusat', 'Sistem pengelolaan akses terpusat', 6, 0),
(36, 8, 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80', 'Instalasi kamera pengawas CCTV IP Outdoor Weatherproof IP67 untuk keamanan perimeter gedung dan pabrik', 'Kamera CCTV Outdoor Industri', 1, 1),
(37, 8, 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80', 'Pusat kendali pemantauan (Security Command Center) dengan multi-monitor dan sistem NVR terpusat', 'Ruang Monitoring CCTV Command Center', 2, 0),
(38, 8, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80', 'Kamera PTZ (Pan-Tilt-Zoom) jarak jauh untuk pengawasan area pelabuhan, dermaga, dan kawasan perkebunan', 'Kamera CCTV PTZ High Speed Dome', 3, 0),
(39, 8, 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80', 'Kamera Dome Indoor dengan fitur AI Smart Detection untuk area lobi kantor, perbankan, dan rumah sakit', 'Kamera CCTV Dome Indoor', 4, 0),
(40, 8, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 'Penyimpanan rekaman NVR/DVR di rak server dengan proteksi UPS dan akses pemantauan mobile 24/7', 'Sistem Perekam NVR CCTV', 5, 0);

-- --------------------------------------------------------
-- 23. Tabel Multi-Gambar Dokumentasi Proyek Portofolio (project_images)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `caption` VARCHAR(300) DEFAULT NULL,
  `alt_text` VARCHAR(200) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;




