import React, { useState } from 'react';
import {
  SlideshowCategoryItem,
  SlideshowImageItem,
  INITIAL_SLIDESHOW_CATEGORIES
} from '../../data/companyData';
import {
  Images,
  Plus,
  Trash2,
  Edit3,
  Upload,
  Check,
  Star,
  ArrowUp,
  ArrowDown,
  Eye,
  RotateCcw,
  Save,
  X,
  FolderOpen
} from 'lucide-react';
import {
  MediaLibraryPickerModal,
  recordUploadedMedia
} from './MediaLibraryPickerModal';

interface AdminSlideshowProps {
  categories: SlideshowCategoryItem[];
  onUpdateCategories: React.Dispatch<React.SetStateAction<SlideshowCategoryItem[]>>;
  onLogAction: (action: string, details: string) => void;
  onPreviewCategory?: (slug: string) => void;
}

function slugifyText(input: string): string {
  return input
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-');
}

export const AdminSlideshow: React.FC<AdminSlideshowProps> = ({
  categories,
  onUpdateCategories,
  onLogAction,
  onPreviewCategory
}) => {
  const [selectedCatId, setSelectedCatId] = useState<string>(
    categories[0]?.id || 'ss-cat-1'
  );
  const [isAddingCategory, setIsAddingCategory] = useState(false);
  const [editingCatMeta, setEditingCatMeta] = useState(false);
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  // New Category Form state
  const [newTitle, setNewTitle] = useState('');
  const [newSlug, setNewSlug] = useState('');
  const [newShortDesc, setNewShortDesc] = useState('');
  const [newFullDesc, setNewFullDesc] = useState('');
  const [newCoverUrl, setNewCoverUrl] = useState('');

  // New Image Form state
  const [newImgUrl, setNewImgUrl] = useState('');
  const [newImgCaption, setNewImgCaption] = useState('');
  const [newImgAlt, setNewImgAlt] = useState('');

  // Inline image edit state
  const [editingImageId, setEditingImageId] = useState<string | null>(null);
  const [editImgCaption, setEditImgCaption] = useState('');
  const [editImgAlt, setEditImgAlt] = useState('');
  const [editImgOrder, setEditImgOrder] = useState<number>(1);
  const [mediaPickerTarget, setMediaPickerTarget] = useState<
    | { mode: 'add_multi' }
    | { mode: 'new_cover' }
    | { mode: 'edit_cover' }
    | { mode: 'replace_img'; imgId: string }
    | null
  >(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3500);
  };

  const sortedCategories = [...categories].sort(
    (a, b) => a.sortOrder - b.sortOrder
  );

  const activeCategory =
    categories.find(c => c.id === selectedCatId) || sortedCategories[0] || null;

  // Edit category metadata form state
  const [catFormTitle, setCatFormTitle] = useState('');
  const [catFormSlug, setCatFormSlug] = useState('');
  const [catFormShortDesc, setCatFormShortDesc] = useState('');
  const [catFormDesc, setCatFormDesc] = useState('');
  const [catFormCover, setCatFormCover] = useState('');
  const [catFormOrder, setCatFormOrder] = useState(1);
  const [catFormActive, setCatFormActive] = useState(true);

  const startEditCategoryMeta = (cat: SlideshowCategoryItem) => {
    setCatFormTitle(cat.title);
    setCatFormSlug(cat.slug);
    setCatFormShortDesc(cat.shortDescription);
    setCatFormDesc(cat.description);
    setCatFormCover(cat.coverImage);
    setCatFormOrder(cat.sortOrder);
    setCatFormActive(cat.isActive);
    setEditingCatMeta(true);
  };

  const handleSaveCategoryMeta = (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeCategory) return;
    const cleanTitle = catFormTitle.trim();
    const cleanSlug = slugifyText(catFormSlug || cleanTitle);
    if (!cleanTitle || !cleanSlug) return;

    const now = new Date().toISOString().slice(0, 16).replace('T', ' ');
    onUpdateCategories(prev =>
      prev.map(c =>
        c.id === activeCategory.id
          ? {
              ...c,
              title: cleanTitle,
              slug: cleanSlug,
              shortDescription: catFormShortDesc.trim(),
              description: catFormDesc.trim(),
              coverImage: catFormCover.trim() || c.coverImage,
              sortOrder: Number(catFormOrder) || c.sortOrder,
              isActive: catFormActive,
              updatedAt: now
            }
          : c
      )
    );
    setEditingCatMeta(false);
    onLogAction(
      'Update Kategori Slide Show',
      `Memperbarui kategori Slide Show "${cleanTitle}" (${cleanSlug})`
    );
    showToast(`Kategori "${cleanTitle}" berhasil diperbarui!`);
  };

  const handleCreateCategory = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanTitle = newTitle.trim();
    const cleanSlug = slugifyText(newSlug || cleanTitle);
    if (!cleanTitle || !cleanSlug) return;

    const now = new Date().toISOString().slice(0, 16).replace('T', ' ');
    const defaultCover =
      newCoverUrl.trim() ||
      'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80';

    const newCat: SlideshowCategoryItem = {
      id: `ss-cat-${Date.now()}`,
      title: cleanTitle,
      slug: cleanSlug,
      shortDescription:
        newShortDesc.trim() || `Dokumentasi dan spesifikasi teknis ${cleanTitle}.`,
      description:
        newFullDesc.trim() || `Penjelasan teknis dan galeri instalasi ${cleanTitle}.`,
      coverImage: defaultCover,
      sortOrder: categories.length + 1,
      isActive: true,
      createdAt: now,
      updatedAt: now,
      images: [
        {
          id: `ss-img-${Date.now()}-1`,
          imagePath: defaultCover,
          caption: `Gambar utama ${cleanTitle}`,
          altText: cleanTitle,
          sortOrder: 1,
          isPrimary: true
        }
      ]
    };

    onUpdateCategories(prev => [...prev, newCat]);
    setSelectedCatId(newCat.id);
    setNewTitle('');
    setNewSlug('');
    setNewShortDesc('');
    setNewFullDesc('');
    setNewCoverUrl('');
    setIsAddingCategory(false);
    onLogAction(
      'Tambah Kategori Slide Show',
      `Menambahkan kategori baru "${cleanTitle}" (${cleanSlug})`
    );
    showToast(`Kategori "${cleanTitle}" berhasil ditambahkan!`);
  };

  const handleToggleCategoryActive = (cat: SlideshowCategoryItem) => {
    onUpdateCategories(prev =>
      prev.map(c => (c.id === cat.id ? { ...c, isActive: !c.isActive } : c))
    );
    onLogAction(
      'Status Kategori Slide Show',
      `Mengubah status kategori "${cat.title}" menjadi ${!cat.isActive ? 'Aktif' : 'Nonaktif'}`
    );
    showToast(
      `Status "${cat.title}" diubah menjadi ${!cat.isActive ? 'Aktif' : 'Nonaktif'}.`
    );
  };

  const handleDeleteCategory = (cat: SlideshowCategoryItem) => {
    onUpdateCategories(prev => {
      const filtered = prev.filter(c => c.id !== cat.id);
      if (selectedCatId === cat.id && filtered[0]) {
        setSelectedCatId(filtered[0].id);
      }
      return filtered;
    });
    onLogAction(
      'Hapus Kategori Slide Show',
      `Menghapus kategori "${cat.title}" beserta ${cat.images.length} gambar`
    );
    showToast(`Kategori "${cat.title}" telah dihapus.`);
  };

  // Multi-file upload handler (converts selected image files to Data URLs)
  const handleMultiFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0 || !activeCategory) return;

    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxBytes = 5 * 1024 * 1024; // 5MB

    Array.from(files).forEach((file, idx) => {
      if (!validTypes.includes(file.type)) {
        showToast(`Format file ${file.name} tidak didukung (Gunakan JPG, PNG, atau WEBP).`);
        return;
      }
      if (file.size > maxBytes) {
        showToast(`Ukuran file ${file.name} melebihi batas maksimal 5MB.`);
        return;
      }

      const reader = new FileReader();
      reader.onload = ev => {
        const dataUrl = ev.target?.result as string;
        if (!dataUrl) return;
        const baseName = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]+/g, ' ');
        recordUploadedMedia(
          dataUrl,
          `${activeCategory.title} — ${baseName}`,
          activeCategory.title,
          file.name
        );

        onUpdateCategories(prev =>
          prev.map(c => {
            if (c.id !== activeCategory.id) return c;
            const nextOrder = c.images.length + idx + 1;
            const newImg: SlideshowImageItem = {
              id: `ss-img-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
              imagePath: dataUrl,
              caption: newImgCaption.trim() || `${c.title} — ${baseName}`,
              altText: newImgAlt.trim() || baseName,
              sortOrder: nextOrder,
              isPrimary: c.images.length === 0
            };
            return {
              ...c,
              coverImage: c.images.length === 0 ? dataUrl : c.coverImage,
              images: [...c.images, newImg]
            };
          })
        );
      };
      reader.readAsDataURL(file);
    });

    onLogAction(
      'Upload Gambar Slide Show',
      `Mengunggah ${files.length} gambar baru ke kategori "${activeCategory.title}"`
    );
    showToast(`${files.length} gambar berhasil ditambahkan ke "${activeCategory.title}"!`);
    e.target.value = '';
  };

  // Add image by URL
  const handleAddImageByUrl = (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeCategory || !newImgUrl.trim()) return;

    const newImg: SlideshowImageItem = {
      id: `ss-img-${Date.now()}`,
      imagePath: newImgUrl.trim(),
      caption: newImgCaption.trim() || `Dokumentasi ${activeCategory.title}`,
      altText: newImgAlt.trim() || activeCategory.title,
      sortOrder: activeCategory.images.length + 1,
      isPrimary: activeCategory.images.length === 0
    };

    onUpdateCategories(prev =>
      prev.map(c =>
        c.id === activeCategory.id
          ? {
              ...c,
              coverImage: c.images.length === 0 ? newImg.imagePath : c.coverImage,
              images: [...c.images, newImg]
            }
          : c
      )
    );

    setNewImgUrl('');
    setNewImgCaption('');
    setNewImgAlt('');
    onLogAction(
      'Tambah Gambar Slide Show',
      `Menambahkan gambar baru ke kategori "${activeCategory.title}"`
    );
    showToast(`Gambar baru berhasil ditambahkan ke "${activeCategory.title}"!`);
  };

  // Replace existing image file
  const handleReplaceImageFile = (
    imgId: string,
    e: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = e.target.files?.[0];
    if (!file || !activeCategory) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const dataUrl = ev.target?.result as string;
      if (!dataUrl) return;
      recordUploadedMedia(
        dataUrl,
        `${activeCategory.title} — ${file.name}`,
        activeCategory.title,
        file.name
      );
      onUpdateCategories(prev =>
        prev.map(c => {
          if (c.id !== activeCategory.id) return c;
          const updatedImages = c.images.map(img =>
            img.id === imgId ? { ...img, imagePath: dataUrl } : img
          );
          const targetImg = c.images.find(i => i.id === imgId);
          const shouldUpdateCover =
            targetImg?.isPrimary || c.coverImage === targetImg?.imagePath;
          return {
            ...c,
            coverImage: shouldUpdateCover ? dataUrl : c.coverImage,
            images: updatedImages
          };
        })
      );
      showToast('File gambar berhasil diganti & disimpan ke Media!');
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  };

  // Set image as primary / cover
  const handleSetPrimaryImage = (img: SlideshowImageItem) => {
    if (!activeCategory) return;
    onUpdateCategories(prev =>
      prev.map(c =>
        c.id === activeCategory.id
          ? {
              ...c,
              coverImage: img.imagePath,
              images: c.images.map(i => ({
                ...i,
                isPrimary: i.id === img.id
              }))
            }
          : c
      )
    );
    onLogAction(
      'Set Sampul Slide Show',
      `Menetapkan gambar "${img.caption}" sebagai sampul utama "${activeCategory.title}"`
    );
    showToast('Gambar telah ditetapkan sebagai Sampul Utama (Cover)!');
  };

  // Move image sort order up/down
  const handleMoveImageOrder = (imgId: string, direction: 'up' | 'down') => {
    if (!activeCategory) return;
    const sorted = [...activeCategory.images].sort(
      (a, b) => a.sortOrder - b.sortOrder
    );
    const index = sorted.findIndex(i => i.id === imgId);
    if (index === -1) return;
    const swapIndex = direction === 'up' ? index - 1 : index + 1;
    if (swapIndex < 0 || swapIndex >= sorted.length) return;

    const temp = sorted[index];
    sorted[index] = sorted[swapIndex];
    sorted[swapIndex] = temp;

    const normalized = sorted.map((item, idx) => ({
      ...item,
      sortOrder: idx + 1
    }));

    onUpdateCategories(prev =>
      prev.map(c =>
        c.id === activeCategory.id ? { ...c, images: normalized } : c
      )
    );
  };

  // Save inline caption / alt / order edit
  const handleSaveImageEdit = (imgId: string) => {
    if (!activeCategory) return;
    onUpdateCategories(prev =>
      prev.map(c =>
        c.id === activeCategory.id
          ? {
              ...c,
              images: c.images.map(img =>
                img.id === imgId
                  ? {
                      ...img,
                      caption: editImgCaption.trim(),
                      altText: editImgAlt.trim(),
                      sortOrder: Number(editImgOrder) || img.sortOrder
                    }
                  : img
              )
            }
          : c
      )
    );
    setEditingImageId(null);
    showToast('Caption, Alt Text, dan urutan gambar diperbarui!');
  };

  // Delete single image
  const handleDeleteImage = (img: SlideshowImageItem) => {
    if (!activeCategory) return;
    onUpdateCategories(prev =>
      prev.map(c => {
        if (c.id !== activeCategory.id) return c;
        const remaining = c.images
          .filter(i => i.id !== img.id)
          .map((item, idx) => ({ ...item, sortOrder: idx + 1 }));
        const nextCover =
          c.coverImage === img.imagePath
            ? remaining[0]?.imagePath || c.coverImage
            : c.coverImage;
        return {
          ...c,
          coverImage: nextCover,
          images: remaining
        };
      })
    );
    onLogAction(
      'Hapus Gambar Slide Show',
      `Menghapus 1 gambar ("${img.caption}") dari kategori "${activeCategory.title}"`
    );
    showToast('Gambar berhasil dihapus dari kategori.');
  };

  const handleResetDefaults = () => {
    onUpdateCategories(INITIAL_SLIDESHOW_CATEGORIES);
    setSelectedCatId(INITIAL_SLIDESHOW_CATEGORIES[0].id);
    onLogAction(
      'Reset Default Slide Show',
      'Mengembalikan 7 kategori Slide Show & multi-gambar ke standar awal PT. KGS'
    );
    showToast('Seluruh 7 kategori Slide Show dikembalikan ke data default.');
  };

  const sortedActiveImages = activeCategory
    ? [...activeCategory.images].sort((a, b) => a.sortOrder - b.sortOrder)
    : [];

  return (
    <div className="space-y-6">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-emerald-500/40 flex items-center gap-3 text-sm font-bold">
          <Check className="w-5 h-5 text-emerald-400 shrink-0" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Top Header Banner */}
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-extrabold uppercase tracking-wider mb-2">
            <Images className="w-3.5 h-3.5" />
            <span>Modul CMS Slide Show &amp; Galeri Multi-Gambar</span>
          </div>
          <h2 className="text-xl sm:text-2xl font-extrabold text-slate-900">
            Kelola Halaman Slide Show &amp; Galeri Infrastruktur
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Kelola 7+ kategori Slide Show (Self Supporting Tower Monopole, Fiber Optik, Guy Wire Tower, Lightning Protector, IP – PBX, Wireless, Access Door) beserta banyak gambar, caption, dan urutan tampil di setiap halaman detail.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2.5 shrink-0">
          <button
            type="button"
            onClick={() => setIsAddingCategory(!isAddingCategory)}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Kategori Baru</span>
          </button>

          <button
            type="button"
            onClick={handleResetDefaults}
            className="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
            title="Kembalikan ke 7 Kategori Standar"
          >
            <RotateCcw className="w-4 h-4" />
            <span>Reset Default</span>
          </button>
        </div>
      </div>

      {/* Add New Category Modal / Expandable Form */}
      {isAddingCategory && (
        <form
          onSubmit={handleCreateCategory}
          className="bg-blue-50/70 border-2 border-blue-200 rounded-2xl p-6 space-y-4"
        >
          <div className="flex items-center justify-between">
            <h3 className="text-base font-extrabold text-slate-900">
              + Tambah Kategori Halaman Slide Show Baru
            </h3>
            <button
              type="button"
              onClick={() => setIsAddingCategory(false)}
              className="text-slate-400 hover:text-slate-700"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Judul Kategori Slide Show *
              </label>
              <input
                type="text"
                required
                value={newTitle}
                onChange={e => {
                  setNewTitle(e.target.value);
                  if (!newSlug) setNewSlug(slugifyText(e.target.value));
                }}
                placeholder="Contoh: SOLAR PANEL SYSTEM"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Slug URL Halaman Detail (`slideshow-detail.php?slug=...`) *
              </label>
              <input
                type="text"
                required
                value={newSlug}
                onChange={e => setNewSlug(slugifyText(e.target.value))}
                placeholder="solar-panel-system"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Deskripsi Singkat (Tampil di Card Halaman Slide Show Utama)
            </label>
            <input
              type="text"
              value={newShortDesc}
              onChange={e => setNewShortDesc(e.target.value)}
              placeholder="Ringkasan 1-2 kalimat mengenai kategori ini..."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Deskripsi Teknis Lengkap (Tampil di Halaman Detail Slide Show)
            </label>
            <textarea
              rows={5}
              value={newFullDesc}
              onChange={e => setNewFullDesc(e.target.value)}
              placeholder="Tuliskan penjelasan lengkap. Gunakan tanda '- ' di awal baris untuk membuat daftar poin keunggulan..."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              URL Gambar Sampul Awal (Opsional)
            </label>
            <div className="flex flex-wrap gap-2">
              <input
                type="text"
                value={newCoverUrl}
                onChange={e => setNewCoverUrl(e.target.value)}
                placeholder="https://images.unsplash.com/..."
                className="flex-1 min-w-[200px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm"
              />
              <button
                type="button"
                onClick={() => setMediaPickerTarget({ mode: 'new_cover' })}
                className="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer"
              >
                <FolderOpen className="w-3.5 h-3.5" />
                <span>🖼️ Pilih dari Media &amp; Upload Gambar</span>
              </button>
            </div>
          </div>

          <div className="flex justify-end gap-2">
            <button
              type="button"
              onClick={() => setIsAddingCategory(false)}
              className="px-4 py-2 rounded-xl bg-white border border-slate-300 text-xs font-bold text-slate-700"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold"
            >
              Simpan Kategori Baru
            </button>
          </div>
        </form>
      )}

      {/* Main Split Workspace: Left Category List (4 cols) + Right Category & Multi-Image Editor (8 cols) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {/* LEFT COLUMN: Category Selector List */}
        <div className="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs space-y-3">
          <div className="flex items-center justify-between px-1">
            <h3 className="text-xs font-extrabold uppercase tracking-wider text-slate-500">
              Daftar Kategori ({sortedCategories.length})
            </h3>
            <span className="text-[11px] text-slate-400 font-semibold">
              Klik untuk kelola foto
            </span>
          </div>

          <div className="space-y-2">
            {sortedCategories.map(cat => {
              const isSelected = activeCategory?.id === cat.id;
              return (
                <div
                  key={cat.id}
                  onClick={() => {
                    setSelectedCatId(cat.id);
                    setEditingCatMeta(false);
                    setEditingImageId(null);
                  }}
                  className={`p-3 rounded-xl border transition-all cursor-pointer flex items-center gap-3 ${
                    isSelected
                      ? 'bg-blue-50/90 border-blue-500 shadow-xs'
                      : 'bg-white hover:bg-slate-50 border-slate-200'
                  }`}
                >
                  <img
                    src={
                      cat.coverImage ||
                      cat.images[0]?.imagePath ||
                      'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=400&q=80'
                    }
                    alt={cat.title}
                    className="w-14 h-14 rounded-lg object-cover shrink-0 border border-slate-200"
                  />
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-1.5">
                      <span className="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                        #{cat.sortOrder}
                      </span>
                      <span
                        className={`text-[10px] font-bold px-1.5 py-0.5 rounded ${
                          cat.isActive
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-slate-200 text-slate-600'
                        }`}
                      >
                        {cat.isActive ? 'Aktif' : 'Nonaktif'}
                      </span>
                      <span className="text-[10px] font-bold text-blue-600">
                        • {cat.images.length} Foto
                      </span>
                    </div>
                    <h4 className="font-extrabold text-xs sm:text-sm text-slate-900 truncate mt-1">
                      {cat.title}
                    </h4>
                    <p className="text-[11px] text-slate-400 font-mono truncate">
                      ?slug={cat.slug}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* RIGHT COLUMN: Selected Category Detail & Multi-Image Manager */}
        <div className="lg:col-span-8 space-y-6">
          {activeCategory ? (
            <>
              {/* Card 1: Category Info & Edit Controls */}
              <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <div className="flex flex-wrap items-start justify-between gap-4 pb-4 border-b border-slate-100">
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="px-2.5 py-0.5 rounded-md bg-blue-100 text-blue-700 text-xs font-extrabold">
                        Urutan #{activeCategory.sortOrder}
                      </span>
                      <span className="text-xs font-mono text-slate-500">
                        slideshow-detail.php?slug={activeCategory.slug}
                      </span>
                    </div>
                    <h3 className="text-xl font-extrabold text-slate-900 mt-1">
                      {activeCategory.title}
                    </h3>
                  </div>

                  <div className="flex flex-wrap items-center gap-2">
                    {onPreviewCategory && (
                      <button
                        type="button"
                        onClick={() => onPreviewCategory(activeCategory.slug)}
                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 cursor-pointer"
                      >
                        <Eye className="w-3.5 h-3.5" />
                        <span>Lihat Halaman Detail</span>
                      </button>
                    )}

                    <button
                      type="button"
                      onClick={() => startEditCategoryMeta(activeCategory)}
                      className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer"
                    >
                      <Edit3 className="w-3.5 h-3.5" />
                      <span>Edit Teks &amp; Deskripsi</span>
                    </button>

                    <button
                      type="button"
                      onClick={() => handleToggleCategoryActive(activeCategory)}
                      className={`px-3 py-2 rounded-xl text-xs font-bold cursor-pointer ${
                        activeCategory.isActive
                          ? 'bg-amber-50 text-amber-700 border border-amber-200'
                          : 'bg-emerald-600 text-white'
                      }`}
                    >
                      {activeCategory.isActive ? 'Nonaktifkan' : 'Aktifkan'}
                    </button>

                    <button
                      type="button"
                      onClick={() => handleDeleteCategory(activeCategory)}
                      className="p-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 cursor-pointer"
                      title="Hapus Kategori"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>

                {editingCatMeta ? (
                  <form onSubmit={handleSaveCategoryMeta} className="mt-4 space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          Judul Kategori *
                        </label>
                        <input
                          type="text"
                          required
                          value={catFormTitle}
                          onChange={e => setCatFormTitle(e.target.value)}
                          className="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          Urutan Tampil (`sort_order`)
                        </label>
                        <input
                          type="number"
                          value={catFormOrder}
                          onChange={e => setCatFormOrder(Number(e.target.value))}
                          className="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          Slug URL (`?slug=...`)
                        </label>
                        <input
                          type="text"
                          required
                          value={catFormSlug}
                          onChange={e => setCatFormSlug(slugifyText(e.target.value))}
                          className="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm font-mono"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-700 mb-1">
                          URL Gambar Sampul (Cover)
                        </label>
                        <div className="flex flex-wrap gap-2">
                          <input
                            type="text"
                            value={catFormCover}
                            onChange={e => setCatFormCover(e.target.value)}
                            className="flex-1 min-w-[160px] px-3 py-2 rounded-xl border border-slate-300 text-sm"
                          />
                          <button
                            type="button"
                            onClick={() => setMediaPickerTarget({ mode: 'edit_cover' })}
                            className="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer"
                          >
                            <FolderOpen className="w-3.5 h-3.5" />
                            <span>🖼️ Pilih Media</span>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">
                        Deskripsi Singkat (Tampil di Card Grid Slide Show)
                      </label>
                      <input
                        type="text"
                        value={catFormShortDesc}
                        onChange={e => setCatFormShortDesc(e.target.value)}
                        className="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">
                        Deskripsi Teknis Lengkap (Tampil di Halaman Detail Slide Show)
                      </label>
                      <textarea
                        rows={7}
                        value={catFormDesc}
                        onChange={e => setCatFormDesc(e.target.value)}
                        className="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"
                      />
                    </div>

                    <div className="flex justify-end gap-2">
                      <button
                        type="button"
                        onClick={() => setEditingCatMeta(false)}
                        className="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        className="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold"
                      >
                        <Save className="w-4 h-4" />
                        <span>Simpan Perubahan Kategori</span>
                      </button>
                    </div>
                  </form>
                ) : (
                  <div className="mt-4 space-y-2 text-xs sm:text-sm text-slate-600">
                    <p className="font-semibold text-slate-800">
                      Ringkasan Card: {activeCategory.shortDescription}
                    </p>
                    <p className="line-clamp-3 text-slate-500 whitespace-pre-line">
                      {activeCategory.description}
                    </p>
                  </div>
                )}
              </div>

              {/* Card 2: Add Multiple Images to This Category */}
              <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-5">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <div>
                    <h4 className="text-base font-extrabold text-slate-900">
                      + Tambah Banyak Gambar ke Halaman "{activeCategory.title}"
                    </h4>
                    <p className="text-xs text-slate-500">
                      Anda dapat mengunggah beberapa file foto sekaligus (JPG, PNG, WEBP maks. 5MB/file) atau menambahkan gambar melalui URL.
                    </p>
                  </div>

                  {/* Multi-File Upload Trigger + Pilih dari Media & Upload Gambar */}
                  <div className="flex flex-wrap items-center gap-2">
                    <label className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                      <Upload className="w-4 h-4" />
                      <span>Choose File / Upload Gambar</span>
                      <input
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        onChange={handleMultiFileUpload}
                        className="hidden"
                      />
                    </label>
                    <button
                      type="button"
                      onClick={() => setMediaPickerTarget({ mode: 'add_multi' })}
                      className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    >
                      <FolderOpen className="w-4 h-4" />
                      <span>🖼️ Pilih dari Media &amp; Upload Gambar</span>
                    </button>
                  </div>
                </div>

                {/* Add via URL + Caption Form */}
                <form
                  onSubmit={handleAddImageByUrl}
                  className="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-3 border-t border-slate-100"
                >
                  <div className="sm:col-span-5">
                    <input
                      type="text"
                      value={newImgUrl}
                      onChange={e => setNewImgUrl(e.target.value)}
                      placeholder="Atau tempel URL Gambar (https://...)"
                      className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"
                    />
                  </div>
                  <div className="sm:col-span-4">
                    <input
                      type="text"
                      value={newImgCaption}
                      onChange={e => setNewImgCaption(e.target.value)}
                      placeholder="Caption / Keterangan Gambar..."
                      className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"
                    />
                  </div>
                  <div className="sm:col-span-3 flex gap-2">
                    <input
                      type="text"
                      value={newImgAlt}
                      onChange={e => setNewImgAlt(e.target.value)}
                      placeholder="Alt Text"
                      className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"
                    />
                    <button
                      type="submit"
                      className="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shrink-0 cursor-pointer"
                    >
                      + Tambah
                    </button>
                  </div>
                </form>
              </div>

              {/* Card 3: Gallery Image List & Management */}
              <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <div className="flex items-center justify-between mb-4">
                  <h4 className="text-base font-extrabold text-slate-900">
                    Daftar Gambar dalam Kategori Ini ({sortedActiveImages.length} Gambar)
                  </h4>
                  <span className="text-xs text-slate-400">
                    Gunakan tombol panah untuk mengatur urutan slide
                  </span>
                </div>

                {sortedActiveImages.length === 0 ? (
                  <div className="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-sm">
                    Belum ada gambar di kategori ini. Klik tombol "Upload Beberapa Gambar Sekaligus" di atas.
                  </div>
                ) : (
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {sortedActiveImages.map((img, idx) => {
                      const isCover =
                        img.isPrimary ||
                        activeCategory.coverImage === img.imagePath;
                      const isEditingThis = editingImageId === img.id;

                      return (
                        <div
                          key={img.id}
                          className={`rounded-2xl border p-3.5 flex flex-col justify-between gap-3 transition ${
                            isCover
                              ? 'border-blue-500 bg-blue-50/30'
                              : 'border-slate-200 bg-slate-50/50'
                          }`}
                        >
                          <div className="flex gap-3.5">
                            <div className="relative w-28 h-20 rounded-xl overflow-hidden bg-slate-900 shrink-0 border border-slate-200">
                              <img
                                src={img.imagePath}
                                alt={img.altText}
                                className="w-full h-full object-cover"
                              />
                              <span className="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-extrabold">
                                #{idx + 1}
                              </span>
                              {isCover && (
                                <span className="absolute bottom-1 left-1 right-1 text-center py-0.5 rounded bg-blue-600 text-white text-[9px] font-extrabold uppercase">
                                  Sampul Utama
                                </span>
                              )}
                            </div>

                            <div className="min-w-0 flex-1">
                              {isEditingThis ? (
                                <div className="space-y-2">
                                  <input
                                    type="text"
                                    value={editImgCaption}
                                    onChange={e => setEditImgCaption(e.target.value)}
                                    placeholder="Caption gambar"
                                    className="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white"
                                  />
                                  <div className="flex gap-2">
                                    <input
                                      type="text"
                                      value={editImgAlt}
                                      onChange={e => setEditImgAlt(e.target.value)}
                                      placeholder="Alt text"
                                      className="flex-1 px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white"
                                    />
                                    <input
                                      type="number"
                                      value={editImgOrder}
                                      onChange={e =>
                                        setEditImgOrder(Number(e.target.value))
                                      }
                                      className="w-16 px-2 py-1.5 rounded-lg border border-slate-300 text-xs bg-white"
                                    />
                                  </div>
                                  <div className="flex gap-1.5">
                                    <button
                                      type="button"
                                      onClick={() => handleSaveImageEdit(img.id)}
                                      className="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold"
                                    >
                                      Simpan
                                    </button>
                                    <button
                                      type="button"
                                      onClick={() => setEditingImageId(null)}
                                      className="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 text-[11px] font-bold"
                                    >
                                      Batal
                                    </button>
                                  </div>
                                </div>
                              ) : (
                                <>
                                  <div className="text-[11px] font-bold text-blue-600 truncate">
                                    Alt: {img.altText || activeCategory.title}
                                  </div>
                                  <p className="text-xs font-semibold text-slate-800 line-clamp-2 mt-0.5">
                                    {img.caption || 'Tanpa keterangan'}
                                  </p>
                                </>
                              )}
                            </div>
                          </div>

                          {/* Image Action Toolbar */}
                          <div className="flex flex-wrap items-center justify-between gap-1.5 pt-2.5 border-t border-slate-200/80 text-[11px]">
                            <div className="flex items-center gap-1">
                              <button
                                type="button"
                                onClick={() => handleMoveImageOrder(img.id, 'up')}
                                disabled={idx === 0}
                                title="Geser ke Atas"
                                className="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 cursor-pointer"
                              >
                                <ArrowUp className="w-3.5 h-3.5" />
                              </button>
                              <button
                                type="button"
                                onClick={() => handleMoveImageOrder(img.id, 'down')}
                                disabled={idx === sortedActiveImages.length - 1}
                                title="Geser ke Bawah"
                                className="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 cursor-pointer"
                              >
                                <ArrowDown className="w-3.5 h-3.5" />
                              </button>
                              <button
                                type="button"
                                onClick={() => {
                                  setEditingImageId(img.id);
                                  setEditImgCaption(img.caption);
                                  setEditImgAlt(img.altText);
                                  setEditImgOrder(img.sortOrder);
                                }}
                                className="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold cursor-pointer"
                              >
                                Edit Caption
                              </button>
                            </div>

                            <div className="flex items-center gap-1">
                              {!isCover && (
                                <button
                                  type="button"
                                  onClick={() => handleSetPrimaryImage(img)}
                                  title="Jadikan Sampul Utama Kategori"
                                  className="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold flex items-center gap-1 cursor-pointer"
                                >
                                  <Star className="w-3 h-3" />
                                  <span>Set Sampul</span>
                                </button>
                              )}

                              <label
                                title="Ganti File Gambar (Choose File)"
                                className="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold flex items-center gap-1 cursor-pointer"
                              >
                                <Upload className="w-3 h-3 text-blue-600" />
                                <span>Choose File</span>
                                <input
                                  type="file"
                                  accept="image/jpeg,image/png,image/webp"
                                  onChange={e => handleReplaceImageFile(img.id, e)}
                                  className="hidden"
                                />
                              </label>

                              <button
                                type="button"
                                onClick={() =>
                                  setMediaPickerTarget({ mode: 'replace_img', imgId: img.id })
                                }
                                title="Pilih dari Media & Upload Gambar"
                                className="px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold flex items-center gap-1 cursor-pointer"
                              >
                                <FolderOpen className="w-3 h-3 text-indigo-600" />
                                <span>🖼️ Media</span>
                              </button>

                              <button
                                type="button"
                                onClick={() => handleDeleteImage(img)}
                                title="Hapus Gambar Ini"
                                className="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 cursor-pointer"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
                              </button>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </>
          ) : null}
        </div>
      </div>

      {/* Modal Pilih dari Media & Upload Gambar */}
      <MediaLibraryPickerModal
        isOpen={mediaPickerTarget !== null}
        onClose={() => setMediaPickerTarget(null)}
        multiple={mediaPickerTarget?.mode === 'add_multi'}
        slideshowCategories={categories}
        title={
          mediaPickerTarget?.mode === 'add_multi'
            ? `Pilih Gambar dari Media & Upload Gambar untuk "${activeCategory?.title || ''}"`
            : 'Pilih Gambar dari Media & Upload Gambar'
        }
        onSelect={item => {
          if (!mediaPickerTarget) return;
          if (mediaPickerTarget.mode === 'new_cover') {
            setNewCoverUrl(item.url);
            showToast(`Gambar sampul dipilih dari Media: "${item.title}"`);
          } else if (mediaPickerTarget.mode === 'edit_cover') {
            setCatFormCover(item.url);
            showToast(`Gambar sampul dipilih dari Media: "${item.title}"`);
          } else if (mediaPickerTarget.mode === 'replace_img' && activeCategory) {
            const targetId = mediaPickerTarget.imgId;
            onUpdateCategories(prev =>
              prev.map(c => {
                if (c.id !== activeCategory.id) return c;
                const updatedImages = c.images.map(img =>
                  img.id === targetId ? { ...img, imagePath: item.url } : img
                );
                const targetImg = c.images.find(i => i.id === targetId);
                const shouldUpdateCover =
                  targetImg?.isPrimary || c.coverImage === targetImg?.imagePath;
                return {
                  ...c,
                  coverImage: shouldUpdateCover ? item.url : c.coverImage,
                  images: updatedImages
                };
              })
            );
            showToast(`Gambar berhasil diganti dari Media: "${item.title}"`);
          }
        }}
        onSelectMultiple={items => {
          if (!activeCategory || items.length === 0) return;
          onUpdateCategories(prev =>
            prev.map(c => {
              if (c.id !== activeCategory.id) return c;
              const startOrder = c.images.length;
              const added: SlideshowImageItem[] = items.map((item, idx) => ({
                id: `ss-img-${Date.now()}-${idx}-${Math.random().toString(36).slice(2, 6)}`,
                imagePath: item.url,
                caption: newImgCaption.trim() || item.caption || item.title,
                altText: newImgAlt.trim() || item.title,
                sortOrder: startOrder + idx + 1,
                isPrimary: c.images.length === 0 && idx === 0
              }));
              return {
                ...c,
                coverImage: c.images.length === 0 ? added[0].imagePath : c.coverImage,
                images: [...c.images, ...added]
              };
            })
          );
          onLogAction(
            'Pilih Media Slide Show',
            `Menambahkan ${items.length} gambar dari Media Library ke "${activeCategory.title}"`
          );
          showToast(
            `${items.length} gambar dari Media & Upload Gambar berhasil ditambahkan ke "${activeCategory.title}"!`
          );
        }}
      />
    </div>
  );
};
