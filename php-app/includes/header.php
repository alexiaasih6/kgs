<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/seo.php';

$company_name  = get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO');
$tagline       = get_setting($pdo, 'tagline', 'System Integrator, Security Solution & Internet Provider');
if ($tagline === 'Your Solution & Partner' || strcasecmp($tagline, 'System Integrator, Security Solution & Internet Service Provider') === 0) {
    $tagline = 'System Integrator, Security Solution & Internet Provider';
}
$company_name_color = sanitize_hex_color(get_setting($pdo, 'company_name_color', '#0f172a'), '#0f172a');
$tagline_color      = sanitize_hex_color(get_setting($pdo, 'tagline_color', '#2563eb'), '#2563eb');
$phone_hunting = get_setting($pdo, 'phone_palembang', get_setting($pdo, 'phone_hunting', '0711-316088'));
$phone_hunting = trim(str_ireplace('(Hunting)', '', $phone_hunting));
$email_general = get_setting($pdo, 'email', get_setting($pdo, 'email_general', 'info@creative.co.id'));
if ($email_general === 'info@kgs.net.id' || $email_general === '') {
    $email_general = 'info@creative.co.id';
}
$address_plg   = get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113');
$topbar_badge  = get_setting($pdo, 'topbar_badge', $address_plg);
if ($topbar_badge === 'Sejak 1997 (Creative Com)' || $topbar_badge === '') {
    $topbar_badge = $address_plg;
}

// Ambil logo perusahaan kustom yang diunggah melalui Admin CMS (jika ada)
$custom_logo = trim(get_setting($pdo, 'logo', ''));
if (empty($custom_logo)) {
    $custom_logo = trim(get_setting($pdo, 'company_logo', ''));
}
if (strpos($custom_logo, 'image_8b7e60bd') !== false || $custom_logo === 'assets/images/logo-kgs.svg') {
    $custom_logo = '';
}
$custom_logo = ltrim($custom_logo, '/');

$current_page = basename($_SERVER['PHP_SELF']);
$header_menus = get_navigation_menus($pdo, 'header', true);
$active_theme = get_active_website_theme($pdo);
$safe_primary = sanitize_hex_color($active_theme['primary_color'] ?? '#1d4ed8', '#1d4ed8');
$safe_hover   = sanitize_hex_color($active_theme['primary_hover'] ?? '#1e40af', '#1e40af');
$safe_hero_bg = sanitize_hex_color($active_theme['hero_bg_color'] ?? '#0b1329', '#0b1329');
$logo_bg_color   = sanitize_hex_color(get_setting($pdo, 'logo_bg_color', $safe_primary), $safe_primary);
$logo_text_color = sanitize_hex_color(get_setting($pdo, 'logo_text_color', '#ffffff'), '#ffffff');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php render_seo_meta($pdo, $page_title ?? '', $page_desc ?? ($page_description ?? ''), $og_type ?? 'website', $og_image ?? ''); ?>
    <link rel="icon" type="image/svg+xml" href="<?= e(get_setting($pdo, 'favicon', 'assets/images/favicon.svg')) ?>">
    <!-- Early Theme Initialization & Interactive Light/Dark Controller -->
    <script>
        (function() {
            function applyKgsTheme(mode) {
                var resolved = mode === 'dark' ? 'dark' : 'light';
                var root = document.documentElement;
                if (resolved === 'dark') {
                    root.classList.add('dark');
                    root.setAttribute('data-theme', 'dark');
                } else {
                    root.classList.remove('dark');
                    root.setAttribute('data-theme', 'light');
                }
                root.setAttribute('data-color-mode', resolved);
                document.querySelectorAll('[data-theme-btn], [data-theme-mode]').forEach(function(btn) {
                    var btnMode = btn.getAttribute('data-theme-btn') || btn.getAttribute('data-theme-mode');
                    var isActive = btnMode === resolved;
                    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    btn.classList.toggle('bg-blue-600', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('shadow-xs', isActive);
                    btn.classList.toggle('text-slate-600', !isActive && resolved === 'light');
                    btn.classList.toggle('text-slate-300', !isActive && resolved === 'dark');
                });
            }
            window.setKgsThemeMode = function(mode) {
                var next = mode === 'dark' ? 'dark' : 'light';
                try { localStorage.setItem('kgs_color_mode', next); } catch (e) {}
                applyKgsTheme(next);
            };
            window.setKgsColorMode = window.setKgsThemeMode;
            window.cycleKgsThemeMode = function() {
                var current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                window.setKgsThemeMode(current === 'dark' ? 'light' : 'dark');
            };
            try {
                var saved = localStorage.getItem('kgs_color_mode') === 'dark' ? 'dark' : 'light';
                applyKgsTheme(saved);
            } catch (e) {
                applyKgsTheme('light');
            }
            document.addEventListener('DOMContentLoaded', function() {
                var cur = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                applyKgsTheme(cur);
            });
        })();
    </script>
    <script src="assets/js/main.js" defer></script>
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
        @media (max-width: 1023.98px) {
            .kgs-desktop-nav-tier {
                display: none !important;
            }
        }
        @media (min-width: 1024px) {
            .kgs-desktop-nav-tier {
                display: block !important;
            }
        }
    </style>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased flex flex-col min-h-screen selection:bg-blue-600 selection:text-white transition-colors duration-200">

    <!-- Top Utility Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2.5 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-3">
                <span class="px-2 py-0.5 bg-blue-600/20 text-blue-400 border border-blue-500/30 rounded font-semibold" data-live-setting="address_palembang">📍 <?= e($topbar_badge) ?></span>
                <span class="hidden sm:inline text-slate-400" data-live-setting="topbar_text"><?= e(get_setting($pdo, 'topbar_text', 'System Integrator, Security Solution & ISP Resmi APJII (KGSNet)')) ?></span>
            </div>
            <div class="flex items-center gap-4 font-mono">
                <a href="tel:0711316088" class="hover:text-white transition">📞 <span data-live-setting="phone_palembang"><?= e($phone_hunting) ?></span></a>
                <a href="mailto:<?= e($email_general) ?>" class="hover:text-white transition">✉️ <span data-live-setting="email"><?= e($email_general) ?></span></a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Header: 2-Tier Architecture (Brand Identity di Atas Menu, Navigasi Menu di Bawahnya) -->
    <header class="sticky top-0 z-40 <?= $active_theme['navbar_style'] === 'dark' ? 'bg-slate-900/95 border-slate-800 text-white' : 'bg-white/95 border-slate-200 text-slate-900' ?> backdrop-blur-md border-b shadow-xs">
        <!-- TIER 1 (DI ATAS MENU): Brand Identity & Quick Actions (Mobile: Ramping & Bertingkat ke Bawah) -->
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-1.5 sm:py-2.5 lg:py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-1.5 sm:gap-2 lg:gap-4">
            <!-- Baris 1 Mobile: Brand Identity (Baris Atas: Logo + PT. KREATIF GLOBAL SOLUSINDO, Baris Bawah: System Integrator, Security Solution & Internet Provider 1 Baris) -->
            <a href="index.php" class="flex flex-col justify-center group min-w-0 w-full lg:w-auto lg:flex-1 text-left">
                <div class="flex flex-row items-center gap-2 sm:gap-2.5 min-w-0">
                    <?php if (!empty($custom_logo)): ?>
                        <img src="<?= e($custom_logo) ?>" alt="<?= e($company_name) ?>" data-live-img="logo" class="h-7 sm:h-10 w-auto max-w-[34px] sm:max-w-[120px] rounded-lg object-contain bg-white border border-slate-200 dark:border-slate-700 p-0.5 shadow-xs shrink-0">
                    <?php else: ?>
                        <div data-live-img="logo" title="Klik pada Mode Edit Langsung untuk mengunggah Logo" style="background-color: <?= e($logo_bg_color) ?>; color: <?= e($logo_text_color) ?>;" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center font-black text-[10px] sm:text-sm tracking-tighter shadow-sm group-hover:scale-105 transition shrink-0">
                            KGS
                        </div>
                    <?php endif; ?>
                    <span style="color: <?= e($company_name_color) ?>;" class="font-extrabold text-[13.5px] sm:text-lg md:text-xl tracking-tight leading-none whitespace-nowrap <?= strtolower($company_name_color) === '#0f172a' ? 'dark:text-white' : '' ?>" data-live-setting="company_name"><?= e($company_name) ?></span>
                </div>
                <span style="color: <?= e($tagline_color) ?>;" class="text-[10.5px] sm:text-xs md:text-sm font-semibold tracking-tight sm:tracking-wide block leading-tight mt-1 whitespace-nowrap <?= strtolower($tagline_color) === '#2563eb' ? 'dark:text-blue-400' : '' ?>" data-live-setting="tagline"><?= e($tagline) ?></span>
            </a>

            <!-- Baris 2 Mobile (Di Bawah Logo & Nama PT): Search Icon, Light & Dark Mode, Konsultasi & Tombol Garis Tiga Menu di Samping Konsultasi -->
            <div class="w-full lg:w-auto pt-1.5 lg:pt-0 border-t border-slate-200/70 dark:border-slate-800/80 lg:border-t-0 flex items-center justify-between lg:justify-end gap-1.5 sm:gap-2 shrink-0">
                <div class="flex items-center gap-1 sm:gap-2">
                    <!-- Tombol Pencarian Cepat (Search Icon Menu) -->
                    <button type="button" onclick="window.toggleKgsSearchModal(true)" title="Cari Layanan, Artikel & Solusi (Ctrl+K)" aria-label="Buka Pencarian" class="px-2 py-1 sm:px-3.5 sm:py-1.5 rounded-lg sm:rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer flex items-center gap-1 sm:gap-1.5 text-[11px] sm:text-xs font-bold">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/></svg>
                        <span>Cari</span>
                    </button>

                    <!-- Tombol Toggle Tema (Light / Dark) -->
                    <div class="inline-flex items-center rounded-lg sm:rounded-xl p-0.5 border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                        <button type="button" data-theme-btn="light" data-theme-mode="light" onclick="window.setKgsThemeMode('light')" title="Light Mode (Terang)" aria-label="Light Mode" class="kgs-theme-btn px-1.5 sm:px-2 py-1 rounded-md sm:rounded-lg transition cursor-pointer flex items-center gap-1 text-[11px] sm:text-xs font-bold">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>
                            <span>Light</span>
                        </button>
                        <button type="button" data-theme-btn="dark" data-theme-mode="dark" onclick="window.setKgsThemeMode('dark')" title="Dark Mode (Gelap)" aria-label="Dark Mode" class="kgs-theme-btn px-1.5 sm:px-2 py-1 rounded-md sm:rounded-lg transition cursor-pointer flex items-center gap-1 text-[11px] sm:text-xs font-bold">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <span>Dark</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <a href="kontak.php#konsultasi" class="px-2.5 sm:px-4 py-1 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-xs font-bold rounded-lg sm:rounded-xl shadow-sm transition whitespace-nowrap flex items-center gap-1">
                        <span>Konsultasi</span>
                        <span class="hidden sm:inline">&rarr;</span>
                    </a>

                    <button id="mobileMenuBtn" type="button" class="lg:hidden p-1.5 sm:p-2 rounded-lg sm:rounded-xl text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 cursor-pointer flex items-center justify-center" aria-label="Buka Menu" title="Menu Navigasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- TIER 2 (DI BAWAH BRAND): Dedicated Navigation Menu Bar (Hanya Desktop lg+, di Mobile Disembunyikan di Tombol Garis Tiga) -->
        <div class="hidden lg:block kgs-desktop-nav-tier border-t <?= $active_theme['navbar_style'] === 'dark' ? 'border-slate-800/80 bg-slate-950/40' : 'border-slate-100 bg-slate-50/70 dark:border-slate-800/80 dark:bg-slate-950/40' ?>">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-1 xl:gap-2 text-xs sm:text-sm font-semibold py-1.5 overflow-x-auto">
                    <?php
                    if (empty($header_menus)) {
                        $header_menus = [
                            ['title' => 'Beranda', 'url' => 'index.php', 'target' => '_self'],
                            ['title' => 'Produk & Layanan', 'url' => 'layanan.php', 'target' => '_self'],
                            ['title' => 'Promo', 'url' => 'promo.php', 'target' => '_self'],
                            ['title' => 'Client / Pelanggan', 'url' => 'klien.php', 'target' => '_self'],
                            ['title' => 'Kemitraan', 'url' => 'kemitraan.php', 'target' => '_self'],
                            ['title' => 'Testimoni', 'url' => 'testimoni.php', 'target' => '_self'],
                            ['title' => 'Hubungi Kami', 'url' => 'kontak.php', 'target' => '_self'],
                        ];
                    }
                    $seen_header_titles = [];
                    foreach ($header_menus as $menu_item):
                        $m_title = trim((string)($menu_item['title'] ?? ''));
                        if ($m_title === '') continue;
                        $m_key = strtolower($m_title);
                        if (isset($seen_header_titles[$m_key])) continue;
                        $seen_header_titles[$m_key] = true;
                        $m_url = sanitize_nav_url(normalize_core_menu_url($m_title, (string)($menu_item['url'] ?? 'index.php'), $menu_item['page_slug'] ?? null));
                        $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        $is_active = ($current_page === basename(parse_url($m_url, PHP_URL_PATH) ?: ''))
                            || ($m_url === 'layanan.php' && $current_page === 'detail-layanan.php')
                            || ($m_url === 'slideshow.php' && $current_page === 'slideshow-detail.php')
                            || (($m_url === 'portofolio.php' || $m_url === 'klien.php') && ($current_page === 'klien.php' || $current_page === 'portofolio.php'));
                    ?>
                        <a href="<?= e($m_url) ?>"<?= $m_target ?> class="px-3 sm:px-3.5 py-1.5 rounded-lg transition whitespace-nowrap relative <?= $is_active ? 'bg-blue-50 text-blue-700 font-bold dark:bg-slate-800 dark:text-blue-400' : ($active_theme['navbar_style'] === 'dark' ? 'text-slate-200 hover:bg-slate-800' : 'text-slate-700 hover:bg-slate-200/60 dark:text-slate-200 dark:hover:bg-slate-800') ?>">
                            <?= e($m_title) ?>
                            <?php if ($is_active): ?>
                                <span class="absolute bottom-0 left-2 right-2 h-0.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- Mobile Menu Dropdown (Dinamis dari Tabel `menus` Database) -->
        <div id="mobileMenu" class="hidden lg:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 pt-3 pb-5 space-y-2.5 text-sm font-semibold">
            <!-- Identitas Mobile: Logo + PT. KREATIF GLOBAL SOLUSINDO di Atas, System Integrator, Security Solution & Internet Provider 1 Baris di Bawahnya -->
            <div class="pb-2.5 mb-2 border-b border-slate-100 dark:border-slate-800 flex flex-col justify-center text-left">
                <div class="flex flex-row items-center gap-2">
                    <?php if (!empty($custom_logo)): ?>
                        <img src="<?= e($custom_logo) ?>" alt="<?= e($company_name) ?>" class="h-7 w-auto max-w-[34px] rounded-lg object-contain bg-white border border-slate-200 dark:border-slate-700 p-0.5 shrink-0">
                    <?php else: ?>
                        <div style="background-color: <?= e($logo_bg_color) ?>; color: <?= e($logo_text_color) ?>;" class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-[10px] tracking-tighter shadow-xs shrink-0">
                            KGS
                        </div>
                    <?php endif; ?>
                    <span style="color: <?= e($company_name_color) ?>;" class="font-extrabold text-[13px] sm:text-sm tracking-tight leading-none whitespace-nowrap <?= strtolower($company_name_color) === '#0f172a' ? 'dark:text-white' : '' ?>"><?= e($company_name) ?></span>
                </div>
                <span style="color: <?= e($tagline_color) ?>;" class="text-[10.5px] font-semibold tracking-tight block leading-tight mt-1 whitespace-nowrap <?= strtolower($tagline_color) === '#2563eb' ? 'dark:text-blue-400' : '' ?>"><?= e($tagline) ?></span>
            </div>

            <!-- Icon & Form Pencarian di dalam Menu Mobile -->
            <div class="space-y-2">
                <form action="cari.php" method="GET" class="flex gap-2">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/></svg>
                        <input type="search" name="q" placeholder="Cari layanan, harga, artikel, proyek..." required minlength="2" class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl shrink-0 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/></svg>
                        <span>Cari</span>
                    </button>
                </form>
            </div>

            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-1">Navigasi Halaman</div>
            <?php
            $seen_mobile_titles = [];
            foreach ($header_menus as $menu_item):
                $m_title = trim((string)($menu_item['title'] ?? ''));
                if ($m_title === '') continue;
                $m_key = strtolower($m_title);
                if (isset($seen_mobile_titles[$m_key])) continue;
                $seen_mobile_titles[$m_key] = true;
                $m_url = sanitize_nav_url(normalize_core_menu_url($m_title, (string)($menu_item['url'] ?? 'index.php'), $menu_item['page_slug'] ?? null));
                $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
                <a href="<?= e($m_url) ?>"<?= $m_target ?> class="block px-3 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100">
                    <?= e($m_title) ?>
                </a>
            <?php endforeach; ?>
            <div class="pt-2">
                <a href="kontak.php" class="block text-center py-2.5 bg-blue-600 text-white rounded-xl font-bold">Minta Penawaran</a>
            </div>
        </div>
    </header>

    <!-- Global Search Modal Overlay -->
    <div id="kgsSearchModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs items-start justify-center pt-16 sm:pt-24 px-4" onclick="if(event.target===this)window.toggleKgsSearchModal(false)">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/></svg>
                    <span>Pencarian Cepat Website PT. KGS</span>
                </div>
                <button type="button" onclick="window.toggleKgsSearchModal(false)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold cursor-pointer">
                    ESC ✕
                </button>
            </div>
            <form action="cari.php" method="GET" class="p-4 sm:p-5 space-y-4">
                <div class="flex gap-2.5">
                    <input
                        id="kgsSearchModalInput"
                        type="search"
                        name="q"
                        required
                        minlength="2"
                        placeholder="Ketik layanan, paket harga, proyek, atau topik teknis..."
                        class="flex-1 px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <button type="submit" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer shrink-0">
                        Cari
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-400 font-medium mr-1">Pencarian populer:</span>
                    <?php foreach (['KGSNet', 'Fiber Optik', 'Tower Triangle', 'Penangkal Petir', 'CCTV', 'SLA 99.5%'] as $kw): ?>
                        <a href="cari.php?q=<?= urlencode($kw) ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition font-medium">
                            <?= e($kw) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>
    <script>
        window.toggleKgsSearchModal = function(show) {
            var modal = document.getElementById('kgsSearchModal');
            if (!modal) return;
            var shouldShow = typeof show === 'boolean' ? show : modal.classList.contains('hidden');
            if (shouldShow) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                var inp = document.getElementById('kgsSearchModalInput');
                if (inp) setTimeout(function() { inp.focus(); }, 40);
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                window.toggleKgsSearchModal(true);
            } else if (e.key === 'Escape') {
                window.toggleKgsSearchModal(false);
            }
        });
    </script>
    <main class="flex-1">
