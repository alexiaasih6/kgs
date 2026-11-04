import React, { useState, useEffect, useMemo } from 'react';
import {
  INITIAL_SLIDESHOW_CATEGORIES,
  PROJECT_DOCUMENTARY,
  SlideshowCategoryItem,
  ProjectPhoto
} from '../../data/companyData';
import {
  Image as ImageIcon,
  Upload,
  Check,
  Search,
  Trash2,
  Copy,
  X,
  FolderOpen,
  Sparkles
} from 'lucide-react';

export interface MediaLibraryItem {
  id: string;
  name: string;
  title: string;
  caption?: string;
  url: string;
  category: 'uploaded' | 'slideshow' | 'portfolio';
  sourceLabel: string;
  uploadedAt: string;
}

const STORAGE_KEY = 'kgs_media_library_items';

export function getStoredMediaItems(): MediaLibraryItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) return [];
    return parsed.map((item: any) => ({
      ...item,
      name: item.name || item.title || 'Gambar Terunggah',
      title: item.title || item.name || 'Gambar Terunggah',
      caption: item.caption || item.title || item.name || ''
    }));
  } catch {
    return [];
  }
}

export function saveStoredMediaItems(items: MediaLibraryItem[]): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    // ignore storage quota errors
  }
}

export function recordUploadedMedia(
  url: string,
  name = 'Berkas Gambar Terunggah',
  sourceLabel = 'Media & Upload Gambar',
  _fileName?: string
): MediaLibraryItem {
  const cleanUrl = url.trim();
  const existing = getStoredMediaItems();
  const found = existing.find(item => item.url === cleanUrl);
  if (found) return found;

  const cleanLabel = name.replace(/\.[^/.]+$/, '').replace(/[-_]+/g, ' ') || 'Gambar Terunggah';
  const newItem: MediaLibraryItem = {
    id: `media-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
    name: cleanLabel,
    title: cleanLabel,
    caption: cleanLabel,
    url: cleanUrl,
    category: 'uploaded',
    sourceLabel,
    uploadedAt: new Date().toISOString().slice(0, 16).replace('T', ' ')
  };
  const updated = [newItem, ...existing];
  saveStoredMediaItems(updated);
  return newItem;
}

export interface CollectMediaOptions {
  slideshowCategories?: SlideshowCategoryItem[];
  sliderConfig?: any;
  projects?: ProjectPhoto[];
  companyLogo?: string;
}

export function collectAllAvailableMedia(options?: CollectMediaOptions): MediaLibraryItem[] {
  const uploaded = getStoredMediaItems();
  const seenUrls = new Set<string>();
  const result: MediaLibraryItem[] = [];

  const pushUnique = (item: MediaLibraryItem) => {
    if (!item.url || seenUrls.has(item.url)) return;
    seenUrls.add(item.url);
    result.push(item);
  };

  // 1. Custom uploaded items from localStorage
  uploaded.forEach(pushUnique);

  // 2. Custom company logo if uploaded
  try {
    const customLogo = options?.companyLogo || localStorage.getItem('kgs_custom_logo');
    if (customLogo && !customLogo.includes('image_8b7e60bd')) {
      pushUnique({
        id: 'media-custom-logo',
        name: 'Logo Utama PT. Kreatif Global Solusindo',
        title: 'Logo Utama PT. Kreatif Global Solusindo',
        caption: 'Logo Utama PT. Kreatif Global Solusindo',
        url: customLogo,
        category: 'uploaded',
        sourceLabel: 'Logo Perusahaan',
        uploadedAt: 'Tersimpan'
      });
    }
  } catch {
    // ignore
  }

  // 2b. Homepage slider slides if any custom uploaded
  if (options?.sliderConfig?.slides && Array.isArray(options.sliderConfig.slides)) {
    options.sliderConfig.slides.forEach((s: any, idx: number) => {
      if (s?.imageUrl) {
        const label = s.title || `Slide Beranda #${idx + 1}`;
        pushUnique({
          id: `hero-slide-${s.id || idx}`,
          name: label,
          title: label,
          caption: s.subtitle || label,
          url: s.imageUrl,
          category: 'slideshow',
          sourceLabel: s.badgeText || 'Slider Beranda',
          uploadedAt: 'Aktif'
        });
      }
    });
  }

  // 3. Slideshow categories & multi-images (from props, localStorage, or INITIAL_SLIDESHOW_CATEGORIES)
  let slideshowCats: SlideshowCategoryItem[] =
    options?.slideshowCategories && options.slideshowCategories.length > 0
      ? options.slideshowCategories
      : INITIAL_SLIDESHOW_CATEGORIES;
  if (!options?.slideshowCategories) {
    try {
      const savedCats = localStorage.getItem('kgs_slideshow_categories');
      if (savedCats) {
        const parsed = JSON.parse(savedCats);
        if (Array.isArray(parsed) && parsed.length > 0) {
          slideshowCats = parsed;
        }
      }
    } catch {
      // ignore
    }
  }

  slideshowCats.forEach(cat => {
    if (cat.coverImage) {
      const coverTitle = `${cat.title} (Sampul Utama)`;
      pushUnique({
        id: `ss-cover-${cat.id}`,
        name: coverTitle,
        title: coverTitle,
        caption: cat.description || coverTitle,
        url: cat.coverImage,
        category: 'slideshow',
        sourceLabel: `Slide Show · ${cat.title}`,
        uploadedAt: cat.updatedAt || 'Default'
      });
    }
    (cat.images || []).forEach((img, idx) => {
      const imgTitle = img.caption || `${cat.title} #${idx + 1}`;
      pushUnique({
        id: `ss-img-${cat.id}-${img.id || idx}`,
        name: imgTitle,
        title: imgTitle,
        caption: img.caption || imgTitle,
        url: img.imagePath,
        category: 'slideshow',
        sourceLabel: `Slide Show · ${cat.title}`,
        uploadedAt: cat.updatedAt || 'Default'
      });
    });
  });

  // 4. Portfolio projects (from props, localStorage, or PROJECT_DOCUMENTARY)
  let portfolioList: ProjectPhoto[] =
    options?.projects && options.projects.length > 0
      ? options.projects
      : PROJECT_DOCUMENTARY;
  if (!options?.projects) {
    try {
      const savedProj = localStorage.getItem('kgs_projects');
      if (savedProj) {
        const parsed = JSON.parse(savedProj);
        if (Array.isArray(parsed) && parsed.length > 0) {
          portfolioList = parsed;
        }
      }
    } catch {
      // ignore
    }
  }

  portfolioList.forEach(proj => {
    if (proj.imageUrl) {
      const projTitle = `${proj.title} (${proj.clientOrLocation})`;
      pushUnique({
        id: `proj-cover-${proj.id}`,
        name: projTitle,
        title: projTitle,
        caption: proj.description || projTitle,
        url: proj.imageUrl,
        category: 'portfolio',
        sourceLabel: `Portofolio · ${proj.category}`,
        uploadedAt: 'Tersimpan'
      });
    }
    (proj.images || []).forEach((img, idx) => {
      const imgUrl = img.imageUrl || (img as any).imagePath || '';
      const imgTitle = img.caption || `${proj.title} #${idx + 1}`;
      pushUnique({
        id: `proj-img-${proj.id}-${img.id || idx}`,
        name: imgTitle,
        title: imgTitle,
        caption: img.caption || imgTitle,
        url: imgUrl,
        category: 'portfolio',
        sourceLabel: `Portofolio · ${proj.category}`,
        uploadedAt: 'Tersimpan'
      });
    });
  });

  return result;
}

export interface MediaLibraryPickerModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (item: MediaLibraryItem) => void;
  onSelectMultiple?: (items: MediaLibraryItem[]) => void;
  multiple?: boolean;
  multiSelect?: boolean;
  title?: string;
  slideshowCategories?: SlideshowCategoryItem[];
  sliderConfig?: any;
  projects?: ProjectPhoto[];
  companyLogo?: string;
}

export const MediaLibraryPickerModal: React.FC<MediaLibraryPickerModalProps> = ({
  isOpen,
  onClose,
  onSelect,
  onSelectMultiple,
  multiple = false,
  multiSelect = false,
  title = 'Pilih dari File yang Pernah Di-upload (🖼️ Media & Upload Gambar)',
  slideshowCategories,
  sliderConfig,
  projects,
  companyLogo
}) => {
  const isMulti = Boolean(multiple || multiSelect);
  const [filterTab, setFilterTab] = useState<'all' | 'uploaded' | 'slideshow' | 'portfolio'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedUrls, setSelectedUrls] = useState<string[]>([]);
  const [refreshTick, setRefreshTick] = useState(0);
  const [uploadNotice, setUploadNotice] = useState('');

  useEffect(() => {
    if (isOpen) {
      setSelectedUrls([]);
      setRefreshTick(t => t + 1);
    }
  }, [isOpen]);

  const allItems = useMemo(
    () =>
      collectAllAvailableMedia({
        slideshowCategories,
        sliderConfig,
        projects,
        companyLogo
      }),
    [refreshTick, isOpen, slideshowCategories, sliderConfig, projects, companyLogo]
  );

  const filteredItems = useMemo(() => {
    return allItems.filter(item => {
      if (filterTab !== 'all' && item.category !== filterTab) return false;
      if (searchQuery.trim() !== '') {
        const q = searchQuery.toLowerCase();
        return (
          item.name.toLowerCase().includes(q) ||
          item.sourceLabel.toLowerCase().includes(q) ||
          item.url.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [allItems, filterTab, searchQuery]);

  if (!isOpen) return null;

  const handleUploadNewToLibrary = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    Array.from(files).forEach(file => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = ev => {
        const dataUrl = ev.target?.result as string;
        if (!dataUrl) return;
        const created = recordUploadedMedia(dataUrl, file.name, 'Media & Upload Gambar');
        setRefreshTick(t => t + 1);
        setFilterTab('uploaded');
        setUploadNotice(`Berkas "${created.name}" berhasil ditambahkan ke Pustaka Media!`);
        setTimeout(() => setUploadNotice(''), 3500);
      };
      reader.readAsDataURL(file);
    });
  };

  const handleItemClick = (item: MediaLibraryItem) => {
    if (!isMulti) {
      onSelect(item);
      onClose();
      return;
    }
    setSelectedUrls(prev =>
      prev.includes(item.url) ? prev.filter(u => u !== item.url) : [...prev, item.url]
    );
  };

  const handleConfirmMulti = () => {
    if (selectedUrls.length === 0) return;
    const chosenItems = allItems.filter(i => selectedUrls.includes(i.url));
    if (onSelectMultiple) {
      onSelectMultiple(chosenItems);
    } else if (chosenItems[0]) {
      onSelect(chosenItems[0]);
    }
    onClose();
  };

  return (
    <div
      className="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4"
      onClick={e => {
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div className="bg-white rounded-3xl border border-slate-200 max-w-4xl w-full max-h-[88vh] flex flex-col shadow-2xl overflow-hidden text-xs">
        {/* Header */}
        <div className="p-5 bg-slate-900 text-white flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shrink-0">
              <ImageIcon className="w-5 h-5" />
            </div>
            <div>
              <div className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 text-[10px] font-bold uppercase tracking-wider mb-0.5">
                <Sparkles className="w-3 h-3" />
                <span>Pustaka Berkas Terunggah · Media Manager</span>
              </div>
              <h3 className="text-sm sm:text-base font-extrabold text-white">{title}</h3>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white cursor-pointer"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Filter Bar + Upload New to Media Library */}
        <div className="p-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
          <div className="flex flex-wrap items-center gap-1.5">
            <button
              type="button"
              onClick={() => setFilterTab('all')}
              className={`px-3 py-1.5 rounded-xl font-bold transition cursor-pointer ${
                filterTab === 'all'
                  ? 'bg-slate-900 text-white'
                  : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'
              }`}
            >
              Semua ({allItems.length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('uploaded')}
              className={`px-3 py-1.5 rounded-xl font-bold transition cursor-pointer ${
                filterTab === 'uploaded'
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'
              }`}
            >
              🖼️ Media Terunggah ({allItems.filter(i => i.category === 'uploaded').length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('slideshow')}
              className={`px-3 py-1.5 rounded-xl font-bold transition cursor-pointer ${
                filterTab === 'slideshow'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'
              }`}
            >
              Slide Show ({allItems.filter(i => i.category === 'slideshow').length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('portfolio')}
              className={`px-3 py-1.5 rounded-xl font-bold transition cursor-pointer ${
                filterTab === 'portfolio'
                  ? 'bg-emerald-600 text-white'
                  : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'
              }`}
            >
              Portofolio ({allItems.filter(i => i.category === 'portfolio').length})
            </button>
          </div>

          <div className="flex items-center gap-2">
            <div className="relative flex-1 sm:w-56">
              <Search className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                placeholder="Cari nama gambar / kategori..."
                className="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs"
              />
            </div>

            <label className="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl cursor-pointer inline-flex items-center gap-1.5 shrink-0 shadow-2xs">
              <Upload className="w-3.5 h-3.5" />
              <span>+ Upload Baru ke Media</span>
              <input
                type="file"
                accept="image/*"
                multiple
                onChange={handleUploadNewToLibrary}
                className="hidden"
              />
            </label>
          </div>
        </div>

        {uploadNotice && (
          <div className="mx-4 mt-3 px-3.5 py-2 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-700 shrink-0" />
            <span>{uploadNotice}</span>
          </div>
        )}

        {/* Grid of Media Items */}
        <div className="p-4 overflow-y-auto flex-1">
          {filteredItems.length === 0 ? (
            <div className="py-12 text-center text-slate-400 space-y-2">
              <FolderOpen className="w-10 h-10 mx-auto text-slate-300" />
              <div className="font-bold text-slate-600">Tidak ada berkas gambar yang cocok</div>
              <p>Klik tombol &quot;+ Upload Baru ke Media&quot; di kanan atas untuk mengunggah gambar baru.</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
              {filteredItems.map(item => {
                const isSelected = selectedUrls.includes(item.url);
                return (
                  <div
                    key={item.id}
                    onClick={() => handleItemClick(item)}
                    className={`group rounded-2xl border-2 overflow-hidden cursor-pointer flex flex-col justify-between transition ${
                      isSelected
                        ? 'border-blue-600 bg-blue-50/40 ring-2 ring-blue-500/30'
                        : 'border-slate-200 hover:border-blue-500 bg-white'
                    }`}
                  >
                    <div className="relative h-32 bg-slate-900 overflow-hidden">
                      <img
                        src={item.url}
                        alt={item.name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                      <span className="absolute top-2 left-2 px-2 py-0.5 rounded bg-slate-900/85 text-white font-bold text-[9px] truncate max-w-[85%]">
                        {item.sourceLabel}
                      </span>
                      {isSelected && (
                        <div className="absolute top-2 right-2 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center shadow">
                          <Check className="w-3.5 h-3.5" />
                        </div>
                      )}
                    </div>

                    <div className="p-2.5 space-y-2">
                      <div className="font-bold text-slate-800 truncate" title={item.name}>
                        {item.name}
                      </div>
                      <button
                        type="button"
                        onClick={e => {
                          e.stopPropagation();
                          handleItemClick(item);
                        }}
                        className={`w-full py-1.5 rounded-lg font-bold text-[11px] flex items-center justify-center gap-1 transition cursor-pointer ${
                          isSelected
                            ? 'bg-blue-600 text-white'
                            : 'bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white'
                        }`}
                      >
                        <Check className="w-3.5 h-3.5" />
                        <span>{isMulti ? (isSelected ? 'Terpilih' : 'Pilih Gambar') : 'Gunakan Gambar Ini'}</span>
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
          <div className="text-[11px] text-slate-500">
            Menampilkan <strong>{filteredItems.length}</strong> berkas gambar dari Pustaka Media &amp; Galeri Website.
          </div>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-bold cursor-pointer"
            >
              Tutup
            </button>
            {isMulti && (
              <button
                type="button"
                disabled={selectedUrls.length === 0}
                onClick={handleConfirmMulti}
                className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold cursor-pointer flex items-center gap-1.5"
              >
                <Check className="w-4 h-4" />
                <span>Gunakan {selectedUrls.length} Gambar Terpilih</span>
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export const AdminMediaManager: React.FC<CollectMediaOptions> = ({
  slideshowCategories,
  sliderConfig,
  projects,
  companyLogo
}) => {
  const [refreshKey, setRefreshKey] = useState(0);
  const [filterTab, setFilterTab] = useState<'all' | 'uploaded' | 'slideshow' | 'portfolio'>('all');
  const [notice, setNotice] = useState('');

  const allItems = useMemo(
    () =>
      collectAllAvailableMedia({
        slideshowCategories,
        sliderConfig,
        projects,
        companyLogo
      }),
    [refreshKey, slideshowCategories, sliderConfig, projects, companyLogo]
  );
  const filtered = allItems.filter(i => filterTab === 'all' || i.category === filterTab);

  const showMsg = (m: string) => {
    setNotice(m);
    setTimeout(() => setNotice(''), 3500);
  };

  const handleUploadFiles = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;
    Array.from(files).forEach(file => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = ev => {
        const res = ev.target?.result as string;
        if (res) {
          recordUploadedMedia(res, file.name, 'Media & Upload Gambar');
          setRefreshKey(k => k + 1);
          showMsg(`Berkas "${file.name}" berhasil diunggah ke Pustaka Media & Upload Gambar!`);
        }
      };
      reader.readAsDataURL(file);
    });
  };

  const handleDeleteUploaded = (id: string) => {
    const current = getStoredMediaItems();
    const updated = current.filter(item => item.id !== id);
    saveStoredMediaItems(updated);
    setRefreshKey(k => k + 1);
    showMsg('Berkas media berhasil dihapus dari pustaka.');
  };

  const handleCopyPath = (url: string) => {
    navigator.clipboard?.writeText(url);
    showMsg('URL / Path gambar berhasil disalin!');
  };

  return (
    <div className="space-y-6 text-xs">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl font-bold text-slate-900">🖼️ Media &amp; Upload Gambar (Pustaka Berkas Terunggah)</h1>
          <p className="text-xs text-slate-500">
            Unggah gambar (JPG, PNG, WEBP, GIF) ke pustaka media. Semua gambar di sini dapat langsung dipilih tanpa perlu upload ulang saat mengatur Slider Beranda, Slide Show, Portofolio, Layanan, Artikel, maupun Logo.
          </p>
        </div>
      </div>

      {notice && (
        <div className="bg-emerald-100 border border-emerald-300 text-emerald-900 p-3.5 rounded-xl font-bold flex items-center gap-2">
          <Check className="w-4 h-4 text-emerald-700 shrink-0" />
          <span>{notice}</span>
        </div>
      )}

      {/* Upload Box */}
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div className="space-y-1">
          <div className="font-extrabold text-slate-900 text-sm">
            Unggah Berkas Gambar Baru ke Pustaka Media
          </div>
          <p className="text-slate-500 text-xs">
            Mendukung pemilihan banyak file sekaligus (Multi-Upload JPG, PNG, WEBP, GIF).
          </p>
        </div>
        <label className="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer inline-flex items-center gap-2 shrink-0">
          <Upload className="w-4 h-4" />
          <span>Choose File / Unggah Gambar Baru</span>
          <input
            type="file"
            accept="image/*"
            multiple
            onChange={handleUploadFiles}
            className="hidden"
          />
        </label>
      </div>

      {/* Library Grid */}
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
          <h2 className="font-extrabold text-slate-900 text-sm">
            Pustaka Berkas Terunggah ({filtered.length} File)
          </h2>
          <div className="flex flex-wrap items-center gap-1.5">
            <button
              type="button"
              onClick={() => setFilterTab('all')}
              className={`px-3 py-1.5 rounded-xl font-bold cursor-pointer ${
                filterTab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'
              }`}
            >
              Semua ({allItems.length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('uploaded')}
              className={`px-3 py-1.5 rounded-xl font-bold cursor-pointer ${
                filterTab === 'uploaded' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'
              }`}
            >
              Media Baru ({allItems.filter(i => i.category === 'uploaded').length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('slideshow')}
              className={`px-3 py-1.5 rounded-xl font-bold cursor-pointer ${
                filterTab === 'slideshow' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700'
              }`}
            >
              Slide Show ({allItems.filter(i => i.category === 'slideshow').length})
            </button>
            <button
              type="button"
              onClick={() => setFilterTab('portfolio')}
              className={`px-3 py-1.5 rounded-xl font-bold cursor-pointer ${
                filterTab === 'portfolio' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'
              }`}
            >
              Portofolio ({allItems.filter(i => i.category === 'portfolio').length})
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {filtered.map(item => (
            <div
              key={item.id}
              className="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex flex-col justify-between"
            >
              <div className="h-36 bg-slate-900 relative overflow-hidden">
                <img
                  src={item.url}
                  alt={item.name}
                  className="w-full h-full object-cover"
                />
                <span className="absolute top-2 left-2 px-2 py-0.5 rounded bg-slate-900/80 text-white font-bold text-[10px]">
                  {item.sourceLabel}
                </span>
              </div>
              <div className="p-3 space-y-2">
                <div className="font-bold text-slate-800 truncate" title={item.name}>
                  {item.name}
                </div>
                <input
                  type="text"
                  readOnly
                  value={item.url}
                  className="w-full bg-white border border-slate-200 rounded px-2 py-1 font-mono text-[10px] text-slate-600"
                />
                <div className="flex items-center justify-between gap-2 pt-1">
                  <button
                    type="button"
                    onClick={() => handleCopyPath(item.url)}
                    className="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-semibold text-[11px] flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <Copy className="w-3 h-3" />
                    <span>Salin Path</span>
                  </button>
                  {item.category === 'uploaded' && (
                    <button
                      type="button"
                      onClick={() => handleDeleteUploaded(item.id)}
                      className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-[11px] cursor-pointer flex items-center gap-1"
                    >
                      <Trash2 className="w-3 h-3" />
                      <span>Hapus</span>
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
