import React, { useState, useEffect } from 'react';
import {
  COMPANY_INFO,
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME
} from '../data/companyData';
import { ColorMode } from '../utils/exportSync';
import { Download, Menu, X, ArrowRight, Clock, Sun, Moon } from 'lucide-react';

interface NavbarProps {
  currentView: 'public' | 'admin' | 'php-export' | 'pdf-audit';
  setCurrentView: (view: 'public' | 'admin' | 'php-export' | 'pdf-audit') => void;
  onOpenAudit: () => void;
  onOpenPhpExport: () => void;
  onOpenConsultation?: () => void;
  onOpenPage?: (slug: string) => void;
  companyLogo?: string;
  navigationMenus?: NavigationMenuItem[];
  companySettings?: CompanySettings;
  themeConfig?: WebsiteThemeConfig;
  colorMode?: ColorMode;
  isDarkMode?: boolean;
  onChangeColorMode?: (mode: ColorMode) => void;
}

export function resolveMenuUrlToSectionId(url: string): string {
  const clean = url.trim().toLowerCase();
  if (clean.includes('index.php') || clean.includes('beranda')) return 'beranda';
  if (clean.includes('tentang')) return 'tentang-kami';
  if (clean.includes('layanan')) return 'layanan';
  if (clean.includes('harga')) return 'harga';
  if (clean.includes('faq')) return 'faq';
  if (clean.includes('portofolio') || clean.includes('klien')) return 'portofolio';
  if (clean.includes('blog') || clean.includes('artikel')) return 'artikel';
  if (clean.includes('kontak') || clean.includes('konsultasi')) return 'kontak';
  if (clean.startsWith('#')) return clean.slice(1);
  return clean;
}

export const Navbar: React.FC<NavbarProps> = ({
  currentView,
  setCurrentView,
  onOpenPhpExport,
  onOpenConsultation,
  onOpenPage,
  companyLogo,
  navigationMenus = INITIAL_NAVIGATION_MENUS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  themeConfig = DEFAULT_WEBSITE_THEME,
  colorMode = 'light',
  isDarkMode = false,
  onChangeColorMode
}) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeSection, setActiveSection] = useState<string>('beranda');

  const headerMenus = [...navigationMenus]
    .filter(m => m.location === 'header' && m.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id);

  // Track active section on scroll
  useEffect(() => {
    if (currentView !== 'public') return;

    const handleScroll = () => {
      const scrollPosition = window.scrollY + 200;

      for (let i = headerMenus.length - 1; i >= 0; i--) {
        const item = headerMenus[i];
        const secId = resolveMenuUrlToSectionId(item.url);
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
  }, [currentView, headerMenus]);

  const handleMenuClick = (
    e: React.MouseEvent<HTMLAnchorElement>,
    item: NavigationMenuItem
  ) => {
    const rawUrl = item.url.trim();

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

    const sectionId = resolveMenuUrlToSectionId(rawUrl);
    if (sectionId === 'beranda') {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      setActiveSection('beranda');
      return;
    }

    const target = document.getElementById(sectionId);
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
      setActiveSection(sectionId);
    }
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
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
          <div className="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1 text-slate-300">
            <span>📍 Alamat Kantor: {companySettings.addressPalembang || COMPANY_INFO.officePalembang.address}</span>
            <span className="hidden md:inline text-slate-700">|</span>
            <span className="hidden md:inline">📞 {companySettings.phonePalembang || COMPANY_INFO.officePalembang.phone}</span>
          </div>
          <div className="flex flex-wrap items-center justify-center sm:justify-end gap-x-4 gap-y-1">
            <span className="text-emerald-400 font-semibold">💬 WA: {companySettings.whatsapp || COMPANY_INFO.officePalembang.whatsapp}</span>
            <span className="hidden sm:inline text-slate-700">|</span>
            <span className="text-slate-300 flex items-center gap-1.5">
              <Clock className="w-4 h-4 text-blue-400 shrink-0" />
              <span>
                {companySettings.operationalHours ||
                  DEFAULT_COMPANY_SETTINGS.operationalHours ||
                  'Senin - Sabtu: 08:30 - 17:00 WIB'}
              </span>
            </span>
          </div>
        </div>
      </div>

      {/* Main Sticky Header */}
      <header
        className={`backdrop-blur-md border-b sticky top-0 z-40 transition-colors duration-200 shadow-xs ${
          isDarkNavbar
            ? 'bg-slate-900/95 border-slate-800 text-white'
            : 'bg-white/95 border-slate-200 text-slate-900'
        }`}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[5.25rem] py-2.5 sm:py-3 flex items-center justify-between gap-2.5 sm:gap-4 xl:gap-6">
          
          {/* LEFT ZONE: Logo Brand (Visible on Mobile & Desktop) + Dynamic Header Menu */}
          <div className="flex items-center gap-3 sm:gap-5 xl:gap-7 min-w-0 flex-1">
            <a
              href="#beranda"
              onClick={e => {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setActiveSection('beranda');
              }}
              className="flex items-center gap-2.5 sm:gap-3 text-left cursor-pointer group min-w-0 shrink-0"
            >
              {companyLogo ? (
                <img
                  src={companyLogo}
                  alt="Logo PT. KGS"
                  className="h-10 sm:h-12 w-auto max-w-[115px] sm:max-w-[140px] object-contain rounded-xl shrink-0"
                />
              ) : (
                <div
                  style={{ backgroundColor: themeConfig.primaryColor }}
                  className="w-10 h-10 sm:w-12 sm:h-12 text-white font-black text-base sm:text-xl rounded-xl flex items-center justify-center shadow-sm transition-colors shrink-0"
                >
                  KGS
                </div>
              )}
              <div className="leading-snug block min-w-0 max-w-[175px] xs:max-w-[210px] sm:max-w-[240px] xl:max-w-none">
                <span
                  className={`font-extrabold text-sm sm:text-base xl:text-lg tracking-tight block leading-tight transition-colors ${
                    isDarkNavbar ? 'text-white' : 'text-slate-900'
                  }`}
                >
                  {companySettings.legalName || COMPANY_INFO.legalName}
                </span>
                <span
                  style={{ color: isDarkNavbar ? themeConfig.primaryTextAccent : themeConfig.primaryColor }}
                  className="text-xs font-semibold tracking-wide block leading-tight mt-0.5"
                >
                  {companySettings.slogan || COMPANY_INFO.slogan}
                </span>
              </div>
            </a>

            {/* Dynamic Header Navigation */}
            <nav
              className={`hidden xl:flex items-center gap-5 text-base font-semibold pl-5 border-l flex-wrap ${
                isDarkNavbar ? 'text-slate-200 border-slate-800' : 'text-slate-700 border-slate-200'
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
                    className={`transition-colors py-2 relative whitespace-nowrap cursor-pointer ${
                      isActive
                        ? 'font-bold'
                        : isDarkNavbar
                        ? 'text-slate-200 hover:text-white'
                        : 'text-slate-700 hover:text-slate-950'
                    }`}
                  >
                    {item.title}
                    {isActive && (
                      <span
                        style={{ backgroundColor: themeConfig.primaryColor }}
                        className="absolute bottom-0 left-0 right-0 h-0.5 rounded-full"
                      />
                    )}
                  </a>
                );
              })}
            </nav>
          </div>

          {/* RIGHT ZONE: Theme Toggle + Action Buttons */}
          <div className="flex items-center gap-2 sm:gap-2.5 shrink-0">
            {/* Dark / Light Mode Toggle Button */}
            {onChangeColorMode && (
              <button
                type="button"
                onClick={handleToggleQuickTheme}
                aria-label={isDarkMode ? 'Aktifkan Light Mode' : 'Aktifkan Dark Mode'}
                title={
                  isDarkMode
                    ? 'Mode Gelap Aktif — Klik untuk beralih ke Light Mode'
                    : 'Mode Terang Aktif — Klik untuk beralih ke Dark Mode'
                }
                className={`p-2.5 rounded-xl border transition-colors flex items-center gap-1.5 cursor-pointer ${
                  isDarkNavbar
                    ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-amber-300'
                    : 'bg-slate-100 hover:bg-slate-200 border-slate-200 text-slate-700'
                }`}
              >
                {isDarkMode ? (
                  <Sun className="w-5 h-5 text-amber-400 shrink-0" />
                ) : (
                  <Moon className="w-5 h-5 text-slate-700 shrink-0" />
                )}
                <span className="hidden lg:inline text-xs font-bold">
                  {isDarkMode ? 'Light' : 'Dark'}
                </span>
              </button>
            )}

            <button
              onClick={handleCtaClick}
              style={{ backgroundColor: themeConfig.primaryColor }}
              className="hidden sm:flex px-4 sm:px-5 py-2.5 hover:opacity-95 text-white text-base font-bold rounded-xl shadow-xs transition-colors items-center gap-2 cursor-pointer whitespace-nowrap"
            >
              <span>Konsultasi Sekarang</span>
              <ArrowRight className="w-4 h-4 shrink-0" />
            </button>

            <button
              onClick={onOpenPhpExport}
              className={`hidden md:inline-flex items-center gap-1.5 px-3.5 py-2.5 text-base font-semibold border rounded-xl transition-colors cursor-pointer whitespace-nowrap ${
                isDarkNavbar
                  ? 'text-slate-200 bg-slate-800 hover:bg-slate-700 border-slate-700'
                  : 'text-slate-700 bg-slate-100 hover:bg-slate-200 border-slate-200'
              }`}
              title="Unduh & Lihat Source Code PHP Native + MySQL"
            >
              <Download className="w-4 h-4 text-blue-500 shrink-0" />
              <span>Kode PHP</span>
            </button>

            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className={`xl:hidden p-2.5 rounded-xl border cursor-pointer focus:outline-none ${
                isDarkNavbar
                  ? 'text-slate-200 border-slate-700 hover:bg-slate-800'
                  : 'text-slate-700 border-slate-200 hover:text-slate-950 hover:bg-slate-100'
              }`}
              aria-label="Toggle navigation menu"
            >
              {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>

        {/* Mobile & Tablet Navigation Drawer */}
        {mobileMenuOpen && (
          <div
            className={`xl:hidden border-b px-4 sm:px-6 pt-3 pb-6 space-y-2 shadow-xl ${
              isDarkNavbar
                ? 'bg-slate-900 border-slate-800 text-white'
                : 'bg-white border-slate-200 text-slate-900'
            }`}
          >
            {/* Mobile Brand Header inside Drawer */}
            <div className="flex items-center justify-between pb-3 mb-2 border-b border-slate-200 dark:border-slate-800">
              <div className="flex items-center gap-3 min-w-0">
                {companyLogo ? (
                  <img
                    src={companyLogo}
                    alt="Logo PT. KGS"
                    className="h-10 w-auto max-w-[120px] object-contain rounded-lg shrink-0"
                  />
                ) : (
                  <div
                    style={{ backgroundColor: themeConfig.primaryColor }}
                    className="w-10 h-10 text-white font-black text-base rounded-lg flex items-center justify-center shrink-0"
                  >
                    KGS
                  </div>
                )}
                <div className="min-w-0">
                  <div className="font-extrabold text-sm leading-tight">
                    {companySettings.legalName || COMPANY_INFO.legalName}
                  </div>
                  <div className="text-xs text-blue-500 font-semibold">
                    {companySettings.slogan || COMPANY_INFO.slogan}
                  </div>
                </div>
              </div>
            </div>

            {/* Mobile Theme Mode Selector (Light / Dark) */}
            {onChangeColorMode && (
              <div className="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2">
                <span className="text-xs font-bold px-1">Tema Tampilan:</span>
                <div className="flex items-center gap-1">
                  <button
                    type="button"
                    onClick={() => onChangeColorMode('light')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 cursor-pointer transition ${
                      colorMode === 'light'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                    }`}
                  >
                    <Sun className="w-3.5 h-3.5" />
                    <span>Light</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => onChangeColorMode('dark')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 cursor-pointer transition ${
                      colorMode === 'dark'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                    }`}
                  >
                    <Moon className="w-3.5 h-3.5" />
                    <span>Dark</span>
                  </button>
                </div>
              </div>
            )}

            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 pt-2 pb-1">
              Navigasi Halaman
            </div>
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
    </>
  );
};
