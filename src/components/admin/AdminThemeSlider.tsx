import React, { useState } from 'react';
import {
  WebsiteThemeConfig,
  WEBSITE_THEME_PRESETS,
  HeroSliderConfig,
  HeroSlideItem,
  HeroSliderLayoutMode,
  SlideshowCategoryItem,
  INITIAL_SLIDESHOW_CATEGORIES,
  CompanySettings
} from '../../data/companyData';
import {
  Palette,
  Image as ImageIcon,
  Upload,
  Plus,
  Trash2,
  Check,
  ArrowUp,
  ArrowDown,
  Eye,
  Layout,
  Maximize2,
  Columns,
  Activity,
  Sparkles,
  Edit3,
  X
} from 'lucide-react';
import {
  MediaLibraryPickerModal,
  MediaLibraryItem,
  recordUploadedMedia
} from './MediaLibraryPickerModal';

interface AdminThemeSliderProps {
  themeConfig: WebsiteThemeConfig;
  onUpdateThemeConfig: (theme: WebsiteThemeConfig) => void;
  sliderConfig: HeroSliderConfig;
  onUpdateSliderConfig: (slider: HeroSliderConfig) => void;
  slideshowCategories?: SlideshowCategoryItem[];
  onOpenSlideshowTab?: () => void;
  companySettings?: CompanySettings;
  onUpdateCompanySettings?: (settings: CompanySettings) => void;
  onOpenLiveEditor: () => void;
}

export const AdminThemeSlider: React.FC<AdminThemeSliderProps> = ({
  themeConfig,
  onUpdateThemeConfig,
  sliderConfig,
  onUpdateSliderConfig,
  slideshowCategories = INITIAL_SLIDESHOW_CATEGORIES,
  onOpenSlideshowTab,
  companySettings,
  onUpdateCompanySettings,
  onOpenLiveEditor
}) => {
  const [activeSubTab, setActiveSubTab] = useState<'slider' | 'theme'>('slider');
  const [savedNotice, setSavedNotice] = useState<string>('');

  // State for adding/editing a slide
  const [editingSlide, setEditingSlide] = useState<HeroSlideItem | null>(null);
  const [isAddingSlide, setIsAddingSlide] = useState(false);
  const [slideForm, setSlideForm] = useState<{
    title: string;
    subtitle: string;
    badgeText: string;
    imageUrl: string;
    isActive: boolean;
  }>({
    title: '',
    subtitle: '',
    badgeText: 'Infrastruktur & Solusi KGS',
    imageUrl: '',
    isActive: true
  });

  const showToast = (msg: string) => {
    setSavedNotice(msg);
    setTimeout(() => setSavedNotice(''), 3500);
  };

  const handleSelectThemePreset = (preset: WebsiteThemeConfig) => {
    onUpdateThemeConfig({ ...preset });
    showToast(`Tema "${preset.name}" berhasil diterapkan ke seluruh halaman website!`);
  };

  const handleLayoutModeChange = (mode: HeroSliderLayoutMode) => {
    onUpdateSliderConfig({
      ...sliderConfig,
      layoutMode: mode
    });
    const modeLabel =
      mode === 'side'
        ? 'Slider Gambar di Sebelah Kanan Teks'
        : mode === 'background'
        ? 'Slider Gambar sebagai Background Beranda'
        : mode === 'both'
        ? 'Slider di Background + Sebelah Kanan'
        : 'Panel NOC Default';
    showToast(`Posisi slider beranda diubah menjadi: ${modeLabel}`);
  };

  const [mediaPickerTarget, setMediaPickerTarget] = useState<{
    mode: 'new_slide' | 'form_slide' | 'replace_slide';
    slideId?: string;
    slideIndex?: number;
  } | null>(null);

  const handleFileUpload = (
    e: React.ChangeEvent<HTMLInputElement>,
    onLoaded: (dataUrl: string) => void
  ) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const result = ev.target?.result as string;
      if (result) {
        recordUploadedMedia(result, file.name, 'Slider Beranda');
        onLoaded(result);
      }
    };
    reader.readAsDataURL(file);
  };

  const handleQuickUploadNewSlide = (e: React.ChangeEvent<HTMLInputElement>) => {
    handleFileUpload(e, dataUrl => {
      const nextOrder =
        sliderConfig.slides.length > 0
          ? Math.max(...sliderConfig.slides.map(s => s.sortOrder)) + 1
          : 1;
      const newSlide: HeroSlideItem = {
        id: `slide-${Date.now()}`,
        title: 'Dokumentasi Infrastruktur & Layanan PT. KGS',
        subtitle: 'Solusi terintegrasi jaringan Fiber Optic, Tower Telekomunikasi, CCTV & ISP KGSNet.',
        badgeText: 'Dokumentasi Resmi KGS',
        imageUrl: dataUrl,
        sortOrder: nextOrder,
        isActive: true
      };
      onUpdateSliderConfig({
        ...sliderConfig,
        slides: [...sliderConfig.slides, newSlide]
      });
      showToast('Foto baru berhasil di-upload dan ditambahkan ke Slider Beranda!');
    });
  };

  const openAddSlideModal = () => {
    setEditingSlide(null);
    setSlideForm({
      title: '',
      subtitle: '',
      badgeText: 'Solusi Unggulan KGS',
      imageUrl: '',
      isActive: true
    });
    setIsAddingSlide(true);
  };

  const openEditSlideModal = (slide: HeroSlideItem) => {
    setEditingSlide(slide);
    setSlideForm({
      title: slide.title,
      subtitle: slide.subtitle,
      badgeText: slide.badgeText || 'Solusi Unggulan KGS',
      imageUrl: slide.imageUrl,
      isActive: slide.isActive
    });
    setIsAddingSlide(true);
  };

  const handleSaveSlideForm = (e: React.FormEvent) => {
    e.preventDefault();
    if (!slideForm.imageUrl.trim()) return;

    if (editingSlide) {
      const updated = sliderConfig.slides.map(s =>
        s.id === editingSlide.id
          ? {
              ...s,
              title: slideForm.title.trim() || 'Slide Beranda KGS',
              subtitle: slideForm.subtitle.trim(),
              badgeText: slideForm.badgeText.trim(),
              imageUrl: slideForm.imageUrl.trim(),
              isActive: slideForm.isActive
            }
          : s
      );
      onUpdateSliderConfig({ ...sliderConfig, slides: updated });
      showToast('Perubahan slide beranda berhasil disimpan!');
    } else {
      const nextOrder =
        sliderConfig.slides.length > 0
          ? Math.max(...sliderConfig.slides.map(s => s.sortOrder)) + 1
          : 1;
      const newSlide: HeroSlideItem = {
        id: `slide-${Date.now()}`,
        title: slideForm.title.trim() || 'Slide Beranda KGS',
        subtitle: slideForm.subtitle.trim(),
        badgeText: slideForm.badgeText.trim() || 'Solusi KGS',
        imageUrl: slideForm.imageUrl.trim(),
        sortOrder: nextOrder,
        isActive: slideForm.isActive
      };
      onUpdateSliderConfig({
        ...sliderConfig,
        slides: [...sliderConfig.slides, newSlide]
      });
      showToast('Slide foto baru berhasil ditambahkan ke Beranda!');
    }
    setIsAddingSlide(false);
    setEditingSlide(null);
  };

  const handleDeleteSlide = (id: string) => {
    const filtered = sliderConfig.slides
      .filter(s => s.id !== id)
      .map((s, idx) => ({ ...s, sortOrder: idx + 1 }));
    onUpdateSliderConfig({ ...sliderConfig, slides: filtered });
    showToast('Slide berhasil dihapus dari Beranda.');
  };

  const handleToggleSlideActive = (id: string) => {
    const updated = sliderConfig.slides.map(s =>
      s.id === id ? { ...s, isActive: !s.isActive } : s
    );
    onUpdateSliderConfig({ ...sliderConfig, slides: updated });
    showToast('Status tampil slide diperbarui.');
  };

  const handleMoveSlide = (index: number, direction: 'up' | 'down') => {
    const sorted = [...sliderConfig.slides].sort((a, b) => a.sortOrder - b.sortOrder);
    const targetIdx = direction === 'up' ? index - 1 : index + 1;
    if (targetIdx < 0 || targetIdx >= sorted.length) return;
    const temp = sorted[index];
    sorted[index] = sorted[targetIdx];
    sorted[targetIdx] = temp;
    const reordered = sorted.map((s, i) => ({ ...s, sortOrder: i + 1 }));
    onUpdateSliderConfig({ ...sliderConfig, slides: reordered });
    showToast('Urutan slide beranda berhasil diperbarui.');
  };

  const sortedSlides = [...sliderConfig.slides].sort((a, b) => a.sortOrder - b.sortOrder);

  return (
    <div className="space-y-6 text-xs">
      {/* Top Banner & Live Visual Editor Shortcut */}
      <div className="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div className="space-y-1.5">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 font-bold text-[11px]">
            <Sparkles className="w-3.5 h-3.5" />
            <span>KUSTOMISASI VISUAL, TEMA & SLIDER BERANDA</span>
          </div>
          <h2 className="text-lg sm:text-xl font-extrabold">
            Pengaturan Tema Website, Slider Foto Beranda & Edit Langsung
          </h2>
          <p className="text-slate-300 text-xs max-w-2xl leading-relaxed">
            Atur gambar slider halaman Beranda (tampil di <strong>sebelah kanan teks</strong> atau sebagai <strong>background beranda</strong>), ganti tema warna seluruh website, atau buka website dalam <strong>Mode Edit Langsung</strong> untuk mengedit teks dan gambar langsung di halaman publik.
          </p>
        </div>

        <button
          type="button"
          onClick={onOpenLiveEditor}
          className="px-4 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold rounded-xl shadow-md transition flex items-center gap-2 shrink-0 cursor-pointer"
        >
          <Edit3 className="w-4 h-4" />
          <span>Edit Langsung di Website (Live Editor)</span>
        </button>
      </div>

      {/* Toast Notification */}
      {savedNotice && (
        <div className="bg-emerald-100 border border-emerald-300 text-emerald-900 px-4 py-3 rounded-xl font-bold flex items-center justify-between shadow-xs">
          <div className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-700 shrink-0" />
            <span>{savedNotice}</span>
          </div>
          <button
            type="button"
            onClick={() => setSavedNotice('')}
            className="text-emerald-700 hover:text-emerald-950 cursor-pointer"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Navigation Tabs */}
      <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <button
          type="button"
          onClick={() => setActiveSubTab('slider')}
          className={`px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition cursor-pointer ${
            activeSubTab === 'slider'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
          }`}
        >
          <ImageIcon className="w-4 h-4 text-blue-400" />
          <span>1. Pengaturan Slider Gambar Beranda ({sliderConfig.slides.length} Slide — Sumber: slideshow.php)</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('theme')}
          className={`px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition cursor-pointer ${
            activeSubTab === 'theme'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
          }`}
        >
          <Palette className="w-4 h-4 text-amber-400" />
          <span>2. Pengaturan Tema, Warna Logo &amp; PT. KREATIF GLOBAL SOLUSINDO ({themeConfig.name})</span>
        </button>
      </div>

      {/* PANEL CEPAT KUSTOMISASI LOGO SEBELAH KIRI & WARNA TULISAN PT. KREATIF GLOBAL SOLUSINDO (VERSI MOBILE & DESKTOP) */}
      {companySettings && onUpdateCompanySettings && (
        <div className="bg-white rounded-2xl border-2 border-blue-200 p-5 shadow-xs space-y-4">
          <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-3.5">
            <div className="space-y-1">
              <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-extrabold uppercase tracking-wider">
                <span>📱 Tampilan Versi Mobile &amp; Desktop Header</span>
              </div>
              <h3 className="text-sm font-extrabold text-slate-900">
                Logo di Sebelah Kiri Tulisan &quot;PT. KREATIF GLOBAL SOLUSINDO&quot; &amp; Kustomisasi Warna
              </h3>
              <p className="text-[11px] text-slate-500">
                Pada versi mobile maupun desktop, posisi Logo berada tepat di sebelah kiri tulisan <strong>PT. KREATIF GLOBAL SOLUSINDO</strong>. Sesuaikan warna latar logo, warna teks logo, warna judul perusahaan, dan sub-judul layanan di bawah ini:
              </p>
            </div>

            {/* Live Mobile Header Preview */}
            <div className="bg-slate-100 p-2.5 rounded-2xl border border-slate-200 shrink-0">
              <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 px-1 flex items-center justify-between">
                <span>Preview Tampilan Mobile (Layar HP)</span>
                <span className="text-emerald-600 font-extrabold">● Live</span>
              </div>
              <div className="bg-white rounded-xl border border-slate-200 px-3 py-2.5 shadow-2xs flex items-center justify-between gap-3 max-w-[340px]">
                <div className="flex flex-row items-center gap-2.5 min-w-0 flex-1">
                  {companySettings.logoUrl ? (
                    <img
                      src={companySettings.logoUrl}
                      alt={companySettings.legalName}
                      className="h-9 w-auto max-w-[90px] object-contain rounded-lg border border-slate-200 p-0.5 shrink-0 bg-white"
                    />
                  ) : (
                    <div
                      style={{
                        backgroundColor: companySettings.logoBgColor || '#0f172a',
                        color: companySettings.logoTextColor || '#ffffff'
                      }}
                      className="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs tracking-tight shadow-xs shrink-0"
                    >
                      KGS
                    </div>
                  )}
                  <div className="flex flex-col justify-center min-w-0 flex-1 text-left">
                    <span
                      style={{ color: companySettings.companyNameColor || '#0f172a' }}
                      className="font-extrabold text-xs tracking-tight leading-tight truncate block"
                    >
                      {companySettings.legalName || 'PT. KREATIF GLOBAL SOLUSINDO'}
                    </span>
                    <span
                      style={{ color: companySettings.sloganColor || '#2563eb' }}
                      className="text-[10px] font-semibold tracking-wide leading-snug truncate block mt-0.5"
                    >
                      {companySettings.slogan || 'System Integrator, Security Solution & Internet Provider'}
                    </span>
                  </div>
                </div>
                <div className="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold shrink-0">
                  ☰
                </div>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                1. Warna Latar Logo (Sebelah Kiri)
              </label>
              <div className="flex items-center gap-2">
                <input
                  type="color"
                  value={companySettings.logoBgColor || '#0f172a'}
                  onChange={e => {
                    onUpdateCompanySettings({
                      ...companySettings,
                      logoBgColor: e.target.value
                    });
                    showToast('Warna latar Logo KGS berhasil diperbarui!');
                  }}
                  className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0 bg-white p-0.5"
                />
                <input
                  type="text"
                  value={companySettings.logoBgColor || '#0f172a'}
                  onChange={e =>
                    onUpdateCompanySettings({
                      ...companySettings,
                      logoBgColor: e.target.value
                    })
                  }
                  className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                />
              </div>
            </div>

            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                2. Warna Teks &quot;KGS&quot; pada Logo
              </label>
              <div className="flex items-center gap-2">
                <input
                  type="color"
                  value={companySettings.logoTextColor || '#ffffff'}
                  onChange={e => {
                    onUpdateCompanySettings({
                      ...companySettings,
                      logoTextColor: e.target.value
                    });
                    showToast('Warna teks Logo KGS berhasil diperbarui!');
                  }}
                  className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0 bg-white p-0.5"
                />
                <input
                  type="text"
                  value={companySettings.logoTextColor || '#ffffff'}
                  onChange={e =>
                    onUpdateCompanySettings({
                      ...companySettings,
                      logoTextColor: e.target.value
                    })
                  }
                  className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                />
              </div>
            </div>

            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                3. Warna Tulisan PT. KREATIF GLOBAL SOLUSINDO
              </label>
              <div className="flex items-center gap-2">
                <input
                  type="color"
                  value={companySettings.companyNameColor || '#0f172a'}
                  onChange={e => {
                    onUpdateCompanySettings({
                      ...companySettings,
                      companyNameColor: e.target.value
                    });
                    showToast('Warna tulisan PT. KREATIF GLOBAL SOLUSINDO berhasil diperbarui!');
                  }}
                  className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0 bg-white p-0.5"
                />
                <input
                  type="text"
                  value={companySettings.companyNameColor || '#0f172a'}
                  onChange={e =>
                    onUpdateCompanySettings({
                      ...companySettings,
                      companyNameColor: e.target.value
                    })
                  }
                  className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                />
              </div>
            </div>

            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                4. Warna Sub-Judul (System Integrator...)
              </label>
              <div className="flex items-center gap-2">
                <input
                  type="color"
                  value={companySettings.sloganColor || '#2563eb'}
                  onChange={e => {
                    onUpdateCompanySettings({
                      ...companySettings,
                      sloganColor: e.target.value
                    });
                    showToast('Warna sub-judul layanan berhasil diperbarui!');
                  }}
                  className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0 bg-white p-0.5"
                />
                <input
                  type="text"
                  value={companySettings.sloganColor || '#2563eb'}
                  onChange={e =>
                    onUpdateCompanySettings({
                      ...companySettings,
                      sloganColor: e.target.value
                    })
                  }
                  className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* TAB 1: HERO SLIDER BERANDA */}
      {activeSubTab === 'slider' && (
        <div className="space-y-6">
          {/* Card 1: Posisi Tampilan Slider (Sebelah Kanan vs Background vs Keduanya) */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
            <div>
              <h3 className="text-sm font-extrabold text-slate-900">
                A. Pilih Posisi / Tata Letak Gambar Slider di Halaman Beranda
              </h3>
              <p className="text-slate-500 text-xs mt-0.5">
                Tentukan apakah foto slider yang di-upload ditampilkan <strong>di sebelah kanan teks beranda</strong>, dijadikan <strong>background penuh beranda</strong>, atau keduanya.
              </p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {/* Option 1: Side */}
              <button
                type="button"
                onClick={() => handleLayoutModeChange('side')}
                className={`p-4 rounded-2xl border-2 text-left transition cursor-pointer flex flex-col justify-between gap-3 ${
                  sliderConfig.layoutMode === 'side'
                    ? 'border-blue-600 bg-blue-50/60 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                }`}
              >
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="p-2 rounded-xl bg-blue-600 text-white">
                      <Columns className="w-4 h-4" />
                    </span>
                    {sliderConfig.layoutMode === 'side' && (
                      <span className="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold">
                        AKTIF
                      </span>
                    )}
                  </div>
                  <div className="font-extrabold text-slate-900 text-xs">
                    Di Sebelah Teks (Split Kanan)
                  </div>
                  <p className="text-[11px] text-slate-600 leading-relaxed">
                    Teks utama Beranda di sebelah kiri, dan <strong>Slider Foto interaktif tampil di sebelah kanan</strong>.
                  </p>
                </div>
                <div className="grid grid-cols-12 gap-1 h-10 bg-slate-900 rounded-lg p-1.5 items-center">
                  <div className="col-span-6 space-y-1 pl-1">
                    <div className="h-1.5 w-10 bg-slate-400 rounded"></div>
                    <div className="h-1 w-8 bg-slate-600 rounded"></div>
                  </div>
                  <div className="col-span-6 h-full bg-blue-500/80 rounded flex items-center justify-center text-[9px] font-bold text-white">
                    SLIDER FOTO
                  </div>
                </div>
              </button>

              {/* Option 2: Background */}
              <button
                type="button"
                onClick={() => handleLayoutModeChange('background')}
                className={`p-4 rounded-2xl border-2 text-left transition cursor-pointer flex flex-col justify-between gap-3 ${
                  sliderConfig.layoutMode === 'background'
                    ? 'border-blue-600 bg-blue-50/60 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                }`}
              >
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="p-2 rounded-xl bg-indigo-600 text-white">
                      <Maximize2 className="w-4 h-4" />
                    </span>
                    {sliderConfig.layoutMode === 'background' && (
                      <span className="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold">
                        AKTIF
                      </span>
                    )}
                  </div>
                  <div className="font-extrabold text-slate-900 text-xs">
                    Sebagai Background Beranda
                  </div>
                  <p className="text-[11px] text-slate-600 leading-relaxed">
                    Foto slider menjadi <strong>latar belakang (background) penuh</strong> di belakang teks Beranda & panel NOC.
                  </p>
                </div>
                <div className="relative h-10 bg-gradient-to-r from-indigo-900 via-blue-800 to-slate-900 rounded-lg p-1.5 flex items-center justify-between overflow-hidden">
                  <span className="text-[9px] font-bold text-white/90 px-1.5 py-0.5 bg-black/40 rounded">
                    BG SLIDER PENUH
                  </span>
                </div>
              </button>

              {/* Option 3: Both */}
              <button
                type="button"
                onClick={() => handleLayoutModeChange('both')}
                className={`p-4 rounded-2xl border-2 text-left transition cursor-pointer flex flex-col justify-between gap-3 ${
                  sliderConfig.layoutMode === 'both'
                    ? 'border-blue-600 bg-blue-50/60 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                }`}
              >
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="p-2 rounded-xl bg-emerald-600 text-white">
                      <Layout className="w-4 h-4" />
                    </span>
                    {sliderConfig.layoutMode === 'both' && (
                      <span className="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold">
                        AKTIF
                      </span>
                    )}
                  </div>
                  <div className="font-extrabold text-slate-900 text-xs">
                    Background + Sebelah Kanan
                  </div>
                  <p className="text-[11px] text-slate-600 leading-relaxed">
                    Kombinasi gambar di <strong>background beranda</strong> sekaligus <strong>kartu slider di sebelah kanan</strong>.
                  </p>
                </div>
                <div className="grid grid-cols-12 gap-1 h-10 bg-gradient-to-r from-emerald-950 to-blue-900 rounded-lg p-1.5 items-center">
                  <div className="col-span-6 text-[8px] font-bold text-emerald-300 pl-1">
                    BG + TEKS
                  </div>
                  <div className="col-span-6 h-full bg-emerald-500/80 rounded flex items-center justify-center text-[9px] font-bold text-white">
                    SLIDER
                  </div>
                </div>
              </button>

              {/* Option 4: NOC Default */}
              <button
                type="button"
                onClick={() => handleLayoutModeChange('noc')}
                className={`p-4 rounded-2xl border-2 text-left transition cursor-pointer flex flex-col justify-between gap-3 ${
                  sliderConfig.layoutMode === 'noc'
                    ? 'border-blue-600 bg-blue-50/60 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                }`}
              >
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="p-2 rounded-xl bg-slate-700 text-white">
                      <Activity className="w-4 h-4" />
                    </span>
                    {sliderConfig.layoutMode === 'noc' && (
                      <span className="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold">
                        AKTIF
                      </span>
                    )}
                  </div>
                  <div className="font-extrabold text-slate-900 text-xs">
                    Panel Status NOC (Tanpa Foto)
                  </div>
                  <p className="text-[11px] text-slate-600 leading-relaxed">
                    Tampilan klasik Beranda dengan kartu status jaringan NOC KGSNet tanpa slider foto.
                  </p>
                </div>
                <div className="h-10 bg-slate-950 rounded-lg p-1.5 flex items-center justify-center text-[9px] font-mono text-emerald-400">
                  ● NOC ONLINE MODE
                </div>
              </button>
            </div>

            {/* Slider Controls: Autoplay, Interval, Overlay Opacity */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-200">
              <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                  <div className="font-bold text-slate-800">Geser Otomatis (Auto-Slide)</div>
                  <div className="text-[11px] text-slate-500">Pergantian foto otomatis</div>
                </div>
                <input
                  type="checkbox"
                  checked={sliderConfig.autoPlay}
                  onChange={e =>
                    onUpdateSliderConfig({ ...sliderConfig, autoPlay: e.target.checked })
                  }
                  className="w-4 h-4 accent-blue-600 cursor-pointer"
                />
              </div>

              <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <label className="block font-bold text-slate-800 mb-1">
                  Durasi Pergantian Slide ({sliderConfig.intervalSeconds} Detik)
                </label>
                <input
                  type="range"
                  min={2}
                  max={12}
                  value={sliderConfig.intervalSeconds}
                  onChange={e =>
                    onUpdateSliderConfig({
                      ...sliderConfig,
                      intervalSeconds: Number(e.target.value)
                    })
                  }
                  className="w-full accent-blue-600 cursor-pointer"
                />
              </div>

              <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <label className="block font-bold text-slate-800 mb-1">
                  Gelap Overlay Background ({sliderConfig.overlayOpacity}%)
                </label>
                <input
                  type="range"
                  min={35}
                  max={92}
                  value={sliderConfig.overlayOpacity}
                  onChange={e =>
                    onUpdateSliderConfig({
                      ...sliderConfig,
                      overlayOpacity: Number(e.target.value)
                    })
                  }
                  className="w-full accent-blue-600 cursor-pointer"
                />
                <span className="text-[10px] text-slate-500 block">
                  Mengatur ketajaman teks saat mode Background aktif
                </span>
              </div>
            </div>
          </div>

          {/* Card 2: Daftar Foto Slider & Upload (Menggunakan Halaman Slide Show Infrastruktur slideshow.php) */}
          <div className="bg-white rounded-2xl border-2 border-emerald-200 p-6 shadow-xs space-y-5">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
              <div>
                <div className="flex flex-wrap items-center gap-2">
                  <span className="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full text-[10px] font-extrabold">
                    ✓ Default Slider Beranda Aktif (Sumber: slideshow.php)
                  </span>
                  <h3 className="text-sm font-extrabold text-slate-900">
                    B. Daftar Gambar Slider Beranda Saat Ini ({sortedSlides.length} Slide — Halaman Slide Show Infrastruktur)
                  </h3>
                </div>
                <p className="text-slate-500 text-xs mt-1">
                  Menggunakan daftar kategori &amp; gambar dari <strong>Halaman Slide Show Infrastruktur (<code>slideshow.php</code>)</strong>: Fiber Optik, Guy Wire Tower, Self Supporting Tower Monopole, Lightning Protector, IP – PBX, Wireless, Access Door, dan CCTV.
                </p>
              </div>

              <div className="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  onClick={() => {
                    const syncedFromSlideshow: HeroSlideItem[] = [...(slideshowCategories || INITIAL_SLIDESHOW_CATEGORIES)]
                      .sort((a, b) => a.sortOrder - b.sortOrder)
                      .map((cat, idx) => ({
                        id: cat.id || `ss-cat-${idx + 1}`,
                        title: cat.title,
                        subtitle: cat.shortDescription,
                        badgeText: `Slide Show Infrastruktur · ${(cat.images || []).length} Gambar`,
                        imageUrl: cat.coverImage || cat.images?.[0]?.imagePath || '',
                        sortOrder: idx + 1,
                        isActive: cat.isActive !== false,
                        slug: cat.slug,
                        imageCount: (cat.images || []).length
                      }));
                    onUpdateSliderConfig({
                      ...sliderConfig,
                      layoutMode: sliderConfig.layoutMode === 'noc' ? 'side' : sliderConfig.layoutMode,
                      autoPlay: true,
                      slides: syncedFromSlideshow
                    });
                    showToast(
                      `Daftar Gambar Slider Beranda berhasil disinkronkan dari Halaman Slide Show Infrastruktur (${syncedFromSlideshow.length} Slide)!`
                    );
                  }}
                  className="px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer text-xs"
                >
                  <Check className="w-4 h-4 text-emerald-400" />
                  <span>Sinkronkan dari Slide Show Infrastruktur (slideshow.php)</span>
                </button>

                {onOpenSlideshowTab && (
                  <button
                    type="button"
                    onClick={onOpenSlideshowTab}
                    className="px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer text-xs"
                  >
                    <Eye className="w-4 h-4 text-indigo-600" />
                    <span>Kelola Multi-Gambar (slideshow.php)</span>
                  </button>
                )}

                <label className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                  <Upload className="w-4 h-4" />
                  <span>+ Choose File / Upload Foto</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleQuickUploadNewSlide}
                    className="hidden"
                  />
                </label>

                <button
                  type="button"
                  onClick={() => setMediaPickerTarget({ mode: 'new_slide' })}
                  className="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                >
                  <ImageIcon className="w-4 h-4" />
                  <span>🖼️ Pilih dari Media &amp; Upload Gambar</span>
                </button>

                <button
                  type="button"
                  onClick={openAddSlideModal}
                  className="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                >
                  <Plus className="w-4 h-4" />
                  <span>+ Tambah Slide &amp; Caption</span>
                </button>
              </div>
            </div>

            {sortedSlides.length === 0 ? (
              <div className="p-10 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 space-y-3">
                <ImageIcon className="w-10 h-10 text-slate-400 mx-auto" />
                <div className="font-bold text-slate-700 text-sm">
                  Belum Ada Gambar pada Slider Beranda
                </div>
                <p className="text-slate-500 text-xs max-w-md mx-auto">
                  Klik tombol <strong>+ Upload Foto Langsung</strong> di atas untuk mengunggah gambar slider halaman Beranda.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                {sortedSlides.map((slide, idx) => (
                  <div
                    key={slide.id}
                    className={`rounded-2xl border overflow-hidden flex flex-col justify-between transition ${
                      slide.isActive
                        ? 'border-slate-200 bg-white shadow-xs'
                        : 'border-slate-200 bg-slate-50 opacity-65'
                    }`}
                  >
                    <div>
                      {/* Image Preview */}
                      <div className="relative h-44 bg-slate-900 overflow-hidden group">
                        <img
                          src={slide.imageUrl}
                          alt={slide.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent" />
                        <div className="absolute top-3 left-3 flex items-center gap-1.5">
                          <span className="px-2.5 py-1 rounded-lg bg-slate-900/90 text-white font-bold text-[10px]">
                            Slide #{idx + 1}
                          </span>
                          <span
                            className={`px-2 py-0.5 rounded-lg text-[10px] font-bold ${
                              slide.isActive
                                ? 'bg-emerald-500 text-slate-950'
                                : 'bg-slate-700 text-slate-300'
                            }`}
                          >
                            {slide.isActive ? 'Aktif' : 'Nonaktif'}
                          </span>
                        </div>

                        {/* Quick Replace Photo Buttons on Hover: Choose File OR Pilih dari Media */}
                        <div className="absolute bottom-3 right-3 flex items-center gap-1.5">
                          <button
                            type="button"
                            onClick={() =>
                              setMediaPickerTarget({
                                mode: 'replace_slide',
                                slideId: slide.id,
                                slideIndex: idx
                              })
                            }
                            className="px-2.5 py-1.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold rounded-lg text-[10px] shadow-md flex items-center gap-1 cursor-pointer"
                            title="Pilih dari File yang Pernah Di-upload (Media & Upload Gambar)"
                          >
                            <ImageIcon className="w-3 h-3" />
                            <span>🖼️ Pilih Media</span>
                          </button>

                          <label className="px-2.5 py-1.5 bg-white/95 hover:bg-white text-slate-900 font-bold rounded-lg text-[10px] shadow-md flex items-center gap-1 cursor-pointer">
                            <Upload className="w-3 h-3 text-blue-600" />
                            <span>Choose File</span>
                            <input
                              type="file"
                              accept="image/*"
                              onChange={e =>
                                handleFileUpload(e, dataUrl => {
                                  const updated = sliderConfig.slides.map(s =>
                                    s.id === slide.id ? { ...s, imageUrl: dataUrl } : s
                                  );
                                  onUpdateSliderConfig({ ...sliderConfig, slides: updated });
                                  showToast(`Foto Slide #${idx + 1} berhasil diganti!`);
                                })
                              }
                              className="hidden"
                            />
                          </label>
                        </div>
                      </div>

                      {/* Slide Info */}
                      <div className="p-4 space-y-1.5">
                        {slide.badgeText && (
                          <span className="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 rounded font-semibold text-[10px]">
                            {slide.badgeText}
                          </span>
                        )}
                        <h4 className="font-bold text-slate-900 text-xs line-clamp-1">
                          {slide.title}
                        </h4>
                        <p className="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                          {slide.subtitle}
                        </p>
                      </div>
                    </div>

                    {/* Slide Actions */}
                    <div className="px-4 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                      <div className="flex items-center gap-1">
                        <button
                          type="button"
                          onClick={() => handleMoveSlide(idx, 'up')}
                          disabled={idx === 0}
                          className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 cursor-pointer"
                          title="Geser Naik"
                        >
                          <ArrowUp className="w-3.5 h-3.5" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleMoveSlide(idx, 'down')}
                          disabled={idx === sortedSlides.length - 1}
                          className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 cursor-pointer"
                          title="Geser Turun"
                        >
                          <ArrowDown className="w-3.5 h-3.5" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleToggleSlideActive(slide.id)}
                          className="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-semibold text-[11px] cursor-pointer"
                        >
                          {slide.isActive ? 'Nonaktifkan' : 'Aktifkan'}
                        </button>
                      </div>

                      <div className="flex items-center gap-1.5">
                        <button
                          type="button"
                          onClick={() => openEditSlideModal(slide)}
                          className="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg text-[11px] cursor-pointer"
                        >
                          Edit
                        </button>
                        <button
                          type="button"
                          onClick={() => handleDeleteSlide(slide.id)}
                          className="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg cursor-pointer"
                          title="Hapus Slide"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 2: PENGATURAN TEMA WEBSITE */}
      {activeSubTab === 'theme' && (
        <div className="space-y-6">
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
            <div>
              <h3 className="text-sm font-extrabold text-slate-900">
                A. Pilih Preset Tema Website (1-Klik Langsung Berubah)
              </h3>
              <p className="text-slate-500 text-xs mt-0.5">
                Pilih tema warna yang sesuai dengan identitas korporasi. Seluruh tombol, aksen bagian, header, dan latar beranda akan otomatis menyesuaikan.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {WEBSITE_THEME_PRESETS.map(preset => {
                const isSelected = themeConfig.presetId === preset.presetId;
                return (
                  <button
                    key={preset.presetId}
                    type="button"
                    onClick={() => handleSelectThemePreset(preset)}
                    className={`p-4 rounded-2xl border-2 text-left transition cursor-pointer flex flex-col justify-between gap-4 ${
                      isSelected
                        ? 'border-blue-600 bg-blue-50/40 shadow-sm'
                        : 'border-slate-200 bg-white hover:border-slate-300'
                    }`}
                  >
                    <div className="space-y-2">
                      <div className="flex items-center justify-between gap-2">
                        <span className="font-extrabold text-slate-900 text-xs">
                          {preset.name}
                        </span>
                        {isSelected && (
                          <span className="px-2 py-0.5 bg-emerald-600 text-white rounded-full text-[10px] font-bold shrink-0">
                            ✓ Tema Aktif
                          </span>
                        )}
                      </div>
                      <p className="text-[11px] text-slate-600 leading-relaxed">
                        {preset.description}
                      </p>
                    </div>

                    {/* Visual Swatch Bar */}
                    <div className="space-y-2 w-full">
                      <div
                        className="h-14 rounded-xl p-3 flex items-center justify-between border border-slate-700/30"
                        style={{ backgroundColor: preset.heroBgColor }}
                      >
                        <div className="space-y-1">
                          <div className="h-2 w-20 bg-white/90 rounded"></div>
                          <div
                            className="h-1.5 w-14 rounded"
                            style={{ backgroundColor: preset.primaryTextAccent }}
                          ></div>
                        </div>
                        <span
                          className="px-2.5 py-1 rounded-lg text-[10px] font-bold text-white shadow-xs"
                          style={{ backgroundColor: preset.primaryColor }}
                        >
                          Tombol Utama
                        </span>
                      </div>

                      <div className="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                        <span>Aksen: {preset.primaryColor}</span>
                        <span>Hero: {preset.heroBgColor}</span>
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>

            {/* Custom Color & Typography Fine-Tuning */}
            <div className="pt-5 border-t border-slate-200 space-y-4">
              <h4 className="font-extrabold text-slate-900 text-xs">
                B. Kustomisasi Lanjutan (Warna Aksen, Gaya Header & Tipografi)
              </h4>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                  <label className="block font-bold text-slate-700 mb-1.5">
                    Warna Utama (Primary Accent)
                  </label>
                  <div className="flex items-center gap-2">
                    <input
                      type="color"
                      value={themeConfig.primaryColor}
                      onChange={e =>
                        onUpdateThemeConfig({
                          ...themeConfig,
                          primaryColor: e.target.value,
                          primaryHover: e.target.value
                        })
                      }
                      className="w-9 h-9 rounded-lg border border-slate-300 cursor-pointer"
                    />
                    <input
                      type="text"
                      value={themeConfig.primaryColor}
                      onChange={e =>
                        onUpdateThemeConfig({
                          ...themeConfig,
                          primaryColor: e.target.value
                        })
                      }
                      className="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono text-xs bg-white"
                    />
                  </div>
                </div>

                <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                  <label className="block font-bold text-slate-700 mb-1.5">
                    Warna Latar Beranda (Hero BG)
                  </label>
                  <div className="flex items-center gap-2">
                    <input
                      type="color"
                      value={themeConfig.heroBgColor}
                      onChange={e =>
                        onUpdateThemeConfig({
                          ...themeConfig,
                          heroBgColor: e.target.value
                        })
                      }
                      className="w-9 h-9 rounded-lg border border-slate-300 cursor-pointer"
                    />
                    <input
                      type="text"
                      value={themeConfig.heroBgColor}
                      onChange={e =>
                        onUpdateThemeConfig({
                          ...themeConfig,
                          heroBgColor: e.target.value
                        })
                      }
                      className="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono text-xs bg-white"
                    />
                  </div>
                </div>

                <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                  <label className="block font-bold text-slate-700 mb-1.5">
                    Gaya Header / Navbar
                  </label>
                  <select
                    value={themeConfig.navbarStyle}
                    onChange={e =>
                      onUpdateThemeConfig({
                        ...themeConfig,
                        navbarStyle: e.target.value as WebsiteThemeConfig['navbarStyle']
                      })
                    }
                    className="w-full border border-slate-300 rounded-lg px-2.5 py-2 bg-white font-semibold text-slate-800"
                  >
                    <option value="light">Terang Bersih (White Corporate)</option>
                    <option value="glass">Transparan Modern (Glassmorphism)</option>
                    <option value="dark">Gelap Eksekutif (Dark Navy)</option>
                  </select>
                </div>

                <div className="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                  <label className="block font-bold text-slate-700 mb-1.5">
                    Jenis Huruf (Tipografi)
                  </label>
                  <select
                    value={themeConfig.fontFamily}
                    onChange={e =>
                      onUpdateThemeConfig({
                        ...themeConfig,
                        fontFamily: e.target.value as WebsiteThemeConfig['fontFamily']
                      })
                    }
                    className="w-full border border-slate-300 rounded-lg px-2.5 py-2 bg-white font-semibold text-slate-800"
                  >
                    <option value="jakarta">Plus Jakarta Sans (Korporat Modern)</option>
                    <option value="inter">Inter UI (Bersih & Teknis)</option>
                    <option value="mono-tech">Engineering Sans & Tech</option>
                  </select>
                </div>
              </div>

              {/* Custom Colors for Mobile & Desktop Header Logo KGS + PT. KREATIF GLOBAL SOLUSINDO */}
              {companySettings && onUpdateCompanySettings && (
                <div className="p-4 rounded-2xl bg-blue-50/50 border border-blue-200 space-y-3 mt-4">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                      <h5 className="font-extrabold text-slate-900 text-xs">
                        C. Warna Logo KGS &amp; Tulisan &quot;PT. KREATIF GLOBAL SOLUSINDO&quot; (Tampil Sebelah Kiri di Mobile &amp; Desktop)
                      </h5>
                      <p className="text-[11px] text-slate-500 mt-0.5">
                        Sesuaikan warna kotak logo KGS di sebelah kiri beserta warna tulisan PT. KREATIF GLOBAL SOLUSINDO dan sub-judulnya.
                      </p>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div className="p-3 rounded-xl bg-white border border-slate-200">
                      <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                        Warna Latar Logo KGS
                      </label>
                      <div className="flex items-center gap-2">
                        <input
                          type="color"
                          value={companySettings.logoBgColor || '#0f172a'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              logoBgColor: e.target.value
                            })
                          }
                          className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0"
                        />
                        <input
                          type="text"
                          value={companySettings.logoBgColor || '#0f172a'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              logoBgColor: e.target.value
                            })
                          }
                          className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                        />
                      </div>
                    </div>

                    <div className="p-3 rounded-xl bg-white border border-slate-200">
                      <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                        Warna Teks &quot;KGS&quot; Logo
                      </label>
                      <div className="flex items-center gap-2">
                        <input
                          type="color"
                          value={companySettings.logoTextColor || '#ffffff'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              logoTextColor: e.target.value
                            })
                          }
                          className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0"
                        />
                        <input
                          type="text"
                          value={companySettings.logoTextColor || '#ffffff'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              logoTextColor: e.target.value
                            })
                          }
                          className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                        />
                      </div>
                    </div>

                    <div className="p-3 rounded-xl bg-white border border-slate-200">
                      <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                        Warna Tulisan PT. KREATIF GLOBAL SOLUSINDO
                      </label>
                      <div className="flex items-center gap-2">
                        <input
                          type="color"
                          value={companySettings.companyNameColor || '#0f172a'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              companyNameColor: e.target.value
                            })
                          }
                          className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0"
                        />
                        <input
                          type="text"
                          value={companySettings.companyNameColor || '#0f172a'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              companyNameColor: e.target.value
                            })
                          }
                          className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                        />
                      </div>
                    </div>

                    <div className="p-3 rounded-xl bg-white border border-slate-200">
                      <label className="block font-bold text-slate-700 mb-1 text-[11px]">
                        Warna Sub-Judul Layanan
                      </label>
                      <div className="flex items-center gap-2">
                        <input
                          type="color"
                          value={companySettings.sloganColor || '#2563eb'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              sloganColor: e.target.value
                            })
                          }
                          className="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer shrink-0"
                        />
                        <input
                          type="text"
                          value={companySettings.sloganColor || '#2563eb'}
                          onChange={e =>
                            onUpdateCompanySettings({
                              ...companySettings,
                              sloganColor: e.target.value
                            })
                          }
                          className="w-full border border-slate-300 rounded-lg px-2 py-1 font-mono text-xs bg-white"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Modal Tambah / Edit Slide */}
      {isAddingSlide && (
        <div className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <h3 className="text-sm font-extrabold text-slate-900">
                {editingSlide ? 'Edit Slide Gambar Beranda' : 'Tambah Slide Gambar Beranda Baru'}
              </h3>
              <button
                type="button"
                onClick={() => setIsAddingSlide(false)}
                className="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSaveSlideForm} className="space-y-4">
              {/* Upload Photo or URL */}
              <div className="space-y-2">
                <label className="block font-bold text-slate-800">
                  Foto Slide Beranda (Upload File atau Masukkan URL) *
                </label>

                {slideForm.imageUrl && (
                  <div className="relative h-40 rounded-xl overflow-hidden bg-slate-900 border border-slate-200">
                    <img
                      src={slideForm.imageUrl}
                      alt="Preview"
                      className="w-full h-full object-cover"
                    />
                  </div>
                )}

                <div className="flex flex-wrap items-center gap-2">
                  <label className="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl cursor-pointer inline-flex items-center gap-1.5">
                    <Upload className="w-3.5 h-3.5" />
                    <span>Choose File (Upload dari Perangkat)</span>
                    <input
                      type="file"
                      accept="image/*"
                      onChange={e =>
                        handleFileUpload(e, dataUrl =>
                          setSlideForm(prev => ({ ...prev, imageUrl: dataUrl }))
                        )
                      }
                      className="hidden"
                    />
                  </label>

                  <button
                    type="button"
                    onClick={() => setMediaPickerTarget({ mode: 'form_slide' })}
                    className="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-xl cursor-pointer inline-flex items-center gap-1.5"
                  >
                    <ImageIcon className="w-3.5 h-3.5" />
                    <span>🖼️ Pilih dari Media &amp; Upload Gambar</span>
                  </button>
                </div>

                <input
                  type="text"
                  required
                  value={slideForm.imageUrl}
                  onChange={e => setSlideForm({ ...slideForm, imageUrl: e.target.value })}
                  placeholder="Atau tempel URL / Path gambar dari Media Manager (uploads/...)"
                  className="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-800 mb-1">
                  Label Badge Kecil (Opsional)
                </label>
                <input
                  type="text"
                  value={slideForm.badgeText}
                  onChange={e => setSlideForm({ ...slideForm, badgeText: e.target.value })}
                  placeholder="Contoh: Infrastruktur Telekomunikasi & ISP"
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-800 mb-1">
                  Judul Slide / Caption Utama *
                </label>
                <input
                  type="text"
                  required
                  value={slideForm.title}
                  onChange={e => setSlideForm({ ...slideForm, title: e.target.value })}
                  placeholder="Contoh: Jaringan Fiber Optic & Tower Guywire Mandiri"
                  className="w-full border border-slate-300 rounded-xl p-2.5 font-semibold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-800 mb-1">
                  Subjudul / Keterangan Slide
                </label>
                <textarea
                  rows={2}
                  value={slideForm.subtitle}
                  onChange={e => setSlideForm({ ...slideForm, subtitle: e.target.value })}
                  placeholder="Keterangan singkat mengenai foto/layanan pada slide ini..."
                  className="w-full border border-slate-300 rounded-xl p-2.5"
                />
              </div>

              <label className="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                <input
                  type="checkbox"
                  checked={slideForm.isActive}
                  onChange={e => setSlideForm({ ...slideForm, isActive: e.target.checked })}
                  className="w-4 h-4 accent-blue-600"
                />
                <span>Tampilkan slide ini secara aktif di halaman Beranda</span>
              </label>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setIsAddingSlide(false)}
                  className="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold cursor-pointer flex items-center gap-1.5"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Slide</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Pilih dari Media & Upload Gambar */}
      <MediaLibraryPickerModal
        isOpen={Boolean(mediaPickerTarget)}
        onClose={() => setMediaPickerTarget(null)}
        slideshowCategories={slideshowCategories}
        sliderConfig={sliderConfig}
        onSelect={(item: MediaLibraryItem) => {
          if (!mediaPickerTarget) return;
          const url = item.url;
          if (mediaPickerTarget.mode === 'form_slide') {
            setSlideForm(prev => ({
              ...prev,
              imageUrl: url,
              title: prev.title || item.title || item.name || prev.title,
              badgeText: prev.badgeText || item.sourceLabel || 'Dokumentasi KGS'
            }));
            showToast('Gambar berhasil dipilih dari Pustaka Media & Upload Gambar!');
          } else if (mediaPickerTarget.mode === 'replace_slide' && mediaPickerTarget.slideId) {
            const updated = sliderConfig.slides.map(s =>
              s.id === mediaPickerTarget.slideId ? { ...s, imageUrl: url } : s
            );
            onUpdateSliderConfig({ ...sliderConfig, slides: updated });
            showToast(
              `Foto Slide #${(mediaPickerTarget.slideIndex ?? 0) + 1} berhasil diganti dari Media & Upload Gambar!`
            );
          } else if (mediaPickerTarget.mode === 'new_slide') {
            const nextOrder =
              sliderConfig.slides.length > 0
                ? Math.max(...sliderConfig.slides.map(s => s.sortOrder)) + 1
                : 1;
            const newSlide: HeroSlideItem = {
              id: `slide-${Date.now()}`,
              title: item.title || item.name || 'Dokumentasi Infrastruktur & Layanan PT. KGS',
              subtitle:
                'Solusi terintegrasi jaringan Fiber Optic, Tower Telekomunikasi, CCTV & ISP KGSNet.',
              badgeText: item.sourceLabel || 'Media & Upload Gambar',
              imageUrl: url,
              sortOrder: nextOrder,
              isActive: true
            };
            onUpdateSliderConfig({
              ...sliderConfig,
              slides: [...sliderConfig.slides, newSlide]
            });
            showToast('Slide baru dari Media & Upload Gambar berhasil ditambahkan ke Beranda!');
          }
          setMediaPickerTarget(null);
        }}
      />
    </div>
  );
};
