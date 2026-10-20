import React, { useState } from 'react';
import {
  FAQ_DATA,
  FaqItem,
  StaticPageItem,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  formatWhatsAppNumber
} from '../data/companyData';
import {
  ChevronDown,
  HelpCircle,
  MessageCircle,
  ArrowUpRight,
  Edit3
} from 'lucide-react';

interface FaqSectionProps {
  onRequestQuote: () => void;
  pageContent?: StaticPageItem;
  faqs?: FaqItem[];
  companySettings?: CompanySettings;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const FaqSection: React.FC<FaqSectionProps> = ({
  onRequestQuote,
  pageContent,
  faqs = FAQ_DATA,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [openIds, setOpenIds] = useState<string[]>(['faq-1', 'faq-2']);
  const [activeCategory, setActiveCategory] = useState<string>('Semua');

  const toggleFaq = (id: string) => {
    setOpenIds(prev =>
      prev.includes(id) ? prev.filter(item => item !== id) : [...prev, id]
    );
  };

  const categories = [
    'Semua',
    'Umum',
    'Internet ISP',
    'Keamanan & Tower',
    'Kontrak & Legalitas'
  ];

  const filteredFaq = faqs.filter(item => {
    if (activeCategory === 'Semua') return true;
    return item.category === activeCategory;
  });

  const waNum = formatWhatsAppNumber(companySettings.whatsapp);

  return (
    <section id="faq" className="py-24 bg-white border-b border-slate-200">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-12">
          <div className="flex items-center justify-center gap-2 text-xs font-semibold text-blue-700 uppercase tracking-wider mb-2">
            <HelpCircle className="w-4 h-4 text-blue-600" />
            <span>Frequently Asked Questions</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            {pageContent?.title || 'Pertanyaan Umum & Jawaban Teknis'}
          </h2>
          <p className="text-sm text-slate-600 mt-3 leading-relaxed">
            {pageContent?.summary ||
              'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.'}
          </p>
          {pageContent?.content && (
            <div className="mt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line">
              {pageContent.content}
            </div>
          )}

          {onOpenAdminSection && isLiveEditMode && (
            <div className="mt-4">
              <button
                type="button"
                onClick={() => onOpenAdminSection('faq')}
                className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>+ Tambah / Edit Pertanyaan FAQ di Admin CMS</span>
              </button>
            </div>
          )}
        </div>

        {/* Category Tabs */}
        <div className="flex flex-wrap items-center justify-center gap-2 mb-10" data-live-nav="true">
          {categories.map(cat => (
            <button
              key={cat}
              onClick={() => setActiveCategory(cat)}
              className={`px-4 py-2 text-xs font-medium rounded-lg transition-colors cursor-pointer ${
                activeCategory === cat
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* FAQ Accordion List */}
        <div className="space-y-3">
          {filteredFaq.map(faq => {
            const isOpen = isLiveEditMode || openIds.includes(faq.id);
            return (
              <div
                key={faq.id}
                className={`border rounded-xl transition-all duration-200 overflow-hidden ${
                  isOpen
                    ? 'border-blue-300 bg-blue-50/20 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                }`}
              >
                <button
                  onClick={() => toggleFaq(faq.id)}
                  className="w-full text-left px-6 py-5 flex items-center justify-between gap-4 cursor-pointer"
                  aria-expanded={isOpen}
                >
                  <div className="space-y-1">
                    <span className="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">
                      {faq.category}
                    </span>
                    <h3 className="text-base font-bold text-slate-900">{faq.question}</h3>
                  </div>
                  <div
                    className={`w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200 ${
                      isOpen ? 'rotate-180 bg-blue-600 text-white' : 'bg-slate-100 text-slate-500'
                    }`}
                  >
                    <ChevronDown className="w-4 h-4" />
                  </div>
                </button>

                {isOpen && (
                  <div className="px-6 pb-6 pt-1 text-slate-600 text-xs sm:text-sm leading-relaxed border-t border-slate-100">
                    <p>{faq.answer}</p>
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {/* Support & Direct Help Box */}
        <div className="mt-14 p-6 sm:p-8 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6">
          <div className="space-y-1 text-center sm:text-left">
            <h4 className="text-base font-bold text-slate-900">
              {companySettings.faqSupportTitle || DEFAULT_COMPANY_SETTINGS.faqSupportTitle}
            </h4>
            <p className="text-xs text-slate-600">
              {companySettings.faqSupportDesc || DEFAULT_COMPANY_SETTINGS.faqSupportDesc}
            </p>
          </div>

          <div className="flex items-center gap-3 shrink-0">
            <a
              href={`https://wa.me/${waNum}?text=Halo%20PT%20Kreatif%20Global%20Solusindo,%20saya%20ingin%20konsultasi%20solusi%20teknologi`}
              target="_blank"
              rel="noopener noreferrer"
              className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg flex items-center gap-2 shadow-xs transition"
            >
              <MessageCircle className="w-4 h-4" />
              <span>WhatsApp Official</span>
            </a>
            <button
              onClick={onRequestQuote}
              className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg flex items-center gap-2 shadow-xs transition cursor-pointer"
            >
              <span>Form Konsultasi</span>
              <ArrowUpRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </section>
  );
};
