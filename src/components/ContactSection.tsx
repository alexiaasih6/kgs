import React, { useState } from 'react';
import {
  COMPANY_INFO,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  ServiceItem,
  InquiryMessage,
  StaticPageItem,
  formatWhatsAppNumber,
  buildGoogleMapEmbedUrl,
  buildGoogleMapDirectLink
} from '../data/companyData';
import {
  Phone,
  Mail,
  MapPin,
  MessageSquare,
  Send,
  CheckCircle2,
  ExternalLink,
  Navigation,
  Edit3
} from 'lucide-react';

interface ContactSectionProps {
  services: ServiceItem[];
  prefilledService?: string;
  onAddInquiry: (inquiry: Omit<InquiryMessage, 'id' | 'createdAt' | 'status'>) => void;
  companySettings?: CompanySettings;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const ContactSection: React.FC<ContactSectionProps> = ({
  services,
  prefilledService = '',
  onAddInquiry,
  companySettings,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const settings: CompanySettings = companySettings || (() => {
    const saved = localStorage.getItem('kgs_company_settings');
    return saved ? { ...DEFAULT_COMPANY_SETTINGS, ...JSON.parse(saved) } : DEFAULT_COMPANY_SETTINGS;
  })();

  const [formData, setFormData] = useState({
    name: '',
    company: '',
    email: '',
    phone: '',
    serviceId: prefilledService || (services[0]?.id || ''),
    location: '',
    message: ''
  });

  const [submitted, setSubmitted] = useState(false);
  const [lastWhatsappUrl, setLastWhatsappUrl] = useState('');

  React.useEffect(() => {
    if (prefilledService) {
      const matched = services.find(
        s => s.id === prefilledService || s.name.toLowerCase() === prefilledService.toLowerCase()
      );
      setFormData(prev => ({
        ...prev,
        serviceId: matched ? matched.id : prev.serviceId,
        message: prev.message
          ? `${prev.message}\n[Permintaan Paket/Layanan: ${prefilledService}]`
          : `Tertarik dengan layanan/paket: ${prefilledService}`
      }));
    }
  }, [prefilledService, services]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.company || !formData.email || !formData.phone) {
      return;
    }

    const selectedServiceObj = services.find(s => s.id === formData.serviceId);
    const serviceLabel = selectedServiceObj
      ? `${selectedServiceObj.categoryName} - ${selectedServiceObj.name}`
      : formData.serviceId || 'Konsultasi Umum';
    const projectLocation = formData.location || 'Palembang / Sumatera Selatan';

    // 1. Save inquiry to Admin CMS backend
    onAddInquiry({
      name: formData.name,
      company: formData.company,
      email: formData.email,
      phone: formData.phone,
      serviceId: formData.serviceId,
      location: projectLocation,
      message: formData.message
    });

    // 2. Construct direct WhatsApp message to CS number from Company Identity Settings
    const csWaNumber = formatWhatsAppNumber(settings.whatsapp);
    const waText = [
      `Halo CS *${settings.legalName || COMPANY_INFO.legalName}*,`,
      `Saya mengajukan *Permintaan Penawaran & Konsultasi* melalui website resmi:`,
      ``,
      `*Nama Lengkap:* ${formData.name}`,
      `*Perusahaan / Instansi:* ${formData.company}`,
      `*Email Resmi:* ${formData.email}`,
      `*No. Telepon / WA:* ${formData.phone}`,
      `*Kategori Layanan:* ${serviceLabel}`,
      `*Lokasi Proyek / Site:* ${projectLocation}`,
      `*Catatan Kebutuhan Teknis:* ${formData.message || '-'}`,
      ``,
      `Mohon informasi dan tindak lanjut penawarannya. Terima kasih.`
    ].join('\n');

    const waUrl = `https://wa.me/${csWaNumber}?text=${encodeURIComponent(waText)}`;
    setLastWhatsappUrl(waUrl);
    setSubmitted(true);

    // 3. Immediately open WhatsApp CS in a new tab using an anchor click
    const link = document.createElement('a');
    link.href = waUrl;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const mapEmbedUrl = buildGoogleMapEmbedUrl(settings.googleMapInput);
  const mapDirectUrl = buildGoogleMapDirectLink(settings.googleMapInput);

  return (
    <section id="kontak" className="py-20 bg-slate-50 border-b border-slate-200 relative">
      <div id="konsultasi" className="absolute -top-24 left-0"></div>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-12">
          <div className="max-w-3xl">
            <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">
              Kontak & Kantor Operasional Resmi
            </div>
            <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || `Hubungi Tim ${settings.legalName}`}
            </h2>
            <p className="text-sm text-slate-600 mt-2 leading-relaxed">
              {pageContent?.summary || 'Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.'}
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
              onClick={() => onOpenAdminSection('settings')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Edit Alamat Kantor, WA & Maps di Admin CMS</span>
            </button>
          )}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* Left Column: Official Office Addresses, Channels & Google Maps (5 cols) */}
          <div className="lg:col-span-5 space-y-5">
            {/* Alamat Kantor */}
            <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
              <div className="flex items-center justify-between mb-4">
                <span className="text-xs font-bold text-blue-700 uppercase tracking-wide">
                  Alamat Kantor
                </span>
                <span className="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-semibold">
                  Kantor Operasional Resmi
                </span>
              </div>
              <div className="space-y-3 text-xs text-slate-700">
                <div className="flex items-start gap-3">
                  <MapPin className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                  <span>{settings.addressPalembang}</span>
                </div>
                <div className="flex items-center gap-3">
                  <Phone className="w-4 h-4 text-slate-400 shrink-0" />
                  <span>Telp: {settings.phonePalembang}</span>
                </div>
                <div className="flex items-center gap-3">
                  <MessageSquare className="w-4 h-4 text-emerald-500 shrink-0" />
                  <span>
                    WhatsApp CS Resmi: <strong className="text-emerald-700">{settings.whatsapp}</strong>
                  </span>
                </div>
                <div className="flex items-center gap-3">
                  <Mail className="w-4 h-4 text-slate-400 shrink-0" />
                  <span>Email: {settings.email}</span>
                </div>
              </div>
            </div>

            {/* Interactive Google Maps Embed Card */}
            <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
              <div className="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/70">
                <div className="flex items-start gap-2.5 min-w-0">
                  <Navigation className="w-4 h-4 text-blue-600 shrink-0 mt-1" />
                  <div className="min-w-0">
                    <h4 className="text-sm font-bold text-slate-900">Peta Lokasi Google Maps</h4>
                    <p className="text-xs text-slate-600 break-words">
                      {settings.googleMapInput}
                    </p>
                  </div>
                </div>
                <a
                  href={mapDirectUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition shrink-0"
                >
                  <span>Buka Maps</span>
                  <ExternalLink className="w-3.5 h-3.5" />
                </a>
              </div>
              <div className="w-full h-64 bg-slate-100 relative">
                <iframe
                  title="Lokasi Kantor PT. Kreatif Global Solusindo"
                  src={mapEmbedUrl}
                  className="w-full h-full border-0"
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  allowFullScreen
                />
              </div>
            </div>

            {/* Online Links */}
            <div className="bg-slate-900 text-white rounded-2xl p-6 shadow-xs space-y-3 text-xs">
              <span className="text-xs font-bold text-blue-400 uppercase tracking-wider block mb-1">
                Saluran Digital & Layanan Pelanggan
              </span>
              <div className="flex justify-between items-center py-1 border-b border-slate-800">
                <span className="text-slate-400">Portal Korporat:</span>
                <span className="font-mono text-slate-200">
                  {settings.websiteCorporate || 'www.creative.co.id'}
                </span>
              </div>
              <div className="flex justify-between items-center py-1 border-b border-slate-800">
                <span className="text-slate-400">Portal ISP KGSNet:</span>
                <span className="font-mono text-slate-200">
                  {settings.websiteIsp || 'www.kgs.net.id'}
                </span>
              </div>
              <div className="flex justify-between items-center py-1">
                <span className="text-slate-400">WhatsApp CS Penawaran:</span>
                <a
                  href={`https://wa.me/${formatWhatsAppNumber(settings.whatsapp)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="font-mono text-emerald-400 hover:text-emerald-300 font-bold underline"
                >
                  {settings.whatsapp}
                </a>
              </div>
            </div>
          </div>

          {/* Right Column: Interactive Consultation & Direct WhatsApp Quote Form (7 cols) */}
          <div className="lg:col-span-7">
            <div className="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
              <div className="flex flex-wrap items-center justify-between gap-2 mb-1">
                <h3 className="text-lg font-bold text-slate-900">
                  Formulir Permintaan Penawaran & Konsultasi
                </h3>
                <span className="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold inline-flex items-center gap-1">
                  <MessageSquare className="w-3.5 h-3.5 text-emerald-600" />
                  <span>Direct WhatsApp CS ({settings.whatsapp})</span>
                </span>
              </div>
              <p className="text-xs text-slate-500 mb-6">
                Silakan lengkapi kebutuhan proyek Anda. Saat dikirim, permintaan otomatis tercatat di sistem dan langsung mengarahkan Anda ke WhatsApp Customer Service resmi kami ({settings.whatsapp}).
              </p>

              {submitted ? (
                <div className="p-6 bg-emerald-50 border border-emerald-200 rounded-2xl text-center space-y-4">
                  <div className="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto">
                    <CheckCircle2 className="w-6 h-6" />
                  </div>
                  <div className="space-y-1">
                    <h4 className="text-base font-bold text-emerald-950">
                      Permintaan Penawaran Diteruskan ke WhatsApp CS!
                    </h4>
                    <p className="text-xs text-emerald-800 max-w-md mx-auto leading-relaxed">
                      Terima kasih Bapak/Ibu <strong>{formData.name}</strong> dari{' '}
                      <strong>{formData.company}</strong>. Data penawaran Anda telah tersimpan dan diarahkan langsung ke WhatsApp CS resmi kami di nomor{' '}
                      <strong>{settings.whatsapp}</strong>.
                    </p>
                  </div>

                  <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
                    {lastWhatsappUrl && (
                      <a
                        href={lastWhatsappUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2 cursor-pointer"
                      >
                        <MessageSquare className="w-4 h-4" />
                        <span>Buka Chat WhatsApp CS ({settings.whatsapp})</span>
                      </a>
                    )}
                    <button
                      type="button"
                      onClick={() => {
                        setSubmitted(false);
                        setFormData({
                          name: '',
                          company: '',
                          email: '',
                          phone: '',
                          serviceId: services[0]?.id || '',
                          location: '',
                          message: ''
                        });
                      }}
                      className="px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold rounded-xl transition cursor-pointer"
                    >
                      Kirim Permintaan Lain
                    </button>
                  </div>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Lengkap *
                      </label>
                      <input
                        type="text"
                        required
                        value={formData.name}
                        onChange={e => setFormData({ ...formData, name: e.target.value })}
                        placeholder="Contoh: Ir. Budi Santoso"
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Perusahaan / Instansi *
                      </label>
                      <input
                        type="text"
                        required
                        value={formData.company}
                        onChange={e => setFormData({ ...formData, company: e.target.value })}
                        placeholder="Contoh: PT. Sawit Makmur Jaya"
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Alamat Email Perusahaan *
                      </label>
                      <input
                        type="email"
                        required
                        value={formData.email}
                        onChange={e => setFormData({ ...formData, email: e.target.value })}
                        placeholder="nama@perusahaan.co.id"
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor Telepon / WhatsApp *
                      </label>
                      <input
                        type="tel"
                        required
                        value={formData.phone}
                        onChange={e => setFormData({ ...formData, phone: e.target.value })}
                        placeholder="0812-xxxx-xxxx"
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Kategori Layanan Yang Dibutuhkan *
                      </label>
                      <select
                        value={formData.serviceId}
                        onChange={e => setFormData({ ...formData, serviceId: e.target.value })}
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      >
                        {services.map(s => (
                          <option key={s.id} value={s.id}>
                            {s.categoryName} - {s.name}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-700 mb-1">
                        Lokasi Proyek / Site
                      </label>
                      <input
                        type="text"
                        value={formData.location}
                        onChange={e => setFormData({ ...formData, location: e.target.value })}
                        placeholder="Contoh: Kab. Musi Banyuasin, Sumsel"
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold text-slate-700 mb-1">
                      Catatan Kebutuhan Teknis / Volume Pekerjaan
                    </label>
                    <textarea
                      rows={4}
                      value={formData.message}
                      onChange={e => setFormData({ ...formData, message: e.target.value })}
                      placeholder="Jelaskan kebutuhan spesifik Anda (misal: panjang penarikan fiber optik, kapasitas PABX, ketinggian guywire tower, atau bandwidth dedicated)..."
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition"
                    ></textarea>
                  </div>

                  <button
                    type="submit"
                    className="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <Send className="w-4 h-4" />
                    <span>Kirim Permintaan Penawaran (Direct WhatsApp CS)</span>
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
