import React, { useState, useEffect } from 'react';
import {
  StaticPageItem,
  HeroSliderConfig,
  INITIAL_HERO_SLIDER_CONFIG,
  HeroSlideItem,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS,
  SlideshowCategoryItem,
  INITIAL_SLIDESHOW_CATEGORIES
} from '../data/companyData';
import {
  CheckCircle2,
  ArrowRight,
  ChevronLeft,
  ChevronRight,
  Upload,
  Columns,
  Maximize2,
  Edit3,
  Check,
  X,
  Image as ImageIcon,
  Images
} from 'lucide-react';
import { SmartLandscapeImage } from './SlideshowSection';

interface HeroProps {
  onOpenAudit: () => void;
  onOpenConsultation: () => void;
  pageContent?: StaticPageItem;
  sliderConfig?: HeroSliderConfig;
  onUpdateSliderConfig?: (config: HeroSliderConfig) => void;
  onUpdatePageContent?: (updated: { title: string; summary: string; content: string }) => void;
  isLiveEditMode?: boolean;
  themeConfig?: WebsiteThemeConfig;
  companySettings?: CompanySettings;
  onOpenAdminSection?: (tab: string) => void;
  slideshowCategories?: SlideshowCategoryItem[];
  onSelectSlideshowSlug?: (slug: string) => void;
}

export const Hero: React.FC<HeroProps> = ({
  onOpenConsultation,
  pageContent,
  sliderConfig = INITIAL_HERO_SLIDER_CONFIG,
  onUpdateSliderConfig,
  onUpdatePageContent,
  isLiveEditMode = false,
  themeConfig = DEFAULT_WEBSITE_THEME,
  companySettings = DEFAULT_COMPANY_SETTINGS,
  onOpenAdminSection,
  slideshowCategories = INITIAL_SLIDESHOW_CATEGORIES,
  onSelectSlideshowSlug
}) => {
  const heroHighlights =
    companySettings.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
  const heroStats = companySettings.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];

  const [currentSlideIdx, setCurrentSlideIdx] = useState(0);
  const [isEditingHeroText, setIsEditingHeroText] = useState(false);
  const [heroForm, setHeroForm] = useState({
    title: '',
    summary: '',
    content: ''
  });

  // Active Slideshow Categories (the 8 items: Fiber Optik, Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE, LIGHTNING PROTECTOR, IP – PBX, Wireless, Access Door, CCTV)
  const activeSlideshowCategories = [...(slideshowCategories || INITIAL_SLIDESHOW_CATEGORIES)]
    .filter(c => c.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder);

  const totalSlideshowItems = activeSlideshowCategories.length;
  const safeCategoryIdx =
    totalSlideshowItems > 0 ? currentSlideIdx % totalSlideshowItems : 0;
  const currentCategory: SlideshowCategoryItem | undefined =
    activeSlideshowCategories[safeCategoryIdx];

  // Background slides fallback if layoutMode uses background
  const activeBgSlides = [...(sliderConfig.slides || [])]
    .filter(s => s.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder);
  const safeBgIdx = activeBgSlides.length > 0 ? currentSlideIdx % activeBgSlides.length : 0;
  const currentBgSlide: HeroSlideItem | undefined = activeBgSlides[safeBgIdx];

  // Auto-play side Slideshows (menghormati prefers-reduced-motion)
  useEffect(() => {
    if (!sliderConfig.autoPlay || totalSlideshowItems <= 1) {
      return;
    }
    if (
      typeof window !== 'undefined' &&
      window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
      return;
    }
    const timer = setInterval(() => {
      setCurrentSlideIdx(prev => (prev + 1) % totalSlideshowItems);
    }, (sliderConfig.intervalSeconds || 5) * 1000);
    return () => clearInterval(timer);
  }, [sliderConfig.autoPlay, sliderConfig.intervalSeconds, totalSlideshowItems]);

  const handlePrevSlide = () => {
    if (totalSlideshowItems <= 1) return;
    setCurrentSlideIdx(prev => (prev - 1 + totalSlideshowItems) % totalSlideshowItems);
  };

  const handleNextSlide = () => {
    if (totalSlideshowItems <= 1) return;
    setCurrentSlideIdx(prev => (prev + 1) % totalSlideshowItems);
  };

  const handleReadMoreSlideshow = (slug: string) => {
    if (onSelectSlideshowSlug) {
      onSelectSlideshowSlug(slug);
    }
    setTimeout(() => {
      const el = document.getElementById('slideshow');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 50);
  };

  const handleUploadSlideImage = (
    e: React.ChangeEvent<HTMLInputElement>,
    mode: 'new' | 'replace'
  ) => {
    const file = e.target.files?.[0];
    if (!file || !file.type.startsWith('image/') || !onUpdateSliderConfig) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const dataUrl = ev.target?.result as string;
      if (!dataUrl) return;
      if (mode === 'replace' && currentBgSlide) {
        const updatedSlides = sliderConfig.slides.map(s =>
          s.id === currentBgSlide.id ? { ...s, imageUrl: dataUrl } : s
        );
        onUpdateSliderConfig({ ...sliderConfig, slides: updatedSlides });
      } else {
        const nextOrder =
          sliderConfig.slides.length > 0
            ? Math.max(...sliderConfig.slides.map(s => s.sortOrder)) + 1
            : 1;
        const newSlide: HeroSlideItem = {
          id: `slide-${Date.now()}`,
          title: 'Dokumentasi Proyek & Infrastruktur PT. KGS',
          subtitle: 'Jaringan Fiber Optic, Tower Telekomunikasi, CCTV & Layanan ISP KGSNet.',
          badgeText: 'Dokumentasi KGS',
          imageUrl: dataUrl,
          sortOrder: nextOrder,
          isActive: true
        };
        const updatedSlides = [...sliderConfig.slides, newSlide];
        onUpdateSliderConfig({ ...sliderConfig, slides: updatedSlides });
      }
    };
    reader.readAsDataURL(file);
  };

  const openHeroTextEditor = () => {
    setHeroForm({
      title:
        pageContent?.title ||
        'System Integrator, Security Solution & Internet Provider',
      summary:
        pageContent?.summary ||
        'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.',
      content: pageContent?.content || ''
    });
    setIsEditingHeroText(true);
  };

  const saveHeroTextEditor = (e: React.FormEvent) => {
    e.preventDefault();
    if (onUpdatePageContent) {
      onUpdatePageContent(heroForm);
    }
    setIsEditingHeroText(false);
  };

  const showBackgroundSlider =
    (sliderConfig.layoutMode === 'background' || sliderConfig.layoutMode === 'both') &&
    activeBgSlides.length > 0;

  return (
    <section
      id="beranda"
      style={{ backgroundColor: themeConfig.heroBgColor }}
      className="text-white relative overflow-hidden border-b border-slate-800 transition-colors duration-300"
    >
      {/* Optional Background Image Slider when layoutMode is 'background' or 'both' */}
      {showBackgroundSlider && currentBgSlide && (
        <div className="absolute inset-0 z-0">
          {activeBgSlides.map((slide, idx) => (
            <div
              key={slide.id}
              className={`absolute inset-0 transition-opacity duration-700 ${
                idx === safeBgIdx ? 'opacity-100' : 'opacity-0 pointer-events-none'
              }`}
            >
              <img
                src={slide.imageUrl}
                alt={slide.title}
                className="w-full h-full object-cover"
              />
            </div>
          ))}
          <div
            className="absolute inset-0 transition-opacity duration-300"
            style={{
              backgroundColor: themeConfig.heroBgColor,
              opacity: (sliderConfig.overlayOpacity || 75) / 100
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/60 to-slate-950/40" />
        </div>
      )}

      {/* Background Grid Pattern */}
      <div className="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-35 pointer-events-none z-0"></div>

      {/* Live Edit Floating Bar on Top-Right of Hero */}
      {isLiveEditMode && (
        <div className="relative z-30 bg-slate-950/90 border-b border-emerald-500/40 px-4 py-2.5">
          <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 text-xs">
            <div className="flex items-center gap-2 text-emerald-400 font-bold">
              <Edit3 className="w-3.5 h-3.5" />
              <span>Kontrol Cepat Beranda &amp; Slideshow (Tersinkron Otomatis ke Admin CMS):</span>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <button
                type="button"
                onClick={openHeroTextEditor}
                className="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg flex items-center gap-1.5 cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>Edit Teks Beranda</span>
              </button>

              {onOpenAdminSection && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('slideshow')}
                  className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg flex items-center gap-1.5 cursor-pointer"
                >
                  <Images className="w-3.5 h-3.5" />
                  <span>Kelola 8 Kategori &amp; Multi-Gambar Slideshow</span>
                </button>
              )}

              <button
                type="button"
                onClick={() =>
                  onUpdateSliderConfig?.({
                    ...sliderConfig,
                    layoutMode: sliderConfig.layoutMode === 'side' ? 'both' : 'side'
                  })
                }
                className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white border border-slate-600 font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer"
              >
                {sliderConfig.layoutMode === 'side' ? (
                  <>
                    <Maximize2 className="w-3.5 h-3.5 text-amber-400" />
                    <span>Aktifkan juga Background Slider</span>
                  </>
                ) : (
                  <>
                    <Columns className="w-3.5 h-3.5 text-blue-400" />
                    <span>Hanya Slideshow di Samping Teks</span>
                  </>
                )}
              </button>

              <label className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer border border-slate-700">
                <Upload className="w-3.5 h-3.5" />
                <span>+ Foto Background</span>
                <input
                  type="file"
                  accept="image/*"
                  onChange={e => handleUploadSlideImage(e, 'new')}
                  className="hidden"
                />
              </label>

              {currentBgSlide && showBackgroundSlider && (
                <label className="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg flex items-center gap-1.5 cursor-pointer">
                  <ImageIcon className="w-3.5 h-3.5" />
                  <span>Ganti Background</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={e => handleUploadSlideImage(e, 'replace')}
                    className="hidden"
                  />
                </label>
              )}
            </div>
          </div>
        </div>
      )}

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative z-10">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
          {/* LEFT COLUMN (7 cols):
              PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP
              System Integrator, Security Solution & Internet Provider
              Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.
          */}
          <div className="lg:col-span-7 space-y-6">
            {/* Category label / kicker */}
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-blue-900/40 border border-blue-700/50 rounded-lg text-xs font-extrabold tracking-wider text-blue-300 uppercase">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>
                {companySettings.heroBadgeText ||
                  'PT. KREATIF GLOBAL SOLUSINDO · SYSTEM INTEGRATOR & ISP'}
              </span>
            </div>

            {isEditingHeroText ? (
              <form
                onSubmit={saveHeroTextEditor}
                className="bg-slate-900/95 border-2 border-emerald-500 rounded-2xl p-5 space-y-3 text-xs shadow-2xl"
              >
                <div className="flex items-center justify-between border-b border-slate-800 pb-2">
                  <span className="font-bold text-emerald-400 uppercase tracking-wider">
                    Edit Langsung Teks Utama Beranda (Tersimpan ke CMS)
                  </span>
                  <button
                    type="button"
                    onClick={() => setIsEditingHeroText(false)}
                    className="text-slate-400 hover:text-white cursor-pointer"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
                <div>
                  <label className="block font-semibold text-slate-300 mb-1">
                    Judul Utama (Headline Beranda)
                  </label>
                  <input
                    type="text"
                    required
                    value={heroForm.title}
                    onChange={e => setHeroForm({ ...heroForm, title: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-white font-bold text-sm"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-300 mb-1">
                    Ringkasan / Deskripsi Utama Beranda
                  </label>
                  <textarea
                    rows={4}
                    value={heroForm.summary}
                    onChange={e => setHeroForm({ ...heroForm, summary: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-white"
                  />
                </div>
                <div className="flex justify-end gap-2 pt-2">
                  <button
                    type="button"
                    onClick={() => setIsEditingHeroText(false)}
                    className="px-3.5 py-2 bg-slate-800 text-slate-300 rounded-xl font-semibold cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold rounded-xl flex items-center gap-1.5 cursor-pointer"
                  >
                    <Check className="w-4 h-4" />
                    <span>Simpan ke Website &amp; Admin CMS</span>
                  </button>
                </div>
              </form>
            ) : (
              <div
                onClick={() => {
                  if (isLiveEditMode) openHeroTextEditor();
                }}
                className={`space-y-4 rounded-2xl transition ${
                  isLiveEditMode
                    ? 'p-3 -m-3 border-2 border-dashed border-emerald-400/70 hover:bg-slate-900/50 cursor-pointer relative group'
                    : ''
                }`}
              >
                {isLiveEditMode && (
                  <span className="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-500 text-slate-950 font-extrabold text-[10px] rounded-md shadow-xs">
                    <Edit3 className="w-3 h-3" /> Klik untuk Edit Judul &amp; Isi Beranda
                  </span>
                )}

                {/* Headline */}
                <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.15] text-white">
                  {pageContent?.title ||
                    'System Integrator, Security Solution & Internet Provider'}
                </h1>

                {/* Description */}
                <p className="text-base sm:text-lg text-slate-200 leading-relaxed max-w-2xl">
                  {pageContent?.summary ||
                    'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.'}
                </p>
              </div>
            )}

            {/* CTAs */}
            <div className="flex flex-wrap items-center gap-3.5 pt-2">
              <button
                onClick={onOpenConsultation}
                style={{ backgroundColor: themeConfig.primaryColor }}
                className="px-6 py-3.5 hover:opacity-95 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer"
              >
                <span>
                  {companySettings.heroCtaPrimary || DEFAULT_COMPANY_SETTINGS.heroCtaPrimary}
                </span>
                <ArrowRight className="w-4 h-4" />
              </button>

              <a
                href="#harga"
                className="px-6 py-3.5 bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-sm rounded-xl transition-all cursor-pointer flex items-center gap-1.5"
              >
                <span>
                  {companySettings.heroCtaSecondary || DEFAULT_COMPANY_SETTINGS.heroCtaSecondary}
                </span>
              </a>

              <a
                href="#layanan"
                className="px-5 py-3.5 text-xs text-blue-300 hover:text-white font-medium transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <span>
                  {companySettings.heroCtaTertiary || DEFAULT_COMPANY_SETTINGS.heroCtaTertiary}
                </span>
              </a>
            </div>

            {/* Key Trust Highlights */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-6 border-t border-slate-800/80 text-xs text-slate-300">
              {heroHighlights.map((hl, idx) => (
                <div key={idx} className="flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>{hl}</span>
                </div>
              ))}
            </div>
          </div>

          {/* RIGHT COLUMN (5 cols):
              Interactive SLIDESHOWS displayed right beside the Hero text!
              Cycles through all 8 items (Fiber Optik, Guy Wire Tower, SELF SUPPORTING TOWER MONOPOLE,
              LIGHTNING PROTECTOR, IP – PBX, Wireless, Access Door, CCTV) with Read More button.
          */}
          <div className="lg:col-span-5">
            {currentCategory && (
              <div className="bg-slate-900/95 rounded-2xl border border-slate-700/80 overflow-hidden shadow-2xl backdrop-blur-sm">
                {/* Interactive Image Slider Box (Rasio Landscape 16:9 Stabil + Portrait Blur Detection) */}
                <div
                  onClick={() => handleReadMoreSlideshow(currentCategory.slug)}
                  style={{ aspectRatio: '16 / 9' }}
                  className="relative w-full min-h-[220px] sm:min-h-[260px] bg-slate-950 overflow-hidden group cursor-pointer"
                >
                  <SmartLandscapeImage
                    src={
                      (currentCategory as any).primaryImageUrl ||
                      currentCategory.coverImage ||
                      (currentCategory.images?.[0] as any)?.imageUrl ||
                      currentCategory.images?.[0]?.imagePath ||
                      'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80'
                    }
                    alt={currentCategory.title}
                    priority={true}
                    className="w-full h-full"
                    imgClassName="group-hover:scale-105"
                  />
                  <div className="absolute inset-0 z-20 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent pointer-events-none" />

                  {/* Top Badge & Slide Counter */}
                  <div className="absolute top-3.5 left-3.5 right-3.5 flex items-center justify-between gap-2 z-30">
                    <span
                      style={{ backgroundColor: themeConfig.primaryColor }}
                      className="px-3 py-1 rounded-lg text-[11px] font-bold text-white shadow-md flex items-center gap-1.5"
                    >
                      <Images className="w-3.5 h-3.5" />
                      <span>Slideshow • {currentCategory.images?.length || 0} Gambar</span>
                    </span>
                    <span className="px-2.5 py-1 rounded-lg bg-slate-950/85 border border-slate-700 text-[11px] font-mono text-slate-200">
                      {safeCategoryIdx + 1} / {totalSlideshowItems}
                    </span>
                  </div>

                  {/* Prev / Next Navigation Buttons */}
                  {totalSlideshowItems > 1 && (
                    <>
                      <button
                        type="button"
                        onClick={e => {
                          e.stopPropagation();
                          handlePrevSlide();
                        }}
                        className="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/80 hover:bg-blue-600 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg z-30"
                        aria-label="Slide Sebelumnya"
                      >
                        <ChevronLeft className="w-4 h-4" />
                      </button>
                      <button
                        type="button"
                        onClick={e => {
                          e.stopPropagation();
                          handleNextSlide();
                        }}
                        className="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/80 hover:bg-blue-600 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg z-30"
                        aria-label="Slide Selanjutnya"
                      >
                        <ChevronRight className="w-4 h-4" />
                      </button>
                    </>
                  )}
                </div>

                {/* Slide Title, Snippet & Read More Button (Tinggi Terkunci Presisi agar Semua 8 Slide Sama Rata) */}
                <div className="p-5 sm:p-6 space-y-4">
                  <div>
                    <h3
                      onClick={() => handleReadMoreSlideshow(currentCategory.slug)}
                      title={currentCategory.title}
                      className="text-base sm:text-lg font-extrabold text-white hover:text-blue-400 transition-colors cursor-pointer leading-snug line-clamp-1 min-h-[1.75rem]"
                    >
                      {currentCategory.title}
                    </h3>
                    <p className="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed line-clamp-3 min-h-[3.75rem]">
                      {currentCategory.shortDescription}
                    </p>
                  </div>

                  {/* Read More CTA + Slide Indicators */}
                  <div className="pt-3 border-t border-slate-800 flex items-center justify-between gap-3">
                    <button
                      type="button"
                      onClick={() => handleReadMoreSlideshow(currentCategory.slug)}
                      style={{ backgroundColor: themeConfig.primaryColor }}
                      className="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-bold text-xs sm:text-sm hover:opacity-95 transition shadow-md cursor-pointer"
                    >
                      <span>Read More</span>
                      <ArrowRight className="w-4 h-4" />
                    </button>

                    {/* Dots for all 8 Slides */}
                    <div className="flex items-center gap-1.5 flex-wrap justify-end">
                      {activeSlideshowCategories.map((cat, idx) => (
                        <button
                          key={cat.id}
                          type="button"
                          onClick={() => setCurrentSlideIdx(idx)}
                          title={cat.title}
                          className={`h-2 rounded-full transition-all cursor-pointer ${
                            idx === safeCategoryIdx
                              ? 'w-6 bg-blue-400'
                              : 'w-2 bg-slate-700 hover:bg-slate-500'
                          }`}
                          aria-label={`Pilih Slide ${cat.title}`}
                        />
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Metrics Bar below Hero */}
      <div className="bg-slate-950/90 backdrop-blur-xs border-t border-slate-800/80 py-6 relative z-10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
          {heroStats.map((stat, idx) => (
            <div key={idx}>
              <div
                className={`text-2xl sm:text-3xl font-extrabold tabular-nums ${
                  idx === 3 ? 'text-emerald-400' : 'text-blue-400'
                }`}
              >
                {stat.value}
              </div>
              <div className="text-xs text-slate-400 mt-1">{stat.label}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
