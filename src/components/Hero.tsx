import React, { useState, useEffect } from 'react';
import {
  StaticPageItem,
  HeroSliderConfig,
  INITIAL_HERO_SLIDER_CONFIG,
  HeroSlideItem,
  WebsiteThemeConfig,
  DEFAULT_WEBSITE_THEME,
  CompanySettings,
  DEFAULT_COMPANY_SETTINGS
} from '../data/companyData';
import {
  Radio,
  Server,
  CheckCircle2,
  ArrowRight,
  Shield,
  ChevronLeft,
  ChevronRight,
  Upload,
  Columns,
  Maximize2,
  Edit3,
  Check,
  X,
  Image as ImageIcon
} from 'lucide-react';

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
  onOpenAdminSection
}) => {
  const heroHighlights =
    companySettings.heroHighlights || DEFAULT_COMPANY_SETTINGS.heroHighlights || [];
  const heroStats = companySettings.heroStats || DEFAULT_COMPANY_SETTINGS.heroStats || [];
  const nocNodes = companySettings.nocNodes || DEFAULT_COMPANY_SETTINGS.nocNodes || [];

  const [ping, setPing] = useState(12);
  const [currentSlideIdx, setCurrentSlideIdx] = useState(0);
  const [isEditingHeroText, setIsEditingHeroText] = useState(false);
  const [heroForm, setHeroForm] = useState({
    title: '',
    summary: '',
    content: ''
  });

  const [isEditingSlideCaption, setIsEditingSlideCaption] = useState(false);
  const [slideCaptionForm, setSlideCaptionForm] = useState({
    title: '',
    subtitle: '',
    badgeText: ''
  });

  const activeSlides = [...(sliderConfig.slides || [])]
    .filter(s => s.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder);

  const safeSlideIdx =
    activeSlides.length > 0 ? currentSlideIdx % activeSlides.length : 0;
  const currentSlide: HeroSlideItem | undefined = activeSlides[safeSlideIdx];

  // Subtle real-time ping simulation for NOC panel
  useEffect(() => {
    const interval = setInterval(() => {
      setPing(Math.floor(10 + Math.random() * 5));
    }, 4000);
    return () => clearInterval(interval);
  }, []);

  // Auto-play slider
  useEffect(() => {
    if (!sliderConfig.autoPlay || activeSlides.length <= 1 || isEditingSlideCaption) {
      return;
    }
    const timer = setInterval(() => {
      setCurrentSlideIdx(prev => (prev + 1) % activeSlides.length);
    }, (sliderConfig.intervalSeconds || 5) * 1000);
    return () => clearInterval(timer);
  }, [
    sliderConfig.autoPlay,
    sliderConfig.intervalSeconds,
    activeSlides.length,
    isEditingSlideCaption
  ]);

  const handlePrevSlide = () => {
    if (activeSlides.length <= 1) return;
    setCurrentSlideIdx(prev => (prev - 1 + activeSlides.length) % activeSlides.length);
  };

  const handleNextSlide = () => {
    if (activeSlides.length <= 1) return;
    setCurrentSlideIdx(prev => (prev + 1) % activeSlides.length);
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
      if (mode === 'replace' && currentSlide) {
        const updatedSlides = sliderConfig.slides.map(s =>
          s.id === currentSlide.id ? { ...s, imageUrl: dataUrl } : s
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
        setCurrentSlideIdx(activeSlides.length);
      }
    };
    reader.readAsDataURL(file);
  };

  const openHeroTextEditor = () => {
    setHeroForm({
      title:
        pageContent?.title ||
        'Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider',
      summary:
        pageContent?.summary ||
        'Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.',
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

  const openSlideCaptionEditor = () => {
    if (!currentSlide) return;
    setSlideCaptionForm({
      title: currentSlide.title,
      subtitle: currentSlide.subtitle,
      badgeText: currentSlide.badgeText || ''
    });
    setIsEditingSlideCaption(true);
  };

  const saveSlideCaptionEditor = (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentSlide || !onUpdateSliderConfig) return;
    const updatedSlides = sliderConfig.slides.map(s =>
      s.id === currentSlide.id
        ? {
            ...s,
            title: slideCaptionForm.title,
            subtitle: slideCaptionForm.subtitle,
            badgeText: slideCaptionForm.badgeText
          }
        : s
    );
    onUpdateSliderConfig({ ...sliderConfig, slides: updatedSlides });
    setIsEditingSlideCaption(false);
  };

  const showBackgroundSlider =
    (sliderConfig.layoutMode === 'background' || sliderConfig.layoutMode === 'both') &&
    activeSlides.length > 0;

  const showSideSlider =
    (sliderConfig.layoutMode === 'side' || sliderConfig.layoutMode === 'both') &&
    activeSlides.length > 0;

  return (
    <section
      id="beranda"
      style={{ backgroundColor: themeConfig.heroBgColor }}
      className="text-white relative overflow-hidden border-b border-slate-800 transition-colors duration-300"
    >
      {/* Background Image Slider when layoutMode is 'background' or 'both' */}
      {showBackgroundSlider && currentSlide && (
        <div className="absolute inset-0 z-0">
          {activeSlides.map((slide, idx) => (
            <div
              key={slide.id}
              className={`absolute inset-0 transition-opacity duration-700 ${
                idx === safeSlideIdx ? 'opacity-100' : 'opacity-0 pointer-events-none'
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
              <span>Kontrol Cepat Beranda & Slider Gambar (Tersinkron Otomatis ke Admin CMS):</span>
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

              <button
                type="button"
                onClick={() =>
                  onUpdateSliderConfig?.({
                    ...sliderConfig,
                    layoutMode: sliderConfig.layoutMode === 'side' ? 'background' : 'side'
                  })
                }
                className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white border border-slate-600 font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer"
              >
                {sliderConfig.layoutMode === 'side' ? (
                  <>
                    <Maximize2 className="w-3.5 h-3.5 text-amber-400" />
                    <span>Pindahkan Slider ke Background</span>
                  </>
                ) : (
                  <>
                    <Columns className="w-3.5 h-3.5 text-blue-400" />
                    <span>Pindahkan Slider ke Sebelah Teks</span>
                  </>
                )}
              </button>

              <label className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg flex items-center gap-1.5 cursor-pointer">
                <Upload className="w-3.5 h-3.5" />
                <span>+ Upload Foto Slider</span>
                <input
                  type="file"
                  accept="image/*"
                  onChange={e => handleUploadSlideImage(e, 'new')}
                  className="hidden"
                />
              </label>

              {currentSlide && (
                <label className="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg flex items-center gap-1.5 cursor-pointer">
                  <ImageIcon className="w-3.5 h-3.5" />
                  <span>Ganti Foto Slide Ini</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={e => handleUploadSlideImage(e, 'replace')}
                    className="hidden"
                  />
                </label>
              )}

              {onOpenAdminSection && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('hero_sections')}
                  className="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg flex items-center gap-1.5 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>Edit NOC, CTA & 4 Angka Statistik</span>
                </button>
              )}
            </div>
          </div>
        </div>
      )}

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 relative z-10">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
          {/* Main Headline & Value Proposition (7 cols) */}
          <div className="lg:col-span-7 space-y-6">
            {/* Category label / kicker */}
            <div className="inline-flex items-center gap-2 px-3 py-1 bg-blue-900/40 border border-blue-700/50 rounded-lg text-xs font-semibold text-blue-300">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>
                {companySettings.heroBadgeText || DEFAULT_COMPANY_SETTINGS.heroBadgeText}
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
                    Ringkasan / Subjudul Beranda
                  </label>
                  <textarea
                    rows={3}
                    value={heroForm.summary}
                    onChange={e => setHeroForm({ ...heroForm, summary: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-white"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-300 mb-1">
                    Paragraf Tambahan Beranda
                  </label>
                  <textarea
                    rows={3}
                    value={heroForm.content}
                    onChange={e => setHeroForm({ ...heroForm, content: e.target.value })}
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
                    <span>Simpan ke Website & Admin CMS</span>
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
                    <Edit3 className="w-3 h-3" /> Klik untuk Edit Judul & Isi Beranda
                  </span>
                )}

                {/* Headline */}
                <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.15] text-white">
                  {pageContent?.title ||
                    'Infrastruktur Jaringan, Solusi Keamanan & Internet Service Provider'}
                </h1>

                {/* Description */}
                <p className="text-base sm:text-lg text-slate-200 leading-relaxed max-w-2xl">
                  {pageContent?.summary ||
                    'Mitra teknologi B2B terpercaya sejak 1997. Kami merancang, membangun, dan mengelola solusi jaringan fiber optic, tower guywire mandiri, sistem pengawasan CCTV AI, proteksi petir & grounding < 2 Ohm, serta koneksi internet berlisensi resmi KGSNet.'}
                </p>

                {pageContent?.content && (
                  <div className="text-xs sm:text-sm text-slate-300/90 leading-relaxed max-w-2xl whitespace-pre-line">
                    {pageContent.content}
                  </div>
                )}
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

            {/* Background Slider Controls when in Background-only mode */}
            {sliderConfig.layoutMode === 'background' && activeSlides.length > 0 && currentSlide && (
              <div className="p-4 rounded-2xl bg-slate-900/85 border border-slate-700/80 backdrop-blur-md flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div className="space-y-0.5">
                  <div className="flex items-center gap-2">
                    <span className="px-2 py-0.5 bg-blue-600/30 border border-blue-500/40 text-blue-300 rounded text-[10px] font-bold">
                      {currentSlide.badgeText || `Slide ${safeSlideIdx + 1}/${activeSlides.length}`}
                    </span>
                    {isLiveEditMode && (
                      <button
                        type="button"
                        onClick={openSlideCaptionEditor}
                        className="text-[10px] text-emerald-400 font-bold underline cursor-pointer"
                      >
                        Edit Caption Slide
                      </button>
                    )}
                  </div>
                  <div className="text-sm font-bold text-white">{currentSlide.title}</div>
                  {currentSlide.subtitle && (
                    <div className="text-xs text-slate-300 leading-relaxed">
                      {currentSlide.subtitle}
                    </div>
                  )}
                </div>

                <div className="flex items-center gap-1.5 shrink-0">
                  <button
                    type="button"
                    onClick={handlePrevSlide}
                    className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 cursor-pointer"
                    aria-label="Slide Sebelumnya"
                  >
                    <ChevronLeft className="w-4 h-4" />
                  </button>
                  <span className="text-xs font-mono text-slate-300 px-1.5">
                    {safeSlideIdx + 1}/{activeSlides.length}
                  </span>
                  <button
                    type="button"
                    onClick={handleNextSlide}
                    className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 cursor-pointer"
                    aria-label="Slide Berikutnya"
                  >
                    <ChevronRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}

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

          {/* Right Column (5 cols): Either Side Image Slider OR NOC Status Card */}
          <div className="lg:col-span-5 space-y-4">
            {showSideSlider && currentSlide ? (
              <div className="bg-slate-900/95 rounded-2xl border border-slate-700/80 overflow-hidden shadow-2xl backdrop-blur-sm">
                {/* Interactive Image Slider Box */}
                <div className="relative h-64 sm:h-72 bg-slate-950 overflow-hidden group">
                  {activeSlides.map((slide, idx) => (
                    <img
                      key={slide.id}
                      src={slide.imageUrl}
                      alt={slide.title}
                      className={`absolute inset-0 w-full h-full object-cover transition-all duration-700 ${
                        idx === safeSlideIdx
                          ? 'opacity-100 scale-100'
                          : 'opacity-0 scale-105 pointer-events-none'
                      }`}
                    />
                  ))}
                  <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/35 to-transparent" />

                  {/* Top Badge & Slide Counter */}
                  <div className="absolute top-3.5 left-3.5 right-3.5 flex items-center justify-between gap-2">
                    <span
                      style={{ backgroundColor: themeConfig.primaryColor }}
                      className="px-3 py-1 rounded-lg text-[11px] font-bold text-white shadow-md"
                    >
                      {currentSlide.badgeText || 'Dokumentasi & Infrastruktur KGS'}
                    </span>
                    <span className="px-2.5 py-1 rounded-lg bg-slate-950/80 border border-slate-700 text-[11px] font-mono text-slate-200">
                      {safeSlideIdx + 1} / {activeSlides.length}
                    </span>
                  </div>

                  {/* Prev / Next Navigation Buttons */}
                  {activeSlides.length > 1 && (
                    <>
                      <button
                        type="button"
                        onClick={handlePrevSlide}
                        className="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/75 hover:bg-slate-900 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg"
                        aria-label="Slide Sebelumnya"
                      >
                        <ChevronLeft className="w-4 h-4" />
                      </button>
                      <button
                        type="button"
                        onClick={handleNextSlide}
                        className="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-950/75 hover:bg-slate-900 text-white border border-slate-700 flex items-center justify-center transition cursor-pointer shadow-lg"
                        aria-label="Slide Selanjutnya"
                      >
                        <ChevronRight className="w-4 h-4" />
                      </button>
                    </>
                  )}

                  {/* Quick Upload Overlay in Live Edit Mode */}
                  {isLiveEditMode && (
                    <div className="absolute bottom-3 right-3 flex items-center gap-1.5 z-20">
                      <button
                        type="button"
                        onClick={openSlideCaptionEditor}
                        className="px-2.5 py-1.5 bg-slate-900/95 hover:bg-slate-800 text-emerald-400 border border-emerald-500/50 rounded-lg text-[11px] font-bold cursor-pointer"
                      >
                        Edit Caption
                      </button>
                      <label className="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-lg text-[11px] font-extrabold cursor-pointer flex items-center gap-1 shadow-md">
                        <Upload className="w-3 h-3" />
                        <span>Ganti Foto</span>
                        <input
                          type="file"
                          accept="image/*"
                          onChange={e => handleUploadSlideImage(e, 'replace')}
                          className="hidden"
                        />
                      </label>
                    </div>
                  )}
                </div>

                {/* Slide Caption & Dots Footer */}
                <div className="p-5 space-y-3">
                  {isEditingSlideCaption ? (
                    <form onSubmit={saveSlideCaptionEditor} className="space-y-2.5 text-xs">
                      <input
                        type="text"
                        value={slideCaptionForm.badgeText}
                        onChange={e =>
                          setSlideCaptionForm({ ...slideCaptionForm, badgeText: e.target.value })
                        }
                        placeholder="Label Badge Slide"
                        className="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white"
                      />
                      <input
                        type="text"
                        required
                        value={slideCaptionForm.title}
                        onChange={e =>
                          setSlideCaptionForm({ ...slideCaptionForm, title: e.target.value })
                        }
                        placeholder="Judul Slide"
                        className="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white font-bold"
                      />
                      <textarea
                        rows={2}
                        value={slideCaptionForm.subtitle}
                        onChange={e =>
                          setSlideCaptionForm({ ...slideCaptionForm, subtitle: e.target.value })
                        }
                        placeholder="Keterangan Slide"
                        className="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white"
                      />
                      <div className="flex justify-end gap-2">
                        <button
                          type="button"
                          onClick={() => setIsEditingSlideCaption(false)}
                          className="px-3 py-1 bg-slate-800 text-slate-300 rounded-lg cursor-pointer"
                        >
                          Batal
                        </button>
                        <button
                          type="submit"
                          className="px-3 py-1 bg-emerald-500 text-slate-950 font-bold rounded-lg cursor-pointer"
                        >
                          Simpan Caption
                        </button>
                      </div>
                    </form>
                  ) : (
                    <>
                      <div>
                        <h3 className="text-sm sm:text-base font-extrabold text-white leading-snug">
                          {currentSlide.title}
                        </h3>
                        {currentSlide.subtitle && (
                          <p className="text-xs text-slate-300 mt-1 leading-relaxed">
                            {currentSlide.subtitle}
                          </p>
                        )}
                      </div>

                      {/* Dots + NOC Live Status Mini Bar */}
                      <div className="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                        <div className="flex items-center gap-1.5">
                          {activeSlides.map((s, idx) => (
                            <button
                              key={s.id}
                              type="button"
                              onClick={() => setCurrentSlideIdx(idx)}
                              className={`h-2 rounded-full transition-all cursor-pointer ${
                                idx === safeSlideIdx
                                  ? 'w-6 bg-blue-400'
                                  : 'w-2 bg-slate-700 hover:bg-slate-500'
                              }`}
                              aria-label={`Pilih Slide ${idx + 1}`}
                            />
                          ))}
                        </div>

                        <div className="flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping" />
                          <span>NOC ONLINE · {ping} ms</span>
                        </div>
                      </div>
                    </>
                  )}
                </div>
              </div>
            ) : (
              /* Classic NOC Operations Status Card when layoutMode is 'background' or 'noc' */
              <div className="bg-slate-900/90 rounded-2xl border border-slate-700/80 p-6 sm:p-7 backdrop-blur-sm shadow-2xl space-y-5">
                {/* Header Box */}
                <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                      <span className="text-xs font-mono font-semibold text-emerald-400 tracking-wider">
                        {companySettings.nocStatusLabel || DEFAULT_COMPANY_SETTINGS.nocStatusLabel}
                      </span>
                    </div>
                    <div className="text-base font-bold text-white mt-1">
                      {companySettings.nocCardTitle || DEFAULT_COMPANY_SETTINGS.nocCardTitle}
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-xs font-mono text-slate-400">LATENCY</div>
                    <div className="text-sm font-bold text-emerald-400 font-mono">{ping} ms</div>
                  </div>
                </div>

                {/* Status Nodes */}
                <div className="space-y-3 text-xs">
                  {nocNodes.map((node, idx) => (
                    <div
                      key={idx}
                      className="p-3 rounded-xl bg-slate-800/70 border border-slate-700/50 flex items-center justify-between"
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center shrink-0">
                          {idx === 0 ? (
                            <Server className="w-4 h-4" />
                          ) : idx === 1 ? (
                            <Radio className="w-4 h-4" />
                          ) : (
                            <Shield className="w-4 h-4" />
                          )}
                        </div>
                        <div>
                          <div className="font-semibold text-white">{node.title}</div>
                          <div className="text-[11px] text-slate-400">{node.subtitle}</div>
                        </div>
                      </div>
                      <span className="text-[11px] font-semibold text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded border border-emerald-800">
                        {node.statusBadge}
                      </span>
                    </div>
                  ))}
                </div>

                {/* Coverage Footer */}
                <div className="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
                  <div>
                    <span className="text-[11px] text-slate-400 block">
                      Jangkauan Proyek Operasional:
                    </span>
                    <span className="text-slate-200 font-medium text-[11px]">
                      {companySettings.nocCoverageText ||
                        DEFAULT_COMPANY_SETTINGS.nocCoverageText}
                    </span>
                  </div>
                  <div className="w-2 h-2 rounded-full bg-blue-500"></div>
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
