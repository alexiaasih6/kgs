import React from 'react';
import {
  COMPANY_INFO,
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME
} from '../data/companyData';
import { resolveMenuUrlToSectionId } from './Navbar';
import { Download, Edit3, ShieldCheck } from 'lucide-react';

interface FooterProps {
  onOpenAudit: () => void;
  onOpenPhpExport: () => void;
  onOpenAdmin: () => void;
  onOpenPage?: (slug: string) => void;
  companyLogo?: string;
  navigationMenus?: NavigationMenuItem[];
  companySettings?: CompanySettings;
  themeConfig?: WebsiteThemeConfig;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab?: string) => void;
}

export const Footer: React.FC<FooterProps> = ({
  onOpenPhpExport,
  onOpenPage,
  companyLogo,
  navigationMenus = INITIAL_NAVIGATION_MENUS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  themeConfig = DEFAULT_WEBSITE_THEME,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const headerMenus = [...navigationMenus]
    .filter(m => m.location === 'header' && m.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id);

  const footerMenus = [...navigationMenus]
    .filter(m => m.location === 'footer' && m.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id);

  const rawBrandLabel = (companySettings.footerBrandLabel || 'KGSNET').trim();
  const footerBrandLabel =
    rawBrandLabel.toUpperCase() === 'PT. KGS' || rawBrandLabel.toUpperCase() === 'KGS'
      ? 'KGSNET'
      : rawBrandLabel;

  const activeFooterLogo = companySettings.footerLogo || companyLogo || '';

  const certBadges = (
    companySettings.footerCertBadges ||
    DEFAULT_COMPANY_SETTINGS.footerCertBadges ||
    'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified'
  )
    .split('|')
    .map(b => b.trim())
    .filter(Boolean);

  const rawCopyright = (
    companySettings.copyrightText ||
    DEFAULT_COMPANY_SETTINGS.copyrightText ||
    `© ${new Date().getFullYear()} ${footerBrandLabel} — Creative.co.id. All Rights Reserved.`
  ).trim();

  const displayCopyright =
    rawCopyright === 'Hak Cipta Dilindungi.' || rawCopyright === 'All Rights Reserved.'
      ? `© ${new Date().getFullYear()} ${footerBrandLabel} — Creative.co.id. All Rights Reserved.`
      : rawCopyright;

  const handleFooterMenuClick = (
    e: React.MouseEvent<HTMLAnchorElement>,
    item: NavigationMenuItem
  ) => {
    const rawUrl = item.url.trim();
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

    const sectionId = resolveMenuUrlToSectionId(rawUrl);
    if (sectionId === 'beranda') {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    const target = document.getElementById(sectionId);
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
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
            <div className="flex items-center gap-3.5">
              {activeFooterLogo ? (
                <img
                  src={activeFooterLogo}
                  alt="Logo KGSNET"
                  className="h-11 w-auto max-w-[130px] object-contain rounded-xl bg-white p-1 border border-slate-700 shrink-0"
                />
              ) : (
                <div
                  style={{ backgroundColor: themeConfig.primaryColor }}
                  className="px-3 h-11 text-white font-extrabold text-sm rounded-xl flex items-center justify-center shadow-sm shrink-0 tracking-tight"
                >
                  <span>{footerBrandLabel}</span>
                </div>
              )}
              <div>
                <div className="flex flex-wrap items-center gap-2">
                  <span className="px-2 py-0.5 rounded bg-blue-600/20 border border-blue-500/40 text-blue-400 font-extrabold text-xs tracking-wider uppercase">
                    {footerBrandLabel}
                  </span>
                  <span className="font-extrabold text-white text-lg block tracking-tight">
                    {companySettings.legalName || COMPANY_INFO.legalName}
                  </span>
                </div>
                <span
                  style={{ color: themeConfig.primaryTextAccent }}
                  className="text-xs font-semibold block mt-0.5"
                >
                  {companySettings.slogan || COMPANY_INFO.slogan}
                </span>
              </div>
            </div>

            <p className="text-slate-300 leading-relaxed text-sm max-w-md">
              {companySettings.footerDescription ||
                DEFAULT_COMPANY_SETTINGS.footerDescription ||
                `Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.`}
            </p>

            {/* Lisensi, Sertifikasi & Keanggotaan */}
            <div className="pt-3 border-t border-slate-900 space-y-2">
              <div className="flex items-center justify-between gap-2">
                <h5 className="text-slate-200 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                  <ShieldCheck className="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                  <span>{companySettings.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan'}</span>
                </h5>
                <span className="text-[11px] text-emerald-400 font-semibold">
                  {companySettings.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi'}
                </span>
              </div>
              <div className="flex flex-wrap gap-1.5">
                {certBadges.map((badge, idx) => (
                  <span
                    key={idx}
                    className="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-md text-xs font-semibold text-slate-300"
                  >
                    {badge}
                  </span>
                ))}
              </div>
            </div>
          </div>

          {/* Kolom 2: Alamat Kantor & Kontak */}
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
            </div>
          </div>

          {/* Kolom 3: Navigasi Halaman */}
          <div>
            <h4 className="text-white font-bold mb-4 text-sm uppercase tracking-wider">
              {companySettings.footerNavHeading || 'Navigasi Halaman'}
            </h4>
            <ul className="space-y-2.5 text-slate-300 text-xs">
              {headerMenus.map(item => (
                <li key={item.id}>
                  <a
                    href={item.url}
                    target={item.target === '_blank' ? '_blank' : undefined}
                    rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                    onClick={e => handleFooterMenuClick(e, item)}
                    className="hover:text-blue-400 transition-colors cursor-pointer block py-0.5"
                  >
                    {item.title}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Kolom 4: Informasi & Akses Cepat */}
          <div>
            <h4 className="text-white font-bold mb-4 text-sm uppercase tracking-wider">
              {companySettings.footerQuickHeading || 'Informasi & Akses Cepat'}
            </h4>
            <ul className="space-y-2.5 text-slate-300 text-xs">
              {footerMenus.map(item => (
                <li key={item.id}>
                  <a
                    href={item.url}
                    target={item.target === '_blank' ? '_blank' : undefined}
                    rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                    onClick={e => handleFooterMenuClick(e, item)}
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

        {/* Bottom Bar: Hak Cipta & Domain Resmi */}
        <div className="mt-12 pt-8 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-slate-400 text-xs">
          <p className="font-medium text-slate-300">
            {displayCopyright}
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <span className="text-blue-400 font-bold">{footerBrandLabel}</span>
            <span>·</span>
            <span>{companySettings.websiteCorporate || 'www.creative.co.id'}</span>
            <span>·</span>
            <span>{companySettings.websiteIsp || 'www.kgs.net.id'}</span>
          </div>
        </div>
      </div>
    </footer>
  );
};
