import React, { useState, useEffect } from 'react';
import {
  NavigationMenuItem,
  INITIAL_NAVIGATION_MENUS,
  StaticPageItem,
  resolveUrlToPageSlug,
  resolvePageSlugToUrl
} from '../../data/companyData';
import {
  Plus,
  Save,
  Trash2,
  Edit3,
  ArrowUp,
  ArrowDown,
  RotateCcw,
  Check,
  X,
  Compass,
  Power,
  FileText
} from 'lucide-react';

interface AdminNavigationProps {
  menus: NavigationMenuItem[];
  pages: StaticPageItem[];
  onUpdateMenus: (menus: NavigationMenuItem[]) => void;
  onUpdatePages?: (pages: StaticPageItem[]) => void;
}

export const AdminNavigation: React.FC<AdminNavigationProps> = ({
  menus,
  pages,
  onUpdateMenus,
  onUpdatePages
}) => {
  const [activeTab, setActiveTab] = useState<'all' | 'header' | 'footer'>('all');
  const [draftMenus, setDraftMenus] = useState<NavigationMenuItem[]>(menus);
  const [statusMessage, setStatusMessage] = useState<string>('');
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [showAddForm, setShowAddForm] = useState<boolean>(false);
  const [editingItem, setEditingItem] = useState<NavigationMenuItem | null>(null);

  // Page content state inside the Edit Menu & Page Modal
  const [editPageTitle, setEditPageTitle] = useState<string>('');
  const [editPageSlug, setEditPageSlug] = useState<string>('');
  const [editPageSummary, setEditPageSummary] = useState<string>('');
  const [editPageContent, setEditPageContent] = useState<string>('');

  // New Menu + Page Form state
  const [newLocation, setNewLocation] = useState<'header' | 'footer'>('header');
  const [newTitle, setNewTitle] = useState<string>('');
  const [newUrl, setNewUrl] = useState<string>('');
  const [newSortOrder, setNewSortOrder] = useState<number>(8);
  const [newIsActive, setNewIsActive] = useState<boolean>(true);
  const [newTarget, setNewTarget] = useState<'_self' | '_blank'>('_self');
  const [newPageTitle, setNewPageTitle] = useState<string>('');
  const [newPageSlug, setNewPageSlug] = useState<string>('');
  const [newPageSummary, setNewPageSummary] = useState<string>('');
  const [newPageContent, setNewPageContent] = useState<string>('');

  useEffect(() => {
    setDraftMenus(menus);
  }, [menus]);

  const showToast = (msg: string, isError = false) => {
    if (isError) {
      setErrorMessage(msg);
      setStatusMessage('');
    } else {
      setStatusMessage(msg);
      setErrorMessage('');
    }
    setTimeout(() => {
      setStatusMessage('');
      setErrorMessage('');
    }, 4500);
  };

  const sanitizeUrl = (raw: string): string => {
    const trimmed = raw.trim();
    if (/^\s*(javascript|data|vbscript)\s*:/i.test(trimmed)) {
      return '';
    }
    return trimmed;
  };

  const getLinkedPage = (url: string): StaticPageItem | undefined => {
    const slug = resolveUrlToPageSlug(url);
    if (!slug) return undefined;
    return pages.find(p => p.slug === slug);
  };

  const handleOpenEditModal = (item: NavigationMenuItem) => {
    setEditingItem({ ...item });
    const slug = resolveUrlToPageSlug(item.url);
    const linked = pages.find(p => p.slug === slug);
    if (linked) {
      setEditPageTitle(linked.title);
      setEditPageSlug(linked.slug);
      setEditPageSummary(linked.summary);
      setEditPageContent(linked.content);
    } else {
      setEditPageTitle(item.title);
      setEditPageSlug(slug || item.title.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
      setEditPageSummary(`Informasi resmi mengenai ${item.title} PT. Kreatif Global Solusindo.`);
      setEditPageContent(`Tuliskan isi konten halaman ${item.title} di sini...`);
    }
  };

  const headerMenus = [...draftMenus]
    .filter(m => m.location === 'header')
    .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id);

  const footerMenus = [...draftMenus]
    .filter(m => m.location === 'footer')
    .sort((a, b) => a.sortOrder - b.sortOrder || a.id - b.id);

  const handleAddMenu = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanTitle = newTitle.trim();
    const computedSlug = (newPageSlug.trim() || cleanTitle)
      .toLowerCase()
      .replace(/[^a-z0-9-]+/g, '-')
      .replace(/^-|-$/g, '');
    const rawTargetUrl = newUrl.trim() || resolvePageSlugToUrl(computedSlug);
    const cleanUrl = sanitizeUrl(rawTargetUrl);

    if (!cleanTitle || !cleanUrl) {
      showToast('Label Menu dan Target URL/Slug yang aman wajib diisi.', true);
      return;
    }

    const nextId = draftMenus.reduce((max, m) => Math.max(max, m.id), 0) + 1;
    const newItem: NavigationMenuItem = {
      id: nextId,
      title: cleanTitle,
      url: cleanUrl,
      location: newLocation,
      sortOrder: Number(newSortOrder) || 1,
      target: newTarget,
      isActive: newIsActive
    };

    const updatedMenus = [...draftMenus, newItem];
    setDraftMenus(updatedMenus);
    onUpdateMenus(updatedMenus);

    // Simpan atau perbarui juga halaman terkait di daftar pages
    if (onUpdatePages) {
      const targetSlug = resolveUrlToPageSlug(cleanUrl) || computedSlug;
      if (targetSlug) {
        const finalPageTitle = newPageTitle.trim() || cleanTitle;
        const finalPageSummary =
          newPageSummary.trim() ||
          `Informasi resmi mengenai ${finalPageTitle} PT. Kreatif Global Solusindo.`;
        const finalPageContent =
          newPageContent.trim() ||
          `${finalPageTitle}\n\n${finalPageSummary}`;
        const nowStr = new Date().toISOString().replace('T', ' ').slice(0, 16);
        const existingIdx = pages.findIndex(p => p.slug === targetSlug);

        if (existingIdx >= 0) {
          if (newPageTitle.trim() || newPageSummary.trim() || newPageContent.trim()) {
            const nextPages = pages.map((p, idx) =>
              idx === existingIdx
                ? {
                    ...p,
                    title: finalPageTitle,
                    summary: finalPageSummary,
                    content: finalPageContent,
                    updatedAt: nowStr
                  }
                : p
            );
            onUpdatePages(nextPages);
          }
        } else {
          const newPageItem: StaticPageItem = {
            id: `page-${Date.now()}`,
            title: finalPageTitle,
            slug: targetSlug,
            summary: finalPageSummary,
            content: finalPageContent,
            isPublished: true,
            sortOrder: pages.length + 1,
            updatedAt: nowStr
          };
          onUpdatePages([...pages, newPageItem]);
        }
      }
    }

    setNewTitle('');
    setNewUrl('');
    setNewPageTitle('');
    setNewPageSlug('');
    setNewPageSummary('');
    setNewPageContent('');
    setNewSortOrder(
      updatedMenus.filter(m => m.location === newLocation).length + 1
    );
    setShowAddForm(false);
    showToast(
      `Menu "${cleanTitle}" beserta isi konten halamannya berhasil ditambahkan ke Navigasi ${
        newLocation === 'header' ? 'Header (Atas)' : 'Footer (Bawah)'
      }.`
    );
  };

  const handleSaveEditModal = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingItem) return;
    const cleanTitle = editingItem.title.trim();
    const cleanUrl = sanitizeUrl(editingItem.url);

    if (!cleanTitle || !cleanUrl) {
      showToast('Label Menu dan Target URL tidak boleh kosong atau berbahaya.', true);
      return;
    }

    const updated = draftMenus.map(m =>
      m.id === editingItem.id
        ? { ...editingItem, title: cleanTitle, url: cleanUrl }
        : m
    );
    setDraftMenus(updated);
    onUpdateMenus(updated);

    // Simpan perubahan isi halaman website terkait
    if (onUpdatePages) {
      const targetSlug =
        editPageSlug.trim() || resolveUrlToPageSlug(cleanUrl) || cleanTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-');
      const nowStr = new Date().toISOString().replace('T', ' ').slice(0, 16);
      const existing = pages.find(p => p.slug === targetSlug);

      if (existing) {
        const updatedPages = pages.map(p =>
          p.slug === targetSlug
            ? {
                ...p,
                title: editPageTitle.trim() || cleanTitle,
                summary: editPageSummary.trim(),
                content: editPageContent.trim(),
                updatedAt: nowStr
              }
            : p
        );
        onUpdatePages(updatedPages);
      } else {
        const newPage: StaticPageItem = {
          id: `page-${Date.now()}`,
          title: editPageTitle.trim() || cleanTitle,
          slug: targetSlug,
          summary: editPageSummary.trim(),
          content: editPageContent.trim(),
          isPublished: true,
          sortOrder: pages.length + 1,
          updatedAt: nowStr
        };
        onUpdatePages([...pages, newPage]);
      }
    }

    setEditingItem(null);
    showToast(`Perubahan menu "${cleanTitle}" dan isi konten halamannya berhasil disimpan ke website!`);
  };

  const handleInlineChange = (
    id: number,
    field: keyof NavigationMenuItem,
    value: string | number | boolean
  ) => {
    setDraftMenus(prev =>
      prev.map(m => (m.id === id ? { ...m, [field]: value } : m))
    );
  };

  const handleSaveAllChanges = () => {
    for (const item of draftMenus) {
      if (!item.title.trim() || !sanitizeUrl(item.url)) {
        showToast(
          `Terdapat Label Menu atau URL kosong/tidak valid pada baris #${item.id}.`,
          true
        );
        return;
      }
    }
    const cleaned = draftMenus.map(m => ({
      ...m,
      title: m.title.trim(),
      url: sanitizeUrl(m.url),
      sortOrder: Number(m.sortOrder) || 1
    }));
    setDraftMenus(cleaned);
    onUpdateMenus(cleaned);
    showToast('Seluruh perubahan navigasi Header & Footer berhasil disimpan dan diterapkan ke website publik!');
  };

  const handleToggleActive = (id: number) => {
    const target = draftMenus.find(m => m.id === id);
    if (!target) return;
    const nextState = !target.isActive;
    const updated = draftMenus.map(m =>
      m.id === id ? { ...m, isActive: nextState } : m
    );
    setDraftMenus(updated);
    onUpdateMenus(updated);
    showToast(
      `Status menu "${target.title}" diubah menjadi ${
        nextState ? 'Aktif' : 'Nonaktif'
      }.`
    );
  };

  const handleMoveOrder = (id: number, direction: 'up' | 'down') => {
    const item = draftMenus.find(m => m.id === id);
    if (!item) return;
    const list = item.location === 'header' ? headerMenus : footerMenus;
    const idx = list.findIndex(m => m.id === id);
    const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
    if (swapIdx < 0 || swapIdx >= list.length) return;

    const neighbor = list[swapIdx];
    let orderA = neighbor.sortOrder;
    let orderB = item.sortOrder;
    if (orderA === orderB) {
      orderA = direction === 'up' ? Math.max(1, orderB - 1) : orderB + 1;
    }

    const updated = draftMenus.map(m => {
      if (m.id === item.id) return { ...m, sortOrder: orderA };
      if (m.id === neighbor.id) return { ...m, sortOrder: orderB };
      return m;
    });

    setDraftMenus(updated);
    onUpdateMenus(updated);
    showToast(`Urutan menu "${item.title}" berhasil digeser.`);
  };

  const handleDeleteMenu = (id: number) => {
    const target = draftMenus.find(m => m.id === id);
    if (!target) return;
    const updated = draftMenus.filter(m => m.id !== id);
    setDraftMenus(updated);
    onUpdateMenus(updated);
    showToast(`Menu "${target.title}" berhasil dihapus dari navigasi.`);
  };

  const handleResetDefault = () => {
    setDraftMenus(INITIAL_NAVIGATION_MENUS);
    onUpdateMenus(INITIAL_NAVIGATION_MENUS);
    showToast(
      'Data navigasi berhasil dikembalikan ke pengaturan awal (7 Menu Header & 5 Menu Footer).'
    );
  };

  const renderMenuTable = (
    title: string,
    subtitle: string,
    location: 'header' | 'footer',
    items: NavigationMenuItem[]
  ) => {
    const activeCount = items.filter(m => m.isActive).length;
    return (
      <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div className="p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <div className="flex items-center gap-2">
              <span
                className={`w-2.5 h-2.5 rounded-full ${
                  location === 'header' ? 'bg-blue-600' : 'bg-indigo-600'
                }`}
              />
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">
                {title}
              </h3>
              <span className="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-blue-100 text-blue-800">
                {activeCount} Aktif / {items.length} Menu
              </span>
            </div>
            <p className="text-xs text-slate-500 mt-1">{subtitle}</p>
          </div>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => {
                setNewLocation(location);
                setNewSortOrder(items.length + 1);
                setShowAddForm(true);
              }}
              className="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Tambah Menu & Halaman {location === 'header' ? 'Header' : 'Footer'}</span>
            </button>
            <button
              type="button"
              onClick={handleSaveAllChanges}
              className="px-4 py-2 bg-slate-900 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
            >
              <Save className="w-3.5 h-3.5" />
              <span>Simpan Perubahan</span>
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-100/80 uppercase text-[10px] text-slate-500 border-b border-slate-200">
              <tr>
                <th className="p-3.5 w-20 text-center">Urutan</th>
                <th className="p-3.5 w-44">Label Menu</th>
                <th className="p-3.5 w-52">Target URL</th>
                <th className="p-3.5">Isi Konten Halaman Website (Judul & Ringkasan)</th>
                <th className="p-3.5 w-28 text-center">Status</th>
                <th className="p-3.5 w-64 text-right">Aksi (Menu & Isi Halaman)</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {items.length === 0 ? (
                <tr>
                  <td colSpan={6} className="p-8 text-center text-slate-400">
                    Belum ada menu pada bagian ini. Klik tombol{' '}
                    <strong>+ Tambah Menu & Halaman</strong> untuk menambahkan.
                  </td>
                </tr>
              ) : (
                items.map((item, idx) => {
                  const linkedPage = getLinkedPage(item.url);
                  return (
                    <tr
                      key={item.id}
                      className={`hover:bg-slate-50/90 transition ${
                        !item.isActive ? 'bg-slate-50/50 opacity-75' : ''
                      }`}
                    >
                      {/* Urutan */}
                      <td className="p-3.5 text-center">
                        <input
                          type="number"
                          min={1}
                          value={item.sortOrder}
                          onChange={e =>
                            handleInlineChange(
                              item.id,
                              'sortOrder',
                              parseInt(e.target.value, 10) || 1
                            )
                          }
                          className="w-16 text-center font-mono font-bold text-slate-900 border border-slate-300 rounded-lg py-1.5 px-2 bg-white focus:border-blue-600 focus:outline-none"
                        />
                      </td>

                      {/* Label Menu */}
                      <td className="p-3.5">
                        <input
                          type="text"
                          value={item.title}
                          onChange={e =>
                            handleInlineChange(item.id, 'title', e.target.value)
                          }
                          className="w-full font-bold text-slate-900 border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white focus:border-blue-600 focus:outline-none"
                        />
                      </td>

                      {/* Target URL */}
                      <td className="p-3.5">
                        <input
                          type="text"
                          value={item.url}
                          onChange={e =>
                            handleInlineChange(item.id, 'url', e.target.value)
                          }
                          className="w-full font-mono text-[11px] text-blue-700 border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white focus:border-blue-600 focus:outline-none"
                        />
                      </td>

                      {/* Isi Konten Halaman Website Terkait */}
                      <td className="p-3.5">
                        {linkedPage ? (
                          <div>
                            <div className="font-bold text-slate-800 line-clamp-1">
                              {linkedPage.title}
                            </div>
                            <div className="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                              {linkedPage.summary}
                            </div>
                          </div>
                        ) : (
                          <span className="text-[11px] text-slate-400 italic">
                            Klik "Edit Isi Halaman" untuk mengisi konten halaman
                          </span>
                        )}
                      </td>

                      {/* Status */}
                      <td className="p-3.5 text-center">
                        <button
                          type="button"
                          onClick={() => handleToggleActive(item.id)}
                          className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold border transition cursor-pointer ${
                            item.isActive
                              ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                              : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200'
                          }`}
                        >
                          <Power className="w-3 h-3" />
                          <span>{item.isActive ? 'Aktif' : 'Nonaktif'}</span>
                        </button>
                      </td>

                      {/* Aksi */}
                      <td className="p-3.5 text-right whitespace-nowrap">
                        <div className="inline-flex items-center justify-end gap-1">
                          <button
                            type="button"
                            disabled={idx === 0}
                            onClick={() => handleMoveOrder(item.id, 'up')}
                            title="Geser Urutan Naik"
                            className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 disabled:opacity-30 cursor-pointer"
                          >
                            <ArrowUp className="w-3.5 h-3.5" />
                          </button>
                          <button
                            type="button"
                            disabled={idx === items.length - 1}
                            onClick={() => handleMoveOrder(item.id, 'down')}
                            title="Geser Urutan Turun"
                            className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 disabled:opacity-30 cursor-pointer"
                          >
                            <ArrowDown className="w-3.5 h-3.5" />
                          </button>
                          <button
                            type="button"
                            onClick={() => handleOpenEditModal(item)}
                            className="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] inline-flex items-center gap-1 cursor-pointer shadow-2xs"
                          >
                            <Edit3 className="w-3 h-3" />
                            <span>Edit Isi Halaman</span>
                          </button>
                          <button
                            type="button"
                            onClick={() => handleDeleteMenu(item.id)}
                            className="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] inline-flex items-center gap-1 cursor-pointer"
                          >
                            <Trash2 className="w-3 h-3" />
                            <span>Hapus</span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        <div className="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
          <span className="text-slate-500">
            Klik <strong>Edit Isi Halaman</strong> pada baris mana pun untuk mengubah Judul H1, Ringkasan Hero, dan Isi Konten Lengkap halaman tersebut.
          </span>
          <button
            type="button"
            onClick={handleSaveAllChanges}
            className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
          >
            <Save className="w-3.5 h-3.5" />
            <span>
              Simpan Perubahan {location === 'header' ? 'Header' : 'Footer'}
            </span>
          </button>
        </div>
      </div>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header Banner */}
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
          <div className="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-blue-600 mb-1">
            <Compass className="w-3.5 h-3.5" />
            <span>CMS Navigation & Page Content Manager</span>
            <span>·</span>
            <span>Edit Menu, Slug, Posisi & Isi Halaman</span>
          </div>
          <h2 className="text-xl font-bold text-slate-900">
            Pengaturan Navigasi (Header & Footer) & Isi Konten Halaman
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Setiap halaman di Navigasi Header (Atas) maupun Navigasi Footer (Bawah) dapat ditambah dan diedit langsung <strong>isi konten halamannya</strong> sesuai tampilan di website, bukan hanya nama menu, slug, dan posisi urutannya saja.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2.5">
          <button
            type="button"
            onClick={() => setShowAddForm(prev => !prev)}
            className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>+ Tambah Menu & Halaman Baru</span>
          </button>
          <button
            type="button"
            onClick={handleSaveAllChanges}
            className="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer"
          >
            <Save className="w-4 h-4" />
            <span>Simpan Perubahan</span>
          </button>
          <button
            type="button"
            onClick={handleResetDefault}
            className="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition inline-flex items-center gap-1.5 cursor-pointer"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Pulihkan Data Awal</span>
          </button>
        </div>
      </div>

      {/* Toast Feedback */}
      {statusMessage && (
        <div className="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-4 rounded-xl font-semibold flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-600" />
            <span>{statusMessage}</span>
          </div>
          <button
            type="button"
            onClick={() => setStatusMessage('')}
            className="text-emerald-700 hover:text-emerald-900 font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>
      )}

      {errorMessage && (
        <div className="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-4 rounded-xl font-semibold flex items-center justify-between">
          <span>⚠️ {errorMessage}</span>
          <button
            type="button"
            onClick={() => setErrorMessage('')}
            className="text-rose-700 hover:text-rose-900 font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>
      )}

      {/* Filter Tabs */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="inline-flex bg-white p-1.5 rounded-xl border border-slate-200 shadow-xs text-xs font-bold">
          <button
            type="button"
            onClick={() => setActiveTab('all')}
            className={`px-4 py-2 rounded-lg transition cursor-pointer ${
              activeTab === 'all'
                ? 'bg-slate-900 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            Semua Bagian ({headerMenus.length + footerMenus.length})
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('header')}
            className={`px-4 py-2 rounded-lg transition cursor-pointer ${
              activeTab === 'header'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            A. Navigasi Header (Atas) (
            {headerMenus.filter(m => m.isActive).length}/{headerMenus.length})
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('footer')}
            className={`px-4 py-2 rounded-lg transition cursor-pointer ${
              activeTab === 'footer'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            B. Navigasi Footer (Bawah) (
            {footerMenus.filter(m => m.isActive).length}/{footerMenus.length})
          </button>
        </div>

        <div className="text-xs text-slate-500">
          Klik tombol <strong>Edit Isi Halaman</strong> untuk mengubah teks judul, subjudul, dan konten lengkap halaman website.
        </div>
      </div>

      {/* Form Tambah Menu & Halaman Baru */}
      {showAddForm && (
        <div className="bg-white rounded-2xl border-2 border-blue-200 p-6 shadow-sm space-y-4 text-xs">
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">
                Tambah Navigasi & Halaman Website Baru
              </span>
              <h3 className="text-sm font-bold text-slate-900">
                Tambahkan Menu ke Header / Footer Beserta Isi Konten Halamannya
              </h3>
            </div>
            <button
              type="button"
              onClick={() => setShowAddForm(false)}
              className="text-slate-400 hover:text-slate-700 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <form onSubmit={handleAddMenu} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Lokasi Menu *
                </label>
                <select
                  value={newLocation}
                  onChange={e =>
                    setNewLocation(e.target.value as 'header' | 'footer')
                  }
                  className="w-full border border-slate-300 rounded-lg p-2.5 bg-slate-50 font-semibold"
                >
                  <option value="header">Header (Atas)</option>
                  <option value="footer">Footer (Bawah)</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Urutan Tampil *
                </label>
                <input
                  type="number"
                  min={1}
                  required
                  value={newSortOrder}
                  onChange={e =>
                    setNewSortOrder(parseInt(e.target.value, 10) || 1)
                  }
                  className="w-full border border-slate-300 rounded-lg p-2.5 font-mono"
                />
              </div>

              <div className="lg:col-span-2">
                <label className="block font-semibold text-slate-700 mb-1">
                  Label Menu *
                </label>
                <input
                  type="text"
                  required
                  placeholder="Contoh: Standar K3 & Keselamatan"
                  value={newTitle}
                  onChange={e => setNewTitle(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Target URL / Slug
                </label>
                <input
                  type="text"
                  placeholder="Otomatis dari slug halaman"
                  value={newUrl}
                  onChange={e => setNewUrl(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 font-mono"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Status *
                </label>
                <select
                  value={newIsActive ? '1' : '0'}
                  onChange={e => setNewIsActive(e.target.value === '1')}
                  className="w-full border border-slate-300 rounded-lg p-2.5 bg-slate-50 font-semibold"
                >
                  <option value="1">Aktif (Tampil)</option>
                  <option value="0">Nonaktif (Sembunyi)</option>
                </select>
              </div>
            </div>

            {/* Isi Konten Halaman Baru */}
            <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
              <div className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-blue-700">
                <FileText className="w-3.5 h-3.5" />
                <span>Isi Konten Halaman Website (Dapat Diedit Sesuai Tampilan Halaman)</span>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">
                    Judul Utama Halaman (Hero Title / H1)
                  </label>
                  <input
                    type="text"
                    placeholder="Kosongkan jika sama dengan Label Menu"
                    value={newPageTitle}
                    onChange={e => setNewPageTitle(e.target.value)}
                    className="w-full border border-slate-300 rounded-lg p-2 bg-white font-semibold"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">
                    Slug Halaman (contoh: standar-k3)
                  </label>
                  <input
                    type="text"
                    placeholder="Otomatis dibuatkan page.php?slug=..."
                    value={newPageSlug}
                    onChange={e => setNewPageSlug(e.target.value)}
                    className="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Ringkasan / Subjudul Halaman (Tampil di Bagian Atas Halaman)
                </label>
                <textarea
                  rows={2}
                  placeholder="Tuliskan ringkasan atau subjudul halaman..."
                  value={newPageSummary}
                  onChange={e => setNewPageSummary(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2 bg-white"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">
                  Isi Konten Lengkap Halaman
                </label>
                <textarea
                  rows={6}
                  placeholder="Tuliskan isi konten lengkap halaman website di sini..."
                  value={newPageContent}
                  onChange={e => setNewPageContent(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono leading-relaxed"
                />
              </div>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
              <div className="flex flex-wrap items-center gap-1.5">
                <span className="text-[11px] text-slate-500 font-semibold">
                  Pilih Cepat URL Halaman:
                </span>
                {[
                  { label: 'Beranda', url: 'index.php' },
                  { label: 'Tentang Kami', url: 'tentang.php' },
                  { label: 'Layanan', url: 'layanan.php' },
                  { label: 'Harga', url: 'harga.php' },
                  { label: 'FAQ', url: 'faq.php' },
                  { label: 'Portofolio', url: 'portofolio.php' },
                  { label: 'Kontak', url: 'kontak.php' },
                  { label: 'Artikel Edukasi', url: 'blog.php' },
                  { label: 'Kebijakan Privasi', url: 'kebijakan-privasi.php' }
                ].map(q => (
                  <button
                    key={q.url}
                    type="button"
                    onClick={() => {
                      setNewUrl(q.url);
                      if (!newTitle) setNewTitle(q.label);
                    }}
                    className="px-2 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-600 rounded text-[10px] font-mono border border-slate-200 transition cursor-pointer"
                  >
                    {q.label} ({q.url})
                  </button>
                ))}
              </div>

              <div className="flex items-center gap-2 shrink-0">
                <button
                  type="button"
                  onClick={() => setShowAddForm(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 font-semibold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer"
                >
                  + Simpan Menu & Isi Halaman
                </button>
              </div>
            </div>
          </form>
        </div>
      )}

      {/* Modal Edit Menu & Isi Konten Halaman Lengkap */}
      {editingItem && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl max-h-[92vh] overflow-y-auto space-y-5 text-xs">
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">
                  Editor Terpadu: Navigasi & Isi Konten Halaman Website
                </span>
                <h3 className="text-base font-bold text-slate-900">
                  Edit Menu & Isi Halaman: {editingItem.title} ({editingItem.url})
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setEditingItem(null)}
                className="text-slate-400 hover:text-slate-700 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveEditModal} className="space-y-5">
              {/* Bagian 1: Pengaturan Navigasi Menu */}
              <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                <div className="text-[11px] font-bold uppercase tracking-wider text-blue-700">
                  1. Pengaturan Posisi, Label Menu & Target URL
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Lokasi Navigasi *
                    </label>
                    <select
                      value={editingItem.location}
                      onChange={e =>
                        setEditingItem({
                          ...editingItem,
                          location: e.target.value as 'header' | 'footer'
                        })
                      }
                      className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-semibold"
                    >
                      <option value="header">Header (Atas)</option>
                      <option value="footer">Footer (Bawah)</option>
                    </select>
                  </div>

                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Urutan Tampil *
                    </label>
                    <input
                      type="number"
                      min={1}
                      required
                      value={editingItem.sortOrder}
                      onChange={e =>
                        setEditingItem({
                          ...editingItem,
                          sortOrder: parseInt(e.target.value, 10) || 1
                        })
                      }
                      className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Status Tampil *
                    </label>
                    <select
                      value={editingItem.isActive ? '1' : '0'}
                      onChange={e =>
                        setEditingItem({
                          ...editingItem,
                          isActive: e.target.value === '1'
                        })
                      }
                      className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-semibold"
                    >
                      <option value="1">Aktif (Tampil di Publik)</option>
                      <option value="0">Nonaktif (Disembunyikan)</option>
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Label Menu *
                    </label>
                    <input
                      type="text"
                      required
                      value={editingItem.title}
                      onChange={e =>
                        setEditingItem({ ...editingItem, title: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-slate-700 mb-1">
                      Target URL *
                    </label>
                    <input
                      type="text"
                      required
                      value={editingItem.url}
                      onChange={e =>
                        setEditingItem({ ...editingItem, url: e.target.value })
                      }
                      className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-mono"
                    />
                  </div>
                </div>
              </div>

              {/* Bagian 2: Edit Isi Konten Halaman Website */}
              <div className="bg-blue-50/50 p-4 rounded-xl border border-blue-200 space-y-3">
                <div className="flex items-center justify-between">
                  <div className="text-[11px] font-bold uppercase tracking-wider text-blue-800">
                    2. Edit Isi Konten Halaman Website (Slug: {editPageSlug})
                  </div>
                  <span className="text-[11px] text-slate-500">
                    Perubahan langsung tampil pada halaman website
                  </span>
                </div>

                <div>
                  <label className="block font-semibold text-slate-700 mb-1">
                    Judul Utama Halaman di Website (Hero Title / H1) *
                  </label>
                  <input
                    type="text"
                    required
                    value={editPageTitle}
                    onChange={e => setEditPageTitle(e.target.value)}
                    className="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold text-slate-900"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-slate-700 mb-1">
                    Ringkasan / Subjudul Halaman (Tampil di Bawah Judul Utama) *
                  </label>
                  <textarea
                    rows={2}
                    required
                    value={editPageSummary}
                    onChange={e => setEditPageSummary(e.target.value)}
                    className="w-full border border-slate-300 rounded-lg p-2.5 bg-white"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-slate-700 mb-1">
                    Isi Konten Lengkap Halaman Website *
                  </label>
                  <textarea
                    rows={9}
                    required
                    value={editPageContent}
                    onChange={e => setEditPageContent(e.target.value)}
                    className="w-full border border-slate-300 rounded-lg p-3 bg-white font-mono leading-relaxed"
                  />
                </div>
              </div>

              <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setEditingItem(null)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 font-semibold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer"
                >
                  Simpan Menu & Isi Halaman
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Tabel Navigasi Header & Footer */}
      <div className="space-y-8">
        {(activeTab === 'all' || activeTab === 'header') &&
          renderMenuTable(
            'A. Navigasi Header (Atas)',
            'Ditampilkan pada bilah navigasi utama bagian atas (Desktop & Mobile Drawer) di seluruh halaman website.',
            'header',
            headerMenus
          )}

        {(activeTab === 'all' || activeTab === 'footer') &&
          renderMenuTable(
            'B. Navigasi Footer (Bawah)',
            'Ditampilkan pada kolom tautan informasi & legalitas di bagian bawah (Footer) di seluruh halaman website.',
            'footer',
            footerMenus
          )}
      </div>

      {/* Pratinjau Langsung Navigasi Header & Footer */}
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
          <div>
            <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">
              Pratinjau Langsung (Live Preview)
            </span>
            <h3 className="text-base font-bold text-slate-900">
              Simulasi Tampilan Navigasi Header & Footer
            </h3>
            <p className="text-xs text-slate-500">
              Membedakan menu berstatus <strong>Aktif</strong> (tampil di publik) dan{' '}
              <strong>Nonaktif</strong> (disembunyikan dari pengunjung).
            </p>
          </div>
          <div className="flex items-center gap-3 text-xs">
            <span className="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500" /> Menu Aktif
            </span>
            <span className="inline-flex items-center gap-1.5 font-semibold text-slate-400">
              <span className="w-2.5 h-2.5 rounded-full bg-slate-300" /> Menu Nonaktif
            </span>
          </div>
        </div>

        {/* Preview Header */}
        <div className="space-y-2">
          <div className="text-xs font-bold text-slate-700 uppercase tracking-wider">
            1. Pratinjau Navigasi Header (Atas)
          </div>
          <div className="bg-slate-900 text-white p-4 rounded-xl flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-black text-xs">
                KGS
              </div>
              <div>
                <div className="text-xs font-bold leading-none">
                  PT. KREATIF GLOBAL SOLUSINDO
                </div>
                <div className="text-[10px] text-slate-400 mt-0.5">
                  Header Navigation Bar
                </div>
              </div>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              {headerMenus.map(hm => (
                <div
                  key={hm.id}
                  className={`px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1.5 ${
                    hm.isActive
                      ? 'bg-slate-800 text-white border border-slate-700'
                      : 'bg-slate-950/60 text-slate-500 line-through border border-dashed border-slate-800'
                  }`}
                >
                  <span className="text-[10px] font-mono text-blue-400">
                    #{hm.sortOrder}
                  </span>
                  <span>{hm.title}</span>
                  <span className="text-[10px] font-mono text-slate-400">
                    ({hm.url})
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Preview Footer */}
        <div className="space-y-2">
          <div className="text-xs font-bold text-slate-700 uppercase tracking-wider">
            2. Pratinjau Navigasi Footer (Bawah)
          </div>
          <div className="bg-slate-950 text-slate-300 p-5 rounded-xl border border-slate-800">
            <div className="text-xs font-bold text-white uppercase tracking-wider mb-3">
              Tautan Informasi & Legalitas (Footer)
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
              {footerMenus.map(fm => (
                <div
                  key={fm.id}
                  className={`p-2.5 rounded-lg border text-xs flex flex-col justify-between ${
                    fm.isActive
                      ? 'bg-slate-900 border-slate-800 text-slate-200'
                      : 'bg-slate-950 border-dashed border-slate-800/70 text-slate-600 line-through'
                  }`}
                >
                  <div className="flex items-center justify-between gap-1 mb-1">
                    <span className="font-bold">{fm.title}</span>
                    <span
                      className={`text-[10px] font-mono px-1.5 py-0.5 rounded ${
                        fm.isActive
                          ? 'bg-emerald-950 text-emerald-400'
                          : 'bg-slate-900 text-slate-500'
                      }`}
                    >
                      {fm.isActive ? 'Aktif' : 'Nonaktif'}
                    </span>
                  </div>
                  <div className="text-[10px] font-mono text-slate-400 truncate">
                    Urutan #{fm.sortOrder} · {fm.url}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
