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
  VERIFIED_PARTNERS
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

export function getCurrentLiveExportSnapshot(): LiveExportSnapshot {
  const rawSettings = readJsonFromLocalStorage<Partial<CompanySettings>>(
    'kgs_company_settings',
    DEFAULT_COMPANY_SETTINGS
  );
  const companySettings: CompanySettings = {
    ...DEFAULT_COMPANY_SETTINGS,
    ...rawSettings
  };

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
    navigationMenus: readJsonFromLocalStorage<NavigationMenuItem[]>(
      'kgs_navigation_menus',
      INITIAL_NAVIGATION_MENUS
    ),
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
    short_name: s.footerBrandLabel || 'KGSNET',
    footer_brand_label: s.footerBrandLabel || 'KGSNET',
    tagline: s.slogan || DEFAULT_COMPANY_SETTINGS.slogan,
    company_profile: s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || '',
    footer_description: s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || '',
    footer_contact_heading: s.footerContactHeading || DEFAULT_COMPANY_SETTINGS.footerContactHeading || 'Alamat Kantor & Kontak',
    footer_address_label: s.footerAddressLabel || DEFAULT_COMPANY_SETTINGS.footerAddressLabel || 'Alamat Kantor:',
    footer_nav_heading: s.footerNavHeading || DEFAULT_COMPANY_SETTINGS.footerNavHeading || 'Navigasi Halaman',
    footer_quick_heading: s.footerQuickHeading || DEFAULT_COMPANY_SETTINGS.footerQuickHeading || 'Informasi & Akses Cepat',
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
    hero_slider_layout: snap.sliderConfig.layoutMode,
    hero_slider_autoplay: snap.sliderConfig.autoPlay ? '1' : '0',
    hero_slider_interval: String(snap.sliderConfig.intervalSeconds || 5),
    hero_slider_overlay: String(snap.sliderConfig.overlayOpacity || 75),
    inline_overrides_json: JSON.stringify(snap.inlineOverrides || {})
  };

  if (snap.companyLogo && !snap.companyLogo.startsWith('data:image/')) {
    settingsMap.company_logo = snap.companyLogo;
    settingsMap.logo = snap.companyLogo;
  }
  if (s.footerLogo && !s.footerLogo.startsWith('data:image/')) {
    settingsMap.footer_logo = s.footerLogo;
  }

  return JSON.stringify(
    {
      buildId: snap.buildId,
      exportedAt: snap.exportedAt,
      settings: settingsMap,
      inlineOverrides: snap.inlineOverrides || {},
      pages: snap.pages,
      menus: snap.navigationMenus
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
    ['identity', 'short_name', s.footerBrandLabel || 'KGSNET'],
    ['footer', 'footer_brand_label', s.footerBrandLabel || 'KGSNET'],
    ['identity', 'tagline', s.slogan || DEFAULT_COMPANY_SETTINGS.slogan],
    ['identity', 'company_profile', s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || ''],
    ['footer', 'footer_description', s.footerDescription || DEFAULT_COMPANY_SETTINGS.footerDescription || ''],
    ['footer', 'footer_contact_heading', s.footerContactHeading || DEFAULT_COMPANY_SETTINGS.footerContactHeading || 'Alamat Kantor & Kontak'],
    ['footer', 'footer_address_label', s.footerAddressLabel || DEFAULT_COMPANY_SETTINGS.footerAddressLabel || 'Alamat Kantor:'],
    ['footer', 'footer_nav_heading', s.footerNavHeading || DEFAULT_COMPANY_SETTINGS.footerNavHeading || 'Navigasi Halaman'],
    ['footer', 'footer_quick_heading', s.footerQuickHeading || DEFAULT_COMPANY_SETTINGS.footerQuickHeading || 'Informasi & Akses Cepat'],
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
    [' branding', 'theme_preset', snap.themeConfig.presetId],
    ['branding', 'primary_color', snap.themeConfig.primaryColor],
    ['branding', 'hero_bg_color', snap.themeConfig.heroBgColor],
    ['branding', 'navbar_style', snap.themeConfig.navbarStyle],
    ['homepage', 'hero_slider_layout', snap.sliderConfig.layoutMode],
    ['homepage', 'hero_slider_autoplay', snap.sliderConfig.autoPlay ? '1' : '0'],
    ['homepage', 'hero_slider_interval', String(snap.sliderConfig.intervalSeconds || 5)],
    ['homepage', 'hero_slider_overlay', String(snap.sliderConfig.overlayOpacity || 75)],
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
  pagesValuesSql
    ? `INSERT INTO \`pages\` (\`title\`, \`slug\`, \`summary\`, \`content\`, \`is_published\`, \`sort_order\`) VALUES
${pagesValuesSql}
ON DUPLICATE KEY UPDATE \`title\` = VALUES(\`title\`), \`summary\` = VALUES(\`summary\`), \`content\` = VALUES(\`content\`), \`is_published\` = VALUES(\`is_published\`), \`sort_order\` = VALUES(\`sort_order\`);`
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

