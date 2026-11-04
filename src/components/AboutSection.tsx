import React from 'react';
import {
  COMPANY_INFO,
  VERIFIED_PARTNERS,
  VERIFIED_CERTIFICATIONS,
  INITIAL_TIMELINE_MILESTONES,
  StaticPageItem,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  TimelineMilestoneItem,
  CertificationItem,
  PartnerItem,
  parseFooterCertBadges
} from '../data/companyData';
import {
  Award,
  ShieldCheck,
  History,
  Compass,
  HeartHandshake,
  Users,
  Edit3,
  ExternalLink
} from 'lucide-react';

interface AboutSectionProps {
  pageContent?: StaticPageItem;
  companySettings?: CompanySettings;
  timeline?: TimelineMilestoneItem[];
  certifications?: CertificationItem[];
  partners?: PartnerItem[];
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const AboutSection: React.FC<AboutSectionProps> = ({
  pageContent,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  timeline = INITIAL_TIMELINE_MILESTONES,
  certifications = VERIFIED_CERTIFICATIONS,
  partners = VERIFIED_PARTNERS,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const profileText =
    pageContent?.content ||
    pageContent?.summary ||
    COMPANY_INFO.profile;
  const visionText = companySettings.vision || COMPANY_INFO.vision;
  const cultureText = companySettings.culture || COMPANY_INFO.culture;
  const commitmentText = companySettings.commitment || COMPANY_INFO.commitment;
  const licenseBadges = parseFooterCertBadges(
    companySettings.footerCertBadges || DEFAULT_COMPANY_SETTINGS.footerCertBadges
  );

  return (
    <section id="tentang-kami" className="py-20 bg-white border-b border-slate-200 relative">
      <div id="tentang" className="absolute -top-24 left-0"></div>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header: Profile */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-10">
          <div className="max-w-4xl">
            <div className="text-xs font-extrabold text-blue-600 uppercase tracking-wider mb-2">
              PROFIL PERUSAHAAN • SEJARAH • VISI & MISI • BUDAYA • KOMITMEN • PENGALAMAN & DEDIKASI
            </div>
            <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Profil PT. Kreatif Global Solusindo (KGS)'}
            </h2>
            <div className="mt-4 p-6 rounded-2xl bg-slate-50 border border-slate-200 text-base text-slate-800 leading-relaxed font-medium space-y-3">
              <p>{profileText}</p>
              <div className="pt-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                <span className="inline-flex items-center gap-2 text-xs sm:text-sm font-extrabold text-blue-700 bg-blue-50 px-3.5 py-1.5 rounded-xl border border-blue-200">
                  <span>📢 &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;</span>
                </span>
              </div>
            </div>
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <button
              type="button"
              onClick={() => onOpenAdminSection('about_sections')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Edit Profile, Visi, Budaya & Komitmen di Admin CMS</span>
            </button>
          )}
        </div>

        {/* VISI & MISI, BUDAYA, KOMITMEN, PENGALAMAN & DEDIKASI */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
          {/* VISI & MISI */}
          <div className="p-6 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-extrabold text-blue-700 uppercase tracking-wider">
                  <Compass className="w-5 h-5" />
                  <span>VISI & MISI</span>
                </div>
                {onOpenAdminSection && isLiveEditMode && (
                  <button
                    type="button"
                    onClick={() => onOpenAdminSection('about_sections')}
                    className="text-xs font-bold text-blue-600 underline cursor-pointer"
                  >
                    Edit
                  </button>
                )}
              </div>
              <p className="text-xs sm:text-sm text-slate-700 leading-relaxed mb-3">
                {visionText}
              </p>
              <div className="pt-3 border-t border-slate-200/70 text-xs text-slate-600 space-y-1">
                <div className="font-bold text-slate-800">Misi Utama:</div>
                <p>Menghadirkan solusi integrasi sistem, keamanan elektronik, telekomunikasi, dan internet KGSNet yang handal, tepat guna, dan berkesinambungan.</p>
              </div>
            </div>
          </div>

          {/* BUDAYA */}
          <div className="p-6 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-extrabold text-blue-700 uppercase tracking-wider">
                  <HeartHandshake className="w-5 h-5" />
                  <span>BUDAYA KERJA</span>
                </div>
                {onOpenAdminSection && isLiveEditMode && (
                  <button
                    type="button"
                    onClick={() => onOpenAdminSection('about_sections')}
                    className="text-xs font-bold text-blue-600 underline cursor-pointer"
                  >
                    Edit
                  </button>
                )}
              </div>
              <p className="text-xs sm:text-sm text-slate-700 leading-relaxed">
                {cultureText}
              </p>
            </div>
          </div>

          {/* KOMITMEN */}
          <div className="p-6 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-extrabold text-blue-700 uppercase tracking-wider">
                  <ShieldCheck className="w-5 h-5" />
                  <span>KOMITMEN</span>
                </div>
                {onOpenAdminSection && isLiveEditMode && (
                  <button
                    type="button"
                    onClick={() => onOpenAdminSection('about_sections')}
                    className="text-xs font-bold text-blue-600 underline cursor-pointer"
                  >
                    Edit
                  </button>
                )}
              </div>
              <p className="text-xs sm:text-sm text-slate-700 leading-relaxed">
                {commitmentText}
              </p>
            </div>
          </div>

          {/* PENGALAMAN & DEDIKASI */}
          <div className="p-6 rounded-2xl border border-blue-200 bg-blue-50/50 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-extrabold text-blue-700 uppercase tracking-wider">
                  <Users className="w-5 h-5" />
                  <span>PENGALAMAN & DEDIKASI</span>
                </div>
              </div>
              <p className="text-xs sm:text-sm text-slate-700 leading-relaxed">
                Berpengalaman lebih dari <strong>28 tahun (sejak 1997)</strong> menangani ratusan proyek Data Networking, Radio Komunikasi, PABX, CCTV, Access Door, Fire Alarm, Public Address & Nursecall, serta layanan ISP KGSNet dengan dedikasi penuh tim engineer bersertifikat dan dukungan NOC 24/7.
              </p>
            </div>
          </div>
        </div>

        {/* Timeline Perjalanan */}
        <div className="mb-16">
          <div className="flex flex-wrap items-center justify-between gap-2 mb-8">
            <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
              <History className="w-5 h-5 text-blue-600" />
              <span>Sejarah Perjalanan Perusahaan (1997 - Sekarang)</span>
            </h3>
            {onOpenAdminSection && isLiveEditMode && (
              <button
                type="button"
                onClick={() => onOpenAdminSection('about_sections')}
                className="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Tambah / Edit Sejarah</span>
              </button>
            )}
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {timeline.map(item => (
              <div
                key={item.id}
                className={`p-6 rounded-2xl border relative transition-shadow hover:shadow-xs ${
                  item.isHighlighted
                    ? 'border-blue-300 bg-blue-50/60'
                    : 'border-slate-200 bg-slate-50/80'
                }`}
              >
                <div className="text-blue-700 font-extrabold text-2xl">{item.year}</div>
                <h4 className="text-lg font-bold text-slate-900 mt-1.5">{item.title}</h4>
                <p className="text-base text-slate-600 mt-2 leading-relaxed">
                  {item.description}
                </p>
              </div>
            ))}
          </div>
        </div>

        {/* Lisensi, Sertifikasi & Keanggotaan (Tulisan, Link & Logo) */}
        <div className="mb-16 p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div>
            <div className="flex flex-wrap items-center gap-2">
              <span className="text-xs font-extrabold uppercase tracking-wider text-blue-400">
                {companySettings.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan'}
              </span>
              <span className="text-xs font-bold text-emerald-400">
                {companySettings.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi'}
              </span>
            </div>
            <p className="text-xs text-slate-300 mt-1">
              Keanggotaan asosiasi penyelenggara internet nasional dan lisensi prinsipal resmi PT. Kreatif Global Solusindo.
            </p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            {licenseBadges.map((item, idx) => {
              const hasLink = Boolean(item.url && item.url !== '#');
              return hasLink ? (
                <a
                  key={idx}
                  href={item.url}
                  target={/^https?:\/\//i.test(item.url) ? '_blank' : undefined}
                  rel={/^https?:\/\//i.test(item.url) ? 'noopener noreferrer' : undefined}
                  className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-blue-950 border border-slate-700 hover:border-blue-500 text-slate-100 text-xs font-semibold inline-flex items-center gap-2 transition"
                >
                  {item.logo && (
                    <img
                      src={item.logo}
                      alt={item.label}
                      className="w-4 h-4 object-contain rounded bg-white p-0.5"
                    />
                  )}
                  <span>{item.label}</span>
                  <ExternalLink className="w-3 h-3 text-blue-400 shrink-0" />
                </a>
              ) : (
                <span
                  key={idx}
                  className="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-semibold inline-flex items-center gap-2"
                >
                  {item.logo && (
                    <img
                      src={item.logo}
                      alt={item.label}
                      className="w-4 h-4 object-contain rounded bg-white p-0.5"
                    />
                  )}
                  <span>{item.label}</span>
                </span>
              );
            })}
            {onOpenAdminSection && (
              <button
                type="button"
                onClick={() => onOpenAdminSection('about_sections')}
                className="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold inline-flex items-center gap-1 cursor-pointer transition"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Atur Link & Tulisan</span>
              </button>
            )}
          </div>
        </div>

        {/* Sertifikasi & Lisensi Resmi */}
        <div className="mb-20">
          <div className="flex flex-wrap items-center justify-between gap-2 mb-8">
            <div>
              <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                Kredensial & Kemitraan Prinsipal
              </div>
              <h3 className="text-xl font-bold text-slate-900">
                Sertifikasi Resmi, Lisensi & Otorisasi Brand
              </h3>
            </div>
            <div className="flex items-center gap-2">
              {onOpenAdminSection && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('about_sections')}
                  className="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>+ Tambah / Edit Sertifikasi, Logo & Link</span>
                </button>
              )}
              <span className="text-xs text-slate-500 font-medium hidden sm:inline">
                Terdaftar Resmi
              </span>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {certifications.map(cert => (
              <div
                key={cert.id}
                className="p-5 rounded-xl border border-slate-200 bg-white hover:border-blue-300 transition-all shadow-xs flex items-start gap-4"
              >
                {cert.logoUrl ? (
                  <img
                    src={cert.logoUrl}
                    alt={cert.title}
                    className="w-12 h-12 rounded-xl object-contain bg-white border border-slate-200 p-1 shrink-0"
                  />
                ) : (
                  <div className="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <Award className="w-5 h-5" />
                  </div>
                )}
                <div className="min-w-0 flex-1">
                  <div className="flex items-start justify-between gap-2">
                    <h4 className="text-sm font-bold text-slate-900">{cert.title}</h4>
                    {cert.linkUrl && (
                      <a
                        href={cert.linkUrl}
                        target={/^https?:\/\//i.test(cert.linkUrl) ? '_blank' : undefined}
                        rel={/^https?:\/\//i.test(cert.linkUrl) ? 'noopener noreferrer' : undefined}
                        className="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 shrink-0"
                      >
                        <span>Link</span>
                        <ExternalLink className="w-3 h-3" />
                      </a>
                    )}
                  </div>
                  <div className="text-xs text-blue-600 font-medium mt-1">
                    {cert.issuerOrBrand}
                  </div>
                  <div className="text-[11px] text-slate-500 mt-1">Penerima: {cert.holder}</div>
                  {cert.yearOrValidity && (
                    <div className="text-[11px] text-slate-400 mt-0.5">{cert.yearOrValidity}</div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Mitra Kerja Terverifikasi */}
        <div>
          <div className="flex flex-wrap items-center justify-between gap-2 mb-6">
            <div>
              <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                Mitra Kerja & Jaringan Rekanan
              </div>
              <h3 className="text-xl font-bold text-slate-900">
                Mitra Kerja Dukungan Lastmile & Guywire Tower ISP
              </h3>
            </div>
            {onOpenAdminSection && (
              <button
                type="button"
                onClick={() => onOpenAdminSection('about_sections')}
                className="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Tambah / Edit Mitra, Logo & Link</span>
              </button>
            )}
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            {partners.map(partner => (
              <div
                key={partner.id}
                className="p-4 rounded-xl border border-slate-200 bg-slate-50 text-center hover:bg-white hover:border-blue-300 transition flex flex-col items-center justify-between gap-2"
              >
                {partner.logoUrl && (
                  <img
                    src={partner.logoUrl}
                    alt={partner.name}
                    className="h-10 w-auto max-w-[100px] object-contain mx-auto bg-white p-1 rounded-lg border border-slate-200"
                  />
                )}
                <div>
                  <div className="text-xs font-bold text-slate-900 mb-1">{partner.name}</div>
                  <div className="text-[10px] text-blue-600 font-medium leading-tight">
                    {partner.role}
                  </div>
                </div>
                {partner.linkUrl && (
                  <a
                    href={partner.linkUrl}
                    target={/^https?:\/\//i.test(partner.linkUrl) ? '_blank' : undefined}
                    rel={/^https?:\/\//i.test(partner.linkUrl) ? 'noopener noreferrer' : undefined}
                    className="text-[11px] font-bold text-blue-600 hover:underline inline-flex items-center gap-1 mt-1"
                  >
                    <span>Buka Link</span>
                    <ExternalLink className="w-3 h-3" />
                  </a>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};
