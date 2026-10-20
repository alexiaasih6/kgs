import React, { useState } from 'react';
import {
  ServiceItem,
  ClientItem,
  InquiryMessage,
  ServiceCategory,
  ProjectPhoto,
  ArticleItem,
  StaticPageItem,
  AdminUserItem,
  AuditLogItem,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME,
  HeroSliderConfig,
  INITIAL_HERO_SLIDER_CONFIG,
  TimelineMilestoneItem,
  INITIAL_TIMELINE_MILESTONES,
  CertificationItem,
  VERIFIED_CERTIFICATIONS,
  PartnerItem,
  VERIFIED_PARTNERS,
  PricingPlanItem,
  B2B_PRICING_PLANS,
  FaqItem,
  FAQ_DATA,
  buildGoogleMapEmbedUrl,
  buildGoogleMapDirectLink
} from '../../data/companyData';
import { AdminServices } from './AdminServices';
import { AdminCategories } from './AdminCategories';
import { AdminClients } from './AdminClients';
import { AdminPortfolio } from './AdminPortfolio';
import { AdminArticles } from './AdminArticles';
import { AdminPages } from './AdminPages';
import { AdminUsers } from './AdminUsers';
import { AdminAuditLogs } from './AdminAuditLogs';
import { AdminInquiries } from './AdminInquiries';
import { AdminAudit } from './AdminAudit';
import { AdminTestingCenter } from './AdminTestingCenter';
import { AdminNavigation } from './AdminNavigation';
import { AdminThemeSlider } from './AdminThemeSlider';
import { AdminWebsiteSections } from './AdminWebsiteSections';
import {
  LayoutDashboard,
  Layers,
  FolderTree,
  Users,
  MessageSquare,
  ShieldCheck,
  FlaskConical,
  Settings,
  ArrowLeft,
  Download,
  Lock,
  LogOut,
  AlertCircle,
  FileText,
  Camera,
  BookOpen,
  UserCheck,
  Activity,
  Plus,
  Send,
  Server,
  Upload,
  Image as ImageIcon,
  Trash2,
  Check,
  MapPin,
  Navigation,
  ExternalLink,
  Menu,
  X,
  Palette,
  Edit3,
  Sun,
  Moon
} from 'lucide-react';
import { ColorMode } from '../../utils/exportSync';

interface AdminDashboardProps {
  services: ServiceItem[];
  categories: ServiceCategory[];
  clients: ClientItem[];
  projects: ProjectPhoto[];
  articles: ArticleItem[];
  pages: StaticPageItem[];
  users: AdminUserItem[];
  logs: AuditLogItem[];
  inquiries: InquiryMessage[];
  onUpdateServices: (services: ServiceItem[]) => void;
  onUpdateCategories: (categories: ServiceCategory[]) => void;
  onUpdateClients: (clients: ClientItem[]) => void;
  onUpdateProjects: (projects: ProjectPhoto[]) => void;
  onUpdateArticles: (articles: ArticleItem[]) => void;
  onUpdatePages: (pages: StaticPageItem[]) => void;
  onUpdateUsers: (users: AdminUserItem[]) => void;
  onUpdateInquiries: (inquiries: InquiryMessage[]) => void;
  onExitAdmin: () => void;
  onOpenPhpExport: () => void;
  companyLogo?: string;
  onUpdateCompanyLogo?: (logo: string) => void;
  companySettings?: CompanySettings;
  onUpdateCompanySettings?: (settings: CompanySettings) => void;
  navigationMenus?: NavigationMenuItem[];
  onUpdateNavigationMenus?: (menus: NavigationMenuItem[]) => void;
  themeConfig?: WebsiteThemeConfig;
  onUpdateThemeConfig?: (theme: WebsiteThemeConfig) => void;
  sliderConfig?: HeroSliderConfig;
  onUpdateSliderConfig?: (slider: HeroSliderConfig) => void;
  timeline?: TimelineMilestoneItem[];
  onUpdateTimeline?: (items: TimelineMilestoneItem[]) => void;
  certifications?: CertificationItem[];
  onUpdateCertifications?: (items: CertificationItem[]) => void;
  partners?: PartnerItem[];
  onUpdatePartners?: (items: PartnerItem[]) => void;
  pricingPlans?: PricingPlanItem[];
  onUpdatePricingPlans?: (items: PricingPlanItem[]) => void;
  faqs?: FaqItem[];
  onUpdateFaqs?: (items: FaqItem[]) => void;
  onOpenLiveEditor?: () => void;
  initialTab?: string;
  colorMode?: ColorMode;
  isDarkMode?: boolean;
  onChangeColorMode?: (mode: ColorMode) => void;
}

export const AdminDashboard: React.FC<AdminDashboardProps> = ({
  services,
  categories,
  clients,
  projects,
  articles,
  pages,
  users,
  logs,
  inquiries,
  onUpdateServices,
  onUpdateCategories,
  onUpdateClients,
  onUpdateProjects,
  onUpdateArticles,
  onUpdatePages,
  onUpdateUsers,
  onUpdateInquiries,
  onExitAdmin,
  onOpenPhpExport,
  companyLogo = '',
  onUpdateCompanyLogo,
  companySettings: propCompanySettings,
  onUpdateCompanySettings,
  navigationMenus = INITIAL_NAVIGATION_MENUS,
  onUpdateNavigationMenus,
  themeConfig = DEFAULT_WEBSITE_THEME,
  onUpdateThemeConfig,
  sliderConfig = INITIAL_HERO_SLIDER_CONFIG,
  onUpdateSliderConfig,
  timeline = INITIAL_TIMELINE_MILESTONES,
  onUpdateTimeline,
  certifications = VERIFIED_CERTIFICATIONS,
  onUpdateCertifications,
  partners = VERIFIED_PARTNERS,
  onUpdatePartners,
  pricingPlans = B2B_PRICING_PLANS,
  onUpdatePricingPlans,
  faqs = FAQ_DATA,
  onUpdateFaqs,
  onOpenLiveEditor,
  initialTab,
  colorMode = 'light',
  isDarkMode = false,
  onChangeColorMode
}) => {
  // Authentication gate state
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(() => {
    return localStorage.getItem('kgs_admin_auth') === 'true';
  });

  const [username, setUsername] = useState('admin_kgs');
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState('');
  const [loginAttempts, setLoginAttempts] = useState(0);
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

  const mapInitialTab = (raw?: string): { tab: string; sub?: any } => {
    if (!raw) return { tab: 'overview' };
    if (raw === 'hero_sections' || raw === 'hero_noc' || raw === 'hero') return { tab: 'website_sections', sub: 'hero_noc' };
    if (raw === 'about_sections' || raw === 'about_details' || raw === 'about') return { tab: 'website_sections', sub: 'about_details' };
    if (raw === 'pricing_sections' || raw === 'pricing_plans' || raw === 'pricing') return { tab: 'website_sections', sub: 'pricing_plans' };
    if (raw === 'faq_sections' || raw === 'faq_items' || raw === 'faq') return { tab: 'website_sections', sub: 'faq_items' };
    if (raw === 'footer_sections' || raw === 'header_footer_text' || raw === 'footer') return { tab: 'website_sections', sub: 'header_footer_text' };
    if (raw === 'guide' || raw === 'website_sections') return { tab: 'website_sections', sub: 'guide' };
    return { tab: raw };
  };

  const resolvedInit = mapInitialTab(initialTab);

  const [activeTab, setActiveTab] = useState<
    | 'overview'
    | 'website_sections'
    | 'services'
    | 'categories'
    | 'portfolio'
    | 'clients'
    | 'articles'
    | 'pages'
    | 'inquiries'
    | 'navigation'
    | 'theme_slider'
    | 'users'
    | 'logs'
    | 'audit'
    | 'testing'
    | 'settings'
  >((resolvedInit.tab as any) || 'overview');

  const [websiteSectionSubTab, setWebsiteSectionSubTab] = useState<any>(
    resolvedInit.sub || 'guide'
  );

  React.useEffect(() => {
    if (initialTab) {
      const mapped = mapInitialTab(initialTab);
      setActiveTab(mapped.tab as any);
      if (mapped.sub) {
        setWebsiteSectionSubTab(mapped.sub);
      }
    }
  }, [initialTab]);

  const newInquiriesCount = inquiries.filter(i => i.status === 'Baru').length;

  const serviceCounts = categories.reduce((acc, cat) => {
    acc[cat.id] = services.filter(s => s.categoryId === cat.id).length;
    return acc;
  }, {} as Record<string, number>);

  // Company identity, WhatsApp CS, Google Maps & logo settings state
  const [companySettings, setCompanySettings] = useState<CompanySettings>(() => {
    if (propCompanySettings) return propCompanySettings;
    const saved = localStorage.getItem('kgs_company_settings');
    return saved ? { ...DEFAULT_COMPANY_SETTINGS, ...JSON.parse(saved) } : DEFAULT_COMPANY_SETTINGS;
  });

  const [latInput, setLatInput] = useState('-2.9718');
  const [lngInput, setLngInput] = useState('104.7644');

  const [logoPreview, setLogoPreview] = useState<string>(() => {
    return localStorage.getItem('kgs_custom_logo') || companyLogo || '';
  });

  const [settingsSavedMsg, setSettingsSavedMsg] = useState(false);

  const handleLogoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      return;
    }
    const reader = new FileReader();
    reader.onload = event => {
      const result = event.target?.result as string;
      setLogoPreview(result);
    };
    reader.readAsDataURL(file);
  };

  const handleRemoveLogo = () => {
    setLogoPreview('');
    localStorage.removeItem('kgs_custom_logo');
    onUpdateCompanyLogo?.('');
  };

  const handleSaveCompanySettings = (e: React.FormEvent) => {
    e.preventDefault();
    localStorage.setItem('kgs_company_settings', JSON.stringify(companySettings));
    onUpdateCompanySettings?.(companySettings);
    if (logoPreview) {
      localStorage.setItem('kgs_custom_logo', logoPreview);
      onUpdateCompanyLogo?.(logoPreview);
    } else {
      localStorage.removeItem('kgs_custom_logo');
      onUpdateCompanyLogo?.('');
    }
    setSettingsSavedMsg(true);
    setTimeout(() => setSettingsSavedMsg(false), 4000);
  };

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    if (loginAttempts >= 5) {
      setLoginError('Terlalu banyak percobaan gagal. Akses terkunci sementara selama 15 menit.');
      return;
    }

    if (password.length < 6) {
      setLoginAttempts(prev => prev + 1);
      setLoginError(`Password salah. Minimal 6 karakter. Sisa percobaan: ${4 - loginAttempts}`);
      return;
    }

    setIsAuthenticated(true);
    localStorage.setItem('kgs_admin_auth', 'true');
    setLoginError('');
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    localStorage.removeItem('kgs_admin_auth');
    setPassword('');
  };

  const handleSelectTab = (tab: typeof activeTab) => {
    setActiveTab(tab);
    setMobileSidebarOpen(false);
  };

  // If not authenticated, render secure login form
  if (!isAuthenticated) {
    return (
      <div className="min-h-screen bg-slate-900 text-slate-100 flex items-center justify-center p-4">
        <div className="max-w-md w-full bg-slate-800 rounded-3xl border border-slate-700 p-8 shadow-2xl space-y-6">
          <div className="text-center space-y-2">
            <div className="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto text-white font-black text-xl shadow-lg">
              KGS
            </div>
            <h2 className="text-xl font-bold text-white tracking-tight">Login Administrator CMS</h2>
            <p className="text-xs text-slate-400">
              PT. Kreatif Global Solusindo — Portal Kendali Perusahaan
            </p>
          </div>

          <div className="bg-slate-700/50 p-3.5 rounded-xl border border-slate-600/60 text-xs text-slate-300 space-y-1">
            <div className="font-semibold text-emerald-400 flex items-center gap-1.5">
              <ShieldCheck className="w-4 h-4" />
              <span>Proteksi Anti Brute-Force & BCRYPT Hash Aktif</span>
            </div>
            <p className="text-[11px] text-slate-400">
              Gunakan kata sandi administrator (minimal 6 karakter) untuk mengakses panel kendali.
            </p>
          </div>

          {loginError && (
            <div className="bg-rose-950/60 border border-rose-800 text-rose-300 p-3.5 rounded-xl text-xs flex items-center gap-2">
              <AlertCircle className="w-4 h-4 shrink-0 text-rose-400" />
              <span>{loginError}</span>
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4 text-xs">
            <div>
              <label className="block text-slate-300 font-semibold mb-1">Username Admin</label>
              <input
                type="text"
                value={username}
                onChange={e => setUsername(e.target.value)}
                required
                className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
              />
            </div>

            <div>
              <label className="block text-slate-300 font-semibold mb-1">Kata Sandi (Password)</label>
              <div className="relative">
                <input
                  type="password"
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  required
                  placeholder="Ketik password admin (misal: admin123)"
                  className="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                />
              </div>
            </div>

            <button
              type="submit"
              className="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition shadow-lg flex items-center justify-center gap-2 cursor-pointer"
            >
              <Lock className="w-4 h-4" />
              <span>Masuk ke Admin CMS</span>
            </button>
          </form>

          <div className="pt-2 text-center">
            <button
              onClick={onExitAdmin}
              className="text-xs text-slate-400 hover:text-white transition inline-flex items-center gap-1 cursor-pointer"
            >
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Kembali ke Website Publik</span>
            </button>
          </div>
        </div>
      </div>
    );
  }

  const sidebarContent = (
    <>
      {/* Brand Identity in Left Sidebar */}
      <div className="p-5 border-b border-slate-800 flex items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          {logoPreview ? (
            <img
              src={logoPreview}
              alt="Logo KGS"
              className="w-9 h-9 rounded-lg bg-white p-1 object-contain shrink-0"
            />
          ) : (
            <span className="w-9 h-9 bg-blue-600 text-white font-black text-sm rounded-xl flex items-center justify-center shrink-0 shadow-sm">
              KGS
            </span>
          )}
          <div>
            <span className="font-extrabold text-sm text-white block leading-tight">
              Admin CMS Corporate
            </span>
            <span className="text-[10px] text-blue-400 font-semibold block">
              PT. Kreatif Global Solusindo
            </span>
          </div>
        </div>
        <button
          type="button"
          onClick={() => setMobileSidebarOpen(false)}
          className="lg:hidden p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      {/* Left Vertical Menu Navigation */}
      <nav className="flex-1 overflow-y-auto p-3.5 space-y-5 text-xs">
        {/* Group 1: Utama */}
        <div className="space-y-1">
          <div className="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
            Menu Utama
          </div>
          <button
            onClick={() => handleSelectTab('overview')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'overview'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <LayoutDashboard className="w-4 h-4 shrink-0" />
              <span>Dashboard</span>
            </div>
          </button>

          <button
            onClick={() => handleSelectTab('inquiries')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'inquiries'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <MessageSquare className="w-4 h-4 shrink-0" />
              <span>Permintaan Masuk</span>
            </div>
            {newInquiriesCount > 0 && (
              <span className="px-2 py-0.5 bg-amber-500 text-slate-950 rounded-full text-[10px] font-extrabold">
                {newInquiriesCount}
              </span>
            )}
          </button>
        </div>

        {/* Group 2: Katalog & Konten */}
        <div className="space-y-1">
          <div className="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
            Katalog & Konten Website
          </div>

          <button
            onClick={() => {
              setWebsiteSectionSubTab('guide');
              handleSelectTab('website_sections');
            }}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'website_sections'
                ? 'bg-emerald-600 text-white shadow-sm'
                : 'text-emerald-300 bg-emerald-950/40 hover:bg-emerald-900/50 border border-emerald-800/50'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Edit3 className="w-4 h-4 shrink-0" />
              <span>Semua Bagian Halaman</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500 text-slate-950 font-extrabold">
              Lengkap
            </span>
          </button>

          <button
            onClick={() => {
              setWebsiteSectionSubTab('hero_noc');
              handleSelectTab('website_sections');
            }}
            className="w-full px-3 py-2 rounded-xl font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition flex items-center justify-between cursor-pointer text-[11px]"
          >
            <span>↳ Beranda (Statistik & NOC)</span>
          </button>

          <button
            onClick={() => {
              setWebsiteSectionSubTab('about_details');
              handleSelectTab('website_sections');
            }}
            className="w-full px-3 py-2 rounded-xl font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition flex items-center justify-between cursor-pointer text-[11px]"
          >
            <span>↳ Sejarah, Visi-Misi & Mitra</span>
          </button>

          <button
            onClick={() => {
              setWebsiteSectionSubTab('pricing_plans');
              handleSelectTab('website_sections');
            }}
            className="w-full px-3 py-2 rounded-xl font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition flex items-center justify-between cursor-pointer text-[11px]"
          >
            <span>↳ Paket Harga B2B ({pricingPlans.length})</span>
          </button>

          <button
            onClick={() => {
              setWebsiteSectionSubTab('faq_items');
              handleSelectTab('website_sections');
            }}
            className="w-full px-3 py-2 rounded-xl font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition flex items-center justify-between cursor-pointer text-[11px]"
          >
            <span>↳ Tanya Jawab / FAQ ({faqs.length})</span>
          </button>

          <button
            onClick={() => handleSelectTab('audit')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'audit'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <ShieldCheck
                className={`w-4 h-4 shrink-0 ${
                  activeTab === 'audit' ? 'text-white' : 'text-emerald-400'
                }`}
              />
              <span>Katalog Layanan ({services.length} Standar)</span>
            </div>
          </button>

          <button
            onClick={() => handleSelectTab('services')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'services'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Layers className="w-4 h-4 shrink-0" />
              <span>Layanan</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {services.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('categories')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'categories'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <FolderTree className="w-4 h-4 shrink-0" />
              <span>Kategori Segmen</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {categories.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('portfolio')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'portfolio'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Camera className="w-4 h-4 shrink-0" />
              <span>Dokumentasi Proyek</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {projects.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('clients')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'clients'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Users className="w-4 h-4 shrink-0" />
              <span>Daftar Klien</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {clients.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('articles')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'articles'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <BookOpen className="w-4 h-4 shrink-0" />
              <span>Artikel & Label</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {articles.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('pages')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'pages'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <FileText className="w-4 h-4 shrink-0" />
              <span>Halaman Statis</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {pages.length}
            </span>
          </button>
        </div>

        {/* Group 3: Pengaturan & Sistem */}
        <div className="space-y-1">
          <div className="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
            Pengaturan & Keamanan
          </div>

          <button
            onClick={() => handleSelectTab('theme_slider')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'theme_slider'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Palette className="w-4 h-4 shrink-0 text-amber-400" />
              <span>Tema & Slider Beranda</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
              Baru
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('settings')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'settings'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Settings className="w-4 h-4 shrink-0" />
              <span>Identitas, Logo & Maps</span>
            </div>
          </button>

          <button
            onClick={() => handleSelectTab('navigation')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'navigation'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Navigation className="w-4 h-4 shrink-0" />
              <span>Pengaturan Navigasi</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {navigationMenus.filter(m => m.isActive).length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('users')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'users'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <UserCheck className="w-4 h-4 shrink-0" />
              <span>Pengguna Admin</span>
            </div>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
              {users.length}
            </span>
          </button>

          <button
            onClick={() => handleSelectTab('logs')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'logs'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <Activity className="w-4 h-4 shrink-0" />
              <span>Log Keamanan</span>
            </div>
          </button>

          <button
            onClick={() => handleSelectTab('testing')}
            className={`w-full px-3 py-2.5 rounded-xl font-semibold transition flex items-center justify-between cursor-pointer ${
              activeTab === 'testing'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
            }`}
          >
            <div className="flex items-center gap-2.5">
              <FlaskConical className="w-4 h-4 shrink-0" />
              <span>Pusat Uji Sistem</span>
            </div>
          </button>
        </div>
      </nav>

      {/* Sidebar Bottom Actions */}
      <div className="p-4 border-t border-slate-800 space-y-2 text-xs">
        <button
          onClick={onOpenPhpExport}
          className="w-full py-2.5 px-3 bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 font-semibold rounded-xl transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <Download className="w-3.5 h-3.5" />
          <span>Export Source Code PHP</span>
        </button>

        <div className="grid grid-cols-2 gap-2">
          <button
            onClick={onExitAdmin}
            className="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            <span>Website</span>
          </button>
          <button
            onClick={handleLogout}
            className="py-2 px-3 bg-rose-950/70 hover:bg-rose-900 text-rose-300 border border-rose-800/80 font-semibold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <LogOut className="w-3.5 h-3.5" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </>
  );

  return (
    <div className="min-h-screen bg-slate-100 text-slate-800 flex font-sans">
      {/* Desktop Left Sidebar */}
      <aside className="hidden lg:flex w-72 bg-slate-900 text-slate-200 flex-col shrink-0 border-r border-slate-800 sticky top-0 h-screen z-40">
        {sidebarContent}
      </aside>

      {/* Mobile Left Sidebar Drawer */}
      {mobileSidebarOpen && (
        <div className="fixed inset-0 z-50 lg:hidden flex">
          <div
            className="fixed inset-0 bg-slate-950/70 backdrop-blur-xs"
            onClick={() => setMobileSidebarOpen(false)}
          />
          <aside className="relative w-72 max-w-[85vw] bg-slate-900 text-slate-200 flex flex-col h-full z-10 shadow-2xl">
            {sidebarContent}
          </aside>
        </div>
      )}

      {/* Right Main Content Column */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top Utility Header */}
        <header className="bg-white border-b border-slate-200 sticky top-0 z-30 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 shadow-2xs">
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => setMobileSidebarOpen(true)}
              className="lg:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer"
              title="Buka Menu Admin"
            >
              <Menu className="w-5 h-5" />
            </button>
            <div>
              <h1 className="text-sm sm:text-base font-extrabold text-slate-900">
                {activeTab === 'overview' && 'Dashboard Utama CMS'}
                {activeTab === 'website_sections' && 'Kelola Semua Bagian Halaman Website (Beranda, Tentang, Harga, FAQ, Header/Footer)'}
                {activeTab === 'audit' && `Katalog Layanan (${services.length} Standar)`}
                {activeTab === 'services' && 'Manajemen Layanan'}
                {activeTab === 'categories' && 'Kategori Segmen Layanan'}
                {activeTab === 'portfolio' && 'Dokumentasi Proyek Lapangan'}
                {activeTab === 'clients' && 'Daftar Klien Terverifikasi'}
                {activeTab === 'articles' && 'Manajemen Artikel, Gambar & Label'}
                {activeTab === 'pages' && 'Manajemen Halaman Statis'}
                {activeTab === 'inquiries' && 'Permintaan Penawaran Masuk (Leads)'}
                {activeTab === 'settings' && 'Identitas Perusahaan, Logo, Alamat Kantor, WhatsApp CS & Google Maps'}
                {activeTab === 'navigation' && 'Pengaturan Navigasi — Header & Footer'}
                {activeTab === 'theme_slider' && 'Pengaturan Tema Website & Gambar Slider Beranda'}
                {activeTab === 'users' && 'Manajemen Pengguna Admin'}
                {activeTab === 'logs' && 'Log Aktivitas & Keamanan'}
                {activeTab === 'testing' && 'Pusat Uji Sistem'}
              </h1>
              <p className="text-[11px] text-slate-500 hidden sm:block">
                {companySettings.legalName} — Tema Aktif: <strong>{themeConfig.name}</strong>
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            {/* Dark / Light Theme Switcher in Admin CMS */}
            {onChangeColorMode && (
              <div
                className="inline-flex items-center rounded-xl p-1 border bg-slate-100 border-slate-200"
                role="group"
                aria-label="Pilih Tema Tampilan Admin"
              >
                <button
                  type="button"
                  onClick={() => onChangeColorMode('light')}
                  title="Light Mode (Terang)"
                  className={`px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-xs ${
                    colorMode === 'light'
                      ? 'bg-white text-amber-600 shadow-xs font-bold'
                      : 'text-slate-600 hover:text-slate-900'
                  }`}
                >
                  <Sun className="w-3.5 h-3.5" />
                  <span className="hidden xl:inline">Light</span>
                </button>
                <button
                  type="button"
                  onClick={() => onChangeColorMode('dark')}
                  title="Dark Mode (Gelap)"
                  className={`px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1 text-xs ${
                    colorMode === 'dark'
                      ? 'bg-slate-800 text-blue-400 shadow-xs font-bold'
                      : 'text-slate-600 hover:text-slate-900'
                  }`}
                >
                  <Moon className="w-3.5 h-3.5" />
                  <span className="hidden xl:inline">Dark</span>
                </button>
              </div>
            )}
            <button
              onClick={() => setActiveTab('theme_slider')}
              className="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
            >
              <Palette className="w-3.5 h-3.5 text-amber-600" />
              <span className="hidden md:inline">Tema & Slider</span>
            </button>
            <button
              onClick={() => {
                if (onOpenLiveEditor) onOpenLiveEditor();
                else onExitAdmin();
              }}
              className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">Edit Langsung di Website</span>
            </button>
            <button
              onClick={onExitAdmin}
              className="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
            >
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Lihat Website</span>
            </button>
          </div>
        </header>

        {/* Main Workspace Body */}
        <main className="p-4 sm:p-8 flex-1 w-full max-w-6xl mx-auto space-y-6">
          {/* Tab 1: Overview */}
          {activeTab === 'overview' && (
            <div className="space-y-6">
              {/* Quick Actions & Welcome */}
              <div className="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div className="space-y-2">
                  <span className="px-3 py-1 bg-blue-500/30 text-blue-300 text-xs font-bold rounded-full border border-blue-400/30 uppercase tracking-wider">
                    Panel Kendali Pusat PT. KGS
                  </span>
                  <h2 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, Administrator Utama!
                  </h2>
                  <p className="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Sistem CMS Enterprise {companySettings.legalName}. Kelola katalog standar layanan, dokumentasi proyek teknis, artikel edukasi, lokasi Google Maps, serta terima permintaan penawaran langsung ke WhatsApp CS ({companySettings.whatsapp}).
                  </p>
                </div>

                {/* Quick Actions */}
                <div className="flex flex-wrap items-center gap-2">
                  <button
                    onClick={() => setActiveTab('audit')}
                    className="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                  >
                    <ShieldCheck className="w-4 h-4" />
                    <span>Katalog ({services.length} Standar)</span>
                  </button>
                  <button
                    onClick={() => setActiveTab('articles')}
                    className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                  >
                    <Plus className="w-4 h-4" />
                    <span>Tulis Artikel</span>
                  </button>
                  <button
                    onClick={() => setActiveTab('settings')}
                    className="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-900 font-extrabold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                  >
                    <MapPin className="w-4 h-4" />
                    <span>Kontak & Google Maps</span>
                  </button>
                </div>
              </div>

              {/* KPI Cards Grid */}
              <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                  onClick={() => setActiveTab('audit')}
                  className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs cursor-pointer hover:border-blue-400 transition"
                >
                  <div className="text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    Katalog Layanan
                  </div>
                  <div className="text-3xl font-black text-blue-700 mt-1">{services.length}</div>
                  <div className="text-xs text-emerald-600 font-semibold mt-1">
                    ✓ {services.filter(s => s.isPublished).length} Terbit (Dapat Diedit)
                  </div>
                </div>

                <div
                  onClick={() => setActiveTab('clients')}
                  className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs cursor-pointer hover:border-blue-400 transition"
                >
                  <div className="text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    Klien Terverifikasi
                  </div>
                  <div className="text-3xl font-black text-slate-900 mt-1">{clients.length}</div>
                  <div className="text-xs text-slate-500 mt-1">Mitra Korporasi & Instansi</div>
                </div>

                <div
                  onClick={() => setActiveTab('portfolio')}
                  className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs cursor-pointer hover:border-blue-400 transition"
                >
                  <div className="text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    Dokumentasi Proyek
                  </div>
                  <div className="text-3xl font-black text-indigo-700 mt-1">{projects.length}</div>
                  <div className="text-xs text-slate-500 mt-1">Pabrikasi, Tower & Grounding</div>
                </div>

                <div
                  onClick={() => setActiveTab('inquiries')}
                  className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs cursor-pointer hover:border-blue-400 transition"
                >
                  <div className="text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    Permintaan Penawaran
                  </div>
                  <div className="text-3xl font-black text-amber-600 mt-1">
                    {newInquiriesCount} Baru
                  </div>
                  <div className="text-xs text-slate-500 mt-1">Total {inquiries.length} Masuk</div>
                </div>
              </div>

              {/* Content Split: Recent Leads & System Status */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                  <div className="flex items-center justify-between border-b pb-3">
                    <h3 className="font-bold text-sm text-slate-900">
                      Permintaan Penawaran Terbaru (Direct WhatsApp CS)
                    </h3>
                    <button
                      onClick={() => setActiveTab('inquiries')}
                      className="text-xs text-blue-600 font-bold hover:underline cursor-pointer"
                    >
                      Buka Semua Leads &rarr;
                    </button>
                  </div>

                  <div className="space-y-3">
                    {inquiries.slice(0, 4).map(inq => (
                      <div
                        key={inq.id}
                        className="p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs"
                      >
                        <div>
                          <div className="flex items-center gap-2">
                            <span className="font-extrabold text-slate-900">{inq.name}</span>
                            <span className="text-[11px] text-blue-700 bg-blue-50 px-2 py-0.2 rounded font-semibold">
                              {inq.company}
                            </span>
                          </div>
                          <p className="text-slate-500 text-[11px] mt-0.5">
                            {inq.location} · 📞 {inq.phone}
                          </p>
                          <p className="text-slate-700 mt-1 line-clamp-1 italic">"{inq.message}"</p>
                        </div>

                        <div className="flex items-center gap-2 shrink-0">
                          <span
                            className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                              inq.status === 'Baru'
                                ? 'bg-amber-100 text-amber-800'
                                : inq.status === 'Diproses'
                                ? 'bg-blue-100 text-blue-800'
                                : 'bg-emerald-100 text-emerald-800'
                            }`}
                          >
                            {inq.status}
                          </span>
                          <button
                            onClick={() => setActiveTab('inquiries')}
                            className="px-2.5 py-1 bg-white hover:bg-slate-100 border text-slate-700 font-semibold rounded text-[11px] cursor-pointer"
                          >
                            Tindak Lanjut
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* System Security & Quick Info Box */}
                <div className="space-y-6">
                  <div className="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                    <div className="flex items-center gap-2 font-bold text-slate-900">
                      <Server className="w-4 h-4 text-blue-600" />
                      <span>Status Lingkungan & Keamanan</span>
                    </div>
                    <div className="space-y-2 pt-2 border-t text-slate-600">
                      <div className="flex justify-between">
                        <span>Runtime Backend:</span>
                        <strong className="text-slate-900 font-mono">PHP 8.2 / Native</strong>
                      </div>
                      <div className="flex justify-between">
                        <span>Database Engine:</span>
                        <strong className="text-slate-900 font-mono">MySQL / MariaDB</strong>
                      </div>
                      <div className="flex justify-between">
                        <span>WhatsApp CS Penawaran:</span>
                        <strong className="text-emerald-700 font-semibold">
                          {companySettings.whatsapp}
                        </strong>
                      </div>
                      <div className="flex justify-between">
                        <span>Peta Google Maps:</span>
                        <strong className="text-emerald-700 font-semibold">✓ Aktif di Kontak</strong>
                      </div>
                    </div>
                  </div>

                  <div className="bg-slate-900 text-white rounded-2xl p-5 shadow-xs space-y-3 text-xs">
                    <div className="flex items-center justify-between">
                      <span className="text-[10px] font-bold uppercase text-blue-400">
                        Katalog Standar KGS
                      </span>
                      <span className="text-emerald-400 font-bold">{services.length} Layanan</span>
                    </div>
                    <h4 className="font-bold text-sm">Katalog Layanan Dapat Diedit & Dihapus</h4>
                    <p className="text-slate-300 leading-relaxed text-[11px]">
                      Kelola seluruh standar layanan resmi perusahaan, ubah rincian spesifikasi teknis, tambah layanan baru, atau hapus layanan langsung dari menu Katalog Layanan.
                    </p>
                    <button
                      onClick={() => setActiveTab('audit')}
                      className="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-center transition cursor-pointer"
                    >
                      Kelola Katalog Layanan &rarr;
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Tab 2: Services CRUD */}
          {activeTab === 'services' && (
            <AdminServices services={services} onUpdateServices={onUpdateServices} />
          )}

          {/* Tab 3: Categories CRUD */}
          {activeTab === 'categories' && (
            <AdminCategories
              categories={categories}
              onUpdateCategories={onUpdateCategories}
              serviceCounts={serviceCounts}
            />
          )}

          {/* Tab 4: Portfolio CRUD */}
          {activeTab === 'portfolio' && (
            <AdminPortfolio projects={projects} onUpdateProjects={onUpdateProjects} />
          )}

          {/* Tab 5: Clients CRUD */}
          {activeTab === 'clients' && (
            <AdminClients clients={clients} onUpdateClients={onUpdateClients} />
          )}

          {/* Tab 6: Articles CRUD */}
          {activeTab === 'articles' && (
            <AdminArticles
              articles={articles}
              categories={categories}
              onUpdateArticles={onUpdateArticles}
            />
          )}

          {/* Tab 7: Static Pages */}
          {activeTab === 'pages' && (
            <AdminPages
              pages={pages}
              onUpdatePages={onUpdatePages}
              menus={navigationMenus}
              onUpdateMenus={updated => onUpdateNavigationMenus?.(updated)}
            />
          )}

          {/* Tab 8: Inquiries / Leads */}
          {activeTab === 'inquiries' && (
            <AdminInquiries
              inquiries={inquiries}
              services={services}
              onUpdateInquiries={onUpdateInquiries}
            />
          )}

          {/* Tab 9: Users */}
          {activeTab === 'users' && <AdminUsers users={users} onUpdateUsers={onUpdateUsers} />}

          {/* Tab 10: Logs */}
          {activeTab === 'logs' && <AdminAuditLogs logs={logs} />}

          {/* Tab 11: Katalog Layanan (22 Standar) with Full Edit & Delete */}
          {activeTab === 'audit' && (
            <AdminAudit
              services={services}
              categories={categories}
              onUpdateServices={onUpdateServices}
            />
          )}

          {/* Tab 12: Testing Center */}
          {activeTab === 'testing' && <AdminTestingCenter />}

          {/* Tab 13: Settings with Company Identity, Logo Upload, WhatsApp CS & Google Maps */}
          {activeTab === 'settings' && (
            <div className="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 text-xs max-w-4xl">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
                <div>
                  <h2 className="text-base font-bold text-slate-900">
                    Pengaturan Identitas Perusahaan, Logo, WhatsApp CS & Peta Google Maps
                  </h2>
                  <p className="text-slate-500 text-xs mt-0.5">
                    Atur logo perusahaan, nomor WhatsApp CS untuk penawaran langsung, serta lokasi Google Maps pada halaman Kontak.
                  </p>
                </div>
                {settingsSavedMsg && (
                  <div className="flex items-center gap-1.5 px-3.5 py-2 bg-emerald-100 text-emerald-800 rounded-xl font-bold animate-in fade-in duration-200 shrink-0">
                    <Check className="w-4 h-4" />
                    <span>Pengaturan, Logo & Google Maps berhasil disimpan!</span>
                  </div>
                )}
              </div>

              <form onSubmit={handleSaveCompanySettings} className="space-y-6">
                {/* 1. Logo Upload Card */}
                <div className="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                  <label className="block font-bold text-slate-900 text-xs">
                    1. Logo Perusahaan (Upload Berkas Gambar Logo)
                  </label>
                  <div className="flex flex-col sm:flex-row items-center gap-5">
                    {/* Preview Box */}
                    <div className="w-36 h-20 bg-white rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center p-2 overflow-hidden shadow-xs shrink-0">
                      {logoPreview ? (
                        <img
                          src={logoPreview}
                          alt="Logo Perusahaan Preview"
                          className="max-h-full max-w-full object-contain"
                        />
                      ) : (
                        <div className="text-center text-slate-400">
                          <ImageIcon className="w-6 h-6 mx-auto mb-1 text-slate-300" />
                          <span className="text-[10px] block font-medium">Belum Ada Logo</span>
                        </div>
                      )}
                    </div>

                    {/* Upload Controls */}
                    <div className="space-y-2 flex-1 w-full">
                      <div className="flex flex-wrap items-center gap-2">
                        <label className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-xs cursor-pointer flex items-center gap-1.5 transition shadow-xs">
                          <Upload className="w-3.5 h-3.5" />
                          <span>Upload File Logo Baru</span>
                          <input
                            type="file"
                            accept="image/png, image/jpeg, image/webp, image/svg+xml"
                            onChange={handleLogoUpload}
                            className="hidden"
                          />
                        </label>

                        <button
                          type="button"
                          onClick={() => {
                            const svg =
                              'data:image/svg+xml;utf8,' +
                              encodeURIComponent(
                                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 64" fill="none"><rect width="64" height="64" rx="14" fill="#1d4ed8"/><text x="32" y="42" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="24" fill="#ffffff" text-anchor="middle">KGS</text><text x="76" y="28" font-family="system-ui, -apple-system, sans-serif" font-weight="800" font-size="16" fill="#0f172a">PT. KREATIF GLOBAL SOLUSINDO</text><text x="76" y="48" font-family="system-ui, -apple-system, sans-serif" font-weight="600" font-size="12" fill="#2563eb">System Integrator · Security · ISP</text></svg>'
                              );
                            setLogoPreview(svg);
                          }}
                          className="px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-lg font-semibold text-xs flex items-center gap-1 cursor-pointer transition"
                        >
                          <ImageIcon className="w-3.5 h-3.5 text-blue-600" />
                          <span>Gunakan Logo Resmi KGS (Preset Vector)</span>
                        </button>

                        {logoPreview && (
                          <button
                            type="button"
                            onClick={handleRemoveLogo}
                            className="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-xs flex items-center gap-1 cursor-pointer transition"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                            <span>Hapus Logo</span>
                          </button>
                        )}
                      </div>
                      <p className="text-[11px] text-slate-500">
                        Format yang didukung: PNG, JPG, SVG, WebP. Logo langsung tampil pada Header & Footer website publik.
                      </p>
                    </div>
                  </div>
                </div>

                {/* 2. Editable Company Info & Direct WhatsApp CS */}
                <div className="space-y-4">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block font-bold mb-1 text-slate-800">
                        Nama Legalitas Perusahaan *
                      </label>
                      <input
                        type="text"
                        required
                        value={companySettings.legalName}
                        onChange={e =>
                          setCompanySettings({ ...companySettings, legalName: e.target.value })
                        }
                        className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900 font-semibold"
                      />
                    </div>

                    <div>
                      <label className="block font-bold mb-1 text-slate-800">
                        Slogan / Tagline Resmi *
                      </label>
                      <input
                        type="text"
                        required
                        value={companySettings.slogan}
                        onChange={e =>
                          setCompanySettings({ ...companySettings, slogan: e.target.value })
                        }
                        className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                      <label className="block font-semibold mb-1 text-slate-700">
                        Telepon Kantor (Hunting)
                      </label>
                      <input
                        type="text"
                        value={companySettings.phonePalembang}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            phonePalembang: e.target.value
                          })
                        }
                        className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900"
                      />
                    </div>
                    <div>
                      <label className="block font-bold mb-1 text-emerald-700">
                        WhatsApp CS Resmi (Direct Penawaran) *
                      </label>
                      <input
                        type="text"
                        required
                        value={companySettings.whatsapp}
                        onChange={e =>
                          setCompanySettings({ ...companySettings, whatsapp: e.target.value })
                        }
                        placeholder="Contoh: 0811-782119 atau 62811782119"
                        className="w-full border-2 border-emerald-500 rounded-xl p-2.5 bg-emerald-50/40 text-slate-900 font-bold"
                      />
                      <span className="text-[10px] text-emerald-700 block mt-1">
                        ✓ Tombol "Kirim Permintaan Penawaran" langsung menuju ke nomor WA ini
                      </span>
                    </div>
                    <div>
                      <label className="block font-semibold mb-1 text-slate-700">
                        Email Resmi Perusahaan
                      </label>
                      <input
                        type="email"
                        value={companySettings.email}
                        onChange={e =>
                          setCompanySettings({ ...companySettings, email: e.target.value })
                        }
                        className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block font-semibold mb-1 text-slate-700">
                      Alamat Kantor
                    </label>
                    <input
                      type="text"
                      value={companySettings.addressPalembang}
                      onChange={e =>
                        setCompanySettings({
                          ...companySettings,
                          addressPalembang: e.target.value
                        })
                      }
                      className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900"
                    />
                  </div>
                  <div className="md:col-span-2">
                    <label className="block font-semibold mb-1 text-slate-700">
                      Label Brand di Footer (Default: KGSNET)
                    </label>
                    <input
                      type="text"
                      value={companySettings.footerBrandLabel || 'KGSNET'}
                      onChange={e =>
                        setCompanySettings({
                          ...companySettings,
                          footerBrandLabel: e.target.value
                        })
                      }
                      placeholder="KGSNET"
                      className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900 font-bold"
                    />
                  </div>
                </div>

                {/* 3. Google Maps Configuration Card */}
                <div className="p-5 rounded-2xl bg-blue-50/50 border border-blue-200 space-y-4">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                      <label className="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                        <Navigation className="w-4 h-4 text-blue-600" />
                        <span>
                          2. Pengaturan Peta Google Maps (Tampil di Kontak Website)
                        </span>
                      </label>
                      <p className="text-[11px] text-slate-600 mt-0.5">
                        Masukkan <strong>Link Google Maps</strong>, <strong>Nama Tempat / Alamat</strong>, atau <strong>Koordinat Latitude & Longitude</strong>.
                      </p>
                    </div>
                    <a
                      href={buildGoogleMapDirectLink(companySettings.googleMapInput)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="px-3 py-1.5 bg-white hover:bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-semibold inline-flex items-center gap-1 self-start"
                    >
                      <span>Tes Buka Link Maps</span>
                      <ExternalLink className="w-3 h-3" />
                    </a>
                  </div>

                  {/* Main Input for Link / Place Name / Coordinates */}
                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Input Link Google Maps / Nama Lokasi / Koordinat (Lat, Long) *
                    </label>
                    <input
                      type="text"
                      required
                      value={companySettings.googleMapInput}
                      onChange={e =>
                        setCompanySettings({
                          ...companySettings,
                          googleMapInput: e.target.value
                        })
                      }
                      placeholder="Contoh: https://maps.google.com/... atau Jln. Mayor Ruslan No.175C Palembang atau -2.9718, 104.7644"
                      className="w-full border border-blue-300 rounded-xl p-2.5 bg-white text-slate-900 font-mono text-xs focus:outline-none focus:border-blue-600"
                    />
                  </div>

                  {/* Quick Coordinate Input Helper & Presets */}
                  <div className="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-3.5 rounded-xl border border-slate-200">
                    <div className="md:col-span-4">
                      <label className="block text-[11px] font-semibold text-slate-600 mb-1">
                        Input Latitude (Garis Lintang)
                      </label>
                      <input
                        type="text"
                        value={latInput}
                        onChange={e => setLatInput(e.target.value)}
                        placeholder="-2.9718"
                        className="w-full border border-slate-300 rounded-lg px-3 py-1.5 font-mono text-xs"
                      />
                    </div>
                    <div className="md:col-span-4">
                      <label className="block text-[11px] font-semibold text-slate-600 mb-1">
                        Input Longitude (Garis Bujur)
                      </label>
                      <input
                        type="text"
                        value={lngInput}
                        onChange={e => setLngInput(e.target.value)}
                        placeholder="104.7644"
                        className="w-full border border-slate-300 rounded-lg px-3 py-1.5 font-mono text-xs"
                      />
                    </div>
                    <div className="md:col-span-4">
                      <button
                        type="button"
                        onClick={() => {
                          if (latInput.trim() && lngInput.trim()) {
                            setCompanySettings({
                              ...companySettings,
                              googleMapInput: `${latInput.trim()}, ${lngInput.trim()}`
                            });
                          }
                        }}
                        className="w-full py-2 px-3 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-lg text-xs cursor-pointer transition"
                      >
                        Gunakan Koordinat Lat/Long
                      </button>
                    </div>
                  </div>

                  {/* Quick Location Presets */}
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="text-[11px] text-slate-500 font-semibold">Preset Cepat:</span>
                    <button
                      type="button"
                      onClick={() =>
                        setCompanySettings({
                          ...companySettings,
                          googleMapInput: companySettings.addressPalembang
                        })
                      }
                      className="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer"
                    >
                      Alamat Kantor
                    </button>
                    <button
                      type="button"
                      onClick={() => {
                        setLatInput('-2.9718');
                        setLngInput('104.7644');
                        setCompanySettings({
                          ...companySettings,
                          googleMapInput: '-2.9718, 104.7644'
                        });
                      }}
                      className="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer"
                    >
                      Koordinat Kantor (-2.9718, 104.7644)
                    </button>
                  </div>

                  {/* Live Embedded Google Maps Preview */}
                  <div className="rounded-xl overflow-hidden border border-slate-300 bg-slate-100 h-56">
                    <iframe
                      title="Preview Peta Google Maps"
                      src={buildGoogleMapEmbedUrl(companySettings.googleMapInput)}
                      className="w-full h-full border-0"
                      loading="lazy"
                      referrerPolicy="no-referrer-when-downgrade"
                    />
                  </div>
                </div>

                {/* Visi & Misi */}
                <div className="space-y-4">
                  <div>
                    <label className="block font-semibold mb-1 text-slate-700">
                      Visi Perusahaan
                    </label>
                    <textarea
                      rows={2}
                      value={companySettings.vision}
                      onChange={e =>
                        setCompanySettings({ ...companySettings, vision: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900 leading-relaxed"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold mb-1 text-slate-700">
                      Misi Perusahaan
                    </label>
                    <textarea
                      rows={3}
                      value={companySettings.mission}
                      onChange={e =>
                        setCompanySettings({ ...companySettings, mission: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-xl p-2.5 bg-white text-slate-900 leading-relaxed"
                    />
                  </div>
                </div>

                {/* 4. Pengaturan Seluruh Footer Website Lengkap */}
                <div className="p-5 rounded-2xl bg-slate-900 text-slate-200 border border-slate-800 space-y-5">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                      <span className="px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold text-[10px] uppercase tracking-wider">
                        Pengaturan Footer Terpadu
                      </span>
                      <h4 className="font-bold text-white text-sm mt-1.5">
                        4. Pengaturan Seluruh Bagian Footer Website
                      </h4>
                      <p className="text-[11px] text-slate-400">
                        Logo Footer, Tentang Perusahaan, Alamat Kantor & Kontak, Navigasi Halaman, Informasi & Akses Cepat, Lisensi Sertifikasi & Keanggotaan, serta Hak Cipta (© 2026 KGSNET — Creative.co.id. All Rights Reserved.).
                      </p>
                    </div>
                    <button
                      type="button"
                      onClick={() => setActiveTab('navigation')}
                      className="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg text-[11px] shrink-0 cursor-pointer"
                    >
                      Kelola Link Navigasi Footer &rarr;
                    </button>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label className="block font-bold text-slate-200 mb-1">
                        Label Brand di Footer (Default: KGSNET)
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerBrandLabel || 'KGSNET'}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerBrandLabel: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5 font-extrabold"
                      />
                    </div>
                    <div className="md:col-span-2">
                      <label className="block font-bold text-slate-200 mb-1">
                        Logo Khusus Footer (Opsional — Kosongkan untuk memakai Logo Utama)
                      </label>
                      <div className="flex gap-2">
                        <input
                          type="text"
                          value={companySettings.footerLogo || ''}
                          onChange={e =>
                            setCompanySettings({
                              ...companySettings,
                              footerLogo: e.target.value
                            })
                          }
                          placeholder="Kosongkan untuk mengikuti Logo Utama..."
                          className="flex-1 border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5 font-mono"
                        />
                        <label className="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl cursor-pointer inline-flex items-center shrink-0">
                          <span>Upload Logo Footer</span>
                          <input
                            type="file"
                            accept="image/*"
                            className="hidden"
                            onChange={e => {
                              const file = e.target.files?.[0];
                              if (!file) return;
                              const reader = new FileReader();
                              reader.onload = ev => {
                                const res = ev.target?.result;
                                if (typeof res === 'string') {
                                  setCompanySettings({
                                    ...companySettings,
                                    footerLogo: res
                                  });
                                }
                              };
                              reader.readAsDataURL(file);
                            }}
                          />
                        </label>
                      </div>
                    </div>

                    <div className="md:col-span-3">
                      <label className="block font-bold text-slate-200 mb-1">
                        Tentang Perusahaan / Deskripsi Singkat di Footer (Kolom 1)
                      </label>
                      <textarea
                        rows={2}
                        value={companySettings.footerDescription || ''}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerDescription: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5"
                      />
                    </div>

                    <div>
                      <label className="block font-bold text-slate-200 mb-1">
                        Judul Kolom Alamat & Kontak (Kolom 2)
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerContactHeading || 'Alamat Kantor & Kontak'}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerContactHeading: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5"
                      />
                    </div>
                    <div>
                      <label className="block font-bold text-slate-200 mb-1">
                        Judul Kolom Navigasi Halaman (Kolom 3)
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerNavHeading || 'Navigasi Halaman'}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerNavHeading: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5"
                      />
                    </div>
                    <div>
                      <label className="block font-bold text-slate-200 mb-1">
                        Judul Kolom Informasi & Akses Cepat (Kolom 4)
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerQuickHeading || 'Informasi & Akses Cepat'}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerQuickHeading: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5"
                      />
                    </div>

                    <div>
                      <label className="block font-bold text-emerald-400 mb-1">
                        Judul Bagian Lisensi & Sertifikasi Footer
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan'}
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerCertHeading: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5"
                      />
                    </div>
                    <div className="md:col-span-2">
                      <label className="block font-bold text-emerald-400 mb-1">
                        Badge Lisensi, Sertifikasi & Keanggotaan (Pisahkan dengan |)
                      </label>
                      <input
                        type="text"
                        value={
                          companySettings.footerCertBadges ||
                          'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified'
                        }
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            footerCertBadges: e.target.value
                          })
                        }
                        className="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5 font-semibold"
                      />
                    </div>

                    <div className="md:col-span-3">
                      <label className="block font-bold text-amber-300 mb-1">
                        Teks Hak Cipta Lengkap (© 2026 KGSNET — Creative.co.id. All Rights Reserved.)
                      </label>
                      <input
                        type="text"
                        value={
                          companySettings.copyrightText ||
                          '© 2026 KGSNET — Creative.co.id. All Rights Reserved.'
                        }
                        onChange={e =>
                          setCompanySettings({
                            ...companySettings,
                            copyrightText: e.target.value
                          })
                        }
                        className="w-full border border-amber-500/50 bg-slate-800 text-white rounded-xl p-2.5 font-bold"
                      />
                    </div>
                  </div>
                </div>

                <div className="pt-4 border-t flex justify-end">
                  <button
                    type="submit"
                    className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2"
                  >
                    <Check className="w-4 h-4" />
                    <span>Simpan Pengaturan, Logo, Maps & Seluruh Footer</span>
                  </button>
                </div>
              </form>
            </div>
          )}

          {/* Tab Navigation Manager: Pengaturan Navigasi Header & Footer */}
          {activeTab === 'navigation' && (
            <AdminNavigation
              menus={navigationMenus}
              pages={pages}
              onUpdateMenus={updated => onUpdateNavigationMenus?.(updated)}
              onUpdatePages={onUpdatePages}
            />
          )}

          {/* Tab Theme & Homepage Slider Manager */}
          {activeTab === 'theme_slider' && (
            <AdminThemeSlider
              themeConfig={themeConfig}
              onUpdateThemeConfig={updated => onUpdateThemeConfig?.(updated)}
              sliderConfig={sliderConfig}
              onUpdateSliderConfig={updated => onUpdateSliderConfig?.(updated)}
              onOpenLiveEditor={() => {
                if (onOpenLiveEditor) onOpenLiveEditor();
                else onExitAdmin();
              }}
            />
          )}

          {/* Tab Website Sections Manager: All Sections of All Pages */}
          {activeTab === 'website_sections' && (
            <AdminWebsiteSections
              initialSubTab={websiteSectionSubTab}
              companySettings={companySettings}
              onUpdateCompanySettings={updated => {
                setCompanySettings(updated);
                localStorage.setItem('kgs_company_settings', JSON.stringify(updated));
                onUpdateCompanySettings?.(updated);
              }}
              timeline={timeline}
              onUpdateTimeline={items => onUpdateTimeline?.(items)}
              certifications={certifications}
              onUpdateCertifications={items => onUpdateCertifications?.(items)}
              partners={partners}
              onUpdatePartners={items => onUpdatePartners?.(items)}
              pricingPlans={pricingPlans}
              onUpdatePricingPlans={items => onUpdatePricingPlans?.(items)}
              faqs={faqs}
              onUpdateFaqs={items => onUpdateFaqs?.(items)}
              pages={pages}
              onUpdatePages={onUpdatePages}
              onNavigateAdminTab={tab => handleSelectTab(tab as any)}
            />
          )}
        </main>
      </div>
    </div>
  );
};
