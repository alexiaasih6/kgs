import React, { useState, useEffect, useCallback } from 'react';
import {
  SlideshowCategoryItem,
  StaticPageItem
} from '../data/companyData';
import {
  ChevronLeft,
  ChevronRight,
  ArrowRight,
  ArrowLeft,
  CheckCircle2,
  ShieldCheck,
  Award,
  Clock,
  Wrench,
  Layers,
  MapPin,
  PhoneCall,
  Pause,
  Play,
  Images
} from 'lucide-react';

interface SlideshowSectionProps {
  categories: SlideshowCategoryItem[];
  pageData?: StaticPageItem;
  selectedSlug: string | null;
  onSelectSlug: (slug: string | null) => void;
  onRequestConsultation: (serviceName?: string) => void;
}

interface SmartLandscapeImageProps {
  src: string;
  alt: string;
  objectPosition?: string;
  forcePortraitBlur?: boolean;
  priority?: boolean;
  className?: string;
  imgClassName?: string;
}

/**
 * Komponen Frame Landscape Konsisten (16:9 di Desktop/Tablet, 4:3 -> 16:9 di Mobile)
 * Mendeteksi otomatis gambar Portrait (naturalHeight > naturalWidth) agar tampil utuh
 * menggunakan komposisi Background Blur + Object-Contain tanpa mengubah ukuran frame
 * dan tanpa menyebabkan Cumulative Layout Shift (CLS).
 */
export const SmartLandscapeImage: React.FC<SmartLandscapeImageProps> = ({
  src,
  alt,
  objectPosition = 'center center',
  forcePortraitBlur = false,
  priority = false,
  className = 'aspect-4/3 sm:aspect-16/9',
  imgClassName = ''
}) => {
  const [isPortraitDetected, setIsPortraitDetected] = useState(false);

  useEffect(() => {
    setIsPortraitDetected(false);
  }, [src]);

  const handleImageLoad = (e: React.SyntheticEvent<HTMLImageElement>) => {
    const img = e.currentTarget;
    if (img.naturalHeight > img.naturalWidth * 1.05) {
      setIsPortraitDetected(true);
    } else {
      setIsPortraitDetected(false);
    }
  };

  const usePortraitBlur = forcePortraitBlur || isPortraitDetected;

  return (
    <div
      style={{ aspectRatio: '16 / 9' }}
      className={`hero-slide relative w-full overflow-hidden isolate bg-slate-950 select-none ${
        usePortraitBlur ? 'is-portrait' : ''
      } ${className}`}
    >
      {/* Background Blur Layer khusus untuk Gambar Portrait agar sisi kiri/kanan terisi rapi */}
      {usePortraitBlur && (
        <img
          src={src}
          alt=""
          aria-hidden="true"
          decoding="async"
          loading={priority ? 'eager' : 'lazy'}
          className="hero-slide-bg absolute -inset-3 w-[calc(100%+24px)] h-[calc(100%+24px)] object-cover blur-md scale-110 opacity-75 pointer-events-none select-none"
          style={{ objectPosition }}
        />
      )}

      {/* Gambar Utama dengan tinggi 16:9 stabil */}
      <img
        src={src}
        alt={alt}
        width={1280}
        height={720}
        decoding="async"
        loading={priority ? 'eager' : 'lazy'}
        fetchPriority={priority ? 'high' : 'auto'}
        onLoad={handleImageLoad}
        className={`hero-slide-image relative z-10 w-full h-full block transition-transform duration-500 ${
          usePortraitBlur ? 'object-contain object-center' : 'object-cover'
        } ${imgClassName}`}
        style={{
          aspectRatio: '16 / 9',
          ...(usePortraitBlur ? {} : { objectPosition })
        }}
      />
    </div>
  );
};

export const SlideshowSection: React.FC<SlideshowSectionProps> = ({
  categories,
  pageData,
  selectedSlug,
  onSelectSlug,
  onRequestConsultation
}) => {
  const activeCategories = [...categories]
    .filter(c => c.isActive)
    .sort((a, b) => a.sortOrder - b.sortOrder);

  const selectedCategory = selectedSlug
    ? activeCategories.find(c => c.slug === selectedSlug) || null
    : null;

  // State untuk Banner Slider Utama (Landscape 16:9) tepat di atas Why Choose Us?
  const [bannerSlideIdx, setBannerSlideIdx] = useState(0);
  const [isBannerPaused, setIsBannerPaused] = useState(false);
  const [prefersReducedMotion, setPrefersReducedMotion] = useState(false);

  // State untuk Detail Category Slide
  const [activeSlideIdx, setActiveSlideIdx] = useState(0);
  const [isAutoPlaying, setIsAutoPlaying] = useState(true);

  // Hormati preferensi prefers-reduced-motion pengguna
  useEffect(() => {
    if (typeof window === 'undefined' || !window.matchMedia) return;
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    setPrefersReducedMotion(mediaQuery.matches);
    const listener = (event: MediaQueryListEvent) => {
      setPrefersReducedMotion(event.matches);
    };
    mediaQuery.addEventListener('change', listener);
    return () => mediaQuery.removeEventListener('change', listener);
  }, []);

  useEffect(() => {
    setActiveSlideIdx(0);
  }, [selectedSlug]);

  const activeCategoryImages = selectedCategory
    ? [...(selectedCategory.images || [])].sort((a, b) => a.sortOrder - b.sortOrder)
    : [];

  // Autoplay untuk Featured Landscape Slider di Beranda (5.5 detik, berhenti saat hover/pause/reduced-motion)
  useEffect(() => {
    if (
      selectedCategory ||
      isBannerPaused ||
      prefersReducedMotion ||
      activeCategories.length <= 1
    ) {
      return;
    }
    const timer = setInterval(() => {
      setBannerSlideIdx(prev => (prev + 1) % activeCategories.length);
    }, 5500);
    return () => clearInterval(timer);
  }, [selectedCategory, isBannerPaused, prefersReducedMotion, activeCategories.length]);

  // Autoplay untuk Detail Category Carousel
  useEffect(() => {
    if (
      !selectedCategory ||
      !isAutoPlaying ||
      prefersReducedMotion ||
      activeCategoryImages.length <= 1
    ) {
      return;
    }
    const timer = setInterval(() => {
      setActiveSlideIdx(prev => (prev + 1) % activeCategoryImages.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [selectedCategory, isAutoPlaying, prefersReducedMotion, activeCategoryImages.length]);

  const handleBannerPrev = useCallback(() => {
    if (activeCategories.length === 0) return;
    setBannerSlideIdx(prev => (prev - 1 + activeCategories.length) % activeCategories.length);
  }, [activeCategories.length]);

  const handleBannerNext = useCallback(() => {
    if (activeCategories.length === 0) return;
    setBannerSlideIdx(prev => (prev + 1) % activeCategories.length);
  }, [activeCategories.length]);

  const handlePrevSlide = useCallback(() => {
    if (activeCategoryImages.length === 0) return;
    setActiveSlideIdx(
      prev => (prev - 1 + activeCategoryImages.length) % activeCategoryImages.length
    );
  }, [activeCategoryImages.length]);

  const handleNextSlide = useCallback(() => {
    if (activeCategoryImages.length === 0) return;
    setActiveSlideIdx(prev => (prev + 1) % activeCategoryImages.length);
  }, [activeCategoryImages.length]);

  const handleOpenCategory = (slug: string) => {
    onSelectSlug(slug);
    const el = document.getElementById('slideshow');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const currentBannerCat =
    activeCategories[bannerSlideIdx] || activeCategories[0] || null;
  const currentBannerSlide = currentBannerCat?.images?.[0];
  const currentBannerImage =
    currentBannerCat?.coverImage ||
    currentBannerSlide?.imagePath ||
    'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80';

  return (
    <section
      id="slideshow"
      className="py-16 lg:py-24 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800 transition-colors"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* =====================================================================
            MODE 1: HALAMAN READ MORE / DETAIL KATEGORI SLIDESHOW (SAAT DIKLIK)
           ===================================================================== */}
        {selectedCategory ? (
          <div className="space-y-10 animate-fadeIn">
            {/* Top Navigation Bar & Breadcrumb */}
            <div className="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
              <div className="flex items-center gap-3">
                <button
                  type="button"
                  onClick={() => onSelectSlug(null)}
                  className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 dark:bg-slate-800 dark:hover:bg-blue-600 text-white text-xs sm:text-sm font-bold transition cursor-pointer shadow-xs"
                >
                  <ArrowLeft className="w-4 h-4" />
                  <span>Kembali ke Semua Slide Show (8 Kategori)</span>
                </button>
                <div className="hidden md:flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                  <span>Slide Show</span>
                  <span>/</span>
                  <span className="font-bold text-blue-600 dark:text-blue-400">
                    {selectedCategory.title}
                  </span>
                </div>
              </div>

              {/* Quick Switcher Pill Tabs */}
              <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                {activeCategories.map(cat => (
                  <button
                    key={cat.id}
                    type="button"
                    onClick={() => {
                      setActiveSlideIdx(0);
                      onSelectSlug(cat.slug);
                    }}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer ${
                      cat.slug === selectedCategory.slug
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                    }`}
                  >
                    {cat.title}
                  </button>
                ))}
              </div>
            </div>

            {/* Detail Main Grid: Interactive Slideshow Player + Full Article Specification */}
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
              {/* Left 7 Cols: Interactive Image Slideshow Carousel (Landscape 16:9 Stabil) */}
              <div className="lg:col-span-7 space-y-4">
                <div
                  className="relative rounded-3xl overflow-hidden bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-xl group"
                  onMouseEnter={() => setIsAutoPlaying(false)}
                  onMouseLeave={() => setIsAutoPlaying(true)}
                >
                  {activeCategoryImages.length > 0 ? (
                    <div className="relative w-full">
                      <SmartLandscapeImage
                        src={
                          activeCategoryImages[activeSlideIdx]?.imagePath ||
                          selectedCategory.coverImage
                        }
                        alt={
                          activeCategoryImages[activeSlideIdx]?.altText ||
                          selectedCategory.title
                        }
                        priority={true}
                        className="aspect-4/3 sm:aspect-16/9"
                      />
                      <div className="absolute inset-0 z-20 bg-gradient-to-t from-slate-950/95 via-slate-950/35 to-transparent pointer-events-none" />

                      {/* Top Badges */}
                      <div className="absolute top-4 left-4 right-4 z-30 flex items-center justify-between gap-2">
                        <span className="px-3.5 py-1.5 rounded-xl bg-blue-600/95 backdrop-blur-md text-white text-xs font-extrabold tracking-wide shadow-lg inline-flex items-center gap-1.5">
                          <Images className="w-3.5 h-3.5" />
                          <span>{selectedCategory.title}</span>
                        </span>
                        <span className="px-3 py-1 rounded-xl bg-slate-900/85 backdrop-blur-md text-slate-200 text-xs font-mono border border-white/15">
                          Slide {activeSlideIdx + 1} / {activeCategoryImages.length}
                        </span>
                      </div>

                      {/* Prev / Next Controls */}
                      {activeCategoryImages.length > 1 && (
                        <>
                          <button
                            type="button"
                            onClick={handlePrevSlide}
                            aria-label="Slide Sebelumnya"
                            className="absolute left-3 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center transition shadow-lg cursor-pointer"
                          >
                            <ChevronLeft className="w-5 h-5" />
                          </button>
                          <button
                            type="button"
                            onClick={handleNextSlide}
                            aria-label="Slide Berikutnya"
                            className="absolute right-3 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center transition shadow-lg cursor-pointer"
                          >
                            <ChevronRight className="w-5 h-5" />
                          </button>
                        </>
                      )}

                      {/* Slide Caption Overlay */}
                      <div className="absolute bottom-0 inset-x-0 z-30 p-5 sm:p-7 text-white">
                        <h3 className="text-lg sm:text-xl font-extrabold mb-1.5 drop-shadow-xs">
                          {activeCategoryImages[activeSlideIdx]?.altText ||
                            selectedCategory.title}
                        </h3>
                        <p className="text-xs sm:text-sm text-slate-200 leading-relaxed max-w-2xl">
                          {activeCategoryImages[activeSlideIdx]?.caption ||
                            selectedCategory.shortDescription}
                        </p>
                      </div>
                    </div>
                  ) : (
                    <div className="relative">
                      <SmartLandscapeImage
                        src={selectedCategory.coverImage}
                        alt={selectedCategory.title}
                        priority={true}
                        className="aspect-4/3 sm:aspect-16/9"
                      />
                    </div>
                  )}
                </div>

                {/* Thumbnail Selector Strip (Landscape 16:9 Thumbnails) */}
                {activeCategoryImages.length > 1 && (
                  <div className="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    {activeCategoryImages.map((slide, idx) => (
                      <button
                        key={slide.id}
                        type="button"
                        onClick={() => setActiveSlideIdx(idx)}
                        className={`relative rounded-xl overflow-hidden border-2 transition cursor-pointer text-left group ${
                          idx === activeSlideIdx
                            ? 'border-blue-600 ring-2 ring-blue-500/30 scale-[1.01]'
                            : 'border-slate-200 dark:border-slate-800 opacity-75 hover:opacity-100'
                        }`}
                      >
                        <SmartLandscapeImage
                          src={slide.imagePath}
                          alt={slide.altText || slide.caption}
                          className="aspect-16/9"
                        />
                        <div className="absolute inset-0 z-20 bg-gradient-to-t from-slate-950/85 via-transparent to-transparent flex items-end p-2">
                          <span className="text-[11px] font-bold text-white line-clamp-1">
                            {idx + 1}. {slide.altText || slide.caption}
                          </span>
                        </div>
                      </button>
                    ))}
                  </div>
                )}
              </div>

              {/* Right 5 Cols: Full Description & Direct Consultation CTA */}
              <div className="lg:col-span-5 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                <div>
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-xs font-extrabold mb-3">
                    Infrastruktur & Solusi KGS • {activeCategoryImages.length} Slide
                  </span>
                  <h2 className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    {selectedCategory.title}
                  </h2>
                  <p className="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-1">
                    {selectedCategory.shortDescription}
                  </p>
                </div>

                <div className="prose prose-sm dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed">
                  {selectedCategory.description.split('\n\n').map((para, i) => {
                    const trimmed = para.trim();
                    if (!trimmed) return null;
                    const lines = trimmed.split('\n');
                    const isBulletBlock = lines.some(l => l.trim().startsWith('- '));
                    if (isBulletBlock) {
                      const headingLine = !lines[0].trim().startsWith('- ') ? lines[0] : null;
                      const bullets = lines.filter(l => l.trim().startsWith('- '));
                      return (
                        <div key={i} className="space-y-2 pt-2">
                          {headingLine && (
                            <h4 className="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                              {headingLine}
                            </h4>
                          )}
                          <ul className="space-y-2">
                            {bullets.map((b, bi) => (
                              <li
                                key={bi}
                                className="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-medium"
                              >
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>{b.replace(/^- /, '')}</span>
                              </li>
                            ))}
                          </ul>
                        </div>
                      );
                    }
                    return (
                      <p key={i} className="text-sm leading-relaxed">
                        {trimmed}
                      </p>
                    );
                  })}
                </div>

                {/* Action Buttons */}
                <div className="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3">
                  <button
                    type="button"
                    onClick={() => onRequestConsultation(selectedCategory.title)}
                    className="flex-1 py-3.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <PhoneCall className="w-4 h-4" />
                    <span>Konsultasi & Survei {selectedCategory.title}</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => onSelectSlug(null)}
                    className="py-3.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs sm:text-sm transition cursor-pointer"
                  >
                    Lihat Kategori Lain
                  </button>
                </div>
              </div>
            </div>
          </div>
        ) : (
          /* =====================================================================
              MODE 2: 8 GAMBAR KATEGORI DI BAWAH HEADER + WHY CHOOSE US?
             ===================================================================== */
          <>
            {/* 8 Category Cards Grid — Presisi Sejajar dengan Frame Landscape 16:9 */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
              {activeCategories.map((cat, idx) => {
                const previewImage =
                  (cat as any).primaryImageUrl ||
                  cat.coverImage ||
                  (cat.images?.[0] as any)?.imageUrl ||
                  cat.images?.[0]?.imagePath ||
                  'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80';
                return (
                  <article
                    key={cat.id}
                    onClick={() => handleOpenCategory(cat.slug)}
                    className="group bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full cursor-pointer"
                  >
                    {/* Image Header dengan Rasio Landscape 16:9 Stabil & Deteksi Portrait */}
                    <div className="relative w-full overflow-hidden bg-slate-900 shrink-0" style={{ aspectRatio: '16 / 9' }}>
                      <SmartLandscapeImage
                        src={previewImage}
                        alt={cat.title}
                        className="w-full h-full"
                        imgClassName="group-hover:scale-105"
                      />
                      <div className="absolute inset-0 z-20 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent pointer-events-none" />

                      {/* Top Number & Slide Count */}
                      <div className="absolute top-3 left-3 right-3 z-30 flex items-center justify-between gap-2">
                        <span className="px-2.5 py-1 rounded-lg bg-blue-600/95 text-white text-[11px] font-extrabold shadow-xs">
                          0{idx + 1}. Infrastruktur
                        </span>
                        <span className="px-2 py-0.5 rounded-md bg-slate-900/80 text-slate-200 text-[10px] font-mono border border-white/15">
                          {cat.images?.length || 0} Gambar
                        </span>
                      </div>

                      {/* Title inside Image Footer */}
                      <div className="absolute bottom-3 left-3.5 right-3.5 z-30">
                        <h3
                          className="text-base sm:text-lg font-extrabold text-white group-hover:text-blue-300 transition-colors drop-shadow-xs leading-snug line-clamp-1"
                          title={cat.title}
                        >
                          {cat.title}
                        </h3>
                      </div>
                    </div>

                    {/* Card Body */}
                    <div className="p-5 flex-1 flex flex-col justify-between space-y-4">
                      <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3 min-h-[3.75rem]">
                        {cat.shortDescription}
                      </p>

                      {/* Read More Button */}
                      <div className="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span className="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                          {cat.images?.length || 0} Slide Foto
                        </span>
                        <button
                          type="button"
                          onClick={e => {
                            e.stopPropagation();
                            handleOpenCategory(cat.slug);
                          }}
                          className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-600 dark:bg-blue-950/60 dark:hover:bg-blue-600 text-blue-700 hover:text-white dark:text-blue-300 dark:hover:text-white text-xs font-extrabold transition cursor-pointer"
                        >
                          <span>Read More</span>
                          <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </article>
                );
              })}
            </div>
          </>
        )}

        {/* =====================================================================
            BAGIAN BAWAH: WHY CHOOSE US? (MENGAPA MEMILIH PT. KGS)
           ===================================================================== */}
        <div className="mt-16 lg:mt-20 bg-slate-900 dark:bg-slate-950 rounded-3xl p-6 sm:p-10 lg:p-12 text-white border border-slate-800 shadow-2xl relative overflow-hidden">
          {/* Subtle decorative glow */}
          <div className="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-blue-600/15 blur-3xl pointer-events-none" />
          <div className="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none" />

          <div className="relative z-10">
            <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10 pb-8 border-b border-slate-800">
              <div>
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-extrabold uppercase tracking-wider mb-3">
                  <Award className="w-3.5 h-3.5" />
                  <span>Value Proposition & Keunggulan Kompetitif</span>
                </div>
                <h3 className="text-2xl sm:text-4xl font-black tracking-tight text-white">
                  Why Choose Us?
                </h3>
                <p className="text-sm sm:text-base text-slate-300 mt-2 max-w-2xl">
                  Mengapa ratusan instansi pemerintah, rumah sakit, perbankan, perkebunan, dan
                  perusahaan pelayaran mempercayakan infrastruktur mereka kepada{' '}
                  <strong className="text-white">PT. Kreatif Global Solusindo</strong> sejak 2007:
                </p>
              </div>
              <div className="flex flex-wrap items-center gap-3 shrink-0">
                <button
                  type="button"
                  onClick={() => onRequestConsultation('Konsultasi Infrastruktur B2B')}
                  className="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs sm:text-sm shadow-lg transition inline-flex items-center gap-2 cursor-pointer"
                >
                  <span>Jadwalkan Survei Gratis</span>
                  <ArrowRight className="w-4 h-4" />
                </button>
              </div>
            </div>

            {/* 6 Pillars of Why Choose Us */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
              {[
                {
                  icon: ShieldCheck,
                  badge: 'Sejak 21 Mei 2007',
                  title: '1. Legalitas Penuh & Anggota Resmi APJII',
                  desc: 'Berbadan hukum resmi dan terdaftar sebagai anggota APJII (KGSNet) dengan izin penyelenggaraan jaringan dan telekomunikasi yang sah.'
                },
                {
                  icon: Layers,
                  badge: 'End-to-End Solution',
                  title: '2. Integrator 8 Solusi dalam 1 Atap',
                  desc: 'Mulai dari Fiber Optik, Wireless, VSAT, Tower, Penangkal Petir, Server, PABX, hingga CCTV ditangani oleh satu tim ahli terpadu tanpa lempar tanggung jawab.'
                },
                {
                  icon: Clock,
                  badge: 'SLA 99.5% & NOC 24/7',
                  title: '3. Jaminan Uptime & Respon Cepat',
                  desc: 'Monitoring jaringan proaktif 24/7 dari NOC Palembang & Lampung dengan dukungan tim teknisi lapangan siap siaga untuk penanganan gangguan.'
                },
                {
                  icon: Wrench,
                  badge: 'Certified Engineers',
                  title: '4. Tim Teknis Bersertifikasi Resmi',
                  desc: 'Didukung teknisi bersertifikasi Working at Height (WAH) untuk tower, spesialis splicing FO dengan alat ukur OTDR, serta sertifikasi prinsipal resmi.'
                },
                {
                  icon: Award,
                  badge: 'Authorized Partner',
                  title: '5. Perangkat Original & Bergaransi Resmi',
                  desc: 'Kemitraan resmi dengan Panasonic, NETVIEL, Belden, Furukawa, Hikvision, MikroTik, dan Ubiquiti menjamin keaslian material dan klaim garansi.'
                },
                {
                  icon: MapPin,
                  badge: 'Sumatera & Nasional',
                  title: '6. Pengalaman Medan Ekstrem & Maritim',
                  desc: 'Terbukti sukses membangun infrastruktur di area perkebunan terpencil, tambang, pelabuhan, kapal laut (AIS/VHF), hingga gedung bertingkat.'
                }
              ].map((item, idx) => {
                const IconComp = item.icon;
                return (
                  <div
                    key={idx}
                    className="p-5 sm:p-6 rounded-2xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/70 transition group"
                  >
                    <div className="flex items-center justify-between gap-2 mb-3">
                      <div className="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition">
                        <IconComp className="w-5 h-5" />
                      </div>
                      <span className="px-2.5 py-1 rounded-md bg-slate-900/90 text-slate-300 text-[11px] font-bold border border-slate-700">
                        {item.badge}
                      </span>
                    </div>
                    <h4 className="text-base font-extrabold text-white mb-2">{item.title}</h4>
                    <p className="text-xs sm:text-sm text-slate-300 leading-relaxed">{item.desc}</p>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
