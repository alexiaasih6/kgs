import {
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  StaticPageItem,
  INITIAL_PAGES,
  ServiceItem,
  SERVICES_DATA,
  ServiceCategory,
  SERVICE_CATEGORIES,
  PricingPlanItem,
  B2B_PRICING_PLANS,
  FaqItem,
  FAQ_DATA,
  ProjectPhoto,
  PROJECT_DOCUMENTARY,
  ClientItem,
  VERIFIED_CLIENTS,
  ArticleItem,
  INITIAL_ARTICLES,
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
  getEffectiveSocialLinks
} from '../data/companyData';

export type ColorMode = 'light' | 'dark';

export function getStoredColorMode(): ColorMode {
  try {
    const val = localStorage.getItem('kgs_color_mode');
    if (val === 'dark') return 'dark';
    if (val === 'light') return 'light';
  } catch {
    // ignore
  }
  return 'light';
}

export function resolveIsDarkMode(mode: ColorMode): boolean {
  return mode === 'dark';
}

export function applyColorModeToDocument(mode: ColorMode): boolean {
  const isDark = resolveIsDarkMode(mode);
  if (typeof document !== 'undefined') {
    const root = document.documentElement;
    if (isDark) {
      root.classList.add('dark');
      root.setAttribute('data-theme', 'dark');
    } else {
      root.classList.remove('dark');
      root.setAttribute('data-theme', 'light');
    }
  }
  return isDark;
}

export interface LiveExportSnapshot {
  buildId: string;
  exportedAt: string;
  colorMode: ColorMode;
  companyLogo: string;
  companySettings: CompanySettings;
  pages: StaticPageItem[];
  services: ServiceItem[];
  categories: ServiceCategory[];
  pricingPlans: PricingPlanItem[];
  faqs: FaqItem[];
  projects: ProjectPhoto[];
  clients: ClientItem[];
  articles: ArticleItem[];
  navigationMenus: NavigationMenuItem[];
  themeConfig: WebsiteThemeConfig;
  sliderConfig: HeroSliderConfig;
  timeline: TimelineMilestoneItem[];
  certifications: CertificationItem[];
  partners: PartnerItem[];
  inlineOverrides: Record<string, string>;
}

function readJsonFromLocalStorage<T>(key: string, fallback: T): T {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return fallback;
    return JSON.parse(raw) as T;
  } catch {
    return fallback;
  }
}

export function normalizePhpMenuUrl(url: string | undefined, label: string, pageSlug?: string): string {
  const raw = (url || '').trim();
  const lowUrl = raw.toLowerCase();
  const lowLabel = (label || '').trim().toLowerCase();
  const lowSlug = (pageSlug || '').trim().toLowerCase();

  if (raw && !raw.startsWith('#') && raw !== '/' && raw !== 'javascript:void(0)') {
    return raw;
  }

  if (lowSlug === 'beranda' || lowUrl === '#beranda' || lowUrl === '#home' || lowLabel === 'beranda' || lowLabel === 'home') return 'index.php';
  if (lowSlug === 'profil-perusahaan' || lowUrl === '#profil' || lowLabel.includes('profil perusahaan')) return 'page.php?slug=profil-perusahaan';
  if (lowSlug === 'visi-misi-budaya' || lowUrl === '#visi-misi' || lowLabel.includes('visi')) return 'page.php?slug=visi-misi-budaya';
  if (lowSlug === 'tentang-kami' || lowUrl === '#tentang' || lowUrl === '#about' || lowLabel.includes('tentang')) return 'tentang.php';
  if (lowSlug === 'layanan' || lowUrl === '#layanan' || lowUrl === '#services' || lowLabel.includes('product') || lowLabel.includes('layanan')) return 'layanan.php';
  if (lowSlug === 'harga' || lowUrl === '#harga' || lowUrl === '#pricing' || lowLabel.includes('harga') || lowLabel.includes('paket internet')) return 'harga.php';
  if (lowSlug === 'faq' || lowUrl === '#faq' || lowLabel.includes('faq') || lowLabel.includes('pertanyaan')) return 'faq.php';
  if (lowSlug === 'portofolio' || lowUrl === '#portofolio' || lowUrl === '#portfolio' || lowLabel.includes('portofolio')) return 'portofolio.php';
  if (lowSlug === 'blog' || lowUrl === '#blog' || lowUrl === '#artikel' || lowLabel.includes('artikel') || lowLabel.includes('blog')) return 'blog.php';
  if (lowSlug === 'kebijakan-privasi' || lowUrl === '#privasi' || lowLabel.includes('privasi')) return 'kebijakan-privasi.php';
  if (lowLabel.includes('peta') || lowLabel.includes('maps')) return 'kontak.php#peta-lokasi';
  if (lowLabel.includes('penawaran') || lowLabel.includes('survei')) return 'kontak.php#konsultasi';
  if (lowSlug === 'kontak' || lowUrl === '#kontak' || lowUrl === '#contact' || lowLabel.includes('kontak')) return 'kontak.php';

  return 'index.php';
}

export function getCurrentLiveExportSnapshot(): LiveExportSnapshot {
  const rawSettings = readJsonFromLocalStorage<Partial<CompanySettings>>(
    'kgs_company_settings',
    DEFAULT_COMPANY_SETTINGS
  );
  const companySettings: CompanySettings = {
    ...DEFAULT_COMPANY_SETTINGS,
    ...rawSettings
  };

  if (
    !companySettings.legalName ||
    companySettings.legalName.toLowerCase().includes('creatve') ||
    companySettings.legalName.toLowerCase() === 'creative.co.id'
  ) {
    companySettings.legalName = 'PT. KREATIF GLOBAL SOLUSINDO';
  }

  // Ensure footerBrandLabel is always KGSNET if unset or legacy PT. KGS / KGS
  if (
    !companySettings.footerBrandLabel ||
    companySettings.footerBrandLabel.trim().toUpperCase() === 'PT. KGS' ||
    companySettings.footerBrandLabel.trim().toUpperCase() === 'KGS'
  ) {
    companySettings.footerBrandLabel = 'KGSNET';
  }

  // Ensure Jakarta office is removed from settings
  delete companySettings.addressJakarta;

  const rawMenus = readJsonFromLocalStorage<NavigationMenuItem[]>(
    'kgs_navigation_menus',
    INITIAL_NAVIGATION_MENUS
  );
  const seenMenuKeys = new Set<string>();
  const normalizedMenus: NavigationMenuItem[] = [];
  for (const m of rawMenus) {
    const rawTitle = (m.title || '').trim();
    if (!rawTitle) continue;
    const cleanTitle =
      rawTitle === 'Layanan' || rawTitle === 'Katalog Layanan' ? 'Product & Service' : rawTitle;
    const cleanUrl = normalizePhpMenuUrl(m.url, cleanTitle);
    const dedupeKey = `${m.location || 'header'}:${cleanTitle.toLowerCase()}`;
    if (!seenMenuKeys.has(dedupeKey)) {
      seenMenuKeys.add(dedupeKey);
      normalizedMenus.push({
        ...m,
        title: cleanTitle,
        url: cleanUrl
      });
    }
  }

  const now = new Date();
  const buildId = `KGS-SYNC-${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(
    now.getDate()
  ).padStart(2, '0')}-${String(now.getHours()).padStart(2, '0')}${String(
    now.getMinutes()
  ).padStart(2, '0')}${String(now.getSeconds()).padStart(2, '0')}`;

  return {
    buildId,
    exportedAt: now.toISOString(),
    colorMode: getStoredColorMode(),
    companyLogo: localStorage.getItem('kgs_custom_logo') || '',
    companySettings,
    pages: readJsonFromLocalStorage<StaticPageItem[]>('kgs_pages', INITIAL_PAGES),
    services: readJsonFromLocalStorage<ServiceItem[]>('kgs_services', SERVICES_DATA),
    categories: readJsonFromLocalStorage<ServiceCategory[]>('kgs_categories', SERVICE_CATEGORIES),
    pricingPlans: readJsonFromLocalStorage<PricingPlanItem[]>('kgs_pricing_plans', B2B_PRICING_PLANS),
    faqs: readJsonFromLocalStorage<FaqItem[]>('kgs_faqs', FAQ_DATA),
    projects: readJsonFromLocalStorage<ProjectPhoto[]>('kgs_projects', PROJECT_DOCUMENTARY),
    clients: readJsonFromLocalStorage<ClientItem[]>('kgs_clients', VERIFIED_CLIENTS),
    articles: readJsonFromLocalStorage<ArticleItem[]>('kgs_articles', INITIAL_ARTICLES),
    navigationMenus: normalizedMenus.length > 0 ? normalizedMenus : INITIAL_NAVIGATION_MENUS,
    themeConfig: {
      ...DEFAULT_WEBSITE_THEME,
      ...readJsonFromLocalStorage<Partial<WebsiteThemeConfig>>(
        'kgs_theme_config',
        DEFAULT_WEBSITE_THEME
      )
    },
    sliderConfig: {
      ...INITIAL_HERO_SLIDER_CONFIG,
      ...readJsonFromLocalStorage<Partial<HeroSliderConfig>>(
        'kgs_slider_config',
        INITIAL_HERO_SLIDER_CONFIG
      )
    },
    timeline: readJsonFromLocalStorage<TimelineMilestoneItem[]>(
      'kgs_timeline',
      INITIAL_TIMELINE_MILESTONES
    ),
    certifications: readJsonFromLocalStorage<CertificationItem[]>(
      'kgs_certifications',
      VERIFIED_CERTIFICATIONS
    ),
    partners: readJsonFromLocalStorage<PartnerItem[]>('kgs_partners', VERIFIED_PARTNERS),
    inlineOverrides: readJsonFromLocalStorage<Record<string, string>>('kgs_inline_overrides', {})
  };
}

/**
 * Escapes SQL strings using standard ANSI doubled single quotes ('') and strips
 * any backslashes so phpMyAdmin imports never fail due to backslash escaping.
 */
function sqlStr(val: string | undefined | null): string {
  const clean = String(val ?? '')
    .replace(/\\/g, '/')
    .replace(/'/g, "''");
  return `'${clean}'`;
}

export function buildLivePreviewJson(snapshot?: ExportStatePayload): string {
  const baseSnap = getCurrentLiveExportSnapshot();
  const snap: LiveExportSnapshot = snapshot
    ? {
        ...baseSnap,
        ...snapshot,
        buildId: snapshot.buildId || computeExportBuildId(snapshot),
        companySettings: {
          ...baseSnap.companySettings,
          ...(snapshot.companySettings || {})
        }
      }
    : baseSnap;
  const s = snap.companySettings;
  const hl = s.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
  const st = s.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];

  const settingsMap: Record<string, string> = {
    build_sync_id: snap.buildId,
    exported_at: snap.exportedAt,
    color_mode_default: snap.colorMode,
    company_name: s.legalName || DEFAULT_COMPANY_SETTINGS.legalName,
    logo_bg_color: s.logoBgColor || snap.themeConfig.primaryColor || '#2563eb',
    logo_text_color: s.logoTextColor || '#ffffff',
    company_name_color: s.companyNameColor || '#0f172a',
    tagline_color: s.sloganColor || '#2563eb',
    short_name: s.footerBrandLabel || 'KGSNET',
    footer_brand_label: s.footerBrandLabel || 'KGSNET',
    tagline: s.slogan || DEFAULT_COMPANY_SETTINGS.slogan,
    company_profile: s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || '',
    footer_description: s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || '',
    footer_contact_heading: s.footerContactHeading || DEFAULT_COMPANY_SETTINGS.footerContactHeading || 'Alamat Kantor & Kontak',
    footer_address_label: s.footerAddressLabel || DEFAULT_COMPANY_SETTINGS.footerAddressLabel || 'Alamat Kantor:',
    footer_nav_heading: s.footerNavHeading || DEFAULT_COMPANY_SETTINGS.footerNavHeading || 'Navigasi Halaman',
    footer_quick_heading: s.footerQuickHeading || DEFAULT_COMPANY_SETTINGS.footerQuickHeading || 'Informasi & Akses Cepat',
    footer_quick_1_label: 'Kebijakan Privasi & SLA',
    footer_quick_1_url: 'kebijakan-privasi.php',
    footer_quick_2_label: '3 Segmen Paket Internet ISP',
    footer_quick_2_url: 'harga.php',
    footer_quick_3_label: 'Pertanyaan Umum (FAQ)',
    footer_quick_3_url: 'faq.php',
    footer_quick_4_label: 'Minta Penawaran / Survei',
    footer_quick_4_url: 'kontak.php#konsultasi',
    footer_quick_5_label: 'Peta Lokasi Google Maps',
    footer_quick_5_url: 'kontak.php#peta-lokasi',
    footer_cert_heading: s.footerCertHeading || DEFAULT_COMPANY_SETTINGS.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan',
    footer_cert_badges: s.footerCertBadges || DEFAULT_COMPANY_SETTINGS.footerCertBadges || 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified',
    footer_cert_card_title: s.footerCertCardTitle || DEFAULT_COMPANY_SETTINGS.footerCertCardTitle || 'Sertifikasi & Keanggotaan Resmi',
    footer_cert_card_status: s.footerCertCardStatus || DEFAULT_COMPANY_SETTINGS.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi',
    copyright_text: s.copyrightText || DEFAULT_COMPANY_SETTINGS.copyrightText || '© 2026 KGSNET — Creative.co.id. All Rights Reserved.',
    phone_palembang: s.phonePalembang || DEFAULT_COMPANY_SETTINGS.phonePalembang,
    phone_hunting: s.phonePalembang || DEFAULT_COMPANY_SETTINGS.phonePalembang,
    whatsapp: s.whatsapp || DEFAULT_COMPANY_SETTINGS.whatsapp,
    email: s.email || DEFAULT_COMPANY_SETTINGS.email,
    operating_hours: s.operationalHours || DEFAULT_COMPANY_SETTINGS.operationalHours || '',
    operational_hours: s.operationalHours || DEFAULT_COMPANY_SETTINGS.operationalHours || '',
    address_palembang: s.addressPalembang || DEFAULT_COMPANY_SETTINGS.addressPalembang,
    google_map_input: s.googleMapInput || s.addressPalembang || DEFAULT_COMPANY_SETTINGS.googleMapInput,
    vision: s.vision || DEFAULT_COMPANY_SETTINGS.vision,
    mission: s.mission || DEFAULT_COMPANY_SETTINGS.mission,
    culture: s.culture || DEFAULT_COMPANY_SETTINGS.culture || '',
    commitment: s.commitment || DEFAULT_COMPANY_SETTINGS.commitment || '',
    website_corporate: s.websiteCorporate || 'www.creative.co.id',
    website_isp: s.websiteIsp || 'www.kgs.net.id',
    social_facebook: s.socialFacebook || DEFAULT_COMPANY_SETTINGS.socialFacebook || 'https://facebook.com/kgs.palembang',
    social_instagram: s.socialInstagram || DEFAULT_COMPANY_SETTINGS.socialInstagram || 'https://instagram.com/kgs.palembang',
    social_linkedin: s.socialLinkedin || DEFAULT_COMPANY_SETTINGS.socialLinkedin || 'https://linkedin.com/company/kreatif-global-solusindo',
    social_youtube: s.socialYoutube || DEFAULT_COMPANY_SETTINGS.socialYoutube || 'https://youtube.com/@kreatifglobalsolusindo',
    social_tiktok: s.socialTiktok || DEFAULT_COMPANY_SETTINGS.socialTiktok || 'https://tiktok.com/@kgs.palembang',
    social_whatsapp: s.socialWhatsapp || DEFAULT_COMPANY_SETTINGS.socialWhatsapp || 'https://wa.me/62811782119',
    social_links_json: JSON.stringify(getEffectiveSocialLinks(s)),
    hero_badge_text: s.heroBadgeText || DEFAULT_COMPANY_SETTINGS.heroBadgeText || '',
    hero_cta_primary: s.heroCtaPrimary || DEFAULT_COMPANY_SETTINGS.heroCtaPrimary || '',
    hero_cta_secondary: s.heroCtaSecondary || DEFAULT_COMPANY_SETTINGS.heroCtaSecondary || '',
    hero_highlight_1: hl[0] || 'ISP Resmi KGSNet & Anggota APJII',
    hero_highlight_2: hl[1] || 'Pabrikasi & Instalasi Tower Guywire Mandiri',
    hero_highlight_3: hl[2] || 'Garansi Uji Tahanan Grounding < 2 Ohm',
    hero_highlight_4: hl[3] || 'Klien Korporasi, BUMN, Perbankan & Rumah Sakit',
    hero_stat_1_val: st[0]?.value || '1997',
    hero_stat_1_lbl: st[0]?.label || 'Awal Berdiri (Creative Com)',
    hero_stat_2_val: st[1]?.value || '5 Segmen',
    hero_stat_2_lbl: st[1]?.label || 'Layanan Solusi Terintegrasi',
    hero_stat_3_val: st[2]?.value || '100+',
    hero_stat_3_lbl: st[2]?.label || 'Klien BUMN, Swasta & Instansi',
    hero_stat_4_val: st[3]?.value || '< 2 Ω',
    hero_stat_4_lbl: st[3]?.label || 'Standar Grounding & Proteksi',
    noc_card_title: s.nocCardTitle || DEFAULT_COMPANY_SETTINGS.nocCardTitle || '',
    noc_status_badge: s.nocStatusLabel || DEFAULT_COMPANY_SETTINGS.nocStatusLabel || '',
    pricing_banner_title: s.pricingBannerTitle || '',
    pricing_banner_desc: s.pricingBannerDesc || '',
    faq_banner_title: s.faqSupportTitle || '',
    faq_banner_desc: s.faqSupportDesc || '',
    theme_preset: snap.themeConfig.presetId,
    primary_color: snap.themeConfig.primaryColor,
    hero_bg_color: snap.themeConfig.heroBgColor,
    navbar_style: snap.themeConfig.navbarStyle,
    hero_slider_enabled: snap.sliderConfig.layoutMode === 'noc' ? '0' : '1',
    hero_slider_layout: snap.sliderConfig.layoutMode,
    hero_slider_autoplay: snap.sliderConfig.autoPlay ? '1' : '0',
    hero_slider_interval: String(snap.sliderConfig.intervalSeconds || 5),
    hero_slider_overlay: String(snap.sliderConfig.overlayOpacity || 75),
    default_meta_title: s.seoDefaultMetaTitle || DEFAULT_COMPANY_SETTINGS.seoDefaultMetaTitle || '',
    default_meta_desc: s.seoDefaultMetaDesc || DEFAULT_COMPANY_SETTINGS.seoDefaultMetaDesc || '',
    meta_keywords: s.seoMetaKeywords || DEFAULT_COMPANY_SETTINGS.seoMetaKeywords || '',
    seo_author: s.seoAuthor || DEFAULT_COMPANY_SETTINGS.seoAuthor || '',
    seo_title_separator: s.seoTitleSeparator || '|',
    seo_canonical_base: s.seoCanonicalBase || DEFAULT_COMPANY_SETTINGS.seoCanonicalBase || '',
    seo_robots: s.seoRobots || 'index, follow',
    seo_googlebot: s.seoGooglebot || 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
    seo_google_verification: s.seoGoogleVerification || '',
    seo_search_console_txt: s.seoSearchConsoleTxt || '',
    seo_bing_verification: s.seoBingVerification || '',
    seo_yandex_verification: s.seoYandexVerification || '',
    seo_fb_domain_verification: s.seoFbDomainVerification || '',
    seo_pinterest_verification: s.seoPinterestVerification || '',
    seo_og_type: s.seoOgType || 'website',
    seo_og_site_name: s.seoOgSiteName || s.legalName || 'PT. Kreatif Global Solusindo',
    seo_og_image: s.seoOgImage || '',
    seo_twitter_card: s.seoTwitterCard || 'summary_large_image',
    seo_twitter_site: s.seoTwitterSite || '',
    seo_geo_region: s.seoGeoRegion || 'ID-SS',
    seo_geo_placename: s.seoGeoPlacename || 'Palembang, Sumatera Selatan',
    seo_geo_position: s.seoGeoPosition || '-2.9718;104.7644',
    seo_schema_type: s.seoSchemaType || 'Organization',
    seo_schema_founding_year: s.seoSchemaFoundingYear || '1997',
    seo_schema_price_range: s.seoSchemaPriceRange || '$$$',
    seo_google_analytics_id: s.seoGoogleAnalyticsId || '',
    inline_overrides_json: JSON.stringify(snap.inlineOverrides || {})
  };

  if (snap.companyLogo && !snap.companyLogo.startsWith('data:image/')) {
    settingsMap.company_logo = snap.companyLogo;
    settingsMap.logo = snap.companyLogo;
  }
  if (s.footerLogo && !s.footerLogo.startsWith('data:image/')) {
    settingsMap.footer_logo = s.footerLogo;
  }

  const heroSlidersPayload = (snap.sliderConfig.slides || []).map((sl, idx) => ({
    id: idx + 1,
    title: sl.title,
    subtitle: sl.subtitle,
    badge_text: sl.badgeText || 'Dokumentasi KGS',
    image_url:
      sl.imageUrl === 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80'
        ? 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80'
        : sl.imageUrl,
    sort_order: sl.sortOrder || idx + 1,
    is_active: sl.isActive ? 1 : 0
  }));

  const normalizedMenus = (snap.navigationMenus && snap.navigationMenus.length > 0 ? snap.navigationMenus : INITIAL_NAVIGATION_MENUS)
    .filter(m => (m.title || '').trim() !== '')
    .map(m => ({
      ...m,
      title: m.title.trim(),
      url: normalizePhpMenuUrl(m.url, m.title)
    }));

  return JSON.stringify(
    {
      buildId: snap.buildId,
      exportedAt: snap.exportedAt,
      settings: settingsMap,
      inlineOverrides: snap.inlineOverrides || {},
      pages: snap.pages,
      menus: normalizedMenus,
      hero_sliders: heroSlidersPayload
    },
    null,
    2
  );
}

export function buildDynamicDatabaseSql(baseSql: string, snapshot?: LiveExportSnapshot): string {
  const snap = snapshot || getCurrentLiveExportSnapshot();
  const s = snap.companySettings;
  const hl = s.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
  const st = s.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];

  const settingsEntries: Array<[string, string, string]> = [
    ['system', 'build_sync_id', snap.buildId],
    ['system', 'color_mode_default', snap.colorMode],
    ['identity', 'company_name', s.legalName || DEFAULT_COMPANY_SETTINGS.legalName],
    ['branding', 'logo_bg_color', s.logoBgColor || snap.themeConfig.primaryColor || '#2563eb'],
    ['branding', 'logo_text_color', s.logoTextColor || '#ffffff'],
    ['branding', 'company_name_color', s.companyNameColor || '#0f172a'],
    ['branding', 'tagline_color', s.sloganColor || '#2563eb'],
    ['identity', 'short_name', s.footerBrandLabel || 'KGSNET'],
    ['footer', 'footer_brand_label', s.footerBrandLabel || 'KGSNET'],
    ['identity', 'tagline', s.slogan || DEFAULT_COMPANY_SETTINGS.slogan],
    ['identity', 'company_profile', s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || ''],
    ['footer', 'footer_description', s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || ''],
    ['footer', 'footer_contact_heading', s.footerContactHeading || DEFAULT_COMPANY_SETTINGS.footerContactHeading || 'Alamat Kantor & Kontak'],
    ['footer', 'footer_address_label', s.footerAddressLabel || DEFAULT_COMPANY_SETTINGS.footerAddressLabel || 'Alamat Kantor:'],
    ['footer', 'footer_nav_heading', s.footerNavHeading || DEFAULT_COMPANY_SETTINGS.footerNavHeading || 'Navigasi Halaman'],
    ['footer', 'footer_quick_heading', s.footerQuickHeading || DEFAULT_COMPANY_SETTINGS.footerQuickHeading || 'Informasi & Akses Cepat'],
    ['footer', 'footer_quick_1_label', 'Kebijakan Privasi & SLA'],
    ['footer', 'footer_quick_1_url', 'kebijakan-privasi.php'],
    ['footer', 'footer_quick_2_label', '3 Segmen Paket Internet ISP'],
    ['footer', 'footer_quick_2_url', 'harga.php'],
    ['footer', 'footer_quick_3_label', 'Pertanyaan Umum (FAQ)'],
    ['footer', 'footer_quick_3_url', 'faq.php'],
    ['footer', 'footer_quick_4_label', 'Minta Penawaran / Survei'],
    ['footer', 'footer_quick_4_url', 'kontak.php#konsultasi'],
    ['footer', 'footer_quick_5_label', 'Peta Lokasi Google Maps'],
    ['footer', 'footer_quick_5_url', 'kontak.php#peta-lokasi'],
    ['footer', 'footer_cert_heading', s.footerCertHeading || DEFAULT_COMPANY_SETTINGS.footerCertHeading || 'Lisensi, Sertifikasi & Keanggotaan'],
    ['footer', 'footer_cert_badges', s.footerCertBadges || DEFAULT_COMPANY_SETTINGS.footerCertBadges || 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified'],
    ['footer', 'footer_cert_card_title', s.footerCertCardTitle || DEFAULT_COMPANY_SETTINGS.footerCertCardTitle || 'Sertifikasi & Keanggotaan Resmi'],
    ['footer', 'footer_cert_card_status', s.footerCertCardStatus || DEFAULT_COMPANY_SETTINGS.footerCertCardStatus || '✓ Terverifikasi & Berlisensi Resmi'],
    ['footer', 'copyright_text', s.copyrightText || DEFAULT_COMPANY_SETTINGS.copyrightText || '© 2026 KGSNET — Creative.co.id. All Rights Reserved.'],
    ['contact', 'phone_palembang', s.phonePalembang || DEFAULT_COMPANY_SETTINGS.phonePalembang],
    ['contact', 'phone_hunting', s.phonePalembang || DEFAULT_COMPANY_SETTINGS.phonePalembang],
    ['contact', 'whatsapp', s.whatsapp || DEFAULT_COMPANY_SETTINGS.whatsapp],
    ['contact', 'email', s.email || DEFAULT_COMPANY_SETTINGS.email],
    ['contact', 'operating_hours', s.operationalHours || DEFAULT_COMPANY_SETTINGS.operationalHours || ''],
    ['contact', 'address_palembang', s.addressPalembang || DEFAULT_COMPANY_SETTINGS.addressPalembang],
    ['contact', 'google_map_input', s.googleMapInput || s.addressPalembang || DEFAULT_COMPANY_SETTINGS.googleMapInput],
    ['contact', 'social_facebook', s.socialFacebook || DEFAULT_COMPANY_SETTINGS.socialFacebook || 'https://facebook.com/kgs.palembang'],
    ['contact', 'social_instagram', s.socialInstagram || DEFAULT_COMPANY_SETTINGS.socialInstagram || 'https://instagram.com/kgs.palembang'],
    ['contact', 'social_linkedin', s.socialLinkedin || DEFAULT_COMPANY_SETTINGS.socialLinkedin || 'https://linkedin.com/company/kreatif-global-solusindo'],
    ['contact', 'social_youtube', s.socialYoutube || DEFAULT_COMPANY_SETTINGS.socialYoutube || 'https://youtube.com/@kreatifglobalsolusindo'],
    ['contact', 'social_tiktok', s.socialTiktok || DEFAULT_COMPANY_SETTINGS.socialTiktok || 'https://tiktok.com/@kgs.palembang'],
    ['contact', 'social_whatsapp', s.socialWhatsapp || DEFAULT_COMPANY_SETTINGS.socialWhatsapp || 'https://wa.me/62811782119'],
    ['contact', 'social_links_json', JSON.stringify(getEffectiveSocialLinks(s))],
    ['identity', 'vision', s.vision || DEFAULT_COMPANY_SETTINGS.vision],
    ['identity', 'mission', s.mission || DEFAULT_COMPANY_SETTINGS.mission],
    ['identity', 'culture', s.culture || DEFAULT_COMPANY_SETTINGS.culture || ''],
    ['identity', 'commitment', s.commitment || DEFAULT_COMPANY_SETTINGS.commitment || ''],
    ['banners', 'website_corporate', s.websiteCorporate || 'www.creative.co.id'],
    ['banners', 'website_isp', s.websiteIsp || 'www.kgs.net.id'],
    ['homepage', 'hero_badge_text', s.heroBadgeText || DEFAULT_COMPANY_SETTINGS.heroBadgeText || ''],
    ['homepage', 'hero_cta_primary', s.heroCtaPrimary || DEFAULT_COMPANY_SETTINGS.heroCtaPrimary || ''],
    ['homepage', 'hero_cta_secondary', s.heroCtaSecondary || DEFAULT_COMPANY_SETTINGS.heroCtaSecondary || ''],
    ['homepage', 'hero_highlight_1', hl[0] || 'ISP Resmi KGSNet & Anggota APJII'],
    ['homepage', 'hero_highlight_2', hl[1] || 'Pabrikasi & Instalasi Tower Guywire Mandiri'],
    ['homepage', 'hero_highlight_3', hl[2] || 'Garansi Uji Tahanan Grounding < 2 Ohm'],
    ['homepage', 'hero_highlight_4', hl[3] || 'Klien Korporasi, BUMN, Perbankan & Rumah Sakit'],
    ['homepage', 'hero_stat_1_val', st[0]?.value || '1997'],
    ['homepage', 'hero_stat_1_lbl', st[0]?.label || 'Awal Berdiri (Creative Com)'],
    ['homepage', 'hero_stat_2_val', st[1]?.value || '5 Segmen'],
    ['homepage', 'hero_stat_2_lbl', st[1]?.label || 'Layanan Solusi Terintegrasi'],
    ['homepage', 'hero_stat_3_val', st[2]?.value || '100+'],
    ['homepage', 'hero_stat_3_lbl', st[2]?.label || 'Klien BUMN, Swasta & Instansi'],
    ['homepage', 'hero_stat_4_val', st[3]?.value || '< 2 Ω'],
    ['homepage', 'hero_stat_4_lbl', st[3]?.label || 'Standar Grounding & Proteksi'],
    ['homepage', 'noc_card_title', s.nocCardTitle || DEFAULT_COMPANY_SETTINGS.nocCardTitle || ''],
    ['homepage', 'noc_status_badge', s.nocStatusLabel || DEFAULT_COMPANY_SETTINGS.nocStatusLabel || ''],
    ['branding', 'theme_preset', snap.themeConfig.presetId],
    ['branding', 'primary_color', snap.themeConfig.primaryColor],
    ['branding', 'hero_bg_color', snap.themeConfig.heroBgColor],
    ['branding', 'navbar_style', snap.themeConfig.navbarStyle],
    ['homepage', 'hero_slider_enabled', snap.sliderConfig.layoutMode === 'noc' ? '0' : '1'],
    ['homepage', 'hero_slider_layout', snap.sliderConfig.layoutMode],
    ['homepage', 'hero_slider_autoplay', snap.sliderConfig.autoPlay ? '1' : '0'],
    ['homepage', 'hero_slider_interval', String(snap.sliderConfig.intervalSeconds || 5)],
    ['homepage', 'hero_slider_overlay', String(snap.sliderConfig.overlayOpacity || 75)],
    ['seo', 'default_meta_title', s.seoDefaultMetaTitle || DEFAULT_COMPANY_SETTINGS.seoDefaultMetaTitle || ''],
    ['seo', 'default_meta_desc', s.seoDefaultMetaDesc || DEFAULT_COMPANY_SETTINGS.seoDefaultMetaDesc || ''],
    ['seo', 'meta_keywords', s.seoMetaKeywords || DEFAULT_COMPANY_SETTINGS.seoMetaKeywords || ''],
    ['seo', 'seo_author', s.seoAuthor || DEFAULT_COMPANY_SETTINGS.seoAuthor || ''],
    ['seo', 'seo_title_separator', s.seoTitleSeparator || '|'],
    ['seo', 'seo_canonical_base', s.seoCanonicalBase || DEFAULT_COMPANY_SETTINGS.seoCanonicalBase || ''],
    ['seo', 'seo_robots', s.seoRobots || 'index, follow'],
    ['seo', 'seo_googlebot', s.seoGooglebot || 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1'],
    ['seo', 'seo_google_verification', s.seoGoogleVerification || ''],
    ['seo', 'seo_search_console_txt', s.seoSearchConsoleTxt || ''],
    ['seo', 'seo_bing_verification', s.seoBingVerification || ''],
    ['seo', 'seo_yandex_verification', s.seoYandexVerification || ''],
    ['seo', 'seo_fb_domain_verification', s.seoFbDomainVerification || ''],
    ['seo', 'seo_pinterest_verification', s.seoPinterestVerification || ''],
    ['seo', 'seo_og_type', s.seoOgType || 'website'],
    ['seo', 'seo_og_site_name', s.seoOgSiteName || s.legalName || 'PT. Kreatif Global Solusindo'],
    ['seo', 'seo_og_image', s.seoOgImage || ''],
    ['seo', 'seo_twitter_card', s.seoTwitterCard || 'summary_large_image'],
    ['seo', 'seo_twitter_site', s.seoTwitterSite || ''],
    ['seo', 'seo_geo_region', s.seoGeoRegion || 'ID-SS'],
    ['seo', 'seo_geo_placename', s.seoGeoPlacename || 'Palembang, Sumatera Selatan'],
    ['seo', 'seo_geo_position', s.seoGeoPosition || '-2.9718;104.7644'],
    ['seo', 'seo_schema_type', s.seoSchemaType || 'Organization'],
    ['seo', 'seo_schema_founding_year', s.seoSchemaFoundingYear || '1997'],
    ['seo', 'seo_schema_price_range', s.seoSchemaPriceRange || '$$$'],
    ['seo', 'seo_google_analytics_id', s.seoGoogleAnalyticsId || ''],
    ['system', 'inline_overrides_json', JSON.stringify(snap.inlineOverrides || {})]
  ];

  const settingsValuesSql = settingsEntries
    .map(([grp, key, val]) => `(${sqlStr(grp.trim())}, ${sqlStr(key)}, ${sqlStr(val)})`)
    .join(',\n');

  const pagesValuesSql = snap.pages
    .map(
      (p, idx) =>
        `(${sqlStr(p.title)}, ${sqlStr(p.slug)}, ${sqlStr(p.summary)}, ${sqlStr(p.content)}, ${
          p.isPublished ? 1 : 0
        }, ${p.sortOrder || idx + 1})`
    )
    .join(',\n');

  const validMenus = (snap.navigationMenus || INITIAL_NAVIGATION_MENUS).filter(
    m => (m.title || '').trim() !== ''
  );
  const menusSource = validMenus.length > 0 ? validMenus : INITIAL_NAVIGATION_MENUS;

  const menusValuesSql = menusSource
    .map((m, idx) => {
      const cleanTitle = (m.title || '').trim();
      const cleanUrl = normalizePhpMenuUrl(m.url, cleanTitle);
      const loc = m.location === 'footer' ? 'footer' : 'header';
      return `(${idx + 1}, ${sqlStr(cleanTitle)}, ${sqlStr(cleanUrl)}, NULL, ${sqlStr(loc)}, ${sqlStr(loc)}, NULL, ${m.sortOrder || idx + 1}, '_self', ${m.isActive !== false ? 1 : 0})`;
    })
    .join(',\n');

  const slidersValuesSql = (snap.sliderConfig.slides || [])
    .filter(sl => sl.imageUrl && !sl.imageUrl.startsWith('data:image/'))
    .map((sl, idx) => {
      const safeImg =
        sl.imageUrl === 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80'
          ? 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80'
          : sl.imageUrl;
      return `(${idx + 1}, ${sqlStr(sl.title)}, ${sqlStr(sl.subtitle)}, ${sqlStr(
        sl.badgeText || 'Dokumentasi KGS'
      )}, ${sqlStr(safeImg)}, ${sl.sortOrder || idx + 1}, ${sl.isActive ? 1 : 0})`;
    })
    .join(',\n');

  const syncBlock = `

-- ==========================================================
-- SINKRONISASI LIVE EDIT & ADMIN CMS PREVIEW (${snap.buildId})
-- Waktu Ekspor: ${snap.exportedAt}
-- ==========================================================
DELETE FROM \`site_settings\` WHERE \`key_name\` = 'address_jakarta';

INSERT INTO \`site_settings\` (\`group_name\`, \`key_name\`, \`key_value\`) VALUES
${settingsValuesSql}
ON DUPLICATE KEY UPDATE \`key_value\` = VALUES(\`key_value\`);

${
  menusValuesSql
    ? `DELETE FROM \`menus\`;
INSERT INTO \`menus\` (\`id\`, \`title\`, \`url\`, \`page_slug\`, \`location\`, \`menu_location\`, \`parent_id\`, \`sort_order\`, \`target\`, \`is_active\`) VALUES
${menusValuesSql}
ON DUPLICATE KEY UPDATE \`title\` = VALUES(\`title\`), \`url\` = VALUES(\`url\`), \`page_slug\` = VALUES(\`page_slug\`), \`location\` = VALUES(\`location\`), \`menu_location\` = VALUES(\`menu_location\`), \`sort_order\` = VALUES(\`sort_order\`), \`is_active\` = VALUES(\`is_active\`);`
    : ''
}

${
  pagesValuesSql
    ? `INSERT INTO \`pages\` (\`title\`, \`slug\`, \`summary\`, \`content\`, \`is_published\`, \`sort_order\`) VALUES
${pagesValuesSql}
ON DUPLICATE KEY UPDATE \`title\` = VALUES(\`title\`), \`summary\` = VALUES(\`summary\`), \`content\` = VALUES(\`content\`), \`is_published\` = VALUES(\`is_published\`), \`sort_order\` = VALUES(\`sort_order\`);`
    : ''
}

${
  slidersValuesSql
    ? `INSERT INTO \`hero_sliders\` (\`id\`, \`title\`, \`subtitle\`, \`badge_text\`, \`image_url\`, \`sort_order\`, \`is_active\`) VALUES
${slidersValuesSql}
ON DUPLICATE KEY UPDATE \`title\` = VALUES(\`title\`), \`subtitle\` = VALUES(\`subtitle\`), \`badge_text\` = VALUES(\`badge_text\`), \`image_url\` = VALUES(\`image_url\`), \`sort_order\` = VALUES(\`sort_order\`), \`is_active\` = VALUES(\`is_active\`);`
    : ''
}
`;

  // Clean up any trailing SET FOREIGN_KEY_CHECKS = 1; and place it at the very end
  const stripped = baseSql.replace(/SET\s+FOREIGN_KEY_CHECKS\s*=\s*1\s*;\s*$/i, '').trim();
  return `${stripped}\n${syncBlock}\nSET FOREIGN_KEY_CHECKS = 1;\n`;
}

let syncTimeout: ReturnType<typeof setTimeout> | null = null;

export type ExportStatePayload = Partial<LiveExportSnapshot>;

export function setStoredColorMode(mode: ColorMode): boolean {
  try {
    localStorage.setItem('kgs_color_mode', mode);
  } catch {
    // ignore
  }
  return applyColorModeToDocument(mode);
}

export function computeExportBuildId(payload?: ExportStatePayload): string {
  const snap = getCurrentLiveExportSnapshot();
  const s = payload?.companySettings || snap.companySettings;
  const overridesCount = Object.keys(payload?.inlineOverrides || snap.inlineOverrides || {}).length;
  const raw = JSON.stringify({
    name: s.legalName,
    brand: s.footerBrandLabel,
    addr: s.addressPalembang,
    wa: s.whatsapp,
    theme: (payload?.themeConfig || snap.themeConfig)?.presetId,
    overridesCount,
    pagesCount: (payload?.pages || snap.pages)?.length || 0,
    servicesCount: (payload?.services || snap.services)?.length || 0
  });
  let hash = 5381;
  for (let i = 0; i < raw.length; i++) {
    hash = (hash * 33) ^ raw.charCodeAt(i);
  }
  const hex = (hash >>> 0).toString(16).toUpperCase().padStart(8, '0');
  return `KGS-SYNC-${hex}`;
}

export function buildDynamicSqlPatch(payload?: ExportStatePayload): string {
  const baseSnap = getCurrentLiveExportSnapshot();
  const mergedSnap: LiveExportSnapshot = {
    ...baseSnap,
    ...payload,
    buildId: computeExportBuildId(payload),
    companySettings: {
      ...baseSnap.companySettings,
      ...(payload?.companySettings || {})
    }
  };
  return buildDynamicDatabaseSql('', mergedSnap);
}

export async function syncLiveStateToServer(payload?: ExportStatePayload): Promise<boolean> {
  if (typeof window === 'undefined') return false;
  try {
    const baseSnap = getCurrentLiveExportSnapshot();
    const mergedSnap: LiveExportSnapshot = {
      ...baseSnap,
      ...payload,
      buildId: computeExportBuildId(payload),
      companySettings: {
        ...baseSnap.companySettings,
        ...(payload?.companySettings || {})
      }
    };
    const liveJson = buildLivePreviewJson(mergedSnap);
    const sqlPatch = buildDynamicSqlPatch(mergedSnap);
    await fetch('/api/sync-live-export', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        buildId: mergedSnap.buildId,
        liveJson,
        sqlPatch,
        inlineOverrides: mergedSnap.inlineOverrides
      })
    }).catch(() => {});
    return true;
  } catch {
    return false;
  }
}

export function scheduleLiveServerSync(snapshot?: LiveExportSnapshot): void {
  if (typeof window === 'undefined') return;
  if (syncTimeout) clearTimeout(syncTimeout);
  syncTimeout = setTimeout(() => {
    syncLiveStateToServer(snapshot);
  }, 500);
}

