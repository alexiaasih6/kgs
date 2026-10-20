import React, { useState, useEffect } from 'react';
import {
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  TimelineMilestoneItem,
  CertificationItem,
  PartnerItem,
  PricingPlanItem,
  FaqItem,
  StaticPageItem
} from '../../data/companyData';
import {
  Compass,
  History,
  Award,
  Handshake,
  DollarSign,
  HelpCircle,
  Server,
  Plus,
  Edit3,
  Trash2,
  Check,
  X,
  ArrowUpRight,
  Layers,
  LayoutGrid,
  FileText,
  Camera,
  BookOpen,
  Phone,
  Palette
} from 'lucide-react';

export type WebsiteSectionSubTab =
  | 'guide'
  | 'hero_noc'
  | 'about_details'
  | 'pricing_plans'
  | 'faq_items'
  | 'header_footer_text';

interface AdminWebsiteSectionsProps {
  initialSubTab?: WebsiteSectionSubTab;
  companySettings: CompanySettings;
  onUpdateCompanySettings: (settings: CompanySettings) => void;
  timeline: TimelineMilestoneItem[];
  onUpdateTimeline: (items: TimelineMilestoneItem[]) => void;
  certifications: CertificationItem[];
  onUpdateCertifications: (items: CertificationItem[]) => void;
  partners: PartnerItem[];
  onUpdatePartners: (items: PartnerItem[]) => void;
  pricingPlans: PricingPlanItem[];
  onUpdatePricingPlans: (items: PricingPlanItem[]) => void;
  faqs: FaqItem[];
  onUpdateFaqs: (items: FaqItem[]) => void;
  pages: StaticPageItem[];
  onUpdatePages: (items: StaticPageItem[]) => void;
  onNavigateAdminTab: (tab: string) => void;
}

export const AdminWebsiteSections: React.FC<AdminWebsiteSectionsProps> = ({
  initialSubTab = 'guide',
  companySettings,
  onUpdateCompanySettings,
  timeline,
  onUpdateTimeline,
  certifications,
  onUpdateCertifications,
  partners,
  onUpdatePartners,
  pricingPlans,
  onUpdatePricingPlans,
  faqs,
  onUpdateFaqs,
  onNavigateAdminTab
}) => {
  const [subTab, setSubTab] = useState<WebsiteSectionSubTab>(initialSubTab);
  const [toast, setToast] = useState('');

  useEffect(() => {
    setSubTab(initialSubTab);
  }, [initialSubTab]);

  const showToast = (msg: string) => {
    setToast(msg);
    setTimeout(() => setToast(''), 4000);
  };

  // Local form copy of CompanySettings for Hero/NOC/Culture/Banners/Footer
  const [settingsForm, setSettingsForm] = useState<CompanySettings>({
    ...DEFAULT_COMPANY_SETTINGS,
    ...companySettings
  });

  useEffect(() => {
    setSettingsForm({
      ...DEFAULT_COMPANY_SETTINGS,
      ...companySettings
    });
  }, [companySettings]);

  const handleSaveSettingsForm = (e: React.FormEvent, label: string) => {
    e.preventDefault();
    onUpdateCompanySettings(settingsForm);
    showToast(`${label} berhasil disimpan dan langsung tampil di website!`);
  };

  // ============================================================================
  // 1. TIMELINE SEJARAH CRUD
  // ============================================================================
  const [editingTimeline, setEditingTimeline] = useState<TimelineMilestoneItem | null>(null);
  const [isAddingTimeline, setIsAddingTimeline] = useState(false);
  const [tlForm, setTlForm] = useState<{
    year: string;
    title: string;
    description: string;
    isHighlighted: boolean;
  }>({ year: '', title: '', description: '', isHighlighted: false });

  const handleSaveTimeline = (e: React.FormEvent) => {
    e.preventDefault();
    if (!tlForm.year.trim() || !tlForm.title.trim()) return;
    if (editingTimeline) {
      onUpdateTimeline(
        timeline.map(t =>
          t.id === editingTimeline.id
            ? {
                ...t,
                year: tlForm.year.trim(),
                title: tlForm.title.trim(),
                description: tlForm.description.trim(),
                isHighlighted: tlForm.isHighlighted
              }
            : t
        )
      );
      setEditingTimeline(null);
      showToast(`Sejarah tahun ${tlForm.year} berhasil diperbarui!`);
    } else {
      const newItem: TimelineMilestoneItem = {
        id: `tl-${Date.now()}`,
        year: tlForm.year.trim(),
        title: tlForm.title.trim(),
        description: tlForm.description.trim(),
        isHighlighted: tlForm.isHighlighted
      };
      onUpdateTimeline([...timeline, newItem]);
      setIsAddingTimeline(false);
      showToast(`Milestone tahun ${tlForm.year} berhasil ditambahkan!`);
    }
  };

  const handleDeleteTimeline = (id: string) => {
    onUpdateTimeline(timeline.filter(t => t.id !== id));
    showToast('Item sejarah berhasil dihapus.');
  };

  // ============================================================================
  // 2. SERTIFIKASI RESMI CRUD
  // ============================================================================
  const [editingCert, setEditingCert] = useState<CertificationItem | null>(null);
  const [isAddingCert, setIsAddingCert] = useState(false);
  const [certForm, setCertForm] = useState({
    title: '',
    issuerOrBrand: '',
    holder: 'PT. KREATIF GLOBAL SOLUSINDO',
    yearOrValidity: ''
  });

  const handleSaveCert = (e: React.FormEvent) => {
    e.preventDefault();
    if (!certForm.title.trim() || !certForm.issuerOrBrand.trim()) return;
    if (editingCert) {
      onUpdateCertifications(
        certifications.map(c =>
          c.id === editingCert.id
            ? {
                ...c,
                title: certForm.title.trim(),
                issuerOrBrand: certForm.issuerOrBrand.trim(),
                holder: certForm.holder.trim(),
                yearOrValidity: certForm.yearOrValidity.trim()
              }
            : c
        )
      );
      setEditingCert(null);
      showToast(`Sertifikasi "${certForm.title}" berhasil diperbarui!`);
    } else {
      const newItem: CertificationItem = {
        id: `cert-${Date.now()}`,
        title: certForm.title.trim(),
        issuerOrBrand: certForm.issuerOrBrand.trim(),
        holder: certForm.holder.trim() || 'PT. KREATIF GLOBAL SOLUSINDO',
        yearOrValidity: certForm.yearOrValidity.trim() || 'Terdaftar Resmi',
        pdfPage: 21
      };
      onUpdateCertifications([...certifications, newItem]);
      setIsAddingCert(false);
      showToast(`Sertifikasi "${certForm.title}" berhasil ditambahkan!`);
    }
  };

  const handleDeleteCert = (id: string) => {
    onUpdateCertifications(certifications.filter(c => c.id !== id));
    showToast('Sertifikasi berhasil dihapus.');
  };

  // ============================================================================
  // 3. MITRA KERJA CRUD
  // ============================================================================
  const [editingPartner, setEditingPartner] = useState<PartnerItem | null>(null);
  const [isAddingPartner, setIsAddingPartner] = useState(false);
  const [partnerForm, setPartnerForm] = useState({
    name: '',
    role: 'Guywire Tower Support for ISP',
    serviceType: 'Tower & Backbone'
  });

  const handleSavePartner = (e: React.FormEvent) => {
    e.preventDefault();
    if (!partnerForm.name.trim() || !partnerForm.role.trim()) return;
    if (editingPartner) {
      onUpdatePartners(
        partners.map(p =>
          p.id === editingPartner.id
            ? {
                ...p,
                name: partnerForm.name.trim(),
                role: partnerForm.role.trim(),
                serviceType: partnerForm.serviceType.trim()
              }
            : p
        )
      );
      setEditingPartner(null);
      showToast(`Mitra kerja "${partnerForm.name}" berhasil diperbarui!`);
    } else {
      const newItem: PartnerItem = {
        id: `p-${Date.now()}`,
        name: partnerForm.name.trim(),
        role: partnerForm.role.trim(),
        serviceType: partnerForm.serviceType.trim() || 'Tower & Backbone',
        pdfPage: 16
      };
      onUpdatePartners([...partners, newItem]);
      setIsAddingPartner(false);
      showToast(`Mitra kerja "${partnerForm.name}" berhasil ditambahkan!`);
    }
  };

  const handleDeletePartner = (id: string) => {
    onUpdatePartners(partners.filter(p => p.id !== id));
    showToast('Mitra kerja berhasil dihapus.');
  };

  // ============================================================================
  // 4. PAKET HARGA B2B CRUD
  // ============================================================================
  const [editingPlan, setEditingPlan] = useState<PricingPlanItem | null>(null);
  const [isAddingPlan, setIsAddingPlan] = useState(false);
  const [planForm, setPlanForm] = useState({
    name: '',
    subtitle: '',
    targetAudience: '',
    badge: '',
    isPopular: false,
    sla: '',
    featuresText: '',
    recommendedFor: ''
  });

  const openEditPlan = (plan: PricingPlanItem) => {
    setEditingPlan(plan);
    setPlanForm({
      name: plan.name,
      subtitle: plan.subtitle,
      targetAudience: plan.targetAudience,
      badge: plan.badge || '',
      isPopular: Boolean(plan.isPopular),
      sla: plan.sla,
      featuresText: plan.features.join('\n'),
      recommendedFor: plan.recommendedFor
    });
  };

  const openAddPlan = () => {
    setEditingPlan(null);
    setPlanForm({
      name: '',
      subtitle: '',
      targetAudience: 'Perkantoran, Pabrik & Instansi',
      badge: '',
      isPopular: false,
      sla: 'SLA 99.5% & Garansi Resmi',
      featuresText:
        'Survei Lokasi & Konsultasi Teknis Gratis\nInstalasi Standar Industri Bersertifikasi\nDukungan NOC & Teknisi Siaga 24/7',
      recommendedFor: 'Perusahaan dan instansi yang membutuhkan keandalan infrastruktur jangka panjang'
    });
    setIsAddingPlan(true);
  };

  const handleSavePlan = (e: React.FormEvent) => {
    e.preventDefault();
    if (!planForm.name.trim()) return;
    const features = planForm.featuresText
      .split('\n')
      .map(s => s.trim())
      .filter(Boolean);

    if (editingPlan) {
      onUpdatePricingPlans(
        pricingPlans.map(p =>
          p.id === editingPlan.id
            ? {
                ...p,
                name: planForm.name.trim(),
                subtitle: planForm.subtitle.trim(),
                targetAudience: planForm.targetAudience.trim(),
                badge: planForm.badge.trim() || undefined,
                isPopular: planForm.isPopular,
                sla: planForm.sla.trim(),
                features,
                recommendedFor: planForm.recommendedFor.trim()
              }
            : p
        )
      );
      setEditingPlan(null);
      showToast(`Paket "${planForm.name}" berhasil diperbarui!`);
    } else {
      const newPlan: PricingPlanItem = {
        id: `plan-${Date.now()}`,
        name: planForm.name.trim(),
        subtitle: planForm.subtitle.trim(),
        targetAudience: planForm.targetAudience.trim(),
        badge: planForm.badge.trim() || undefined,
        isPopular: planForm.isPopular,
        sla: planForm.sla.trim(),
        features,
        recommendedFor: planForm.recommendedFor.trim()
      };
      onUpdatePricingPlans([...pricingPlans, newPlan]);
      setIsAddingPlan(false);
      showToast(`Paket baru "${planForm.name}" berhasil ditambahkan ke halaman Harga!`);
    }
  };

  const handleDeletePlan = (id: string) => {
    onUpdatePricingPlans(pricingPlans.filter(p => p.id !== id));
    showToast('Paket harga berhasil dihapus.');
  };

  // ============================================================================
  // 5. FAQ (TANYA JAWAB) CRUD
  // ============================================================================
  const [editingFaq, setEditingFaq] = useState<FaqItem | null>(null);
  const [isAddingFaq, setIsAddingFaq] = useState(false);
  const [faqForm, setFaqForm] = useState<{
    category: FaqItem['category'];
    question: string;
    answer: string;
  }>({
    category: 'Umum',
    question: '',
    answer: ''
  });

  const handleSaveFaq = (e: React.FormEvent) => {
    e.preventDefault();
    if (!faqForm.question.trim() || !faqForm.answer.trim()) return;
    if (editingFaq) {
      onUpdateFaqs(
        faqs.map(f =>
          f.id === editingFaq.id
            ? {
                ...f,
                category: faqForm.category,
                question: faqForm.question.trim(),
                answer: faqForm.answer.trim()
              }
            : f
        )
      );
      setEditingFaq(null);
      showToast('Pertanyaan FAQ berhasil diperbarui!');
    } else {
      const newFaq: FaqItem = {
        id: `faq-${Date.now()}`,
        category: faqForm.category,
        question: faqForm.question.trim(),
        answer: faqForm.answer.trim()
      };
      onUpdateFaqs([...faqs, newFaq]);
      setIsAddingFaq(false);
      showToast('Pertanyaan FAQ baru berhasil ditambahkan!');
    }
  };

  const handleDeleteFaq = (id: string) => {
    onUpdateFaqs(faqs.filter(f => f.id !== id));
    showToast('Item FAQ berhasil dihapus.');
  };

  const heroHighlights = settingsForm.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
  const heroStats = settingsForm.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];
  const nocNodes = settingsForm.nocNodes || DEFAULT_COMPANY_SETTINGS.nocNodes || [];

  return (
    <div className="space-y-6">
      {/* Top Banner */}
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-blue-600 mb-1">
            <LayoutGrid className="w-3.5 h-3.5" />
            <span>Master Section & Component Manager</span>
          </div>
          <h2 className="text-xl font-extrabold text-slate-900">
            Kelola Seluruh Bagian & Komponen di Setiap Halaman Website
          </h2>
          <p className="text-xs text-slate-500 mt-1 max-w-3xl">
            Pusat pengaturan lengkap untuk mengedit seluruh bagian halaman website yang sebelumnya belum terjangkau: Statistik & Panel NOC Beranda, Sejarah Perjalanan (1997–Sekarang), Visi/Misi/Budaya/Komitmen, Sertifikasi Resmi, Mitra Kerja, Paket Harga B2B, hingga Daftar Tanya Jawab (FAQ).
          </p>
        </div>

        {/* Sub-navigation Tabs */}
        <div className="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1.5 rounded-xl text-xs">
          <button
            type="button"
            onClick={() => setSubTab('guide')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'guide'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            🗺️ Peta Lokasi Edit
          </button>
          <button
            type="button"
            onClick={() => setSubTab('hero_noc')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'hero_noc'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            🏠 Beranda, NOC & Angka
          </button>
          <button
            type="button"
            onClick={() => setSubTab('about_details')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'about_details'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            🏢 Sejarah, Sertifikasi & Mitra
          </button>
          <button
            type="button"
            onClick={() => setSubTab('pricing_plans')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'pricing_plans'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            💼 Paket Harga B2B ({pricingPlans.length})
          </button>
          <button
            type="button"
            onClick={() => setSubTab('faq_items')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'faq_items'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            ❓ Daftar FAQ ({faqs.length})
          </button>
          <button
            type="button"
            onClick={() => setSubTab('header_footer_text')}
            className={`px-3 py-2 rounded-lg font-bold transition cursor-pointer ${
              subTab === 'header_footer_text'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            🌐 Header & Footer
          </button>
        </div>
      </div>

      {toast && (
        <div className="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center justify-between shadow-xs">
          <div className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-600" />
            <span>{toast}</span>
          </div>
          <button onClick={() => setToast('')} className="text-emerald-700 cursor-pointer">
            ✕
          </button>
        </div>
      )}

      {/* =====================================================================
          SUB-TAB 0: PETA LOKASI EDIT SELURUH BAGIAN WEBSITE
      ===================================================================== */}
      {subTab === 'guide' && (
        <div className="space-y-6">
          <div className="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-2xl p-6 shadow-md">
            <h3 className="text-base font-extrabold mb-1">
              Bingung Dimana Mengedit Suatu Bagian di Halaman Website?
            </h3>
            <p className="text-xs text-slate-300 leading-relaxed">
              Berikut adalah daftar lengkap seluruh bagian dari atas sampai bawah pada website PT. Kreatif Global Solusindo beserta tombol pintasan langsung untuk mengedit isinya:
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {/* 1. Header & Navigasi */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase">
                  1. Bagian Atas & Bawah
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Menu Navigasi Header & Footer
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tambah, ubah label, ubah urutan, atau nonaktifkan tautan menu di bagian atas (Header) dan bawah (Footer).
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('navigation')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <span>Buka Pengaturan Navigasi</span>
                  <ArrowUpRight className="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  onClick={() => setSubTab('header_footer_text')}
                  className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-semibold cursor-pointer"
                >
                  Edit Teks Jam & Hak Cipta
                </button>
              </div>
            </div>

            {/* 2. Beranda: Slider, Tema & Judul */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold uppercase">
                  2. Halaman Beranda (index.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Slider Foto Beranda, Tema Warna, Panel NOC & 4 Statistik
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Upload foto slider beranda (di sebelah/background), ganti tema warna website, edit judul beranda, kartu NOC Operations, & angka statistik (1997, 5 Segmen, 100+, &lt; 2 Ω).
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('theme_slider')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Palette className="w-3.5 h-3.5" />
                  <span>Tema & Foto Slider</span>
                </button>
                <button
                  type="button"
                  onClick={() => setSubTab('hero_noc')}
                  className="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold cursor-pointer"
                >
                  Edit NOC, CTA & Angka Statistik
                </button>
              </div>
            </div>

            {/* 3. Tentang Kami */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px] font-bold uppercase">
                  3. Halaman Tentang Kami (tentang.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Sejarah (1997–Kini), Visi Misi, Sertifikasi & Mitra Kerja
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Edit timeline perjalanan perusahaan sejak 1997, Visi, Misi, Budaya Kerja, Komitmen Pelanggan, daftar Sertifikasi Resmi, serta Mitra Kerja ISP.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => setSubTab('about_details')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <History className="w-3.5 h-3.5" />
                  <span>Edit Sejarah, Sertifikasi & Mitra</span>
                </button>
              </div>
            </div>

            {/* 4. Layanan */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-cyan-50 text-cyan-700 rounded text-[10px] font-bold uppercase">
                  4. Halaman Layanan (layanan.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  5 Segmen Kategori & 22+ Katalog Layanan Teknis
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tambah/edit kartu layanan, spesifikasi teknis, brand terkait, dan foto layanan maupun nama 5 segmen kategori.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('services')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Layers className="w-3.5 h-3.5" />
                  <span>Kelola Layanan ({ '22+' })</span>
                </button>
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('categories')}
                  className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-semibold cursor-pointer"
                >
                  Edit 5 Segmen Kategori
                </button>
              </div>
            </div>

            {/* 5. Paket Harga B2B */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold uppercase">
                  5. Halaman Harga (harga.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Paket Solusi B2B & Banner Tender BoQ / KAK
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tambah, edit, atau hapus kartu paket solusi B2B (Dedicated Internet, Security, Tower, Solar, Tender), daftar fitur paket, SLA, dan banner BoQ di bawahnya.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => setSubTab('pricing_plans')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <DollarSign className="w-3.5 h-3.5" />
                  <span>Kelola Paket Harga B2B ({pricingPlans.length})</span>
                </button>
              </div>
            </div>

            {/* 6. FAQ */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-purple-50 text-purple-700 rounded text-[10px] font-bold uppercase">
                  6. Halaman FAQ (faq.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Pertanyaan Umum & Jawaban Teknis (FAQ)
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tambah pertanyaan baru, edit jawaban teknis, atur kategori FAQ (Umum, Internet ISP, Keamanan & Tower, Kontrak), serta edit kotak bantuan bawah.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => setSubTab('faq_items')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <HelpCircle className="w-3.5 h-3.5" />
                  <span>Kelola Daftar FAQ ({faqs.length})</span>
                </button>
              </div>
            </div>

            {/* 7. Portofolio & Klien */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-rose-50 text-rose-700 rounded text-[10px] font-bold uppercase">
                  7. Halaman Portofolio (portofolio.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Dokumentasi Proyek Lapangan & Daftar Klien B2B
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Upload foto pengerjaan proyek di lapangan dan kelola daftar 100+ perusahaan klien BUMN & swasta.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('portfolio')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Camera className="w-3.5 h-3.5" />
                  <span>Dokumentasi Proyek</span>
                </button>
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('clients')}
                  className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-semibold cursor-pointer"
                >
                  Daftar Klien B2B
                </button>
              </div>
            </div>

            {/* 8. Artikel & Halaman Statis */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-teal-50 text-teal-700 rounded text-[10px] font-bold uppercase">
                  8. Blog & Halaman Statis (blog.php / page.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Artikel Edukasi & Judul/Ringkasan Semua Halaman
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tulis artikel edukasi baru, edit Kebijakan Privasi, atau tambah halaman baru ke Header/Footer.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('articles')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <BookOpen className="w-3.5 h-3.5" />
                  <span>Kelola Artikel Blog</span>
                </button>
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('pages')}
                  className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-semibold inline-flex items-center gap-1 cursor-pointer"
                >
                  <FileText className="w-3.5 h-3.5" />
                  <span>Judul & Isi Halaman</span>
                </button>
              </div>
            </div>

            {/* 9. Kontak, WhatsApp & Maps */}
            <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
              <div>
                <span className="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-bold uppercase">
                  9. Halaman Kontak (kontak.php)
                </span>
                <h4 className="text-sm font-extrabold text-slate-900 mt-2">
                  Alamat Kantor, Nomor WhatsApp CS, Logo & Google Maps
                </h4>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Ubah alamat kantor operasional, nomor telepon/WhatsApp CS tujuan form penawaran, logo perusahaan, dan titik Google Maps.
                </p>
              </div>
              <div className="pt-4 mt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => onNavigateAdminTab('settings')}
                  className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Phone className="w-3.5 h-3.5" />
                  <span>Edit Kontak, Logo & Maps</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* =====================================================================
          SUB-TAB 1: BERANDA, PANEL NOC & 4 ANGKA STATISTIK
      ===================================================================== */}
      {subTab === 'hero_noc' && (
        <form
          onSubmit={e => handleSaveSettingsForm(e, 'Pengaturan Komponen Beranda, NOC & Statistik')}
          className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6 text-xs"
        >
          <div className="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
              <h3 className="text-base font-extrabold text-slate-900">
                Edit Komponen Beranda: Badge, Tombol CTA, 4 Keunggulan, Panel NOC & 4 Angka Statistik
              </h3>
              <p className="text-slate-500 mt-0.5">
                Seluruh teks kecil, tombol, kartu status NOC, dan 4 angka statistik di bagian bawah Beranda dapat diubah di sini.
              </p>
            </div>
            <button
              type="submit"
              className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5"
            >
              <Check className="w-4 h-4" />
              <span>Simpan Perubahan Beranda</span>
            </button>
          </div>

          {/* Badge & CTA Buttons */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="md:col-span-2">
              <label className="block font-bold text-slate-700 mb-1">
                Label Badge Atas Beranda (Kicker)
              </label>
              <input
                type="text"
                value={settingsForm.heroBadgeText || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, heroBadgeText: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Teks Tombol Utama Beranda (CTA 1)
              </label>
              <input
                type="text"
                value={settingsForm.heroCtaPrimary || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, heroCtaPrimary: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Teks Tombol Kedua Beranda (CTA 2)
              </label>
              <input
                type="text"
                value={settingsForm.heroCtaSecondary || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, heroCtaSecondary: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>
          </div>

          {/* 4 Poin Keunggulan (Highlights) */}
          <div className="pt-4 border-t border-slate-200">
            <h4 className="font-extrabold text-slate-900 mb-3">
              4 Poin Keunggulan Beranda (Tampil di Bawah Tombol Beranda)
            </h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              {[0, 1, 2, 3].map(idx => (
                <div key={idx}>
                  <label className="block text-[11px] font-semibold text-slate-500 mb-1">
                    Poin Keunggulan #{idx + 1}
                  </label>
                  <input
                    type="text"
                    value={heroHighlights[idx] || ''}
                    onChange={e => {
                      const next = [...heroHighlights];
                      next[idx] = e.target.value;
                      setSettingsForm({ ...settingsForm, heroHighlights: next });
                    }}
                    className="w-full border border-slate-300 rounded-xl p-2.5 font-semibold"
                  />
                </div>
              ))}
            </div>
          </div>

          {/* 4 Angka Statistik Bawah Beranda */}
          <div className="pt-4 border-t border-slate-200">
            <h4 className="font-extrabold text-slate-900 mb-3">
              4 Angka Statistik di Bar Bawah Beranda (Contoh: 1997, 5 Segmen, 100+, &lt; 2 Ω)
            </h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {[0, 1, 2, 3].map(idx => {
                const stat = heroStats[idx] || { value: '', label: '' };
                return (
                  <div
                    key={idx}
                    className="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2"
                  >
                    <div className="text-[10px] font-bold uppercase text-blue-600">
                      Kotak Statistik #{idx + 1}
                    </div>
                    <div>
                      <label className="block text-[11px] text-slate-500 mb-0.5">
                        Angka / Nilai Utama
                      </label>
                      <input
                        type="text"
                        value={stat.value}
                        onChange={e => {
                          const next = [...heroStats];
                          next[idx] = { ...stat, value: e.target.value };
                          setSettingsForm({ ...settingsForm, heroStats: next });
                        }}
                        className="w-full bg-white border border-slate-300 rounded-lg p-2 font-extrabold text-slate-900"
                      />
                    </div>
                    <div>
                      <label className="block text-[11px] text-slate-500 mb-0.5">
                        Keterangan di Bawah Angka
                      </label>
                      <input
                        type="text"
                        value={stat.label}
                        onChange={e => {
                          const next = [...heroStats];
                          next[idx] = { ...stat, label: e.target.value };
                          setSettingsForm({ ...settingsForm, heroStats: next });
                        }}
                        className="w-full bg-white border border-slate-300 rounded-lg p-2"
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Panel KGS NOC Operations */}
          <div className="pt-4 border-t border-slate-200 space-y-4">
            <div className="flex items-center gap-2">
              <Server className="w-4 h-4 text-blue-600" />
              <h4 className="font-extrabold text-slate-900">
                Kartu Panel KGS NOC Operations (Tampil Jika Mode Kartu NOC Dipilih)
              </h4>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Label Status Hijau NOC
                </label>
                <input
                  type="text"
                  value={settingsForm.nocStatusLabel || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, nocStatusLabel: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Judul Kartu NOC
                </label>
                <input
                  type="text"
                  value={settingsForm.nocCardTitle || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, nocCardTitle: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Teks Jangkauan Operasional Bawah Kartu
                </label>
                <input
                  type="text"
                  value={settingsForm.nocCoverageText || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, nocCoverageText: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {[0, 1, 2].map(idx => {
                const node = nocNodes[idx] || { title: '', subtitle: '', statusBadge: 'Active' };
                return (
                  <div
                    key={idx}
                    className="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2"
                  >
                    <div className="text-[10px] font-bold uppercase text-blue-600">
                      Baris Infrastruktur NOC #{idx + 1}
                    </div>
                    <input
                      type="text"
                      placeholder="Judul Baris"
                      value={node.title}
                      onChange={e => {
                        const next = [...nocNodes];
                        next[idx] = { ...node, title: e.target.value };
                        setSettingsForm({ ...settingsForm, nocNodes: next });
                      }}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold"
                    />
                    <input
                      type="text"
                      placeholder="Subjudul"
                      value={node.subtitle}
                      onChange={e => {
                        const next = [...nocNodes];
                        next[idx] = { ...node, subtitle: e.target.value };
                        setSettingsForm({ ...settingsForm, nocNodes: next });
                      }}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                    <input
                      type="text"
                      placeholder="Badge Status (Active / Linked / Certified)"
                      value={node.statusBadge}
                      onChange={e => {
                        const next = [...nocNodes];
                        next[idx] = { ...node, statusBadge: e.target.value };
                        setSettingsForm({ ...settingsForm, nocNodes: next });
                      }}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-mono text-emerald-700"
                    />
                  </div>
                );
              })}
            </div>
          </div>

          <div className="flex justify-end pt-2">
            <button
              type="submit"
              className="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5"
            >
              <Check className="w-4 h-4" />
              <span>Simpan Perubahan Beranda</span>
            </button>
          </div>
        </form>
      )}

      {/* =====================================================================
          SUB-TAB 2: TENTANG KAMI (SEJARAH, VISI/MISI/BUDAYA, SERTIFIKASI, MITRA)
      ===================================================================== */}
      {subTab === 'about_details' && (
        <div className="space-y-8 text-xs">
          {/* A. SEJARAH PERJALANAN PERUSAHAAN (TIMELINE) */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <History className="w-4 h-4" />
                  <span>Bagian 1 · Halaman Tentang Kami</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Sejarah Perjalanan Perusahaan (1997 – Sekarang)
                </h3>
              </div>
              <button
                type="button"
                onClick={() => {
                  setEditingTimeline(null);
                  setTlForm({ year: '', title: '', description: '', isHighlighted: false });
                  setIsAddingTimeline(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shrink-0"
              >
                <Plus className="w-4 h-4" />
                <span>+ Tambah Tahun Sejarah</span>
              </button>
            </div>

            {(isAddingTimeline || editingTimeline) && (
              <form
                onSubmit={handleSaveTimeline}
                className="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-3"
              >
                <div className="font-bold text-blue-900">
                  {editingTimeline
                    ? `Edit Sejarah Tahun ${editingTimeline.year}`
                    : 'Tambah Tahun Sejarah Baru'}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label className="block font-bold mb-1">Tahun *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: 2024"
                      value={tlForm.year}
                      onChange={e => setTlForm({ ...tlForm, year: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block font-bold mb-1">Judul Tonggak Sejarah *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: Ekspansi Jaringan Fiber Optik"
                      value={tlForm.title}
                      onChange={e => setTlForm({ ...tlForm, title: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold"
                    />
                  </div>
                </div>
                <div>
                  <label className="block font-bold mb-1">Deskripsi Singkat *</label>
                  <textarea
                    rows={2}
                    required
                    value={tlForm.description}
                    onChange={e => setTlForm({ ...tlForm, description: e.target.value })}
                    className="w-full bg-white border border-slate-300 rounded-lg p-2"
                  />
                </div>
                <div className="flex items-center justify-between">
                  <label className="inline-flex items-center gap-2 font-semibold cursor-pointer">
                    <input
                      type="checkbox"
                      checked={tlForm.isHighlighted}
                      onChange={e => setTlForm({ ...tlForm, isHighlighted: e.target.checked })}
                    />
                    <span>Beri sorotan warna biru (Highlight)</span>
                  </label>
                  <div className="flex gap-2">
                    <button
                      type="button"
                      onClick={() => {
                        setIsAddingTimeline(false);
                        setEditingTimeline(null);
                      }}
                      className="px-3 py-1.5 bg-white border border-slate-300 rounded-lg font-semibold cursor-pointer"
                    >
                      Batal
                    </button>
                    <button
                      type="submit"
                      className="px-4 py-1.5 bg-blue-600 text-white rounded-lg font-bold cursor-pointer"
                    >
                      Simpan Sejarah
                    </button>
                  </div>
                </div>
              </form>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {timeline.map(item => (
                <div
                  key={item.id}
                  className={`p-4 rounded-xl border flex flex-col justify-between ${
                    item.isHighlighted
                      ? 'bg-blue-50/40 border-blue-300'
                      : 'bg-slate-50 border-slate-200'
                  }`}
                >
                  <div>
                    <div className="text-base font-extrabold text-blue-700">{item.year}</div>
                    <div className="font-bold text-slate-900 mt-0.5">{item.title}</div>
                    <p className="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                      {item.description}
                    </p>
                  </div>
                  <div className="flex justify-end gap-1.5 pt-3 mt-3 border-t border-slate-200/70">
                    <button
                      type="button"
                      onClick={() => {
                        setEditingTimeline(item);
                        setIsAddingTimeline(false);
                        setTlForm({
                          year: item.year,
                          title: item.title,
                          description: item.description,
                          isHighlighted: Boolean(item.isHighlighted)
                        });
                      }}
                      className="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Edit3 className="w-3 h-3" /> Edit
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeleteTimeline(item.id)}
                      className="px-2.5 py-1 bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Trash2 className="w-3 h-3" /> Hapus
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* B. VISI, MISI, BUDAYA KERJA & KOMITMEN PELANGGAN */}
          <form
            onSubmit={e =>
              handleSaveSettingsForm(e, 'Visi, Misi, Budaya Kerja & Komitmen Pelanggan')
            }
            className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4"
          >
            <div className="flex items-center justify-between border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <Compass className="w-4 h-4" />
                  <span>Bagian 2 · Halaman Tentang Kami</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Visi, Misi, Budaya Kerja Fungsional & Komitmen Pelanggan
                </h3>
              </div>
              <button
                type="submit"
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Visi, Misi & Budaya</span>
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold text-slate-700 mb-1">Visi Perusahaan</label>
                <textarea
                  rows={3}
                  value={settingsForm.vision}
                  onChange={e => setSettingsForm({ ...settingsForm, vision: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl p-3"
                />
              </div>
              <div>
                <label className="block font-bold text-slate-700 mb-1">Misi Perusahaan</label>
                <textarea
                  rows={3}
                  value={settingsForm.mission}
                  onChange={e => setSettingsForm({ ...settingsForm, mission: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl p-3"
                />
              </div>
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Budaya Kerja Fungsional
                </label>
                <textarea
                  rows={3}
                  value={settingsForm.culture || ''}
                  onChange={e => setSettingsForm({ ...settingsForm, culture: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl p-3"
                />
              </div>
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Komitmen Kepada Pelanggan
                </label>
                <textarea
                  rows={3}
                  value={settingsForm.commitment || ''}
                  onChange={e => setSettingsForm({ ...settingsForm, commitment: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl p-3"
                />
              </div>
            </div>
          </form>

          {/* C. SERTIFIKASI RESMI, LISENSI & OTORISASI BRAND */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <Award className="w-4 h-4" />
                  <span>Bagian 3 · Halaman Tentang Kami</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Sertifikasi Resmi, Lisensi & Otorisasi Brand ({certifications.length})
                </h3>
              </div>
              <button
                type="button"
                onClick={() => {
                  setEditingCert(null);
                  setCertForm({
                    title: '',
                    issuerOrBrand: '',
                    holder: 'PT. KREATIF GLOBAL SOLUSINDO',
                    yearOrValidity: ''
                  });
                  setIsAddingCert(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                <span>+ Tambah Sertifikasi / Lisensi</span>
              </button>
            </div>

            {(isAddingCert || editingCert) && (
              <form
                onSubmit={handleSaveCert}
                className="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-3"
              >
                <div className="font-bold text-blue-900">
                  {editingCert ? `Edit Sertifikasi: ${editingCert.title}` : 'Tambah Sertifikasi Baru'}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="block font-bold mb-1">Nama Sertifikasi / Lisensi *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: Sony Authorized Dealer"
                      value={certForm.title}
                      onChange={e => setCertForm({ ...certForm, title: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Penerbit / Prinsipal Brand *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: Sony / Panasonic / APJII"
                      value={certForm.issuerOrBrand}
                      onChange={e => setCertForm({ ...certForm, issuerOrBrand: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Penerima Sertifikat</label>
                    <input
                      type="text"
                      value={certForm.holder}
                      onChange={e => setCertForm({ ...certForm, holder: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Tahun / Status Validitas</label>
                    <input
                      type="text"
                      placeholder="Contoh: Resmi sejak 2020"
                      value={certForm.yearOrValidity}
                      onChange={e => setCertForm({ ...certForm, yearOrValidity: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                  </div>
                </div>
                <div className="flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => {
                      setIsAddingCert(false);
                      setEditingCert(null);
                    }}
                    className="px-3 py-1.5 bg-white border border-slate-300 rounded-lg font-semibold cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-1.5 bg-blue-600 text-white rounded-lg font-bold cursor-pointer"
                  >
                    Simpan Sertifikasi
                  </button>
                </div>
              </form>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {certifications.map(cert => (
                <div
                  key={cert.id}
                  className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between"
                >
                  <div>
                    <div className="font-bold text-slate-900 text-sm">{cert.title}</div>
                    <div className="text-blue-600 font-semibold mt-0.5">{cert.issuerOrBrand}</div>
                    <div className="text-[11px] text-slate-500 mt-1">Penerima: {cert.holder}</div>
                    {cert.yearOrValidity && (
                      <div className="text-[11px] text-slate-400 mt-0.5">{cert.yearOrValidity}</div>
                    )}
                  </div>
                  <div className="flex justify-end gap-1.5 pt-3 mt-3 border-t border-slate-200/70">
                    <button
                      type="button"
                      onClick={() => {
                        setEditingCert(cert);
                        setIsAddingCert(false);
                        setCertForm({
                          title: cert.title,
                          issuerOrBrand: cert.issuerOrBrand,
                          holder: cert.holder,
                          yearOrValidity: cert.yearOrValidity || ''
                        });
                      }}
                      className="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Edit3 className="w-3 h-3" /> Edit
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeleteCert(cert.id)}
                      className="px-2.5 py-1 bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Trash2 className="w-3 h-3" /> Hapus
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* D. MITRA KERJA & JARINGAN REKANAN */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <Handshake className="w-4 h-4" />
                  <span>Bagian 4 · Halaman Tentang Kami</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Mitra Kerja Dukungan Lastmile & Guywire Tower ISP ({partners.length})
                </h3>
              </div>
              <button
                type="button"
                onClick={() => {
                  setEditingPartner(null);
                  setPartnerForm({
                    name: '',
                    role: 'Guywire Tower Support for ISP',
                    serviceType: 'Tower & Backbone'
                  });
                  setIsAddingPartner(true);
                }}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                <span>+ Tambah Mitra Kerja</span>
              </button>
            </div>

            {(isAddingPartner || editingPartner) && (
              <form
                onSubmit={handleSavePartner}
                className="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-3"
              >
                <div className="font-bold text-blue-900">
                  {editingPartner ? `Edit Mitra: ${editingPartner.name}` : 'Tambah Mitra Kerja Baru'}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label className="block font-bold mb-1">Nama Perusahaan Mitra *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: PT. Telkom Indonesia"
                      value={partnerForm.name}
                      onChange={e => setPartnerForm({ ...partnerForm, name: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Peran Kerjasama *</label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: Lastmile Support for ISP"
                      value={partnerForm.role}
                      onChange={e => setPartnerForm({ ...partnerForm, role: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Bidang Layanan</label>
                    <input
                      type="text"
                      value={partnerForm.serviceType}
                      onChange={e => setPartnerForm({ ...partnerForm, serviceType: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-lg p-2"
                    />
                  </div>
                </div>
                <div className="flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => {
                      setIsAddingPartner(false);
                      setEditingPartner(null);
                    }}
                    className="px-3 py-1.5 bg-white border border-slate-300 rounded-lg font-semibold cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-1.5 bg-blue-600 text-white rounded-lg font-bold cursor-pointer"
                  >
                    Simpan Mitra
                  </button>
                </div>
              </form>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              {partners.map(p => (
                <div
                  key={p.id}
                  className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2"
                >
                  <div>
                    <div className="font-bold text-slate-900">{p.name}</div>
                    <div className="text-[11px] text-blue-600 font-medium">{p.role}</div>
                  </div>
                  <div className="flex items-center gap-1 shrink-0">
                    <button
                      type="button"
                      onClick={() => {
                        setEditingPartner(p);
                        setIsAddingPartner(false);
                        setPartnerForm({
                          name: p.name,
                          role: p.role,
                          serviceType: p.serviceType
                        });
                      }}
                      className="p-1.5 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 cursor-pointer"
                      title="Edit Mitra"
                    >
                      <Edit3 className="w-3.5 h-3.5 text-slate-700" />
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeletePartner(p.id)}
                      className="p-1.5 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 cursor-pointer"
                      title="Hapus Mitra"
                    >
                      <Trash2 className="w-3.5 h-3.5 text-rose-700" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* =====================================================================
          SUB-TAB 3: PAKET HARGA B2B & BANNER TENDER BoQ/KAK
      ===================================================================== */}
      {subTab === 'pricing_plans' && (
        <div className="space-y-6 text-xs">
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <DollarSign className="w-4 h-4" />
                  <span>Halaman Harga (harga.php)</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Kelola Kartu Paket Solusi & Layanan B2B ({pricingPlans.length} Paket)
                </h3>
              </div>
              <button
                type="button"
                onClick={openAddPlan}
                className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shrink-0"
              >
                <Plus className="w-4 h-4" />
                <span>+ Tambah Paket B2B Baru</span>
              </button>
            </div>

            {(isAddingPlan || editingPlan) && (
              <form
                onSubmit={handleSavePlan}
                className="p-5 rounded-2xl bg-blue-50/60 border-2 border-blue-200 space-y-4"
              >
                <div className="flex items-center justify-between border-b border-blue-200 pb-2">
                  <span className="font-extrabold text-blue-950 text-sm">
                    {editingPlan ? `Edit Paket: ${editingPlan.name}` : 'Tambah Paket Solusi B2B Baru'}
                  </span>
                  <button
                    type="button"
                    onClick={() => {
                      setIsAddingPlan(false);
                      setEditingPlan(null);
                    }}
                    className="text-slate-500 hover:text-slate-800 cursor-pointer"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block font-bold mb-1">Nama Paket Solusi *</label>
                    <input
                      type="text"
                      required
                      value={planForm.name}
                      onChange={e => setPlanForm({ ...planForm, name: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5 font-bold"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Target Segmen Pelanggan *</label>
                    <input
                      type="text"
                      required
                      value={planForm.targetAudience}
                      onChange={e => setPlanForm({ ...planForm, targetAudience: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block font-bold mb-1">Subjudul / Deskripsi Singkat Paket *</label>
                    <input
                      type="text"
                      required
                      value={planForm.subtitle}
                      onChange={e => setPlanForm({ ...planForm, subtitle: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Jaminan SLA / Garansi *</label>
                    <input
                      type="text"
                      required
                      value={planForm.sla}
                      onChange={e => setPlanForm({ ...planForm, sla: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5 font-semibold text-emerald-800"
                    />
                  </div>
                  <div>
                    <label className="block font-bold mb-1">Label Pita Atas (Opsional)</label>
                    <input
                      type="text"
                      placeholder="Contoh: Paling Diminati / Spesialisasi Lapangan"
                      value={planForm.badge}
                      onChange={e => setPlanForm({ ...planForm, badge: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5"
                    />
                  </div>
                </div>

                <div>
                  <label className="block font-bold mb-1">
                    Daftar Fitur & Cakupan Layanan (1 baris = 1 poin centang) *
                  </label>
                  <textarea
                    rows={5}
                    required
                    value={planForm.featuresText}
                    onChange={e => setPlanForm({ ...planForm, featuresText: e.target.value })}
                    className="w-full bg-white border border-slate-300 rounded-xl p-3 font-mono text-[11px]"
                  />
                </div>

                <div>
                  <label className="block font-bold mb-1">Catatan Rekomendasi Penggunaan</label>
                  <input
                    type="text"
                    value={planForm.recommendedFor}
                    onChange={e => setPlanForm({ ...planForm, recommendedFor: e.target.value })}
                    className="w-full bg-white border border-slate-300 rounded-xl p-2.5"
                  />
                </div>

                <div className="flex items-center justify-between pt-2">
                  <label className="inline-flex items-center gap-2 font-bold text-blue-900 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={planForm.isPopular}
                      onChange={e => setPlanForm({ ...planForm, isPopular: e.target.checked })}
                    />
                    <span>Jadikan Paket Unggulan Utama (Bingkai Biru & Pita Terlaris)</span>
                  </label>
                  <div className="flex gap-2">
                    <button
                      type="button"
                      onClick={() => {
                        setIsAddingPlan(false);
                        setEditingPlan(null);
                      }}
                      className="px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold cursor-pointer"
                    >
                      Batal
                    </button>
                    <button
                      type="submit"
                      className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold cursor-pointer"
                    >
                      Simpan Paket B2B
                    </button>
                  </div>
                </div>
              </form>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {pricingPlans.map(plan => (
                <div
                  key={plan.id}
                  className={`p-5 rounded-2xl border flex flex-col justify-between ${
                    plan.isPopular
                      ? 'border-2 border-blue-600 bg-blue-50/20'
                      : 'border-slate-200 bg-slate-50/70'
                  }`}
                >
                  <div>
                    <div className="flex items-center justify-between gap-2 mb-2">
                      <span className="text-[10px] font-bold uppercase text-slate-500">
                        {plan.targetAudience}
                      </span>
                      {plan.isPopular && (
                        <span className="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold">
                          {plan.badge || 'Unggulan'}
                        </span>
                      )}
                    </div>
                    <h4 className="text-sm font-extrabold text-slate-900">{plan.name}</h4>
                    <p className="text-[11px] text-slate-600 mt-1">{plan.subtitle}</p>
                    <div className="mt-2 inline-block px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-[11px] font-bold">
                      {plan.sla}
                    </div>
                    <ul className="mt-3 space-y-1 text-[11px] text-slate-700">
                      {plan.features.slice(0, 4).map((f, i) => (
                        <li key={i} className="truncate">
                          ✓ {f}
                        </li>
                      ))}
                      {plan.features.length > 4 && (
                        <li className="text-slate-400 italic">
                          +{plan.features.length - 4} fitur lainnya...
                        </li>
                      )}
                    </ul>
                  </div>

                  <div className="flex justify-end gap-2 pt-4 mt-4 border-t border-slate-200">
                    <button
                      type="button"
                      onClick={() => openEditPlan(plan)}
                      className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold flex items-center gap-1 cursor-pointer"
                    >
                      <Edit3 className="w-3.5 h-3.5" /> Edit Paket
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeletePlan(plan.id)}
                      className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Trash2 className="w-3.5 h-3.5" /> Hapus
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Banner Tender BoQ / KAK di Bawah Halaman Harga */}
          <form
            onSubmit={e => handleSaveSettingsForm(e, 'Banner Tender & BoQ/KAK')}
            className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4"
          >
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div>
                <h4 className="text-sm font-extrabold text-slate-900">
                  Banner Bawah Halaman Harga (Kebutuhan Tender, BoQ & KAK)
                </h4>
                <p className="text-[11px] text-slate-500">
                  Mengatur teks pada kotak biru tua di bagian bawah daftar Paket Solusi B2B.
                </p>
              </div>
              <button
                type="submit"
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer flex items-center gap-1.5"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Banner Tender</span>
              </button>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold mb-1">Label Kecil Atas (Kicker)</label>
                <input
                  type="text"
                  value={settingsForm.pricingBannerKicker || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, pricingBannerKicker: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">Teks Tombol Banner</label>
                <input
                  type="text"
                  value={settingsForm.pricingBannerCta || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, pricingBannerCta: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
              <div className="md:col-span-2">
                <label className="block font-bold mb-1">Judul Utama Banner Tender</label>
                <input
                  type="text"
                  value={settingsForm.pricingBannerTitle || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, pricingBannerTitle: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5 font-bold"
                />
              </div>
              <div className="md:col-span-2">
                <label className="block font-bold mb-1">Deskripsi Banner Tender</label>
                <textarea
                  rows={2}
                  value={settingsForm.pricingBannerDesc || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, pricingBannerDesc: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
            </div>
          </form>
        </div>
      )}

      {/* =====================================================================
          SUB-TAB 4: FAQ (PERTANYAAN UMUM & JAWABAN TEKNIS)
      ===================================================================== */}
      {subTab === 'faq_items' && (
        <div className="space-y-6 text-xs">
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex items-center gap-2 text-blue-600 font-bold uppercase text-[11px]">
                  <HelpCircle className="w-4 h-4" />
                  <span>Halaman FAQ (faq.php)</span>
                </div>
                <h3 className="text-base font-extrabold text-slate-900 mt-0.5">
                  Kelola Pertanyaan Umum & Jawaban Teknis ({faqs.length} Item FAQ)
                </h3>
              </div>
              <button
                type="button"
                onClick={() => {
                  setEditingFaq(null);
                  setFaqForm({ category: 'Umum', question: '', answer: '' });
                  setIsAddingFaq(true);
                }}
                className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shrink-0"
              >
                <Plus className="w-4 h-4" />
                <span>+ Tambah Pertanyaan FAQ</span>
              </button>
            </div>

            {(isAddingFaq || editingFaq) && (
              <form
                onSubmit={handleSaveFaq}
                className="p-5 rounded-2xl bg-blue-50/60 border-2 border-blue-200 space-y-3"
              >
                <div className="font-extrabold text-blue-950 text-sm">
                  {editingFaq ? 'Edit Pertanyaan & Jawaban FAQ' : 'Tambah Pertanyaan FAQ Baru'}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label className="block font-bold mb-1">Kategori FAQ *</label>
                    <select
                      value={faqForm.category}
                      onChange={e =>
                        setFaqForm({
                          ...faqForm,
                          category: e.target.value as FaqItem['category']
                        })
                      }
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5 font-semibold"
                    >
                      <option value="Umum">Umum</option>
                      <option value="Internet ISP">Internet ISP</option>
                      <option value="Keamanan & Tower">Keamanan & Tower</option>
                      <option value="Kontrak & Legalitas">Kontrak & Legalitas</option>
                    </select>
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block font-bold mb-1">Pertanyaan *</label>
                    <input
                      type="text"
                      required
                      placeholder="Masukkan kalimat pertanyaan..."
                      value={faqForm.question}
                      onChange={e => setFaqForm({ ...faqForm, question: e.target.value })}
                      className="w-full bg-white border border-slate-300 rounded-xl p-2.5 font-bold"
                    />
                  </div>
                </div>
                <div>
                  <label className="block font-bold mb-1">Jawaban Teknis Lengkap *</label>
                  <textarea
                    rows={4}
                    required
                    placeholder="Masukkan penjelasan jawaban..."
                    value={faqForm.answer}
                    onChange={e => setFaqForm({ ...faqForm, answer: e.target.value })}
                    className="w-full bg-white border border-slate-300 rounded-xl p-3"
                  />
                </div>
                <div className="flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => {
                      setIsAddingFaq(false);
                      setEditingFaq(null);
                    }}
                    className="px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-5 py-2 bg-blue-600 text-white rounded-xl font-bold cursor-pointer"
                  >
                    Simpan FAQ
                  </button>
                </div>
              </form>
            )}

            <div className="space-y-3">
              {faqs.map(faq => (
                <div
                  key={faq.id}
                  className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-start justify-between gap-4"
                >
                  <div className="space-y-1">
                    <span className="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-bold uppercase">
                      {faq.category}
                    </span>
                    <h4 className="text-sm font-bold text-slate-900">{faq.question}</h4>
                    <p className="text-xs text-slate-600 leading-relaxed">{faq.answer}</p>
                  </div>
                  <div className="flex items-center gap-1.5 shrink-0">
                    <button
                      type="button"
                      onClick={() => {
                        setEditingFaq(faq);
                        setIsAddingFaq(false);
                        setFaqForm({
                          category: faq.category,
                          question: faq.question,
                          answer: faq.answer
                        });
                      }}
                      className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold flex items-center gap-1 cursor-pointer"
                    >
                      <Edit3 className="w-3.5 h-3.5" /> Edit
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeleteFaq(faq.id)}
                      className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Trash2 className="w-3.5 h-3.5" /> Hapus
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Kotak Bantuan di Bawah FAQ */}
          <form
            onSubmit={e => handleSaveSettingsForm(e, 'Kotak Bantuan Bawah FAQ')}
            className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4"
          >
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div>
                <h4 className="text-sm font-extrabold text-slate-900">
                  Kotak Bantuan Langsung di Bawah Daftar FAQ
                </h4>
              </div>
              <button
                type="submit"
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer flex items-center gap-1.5"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Kotak Bantuan FAQ</span>
              </button>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold mb-1">Judul Kotak Bantuan FAQ</label>
                <input
                  type="text"
                  value={settingsForm.faqSupportTitle || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, faqSupportTitle: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5 font-bold"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">Deskripsi Kotak Bantuan FAQ</label>
                <input
                  type="text"
                  value={settingsForm.faqSupportDesc || ''}
                  onChange={e =>
                    setSettingsForm({ ...settingsForm, faqSupportDesc: e.target.value })
                  }
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>
            </div>
          </form>
        </div>
      )}

      {/* =====================================================================
          SUB-TAB 5: HEADER TOPBAR, SALURAN DIGITAL & TEKS FOOTER
      ===================================================================== */}
      {subTab === 'header_footer_text' && (
        <form
          onSubmit={e =>
            handleSaveSettingsForm(e, 'Pengaturan Teks Header, Saluran Digital & Footer')
          }
          className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs"
        >
          <div className="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
              <h3 className="text-base font-extrabold text-slate-900">
                Edit Jam Operasional Header, Saluran Website & Deskripsi Footer
              </h3>
              <p className="text-slate-500 mt-0.5">
                Mengatur teks jam operasional di bar atas, alamat website di halaman Kontak, deskripsi perusahaan di Footer, dan teks hak cipta.
              </p>
            </div>
            <button
              type="submit"
              className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer flex items-center gap-1.5"
            >
              <Check className="w-4 h-4" />
              <span>Simpan Header & Footer</span>
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-bold mb-1">
                Label Brand di Footer (Menggantikan PT. KGS — Default: KGSNET)
              </label>
              <input
                type="text"
                value={settingsForm.footerBrandLabel || 'KGSNET'}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, footerBrandLabel: e.target.value })
                }
                placeholder="KGSNET"
                className="w-full border border-slate-300 rounded-xl p-2.5 font-bold"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">
                Alamat Kantor (Tampil di Footer & Kontak)
              </label>
              <input
                type="text"
                value={settingsForm.addressPalembang || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, addressPalembang: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">
                Jam Operasional (Tampil di Bar Atas Header)
              </label>
              <input
                type="text"
                value={settingsForm.operationalHours || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, operationalHours: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Teks Hak Cipta (Copyright Footer)</label>
              <input
                type="text"
                value={settingsForm.copyrightText || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, copyrightText: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Domain Portal Korporat (Kontak)</label>
              <input
                type="text"
                value={settingsForm.websiteCorporate || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, websiteCorporate: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Domain Portal ISP KGSNet (Kontak)</label>
              <input
                type="text"
                value={settingsForm.websiteIsp || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, websiteIsp: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
              />
            </div>
            <div className="md:col-span-2">
              <label className="block font-bold mb-1">
                Deskripsi Perusahaan di Kolom Kiri Footer (Bawah)
              </label>
              <textarea
                rows={3}
                value={settingsForm.footerDescription || ''}
                onChange={e =>
                  setSettingsForm({ ...settingsForm, footerDescription: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-3"
              />
            </div>
          </div>
        </form>
      )}
    </div>
  );
};
