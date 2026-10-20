import React, { useState } from 'react';
import { ServiceItem, ServiceCategory, SERVICE_CATEGORIES, StaticPageItem } from '../data/companyData';
import { ArrowUpRight, ShieldCheck, ChevronRight, Edit3 } from 'lucide-react';
import { ServiceDetailModal } from './ServiceDetailModal';

interface ServicesSectionProps {
  services: ServiceItem[];
  categories?: ServiceCategory[];
  onRequestQuote: (serviceName: string) => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const ServicesSection: React.FC<ServicesSectionProps> = ({
  services,
  categories = SERVICE_CATEGORIES,
  onRequestQuote,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [activeServiceModal, setActiveServiceModal] = useState<ServiceItem | null>(null);

  const filteredServices = selectedCategory === 'all'
    ? services.filter(s => s.isPublished)
    : services.filter(s => s.isPublished && s.categoryId === selectedCategory);

  return (
    <section id="layanan" className="py-20 bg-slate-50 border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-12">
          <div className="max-w-3xl">
            <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">
              Katalog Solusi & Layanan B2B Terpadu
            </div>
            <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || '5 Segmen Layanan & Solusi Terintegrasi'}
            </h2>
            <p className="text-sm text-slate-600 mt-2 leading-relaxed">
              {pageContent?.summary || 'Infrastruktur komputasi, telekomunikasi radio & PABX, surveillance keamanan CCTV, internet ISP KGSNet, hingga fabrikasi tower dan sistem proteksi petir.'}
            </p>
            {pageContent?.content && (
              <div className="mt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                {pageContent.content}
              </div>
            )}
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <div className="flex flex-wrap gap-2 shrink-0">
              <button
                type="button"
                onClick={() => onOpenAdminSection('services')}
                className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Tambah / Edit Layanan (22 Item)</span>
              </button>
              <button
                type="button"
                onClick={() => onOpenAdminSection('categories')}
                className="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>Edit 5 Segmen Kategori</span>
              </button>
            </div>
          )}
        </div>

        {/* Category Segmented Tabs */}
        <div className="flex flex-wrap items-center gap-1.5 p-1.5 bg-slate-200/80 rounded-xl mb-10 w-fit max-w-full overflow-x-auto" data-live-nav="true">
          <button
            onClick={() => setSelectedCategory('all')}
            className={`px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors cursor-pointer whitespace-nowrap ${
              selectedCategory === 'all'
                ? 'bg-white text-slate-900 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            Semua Segmen ({services.filter(s => s.isPublished).length})
          </button>
          {categories.map((cat) => (
            <button
              key={cat.id}
              onClick={() => setSelectedCategory(cat.id)}
              className={`px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors cursor-pointer whitespace-nowrap ${
                selectedCategory === cat.id
                  ? 'bg-white text-slate-900 shadow-xs'
                  : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {cat.segmentNumber}. {cat.name}
            </button>
          ))}
        </div>

        {/* Services Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
          {filteredServices.map((srv) => (
            <div
              key={srv.id}
              className="bg-white rounded-2xl border border-slate-200 p-6 sm:p-7 flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all group"
            >
              <div>
                {/* Clean Metadata Header */}
                <div className="flex items-center justify-between text-xs text-slate-500 mb-3.5">
                  <span className="font-semibold text-blue-600">{srv.categoryName}</span>
                  <div className="flex items-center gap-1.5 text-emerald-700 font-medium text-xs">
                    <ShieldCheck className="w-4 h-4 shrink-0" />
                    <span>Standar Teruji</span>
                  </div>
                </div>

                {/* Service Title */}
                <h3 className="text-xl font-bold text-slate-900 mb-2.5 leading-snug group-hover:text-blue-700 transition-colors">
                  {srv.name}
                </h3>

                {/* Summary */}
                <p className="text-base text-slate-600 leading-relaxed mb-5">
                  {srv.summary}
                </p>

                {/* Verified Brands */}
                {srv.brands && srv.brands.length > 0 && (
                  <div className="text-xs text-slate-500 mb-5 pt-3.5 border-t border-slate-100">
                    <span className="font-semibold text-slate-700">Brand Terkait: </span>
                    <span>{srv.brands.join(', ')}</span>
                  </div>
                )}
              </div>

              {/* Action Buttons */}
              <div className="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <button
                  onClick={() => setActiveServiceModal(srv)}
                  className="text-base font-semibold text-blue-600 hover:text-blue-800 transition flex items-center gap-1 cursor-pointer py-1.5"
                >
                  <span>Spesifikasi Detail</span>
                  <ChevronRight className="w-4 h-4" />
                </button>

                <button
                  onClick={() => onRequestQuote(srv.name)}
                  className="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-base font-semibold transition cursor-pointer"
                >
                  Minta Penawaran
                </button>
              </div>
            </div>
          ))}
        </div>

      </div>

      {/* Service Detail Modal */}
      {activeServiceModal && (
        <ServiceDetailModal
          service={activeServiceModal}
          onClose={() => setActiveServiceModal(null)}
          onRequestQuote={onRequestQuote}
        />
      )}
    </section>
  );
};
