import React, { useState, useEffect } from 'react';
import {
  StaticPageItem,
  ServiceItem,
  ProjectPhoto,
  ArticleItem,
  CompanySettings,
  WebsiteThemeConfig,
  WEBSITE_THEME_PRESETS,
  HeroSliderConfig,
  HeroSlideItem,
  DEFAULT_COMPANY_SETTINGS,
  TimelineMilestoneItem,
  CertificationItem,
  PartnerItem,
  PricingPlanItem,
  FaqItem
} from '../data/companyData';
import {
  ExportStatePayload,
  computeExportBuildId,
  syncLiveStateToServer
} from '../utils/exportSync';
import {
  Edit3,
  Palette,
  Image as ImageIcon,
  Upload,
  Check,
  X,
  Settings,
  Columns,
  Maximize2,
  Eye,
  Sparkles,
  FileText,
  Layers
} from 'lucide-react';

interface LiveWebsiteEditorBarProps {
  isLiveEditMode: boolean;
  onToggleLiveEditMode: (val: boolean) => void;
  onOpenAdminCms: (tab?: string) => void;
  pages: StaticPageItem[];
  onUpdatePages: (pages: StaticPageItem[]) => void;
  services: ServiceItem[];
  onUpdateServices: (services: ServiceItem[]) => void;
  projects: ProjectPhoto[];
  onUpdateProjects: (projects: ProjectPhoto[]) => void;
  articles: ArticleItem[];
  onUpdateArticles: (articles: ArticleItem[]) => void;
  companySettings: CompanySettings;
  onUpdateCompanySettings: (settings: CompanySettings) => void;
  companyLogo: string;
  onUpdateCompanyLogo: (logo: string) => void;
  themeConfig: WebsiteThemeConfig;
  onUpdateThemeConfig: (theme: WebsiteThemeConfig) => void;
  sliderConfig: HeroSliderConfig;
  onUpdateSliderConfig: (slider: HeroSliderConfig) => void;
  inlineOverrides: Record<string, string>;
  onUpdateInlineOverrides: (overrides: Record<string, string>) => void;
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
  exportState?: ExportStatePayload;
}

export const LiveWebsiteEditorBar: React.FC<LiveWebsiteEditorBarProps> = ({
  isLiveEditMode,
  onToggleLiveEditMode,
  onOpenAdminCms,
  pages,
  onUpdatePages,
  services,
  onUpdateServices,
  projects,
  onUpdateProjects,
  articles,
  onUpdateArticles,
  companySettings,
  onUpdateCompanySettings,
  companyLogo,
  onUpdateCompanyLogo,
  themeConfig,
  onUpdateThemeConfig,
  sliderConfig,
  onUpdateSliderConfig,
  inlineOverrides,
  onUpdateInlineOverrides,
  timeline = [],
  onUpdateTimeline,
  certifications = [],
  onUpdateCertifications,
  partners = [],
  onUpdatePartners,
  pricingPlans = [],
  onUpdatePricingPlans,
  faqs = [],
  onUpdateFaqs,
  exportState
}) => {
  const [activeDrawer, setActiveDrawer] = useState<
    null | 'pages' | 'images' | 'theme' | 'slider' | 'company'
  >(null);
  const [selectedSlug, setSelectedSlug] = useState<string>('beranda');
  const [toastMsg, setToastMsg] = useState<string>('');

  // State for direct DOM click-to-edit modal
  const [domEditTarget, setDomEditTarget] = useState<{
    type:
      | 'text'
      | 'image'
      | 'page-section'
      | 'project-card'
      | 'article-card'
      | 'service-card'
      | 'company-setting'
      | 'hero-highlight'
      | 'hero-stat'
      | 'timeline-item'
      | 'cert-item'
      | 'partner-item'
      | 'pricing-item'
      | 'faq-item';
    key: string;
    label: string;
    value: string;
    secondaryValue?: string;
    imageUrl?: string;
    entityId?: string;
    index?: number;
    elementRef?: HTMLElement;
  } | null>(null);

  const showToast = (msg: string) => {
    setToastMsg(msg);
    setTimeout(() => setToastMsg(''), 3200);
  };

  // Apply persisted inline overrides to DOM elements with data-live-key or matching original text
  useEffect(() => {
    Object.entries(inlineOverrides).forEach(([key, val]) => {
      const byAttr = document.querySelector(`[data-live-key="${CSS.escape(key)}"]`);
      if (byAttr) {
        if (byAttr.tagName === 'IMG') {
          (byAttr as HTMLImageElement).src = val;
        } else {
          byAttr.textContent = val;
        }
        return;
      }
      if (key.startsWith('orig:')) {
        const origText = key.slice(5);
        const candidates = document.querySelectorAll('main h1, main h2, main h3, main h4, main p, main span, main div, main li, footer p, footer span');
        candidates.forEach(node => {
          const htmlEl = node as HTMLElement;
          if (htmlEl.children.length === 0 && htmlEl.innerText?.trim() === origText) {
            htmlEl.textContent = val;
            htmlEl.setAttribute('data-live-key', key);
          }
        });
      }
    });
  }, [inlineOverrides, isLiveEditMode]);

  // Global click interceptor when isLiveEditMode is ON so admin can click ANY text or image on the website
  useEffect(() => {
    if (!isLiveEditMode) return;

    const handleDocumentClick = (e: MouseEvent) => {
      const target = e.target as HTMLElement | null;
      if (!target) return;

      // Ignore clicks inside the Live Editor Bar or Modals
      if (target.closest('[data-live-editor-ui="true"]')) {
        return;
      }

      // Allow normal menu navigation when clicking inside <nav>, mobile menu, or category filter tabs
      if (
        target.closest('nav') ||
        target.closest('#mobileMenu') ||
        target.closest('[data-live-nav="true"]') ||
        target.closest('[data-theme-btn]')
      ) {
        return;
      }

      // Ignore admin shortcut buttons that open Admin CMS directly
      if (
        target.closest('button') &&
        (target.innerText.includes('Admin CMS') ||
          target.innerText.includes('Tambah / Edit') ||
          target.innerText.includes('Pengaturan Seluruh Footer') ||
          target.innerText.includes('Edit Menu Navigasi') ||
          target.innerText.includes('Download Kode PHP'))
      ) {
        return;
      }

      // 1. Check if clicked an <img> or image wrapper
      const imgEl = (target.tagName === 'IMG' ? target : target.querySelector('img')) as HTMLImageElement | null;
      if (target.tagName === 'IMG' && imgEl) {
        e.preventDefault();
        e.stopPropagation();

        if (imgEl.alt?.toLowerCase().includes('logo')) {
          setDomEditTarget({
            type: 'image',
            key: 'company_logo',
            label: 'Logo Perusahaan (Header & Footer)',
            value: companyLogo || imgEl.src,
            elementRef: imgEl
          });
          return;
        }

        const key = imgEl.getAttribute('data-live-key') || `img_${imgEl.alt || 'photo'}`;
        imgEl.setAttribute('data-live-key', key);
        setDomEditTarget({
          type: 'image',
          key,
          label: `Gambar Website (${imgEl.alt || 'Foto Halaman'})`,
          value: imgEl.src,
          elementRef: imgEl
        });
        return;
      }

      // 2. Find the nearest editable text element (including inside buttons/cards in Layanan, Harga, FAQ, Portofolio, Artikel, Footer)
      const editableTags = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'SPAN', 'LI', 'DIV', 'STRONG', 'TD', 'TH', 'A', 'BUTTON'];
      let editEl: HTMLElement | null = target;
      while (editEl && editEl !== document.body) {
        if (editEl.closest('[data-live-editor-ui="true"]')) {
          editEl = null;
          break;
        }
        const tag = editEl.tagName;
        const txt = (editEl.innerText || '').trim();
        const isEligible =
          editableTags.includes(tag) &&
          txt.length > 0 &&
          txt.length < 1500 &&
          !editEl.querySelector('input, textarea, select') &&
          (['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'SPAN', 'LI', 'STRONG', 'TD', 'TH', 'A'].includes(tag) ||
            (tag === 'DIV' && editEl.children.length === 0) ||
            (tag === 'BUTTON' && editEl.children.length <= 2));
        if (isEligible) {
          break;
        }
        editEl = editEl.parentElement;
      }

      if (editEl && (editEl.innerText || '').trim().length > 0) {
        e.preventDefault();
        e.stopPropagation();

        const currentText = editEl.innerText.trim().replace(/^[“"']|[”"']$/g, '');
        const norm = currentText.toLowerCase();

        // A. Match Hero Stats (e.g. "Sejak 1997", "5 Segmen", "KGSNet", "< 2 Ohm")
        const statsList = companySettings.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];
        const statIdx = statsList.findIndex(
          st => st.value.trim().toLowerCase() === norm || st.label.trim().toLowerCase() === norm
        );
        if (statIdx !== -1) {
          setDomEditTarget({
            type: 'hero-stat',
            key: `stat_${statIdx}`,
            index: statIdx,
            label: `Statistik Utama Beranda #${statIdx + 1}`,
            value: statsList[statIdx].value,
            secondaryValue: statsList[statIdx].label,
            elementRef: editEl
          });
          return;
        }

        // B. Match Hero Highlights (4 checkmarks on Hero)
        const hlList = companySettings.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
        const hlIdx = hlList.findIndex(h => h.trim().toLowerCase() === norm);
        if (hlIdx !== -1) {
          setDomEditTarget({
            type: 'hero-highlight',
            key: `hl_${hlIdx}`,
            index: hlIdx,
            label: `Keunggulan Utama Beranda #${hlIdx + 1}`,
            value: hlList[hlIdx],
            elementRef: editEl
          });
          return;
        }

        // C. Match CompanySettings text fields (Header, Hero, Kontak, Harga, FAQ & Seluruh Footer)
        const settingsFields: Array<{ field: keyof CompanySettings; label: string }> = [
          { field: 'legalName', label: 'Nama Resmi Perusahaan (Header & Footer)' },
          { field: 'slogan', label: 'Slogan / Tagline Perusahaan' },
          { field: 'footerBrandLabel', label: 'Label Brand Footer (KGSNET)' },
          { field: 'heroBadgeText', label: 'Badge Atas Beranda' },
          { field: 'heroCtaPrimary', label: 'Teks Tombol Utama Beranda' },
          { field: 'heroCtaSecondary', label: 'Teks Tombol Kedua Beranda' },
          { field: 'heroCtaTertiary', label: 'Teks Tombol Ketiga Beranda' },
          { field: 'nocStatusLabel', label: 'Status Badge Panel NOC' },
          { field: 'nocCardTitle', label: 'Judul Panel NOC Beranda' },
          { field: 'nocCoverageText', label: 'Cakupan Area Panel NOC' },
          { field: 'vision', label: 'Visi Perusahaan' },
          { field: 'mission', label: 'Misi Perusahaan' },
          { field: 'culture', label: 'Budaya Kerja & Organisasi' },
          { field: 'commitment', label: 'Komitmen Perusahaan' },
          { field: 'addressPalembang', label: 'Alamat Kantor (Header, Kontak & Footer)' },
          { field: 'phonePalembang', label: 'Telepon Kantor (Header, Kontak & Footer)' },
          { field: 'whatsapp', label: 'WhatsApp CS Resmi (Kontak & Footer)' },
          { field: 'email', label: 'Email Resmi (Header, Kontak & Footer)' },
          { field: 'operationalHours', label: 'Jam Operasional (Header, Kontak & Footer)' },
          { field: 'footerDescription', label: 'Tentang Perusahaan di Footer' },
          { field: 'footerContactHeading', label: 'Judul Kolom Alamat Kantor & Kontak di Footer' },
          { field: 'footerAddressLabel', label: 'Sub-Judul Alamat Kantor di Footer' },
          { field: 'footerNavHeading', label: 'Judul Kolom Navigasi Halaman di Footer' },
          { field: 'footerQuickHeading', label: 'Judul Kolom Informasi & Akses Cepat di Footer' },
          { field: 'footerCertHeading', label: 'Judul Lisensi, Sertifikasi & Keanggotaan di Footer' },
          { field: 'footerCertCardStatus', label: 'Label Status Verifikasi Lisensi di Footer' },
          { field: 'footerCertBadges', label: 'Badge Lisensi, Sertifikasi & Keanggotaan di Footer' },
          { field: 'copyrightText', label: 'Teks Hak Cipta Copyright Footer' },
          { field: 'websiteCorporate', label: 'Domain Website Korporat di Footer' },
          { field: 'websiteIsp', label: 'Domain Website ISP KGSNet di Footer' },
          { field: 'pricingBannerKicker', label: 'Label Kecil Banner Halaman Harga' },
          { field: 'pricingBannerTitle', label: 'Judul Banner Bawah Halaman Harga' },
          { field: 'pricingBannerDesc', label: 'Deskripsi Banner Bawah Halaman Harga' },
          { field: 'pricingBannerCta', label: 'Tombol Banner Bawah Halaman Harga' },
          { field: 'faqSupportTitle', label: 'Judul Banner Bawah Halaman FAQ' },
          { field: 'faqSupportDesc', label: 'Deskripsi Banner Bawah Halaman FAQ' }
        ];

        for (const sf of settingsFields) {
          const val = String(companySettings[sf.field] || DEFAULT_COMPANY_SETTINGS[sf.field] || '').trim();
          if (val && (val.toLowerCase() === norm || (val.length > 10 && currentText.includes(val)))) {
            setDomEditTarget({
              type: 'company-setting',
              key: sf.field as string,
              label: `Pengaturan CMS: ${sf.label}`,
              value: val,
              elementRef: editEl
            });
            return;
          }
        }

        // D. Match Timeline Sejarah item
        const matchedTimeline = timeline.find(
          tm =>
            tm.year.trim().toLowerCase() === norm ||
            tm.title.trim().toLowerCase() === norm ||
            tm.description.trim().toLowerCase() === norm
        );
        if (matchedTimeline) {
          setDomEditTarget({
            type: 'timeline-item',
            key: matchedTimeline.id,
            entityId: matchedTimeline.id,
            label: `Sejarah Perusahaan (${matchedTimeline.year})`,
            value: matchedTimeline.title,
            secondaryValue: matchedTimeline.description,
            elementRef: target
          });
          return;
        }

        // E. Match Certification item
        const matchedCert = certifications.find(
          c =>
            c.title.trim().toLowerCase() === norm ||
            c.issuerOrBrand.trim().toLowerCase() === norm ||
            c.holder.trim().toLowerCase() === norm
        );
        if (matchedCert) {
          setDomEditTarget({
            type: 'cert-item',
            key: matchedCert.id,
            entityId: matchedCert.id,
            label: `Sertifikasi & Lisensi: ${matchedCert.title}`,
            value: matchedCert.title,
            secondaryValue: matchedCert.issuerOrBrand,
            elementRef: target
          });
          return;
        }

        // F. Match Partner item
        const matchedPartner = partners.find(
          pt => pt.name.trim().toLowerCase() === norm || pt.role.trim().toLowerCase() === norm
        );
        if (matchedPartner) {
          setDomEditTarget({
            type: 'partner-item',
            key: matchedPartner.id,
            entityId: matchedPartner.id,
            label: `Mitra Kerja & Brand: ${matchedPartner.name}`,
            value: matchedPartner.name,
            secondaryValue: matchedPartner.role,
            elementRef: target
          });
          return;
        }

        // G. Match Pricing Plan item
        const matchedPlan = pricingPlans.find(
          pl =>
            pl.name.trim().toLowerCase() === norm ||
            pl.subtitle.trim().toLowerCase() === norm ||
            pl.sla.trim().toLowerCase() === norm ||
            pl.targetAudience.trim().toLowerCase() === norm
        );
        if (matchedPlan) {
          setDomEditTarget({
            type: 'pricing-item',
            key: matchedPlan.id,
            entityId: matchedPlan.id,
            label: `Paket Harga B2B: ${matchedPlan.name}`,
            value: matchedPlan.name,
            secondaryValue: matchedPlan.subtitle,
            elementRef: target
          });
          return;
        }

        // H. Match FAQ item
        const matchedFaq = faqs.find(
          fq =>
            fq.question.trim().toLowerCase() === norm ||
            fq.answer.trim().toLowerCase() === norm
        );
        if (matchedFaq) {
          setDomEditTarget({
            type: 'faq-item',
            key: matchedFaq.id,
            entityId: matchedFaq.id,
            label: `Tanya Jawab (FAQ)`,
            value: matchedFaq.question,
            secondaryValue: matchedFaq.answer,
            elementRef: target
          });
          return;
        }

        // I. Check if this text matches any Page Title or Summary in `pages`
        const matchedPageByTitle = pages.find(
          p => p.title.trim().toLowerCase() === norm
        );
        if (matchedPageByTitle) {
          setDomEditTarget({
            type: 'page-section',
            key: matchedPageByTitle.slug,
            entityId: matchedPageByTitle.id,
            label: `Judul & Isi Halaman: ${matchedPageByTitle.title}`,
            value: matchedPageByTitle.title,
            secondaryValue: matchedPageByTitle.summary,
            elementRef: target
          });
          return;
        }

        const matchedPageBySummary = pages.find(
          p => p.summary.trim().toLowerCase() === norm
        );
        if (matchedPageBySummary) {
          setDomEditTarget({
            type: 'page-section',
            key: matchedPageBySummary.slug,
            entityId: matchedPageBySummary.id,
            label: `Judul & Ringkasan Halaman: ${matchedPageBySummary.title}`,
            value: matchedPageBySummary.title,
            secondaryValue: matchedPageBySummary.summary,
            elementRef: target
          });
          return;
        }

        // J. Check if it matches a Service item
        const matchedService = services.find(
          s =>
            s.name.trim().toLowerCase() === norm ||
            s.summary.trim().toLowerCase() === norm
        );
        if (matchedService) {
          setDomEditTarget({
            type: 'service-card',
            key: matchedService.id,
            entityId: matchedService.id,
            label: `Layanan: ${matchedService.name}`,
            value: matchedService.name,
            secondaryValue: matchedService.summary,
            imageUrl: matchedService.imageUrl || '',
            elementRef: target
          });
          return;
        }

        // K. Check if it matches a Portfolio Project item
        const matchedProject = projects.find(
          pr =>
            pr.title.trim().toLowerCase() === norm ||
            pr.description.trim().toLowerCase() === norm
        );
        if (matchedProject) {
          setDomEditTarget({
            type: 'project-card',
            key: matchedProject.id,
            entityId: matchedProject.id,
            label: `Dokumentasi Proyek: ${matchedProject.title}`,
            value: matchedProject.title,
            secondaryValue: matchedProject.description,
            imageUrl: matchedProject.imageUrl || '',
            elementRef: target
          });
          return;
        }

        // L. Check if it matches an Article item
        const matchedArticle = articles.find(
          ar =>
            ar.title.trim().toLowerCase() === norm ||
            ar.summary.trim().toLowerCase() === norm
        );
        if (matchedArticle) {
          setDomEditTarget({
            type: 'article-card',
            key: matchedArticle.id,
            entityId: matchedArticle.id,
            label: `Artikel Website: ${matchedArticle.title}`,
            value: matchedArticle.title,
            secondaryValue: matchedArticle.summary,
            imageUrl: matchedArticle.imageUrl || '',
            elementRef: target
          });
          return;
        }

        // Otherwise generic inline text element persisted by original text key
        let key = editEl.getAttribute('data-live-key');
        if (!key) {
          key = `orig:${currentText}`;
          editEl.setAttribute('data-live-key', key);
        }

        setDomEditTarget({
          type: 'text',
          key,
          label: `Edit Teks Halaman (${editEl.tagName})`,
          value: currentText,
          elementRef: editEl
        });
      }
    };

    document.addEventListener('click', handleDocumentClick, true);
    return () => document.removeEventListener('click', handleDocumentClick, true);
  }, [
    isLiveEditMode,
    pages,
    services,
    projects,
    articles,
    companyLogo,
    companySettings,
    timeline,
    certifications,
    partners,
    pricingPlans,
    faqs
  ]);

  const handleSaveDomEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!domEditTarget) return;

    if (domEditTarget.type === 'hero-stat' && typeof domEditTarget.index === 'number') {
      const statsList = [...(companySettings.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [])];
      statsList[domEditTarget.index] = {
        value: domEditTarget.value,
        label: domEditTarget.secondaryValue ?? statsList[domEditTarget.index].label
      };
      onUpdateCompanySettings({ ...companySettings, heroStats: statsList });
      showToast('Statistik Beranda berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'hero-highlight' && typeof domEditTarget.index === 'number') {
      const hlList = [...(companySettings.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [])];
      hlList[domEditTarget.index] = domEditTarget.value;
      onUpdateCompanySettings({ ...companySettings, heroHighlights: hlList });
      showToast('Keunggulan Beranda berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'company-setting') {
      onUpdateCompanySettings({
        ...companySettings,
        [domEditTarget.key]: domEditTarget.value
      });
      showToast('Pengaturan berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'timeline-item' && onUpdateTimeline) {
      onUpdateTimeline(
        timeline.map(tm =>
          tm.id === domEditTarget.entityId
            ? {
                ...tm,
                title: domEditTarget.value,
                description: domEditTarget.secondaryValue ?? tm.description
              }
            : tm
        )
      );
      showToast('Sejarah Perusahaan berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'cert-item' && onUpdateCertifications) {
      onUpdateCertifications(
        certifications.map(c =>
          c.id === domEditTarget.entityId
            ? {
                ...c,
                title: domEditTarget.value,
                issuerOrBrand: domEditTarget.secondaryValue ?? c.issuerOrBrand
              }
            : c
        )
      );
      showToast('Sertifikasi berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'partner-item' && onUpdatePartners) {
      onUpdatePartners(
        partners.map(pt =>
          pt.id === domEditTarget.entityId
            ? {
                ...pt,
                name: domEditTarget.value,
                role: domEditTarget.secondaryValue ?? pt.role
              }
            : pt
        )
      );
      showToast('Data Mitra Kerja berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'pricing-item' && onUpdatePricingPlans) {
      onUpdatePricingPlans(
        pricingPlans.map(pl =>
          pl.id === domEditTarget.entityId
            ? {
                ...pl,
                name: domEditTarget.value,
                subtitle: domEditTarget.secondaryValue ?? pl.subtitle
              }
            : pl
        )
      );
      showToast('Paket Harga B2B berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'faq-item' && onUpdateFaqs) {
      onUpdateFaqs(
        faqs.map(fq =>
          fq.id === domEditTarget.entityId
            ? {
                ...fq,
                question: domEditTarget.value,
                answer: domEditTarget.secondaryValue ?? fq.answer
              }
            : fq
        )
      );
      showToast('Tanya Jawab (FAQ) berhasil diperbarui di Website & Admin CMS!');
    } else if (domEditTarget.type === 'page-section') {
      const updatedPages = pages.map(p =>
        p.slug === domEditTarget.key
          ? {
              ...p,
              title: domEditTarget.value,
              summary: domEditTarget.secondaryValue ?? p.summary,
              updatedAt: new Date().toISOString().slice(0, 10)
            }
          : p
      );
      onUpdatePages(updatedPages);
      showToast('Teks halaman berhasil diperbarui dan otomatis tersimpan di Admin CMS!');
    } else if (domEditTarget.type === 'service-card') {
      const updatedServices = services.map(s =>
        s.id === domEditTarget.entityId
          ? {
              ...s,
              name: domEditTarget.value,
              summary: domEditTarget.secondaryValue ?? s.summary,
              imageUrl: domEditTarget.imageUrl || s.imageUrl
            }
          : s
      );
      onUpdateServices(updatedServices);
      showToast('Data Layanan & Gambar diperbarui dan tersinkron ke Admin CMS!');
    } else if (domEditTarget.type === 'project-card') {
      const updatedProjects = projects.map(pr =>
        pr.id === domEditTarget.entityId
          ? {
              ...pr,
              title: domEditTarget.value,
              description: domEditTarget.secondaryValue ?? pr.description,
              imageUrl: domEditTarget.imageUrl || pr.imageUrl
            }
          : pr
      );
      onUpdateProjects(updatedProjects);
      showToast('Dokumentasi Proyek & Foto diperbarui dan tersinkron ke Admin CMS!');
    } else if (domEditTarget.type === 'article-card') {
      const updatedArticles = articles.map(ar =>
        ar.id === domEditTarget.entityId
          ? {
              ...ar,
              title: domEditTarget.value,
              summary: domEditTarget.secondaryValue ?? ar.summary,
              imageUrl: domEditTarget.imageUrl || ar.imageUrl
            }
          : ar
      );
      onUpdateArticles(updatedArticles);
      showToast('Artikel & Gambar berhasil diperbarui dan tersinkron ke Admin CMS!');
    } else if (domEditTarget.type === 'image') {
      if (domEditTarget.key === 'company_logo') {
        onUpdateCompanyLogo(domEditTarget.value);
        localStorage.setItem('kgs_custom_logo', domEditTarget.value);
      } else {
        if (domEditTarget.elementRef && domEditTarget.elementRef.tagName === 'IMG') {
          (domEditTarget.elementRef as HTMLImageElement).src = domEditTarget.value;
        }
        onUpdateInlineOverrides({
          ...inlineOverrides,
          [domEditTarget.key]: domEditTarget.value
        });
      }
      showToast('Gambar berhasil diperbarui di Website & Admin CMS!');
    } else {
      if (domEditTarget.elementRef) {
        domEditTarget.elementRef.textContent = domEditTarget.value;
      }
      const nextOverrides = {
        ...inlineOverrides,
        [domEditTarget.key]: domEditTarget.value
      };
      onUpdateInlineOverrides(nextOverrides);
      if (exportState) {
        syncLiveStateToServer({
          ...exportState,
          inlineOverrides: nextOverrides
        });
      }
      showToast('Teks berhasil diubah di Website & tersinkron ke Paket Export ZIP/SQL!');
    }

    setDomEditTarget(null);
  };

  const handleImageFileToBase64 = (
    e: React.ChangeEvent<HTMLInputElement>,
    cb: (dataUrl: string) => void
  ) => {
    const file = e.target.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const res = ev.target?.result as string;
      if (res) cb(res);
    };
    reader.readAsDataURL(file);
  };

  const currentSelectedPage = pages.find(p => p.slug === selectedSlug) || pages[0];

  return (
    <div data-live-editor-ui="true">
      {/* Global CSS for Live Edit Mode hover outlines */}
      {isLiveEditMode && (
        <style>{`
          main h1:hover, main h2:hover, main h3:hover, main h4:hover, main p:hover, main span:hover, main li:hover, main img:hover, header img:hover, footer p:hover, footer span:hover {
            outline: 2px dashed #10b981 !important;
            outline-offset: 4px !important;
            cursor: pointer !important;
            transition: outline 0.15s ease;
          }
        `}</style>
      )}

      {/* Toast Notification */}
      {toastMsg && (
        <div className="fixed top-24 right-5 z-50 bg-emerald-600 text-white px-4 py-3 rounded-2xl shadow-2xl border border-emerald-400 flex items-center gap-2 text-xs font-bold animate-in fade-in duration-200">
          <Check className="w-4 h-4 shrink-0" />
          <span>{toastMsg}</span>
        </div>
      )}

      {/* Direct Click-to-Edit Modal when user clicks any text or image on the website */}
      {domEditTarget && (
        <div className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div className="flex items-center gap-2">
                <span className="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                  <Edit3 className="w-4 h-4" />
                </span>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    {domEditTarget.label}
                  </h3>
                  <p className="text-[11px] text-slate-500">
                    Perubahan langsung tampil di halaman website & otomatis tersimpan di Admin CMS
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setDomEditTarget(null)}
                className="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSaveDomEdit} className="space-y-4">
              {domEditTarget.type === 'image' ? (
                <div className="space-y-3">
                  <div className="h-44 rounded-2xl bg-slate-900 border border-slate-200 overflow-hidden flex items-center justify-center p-2">
                    <img
                      src={domEditTarget.value}
                      alt="Preview"
                      className="max-h-full max-w-full object-contain"
                    />
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <label className="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl cursor-pointer inline-flex items-center gap-1.5">
                      <Upload className="w-4 h-4" />
                      <span>Upload Gambar Baru dari Perangkat</span>
                      <input
                        type="file"
                        accept="image/*"
                        onChange={e =>
                          handleImageFileToBase64(e, dataUrl =>
                            setDomEditTarget({ ...domEditTarget, value: dataUrl })
                          )
                        }
                        className="hidden"
                      />
                    </label>
                  </div>
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">
                      Atau Masukkan URL Gambar
                    </label>
                    <input
                      type="text"
                      value={domEditTarget.value}
                      onChange={e =>
                        setDomEditTarget({ ...domEditTarget, value: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-xl p-2.5 font-mono text-xs"
                    />
                  </div>
                </div>
              ) : (
                <>
                  <div>
                    <label className="block font-bold text-slate-800 mb-1">
                      {domEditTarget.type === 'text' ? 'Isi Teks' : 'Judul Utama'}
                    </label>
                    <textarea
                      rows={domEditTarget.type === 'text' ? 3 : 2}
                      required
                      value={domEditTarget.value}
                      onChange={e =>
                        setDomEditTarget({ ...domEditTarget, value: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-xl p-2.5 font-semibold text-slate-900"
                    />
                  </div>

                  {domEditTarget.secondaryValue !== undefined && (
                    <div>
                      <label className="block font-bold text-slate-800 mb-1">
                        Ringkasan / Deskripsi Isi
                      </label>
                      <textarea
                        rows={4}
                        value={domEditTarget.secondaryValue}
                        onChange={e =>
                          setDomEditTarget({
                            ...domEditTarget,
                            secondaryValue: e.target.value
                          })
                        }
                        className="w-full border border-slate-300 rounded-xl p-2.5 text-slate-800"
                      />
                    </div>
                  )}

                  {domEditTarget.imageUrl !== undefined && (
                    <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                      <label className="block font-bold text-slate-800">
                        Gambar / Foto Item Ini
                      </label>
                      {domEditTarget.imageUrl && (
                        <img
                          src={domEditTarget.imageUrl}
                          alt="Preview"
                          className="h-28 w-full object-cover rounded-xl border border-slate-200"
                        />
                      )}
                      <div className="flex items-center gap-2">
                        <label className="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg cursor-pointer inline-flex items-center gap-1.5">
                          <Upload className="w-3.5 h-3.5" />
                          <span>Upload Foto Baru</span>
                          <input
                            type="file"
                            accept="image/*"
                            onChange={e =>
                              handleImageFileToBase64(e, dataUrl =>
                                setDomEditTarget({ ...domEditTarget, imageUrl: dataUrl })
                              )
                            }
                            className="hidden"
                          />
                        </label>
                        <input
                          type="text"
                          value={domEditTarget.imageUrl}
                          onChange={e =>
                            setDomEditTarget({ ...domEditTarget, imageUrl: e.target.value })
                          }
                          placeholder="Atau URL gambar..."
                          className="flex-1 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono text-[11px] bg-white"
                        />
                      </div>
                    </div>
                  )}
                </>
              )}

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setDomEditTarget(null)}
                  className="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold cursor-pointer flex items-center gap-1.5 shadow-sm"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Perubahan (Website & CMS)</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Quick Live Drawer Panel (Pages / Images / Theme / Slider) */}
      {isLiveEditMode && activeDrawer && (
        <div className="fixed bottom-16 left-4 right-4 max-w-5xl mx-auto z-50 bg-slate-900 text-white rounded-3xl border border-slate-700 shadow-2xl p-5 max-h-[75vh] overflow-y-auto text-xs">
          <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
            <div className="flex items-center gap-2">
              <Sparkles className="w-4 h-4 text-emerald-400" />
              <span className="font-extrabold text-sm">
                {activeDrawer === 'pages' && 'Edit Cepat Teks & Gambar Semua Halaman Website'}
                {activeDrawer === 'images' && 'Edit & Upload Gambar Dokumentasi Proyek, Artikel & Logo'}
                {activeDrawer === 'slider' && 'Pengaturan Slider Gambar Beranda (Sebelah / Background)'}
                {activeDrawer === 'theme' && 'Ganti Tema & Warna Website Langsung'}
              </span>
            </div>
            <button
              type="button"
              onClick={() => setActiveDrawer(null)}
              className="p-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-slate-300 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          {/* Drawer 1: Edit All Pages Text & Image */}
          {activeDrawer === 'pages' && currentSelectedPage && (
            <div className="space-y-4">
              <div className="flex flex-wrap gap-1.5">
                {pages.map(p => (
                  <button
                    key={p.id}
                    type="button"
                    onClick={() => setSelectedSlug(p.slug)}
                    className={`px-3 py-1.5 rounded-xl font-bold text-[11px] transition cursor-pointer ${
                      selectedSlug === p.slug
                        ? 'bg-emerald-500 text-slate-950'
                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                    }`}
                  >
                    {p.title}
                  </button>
                ))}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-950/70 p-4 rounded-2xl border border-slate-800">
                <div className="space-y-3">
                  <div>
                    <label className="block font-bold text-slate-300 mb-1">
                      Judul Halaman ({currentSelectedPage.slug})
                    </label>
                    <input
                      type="text"
                      value={currentSelectedPage.title}
                      onChange={e => {
                        const updated = pages.map(p =>
                          p.slug === currentSelectedPage.slug
                            ? { ...p, title: e.target.value }
                            : p
                        );
                        onUpdatePages(updated);
                      }}
                      className="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white font-bold"
                    />
                  </div>
                  <div>
                    <label className="block font-bold text-slate-300 mb-1">
                      Ringkasan / Subjudul Halaman
                    </label>
                    <textarea
                      rows={3}
                      value={currentSelectedPage.summary}
                      onChange={e => {
                        const updated = pages.map(p =>
                          p.slug === currentSelectedPage.slug
                            ? { ...p, summary: e.target.value }
                            : p
                        );
                        onUpdatePages(updated);
                      }}
                      className="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white"
                    />
                  </div>
                </div>

                <div className="space-y-3">
                  <div>
                    <label className="block font-bold text-slate-300 mb-1">
                      Isi Konten Lengkap Halaman
                    </label>
                    <textarea
                      rows={4}
                      value={currentSelectedPage.content}
                      onChange={e => {
                        const updated = pages.map(p =>
                          p.slug === currentSelectedPage.slug
                            ? { ...p, content: e.target.value }
                            : p
                        );
                        onUpdatePages(updated);
                      }}
                      className="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white"
                    />
                  </div>

                  {/* Identitas Cepat */}
                  <div className="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800">
                    <div>
                      <label className="block text-[10px] text-slate-400 mb-0.5">
                        Nama Perusahaan (Header/Footer)
                      </label>
                      <input
                        type="text"
                        value={companySettings.legalName}
                        onChange={e =>
                          onUpdateCompanySettings({
                            ...companySettings,
                            legalName: e.target.value
                          })
                        }
                        className="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] text-slate-400 mb-0.5">
                        Label Brand Footer (KGSNET)
                      </label>
                      <input
                        type="text"
                        value={companySettings.footerBrandLabel || 'KGSNET'}
                        onChange={e =>
                          onUpdateCompanySettings({
                            ...companySettings,
                            footerBrandLabel: e.target.value
                          })
                        }
                        className="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-blue-400 font-bold"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] text-slate-400 mb-0.5">
                        Nomor WhatsApp CS
                      </label>
                      <input
                        type="text"
                        value={companySettings.whatsapp}
                        onChange={e =>
                          onUpdateCompanySettings({
                            ...companySettings,
                            whatsapp: e.target.value
                          })
                        }
                        className="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-emerald-400 font-bold"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] text-slate-400 mb-0.5">
                        Alamat Kantor
                      </label>
                      <input
                        type="text"
                        value={companySettings.addressPalembang}
                        onChange={e =>
                          onUpdateCompanySettings({
                            ...companySettings,
                            addressPalembang: e.target.value
                          })
                        }
                        className="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Drawer 2: Edit & Upload Images Across Website */}
          {activeDrawer === 'images' && (
            <div className="space-y-4">
              {/* Logo Upload */}
              <div className="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                  <div className="w-16 h-12 bg-white rounded-xl flex items-center justify-center p-1 overflow-hidden">
                    {companyLogo ? (
                      <img src={companyLogo} alt="Logo" className="max-h-full max-w-full object-contain" />
                    ) : (
                      <span className="font-black text-blue-700">KGS</span>
                    )}
                  </div>
                  <div>
                    <div className="font-bold text-white">Logo Utama Website (Header & Footer)</div>
                    <div className="text-[11px] text-slate-400">Otomatis berubah di seluruh halaman & Admin CMS</div>
                  </div>
                </div>
                <label className="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl cursor-pointer flex items-center gap-1.5">
                  <Upload className="w-3.5 h-3.5" />
                  <span>Upload / Ganti Logo</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={e =>
                      handleImageFileToBase64(e, dataUrl => {
                        onUpdateCompanyLogo(dataUrl);
                        localStorage.setItem('kgs_custom_logo', dataUrl);
                        showToast('Logo perusahaan berhasil diperbarui!');
                      })
                    }
                    className="hidden"
                  />
                </label>
              </div>

              {/* Project Photos Quick Upload */}
              <div>
                <div className="font-bold text-slate-300 mb-2">
                  Foto Dokumentasi Proyek (Portofolio Website)
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  {projects.slice(0, 6).map(pr => (
                    <div
                      key={pr.id}
                      className="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-2"
                    >
                      <div className="min-w-0">
                        <div className="font-bold text-white truncate">{pr.title}</div>
                        <div className="text-[10px] text-slate-400 truncate">{pr.clientOrLocation}</div>
                      </div>
                      <label className="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-[10px] shrink-0 cursor-pointer flex items-center gap-1">
                        <Upload className="w-3 h-3" />
                        <span>Foto</span>
                        <input
                          type="file"
                          accept="image/*"
                          onChange={e =>
                            handleImageFileToBase64(e, dataUrl => {
                              const updated = projects.map(item =>
                                item.id === pr.id ? { ...item, imageUrl: dataUrl } : item
                              );
                              onUpdateProjects(updated);
                              showToast(`Foto proyek "${pr.title}" berhasil diganti!`);
                            })
                          }
                          className="hidden"
                        />
                      </label>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Drawer 3: Hero Slider Quick Manager */}
          {activeDrawer === 'slider' && (
            <div className="space-y-4">
              <div className="flex flex-wrap items-center justify-between gap-3 bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="font-bold text-slate-300">Posisi Gambar Slider Beranda:</span>
                  {(
                    [
                      { id: 'side', label: 'Di Sebelah Kanan Teks' },
                      { id: 'background', label: 'Sebagai Background Beranda' },
                      { id: 'both', label: 'Background + Sebelah Kanan' },
                      { id: 'noc', label: 'Panel NOC (Tanpa Foto)' }
                    ] as const
                  ).map(opt => (
                    <button
                      key={opt.id}
                      type="button"
                      onClick={() =>
                        onUpdateSliderConfig({ ...sliderConfig, layoutMode: opt.id })
                      }
                      className={`px-3 py-1.5 rounded-xl font-bold text-[11px] cursor-pointer transition ${
                        sliderConfig.layoutMode === opt.id
                          ? 'bg-blue-600 text-white'
                          : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                      }`}
                    >
                      {opt.label}
                    </button>
                  ))}
                </div>

                <label className="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold rounded-xl cursor-pointer flex items-center gap-1.5">
                  <Upload className="w-3.5 h-3.5" />
                  <span>+ Upload Foto Slider Baru</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={e =>
                      handleImageFileToBase64(e, dataUrl => {
                        const newSlide: HeroSlideItem = {
                          id: `slide-${Date.now()}`,
                          title: 'Infrastruktur & Proyek PT. KGS',
                          subtitle: 'Dokumentasi layanan jaringan, tower, CCTV & ISP KGSNet.',
                          badgeText: 'Dokumentasi KGS',
                          imageUrl: dataUrl,
                          sortOrder: sliderConfig.slides.length + 1,
                          isActive: true
                        };
                        onUpdateSliderConfig({
                          ...sliderConfig,
                          slides: [...sliderConfig.slides, newSlide]
                        });
                        showToast('Foto baru berhasil ditambahkan ke Slider Beranda!');
                      })
                    }
                    className="hidden"
                  />
                </label>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {sliderConfig.slides.map((s, i) => (
                  <div
                    key={s.id}
                    className="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center gap-3"
                  >
                    <img
                      src={s.imageUrl}
                      alt={s.title}
                      className="w-16 h-12 object-cover rounded-lg shrink-0"
                    />
                    <div className="min-w-0 flex-1">
                      <div className="font-bold text-white truncate">
                        #{i + 1} {s.title}
                      </div>
                      <label className="text-[10px] text-emerald-400 font-bold underline cursor-pointer">
                        Ganti Foto
                        <input
                          type="file"
                          accept="image/*"
                          onChange={e =>
                            handleImageFileToBase64(e, dataUrl => {
                              const updated = sliderConfig.slides.map(item =>
                                item.id === s.id ? { ...item, imageUrl: dataUrl } : item
                              );
                              onUpdateSliderConfig({ ...sliderConfig, slides: updated });
                              showToast(`Foto Slide #${i + 1} berhasil diperbarui!`);
                            })
                          }
                          className="hidden"
                        />
                      </label>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Drawer 4: Theme Switcher */}
          {activeDrawer === 'theme' && (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
              {WEBSITE_THEME_PRESETS.map(preset => (
                <button
                  key={preset.presetId}
                  type="button"
                  onClick={() => {
                    onUpdateThemeConfig({ ...preset });
                    showToast(`Tema website diubah menjadi: ${preset.name}`);
                  }}
                  className={`p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between gap-3 ${
                    themeConfig.presetId === preset.presetId
                      ? 'border-emerald-400 bg-slate-800'
                      : 'border-slate-800 bg-slate-950 hover:border-slate-700'
                  }`}
                >
                  <div>
                    <div className="font-bold text-white text-xs">{preset.name}</div>
                    <div className="text-[10px] text-slate-400 mt-0.5 line-clamp-1">
                      {preset.description}
                    </div>
                  </div>
                  <div className="flex items-center gap-1.5 shrink-0">
                    <span
                      className="w-5 h-5 rounded-full border border-white/20"
                      style={{ backgroundColor: preset.primaryColor }}
                    />
                    <span
                      className="w-5 h-5 rounded-full border border-white/20"
                      style={{ backgroundColor: preset.heroBgColor }}
                    />
                  </div>
                </button>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Floating Bottom Live Visual Editor Bar */}
      <div className="fixed bottom-3 left-1/2 -translate-x-1/2 z-50 max-w-[96vw] space-y-1.5">
        {isLiveEditMode && (
          <div className="bg-slate-950/95 backdrop-blur-md text-white border border-emerald-500/40 rounded-2xl px-3 py-1.5 shadow-xl flex flex-wrap items-center justify-center gap-1.5 text-[11px]">
            <span className="font-extrabold text-emerald-400 mr-1">
              📍 Pilih Menu Edit Langsung:
            </span>
            {[
              { id: 'beranda', label: 'Beranda', slug: 'beranda' },
              { id: 'tentang-kami', label: 'Tentang Kami', slug: 'tentang-kami' },
              { id: 'layanan', label: 'Layanan', slug: 'layanan' },
              { id: 'harga', label: 'Harga', slug: 'harga' },
              { id: 'faq', label: 'FAQ', slug: 'faq' },
              { id: 'portofolio', label: 'Portofolio', slug: 'portofolio' },
              { id: 'klien', label: 'Klien', slug: 'portofolio' },
              { id: 'artikel', label: 'Artikel', slug: 'blog' },
              { id: 'kontak', label: 'Kontak', slug: 'kontak' },
              { id: 'footer', label: 'Footer', slug: 'beranda' }
            ].map(menu => (
              <button
                key={menu.id}
                type="button"
                onClick={() => {
                  setSelectedSlug(menu.slug);
                  if (menu.id === 'beranda') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                  } else {
                    const el = document.getElementById(menu.id);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                  }
                }}
                className="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-emerald-600 text-slate-200 hover:text-white font-bold transition cursor-pointer"
              >
                {menu.label}
              </button>
            ))}
          </div>
        )}

        <div className="bg-slate-900/95 backdrop-blur-md text-white border border-slate-700/90 rounded-2xl px-3 py-2 shadow-2xl flex flex-wrap items-center gap-2 text-xs">
          {/* Toggle Live Edit Mode */}
          <button
            type="button"
            onClick={() => {
              onToggleLiveEditMode(!isLiveEditMode);
              if (isLiveEditMode) setActiveDrawer(null);
            }}
            className={`px-3.5 py-2 rounded-xl font-extrabold flex items-center gap-2 transition cursor-pointer ${
              isLiveEditMode
                ? 'bg-emerald-500 text-slate-950 shadow-sm'
                : 'bg-blue-600 hover:bg-blue-500 text-white'
            }`}
          >
            <Edit3 className="w-3.5 h-3.5" />
            <span>
              {isLiveEditMode
                ? 'Mode Edit Langsung: AKTIF (Klik Teks/Gambar di Semua Menu)'
                : 'Aktifkan Edit Langsung Website (Admin)'}
            </span>
          </button>

          {isLiveEditMode && (
            <>
              <button
                type="button"
                onClick={() =>
                  setActiveDrawer(activeDrawer === 'pages' ? null : 'pages')
                }
                className={`px-3 py-2 rounded-xl font-bold flex items-center gap-1.5 transition cursor-pointer ${
                  activeDrawer === 'pages'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-800 hover:bg-slate-700 text-slate-200'
                }`}
              >
                <FileText className="w-3.5 h-3.5 text-emerald-400" />
                <span>Edit Teks Halaman</span>
              </button>

              <button
                type="button"
                onClick={() =>
                  setActiveDrawer(activeDrawer === 'images' ? null : 'images')
                }
                className={`px-3 py-2 rounded-xl font-bold flex items-center gap-1.5 transition cursor-pointer ${
                  activeDrawer === 'images'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-800 hover:bg-slate-700 text-slate-200'
                }`}
              >
                <ImageIcon className="w-3.5 h-3.5 text-blue-400" />
                <span>Upload / Ganti Gambar</span>
              </button>

              <button
                type="button"
                onClick={() =>
                  setActiveDrawer(activeDrawer === 'slider' ? null : 'slider')
                }
                className={`px-3 py-2 rounded-xl font-bold flex items-center gap-1.5 transition cursor-pointer ${
                  activeDrawer === 'slider'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-800 hover:bg-slate-700 text-slate-200'
                }`}
              >
                <Columns className="w-3.5 h-3.5 text-amber-400" />
                <span>Slider Beranda</span>
              </button>

              <button
                type="button"
                onClick={() =>
                  setActiveDrawer(activeDrawer === 'theme' ? null : 'theme')
                }
                className={`px-3 py-2 rounded-xl font-bold flex items-center gap-1.5 transition cursor-pointer ${
                  activeDrawer === 'theme'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-800 hover:bg-slate-700 text-slate-200'
                }`}
              >
                <Palette className="w-3.5 h-3.5 text-pink-400" />
                <span>Tema Website</span>
              </button>
            </>
          )}

          <button
            type="button"
            onClick={() => onOpenAdminCms('website_sections')}
            className="px-3 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white font-bold flex items-center gap-1.5 cursor-pointer"
          >
            <Layers className="w-3.5 h-3.5" />
            <span>Semua Bagian & Footer</span>
          </button>

          {exportState && (
            <span
              className="hidden xl:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-blue-500/20 border border-blue-500/40 text-blue-300 text-[10px] font-mono font-bold"
              title="Versi Sinkronisasi Live Edit & Paket Export ZIP/SQL"
            >
              Sync ID: {computeExportBuildId(exportState)}
            </span>
          )}

          <button
            type="button"
            onClick={() => onOpenAdminCms('theme_slider')}
            className="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold flex items-center gap-1.5 cursor-pointer"
          >
            <Settings className="w-3.5 h-3.5 text-blue-400" />
            <span>Buka Admin CMS</span>
          </button>
        </div>
      </div>
    </div>
  );
};
