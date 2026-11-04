import React, { useState } from 'react';
import {
  PromoItem,
  PartnerItem,
  CertificationItem,
  TestimonialItem,
  StaticPageItem,
  InquiryMessage,
  COMPANY_INFO,
  SERVICE_CATEGORIES,
} from '../data/companyData';
import {
  Sparkles,
  CheckCircle2,
  ArrowRight,
  Clock,
  Handshake,
  Award,
  ShieldCheck,
  Star,
  Quote,
  Building2,
  Edit3,
  ExternalLink,
  Send,
  FileText,
  MessageSquarePlus,
  Wifi,
  MapPin,
  Phone,
  Mail,
  User,
} from 'lucide-react';

interface PromoSectionProps {
  promos: PromoItem[];
  onRequestQuote: (serviceName?: string) => void;
  onAddInquiry?: (inquiry: Omit<InquiryMessage, 'id' | 'createdAt' | 'status'>) => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const PromoSection: React.FC<PromoSectionProps> = ({
  promos,
  onRequestQuote,
  onAddInquiry,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection,
}) => {
  const [selectedCategory, setSelectedCategory] = useState<string>('Semua');
  const [formData, setFormData] = useState({
    name: '',
    company: '',
    email: '',
    phone: '',
    service: 'Data Networking',
    location: '',
    message: '',
  });
  const [submitted, setSubmitted] = useState(false);

  const activePromos = promos.filter(p => p.isActive !== false);
  const categories = ['Semua', 'Internet ISP', 'CCTV & Security', 'Networking & Fiber', 'PBX & Tower'];

  const bidangOptions = [
    ...SERVICE_CATEGORIES.map(c => c.name),
    ...activePromos.map(p => `Promo: ${p.title}`),
  ];

  const filteredPromos =
    selectedCategory === 'Semua'
      ? activePromos
      : activePromos.filter(p => p.category === selectedCategory);

  const handleSubmitQuoteForm = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name.trim() || !formData.phone.trim()) return;

    if (onAddInquiry) {
      onAddInquiry({
        name: formData.name,
        company: formData.company || 'Perorangan / Instansi',
        email: formData.email || '-',
        phone: formData.phone,
        serviceId: formData.service,
        location: formData.location || 'Indonesia',
        message: formData.message,
      });
    }
    setSubmitted(true);
  };

  const handleSelectPromoToForm = (promoTitle: string) => {
    setFormData(prev => ({
      ...prev,
      service: `Promo: ${promoTitle}`,
    }));
    const formEl = document.getElementById('form-permintaan-penawaran');
    if (formEl) {
      formEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      onRequestQuote(`Promo: ${promoTitle}`);
    }
  };

  const waQuoteUrl = `https://wa.me/${COMPANY_INFO.whatsapp}?text=${encodeURIComponent(
    `Halo PT. Kreatif Global Solusindo, saya ingin mengajukan Permintaan Penawaran Harga:\n\nNama: ${formData.name}\nPerusahaan/Instansi: ${formData.company || '-'}\nEmail: ${formData.email || '-'}\nNo. WA: ${formData.phone}\nBidang/Layanan: ${formData.service}\nLokasi: ${formData.location || '-'}\nKebutuhan: ${formData.message || '-'}`
  )}`;

  return (
    <section
      id="promo"
      className="py-20 bg-gradient-to-b from-slate-50 via-white to-blue-50/40 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-t border-slate-200/80 dark:border-slate-800/80 scroll-mt-24"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {isLiveEditMode && onOpenAdminSection && (
          <div
            className="mb-8 flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800"
            data-live-editor-ui="true"
          >
            <div className="text-xs font-semibold text-blue-900 dark:text-blue-200">
              💡 <strong>Mode Edit Langsung Promo:</strong> Anda dapat menambah, mengubah, atau menonaktifkan kartu Promo melalui Admin CMS.
            </div>
            <button
              type="button"
              onClick={() => onOpenAdminSection('promos')}
              className="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Kelola Promo di Admin CMS</span>
            </button>
          </div>
        )}

        <div className="text-center max-w-3xl mx-auto mb-12">
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-4 border border-blue-200 dark:border-blue-800">
            <Sparkles className="w-3.5 h-3.5" />
            <span>Promo & Form Permintaan Penawaran Harga</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            {pageContent?.title || 'Form Permintaan Penawaran Harga & Promo Spesial'}
          </h2>
          <p className="mt-3 text-base text-slate-600 dark:text-slate-300 leading-relaxed">
            {pageContent?.summary ||
              ' Silakan isi formulir permintaan penawaran harga (Request for Quotation / BOQ) di bawah ini untuk mendapatkan estimasi biaya terbaik sesuai kebutuhan proyek atau koneksi internet Anda.'}
          </p>
        </div>

        {/* FORM PERMINTAAN PENAWARAN HARGA (UTAMA DI HALAMAN PROMO) */}
        <div
          id="form-permintaan-penawaran"
          className="mb-16 rounded-3xl bg-white dark:bg-slate-900 border-2 border-blue-500/70 dark:border-blue-500/60 shadow-xl overflow-hidden scroll-mt-28"
        >
          <div className="grid grid-cols-1 lg:grid-cols-12">
            {/* Left Info Column */}
            <div className="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-900 to-blue-950 text-white p-6 sm:p-8 flex flex-col justify-between">
              <div>
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                  <FileText className="w-3.5 h-3.5" />
                  <span>Request For Quotation (RFQ)</span>
                </span>
                <h3 className="text-2xl font-extrabold text-white leading-snug mb-3">
                  Formulir Permintaan Penawaran Harga Resmi
                </h3>
                <p className="text-xs sm:text-sm text-slate-300 leading-relaxed mb-6">
                  Tim Pre-Sales Engineer & Account Manager PT. Kreatif Global Solusindo siap menyusun Rencana Anggaran Biaya (RAB/BOQ), jadwal survei lokasi, dan spesifikasi teknis untuk 7 bidang layanan kami:
                </p>

                <ul className="space-y-2.5 text-xs sm:text-sm text-slate-200 mb-6">
                  {SERVICE_CATEGORIES.map(cat => (
                    <li key={cat.id} className="flex items-center gap-2.5">
                      <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                      <span className="font-semibold">{cat.name}</span>
                    </li>
                  ))}
                </ul>
              </div>

              <div className="p-4 rounded-2xl bg-amber-500/15 border border-amber-400/40">
                <div className="flex items-start gap-2.5">
                  <Handshake className="w-5 h-5 text-amber-400 shrink-0 mt-0.5" />
                  <p className="text-xs font-extrabold text-amber-300 leading-relaxed">
                    "KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia"
                  </p>
                </div>
              </div>
            </div>

            {/* Right Form Column */}
            <div className="lg:col-span-7 p-6 sm:p-8 lg:p-10">
              {submitted ? (
                <div className="h-full flex flex-col items-center justify-center text-center py-8">
                  <div className="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                    <CheckCircle2 className="w-9 h-9" />
                  </div>
                  <h4 className="text-xl font-extrabold text-slate-900 dark:text-white mb-2">
                    Permintaan Penawaran Harga Berhasil Dikirim!
                  </h4>
                  <p className="text-sm text-slate-600 dark:text-slate-300 max-w-md mb-6 leading-relaxed">
                    Data permintaan penawaran Anda untuk layanan <strong>{formData.service}</strong> telah tercatat di sistem kami. Anda juga dapat langsung meneruskan ke WhatsApp tim kami agar direspon detik ini juga.
                  </p>
                  <div className="flex flex-wrap items-center justify-center gap-3">
                    <a
                      href={waQuoteUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold inline-flex items-center gap-2 shadow-sm"
                    >
                      <Send className="w-4 h-4" />
                      <span>Kirim Cepat via WhatsApp Sekarang</span>
                    </a>
                    <button
                      type="button"
                      onClick={() => {
                        setSubmitted(false);
                        setFormData({
                          name: '',
                          company: '',
                          email: '',
                          phone: '',
                          service: 'Data Networking',
                          location: '',
                          message: '',
                        });
                      }}
                      className="px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold cursor-pointer"
                    >
                      Buat Permintaan Baru
                    </button>
                  </div>
                </div>
              ) : (
                <form onSubmit={handleSubmitQuoteForm} className="space-y-4">
                  <div className="border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                    <h4 className="text-lg font-extrabold text-slate-900 dark:text-white">
                      Isi Data Permintaan Penawaran Harga
                    </h4>
                    <p className="text-xs text-slate-500 dark:text-slate-400">
                      Lengkapi informasi di bawah ini untuk mendapatkan penawaran harga terbaik dari PT. KGS.
                    </p>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Lengkap / PIC *
                      </label>
                      <div className="relative">
                        <User className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                          type="text"
                          required
                          value={formData.name}
                          onChange={e => setFormData({ ...formData, name: e.target.value })}
                          placeholder="Contoh: Budi Santoso"
                          className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Perusahaan / Instansi / Pribadi
                      </label>
                      <div className="relative">
                        <Building2 className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                          type="text"
                          value={formData.company}
                          onChange={e => setFormData({ ...formData, company: e.target.value })}
                          placeholder="Contoh: PT. Maju Bersama / Dinas..."
                          className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                        />
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nomor WhatsApp / Telepon Aktif *
                      </label>
                      <div className="relative">
                        <Phone className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                          type="tel"
                          required
                          value={formData.phone}
                          onChange={e => setFormData({ ...formData, phone: e.target.value })}
                          placeholder="Contoh: 081144200720"
                          className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Alamat Email
                      </label>
                      <div className="relative">
                        <Mail className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                          type="email"
                          value={formData.email}
                          onChange={e => setFormData({ ...formData, email: e.target.value })}
                          placeholder="nama@perusahaan.co.id"
                          className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                        />
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Pilih Bidang Pekerjaan / Promo *
                      </label>
                      <select
                        value={formData.service}
                        onChange={e => setFormData({ ...formData, service: e.target.value })}
                        className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                      >
                        {bidangOptions.map((opt, idx) => (
                          <option key={idx} value={opt}>
                            {opt}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Lokasi Proyek / Kota Pemasangan
                      </label>
                      <div className="relative">
                        <MapPin className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                          type="text"
                          value={formData.location}
                          onChange={e => setFormData({ ...formData, location: e.target.value })}
                          placeholder="Contoh: Makassar / Gowa / Morowali"
                          className="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                        />
                      </div>
                    </div>
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                      Rincian Kebutuhan / Spesifikasi / Jumlah Titik *
                    </label>
                    <textarea
                      rows={3}
                      required
                      value={formData.message}
                      onChange={e => setFormData({ ...formData, message: e.target.value })}
                      placeholder="Tuliskan rincian kebutuhan Anda (misal: kebutuhan bandwidth internet dedicated 100 Mbps, instalasi 16 titik CCTV IP Camera, PABX, atau Radio Repeater)..."
                      className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                    />
                  </div>

                  <div className="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <button
                      type="submit"
                      className="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-extrabold inline-flex items-center justify-center gap-2 transition cursor-pointer shadow-md"
                    >
                      <Send className="w-4 h-4" />
                      <span>Kirim Permintaan Penawaran Harga</span>
                    </button>

                    <a
                      href={waQuoteUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 transition"
                    >
                      <span>Kirim Langsung via WhatsApp</span>
                      <ArrowRight className="w-3.5 h-3.5" />
                    </a>
                  </div>
                </form>
              )}
            </div>
          </div>
        </div>

        {/* Katalog Paket Promo Spesial */}
        <div className="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h3 className="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
              Daftar Paket Promo Unggulan
            </h3>
            <p className="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
              Klik pada paket promo di bawah ini untuk memilih promo langsung ke Formulir Permintaan Penawaran Harga di atas.
            </p>
          </div>
          {/* Category Filter Tabs */}
          <div className="flex flex-wrap items-center gap-2">
            {categories.map(cat => (
              <button
                key={cat}
                type="button"
                onClick={() => setSelectedCategory(cat)}
                className={`px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer border ${
                  selectedCategory === cat
                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                    : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-blue-400'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Promo Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
          {filteredPromos.map(promo => (
            <div
              key={promo.id}
              className={`rounded-2xl p-6 sm:p-8 flex flex-col justify-between transition-all border relative overflow-hidden ${
                promo.isHighlight
                  ? 'bg-white dark:bg-slate-900 border-blue-500/80 dark:border-blue-500/70 shadow-lg ring-1 ring-blue-500/20'
                  : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm hover:border-blue-400'
              }`}
            >
              <div>
                <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-xs font-extrabold border border-blue-200/80 dark:border-blue-800">
                    {promo.badge || promo.category}
                  </span>
                  {promo.discountLabel && (
                    <span className="px-3 py-1 rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 text-xs font-extrabold">
                      {promo.discountLabel}
                    </span>
                  )}
                </div>

                <h3 className="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white leading-snug mb-2">
                  {promo.title}
                </h3>

                <p className="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-5">
                  {promo.description}
                </p>

                <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 mb-5 flex flex-wrap items-baseline justify-between gap-2">
                  <div>
                    {promo.originalPrice && (
                      <div className="text-xs text-slate-400 line-through font-medium">
                        {promo.originalPrice}
                      </div>
                    )}
                    <div className="text-lg sm:text-xl font-black text-blue-600 dark:text-blue-400">
                      {promo.promoPrice}
                    </div>
                  </div>
                  {promo.validUntil && (
                    <div className="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                      <Clock className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                      <span>{promo.validUntil}</span>
                    </div>
                  )}
                </div>

                <ul className="space-y-2.5 mb-6">
                  {(promo.features || []).map((feat, idx) => (
                    <li
                      key={idx}
                      className="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700 dark:text-slate-200"
                    >
                      <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                      <span>{feat}</span>
                    </li>
                  ))}
                </ul>
              </div>

              <div className="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                  Kategori: {promo.category}
                </span>
                <button
                  type="button"
                  onClick={() => handleSelectPromoToForm(promo.title)}
                  className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold inline-flex items-center gap-2 transition cursor-pointer shadow-xs"
                >
                  <span>{promo.ctaText || 'Minta Penawaran Promo Ini'}</span>
                  <ArrowRight className="w-4 h-4" />
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

interface KemitraanSectionProps {
  partners: PartnerItem[];
  certifications: CertificationItem[];
  onRequestQuote: (serviceName?: string) => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const KemitraanSection: React.FC<KemitraanSectionProps> = ({
  partners,
  certifications,
  onRequestQuote,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection,
}) => {
  const waResellerLink = `https://wa.me/${COMPANY_INFO.whatsapp}?text=${encodeURIComponent(
    'Halo PT. Kreatif Global Solusindo (KGSNet), saya tertarik untuk mendaftar program kerjasama kemitraan reseller layanan internet KGSNet.'
  )}`;

  return (
    <section
      id="kemitraan"
      className="py-20 bg-white dark:bg-slate-900 border-t border-slate-200/80 dark:border-slate-800/80 scroll-mt-24"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {isLiveEditMode && onOpenAdminSection && (
          <div
            className="mb-8 flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800"
            data-live-editor-ui="true"
          >
            <div className="text-xs font-semibold text-indigo-900 dark:text-indigo-200">
              💡 <strong>Mode Edit Langsung Kemitraan:</strong> Kelola daftar Mitra Teknologi, Prinsipal Resmi & Sertifikasi di Admin CMS.
            </div>
            <button
              type="button"
              onClick={() => onOpenAdminSection('partners_admin')}
              className="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Kelola Kemitraan di Admin CMS</span>
            </button>
          </div>
        )}

        <div className="text-center max-w-3xl mx-auto mb-10">
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-4 border border-blue-200 dark:border-blue-800">
            <Handshake className="w-3.5 h-3.5" />
            <span>Program Kerjasama Kemitraan KGSNet & Sistem Integrator</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            {pageContent?.title || 'Membuka Kerjasama Kemitraan di Seluruh Indonesia'}
          </h2>
          <p className="mt-3 text-base text-slate-600 dark:text-slate-300 leading-relaxed">
            {pageContent?.summary ||
              'PT. Kreatif Global Solusindo membuka peluang kerjasama kemitraan reseller layanan internet (KGSNet) serta kolaborasi proyek infrastruktur IT & telekomunikasi di seluruh Indonesia.'}
          </p>
        </div>

        {/* HIGHLIGHT UTAMA: KALIMAT WAJIB KEMITRAAN RESELLER KGSNET */}
        <div className="mb-14 rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white p-6 sm:p-10 border-2 border-amber-400/60 shadow-2xl relative overflow-hidden">
          <div className="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div className="max-w-3xl">
              <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/40 text-xs font-extrabold uppercase tracking-wider mb-4">
                <Wifi className="w-4 h-4 text-amber-400" />
                <span>Program Kemitraan Reseller Resmi ISP APJII</span>
              </div>

              <h3 className="text-2xl sm:text-3xl lg:text-4xl font-black text-amber-300 leading-tight mb-4">
                &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;
              </h3>

              <p className="text-sm sm:text-base text-slate-200 leading-relaxed mb-6">
                Kami membuka kerjasama kemitraan bagi pengusaha jaringan lokal, pengelola RT/RW Net, pengelola kawasan bisnis/perumahan, maupun badan usaha di seluruh wilayah Indonesia yang ingin mengembangkan bisnis layanan internet dengan dukungan legalitas ISP resmi APJII, suplai bandwidth dedicated Fiber Optic / Wireless Point-to-Point, serta pendampingan teknis NOC 24/7 dari PT. Kreatif Global Solusindo.
              </p>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-bold text-slate-100">
                <div className="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex items-center gap-2.5">
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Payung Legalitas Resmi ISP Anggota APJII</span>
                </div>
                <div className="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex items-center gap-2.5">
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Suplai Bandwidth Dedicated Stabil & SLA 99.5%</span>
                </div>
                <div className="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex items-center gap-2.5">
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Dukungan Engineering, MikroTik & NOC 24/7</span>
                </div>
              </div>
            </div>

            <div className="flex flex-col gap-3 shrink-0 lg:w-72 bg-slate-900/90 p-6 rounded-2xl border border-slate-700">
              <div className="text-xs font-bold text-amber-300 uppercase tracking-wider">
                Bergabung Jadi Mitra KGSNet
              </div>
              <p className="text-xs text-slate-300 leading-relaxed">
                Hubungi tim kemitraan kami untuk informasi syarat, skema bagi hasil / subnet reseller, dan ketersediaan jaringan di kota Anda.
              </p>
              <a
                href={waResellerLink}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition shadow-md"
              >
                <span>Daftar Kemitraan via WA</span>
                <ArrowRight className="w-4 h-4" />
              </a>
              <button
                type="button"
                onClick={() =>
                  onRequestQuote('Kemitraan Reseller Layanan Internet KGSNet Seluruh Indonesia')
                }
                className="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition cursor-pointer"
              >
                <span>Isi Form Pengajuan Mitra</span>
              </button>
            </div>
          </div>
        </div>

        {/* Lisensi & Sertifikasi Kemitraan Resmi */}
        <div className="mb-12">
          <h3 className="text-lg font-extrabold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
            <Award className="w-5 h-5 text-blue-600" />
            <span>Lisensi, Keanggotaan & Otorisasi Prinsipal</span>
          </h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {certifications.map(cert => (
              <div
                key={cert.id}
                className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700/80 flex flex-col justify-between gap-3 hover:border-blue-400 transition"
              >
                <div>
                  <div className="flex items-center justify-between gap-2 mb-2">
                    <span className="px-2.5 py-1 rounded-md bg-blue-600/10 text-blue-700 dark:text-blue-300 text-[11px] font-extrabold border border-blue-500/20">
                      {cert.issuerOrBrand}
                    </span>
                    {cert.yearOrValidity && (
                      <span className="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <ShieldCheck className="w-3.5 h-3.5" />
                        <span>{cert.yearOrValidity}</span>
                      </span>
                    )}
                  </div>
                  <h4 className="font-extrabold text-base text-slate-900 dark:text-white leading-snug">
                    {cert.title}
                  </h4>
                  {cert.description && (
                    <p className="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                      {cert.description}
                    </p>
                  )}
                </div>
                <div className="pt-2 border-t border-slate-200/70 dark:border-slate-700/60 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                  <span>Pemegang: {cert.holder}</span>
                  {cert.linkUrl && (
                    <a
                      href={cert.linkUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 dark:text-blue-400 font-bold inline-flex items-center gap-1 hover:underline"
                    >
                      <span>Verifikasi</span>
                      <ExternalLink className="w-3 h-3" />
                    </a>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Daftar Mitra Prinsipal Teknologi */}
        <div>
          <h3 className="text-lg font-extrabold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
            <Handshake className="w-5 h-5 text-blue-600" />
            <span>Mitra Prinsipal & Brand Teknologi Pendukung</span>
          </h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {partners.map(partner => (
              <div
                key={partner.id}
                className="p-5 rounded-2xl bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 shadow-2xs hover:shadow-md hover:border-blue-400 transition flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between gap-2 mb-2">
                    <span className="text-[11px] font-bold px-2.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                      {partner.serviceType}
                    </span>
                  </div>
                  <h4 className="text-base font-extrabold text-slate-900 dark:text-white">
                    {partner.name}
                  </h4>
                  <p className="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-1">
                    {partner.role}
                  </p>
                </div>
                {partner.linkUrl && (
                  <div className="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/70">
                    <a
                      href={partner.linkUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 inline-flex items-center gap-1"
                    >
                      <span>Website Prinsipal</span>
                      <ExternalLink className="w-3 h-3" />
                    </a>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

interface TestimoniSectionProps {
  testimonials: TestimonialItem[];
  onAddTestimonial?: (testimonial: Omit<TestimonialItem, 'id'>) => void;
  onRequestQuote: (serviceName?: string) => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const TestimoniSection: React.FC<TestimoniSectionProps> = ({
  testimonials,
  onAddTestimonial,
  onRequestQuote,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection,
}) => {
  const [selectedSector, setSelectedSector] = useState<string>('Semua');
  const [formState, setFormState] = useState({
    personName: '',
    roleOrDepartment: '',
    clientName: '',
    sector: 'BUMN & Korporasi',
    serviceUsed: 'Data Networking',
    rating: 5,
    quote: '',
  });
  const [submittedSuccess, setSubmittedSuccess] = useState(false);

  const activeTestimonials = testimonials.filter(t => t.isActive !== false);
  const sectors = [
    'Semua',
    'BUMN & Korporasi',
    'Pemerintahan',
    'Perbankan & Medis',
    'Pendidikan & Retail',
  ];

  const filteredTestimonials =
    selectedSector === 'Semua'
      ? activeTestimonials
      : activeTestimonials.filter(t => t.sector === selectedSector);

  const handleTestimonialSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formState.personName.trim() || !formState.clientName.trim() || !formState.quote.trim()) {
      return;
    }

    if (onAddTestimonial) {
      onAddTestimonial({
        personName: formState.personName.trim(),
        roleOrDepartment: formState.roleOrDepartment.trim() || 'Pelanggan / Perwakilan Instansi',
        clientName: formState.clientName.trim(),
        sector: formState.sector,
        serviceUsed: formState.serviceUsed,
        rating: formState.rating,
        quote: formState.quote.trim(),
        isActive: true,
      });
    }

    setSubmittedSuccess(true);
    setFormState({
      personName: '',
      roleOrDepartment: '',
      clientName: '',
      sector: 'BUMN & Korporasi',
      serviceUsed: 'Data Networking',
      rating: 5,
      quote: '',
    });
  };

  return (
    <section
      id="testimoni"
      className="py-20 bg-slate-50 dark:bg-slate-950 border-t border-slate-200/80 dark:border-slate-800/80 scroll-mt-24"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {isLiveEditMode && onOpenAdminSection && (
          <div
            className="mb-8 flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800"
            data-live-editor-ui="true"
          >
            <div className="text-xs font-semibold text-emerald-900 dark:text-emerald-200">
              💡 <strong>Mode Edit Langsung Testimoni:</strong> Tambah atau perbarui ulasan Testimoni Pelanggan & Instansi melalui Admin CMS.
            </div>
            <button
              type="button"
              onClick={() => onOpenAdminSection('testimonials')}
              className="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Kelola Testimoni di Admin CMS</span>
            </button>
          </div>
        )}

        <div className="text-center max-w-3xl mx-auto mb-12">
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-4 border border-blue-200 dark:border-blue-800">
            <Quote className="w-3.5 h-3.5" />
            <span>Suara & Kepuasan Pelanggan PT. KGS</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            {pageContent?.title || 'Formulir & Ulasan Testimoni Pelanggan'}
          </h2>
          <p className="mt-3 text-base text-slate-600 dark:text-slate-300 leading-relaxed">
            {pageContent?.summary ||
              'Berikan penilaian dan testimoni Anda atas layanan maupun pengerjaan proyek PT. Kreatif Global Solusindo melalui formulir di bawah ini.'}
          </p>
        </div>

        {/* FORM UNTUK PELANGGAN MEMBERIKAN TESTIMONI */}
        <div className="mb-14 rounded-3xl bg-white dark:bg-slate-900 border-2 border-blue-500/70 dark:border-blue-500/50 shadow-xl p-6 sm:p-10">
          <div className="max-w-3xl mx-auto">
            <div className="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200 dark:border-slate-800">
              <div className="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                <MessageSquarePlus className="w-6 h-6" />
              </div>
              <div>
                <h3 className="text-xl font-extrabold text-slate-900 dark:text-white">
                  Form Testimoni Pelanggan
                </h3>
                <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                  Bagikan pengalaman kerjasama Anda bersama tim teknis PT. Kreatif Global Solusindo (KGSNet).
                </p>
              </div>
            </div>

            {submittedSuccess && (
              <div className="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 flex items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                  <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                  <p className="text-xs sm:text-sm font-bold text-emerald-900 dark:text-emerald-200">
                    Terima kasih! Testimoni Anda berhasil dikirim dan telah ditampilkan pada daftar testimoni pelanggan di bawah.
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => setSubmittedSuccess(false)}
                  className="text-xs font-extrabold text-emerald-700 dark:text-emerald-300 underline shrink-0 cursor-pointer"
                >
                  Tutup
                </button>
              </div>
            )}

            <form onSubmit={handleTestimonialSubmit} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama Lengkap Pelanggan / pemberi Testimoni *
                  </label>
                  <input
                    type="text"
                    required
                    value={formState.personName}
                    onChange={e => setFormState({ ...formState, personName: e.target.value })}
                    placeholder="Contoh: Ir. Hendra Wijaya"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Jabatan / Divisi
                  </label>
                  <input
                    type="text"
                    value={formState.roleOrDepartment}
                    onChange={e =>
                      setFormState({ ...formState, roleOrDepartment: e.target.value })
                    }
                    placeholder="Contoh: IT Manager / Kepala Bagian Umum"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama Perusahaan / Instansi / Pelanggan *
                  </label>
                  <input
                    type="text"
                    required
                    value={formState.clientName}
                    onChange={e => setFormState({ ...formState, clientName: e.target.value })}
                    placeholder="Contoh: RSUD / PT. ... / Cafe ..."
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Kategori Sektor Pelanggan
                  </label>
                  <select
                    value={formState.sector}
                    onChange={e => setFormState({ ...formState, sector: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                  >
                    {sectors
                      .filter(s => s !== 'Semua')
                      .map(sec => (
                        <option key={sec} value={sec}>
                          {sec}
                        </option>
                      ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Bidang Layanan yang Digunakan *
                  </label>
                  <select
                    value={formState.serviceUsed}
                    onChange={e => setFormState({ ...formState, serviceUsed: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                  >
                    {SERVICE_CATEGORIES.map(c => (
                      <option key={c.id} value={c.name}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                  Rating Kepuasan Layanan
                </label>
                <div className="flex items-center gap-2">
                  {[1, 2, 3, 4, 5].map(star => (
                    <button
                      key={star}
                      type="button"
                      onClick={() => setFormState({ ...formState, rating: star })}
                      className={`p-2 rounded-xl border transition cursor-pointer flex items-center gap-1 ${
                        formState.rating >= star
                          ? 'bg-amber-50 border-amber-300 text-amber-500'
                          : 'bg-slate-100 border-slate-200 text-slate-400'
                      }`}
                    >
                      <Star
                        className={`w-5 h-5 ${
                          formState.rating >= star ? 'fill-amber-400 text-amber-400' : ''
                        }`}
                      />
                    </button>
                  ))}
                  <span className="text-xs font-bold text-slate-600 dark:text-slate-400 ml-2">
                    ({formState.rating} / 5 Bintang)
                  </span>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                  Isi Testimoni / Kesan & Pesan Pelanggan *
                </label>
                <textarea
                  rows={3}
                  required
                  value={formState.quote}
                  onChange={e => setFormState({ ...formState, quote: e.target.value })}
                  placeholder="Tuliskan pengalaman Anda menggunakan layanan atau proyek dari PT. Kreatif Global Solusindo..."
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-600"
                />
              </div>

              <div className="pt-2">
                <button
                  type="submit"
                  className="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-extrabold inline-flex items-center gap-2 transition cursor-pointer shadow-md"
                >
                  <Send className="w-4 h-4" />
                  <span>Kirim Testimoni Sekarang</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Sector Filter */}
        <div className="flex flex-wrap items-center justify-center gap-2 mb-10">
          {sectors.map(sec => (
            <button
              key={sec}
              type="button"
              onClick={() => setSelectedSector(sec)}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer border ${
                selectedSector === sec
                  ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                  : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-blue-400'
              }`}
            >
              {sec}
            </button>
          ))}
        </div>

        {/* Testimonial Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredTestimonials.map(item => (
            <div
              key={item.id}
              className="rounded-2xl p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-blue-400 transition flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between gap-2 mb-4">
                  <div className="flex items-center gap-1 text-amber-400">
                    {Array.from({ length: Math.max(1, Math.min(5, item.rating || 5)) }).map(
                      (_, idx) => (
                        <Star key={idx} className="w-4 h-4 fill-amber-400 text-amber-400" />
                      )
                    )}
                  </div>
                  <span className="px-2.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-[11px] font-bold border border-blue-200/60 dark:border-blue-800/60">
                    {item.sector}
                  </span>
                </div>

                <p className="text-sm text-slate-700 dark:text-slate-200 leading-relaxed italic mb-5">
                  &ldquo;{item.quote}&rdquo;
                </p>
              </div>

              <div className="pt-4 border-t border-slate-100 dark:border-slate-800">
                <div className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-xl bg-blue-600/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                    <Building2 className="w-4 h-4" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <h4 className="font-extrabold text-sm text-slate-900 dark:text-white leading-snug">
                      {item.clientName}
                    </h4>
                    <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                      {item.personName} • {item.roleOrDepartment}
                    </p>
                    <div className="mt-2 inline-block px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-blue-600 dark:text-blue-400">
                      Layanan: {item.serviceUsed}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
