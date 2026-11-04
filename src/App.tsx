import React, { useState, useEffect, useMemo } from 'react';
import {
  SERVICES_DATA,
  SERVICE_CATEGORIES,
  VERIFIED_CLIENTS,
  PROJECT_DOCUMENTARY,
  INITIAL_INQUIRIES,
  INITIAL_ARTICLES,
  INITIAL_PAGES,
  INITIAL_ADMIN_USERS,
  INITIAL_AUDIT_LOGS,
  INITIAL_NAVIGATION_MENUS,
  ServiceItem,
  ServiceCategory,
  ClientItem,
  ProjectPhoto,
  InquiryMessage,
  ArticleItem,
  StaticPageItem,
  AdminUserItem,
  AuditLogItem,
  CompanySettings,
  NavigationMenuItem,
  DEFAULT_COMPANY_SETTINGS,
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
  SlideshowCategoryItem,
  INITIAL_SLIDESHOW_CATEGORIES,
  PromoItem,
  INITIAL_PROMOS,
  TestimonialItem,
  INITIAL_TESTIMONIALS
} from './data/companyData';
import {
  ColorMode,
  getStoredColorMode,
  setStoredColorMode,
  applyColorModeToDocument,
  syncLiveStateToServer,
  ExportStatePayload
} from './utils/exportSync';
import { Navbar, normalizeMenuItemUrl } from './components/Navbar';
import { Hero } from './components/Hero';
import { AboutSection } from './components/AboutSection';
import { ServicesSection } from './components/ServicesSection';
import { SlideshowSection } from './components/SlideshowSection';
import { PricingSection } from './components/PricingSection';
import { FaqSection } from './components/FaqSection';
import { PortfolioSection } from './components/PortfolioSection';
import {
  PromoSection,
  KemitraanSection,
  TestimoniSection
} from './components/PromoPartnersTestimonials';
import { ArticlesSection } from './components/ArticlesSection';
import { ContactSection } from './components/ContactSection';
import { Footer } from './components/Footer';
import { PdfAuditModal } from './components/PdfAuditModal';
import { PhpExportModal } from './components/PhpExportModal';
import { StaticPageModal } from './components/StaticPageModal';
import { LiveWebsiteEditorBar } from './components/LiveWebsiteEditorBar';
import { AdminDashboard } from './components/admin/AdminDashboard';

export default function App() {
  // Dark / Light Theme State
  const [colorMode, setColorMode] = useState<ColorMode>(() => getStoredColorMode());
  const [isDarkMode, setIsDarkMode] = useState<boolean>(() => applyColorModeToDocument(getStoredColorMode()));

  useEffect(() => {
    const resolvedDark = applyColorModeToDocument(colorMode);
    setIsDarkMode(resolvedDark);
  }, [colorMode]);

  const handleChangeColorMode = (nextMode: ColorMode) => {
    setColorMode(nextMode);
    const resolvedDark = setStoredColorMode(nextMode);
    setIsDarkMode(resolvedDark);
  };

  // Load initial state with localStorage persistence & content version migration
  const [services, setServices] = useState<ServiceItem[]>(() => {
    const saved = localStorage.getItem('kgs_services');
    if (saved) {
      try {
        const parsed: ServiceItem[] = JSON.parse(saved);
        const hasSevenBidangServices =
          parsed.some(s => s.categoryId === 'cat_networking') &&
          parsed.some(s => s.categoryId === 'cat_radio') &&
          parsed.some(s => s.categoryId === 'cat_pabx') &&
          parsed.some(s => s.categoryId === 'cat_access');
        if (!hasSevenBidangServices) return SERVICES_DATA;
        return parsed;
      } catch {
        return SERVICES_DATA;
      }
    }
    return SERVICES_DATA;
  });

  const [categories, setCategories] = useState<ServiceCategory[]>(() => {
    const saved = localStorage.getItem('kgs_categories');
    if (saved) {
      try {
        const parsed: ServiceCategory[] = JSON.parse(saved);
        const hasSevenBidang =
          parsed.length === 7 &&
          parsed.some(c => c.id === 'cat_networking') &&
          parsed.some(c => c.id === 'cat_radio') &&
          parsed.some(c => c.id === 'cat_pabx') &&
          parsed.some(c => c.id === 'cat_access');
        if (!hasSevenBidang) return SERVICE_CATEGORIES;
        return parsed;
      } catch {
        return SERVICE_CATEGORIES;
      }
    }
    return SERVICE_CATEGORIES;
  });

  const [clients, setClients] = useState<ClientItem[]>(() => {
    const saved = localStorage.getItem('kgs_clients');
    return saved ? JSON.parse(saved) : VERIFIED_CLIENTS;
  });

  const [projects, setProjects] = useState<ProjectPhoto[]>(() => {
    const saved = localStorage.getItem('kgs_projects');
    if (saved) {
      try {
        const parsed: ProjectPhoto[] = JSON.parse(saved);
        const hasSevenBidangProjects =
          parsed.some(p => p.category === 'Data Networking') &&
          parsed.some(p => p.category.includes('Radio Komunikasi')) &&
          parsed.some(p => p.category === 'Access Door');
        if (!hasSevenBidangProjects) return PROJECT_DOCUMENTARY;
        const defaultMap = new Map(PROJECT_DOCUMENTARY.map(d => [d.id, d]));
        return parsed.map(p => {
          const def = defaultMap.get(p.id);
          if (!p.images || p.images.length === 0) {
            return {
              ...p,
              imageUrl: p.imageUrl || def?.imageUrl,
              images: def?.images || (p.imageUrl ? [{
                id: `${p.id}-img-1`,
                imageUrl: p.imageUrl,
                caption: p.title,
                sortOrder: 1,
                isPrimary: true
              }] : [])
            };
          }
          return p;
        });
      } catch {
        return PROJECT_DOCUMENTARY;
      }
    }
    return PROJECT_DOCUMENTARY;
  });

  const [articles, setArticles] = useState<ArticleItem[]>(() => {
    const saved = localStorage.getItem('kgs_articles');
    return saved ? JSON.parse(saved) : INITIAL_ARTICLES;
  });

  const [pages, setPages] = useState<StaticPageItem[]>(() => {
    const saved = localStorage.getItem('kgs_pages');
    if (saved) {
      try {
        const parsed: StaticPageItem[] = JSON.parse(saved);
        const hasUpdatedProfile = parsed.some(
          p => p.slug === 'tentang-kami' && p.summary.includes('Creative Com, dengan badan usaha CV. Creative berkantor di Palembang')
        );
        if (!hasUpdatedProfile) return INITIAL_PAGES;
        const normalized = parsed.map(p => ({
          ...p,
          content: p.content ? p.content.replace(/info@kgs\.net\.id/g, 'info@creative.co.id') : p.content
        }));
        const existingSlugs = new Set(normalized.map(p => p.slug));
        for (const defPage of INITIAL_PAGES) {
          if (!existingSlugs.has(defPage.slug)) {
            normalized.push(defPage);
          }
        }
        return normalized;
      } catch {
        return INITIAL_PAGES;
      }
    }
    return INITIAL_PAGES;
  });

  const [users, setUsers] = useState<AdminUserItem[]>(() => {
    const saved = localStorage.getItem('kgs_users');
    return saved ? JSON.parse(saved) : INITIAL_ADMIN_USERS;
  });

  const [logs, setLogs] = useState<AuditLogItem[]>(() => {
    const saved = localStorage.getItem('kgs_logs');
    return saved ? JSON.parse(saved) : INITIAL_AUDIT_LOGS;
  });

  const [inquiries, setInquiries] = useState<InquiryMessage[]>(() => {
    const saved = localStorage.getItem('kgs_inquiries');
    return saved ? JSON.parse(saved) : INITIAL_INQUIRIES;
  });

  const [companySettings, setCompanySettings] = useState<CompanySettings>(() => {
    const saved = localStorage.getItem('kgs_company_settings');
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        const merged: CompanySettings = { ...DEFAULT_COMPANY_SETTINGS, ...parsed };
        if (
          !merged.legalName ||
          merged.legalName.toLowerCase().includes('creatve') ||
          merged.legalName.toLowerCase() === 'creative.co.id'
        ) {
          merged.legalName = 'PT. KREATIF GLOBAL SOLUSINDO';
        }
        if (
          !merged.footerBrandLabel ||
          merged.footerBrandLabel.toUpperCase() === 'PT. KGS' ||
          merged.footerBrandLabel.toUpperCase() === 'KGS'
        ) {
          merged.footerBrandLabel = 'KGSNET';
        }
        if (!merged.slogan || merged.slogan === 'Your Solution & Partner') {
          merged.slogan = 'System Integrator, Security Solution & Internet Provider';
        }
        if (!merged.logoBgColor) {
          merged.logoBgColor = DEFAULT_COMPANY_SETTINGS.logoBgColor || '#2563eb';
        }
        if (!merged.logoTextColor) {
          merged.logoTextColor = DEFAULT_COMPANY_SETTINGS.logoTextColor || '#ffffff';
        }
        if (!merged.companyNameColor) {
          merged.companyNameColor = DEFAULT_COMPANY_SETTINGS.companyNameColor || '#0f172a';
        }
        if (!merged.sloganColor) {
          merged.sloganColor = DEFAULT_COMPANY_SETTINGS.sloganColor || '#2563eb';
        }
        // Always ensure Vision, Mission, Culture, Commitment & Hero CTA reflect the latest official text
        if (!merged.vision || !merged.vision.includes('semangat kewirausahaan yang tinggi')) {
          merged.vision = DEFAULT_COMPANY_SETTINGS.vision;
          merged.mission = DEFAULT_COMPANY_SETTINGS.mission;
        }
        if (!merged.culture || !merged.culture.includes('Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan')) {
          merged.culture = DEFAULT_COMPANY_SETTINGS.culture;
        }
        if (!merged.commitment) {
          merged.commitment = DEFAULT_COMPANY_SETTINGS.commitment;
        }
        if (merged.heroCtaTertiary === '5 Segmen Layanan →') {
          merged.heroCtaTertiary = 'Product & Service →';
        }
        if (!merged.email || merged.email === 'info@kgs.net.id') {
          merged.email = 'info@creative.co.id';
        }
        if (merged.phonePalembang) {
          merged.phonePalembang = merged.phonePalembang.replace(/\s*\(Hunting\)/gi, '').trim();
        }
        delete merged.addressJakarta;
        return merged;
      } catch {
        return DEFAULT_COMPANY_SETTINGS;
      }
    }
    return DEFAULT_COMPANY_SETTINGS;
  });

  const [navigationMenus, setNavigationMenus] = useState<NavigationMenuItem[]>(() => {
    const saved = localStorage.getItem('kgs_navigation_menus');
    if (saved) {
      try {
        const parsed: NavigationMenuItem[] = JSON.parse(saved);
        const headerItems = parsed
          .filter(m => m.location !== 'footer')
          .sort((a, b) => a.sortOrder - b.sortOrder);
        // Check if header already matches the 7-item order: Beranda, Produk & Layanan, Promo, Client / Pelanggan, Kemitraan, Testimoni, Hubungi Kami
        const hasUpdated7HeaderMenus =
          headerItems.some(m => m.title === 'Produk & Layanan') &&
          headerItems.some(m => m.title === 'Promo') &&
          headerItems.some(m => m.title === 'Client / Pelanggan') &&
          headerItems.some(m => m.title === 'Kemitraan') &&
          headerItems.some(m => m.title === 'Testimoni') &&
          headerItems.some(m => m.title === 'Hubungi Kami');

        if (!hasUpdated7HeaderMenus) {
          const footerItems = parsed.filter(m => m.location === 'footer');
          const defaultHeaderItems = INITIAL_NAVIGATION_MENUS.filter(m => m.location === 'header');
          const defaultFooterItems = INITIAL_NAVIGATION_MENUS.filter(m => m.location === 'footer');
          return [
            ...defaultHeaderItems,
            ...(footerItems.length > 0 ? footerItems : defaultFooterItems)
          ];
        }

        return parsed.map(rawItem => {
          const cleanTitle = (rawItem.title || '').trim();
          const loc = rawItem.location === 'footer' ? 'footer' : 'header';
          return {
            ...rawItem,
            title: cleanTitle,
            url: normalizeMenuItemUrl(cleanTitle, rawItem.url),
            location: loc
          };
        });
      } catch {
        return INITIAL_NAVIGATION_MENUS;
      }
    }
    return INITIAL_NAVIGATION_MENUS;
  });

  const [themeConfig, setThemeConfig] = useState<WebsiteThemeConfig>(() => {
    const saved = localStorage.getItem('kgs_theme_config');
    return saved ? { ...DEFAULT_WEBSITE_THEME, ...JSON.parse(saved) } : DEFAULT_WEBSITE_THEME;
  });

  const [sliderConfig, setSliderConfig] = useState<HeroSliderConfig>(() => {
    const saved = localStorage.getItem('kgs_slider_config');
    if (saved) {
      try {
        const parsed: HeroSliderConfig = JSON.parse(saved);
        const hasLegacyThreeSlides =
          !Array.isArray(parsed.slides) ||
          parsed.slides.length <= 3 ||
          parsed.slides.some(
            s => s.id === 'slide-1' || s.title === 'Backbone Fiber Optic & Data Center KGSNet'
          );
        if (hasLegacyThreeSlides) {
          return {
            ...INITIAL_HERO_SLIDER_CONFIG,
            ...parsed,
            slides: INITIAL_HERO_SLIDER_CONFIG.slides
          };
        }
        const normalizedSlides = parsed.slides.map(s => ({
          ...s,
          imageUrl: (s.imageUrl || '').replace(
            'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
            'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80'
          )
        }));
        return { ...INITIAL_HERO_SLIDER_CONFIG, ...parsed, slides: normalizedSlides };
      } catch {
        return INITIAL_HERO_SLIDER_CONFIG;
      }
    }
    return INITIAL_HERO_SLIDER_CONFIG;
  });

  const [inlineOverrides, setInlineOverrides] = useState<Record<string, string>>(() => {
    const saved = localStorage.getItem('kgs_inline_overrides');
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        delete parsed.txt_SPAN_Creatve_co_id;
        return parsed;
      } catch {
        return {};
      }
    }
    return {};
  });

  const [timeline, setTimeline] = useState<TimelineMilestoneItem[]>(() => {
    const saved = localStorage.getItem('kgs_timeline');
    return saved ? JSON.parse(saved) : INITIAL_TIMELINE_MILESTONES;
  });

  const [certifications, setCertifications] = useState<CertificationItem[]>(() => {
    const saved = localStorage.getItem('kgs_certifications');
    return saved ? JSON.parse(saved) : VERIFIED_CERTIFICATIONS;
  });

  const [partners, setPartners] = useState<PartnerItem[]>(() => {
    const saved = localStorage.getItem('kgs_partners');
    return saved ? JSON.parse(saved) : VERIFIED_PARTNERS;
  });

  const [promos, setPromos] = useState<PromoItem[]>(() => {
    const saved = localStorage.getItem('kgs_promos');
    return saved ? JSON.parse(saved) : INITIAL_PROMOS;
  });

  const [testimonials, setTestimonials] = useState<TestimonialItem[]>(() => {
    const saved = localStorage.getItem('kgs_testimonials');
    return saved ? JSON.parse(saved) : INITIAL_TESTIMONIALS;
  });

  const [pricingPlans, setPricingPlans] = useState<PricingPlanItem[]>(() => {
    const saved = localStorage.getItem('kgs_pricing_plans');
    if (saved) {
      try {
        const parsed: PricingPlanItem[] = JSON.parse(saved);
        const has3Segments = parsed.some(p => p.id === 'plan-isp-dedicated' || p.name === 'Internet Dedicated');
        if (!has3Segments) return B2B_PRICING_PLANS;
        return parsed;
      } catch {
        return B2B_PRICING_PLANS;
      }
    }
    return B2B_PRICING_PLANS;
  });

  const [faqs, setFaqs] = useState<FaqItem[]>(() => {
    const saved = localStorage.getItem('kgs_faqs');
    if (saved) {
      try {
        const parsed: FaqItem[] = JSON.parse(saved);
        const hasUpdatedFaq = parsed.some(f => f.answer.includes('674/TEL.02.02/2020'));
        if (!hasUpdatedFaq) return FAQ_DATA;
        return parsed;
      } catch {
        return FAQ_DATA;
      }
    }
    return FAQ_DATA;
  });

  const [slideshowCategories, setSlideshowCategories] = useState<SlideshowCategoryItem[]>(() => {
    const saved = localStorage.getItem('kgs_slideshow_categories');
    if (saved) {
      try {
        const parsed: SlideshowCategoryItem[] = JSON.parse(saved);
        const hasAll8AndOrder =
          Array.isArray(parsed) &&
          parsed.length >= 8 &&
          parsed[0]?.slug === 'fiber-optik' &&
          parsed.some(c => c.slug === 'cctv');
        if (hasAll8AndOrder) {
          return parsed.map(c => ({
            ...c,
            primaryImageUrl: (c.primaryImageUrl || '').replace(
              'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
              'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80'
            ),
            images: (c.images || []).map(img => ({
              ...img,
              imageUrl: (img.imageUrl || '').replace(
                'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
                'photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80'
              )
            }))
          }));
        }
      } catch {
        return INITIAL_SLIDESHOW_CATEGORIES;
      }
    }
    return INITIAL_SLIDESHOW_CATEGORIES;
  });

  const [selectedSlideshowSlug, setSelectedSlideshowSlug] = useState<string | null>(null);

  const [isAdminAuthenticated, setIsAdminAuthenticated] = useState<boolean>(() => {
    return localStorage.getItem('kgs_admin_auth') === 'true';
  });
  const [isLiveEditMode, setIsLiveEditMode] = useState<boolean>(false);
  const effectiveLiveEditMode = isAdminAuthenticated && isLiveEditMode;
  const [adminInitialTab, setAdminInitialTab] = useState<string>('overview');

  const [currentView, setCurrentView] = useState<'public' | 'admin'>(() => {
    return window.location.hash === '#admin' ? 'admin' : 'public';
  });
  const [activePageRoute, setActivePageRoute] = useState<string>('beranda');
  const [isAuditModalOpen, setIsAuditModalOpen] = useState(false);
  const [isPhpExportModalOpen, setIsPhpExportModalOpen] = useState(false);
  const [selectedStaticPage, setSelectedStaticPage] = useState<StaticPageItem | null>(null);
  const [isFaqPageOpen, setIsFaqPageOpen] = useState(false);
  const [prefilledService, setPrefilledService] = useState<string>('');
  const [companyLogo, setCompanyLogo] = useState<string>(() => {
    const savedLogo = localStorage.getItem('kgs_custom_logo') || '';
    if (
      savedLogo.includes('image_8b7e60bd') ||
      savedLogo.includes('viewBox%3D%220%200%20280%2064%22')
    ) {
      localStorage.removeItem('kgs_custom_logo');
      return '';
    }
    return savedLogo;
  });

  // Persist state changes
  useEffect(() => {
    localStorage.setItem('kgs_services', JSON.stringify(services));
  }, [services]);

  useEffect(() => {
    localStorage.setItem('kgs_categories', JSON.stringify(categories));
  }, [categories]);

  useEffect(() => {
    localStorage.setItem('kgs_clients', JSON.stringify(clients));
  }, [clients]);

  useEffect(() => {
    localStorage.setItem('kgs_projects', JSON.stringify(projects));
  }, [projects]);

  useEffect(() => {
    localStorage.setItem('kgs_articles', JSON.stringify(articles));
  }, [articles]);

  useEffect(() => {
    localStorage.setItem('kgs_pages', JSON.stringify(pages));
  }, [pages]);

  useEffect(() => {
    localStorage.setItem('kgs_users', JSON.stringify(users));
  }, [users]);

  useEffect(() => {
    localStorage.setItem('kgs_logs', JSON.stringify(logs));
  }, [logs]);

  useEffect(() => {
    localStorage.setItem('kgs_inquiries', JSON.stringify(inquiries));
  }, [inquiries]);

  useEffect(() => {
    localStorage.setItem('kgs_company_settings', JSON.stringify(companySettings));
  }, [companySettings]);

  useEffect(() => {
    localStorage.setItem('kgs_navigation_menus', JSON.stringify(navigationMenus));
  }, [navigationMenus]);

  useEffect(() => {
    localStorage.setItem('kgs_theme_config', JSON.stringify(themeConfig));
  }, [themeConfig]);

  useEffect(() => {
    localStorage.setItem('kgs_slider_config', JSON.stringify(sliderConfig));
  }, [sliderConfig]);

  useEffect(() => {
    localStorage.setItem('kgs_inline_overrides', JSON.stringify(inlineOverrides));
  }, [inlineOverrides]);

  useEffect(() => {
    localStorage.setItem('kgs_timeline', JSON.stringify(timeline));
  }, [timeline]);

  useEffect(() => {
    localStorage.setItem('kgs_certifications', JSON.stringify(certifications));
  }, [certifications]);

  useEffect(() => {
    localStorage.setItem('kgs_partners', JSON.stringify(partners));
  }, [partners]);

  useEffect(() => {
    localStorage.setItem('kgs_promos', JSON.stringify(promos));
  }, [promos]);

  useEffect(() => {
    localStorage.setItem('kgs_testimonials', JSON.stringify(testimonials));
  }, [testimonials]);

  useEffect(() => {
    localStorage.setItem('kgs_pricing_plans', JSON.stringify(pricingPlans));
  }, [pricingPlans]);

  useEffect(() => {
    localStorage.setItem('kgs_faqs', JSON.stringify(faqs));
  }, [faqs]);

  useEffect(() => {
    localStorage.setItem('kgs_slideshow_categories', JSON.stringify(slideshowCategories));
  }, [slideshowCategories]);

  // Unified Export State Payload for Live Edit -> Export/Import synchronization
  const exportState: ExportStatePayload = useMemo(
    () => ({
      companySettings,
      pages,
      services,
      categories,
      pricingPlans,
      faqs,
      projects,
      clients,
      articles,
      navigationMenus,
      themeConfig,
      sliderConfig,
      timeline,
      certifications,
      partners,
      inlineOverrides,
      companyLogo
    }),
    [
      companySettings,
      pages,
      services,
      categories,
      pricingPlans,
      faqs,
      projects,
      clients,
      articles,
      navigationMenus,
      themeConfig,
      sliderConfig,
      timeline,
      certifications,
      partners,
      inlineOverrides,
      companyLogo
    ]
  );

  // Sync live state to backend file config/live-preview-state.json automatically
  useEffect(() => {
    const timer = setTimeout(() => {
      syncLiveStateToServer(exportState);
    }, 350);
    return () => clearTimeout(timer);
  }, [exportState]);

  useEffect(() => {
    const handleHashChange = () => {
      if (window.location.hash === '#admin') {
        setCurrentView('admin');
      }
    };
    window.addEventListener('hashchange', handleHashChange);
    return () => window.removeEventListener('hashchange', handleHashChange);
  }, []);

  const handleRequestQuote = (serviceName?: string) => {
    if (serviceName) setPrefilledService(serviceName);
    setIsFaqPageOpen(false);
    setSelectedStaticPage(null);
    setActivePageRoute('kontak');
    setTimeout(() => {
      const contactElem = document.getElementById('konsultasi');
      if (contactElem) {
        contactElem.scrollIntoView({ behavior: 'smooth' });
      } else {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }, 60);
  };

  const handleAddInquiry = (newInquiry: Omit<InquiryMessage, 'id' | 'createdAt' | 'status'>) => {
    const item: InquiryMessage = {
      ...newInquiry,
      id: `inq-${Date.now()}`,
      createdAt: new Date().toISOString().replace('T', ' ').slice(0, 16),
      status: 'Baru',
      notes: []
    };
    setInquiries([item, ...inquiries]);

    const newLog: AuditLogItem = {
      id: `log-${Date.now()}`,
      username: 'Public Form',
      action: 'Permintaan Penawaran Baru',
      details: `${item.name} (${item.company || 'Perorangan'}) meminta penawaran`,
      ipAddress: '127.0.0.1',
      createdAt: new Date().toISOString().replace('T', ' ').slice(0, 16)
    };
    setLogs([newLog, ...logs]);
  };

  const handleAddTestimonial = (newTesti: Omit<TestimonialItem, 'id'>) => {
    const item: TestimonialItem = {
      ...newTesti,
      id: `testi-${Date.now()}`
    };
    setTestimonials(prev => [item, ...prev]);

    const newLog: AuditLogItem = {
      id: `log-${Date.now()}`,
      username: item.personName || 'Pelanggan',
      action: 'Testimoni Pelanggan Baru',
      details: `${item.personName} (${item.clientName}) mengirim testimoni layanan ${item.serviceUsed}`,
      ipAddress: '127.0.0.1',
      createdAt: new Date().toISOString().replace('T', ' ').slice(0, 16)
    };
    setLogs(prev => [newLog, ...prev]);
  };

  const getPageBySlug = (slug: string): StaticPageItem | undefined => {
    return pages.find(p => p.slug === slug) || INITIAL_PAGES.find(p => p.slug === slug);
  };

  const updatePageBySlug = (
    slug: string,
    updated: { title: string; summary: string; content: string }
  ) => {
    setPages(prev =>
      prev.map(p =>
        p.slug === slug
          ? {
              ...p,
              title: updated.title,
              summary: updated.summary,
              content: updated.content,
              updatedAt: new Date().toISOString().slice(0, 10)
            }
          : p
      )
    );
  };

  const handleOpenPageBySlug = (slug: string) => {
    const page = pages.find(p => p.slug === slug) || INITIAL_PAGES.find(p => p.slug === slug);
    if (page) {
      setSelectedStaticPage(page);
    }
  };

  const handleResetSubViews = () => {
    setSelectedStaticPage(null);
    setSelectedSlideshowSlug(null);
    setIsFaqPageOpen(false);
    setActivePageRoute('beranda');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleOpenFaqPage = () => {
    setSelectedStaticPage(null);
    setSelectedSlideshowSlug(null);
    setIsFaqPageOpen(true);
    setActivePageRoute('faq');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleNavigateRoute = (route: string, anchorId?: string) => {
    setSelectedStaticPage(null);
    setSelectedSlideshowSlug(null);
    if (route === 'faq') {
      setIsFaqPageOpen(true);
      setActivePageRoute('faq');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }
    setIsFaqPageOpen(false);
    setActivePageRoute(route || 'beranda');
    setTimeout(() => {
      if (anchorId) {
        const el = document.getElementById(anchorId);
        if (el) {
          el.scrollIntoView({ behavior: 'smooth' });
          return;
        }
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 50);
  };

  const currentRouteKey = isFaqPageOpen ? 'faq' : activePageRoute;
  const routeBreadcrumbLabels: Record<string, { label: string; phpFile: string }> = {
    'tentang-kami': { label: 'Tentang Kami (Profil, Visi & Misi)', phpFile: 'tentang.php' },
    layanan: { label: 'Produk & Layanan — Solusi Terintegrasi', phpFile: 'layanan.php' },
    promo: { label: 'Promo & Penawaran Spesial Layanan', phpFile: 'promo.php' },
    klien: { label: 'Client / Pelanggan & Portofolio Proyek', phpFile: 'klien.php' },
    portofolio: { label: 'Client / Pelanggan & Portofolio Proyek', phpFile: 'portofolio.php' },
    kemitraan: { label: 'Kemitraan & Partner Teknologi Resmi', phpFile: 'kemitraan.php' },
    testimoni: { label: 'Testimoni & Kepuasan Pelanggan', phpFile: 'testimoni.php' },
    harga: { label: 'Harga & 3 Segmen Paket Internet ISP', phpFile: 'harga.php' },
    faq: { label: 'Pertanyaan Umum & Jawaban Teknis (FAQ)', phpFile: 'faq.php' },
    artikel: { label: 'Artikel Edukasi & Wawasan Teknis', phpFile: 'blog.php' },
    kontak: { label: 'Hubungi Kami, Konsultasi & Lokasi Peta', phpFile: 'kontak.php' }
  };

  return (
    <div
      style={
        {
          '--theme-primary': themeConfig.primaryColor,
          '--theme-primary-hover': themeConfig.primaryHover,
          '--theme-primary-light': themeConfig.primaryLightBg,
          '--theme-hero-bg': themeConfig.heroBgColor
        } as React.CSSProperties
      }
      className="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans selection:bg-blue-600 selection:text-white transition-colors duration-200"
    >
      {/* Dynamic Website Theme Overrides */}
      <style>{`
        .website-public-root .bg-blue-600 { background-color: ${themeConfig.primaryColor} !important; }
        .website-public-root .hover\\:bg-blue-700:hover,
        .website-public-root .hover\\:bg-blue-500:hover { background-color: ${themeConfig.primaryHover} !important; }
        .website-public-root .text-blue-600,
        .website-public-root .text-blue-700 { color: ${themeConfig.primaryColor} !important; }
        html.dark .website-public-root .text-blue-600,
        html.dark .website-public-root .text-blue-700 { color: #60a5fa !important; }
        .website-public-root .border-blue-600,
        .website-public-root .border-blue-500 { border-color: ${themeConfig.primaryColor} !important; }
        .website-public-root .bg-blue-50 { background-color: ${themeConfig.primaryLightBg} !important; }
        html.dark .website-public-root .bg-blue-50 { background-color: rgba(30, 58, 138, 0.25) !important; }
      `}</style>

      {currentView === 'admin' ? (
        <AdminDashboard
          services={services}
          categories={categories}
          clients={clients}
          projects={projects}
          articles={articles}
          pages={pages}
          users={users}
          logs={logs}
          inquiries={inquiries}
          onUpdateServices={setServices}
          onUpdateCategories={setCategories}
          onUpdateClients={setClients}
          onUpdateProjects={setProjects}
          onUpdateArticles={setArticles}
          onUpdatePages={setPages}
          onUpdateUsers={setUsers}
          onUpdateInquiries={setInquiries}
          onExitAdmin={() => {
            if (window.location.hash === '#admin') {
              window.history.replaceState(null, '', window.location.pathname);
            }
            setCurrentView('public');
          }}
          onOpenLiveEditor={() => {
            if (window.location.hash === '#admin') {
              window.history.replaceState(null, '', window.location.pathname);
            }
            if (localStorage.getItem('kgs_admin_auth') === 'true') {
              setIsAdminAuthenticated(true);
              setIsLiveEditMode(true);
            }
            setCurrentView('public');
          }}
          onAuthChange={auth => {
            setIsAdminAuthenticated(auth);
            if (!auth) {
              setIsLiveEditMode(false);
            }
          }}
          onOpenPhpExport={() => setIsPhpExportModalOpen(true)}
          companyLogo={companyLogo}
          onUpdateCompanyLogo={setCompanyLogo}
          companySettings={companySettings}
          onUpdateCompanySettings={setCompanySettings}
          navigationMenus={navigationMenus}
          onUpdateNavigationMenus={setNavigationMenus}
          themeConfig={themeConfig}
          onUpdateThemeConfig={setThemeConfig}
          sliderConfig={sliderConfig}
          onUpdateSliderConfig={setSliderConfig}
          timeline={timeline}
          onUpdateTimeline={setTimeline}
          certifications={certifications}
          onUpdateCertifications={setCertifications}
          partners={partners}
          onUpdatePartners={setPartners}
          promos={promos}
          onUpdatePromos={setPromos}
          testimonials={testimonials}
          onUpdateTestimonials={setTestimonials}
          pricingPlans={pricingPlans}
          onUpdatePricingPlans={setPricingPlans}
          faqs={faqs}
          onUpdateFaqs={setFaqs}
          slideshowCategories={slideshowCategories}
          onUpdateSlideshowCategories={setSlideshowCategories}
          onPreviewSlideshowCategory={slug => {
            if (window.location.hash === '#admin') {
              window.history.replaceState(null, '', window.location.pathname);
            }
            setSelectedSlideshowSlug(slug);
            setActivePageRoute('beranda');
            setIsFaqPageOpen(false);
            setCurrentView('public');
            setTimeout(() => {
              const el = document.getElementById('slideshow');
              if (el) el.scrollIntoView({ behavior: 'smooth' });
            }, 80);
          }}
          initialTab={adminInitialTab}
          colorMode={colorMode}
          isDarkMode={isDarkMode}
          onChangeColorMode={handleChangeColorMode}
        />
      ) : (
        <div className="website-public-root flex flex-col min-h-screen">
          <Navbar
            currentView={currentView}
            setCurrentView={view => {
              if (view === 'php-export') setIsPhpExportModalOpen(true);
              else if (view === 'pdf-audit') setIsAuditModalOpen(true);
              else setCurrentView(view);
            }}
            onOpenAudit={() => setIsAuditModalOpen(true)}
            onOpenPhpExport={() => setIsPhpExportModalOpen(true)}
            onOpenConsultation={() => handleRequestQuote()}
            onOpenPage={handleOpenPageBySlug}
            onOpenFaqPage={handleOpenFaqPage}
            onResetSubViews={handleResetSubViews}
            activeRoute={currentRouteKey}
            onNavigateRoute={handleNavigateRoute}
            companyLogo={companyLogo}
            navigationMenus={navigationMenus}
            companySettings={companySettings}
            themeConfig={themeConfig}
            colorMode={colorMode}
            isDarkMode={isDarkMode}
            onChangeColorMode={handleChangeColorMode}
            services={services}
            pricingPlans={pricingPlans}
            articles={articles}
            projects={projects}
            faqs={faqs}
            pages={pages}
          />

          <main className="flex-1">
            {currentRouteKey !== 'beranda' ? (
              <div className="bg-slate-50 dark:bg-slate-950">
                <div className="bg-slate-900 border-b border-slate-800 text-white py-3.5">
                  <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2 text-xs text-slate-300">
                      <a
                        href="index.php"
                        onClick={e => {
                          e.preventDefault();
                          handleResetSubViews();
                        }}
                        className="hover:text-white font-semibold cursor-pointer"
                      >
                        Beranda
                      </a>
                      <span>/</span>
                      <span className="text-blue-400 font-bold">
                        {routeBreadcrumbLabels[currentRouteKey]?.label || 'Halaman'}
                      </span>
                      {routeBreadcrumbLabels[currentRouteKey]?.phpFile && (
                        <span className="hidden sm:inline-block ml-2 px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-mono text-[10px] border border-slate-700">
                          {routeBreadcrumbLabels[currentRouteKey].phpFile}
                        </span>
                      )}
                    </div>
                    <a
                      href="index.php"
                      onClick={e => {
                        e.preventDefault();
                        handleResetSubViews();
                      }}
                      className="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition cursor-pointer"
                    >
                      &larr; Kembali ke Beranda
                    </a>
                  </div>
                </div>

                {currentRouteKey === 'tentang-kami' && (
                  <AboutSection
                    pageContent={getPageBySlug('tentang-kami')}
                    companySettings={companySettings}
                    timeline={timeline}
                    certifications={certifications}
                    partners={partners}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'layanan' && (
                  <ServicesSection
                    services={services}
                    categories={categories}
                    projects={projects}
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('layanan')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'promo' && (
                  <PromoSection
                    promos={promos}
                    onRequestQuote={handleRequestQuote}
                    onAddInquiry={handleAddInquiry}
                    pageContent={getPageBySlug('promo')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {(currentRouteKey === 'klien' || currentRouteKey === 'portofolio') && (
                  <div id="klien">
                    <PortfolioSection
                      projects={projects}
                      onUpdateProjects={setProjects}
                      clients={clients}
                      onRequestQuote={handleRequestQuote}
                      pageContent={getPageBySlug('klien') || getPageBySlug('portofolio')}
                      isLiveEditMode={effectiveLiveEditMode}
                      onOpenAdminSection={tab => {
                        setAdminInitialTab(tab);
                        setCurrentView('admin');
                      }}
                    />
                  </div>
                )}

                {currentRouteKey === 'kemitraan' && (
                  <KemitraanSection
                    partners={partners}
                    certifications={certifications}
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('kemitraan')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'testimoni' && (
                  <TestimoniSection
                    testimonials={testimonials}
                    onAddTestimonial={handleAddTestimonial}
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('testimoni')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'harga' && (
                  <PricingSection
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('harga')}
                    pricingPlans={pricingPlans}
                    companySettings={companySettings}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'faq' && (
                  <FaqSection
                    onRequestQuote={() => handleRequestQuote()}
                    pageContent={getPageBySlug('faq')}
                    faqs={faqs}
                    companySettings={companySettings}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'artikel' && (
                  <ArticlesSection
                    articles={articles}
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('blog')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}

                {currentRouteKey === 'kontak' && (
                  <ContactSection
                    services={services}
                    prefilledService={prefilledService}
                    onAddInquiry={handleAddInquiry}
                    companySettings={companySettings}
                    pageContent={getPageBySlug('kontak')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                )}
              </div>
            ) : (
              <>
                {/* 1. Beranda & Side Slideshows */}
                <Hero
                  onOpenAudit={() => setIsAuditModalOpen(true)}
                  onOpenConsultation={() => handleRequestQuote()}
                  pageContent={getPageBySlug('beranda')}
                  sliderConfig={sliderConfig}
                  onUpdateSliderConfig={setSliderConfig}
                  onUpdatePageContent={updated => updatePageBySlug('beranda', updated)}
                  isLiveEditMode={effectiveLiveEditMode}
                  themeConfig={themeConfig}
                  companySettings={companySettings}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                  slideshowCategories={slideshowCategories}
                  onSelectSlideshowSlug={setSelectedSlideshowSlug}
                />

                {/* 8 Featured Slideshow Items with Read More & Why Choose Us? */}
                <SlideshowSection
                  categories={slideshowCategories}
                  pageData={getPageBySlug('slideshow')}
                  selectedSlug={selectedSlideshowSlug}
                  onSelectSlug={setSelectedSlideshowSlug}
                  onRequestConsultation={handleRequestQuote}
                />

                {/* Tentang Kami */}
                <AboutSection
                  pageContent={getPageBySlug('tentang-kami')}
                  companySettings={companySettings}
                  timeline={timeline}
                  certifications={certifications}
                  partners={partners}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* 2. Produk & Layanan */}
                <ServicesSection
                  services={services}
                  categories={categories}
                  projects={projects}
                  onRequestQuote={handleRequestQuote}
                  pageContent={getPageBySlug('layanan')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* 3. Promo */}
                <PromoSection
                  promos={promos}
                  onRequestQuote={handleRequestQuote}
                  onAddInquiry={handleAddInquiry}
                  pageContent={getPageBySlug('promo')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* Harga / Paket Solusi B2B */}
                <PricingSection
                  onRequestQuote={handleRequestQuote}
                  pageContent={getPageBySlug('harga')}
                  pricingPlans={pricingPlans}
                  companySettings={companySettings}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* 4. Client / Pelanggan & Portofolio */}
                <div id="klien">
                  <PortfolioSection
                    projects={projects}
                    onUpdateProjects={setProjects}
                    clients={clients}
                    onRequestQuote={handleRequestQuote}
                    pageContent={getPageBySlug('klien') || getPageBySlug('portofolio')}
                    isLiveEditMode={effectiveLiveEditMode}
                    onOpenAdminSection={tab => {
                      setAdminInitialTab(tab);
                      setCurrentView('admin');
                    }}
                  />
                </div>

                {/* 5. Kemitraan */}
                <KemitraanSection
                  partners={partners}
                  certifications={certifications}
                  onRequestQuote={handleRequestQuote}
                  pageContent={getPageBySlug('kemitraan')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* 6. Testimoni */}
                <TestimoniSection
                  testimonials={testimonials}
                  onAddTestimonial={handleAddTestimonial}
                  onRequestQuote={handleRequestQuote}
                  pageContent={getPageBySlug('testimoni')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* Artikel & Wawasan Edukatif */}
                <ArticlesSection
                  articles={articles}
                  onRequestQuote={handleRequestQuote}
                  pageContent={getPageBySlug('blog')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />

                {/* 7. Hubungi Kami & Form Penawaran */}
                <ContactSection
                  services={services}
                  prefilledService={prefilledService}
                  onAddInquiry={handleAddInquiry}
                  companySettings={companySettings}
                  pageContent={getPageBySlug('kontak')}
                  isLiveEditMode={effectiveLiveEditMode}
                  onOpenAdminSection={tab => {
                    setAdminInitialTab(tab);
                    setCurrentView('admin');
                  }}
                />
              </>
            )}
          </main>

          <Footer
            onOpenAudit={() => setIsAuditModalOpen(true)}
            onOpenPhpExport={() => setIsPhpExportModalOpen(true)}
            onOpenAdmin={() => setCurrentView('admin')}
            onOpenPage={handleOpenPageBySlug}
            onOpenFaqPage={handleOpenFaqPage}
            onResetSubViews={handleResetSubViews}
            onNavigateRoute={handleNavigateRoute}
            onUpdateCompanySettings={setCompanySettings}
            companyLogo={companyLogo}
            navigationMenus={navigationMenus}
            companySettings={companySettings}
            certifications={certifications}
            themeConfig={themeConfig}
            isLiveEditMode={effectiveLiveEditMode}
            onOpenAdminSection={tab => {
              if (tab) setAdminInitialTab(tab);
              setCurrentView('admin');
            }}
          />

          {/* Live Visual Website Editor Dock — ONLY rendered when Admin is authenticated */}
          {isAdminAuthenticated && (
            <LiveWebsiteEditorBar
              isLiveEditMode={effectiveLiveEditMode}
              onToggleLiveEditMode={val => {
                if (localStorage.getItem('kgs_admin_auth') === 'true') {
                  setIsLiveEditMode(val);
                } else {
                  setIsAdminAuthenticated(false);
                  setIsLiveEditMode(false);
                }
              }}
              onOpenAdminCms={tab => {
                if (tab) setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
              pages={pages}
              onUpdatePages={setPages}
              services={services}
              onUpdateServices={setServices}
              projects={projects}
              onUpdateProjects={setProjects}
              articles={articles}
              onUpdateArticles={setArticles}
              companySettings={companySettings}
              onUpdateCompanySettings={setCompanySettings}
              companyLogo={companyLogo}
              onUpdateCompanyLogo={setCompanyLogo}
              themeConfig={themeConfig}
              onUpdateThemeConfig={setThemeConfig}
              sliderConfig={sliderConfig}
              onUpdateSliderConfig={setSliderConfig}
              inlineOverrides={inlineOverrides}
              onUpdateInlineOverrides={setInlineOverrides}
              timeline={timeline}
              onUpdateTimeline={setTimeline}
              certifications={certifications}
              onUpdateCertifications={setCertifications}
              partners={partners}
              onUpdatePartners={setPartners}
              pricingPlans={pricingPlans}
              onUpdatePricingPlans={setPricingPlans}
              faqs={faqs}
              onUpdateFaqs={setFaqs}
              exportState={exportState}
            />
          )}
        </div>
      )}

      {/* Static Page Reader Modal */}
      <StaticPageModal
        page={selectedStaticPage}
        onClose={() => setSelectedStaticPage(null)}
        onRequestQuote={() => handleRequestQuote()}
      />

      {/* Master Inventory Audit Modal */}
      <PdfAuditModal
        isOpen={isAuditModalOpen}
        onClose={() => setIsAuditModalOpen(false)}
      />

      {/* PHP Native + MySQL Source Code & ZIP Export Modal */}
      <PhpExportModal
        isOpen={isPhpExportModalOpen}
        onClose={() => setIsPhpExportModalOpen(false)}
        onOpenAdmin={() => setCurrentView('admin')}
        exportState={exportState}
      />
    </div>
  );
}
