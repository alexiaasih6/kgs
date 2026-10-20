import React, { useState } from 'react';
import { ArticleItem, ServiceCategory } from '../../data/companyData';
import {
  Plus,
  Edit3,
  Trash2,
  Search,
  CheckCircle2,
  Eye,
  Star,
  FileText,
  X,
  Upload,
  Image as ImageIcon,
  Tag,
  Clock,
  User,
  Check,
  Layers,
  Sparkles,
  ExternalLink
} from 'lucide-react';

interface AdminArticlesProps {
  articles: ArticleItem[];
  categories: ServiceCategory[];
  onUpdateArticles: (articles: ArticleItem[]) => void;
}

// Preset high quality corporate & tech cover images for 1-click selection
const PRESET_ARTICLE_IMAGES = [
  { label: 'Fiber Optic', url: 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80' },
  { label: 'Data Center', url: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80' },
  { label: 'Tower Guywire', url: 'https://images.unsplash.com/photo-1541888946425-d0fbb186f5f7?auto=format&fit=crop&w=1200&q=80' },
  { label: 'Grounding & Power', url: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80' },
  { label: 'CCTV & Surveillance', url: 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1200&q=80' },
  { label: 'AIS & Telemetri', url: 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1200&q=80' }
];

// Suggested quick labels / tags
const SUGGESTED_LABELS = [
  'Fiber Optic',
  'ISP KGSNet',
  'Keamanan CCTV',
  'Tower & Grounding',
  'Solusi B2B',
  'Studi Kasus',
  'Panduan Teknis',
  'Regulasi APJII',
  'Infrastruktur',
  'Berita Resmi'
];

export const AdminArticles: React.FC<AdminArticlesProps> = ({
  articles,
  categories,
  onUpdateArticles
}) => {
  const [search, setSearch] = useState('');
  const [selectedCat, setSelectedCat] = useState('all');
  const [selectedStatus, setSelectedStatus] = useState<'all' | 'published' | 'draft'>('all');
  const [editingArticle, setEditingArticle] = useState<ArticleItem | null>(null);
  const [isCreating, setIsCreating] = useState(false);
  const [previewArticle, setPreviewArticle] = useState<ArticleItem | null>(null);

  // Form state
  const [formData, setFormData] = useState<Partial<ArticleItem>>({
    title: '',
    categoryId: categories[0]?.id || 'cat_comp_net',
    summary: '',
    content: '',
    author: 'Tim Teknis PT. KGS',
    readTime: '4 min baca',
    imageUrl: '',
    labels: ['Solusi B2B'],
    isFeatured: false,
    status: 'published',
    metaTitle: '',
    metaDescription: ''
  });

  const [newTagInput, setNewTagInput] = useState('');

  const filtered = articles.filter(a => {
    const matchSearch =
      a.title.toLowerCase().includes(search.toLowerCase()) ||
      a.summary.toLowerCase().includes(search.toLowerCase()) ||
      a.author.toLowerCase().includes(search.toLowerCase()) ||
      (a.labels && a.labels.some(l => l.toLowerCase().includes(search.toLowerCase())));
    const matchCat = selectedCat === 'all' || a.categoryId === selectedCat;
    const matchStatus = selectedStatus === 'all' || a.status === selectedStatus;
    return matchSearch && matchCat && matchStatus;
  });

  const handleOpenCreate = () => {
    setFormData({
      title: '',
      categoryId: categories[0]?.id || 'cat_comp_net',
      summary: '',
      content: '',
      author: 'Tim Teknis PT. KGS',
      readTime: '5 min baca',
      imageUrl: PRESET_ARTICLE_IMAGES[0].url,
      labels: ['Infrastruktur', 'Solusi B2B'],
      isFeatured: false,
      status: 'published',
      metaTitle: '',
      metaDescription: ''
    });
    setNewTagInput('');
    setIsCreating(true);
    setEditingArticle(null);
  };

  const handleOpenEdit = (art: ArticleItem) => {
    setEditingArticle(art);
    setFormData({
      ...art,
      labels: art.labels || []
    });
    setNewTagInput('');
    setIsCreating(false);
  };

  const handleImageFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      alert('File harus berupa berkas gambar (JPG, PNG, WebP, SVG)!');
      return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
      const result = event.target?.result as string;
      setFormData(prev => ({ ...prev, imageUrl: result }));
    };
    reader.readAsDataURL(file);
  };

  const handleAddTag = (tagToAdd?: string) => {
    const tag = (tagToAdd || newTagInput).trim();
    if (!tag) return;
    const current = formData.labels || [];
    if (!current.includes(tag)) {
      setFormData(prev => ({ ...prev, labels: [...current, tag] }));
    }
    if (!tagToAdd) setNewTagInput('');
  };

  const handleRemoveTag = (tagToRemove: string) => {
    const current = formData.labels || [];
    setFormData(prev => ({ ...prev, labels: current.filter(t => t !== tagToRemove) }));
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.title || !formData.summary || !formData.content) return;

    const catObj = categories.find(c => c.id === formData.categoryId);
    const catName = catObj?.name || 'Umum';
    const slug = formData.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '');

    if (editingArticle) {
      const updated = articles.map(a =>
        a.id === editingArticle.id
          ? ({
              ...a,
              ...formData,
              categoryName: catName,
              slug,
              labels: formData.labels || [],
              metaTitle: formData.metaTitle || formData.title,
              metaDescription: formData.metaDescription || formData.summary
            } as ArticleItem)
          : a
      );
      onUpdateArticles(updated);
    } else {
      const newArt: ArticleItem = {
        id: `art-${Date.now()}`,
        title: formData.title,
        slug,
        categoryId: formData.categoryId || 'cat_comp_net',
        categoryName: catName,
        summary: formData.summary,
        content: formData.content,
        author: formData.author || 'Tim Teknis PT. KGS',
        readTime: formData.readTime || '5 min baca',
        imageUrl: formData.imageUrl || PRESET_ARTICLE_IMAGES[0].url,
        labels: formData.labels || ['Solusi B2B'],
        isFeatured: !!formData.isFeatured,
        status: (formData.status as 'published' | 'draft') || 'published',
        views: 0,
        createdAt: new Date().toISOString().slice(0, 10),
        metaTitle: formData.metaTitle || formData.title,
        metaDescription: formData.metaDescription || formData.summary
      };
      onUpdateArticles([newArt, ...articles]);
    }

    setIsCreating(false);
    setEditingArticle(null);
  };

  const handleDelete = (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus artikel ini?')) {
      onUpdateArticles(articles.filter(a => a.id !== id));
    }
  };

  const toggleStatus = (art: ArticleItem) => {
    const updated = articles.map(a =>
      a.id === art.id
        ? { ...a, status: a.status === 'published' ? ('draft' as const) : ('published' as const) }
        : a
    );
    onUpdateArticles(updated);
  };

  const toggleFeatured = (art: ArticleItem) => {
    const updated = articles.map(a =>
      a.id === art.id
        ? { ...a, isFeatured: !a.isFeatured }
        : a
    );
    onUpdateArticles(updated);
  };

  const publishedCount = articles.filter(a => a.status === 'published').length;
  const draftCount = articles.filter(a => a.status === 'draft').length;

  return (
    <div className="space-y-6">
      {/* Header with Title & Stats */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-xl font-bold text-slate-900">Kelola Artikel, Berita & Wawasan Teknis</h2>
            <span className="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full">
              {articles.length} Total
            </span>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Publikasikan artikel edukasi sistem integrator, fiber optic, grounding, dan telekomunikasi lengkap dengan gambar cover dan label topik.
          </p>
        </div>

        <button
          onClick={handleOpenCreate}
          className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Artikel Baru</span>
        </button>
      </div>

      {/* Stats Summary Bar */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div className="bg-white p-3.5 rounded-xl border border-slate-200">
          <div className="text-slate-400 font-medium">Artikel Terbit (Live)</div>
          <div className="text-xl font-extrabold text-emerald-600 mt-1">{publishedCount} Artikel</div>
        </div>
        <div className="bg-white p-3.5 rounded-xl border border-slate-200">
          <div className="text-slate-400 font-medium">Draft Belum Terbit</div>
          <div className="text-xl font-extrabold text-slate-600 mt-1">{draftCount} Draft</div>
        </div>
        <div className="bg-white p-3.5 rounded-xl border border-slate-200">
          <div className="text-slate-400 font-medium">Artikel Unggulan</div>
          <div className="text-xl font-extrabold text-amber-500 mt-1">
            {articles.filter(a => a.isFeatured).length} Pilihan
          </div>
        </div>
        <div className="bg-white p-3.5 rounded-xl border border-slate-200">
          <div className="text-slate-400 font-medium">Total Pembaca (Views)</div>
          <div className="text-xl font-extrabold text-blue-600 mt-1 font-mono">
            {articles.reduce((acc, a) => acc + (a.views || 0), 0)}
          </div>
        </div>
      </div>

      {/* Search & Filter Toolbar */}
      <div className="bg-white p-4 rounded-xl border border-slate-200 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div className="relative flex-1">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari judul, ringkasan, penulis, atau label artikel..."
            className="w-full text-xs pl-9 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div className="flex items-center gap-2">
          {/* Status Filter */}
          <div className="flex items-center p-1 bg-slate-100 rounded-lg text-xs">
            <button
              onClick={() => setSelectedStatus('all')}
              className={`px-2.5 py-1 rounded font-semibold transition cursor-pointer ${
                selectedStatus === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'
              }`}
            >
              Semua
            </button>
            <button
              onClick={() => setSelectedStatus('published')}
              className={`px-2.5 py-1 rounded font-semibold transition cursor-pointer ${
                selectedStatus === 'published' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
              }`}
            >
              Terbit ({publishedCount})
            </button>
            <button
              onClick={() => setSelectedStatus('draft')}
              className={`px-2.5 py-1 rounded font-semibold transition cursor-pointer ${
                selectedStatus === 'draft' ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
              }`}
            >
              Draft ({draftCount})
            </button>
          </div>

          {/* Category Dropdown */}
          <select
            value={selectedCat}
            onChange={e => setSelectedCat(e.target.value)}
            className="text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white font-medium"
          >
            <option value="all">Semua Kategori</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Articles Table with Thumbnails & Labels */}
      <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-50 uppercase text-[10px] text-slate-500 border-b border-slate-200">
              <tr>
                <th className="p-3.5 w-16">Cover</th>
                <th className="p-3.5">Judul & Label Topik</th>
                <th className="p-3.5">Kategori</th>
                <th className="p-3.5">Penulis & Tanggal</th>
                <th className="p-3.5 text-center">Status</th>
                <th className="p-3.5 text-center">Featured</th>
                <th className="p-3.5 text-center">Views</th>
                <th className="p-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={8} className="p-10 text-center text-slate-400">
                    Tidak ada artikel yang cocok dengan filter atau kata kunci.
                  </td>
                </tr>
              ) : (
                filtered.map(art => (
                  <tr key={art.id} className="hover:bg-slate-50 transition group">
                    {/* Cover Thumbnail */}
                    <td className="p-3.5">
                      <div className="w-14 h-11 rounded-lg overflow-hidden bg-slate-900 border border-slate-200 shrink-0 relative">
                        {art.imageUrl ? (
                          <img
                            src={art.imageUrl}
                            alt={art.title}
                            className="w-full h-full object-cover"
                          />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center text-slate-400">
                            <ImageIcon className="w-4 h-4" />
                          </div>
                        )}
                      </div>
                    </td>

                    {/* Title & Labels */}
                    <td className="p-3.5 max-w-sm">
                      <div className="font-bold text-slate-900 line-clamp-1 group-hover:text-blue-600 transition">
                        {art.title}
                      </div>
                      <div className="flex flex-wrap items-center gap-1 mt-1.5">
                        {art.labels && art.labels.length > 0 ? (
                          art.labels.map((lbl, idx) => (
                            <span
                              key={idx}
                              className="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold"
                            >
                              #{lbl}
                            </span>
                          ))
                        ) : (
                          <span className="text-[10px] text-slate-400">Belum ada label</span>
                        )}
                        {art.readTime && (
                          <span className="text-[10px] text-slate-400 flex items-center gap-0.5 ml-1">
                            <Clock className="w-2.5 h-2.5" />
                            <span>{art.readTime}</span>
                          </span>
                        )}
                      </div>
                    </td>

                    {/* Category */}
                    <td className="p-3.5">
                      <span className="inline-block text-[10px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                        {art.categoryName}
                      </span>
                    </td>

                    {/* Author & Date */}
                    <td className="p-3.5 text-slate-600 whitespace-nowrap">
                      <div className="font-medium text-slate-800 flex items-center gap-1">
                        <User className="w-3 h-3 text-slate-400" />
                        <span>{art.author}</span>
                      </div>
                      <div className="text-[10px] text-slate-400 mt-0.5">{art.createdAt}</div>
                    </td>

                    {/* Status Badge & Toggle */}
                    <td className="p-3.5 text-center">
                      <button
                        onClick={() => toggleStatus(art)}
                        className={`px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer ${
                          art.status === 'published'
                            ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                            : 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                        }`}
                        title="Klik untuk ubah status publikasi"
                      >
                        {art.status === 'published' ? '● Terbit' : '○ Draft'}
                      </button>
                    </td>

                    {/* Featured Star Toggle */}
                    <td className="p-3.5 text-center">
                      <button
                        onClick={() => toggleFeatured(art)}
                        className="p-1 hover:bg-slate-100 rounded transition cursor-pointer"
                        title="Klik untuk tandai/hilangkan unggulan"
                      >
                        {art.isFeatured ? (
                          <Star className="w-4 h-4 text-amber-500 fill-amber-500 inline" />
                        ) : (
                          <Star className="w-4 h-4 text-slate-300 inline" />
                        )}
                      </button>
                    </td>

                    {/* Views */}
                    <td className="p-3.5 text-center font-mono text-slate-600">
                      {art.views}
                    </td>

                    {/* Actions */}
                    <td className="p-3.5 text-right space-x-1.5 whitespace-nowrap">
                      <button
                        onClick={() => setPreviewArticle(art)}
                        className="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded text-[11px] border border-slate-200 transition cursor-pointer"
                        title="Lihat Pratinjau Artikel"
                      >
                        <Eye className="w-3 h-3 inline mr-1" />
                        <span>Lihat</span>
                      </button>
                      <button
                        onClick={() => handleOpenEdit(art)}
                        className="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded text-[11px] border border-blue-200 transition cursor-pointer"
                      >
                        <Edit3 className="w-3 h-3 inline mr-1" />
                        <span>Edit</span>
                      </button>
                      <button
                        onClick={() => handleDelete(art.id)}
                        className="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded text-[11px] border border-rose-200 transition cursor-pointer"
                        title="Hapus Artikel"
                      >
                        <Trash2 className="w-3 h-3 inline" />
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Create / Edit Artikel */}
      {(isCreating || editingArticle) && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl max-h-[92vh] overflow-y-auto space-y-5 text-xs animate-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-slate-100 pb-3">
              <div>
                <h3 className="text-base font-bold text-slate-900">
                  {isCreating ? 'Tambah Artikel / Berita Baru' : `Edit Artikel: ${editingArticle?.title}`}
                </h3>
                <p className="text-slate-500 text-[11px] mt-0.5">
                  Lengkapi judul, cover gambar, kategori, label topik, dan isi materi edukatif.
                </p>
              </div>
              <button
                onClick={() => {
                  setIsCreating(false);
                  setEditingArticle(null);
                }}
                className="text-slate-400 hover:text-slate-700 p-1 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              {/* Title */}
              <div>
                <label className="block font-bold mb-1 text-slate-800">Judul Artikel *</label>
                <input
                  type="text"
                  required
                  value={formData.title}
                  onChange={e => setFormData({ ...formData, title: e.target.value })}
                  placeholder="Contoh: Implementasi Sistem Fiber Optic Backbone di Kawasan Pabrik Kelapa Sawit"
                  className="w-full text-xs p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold text-slate-900"
                />
              </div>

              {/* Category, Author, ReadTime */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block font-bold mb-1 text-slate-800">Kategori Segmen *</label>
                  <select
                    value={formData.categoryId}
                    onChange={e => setFormData({ ...formData, categoryId: e.target.value })}
                    className="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white"
                  >
                    {categories.map(c => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-bold mb-1 text-slate-800">Penulis (Author)</label>
                  <input
                    type="text"
                    value={formData.author}
                    onChange={e => setFormData({ ...formData, author: e.target.value })}
                    placeholder="Contoh: Tim Teknis PT. KGS"
                    className="w-full text-xs p-2.5 border border-slate-200 rounded-xl"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1 text-slate-800">Waktu Baca</label>
                  <input
                    type="text"
                    value={formData.readTime}
                    onChange={e => setFormData({ ...formData, readTime: e.target.value })}
                    placeholder="Contoh: 5 min baca"
                    className="w-full text-xs p-2.5 border border-slate-200 rounded-xl"
                  />
                </div>
              </div>

              {/* COVER IMAGE UPLOAD & PRESET SECTION */}
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div className="flex items-center justify-between">
                  <label className="block font-bold text-slate-900 text-xs">
                    Gambar Cover / Featured Image Artikel
                  </label>
                  <span className="text-[10px] text-slate-500">Mendukung file JPG, PNG, WebP</span>
                </div>

                {/* Preview Box */}
                {formData.imageUrl ? (
                  <div className="relative h-44 w-full rounded-xl overflow-hidden border border-slate-300 bg-slate-900 shadow-xs">
                    <img
                      src={formData.imageUrl}
                      alt="Cover Preview"
                      className="w-full h-full object-cover"
                    />
                    <button
                      type="button"
                      onClick={() => setFormData({ ...formData, imageUrl: '' })}
                      className="absolute top-2 right-2 px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold cursor-pointer shadow-md flex items-center gap-1"
                    >
                      <Trash2 className="w-3 h-3" />
                      <span>Hapus Gambar</span>
                    </button>
                  </div>
                ) : (
                  <div className="h-28 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center text-slate-400 bg-white">
                    <ImageIcon className="w-8 h-8 mb-1 text-slate-300" />
                    <span className="text-xs font-medium">Belum ada gambar cover dipilih</span>
                  </div>
                )}

                {/* Upload & Preset Buttons */}
                <div className="flex flex-wrap items-center gap-2 pt-1">
                  <label className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-xs cursor-pointer flex items-center gap-1.5 transition shadow-xs">
                    <Upload className="w-3.5 h-3.5" />
                    <span>Upload Foto dari Perangkat</span>
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handleImageFileUpload}
                      className="hidden"
                    />
                  </label>

                  <input
                    type="url"
                    value={formData.imageUrl && formData.imageUrl.startsWith('data:') ? '' : formData.imageUrl}
                    onChange={e => setFormData({ ...formData, imageUrl: e.target.value })}
                    placeholder="Atau tempel URL gambar..."
                    className="flex-1 min-w-[200px] border border-slate-200 rounded-lg p-2 text-xs bg-white"
                  />
                </div>

                {/* Preset Suggestions */}
                <div>
                  <span className="text-[11px] font-semibold text-slate-500 block mb-1.5">
                    Pilihan Cepat Foto Tema B2B KGS:
                  </span>
                  <div className="flex flex-wrap gap-1.5">
                    {PRESET_ARTICLE_IMAGES.map((preset, idx) => (
                      <button
                        key={idx}
                        type="button"
                        onClick={() => setFormData({ ...formData, imageUrl: preset.url })}
                        className="px-2.5 py-1 bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 rounded-lg text-[11px] font-medium transition cursor-pointer"
                      >
                        {preset.label}
                      </button>
                    ))}
                  </div>
                </div>
              </div>

              {/* LABELS / TAGS SECTION */}
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div className="flex items-center justify-between">
                  <label className="block font-bold text-slate-900 text-xs flex items-center gap-1.5">
                    <Tag className="w-3.5 h-3.5 text-blue-600" />
                    <span>Label & Tag Topik Artikel *</span>
                  </label>
                  <span className="text-[10px] text-slate-500">Membantu pembaca memfilter topik</span>
                </div>

                {/* Current Tags Chips */}
                <div className="flex flex-wrap items-center gap-1.5 min-h-[32px] p-2 bg-white border border-slate-200 rounded-xl">
                  {formData.labels && formData.labels.length > 0 ? (
                    formData.labels.map((lbl, idx) => (
                      <span
                        key={idx}
                        className="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold text-[11px] flex items-center gap-1.5"
                      >
                        <span>#{lbl}</span>
                        <button
                          type="button"
                          onClick={() => handleRemoveTag(lbl)}
                          className="hover:text-rose-600 cursor-pointer"
                        >
                          &times;
                        </button>
                      </span>
                    ))
                  ) : (
                    <span className="text-slate-400 text-xs italic">Belum ada label ditambahkan</span>
                  )}
                </div>

                {/* Tag Input */}
                <div className="flex items-center gap-2">
                  <input
                    type="text"
                    value={newTagInput}
                    onChange={e => setNewTagInput(e.target.value)}
                    onKeyDown={e => {
                      if (e.key === 'Enter') {
                        e.preventDefault();
                        handleAddTag();
                      }
                    }}
                    placeholder="Ketik label baru (misal: Netviel, OTDR, BGP) lalu tekan Enter / Tambah..."
                    className="flex-1 border border-slate-200 rounded-lg p-2 text-xs bg-white"
                  />
                  <button
                    type="button"
                    onClick={() => handleAddTag()}
                    className="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold text-xs cursor-pointer"
                  >
                    Tambah Label
                  </button>
                </div>

                {/* Suggested Quick Labels */}
                <div>
                  <span className="text-[11px] font-semibold text-slate-500 block mb-1.5">
                    Label Populer Tersedia:
                  </span>
                  <div className="flex flex-wrap gap-1.5">
                    {SUGGESTED_LABELS.map((sug, idx) => {
                      const isSelected = formData.labels?.includes(sug);
                      return (
                        <button
                          key={idx}
                          type="button"
                          onClick={() => isSelected ? handleRemoveTag(sug) : handleAddTag(sug)}
                          className={`px-2 py-0.5 rounded text-[10px] font-semibold transition cursor-pointer border ${
                            isSelected
                              ? 'bg-blue-600 text-white border-blue-600'
                              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'
                          }`}
                        >
                          {isSelected ? '✓ ' : '+ '} {sug}
                        </button>
                      );
                    })}
                  </div>
                </div>
              </div>

              {/* Summary */}
              <div>
                <label className="block font-bold mb-1 text-slate-800">Ringkasan Artikel (Excerpt) *</label>
                <textarea
                  rows={2}
                  required
                  value={formData.summary}
                  onChange={e => setFormData({ ...formData, summary: e.target.value })}
                  placeholder="Tuliskan 1-2 kalimat ringkasan inti artikel untuk tampilan cuplikan kartu..."
                  className="w-full text-xs p-2.5 border border-slate-200 rounded-xl"
                />
              </div>

              {/* Content */}
              <div>
                <label className="block font-bold mb-1 text-slate-800">Konten Lengkap Artikel *</label>
                <textarea
                  rows={9}
                  required
                  value={formData.content}
                  onChange={e => setFormData({ ...formData, content: e.target.value })}
                  placeholder="Tuliskan naskah artikel lengkap di sini. Gunakan baris baru untuk paragraf..."
                  className="w-full text-xs p-3 border border-slate-200 rounded-xl font-mono leading-relaxed"
                />
              </div>

              {/* Status & Featured Options */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <div>
                  <label className="block font-bold mb-1.5 text-slate-800">Status Publikasi</label>
                  <div className="flex items-center gap-4">
                    <label className="flex items-center gap-1.5 cursor-pointer font-medium">
                      <input
                        type="radio"
                        name="pub_status"
                        checked={formData.status === 'published'}
                        onChange={() => setFormData({ ...formData, status: 'published' })}
                      />
                      <span className="text-emerald-700 font-semibold">Publikasikan (Live)</span>
                    </label>
                    <label className="flex items-center gap-1.5 cursor-pointer font-medium">
                      <input
                        type="radio"
                        name="pub_status"
                        checked={formData.status === 'draft'}
                        onChange={() => setFormData({ ...formData, status: 'draft' })}
                      />
                      <span className="text-slate-600">Simpan sebagai Draft</span>
                    </label>
                  </div>
                </div>

                <div className="flex items-center">
                  <label className="inline-flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                    <input
                      type="checkbox"
                      checked={formData.isFeatured}
                      onChange={e => setFormData({ ...formData, isFeatured: e.target.checked })}
                      className="rounded text-blue-600 w-4 h-4"
                    />
                    <span>Jadikan Artikel Unggulan (Featured)</span>
                  </label>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => {
                    setIsCreating(false);
                    setEditingArticle(null);
                  }}
                  className="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 cursor-pointer font-semibold"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Artikel & Gambar</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Preview Modal */}
      {previewArticle && (
        <div className="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4 text-xs">
            <div className="flex items-center justify-between border-b pb-3">
              <span className="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">
                Pratinjau Artikel: {previewArticle.categoryName}
              </span>
              <button onClick={() => setPreviewArticle(null)} className="text-slate-400 hover:text-slate-700 cursor-pointer">
                <X className="w-5 h-5" />
              </button>
            </div>

            {previewArticle.imageUrl && (
              <div className="h-56 w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-900">
                <img src={previewArticle.imageUrl} alt={previewArticle.title} className="w-full h-full object-cover" />
              </div>
            )}

            <h2 className="text-xl font-bold text-slate-900 leading-snug">{previewArticle.title}</h2>

            <div className="flex flex-wrap items-center gap-1.5">
              {previewArticle.labels?.map((lbl, idx) => (
                <span key={idx} className="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">
                  #{lbl}
                </span>
              ))}
              <span className="text-slate-400 text-[11px] ml-2">
                Oleh {previewArticle.author} · {previewArticle.createdAt}
              </span>
            </div>

            <div className="p-3 bg-slate-50 border-l-4 border-blue-600 rounded-r-lg italic text-slate-700">
              {previewArticle.summary}
            </div>

            <div className="text-slate-700 leading-relaxed whitespace-pre-line">
              {previewArticle.content}
            </div>

            <div className="pt-3 border-t flex justify-end">
              <button
                onClick={() => setPreviewArticle(null)}
                className="px-4 py-2 bg-slate-900 text-white rounded-xl font-semibold cursor-pointer"
              >
                Tutup Pratinjau
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
