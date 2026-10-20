<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/seo.php';

$company_name  = get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO');
$tagline       = get_setting($pdo, 'tagline', 'Your Solution & Partner');
$phone_hunting = get_setting($pdo, 'phone_palembang', get_setting($pdo, 'phone_hunting', '0711-316088 (Hunting)'));
$email_general = get_setting($pdo, 'email', get_setting($pdo, 'email_general', 'info@kgs.net.id'));
$address_plg   = get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');

// Ambil logo perusahaan kustom yang diunggah melalui Admin CMS (jika ada)
$custom_logo = trim(get_setting($pdo, 'logo', ''));
if (empty($custom_logo)) {
    $custom_logo = trim(get_setting($pdo, 'company_logo', ''));
}
if (empty($custom_logo)) {
    $fallback_logo = trim(get_setting($pdo, 'logo_main', ''));
    if (!empty($fallback_logo) && $fallback_logo !== 'assets/images/logo-kgs.svg') {
        $custom_logo = $fallback_logo;
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$header_menus = get_navigation_menus($pdo, 'header', true);
$active_theme = get_active_website_theme($pdo);
$safe_primary = sanitize_hex_color($active_theme['primary_color'] ?? '#1d4ed8', '#1d4ed8');
$safe_hover   = sanitize_hex_color($active_theme['primary_hover'] ?? '#1e40af', '#1e40af');
$safe_hero_bg = sanitize_hex_color($active_theme['hero_bg_color'] ?? '#0b1329', '#0b1329');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php render_seo_meta($pdo, $page_title ?? '', $page_desc ?? '', $og_type ?? 'website', $og_image ?? ''); ?>
    <link rel="icon" type="image/svg+xml" href="<?= e(get_setting($pdo, 'favicon', 'assets/images/favicon.svg')) ?>">
    <!-- Early Theme Initialization (Mencegah Flash of Incorrect Theme / FOUC) -->
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('kgs_color_mode') === 'dark' ? 'dark' : 'light';
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
                document.documentElement.setAttribute('data-color-mode', saved);
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Dynamic Website Theme Variables & Dark/Light Mode Adaptive Tokens */
        :root {
            --kgs-primary: <?= e($safe_primary) ?>;
            --kgs-primary-hover: <?= e($safe_hover) ?>;
            --kgs-hero-bg: <?= e($safe_hero_bg) ?>;
        }
        body .bg-blue-600 { background-color: var(--kgs-primary) !important; }
        body .hover\:bg-blue-700:hover,
        body .hover\:bg-blue-500:hover { background-color: var(--kgs-primary-hover) !important; }
        body .text-blue-600,
        body .text-blue-700 { color: var(--kgs-primary) !important; }
        body .border-blue-600 { border-color: var(--kgs-primary) !important; }

        /* Dark Mode Comprehensive Adaptive Styles */
        html.dark body {
            background-color: #020617 !important;
            color: #f1f5f9 !important;
        }
        html.dark header.sticky {
            background-color: rgba(15, 23, 42, 0.96) !important;
            border-color: #1e293b !important;
        }
        html.dark .bg-white {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }
        html.dark .bg-slate-50,
        html.dark .bg-slate-100 {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-900,
        html.dark .text-slate-800 {
            color: #f8fafc !important;
        }
        html.dark .text-slate-700,
        html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-500 {
            color: #94a3b8 !important;
        }
        html.dark .border-slate-100,
        html.dark .border-slate-200,
        html.dark .border-slate-300 {
            border-color: #334155 !important;
        }
        html.dark .text-blue-600,
        html.dark .text-blue-700 {
            color: #60a5fa !important;
        }
        html.dark .bg-blue-50 {
            background-color: rgba(30, 58, 138, 0.3) !important;
            color: #93c5fd !important;
        }
        html.dark input:not([type="checkbox"]):not([type="radio"]),
        html.dark textarea,
        html.dark select {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }
    </style>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased flex flex-col min-h-screen selection:bg-blue-600 selection:text-white transition-colors duration-200">

    <!-- Top Utility Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2.5 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-3">
                <span class="px-2 py-0.5 bg-blue-600/20 text-blue-400 border border-blue-500/30 rounded font-semibold" data-live-setting="topbar_badge"><?= e(get_setting($pdo, 'topbar_badge', 'Sejak 1997 (Creative Com)')) ?></span>
                <span class="hidden sm:inline text-slate-400" data-live-setting="topbar_text"><?= e(get_setting($pdo, 'topbar_text', 'System Integrator, Security Solution & ISP Resmi APJII (KGSNet)')) ?></span>
            </div>
            <div class="flex items-center gap-4 font-mono">
                <a href="tel:0711316088" class="hover:text-white transition">📞 <span data-live-setting="phone_palembang"><?= e($phone_hunting) ?></span></a>
                <a href="mailto:<?= e($email_general) ?>" class="hover:text-white transition">✉️ <span data-live-setting="email"><?= e($email_general) ?></span></a>
                <a href="admin/login.php" class="text-slate-400 hover:text-blue-400 transition">Admin CMS</a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Header -->
    <header class="sticky top-0 z-40 <?= $active_theme['navbar_style'] === 'dark' ? 'bg-slate-900/95 border-slate-800 text-white' : 'bg-white/95 border-slate-200 text-slate-900' ?> backdrop-blur-md border-b shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Brand Identity & Logo -->
            <a href="index.php" class="flex items-center gap-2.5 sm:gap-3.5 group min-w-0">
                <?php if (!empty($custom_logo)): ?>
                    <img src="<?= e($custom_logo) ?>" alt="<?= e($company_name) ?>" data-live-img="logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl object-contain bg-white border border-slate-200 dark:border-slate-700 p-1 shadow-xs shrink-0">
                <?php else: ?>
                    <div data-live-img="logo" title="Klik pada Mode Edit Langsung untuk mengunggah Logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-700 to-slate-900 flex items-center justify-center text-white font-extrabold text-base sm:text-lg tracking-tighter shadow-md group-hover:scale-105 transition shrink-0">
                        KGS
                    </div>
                <?php endif; ?>
                <div class="min-w-0">
                    <span class="font-extrabold text-xs sm:text-base md:text-lg <?= $active_theme['navbar_style'] === 'dark' ? 'text-white' : 'text-slate-900 dark:text-white' ?> tracking-tight block leading-tight truncate sm:whitespace-normal" data-live-setting="company_name"><?= e($company_name) ?></span>
                    <span class="text-[10px] sm:text-xs text-blue-600 dark:text-blue-400 font-semibold tracking-wide block truncate" data-live-setting="tagline"><?= e($tagline) ?></span>
                </div>
            </a>

            <!-- Desktop Menu (Dinamis dari Tabel `menus` Database) -->
            <nav class="hidden lg:flex items-center gap-1 text-sm font-semibold">
                <?php foreach ($header_menus as $menu_item): ?>
                    <?php
                    $m_url = sanitize_nav_url((string)($menu_item['url'] ?? '#'));
                    $m_title = (string)($menu_item['title'] ?? '');
                    $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $is_active = ($current_page === basename(parse_url($m_url, PHP_URL_PATH) ?: ''))
                        || ($m_url === 'layanan.php' && $current_page === 'detail-layanan.php')
                        || ($m_url === 'portofolio.php' && $current_page === 'klien.php');
                    ?>
                    <a href="<?= e($m_url) ?>"<?= $m_target ?> class="px-3 py-2 rounded-lg transition <?= $is_active ? 'bg-blue-50 text-blue-700' : ($active_theme['navbar_style'] === 'dark' ? 'text-slate-200 hover:bg-slate-800' : 'text-slate-600 hover:bg-slate-100') ?>">
                        <?= e($m_title) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- CTA Button & Theme Switcher -->
            <div class="hidden sm:flex items-center gap-2.5">
                <!-- Tombol Toggle Tema (Light / Dark) -->
                <div class="inline-flex items-center rounded-xl p-1 border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                    <button type="button" data-theme-btn="light" data-theme-mode="light" onclick="window.setKgsThemeMode('light')" title="Light Mode (Terang)" aria-label="Light Mode" class="kgs-theme-btn px-2 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>
                        <span class="hidden xl:inline">Light</span>
                    </button>
                    <button type="button" data-theme-btn="dark" data-theme-mode="dark" onclick="window.setKgsThemeMode('dark')" title="Dark Mode (Gelap)" aria-label="Dark Mode" class="kgs-theme-btn px-2 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="hidden xl:inline">Dark</span>
                    </button>
                </div>

                <a href="kontak.php" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Minta Penawaran
                </a>
            </div>

            <!-- Mobile Controls (Theme Toggle + Menu Button) -->
            <div class="flex items-center gap-1.5 lg:hidden shrink-0">
                <button type="button" onclick="window.cycleKgsThemeMode()" class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700" aria-label="Ganti Tema">
                    <svg class="w-5 h-5 dark:hidden text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>
                    <svg class="w-5 h-5 hidden dark:block text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <button id="mobileMenuBtn" class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Buka Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown (Dinamis dari Tabel `menus` Database) -->
        <div id="mobileMenu" class="hidden lg:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 pt-2 pb-5 space-y-1.5 text-sm font-semibold">
            <!-- Mobile Theme Selector Bar -->
            <div class="flex items-center justify-between px-3 py-2 mb-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                <span class="font-bold text-slate-700 dark:text-slate-200">Tema Tampilan:</span>
                <div class="inline-flex items-center gap-1">
                    <button type="button" data-theme-btn="light" data-theme-mode="light" onclick="window.setKgsThemeMode('light')" class="kgs-theme-btn px-2.5 py-1 rounded-lg font-bold transition">☀️ Light</button>
                    <button type="button" data-theme-btn="dark" data-theme-mode="dark" onclick="window.setKgsThemeMode('dark')" class="kgs-theme-btn px-2.5 py-1 rounded-lg font-bold transition">🌙 Dark</button>
                </div>
            </div>
            <?php foreach ($header_menus as $menu_item): ?>
                <?php
                $m_url = sanitize_nav_url((string)($menu_item['url'] ?? '#'));
                $m_title = (string)($menu_item['title'] ?? '');
                $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                ?>
                <a href="<?= e($m_url) ?>"<?= $m_target ?> class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100">
                    <?= e($m_title) ?>
                </a>
            <?php endforeach; ?>
            <div class="pt-2">
                <a href="kontak.php" class="block text-center py-2.5 bg-blue-600 text-white rounded-xl font-bold">Minta Penawaran</a>
            </div>
        </div>
    </header>
    <main class="flex-1">
