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
  FAQ_DATA
} from './data/companyData';
import {
  ColorMode,
  getStoredColorMode,
  setStoredColorMode,
  applyColorModeToDocument,
  syncLiveStateToServer,
  ExportStatePayload
} from './utils/exportSync';
import { Navbar } from './components/Navbar';
import { Hero } from './components/Hero';
import { AboutSection } from './components/AboutSection';
import { ServicesSection } from './components/ServicesSection';
import { PricingSection } from './components/PricingSection';
import { FaqSection } from './components/FaqSection';
import { PortfolioSection } from './components/PortfolioSection';
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

  // Load initial state with localStorage persistence
  const [services, setServices] = useState<ServiceItem[]>(() => {
    const saved = localStorage.getItem('kgs_services');
    return saved ? JSON.parse(saved) : SERVICES_DATA;
  });

  const [categories, setCategories] = useState<ServiceCategory[]>(() => {
    const saved = localStorage.getItem('kgs_categories');
    return saved ? JSON.parse(saved) : SERVICE_CATEGORIES;
  });

  const [clients, setClients] = useState<ClientItem[]>(() => {
    const saved = localStorage.getItem('kgs_clients');
    return saved ? JSON.parse(saved) : VERIFIED_CLIENTS;
  });

  const [projects, setProjects] = useState<ProjectPhoto[]>(() => {
    const saved = localStorage.getItem('kgs_projects');
    return saved ? JSON.parse(saved) : PROJECT_DOCUMENTARY;
  });

  const [articles, setArticles] = useState<ArticleItem[]>(() => {
    const saved = localStorage.getItem('kgs_articles');
    return saved ? JSON.parse(saved) : INITIAL_ARTICLES;
  });

  const [pages, setPages] = useState<StaticPageItem[]>(() => {
    const saved = localStorage.getItem('kgs_pages');
    if (saved) {
      try {
        const parsed: StaticPageItem[] = JSON.parse(saved).map((p: StaticPageItem) => {
          if (p.slug === 'kontak' && p.content && (p.content.includes('Kantor Perwakilan Jakarta') || p.content.includes('Kantor Pusat Palembang'))) {
            return {
              ...p,
              content: 'Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, survei lokasi, dan pengajuan penawaran resmi.'
            };
          }
          return p;
        });
        const existingSlugs = new Set(parsed.map(p => p.slug));
        const missingDefaults = INITIAL_PAGES.filter(p => !existingSlugs.has(p.slug));
        return [...parsed, ...missingDefaults];
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
          !merged.footerBrandLabel ||
          merged.footerBrandLabel.toUpperCase() === 'PT. KGS' ||
          merged.footerBrandLabel.toUpperCase() === 'KGS'
        ) {
          merged.footerBrandLabel = 'KGSNET';
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
    return saved ? JSON.parse(saved) : INITIAL_NAVIGATION_MENUS;
  });

  const [themeConfig, setThemeConfig] = useState<WebsiteThemeConfig>(() => {
    const saved = localStorage.getItem('kgs_theme_config');
    return saved ? { ...DEFAULT_WEBSITE_THEME, ...JSON.parse(saved) } : DEFAULT_WEBSITE_THEME;
  });

  const [sliderConfig, setSliderConfig] = useState<HeroSliderConfig>(() => {
    const saved = localStorage.getItem('kgs_slider_config');
    return saved
      ? { ...INITIAL_HERO_SLIDER_CONFIG, ...JSON.parse(saved) }
      : INITIAL_HERO_SLIDER_CONFIG;
  });

  const [inlineOverrides, setInlineOverrides] = useState<Record<string, string>>(() => {
    const saved = localStorage.getItem('kgs_inline_overrides');
    return saved ? JSON.parse(saved) : {};
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

  const [pricingPlans, setPricingPlans] = useState<PricingPlanItem[]>(() => {
    const saved = localStorage.getItem('kgs_pricing_plans');
    return saved ? JSON.parse(saved) : B2B_PRICING_PLANS;
  });

  const [faqs, setFaqs] = useState<FaqItem[]>(() => {
    const saved = localStorage.getItem('kgs_faqs');
    return saved ? JSON.parse(saved) : FAQ_DATA;
  });

  const [isLiveEditMode, setIsLiveEditMode] = useState<boolean>(false);
  const [adminInitialTab, setAdminInitialTab] = useState<string>('overview');

  const [currentView, setCurrentView] = useState<'public' | 'admin'>(() => {
    return window.location.hash === '#admin' ? 'admin' : 'public';
  });
  const [isAuditModalOpen, setIsAuditModalOpen] = useState(false);
  const [isPhpExportModalOpen, setIsPhpExportModalOpen] = useState(false);
  const [selectedStaticPage, setSelectedStaticPage] = useState<StaticPageItem | null>(null);
  const [prefilledService, setPrefilledService] = useState<string>('');
  const [companyLogo, setCompanyLogo] = useState<string>(() => {
    return localStorage.getItem('kgs_custom_logo') || '';
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
    localStorage.setItem('kgs_pricing_plans', JSON.stringify(pricingPlans));
  }, [pricingPlans]);

  useEffect(() => {
    localStorage.setItem('kgs_faqs', JSON.stringify(faqs));
  }, [faqs]);

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
    const contactElem = document.getElementById('konsultasi');
    if (contactElem) {
      contactElem.scrollIntoView({ behavior: 'smooth' });
    }
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

  const getPageBySlug = (slug: string): StaticPageItem | undefined => {
    return pages.find(p => p.slug === slug);
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
    const page = pages.find(p => p.slug === slug);
    if (page) {
      setSelectedStaticPage(page);
    }
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
            setIsLiveEditMode(true);
            setCurrentView('public');
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
          pricingPlans={pricingPlans}
          onUpdatePricingPlans={setPricingPlans}
          faqs={faqs}
          onUpdateFaqs={setFaqs}
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
            companyLogo={companyLogo}
            navigationMenus={navigationMenus}
            companySettings={companySettings}
            themeConfig={themeConfig}
            colorMode={colorMode}
            isDarkMode={isDarkMode}
            onChangeColorMode={handleChangeColorMode}
          />

          <main className="flex-1">
            {/* 1. Beranda & Slider */}
            <Hero
              onOpenAudit={() => setIsAuditModalOpen(true)}
              onOpenConsultation={() => handleRequestQuote()}
              pageContent={getPageBySlug('beranda')}
              sliderConfig={sliderConfig}
              onUpdateSliderConfig={setSliderConfig}
              onUpdatePageContent={updated => updatePageBySlug('beranda', updated)}
              isLiveEditMode={isLiveEditMode}
              themeConfig={themeConfig}
              companySettings={companySettings}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 2. Tentang Kami */}
            <AboutSection
              pageContent={getPageBySlug('tentang-kami')}
              companySettings={companySettings}
              timeline={timeline}
              certifications={certifications}
              partners={partners}
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 3. Layanan */}
            <ServicesSection
              services={services}
              categories={categories}
              onRequestQuote={handleRequestQuote}
              pageContent={getPageBySlug('layanan')}
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 4. Harga / Paket Solusi B2B */}
            <PricingSection
              onRequestQuote={handleRequestQuote}
              pageContent={getPageBySlug('harga')}
              pricingPlans={pricingPlans}
              companySettings={companySettings}
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 5. FAQ */}
            <FaqSection
              onRequestQuote={() => handleRequestQuote()}
              pageContent={getPageBySlug('faq')}
              faqs={faqs}
              companySettings={companySettings}
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 6. Portofolio (Dokumentasi Lapangan & Klien Terverifikasi) */}
            <PortfolioSection
              projects={projects}
              clients={clients}
              onRequestQuote={handleRequestQuote}
              pageContent={getPageBySlug('portofolio')}
              isLiveEditMode={isLiveEditMode}
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
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />

            {/* 7. Kontak & Form Penawaran */}
            <ContactSection
              services={services}
              prefilledService={prefilledService}
              onAddInquiry={handleAddInquiry}
              companySettings={companySettings}
              pageContent={getPageBySlug('kontak')}
              isLiveEditMode={isLiveEditMode}
              onOpenAdminSection={tab => {
                setAdminInitialTab(tab);
                setCurrentView('admin');
              }}
            />
          </main>

          <Footer
            onOpenAudit={() => setIsAuditModalOpen(true)}
            onOpenPhpExport={() => setIsPhpExportModalOpen(true)}
            onOpenAdmin={() => setCurrentView('admin')}
            onOpenPage={handleOpenPageBySlug}
            companyLogo={companyLogo}
            navigationMenus={navigationMenus}
            companySettings={companySettings}
            themeConfig={themeConfig}
            isLiveEditMode={isLiveEditMode}
            onOpenAdminSection={tab => {
              if (tab) setAdminInitialTab(tab);
              setCurrentView('admin');
            }}
          />

          {/* Live Visual Website Editor Dock for Admin */}
          <LiveWebsiteEditorBar
            isLiveEditMode={isLiveEditMode}
            onToggleLiveEditMode={setIsLiveEditMode}
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
