import React, { useState } from 'react';
import {
  B2B_PRICING_PLANS,
  OTHER_PRODUCT_SERVICE_SEGMENTS,
  PricingPlanItem,
  StaticPageItem,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  ISP_LEGALITAS
} from '../data/companyData';
import {
  Check,
  ArrowRight,
  ShieldCheck,
  Zap,
  Server,
  Shield,
  Radio,
  Sun,
  Building2,
  Edit3,
  FileCheck2,
  Network,
  PhoneCall,
  TowerControl,
  ClipboardCheck
} from 'lucide-react';

interface PricingSectionProps {
  onRequestQuote: (planName: string) => void;
  pageContent?: StaticPageItem;
  pricingPlans?: PricingPlanItem[];
  companySettings?: CompanySettings;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

const LEGACY_PRICING_TITLES = new Set([
  'Pilihan Paket Solusi & Layanan B2B',
  '3 Segmen Paket Internet & Solusi Product & Service',
  '3 Segmen Paket Internet Service Provider (ISP)',
  '3 Segmen Paket Internet Service Provider'
]);

const LEGACY_PRICING_SUMMARIES = new Set([
  'Pilihan paket Internet Service Provider resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, dan Home Internet).',
  'Pilihan paket Internet Service Provider resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, dan Home Internet) serta solusi proyek terintegrasi.'
]);

export const PricingSection: React.FC<PricingSectionProps> = ({
  onRequestQuote,
  pageContent,
  pricingPlans = B2B_PRICING_PLANS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [selectedCategory, setSelectedCategory] = useState<
    'all' | 'dedicated' | 'bisnis' | 'home' | 'other'
  >('all');

  const filteredPlans = pricingPlans.filter(plan => {
    if (selectedCategory === 'other') return false;
    if (selectedCategory === 'all') return true;
    if (selectedCategory === 'dedicated') {
      return (
        plan.id === 'plan-isp-dedicated' ||
        plan.name.toLowerCase().includes('dedicated')
      );
    }
    if (selectedCategory === 'bisnis') {
      return (
        plan.id === 'plan-isp-bisnis' ||
        plan.name.toLowerCase().includes('bisnis')
      );
    }
    if (selectedCategory === 'home') {
      return (
        plan.id === 'plan-isp-home' ||
        plan.name.toLowerCase().includes('home')
      );
    }
    return true;
  });

  const showOtherSegments =
    selectedCategory === 'all' || selectedCategory === 'other';

  const getPlanIcon = (id: string) => {
    switch (id) {
      case 'plan-isp-dedicated':
      case 'plan-internet':
        return <Server className="w-5 h-5 text-blue-600" />;
      case 'plan-isp-bisnis':
      case 'plan-security':
        return <Building2 className="w-5 h-5 text-emerald-600" />;
      case 'plan-isp-home':
      case 'plan-tower-radio':
        return <Radio className="w-5 h-5 text-indigo-600" />;
      case 'plan-solar-power':
        return <Sun className="w-5 h-5 text-amber-600" />;
      case 'plan-custom-tender':
        return <Shield className="w-5 h-5 text-cyan-600" />;
      default:
        return <Zap className="w-5 h-5 text-blue-600" />;
    }
  };

  const getOtherSegmentIcon = (id: string) => {
    switch (id) {
      case 'seg-security':
        return <Shield className="w-5 h-5 text-rose-600" />;
      case 'seg-networking':
        return <Network className="w-5 h-5 text-blue-600" />;
      case 'seg-pbx':
        return <PhoneCall className="w-5 h-5 text-indigo-600" />;
      case 'seg-telecom':
        return <TowerControl className="w-5 h-5 text-amber-600" />;
      default:
        return <Zap className="w-5 h-5 text-blue-600" />;
    }
  };

  const getOtherSegmentBadgeStyle = (id: string) => {
    switch (id) {
      case 'seg-security':
        return 'bg-rose-50 text-rose-700 border-rose-200';
      case 'seg-networking':
        return 'bg-blue-50 text-blue-700 border-blue-200';
      case 'seg-pbx':
        return 'bg-indigo-50 text-indigo-700 border-indigo-200';
      case 'seg-telecom':
        return 'bg-amber-50 text-amber-700 border-amber-200';
      default:
        return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const resolvedTitle =
    pageContent?.title && !LEGACY_PRICING_TITLES.has(pageContent.title.trim())
      ? pageContent.title
      : 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya';

  const resolvedSummary =
    pageContent?.summary && !LEGACY_PRICING_SUMMARIES.has(pageContent.summary.trim())
      ? pageContent.summary
      : 'Daftar paket berlangganan 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, dan Home Internet) berizin resmi Kominfo RI, serta solusi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication) berbasis konsultasi teknis & survei lokasi.';

  return (
    <section id="harga" className="py-24 bg-slate-50 border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-10">
          <div className="max-w-3xl">
            <div className="flex flex-wrap items-center gap-2 text-xs font-extrabold text-blue-700 uppercase tracking-wider mb-2">
              <span>Internet Service Provider (KGSNet)</span>
              <span aria-hidden="true">·</span>
              <span>System Integrator, Security Solution & Telecommunication</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              {resolvedTitle}
            </h2>
            <p className="text-base text-slate-600 mt-3 leading-relaxed">
              {resolvedSummary}
            </p>
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <button
              type="button"
              onClick={() => onOpenAdminSection('pricing')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>+ Tambah / Edit Paket Harga di Admin CMS</span>
            </button>
          )}
        </div>

        {/* Legalitas ISP Box */}
        <div className="mb-10 p-5 sm:p-6 rounded-2xl bg-slate-900 text-white border border-slate-800">
          <div className="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-blue-400 mb-3">
            <FileCheck2 className="w-4 h-4 text-emerald-400" />
            <span>Legalitas Internet Service Provider :</span>
          </div>
          <ul className="space-y-2 text-sm text-slate-200">
            {ISP_LEGALITAS.map((item, idx) => (
              <li key={idx} className="flex items-start gap-2 leading-relaxed">
                <span className="text-emerald-400 font-bold shrink-0">–</span>
                <span>{item}</span>
              </li>
            ))}
          </ul>
        </div>

        {/* Filter Categories */}
        <div className="flex flex-wrap items-center gap-2 mb-10 pb-4 border-b border-slate-200 text-xs">
          <button
            onClick={() => setSelectedCategory('all')}
            className={`px-4 py-2 font-bold rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'all'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            Semua Segmen (ISP & Produk Lainnya)
          </button>
          <button
            onClick={() => setSelectedCategory('dedicated')}
            className={`px-4 py-2 font-bold rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'dedicated'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            1. Internet Dedicated
          </button>
          <button
            onClick={() => setSelectedCategory('bisnis')}
            className={`px-4 py-2 font-bold rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'bisnis'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            2. Internet Bisnis
          </button>
          <button
            onClick={() => setSelectedCategory('home')}
            className={`px-4 py-2 font-bold rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'home'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            3. Home Internet
          </button>
          <button
            onClick={() => setSelectedCategory('other')}
            className={`px-4 py-2 font-bold rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'other'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'bg-white text-blue-700 hover:bg-blue-50 border border-blue-200'
            }`}
          >
            4. Segmen Produk & Layanan Lainnya ({OTHER_PRODUCT_SERVICE_SEGMENTS.length})
          </button>
        </div>

        {/* BAGIAN 1: 3 Segmen Paket Internet Service Provider (ISP) */}
        {filteredPlans.length > 0 && (
          <div className="mb-16">
            <div className="mb-6">
              <span className="text-xs font-extrabold uppercase tracking-wider text-blue-600">
                Bagian 1 · Layanan Koneksi Internet Resmi Kominfo RI
              </span>
              <h3 className="text-2xl font-extrabold text-slate-900 mt-1">
                3 Segmen Paket Internet Service Provider (ISP)
              </h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {filteredPlans.map(plan => {
                const isFeatured = plan.isPopular;
                return (
                  <div
                    key={plan.id}
                    className={`rounded-2xl transition-all duration-200 flex flex-col justify-between ${
                      isFeatured
                        ? 'bg-white border-2 border-blue-600 shadow-lg relative -translate-y-1'
                        : 'bg-white border border-slate-200 shadow-xs hover:border-slate-300'
                    }`}
                  >
                    {isFeatured && (
                      <div className="bg-blue-600 text-white text-[11px] font-bold uppercase tracking-wider py-1 px-4 rounded-t-xl text-center">
                        {plan.badge || 'Solusi Utama Terlaris'}
                      </div>
                    )}

                    <div className="p-6 sm:p-7 flex-1 flex flex-col">
                      <div className="flex items-center justify-between mb-4">
                        <div className="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                          {getPlanIcon(plan.id)}
                        </div>
                        <span className="text-xs font-semibold text-slate-500">
                          {plan.targetAudience}
                        </span>
                      </div>

                      <h3 className="text-xl font-bold text-slate-900 mb-2 leading-snug">
                        {plan.name}
                      </h3>
                      <p className="text-xs text-slate-600 leading-relaxed mb-6">
                        {plan.subtitle}
                      </p>

                      <div className="bg-slate-50 rounded-xl p-3 border border-slate-200 flex items-center gap-2 mb-6 text-xs text-slate-700">
                        <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0" />
                        <span className="font-semibold">{plan.sla}</span>
                      </div>

                      <div className="space-y-3 mb-8 flex-1">
                        <div className="text-xs font-bold text-slate-900 uppercase tracking-wider">
                          Pilihan Kapasitas & Rincian Paket:
                        </div>
                        <ul className="space-y-2.5">
                          {plan.features.map((feat, idx) => (
                            <li
                              key={idx}
                              className="flex items-start gap-2.5 text-xs text-slate-700 leading-normal"
                            >
                              <Check className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                              <span>{feat}</span>
                            </li>
                          ))}
                        </ul>
                      </div>

                      <div className="pt-4 border-t border-slate-100 text-[11px] text-slate-500 italic mb-6">
                        <span className="font-medium text-slate-700 not-italic">
                          Rekomendasi:{' '}
                        </span>
                        {plan.recommendedFor}
                      </div>

                      <button
                        onClick={() => onRequestQuote(plan.name)}
                        className={`w-full py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer ${
                          isFeatured
                            ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'
                            : 'bg-slate-900 hover:bg-slate-800 text-white'
                        }`}
                      >
                        <span>Minta Penawaran Paket Ini</span>
                        <ArrowRight className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* BAGIAN 2: Segmen Produk dan Layanan Lainnya (Selain 3 Segmen ISP) — Tanpa Harga */}
        {showOtherSegments && (
          <div className="pt-6 border-t border-slate-200">
            <div className="mb-8 max-w-3xl">
              <span className="text-xs font-extrabold uppercase tracking-wider text-emerald-700">
                Bagian 2 · System Integrator, Security Solution & Telecommunication
              </span>
              <h3 className="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                Segmen Produk & Layanan Lainnya
              </h3>
              <p className="text-sm text-slate-600 mt-2 leading-relaxed">
                Selain 3 Segmen Paket Internet Service Provider (ISP), PT. Kreatif Global Solusindo melayani pengadaan, instalasi, dan integrasi produk serta layanan infrastruktur di bawah ini melalui konsultasi teknis dan survei lokasi sesuai kebutuhan proyek Anda.
              </p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
              {OTHER_PRODUCT_SERVICE_SEGMENTS.map(seg => (
                <div
                  key={seg.id}
                  className="bg-white rounded-2xl border border-slate-200 shadow-xs hover:border-blue-400 transition-all duration-200 p-6 sm:p-8 flex flex-col justify-between"
                >
                  <div>
                    {/* Top Bar */}
                    <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
                      <div className="flex items-center gap-3">
                        <div className="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                          {getOtherSegmentIcon(seg.id)}
                        </div>
                        <span
                          className={`px-3 py-1 rounded-full text-[11px] font-extrabold border ${getOtherSegmentBadgeStyle(
                            seg.id
                          )}`}
                        >
                          {seg.segmentLabel}
                        </span>
                      </div>
                      <span className="text-[11px] font-semibold text-slate-500">
                        {seg.targetAudience}
                      </span>
                    </div>

                    {/* Title & Subtitle */}
                    <h4 className="text-xl sm:text-2xl font-extrabold text-slate-900 mb-2">
                      {seg.name}
                    </h4>
                    <p className="text-xs sm:text-sm text-slate-600 leading-relaxed mb-5">
                      {seg.subtitle}
                    </p>

                    {/* Consultation Scheme Banner (No Price) */}
                    <div className="bg-slate-50 rounded-xl p-3.5 border border-slate-200 flex items-center gap-2.5 mb-6 text-xs text-slate-800">
                      <ClipboardCheck className="w-4 h-4 text-blue-600 shrink-0" />
                      <span className="font-bold">{seg.schemeLabel}</span>
                    </div>

                    {/* Sub-products & Services */}
                    <div className="space-y-3 mb-6">
                      <div className="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                        Daftar Produk & Layanan dalam Segmen Ini:
                      </div>
                      <div className="space-y-2.5">
                        {seg.items.map((item, idx) => (
                          <div
                            key={idx}
                            className="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80"
                          >
                            <div className="flex items-center gap-2 font-extrabold text-slate-900 text-xs sm:text-sm">
                              <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                              <span>{item.name}</span>
                            </div>
                            <p className="text-xs text-slate-600 leading-relaxed mt-1 pl-6">
                              {item.description}
                            </p>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Supported Brands */}
                    <div className="mb-6">
                      <div className="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Merek & Principal Pendukung:
                      </div>
                      <div className="flex flex-wrap gap-1.5">
                        {seg.brands.map((brand, bIdx) => (
                          <span
                            key={bIdx}
                            className="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-200"
                          >
                            {brand}
                          </span>
                        ))}
                      </div>
                    </div>
                  </div>

                  <div>
                    <div className="pt-4 border-t border-slate-100 text-[11px] text-slate-500 italic mb-5">
                      <span className="font-semibold text-slate-700 not-italic">
                        Peruntukan:{' '}
                      </span>
                      {seg.recommendedFor}
                    </div>

                    <button
                      onClick={() => onRequestQuote(`Konsultasi Segmen ${seg.name}`)}
                      className="w-full py-3.5 px-4 rounded-xl text-xs font-bold bg-slate-900 hover:bg-blue-600 text-white transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                      <span>Konsultasi & Minta Penawaran {seg.name}</span>
                      <ArrowRight className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Enterprise Bottom Banner */}
        <div className="mt-14 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-2xl p-8 text-white border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="space-y-2 max-w-2xl text-center md:text-left">
            <div className="text-xs text-blue-400 font-semibold uppercase tracking-wider">
              {companySettings.pricingBannerKicker ||
                DEFAULT_COMPANY_SETTINGS.pricingBannerKicker}
            </div>
            <h4 className="text-xl font-bold">
              {companySettings.pricingBannerTitle || DEFAULT_COMPANY_SETTINGS.pricingBannerTitle}
            </h4>
            <p className="text-xs text-slate-300 leading-relaxed">
              {companySettings.pricingBannerDesc || DEFAULT_COMPANY_SETTINGS.pricingBannerDesc}
            </p>
          </div>
          <button
            onClick={() => onRequestQuote('Kustom Tender & Konsultasi Teknis')}
            className="px-6 py-3 bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs rounded-xl transition-all shadow-sm shrink-0 cursor-pointer"
          >
            {companySettings.pricingBannerCta || DEFAULT_COMPANY_SETTINGS.pricingBannerCta}
          </button>
        </div>
      </div>
    </section>
  );
};
