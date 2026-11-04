import React, { useState, useEffect } from 'react';
import {
  COMPANY_INFO,
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME,
  ServiceItem,
  SERVICES_DATA,
  PricingPlanItem,
  B2B_PRICING_PLANS,
  ArticleItem,
  INITIAL_ARTICLES,
  ProjectPhoto,
  PROJECT_DOCUMENTARY,
  FaqItem,
  FAQ_DATA,
  StaticPageItem,
  INITIAL_PAGES
} from '../data/companyData';
import { ColorMode } from '../utils/exportSync';
import { Download, Menu, X, ArrowRight, Clock, Sun, Moon, Search } from 'lucide-react';

interface NavbarProps {
  currentView: 'public' | 'admin' | 'php-export' | 'pdf-audit';
  setCurrentView: (view: 'public' | 'admin' | 'php-export' | 'pdf-audit') => void;
  onOpenAudit: () => void;
  onOpenPhpExport: () => void;
  onOpenConsultation?: (serviceName?: string) => void;
  onOpenPage?: (slug: string) => void;
  onOpenFaqPage?: () => void;
  onResetSubViews?: () => void;
  activeRoute?: string;
  onNavigateRoute?: (route: string, anchorId?: string) => void;
  companyLogo?: string;
  navigationMenus?: NavigationMenuItem[];
  companySettings?: CompanySettings;
  themeConfig?: WebsiteThemeConfig;
  colorMode?: ColorMode;
  isDarkMode?: boolean;
  onChangeColorMode?: (mode: ColorMode) => void;
  services?: ServiceItem[];
  pricingPlans?: PricingPlanItem[];
  articles?: ArticleItem[];
  projects?: ProjectPhoto[];
  faqs?: FaqItem[];
  pages?: StaticPageItem[];
}

export function normalizeMenuItemUrl(title: string, rawUrl: string): string {
  const cleanUrl = (rawUrl || '').trim();
  const t = (title || '').trim().toLowerCase();

  // Prioritaskan URL kanonik untuk 7 menu utama Header & menu Footer agar selalu bisa diklik
  if (t === 'beranda' || t === 'home') return 'index.php';
  if (t === 'tentang kami' || t === 'tentang') return 'tentang.php';
  if (
    t === 'produk & layanan' ||
    t === 'produk dan layanan' ||
    t === 'product & service' ||
    t === 'product and service' ||
    t === 'layanan' ||
    t === 'katalog layanan'
  ) {
    return 'layanan.php';
  }
  if (t === 'promo' || t.includes('promo')) return 'promo.php';
  if (
    t === 'client / pelanggan' ||
    t === 'client & pelanggan' ||
    t === 'klien' ||
    t === 'pelanggan' ||
    t.includes('client') ||
    t.includes('pelanggan')
  ) {
    return 'klien.php';
  }
  if (t === 'kemitraan' || t.includes('kemitraan') || t === 'mitra') return 'kemitraan.php';
  if (t === 'testimoni' || t.includes('testimoni')) return 'testimoni.php';
  if (t === 'slide show' || t === 'slideshow') return 'slideshow.php';
  if (t === 'harga' || t === 'paket harga' || t.includes('3 segmen paket internet')) return 'harga.php';
  if (t === 'faq' || t.includes('pertanyaan umum')) return 'faq.php';
  if (t === 'portofolio' || t === 'portfolio') return 'portofolio.php';
  if (t === 'hubungi kami' || t === 'kontak') return 'kontak.php';
  if (t === 'profil perusahaan' || t === 'profile perusahaan') return 'page.php?slug=profil-perusahaan';
  if (t === 'visi & misi' || t.includes('visi') || t.includes('misi')) return 'page.php?slug=visi-misi-budaya';
  if (t === 'artikel edukasi' || t === 'artikel' || t === 'blog') return 'blog.php';
  if (t === 'kebijakan privasi' || t === 'kebijakan privasi & sla' || t.includes('kebijakan privasi')) return 'kebijakan-privasi.php';
  if (t.includes('minta penawaran') || t.includes('survei')) return 'kontak.php#konsultasi';
  if (t.includes('peta lokasi') || t.includes('google maps')) return 'kontak.php#peta-lokasi';

  if (cleanUrl && cleanUrl !== '#') {
    return cleanUrl;
  }

  if (t.includes('tentang')) return 'tentang.php';
  if (t.includes('produk') || t.includes('product') || t.includes('service') || t.includes('layanan')) return 'layanan.php';
  if (t.includes('harga') || t.includes('paket')) return 'harga.php';
  if (t.includes('faq') || t.includes('pertanyaan')) return 'faq.php';
  if (t.includes('portofolio') || t.includes('proyek')) return 'portofolio.php';
  if (t.includes('kontak') || t.includes('hubungi')) return 'kontak.php';
  if (t.includes('artikel') || t.includes('blog')) return 'blog.php';

  return cleanUrl || 'index.php';
}

export function resolveMenuUrlToSectionId(url: string, title?: string): string {
  const effectiveUrl = normalizeMenuItemUrl(title || '', url);
  const clean = effectiveUrl.trim().toLowerCase();
  if (clean.includes('#peta-lokasi')) return 'peta-lokasi';
  if (clean.includes('#konsultasi')) return 'konsultasi';
  if (clean.includes('index.php') || clean.includes('beranda')) return 'beranda';
  if (clean.includes('tentang')) return 'tentang-kami';
  if (clean.includes('layanan') || clean.includes('produk') || clean.includes('product')) return 'layanan';
  if (clean.includes('promo')) return 'promo';
  if (clean.includes('klien') || clean.includes('client') || clean.includes('pelanggan')) return 'klien';
  if (clean.includes('kemitraan') || clean.includes('mitra')) return 'kemitraan';
  if (clean.includes('testimoni')) return 'testimoni';
  if (clean.includes('slideshow')) return 'slideshow';
  if (clean.includes('harga') || clean.includes('paket')) return 'harga';
  if (clean.includes('faq')) return 'faq';
  if (clean.includes('portofolio')) return 'portofolio';
  if (clean.includes('blog') || clean.includes('artikel')) return 'artikel';
  if (clean.includes('kontak') || clean.includes('hubungi') || clean.includes('konsultasi')) return 'kontak';
  if (clean.startsWith('#')) return clean.slice(1);
  return clean;
}

export const KgsBrandLogo: React.FC<{
  companyLogo?: string;
  companySettings?: CompanySettings;
  isDarkNavbar?: boolean;
  themeConfig?: WebsiteThemeConfig;
}> = ({
  companyLogo,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  isDarkNavbar = false,
  themeConfig = DEFAULT_WEBSITE_THEME
}) => {
  const rawName = (companySettings.legalName || COMPANY_INFO.legalName).trim();
  const titleText =
    !rawName ||
    rawName.toLowerCase().includes('creatve') ||
    rawName.toLowerCase() === 'creative.co.id'
      ? 'PT. KREATIF GLOBAL SOLUSINDO'
      : rawName;

  const rawSubtitle = (
    companySettings.slogan ||
    COMPANY_INFO.slogan ||
    'System Integrator, Security Solution & Internet Provider'
  ).trim();
  const subtitleText =
    rawSubtitle.toLowerCase() === 'system integrator, security solution & internet service provider'
      ? 'System Integrator, Security Solution & Internet Provider'
      : rawSubtitle;

  const rawTitleColor = (companySettings.companyNameColor || '#0f172a').trim();
  const isDefaultDarkTitleColor =
    rawTitleColor.toLowerCase() === '#0f172a' ||
    rawTitleColor.toLowerCase() === '#1e293b' ||
    rawTitleColor.toLowerCase() === '#000000';
  const effectiveTitleColor =
    isDarkNavbar && isDefaultDarkTitleColor ? '#ffffff' : rawTitleColor;

  const rawSloganColor = (companySettings.sloganColor || themeConfig.primaryColor || '#2563eb').trim();
  const isDefaultSloganColor =
    rawSloganColor.toLowerCase() === '#2563eb' ||
    rawSloganColor.toLowerCase() === '#1d4ed8' ||
    rawSloganColor.toLowerCase() === '#0f172a';
  const effectiveSloganColor =
    isDarkNavbar && isDefaultSloganColor ? themeConfig.primaryTextAccent : rawSloganColor;

  const effectiveLogoBg = (companySettings.logoBgColor || themeConfig.primaryColor || '#2563eb').trim();
  const effectiveLogoText = (companySettings.logoTextColor || '#ffffff').trim();

  return (
    <div className="flex flex-col justify-center min-w-0">
      {/* Baris 1: Logo di Kiri + PT. KREATIF GLOBAL SOLUSINDO di Samping Logo */}
      <div className="flex flex-row items-center gap-2 sm:gap-2.5 min-w-0">
        {companyLogo ? (
          <img
            src={companyLogo}
            alt="Logo PT. KGS"
            data-live-brand-logo="true"
            className={`h-7 w-7 sm:h-10 sm:w-auto max-w-[34px] sm:max-w-[120px] object-contain rounded-lg shrink-0 ${
              isDarkNavbar ? 'bg-white p-0.5' : 'bg-white p-0.5 border border-slate-200/80 shadow-2xs'
            }`}
          />
        ) : (
          <div
            data-live-brand-logo="true"
            style={{ backgroundColor: effectiveLogoBg, color: effectiveLogoText }}
            className="w-7 h-7 sm:w-9 sm:h-9 font-black text-[10px] sm:text-sm rounded-lg flex items-center justify-center shadow-sm transition-colors shrink-0 border border-black/10 dark:border-white/15"
          >
            KGS
          </div>
        )}

        <span
          data-live-brand-title="true"
          style={{ color: effectiveTitleColor }}
          className="font-extrabold text-[13.5px] sm:text-lg md:text-xl tracking-tight leading-none whitespace-nowrap transition-colors"
        >
          {titleText}
        </span>
      </div>

      {/* Baris 2 (Di Bawah Logo & PT. KREATIF GLOBAL SOLUSINDO): System Integrator, Security Solution & Internet Provider (1 Baris) */}
      <span
        data-live-brand-slogan="true"
        style={{ color: effectiveSloganColor }}
        className="text-[10.5px] sm:text-xs md:text-sm font-semibold tracking-tight sm:tracking-wide block leading-tight mt-1 whitespace-nowrap transition-colors"
      >
        {subtitleText}
      </span>
    </div>
  );
};

export const Navbar: React.FC<NavbarProps> = ({
  currentView,
  setCurrentView,
  onOpenPhpExport,
  onOpenConsultation,
  onOpenPage,
  onOpenFaqPage,
  onResetSubViews,
  activeRoute = 'beranda',
  onNavigateRoute,
  companyLogo,
  navigationMenus = INITIAL_NAVIGATION_MENUS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  themeConfig = DEFAULT_WEBSITE_THEME,
  colorMode = 'light',
  isDarkMode = false,
  onChangeColorMode,
  services = SERVICES_DATA,
  pricingPlans = B2B_PRICING_PLANS,
  articles = INITIAL_ARTICLES,
  projects = PROJECT_DOCUMENTARY,
  faqs = FAQ_DATA,
  pages = INITIAL_PAGES
}) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeSection, setActiveSection] = useState<string>(activeRoute || 'beranda');
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    if (activeRoute) {
      setActiveSection(activeRoute);
    }
  }, [activeRoute]);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setIsSearchOpen(true);
      } else if (e.key === 'Escape') {
        setIsSearchOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const normalizedQuery = searchQuery.trim().toLowerCase();

  const matchedServices = normalizedQuery.length >= 2
    ? services.filter(
        s =>
          s.isPublished !== false &&
          (s.name.toLowerCase().includes(normalizedQuery) ||
            s.summary.toLowerCase().includes(normalizedQuery) ||
            s.description.toLowerCase().includes(normalizedQuery))
      ).slice(0, 6)
    : [];

  const matchedPricing = normalizedQuery.length >= 2
    ? pricingPlans.filter(
        p =>
          p.name.toLowerCase().includes(normalizedQuery) ||
          p.subtitle.toLowerCase().includes(normalizedQuery) ||
          p.targetAudience.toLowerCase().includes(normalizedQuery) ||
          p.features.some(f => f.toLowerCase().includes(normalizedQuery))
      ).slice(0, 4)
    : [];

  const matchedArticles = normalizedQuery.length >= 2
    ? articles.filter(
        a =>
          a.status === 'published' &&
          (a.title.toLowerCase().includes(normalizedQuery) ||
            a.summary.toLowerCase().includes(normalizedQuery) ||
            a.categoryName.toLowerCase().includes(normalizedQuery))
      ).slice(0, 4)
    : [];

  const matchedProjects = normalizedQuery.length >= 2
    ? projects.filter(
        pr =>
          pr.title.toLowerCase().includes(normalizedQuery) ||
          pr.clientOrLocation.toLowerCase().includes(normalizedQuery) ||
          pr.category.toLowerCase().includes(normalizedQuery) ||
          pr.description.toLowerCase().includes(normalizedQuery)
      ).slice(0, 4)
    : [];

  const matchedFaqs = normalizedQuery.length >= 2
    ? faqs.filter(
        f =>
          f.question.toLowerCase().includes(normalizedQuery) ||
          f.answer.toLowerCase().includes(normalizedQuery) ||
          f.category.toLowerCase().includes(normalizedQuery)
      ).slice(0, 4)
    : [];

  const matchedPages = normalizedQuery.length >= 2
    ? pages.filter(
        pg =>
          pg.isPublished !== false &&
          (pg.title.toLowerCase().includes(normalizedQuery) ||
            pg.summary.toLowerCase().includes(normalizedQuery))
      ).slice(0, 4)
    : [];

  const totalSearchMatches =
    matchedServices.length +
    matchedPricing.length +
    matchedArticles.length +
    matchedProjects.length +
    matchedFaqs.length +
    matchedPages.length;

  const navigateToSection = (sectionId: string) => {
    setIsSearchOpen(false);
    setMobileMenuOpen(false);
    if (currentView !== 'public') {
      setCurrentView('public');
    }
    if (sectionId === 'faq' && onOpenFaqPage) {
      onOpenFaqPage();
      setActiveSection('faq');
      return;
    }
    if (onNavigateRoute) {
      const anchor =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? sectionId : undefined;
      const route =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? 'kontak' : sectionId;
      onNavigateRoute(route, anchor);
      setActiveSection(route);
      return;
    }
    if (onResetSubViews) {
      onResetSubViews();
    }
    setTimeout(() => {
      if (sectionId === 'beranda') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setActiveSection('beranda');
        return;
      }
      const el = document.getElementById(sectionId);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
        setActiveSection(sectionId);
      }
    }, 50);
  };

  const headerMenus = (() => {
    const seen = new Set<string>();
    const rawHeader = [...navigationMenus].filter(m => m.location === 'header' && m.isActive);
    const sourceList =
      rawHeader.length > 0
        ? rawHeader
        : INITIAL_NAVIGATION_MENUS.filter(m => m.location === 'header');
    const list = sourceList
      .map(m => {
        const cleanTitle =
          m.title === 'Layanan' || m.title === 'Katalog Layanan'
            ? 'Product & Service'
            : (m.title || '').trim();
        return {
          ...m,
          title: cleanTitle,
          url: normalizeMenuItemUrl(cleanTitle, m.url)
        };
      })
      .filter(m => m.title !== '')
      .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id)
      .filter(m => {
        const key = m.title.toLowerCase();
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
      });
    for (const def of INITIAL_NAVIGATION_MENUS.filter(m => m.location === 'header')) {
      const key = def.title.toLowerCase();
      if (!seen.has(key)) {
        seen.add(key);
        list.push(def);
      }
    }
    return list;
  })();

  // Track active section on scroll only when on Beranda
  useEffect(() => {
    if (currentView !== 'public' || (activeRoute && activeRoute !== 'beranda')) return;

    const handleScroll = () => {
      const scrollPosition = window.scrollY + 220;

      for (let i = headerMenus.length - 1; i >= 0; i--) {
        const item = headerMenus[i];
        const secId = resolveMenuUrlToSectionId(item.url, item.title);
        const section = document.getElementById(secId);
        if (section) {
          const top = section.offsetTop;
          if (scrollPosition >= top) {
            setActiveSection(secId);
            break;
          }
        }
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, [currentView, headerMenus, activeRoute]);

  const handleMenuClick = (
    e: React.MouseEvent<HTMLAnchorElement>,
    item: NavigationMenuItem
  ) => {
    const rawUrl = normalizeMenuItemUrl(item.title, item.url);

    // External URL
    if (/^https?:\/\//i.test(rawUrl)) {
      setMobileMenuOpen(false);
      return;
    }

    e.preventDefault();
    if (currentView !== 'public') {
      setCurrentView('public');
    }
    setMobileMenuOpen(false);

    // Static page slug (e.g. page.php?slug=profil-perusahaan or kebijakan-privasi.php)
    if (rawUrl.includes('page.php?slug=')) {
      const slug = rawUrl.split('page.php?slug=')[1]?.split('&')[0];
      if (slug && onOpenPage) {
        onOpenPage(slug);
        return;
      }
    }
    if (rawUrl.includes('kebijakan-privasi.php') && onOpenPage) {
      onOpenPage('kebijakan-privasi');
      return;
    }

    const sectionId = resolveMenuUrlToSectionId(rawUrl, item.title);
    if (sectionId === 'faq' && onOpenFaqPage) {
      onOpenFaqPage();
      setActiveSection('faq');
      return;
    }

    if (onNavigateRoute) {
      const anchor =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? sectionId : undefined;
      const route =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? 'kontak' : sectionId;
      onNavigateRoute(route, anchor);
      setActiveSection(route);
      return;
    }

    if (onResetSubViews) {
      onResetSubViews();
    }

    setTimeout(() => {
      if (sectionId === 'beranda') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setActiveSection('beranda');
        return;
      }

      const target =
        document.getElementById(sectionId) ||
        (sectionId === 'peta-lokasi' || sectionId === 'konsultasi'
          ? document.getElementById('kontak')
          : null);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        setActiveSection(sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? 'kontak' : sectionId);
      }
    }, 30);
  };

  const handleCtaClick = () => {
    setMobileMenuOpen(false);
    if (currentView !== 'public') setCurrentView('public');
    if (onOpenConsultation) {
      onOpenConsultation();
    } else {
      const el = document.getElementById('kontak');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleToggleQuickTheme = () => {
    if (!onChangeColorMode) return;
    onChangeColorMode(isDarkMode ? 'light' : 'dark');
  };

  const isDarkNavbar = isDarkMode || themeConfig.navbarStyle === 'dark';

  return (
    <>
      {/* Top Utility Contact Bar */}
      <div className="bg-slate-900 text-slate-300 text-xs py-2.5 border-b border-slate-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap justify-between items-center gap-2">
          <div className="flex flex-wrap items-center gap-3 text-slate-300">
            <span className="px-2 py-0.5 bg-blue-600/20 text-blue-400 border border-blue-500/30 rounded font-semibold">
              📍 {companySettings.addressPalembang || COMPANY_INFO.officePalembang.address}
            </span>
            <span className="hidden lg:inline text-slate-400">
              System Integrator, Security Solution &amp; ISP Resmi APJII (KGSNet)
            </span>
          </div>
          <div className="flex flex-wrap items-center gap-4 font-mono">
            <a href="tel:0711316088" className="hover:text-white transition">
              📞 {companySettings.phonePalembang || COMPANY_INFO.officePalembang.phone}
            </a>
            <a
              href={`mailto:${companySettings.email || COMPANY_INFO.officePalembang.email}`}
              className="hover:text-white transition"
            >
              ✉️ {companySettings.email || COMPANY_INFO.officePalembang.email}
            </a>
            <span className="hidden sm:inline text-slate-700">|</span>
            <span className="hidden sm:flex items-center gap-1.5 text-slate-300 font-sans">
              <Clock className="w-3.5 h-3.5 text-blue-400 shrink-0" />
              <span>
                {companySettings.operationalHours ||
                  DEFAULT_COMPANY_SETTINGS.operationalHours ||
                  'Senin - Sabtu: 08:30 - 17:00 WIB'}
              </span>
            </span>
            <span className="hidden sm:inline text-slate-700">|</span>
            <button
              type="button"
              onClick={() => setCurrentView('admin')}
              className="px-2 py-0.5 rounded bg-slate-800 hover:bg-blue-600 text-slate-300 hover:text-white border border-slate-700 font-sans font-semibold text-[11px] transition cursor-pointer"
            >
              🔒 Admin CMS
            </button>
          </div>
        </div>
      </div>

      {/* Main Sticky Header: 2-Tier Architecture (Brand Bar di Atas Menu, Menu Bar di Bawahnya) */}
      <header
        className={`backdrop-blur-md border-b sticky top-0 z-40 transition-colors duration-200 shadow-xs ${
          isDarkNavbar
            ? 'bg-slate-900/95 border-slate-800 text-white'
            : 'bg-white/95 border-slate-200 text-slate-900'
        }`}
      >
        {/* TIER 1 (DI ATAS MENU): Brand Identity di Atas & Quick Actions (Cari, Light/Dark, Konsultasi + Garis Tiga Menu) di Bawahnya pada Versi Mobile */}
        <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-1.5 sm:py-2.5 lg:py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-1.5 sm:gap-2 lg:gap-4">
          {/* BARIS ATAS MOBILE / KIRI DESKTOP: Logo + PT. KREATIF GLOBAL SOLUSINDO */}
          <a
            href="index.php"
            onClick={e => {
              e.preventDefault();
              if (onResetSubViews) onResetSubViews();
              window.scrollTo({ top: 0, behavior: 'smooth' });
              setActiveSection('beranda');
            }}
            className="flex items-center text-left cursor-pointer group w-full lg:w-auto lg:flex-1 min-w-0"
          >
            <KgsBrandLogo
              companyLogo={companyLogo}
              companySettings={companySettings}
              isDarkNavbar={isDarkNavbar}
              themeConfig={themeConfig}
            />
          </a>

          {/* BARIS BAWAH MOBILE / KANAN DESKTOP: Icon Pencarian + Mode Light/Dark + Konsultasi + Tombol Garis Tiga Menu di Samping Konsultasi */}
          <div
            className={`w-full lg:w-auto pt-1.5 lg:pt-0 border-t lg:border-t-0 flex items-center justify-between lg:justify-end gap-1.5 sm:gap-2 shrink-0 ${
              isDarkNavbar ? 'border-slate-800/80' : 'border-slate-200/70'
            }`}
          >
            <div className="flex items-center gap-1 sm:gap-2">
              <button
                type="button"
                onClick={() => setIsSearchOpen(true)}
                aria-label="Cari layanan, paket harga, proyek, atau artikel"
                title="Pencarian Cepat Website (Ctrl+K)"
                className={`flex px-2 py-1 sm:px-3 sm:py-1.5 rounded-lg sm:rounded-xl border transition-colors items-center gap-1 sm:gap-1.5 cursor-pointer ${
                  isDarkNavbar
                    ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-200'
                    : 'bg-slate-100 hover:bg-slate-200 border-slate-200 text-slate-700'
                }`}
              >
                <Search className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-500 shrink-0" />
                <span className="text-[11px] sm:text-xs font-bold">Cari</span>
              </button>

              {onChangeColorMode && (
                <div
                  className={`inline-flex items-center rounded-lg sm:rounded-xl p-0.5 border ${
                    isDarkNavbar
                      ? 'bg-slate-800 border-slate-700'
                      : 'bg-slate-100 border-slate-200'
                  }`}
                >
                  <button
                    type="button"
                    onClick={() => onChangeColorMode('light')}
                    aria-label="Light Mode"
                    title="Light Mode (Terang)"
                    className={`px-1.5 sm:px-2 py-1 rounded-md sm:rounded-lg transition-colors flex items-center gap-1 text-[11px] sm:text-xs font-bold cursor-pointer ${
                      !isDarkMode
                        ? 'bg-blue-600 text-white shadow-2xs'
                        : 'text-slate-400 hover:text-white'
                    }`}
                  >
                    <Sun className="w-3.5 h-3.5 shrink-0" />
                    <span>Light</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => onChangeColorMode('dark')}
                    aria-label="Dark Mode"
                    title="Dark Mode (Gelap)"
                    className={`px-1.5 sm:px-2 py-1 rounded-md sm:rounded-lg transition-colors flex items-center gap-1 text-[11px] sm:text-xs font-bold cursor-pointer ${
                      isDarkMode
                        ? 'bg-blue-600 text-white shadow-2xs'
                        : 'text-slate-600 hover:text-slate-900'
                    }`}
                  >
                    <Moon className="w-3.5 h-3.5 shrink-0" />
                    <span>Dark</span>
                  </button>
                </div>
              )}
            </div>

            <div className="flex items-center gap-1.5 sm:gap-2">
              <button
                onClick={handleCtaClick}
                style={{ backgroundColor: themeConfig.primaryColor }}
                className="flex px-2.5 sm:px-4 py-1 sm:py-2 hover:opacity-95 text-white text-[11px] sm:text-sm font-bold rounded-lg sm:rounded-xl shadow-xs transition-colors items-center gap-1 sm:gap-1.5 cursor-pointer whitespace-nowrap"
              >
                <span>Konsultasi</span>
                <ArrowRight className="w-3 h-3 sm:w-4 sm:h-4 shrink-0" />
              </button>

              <button
                onClick={onOpenPhpExport}
                className={`hidden md:inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-semibold border rounded-xl transition-colors cursor-pointer whitespace-nowrap ${
                  isDarkNavbar
                    ? 'text-slate-200 bg-slate-800 hover:bg-slate-700 border-slate-700'
                    : 'text-slate-700 bg-slate-100 hover:bg-slate-200 border-slate-200'
                }`}
                title="Unduh & Lihat Source Code PHP Native + MySQL"
              >
                <Download className="w-4 h-4 text-blue-500 shrink-0" />
                <span>Kode PHP</span>
              </button>

              {/* Tombol Garis Tiga (Hamburger Menu) tepat di samping Konsultasi pada Mobile */}
              <button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className={`lg:hidden p-1.5 sm:p-2 rounded-lg sm:rounded-xl border cursor-pointer focus:outline-none shrink-0 flex items-center justify-center ${
                  isDarkNavbar
                    ? 'text-slate-200 border-slate-700 bg-slate-800 hover:bg-slate-700'
                    : 'text-slate-700 border-slate-200 bg-slate-100 hover:text-slate-950 hover:bg-slate-200'
                }`}
                aria-label="Toggle navigation menu"
                title="Menu Navigasi"
              >
                {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
              </button>
            </div>
          </div>
        </div>

        {/* TIER 2 (DI BAWAH BRAND): Dedicated Navigation Menu Bar (Hanya Desktop lg+, di Mobile Disembunyikan di Tombol Garis Tiga) */}
        <div
          className={`hidden lg:block border-t ${
            isDarkNavbar
              ? 'border-slate-800/80 bg-slate-950/40'
              : 'border-slate-100 bg-slate-50/70'
          }`}
        >
          <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <nav
              className={`flex items-center gap-1 xl:gap-2 text-xs sm:text-sm font-semibold py-1.5 overflow-x-auto no-scrollbar ${
                isDarkNavbar ? 'text-slate-200' : 'text-slate-700'
              }`}
            >
              {headerMenus.map(item => {
                const secId = resolveMenuUrlToSectionId(item.url);
                const isActive = activeSection === secId;
                return (
                  <a
                    key={item.id}
                    href={item.url}
                    target={item.target === '_blank' ? '_blank' : undefined}
                    rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                    onClick={e => handleMenuClick(e, item)}
                    style={
                      isActive
                        ? { color: isDarkNavbar ? themeConfig.primaryTextAccent : themeConfig.primaryColor }
                        : undefined
                    }
                    className={`px-3.5 py-1.5 rounded-lg transition-colors relative whitespace-nowrap cursor-pointer ${
                      isActive
                        ? isDarkNavbar
                          ? 'font-bold bg-slate-800/90'
                          : 'font-bold bg-blue-50/90'
                        : isDarkNavbar
                        ? 'text-slate-200 hover:text-white hover:bg-slate-800/60'
                        : 'text-slate-700 hover:text-slate-950 hover:bg-slate-200/60'
                    }`}
                  >
                    {item.title}
                    {isActive && (
                      <span
                        style={{ backgroundColor: themeConfig.primaryColor }}
                        className="absolute bottom-0 left-2 right-2 h-0.5 rounded-full"
                      />
                    )}
                  </a>
                );
              })}
            </nav>
          </div>
        </div>

        {/* Mobile & Tablet Navigation Drawer */}
        {mobileMenuOpen && (
          <div
            id="mobileMenu"
            data-live-nav="true"
            className={`lg:hidden border-b px-4 sm:px-6 pt-3 pb-6 space-y-2 shadow-xl ${
              isDarkNavbar
                ? 'bg-slate-900 border-slate-800 text-white'
                : 'bg-white border-slate-200 text-slate-900'
            }`}
          >
            {/* Mobile Brand Header inside Drawer (Logo sebelah kiri PT. KREATIF GLOBAL SOLUSINDO dengan warna custom) */}
            <div className="flex items-center justify-between pb-3 mb-2 border-b border-slate-200 dark:border-slate-800">
              <KgsBrandLogo
                companyLogo={companyLogo}
                companySettings={companySettings}
                isDarkNavbar={isDarkNavbar}
                themeConfig={themeConfig}
              />
            </div>

            {/* Mobile Quick Search Trigger inside Drawer */}
            <button
              type="button"
              onClick={() => {
                setMobileMenuOpen(false);
                setIsSearchOpen(true);
              }}
              className="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:border-blue-500 transition"
            >
              <span className="flex items-center gap-2.5">
                <Search className="w-4 h-4 text-blue-500 shrink-0" />
                <span>Cari layanan, harga, artikel, proyek...</span>
              </span>
              <span className="px-2 py-0.5 rounded-md bg-blue-600 text-white text-[10px] font-bold shrink-0">
                🔍 Cari
              </span>
            </button>

            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 pt-2 pb-1">
              Navigasi Halaman
            </div>
            {headerMenus.map(item => {
              const secId = resolveMenuUrlToSectionId(item.url, item.title);
              const isActive = activeSection === secId;
              return (
                <a
                  key={item.id}
                  href={item.url}
                  target={item.target === '_blank' ? '_blank' : undefined}
                  rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                  onClick={e => handleMenuClick(e, item)}
                  className={`block px-4 py-3 rounded-xl text-base font-semibold transition ${
                    isActive
                      ? 'bg-blue-50 text-blue-700 font-bold'
                      : 'text-slate-700 hover:bg-slate-50 hover:text-blue-600'
                  }`}
                >
                  {item.title}
                </a>
              );
            })}

            <div className="pt-4 border-t border-slate-100 space-y-2.5">
              <button
                onClick={handleCtaClick}
                className="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl flex items-center justify-center gap-2 cursor-pointer shadow-xs"
              >
                <span>Konsultasi Sekarang</span>
                <ArrowRight className="w-4 h-4" />
              </button>

              <div className="pt-1">
                <button
                  onClick={() => {
                    setMobileMenuOpen(false);
                    onOpenPhpExport();
                  }}
                  className="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 text-base font-semibold rounded-xl flex items-center justify-center gap-2 cursor-pointer"
                >
                  <Download className="w-4 h-4 text-blue-600" />
                  <span>Kode PHP + SQL</span>
                </button>
              </div>
            </div>
          </div>
        )}
      </header>

      {/* Global Search Modal Overlay */}
      {isSearchOpen && (
        <div
          className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-start justify-center pt-14 sm:pt-20 px-4"
          onClick={e => {
            if (e.target === e.currentTarget) setIsSearchOpen(false);
          }}
        >
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[82vh] flex flex-col">
            <div className="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3">
              <Search className="w-5 h-5 text-blue-600 shrink-0" />
              <input
                type="search"
                autoFocus
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                placeholder="Cari layanan, paket harga B2B, proyek, FAQ, atau artikel teknis..."
                className="flex-1 bg-transparent text-slate-900 dark:text-white text-sm sm:text-base font-medium focus:outline-none placeholder-slate-400"
              />
              {searchQuery && (
                <button
                  type="button"
                  onClick={() => setSearchQuery('')}
                  className="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 px-2 py-1 cursor-pointer"
                >
                  Hapus
                </button>
              )}
              <button
                type="button"
                onClick={() => setIsSearchOpen(false)}
                className="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold cursor-pointer"
              >
                ESC ✕
              </button>
            </div>

            <div className="p-4 sm:p-5 overflow-y-auto space-y-5 text-xs">
              {/* Quick Keyword Suggestions */}
              <div className="flex flex-wrap items-center gap-1.5">
                <span className="text-slate-400 font-semibold mr-1">Kata kunci cepat:</span>
                {['KGSNet', 'Fiber Optik', 'Tower', 'Penangkal Petir', 'CCTV', 'Grounding', 'AIS'].map(kw => (
                  <button
                    key={kw}
                    type="button"
                    onClick={() => setSearchQuery(kw)}
                    className="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition font-semibold cursor-pointer"
                  >
                    {kw}
                  </button>
                ))}
              </div>

              {normalizedQuery.length > 0 && normalizedQuery.length < 2 && (
                <div className="py-6 text-center text-slate-400">
                  Ketik minimal 2 karakter untuk mulai mencari...
                </div>
              )}

              {normalizedQuery.length >= 2 && totalSearchMatches === 0 && (
                <div className="py-8 text-center space-y-2">
                  <div className="text-sm font-bold text-slate-800 dark:text-slate-200">
                    Tidak ditemukan hasil untuk "{searchQuery}"
                  </div>
                  <p className="text-slate-500">
                    Coba kata kunci lain seperti Internet, Tower, CCTV, atau hubungi tim teknis kami.
                  </p>
                </div>
              )}

              {normalizedQuery.length >= 2 && totalSearchMatches > 0 && (
                <div className="space-y-5">
                  <div className="text-slate-500 font-semibold">
                    Menampilkan <span className="text-blue-600 font-extrabold">{totalSearchMatches}</span> hasil pencarian untuk "{searchQuery}"
                  </div>

                  {matchedServices.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Katalog Layanan & Solusi ({matchedServices.length})
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {matchedServices.map(srv => (
                          <button
                            key={srv.id}
                            type="button"
                            onClick={() => navigateToSection('layanan')}
                            className="text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="font-bold text-slate-900 dark:text-white text-xs mb-1">
                              {srv.name}
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {srv.summary}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {matchedPricing.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Paket Harga B2B ({matchedPricing.length})
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {matchedPricing.map(pkg => (
                          <button
                            key={pkg.id}
                            type="button"
                            onClick={() => navigateToSection('harga')}
                            className="text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="flex items-center justify-between gap-2 mb-1">
                              <span className="font-bold text-slate-900 dark:text-white text-xs">{pkg.name}</span>
                              <span className="text-blue-600 font-bold text-[11px]">{pkg.sla}</span>
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {pkg.subtitle}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {matchedArticles.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Artikel & Wawasan Teknis ({matchedArticles.length})
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {matchedArticles.map(art => (
                          <button
                            key={art.id}
                            type="button"
                            onClick={() => navigateToSection('artikel')}
                            className="text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="font-bold text-slate-900 dark:text-white text-xs mb-1">
                              {art.title}
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {art.summary}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {matchedProjects.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Portofolio Proyek ({matchedProjects.length})
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {matchedProjects.map(pr => (
                          <button
                            key={pr.id}
                            type="button"
                            onClick={() => navigateToSection('portofolio')}
                            className="text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="font-bold text-slate-900 dark:text-white text-xs mb-0.5">
                              {pr.title}
                            </div>
                            <div className="text-blue-600 font-semibold text-[11px] mb-1">
                              {pr.clientOrLocation}
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {pr.description}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {matchedFaqs.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Tanya Jawab / FAQ ({matchedFaqs.length})
                      </div>
                      <div className="space-y-2">
                        {matchedFaqs.map(f => (
                          <button
                            key={f.id}
                            type="button"
                            onClick={() => navigateToSection('faq')}
                            className="w-full text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="font-bold text-slate-900 dark:text-white text-xs mb-1">
                              {f.question}
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {f.answer}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {matchedPages.length > 0 && (
                    <div className="space-y-2">
                      <div className="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Halaman Website ({matchedPages.length})
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {matchedPages.map(pg => (
                          <button
                            key={pg.id}
                            type="button"
                            onClick={() => {
                              setIsSearchOpen(false);
                              const secId = resolveMenuUrlToSectionId(pg.slug);
                              const el = document.getElementById(secId);
                              if (el) {
                                navigateToSection(secId);
                              } else if (onOpenPage) {
                                onOpenPage(pg.slug);
                              }
                            }}
                            className="text-left p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-500 bg-slate-50/70 dark:bg-slate-800/60 transition cursor-pointer"
                          >
                            <div className="font-bold text-slate-900 dark:text-white text-xs mb-1">
                              {pg.title}
                            </div>
                            <div className="text-slate-500 dark:text-slate-400 line-clamp-2 text-[11px]">
                              {pg.summary}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
};
