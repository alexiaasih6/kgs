import React, { useState } from 'react';
import {
  COMPANY_INFO,
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME,
  CertificationItem,
  VERIFIED_CERTIFICATIONS,
  parseFooterCertBadges,
  getEffectiveSocialLinks
} from '../data/companyData';
import { resolveMenuUrlToSectionId, normalizeMenuItemUrl } from './Navbar';
import { Download, Edit3, ShieldCheck, ExternalLink, Globe, Check, X } from 'lucide-react';

interface FooterProps {
  onOpenAudit: () => void;
  onOpenPhpExport: () => void;
  onOpenAdmin: () => void;
  onOpenPage?: (slug: string) => void;
  onOpenFaqPage?: () => void;
  onResetSubViews?: () => void;
  onNavigateRoute?: (route: string, anchorId?: string) => void;
  companyLogo?: string;
  navigationMenus?: NavigationMenuItem[];
  companySettings?: CompanySettings;
  onUpdateCompanySettings?: (next: CompanySettings) => void;
  certifications?: CertificationItem[];
  themeConfig?: WebsiteThemeConfig;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab?: string) => void;
}

const renderSocialPlatformIcon = (platformName: string) => {
  const p = (platformName || '').toLowerCase();
  if (p.includes('facebook') || p === 'fb') {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
      </svg>
    );
  }
  if (p.includes('instagram') || p === 'ig') {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
      </svg>
    );
  }
  if (p.includes('linkedin')) {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" />
      </svg>
    );
  }
  if (p.includes('youtube') || p === 'yt') {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
      </svg>
    );
  }
  if (p.includes('tiktok')) {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
      </svg>
    );
  }
  if (p.includes('whatsapp') || p === 'wa') {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.57.12.17 1.72 2.75 4.25 3.74 2.1.83 2.52.66 2.98.62.46-.04 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.22-.16-.47-.28-.25-.12-1.47-.73-1.7-.81-.23-.08-.39-.12-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.84-.2-.48-.4-.41-.56-.41z" />
      </svg>
    );
  }
  if (p.includes('twitter') || p === 'x') {
    return (
      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
      </svg>
    );
  }
  return <Globe className="w-4 h-4" />;
};

const getSocialHoverClass = (platformName: string) => {
  const p = (platformName || '').toLowerCase();
  if (p.includes('facebook') || p === 'fb') return 'hover:bg-blue-600 hover:border-blue-500 hover:text-white';
  if (p.includes('instagram') || p === 'ig') return 'hover:bg-pink-600 hover:border-pink-500 hover:text-white';
  if (p.includes('linkedin')) return 'hover:bg-sky-600 hover:border-sky-500 hover:text-white';
  if (p.includes('youtube') || p === 'yt') return 'hover:bg-red-600 hover:border-red-500 hover:text-white';
  if (p.includes('whatsapp') || p === 'wa') return 'hover:bg-emerald-600 hover:border-emerald-500 hover:text-white';
  return 'hover:bg-slate-700 hover:border-slate-500 hover:text-white';
};

export const Footer: React.FC<FooterProps> = ({
  onOpenPhpExport,
  onOpenAdmin,
  onOpenPage,
  onOpenFaqPage,
  onResetSubViews,
  onNavigateRoute,
  companyLogo,
  navigationMenus = INITIAL_NAVIGATION_MENUS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  onUpdateCompanySettings,
  certifications = VERIFIED_CERTIFICATIONS,
  themeConfig = DEFAULT_WEBSITE_THEME,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [isEditingSocials, setIsEditingSocials] = useState(false);
  const [socialDraft, setSocialDraft] = useState(() => ({
    facebook: companySettings.socialFacebook || 'https://facebook.com/kgs.palembang',
    instagram: companySettings.socialInstagram || 'https://instagram.com/kgs.palembang',
    linkedin: companySettings.socialLinkedin || 'https://linkedin.com/company/kreatif-global-solusindo',
    youtube: companySettings.socialYoutube || 'https://youtube.com/@kreatifglobalsolusindo',
    tiktok: companySettings.socialTiktok || 'https://tiktok.com/@kgs.palembang',
    whatsapp: companySettings.socialWhatsapp || 'https://wa.me/62811782119'
  }));

  const openSocialLiveEditor = () => {
    const effective = getEffectiveSocialLinks(companySettings);
    const findUrl = (plat: string, fallback: string) =>
      effective.find(s => s.platform.toLowerCase().includes(plat))?.url || fallback;
    setSocialDraft({
      facebook: findUrl('facebook', 'https://facebook.com/kgs.palembang'),
      instagram: findUrl('instagram', 'https://instagram.com/kgs.palembang'),
      linkedin: findUrl('linkedin', 'https://linkedin.com/company/kreatif-global-solusindo'),
      youtube: findUrl('youtube', 'https://youtube.com/@kreatifglobalsolusindo'),
      tiktok: findUrl('tiktok', 'https://tiktok.com/@kgs.palembang'),
      whatsapp: findUrl('whatsapp', 'https://wa.me/62811782119')
    });
    setIsEditingSocials(true);
  };

  const handleSaveSocialLiveEditor = (e: React.FormEvent) => {
    e.preventDefault();
    const updatedLinks = [
      { id: 'soc-fb', platform: 'Facebook', url: socialDraft.facebook.trim(), isActive: Boolean(socialDraft.facebook.trim()), sortOrder: 1 },
      { id: 'soc-ig', platform: 'Instagram', url: socialDraft.instagram.trim(), isActive: Boolean(socialDraft.instagram.trim()), sortOrder: 2 },
      { id: 'soc-in', platform: 'LinkedIn', url: socialDraft.linkedin.trim(), isActive: Boolean(socialDraft.linkedin.trim()), sortOrder: 3 },
      { id: 'soc-yt', platform: 'YouTube', url: socialDraft.youtube.trim(), isActive: Boolean(socialDraft.youtube.trim()), sortOrder: 4 },
      { id: 'soc-tt', platform: 'TikTok', url: socialDraft.tiktok.trim(), isActive: Boolean(socialDraft.tiktok.trim()), sortOrder: 5 },
      { id: 'soc-wa', platform: 'WhatsApp', url: socialDraft.whatsapp.trim(), isActive: Boolean(socialDraft.whatsapp.trim()), sortOrder: 6 }
    ];
    const nextSettings: CompanySettings = {
      ...companySettings,
      socialFacebook: socialDraft.facebook.trim(),
      socialInstagram: socialDraft.instagram.trim(),
      socialLinkedin: socialDraft.linkedin.trim(),
      socialYoutube: socialDraft.youtube.trim(),
      socialTiktok: socialDraft.tiktok.trim(),
      socialWhatsapp: socialDraft.whatsapp.trim(),
      socialLinks: updatedLinks
    };
    try {
      localStorage.setItem('kgs_company_settings', JSON.stringify(nextSettings));
    } catch {
      // ignore
    }
    if (onUpdateCompanySettings) {
      onUpdateCompanySettings(nextSettings);
    }
    setIsEditingSocials(false);
  };

  const footerMenusSource = [...navigationMenus].filter(
    m => m.location === 'footer' && m.isActive
  );
  const footerMenus = (() => {
    const seen = new Set<string>();
    const source =
      footerMenusSource.length > 0
        ? footerMenusSource
        : INITIAL_NAVIGATION_MENUS.filter(m => m.location === 'footer' && m.isActive);
    return source
      .map(m => {
        const cleanTitle =
          m.title === 'Layanan' || m.title === 'Katalog Layanan'
            ? 'Product & Service'
            : m.title.trim();
        return {
          ...m,
          title: cleanTitle,
          url: normalizeMenuItemUrl(cleanTitle, m.url)
        };
      })
      .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id)
      .filter(m => {
        const key = m.title.toLowerCase();
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
      });
  })();

  const quickLinks = [
    {
      id: 'fq-1',
      title:
        companySettings.footerQuick1Label ||
        DEFAULT_COMPANY_SETTINGS.footerQuick1Label ||
        'Kebijakan Privasi & SLA',
      url: normalizeMenuItemUrl(
        companySettings.footerQuick1Label || 'Kebijakan Privasi & SLA',
        companySettings.footerQuick1Url || 'kebijakan-privasi.php'
      )
    },
    {
      id: 'fq-2',
      title:
        companySettings.footerQuick2Label &&
        companySettings.footerQuick2Label !== 'Katalog Paket & Harga B2B'
          ? companySettings.footerQuick2Label
          : '3 Segmen Paket Internet ISP',
      url: normalizeMenuItemUrl(
        '3 Segmen Paket Internet ISP',
        companySettings.footerQuick2Url || 'harga.php'
      )
    },
    {
      id: 'fq-3',
      title:
        companySettings.footerQuick3Label ||
        DEFAULT_COMPANY_SETTINGS.footerQuick3Label ||
        'Pertanyaan Umum (FAQ)',
      url: normalizeMenuItemUrl(
        companySettings.footerQuick3Label || 'Pertanyaan Umum (FAQ)',
        companySettings.footerQuick3Url || 'faq.php'
      )
    },
    {
      id: 'fq-4',
      title:
        companySettings.footerQuick4Label ||
        DEFAULT_COMPANY_SETTINGS.footerQuick4Label ||
        'Minta Penawaran / Survei',
      url: normalizeMenuItemUrl(
        companySettings.footerQuick4Label || 'Minta Penawaran / Survei',
        companySettings.footerQuick4Url || 'kontak.php#konsultasi'
      )
    },
    {
      id: 'fq-5',
      title:
        companySettings.footerQuick5Label ||
        DEFAULT_COMPANY_SETTINGS.footerQuick5Label ||
        'Peta Lokasi Google Maps',
      url: normalizeMenuItemUrl(
        companySettings.footerQuick5Label || 'Peta Lokasi Google Maps',
        companySettings.footerQuick5Url || 'kontak.php#peta-lokasi'
      )
    }
  ];

  const rawBrandLabel = (companySettings.footerBrandLabel || 'KGSNET').trim();
  const footerBrandLabel =
    rawBrandLabel.toUpperCase() === 'PT. KGS' || rawBrandLabel.toUpperCase() === 'KGS'
      ? 'KGSNET'
      : rawBrandLabel;

  const activeFooterLogo = companySettings.footerLogo || companyLogo || '';

  const showCertSection = companySettings.footerShowCertSection !== false;
  const showCertStatus = companySettings.footerShowCertStatus !== false;
  const showCertBadges = companySettings.footerShowCertBadges !== false;

  const certBadges = parseFooterCertBadges(
    companySettings.footerCertBadges ||
      DEFAULT_COMPANY_SETTINGS.footerCertBadges ||
      'APJII Official Member :: https://apjii.or.id | KGSNet ISP Resmi :: https://www.kgs.net.id | Panasonic Authorized | NETVIEL Certified'
  );

  const rawCopyright = (
    companySettings.copyrightText ||
    DEFAULT_COMPANY_SETTINGS.copyrightText ||
    `© ${new Date().getFullYear()} ${footerBrandLabel} — Creative.co.id. All Rights Reserved.`
  ).trim();

  const displayCopyright =
    rawCopyright === 'Hak Cipta Dilindungi.' || rawCopyright === 'All Rights Reserved.'
      ? `© ${new Date().getFullYear()} ${footerBrandLabel} — Creative.co.id. All Rights Reserved.`
      : rawCopyright;

  const handleFooterLinkClick = (
    e: React.MouseEvent<HTMLAnchorElement>,
    title: string,
    url: string
  ) => {
    const rawUrl = normalizeMenuItemUrl(title, url);
    if (/^https?:\/\//i.test(rawUrl)) {
      return;
    }

    e.preventDefault();

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

    const sectionId = resolveMenuUrlToSectionId(rawUrl, title);
    if (sectionId === 'faq' && onOpenFaqPage) {
      onOpenFaqPage();
      return;
    }

    if (onNavigateRoute) {
      const anchor =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? sectionId : undefined;
      const route =
        sectionId === 'peta-lokasi' || sectionId === 'konsultasi' ? 'kontak' : sectionId;
      onNavigateRoute(route, anchor);
      return;
    }

    if (onResetSubViews) {
      onResetSubViews();
    }

    setTimeout(() => {
      if (sectionId === 'beranda') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      const target =
        document.getElementById(sectionId) ||
        (sectionId === 'peta-lokasi' || sectionId === 'konsultasi'
          ? document.getElementById('kontak')
          : null);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    }, 30);
  };

  return (
    <footer id="footer" className="bg-slate-950 text-slate-300 text-sm border-t border-slate-800 pb-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {isLiveEditMode && onOpenAdminSection && (
          <div className="mb-8 flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-indigo-950/60 border border-indigo-500/40" data-live-editor-ui="true">
            <div className="text-xs text-indigo-200 font-semibold">
              💡 <strong>Mode Edit Langsung Footer Aktif:</strong> Klik teks apapun di Footer untuk mengedit langsung, atau buka Panel Pengaturan Seluruh Footer.
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <button
                type="button"
                onClick={() => onOpenAdminSection('header_footer_text')}
                className="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow-sm inline-flex items-center gap-1.5 cursor-pointer transition"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>Pengaturan Seluruh Footer (Logo, Kontak, Navigasi, Sertifikasi & Copyright)</span>
              </button>
              <button
                type="button"
                onClick={() => onOpenAdminSection('navigation')}
                className="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5 cursor-pointer transition"
              >
                <span>Edit Menu Navigasi Footer</span>
              </button>
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
          {/* Kolom 1: Logo, Tentang Perusahaan & Lisensi Sertifikasi & Keanggotaan (2 cols) */}
          <div className="lg:col-span-2 space-y-4">
            <div className="flex flex-col justify-center min-w-0">
              {/* Baris 1 Footer: Logo di Kiri + PT. KREATIF GLOBAL SOLUSINDO di Samping Logo */}
              <div className="flex flex-row items-center gap-2 sm:gap-2.5 min-w-0">
                {activeFooterLogo ? (
                  <img
                    src={activeFooterLogo}
                    alt="Logo PT. Kreatif Global Solusindo"
                    className="h-7 w-7 sm:h-9 sm:w-auto max-w-[36px] sm:max-w-[120px] object-contain rounded-lg bg-white p-0.5 shrink-0"
                  />
                ) : (
                  <div
                    style={{
                      backgroundColor:
                        companySettings.logoBgColor || themeConfig.primaryColor || '#2563eb',
                      color: companySettings.logoTextColor || '#ffffff'
                    }}
                    className="w-7 h-7 sm:w-9 sm:h-9 font-black text-[10px] sm:text-sm rounded-lg flex items-center justify-center shadow-sm shrink-0 tracking-tight border border-white/10"
                  >
                    <span>KGS</span>
                  </div>
                )}
                <span
                  style={{
                    color:
                      companySettings.companyNameColor &&
                      companySettings.companyNameColor.toLowerCase() !== '#0f172a' &&
                      companySettings.companyNameColor.toLowerCase() !== '#1e293b' &&
                      companySettings.companyNameColor.toLowerCase() !== '#000000'
                        ? companySettings.companyNameColor
                        : '#ffffff'
                  }}
                  className="font-extrabold text-[13.5px] sm:text-base md:text-lg block tracking-tight leading-none whitespace-nowrap"
                >
                  {companySettings.legalName &&
                  !companySettings.legalName.toLowerCase().includes('creatve') &&
                  companySettings.legalName.toLowerCase() !== 'creative.co.id'
                    ? companySettings.legalName
                    : COMPANY_INFO.legalName}
                </span>
              </div>

              {/* Baris 2 Footer (Di Bawah Logo & PT. KREATIF GLOBAL SOLUSINDO): System Integrator, Security Solution & Internet Provider (1 Baris) */}
              <span
                style={{
                  color:
                    companySettings.sloganColor &&
                    companySettings.sloganColor.toLowerCase() !== '#0f172a' &&
                    companySettings.sloganColor.toLowerCase() !== '#1e293b'
                      ? companySettings.sloganColor
                      : themeConfig.primaryTextAccent
                }}
                className="text-[10.5px] sm:text-xs font-semibold tracking-tight sm:tracking-wide block leading-tight mt-1 whitespace-nowrap"
              >
                {(companySettings.slogan || COMPANY_INFO.slogan || '').toLowerCase() ===
                'system integrator, security solution & internet service provider'
                  ? 'System Integrator, Security Solution & Internet Provider'
                  : companySettings.slogan || COMPANY_INFO.slogan}
              </span>
            </div>

            <p className="text-slate-300 leading-relaxed text-sm max-w-md">
              {companySettings.footerDescription ||
                DEFAULT_COMPANY_SETTINGS.footerDescription ||
                `Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.`}
            </p>

            {/* Lisensi, Sertifikasi & Keanggotaan (Dapat Ditampilkan / Disembunyikan dari Admin CMS) */}
            {showCertBadges && (
              <div className="pt-3 border-t border-slate-900 space-y-2">
                <div className="flex items-center justify-between gap-2">
                  <h5 className="text-slate-200 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                    <span>{companySettings.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan'}</span>
                  </h5>
                  {showCertStatus && !showCertSection && (
                    <span className="text-[11px] text-emerald-400 font-semibold">
                      {companySettings.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi'}
                    </span>
                  )}
                </div>
                <div className="flex flex-wrap gap-1.5">
                  {certBadges.map((badge, idx) => {
                    const hasLink = Boolean(badge.url && badge.url !== '#');
                    return hasLink ? (
                      <a
                        key={idx}
                        href={badge.url}
                        target={/^https?:\/\//i.test(badge.url) ? '_blank' : undefined}
                        rel={/^https?:\/\//i.test(badge.url) ? 'noopener noreferrer' : undefined}
                        className="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-blue-500 rounded-md text-xs font-semibold text-slate-200 hover:text-white inline-flex items-center gap-1.5 transition"
                      >
                        {badge.logo && (
                          <img
                            src={badge.logo}
                            alt={badge.label}
                            className="w-3.5 h-3.5 object-contain rounded bg-white p-0.5"
                          />
                        )}
                        <span>{badge.label}</span>
                        <ExternalLink className="w-3 h-3 text-blue-400 shrink-0" />
                      </a>
                    ) : (
                      <span
                        key={idx}
                        className="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-md text-xs font-semibold text-slate-300 inline-flex items-center gap-1.5"
                      >
                        {badge.logo && (
                          <img
                            src={badge.logo}
                            alt={badge.label}
                            className="w-3.5 h-3.5 object-contain rounded bg-white p-0.5"
                          />
                        )}
                        <span>{badge.label}</span>
                      </span>
                    );
                  })}
                </div>
              </div>
            )}
          </div>

          {/* Kolom 2: Alamat Kantor & Kontak + Icon Sosial Media */}
          <div>
            <h4 className="text-white font-bold mb-4 text-sm uppercase tracking-wider">
              {companySettings.footerContactHeading || 'Alamat Kantor & Kontak'}
            </h4>
            <div className="space-y-3 text-slate-300 text-xs leading-relaxed">
              <div>
                <strong className="text-white block mb-0.5">
                  {companySettings.footerAddressLabel || 'Alamat Kantor:'}
                </strong>
                <p>{companySettings.addressPalembang || COMPANY_INFO.officePalembang.address}</p>
              </div>
              <div className="space-y-1.5">
                <p>
                  <strong className="text-white">Telp:</strong>{' '}
                  <span>{companySettings.phonePalembang || COMPANY_INFO.officePalembang.phone}</span>
                </p>
                <p>
                  <strong className="text-white">WhatsApp CS:</strong>{' '}
                  <span>{companySettings.whatsapp || COMPANY_INFO.officePalembang.whatsapp}</span>
                </p>
                <p>
                  <strong className="text-white">Email:</strong>{' '}
                  <span>{companySettings.email || COMPANY_INFO.officePalembang.email}</span>
                </p>
                <p>
                  <strong className="text-white">Jam Kerja:</strong>{' '}
                  <span>{companySettings.operationalHours || 'Senin - Sabtu: 08:30 - 17:00 WIB'}</span>
                </p>
              </div>

              {/* Icon Sosial Media Resmi (Dapat diatur di Admin CMS maupun Live Edit) */}
              <div className="pt-3 border-t border-slate-800/80" data-live-nav="true">
                <div className="flex items-center justify-between gap-2 mb-2.5">
                  <span className="text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                    Sosial Media Resmi:
                  </span>
                  <div className="flex items-center gap-2">
                    <button
                      type="button"
                      onClick={openSocialLiveEditor}
                      className="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500 hover:text-slate-950 font-bold cursor-pointer transition"
                    >
                      ✏️ Live Edit Sosmed
                    </button>
                    {onOpenAdminSection && (
                      <button
                        type="button"
                        onClick={() => onOpenAdminSection('header_footer_text')}
                        className="text-[10px] text-blue-400 hover:underline font-semibold cursor-pointer"
                      >
                        ⚙️ CMS
                      </button>
                    )}
                  </div>
                </div>

                {isEditingSocials ? (
                  <form
                    onSubmit={handleSaveSocialLiveEditor}
                    className="p-3 rounded-xl bg-slate-900 border border-emerald-500/60 space-y-2 text-[11px] mb-2 shadow-xl"
                  >
                    <div className="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-1">
                      <span className="font-extrabold text-emerald-400">Edit Link Sosial Media</span>
                      <button
                        type="button"
                        onClick={() => setIsEditingSocials(false)}
                        className="text-slate-400 hover:text-white cursor-pointer"
                      >
                        <X className="w-3.5 h-3.5" />
                      </button>
                    </div>
                    {[
                      { key: 'facebook', label: 'Facebook URL' },
                      { key: 'instagram', label: 'Instagram URL' },
                      { key: 'linkedin', label: 'LinkedIn URL' },
                      { key: 'youtube', label: 'YouTube URL' },
                      { key: 'tiktok', label: 'TikTok URL' },
                      { key: 'whatsapp', label: 'WhatsApp URL' }
                    ].map(field => (
                      <div key={field.key}>
                        <label className="block text-[10px] font-bold text-slate-400 mb-0.5">
                          {field.label}
                        </label>
                        <input
                          type="url"
                          value={socialDraft[field.key as keyof typeof socialDraft]}
                          onChange={e =>
                            setSocialDraft(prev => ({
                              ...prev,
                              [field.key]: e.target.value
                            }))
                          }
                          placeholder={`https://...`}
                          className="w-full px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-[11px]"
                        />
                      </div>
                    ))}
                    <div className="flex justify-end gap-1.5 pt-1.5">
                      <button
                        type="button"
                        onClick={() => setIsEditingSocials(false)}
                        className="px-2.5 py-1 rounded bg-slate-800 text-slate-300 font-semibold cursor-pointer"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        className="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold inline-flex items-center gap-1 cursor-pointer"
                      >
                        <Check className="w-3 h-3" />
                        <span>Simpan</span>
                      </button>
                    </div>
                  </form>
                ) : null}

                <div className="flex flex-wrap items-center gap-2">
                  {getEffectiveSocialLinks(companySettings)
                    .filter(soc => soc.isActive && soc.url && soc.url.trim() !== '')
                    .map(soc => (
                      <a
                        key={soc.id}
                        href={soc.url.trim()}
                        target="_blank"
                        rel="noopener noreferrer"
                        onClick={e => {
                          if (isLiveEditMode) {
                            e.preventDefault();
                            openSocialLiveEditor();
                          }
                        }}
                        title={
                          isLiveEditMode
                            ? `Klik untuk Edit Link ${soc.platform}`
                            : `${soc.platform} PT. Kreatif Global Solusindo`
                        }
                        aria-label={`${soc.platform} PT. Kreatif Global Solusindo`}
                        className={`w-9 h-9 rounded-xl bg-slate-900 border border-slate-700/90 text-slate-200 flex items-center justify-center transition-all duration-200 shadow-xs ${getSocialHoverClass(soc.platform)}`}
                      >
                        {renderSocialPlatformIcon(soc.platform)}
                      </a>
                    ))}
                </div>
              </div>
            </div>
          </div>

          {/* Kolom 3: Navigasi Halaman */}
          <div data-live-nav="true">
            <h4 className="text-white font-bold mb-4 text-sm uppercase tracking-wider">
              {companySettings.footerNavHeading || 'Navigasi Halaman'}
            </h4>
            <ul className="space-y-2.5 text-slate-300 text-xs">
              {footerMenus.map(item => (
                <li key={item.id}>
                  <a
                    href={item.url}
                    target={item.target === '_blank' ? '_blank' : undefined}
                    rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                    onClick={e => handleFooterLinkClick(e, item.title, item.url)}
                    className="hover:text-blue-400 transition-colors cursor-pointer block py-0.5"
                  >
                    {item.title}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Kolom 4: Informasi & Akses Cepat */}
          <div data-live-nav="true">
            <h4 className="text-white font-bold mb-4 text-sm uppercase tracking-wider">
              {companySettings.footerQuickHeading || 'Informasi & Akses Cepat'}
            </h4>
            <ul className="space-y-2.5 text-slate-300 text-xs">
              {quickLinks.map(item => (
                <li key={item.id}>
                  <a
                    href={item.url}
                    onClick={e => handleFooterLinkClick(e, item.title, item.url)}
                    className="hover:text-blue-400 transition-colors text-left cursor-pointer block py-0.5"
                  >
                    {item.title}
                  </a>
                </li>
              ))}
              <li className="pt-3 border-t border-slate-900">
                <button
                  onClick={onOpenPhpExport}
                  className="flex items-center gap-2 text-slate-200 hover:text-white transition-colors cursor-pointer py-1 font-semibold"
                >
                  <Download className="w-4 h-4 shrink-0" />
                  <span>Download Kode PHP + SQL</span>
                </button>
              </li>
            </ul>
          </div>
        </div>

        {/* Baris Sertifikasi & Keanggotaan Resmi di Footer (Pilihan Ditampilkan / Tidak Ditampilkan) */}
        {showCertSection && certifications && certifications.length > 0 && (
          <div className="mt-10 pt-8 border-t border-slate-900">
            <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
              <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                {companySettings.footerCertCardTitle || 'Sertifikasi & Keanggotaan Resmi'}
              </span>
              {showCertStatus && (
                <span className="text-[11px] text-emerald-400 font-semibold">
                  {companySettings.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi'}
                </span>
              )}
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
              {certifications.slice(0, 4).map(fc => {
                const badgeCode = (fc.issuerOrBrand || 'CERT')
                  .replace(/[^A-Za-z0-9]/g, '')
                  .slice(0, 6)
                  .toUpperCase() || 'CERT';
                return (
                  <div
                    key={fc.id}
                    className="p-3 rounded-xl bg-slate-900/80 border border-slate-800 flex items-start gap-2.5"
                  >
                    {fc.logoUrl ? (
                      <img
                        src={fc.logoUrl}
                        alt={fc.title}
                        className="w-8 h-8 rounded bg-white p-0.5 object-contain shrink-0"
                      />
                    ) : (
                      <span className="px-2 py-1 rounded bg-blue-500/20 text-blue-300 font-extrabold text-[10px] shrink-0">
                        {badgeCode}
                      </span>
                    )}
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center justify-between gap-1">
                        <div className="font-bold text-slate-200 truncate">{fc.title}</div>
                        {fc.linkUrl && (
                          <a
                            href={fc.linkUrl}
                            target={/^https?:\/\//i.test(fc.linkUrl) ? '_blank' : undefined}
                            rel={/^https?:\/\//i.test(fc.linkUrl) ? 'noopener noreferrer' : undefined}
                            className="text-[10px] text-blue-400 hover:underline shrink-0"
                          >
                            &nearr;
                          </a>
                        )}
                      </div>
                      <div className="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                        {fc.yearOrValidity || fc.issuerOrBrand}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* Bottom Bar: Hak Cipta & Domain Resmi */}
        <div className="mt-10 pt-8 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-slate-400 text-xs">
          <p className="font-medium text-slate-300">
            {displayCopyright}
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <span className="text-blue-400 font-bold">{footerBrandLabel}</span>
            <span>·</span>
            <span>{companySettings.websiteCorporate || 'www.creative.co.id'}</span>
            <span>·</span>
            <span>{companySettings.websiteIsp || 'www.kgs.net.id'}</span>
            <span>·</span>
            <button
              type="button"
              onClick={onOpenAdmin}
              className="text-slate-400 hover:text-blue-400 font-semibold transition cursor-pointer"
            >
              🔒 Admin CMS
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
};
