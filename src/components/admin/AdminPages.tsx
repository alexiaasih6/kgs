import React, { useState } from 'react';
import {
  StaticPageItem,
  NavigationMenuItem,
  resolvePageSlugToUrl
} from '../../data/companyData';
import { FileText, Edit3, Plus, Trash2, X, Check } from 'lucide-react';

interface AdminPagesProps {
  pages: StaticPageItem[];
  onUpdatePages: (pages: StaticPageItem[]) => void;
  menus?: NavigationMenuItem[];
  onUpdateMenus?: (menus: NavigationMenuItem[]) => void;
}

const CORE_SLUGS = [
  'beranda',
  'tentang-kami',
  'layanan',
  'harga',
  'faq',
  'portofolio',
  'kontak',
  'profil-perusahaan',
  'visi-misi-budaya',
  'blog',
  'kebijakan-privasi'
];

export const AdminPages: React.FC<AdminPagesProps> = ({
  pages,
  onUpdatePages,
  menus = [],
  onUpdateMenus
}) => {
  const [editingPage, setEditingPage] = useState<StaticPageItem | null>(null);
  const [formData, setFormData] = useState<Partial<StaticPageItem>>({});
  const [isAdding, setIsAdding] = useState(false);
  const [statusMessage, setStatusMessage] = useState('');

  // Add New Page state
  const [newTitle, setNewTitle] = useState('');
  const [newSlug, setNewSlug] = useState('');
  const [newSummary, setNewSummary] = useState('');
  const [newContent, setNewContent] = useState('');
  const [newNavLocation, setNewNavLocation] = useState<'none' | 'header' | 'footer'>('footer');
  const [newNavLabel, setNewNavLabel] = useState('');
  const [newNavOrder, setNewNavOrder] = useState<number>(6);

  const showToast = (msg: string) => {
    setStatusMessage(msg);
    setTimeout(() => setStatusMessage(''), 4000);
  };

  const handleOpenEdit = (page: StaticPageItem) => {
    setEditingPage(page);
    setFormData(page);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPage || !formData.title || !formData.content) return;

    const updated = pages.map(p =>
      p.id === editingPage.id
        ? ({
            ...p,
            ...formData,
            updatedAt: new Date().toISOString().replace('T', ' ').slice(0, 16)
          } as StaticPageItem)
        : p
    );

    onUpdatePages(updated);
    setEditingPage(null);
    showToast(`Konten halaman "${formData.title}" berhasil diperbarui di website!`);
  };

  const handleAddPage = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanTitle = newTitle.trim();
    const cleanContent = newContent.trim();
    if (!cleanTitle || !cleanContent) return;

    const slug = (newSlug.trim() || cleanTitle)
      .toLowerCase()
      .replace(/[^a-z0-9-]+/g, '-')
      .replace(/^-|-$/g, '');

    const nowStr = new Date().toISOString().replace('T', ' ').slice(0, 16);
    const newPage: StaticPageItem = {
      id: `page-${Date.now()}`,
      title: cleanTitle,
      slug,
      summary:
        newSummary.trim() ||
        `Informasi resmi mengenai ${cleanTitle} PT. Kreatif Global Solusindo.`,
      content: cleanContent,
      isPublished: true,
      sortOrder: pages.length + 1,
      updatedAt: nowStr
    };

    onUpdatePages([...pages, newPage]);

    if (newNavLocation !== 'none' && onUpdateMenus) {
      const nextMenuId = menus.reduce((max, m) => Math.max(max, m.id), 0) + 1;
      const targetUrl = resolvePageSlugToUrl(slug);
      const newMenu: NavigationMenuItem = {
        id: nextMenuId,
        title: newNavLabel.trim() || cleanTitle,
        url: targetUrl,
        location: newNavLocation,
        sortOrder: Number(newNavOrder) || 1,
        target: '_self',
        isActive: true
      };
      onUpdateMenus([...menus, newMenu]);
    }

    setNewTitle('');
    setNewSlug('');
    setNewSummary('');
    setNewContent('');
    setNewNavLabel('');
    setIsAdding(false);
    showToast(
      `Halaman "${cleanTitle}" berhasil dibuat${
        newNavLocation !== 'none'
          ? ` dan ditambahkan ke Navigasi ${
              newNavLocation === 'header' ? 'Header (Atas)' : 'Footer (Bawah)'
            }`
          : ''
      }!`
    );
  };

  const handleDeletePage = (page: StaticPageItem) => {
    const updated = pages.filter(p => p.id !== page.id);
    onUpdatePages(updated);
    showToast(`Halaman "${page.title}" berhasil dihapus.`);
  };

  const sortedPages = [...pages].sort((a, b) => a.sortOrder - b.sortOrder);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
          <div className="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-blue-600 mb-1">
            <FileText className="w-3.5 h-3.5" />
            <span>CMS Page & Content Editor</span>
            <span>·</span>
            <span>Seluruh Halaman Header & Footer</span>
          </div>
          <h2 className="text-xl font-bold text-slate-900">
            Kelola Seluruh Halaman & Isi Konten Website
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Tambah halaman baru atau edit judul utama (H1), subjudul hero, dan isi konten lengkap setiap halaman yang ada di Navigasi Header (Atas) maupun Navigasi Footer (Bawah).
          </p>
        </div>
        <button
          type="button"
          onClick={() => setIsAdding(true)}
          className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>+ Tambah Halaman Baru</span>
        </button>
      </div>

      {statusMessage && (
        <div className="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-4 rounded-xl font-semibold flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-600" />
            <span>{statusMessage}</span>
          </div>
          <button
            type="button"
            onClick={() => setStatusMessage('')}
            className="text-emerald-700 font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>
      )}

      <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-50 uppercase text-[10px] text-slate-500 border-b">
              <tr>
                <th className="p-3.5 w-14 text-center">Urutan</th>
                <th className="p-3.5">Judul Halaman & Target URL</th>
                <th className="p-3.5">Ringkasan & Cuplikan Isi Halaman</th>
                <th className="p-3.5 text-center">Status</th>
                <th className="p-3.5 text-center">Terakhir Diperbarui</th>
                <th className="p-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {sortedPages.map(p => {
                const publicUrl = resolvePageSlugToUrl(p.slug);
                const isCore = CORE_SLUGS.includes(p.slug);
                return (
                  <tr key={p.id} className="hover:bg-slate-50">
                    <td className="p-3.5 text-center font-mono font-bold text-slate-500">
                      {p.sortOrder}
                    </td>
                    <td className="p-3.5">
                      <div className="font-bold text-slate-900">{p.title}</div>
                      <div className="flex items-center gap-2 mt-0.5">
                        <span className="font-mono text-[11px] text-blue-600">
                          {publicUrl}
                        </span>
                        <span className="text-[10px] text-slate-400">
                          · slug: {p.slug}
                        </span>
                      </div>
                    </td>
                    <td className="p-3.5 text-slate-600 max-w-md">
                      <div className="font-medium text-slate-800 line-clamp-1">
                        {p.summary}
                      </div>
                      <div className="text-[11px] text-slate-400 truncate mt-0.5">
                        {p.content}
                      </div>
                    </td>
                    <td className="p-3.5 text-center">
                      <span
                        className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                          p.isPublished
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-slate-200 text-slate-700'
                        }`}
                      >
                        {p.isPublished ? 'Aktif' : 'Draft'}
                      </span>
                    </td>
                    <td className="p-3.5 text-center font-mono text-slate-400 text-[11px]">
                      {p.updatedAt}
                    </td>
                    <td className="p-3.5 text-right whitespace-nowrap space-x-1.5">
                      <button
                        onClick={() => handleOpenEdit(p)}
                        className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs transition inline-flex items-center gap-1 cursor-pointer shadow-2xs"
                      >
                        <Edit3 className="w-3.5 h-3.5" />
                        <span>Edit Isi Halaman</span>
                      </button>
                      {!isCore && (
                        <button
                          onClick={() => handleDeletePage(p)}
                          className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg border border-rose-200 text-xs transition inline-flex items-center gap-1 cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          <span>Hapus</span>
                        </button>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Tambah Halaman Baru */}
      {isAdding && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4 text-xs">
            <div className="flex items-center justify-between border-b pb-3">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">
                  Buat Halaman Baru
                </span>
                <h3 className="text-base font-bold text-slate-900">
                  Tambah Halaman Website & Isi Konten
                </h3>
              </div>
              <button
                onClick={() => setIsAdding(false)}
                className="text-slate-400 hover:text-slate-700 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleAddPage} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-bold mb-1">
                    Judul Utama Halaman (H1) *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="Contoh: Standar K3 & Keselamatan"
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                    className="w-full text-xs p-2.5 border rounded-lg font-semibold"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">
                    Slug URL (Otomatis jika dikosongkan)
                  </label>
                  <input
                    type="text"
                    placeholder="standar-k3-keselamatan"
                    value={newSlug}
                    onChange={e => setNewSlug(e.target.value)}
                    className="w-full text-xs p-2.5 border rounded-lg font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold mb-1">
                  Ringkasan / Subjudul Halaman (Hero Subtitle) *
                </label>
                <textarea
                  rows={2}
                  required
                  placeholder="Ringkasan singkat yang tampil di bagian atas halaman..."
                  value={newSummary}
                  onChange={e => setNewSummary(e.target.value)}
                  className="w-full text-xs p-2.5 border rounded-lg"
                />
              </div>

              <div>
                <label className="block font-bold mb-1">
                  Isi Konten Lengkap Halaman *
                </label>
                <textarea
                  rows={9}
                  required
                  placeholder="Tuliskan isi konten halaman secara lengkap..."
                  value={newContent}
                  onChange={e => setNewContent(e.target.value)}
                  className="w-full text-xs p-2.5 border rounded-lg leading-relaxed font-mono"
                />
              </div>

              <div className="bg-blue-50/60 p-4 rounded-xl border border-blue-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block font-bold mb-1">
                    Tampilkan di Navigasi?
                  </label>
                  <select
                    value={newNavLocation}
                    onChange={e =>
                      setNewNavLocation(
                        e.target.value as 'none' | 'header' | 'footer'
                      )
                    }
                    className="w-full text-xs p-2 border rounded-lg bg-white font-semibold"
                  >
                    <option value="footer">Navigasi Footer (Bawah)</option>
                    <option value="header">Navigasi Header (Atas)</option>
                    <option value="none">Jangan Ditambahkan</option>
                  </select>
                </div>
                <div>
                  <label className="block font-bold mb-1">Label Menu</label>
                  <input
                    type="text"
                    placeholder="Sama dengan judul"
                    value={newNavLabel}
                    onChange={e => setNewNavLabel(e.target.value)}
                    className="w-full text-xs p-2 border rounded-lg bg-white"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">Urutan Menu</label>
                  <input
                    type="number"
                    min={1}
                    value={newNavOrder}
                    onChange={e =>
                      setNewNavOrder(parseInt(e.target.value, 10) || 1)
                    }
                    className="w-full text-xs p-2 border rounded-lg bg-white font-mono"
                  />
                </div>
              </div>

              <div className="flex items-center justify-end gap-2 pt-3 border-t">
                <button
                  type="button"
                  onClick={() => setIsAdding(false)}
                  className="px-4 py-2 border rounded-lg text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm cursor-pointer"
                >
                  + Simpan & Terbitkan Halaman
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Edit Halaman */}
      {editingPage && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4 text-xs">
            <div className="flex items-center justify-between border-b pb-3">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">
                  Edit Konten Halaman Website (URL: {resolvePageSlugToUrl(editingPage.slug)})
                </span>
                <h3 className="text-base font-bold text-slate-900">
                  Edit Isi Halaman: {editingPage.title}
                </h3>
              </div>
              <button
                onClick={() => setEditingPage(null)}
                className="text-slate-400 hover:text-slate-700 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div className="sm:col-span-2">
                  <label className="block font-bold mb-1">
                    Judul Utama Halaman (Hero Title / H1) *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.title || ''}
                    onChange={e =>
                      setFormData({ ...formData, title: e.target.value })
                    }
                    className="w-full text-xs p-2.5 border rounded-lg font-semibold"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">Slug Halaman</label>
                  <input
                    type="text"
                    value={formData.slug || ''}
                    readOnly={CORE_SLUGS.includes(editingPage.slug)}
                    onChange={e =>
                      setFormData({ ...formData, slug: e.target.value })
                    }
                    className={`w-full text-xs p-2.5 border rounded-lg font-mono ${
                      CORE_SLUGS.includes(editingPage.slug)
                        ? 'bg-slate-100 text-slate-500'
                        : ''
                    }`}
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold mb-1">
                  Ringkasan / Subjudul Halaman (Tampil di Bawah Judul Utama)
                </label>
                <textarea
                  rows={2}
                  value={formData.summary || ''}
                  onChange={e =>
                    setFormData({ ...formData, summary: e.target.value })
                  }
                  className="w-full text-xs p-2.5 border rounded-lg"
                />
              </div>

              <div>
                <label className="block font-bold mb-1">
                  Isi Konten Lengkap Halaman *
                </label>
                <textarea
                  rows={10}
                  required
                  value={formData.content || ''}
                  onChange={e =>
                    setFormData({ ...formData, content: e.target.value })
                  }
                  className="w-full text-xs p-2.5 border rounded-lg leading-relaxed font-mono"
                />
              </div>

              <div className="flex items-center justify-between pt-3 border-t">
                <label className="inline-flex items-center gap-2 cursor-pointer font-semibold">
                  <input
                    type="checkbox"
                    checked={Boolean(formData.isPublished)}
                    onChange={e =>
                      setFormData({ ...formData, isPublished: e.target.checked })
                    }
                    className="rounded text-blue-600"
                  />
                  <span>Tampilkan ke Publik</span>
                </label>

                <div className="flex gap-2">
                  <button
                    type="button"
                    onClick={() => setEditingPage(null)}
                    className="px-4 py-2 border rounded-lg text-slate-600 hover:bg-slate-50 cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm cursor-pointer"
                  >
                    Simpan Perubahan Konten
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
