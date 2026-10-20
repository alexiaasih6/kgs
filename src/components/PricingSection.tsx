import React, { useState } from 'react';
import {
  B2B_PRICING_PLANS,
  PricingPlanItem,
  StaticPageItem,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS
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
  Edit3
} from 'lucide-react';

interface PricingSectionProps {
  onRequestQuote: (planName: string) => void;
  pageContent?: StaticPageItem;
  pricingPlans?: PricingPlanItem[];
  companySettings?: CompanySettings;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const PricingSection: React.FC<PricingSectionProps> = ({
  onRequestQuote,
  pageContent,
  pricingPlans = B2B_PRICING_PLANS,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [selectedCategory, setSelectedCategory] = useState<
    'all' | 'internet' | 'security' | 'infrastructure'
  >('all');

  const filteredPlans = pricingPlans.filter(plan => {
    if (selectedCategory === 'all') return true;
    if (selectedCategory === 'internet') {
      return (
        plan.id === 'plan-internet' ||
        plan.name.toLowerCase().includes('internet') ||
        plan.name.toLowerCase().includes('isp')
      );
    }
    if (selectedCategory === 'security') {
      return (
        plan.id === 'plan-security' ||
        plan.name.toLowerCase().includes('security') ||
        plan.name.toLowerCase().includes('cctv') ||
        plan.name.toLowerCase().includes('surveillance')
      );
    }
    if (selectedCategory === 'infrastructure') {
      return (
        plan.id !== 'plan-internet' &&
        plan.id !== 'plan-security'
      );
    }
    return true;
  });

  const getPlanIcon = (id: string) => {
    switch (id) {
      case 'plan-internet':
        return <Server className="w-5 h-5 text-blue-600" />;
      case 'plan-security':
        return <Shield className="w-5 h-5 text-emerald-600" />;
      case 'plan-tower-radio':
        return <Radio className="w-5 h-5 text-indigo-600" />;
      case 'plan-solar-power':
        return <Sun className="w-5 h-5 text-amber-600" />;
      case 'plan-custom-tender':
        return <Building2 className="w-5 h-5 text-cyan-600" />;
      default:
        return <Zap className="w-5 h-5 text-blue-600" />;
    }
  };

  return (
    <section id="harga" className="py-24 bg-slate-50 border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-12">
          <div className="max-w-3xl">
            <div className="flex items-center gap-2 text-xs font-semibold text-blue-700 uppercase tracking-wider mb-2">
              <span>Paket Solusi Bisnis & Korporasi</span>
              <span aria-hidden="true">·</span>
              <span>Skema B2B & Enterprise</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Pilihan Paket Solusi & Layanan B2B'}
            </h2>
            <p className="text-base text-slate-600 mt-3 leading-relaxed">
              {pageContent?.summary ||
                'Struktur paket transparan yang dirancang khusus untuk kebutuhan konektivitas bisnis, keamanan terpadu, infrastruktur remote site, dan pengadaan proyek pemerintah/BUMN dengan SLA terjamin.'}
            </p>
            {pageContent?.content && (
              <div className="mt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                {pageContent.content}
              </div>
            )}
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <button
              type="button"
              onClick={() => onOpenAdminSection('pricing')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>+ Tambah / Edit Paket Harga B2B di Admin CMS</span>
            </button>
          )}
        </div>

        {/* Filter Categories */}
        <div className="flex flex-wrap items-center gap-2 mb-10 pb-4 border-b border-slate-200 text-xs">
          <button
            onClick={() => setSelectedCategory('all')}
            className={`px-4 py-2 font-medium rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'all'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            Semua Paket Solusi ({pricingPlans.length})
          </button>
          <button
            onClick={() => setSelectedCategory('internet')}
            className={`px-4 py-2 font-medium rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'internet'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            Dedicated Internet (ISP)
          </button>
          <button
            onClick={() => setSelectedCategory('security')}
            className={`px-4 py-2 font-medium rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'security'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            Surveillance & Security
          </button>
          <button
            onClick={() => setSelectedCategory('infrastructure')}
            className={`px-4 py-2 font-medium rounded-lg transition-colors cursor-pointer ${
              selectedCategory === 'infrastructure'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
            }`}
          >
            Tower, Power & Turnkey
          </button>
        </div>

        {/* Cards Grid */}
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
                {/* Popular Ribbon if applicable */}
                {isFeatured && (
                  <div className="bg-blue-600 text-white text-[11px] font-bold uppercase tracking-wider py-1 px-4 rounded-t-xl text-center">
                    {plan.badge || 'Solusi Utama Terlaris'}
                  </div>
                )}

                <div className="p-6 sm:p-7 flex-1 flex flex-col">
                  {/* Top Bar with Icon & Audience */}
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                      {getPlanIcon(plan.id)}
                    </div>
                    <span className="text-xs font-semibold text-slate-500">
                      {plan.targetAudience}
                    </span>
                  </div>

                  {/* Plan Name & Tagline */}
                  <h3 className="text-xl font-bold text-slate-900 mb-2 leading-snug">
                    {plan.name}
                  </h3>
                  <p className="text-xs text-slate-600 leading-relaxed mb-6">{plan.subtitle}</p>

                  {/* SLA / Guarantee Banner */}
                  <div className="bg-slate-50 rounded-xl p-3 border border-slate-200 flex items-center gap-2 mb-6 text-xs text-slate-700">
                    <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span className="font-semibold">{plan.sla}</span>
                  </div>

                  {/* Features List */}
                  <div className="space-y-3 mb-8 flex-1">
                    <div className="text-xs font-bold text-slate-900 uppercase tracking-wider">
                      Cakupan & Keunggulan Layanan:
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

                  {/* Recommended Note */}
                  <div className="pt-4 border-t border-slate-100 text-[11px] text-slate-500 italic mb-6">
                    <span className="font-medium text-slate-700 not-italic">Rekomendasi: </span>
                    {plan.recommendedFor}
                  </div>

                  {/* CTA Action */}
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
