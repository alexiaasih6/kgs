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
}

export interface ProjectPhoto {
  id: string;
  title: string;
  clientOrLocation: string;
  description: string;
  category: string;
  pdfPage?: number;
  imageUrl?: string;
}

export interface CertificationItem {
  id: string;
  title: string;
  issuerOrBrand: string;
  holder: string;
  yearOrValidity?: string;
  pdfPage: number;
}

export interface PartnerItem {
  id: string;
  name: string;
  role: string;
  serviceType: string;
  pdfPage: number;
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
}

export const COMPANY_INFO = {
  legalName: "PT. KREATIF GLOBAL SOLUSINDO",
  shortName: "PT. KGS",
  brandName: "Creative Com / KGS",
  slogan: "Your Solution & Partner",
  businessFocus: "System Integrator, Security Solution & Internet Service Provider",
  ispProduct: "KGSNet",
  apjiiMember: true,
  establishedYear: 1997,
  ptIncorporationYear: 2013,
  ispLicenseYear: 2020,
  officePalembang: {
    address: "Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113",
    phone: "0711-316088 (Hunting)",
    whatsapp: "0811-782119",
    whatsappRaw: "62811782119",
    email: "info@kgs.net.id",
    website: "www.creative.co.id / www.kgs.net.id"
  },
  vision: "Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.",
  mission: "Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.",
  culture: "Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif.",
  commitment: "Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif."
};

export const DEFAULT_COMPANY_SETTINGS: CompanySettings = {
  legalName: COMPANY_INFO.legalName,
  slogan: COMPANY_INFO.slogan,
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
  footerDescription: "Creative Com berdiri sejak tahun 1997 di Palembang (CV. Creative) dan bertransformasi menjadi PT. Kreatif Global Solusindo pada tahun 2013. Menyediakan solusi terintegrasi IT, Telekomunikasi, CCTV, Tower, Penangkal Petir, serta layanan ISP resmi KGSNet.",
  footerContactHeading: "Alamat Kantor & Kontak",
  footerAddressLabel: "Alamat Kantor:",
  footerNavHeading: "Navigasi Halaman",
  footerQuickHeading: "Informasi & Akses Cepat",
  footerCertHeading: "Lisensi, Sertifikasi & Keanggotaan",
  footerCertBadges: "APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified",
  footerCertCardTitle: "Sertifikasi & Keanggotaan Resmi",
  footerCertCardStatus: "✓ Terverifikasi & Berlisensi Resmi",
  copyrightText: "© 2026 KGSNET — Creative.co.id. All Rights Reserved.",
  heroBadgeText: "PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP",
  heroCtaPrimary: "Konsultasi Sekarang",
  heroCtaSecondary: "Lihat Paket B2B",
  heroCtaTertiary: "5 Segmen Layanan →",
  heroHighlights: [
    "Anggota APJII",
    "SLA 99.5% Uptime",
    "Grounding < 2 Ohm",
    "NOC Siaga 24/7"
  ],
  heroStats: [
    { value: "1997", label: "Awal Berdiri (Creative Com)" },
    { value: "5 Segmen", label: "Layanan Solusi Terintegrasi" },
    { value: "100+", label: "Klien BUMN, Swasta & Instansi" },
    { value: "< 2 Ω", label: "Standar Grounding & Proteksi" }
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
  faqSupportDesc: "Tim support & sales engineering KGS siap berdiskusi secara langsung melalui telepon kantor atau WhatsApp resmi."
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
    id: "cat_comp_net",
    segmentNumber: 1,
    name: "Computer & Networking",
    description: "Pengadaan perangkat komputasi, instalasi jaringan kabel/nirkabel, fiber optik, dan perbaikan perangkat.",
    pdfPage: 4
  },
  {
    id: "cat_telecom",
    segmentNumber: 2,
    name: "Telecommunication",
    description: "Sistem komunikasi suara & data terintegrasi PABX/IP-PBX, radio komunikasi RIG/HT/SSB, dan radiolink.",
    pdfPage: 4
  },
  {
    id: "cat_cctv_sec",
    segmentNumber: 3,
    name: "Video Surveillance / CCTV",
    description: "Sistem pengawasan kamera keamanan Analog maupun IP Network Camera dan akses kontrol pintu.",
    pdfPage: 4
  },
  {
    id: "cat_isp",
    segmentNumber: 4,
    name: "Internet Service Provider",
    description: "Layanan koneksi internet korporat via Wireless & Fiber Optic (KGSNet), hosting, server colo & cloud.",
    pdfPage: 4
  },
  {
    id: "cat_another_service",
    segmentNumber: 5,
    name: "Another Service",
    description: "Solusi terintegrasi meliputi video conference, penangkal petir, fabrikasi tower, alarm kebakaran, dan AIS.",
    pdfPage: 4
  }
];

// Complete inventory of KGS Official Services
export const SERVICES_DATA: ServiceItem[] = [
  // Computer & Networking
  {
    id: "srv-comp-01",
    categoryId: "cat_comp_net",
    categoryName: "Computer & Networking",
    name: "PC Desktop, Notebook, Printer, Scanner & Aksesoris",
    slug: "pc-desktop-notebook-printer-scanner",
    summary: "Pengadaan komputer desktop, laptop/notebook, printer, scanner, dan aksesoris komputer bergaransi resmi.",
    description: "Layanan pengadaan unit PC Desktop, laptop / notebook, printer, scanner serta berbagai perlengkapan aksesoris komputer untuk kebutuhan perkantoran, instansi pemerintah, BUMN, perbankan, dan sektor industri.",
    features: [
      "Pengadaan PC Desktop & Notebook kantor dan industri",
      "Perangkat cetak Printer & Scanner dokumen",
      "Aksesoris dan periferal komputasi lengkap",
      "Instalasi sistem operasi & aplikasi kerja"
    ],
    brands: ["Acer", "HP", "Epson", "Canon", "Datascrip"],
    targetSectors: ["Instansi Pemerintah", "BUMN", "Perbankan", "Pendidikan", "Perusahaan Swasta"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 1
  },
  {
    id: "srv-comp-02",
    categoryId: "cat_comp_net",
    categoryName: "Computer & Networking",
    name: "Instalasi LAN, Wireless LAN, Internet Connection & HotSpot Area",
    slug: "instalasi-lan-wireless-lan-hotspot",
    summary: "Perancangan dan penarikan kabel LAN UTP/STP, konfigurasi Wireless LAN, koneksi internet dan hotspot area.",
    description: "Layanan instalasi infrastruktur jaringan lokal kabel (LAN) dan nirkabel (Wireless LAN), konfigurasi routing & switching, koneksi internet terintegrasi, serta pembuatan hotspot area untuk gedung, pabrik, perkebunan, dan instansi.",
    features: [
      "Pemasangan kabel LAN (UTP/FTP/STP) terstruktur",
      "Konfigurasi Wireless LAN & Access Point jangkauan luas",
      "Setup Hotspot Area & manajemen bandwidth pengguna",
      "Pengaturan gateway internet dan pembagian segmen IP"
    ],
    brands: ["MikroTik", "Cisco", "Ubiquiti", "TP-Link"],
    targetSectors: ["Gedung Kantor", "Pabrik / Mill", "Perkebunan", "Kampus & Sekolah", "Pemerintahan"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 2
  },
  {
    id: "srv-comp-03",
    categoryId: "cat_comp_net",
    categoryName: "Computer & Networking",
    name: "Fibre Optic Cable Networking",
    slug: "fibre-optic-cable-networking",
    summary: "Instalasi kabel fiber optik, splicing, OTDR termination, dan perancangan backbone throughput tinggi.",
    description: "Pemasangan jaringan kabel serat optik (Fiber Optic) untuk backbone antar gedung, kawasan industri, perkebunan, maupun link jarak jauh dengan kapasitas transfer data kecepatan tinggi. Didukung lisensi resmi NETVIEL InfiTe Fiber Optic Connectivity.",
    features: [
      "Penarikan kabel Fiber Optic indoor & outdoor",
      "Terminasi, konektorisasi, dan Optical Splicing",
      "Pengujian kualitas kabel serat optik dengan OTDR",
      "Didukung Authorized Reseller NETVIEL InfiTe Fiber Optic"
    ],
    brands: ["NETVIEL (Authorized Reseller)", "Furukawa", "Corning"],
    targetSectors: ["Kawasan Industri", "Perkebunan Sawit & Pulp", "Instansi Pemerintah", "ISP"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 3
  },
  {
    id: "srv-comp-04",
    categoryId: "cat_comp_net",
    categoryName: "Computer & Networking",
    name: "Komputer Service & Repair",
    slug: "komputer-service-and-repair",
    summary: "Jasa perbaikan perangkat komputer, troubleshooting hardware/software, dan maintenance sistem.",
    description: "Layanan pemeliharaan berkala, diagnosis kerusakan hardware, perbaikan motherboard/power supply, penggantian suku cadang, dan perbaikan software sistem komputer yang telah dirintis sejak 1997.",
    features: [
      "Diagnosa dan perbaikan kerusakan hardware PC / laptop",
      "Perbaikan operating system, recovery, dan penghapusan virus",
      "Pembersihan dan penggantian pasta termal berkala",
      "Layanan kontrak maintenance berkala untuk korporasi"
    ],
    brands: ["Multi-brand compatible"],
    targetSectors: ["Perkantoran", "Institusi Pendidikan", "Bisnis UKM & Korporat"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 4
  },

  // Telecommunication
  {
    id: "srv-tel-01",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "PABX / IP PBX System",
    slug: "pabx-ip-pbx-system",
    summary: "Sistem sentral telepon kantor analog, digital, maupun IP-PBX modern untuk efisiensi komunikasi suara perusahaan.",
    description: "Pemasangan, pemrograman, dan integrasi perangkat Private Automatic Branch Exchange (PABX) serta IP PBX untuk interkoneksi ekstensi internal kantor, hotel, rumah sakit, dan industri. Creative Com telah dipercaya sebagai dealer resmi Panasonic di Palembang sejak tahun 2002.",
    features: [
      "Pemasangan sentral PABX Analog, Hybrid & Full IP-PBX",
      "Konfigurasi ekstensi telepon kantor, IVR & auto attendant",
      "Integrasi Key Telephone System untuk manajemen",
      "Interkoneksi antar kantor cabang via IP Network"
    ],
    brands: ["Panasonic (Authorized Dealer)", "Ericsson-LG", "NEC", "DINSTAR"],
    targetSectors: ["Hotel & Hospitality", "Rumah Sakit", "Perbankan", "Kantor Pajak & BPN", "Gedung Kantor"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 5
  },
  {
    id: "srv-tel-02",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "SSB, Handy Talky, Repeater, RIG & Marine Radio",
    slug: "ssb-handy-talky-repeater-rig-marine",
    summary: "Solusi radio komunikasi 2 arah jarak jauh untuk operasional lapangan, tambang, perkebunan, dan maritim.",
    description: "Penyediaan dan instalasi perangkat radio komunikasi High Frequency (SSB), Very High Frequency / Ultra High Frequency (VHF/UHF), Handy Talky (HT), stasiun pangkalan (RIG), stasiun repeater penguat sinyal, serta sistem radio perkapalan (Marine Radio).",
    features: [
      "Pengadaan HT, Base Station RIG, dan Single Side Band (SSB)",
      "Pemasangan & tuning repeater radio untuk memperluas jangkauan",
      "Radio komunikasi maritim untuk kapal dan pelabuhan",
      "Integrasi antenna outdoor dan tuning VSWR optimal"
    ],
    brands: ["Motorola", "ICOM", "Alinco"],
    targetSectors: ["Perkebunan Sawit", "Pertambangan Batubara", "Pelabuhan & Pelayaran", "Pembangkit Listrik / PLTG"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 6
  },
  {
    id: "srv-tel-03",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "Radiolink Telephone Single Channel & Data",
    slug: "radiolink-telephone-single-channel-data",
    summary: "Transmisi telepon dan data point-to-point nirkabel frekuensi radio ke lokasi terpencil / remote area.",
    description: "Solusi penghubung saluran telepon dan jalur data nirkabel berbasis tautan radio (Radiolink Single Channel) untuk menjangkau lokasi perkebunan, dermaga, basecamp pertambangan, dan stasiun kepanduan yang belum terjangkau jaringan kabel konvensional.",
    features: [
      "Transmisi jalur suara telepon single-channel nirkabel",
      "Koneksi link data jarak jauh point-to-point",
      "Handal untuk kondisi remote area & perkebunan terisolir",
      "Teruji di puluhan site perkebunan, dermaga & tambang"
    ],
    brands: ["Motorola", "ICOM", "Alinco"],
    targetSectors: ["Perkebunan Sawit & Karet", "Site Tambang", "Stasiun Kepanduan Pelabuhan", "Pabrik Kelapa Sawit"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 7
  },
  {
    id: "srv-tel-04",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "Telephone Billing System",
    slug: "telephone-billing-system",
    summary: "Sistem pencatatan, kalkulasi tarif pemakaian telepon, dan pelaporan biaya komunikasi hotel/kantor.",
    description: "Perangkat lunak dan hardware antarmuka pencatat data panggilan telepon (Station Message Detail Recording / SMDR) untuk kontrol anggaran telepon kantor serta kalkulasi tagihan kamar hotel secara otomatis.",
    features: [
      "Pencatatan rincian nomor panggilan masuk dan keluar",
      "Kalkulasi tarif pulsa/durasi otomatis untuk perhotelan",
      "Laporan statistik pemakaian telepon per divisi / ekstensi",
      "Integrasi langsung dengan unit PABX"
    ],
    brands: ["Panasonic", "Ericsson-LG"],
    targetSectors: ["Hotel & Penginapan", "Perusahaan Finansial / Pembiayaan", "Instansi Pemerintah"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 8
  },
  {
    id: "srv-tel-05",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "VOIP Gateway & IP Telephone",
    slug: "voip-gateway-ip-telephone",
    summary: "Integrasi Voice over IP untuk transmisi percakapan telepon melalui jaringan data internet/intranet.",
    description: "Penggunaan gateway VOIP dan pesawat IP Phone untuk mentransmisikan percakapan suara melalui protokol internet (IP), memungkinkan komunikasi gratis antar kantor cabang melalui jaringan data internal tanpa tarif interlokal.",
    features: [
      "Setup VOIP Gateway FXS/FXO untuk menghubungkan jalur PSTN",
      "Instalasi pesawat IP Phone meja kantor",
      "Interkoneksi antar kantor pusat dan site tambang/kebun via VPN/IP",
      "Efisiensi biaya komunikasi telepon korporasi"
    ],
    brands: ["DINSTAR", "Panasonic", "Grandstream"],
    targetSectors: ["Perusahaan Multicabang", "Grup Perkebunan & Pertambangan", "BUMN"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 9
  },
  {
    id: "srv-tel-06",
    categoryId: "cat_telecom",
    categoryName: "Telecommunication",
    name: "NurseCall System",
    slug: "nursecall-system",
    summary: "Sistem pemanggil perawat terintegrasi untuk ruang rawat inap rumah sakit dan klinik bersalin.",
    description: "Instalasi perangkat sistem pemanggil perawat (Nurse Call) dengan tombol darurat di sisi tempat tidur pasien dan kamar mandi yang terhubung langsung ke master station di pos perawat (nurse station).",
    features: [
      "Bedside call button dan corded push button",
      "Emergency toilet pull cord untuk keselamatan pasien",
      "Master station display suara & visual di nurse station",
      "Lampu indikator koridor di depan setiap kamar pasien"
    ],
    brands: ["Commax", "Aiphone"],
    targetSectors: ["Rumah Sakit Umum & Daerah", "Klinik Bersalin / RSIA", "Pusat Rawat Inap"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 10
  },

  // Video Surveillance / CCTV
  {
    id: "srv-cctv-01",
    categoryId: "cat_cctv_sec",
    categoryName: "Video Surveillance / CCTV",
    name: "CCTV Analog & IP (Network) Camera",
    slug: "cctv-analog-ip-network-camera",
    summary: "Sistem pengawasan kamera keamanan beresolusi tinggi, baik sistem Analog maupun IP Camera berbasis jaringan.",
    description: "Perancangan, instalasi kabel coaxial / UTP, konfigurasi Network Video Recorder (NVR) / Digital Video Recorder (DVR), serta integrasi pemantauan kamera pengawas CCTV multi-site. PT. KGS merupakan Sony Authorized Dealer dan bermitra dengan merek global terkemuka.",
    features: [
      "Instalasi IP Network Camera resolusi Full HD hingga 4K",
      "Setup CCTV Analog High Definition (AHD/TVI/CVI)",
      "Pemasangan kabel proteksi outdoor & lightning arrester CCTV",
      "Monitoring terpusat (Central Monitoring System / CMS) & akses jarak jauh via smartphone"
    ],
    brands: ["Sony (Authorized Dealer)", "Samsung", "Panasonic", "BOSCH", "Honeywell", "Avtech", "Keeper", "G-Lenz", "Hikvision", "Dahua"],
    targetSectors: ["Pabrik Industri & Mill", "Rumah Sakit", "Perbankan", "Minimarket / Ritel", "Gedung Perkantoran", "Perpustakaan & Kampus"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 11
  },
  {
    id: "srv-cctv-02",
    categoryId: "cat_cctv_sec",
    categoryName: "Video Surveillance / CCTV",
    name: "Door Access Control System",
    slug: "door-access-control-system",
    summary: "Sistem pengaman pintu elektronik berbasis kartu RFID, PIN, dan biometrik untuk ruang penting.",
    description: "Pemasangan sistem pembatas dan pengaman akses pintu masuk (Access Control) menggunakan magnetic lock / drop bolt dengan autentikasi kartu RFID, smart key, atau biometrik untuk ruang server, brankas, kantor manajemen, dan area sensitif.",
    features: [
      "Magnetic lock & dropbolt berkekuatan tahan tinggi",
      "Reader kartu proximity / mifare dan PIN keypad",
      "Tombol exit emergency & integrasi door phone",
      "Pencatatan log keluar-masuk pegawai secara real-time"
    ],
    brands: ["Rosslare", "HID", "EntryPass"],
    targetSectors: ["Perbankan", "Rumah Sakit", "Travel Office", "Kantor Korporat", "Institusi Pendidikan"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 12
  },

  // Internet Service Provider
  {
    id: "srv-isp-01",
    categoryId: "cat_isp",
    categoryName: "Internet Service Provider",
    name: "Koneksi Internet KGSNet via Wireless & Fiber Optic",
    slug: "koneksi-internet-kgsnet-wireless-fiber-optic",
    summary: "Layanan sambungan internet dedicated dan broadband berizin resmi KGSNet melalui radio wireless dan fiber optik.",
    description: "Layanan penyedia jasa internet (ISP) resmi berizin tahun 2020 dengan brand KGSNet dan anggota resmi APJII. Menghubungkan kawasan industri, perkebunan, pelabuhan, dan perkantoran melalui media transmisi Wireless Point-to-Point maupun kabel Fiber Optic berkecepatan tinggi dengan uptime andal.",
    features: [
      "Koneksi Dedicated Internet 1:1 simetris upload & download",
      "Distribusi link melalui jaringan Wireless Tower & Fiber Optic",
      "Tergabung dalam APJII dengan peering router BGP NeuCentrIX",
      "Dukungan lastmile PT. Telkom Indonesia & tower support rekanan"
    ],
    brands: ["KGSNet (Official ISP Brand)", "APJII Member", "Telkom Indonesia (Lastmile)"],
    targetSectors: ["Perkebunan Kelapa Sawit", "Pertambangan & Pelabuhan Port", "Distrik Navigasi & KSOP", "Instansi BUMN"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 13
  },
  {
    id: "srv-isp-02",
    categoryId: "cat_isp",
    categoryName: "Internet Service Provider",
    name: "Web & Mail Hosting, Server Colocation & Cloud",
    slug: "web-mail-hosting-server-colo-cloud",
    summary: "Layanan hosting website perusahaan, email bisnis korporasi, serta colocation dan cloud server.",
    description: "Penyediaan infrastruktur hosting website domain perusahaan, email hosting korporasi yang stabil dan aman, fasilitas rak colocation server di data center, serta komputasi awan (Cloud) untuk operasional aplikasi bisnis terpusat.",
    features: [
      "Hosting website perusahaan berkecepatan tinggi",
      "Corporate email hosting dengan perlindungan spam & filter",
      "Layanan Server Colocation di Data Center terstandar",
      "Infrastruktur Cloud Server untuk aplikasi bisnis"
    ],
    brands: ["KGSNet Cloud Services"],
    targetSectors: ["Korporasi", "Lembaga Pemerintah", "Institusi Pendidikan", "Pelaku Usaha"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 14
  },
  {
    id: "srv-isp-03",
    categoryId: "cat_isp",
    categoryName: "Internet Service Provider",
    name: "Network Managed Service",
    slug: "network-managed-service",
    summary: "Layanan pengelolaan, pemeliharaan proaktif, dan monitoring performa infrastruktur jaringan pelanggan.",
    description: "Pengelolaan menyeluruh jaringan komputer dan internet perusahaan, meliputi pemeliharaan perangkat router/switch, monitoring trafik transmisi, penanganan kendala teknis cepat tanggap, serta optimalisasi bandwidth.",
    features: [
      "Pemantauan jaringan proaktif dari Network Operation Center (NOC)",
      "Manajemen perangkat router, access point, dan bandwidth limiter",
      "Dukungan teknisi lapangan responsif di wilayah Sumatera & sekitarnya",
      "Laporan statistik performa dan utilisasi trafik jaringan"
    ],
    brands: ["KGSNet Support Team"],
    targetSectors: ["Industri Sawit & Tambang", "Kantor Operasional Pelabuhan", "Jaringan Bisnis Multi-Cabang"],
    pdfPage: 14,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 15
  },

  // Another Service
  {
    id: "srv-oth-01",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Audio – Video Conference System",
    slug: "audio-video-conference-system",
    summary: "Sistem konferensi audio dan video interaktif untuk ruang rapat direksi, aula sidang, dan ruang seminar.",
    description: "Instalasi perangkat sistem telekonferensi video dan tata suara konferensi audio interaktif dengan mikrofon meja delegate/chairman, kamera PTZ tracking, dan sound processor berkualitas tinggi.",
    features: [
      "Kamera PTZ High Definition dengan optical zoom",
      "Sistem mikrofon delegate & chairman untuk meja rapat",
      "Acoustic echo cancellation untuk kejernihan suara",
      "Dapat dihubungkan ke platform video conference modern"
    ],
    brands: ["BOSCH", "TOA", "POLYCOM"],
    targetSectors: ["Instansi Pemerintah (Kanwil Kemenag)", "Klinik & Laboratorium", "Ruang Rapat Perusahaan"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 16
  },
  {
    id: "srv-oth-02",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Biometric Attendant (Mesin Absensi)",
    slug: "biometric-attendant",
    summary: "Mesin pencatat kehadiran pegawai menggunakan sidik jari (fingerprint) dan pengenalan biometrik.",
    description: "Penyediaan dan konfigurasi mesin absensi biometrik untuk mencatat waktu kehadiran karyawan secara akurat, terhubung ke software payroll dan manajemen SDM perusahaan.",
    features: [
      "Sensor sidik jari optik berkecepatan baca tinggi",
      "Koneksi kabel LAN / nirkabel untuk sinkronisasi data absensi",
      "Integrasi laporan keterlambatan dan jam kerja pegawai",
      "Dapat dipadukan dengan modul pembuka pintu (Access Door)"
    ],
    brands: ["Magic", "Solution", "I-Bio"],
    targetSectors: ["Perusahaan Swasta", "Pabrik / Manufaktur", "Rumah Sakit", "Instansi Pemerintah"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 17
  },
  {
    id: "srv-oth-03",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Tower Erection (Guywire & Self Supporting Tower)",
    slug: "tower-erection-guywire-self-supporting",
    summary: "Fabrikasi, ereksi, dan pemeliharaan menara telekomunikasi (Guywire Tower dan Self Supporting Tower).",
    description: "Layanan pabrikasi besi rangka tower di Workshop Creative Com sendiri, dilanjutkan proses ereksi (pemasangan berdiri) tower jenis Guywire (tarikan kawat seling) maupun Self Supporting Tower (SST 3/4 kaki) untuk antena radio, internet wireless, dan penangkal petir.",
    features: [
      "Pabrikasi rangka tiang tower di workshop internal Creative Com",
      "Ereksi Guywire Tower dengan kawat seling baja galvanis",
      "Ereksi Self Supporting Tower (SST) untuk beban antena berat",
      "Pengecatan standar penerbangan, lampu obstacle tower & grounding"
    ],
    brands: ["Creative Com Workshop Fabrication"],
    targetSectors: ["Internet Service Provider", "Perkebunan & Tambang", "Instansi Telekomunikasi"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 18
  },
  {
    id: "srv-oth-04",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Lightning Protection System (Penangkal Petir & Grounding)",
    slug: "lightning-protection-system",
    summary: "Instalasi sistem penangkal petir elektrostatis/konvensional, grounding pembumian, dan arrester panel.",
    description: "Perancangan dan pemasangan sistem proteksi petir eksternal dan internal untuk melindungi gedung, pembangkit listrik, pabrik sawit, dan menara telekomunikasi dari sambaran petir. Meliputi head terminal penangkal petir, instalasi kabel down conductor, pembuatan sistem grounding di bawah 2 Ohm teruji alat ukur Earth Resistance Tester, surge arrester panel MDP, serta lightning counter.",
    features: [
      "Head terminal penangkal petir elektrostatis jangkauan luas",
      "Pengeboran dan instalasi grounding rod dengan resistansi < 2 Ohm",
      "Pemasangan Surge Arrester pada Panel Distribusi Utama (MDP) & CCTV",
      "Pemasangan Lightning Strike Counter untuk merekam sambaran",
      "Pengujian terstandar menggunakan Earth Resistance Tester"
    ],
    brands: ["KURN", "ERICO", "VIKING", "THOMAS"],
    targetSectors: ["Pembangkit Listrik (PLTG)", "Rumah Sakit Umum", "Pabrik Sawit & Agro", "Area Stockpile Tambang"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 19
  },
  {
    id: "srv-oth-05",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "AIS (Automatic Identification System)",
    slug: "automatic-identification-system-ais",
    summary: "Sistem identifikasi dan pelacakan kapal otomatis berbasis AIS Receiver dan integrasi kamera pengawas.",
    description: "Pemasangan dan integrasi sistem identifikasi kapal maritim otomatis (Automatic Identification System / AIS) yang memadukan AIS Receiver VHF dengan kamera pengawas Hikvision resolusi tinggi dan antena menara. Digunakan untuk memantau pergerakan kapal tongkang, kapal motor di alur sungai, dermaga pelabuhan, dan perairan wilayah oleh Distrik Navigasi dan perusahaan logistik.",
    features: [
      "Pemasangan perangkat AIS Receiver kelas profesional",
      "Integrasi perangkat lunak monitoring posisi kapal secara real-time",
      "Kombinasi visual kamera pengawas Hikvision & data GPS kapal",
      "Pemasangan antena VHF marine pada elevasi menara optimal"
    ],
    brands: ["AIS Receiver Industry Grade", "Hikvision Integration"],
    targetSectors: ["Distrik Navigasi (DISNAV)", "Perusahaan Tongkang Batubara & Kayu", "Perusahaan Pelayaran & Dermaga"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 20
  },
  {
    id: "srv-oth-06",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Fire & Intrusion Alarm System",
    slug: "fire-intrusion-alarm-system",
    summary: "Sistem deteksi dini bahaya kebakaran dan alarm keamanan intrusi pencurian untuk gedung dan perbankan.",
    description: "Pemasangan Master Control Fire Alarm (MCFA), detektor asap (smoke detector), detektor panas (heat detector), manual call point, serta sirine evakuasi, dipadukan dengan sensor gerak dan door contact alarm intrusi untuk proteksi fasilitas vital.",
    features: [
      "Instalasi Master Control Fire Alarm (MCFA) zona / addressable",
      "Pemasangan Smoke & Heat Detector di titik strategis",
      "Sensor intrusi dan alarm anti pembobolan untuk kantor bank",
      "Integrasi sirine evakuasi dan indikator lampu darurat"
    ],
    brands: ["BOSCH", "Notifier", "Honeywell", "Hooseki"],
    targetSectors: ["Perbankan (Bank Mandiri, Bank Yudha)", "Rumah Sakit (RS Muhammadiyah, RS Siti Fatimah)", "Telekomunikasi (PT Telkom Indonesia)", "Industri Manufaktur (PT Valmet)"],
    pdfPage: 4,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 21
  },
  {
    id: "srv-oth-07",
    categoryId: "cat_another_service",
    categoryName: "Another Service",
    name: "Pengadaan Khusus: Public Address, Mesin Antri, Labor Bahasa & Solar Panel",
    slug: "pengadaan-khusus-sound-mesin-antri-labor-bahasa-solar",
    summary: "Solusi pengadaan sound system gedung, mesin antrian Quenetic, laboratorium bahasa, dan panel surya.",
    description: "Layanan proyek pengadaan spesifik yang telah sukses diselesaikan oleh Creative Com / PT. KGS, mencakup tata suara Public Address / Sound System aula, Mesin Antrian Quenetic System di kantor imigrasi dan perbankan, perangkat laboratorium bahasa sekolah, serta instalasi sistem panel tenaga surya (Solar Panel Cell) di area industri.",
    features: [
      "Public Address & Sound System gedung aula / pabrik",
      "Quenetic System (Mesin Antrian Elektronik loket kantor publik)",
      "Perangkat Laboratorium Bahasa lengkap untuk institusi pendidikan",
      "Pemasangan modul Panel Solar Sel untuk suplai energi mandiri"
    ],
    brands: ["TOA", "Quenetic", "Tier-1 Solar Cell Module"],
    targetSectors: ["Kantor Imigrasi", "Otoritas Jasa Keuangan (OJK)", "Perbankan", "Yayasan Pendidikan", "Industri Pulp & Paper"],
    pdfPage: 13,
    verificationStatus: "Standar Resmi KGS",
    adminNotes: "Standar Layanan Resmi PT. KGS",
    isPublished: true,
    order: 22
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
  {
    id: "doc-01",
    title: "Pabrikasi Guywire Tower",
    clientOrLocation: "Workshop Creative Com Palembang",
    description: "Pemotongan, perakitan dan pengelasan modul guywire tower oleh tim teknisi internal di workshop resmi Creative Com.",
    category: "Fabrikasi Tower",
    pdfPage: 18
  },
  {
    id: "doc-02",
    title: "External Lightning Protect & Grounding Installation",
    clientOrLocation: "Lokasi Proyek Industri",
    description: "Pemasangan proteksi penangkal petir eksternal dan pengeboran grounding elektroda untuk proteksi sambaran petir.",
    category: "Penangkal Petir",
    pdfPage: 18
  },
  {
    id: "doc-03",
    title: "Pengujian Nilai Tahanan Tanah (Earth Resistance Tester)",
    clientOrLocation: "Area Pembumian Proyek",
    description: "Pengukuran resistansi pembumian menggunakan Earth Resistance Tester digital untuk menjamin nilai tanah di bawah 2 Ohm.",
    category: "Penangkal Petir",
    pdfPage: 18
  },
  {
    id: "doc-04",
    title: "Arrester Panel MDP",
    clientOrLocation: "RSUD Kayu Agung",
    description: "Instalasi surge arrester panel Main Distribution Panel (MDP) untuk melindungi trafo dan instrumen medis dari lonjakan daya petir.",
    category: "Penangkal Petir",
    pdfPage: 18
  },
  {
    id: "doc-05",
    title: "Arrester CCTV",
    clientOrLocation: "PLTU Talang Duku PT. PLN",
    description: "Pemasangan video & power arrester pada kamera CCTV outdoor di area pembangkit tenaga listrik.",
    category: "CCTV & Security",
    pdfPage: 18
  },
  {
    id: "doc-06",
    title: "Instalasi Tower & Jaringan Networking BBIP Group",
    clientOrLocation: "Site STO Telkom Jambi & Mill Jambi",
    description: "Ereksi tower transmisi dan terminasi kabel jaringan komunikasi data dari Site STO Telkom menuju pabrik kelapa sawit.",
    category: "Networking & Tower",
    pdfPage: 18
  },
  {
    id: "doc-07",
    title: "Door Phone & Audio Intercom",
    clientOrLocation: "Hotel Arya Duta Palembang",
    description: "Instalasi interkom komunikasi internal door phone pada kamar dan pintu layanan hotel bintang 5.",
    category: "Telekomunikasi",
    pdfPage: 18
  },
  {
    id: "doc-08",
    title: "Pemasangan CCTV Security System Terintegrasi",
    clientOrLocation: "PT. OKI Pulp & Paper",
    description: "Pemasangan kamera CCTV keamanan, tiang pemantau dan kabel link di kawasan industri pabrik bubur kertas terbesar.",
    category: "CCTV & Security",
    pdfPage: 19
  },
  {
    id: "doc-09",
    title: "Pemasangan Jaringan Intranet Link",
    clientOrLocation: "ADR Group Jambi & Sumsel",
    description: "Pemasangan link radio komunikasi data dan router intranet antar lokasi kebun sawit dengan pusat kendali operasional.",
    category: "Networking & Tower",
    pdfPage: 19
  },
  {
    id: "doc-10",
    title: "Pemasangan Panel Solar Sel Tenaga Surya",
    clientOrLocation: "PT. OKI Pulp & Paper",
    description: "Instalasi modul panel sel surya mandiri untuk suplai energi perangkat pengawas dan sensor transmisi di area terbuka.",
    category: "Solusi Terintegrasi",
    pdfPage: 19
  },
  {
    id: "doc-11",
    title: "Pemasangan Repeater HT MUBA Electric Power",
    clientOrLocation: "Sungai Lalan, Kab. Musi Banyuasin",
    description: "Pemasangan perangkat stasiun pengulang sinyal (repeater) Handy Talky frekuensi VHF untuk koordinasi operasional tenaga listrik daerah.",
    category: "Radio Komunikasi",
    pdfPage: 20
  },
  {
    id: "doc-12",
    title: "Pemasangan Sistem Penangkal Petir Terpadu",
    clientOrLocation: "PT. PISS Langkat, Sumatera Utara",
    description: "Pengeboran sumur grounding, pemasangan head terminal petir radius proteksi luas dan pengkabelan tembaga konduktor.",
    category: "Penangkal Petir",
    pdfPage: 20
  },
  {
    id: "doc-13",
    title: "Pemasangan Jaringan PT. JAPFA COMFEED Tbk",
    clientOrLocation: "Site Jambi dan Padang",
    description: "Pemasangan infrastruktur jaringan komputer, penarikan kabel data dan konfigurasi perangkat jaringan di fasilitas pakan ternak.",
    category: "Networking & Tower",
    pdfPage: 20
  },
  {
    id: "doc-14",
    title: "Pemasangan AIS (Automatic Identification System) Maritim",
    clientOrLocation: "Tulung Selapan OKI / Perairan Sungai Musi",
    description: "Instalasi AIS Receiver terintegrasi kamera Hikvision pada ketinggian menara 28 meter untuk pemantauan lalu lintas kapal secara otomatis.",
    category: "AIS & Navigasi",
    pdfPage: 15
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
    title: "Profil & Sejarah Perusahaan",
    slug: "profil-perusahaan",
    summary: "Sejarah Creative Com sejak 1997 hingga menjadi PT. Kreatif Global Solusindo dan ISP berizin APJII.",
    content: `Creative Com adalah nama tempat usaha yang menjual produk IT dengan legalitas CV. CREATIVE, berdomisili di Palembang sejak 1997 dan sejak 2013 menjadi PT. KREATIF GLOBAL SOLUSINDO. Pada awal berdirinya Creative Com bergerak dalam bidang jasa perbaikan perangkat computer dan jaringan.

Kemudian di 1998, merambah kebidang penjualan perangkat wartel sebagai distributor perangkat wartel merk QPHONE. Lalu pada 2002, Panasonic mempercayakan CREATIVE COM sebagai salah satu dealernya di Palembang.

Dengan mengamati perkembangan teknologi dan kebutuhan masyarakat, maka sejak 2004 melayani permintaan pemasangan jaringan komputer (Cable / Wireless), radio komunikasi, pabx serta tower antenna. Dan saat ini, sejak tahun 2020, PT. KGS juga telah resmi memiliki izin sebagai perusahaan penyelenggara jasa internet (ISP) dengan nama produk KGSNet dan tergabung menjadi anggota APJII.`,
    isPublished: true,
    sortOrder: 1,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-2",
    title: "Visi, Budaya & Komitmen",
    slug: "visi-misi-budaya",
    summary: "Landasan nilai, visi kepemimpinan, model organisasi fungsional, dan komitmen kepuasan pelanggan.",
    content: `Visi Perusahaan:
Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.

Misi Perusahaan:
Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.

Budaya Kerja Organisasi:
Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif.`,
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
Apabila Anda memiliki pertanyaan mengenai kebijakan privasi ini atau ingin memperbarui data kontak Anda, silakan hubungi kami melalui email: info@kgs.net.id atau kantor operasional kami di Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113.`,
    isPublished: true,
    sortOrder: 3,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-4",
    title: "Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider",
    slug: "beranda",
    summary: "Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.",
    content: `PT. Kreatif Global Solusindo (KGS) menghadirkan infrastruktur jaringan komputer, fiber optik NETVIEL, sentral telepon PABX Panasonic, radio komunikasi 2-arah, pengawasan CCTV Sony & Hikvision, proteksi penangkal petir, serta koneksi internet KGSNet resmi APJII dengan dukungan NOC Siaga 24/7 dan SLA 99.5% Uptime.`,
    isPublished: true,
    sortOrder: 4,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-5",
    title: "Tentang PT. Kreatif Global Solusindo",
    slug: "tentang-kami",
    summary: "Berawal dari Creative Com pada tahun 1997 di Palembang hingga bertransformasi menjadi penyelenggara jasa internet dan system integrator berlisensi nasional.",
    content: `Perjalanan dedikasi teknologi dimulai sejak berdirinya CV. CREATIVE pada tahun 1997 di Palembang, berkembang menjadi distributor perangkat telekomunikasi wartel QPHONE (1998), dealer resmi PABX Panasonic (2002), instalasi jaringan LAN/WLAN, radio komunikasi & menara tower (2004), transformasi badan hukum PT. Kreatif Global Solusindo (2013), hingga resmi berizin sebagai Penyelenggara Jasa Internet (ISP) KGSNet dan anggota APJII (2020).`,
    isPublished: true,
    sortOrder: 5,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-6",
    title: "5 Segmen Layanan & Solusi Terintegrasi",
    slug: "layanan",
    summary: "Infrastruktur komputasi, telekomunikasi radio & PABX, surveillance keamanan CCTV, internet ISP KGSNet, hingga fabrikasi tower dan sistem proteksi petir.",
    content: `Katalog lengkap 5 segmen layanan utama PT. Kreatif Global Solusindo: (1) Computer & Networking, (2) Telecommunication, (3) Video Surveillance / CCTV, (4) Internet Service Provider (KGSNet), dan (5) Another Service (Tower Erection, Penangkal Petir Grounding < 2 Ohm, AIS Maritim, Fire Alarm, dan Video Conference).`,
    isPublished: true,
    sortOrder: 6,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-7",
    title: "Pilihan Paket Solusi & Layanan B2B",
    slug: "harga",
    summary: "Struktur paket transparan yang dirancang khusus untuk kebutuhan konektivitas bisnis, keamanan terpadu, infrastruktur remote site, dan pengadaan proyek pemerintah/BUMN dengan SLA terjamin.",
    content: `Setiap paket solusi bisnis B2B didukung oleh tim engineer bersertifikasi untuk melaksanakan survei lokasi (on-site survey) dan menyusun Bill of Quantity (BoQ) yang presisi sesuai kebutuhan infrastruktur perusahaan, pabrik, rumah sakit, atau instansi pemerintah.`,
    isPublished: true,
    sortOrder: 7,
    updatedAt: "2026-10-18 10:00"
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
    summary: "Bukti nyata dedikasi teknis PT. KGS: dari pabrikasi mandiri tower guywire dan pengujian grounding di bawah 2 Ohm, hingga integrasi jaringan dan sistem keamanan untuk 100+ mitra BUMN dan korporasi.",
    content: `Dokumentasi pengerjaan instalasi lapangan oleh tim teknisi PT. Kreatif Global Solusindo di berbagai sektor strategis: BUMN, perbankan, rumah sakit, perkebunan kelapa sawit, pertambangan batubara, pembangkit listrik PLN, dan navigasi pelabuhan.`,
    isPublished: true,
    sortOrder: 9,
    updatedAt: "2026-10-18 10:00"
  },
  {
    id: "page-10",
    title: "Hubungi Tim PT. Kreatif Global Solusindo",
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
    'harga.php': 'harga',
    'faq.php': 'faq',
    'portofolio.php': 'portofolio',
    'klien.php': 'portofolio',
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
    'harga': 'harga.php',
    'faq': 'faq.php',
    'portofolio': 'portofolio.php',
    'kontak': 'kontak.php',
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
    action: "Sinkronisasi Katalog Layanan",
    details: "Verifikasi 22 layanan dan 96+ portofolio klien selesai",
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
    id: "plan-internet",
    name: "Dedicated Internet B2B (KGSNet)",
    subtitle: "Koneksi simetris tanpa kuota untuk operasional kantor & pabrik",
    targetAudience: "Perkantoran, Kampus, Perbankan & Pabrik",
    badge: "Paling Diminati",
    isPopular: true,
    sla: "SLA 99.5% Uptime Garansi",
    features: [
      "Bandwidth 1:1 CIR Simetris (Upload & Download setara)",
      "Redundant Fiber Optic & Wireless Failover",
      "Dedicated IP Public /29 Static",
      "Koneksi Router BGP Peering Neucentrix Data Center",
      "Akses Monitoring MRTG Real-Time untuk IT Team",
      "Dukungan NOC 24/7 & Technical Support Siaga"
    ],
    recommendedFor: "Konektivitas stabil untuk ERP, Video Conference, Cloud Server, dan transaksi perbankan"
  },
  {
    id: "plan-security",
    name: "Integrated Surveillance & Security",
    subtitle: "Sistem keamanan visual AI, NVR terpusat & kontrol akses fisik",
    targetAudience: "Gudang Logistik, Pelabuhan, Pabrik & Rumah Sakit",
    sla: "Garansi Hardware 1-2 Tahun",
    features: [
      "IP Camera AI Human & Vehicle Detection High Resolution",
      "Penyimpanan NVR Enterprise dengan RAID Storage Redundant",
      "Access Control Biometrik Wajah / Sidik Jari & RFID",
      "Integrasi Barrier Gate Otomatis & Walkthrough Metal Detector",
      "Instalasi Kabel UTP Belden / FO Armor Standar Industri",
      "Pemeliharaan Berkala & On-Site Emergency Service"
    ],
    recommendedFor: "Monitoring keamanan perimeter luas, audit kepatuhan ISO/K3, dan pengawasan aset vital"
  },
  {
    id: "plan-tower-radio",
    name: "Tower Guywire & Radio Remote Site",
    subtitle: "Infrastruktur telekomunikasi untuk area blank spot & perkebunan",
    targetAudience: "Perkebunan Sawit, Pertambangan & Area Offshore",
    badge: "Spesialisasi Lapangan",
    sla: "Sertifikasi Grounding < 2 Ohm",
    features: [
      "Pabrikasi Mandiri Guywire Tower 20m - 40m di Workshop KGS",
      "Instalasi Grounding System Teruji < 2 Ohm (Earth Tester)",
      "Radio Komunikasi Repeater VHF/UHF, RIG & Handy Talky",
      "Wireless Point-to-Point (PTP) Jarak Jauh hingga 30+ KM",
      "Head Head Penangkal Petir & Penahan Kawat Seling Baja",
      "Tim Bersertifikasi K3 Ketinggian (Working at Height)"
    ],
    recommendedFor: "Komunikasi suara dan data antar pos jauh di area perkebunan sawit, tambang batu bara, dan pelabuhan"
  },
  {
    id: "plan-solar-power",
    name: "Solar Cell & Industrial Power Backup",
    subtitle: "Penyedia daya mandiri ramah lingkungan & perlindungan lonjakan",
    targetAudience: "Site BTS Remote, Rumah Sakit, Tambang & Cold Storage",
    sla: "Garansi Efisiensi Panel 10 Tahun",
    features: [
      "Panel Surya Monocrystalline Grade Industri Heavy Duty",
      "Smart MPPT Inverter Solar Charge Controller",
      "Battery Bank Deep Cycle Gel / LiFePO4 Kapasitas Tinggi",
      "Online Double Conversion UPS & Automatic Transfer Switch (ATS)",
      "Proteksi Penangkal Petir & Surge Arrester Khusus DC/AC",
      "Sistem Monitoring Beban & Kapasitas Baterai Real-Time"
    ],
    recommendedFor: "Lokasi operasional 24 jam tanpa aliran listrik PLN yang stabil atau proteksi beban server kritis"
  },
  {
    id: "plan-custom-tender",
    name: "Enterprise Turnkey & Pengadaan BUMN",
    subtitle: "Perancangan menyeluruh, penyusunan BoQ dan pelaksanaan tender",
    targetAudience: "Instansi Pemerintah, BUMN, Kontraktor EPC & Multinasional",
    sla: "Kontrak Sesuai Dokumen KAK / TOR",
    features: [
      "Free Site Survey Lapangan (Uji LOS, Resistansi Tanah & Topologi)",
      "Penyusunan Bill of Quantity (BoQ) & Rencana Anggaran Biaya (RAB)",
      "Kemitraan Resmi Dealer (Panasonic, Sony, Netviel, Hikvision)",
      "Kelengkapan Dokumen Legalitas (NIB, SIUP, NPWP, PKP, Sertifikasi)",
      "Manajemen Proyek Dedicated dengan Berita Acara (BAST) Resmi",
      "Skema Pembayaran Fleksibel Berdasarkan Termin Proyek"
    ],
    recommendedFor: "Kebutuhan integrasi multi-disiplin komputasi, jaringan, telekomunikasi, dan sistem proteksi menyeluruh"
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
    answer: "PT. KGS berkantor pusat di Palembang dan memiliki rekam jejak operasional luas di seluruh Sumatera Selatan, Kepulauan Bangka Belitung, Jambi, Riau, Sumatera Utara, hingga wilayah Kalimantan Barat, Kalimantan Timur, dan kawasan industri Cikarang (Jawa Barat). Kami siap mengirimkan tim teknis ke lokasi remote di seluruh Indonesia."
  },
  {
    id: "faq-2",
    category: "Internet ISP",
    question: "Apakah KGS memiliki izin resmi sebagai Penyelenggara Jasa Internet (ISP)?",
    answer: "Ya. PT. Kreatif Global Solusindo mengoperasikan layanan KGSNet dengan Izin Penyelenggaraan Jasa Akses Internet resmi dari Kementerian Komunikasi dan Informatika (Kemenkominfo) RI serta merupakan anggota resmi Asosiasi Penyelenggara Jasa Internet Indonesia (APJII) sejak tahun 2020. Kami memiliki koneksi BGP Peering langsung dengan Neucentrix Data Center."
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
    question: "Berapa Service Level Agreement (SLA) dan bagaimana dukungan teknis 24/7?",
    answer: "Kami memberikan garansi Service Level Agreement (SLA) hingga 99.5% untuk koneksi Dedicated Internet B2B. Network Operation Center (NOC) KGS beroperasi 24 jam sehari, 7 hari seminggu dengan waktu tanggap (response time) remote maksimal 15 menit dan penanganan on-site teknisi darurat maksimal 2-4 jam untuk wilayah operasional utama."
  },
  {
    id: "faq-6",
    category: "Kontrak & Legalitas",
    question: "Apakah PT. KGS melayani tender pengadaan resmi pemerintah dan BUMN?",
    answer: "Ya, kami memiliki kelengkapan legalitas dan perizinan usaha yang lengkap (NIB, SIUP, NPWP, Pengusaha Kena Pajak / PKP, Akta Perusahaan, Surat Izin Kominfo) dan telah berpengalaman mengerjakan pengadaan untuk BUMN ternama seperti PT Semen Tonasa, PT Pelindo, PT Pupuk Sriwidjaja (Pusri), perbankan nasional, serta rumah sakit dan instansi pemerintah daerah."
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
  // Navigasi Header (Atas)
  { id: 1, title: 'Beranda', url: 'index.php', location: 'header', sortOrder: 1, target: '_self', isActive: true },
  { id: 2, title: 'Tentang Kami', url: 'tentang.php', location: 'header', sortOrder: 2, target: '_self', isActive: true },
  { id: 3, title: 'Layanan', url: 'layanan.php', location: 'header', sortOrder: 3, target: '_self', isActive: true },
  { id: 4, title: 'Harga', url: 'harga.php', location: 'header', sortOrder: 4, target: '_self', isActive: true },
  { id: 5, title: 'FAQ', url: 'faq.php', location: 'header', sortOrder: 5, target: '_self', isActive: true },
  { id: 6, title: 'Portofolio', url: 'portofolio.php', location: 'header', sortOrder: 6, target: '_self', isActive: true },
  { id: 7, title: 'Kontak', url: 'kontak.php', location: 'header', sortOrder: 7, target: '_self', isActive: true },
  // Navigasi Footer (Bawah)
  { id: 8, title: 'Profil Perusahaan', url: 'page.php?slug=profil-perusahaan', location: 'footer', sortOrder: 1, target: '_self', isActive: true },
  { id: 9, title: 'Visi & Misi', url: 'page.php?slug=visi-misi-budaya', location: 'footer', sortOrder: 2, target: '_self', isActive: true },
  { id: 10, title: 'Katalog Layanan', url: 'layanan.php', location: 'footer', sortOrder: 3, target: '_self', isActive: true },
  { id: 11, title: 'Artikel Edukasi', url: 'blog.php', location: 'footer', sortOrder: 4, target: '_self', isActive: true },
  { id: 12, title: 'Kebijakan Privasi', url: 'kebijakan-privasi.php', location: 'footer', sortOrder: 5, target: '_self', isActive: true }
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
      id: 'slide-1',
      title: 'Backbone Fiber Optic & Data Center KGSNet',
      subtitle: 'Koneksi Dedicated Internet B2B SLA 99.5% terhubung langsung ke BGP Peering Neucentrix.',
      badgeText: 'ISP Resmi & Anggota APJII',
      imageUrl: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 1,
      isActive: true
    },
    {
      id: 'slide-2',
      title: 'Pabrikasi & Instalasi Tower Guywire Mandiri',
      subtitle: 'Workshop internal untuk pembangunan tower komunikasi radio & wireless PTP perkebunan dan tambang.',
      badgeText: 'Infrastruktur Telekomunikasi',
      imageUrl: 'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 2,
      isActive: true
    },
    {
      id: 'slide-3',
      title: 'Sistem CCTV AI, Access Control & Proteksi Petir < 2 Ohm',
      subtitle: 'Keamanan perimeter industri 24/7 dengan uji grounding bersertifikasi Earth Tester.',
      badgeText: 'Security & Grounding System',
      imageUrl: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
      sortOrder: 3,
      isActive: true
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



