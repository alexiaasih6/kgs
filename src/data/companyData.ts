export interface ServiceCategory {
  id: string;
  name: string;
  segmentNumber: number;
  description: string;
  pdfPage: number;
}

export interface ServiceItem {
  id: string;
  categoryId: string;
  categoryName: string;
  name: string;
  slug: string;
  summary: string;
  description: string;
  features: string[];
  brands: string[];
  targetSectors: string[];
  pdfPage: number;
  verificationStatus: 'Terverifikasi dari PDF' | 'Standar Resmi KGS' | 'Perlu Konfirmasi' | 'Tambahan Disetujui Admin';
  adminNotes: string;
  isPublished: boolean;
  order: number;
  imageUrl?: string;
}

export interface ClientItem {
  id: string;
  name: string;
  category: string;
  location?: string;
  serviceCategory: string;
  pdfPage: number;
  imageUrl?: string;
  logoUrl?: string;
  websiteUrl?: string;
  description?: string;
}

export interface PortfolioImageItem {
  id: string;
  imageUrl: string;
  caption: string;
  altText?: string;
  sortOrder: number;
  isPrimary?: boolean;
}

export interface ProjectPhoto {
  id: string;
  title: string;
  clientOrLocation: string;
  description: string;
  category: string;
  pdfPage?: number;
  imageUrl?: string;
  images?: PortfolioImageItem[];
}

export interface CertificationItem {
  id: string;
  title: string;
  issuerOrBrand: string;
  holder: string;
  yearOrValidity?: string;
  pdfPage: number;
  badgeCode?: string;
  description?: string;
  logoUrl?: string;
  linkUrl?: string;
}

export interface PartnerItem {
  id: string;
  name: string;
  role: string;
  serviceType: string;
  pdfPage: number;
  logoUrl?: string;
  linkUrl?: string;
}

export interface FooterCertBadgeItem {
  label: string;
  url: string;
  logo: string;
}

export function parseFooterCertBadges(raw?: string): FooterCertBadgeItem[] {
  const source =
    (raw || '').trim() ||
    'APJII Official Member :: https://apjii.or.id | KGSNet ISP Resmi :: https://www.kgs.net.id | Panasonic Authorized | NETVIEL Certified';
  const chunks = source
    .split(/[|\n]+/)
    .map(c => c.trim())
    .filter(Boolean);
  const items: FooterCertBadgeItem[] = [];
  for (const chunk of chunks) {
    const parts = chunk.split('::').map(p => p.trim());
    const label = parts[0] || '';
    if (!label) continue;
    items.push({
      label,
      url: parts[1] || '',
      logo: parts[2] || ''
    });
  }
  return items;
}

export function serializeFooterCertBadges(items: FooterCertBadgeItem[]): string {
  return items
    .filter(i => i.label.trim() !== '')
    .map(i => {
      const label = i.label.trim();
      const url = (i.url || '').trim();
      const logo = (i.logo || '').trim();
      if (logo) return `${label} :: ${url || '#'} :: ${logo}`;
      if (url) return `${label} :: ${url}`;
      return label;
    })
    .join(' | ');
}

export interface InquiryNote {
  id: string;
  note: string;
  createdAt: string;
}

export interface InquiryMessage {
  id: string;
  name: string;
  company: string;
  email: string;
  phone: string;
  serviceId: string;
  location: string;
  message: string;
  createdAt: string;
  status: 'Baru' | 'Diproses' | 'Selesai';
  notes?: InquiryNote[];
}

export interface ArticleItem {
  id: string;
  title: string;
  slug: string;
  categoryId: string;
  categoryName: string;
  summary: string;
  content: string;
  author: string;
  isFeatured: boolean;
  status: 'published' | 'draft';
  views: number;
  createdAt: string;
  metaTitle?: string;
  metaDescription?: string;
  imageUrl?: string;
  labels?: string[];
  readTime?: string;
}

export interface StaticPageItem {
  id: string;
  title: string;
  slug: string;
  summary: string;
  content: string;
  isPublished: boolean;
  sortOrder: number;
  updatedAt: string;
  imageUrl?: string;
}

export interface AdminUserItem {
  id: string;
  username: string;
  name: string;
  email: string;
  role: 'superadmin' | 'editor';
  status: 'active' | 'inactive';
  lastLogin: string;
}

export interface AuditLogItem {
  id: string;
  username: string;
  action: string;
  details: string;
  ipAddress: string;
  createdAt: string;
}

export interface HeroStatItem {
  value: string;
  label: string;
}

export interface HeroNocNodeItem {
  title: string;
  subtitle: string;
  statusBadge: string;
}

export interface CompanySettings {
  legalName: string;
  slogan: string;
  companyNameColor?: string;
  sloganColor?: string;
  logoBgColor?: string;
  logoTextColor?: string;
  phonePalembang: string;
  whatsapp: string;
  email: string;
  addressPalembang: string;
  addressJakarta?: string;
  googleMapInput: string;
  vision: string;
  mission: string;
  culture?: string;
  commitment?: string;
  websiteCorporate?: string;
  websiteIsp?: string;
  operationalHours?: string;
  footerLogo?: string;
  footerBrandLabel?: string;
  footerDescription?: string;
  footerContactHeading?: string;
  footerAddressLabel?: string;
  footerNavHeading?: string;
  footerQuickHeading?: string;
  footerCertHeading?: string;
  footerCertBadges?: string;
  footerCertCardTitle?: string;
  footerCertCardStatus?: string;
  footerShowCertSection?: boolean;
  footerShowCertStatus?: boolean;
  footerShowCertBadges?: boolean;
  copyrightText?: string;
  heroBadgeText?: string;
  heroCtaPrimary?: string;
  heroCtaSecondary?: string;
  heroCtaTertiary?: string;
  heroHighlights?: string[];
  heroStats?: HeroStatItem[];
  nocStatusLabel?: string;
  nocCardTitle?: string;
  nocCoverageText?: string;
  nocNodes?: HeroNocNodeItem[];
  pricingBannerKicker?: string;
  pricingBannerTitle?: string;
  pricingBannerDesc?: string;
  pricingBannerCta?: string;
  faqSupportTitle?: string;
  faqSupportDesc?: string;
  // Global SEO & Website Ownership Verification (Google Search Console HTML & TXT, Bing, Yandex, Meta, Schema.org)
  seoDefaultMetaTitle?: string;
  seoDefaultMetaDesc?: string;
  seoMetaKeywords?: string;
  seoAuthor?: string;
  seoTitleSeparator?: string;
  seoCanonicalBase?: string;
  seoRobots?: string;
  seoGooglebot?: string;
  seoGoogleVerification?: string;
  seoSearchConsoleTxt?: string;
  seoBingVerification?: string;
  seoYandexVerification?: string;
  seoFbDomainVerification?: string;
  seoPinterestVerification?: string;
  seoOgType?: string;
  seoOgSiteName?: string;
  seoOgImage?: string;
  seoTwitterCard?: string;
  seoTwitterSite?: string;
  seoGeoRegion?: string;
  seoGeoPlacename?: string;
  seoGeoPosition?: string;
  seoSchemaType?: string;
  seoSchemaFoundingYear?: string;
  seoSchemaPriceRange?: string;
  seoGoogleAnalyticsId?: string;
  // Social Media Links in Footer ("Alamat Kantor & Kontak")
  socialFacebook?: string;
  socialInstagram?: string;
  socialLinkedin?: string;
  socialYoutube?: string;
  socialTiktok?: string;
  socialWhatsapp?: string;
  socialLinks?: SocialMediaLinkItem[];
  // Footer Quick Access Links ("Informasi & Akses Cepat")
  footerQuick1Label?: string;
  footerQuick1Url?: string;
  footerQuick2Label?: string;
  footerQuick2Url?: string;
  footerQuick3Label?: string;
  footerQuick3Url?: string;
  footerQuick4Label?: string;
  footerQuick4Url?: string;
  footerQuick5Label?: string;
  footerQuick5Url?: string;
}

export interface SocialMediaLinkItem {
  id: string;
  platform: string;
  url: string;
  isActive: boolean;
  sortOrder: number;
}

export function getEffectiveSocialLinks(settings: CompanySettings): SocialMediaLinkItem[] {
  const waRaw = (settings.whatsapp || '0811-782119').replace(/\D/g, '').replace(/^0/, '62');
  const defaultMap: Record<string, string> = {
    facebook: 'https://facebook.com/kgs.palembang',
    instagram: 'https://instagram.com/kgs.palembang',
    linkedin: 'https://linkedin.com/company/kreatif-global-solusindo',
    youtube: 'https://youtube.com/@kreatifglobalsolusindo',
    tiktok: 'https://tiktok.com/@kgs.palembang',
    whatsapp: `https://wa.me/${waRaw}`
  };

  if (Array.isArray(settings.socialLinks) && settings.socialLinks.length > 0) {
    return [...settings.socialLinks]
      .map((item, idx) => {
        const key = (item.platform || '').trim().toLowerCase();
        const rawUrl = (item.url || '').trim();
        const resolvedUrl =
          rawUrl && rawUrl !== '#' && /^https?:\/\//i.test(rawUrl)
            ? rawUrl
            : defaultMap[key] || 'https://www.creative.co.id';
        return {
          ...item,
          url: resolvedUrl,
          isActive: item.isActive !== false,
          sortOrder: item.sortOrder || idx + 1
        };
      })
      .sort((a, b) => (a.sortOrder || 99) - (b.sortOrder || 99));
  }

  const pickUrl = (val: string | undefined, fallback: string) => {
    const clean = (val || '').trim();
    return clean && clean !== '#' && /^https?:\/\//i.test(clean) ? clean : fallback;
  };

  return [
    {
      id: 'soc-fb',
      platform: 'Facebook',
      url: pickUrl(settings.socialFacebook, defaultMap.facebook),
      isActive: true,
      sortOrder: 1,
    },
    {
      id: 'soc-ig',
      platform: 'Instagram',
      url: pickUrl(settings.socialInstagram, defaultMap.instagram),
      isActive: true,
      sortOrder: 2,
    },
    {
      id: 'soc-in',
      platform: 'LinkedIn',
      url: pickUrl(settings.socialLinkedin, defaultMap.linkedin),
      isActive: true,
      sortOrder: 3,
    },
    {
      id: 'soc-yt',
      platform: 'YouTube',
      url: pickUrl(settings.socialYoutube, defaultMap.youtube),
      isActive: true,
      sortOrder: 4,
    },
    {
      id: 'soc-tt',
      platform: 'TikTok',
      url: pickUrl(settings.socialTiktok, defaultMap.tiktok),
      isActive: true,
      sortOrder: 5,
    },
    {
      id: 'soc-wa',
      platform: 'WhatsApp',
      url: pickUrl(settings.socialWhatsapp, defaultMap.whatsapp),
      isActive: true,
      sortOrder: 6,
    },
  ];
}


export const COMPANY_INFO = {
  legalName: "PT. KREATIF GLOBAL SOLUSINDO",
  shortName: "PT. KGS",
  brandName: "Creative Com / KGS",
  slogan: "System Integrator, Security Solution & Internet Provider",
  businessFocus: "System Integrator, Security Solution & Internet Provider",
  ispProduct: "KGSNet",
  apjiiMember: true,
  establishedYear: 1997,
  ptIncorporationYear: 2013,
  ispLicenseYear: 2020,
  officePalembang: {
    address: "Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113",
    phone: "0711-316088",
    whatsapp: "0811-782119",
    whatsappRaw: "62811782119",
    email: "info@creative.co.id",
    website: "www.creative.co.id / www.kgs.net.id"
  },
  profile: "Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.",
  vision: "Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.",
  mission: "Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.",
  culture: "Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.",
  commitment: "Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.",
  clientPortfolioIntro1: "Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.",
  clientPortfolioIntro2: "Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;"
};

export const ISP_LEGALITAS = [
  "Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020,",
  "Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020,",
  "NIB (Nomor Induk Berusaha) ; 9120202100598."
];

export interface IspSegmentTier {
  speed: string;
  price: string;
  note: string;
}

export interface IspSegmentGroup {
  id: string;
  name: string;
  badge: string;
  description: string;
  tiers: IspSegmentTier[];
}

export const ISP_SEGMENTS_DATA: IspSegmentGroup[] = [
  {
    id: "isp-dedicated",
    name: "Internet Dedicated",
    badge: "Segmen 1 · Dedicated 1:1",
    description: "Layanan internet dedicated simetris dengan jaminan SLA tinggi untuk UMKM, Sekolah, dan Corporate.",
    tiers: [
      { speed: "10 Mbps", price: "Harga Perbulan mulai 3.500.000", note: "Kebutuhan UMKM" },
      { speed: "10 Mbps", price: "Harga Perbulan mulai 1.750.000", note: "Kebutuhan Sekolah" },
      { speed: "10 Mbps", price: "Harga Perbulan mulai 1.750.000", note: "Kebutuhan Corporate" }
    ]
  },
  {
    id: "isp-bisnis",
    name: "Internet Bisnis",
    badge: "Segmen 2 · Business Broadband",
    description: "Koneksi internet cepat dan stabil untuk menunjang produktivitas operasional bisnis dan kantor.",
    tiers: [
      { speed: "10 Mbps", price: "Rp. 407.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "20 Mbps", price: "Rp. 431.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "30 Mbps", price: "Rp. 453.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "40 Mbps", price: "Rp. 477.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "50 Mbps", price: "Rp. 511.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "100 Mbps", price: "Rp. 1.050.000 / bulan", note: "Paket Internet Bisnis" },
      { speed: "200 Mbps", price: "Rp. 1.560.000 / bulan", note: "Paket Internet Bisnis" }
    ]
  },
  {
    id: "isp-home",
    name: "Home Internet",
    badge: "Segmen 3 · Residential & Home",
    description: "Koneksi internet rumah tangga tanpa kuota yang nyaman untuk streaming, belajar, dan kerja dari rumah.",
    tiers: [
      { speed: "10 Mbps", price: "Rp. 252.000 / bulan", note: "Optiman di 5 Perangkat" },
      { speed: "20 Mbps", price: "Rp. 276.000 / bulan", note: "Optiman di 7 Perangkat" },
      { speed: "30 Mbps", price: "Rp. 298.000 / bulan", note: "Optiman di 9 Perangkat" },
      { speed: "50 Mbps", price: "Rp. 450.000 / bulan", note: "Optiman di 12 Perangkat" },
      { speed: "100 Mbps", price: "Rp. 710.000 / bulan", note: "Optiman di 15 Perangkat" },
      { speed: "200 Mbps", price: "Rp. 1.100.000 / bulan", note: "Optiman di 20 Perangkat" }
    ]
  }
];

export const DEFAULT_COMPANY_SETTINGS: CompanySettings = {
  legalName: COMPANY_INFO.legalName,
  slogan: COMPANY_INFO.slogan,
  companyNameColor: '#0f172a',
  sloganColor: '#2563eb',
  logoBgColor: '#2563eb',
  logoTextColor: '#ffffff',
  phonePalembang: COMPANY_INFO.officePalembang.phone,
  whatsapp: COMPANY_INFO.officePalembang.whatsapp,
  email: COMPANY_INFO.officePalembang.email,
  addressPalembang: COMPANY_INFO.officePalembang.address,
  googleMapInput: "Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113",
  vision: COMPANY_INFO.vision,
  mission: COMPANY_INFO.mission,
  culture: COMPANY_INFO.culture,
  commitment: COMPANY_INFO.commitment,
  websiteCorporate: "www.creative.co.id",
  websiteIsp: "www.kgs.net.id",
  operationalHours: "Senin - Sabtu: 08:00 - 17:00 WIB (NOC 24/7)",
  footerLogo: "",
  footerBrandLabel: "KGSNET",
  footerDescription: "Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.",
  footerContactHeading: "Alamat Kantor & Kontak",
  footerAddressLabel: "Alamat Kantor:",
  footerNavHeading: "Navigasi Halaman",
  footerQuickHeading: "Informasi & Akses Cepat",
  footerCertHeading: "Lisensi, Sertifikasi & Keanggotaan",
  footerCertBadges: "APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified",
  footerCertCardTitle: "Sertifikasi & Keanggotaan Resmi",
  footerCertCardStatus: "✓ Terverifikasi & Berlisensi Resmi",
  footerShowCertSection: true,
  footerShowCertStatus: true,
  footerShowCertBadges: true,
  copyrightText: "© 2026 KGSNET — Creative.co.id. All Rights Reserved.",
  heroBadgeText: "PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP",
  heroCtaPrimary: "Konsultasi Sekarang",
  heroCtaSecondary: "Lihat Paket Internet",
  heroCtaTertiary: "Product & Service →",
  heroHighlights: [
    "Anggota APJII",
    "Kominfo 674/TEL.02.02/2020",
    "SKLO 375/TEL.04.02/2020",
    "NIB 9120202100598"
  ],
  heroStats: [
    { value: "1997", label: "Awal Berdiri (Creative Com)" },
    { value: "5 Kategori", label: "Product & Service Terintegrasi" },
    { value: "100+", label: "Klien BUMN, Swasta & Instansi" },
    { value: "3 Segmen", label: "Dedicated, Bisnis & Home ISP" }
  ],
  nocStatusLabel: "KGS NOC OPERATIONS ONLINE",
  nocCardTitle: "KGSNet Backbone & Router BGP",
  nocCoverageText: "Sumatera, Kalimantan & Kawasan Industri Cikarang",
  nocNodes: [
    {
      title: "BGP Peering Neucentrix Data Center",
      subtitle: "Fiber Optic Redundant Metro E-Link",
      statusBadge: "Active"
    },
    {
      title: "Wireless Backbone & Remote Repeater",
      subtitle: "Jangkauan Site Perkebunan & Tambang",
      statusBadge: "Linked"
    },
    {
      title: "Pabrikasi Tower & Penangkal Petir",
      subtitle: "Workshop internal & Uji Tahanan < 2Ω",
      statusBadge: "Certified"
    }
  ],
  pricingBannerKicker: "Kebutuhan Tender & Spesifikasi Kustom",
  pricingBannerTitle: "Butuh Penyusunan Bill of Quantity (BoQ) & Kerangka Acuan Kerja (KAK)?",
  pricingBannerDesc: "Tim engineer bersertifikasi PT. KGS siap melakukan survei lokasi on-site gratis (uji Line-of-Sight, grounding test tanah, dan topologi FO) untuk pengadaan resmi instansi Anda.",
  pricingBannerCta: "Jadwalkan Survei & Konsultasi",
  faqSupportTitle: "Punya pertanyaan teknis lain yang belum tercantum?",
  faqSupportDesc: "Tim support & sales engineering KGS siap berdiskusi secara langsung melalui telepon kantor atau WhatsApp resmi.",
  seoDefaultMetaTitle: "PT. Kreatif Global Solusindo - System Integrator, Security Solution & Internet Provider",
  seoDefaultMetaDesc: "Creative Com / PT. Kreatif Global Solusindo: System Integrator, Security Solution (Fire & Alarm Security, CCTV, Access Door, Video Door Phone), Networking, PBX, Telecommunication, Tower & Internet Service Provider (KGSNet) di Palembang.",
  seoMetaKeywords: "ISP Palembang, KGSNet, Kontraktor Tower Palembang, Penangkal Petir Grounding, CCTV Palembang, Fiber Optik, PABX Panasonic, PT Kreatif Global Solusindo",
  seoAuthor: "PT. Kreatif Global Solusindo (KGSNet)",
  seoTitleSeparator: "|",
  seoCanonicalBase: "https://www.kgs.net.id",
  seoRobots: "index, follow",
  seoGooglebot: "index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1",
  seoGoogleVerification: "",
  seoSearchConsoleTxt: "",
  seoBingVerification: "",
  seoYandexVerification: "",
  seoFbDomainVerification: "",
  seoPinterestVerification: "",
  seoOgType: "website",
  seoOgSiteName: "PT. Kreatif Global Solusindo (KGSNet)",
  seoOgImage: "",
  seoTwitterCard: "summary_large_image",
  seoTwitterSite: "@kgsnet_id",
  seoGeoRegion: "ID-SS",
  seoGeoPlacename: "Palembang, Sumatera Selatan",
  seoGeoPosition: "-2.9718;104.7644",
  seoSchemaType: "Organization",
  seoSchemaFoundingYear: "1997",
  seoSchemaPriceRange: "$$$",
  seoGoogleAnalyticsId: "",
  socialFacebook: "https://facebook.com/kgs.palembang",
  socialInstagram: "https://instagram.com/kgs.palembang",
  socialLinkedin: "https://linkedin.com/company/kreatif-global-solusindo",
  socialYoutube: "https://youtube.com/@kreatifglobalsolusindo",
  socialTiktok: "https://tiktok.com/@kgs.palembang",
  footerQuick1Label: "Kebijakan Privasi & SLA",
  footerQuick1Url: "kebijakan-privasi.php",
  footerQuick2Label: "3 Segmen Paket Internet ISP",
  footerQuick2Url: "harga.php",
  footerQuick3Label: "Pertanyaan Umum (FAQ)",
  footerQuick3Url: "faq.php",
  footerQuick4Label: "Minta Penawaran / Survei",
  footerQuick4Url: "kontak.php#konsultasi",
  footerQuick5Label: "Peta Lokasi Google Maps",
  footerQuick5Url: "kontak.php#peta-lokasi"
};

export function formatWhatsAppNumber(rawPhone: string): string {
  const digits = (rawPhone || COMPANY_INFO.officePalembang.whatsapp).replace(/\D/g, '');
  if (!digits) return COMPANY_INFO.officePalembang.whatsappRaw;
  if (digits.startsWith('0')) {
    return '62' + digits.slice(1);
  }
  if (digits.startsWith('8')) {
    return '62' + digits;
  }
  return digits;
}

export function buildGoogleMapEmbedUrl(input?: string): string {
  const raw = (input || DEFAULT_COMPANY_SETTINGS.googleMapInput).trim();
  if (!raw) {
    return `https://maps.google.com/maps?q=${encodeURIComponent(DEFAULT_COMPANY_SETTINGS.googleMapInput)}&t=&z=16&ie=UTF8&iwloc=&output=embed`;
  }

  // 1. If user pasted full <iframe src="..."> tag
  const iframeMatch = raw.match(/src=["']([^"']+)["']/i);
  if (iframeMatch && iframeMatch[1]) {
    return iframeMatch[1];
  }

  // 2. If user pasted an already valid Google Maps embed URL
  if (raw.startsWith('https://www.google.com/maps/embed') || raw.includes('output=embed')) {
    return raw;
  }

  // 3. If user pasted a standard Google Maps link with @lat,lng or /place/
  const placeMatch = raw.match(/\/maps\/place\/([^/@?]+)/i);
  const coordMatch = raw.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
  if (placeMatch && placeMatch[1]) {
    const decodedPlace = decodeURIComponent(placeMatch[1].replace(/\+/g, ' '));
    return `https://maps.google.com/maps?q=${encodeURIComponent(decodedPlace)}&t=&z=16&ie=UTF8&iwloc=&output=embed`;
  }
  if (coordMatch) {
    const coords = `${coordMatch[1]},${coordMatch[2]}`;
    return `https://maps.google.com/maps?q=${encodeURIComponent(coords)}&t=&z=16&ie=UTF8&iwloc=&output=embed`;
  }

  // 4. Otherwise treat as Place Name, Address, or Lat,Long Coordinates (e.g. "-2.9718, 104.7644")
  return `https://maps.google.com/maps?q=${encodeURIComponent(raw)}&t=&z=16&ie=UTF8&iwloc=&output=embed`;
}

export function buildGoogleMapDirectLink(input?: string): string {
  const raw = (input || DEFAULT_COMPANY_SETTINGS.googleMapInput).trim();
  if (raw.startsWith('http://') || raw.startsWith('https://')) {
    if (!raw.includes('output=embed')) return raw;
  }
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(raw || DEFAULT_COMPANY_SETTINGS.googleMapInput)}`;
}

export const SERVICE_CATEGORIES: ServiceCategory[] = [
  {
    id: "cat_networking",
    segmentNumber: 1,
    name: "Data Networking",
    description: "Perancangan, instalasi, dan manajemen infrastruktur jaringan komputer (LAN/WAN), penarikan & terminasi kabel Fiber Optic (OTDR), konfigurasi Router/Switch Enterprise (MikroTik/Cisco), serta interkoneksi Wireless Point-to-Point & Point-to-Multipoint antar gedung, pabrik, dan kebun.",
    pdfPage: 4
  },
  {
    id: "cat_radio",
    segmentNumber: 2,
    name: "Radio Komunikasi (Repeater, HT, RIG)",
    description: "Penyediaan, instalasi, dan tuning frekuensi perangkat Radio Komunikasi dua arah: Stasiun Pengulang Sinyal (Repeater VHF/UHF), Handy Talky (HT), Base/Mobile RIG, radio SSB, Marine Radio/AIS, beserta fabrikasi & ereksi Tower Triangle/Monopole dan sistem Penangkal Petir (< 2 Ohm).",
    pdfPage: 4
  },
  {
    id: "cat_pabx",
    segmentNumber: 3,
    name: "PABX",
    description: "Instalasi, pemrograman, dan peremajaan sistem sentral telekomunikasi telepon kantor dan korporasi meliputi Analogue PBX, Hybrid PBX, hingga IP-PBX berbasis SIP/VoIP antar cabang beserta Telephone Billing System.",
    pdfPage: 4
  },
  {
    id: "cat_cctv",
    segmentNumber: 4,
    name: "CCTV",
    description: "Pemasangan dan integrasi sistem kamera pengawas keamanan (Closed Circuit Television) Analog HD maupun IP Network Camera beresolusi tinggi, PTZ Camera, NVR/DVR Central Monitoring Room, serta proteksi Arrester CCTV outdoor & indoor.",
    pdfPage: 4
  },
  {
    id: "cat_access",
    segmentNumber: 5,
    name: "Access Door",
    description: "Sistem pengaman dan pembatas akses pintu elektronik (Door Access Control) berbasis Biometrik Sidik Jari, Face Recognition, Kartu RFID Proximity/Mifare, PIN Keypad, Magnetic Lock/Drop Bolt, serta interkom Video Door Phone.",
    pdfPage: 4
  },
  {
    id: "cat_fire",
    segmentNumber: 6,
    name: "Fire Alarm, Public Address & Nursecall",
    description: "Instalasi sistem deteksi dini bahaya kebakaran (MCFA, Smoke & Heat Detector, Hydrant/Bell), tata suara pengumuman & evakuasi gedung (Public Address / Paging Sound System & Conference), serta sistem panggilan darurat perawat rumah sakit (Nurse Call System).",
    pdfPage: 4
  },
  {
    id: "cat_isp",
    segmentNumber: 7,
    name: "Internet Service Provider",
    description: "Layanan koneksi internet berizin resmi Kominfo RI & APJII (KGSNet) untuk 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet). KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia.",
    pdfPage: 4
  }
];

// Complete inventory of KGS Official Product & Service grouped into the 7 Bidang Pekerjaan
export const SERVICES_DATA: ServiceItem[] = [
  // 1. Data Networking
  {
    id: "srv-net-01",
    categoryId: "cat_networking",
    categoryName: "Data Networking",
    name: "Infrastruktur LAN, WAN & Fiber Optic Backbone",
    slug: "infrastruktur-lan-wan-fiber-optic",
    summary: "Instalasi jaringan kabel terstruktur UTP Cat6/Cat6A dan penarikan Fiber Optic Singlemode/Multimode lengkap dengan splicing & uji OTDR.",
    description: "Layanan perancangan dan implementasi infrastruktur jaringan data terpadu untuk gedung bertingkat, rumah sakit, kampus, dan kawasan pabrik industri. Mencakup penarikan kabel Fiber Optic udara/tanah, pemasangan OTB/Patch Panel, terminasi fusion splicing, pengujian redaman OTDR, serta penataan Rack Server.",
    features: [
      "Penarikan & terminasi Fiber Optic Backbone (Splicing & OTDR Test)",
      "Structured Cabling LAN UTP/STP Cat6 & Cat6A bersertifikasi NETVIEL",
      "Instalasi Rack Server, Patch Panel & Manajemen Kabel Rapi",
      "Dokumentasi topologi jaringan & Fluke/LAN Qualification Test"
    ],
    brands: ["NETVIEL", "Beldon", "CommScope", "AMP", "MikroTik", "Cisco"],
    targetSectors: ["Pabrik & Kawasan Industri", "Rumah Sakit", "Kampus & Sekolah", "Perbankan & Instansi"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 1 - Data Networking",
    isPublished: true,
    order: 1
  },
  {
    id: "srv-net-02",
    categoryId: "cat_networking",
    categoryName: "Data Networking",
    name: "Router Enterprise, Wireless PTP/PTMP & Wi-Fi",
    slug: "router-wireless-enterprise",
    summary: "Konfigurasi Router (BGP/OSPF, Firewall, QoS, VPN) dan transmisi radio Wireless Point-to-Point jarak jauh antar site.",
    description: "Solusi manajemen trafik jaringan oleh engineer bersertifikasi MTCNA serta pembangunan link transmisi nirkabel (Wireless Point-to-Point & Point-to-Multipoint) untuk menghubungkan kantor pusat dengan pabrik atau kebun di lokasi remote.",
    features: [
      "Manajemen bandwidth (QoS), VLAN, Load Balancing & Failover Multi-ISP",
      "Wireless Point-to-Point (PTP) & Point-to-Multipoint jarak jauh",
      "Distribusi Access Point Wi-Fi Indoor/Outdoor Seamless Roaming",
      "Interkoneksi VPN aman antar kantor cabang & site operasional"
    ],
    brands: ["MikroTik", "Ubiquiti", "Cambium Networks", "Cisco", "Ruijie", "TP-Link"],
    targetSectors: ["Perkebunan & Tambang", "Perkantoran Multi-Cabang", "Perhotelan", "Pelabuhan"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 1 - Data Networking",
    isPublished: true,
    order: 2
  },

  // 2. Radio Komunikasi (Repeater, HT, RIG)
  {
    id: "srv-rad-01",
    categoryId: "cat_radio",
    categoryName: "Radio Komunikasi (Repeater, HT, RIG)",
    name: "Radio Komunikasi VHF/UHF/SSB (Repeater, HT, RIG)",
    slug: "radio-komunikasi-repeater-ht-rig",
    summary: "Pengadaan, instalasi & tuning stasiun Repeater VHF/UHF, Handy Talky (HT), Base/Mobile RIG, radio SSB, serta Marine Radio & AIS.",
    description: "Solusi komunikasi suara dua arah yang tangguh untuk koordinasi operasional di area perkebunan kelapa sawit, pertambangan batubara, pembangkit listrik, dan alur pelayaran sungai/laut. Mencakup survei propagasi sinyal, instalasi Repeater di puncak bukit/tower, hingga pemrograman frekuensi HT dan RIG.",
    features: [
      "Instalasi Stasiun Pengulang Sinyal (Repeater VHF/UHF) & Cavity Duplexer",
      "Pengadaan & pemrograman Handy Talky (HT) dan Base/Mobile RIG",
      "Instalasi antena Hustler/Omni, kabel Heliax low-loss & tuning VSWR",
      "Sistem AIS (Automatic Identification System) & Radio Navigasi Maritim"
    ],
    brands: ["Motorola", "ICOM", "Kenwood", "Alinco", "Hytera", "Furuno"],
    targetSectors: ["Perkebunan Kelapa Sawit", "Pertambangan Batubara", "Pelayaran & Kepelabuhanan", "PLN & Migas"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 2 - Radio Komunikasi",
    isPublished: true,
    order: 3
  },
  {
    id: "srv-rad-02",
    categoryId: "cat_radio",
    categoryName: "Radio Komunikasi (Repeater, HT, RIG)",
    name: "Fabrikasi Tower (Guy Wire & Monopole) & Penangkal Petir",
    slug: "fabrikasi-tower-dan-penangkal-petir",
    summary: "Pabrikasi mandiri & ereksi Guy Wire Tower, Self Supporting Tower Monopole, serta instalasi Penangkal Petir & Grounding < 2 Ohm.",
    description: "Dukungan infrastruktur menara antena untuk Radio Komunikasi dan Wireless ISP yang diproduksi langsung di workshop internal KGS Palembang, dilengkapi sistem proteksi petir eksternal, Surge Arrester, dan sumur grounding dengan garansi nilai tahanan tanah di bawah 2 Ohm.",
    features: [
      "Fabrikasi & ereksi Guy Wire Tower segitiga & Self Supporting Tower Monopole",
      "Instalasi Penangkal Petir Elektrostatis & Konvensional radius luas",
      "Pengeboran sumur grounding tembaga dengan uji Earth Tester (< 2 Ohm)",
      "Pemasangan Coaxial Surge Arrester & Arrester Panel MDP"
    ],
    brands: ["KGS Workshop Fabrication", "KURN", "VIKING", "ERICO", "OBO Bettermann"],
    targetSectors: ["Site Perkebunan & Tambang", "Pembangkit Listrik", "Rumah Sakit", "Instansi Pemerintah"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 2 - Radio Komunikasi & Tower",
    isPublished: true,
    order: 4
  },

  // 3. PABX
  {
    id: "srv-pbx-01",
    categoryId: "cat_pabx",
    categoryName: "PABX",
    name: "Analogue PBX, Hybrid PBX & IP-PBX System",
    slug: "analogue-hybrid-ip-pbx-system",
    summary: "Instalasi dan pemrograman sentral telepon Analogue PBX, Hybrid PBX, dan IP-PBX (SIP/VoIP) beserta Telephone Billing System.",
    description: "Sebagai Panasonic Authorized Dealer di Palembang sejak 2002, PT. Kreatif Global Solusindo melayani perancangan, instalasi jaringan kabel telepon, pemrograman sistem PABX (Analogue, Digital Hybrid, hingga IP-PBX multi-cabang), IVR Auto Attendant, Voice Mail, serta integrasi software billing perhotelan dan kantor.",
    features: [
      "Instalasi Analogue PBX & Hybrid PBX untuk kantor, hotel, dan rumah sakit",
      "Implementasi IP-PBX & VoIP Gateway untuk komunikasi gratis antar cabang",
      "Konfigurasi Auto Attendant (DISA/IVR), Call Recording & Conference Call",
      "Integrasi Telephone Billing System & kontrak pemeliharaan berkala"
    ],
    brands: ["Panasonic (Authorized Dealer)", "NEC", "Yeastar", "Grandstream", "DINSTAR"],
    targetSectors: ["Perhotelan", "Rumah Sakit", "Perbankan", "Instansi Pemerintah & BUMN"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 3 - PABX",
    isPublished: true,
    order: 5
  },

  // 4. CCTV
  {
    id: "srv-cctv-01",
    categoryId: "cat_cctv",
    categoryName: "CCTV",
    name: "CCTV Analog HD, IP Camera & Central Monitoring System",
    slug: "cctv-surveillance-system",
    summary: "Pemasangan kamera pengawas CCTV Indoor/Outdoor, PTZ Camera, NVR/DVR, Fiber Optic CCTV Link, dan proteksi Arrester CCTV.",
    description: "Solusi pengawasan visual 24 jam untuk pabrik, gudang, perbankan, rumah sakit, jalan raya, hingga pelabuhan. Didukung teknisi berpengalaman dalam penarikan jalur kabel Coaxial/UTP/Fiber Optic, pemasangan tiang kamera outdoor lengkap dengan Video & Power Surge Arrester, serta konfigurasi pemantauan terpusat via layar Control Room dan Smartphone.",
    features: [
      "Instalasi IP Camera beresolusi tinggi (2MP/4MP/8MP 4K) & PTZ Speed Dome",
      "Konfigurasi NVR/DVR penyimpanan berkapasitas besar & Video Wall CMS",
      "Pemasangan Video & Power Arrester pelindung kamera outdoor dari induksi petir",
      "Akses pantauan real-time multi-lokasi melalui aplikasi Mobile & PC"
    ],
    brands: ["Sony (Authorized Dealer)", "Hikvision", "Dahua", "Panasonic", "BOSCH", "Honeywell"],
    targetSectors: ["Pabrik & Kawasan Industri", "Perbankan", "Rumah Sakit", "Retail & Perhotelan"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 4 - CCTV",
    isPublished: true,
    order: 6
  },

  // 5. Access Door
  {
    id: "srv-acc-01",
    categoryId: "cat_access",
    categoryName: "Access Door",
    name: "Access Door Control Biometrik, RFID & Video Door Phone",
    slug: "access-door-control-system",
    summary: "Sistem pengaman akses pintu elektronik menggunakan Sidik Jari, Face Recognition, Kartu RFID, PIN, Magnetic Lock, dan Video Door Phone.",
    description: "Pengamanan akses ruangan khusus (ruang server, ruang brankas bank, laboratorium, ruang direksi, dan kamar hotel) menggunakan pengunci elektromagnetik (Magnetic Lock / Drop Bolt) yang terintegrasi dengan pembaca biometrik wajah/sidik jari, kartu pintar RFID, maupun interkom Video Door Phone dua arah.",
    features: [
      "Autentikasi Biometrik Wajah (Face Recognition), Sidik Jari, RFID & PIN",
      "Magnetic Lock 600lbs/1200lbs, Drop Bolt, Emergency Breakglass & Exit Button",
      "Integrasi software pencatatan waktu kehadiran (Time Attendance) & log akses",
      "Pemasangan Video Door Phone & Audio Intercom pintu layanan"
    ],
    brands: ["Rosslare", "HID", "Solution", "ZKTeco", "Commax", "Aiphone", "Panasonic"],
    targetSectors: ["Perbankan", "Ruang Server & Data Center", "Rumah Sakit", "Hotel & Gedung Kantor"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 5 - Access Door",
    isPublished: true,
    order: 7
  },

  // 6. Fire Alarm, Public Address & Nursecall
  {
    id: "srv-fire-01",
    categoryId: "cat_fire",
    categoryName: "Fire Alarm, Public Address & Nursecall",
    name: "Fire Alarm System, Public Address (Tata Suara) & Nurse Call",
    slug: "fire-alarm-public-address-nursecall",
    summary: "Instalasi sistem deteksi kebakaran (MCFA, Smoke/Heat Detector), Public Address / Paging Sound System, dan Nurse Call Rumah Sakit.",
    description: "Layanan terintegrasi untuk keselamatan jiwa dan komunikasi darurat gedung: pemasangan panel Master Control Fire Alarm (MCFA) beserta detektor asap/panas dan alarm intrusi; instalasi tata suara gedung (Public Address / Ceiling Speaker / Horn Speaker & Conference System); serta pemasangan sistem pemanggil perawat (Nurse Call) untuk ruang rawat inap rumah sakit.",
    features: [
      "Instalasi MCFA Semi-Addressable / Full-Addressable, Smoke & Heat Detector",
      "Manual Call Point (Titik Panggil Darurat), Alarm Bell & Indicator Lamp",
      "Sistem Tata Suara Gedung (Public Address / Paging System) & Conference Room",
      "Instalasi Nurse Call System (Master Station, Bedside Call, Toilet Pull Cord & Corridor Light)"
    ],
    brands: ["Hooseki", "Notifier", "Honeywell", "BOSCH", "TOA", "Aiphone", "Commax"],
    targetSectors: ["Rumah Sakit & Klinik", "Perbankan", "Gedung Bertingkat & Hotel", "Pabrik & BUMN"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 6 - Fire Alarm, Public Address & Nursecall",
    isPublished: true,
    order: 8
  },

  // 7. Internet Service Provider
  {
    id: "srv-isp-01",
    categoryId: "cat_isp",
    categoryName: "Internet Service Provider",
    name: "KGSNet Internet Dedicated, Internet Bisnis & Home Internet",
    slug: "internet-dedicated",
    summary: "Layanan Internet Resmi Kominfo RI & APJII (Dedicated, Bisnis & Home). KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia.",
    description: "PT. Kreatif Global Solusindo melalui brand resmi KGSNet menyelenggarakan layanan akses internet pita lebar berbasis Fiber Optik dan Radio Wireless (Izin Kominfo RI Nomor: 674/TEL.02.02/2020, SKLO Nomor: 375/TEL.04.02/2020, NIB: 9120202100598). Tersedia dalam 3 segmen (Internet Dedicated 1:1, Internet Bisnis, dan Home Internet) serta membuka kerjasama kemitraan reseller layanan internet di seluruh Indonesia.",
    features: [
      "Internet Dedicated (UMKM, Sekolah & Corporate) rasio simetris 1:1 & SLA 99.5%",
      "Internet Bisnis (10 Mbps s/d 200 Mbps) & Home Internet Unlimited",
      "Berizin resmi Kominfo RI No. 674/TEL.02.02/2020 & SKLO No. 375/TEL.04.02/2020",
      "KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia"
    ],
    brands: ["KGSNet (Official ISP)", "APJII Official Member", "Kominfo RI Licensed"],
    targetSectors: ["Korporasi & Pabrik", "Instansi & Sekolah", "UMKM & Ruko", "Mitra Reseller Seluruh Indonesia"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Bidang 7 - Internet Service Provider",
    isPublished: true,
    order: 9
  }
];

// Verified Clients extracted from pages 8 to 15 of PDF
export const VERIFIED_CLIENTS: ClientItem[] = [
  // Page 8: Radiolink Single Channel, RIG, Repeater HT
  { id: "c-01", name: "PT. Multrada Multi Maju", category: "Perkebunan & Industri", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-02", name: "PT. Karya Sawit Lestari", category: "Kelapa Sawit", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-03", name: "PT. Bumi Sawindo Permai", location: "Tj. Enim", category: "Kelapa Sawit", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-04", name: "PT. Hexindo Adi Perkasa", category: "Alat Berat & Industri", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-05", name: "PT. Pembangkit Jawa Bali - Talang Duku", location: "Talang Duku", category: "Energi & Pembangkit Listrik", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-06", name: "PT. Harapan Lima Roda, ConocoPhillips", category: "Minyak & Gas", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-07", name: "BPK Perwakilan Palembang", location: "Palembang", category: "Instansi Pemerintah", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-08", name: "General Electric Stasiun Kepanduan Tj. Buyut", location: "Tj. Buyut", category: "Pelayaran & Industri", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-09", name: "PT. Pelindo II Cab. Palembang", location: "Palembang", category: "BUMN & Kepelabuhanan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-10", name: "PT. Kirana Permata", category: "Industri Karet", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-11", name: "PT. Tri Aryani", location: "Kab. MUBA", category: "Pertambangan Batubara", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-12", name: "PT. Servo Lintas Raya", category: "Logistik & Tambang", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-13", name: "RS M. Husin Palembang", location: "Palembang", category: "Rumah Sakit", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-14", name: "PT. Puma Energy", location: "Samarinda", category: "Minyak & Energi", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-15", name: "Dinas Perikanan Prov. Sumatera Selatan", location: "Sumsel", category: "Pemerintah Daerah", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-16", name: "PT. Pinang Witmas", category: "Perkebunan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-17", name: "PT. Makin Group", category: "Agro Industri", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-18", name: "PT. Selapan Jaya", category: "Perkebunan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-19", name: "PT. Medco LPG Kaji", location: "Kaji", category: "Minyak & Gas", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-20", name: "PT. Wiradaya Lintas Sukses", category: "Logistik", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-21", name: "PT. Proteksindo Utama Mulia", category: "Medco Group", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-22", name: "TAC Pertamina Retco Prima Energi", location: "Prabumulih", category: "BUMN & Energi", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-23", name: "PT. London Sumatera", category: "Perkebunan Sawit", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-24", name: "PT. Minanga Ogan", category: "Perkebunan Sawit", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-25", name: "PT. ABP Palembang & Jambi", location: "Palembang & Jambi", category: "Perkebunan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-26", name: "PT. OKI Pulp & Paper", location: "OKI", category: "SinarMas Group / Pulp & Paper", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-27", name: "PT. SAMPOERNA AGRO GROUP", category: "Agro Bisnis", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-28", name: "PT. Pulau Hijau Multi Lestari", category: "Perkebunan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },
  { id: "c-29", name: "Indofood Group (PT. Serikat Putra, PT. Pelangi Inti Pertiwi, PT. Gunung Mas Raya)", category: "Makanan & Perkebunan", serviceCategory: "Radiolink & Radio Komunikasi", pdfPage: 8 },

  // Page 9: Key Telp. / PABX & Billing System
  { id: "c-30", name: "Rumah Sakit Islam Siti Khadijah", location: "Palembang", category: "Rumah Sakit", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-31", name: "BPN Prov. Sumsel", location: "Sumsel", category: "Instansi Pemerintah", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-32", name: "BPN Kota Palembang", location: "Palembang", category: "Instansi Pemerintah", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-33", name: "Hotel Swarna Dwipa", location: "Palembang", category: "Perhotelan", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-34", name: "Hypermart Palembang", location: "Palembang", category: "Retail & Mall", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-35", name: "Hotel Ranggonang Sekayu", location: "Sekayu", category: "Perhotelan", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-36", name: "U-Finance / Mandala Finance / WOM Finance", category: "Lembaga Keuangan", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-37", name: "Balitek Unsri & Program Diploma Komputer Unsri", location: "Palembang / Indralaya", category: "Perguruan Tinggi", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-38", name: "PT. BRI Cab. Baturaja, Prabumulih & Lahat", location: "Baturaja, Prabumulih, Lahat", category: "Perbankan BUMN", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-39", name: "Kantor Pelayanan Pajak Pratama Baturaja & Prabumulih", category: "Instansi Pemerintah", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-40", name: "PT. Datascrip Cab. Palembang & ACSC PT. Acer Indonesia", category: "Distributor IT", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-41", name: "Pengadilan Tinggi Prov. Bangka Belitung & Pengadilan Agama Babel", location: "Bangka Belitung", category: "Yudisial", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-42", name: "Dinas Perhubungan Prov. Sumsel", location: "Palembang", category: "Pemerintah Daerah", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-43", name: "PT. SAP (Wilmar Group)", category: "Agro Industri Wilmar", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-44", name: "Bank SumselBabel", location: "Sumsel & Babel", category: "Perbankan Daerah", serviceCategory: "PABX & Billing System", pdfPage: 9 },
  { id: "c-45", name: "Laboratorium Klinik Pramita", category: "Laboratorium Medis", serviceCategory: "PABX & Billing System", pdfPage: 9 },

  // Page 10: Wireless LAN, Networking, Tower Erection
  { id: "c-46", name: "Universitas Negeri Bangka Belitung", location: "Bangka Belitung", category: "Perguruan Tinggi", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-47", name: "Project Jardiknas Kabupaten/Kota se-Prov. Sumsel", location: "Sumatera Selatan", category: "Proyek Pendidikan Nasional", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-48", name: "Jaringan eGov Kab. OKI & Kab. Bangka Induk", location: "OKI & Bangka", category: "Pemerintah Daerah", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-49", name: "PT. Indosat Cab. Palembang", location: "Palembang", category: "Telekomunikasi", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-50", name: "Sampoerna Telkom Palembang", location: "Palembang", category: "Telekomunikasi", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-51", name: "Site BBIP Group", location: "Sumsel, Jambi, Riau & Sumut", category: "Perkebunan", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-52", name: "Site PT. JAPFA COMFEED Tbk", location: "Lampung, Jambi & Padang", category: "Agri-Food Industri", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-53", name: "PT. Cipta Tumbuh Berkembang (CTB)", location: "Kalimantan Barat", category: "Perkebunan", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-54", name: "PT. ADR Group", location: "Jambi & Sumsel", category: "Agro Industri", serviceCategory: "Networking & Tower", pdfPage: 10 },
  { id: "c-55", name: "PT. VALMET Indonesia", location: "S. Baung OKI & Cikarang Bekasi", category: "Manufaktur Multinasional", serviceCategory: "Networking & Tower", pdfPage: 10 },

  // Page 11: CCTV / IP Camera & Door Access
  { id: "c-56", name: "Rumah Sakit Siloam Sriwijaya", location: "Palembang", category: "Rumah Sakit Swasta", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-57", name: "Rumah Sakit Bunda Lubuk Linggau", location: "Lubuk Linggau", category: "Rumah Sakit", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-58", name: "BNI Syari'ah Palembang", location: "Palembang", category: "Perbankan", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-59", name: "Pabrik Sawit BBIP Group", location: "Sumsel", category: "Pabrik Sawit", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-60", name: "Jaringan Minimarket Alfamart", location: "Sumatera Selatan", category: "Retail Berjaringan", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-61", name: "Pabrik Gula PT. Laju Perdana Indah (Indofood Group)", category: "Industri Gula", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-62", name: "Universitas Sriwijaya (UNSRI)", location: "Palembang & Indralaya", category: "Perguruan Tinggi Negeri", serviceCategory: "CCTV & Security", pdfPage: 11 },
  { id: "c-63", name: "Bank Danamon Palembang", location: "Palembang", category: "Perbankan Swasta", serviceCategory: "Door Access Control", pdfPage: 11 },
  { id: "c-64", name: "PT. RIFAN Financindo Berjangka", location: "Palembang", category: "Finansial", serviceCategory: "Door Access Control", pdfPage: 11 },
  { id: "c-65", name: "Universitas Kader Bangsa", location: "Palembang", category: "Perguruan Tinggi", serviceCategory: "Door Access Control", pdfPage: 11 },

  // Page 12: Lightning Protection & Fire/Intrusion Alarm
  { id: "c-66", name: "PLTG Talang Duku PT. PLN Wil IV Sumsel", location: "Talang Duku", category: "PLN / Pembangkit Listrik", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-67", name: "PLTG Borang PT. PLN Wil IV Sumsel", location: "Borang", category: "PLN / Pembangkit Listrik", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-68", name: "Site Camp / Stockpile PT. Tri Aryani", location: "Kab. MUBA", category: "Tambang Batubara", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-69", name: "RSUD Kayu Agung", location: "Kayu Agung", category: "Rumah Sakit Daerah", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-70", name: "PT. Pelangi Inti Pertiwi", location: "Langkat - Sumut", category: "Perkebunan", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-71", name: "PT. PINAGO & PT. Manambang Muara Enim", location: "Muara Enim", category: "Tambang & Perkebunan", serviceCategory: "Penangkal Petir", pdfPage: 12 },
  { id: "c-72", name: "Bank Yudha Cab. Palembang & Bank Mandiri KCP Betung", location: "Palembang & Betung", category: "Perbankan", serviceCategory: "Fire & Intrusion Alarm", pdfPage: 12 },
  { id: "c-73", name: "RS AR-Rasyid & RS Muhammadiyah Palembang", location: "Palembang", category: "Rumah Sakit", serviceCategory: "Fire & Intrusion Alarm", pdfPage: 12 },
  { id: "c-74", name: "PT. Telkom Indonesia", location: "Palembang / Regional", category: "BUMN Telekomunikasi", serviceCategory: "Fire & Intrusion Alarm", pdfPage: 12 },
  { id: "c-75", name: "RSUD Siti Fatimah", location: "Palembang", category: "Rumah Sakit Provinsi", serviceCategory: "Fire & Intrusion Alarm", pdfPage: 12 },

  // Page 13: Another Project (Pengadaan Komputer, Sound System, Mesin Antri, Labor Bahasa)
  { id: "c-76", name: "Kanwil Kemenag Prov. Sumsel", location: "Palembang", category: "Kementerian Agama", serviceCategory: "Pengadaan Komputer & Sound", pdfPage: 13 },
  { id: "c-77", name: "Kantor Imigrasi Palembang", location: "Palembang", category: "Kemenkumham / Imigrasi", serviceCategory: "Quenetic System (Mesin Antri)", pdfPage: 13 },
  { id: "c-78", name: "Kantor Otoritas Jasa Keuangan (OJK)", location: "Jakarta", category: "Lembaga Negara", serviceCategory: "Quenetic System (Mesin Antri)", pdfPage: 13 },
  { id: "c-79", name: "Yayasan Pendidikan Islam Pagaralam", location: "Pagaralam", category: "Pendidikan", serviceCategory: "Perangkat Labor Bahasa", pdfPage: 13 },

  // Page 14: Internet Service Provider (KGSNet) & Network Managed Service
  { id: "c-80", name: "PT. Kasih Agro Mandiri", location: "Banyuasin", category: "Perkebunan Sawit", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-81", name: "PT. Wahana Lestari Makmur Sukses", location: "Musi Banyuasin", category: "Perkebunan", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-82", name: "PT. Global Makara Teknik", location: "Sungai Lilin", category: "Pertambangan", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-83", name: "PT. Trimata Benua", location: "Sungai Lilin", category: "Pertambangan Batubara", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-84", name: "PT. Baramutiara Prima PORT & Tambang", location: "Sungai Lilin & Sri Gunung", category: "Pelabuhan & Tambang", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-85", name: "PT. Global Alam Lestari", location: "Bayung Lencir", category: "Kehutanan / HTI", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-86", name: "PT. IKI", location: "Sembilang & Merang", category: "Industri Perkayuan", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-87", name: "Distrik Navigasi Type A Kelas I Palembang", location: "Palembang", category: "Kementerian Perhubungan", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-88", name: "PT. Bara Pagmer Jaya", location: "Muara Enim & Lahat", category: "Tambang Batubara", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-89", name: "KSOP & BKP Satker Wilker Tanjung Api-Api", location: "Tanjung Api-Api", category: "Kepelabuhanan & Karantina", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-90", name: "PT. Swarnadwipa Dermaga Jaya", location: "Muara Enim", category: "Dermaga Khusus Batubara", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },
  { id: "c-91", name: "PT. Trans Energy Indonesia", location: "Keramasan & Pulau Rimau", category: "Energi & Logistik", serviceCategory: "ISP KGSNet & Managed Service", pdfPage: 14 },

  // Page 15: AIS (Automatic Identification System)
  { id: "c-92", name: "PT. Bumi Khatulistiwa Mandiri", category: "Logistik Maritim", serviceCategory: "AIS (Automatic Identification System)", pdfPage: 15 },
  { id: "c-93", name: "PT. Daya Anugrah Utama", category: "Transportasi Perairan", serviceCategory: "AIS (Automatic Identification System)", pdfPage: 15 },
  { id: "c-94", name: "PT. Hok Tong", category: "Industri Karet / Pengapalan", serviceCategory: "AIS (Automatic Identification System)", pdfPage: 15 },
  { id: "c-95", name: "PT. Hijau Lestari Raya Fiberboard", category: "Industri Fiberboard", serviceCategory: "AIS (Automatic Identification System)", pdfPage: 15 },
  { id: "c-96", name: "DISNAV (Distrik Navigasi)", location: "Sumatera Selatan", category: "Pemerintah / Navigasi Laut", serviceCategory: "AIS (Automatic Identification System)", pdfPage: 15 }
];

export const PROJECT_DOCUMENTARY: ProjectPhoto[] = [
  // 1. Data Networking
  {
    id: "doc-01",
    title: "Instalasi Jaringan Data & Fiber Optic PT. JAPFA COMFEED Tbk",
    clientOrLocation: "Site Jambi, Lampung & Padang",
    description: "Pemasangan infrastruktur jaringan komputer LAN/WAN, penarikan kabel Fiber Optik backbone antar gedung produksi, terminasi OTB, dan konfigurasi rack server.",
    category: "Data Networking",
    pdfPage: 20,
    imageUrl: "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-01-img-1",
        imageUrl: "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi rack server, patch panel, dan switch gigabit pada proyek Data Networking PT. JAPFA COMFEED Tbk.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-01-img-2",
        imageUrl: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80",
        caption: "Penarikan dan terminasi fusion splicing kabel Fiber Optik backbone antar fasilitas pabrik.",
        sortOrder: 2
      },
      {
        id: "doc-01-img-3",
        imageUrl: "https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80",
        caption: "Pengujian kualitas redaman kabel serat optik dengan OTDR dan sertifikasi titik jaringan data.",
        sortOrder: 3
      }
    ]
  },
  {
    id: "doc-02",
    title: "Pemasangan Jaringan Intranet Link & Wireless Point-to-Point",
    clientOrLocation: "ADR Group Jambi & Sumsel / BBIP Group",
    description: "Pemasangan link transmisi data nirkabel (Wireless PTP) dan router intranet penghubung antar kebun sawit dengan pusat kendali operasional.",
    category: "Data Networking",
    pdfPage: 19,
    imageUrl: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-02-img-1",
        imageUrl: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80",
        caption: "Konfigurasi Router MikroTik dan switch distribusi untuk jaringan Intranet Link ADR Group Jambi & Sumsel.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-02-img-2",
        imageUrl: "https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80",
        caption: "Pointing antena radio Wireless Point-to-Point di Site STO Telkom Jambi menuju Mill BBIP Group.",
        sortOrder: 2
      }
    ]
  },

  // 2. Radio Komunikasi (Repeater, HT, RIG)
  {
    id: "doc-03",
    title: "Pemasangan Repeater HT, Base RIG & Antena Radio Komunikasi",
    clientOrLocation: "MUBA Electric Power (Sungai Lalan) & Perkebunan Sumsel",
    description: "Instalasi stasiun pengulang sinyal (Repeater VHF/UHF), radio Base RIG, dan pemrograman Handy Talky (HT) lengkap dengan ereksi tower dan penangkal petir.",
    category: "Radio Komunikasi (Repeater, HT, RIG)",
    pdfPage: 20,
    imageUrl: "https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-03-img-1",
        imageUrl: "https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80",
        caption: "Pemasangan antena omni VHF dan kabel Heliax low-loss pada menara Repeater HT MUBA Electric Power.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-03-img-2",
        imageUrl: "https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80",
        caption: "Tuning Cavity Duplexer, pengujian VSWR antena, dan uji jangkauan komunikasi HT & RIG lintas rayon.",
        sortOrder: 2
      },
      {
        id: "doc-03-img-3",
        imageUrl: "https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=80",
        caption: "Pabrikasi Guywire Tower dan pengujian grounding penangkal petir (< 2 Ohm) untuk proteksi stasiun radio.",
        sortOrder: 3
      }
    ]
  },
  {
    id: "doc-04",
    title: "Instalasi Radio Navigasi & AIS (Automatic Identification System)",
    clientOrLocation: "Tulung Selapan OKI & Alur Sungai Musi Palembang",
    description: "Pemasangan perangkat radio VHF maritim dan AIS Receiver pada menara 28 meter untuk pemantauan komunikasi dan posisi kapal tongkang.",
    category: "Radio Komunikasi (Repeater, HT, RIG)",
    pdfPage: 15,
    imageUrl: "https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-04-img-1",
        imageUrl: "https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi antena VHF AIS Receiver dan radio komunikasi pantai pada menara 28 meter di Tulung Selapan OKI.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-04-img-2",
        imageUrl: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80",
        caption: "Monitoring pergerakan kapal dan komunikasi radio RIG operator kepelabuhanan secara real-time.",
        sortOrder: 2
      }
    ]
  },

  // 3. PABX
  {
    id: "doc-05",
    title: "Instalasi Sentral Telepon PABX, IP-PBX & Billing System",
    clientOrLocation: "Hotel Swarna Dwipa, RSI Siti Khadijah & Bank SumselBabel",
    description: "Pemasangan dan pemrograman sentral PABX Panasonic (Analogue, Hybrid & IP-PBX), jaringan distribusi MDF/IDF, serta sistem interkom kamar dan perkantoran.",
    category: "PABX",
    pdfPage: 18,
    imageUrl: "https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-05-img-1",
        imageUrl: "https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi dan terminasi Main Distribution Frame (MDF) sistem PABX Panasonic untuk ratusan ekstensi.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-05-img-2",
        imageUrl: "https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80",
        caption: "Pemrograman console operator Digital Key Telephone, IP Phone, dan integrasi Telephone Billing System.",
        sortOrder: 2
      }
    ]
  },

  // 4. CCTV
  {
    id: "doc-06",
    title: "Pemasangan Kamera Pengawas CCTV Kawasan Industri & Pembangkit",
    clientOrLocation: "PT. OKI Pulp & Paper, PLTU Talang Duku & RS Siloam Sriwijaya",
    description: "Instalasi jaringan IP Camera & CCTV Analog HD outdoor/indoor, tiang kamera pantau, proteksi Arrester CCTV, serta ruang kontrol NVR Video Wall.",
    category: "CCTV",
    pdfPage: 19,
    imageUrl: "https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-06-img-1",
        imageUrl: "https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80",
        caption: "Pemasangan kamera CCTV outdoor beresolusi tinggi beserta Video & Power Arrester di PLTU Talang Duku.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-06-img-2",
        imageUrl: "https://images.unsplash.com/photo-1580894894513-541e068a3e2b?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi tiang kamera CCTV perimeter dan penarikan link Fiber Optik di kawasan PT. OKI Pulp & Paper.",
        sortOrder: 2
      },
      {
        id: "doc-06-img-3",
        imageUrl: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80",
        caption: "Konfigurasi NVR dan layar Central Monitoring System (CMS) 24 jam di ruang sekuriti utama.",
        sortOrder: 3
      }
    ]
  },

  // 5. Access Door
  {
    id: "doc-07",
    title: "Instalasi Door Access Control Biometrik, RFID & Video Door Phone",
    clientOrLocation: "Bank Danamon, Hotel Arya Duta & Ruang Server Korporat",
    description: "Pemasangan sistem pengunci pintu elektrik (Magnetic Lock / Drop Bolt) terintegrasi reader kartu RFID, sidik jari/wajah, dan interkom pintu masuk.",
    category: "Access Door",
    pdfPage: 18,
    imageUrl: "https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-07-img-1",
        imageUrl: "https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi terminal Access Door Biometrik & RFID beserta Magnetic Lock pada pintu ruang terbatas.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-07-img-2",
        imageUrl: "https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80",
        caption: "Pemasangan unit Door Phone & Audio-Video Intercom di Hotel Arya Duta Palembang.",
        sortOrder: 2
      }
    ]
  },

  // 6. Fire Alarm, Public Address & Nursecall
  {
    id: "doc-08",
    title: "Instalasi Fire Alarm, Public Address (Tata Suara) & Nurse Call RS",
    clientOrLocation: "RSUD Siti Fatimah, RS Muhammadiyah, Telkom & Perbankan",
    description: "Pemasangan panel MCFA Fire Alarm, detektor asap/panas, tata suara evakuasi gedung (Public Address), serta sistem pemanggil perawat (Nurse Call) ruang rawat inap.",
    category: "Fire Alarm, Public Address & Nursecall",
    pdfPage: 18,
    imageUrl: "https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80",
    images: [
      {
        id: "doc-08-img-1",
        imageUrl: "https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi panel Master Control Fire Alarm (MCFA), Smoke Detector, dan Alarm Bell evakuasi gedung.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-08-img-2",
        imageUrl: "https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=80",
        caption: "Pemasangan unit Nurse Call (pemanggil perawat) pada bed pasien dan lampu indikator koridor rumah sakit.",
        sortOrder: 2
      },
      {
        id: "doc-08-img-3",
        imageUrl: "https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=80",
        caption: "Instalasi ceiling speaker Public Address (Paging Sound System) dan audio ruang rapat utama.",
        sortOrder: 3
      }
    ]
  },

  // 7. Internet Service Provider
  {
    id: "doc-09",
    title: "Implementasi Jaringan Internet Dedicated & POP ISP KGSNet",
    clientOrLocation: "Distrik Navigasi Palembang, KSOP Tanjung Api-Api & Kawasan Industri",
    description: "Pembangunan infrastruktur POP (Point of Presence), distribusi Fiber Optik & Radio Wireless ISP KGSNet untuk pelanggan korporat, instansi, dan mitra reseller.",
    category: "Internet Service Provider",
    pdfPage: 14,
    imageUrl: "https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80",
    images: [
      {
        id: "doc-09-img-1",
        imageUrl: "https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80",
        caption: "Infrastruktur menara POP dan transmisi distribusi akses Internet Service Provider KGSNet.",
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: "doc-09-img-2",
        imageUrl: "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80",
        caption: "Ruang NOC & Core Router BGP KGSNet untuk layanan Internet Dedicated dan kemitraan reseller seluruh Indonesia.",
        sortOrder: 2
      }
    ]
  }
];

export const VERIFIED_PARTNERS: PartnerItem[] = [
  { id: "p-01", name: "PT. Telkom Indonesia", role: "Lastmile Support for ISP", serviceType: "Internet Service Provider", pdfPage: 16 },
  { id: "p-02", name: "B.NET", role: "Guywire Tower Support for ISP", serviceType: "Tower & Backbone", pdfPage: 16 },
  { id: "p-03", name: "Inti.Net", role: "Guywire Tower Support for ISP", serviceType: "Tower & Backbone", pdfPage: 16 },
  { id: "p-04", name: "Elnus.Net", role: "Guywire Tower Support for ISP", serviceType: "Tower & Backbone", pdfPage: 16 },
  { id: "p-05", name: "PT. LintasArta", role: "Guywire Tower Support for ISP", serviceType: "Tower & Backbone", pdfPage: 16 },
  { id: "p-06", name: "PT. IES Nusantara", role: "Guywire Tower Support for ISP", serviceType: "Tower & Backbone", pdfPage: 16 }
];

export const VERIFIED_CERTIFICATIONS: CertificationItem[] = [
  {
    id: "cert-01",
    title: "Sony Authorized Dealer",
    issuerOrBrand: "Sony (Analog Surveillance Camera Products)",
    holder: "PT. KREATIF GLOBAL SOLUSINDO",
    yearOrValidity: "Sertifikat Dealer Resmi",
    pdfPage: 21
  },
  {
    id: "cert-02",
    title: "Sony Training Certificate",
    issuerOrBrand: "Sony Certified Authorized Dealer (SCAD) Academy",
    holder: "Moh. Yunus Alfian (PT. Kreatif Global Solusindo)",
    yearOrValidity: "2 May 2013",
    pdfPage: 21
  },
  {
    id: "cert-03",
    title: "Panasonic Certificate of Authorization",
    issuerOrBrand: "Panasonic / ITComm",
    holder: "CREATIVE COM (Dealer Resmi Palembang)",
    yearOrValidity: "Dealer Resmi sejak 2002",
    pdfPage: 21
  },
  {
    id: "cert-04",
    title: "NETVIEL Authorized Reseller",
    issuerOrBrand: "NETVIEL InfiTe™ Fiber Optic Connectivity",
    holder: "KREATIF GLOBAL SOLUSINDO, PT.",
    yearOrValidity: "Reseller Resmi Terdaftar",
    pdfPage: 21
  },
  {
    id: "cert-05",
    title: "MikroTik Certified Network Associate (MTCNA)",
    issuerOrBrand: "MikroTik RouterOS Training",
    holder: "Moh. Yunus Alfian",
    yearOrValidity: "Certified MTCNA",
    pdfPage: 21
  },
  {
    id: "cert-06",
    title: "Anggota Resmi APJII & Izin ISP Nasional",
    issuerOrBrand: "APJII (Asosiasi Penyelenggara Jasa Internet Indonesia) & Kominfo",
    holder: "PT. KREATIF GLOBAL SOLUSINDO (KGSNet)",
    yearOrValidity: "Resmi sejak 2020",
    pdfPage: 3
  }
];

export const INITIAL_INQUIRIES: InquiryMessage[] = [
  {
    id: "inq-01",
    name: "Hendri Prasetyo",
    company: "PT. Sawit Mandiri Riau",
    email: "h.prasetyo@sawitmandiri.co.id",
    phone: "0812-7182-9901",
    serviceId: "srv-isp-01",
    location: "Riau / Jambi",
    message: "Mohon penawaran sambungan internet dedicated KGSNet via radio wireless untuk lokasi pabrik sawit (PKS) di area remote.",
    createdAt: "2026-10-15 10:24",
    status: "Diproses"
  },
  {
    id: "inq-02",
    name: "Bambang Sudiro",
    company: "RS Pratama Sejahtera",
    email: "bsudiro@pratamamedika.id",
    phone: "0813-8821-4455",
    serviceId: "srv-tel-01",
    location: "Palembang",
    message: "Kami membutuhkan peremajaan IP-PBX Panasonic 48 extension terintegrasi billing system dan NurseCall ruang rawat.",
    createdAt: "2026-10-16 14:10",
    status: "Baru"
  },
  {
    id: "inq-03",
    name: "Ir. Gunawan",
    company: "Dermaga Logistik Selapan",
    email: "gunawan@logistikselapan.com",
    phone: "0811-9234-1188",
    serviceId: "srv-oth-05",
    location: "Tulung Selapan, OKI",
    message: "Konsultasi sistem AIS Receiver dan kamera tracking kapal tongkang di alur sungai Musi / Selapan.",
    createdAt: "2026-10-17 08:30",
    status: "Baru",
    notes: [
      {
        id: "note-1",
        note: "Menghubungi nomor WA PIC untuk konfirmasi tinggi tiang antena 28m di dermaga.",
        createdAt: "2026-10-17 09:15"
      }
    ]
  }
];

export const INITIAL_ARTICLES: ArticleItem[] = [
  {
    id: "art-1",
    title: "Pentingnya Backbone Fiber Optik untuk Kawasan Pabrik Kelapa Sawit & Pulp Paper",
    slug: "pentingnya-backbone-fiber-optik-industri",
    categoryId: "cat_comp_net",
    categoryName: "Computer & Networking",
    summary: "Bagaimana kabel serat optik dengan terminasi OTDR terstandar menjamin konektivitas data antar divisi di area pabrik tanpa gangguan interferensi elektromagnetik mesin berat.",
    content: `Di kawasan industri berskala besar seperti Pabrik Kelapa Sawit (PKS) dan pabrik bubur kertas (Pulp & Paper), jarak antar fasilitas seperti kantor timbang, pabrik pengolahan, workshop mekanik, dan kantor administrasi kerap mencapai ratusan meter bahkan beberapa kilometer.

Kabel tembaga konvensional memiliki batasan jarak efektif maksimum 100 meter dan sangat rentan terhadap gangguan interferensi elektromagnetik dari motor listrik tegangan tinggi dan generator pabrik. Penggunaan jaringan Fibre Optic Cable Networking dengan sertifikasi resmi seperti NETVIEL InfiTe™ menjadi standar wajib.

Keunggulan Fiber Optik di Area Industri:
- Bandwidth gigabit simetris tanpa degradasi sinyal jarak jauh.
- Kebal terhadap induksi petir dan radiasi elektromagnetik mesin pabrik.
- Uji redaman akurat menggunakan alat uji Optical Time-Domain Reflectometer (OTDR).`,
    author: "Tim Teknis PT. KGS",
    isFeatured: true,
    status: "published",
    views: 142,
    createdAt: "2026-10-12",
    readTime: "4 min baca",
    imageUrl: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80",
    labels: ["Fiber Optic", "Infrastruktur", "Studi Kasus Pabrik"]
  },
  {
    id: "art-2",
    title: "Standar Nilai Tahanan Pembumian (Grounding) di Bawah 2 Ohm untuk Fasilitas Vital",
    slug: "standar-nilai-tahanan-pembumian-grounding",
    categoryId: "cat_another",
    categoryName: "Another Service",
    summary: "Mengapa pengukuran dengan Earth Resistance Tester sangat penting dalam melindungi gedung, trafo listrik, dan perangkat CCTV dari lonjakan tegangan petir.",
    content: `Sumatera Selatan dan sekitarnya dikenal memiliki hari guruh tahunan yang tinggi. Fasilitas vital seperti Pembangkit Listrik Tenaga Gas (PLTG), Rumah Sakit Daerah, dan menara telekomunikasi memerlukan sistem proteksi petir elektrostatis yang terintegrasi penuh.

Uji Ketahanan Tanah dengan Earth Resistance Tester:
Penangkal petir di atas gedung tidak akan bekerja optimal tanpa pembumian (grounding) yang tepat. Standar teknis yang diterapkan teknisi PT. KGS mewajibkan pengujian resistansi tanah dengan Earth Resistance Tester hingga mencapai nilai tahanan di bawah 2 Ohm.

Selain grounding eksternal, perlindungan internal berupa surge arrester pada panel Main Distribution Panel (MDP) dan arrester CCTV wajib dipasang untuk memotong lonjakan arus induksi sebelum merusak motherboard perangkat.`,
    author: "Eng. Yunus Alfian",
    isFeatured: true,
    status: "published",
    views: 215,
    createdAt: "2026-10-14",
    readTime: "5 min baca",
    imageUrl: "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80",
    labels: ["Grounding & Petir", "Standar K3", "Fasilitas Vital"]
  },
  {
    id: "art-3",
    title: "Implementasi Automatic Identification System (AIS) untuk Monitoring Alur Sungai Musi",
    slug: "implementasi-ais-monitoring-alur-sungai",
    categoryId: "cat_another",
    categoryName: "Another Service",
    summary: "Integrasi perangkat AIS Receiver VHF dengan kamera pengawas resolusi tinggi untuk pelacakan kapal tongkang batubara dan kayu di perairan sungai.",
    content: `Lalu lintas kapal tongkang batubara, kapal penarik (tugboat), dan angkutan logistik di jalur sungai Sumatera Selatan membutuhkan sistem monitoring real-time untuk mencegah tabrakan dan memantau antrean dermaga.

Melalui sistem Automatic Identification System (AIS) yang dipadukan dengan antena menara elevasi tinggi dan kamera Hikvision, Distrik Navigasi dan pengelola pelabuhan dermaga dapat melacak koordinat GPS, nama kapal, kecepatan laju, dan status navigasi kapal secara otomatis.`,
    author: "Tim Teknis PT. KGS",
    isFeatured: false,
    status: "published",
    views: 98,
    createdAt: "2026-10-16",
    readTime: "6 min baca",
    imageUrl: "https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80",
    labels: ["AIS Maritim", "CCTV AI", "Monitoring Sungai"]
  },
  {
    id: "art-4",
    title: "Strategi Redundansi Dual-Homing ISP KGSNet untuk Menjamin Nol Uptime Loss",
    slug: "strategi-redundansi-dual-homing-isp-kgsnet",
    categoryId: "cat_isp",
    categoryName: "Internet Service Provider",
    summary: "Arsitektur konektivitas BGP Peering dual-homing Neucentrix Data Center yang menjamin operasional perbankan dan rumah sakit tetap aktif tanpa jeda.",
    content: `Bagi perbankan, sistem pembayaran digital, dan rumah sakit, downtime internet bahkan selama 5 menit dapat mengakibatkan kerugian finansial yang signifikan dan terhentinya pelayanan darurat.

KGSNet menerapkan arsitektur koneksi internet B2B Dedicated dengan protokol BGP (Border Gateway Protocol) dual-homing upstream ke Telkom Neucentrix dan Indosat Ooredoo. Jika jalur utama mengalami degradasi atau putus fisik, failover terjadi secara instan dalam hitungan milidetik tanpa intervensi manual.

Didukung oleh tim Network Operation Center (NOC) yang bersiaga 24/7/365 dan SLA 99.5% uptime bergaransi restitusi resmi.`,
    author: "NOC Lead PT. KGS",
    isFeatured: true,
    status: "published",
    views: 184,
    createdAt: "2026-10-17",
    readTime: "5 min baca",
    imageUrl: "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80",
    labels: ["ISP KGSNet", "BGP Peering", "SLA 99.5%", "Enterprise"]
  },
  {
    id: "art-5",
    title: "Keunggulan Pabrikasi Mandiri Guywire Tower Segitiga untuk Site Perkebunan Rawa",
    slug: "keunggulan-pabrikasi-mandiri-guywire-tower",
    categoryId: "cat_another",
    categoryName: "Another Service",
    summary: "Pabrikasi tiang menara komunikasi di workshop internal Palembang dengan perhitungan beban angin dan pondasi cakar ayam khusus tanah gambut.",
    content: `Membangun menara telekomunikasi dan radio komunikasi di daerah rawa gambut seperti Musi Banyuasin dan Ogan Komering Ilir membutuhkan spesifikasi struktur khusus.

Workshop Creative Com (PT. KGS) memproduksi tiang guywire tower mandiri dengan spesifikasi pipa seamless bergalvanis celup panas (Hot-Dip Galvanized) anti korosi. Pengelasan teruji dan kawat seling baja berkekuatan tinggi memastikan menara tetap kokoh menahan terpaan angin badai tropis di area terbuka perkebunan sawit.`,
    author: "Tim Workshop PT. KGS",
    isFeatured: false,
    status: "published",
    views: 110,
    createdAt: "2026-10-17",
    readTime: "4 min baca",
    imageUrl: "https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80",
    labels: ["Fabrikasi Tower", "Guywire", "Site Remote", "Infrastruktur"]
  }
];

export const INITIAL_PAGES: StaticPageItem[] = [
  {
    id: "page-1",
    title: "Profile Perusahaan",
    slug: "profil-perusahaan",
    summary: "Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo.",
    content: `Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.`,
    isPublished: true,
    sortOrder: 1,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-2",
    title: "Visi, Budaya & Komitmen",
    slug: "visi-misi-budaya",
    summary: "Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan PT. Kreatif Global Solusindo.",
    content: `VISI
Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.

BUDAYA
Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.

KOMITMEN
Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.`,
    isPublished: true,
    sortOrder: 2,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-3",
    title: "Kebijakan Privasi Pengguna",
    slug: "kebijakan-privasi",
    summary: "Komitmen PT. Kreatif Global Solusindo dalam menjaga kerahasiaan dan keamanan data pelanggan, rekanan, dan calon klien.",
    content: `1. Pengumpulan Informasi
PT. Kreatif Global Solusindo (KGS) menghormati dan melindungi privasi setiap pengunjung situs dan pelanggan kami. Informasi yang dikumpulkan melalui formulir konsultasi, permintaan penawaran harga, atau korespondensi langsung (seperti nama, nama perusahaan, nomor telepon, alamat email, dan lokasi proyek) hanya digunakan untuk kepentingan verifikasi teknis, penyusunan proposal solusi, dan komunikasi resmi perusahaan.

2. Keamanan Data Pelanggan
Kami menerapkan prosedur teknis dan manajemen yang ketat untuk mengamankan data Anda dari akses tanpa izin, perubahan, atau pengungkapan yang tidak sah. Seluruh data penawaran harga disimpan dalam server yang aman dengan enkripsi standar industri.

3. Tidak Ada Pembagian Data kepada Pihak Ketiga
Kami tidak akan menjual, menyewakan, atau membagikan informasi kontak pribadi atau data korporasi Anda kepada pihak ketiga manapun untuk tujuan pemasaran komersial tanpa persetujuan eksplisit dari Anda, kecuali jika diwajibkan oleh peraturan hukum yang berlaku di Republik Indonesia.

4. Hubungi Kami
Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: info@creative.co.id atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.`,
    isPublished: true,
    sortOrder: 3,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-4",
    title: "System Integrator, Security Solution & Internet Provider",
    slug: "beranda",
    summary: "Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.",
    content: `PT. Kreatif Global Solusindo (KGS) menghadirkan Product & Service terintegrasi: Security (FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone), Networking (Router, Wireless), PBX (ANALOGUE PBX, HYBRID PBX, IP – PBX), Telecommunication (LIGHTNING PROTECTOR: Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR; Tower: Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE), serta Internet Service Provider resmi Kominfo RI (Internet Dedicated, Internet Bisnis, Home Internet).`,
    isPublished: true,
    sortOrder: 4,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-5",
    title: "Tentang PT. Kreatif Global Solusindo",
    slug: "tentang-kami",
    summary: "Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.",
    content: `Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.`,
    isPublished: true,
    sortOrder: 5,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-6",
    title: "Produk & Layanan",
    slug: "layanan",
    summary: "Security, Networking, PBX, Telecommunication (LIGHTNING PROTECTOR & Tower), serta Internet Service Provider berizin resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet).",
    content: `Katalog lengkap Produk & Layanan PT. Kreatif Global Solusindo:
- Security : FIRE & ALARM SECURITY, CCTV, Access Door, Video Door Phone
- Networking : Router, Wireless
- PBX : ANALOGUE PBX, HYBRID PBX, IP – PBX
- Telecommunication : LIGHTNING PROTECTOR (Handy Talky, CONFERENCE SYSTEM, LIGHTNING PROTECTOR) & Tower (Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE)
- Internet Service Provider : Legalitas Kominfo RI Nomor 674/TEL.02.02/2020, SKLO Nomor 375/TEL.04.02/2020, NIB 9120202100598 dengan 3 Segmen (Internet Dedicated, Internet Bisnis, Home Internet).`,
    isPublished: true,
    sortOrder: 6,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-7",
    title: "Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya",
    slug: "harga",
    summary: "Daftar paket berlangganan 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, dan Home Internet) berizin resmi Kominfo RI, serta solusi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication) berbasis konsultasi teknis & survei lokasi.",
    content: `Layanan Internet Service Provider PT. Kreatif Global Solusindo (Izin Kominfo RI Nomor: 674/TEL.02.02/2020, SKLO Nomor: 375/TEL.04.02/2020, NIB: 9120202100598) tersedia dalam 3 Segmen utama: Internet Dedicated (UMKM, Sekolah, Corporate), Internet Bisnis (10 Mbps s/d 200 Mbps), dan Home Internet (10 Mbps s/d 200 Mbps), dilengkapi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication).`,
    isPublished: true,
    sortOrder: 7,
    updatedAt: "2026-10-25 12:00"
  },
  {
    id: "page-8",
    title: "Pertanyaan Umum & Jawaban Teknis",
    slug: "faq",
    summary: "Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.",
    content: `Jawaban teknis terverifikasi mengenai cakupan operasional Sumatera hingga nasional, lisensi resmi ISP KGSNet Kemenkominfo & APJII, prosedur survei lapangan on-site, serta garansi pengujian tahanan pembumian di bawah 2 Ohm menggunakan Digital Earth Resistance Tester.`,
    isPublished: true,
    sortOrder: 8,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-9",
    title: "Portofolio Proyek & Klien Korporasi",
    slug: "portofolio",
    summary: "Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.",
    content: `Sejak 1997, banyak pekerjaan yang telah dilakukan, baik terhadap konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN. Ruang layanan pun tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga keprovinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta.

Polahubungan yang sehat tidak saja dikembangkan dalam lingkungan internal, namun juga terhadap lingkungan eksternal. Pesaing tidak dianggap sebagai pesaing, melainkan sebagai mitra atau partner. Client atau konsumen tidak dianggap sebagai objek, melainkan sebagai Stakeholders. Karena dengan dukungan dari rekan konsumenlah, perusahaan dapat bertahan dan berkembang hingga saat ini. Halaman berikut, adalah beberapa client dan pekerjaan sesuai keahlian yang telah dikerjakan ;`,
    isPublished: true,
    sortOrder: 9,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-10",
    title: "Hubungi Kami — PT. Kreatif Global Solusindo",
    slug: "kontak",
    summary: "Konsultasikan kebutuhan infrastruktur IT, telekomunikasi, kamera pengawas, penangkal petir, atau koneksi ISP KGSNet bersama tim ahli kami. Permintaan penawaran akan langsung terhubung ke WhatsApp Customer Service resmi kami.",
    content: `Alamat Kantor (Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113) siap melayani konsultasi teknis, survei lokasi, dan pengajuan penawaran resmi.`,
    isPublished: true,
    sortOrder: 10,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-11",
    title: "Artikel Solusi Teknologi & Praktik Terbaik Industri",
    slug: "blog",
    summary: "Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.",
    content: `Pusat edukasi dan publikasi artikel teknis dari tim engineer PT. Kreatif Global Solusindo seputar infrastruktur jaringan serat optik, proteksi petir kelistrikan industri, telekomunikasi radio, dan navigasi maritim.`,
    isPublished: true,
    sortOrder: 11,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-12",
    title: "Slide Show Infrastruktur & Solusi Teknologi",
    slug: "slideshow",
    summary: "Galeri interaktif dan penjelasan teknis 8 infrastruktur utama PT. Kreatif Global Solusindo (Creative.co.id): Fiber Optik, Self Supporting Tower Monopole, Guy Wire Tower, Lightning Protector, IP – PBX, Wireless, Access Door, dan CCTV.",
    content: `Jelajahi galeri multi-gambar dan spesifikasi teknis dari setiap kategori solusi infrastruktur telekomunikasi, jaringan, keamanan, dan menara yang dikerjakan oleh tim engineer PT. Kreatif Global Solusindo sejak 1997.`,
    isPublished: true,
    sortOrder: 12,
    updatedAt: "2026-10-25 10:00"
  },
  {
    id: "page-13",
    title: "Promo Spesial & Penawaran Unggulan PT. KGS",
    slug: "promo",
    summary: "Nikmati program promo instalasi internet KGSNet (Home, Bisnis & Dedicated), paket bundling CCTV IP Camera, audit jaringan Fiber Optik, dan uji grounding penangkal petir bersertifikat.",
    content: `Program promo resmi PT. Kreatif Global Solusindo dirancang untuk membantu instansi pemerintah, BUMN, korporasi, sekolah, UMKM, maupun pelanggan residensial memperoleh infrastruktur TI, keamanan, dan konektivitas internet terbaik dengan penghematan biaya instalasi dan perangkat.`,
    isPublished: true,
    sortOrder: 13,
    updatedAt: "2026-10-28 08:00"
  },
  {
    id: "page-14",
    title: "Client / Pelanggan Terverifikasi Sejak 1997",
    slug: "klien",
    summary: "Daftar institusi pemerintah, BUMN, perbankan, rumah sakit, perkebunan, pendidikan, perhotelan, dan korporasi swasta yang telah mempercayakan infrastruktur teknologinya kepada PT. Kreatif Global Solusindo.",
    content: `Sejak berdiri tahun 1997 (Creative Com) hingga bertransformasi menjadi PT. Kreatif Global Solusindo (2013), kami memandang setiap client/pelanggan sebagai mitra jangka panjang (Stakeholders).`,
    isPublished: true,
    sortOrder: 14,
    updatedAt: "2026-10-28 08:00"
  },
  {
    id: "page-15",
    title: "Kemitraan Strategis & Prinsipal Teknologi Resmi",
    slug: "kemitraan",
    summary: "Sinergi kemitraan resmi PT. Kreatif Global Solusindo bersama prinsipal teknologi global, asosiasi penyelenggara jasa internet (APJII), dan vendor perangkat telekomunikasi & keamanan terkemuka.",
    content: `PT. Kreatif Global Solusindo didukung oleh ekosistem kemitraan prinsipal resmi (Authorized Dealer, Certified Integrator & Official Member) untuk menjamin keaslian perangkat, ketersediaan suku cadang, dan garansi purna jual terbaik bagi seluruh pelanggan.`,
    isPublished: true,
    sortOrder: 15,
    updatedAt: "2026-10-28 08:00"
  },
  {
    id: "page-16",
    title: "Testimoni Client / Pelanggan & Instansi",
    slug: "testimoni",
    summary: "Ulasan dan apresiasi nyata dari para pimpinan IT, manajer operasional, instansi pemerintahan, BUMN, rumah sakit, dan pelanggan setia PT. Kreatif Global Solusindo.",
    content: `Kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat, dan tepat. Berikut adalah pengalaman langsung dari berbagai instansi dan pelanggan yang telah menggunakan layanan System Integrator, Security Solution & Internet Service Provider KGSNet.`,
    isPublished: true,
    sortOrder: 16,
    updatedAt: "2026-10-28 08:00"
  }
];

export function resolveUrlToPageSlug(url: string): string {
  const clean = (url || '').trim();
  if (!clean || clean === '#') return '';
  if (clean.toLowerCase().includes('page.php') && clean.includes('slug=')) {
    const match = clean.match(/[?&]slug=([^&#]+)/i);
    if (match && match[1]) {
      return decodeURIComponent(match[1]).trim().toLowerCase();
    }
  }
  const base = clean.split('#')[0].split('?')[0].replace(/^\/+/, '').toLowerCase();
  const map: Record<string, string> = {
    'index.php': 'beranda',
    'beranda.php': 'beranda',
    'tentang.php': 'tentang-kami',
    'tentang-kami.php': 'tentang-kami',
    'layanan.php': 'layanan',
    'promo.php': 'promo',
    'klien.php': 'klien',
    'kemitraan.php': 'kemitraan',
    'testimoni.php': 'testimoni',
    'slideshow.php': 'slideshow',
    'harga.php': 'harga',
    'faq.php': 'faq',
    'portofolio.php': 'portofolio',
    'kontak.php': 'kontak',
    'blog.php': 'blog',
    'kebijakan-privasi.php': 'kebijakan-privasi'
  };
  if (map[base]) return map[base];
  return base.replace(/\.php$/i, '').replace(/[^a-z0-9-]+/g, '-').replace(/^-|-$/g, '');
}

export function resolvePageSlugToUrl(slug: string): string {
  const s = (slug || '').trim().toLowerCase();
  const map: Record<string, string> = {
    'beranda': 'index.php',
    'tentang-kami': 'tentang.php',
    'tentang': 'tentang.php',
    'layanan': 'layanan.php',
    'produk-layanan': 'layanan.php',
    'promo': 'promo.php',
    'klien': 'klien.php',
    'client-pelanggan': 'klien.php',
    'kemitraan': 'kemitraan.php',
    'testimoni': 'testimoni.php',
    'slideshow': 'slideshow.php',
    'harga': 'harga.php',
    'faq': 'faq.php',
    'portofolio': 'portofolio.php',
    'kontak': 'kontak.php',
    'hubungi-kami': 'kontak.php',
    'blog': 'blog.php',
    'kebijakan-privasi': 'kebijakan-privasi.php'
  };
  if (map[s]) return map[s];
  return `page.php?slug=${encodeURIComponent(s)}`;
}


export const INITIAL_ADMIN_USERS: AdminUserItem[] = [
  {
    id: "usr-1",
    username: "admin_kgs",
    name: "Administrator Utama",
    email: "admin@kgs.net.id",
    role: "superadmin",
    status: "active",
    lastLogin: "2026-10-17 06:15"
  },
  {
    id: "usr-2",
    username: "editor_teknis",
    name: "Editor Konten & Dokumentasi",
    email: "editor@kgs.net.id",
    role: "editor",
    status: "active",
    lastLogin: "2026-10-16 17:30"
  }
];

export const INITIAL_AUDIT_LOGS: AuditLogItem[] = [
  {
    id: "log-1",
    username: "admin_kgs",
    action: "Login Admin",
    details: "Autentikasi berhasil dengan session token aman",
    ipAddress: "127.0.0.1",
    createdAt: "2026-10-17 06:15"
  },
  {
    id: "log-2",
    username: "admin_kgs",
    action: "Sinkronisasi Katalog Product & Service",
    details: "Verifikasi katalog Product & Service dan 96+ portofolio klien selesai",
    ipAddress: "127.0.0.1",
    createdAt: "2026-10-17 06:20"
  },
  {
    id: "log-3",
    username: "admin_kgs",
    action: "Update Status Lead",
    details: "Memperbarui status permintaan #inq-03 menjadi Baru",
    ipAddress: "127.0.0.1",
    createdAt: "2026-10-17 06:30"
  }
];

export interface PricingPlanItem {
  id: string;
  name: string;
  subtitle: string;
  targetAudience: string;
  badge?: string;
  isPopular?: boolean;
  sla: string;
  features: string[];
  recommendedFor: string;
}

export const B2B_PRICING_PLANS: PricingPlanItem[] = [
  {
    id: "plan-isp-dedicated",
    name: "Internet Dedicated",
    subtitle: "Segmen 1 · Koneksi Dedicated Simetris 1:1 Berizin Resmi Kominfo RI",
    targetAudience: "Kebutuhan UMKM, Kebutuhan Sekolah & Kebutuhan Corporate",
    badge: "Segmen 1 · Dedicated",
    isPopular: true,
    sla: "Kominfo 674/TEL.02.02/2020 & SKLO 375/TEL.04.02/2020",
    features: [
      "10 Mbps — Harga Perbulan mulai 3.500.000 (Kebutuhan UMKM)",
      "10 Mbps — Harga Perbulan mulai 1.750.000 (Kebutuhan Sekolah)",
      "10 Mbps — Harga Perbulan mulai 1.750.000 (Kebutuhan Corporate)",
      "Izin Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020",
      "SKLO Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020",
      "NIB (Nomor Induk Berusaha) ; 9120202100598"
    ],
    recommendedFor: "UMKM, Sekolah/Kampus, dan Perusahaan Corporate yang membutuhkan jaminan bandwidth dedicated dan dukungan NOC 24/7"
  },
  {
    id: "plan-isp-bisnis",
    name: "Internet Bisnis",
    subtitle: "Segmen 2 · Paket Internet Bisnis Cepat & Stabil Tanpa Kuota",
    targetAudience: "Ruko, Kantor Operasional, Klinik, Retail & Pelaku Bisnis",
    badge: "Segmen 2 · Bisnis",
    isPopular: false,
    sla: "Mulai Rp. 407.000 / bulan",
    features: [
      "10 Mbps — Rp. 407.000 / bulan",
      "20 Mbps — Rp. 431.000 / bulan",
      "30 Mbps — Rp. 453.000 / bulan",
      "40 Mbps — Rp. 477.000 / bulan",
      "50 Mbps — Rp. 511.000 / bulan",
      "100 Mbps — Rp. 1.050.000 / bulan",
      "200 Mbps — Rp. 1.560.000 / bulan"
    ],
    recommendedFor: "Operasional bisnis harian, kasir POS, transaksi online, CCTV cloud, dan koneksi Wi-Fi karyawan/pelanggan"
  },
  {
    id: "plan-isp-home",
    name: "Home Internet",
    subtitle: "Segmen 3 · Internet Rumah Tangga Unlimited & Nyaman untuk Keluarga",
    targetAudience: "Rumah Tangga, Perumahan, Work From Home & Belajar Online",
    badge: "Segmen 3 · Home",
    isPopular: false,
    sla: "Mulai Rp. 252.000 / bulan",
    features: [
      "10 Mbps — Rp. 252.000 / bulan (Optiman di 5 Perangkat)",
      "20 Mbps — Rp. 276.000 / bulan (Optiman di 7 Perangkat)",
      "30 Mbps — Rp. 298.000 / bulan (Optiman di 9 Perangkat)",
      "50 Mbps — Rp. 450.000 / bulan (Optiman di 12 Perangkat)",
      "100 Mbps — Rp. 710.000 / bulan (Optiman di 15 Perangkat)",
      "200 Mbps — Rp. 1.100.000 / bulan (Optiman di 20 Perangkat)"
    ],
    recommendedFor: "Kebutuhan internet keluarga di rumah, streaming HD, gaming, kuliah daring, dan aktivitas multi-perangkat"
  }
];

export interface OtherProductServiceSubItem {
  name: string;
  description: string;
}

export interface OtherProductServiceSegment {
  id: string;
  code: string;
  segmentLabel: string;
  name: string;
  subtitle: string;
  targetAudience: string;
  schemeLabel: string;
  items: OtherProductServiceSubItem[];
  brands: string[];
  recommendedFor: string;
}

export const OTHER_PRODUCT_SERVICE_SEGMENTS: OtherProductServiceSegment[] = [
  {
    id: "seg-security",
    code: "SEC",
    segmentLabel: "Segmen Security • System Integrator",
    name: "Security Solution",
    subtitle: "Solusi sistem pengamanan gedung, pengawasan kamera tertutup, kontrol akses pintu elektronik, dan proteksi kebakaran terintegrasi.",
    targetAudience: "Gedung Perkantoran, Perbankan, Rumah Sakit, Pabrik & Kawasan Industri",
    schemeLabel: "Konsultasi Teknis & Survei Lokasi (Sesuai Kebutuhan Proyek)",
    items: [
      {
        name: "FIRE & ALARM SECURITY",
        description: "Instalasi Master Control Fire Alarm (MCFA), detektor asap & panas (Smoke/Heat Detector), Manual Call Point, sirine evakuasi, serta sensor alarm anti-intrusi."
      },
      {
        name: "CCTV (Closed Circuit Television)",
        description: "Sistem pengawasan kamera keamanan CCTV Analog HD & IP Network Camera beresolusi tinggi dengan konfigurasi NVR/DVR dan monitoring terpusat."
      },
      {
        name: "Access Door (Access Control)",
        description: "Sistem kunci pintu elektronik menggunakan akses Kartu ID (RFID Proximity/Mifare), PIN keypad, dan biometrik sidik jari/wajah."
      },
      {
        name: "Video Door Phone",
        description: "Sistem interkom komunikasi audio-visual pintu masuk dua arah untuk verifikasi tamu yang terintegrasi dengan kunci elektrik."
      }
    ],
    brands: ["Sony", "Hikvision", "Dahua", "Panasonic", "BOSCH", "Honeywell", "Rosslare", "HID", "Commax"],
    recommendedFor: "Pengamanan fasilitas vital, ruang server, brankas perbankan, rumah sakit, hotel, dan area pabrik industri"
  },
  {
    id: "seg-networking",
    code: "NET",
    segmentLabel: "Segmen Networking • Infrastruktur IT",
    name: "Networking & Fiber Optik",
    subtitle: "Perancangan, instalasi, dan manajemen infrastruktur jaringan kabel serat optik (Fiber Optik), LAN, Router enterprise, dan transmisi Wireless.",
    targetAudience: "Perkantoran, Kampus, Rumah Sakit, Perkebunan, Pelabuhan & Gudang",
    schemeLabel: "Konsultasi Teknis & Survei Lokasi (Sesuai Topologi Jaringan)",
    items: [
      {
        name: "Router & Manajemen Jaringan",
        description: "Konfigurasi Router enterprise untuk manajemen bandwidth (QoS), pembagian VLAN, Firewall, VPN antar cabang, Load Balancing, Failover, dan routing BGP/OSPF."
      },
      {
        name: "Wireless (Jaringan Nirkabel)",
        description: "Instalasi jaringan Wireless LAN (Wi-Fi Indoor/Outdoor seamless roaming) serta link transmisi radio Point-to-Point (PTP) & Point-to-Multipoint (PTMP) jarak jauh."
      },
      {
        name: "Fiber Optik & Structured Cabling",
        description: "Penarikan, terminasi Fusion Splicing, dan uji OTDR jaringan kabel serat optik (Fiber Optik) backbone berkecepatan gigabit serta instalasi kabel LAN UTP/STP."
      }
    ],
    brands: ["MikroTik (Certified MTCNA)", "Ubiquiti", "Cisco", "Ruijie", "Cambium Networks", "NETVIEL (Authorized Reseller)"],
    recommendedFor: "Interkoneksi jaringan antar gedung/pabrik, distribusi Wi-Fi korporat, dan link komunikasi data site remote"
  },
  {
    id: "seg-pbx",
    code: "PBX",
    segmentLabel: "Segmen PBX • Komunikasi Korporasi",
    name: "PBX Communication System",
    subtitle: "Sistem sentral komunikasi telepon internal dan eksternal kantor mulai dari PABX Analog, Hybrid, hingga IP – PBX terpadu.",
    targetAudience: "Kantor Operasional, Hotel, Rumah Sakit, Perbankan & Perusahaan Multi-Cabang",
    schemeLabel: "Konsultasi Teknis & Survei Lokasi (Sesuai Kapasitas Ekstensi)",
    items: [
      {
        name: "ANALOGUE PBX",
        description: "Sistem sentral telepon PABX Analog yang handal untuk distribusi ekstensi antar ruangan/lantai dengan fitur Call Transfer, Hunting Group, Toll Restriction & Billing."
      },
      {
        name: "HYBRID PBX",
        description: "Sistem sentral telepon Hybrid yang memadukan fleksibilitas jalur telepon analog, digital key telephone, Auto Attendant (DISA/IVR), NurseCall/Paging, dan ekstensi IP."
      },
      {
        name: "IP – PBX",
        description: "Perangkat switching komunikasi telepon dan data berbasis protokol internet (VoIP / SIP Trunk) yang menggabungkan jaringan suara, data, dan video antar cabang."
      }
    ],
    brands: ["Panasonic (Authorized Dealer)", "NEC", "DINSTAR", "Grandstream", "Yeastar"],
    recommendedFor: "Komunikasi telepon terpusat hotel, rumah sakit, kantor pemerintahan, dan interkoneksi suara gratis antar cabang"
  },
  {
    id: "seg-telecom",
    code: "TEL",
    segmentLabel: "Segmen Telecommunication • Proteksi Petir & Tower",
    name: "Telecommunication, Lightning Protector & Tower",
    subtitle: "Solusi perangkat telekomunikasi radio lapangan, sistem konferensi ruang rapat, instalasi penyalur petir & grounding, serta fabrikasi menara telekomunikasi.",
    targetAudience: "Perkebunan Sawit, Pertambangan, Pembangkit Listrik (PLN), BUMN & Pelabuhan",
    schemeLabel: "Konsultasi Teknis & Survei Lokasi (Sesuai Spesifikasi Lapangan)",
    items: [
      {
        name: "LIGHTNING PROTECTOR (Penyalur Petir & Grounding)",
        description: "Instalasi batang penyalur petir, kabel konduktor tembaga, tempat pembumian (grounding) dengan uji Earth Tester < 2 Ohm, serta Surge Arrester panel MDP."
      },
      {
        name: "Handy Talky & Radio Komunikasi",
        description: "Pengadaan dan instalasi radio komunikasi VHF/UHF/SSB, Base Station RIG, Marine Radio, stasiun Repeater, serta AIS (Automatic Identification System) Maritim."
      },
      {
        name: "CONFERENCE SYSTEM",
        description: "Instalasi sistem audio-video konferensi ruang rapat dan aula menggunakan mikrofon Chairman & Delegate, kamera PTZ, dan tata suara terintegrasi."
      },
      {
        name: "Guy Wire Tower",
        description: "Fabrikasi mandiri di workshop internal dan ereksi menara segitiga yang ditopang dengan tali-tali baja (guywire) yang diangkurkan pada landasan tanah."
      },
      {
        name: "SELF SUPPORTING TOWER MONOPOLE",
        description: "Konstruksi dan ereksi menara mandiri Self Supporting Tower (SST) serta tiang tunggal Monopole berintegritas struktural tinggi tanpa bentangan kawat skur."
      }
    ],
    brands: ["Creative Com Workshop Fabrication", "KURN", "ERICO", "VIKING", "Motorola", "ICOM", "Kenwood", "BOSCH", "TOA"],
    recommendedFor: "Pembangunan infrastruktur menara komunikasi, proteksi sambaran petir fasilitas industri, dan radio komunikasi jarak jauh"
  }
];

export interface FaqItem {
  id: string;
  category: 'Umum' | 'Internet ISP' | 'Keamanan & Tower' | 'Kontrak & Legalitas';
  question: string;
  answer: string;
}

export const FAQ_DATA: FaqItem[] = [
  {
    id: "faq-1",
    category: "Umum",
    question: "Apa cakupan wilayah layanan operasional PT. Kreatif Global Solusindo?",
    answer: "Sejak 1997, ruang layanan tidak terbatas di kota Palembang, melainkan juga sampai seluruh kabupaten/kota di provinsi Sumatera Selatan hingga ke provinsi lain seperti Bangka Belitung, Jambi, Riau, Sumatera Barat, Sumatera Utara, Lampung, Bengkulu, Kalimantan Barat, Kalimantan Timur, Bekasi dan Jakarta."
  },
  {
    id: "faq-2",
    category: "Internet ISP",
    question: "Apa saja legalitas resmi Internet Service Provider (ISP) PT. Kreatif Global Solusindo?",
    answer: "PT. Kreatif Global Solusindo memiliki legalitas resmi ISP meliputi: (1) Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020, (2) Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020, dan (3) NIB (Nomor Induk Berusaha) ; 9120202100598."
  },
  {
    id: "faq-3",
    category: "Keamanan & Tower",
    question: "Bagaimana prosedur survei lokasi sebelum penawaran teknis diterbitkan?",
    answer: "Untuk proyek jaringan fiber optic, tower, grounding, maupun surveillance, tim engineer KGS melakukan survei lokasi on-site. Survei mencakup pengukuran Line-of-Sight (LOS) untuk wireless link, pengukuran tahanan jenis tanah dengan Earth Tester untuk grounding, dan audit topologi kabel. Hasil survei menjadi dasar Bill of Quantity (BoQ) yang transparan dan akurat tanpa biaya tersembunyi."
  },
  {
    id: "faq-4",
    category: "Keamanan & Tower",
    question: "Berapa nilai resistansi tanah yang dijamin pada instalasi grounding?",
    answer: "PT. KGS secara ketat menerapkan standar tahanan pembumian di bawah 2 Ohm (< 2 Ω). Kami menggunakan teknik pembumian mendalam dengan elektroda tembaga masif dan bahan penurun resistansi bila diperlukan. Nilai ini diuji dan diverifikasi langsung bersama klien menggunakan alat ukur Digital Earth Resistance Tester sebelum serah terima pekerjaan."
  },
  {
    id: "faq-5",
    category: "Internet ISP",
    question: "Apa saja pilihan segmen paket internet yang tersedia di PT. Kreatif Global Solusindo?",
    answer: "Kami menyediakan 3 Segmen Layanan Internet: (1) Internet Dedicated 10 Mbps untuk Kebutuhan UMKM (mulai Rp 3.500.000/bulan), Kebutuhan Sekolah (mulai Rp 1.750.000/bulan), dan Kebutuhan Corporate (mulai Rp 1.750.000/bulan); (2) Internet Bisnis mulai 10 Mbps (Rp 407.000/bulan) hingga 200 Mbps (Rp 1.560.000/bulan); serta (3) Home Internet mulai 10 Mbps (Rp 252.000/bulan, Optimal 5 Perangkat) hingga 200 Mbps (Rp 1.100.000/bulan, Optimal 20 Perangkat)."
  },
  {
    id: "faq-6",
    category: "Kontrak & Legalitas",
    question: "Apakah PT. KGS melayani tender pengadaan resmi pemerintah dan BUMN?",
    answer: "Ya, kami memiliki kelengkapan legalitas dan perizinan usaha yang lengkap (NIB 9120202100598, Izin Kominfo RI, SKLO, SIUP, NPWP, PKP) dan telah berpengalaman sejak 1997 mengerjakan pengadaan untuk konsumen perorangan, badan usaha, instansi pemerintah maupun BUMN."
  },
  {
    id: "faq-7",
    category: "Umum",
    question: "Bagaimana cara mengajukan permintaan penawaran harga atau konsultasi awal?",
    answer: "Anda dapat mengisi formulir 'Minta Penawaran' pada website ini, menghubungi telepon kantor di 0711-316088, atau berkomunikasi langsung dengan tim sales engineer kami melalui WhatsApp resmi di 0811-782119. Tim kami akan merespons dalam kurun waktu 1x24 jam kerja untuk penjadwalan konsultasi dan survei."
  }
];

export interface NavigationMenuItem {
  id: number;
  title: string;
  url: string;
  location: 'header' | 'footer';
  sortOrder: number;
  target: '_self' | '_blank';
  isActive: boolean;
}

export const INITIAL_NAVIGATION_MENUS: NavigationMenuItem[] = [
  // Navigasi Header (Atas) - Sesuai Urutan Resmi 7 Menu Utama
  { id: 1, title: 'Beranda', url: 'index.php', location: 'header', sortOrder: 1, target: '_self', isActive: true },
  { id: 2, title: 'Produk & Layanan', url: 'layanan.php', location: 'header', sortOrder: 2, target: '_self', isActive: true },
  { id: 3, title: 'Promo', url: 'promo.php', location: 'header', sortOrder: 3, target: '_self', isActive: true },
  { id: 4, title: 'Client / Pelanggan', url: 'klien.php', location: 'header', sortOrder: 4, target: '_self', isActive: true },
  { id: 5, title: 'Kemitraan', url: 'kemitraan.php', location: 'header', sortOrder: 5, target: '_self', isActive: true },
  { id: 6, title: 'Testimoni', url: 'testimoni.php', location: 'header', sortOrder: 6, target: '_self', isActive: true },
  { id: 7, title: 'Hubungi Kami', url: 'kontak.php', location: 'header', sortOrder: 7, target: '_self', isActive: true },
  // Navigasi Footer (Bawah)
  { id: 8, title: 'Profil Perusahaan', url: 'page.php?slug=profil-perusahaan', location: 'footer', sortOrder: 1, target: '_self', isActive: true },
  { id: 9, title: 'Visi & Misi', url: 'page.php?slug=visi-misi-budaya', location: 'footer', sortOrder: 2, target: '_self', isActive: true },
  { id: 10, title: 'Produk & Layanan', url: 'layanan.php', location: 'footer', sortOrder: 3, target: '_self', isActive: true },
  { id: 11, title: 'Artikel Edukasi', url: 'blog.php', location: 'footer', sortOrder: 4, target: '_self', isActive: true },
  { id: 12, title: 'Kebijakan Privasi', url: 'kebijakan-privasi.php', location: 'footer', sortOrder: 5, target: '_self', isActive: true }
];

export interface PromoItem {
  id: string;
  title: string;
  badge: string;
  category: 'Internet ISP' | 'CCTV & Security' | 'Networking & Fiber' | 'PBX & Tower';
  originalPrice: string;
  promoPrice: string;
  discountLabel: string;
  validUntil: string;
  description: string;
  features: string[];
  ctaText: string;
  isHighlight: boolean;
  isActive: boolean;
}

export const INITIAL_PROMOS: PromoItem[] = [
  {
    id: 'promo-1',
    title: 'Promo Instalasi Baru KGSNet Home & Business Broadband',
    badge: 'Terpopuler • Gratis Instalasi',
    category: 'Internet ISP',
    originalPrice: 'Biaya Instalasi Rp 500.000',
    promoPrice: 'Mulai Rp 252.000 / Bulan',
    discountLabel: 'FREE INSTALASI 100%',
    validUntil: 'Berlaku Selama Kuota Area Tersedia',
    description:
      'Nikmati koneksi internet fiber optik super cepat dan stabil dari KGSNet (ISP Resmi Kominfo RI & Anggota APJII) untuk rumah maupun tempat usaha tanpa biaya pasang baru.',
    features: [
      'Gratis biaya penarikan kabel Fiber Optik & registrasi',
      'Dipinjamkan unit ONT / Router Wi-Fi Dual-Band selama berlangganan',
      'Pilihan kecepatan 10 Mbps, 20 Mbps, 30 Mbps, 50 Mbps, 100 Mbps s/d 200 Mbps',
      'Koneksi Unlimited tanpa batasan kuota (Tanpa FUP)',
      'Dukungan teknis NOC & Tim Lapangan Palembang 24/7'
    ],
    ctaText: 'Klaim Promo Internet KGSNet',
    isHighlight: true,
    isActive: true
  },
  {
    id: 'promo-2',
    title: 'Promo Internet Dedicated 1:1 Corporate, Sekolah & Instansi',
    badge: 'Khusus B2B & Pendidikan',
    category: 'Internet ISP',
    originalPrice: 'Mulai Rp 2.200.000 / Bulan',
    promoPrice: 'Mulai Rp 1.750.000 / Bulan',
    discountLabel: 'HEMAT 20% + FREE IP PUBLIC',
    validUntil: 'Program Kemitraan B2B & Pendidikan',
    description:
      'Paket khusus Internet Dedicated rasio bandwidth simetris 1:1 (Upload = Download) dengan garansi SLA 99.5% untuk operasional kantor, rumah sakit, perbankan, kampus, dan sekolah.',
    features: [
      'Bandwidth Dedicated 1:1 murni ke upstream dual-homing',
      'Gratis 1 IP Public Static untuk kebutuhan Server / VPN / CCTV',
      'Akses grafik pemantauan trafik real-time (MRTG) 24 jam',
      'Prioritas penanganan gangguan dengan engineer on-site',
      'Gratis konsultasi & audit manajemen bandwidth MikroTik'
    ],
    ctaText: 'Ajukan Penawaran Dedicated',
    isHighlight: true,
    isActive: true
  },
  {
    id: 'promo-3',
    title: 'Paket Bundling Smart CCTV IP Camera + Akses Cloud Mobile',
    badge: 'Security Solution',
    category: 'CCTV & Security',
    originalPrice: 'Harga Normal Paket + Jasa',
    promoPrice: 'Diskon Jasa Instalasi 25%',
    discountLabel: 'FREE SETTING CLOUD & SURVEY',
    validUntil: 'Berlaku untuk Pemasangan Kantor, Gudang & Rumah',
    description:
      'Solusi pengawasan keamanan 24 jam menggunakan kamera IP / HDTVI Night Vision beresolusi tinggi lengkap dengan perekam NVR/DVR dan konfigurasi pemantauan langsung dari HP.',
    features: [
      'Gratis survei titik buta (blind-spot) lokasi area Palembang',
      'Kamera Outdoor & Indoor tahan cuaca dengan fitur ColorVu / Infrared',
      'Gratis setting aplikasi mobile monitoring di Android, iPhone & PC',
      'Instalasi rapi menggunakan pipa pelindung conduit standar industri',
      'Garansi resmi perangkat & jaminan pemeliharaan pasca-instalasi'
    ],
    ctaText: 'Pesan Paket Promo CCTV',
    isHighlight: false,
    isActive: true
  },
  {
    id: 'promo-4',
    title: 'Paket Audit Fiber Optik, IP-PBX & Uji Grounding Penangkal Petir',
    badge: 'System Integrator',
    category: 'PBX & Tower',
    originalPrice: 'Biaya Survei & Pengukuran Teknis',
    promoPrice: 'Gratis Kunjungan Survei Awal',
    discountLabel: 'FREE AUDIT & UJI < 2 OHM',
    validUntil: 'Khusus Proyek Kantor, Pabrik & Perkebunan',
    description:
      'Layanan terpadu pengecekan kesehatan jaringan kabel serat optik (OTDR), sentral telepon PABX/IP-PBX Panasonic, fabrikasi Tower, serta pengujian tahanan pembumian penyalur petir.',
    features: [
      'Gratis konsultasi desain topologi jaringan & BoQ transparan',
      'Pengukuran tahanan tanah menggunakan Digital Earth Tester (< 2 Ohm)',
      'Pengecekan redaman sambungan core Fiber Optik dengan alat OTDR',
      'Dukungan pengadaan resmi berfaktur pajak (PKP) untuk BUMN & Instansi',
      'Ditangani langsung oleh teknisi bersertifikat dan berpengalaman sejak 1997'
    ],
    ctaText: 'Jadwalkan Survei Gratis',
    isHighlight: false,
    isActive: true
  }
];

export interface TestimonialItem {
  id: string;
  clientName: string;
  personName: string;
  roleOrDepartment: string;
  sector: 'BUMN & Korporasi' | 'Pemerintahan' | 'Perbankan & Medis' | 'Pendidikan & Retail';
  serviceUsed: string;
  quote: string;
  rating: number;
  projectYear: string;
  isActive: boolean;
}

export const INITIAL_TESTIMONIALS: TestimonialItem[] = [
  {
    id: 'testi-1',
    clientName: 'PT. Pupuk Sriwidjaja (PUSRI) Palembang',
    personName: 'Divisi Teknologi Informasi & Telekomunikasi',
    roleOrDepartment: 'Tim Infrastruktur Jaringan & Komunikasi',
    sector: 'BUMN & Korporasi',
    serviceUsed: 'Fiber Optik, Telekomunikasi & Infrastruktur Jaringan',
    quote:
      'PT. Kreatif Global Solusindo (Creative Com) selalu menunjukkan standar kerja teknis yang presisi, mulai dari penarikan dan terminasi jaringan serat optik hingga dukungan teknis di lapangan yang sangat responsif.',
    rating: 5,
    projectYear: 'Mitra Korporasi',
    isActive: true
  },
  {
    id: 'testi-2',
    clientName: 'RSUP Dr. Mohammad Hoesin Palembang',
    personName: 'Instalasi Sistem Informasi & Sarana Prasarana',
    roleOrDepartment: 'Manajemen Operasional & Fasilitas Medis',
    sector: 'Perbankan & Medis',
    serviceUsed: 'PBX Communication System, CCTV & Jaringan Data',
    quote:
      'Keandalan komunikasi ekstensi PABX dan sistem keamanan CCTV di lingkungan rumah sakit sangat krusial bagi pelayanan pasien 24 jam. Tim teknisi PT. KGS bekerja rapi, cepat, dan solutif.',
    rating: 5,
    projectYear: 'Layanan Medis',
    isActive: true
  },
  {
    id: 'testi-3',
    clientName: 'Bank Sumsel Babel',
    personName: 'Bagian Umum & Infrastruktur TI',
    roleOrDepartment: 'Pengelolaan Jaringan & Keamanan Kantor',
    sector: 'Perbankan & Medis',
    serviceUsed: 'Security System (CCTV, Access Door) & Konektivitas',
    quote:
      'Implementasi sistem keamanan terpadu Access Door, kamera pengawas CCTV, dan perangkat jaringan dari PT. KGS memberikan rasa aman dengan dukungan purna jual lokal di Palembang yang selalu siap sedia.',
    rating: 5,
    projectYear: 'Sektor Perbankan',
    isActive: true
  },
  {
    id: 'testi-4',
    clientName: 'Instansi Pemerintah Daerah Provinsi Sumatera Selatan',
    personName: 'Bidang Infrastruktur Teknologi Informasi & Komunikasi',
    roleOrDepartment: 'Tim Pengelola Jaringan Pemerintahan',
    sector: 'Pemerintahan',
    serviceUsed: 'Internet Dedicated KGSNet, Wireless & Tower',
    quote:
      'Koneksi Internet Dedicated KGSNet serta pembangunan menara komunikasi dan jaringan wireless antar-gedung berjalan sangat stabil. Legalitas perusahaan yang lengkap juga memudahkan proses administrasi pengadaan.',
    rating: 5,
    projectYear: 'Sektor Pemerintahan',
    isActive: true
  },
  {
    id: 'testi-5',
    clientName: 'Perusahaan Perkebunan & Pertambangan Sumatera Selatan',
    personName: 'Departemen Engineering & Operasional Site',
    roleOrDepartment: 'Koordinator Infrastruktur Lapangan',
    sector: 'BUMN & Korporasi',
    serviceUsed: 'Guy Wire Tower, Self Supporting Tower & Penangkal Petir',
    quote:
      'Pabrikasi menara komunikasi dan instalasi penangkal petir dengan hasil uji grounding di bawah 2 Ohm dari PT. KGS terbukti tangguh melindungi perangkat radio dan jaringan kami di area site terbuka.',
    rating: 5,
    projectYear: 'Site Industri & Kebun',
    isActive: true
  },
  {
    id: 'testi-6',
    clientName: 'Lembaga Pendidikan & Kampus di Palembang',
    personName: 'Pusat Sistem Informasi & Jaringan Kampus',
    roleOrDepartment: 'Pengelola IT & Laboratorium Komputer',
    sector: 'Pendidikan & Retail',
    serviceUsed: 'Internet Dedicated Sekolah/Kampus & Manajemen Router MikroTik',
    quote:
      'Sejak beralih menggunakan layanan Internet Dedicated KGSNet dan optimasi jaringan Wi-Fi dari PT. KGS, kegiatan belajar mengajar digital dan sistem akademik online berjalan lancar tanpa kendala.',
    rating: 5,
    projectYear: 'Sektor Pendidikan',
    isActive: true
  }
];

export type WebsiteThemePresetId =
  | 'corporate-blue'
  | 'emerald-infra'
  | 'royal-indigo'
  | 'crimson-energy'
  | 'amber-executive'
  | 'arctic-clean';

export interface WebsiteThemeConfig {
  presetId: WebsiteThemePresetId;
  name: string;
  description: string;
  primaryColor: string;
  primaryHover: string;
  primaryLightBg: string;
  primaryTextAccent: string;
  heroBgColor: string;
  heroSecondaryBg: string;
  heroTextMode: 'dark' | 'light';
  navbarStyle: 'light' | 'dark' | 'glass';
  fontFamily: 'jakarta' | 'inter' | 'mono-tech';
  borderRadius: 'rounded' | 'sharp' | 'pill';
}

export const WEBSITE_THEME_PRESETS: WebsiteThemeConfig[] = [
  {
    presetId: 'corporate-blue',
    name: 'Corporate Blue & Navy (Default KGS)',
    description: 'Identitas resmi PT. KGS dengan kombinasi Biru Korporat & Navy Gelap profesional.',
    primaryColor: '#2563eb',
    primaryHover: '#1d4ed8',
    primaryLightBg: '#eff6ff',
    primaryTextAccent: '#60a5fa',
    heroBgColor: '#0b1329',
    heroSecondaryBg: '#0f172a',
    heroTextMode: 'dark',
    navbarStyle: 'light',
    fontFamily: 'jakarta',
    borderRadius: 'rounded'
  },
  {
    presetId: 'emerald-infra',
    name: 'Emerald Green Infrastructure',
    description: 'Tema hijau zamrud modern untuk nuansa jaringan telekomunikasi, energi surya & lingkungan.',
    primaryColor: '#059669',
    primaryHover: '#047857',
    primaryLightBg: '#ecfdf5',
    primaryTextAccent: '#34d399',
    heroBgColor: '#041f1a',
    heroSecondaryBg: '#062c24',
    heroTextMode: 'dark',
    navbarStyle: 'light',
    fontFamily: 'jakarta',
    borderRadius: 'rounded'
  },
  {
    presetId: 'royal-indigo',
    name: 'Royal Indigo & Cloud Data Center',
    description: 'Nuansa biru keunguan elegan untuk layanan ISP, Cloud Server, dan Data Center Enterprise.',
    primaryColor: '#4f46e5',
    primaryHover: '#4338ca',
    primaryLightBg: '#eef2ff',
    primaryTextAccent: '#818cf8',
    heroBgColor: '#0f0c29',
    heroSecondaryBg: '#16123a',
    heroTextMode: 'dark',
    navbarStyle: 'glass',
    fontFamily: 'inter',
    borderRadius: 'rounded'
  },
  {
    presetId: 'crimson-energy',
    name: 'Industrial Crimson & Steel',
    description: 'Tampilan tegas merah industri & baja gelap untuk konstruksi tower, fabrikasi & proteksi petir.',
    primaryColor: '#dc2626',
    primaryHover: '#b91c1c',
    primaryLightBg: '#fef2f2',
    primaryTextAccent: '#f87171',
    heroBgColor: '#181115',
    heroSecondaryBg: '#23171d',
    heroTextMode: 'dark',
    navbarStyle: 'dark',
    fontFamily: 'jakarta',
    borderRadius: 'sharp'
  },
  {
    presetId: 'amber-executive',
    name: 'Executive Gold & Obsidian',
    description: 'Perpaduan hitam obsidian dan aksen emas amber untuk kesan korporasi BUMN & eksekutif.',
    primaryColor: '#d97706',
    primaryHover: '#b45309',
    primaryLightBg: '#fffbeb',
    primaryTextAccent: '#fbbf24',
    heroBgColor: '#14120d',
    heroSecondaryBg: '#1f1b13',
    heroTextMode: 'dark',
    navbarStyle: 'dark',
    fontFamily: 'jakarta',
    borderRadius: 'rounded'
  },
  {
    presetId: 'arctic-clean',
    name: 'Arctic Cyan & Deep Slate',
    description: 'Aksen biru siber cerah (Cyan Sky) dengan kontras tinggi untuk visibilitas teknis modern.',
    primaryColor: '#0284c7',
    primaryHover: '#0369a1',
    primaryLightBg: '#f0f9ff',
    primaryTextAccent: '#38bdf8',
    heroBgColor: '#0c192c',
    heroSecondaryBg: '#11233d',
    heroTextMode: 'dark',
    navbarStyle: 'glass',
    fontFamily: 'inter',
    borderRadius: 'pill'
  }
];

export const DEFAULT_WEBSITE_THEME: WebsiteThemeConfig = WEBSITE_THEME_PRESETS[0];

export interface HeroSlideItem {
  id: string;
  title: string;
  subtitle: string;
  badgeText: string;
  imageUrl: string;
  sortOrder: number;
  isActive: boolean;
  slug?: string;
  imageCount?: number;
}

export type HeroSliderLayoutMode = 'side' | 'background' | 'both' | 'noc';

export interface HeroSliderConfig {
  layoutMode: HeroSliderLayoutMode;
  autoPlay: boolean;
  intervalSeconds: number;
  overlayOpacity: number;
  showNocSummary: boolean;
  slides: HeroSlideItem[];
}

export const INITIAL_HERO_SLIDER_CONFIG: HeroSliderConfig = {
  layoutMode: 'side',
  autoPlay: true,
  intervalSeconds: 5,
  overlayOpacity: 75,
  showNocSummary: true,
  slides: [
    {
      id: 'ss-cat-1',
      title: 'Fiber Optik',
      subtitle: 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari...',
      badgeText: 'Slide Show Infrastruktur · 6 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 1,
      isActive: true,
      slug: 'fiber-optik',
      imageCount: 6
    },
    {
      id: 'ss-cat-2',
      title: 'Guy Wire Tower',
      subtitle: 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah...',
      badgeText: 'Slide Show Infrastruktur · 4 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 2,
      isActive: true,
      slug: 'guy-wire-tower',
      imageCount: 4
    },
    {
      id: 'ss-cat-3',
      title: 'SELF SUPPORTING TOWER MONOPOLE',
      subtitle: 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara...',
      badgeText: 'Slide Show Infrastruktur · 4 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
      sortOrder: 3,
      isActive: true,
      slug: 'self-supporting-tower-monopole',
      imageCount: 4
    },
    {
      id: 'ss-cat-4',
      title: 'LIGHTNING PROTECTOR',
      subtitle: 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada...',
      badgeText: 'Slide Show Infrastruktur · 5 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 4,
      isActive: true,
      slug: 'lightning-protector',
      imageCount: 5
    },
    {
      id: 'ss-cat-5',
      title: 'IP – PBX',
      subtitle: 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis...',
      badgeText: 'Slide Show Infrastruktur · 5 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 5,
      isActive: true,
      slug: 'ip-pbx',
      imageCount: 5
    },
    {
      id: 'ss-cat-6',
      title: 'Wireless',
      subtitle: 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik...',
      badgeText: 'Slide Show Infrastruktur · 5 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 6,
      isActive: true,
      slug: 'wireless',
      imageCount: 5
    },
    {
      id: 'ss-cat-7',
      title: 'Access Door',
      subtitle: 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID...',
      badgeText: 'Slide Show Infrastruktur · 6 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 7,
      isActive: true,
      slug: 'access-door',
      imageCount: 6
    },
    {
      id: 'ss-cat-8',
      title: 'CCTV',
      subtitle: 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal...',
      badgeText: 'Slide Show Infrastruktur · 5 Gambar',
      imageUrl: 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 8,
      isActive: true,
      slug: 'cctv',
      imageCount: 5
    }
  ]
};

export interface TimelineMilestoneItem {
  id: string;
  year: string;
  title: string;
  description: string;
  isHighlighted?: boolean;
}

export const INITIAL_TIMELINE_MILESTONES: TimelineMilestoneItem[] = [
  {
    id: 'tl-1',
    year: '1997',
    title: 'CV. CREATIVE',
    description: 'Creative Com berdiri di Palembang menjual produk IT dan jasa perbaikan komputer & jaringan.'
  },
  {
    id: 'tl-2',
    year: '1998',
    title: 'Distributor Wartel',
    description: 'Merambah ke penjualan perangkat wartel sebagai distributor resmi merk QPHONE.'
  },
  {
    id: 'tl-3',
    year: '2002',
    title: 'Dealer Panasonic',
    description: 'Panasonic mempercayakan Creative Com sebagai salah satu dealernya di Palembang.'
  },
  {
    id: 'tl-4',
    year: '2004',
    title: 'Ekspansi Jaringan & Tower',
    description: 'Melayani pemasangan jaringan LAN/WLAN, radio komunikasi, PABX, serta tower antenna.'
  },
  {
    id: 'tl-5',
    year: '2013',
    title: 'PT. KGS',
    description: 'Transformasi badan hukum resmi menjadi PT. KREATIF GLOBAL SOLUSINDO.'
  },
  {
    id: 'tl-6',
    year: '2020',
    title: 'Izin ISP & APJII',
    description: 'Resmi berizin sebagai Penyelenggara Jasa Internet (KGSNet) dan anggota resmi APJII.',
    isHighlighted: true
  }
];

export const WHY_CHOOSE_US_CONTENT = {
  title: 'Why Choose Us?',
  paragraph1:
    'Perkembangan dunia IT yang begitu cepat, memberikan efek yang tidak kecil terhadap segala sudut kehidupan. Era internet pun datang merambah seluruh ruang lingkup aktivitas kita. Laju perkembangan IT berimbas pada semakin meningkatnya kebutuhan akan penerapan IT dalam dunia bisnis. Persaingan bisnis yang semakin keras, menuntut untuk bisa berinovasi dan menerapkan tool-tool pendukung untuk selalu bisa bertahan atau minimal tidak tertinggal jauh dan selalu terhubung. Salah satu tool pendukung itu adalah pemberdayaan teknologi informasi (TI).Untuk memenuhi kebutuhan itu, Kami hadir membawa solusi dalam bidang IT.',
  paragraph2:
    'Creative Com, merupakan perusahaan yang bergerak di bidang IT, dengan menyediakan berbagai produk untuk mendukung infrastruktur anda dalam berbagai bidang khususnya teknologi informasi. Prinsip kami adalah memberikan solusi terbaik dengan produk yang berkualitas. Kepuasan pelanggan merupakan tujuan utama kami dalam memberikan pelayanan.',
  paragraphs: [
    'Perkembangan dunia IT yang begitu cepat, memberikan efek yang tidak kecil terhadap segala sudut kehidupan. Era internet pun datang merambah seluruh ruang lingkup aktivitas kita. Laju perkembangan IT berimbas pada semakin meningkatnya kebutuhan akan penerapan IT dalam dunia bisnis. Persaingan bisnis yang semakin keras, menuntut untuk bisa berinovasi dan menerapkan tool-tool pendukung untuk selalu bisa bertahan atau minimal tidak tertinggal jauh dan selalu terhubung. Salah satu tool pendukung itu adalah pemberdayaan teknologi informasi (TI).Untuk memenuhi kebutuhan itu, Kami hadir membawa solusi dalam bidang IT.',
    'Creative Com, merupakan perusahaan yang bergerak di bidang IT, dengan menyediakan berbagai produk untuk mendukung infrastruktur anda dalam berbagai bidang khususnya teknologi informasi. Prinsip kami adalah memberikan solusi terbaik dengan produk yang berkualitas. Kepuasan pelanggan merupakan tujuan utama kami dalam memberikan pelayanan.'
  ]
};

export interface SlideshowImageItem {
  id: string;
  imagePath: string;
  caption: string;
  altText: string;
  sortOrder: number;
  isPrimary?: boolean;
  createdAt?: string;
}

export interface SlideshowCategoryItem {
  id: string;
  title: string;
  slug: string;
  shortDescription: string;
  description: string;
  coverImage: string;
  sortOrder: number;
  isActive: boolean;
  images: SlideshowImageItem[];
  createdAt: string;
  updatedAt: string;
}

export const INITIAL_SLIDESHOW_CATEGORIES: SlideshowCategoryItem[] = [
  {
    id: 'ss-cat-1',
    title: 'Fiber Optik',
    slug: 'fiber-optik',
    shortDescription: 'Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari...',
    description: `Fiber Optik adalah saluran transmisi atau sejenis kabel yang terbuat dari kaca atau plastik yang sangat halus dan lebih kecil dari sehelai rambut, dan dapat digunakan untuk mentransmisikan sinyal cahaya dari suatu tempat ke tempat lain. Sumber cahaya yang digunakan biasanya adalah dari sinar laser atau LED.

Kabel ini berdiameter lebih kurang 120 mikrometer. Cahaya yang ada di dalam serat optik tidak keluar karena indeks bias dari kaca lebih besar daripada indeks bias dari udara, karena laser mempunyai spektrum yang sangat sempit. Kecepatan transmisi fiber optik sangat tinggi sehingga sangat bagus digunakan sebagai saluran komunikasi.

Perkembangan teknologi fiber optik saat ini, telah dapat menghasilkan pelemahan (attenuation) kurang dari 20 decibels (dB)/km. Dengan lebar jalur (bandwidth) yang besar, maka mampu dalam mentransmisikan data menjadi lebih banyak dan cepat dibandingan dengan penggunaan kabel konvensional. Dengan demikian fiber optik sangat cocok digunakan terutama dalam aplikasi sistem telekomunikasi.

Sekitar 20 tahun yang lalu, kabel fiber optik telah memngambil alih dan mengubah wajah teknologi industri telepon jarak jauh maupun industri automasi dengan pengontrolan jarak jauh. Serat optik juga memberikan peranan besar membuat Internet dapat digunakan di seluruh dunia. Pada tahun 1997 fiber optik menghubungkan seluruh dunia, Fiber-Optic Link Around the Globe (FLAG) menjadi jaringan kabel terpanjang di seluruh dunia yang menyediakan infrastruktur untuk generasi internet terbaru.

Kelebihan Fiber Optik:
- Bandwidth sangat besar dengan kecepatan transmisi mencapai gigabit-per detik dan menghantarkan informasi jarak jauh tanpa pengulangan
- Biaya pemasangan dan pengoperasian yang rendah serta tingkat keamanan yang lebih tinggi
- Ukuran kecil dan ringan, sehingga hemat pemakaian ruang
- Kebal terhadap gangguan elektromagnetik dan gangguan gelombang radio
- Tidak ada tenaga listrik dan percikan api
- Tidak berkarat`,
    coverImage: 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 1,
    isActive: true,
    createdAt: '2026-10-25 08:00',
    updatedAt: '2026-10-25 08:00',
    images: [
      {
        id: 'ss-img-201',
        imagePath: 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
        caption: 'Kabel fiber optik berinti serat kaca murni untuk transmisi data cahaya berkecepatan tinggi',
        altText: 'Kabel fiber optik',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-202',
        imagePath: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
        caption: 'Kabel dan patchcord fiber optik LC/SC/FC Singlemode & Multimode di rak server',
        altText: 'Kabel dan patchcord fiber optik',
        sortOrder: 2
      },
      {
        id: 'ss-img-203',
        imagePath: 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?auto=format&fit=crop&w=1200&q=80',
        caption: 'Optical Distribution Frame (ODF) dan Optical Distribution Box (ODB/OTB) terintegrasi',
        altText: 'ODF dan optical distribution box',
        sortOrder: 3
      },
      {
        id: 'ss-img-204',
        imagePath: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
        caption: 'Proses fusion splicing dan terminasi presisi inti serat optik oleh teknisi KGS',
        altText: 'Proses splicing dan terminasi',
        sortOrder: 4
      },
      {
        id: 'ss-img-205',
        imagePath: 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
        caption: 'Perangkat pengujian redaman saluran fiber optik menggunakan OTDR & Optical Power Meter',
        altText: 'Perangkat pengujian fiber optik',
        sortOrder: 5
      },
      {
        id: 'ss-img-206',
        imagePath: 'https://images.unsplash.com/photo-1520869562399-e772f042f422?auto=format&fit=crop&w=1200&q=80',
        caption: 'Instalasi penarikan jaringan backbone fiber optik udara dan bawah tanah di lapangan',
        altText: 'Instalasi jaringan fiber optik di lapangan',
        sortOrder: 6
      }
    ]
  },
  {
    id: 'ss-cat-2',
    title: 'Guy Wire Tower',
    slug: 'guy-wire-tower',
    shortDescription: 'Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah...',
    description: `Guy Wire Tower (GWT), adalah jenis menara yang ditopang dengan tali-tali baja (wire) sebagai skur yang diangkurkan pada landasan tanah (Guywire), dimana skur ini merupakan tumpuan utama agar tower tersebut dapat berdiri.

Menara ini juga disusun atas pola batang besi sebagaimana dengan self-supporting tower, akan tetapi menara jenis guyed wire tower memiliki jenis dimensi batang yang lebih kecil dari pada jenis menara self-supporting tower.

Namun demikian jenis guyed wire tower tidak kalah kuat dari segi kekuatan menopang beban, jenis menara ini mampu menerima beban antena dan perangkat lainnya yang terpasang pada tower.

Karena sifatnya yang ditopang oleh kawat baja (wire) dan memiliki dimensi batang yang lebih kecil, menara jenis ini memiliki keuntungan antara lain dari segi biaya dan ruang, menara ini jauh lebih hemat biaya pembuatan dan membutuhkan ruang yang tidak terlalu luas dibanding dengan jenis self-supporting tower.`,
    coverImage: 'https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 2,
    isActive: true,
    createdAt: '2026-10-25 08:05',
    updatedAt: '2026-10-25 08:05',
    images: [
      {
        id: 'ss-img-301',
        imagePath: 'https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80',
        caption: 'Tampilan penuh Guy Wire Tower vertikal dengan rentangan kawat seling penopang',
        altText: 'Tampilan penuh guy wire tower',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-302',
        imagePath: 'https://images.unsplash.com/photo-1513828583688-c52646db42da?auto=format&fit=crop&w=1200&q=80',
        caption: 'Detail tali penyangga baja galvanis (guy wire), spandscrew, dan titik angkur fondasi tanah',
        altText: 'Detail tali penyangga dan titik angkur',
        sortOrder: 2
      },
      {
        id: 'ss-img-303',
        imagePath: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=80',
        caption: 'Struktur rangka modul segitiga hasil fabrikasi presisi di workshop internal PT. KGS',
        altText: 'Struktur rangka menara',
        sortOrder: 3
      },
      {
        id: 'ss-img-304',
        imagePath: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
        caption: 'Contoh instalasi menara telekomunikasi Guy Wire Tower untuk antena radio & wireless ISP di area site',
        altText: 'Contoh instalasi menara telekomunikasi',
        sortOrder: 4
      }
    ]
  },
  {
    id: 'ss-cat-3',
    title: 'SELF SUPPORTING TOWER MONOPOLE',
    slug: 'self-supporting-tower-monopole',
    shortDescription: 'Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara...',
    description: `Tower Monopoles adalah jenis menara telekomunikasi yang terdiri dari tiang tunggal yang menawarkan integritas struktural untuk berdiri secara independen sementara menolak berbagai kondisi lingkungan. Monopole menara tidak memerlukan bracing dari kabel tegangan tinggi atau bingkai logam, yang menawarkan banyak manfaat dan keuntungan.`,
    coverImage: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
    sortOrder: 3,
    isActive: true,
    createdAt: '2026-10-25 08:10',
    updatedAt: '2026-10-25 08:10',
    images: [
      {
        id: 'ss-img-101',
        imagePath: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
        caption: 'Gambar utama menara monopole telekomunikasi berstruktur tiang tunggal mandiri',
        altText: 'Gambar utama menara monopole',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-102',
        imagePath: 'https://images.unsplash.com/photo-1513828583688-c52646db42da?auto=format&fit=crop&w=1200&q=80',
        caption: 'Detail struktur baja galvanis dan sambungan flange baut kekuatan tinggi pada menara monopole',
        altText: 'Detail struktur dan sambungan menara',
        sortOrder: 2
      },
      {
        id: 'ss-img-103',
        imagePath: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=80',
        caption: 'Contoh instalasi fondasi beton bertulang dan ereksi menara monopole di lapangan',
        altText: 'Contoh instalasi di lapangan',
        sortOrder: 3
      },
      {
        id: 'ss-img-104',
        imagePath: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
        caption: 'Contoh penerapan menara monopole pada infrastruktur telekomunikasi dan antena wireless perkotaan',
        altText: 'Contoh penerapan pada infrastruktur telekomunikasi',
        sortOrder: 4
      }
    ]
  },
  {
    id: 'ss-cat-4',
    title: 'LIGHTNING PROTECTOR',
    slug: 'lightning-protector',
    shortDescription: 'Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada...',
    description: `Penyalur petir adalah rangkaian jalur yang difungsikan sebagai jalan bagi petir menuju ke permukaan bumi, tanpa merusak benda-benda yang dilewatinya. Ada 3 bagian utama pada penyalur petir:

Batang penyalur petir
Batang penyalur petir berupa batang tembaga yang ujungnya runcing. Buat batang tembaga yang runcing ini karena muatan listrik mempunyai sifat mudah berkumpul dan lepas pada ujung logam yang runcing. Dengan demikian dapat memperlancar proses tarik menarik dengan muatan listrik yang ada di awan. Batang runcing ini dipasang pada bagian puncak suatu bangunan/gedung.

Kabel konduktor
Kabel konduktor terbuat dari jalinan kawat tembaga. Diameter jalinan kabel konduktor sekitar 1 cm hingga 2 cm. Kabel konduktor berfungsi meneruskan aliran muatan listrik dari batang muatan listrik ke tanah. Kabel konduktor tersebut dipasang pada dinding di bagian luar bangunan/gedung.

Tempat pembumian
Tempat pembumian (grounding) berfungsi mengalirkan muatan listrik dari kabel konduktor ke batang pembumian (ground rod) yang tertanam di tanah. Batang pembumian terbuat dari bahan tembaga berlapis baja, dengan diameter 1,5 cm dan panjang sekitar 1,8 – 3 m.`,
    coverImage: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 4,
    isActive: true,
    createdAt: '2026-10-25 08:15',
    updatedAt: '2026-10-25 08:15',
    images: [
      {
        id: 'ss-img-401',
        imagePath: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
        caption: 'Batang penangkap petir (Air Terminal) di puncak menara/gedung untuk menerima sambaran petir',
        altText: 'Batang penangkap petir',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-402',
        imagePath: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1200&q=80',
        caption: 'Kabel konduktor jalinan kawat tembaga (BC / NYY) penyalur muatan listrik menuju pembumian',
        altText: 'Kabel konduktor',
        sortOrder: 2
      },
      {
        id: 'ss-img-403',
        imagePath: 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=1200&q=80',
        caption: 'Sistem grounding (pembumian) batang tembaga beserta pengujian Earth Resistance Tester < 2 Ohm',
        altText: 'Sistem grounding',
        sortOrder: 3
      },
      {
        id: 'ss-img-404',
        imagePath: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
        caption: 'Instalasi sistem proteksi petir eksternal pada puncak gedung bertingkat dan kawasan industri',
        altText: 'Instalasi proteksi petir di gedung',
        sortOrder: 4
      },
      {
        id: 'ss-img-405',
        imagePath: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
        caption: 'Perangkat proteksi petir dan Surge Arrester panel MDP untuk fasilitas telekomunikasi & pembangkit',
        altText: 'Perangkat proteksi petir untuk fasilitas telekomunikasi',
        sortOrder: 5
      }
    ]
  },
  {
    id: 'ss-cat-5',
    title: 'IP – PBX',
    slug: 'ip-pbx',
    shortDescription: 'IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis...',
    description: `IP PBX atau Internet Protocol Private Branch Exchange adalah PABX yang menggunakan teknologi IP. IP PBX adalah perangkat switching komunikasi telepon dan data berbasis teknologi Internet Protocol (IP) yang mengendalikan ekstension telepon analog (TDM) maupun ekstension IP Phone.

Fungsi-fungsi yang dapat dilakukan antara lain penyambungan, pengendalian, dan pemutusan hubungan telepon; translasi protokol komunikasi; serta pengaturan jalur dan media komunikasi (suara, data, dan video).

Apabila di lingkungan anda telah terdapat perangkat router, maka perangkat IP PBX dapat berbagi jaringan dengan jaringan komputer yang sudah ada. Artinya tidak diperlukan jaringan khusus untuk mentransmisikan sinyal suara maupun video, cukup menumpangkannya pada jaringan data yang ada.

Sebuah IP PBX dapat mengubah panggilan telepon melalui jaringan data dengan menggunakan teknologi VoIP (Voice over Internet Protocol), dan mengkonversi panggilan VoIP ke jaringan telepon lokal (PSTN). Dengan demikian pengguna dalam satu perusahaan dapat berkomunikasi secara internal melalui jaringan IP dan juga tetap terhubung dengan jaringan telepon publik eksternal, baik melalui saluran analog konvensional maupun ISDN.

Dengan hadirnya IP PBX, maka jaringan data dan jaringan suara dapat digabungkan menjadi satu infrastruktur terpadu yang lebih efisien dan fleksibel untuk pengembangan di masa depan.`,
    coverImage: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 5,
    isActive: true,
    createdAt: '2026-10-25 08:20',
    updatedAt: '2026-10-25 08:20',
    images: [
      {
        id: 'ss-img-501',
        imagePath: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
        caption: 'Perangkat sentral komunikasi IP-PBX enterprise untuk manajemen ratusan ekstensi telepon',
        altText: 'Perangkat IP-PBX',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-502',
        imagePath: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
        caption: 'Pesawat telepon meja IP Phone HD Voice dengan layar status ekstensi dan PoE',
        altText: 'IP Phone',
        sortOrder: 2
      },
      {
        id: 'ss-img-503',
        imagePath: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
        caption: 'Server komunikasi SIP Trunking dan gateway PSTN/VoIP pada rak komunikasi perusahaan',
        altText: 'Server komunikasi',
        sortOrder: 3
      },
      {
        id: 'ss-img-504',
        imagePath: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
        caption: 'Topologi dan arsitektur jaringan komunikasi suara & data perusahaan lintas kantor cabang',
        altText: 'Diagram jaringan komunikasi perusahaan',
        sortOrder: 4
      },
      {
        id: 'ss-img-505',
        imagePath: 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
        caption: 'Contoh instalasi sistem telepon kantor, resepsionis, dan ruang operasional BUMN/Rumah Sakit',
        altText: 'Contoh instalasi sistem telepon kantor',
        sortOrder: 5
      }
    ]
  },
  {
    id: 'ss-cat-6',
    title: 'Wireless',
    slug: 'wireless',
    shortDescription: 'Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik...',
    description: `Jaringan wireless adalah jaringan dengan menggunakan teknologi nirkabel, dalam hal ini adalah hubungan telekomunikasi suara maupun data dengan menggunakan gelombang elektromagnetik tanpa menggunakan kabel. Jaringan nirkabel ini sering dipakai untuk jaringan komputer baik pada jarak yang dekat (beberapa meter) maupun pada jarak jauh (Point-to-Point / Point-to-Multipoint antar site maupun satelit).

Jenis jaringan yang populer dalam kategori jaringan nirkabel ini meliputi: Jaringan kawasan lokal nirkabel (Wireless LAN / Wi-Fi pada frekuensi 2.4 GHz dan 5 GHz), Radio Microwave Point-to-Point, dan WiMAX.

Kelebihan:
- Mobilitas Tinggi: Pengguna dapat mengakses jaringan dan informasi dari mana saja selama masih berada dalam jangkauan sinyal Access Point
- Instalasi Mudah dan Cepat: Pembangunan jaringan yang cepat tanpa penarikan kabel rumit ke setiap meja kerja
- Fleksibilitas Tempat: Mudah dan murah untuk direlokasi serta ideal untuk area yang sulit dijangkau kabel
- Pengurangan Biaya Pemeliharaan: Biaya pemeliharaan jalur infrastruktur yang lebih efisien
- Kemudahan Ekspansi (Scalability): Mudah untuk dikembangkan seiring bertambahnya perangkat pengguna`,
    coverImage: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 6,
    isActive: true,
    createdAt: '2026-10-25 08:25',
    updatedAt: '2026-10-25 08:25',
    images: [
      {
        id: 'ss-img-601',
        imagePath: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
        caption: 'Access Point Wi-Fi Enterprise dual-band untuk cakupan jaringan lokal nirkabel berkapasitas tinggi',
        altText: 'Access point Wi-Fi',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-602',
        imagePath: 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
        caption: 'Perangkat radio wireless outdoor tahan cuaca untuk link Point-to-Point & Point-to-Multipoint',
        altText: 'Perangkat wireless outdoor',
        sortOrder: 2
      },
      {
        id: 'ss-img-603',
        imagePath: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&h=675&q=80',
        caption: 'Antena dish radio komunikasi gelombang mikro pada menara transmisi jarak jauh',
        altText: 'Antena radio komunikasi',
        sortOrder: 3
      },
      {
        id: 'ss-img-604',
        imagePath: 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
        caption: 'Instalasi jaringan Wireless LAN (WLAN) terpusat di lingkungan gedung perkantoran & kampus',
        altText: 'Instalasi jaringan wireless di kantor',
        sortOrder: 4
      },
      {
        id: 'ss-img-605',
        imagePath: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
        caption: 'Infrastruktur konektivitas nirkabel antargedung dan interkoneksi site perkebunan/tambang',
        altText: 'Infrastruktur konektivitas nirkabel antargedung',
        sortOrder: 5
      }
    ]
  },
  {
    id: 'ss-cat-7',
    title: 'Access Door',
    slug: 'access-door',
    shortDescription: 'Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID...',
    description: `Access Control merupakan sebuah alat / sistem yang digunakan sebagai kunci pintu electronic (Access Door) dengan menggunakan akses Kartu ID (RFID Proximity / Mifare), kode PIN, maupun pemindaian biometrik Sidik Jari (Fingerprint) dan Pengenalan Wajah (Face Recognition) untuk membuka dan menutup pintu secara otomatis.

Door Access Control biasanya dipasang pada ruangan atau area strategis yang membutuhkan pengamanan khusus untuk membatasi hak akses keluar-masuk personel, seperti ruang Server / Data Center, ruang Direksi, Gudang, dan Laboratorium. Sehingga hanya personel yang telah terdaftar di dalam sistem yang memiliki otorisasi untuk membuka pintu tersebut.

Sistem ini bekerja menggunakan pengunci elektromagnetik seperti Magnetic Lock (Maglock) atau Drop Bolt yang dikontrol oleh unit Access Controller serta dapat diintegrasikan dengan pencatatan waktu kehadiran (Time Attendance) dan tombol darurat (Emergency Breakglass).`,
    coverImage: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 7,
    isActive: true,
    createdAt: '2026-10-25 08:30',
    updatedAt: '2026-10-25 08:30',
    images: [
      {
        id: 'ss-img-701',
        imagePath: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
        caption: 'Mesin reader akses kartu pintar RFID dan keypad PIN untuk otorisasi pintu ruang terbatas',
        altText: 'Mesin akses kartu RFID',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-702',
        imagePath: 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=1200&q=80',
        caption: 'Perangkat pemindai biometrik Fingerprint Access Control terintegrasi log absensi kehadiran',
        altText: 'Fingerprint access control',
        sortOrder: 2
      },
      {
        id: 'ss-img-703',
        imagePath: 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1200&q=80',
        caption: 'Sistem pengunci pintu elektromagnetik (Magnetic Lock / Drop Bolt) dan tombol Exit Push Button',
        altText: 'Electronic door lock',
        sortOrder: 3
      },
      {
        id: 'ss-img-704',
        imagePath: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
        caption: 'Terminal Face Recognition (pengenalan wajah) tanpa sentuh dengan layar verifikasi cepat',
        altText: 'Face recognition terminal',
        sortOrder: 4
      },
      {
        id: 'ss-img-705',
        imagePath: 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
        caption: 'Instalasi perangkat Access Door pada pintu kaca ruang server dan kantor perbankan',
        altText: 'Instalasi access door di kantor',
        sortOrder: 5
      },
      {
        id: 'ss-img-706',
        imagePath: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
        caption: 'Sistem perangkat lunak pengelolaan hak akses pintu dan pemantauan log keluar-masuk terpusat',
        altText: 'Sistem pengelolaan akses terpusat',
        sortOrder: 6
      }
    ]
  },
  {
    id: 'ss-cat-8',
    title: 'CCTV',
    slug: 'cctv',
    shortDescription: 'CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal...',
    description: `CCTV adalah singkatan untuk closed circuit television alias televisi sirkuit tertutup. Ini adalah sistem di mana sirkuitnya tertutup dan semua elemennya terhubung. Hal ini berbeda dengan televisi siaran biasa, di mana sinyal tidak ditransmisikan secara bebas ke publik, melainkan dikirimkan secara khusus melalui kabel koaksial, jaringan serat optik, atau jaringan IP (Nirkabel/LAN) menuju monitor dan alat perekam (DVR / NVR) tertentu.

Sistem kamera pengawas CCTV digunakan secara luas untuk meningkatkan pengamanan aset gedung perkantoran, pabrik industri, pergudangan, pelabuhan, perbankan, rumah sakit, hingga hunian pribadi dengan kemampuan pemantauan visual 24 jam nonstop.

Teknologi CCTV terkini yang diimplementasikan oleh PT. Kreatif Global Solusindo mencakup kamera Analog HD (HDTVI/HDCVI) maupun IP Camera beresolusi tinggi yang dilengkapi fitur Infrared / ColorVu Night Vision, Pan-Tilt-Zoom (PTZ), deteksi pergerakan cerdas, serta akses pemantauan jarak jauh (Remote Monitoring) secara real-time melalui perangkat smartphone maupun komputer.

Kelebihan Sistem CCTV:
- Pemantauan keamanan area dalam dan luar ruangan secara real-time 24 jam penuh
- Mencegah tindak kejahatan (efek deterensi) serta menyediakan bukti rekaman digital yang akurat
- Akses monitoring jarak jauh kapan saja melalui smartphone, tablet, dan PC
- Membantu pengawasan operasional, keselamatan kerja (K3), dan produktivitas di lingkungan industri
- Integrasi penuh dengan sistem keamanan lainnya seperti Alarm Security dan Access Door`,
    coverImage: 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
    sortOrder: 8,
    isActive: true,
    createdAt: '2026-10-25 08:35',
    updatedAt: '2026-10-25 08:35',
    images: [
      {
        id: 'ss-img-801',
        imagePath: 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80',
        caption: 'Kamera pengawas CCTV Outdoor tahan cuaca (IP67) dengan fitur Night Vision untuk keamanan perimeter',
        altText: 'Kamera CCTV Outdoor',
        sortOrder: 1,
        isPrimary: true
      },
      {
        id: 'ss-img-802',
        imagePath: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
        caption: 'Sistem perekaman terpusat Network Video Recorder (NVR) & Digital Video Recorder (DVR)',
        altText: 'Perangkat Perekam NVR DVR CCTV',
        sortOrder: 2
      },
      {
        id: 'ss-img-803',
        imagePath: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
        caption: 'Layar pusat kendali pemantauan (Control Room Multi-View) untuk pengawasan area pabrik dan kantor',
        altText: 'Monitoring Multi-Kamera CCTV',
        sortOrder: 3
      },
      {
        id: 'ss-img-804',
        imagePath: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
        caption: 'Instalasi kamera Speed Dome PTZ (Pan-Tilt-Zoom) jarak jauh untuk kawasan industri dan dermaga',
        altText: 'Instalasi Kamera PTZ Gedung & Kawasan Industri',
        sortOrder: 4
      },
      {
        id: 'ss-img-805',
        imagePath: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
        caption: 'Integrasi sistem CCTV IP Camera dengan jaringan fiber optik dan pemantauan mobile jarak jauh',
        altText: 'Integrasi Jaringan IP Camera CCTV',
        sortOrder: 5
      }
    ]
  }
];





