import React, { useState } from 'react';
import {
  PromoItem,
  INITIAL_PROMOS,
  TestimonialItem,
  INITIAL_TESTIMONIALS,
  PartnerItem,
  CertificationItem
} from '../../data/companyData';
import {
  Plus,
  Edit3,
  Trash2,
  Check,
  X,
  Sparkles,
  Star,
  Handshake,
  Award,
  RotateCcw,
  Power
} from 'lucide-react';

interface AdminPromosProps {
  promos: PromoItem[];
  onUpdatePromos: (items: PromoItem[]) => void;
}

export const AdminPromos: React.FC<AdminPromosProps> = ({ promos, onUpdatePromos }) => {
  const [editingItem, setEditingItem] = useState<PromoItem | null>(null);
  const [isAdding, setIsAdding] = useState(false);
  const [toast, setToast] = useState('');

  const [form, setForm] = useState<{
    title: string;
    badge: string;
    category: PromoItem['category'];
    originalPrice: string;
    promoPrice: string;
    discountLabel: string;
    validUntil: string;
    description: string;
    featuresText: string;
    ctaText: string;
    isHighlight: boolean;
    isActive: boolean;
  }>({
    title: '',
    badge: 'Promo Spesial',
    category: 'Internet ISP',
    originalPrice: '',
    promoPrice: '',
    discountLabel: 'PROMO TERBATAS',
    validUntil: 'Berlaku Bulan Ini',
    description: '',
    featuresText: '',
    ctaText: 'Klaim Promo Sekarang',
    isHighlight: false,
    isActive: true
  });

  const showNotice = (msg: string) => {
    setToast(msg);
    setTimeout(() => setToast(''), 3500);
  };

  const openAdd = () => {
    setEditingItem(null);
    setForm({
      title: '',
      badge: 'Promo Spesial',
      category: 'Internet ISP',
      originalPrice: '',
      promoPrice: '',
      discountLabel: 'PROMO TERBATAS',
      validUntil: 'Berlaku Bulan Ini',
      description: '',
      featuresText:
        'Gratis biaya instalasi standar\nGaransi resmi & dukungan teknis 24/7\nKonsultasi & survei lokasi gratis',
      ctaText: 'Klaim Promo Sekarang',
      isHighlight: false,
      isActive: true
    });
    setIsAdding(true);
  };

  const openEdit = (item: PromoItem) => {
    setIsAdding(false);
    setEditingItem(item);
    setForm({
      title: item.title,
      badge: item.badge,
      category: item.category,
      originalPrice: item.originalPrice,
      promoPrice: item.promoPrice,
      discountLabel: item.discountLabel,
      validUntil: item.validUntil,
      description: item.description,
      featuresText: (item.features || []).join('\n'),
      ctaText: item.ctaText,
      isHighlight: item.isHighlight,
      isActive: item.isActive
    });
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.title.trim() || !form.promoPrice.trim()) return;

    const parsedFeatures = form.featuresText
      .split('\n')
      .map(s => s.trim())
      .filter(Boolean);

    if (editingItem) {
      const updated = promos.map(p =>
        p.id === editingItem.id
          ? {
              ...p,
              title: form.title.trim(),
              badge: form.badge.trim(),
              category: form.category,
              originalPrice: form.originalPrice.trim(),
              promoPrice: form.promoPrice.trim(),
              discountLabel: form.discountLabel.trim(),
              validUntil: form.validUntil.trim(),
              description: form.description.trim(),
              features: parsedFeatures,
              ctaText: form.ctaText.trim() || 'Ambil Promo Sekarang',
              isHighlight: form.isHighlight,
              isActive: form.isActive
            }
          : p
      );
      onUpdatePromos(updated);
      setEditingItem(null);
      showNotice(`Promo "${form.title}" berhasil diperbarui!`);
    } else {
      const newItem: PromoItem = {
        id: `promo-${Date.now()}`,
        title: form.title.trim(),
        badge: form.badge.trim(),
        category: form.category,
        originalPrice: form.originalPrice.trim(),
        promoPrice: form.promoPrice.trim(),
        discountLabel: form.discountLabel.trim(),
        validUntil: form.validUntil.trim(),
        description: form.description.trim(),
        features: parsedFeatures,
        ctaText: form.ctaText.trim() || 'Ambil Promo Sekarang',
        isHighlight: form.isHighlight,
        isActive: form.isActive
      };
      onUpdatePromos([newItem, ...promos]);
      setIsAdding(false);
      showNotice(`Promo baru "${form.title}" berhasil ditambahkan!`);
    }
  };

  const handleToggleActive = (id: string) => {
    onUpdatePromos(promos.map(p => (p.id === id ? { ...p, isActive: !p.isActive } : p)));
    showNotice('Status tayang Promo berhasil diubah.');
  };

  const handleDelete = (id: string, title: string) => {
    onUpdatePromos(promos.filter(p => p.id !== id));
    showNotice(`Promo "${title}" berhasil dihapus.`);
  };

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-blue-600 mb-1">
            <Sparkles className="w-4 h-4" />
            <span>Manajemen Menu Promo</span>
          </div>
          <h2 className="text-xl font-extrabold text-slate-900">
            Pengaturan Promo & Penawaran Spesial
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Kelola daftar paket promo Internet KGSNet, CCTV, Fiber Optik, PBX & Tower yang tampil pada menu <strong>Promo</strong> di halaman depan.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={() => {
              onUpdatePromos(INITIAL_PROMOS);
              showNotice('Daftar Promo berhasil dikembalikan ke standar default PT. KGS.');
            }}
            className="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Reset Default</span>
          </button>
          <button
            type="button"
            onClick={openAdd}
            className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Promo Baru</span>
          </button>
        </div>
      </div>

      {toast && (
        <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
          <Check className="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{toast}</span>
        </div>
      )}

      {(isAdding || editingItem) && (
        <form
          onSubmit={handleSave}
          className="bg-white rounded-2xl border-2 border-blue-500 p-6 shadow-md space-y-4"
        >
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 className="font-extrabold text-base text-slate-900">
              {editingItem ? `Edit Promo: ${editingItem.title}` : 'Tambah Program Promo Baru'}
            </h3>
            <button
              type="button"
              onClick={() => {
                setIsAdding(false);
                setEditingItem(null);
              }}
              className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Judul Promo *
              </label>
              <input
                type="text"
                required
                value={form.title}
                onChange={e => setForm({ ...form, title: e.target.value })}
                placeholder="Contoh: Promo Instalasi Baru KGSNet Home & Bisnis"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Kategori Layanan *
              </label>
              <select
                value={form.category}
                onChange={e =>
                  setForm({ ...form, category: e.target.value as PromoItem['category'] })
                }
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white"
              >
                <option value="Internet ISP">Internet ISP</option>
                <option value="CCTV & Security">CCTV & Security</option>
                <option value="Networking & Fiber">Networking & Fiber</option>
                <option value="PBX & Tower">PBX & Tower</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Harga Promo / Penawaran Utama *
              </label>
              <input
                type="text"
                required
                value={form.promoPrice}
                onChange={e => setForm({ ...form, promoPrice: e.target.value })}
                placeholder="Contoh: Mulai Rp 252.000 / Bulan"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Harga Normal / Coret (Opsional)
              </label>
              <input
                type="text"
                value={form.originalPrice}
                onChange={e => setForm({ ...form, originalPrice: e.target.value })}
                placeholder="Contoh: Biaya Instalasi Rp 500.000"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Label Diskon / Keuntungan
              </label>
              <input
                type="text"
                value={form.discountLabel}
                onChange={e => setForm({ ...form, discountLabel: e.target.value })}
                placeholder="Contoh: FREE INSTALASI 100%"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Badge & Masa Berlaku
              </label>
              <div className="grid grid-cols-2 gap-2">
                <input
                  type="text"
                  value={form.badge}
                  onChange={e => setForm({ ...form, badge: e.target.value })}
                  placeholder="Badge (Terpopuler)"
                  className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"
                />
                <input
                  type="text"
                  value={form.validUntil}
                  onChange={e => setForm({ ...form, validUntil: e.target.value })}
                  placeholder="Masa Berlaku"
                  className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"
                />
              </div>
            </div>
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Deskripsi Singkat Promo
            </label>
            <textarea
              rows={2}
              value={form.description}
              onChange={e => setForm({ ...form, description: e.target.value })}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Daftar Keuntungan / Fitur Promo (1 baris per poin)
            </label>
            <textarea
              rows={4}
              value={form.featuresText}
              onChange={e => setForm({ ...form, featuresText: e.target.value })}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono"
            />
          </div>

          <div className="flex flex-wrap items-center justify-between gap-4 pt-2">
            <div className="flex items-center gap-5 text-xs font-bold text-slate-700">
              <label className="inline-flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.isHighlight}
                  onChange={e => setForm({ ...form, isHighlight: e.target.checked })}
                />
                <span>Sorot sebagai Promo Unggulan</span>
              </label>
              <label className="inline-flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.isActive}
                  onChange={e => setForm({ ...form, isActive: e.target.checked })}
                />
                <span>Tampilkan di Website</span>
              </label>
            </div>

            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => {
                  setIsAdding(false);
                  setEditingItem(null);
                }}
                className="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Promo</span>
              </button>
            </div>
          </div>
        </form>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {promos.map(item => (
          <div
            key={item.id}
            className={`rounded-2xl border p-5 bg-white flex flex-col justify-between gap-4 ${
              item.isActive ? 'border-slate-200' : 'border-slate-200 opacity-60 bg-slate-50'
            }`}
          >
            <div>
              <div className="flex items-center justify-between gap-2 mb-2">
                <span className="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[11px] font-extrabold border border-blue-200">
                  {item.category}
                </span>
                <span className="px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-extrabold border border-emerald-200">
                  {item.discountLabel}
                </span>
              </div>
              <h3 className="font-extrabold text-base text-slate-900">{item.title}</h3>
              <p className="text-xs text-blue-600 font-extrabold mt-1">{item.promoPrice}</p>
              <p className="text-xs text-slate-600 mt-1.5 line-clamp-2">{item.description}</p>
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
              <button
                type="button"
                onClick={() => handleToggleActive(item.id)}
                className={`px-2.5 py-1 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 cursor-pointer ${
                  item.isActive
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                    : 'bg-slate-200 text-slate-600'
                }`}
              >
                <Power className="w-3 h-3" />
                <span>{item.isActive ? 'Aktif' : 'Nonaktif'}</span>
              </button>

              <div className="flex items-center gap-1.5">
                <button
                  type="button"
                  onClick={() => openEdit(item)}
                  className="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>Edit</span>
                </button>
                <button
                  type="button"
                  onClick={() => handleDelete(item.id, item.title)}
                  className="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 cursor-pointer"
                  title="Hapus Promo"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

interface AdminTestimonialsProps {
  testimonials: TestimonialItem[];
  onUpdateTestimonials: (items: TestimonialItem[]) => void;
}

export const AdminTestimonials: React.FC<AdminTestimonialsProps> = ({
  testimonials,
  onUpdateTestimonials
}) => {
  const [editingItem, setEditingItem] = useState<TestimonialItem | null>(null);
  const [isAdding, setIsAdding] = useState(false);
  const [toast, setToast] = useState('');

  const [form, setForm] = useState<{
    clientName: string;
    personName: string;
    roleOrDepartment: string;
    sector: TestimonialItem['sector'];
    serviceUsed: string;
    quote: string;
    rating: number;
    projectYear: string;
    isActive: boolean;
  }>({
    clientName: '',
    personName: '',
    roleOrDepartment: '',
    sector: 'BUMN & Korporasi',
    serviceUsed: '',
    quote: '',
    rating: 5,
    projectYear: 'Mitra KGS',
    isActive: true
  });

  const showNotice = (msg: string) => {
    setToast(msg);
    setTimeout(() => setToast(''), 3500);
  };

  const openAdd = () => {
    setEditingItem(null);
    setForm({
      clientName: '',
      personName: '',
      roleOrDepartment: '',
      sector: 'BUMN & Korporasi',
      serviceUsed: 'Internet Dedicated KGSNet & Jaringan',
      quote: '',
      rating: 5,
      projectYear: 'Mitra KGS',
      isActive: true
    });
    setIsAdding(true);
  };

  const openEdit = (item: TestimonialItem) => {
    setIsAdding(false);
    setEditingItem(item);
    setForm({
      clientName: item.clientName,
      personName: item.personName,
      roleOrDepartment: item.roleOrDepartment,
      sector: item.sector,
      serviceUsed: item.serviceUsed,
      quote: item.quote,
      rating: item.rating || 5,
      projectYear: item.projectYear,
      isActive: item.isActive
    });
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.clientName.trim() || !form.quote.trim()) return;

    if (editingItem) {
      const updated = testimonials.map(t =>
        t.id === editingItem.id
          ? {
              ...t,
              clientName: form.clientName.trim(),
              personName: form.personName.trim() || 'Perwakilan Instansi',
              roleOrDepartment: form.roleOrDepartment.trim() || 'Manajemen TI & Operasional',
              sector: form.sector,
              serviceUsed: form.serviceUsed.trim(),
              quote: form.quote.trim(),
              rating: Number(form.rating) || 5,
              projectYear: form.projectYear.trim(),
              isActive: form.isActive
            }
          : t
      );
      onUpdateTestimonials(updated);
      setEditingItem(null);
      showNotice(`Testimoni dari "${form.clientName}" berhasil diperbarui!`);
    } else {
      const newItem: TestimonialItem = {
        id: `testi-${Date.now()}`,
        clientName: form.clientName.trim(),
        personName: form.personName.trim() || 'Perwakilan Instansi',
        roleOrDepartment: form.roleOrDepartment.trim() || 'Manajemen TI & Operasional',
        sector: form.sector,
        serviceUsed: form.serviceUsed.trim(),
        quote: form.quote.trim(),
        rating: Number(form.rating) || 5,
        projectYear: form.projectYear.trim(),
        isActive: form.isActive
      };
      onUpdateTestimonials([newItem, ...testimonials]);
      setIsAdding(false);
      showNotice(`Testimoni baru dari "${form.clientName}" berhasil ditambahkan!`);
    }
  };

  const handleDelete = (id: string, name: string) => {
    onUpdateTestimonials(testimonials.filter(t => t.id !== id));
    showNotice(`Testimoni "${name}" berhasil dihapus.`);
  };

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-blue-600 mb-1">
            <Star className="w-4 h-4" />
            <span>Manajemen Menu Testimoni</span>
          </div>
          <h2 className="text-xl font-extrabold text-slate-900">
            Pengaturan Testimoni Client / Pelanggan
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Kelola ulasan dan apresiasi pelanggan BUMN, Pemerintahan, Rumah Sakit, Perbankan & Pendidikan yang tampil pada menu <strong>Testimoni</strong>.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={() => {
              onUpdateTestimonials(INITIAL_TESTIMONIALS);
              showNotice('Daftar Testimoni berhasil dikembalikan ke standar default PT. KGS.');
            }}
            className="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Reset Default</span>
          </button>
          <button
            type="button"
            onClick={openAdd}
            className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Testimoni</span>
          </button>
        </div>
      </div>

      {toast && (
        <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
          <Check className="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{toast}</span>
        </div>
      )}

      {(isAdding || editingItem) && (
        <form
          onSubmit={handleSave}
          className="bg-white rounded-2xl border-2 border-blue-500 p-6 shadow-md space-y-4"
        >
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 className="font-extrabold text-base text-slate-900">
              {editingItem ? `Edit Testimoni: ${editingItem.clientName}` : 'Tambah Testimoni Baru'}
            </h3>
            <button
              type="button"
              onClick={() => {
                setIsAdding(false);
                setEditingItem(null);
              }}
              className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Nama Instansi / Perusahaan Client *
              </label>
              <input
                type="text"
                required
                value={form.clientName}
                onChange={e => setForm({ ...form, clientName: e.target.value })}
                placeholder="Contoh: PT. Pupuk Sriwidjaja (PUSRI) Palembang"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Sektor Client *
              </label>
              <select
                value={form.sector}
                onChange={e =>
                  setForm({ ...form, sector: e.target.value as TestimonialItem['sector'] })
                }
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white"
              >
                <option value="BUMN & Korporasi">BUMN & Korporasi</option>
                <option value="Pemerintahan">Pemerintahan</option>
                <option value="Perbankan & Medis">Perbankan & Medis</option>
                <option value="Pendidikan & Retail">Pendidikan & Retail</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Nama Pejabat / Divisi Pemberi Ulasan
              </label>
              <input
                type="text"
                value={form.personName}
                onChange={e => setForm({ ...form, personName: e.target.value })}
                placeholder="Contoh: Divisi Teknologi Informasi"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Jabatan / Unit Operasional
              </label>
              <input
                type="text"
                value={form.roleOrDepartment}
                onChange={e => setForm({ ...form, roleOrDepartment: e.target.value })}
                placeholder="Contoh: Tim Infrastruktur Jaringan"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Produk / Layanan KGS yang Digunakan
              </label>
              <input
                type="text"
                value={form.serviceUsed}
                onChange={e => setForm({ ...form, serviceUsed: e.target.value })}
                placeholder="Contoh: Fiber Optik, CCTV & Internet Dedicated"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Rating Kepuasan (1 - 5 Bintang)
              </label>
              <select
                value={form.rating}
                onChange={e => setForm({ ...form, rating: Number(e.target.value) })}
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white"
              >
                <option value={5}>⭐⭐⭐⭐⭐ (5 Bintang - Sangat Puas)</option>
                <option value={4}>⭐⭐⭐⭐ (4 Bintang - Puas)</option>
                <option value={3}>⭐⭐⭐ (3 Bintang - Cukup)</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Isi Kutipan Testimoni *
            </label>
            <textarea
              rows={3}
              required
              value={form.quote}
              onChange={e => setForm({ ...form, quote: e.target.value })}
              placeholder="Tuliskan pengalaman dan testimoni client terhadap kualitas layanan PT. KGS..."
              className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
            />
          </div>

          <div className="flex items-center justify-between pt-2">
            <label className="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input
                type="checkbox"
                checked={form.isActive}
                onChange={e => setForm({ ...form, isActive: e.target.checked })}
              />
              <span>Tampilkan di Website</span>
            </label>

            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => {
                  setIsAdding(false);
                  setEditingItem(null);
                }}
                className="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Testimoni</span>
              </button>
            </div>
          </div>
        </form>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {testimonials.map(item => (
          <div
            key={item.id}
            className="rounded-2xl border border-slate-200 p-5 bg-white flex flex-col justify-between gap-4"
          >
            <div>
              <div className="flex items-center justify-between gap-2 mb-2">
                <span className="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[11px] font-bold">
                  {item.sector}
                </span>
                <span className="text-amber-500 text-xs font-bold">
                  {'★'.repeat(item.rating || 5)}
                </span>
              </div>
              <h3 className="font-extrabold text-sm text-slate-900">{item.clientName}</h3>
              <p className="text-xs text-slate-500 font-semibold">
                {item.personName} • {item.roleOrDepartment}
              </p>
              <p className="text-xs text-slate-700 italic mt-2">&ldquo;{item.quote}&rdquo;</p>
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
              <span className="text-[11px] font-semibold text-blue-600 truncate">
                {item.serviceUsed}
              </span>
              <div className="flex items-center gap-1.5 shrink-0">
                <button
                  type="button"
                  onClick={() => openEdit(item)}
                  className="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>Edit</span>
                </button>
                <button
                  type="button"
                  onClick={() => handleDelete(item.id, item.clientName)}
                  className="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 cursor-pointer"
                  title="Hapus Testimoni"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

interface AdminKemitraanProps {
  partners: PartnerItem[];
  onUpdatePartners: (items: PartnerItem[]) => void;
  certifications: CertificationItem[];
  onUpdateCertifications: (items: CertificationItem[]) => void;
}

export const AdminKemitraan: React.FC<AdminKemitraanProps> = ({
  partners,
  onUpdatePartners,
  certifications
}) => {
  const [editingPartner, setEditingPartner] = useState<PartnerItem | null>(null);
  const [isAdding, setIsAdding] = useState(false);
  const [toast, setToast] = useState('');
  const [form, setForm] = useState({
    name: '',
    role: 'Authorized Partner / Integrator',
    serviceType: 'Networking & Infrastructure',
    linkUrl: ''
  });

  const showNotice = (msg: string) => {
    setToast(msg);
    setTimeout(() => setToast(''), 3500);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.name.trim()) return;
    if (editingPartner) {
      onUpdatePartners(
        partners.map(p =>
          p.id === editingPartner.id
            ? {
                ...p,
                name: form.name.trim(),
                role: form.role.trim(),
                serviceType: form.serviceType.trim(),
                linkUrl: form.linkUrl.trim()
              }
            : p
        )
      );
      setEditingPartner(null);
      showNotice(`Mitra "${form.name}" berhasil diperbarui!`);
    } else {
      const newItem: PartnerItem = {
        id: `part-${Date.now()}`,
        name: form.name.trim(),
        role: form.role.trim(),
        serviceType: form.serviceType.trim(),
        pdfPage: 3,
        linkUrl: form.linkUrl.trim()
      };
      onUpdatePartners([...partners, newItem]);
      setIsAdding(false);
      showNotice(`Mitra baru "${form.name}" berhasil ditambahkan!`);
    }
  };

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-blue-600 mb-1">
            <Handshake className="w-4 h-4" />
            <span>Manajemen Menu Kemitraan</span>
          </div>
          <h2 className="text-xl font-extrabold text-slate-900">
            Pengaturan Kemitraan Strategis & Prinsipal Resmi
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Kelola daftar mitra prinsipal teknologi & sertifikasi resmi ({certifications.length} lisensi aktif) yang tampil pada menu <strong>Kemitraan</strong>.
          </p>
        </div>
        <button
          type="button"
          onClick={() => {
            setEditingPartner(null);
            setForm({
              name: '',
              role: 'Authorized Partner / Integrator',
              serviceType: 'Networking & Infrastructure',
              linkUrl: ''
            });
            setIsAdding(true);
          }}
          className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Mitra Prinsipal</span>
        </button>
      </div>

      {toast && (
        <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
          <Check className="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{toast}</span>
        </div>
      )}

      {(isAdding || editingPartner) && (
        <form
          onSubmit={handleSave}
          className="bg-white rounded-2xl border-2 border-blue-500 p-6 shadow-md space-y-4"
        >
          <h3 className="font-extrabold text-base text-slate-900">
            {editingPartner ? `Edit Mitra: ${editingPartner.name}` : 'Tambah Mitra Prinsipal Baru'}
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Nama Brand / Mitra Prinsipal *
              </label>
              <input
                type="text"
                required
                value={form.name}
                onChange={e => setForm({ ...form, name: e.target.value })}
                placeholder="Contoh: MikroTik / Panasonic / Hikvision"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Peran / Status Kemitraan *
              </label>
              <input
                type="text"
                required
                value={form.role}
                onChange={e => setForm({ ...form, role: e.target.value })}
                placeholder="Contoh: Authorized Dealer & System Integrator"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Segmen Produk / Teknologi
              </label>
              <input
                type="text"
                value={form.serviceType}
                onChange={e => setForm({ ...form, serviceType: e.target.value })}
                placeholder="Contoh: PBX & Telecommunication"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Website Resmi Prinsipal (Opsional)
              </label>
              <input
                type="url"
                value={form.linkUrl}
                onChange={e => setForm({ ...form, linkUrl: e.target.value })}
                placeholder="https://..."
                className="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"
              />
            </div>
          </div>
          <div className="flex justify-end gap-2">
            <button
              type="button"
              onClick={() => {
                setIsAdding(false);
                setEditingPartner(null);
              }}
              className="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold cursor-pointer"
            >
              Simpan Mitra
            </button>
          </div>
        </form>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {partners.map(p => (
          <div
            key={p.id}
            className="rounded-2xl border border-slate-200 p-4 bg-white flex flex-col justify-between gap-3"
          >
            <div>
              <span className="text-[11px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                {p.serviceType}
              </span>
              <h4 className="font-extrabold text-base text-slate-900 mt-2">{p.name}</h4>
              <p className="text-xs text-slate-600 font-semibold mt-0.5">{p.role}</p>
            </div>
            <div className="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-1.5">
              <button
                type="button"
                onClick={() => {
                  setIsAdding(false);
                  setEditingPartner(p);
                  setForm({
                    name: p.name,
                    role: p.role,
                    serviceType: p.serviceType,
                    linkUrl: p.linkUrl || ''
                  });
                }}
                className="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
              >
                <Edit3 className="w-3 h-3" />
                <span>Edit</span>
              </button>
              <button
                type="button"
                onClick={() => {
                  onUpdatePartners(partners.filter(item => item.id !== p.id));
                  showNotice(`Mitra "${p.name}" dihapus.`);
                }}
                className="p-1.5 rounded-lg bg-red-50 text-red-600 cursor-pointer"
              >
                <Trash2 className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
