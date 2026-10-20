<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$admin_user = $_SESSION['admin_username'] ?? 'admin';
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'superadmin';

try {
    $count_new_leads = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Baru'")->fetchColumn();
} catch (Exception $e) {
    $count_new_leads = 0;
}

$current_script = basename($_SERVER['PHP_SELF']);
$company_name = get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO');
$favicon_path = get_setting($pdo, 'favicon', 'assets/images/favicon.svg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($admin_page_title) ? e($admin_page_title) . ' - ' : '' ?>KGS Admin CMS</title>
    <link rel="icon" href="../<?= e($favicon_path) ?>">
    <script>
      (function () {
        try {
          var saved = localStorage.getItem('kgs_color_mode') === 'dark' ? 'dark' : 'light';
          var root = document.documentElement;
          if (saved === 'dark') {
            root.classList.add('dark');
            root.setAttribute('data-theme', 'dark');
          } else {
            root.classList.remove('dark');
            root.setAttribute('data-theme', 'light');
          }
          root.setAttribute('data-color-mode', saved);
        } catch (e) {}
      })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode: 'class' };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body class="bg-slate-100 dark:bg-slate-950 min-h-screen flex flex-col text-slate-800 dark:text-slate-100 transition-colors duration-200">

    <!-- Topbar Navigation -->
    <header class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="index.php" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-blue-600 text-white font-extrabold text-sm rounded-lg flex items-center justify-center">
                        KGS
                    </div>
                    <div>
                        <span class="font-bold text-sm block leading-tight">Admin CMS Panel</span>
                        <span class="text-[10px] text-blue-400 font-medium block"><?= e($company_name) ?></span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <!-- Dark / Light Theme Switcher -->
                <div class="inline-flex items-center rounded-xl p-1 border bg-slate-800 border-slate-700" role="group" aria-label="Pilih Tema Tampilan Admin">
                    <button type="button" data-theme-btn="light" onclick="window.setKgsColorMode && window.setKgsColorMode('light')" title="Light Mode (Terang)" class="kgs-theme-btn px-2 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-slate-300 font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                        <span class="hidden lg:inline">Light</span>
                    </button>
                    <button type="button" data-theme-btn="dark" onclick="window.setKgsColorMode && window.setKgsColorMode('dark')" title="Dark Mode (Gelap)" class="kgs-theme-btn px-2 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-slate-300 font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        <span class="hidden lg:inline">Dark</span>
                    </button>
                </div>

                <a href="../index.php?live_edit=1" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-xs">
                    <span>✏️ Edit Langsung di Website</span>
                </a>
                <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-slate-300 hover:text-white transition">
                    <span>Lihat Website Publik &nearr;</span>
                </a>

                <div class="flex items-center gap-2 pl-4 border-l border-slate-800">
                    <div class="text-right hidden sm:block">
                        <span class="font-bold text-white block text-xs"><?= e($admin_name) ?></span>
                        <span class="text-[10px] text-emerald-400 uppercase tracking-wider font-semibold"><?= e($admin_role) ?></span>
                    </div>
                    <a href="logout.php" class="px-3 py-1.5 bg-rose-900/60 hover:bg-rose-800 text-rose-200 border border-rose-700/80 rounded-lg transition font-medium">
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Workspace Layout with Left Sidebar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full flex flex-col md:flex-row gap-8">
        
        <!-- Left Sidebar Navigation -->
        <aside class="w-full md:w-64 shrink-0 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-2">
                    Menu Utama CMS
                </div>
                <nav class="space-y-1 text-xs font-semibold">
                    <a href="index.php" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition <?= in_array($current_script, ['index.php', 'dashboard.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>📊 Dashboard</span>
                    </a>
                    <a href="inquiries.php" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition <?= in_array($current_script, ['inquiries.php', 'detail-inquiry.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>💬 Permintaan Penawaran</span>
                        <?php if ($count_new_leads > 0): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-bold"><?= $count_new_leads ?></span>
                        <?php endif; ?>
                    </a>
                </nav>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 pt-4 pb-2 border-t border-slate-100 mt-3">
                    Katalog & Konten Website
                </div>
                <nav class="space-y-1 text-xs font-semibold">
                    <a href="bagian-halaman.php" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition <?= $current_script === 'bagian-halaman.php' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' ?>">
                        <span>🧩 Bagian Halaman & Statistik</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-emerald-600 text-white font-bold">Lengkap</span>
                    </a>
                    <a href="layanan.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['layanan.php', 'services.php', 'tambah-layanan.php', 'edit-layanan.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>📦 Kelola Layanan</span>
                    </a>
                    <a href="audit-pdf.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'audit-pdf.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>📋 Katalog 22 Standar</span>
                    </a>
                    <a href="kategori.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'kategori.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🗂️ Kategori Segmen (5)</span>
                    </a>
                    <a href="harga.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['harga.php', 'pricing.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🏷️ Paket Harga B2B</span>
                    </a>
                    <a href="faq.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'faq.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>❓ Tanya Jawab (FAQ)</span>
                    </a>
                    <a href="portofolio.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['portofolio.php', 'portfolio.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🏗️ Dokumentasi Proyek</span>
                    </a>
                    <a href="klien.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'klien.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🏢 Daftar Klien (100+)</span>
                    </a>
                    <a href="blog.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['blog.php', 'articles.php', 'tambah-artikel.php', 'edit-artikel.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>✍️ Blog & Artikel</span>
                    </a>
                    <a href="halaman.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['halaman.php', 'pages.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>📄 Halaman Statis</span>
                    </a>
                    <a href="media.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'media.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🖼️ Media & Upload Gambar</span>
                    </a>
                </nav>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 pt-4 pb-2 border-t border-slate-100 mt-3">
                    Pengaturan & Keamanan
                </div>
                <nav class="space-y-1 text-xs font-semibold">
                    <a href="tema-slider.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'tema-slider.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🎨 Tema & Slider Beranda</span>
                    </a>
                    <a href="pengaturan.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['pengaturan.php', 'settings.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>⚙️ Identitas, Logo & Maps</span>
                    </a>
                    <a href="menu.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= in_array($current_script, ['menu.php', 'navigasi.php']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🧭 Pengaturan Navigasi</span>
                    </a>
                    <a href="seo.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'seo.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🌐 SEO & Metadata</span>
                    </a>
                    <a href="users.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'users.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>👥 Pengguna & Role</span>
                    </a>
                    <a href="audit-log.php" class="flex items-center justify-between px-3 py-2 rounded-lg transition <?= $current_script === 'audit-log.php' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <span>🛡️ Audit Log Keamanan</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 space-y-6">
