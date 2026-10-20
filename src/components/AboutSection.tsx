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
  PartnerItem
} from '../data/companyData';
import {
  Award,
  ShieldCheck,
  History,
  Compass,
  HeartHandshake,
  Users,
  Edit3
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
  const visionText = companySettings.vision || COMPANY_INFO.vision;
  const missionText = companySettings.mission || COMPANY_INFO.mission;
  const cultureText = companySettings.culture || COMPANY_INFO.culture;
  const commitmentText = companySettings.commitment || COMPANY_INFO.commitment;

  return (
    <section id="tentang-kami" className="py-20 bg-white border-b border-slate-200 relative">
      <div id="tentang" className="absolute -top-24 left-0"></div>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-16">
          <div className="max-w-3xl">
            <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">
              Identitas & Rekam Jejak Perusahaan
            </div>
            <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Tentang PT. Kreatif Global Solusindo'}
            </h2>
            <p className="text-sm text-slate-600 mt-2 leading-relaxed">
              {pageContent?.summary ||
                'Berawal dari Creative Com pada tahun 1997 di Palembang hingga bertransformasi menjadi penyelenggara jasa internet dan system integrator berlisensi nasional.'}
            </p>
            {pageContent?.content && (
              <div className="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                {pageContent.content}
              </div>
            )}
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <button
              type="button"
              onClick={() => onOpenAdminSection('about_sections')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Edit Sejarah, Visi Misi, Sertifikasi & Mitra di Admin CMS</span>
            </button>
          )}
        </div>

        {/* Timeline Perjalanan */}
        <div className="mb-20">
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

        {/* Visi, Misi, Budaya & Komitmen */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
          {/* Visi & Misi */}
          <div className="p-8 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-6">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-bold text-blue-700">
                  <Compass className="w-5 h-5" />
                  <span>VISI PERUSAHAAN</span>
                </div>
                {onOpenAdminSection && isLiveEditMode && (
                  <button
                    type="button"
                    onClick={() => onOpenAdminSection('about_sections')}
                    className="text-xs font-bold text-blue-600 underline cursor-pointer"
                  >
                    Edit Visi & Misi
                  </button>
                )}
              </div>
              <p className="text-base text-slate-800 leading-relaxed font-medium">
                &ldquo;{visionText}&rdquo;
              </p>
            </div>

            <div className="pt-6 border-t border-slate-200">
              <div className="flex items-center gap-2 text-sm font-bold text-blue-700 mb-3">
                <Users className="w-5 h-5" />
                <span>MISI PERUSAHAAN</span>
              </div>
              <p className="text-base text-slate-700 leading-relaxed">&ldquo;{missionText}&rdquo;</p>
            </div>
          </div>

          {/* Budaya & Komitmen */}
          <div className="p-8 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-6">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2 text-sm font-bold text-blue-700">
                  <HeartHandshake className="w-5 h-5" />
                  <span>BUDAYA KERJA FUNGSIONAL</span>
                </div>
                {onOpenAdminSection && isLiveEditMode && (
                  <button
                    type="button"
                    onClick={() => onOpenAdminSection('about_sections')}
                    className="text-xs font-bold text-blue-600 underline cursor-pointer"
                  >
                    Edit Budaya & Komitmen
                  </button>
                )}
              </div>
              <p className="text-base text-slate-700 leading-relaxed">&ldquo;{cultureText}&rdquo;</p>
            </div>

            <div className="pt-6 border-t border-slate-200">
              <div className="flex items-center gap-2 text-sm font-bold text-blue-700 mb-3">
                <ShieldCheck className="w-5 h-5" />
                <span>KOMITMEN KEPADA PELANGGAN</span>
              </div>
              <p className="text-base text-slate-700 leading-relaxed">
                &ldquo;{commitmentText}&rdquo;
              </p>
            </div>
          </div>
        </div>

        {/* Sertifikasi & Lisensi Resmi */}
        <div className="mb-20">
          <div className="flex items-center justify-between mb-8">
            <div>
              <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                Kredensial & Kemitraan Prinsipal
              </div>
              <h3 className="text-xl font-bold text-slate-900">
                Sertifikasi Resmi, Lisensi & Otorisasi Brand
              </h3>
            </div>
            <div className="flex items-center gap-2">
              {onOpenAdminSection && isLiveEditMode && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('about_sections')}
                  className="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>+ Tambah / Edit Sertifikasi</span>
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
                className="p-5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-all shadow-xs flex items-start gap-4"
              >
                <div className="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                  <Award className="w-5 h-5" />
                </div>
                <div>
                  <h4 className="text-sm font-bold text-slate-900">{cert.title}</h4>
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
            {onOpenAdminSection && isLiveEditMode && (
              <button
                type="button"
                onClick={() => onOpenAdminSection('about_sections')}
                className="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Tambah / Edit Mitra Kerja</span>
              </button>
            )}
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            {partners.map(partner => (
              <div
                key={partner.id}
                className="p-4 rounded-xl border border-slate-200 bg-slate-50 text-center hover:bg-white transition"
              >
                <div className="text-xs font-bold text-slate-900 mb-1">{partner.name}</div>
                <div className="text-[10px] text-blue-600 font-medium leading-tight">
                  {partner.role}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};
