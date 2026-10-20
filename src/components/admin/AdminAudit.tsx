import React, { useState } from 'react';
import { ServiceItem, ServiceCategory, SERVICE_CATEGORIES, SERVICES_DATA } from '../../data/companyData';
import {
  ShieldCheck,
  CheckCircle2,
  Plus,
  Edit3,
  Trash2,
  Search,
  X,
  Check,
  Eye,
  EyeOff,
  RotateCcw,
  Layers
} from 'lucide-react';

interface AdminAuditProps {
  services: ServiceItem[];
  categories?: ServiceCategory[];
  onUpdateServices?: (services: ServiceItem[]) => void;
}

export const AdminAudit: React.FC<AdminAuditProps> = ({
  services,
  categories = SERVICE_CATEGORIES,
  onUpdateServices
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [editingService, setEditingService] = useState<ServiceItem | null>(null);
  const [isAddingNew, setIsAddingNew] = useState(false);
  const [deleteConfirmId, setDeleteConfirmId] = useState<string | null>(null);
  const [toastMsg, setToastMsg] = useState<string>('');

  const [formData, setFormData] = useState<{
    name: string;
    categoryId: string;
    summary: string;
    description: string;
    featuresText: string;
    brandsText: string;
    verificationStatus: ServiceItem['verificationStatus'];
    isPublished: boolean;
  }>({
    name: '',
    categoryId: categories[0]?.id || SERVICE_CATEGORIES[0].id,
    summary: '',
    description: '',
    featuresText: '',
    brandsText: '',
    verificationStatus: 'Standar Resmi KGS',
    isPublished: true
  });

  const showToast = (msg: string) => {
    setToastMsg(msg);
    setTimeout(() => setToastMsg(''), 3500);
  };

  const verifiedCount = services.filter(
    s =>
      s.verificationStatus === 'Terverifikasi dari PDF' ||
      s.verificationStatus === 'Standar Resmi KGS' ||
      !s.verificationStatus
  ).length;
  const publishedCount = services.filter(s => s.isPublished).length;
  const customCount = services.filter(s => s.verificationStatus === 'Tambahan Disetujui Admin').length;

  const filteredServices = services.filter(s => {
    const matchCategory = selectedCategory === 'all' || s.categoryId === selectedCategory;
    const matchSearch =
      searchTerm === '' ||
      s.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.categoryName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.summary.toLowerCase().includes(searchTerm.toLowerCase());
    return matchCategory && matchSearch;
  });

  const handleOpenAdd = () => {
    setEditingService(null);
    setFormData({
      name: '',
      categoryId: categories[0]?.id || SERVICE_CATEGORIES[0].id,
      summary: '',
      description: '',
      featuresText: 'Instalasi & Konfigurasi Standar Korporasi\nGaransi & Dukungan Teknis Resmi PT. KGS',
      brandsText: '',
      verificationStatus: 'Standar Resmi KGS',
      isPublished: true
    });
    setIsAddingNew(true);
  };

  const handleOpenEdit = (srv: ServiceItem) => {
    setIsAddingNew(false);
    setEditingService(srv);
    setFormData({
      name: srv.name,
      categoryId: srv.categoryId,
      summary: srv.summary,
      description: srv.description || srv.summary,
      featuresText: (srv.features || []).join('\n'),
      brandsText: (srv.brands || []).join(', '),
      verificationStatus:
        srv.verificationStatus === 'Terverifikasi dari PDF'
          ? 'Standar Resmi KGS'
          : srv.verificationStatus || 'Standar Resmi KGS',
      isPublished: srv.isPublished
    });
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!onUpdateServices || !formData.name.trim()) return;

    const catObj =
      categories.find(c => c.id === formData.categoryId) ||
      SERVICE_CATEGORIES.find(c => c.id === formData.categoryId) ||
      SERVICE_CATEGORIES[0];

    const parsedFeatures = formData.featuresText
      .split('\n')
      .map(f => f.trim())
      .filter(Boolean);

    const parsedBrands = formData.brandsText
      .split(',')
      .map(b => b.trim())
      .filter(Boolean);

    if (isAddingNew) {
      const newItem: ServiceItem = {
        id: `srv-std-${Date.now()}`,
        categoryId: catObj.id,
        categoryName: catObj.name,
        name: formData.name.trim(),
        slug: formData.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
        summary: formData.summary.trim() || formData.name.trim(),
        description: formData.description.trim() || formData.summary.trim(),
        features: parsedFeatures.length > 0 ? parsedFeatures : ['Layanan Terstandarisasi PT. KGS'],
        brands: parsedBrands,
        targetSectors: ['Perusahaan B2B', 'Instansi Pemerintah', 'Industri'],
        pdfPage: 4,
        verificationStatus: formData.verificationStatus,
        adminNotes: 'Standar Layanan Resmi PT. KGS',
        isPublished: formData.isPublished,
        order: services.length + 1
      };
      onUpdateServices([...services, newItem]);
      setIsAddingNew(false);
      showToast(`Standar layanan "${newItem.name}" berhasil ditambahkan.`);
    } else if (editingService) {
      const updated = services.map(s => {
        if (s.id === editingService.id) {
          return {
            ...s,
            name: formData.name.trim(),
            slug: formData.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            categoryId: catObj.id,
            categoryName: catObj.name,
            summary: formData.summary.trim(),
            description: formData.description.trim(),
            features: parsedFeatures.length > 0 ? parsedFeatures : s.features,
            brands: parsedBrands,
            verificationStatus: formData.verificationStatus,
            isPublished: formData.isPublished
          };
        }
        return s;
      });
      onUpdateServices(updated);
      setEditingService(null);
      showToast(`Perubahan pada "${formData.name}" berhasil disimpan.`);
    }
  };

  const handleConfirmDelete = (id: string, name: string) => {
    if (!onUpdateServices) return;
    onUpdateServices(services.filter(s => s.id !== id));
    setDeleteConfirmId(null);
    if (editingService?.id === id) {
      setEditingService(null);
    }
    showToast(`Layanan "${name}" telah dihapus dari katalog.`);
  };

  const handleTogglePublish = (srv: ServiceItem) => {
    if (!onUpdateServices) return;
    const updated = services.map(s =>
      s.id === srv.id ? { ...s, isPublished: !s.isPublished } : s
    );
    onUpdateServices(updated);
    showToast(
      `Status publikasi "${srv.name}" diubah menjadi ${!srv.isPublished ? 'Terbit' : 'Draft'}.`
    );
  };

  const handleResetDefaults = () => {
    if (!onUpdateServices) return;
    onUpdateServices(SERVICES_DATA);
    showToast('Katalog telah dikembalikan ke 22 Standar Layanan Default PT. KGS.');
  };

  return (
    <div className="space-y-6">
      {/* Notification Toast */}
      {toastMsg && (
        <div className="bg-emerald-900 text-white px-4 py-3 rounded-xl shadow-lg flex items-center justify-between text-xs font-semibold animate-in fade-in duration-200">
          <div className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-400 shrink-0" />
            <span>{toastMsg}</span>
          </div>
          <button onClick={() => setToastMsg('')} className="text-emerald-300 hover:text-white cursor-pointer">
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Metric Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="text-xs text-slate-500 font-semibold">Total Katalog Standar Layanan</div>
          <div className="text-3xl font-extrabold text-emerald-600 mt-1">{services.length} Layanan</div>
          <div className="text-[11px] text-slate-400 mt-1">
            {verifiedCount} Standar Resmi · {customCount} Tambahan Kustom
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="text-xs text-slate-500 font-semibold">Status Tayang di Website</div>
          <div className="text-3xl font-extrabold text-blue-600 mt-1">{publishedCount} Aktif</div>
          <div className="text-[11px] text-slate-400 mt-1">
            {services.length - publishedCount} layanan berstatus Draft
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="text-xs text-slate-500 font-semibold">Cakupan Segmen Solusi</div>
          <div className="text-3xl font-extrabold text-slate-900 mt-1">{categories.length} Segmen</div>
          <div className="text-[11px] text-slate-400 mt-1">
            IT, Telekomunikasi, CCTV, ISP KGSNet & Infrastruktur
          </div>
        </div>
      </div>

      {/* Add / Edit Modal Form */}
      {(isAddingNew || editingService) && (
        <div className="bg-white rounded-2xl border-2 border-blue-500 p-6 shadow-xl space-y-5">
          <div className="flex items-center justify-between border-b border-slate-200 pb-3">
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-blue-600" />
              <h3 className="text-sm font-bold text-slate-900">
                {isAddingNew
                  ? 'Tambah Standar Layanan Baru ke Katalog'
                  : `Edit Katalog Standar Layanan: ${editingService?.name}`}
              </h3>
            </div>
            <button
              type="button"
              onClick={() => {
                setIsAddingNew(false);
                setEditingService(null);
              }}
              className="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <form onSubmit={handleSave} className="space-y-4 text-xs">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold text-slate-700 mb-1">Nama Standar Layanan *</label>
                <input
                  type="text"
                  required
                  value={formData.name}
                  onChange={e => setFormData({ ...formData, name: e.target.value })}
                  placeholder="Contoh: Fiber Optic Installation & Splicing"
                  className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-blue-600"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">Segmen Kategori Layanan *</label>
                <select
                  value={formData.categoryId}
                  onChange={e => setFormData({ ...formData, categoryId: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 bg-white focus:outline-none focus:border-blue-600"
                >
                  {categories.map(c => (
                    <option key={c.id} value={c.id}>
                      Segmen {c.segmentNumber}: {c.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold text-slate-700 mb-1">Status Standarisasi</label>
                <select
                  value={formData.verificationStatus}
                  onChange={e =>
                    setFormData({
                      ...formData,
                      verificationStatus: e.target.value as ServiceItem['verificationStatus']
                    })
                  }
                  className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 bg-white focus:outline-none focus:border-blue-600"
                >
                  <option value="Standar Resmi KGS">Standar Resmi KGS</option>
                  <option value="Tambahan Disetujui Admin">Tambahan Disetujui Admin</option>
                  <option value="Perlu Konfirmasi">Perlu Konfirmasi</option>
                </select>
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">Status Tayang Website Publik</label>
                <div className="flex items-center gap-4 pt-2">
                  <label className="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                    <input
                      type="radio"
                      checked={formData.isPublished === true}
                      onChange={() => setFormData({ ...formData, isPublished: true })}
                    />
                    <span>Terbitkan di Website</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                    <input
                      type="radio"
                      checked={formData.isPublished === false}
                      onChange={() => setFormData({ ...formData, isPublished: false })}
                    />
                    <span>Sembunyikan (Draft)</span>
                  </label>
                </div>
              </div>
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">Ringkasan Singkat Layanan *</label>
              <textarea
                rows={2}
                required
                value={formData.summary}
                onChange={e => setFormData({ ...formData, summary: e.target.value })}
                placeholder="Ringkasan singkat layanan yang tampil pada kartu katalog..."
                className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">Deskripsi & Spesifikasi Teknis Lengkap</label>
              <textarea
                rows={3}
                value={formData.description}
                onChange={e => setFormData({ ...formData, description: e.target.value })}
                placeholder="Penjelasan ruang lingkup pekerjaan dan spesifikasi teknis..."
                className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-blue-600"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Poin Spesifikasi / Fitur Utama (1 baris = 1 fitur)
                </label>
                <textarea
                  rows={3}
                  value={formData.featuresText}
                  onChange={e => setFormData({ ...formData, featuresText: e.target.value })}
                  placeholder="Survei & Perancangan Topologi&#10;Instalasi & Terminasi&#10;Garansi Resmi"
                  className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-blue-600"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Brand / Prinsipal Pendukung (Pisahkan dengan koma)
                </label>
                <textarea
                  rows={3}
                  value={formData.brandsText}
                  onChange={e => setFormData({ ...formData, brandsText: e.target.value })}
                  placeholder="Contoh: Panasonic, Sony, Netviel, MikroTik, Ubiquiti"
                  className="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-blue-600"
                />
              </div>
            </div>

            <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
              <button
                type="button"
                onClick={() => {
                  setIsAddingNew(false);
                  setEditingService(null);
                }}
                className="px-4 py-2 border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-100 cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Standar Layanan</span>
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Search, Filter & Action Toolbar */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 text-xs">
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={searchTerm}
              onChange={e => setSearchTerm(e.target.value)}
              placeholder="Cari nama standar layanan, segmen, atau ringkasan..."
              className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:bg-white"
            />
          </div>

          <select
            value={selectedCategory}
            onChange={e => setSelectedCategory(e.target.value)}
            className="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-600"
          >
            <option value="all">Semua Segmen ({services.length})</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>
                Segmen {c.segmentNumber}: {c.name}
              </option>
            ))}
          </select>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={handleResetDefaults}
            className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
            title="Kembalikan ke 22 Standar Layanan Default"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Reset Default (22)</span>
          </button>

          <button
            type="button"
            onClick={handleOpenAdd}
            className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Standar Layanan</span>
          </button>
        </div>
      </div>

      {/* Interactive Table of Services Standar */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
          <div className="flex items-center gap-2">
            <Layers className="w-4 h-4 text-blue-600" />
            <h4 className="font-bold text-slate-900 text-xs">
              Tabel Katalog Standar Layanan Terdaftar ({filteredServices.length} Layanan)
            </h4>
          </div>
          <span className="text-[11px] text-slate-500">
            Klik tombol <strong>Edit</strong> untuk mengubah detail atau <strong>Hapus</strong> untuk menghapus layanan dari katalog
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase text-[10px]">
              <tr>
                <th className="py-3 px-4 w-14">Kode</th>
                <th className="py-3 px-4">Nama Standar Layanan & Ringkasan</th>
                <th className="py-3 px-4">Segmen Kategori</th>
                <th className="py-3 px-4">Status Standar</th>
                <th className="py-3 px-4 text-center">Tayang</th>
                <th className="py-3 px-4 text-right">Aksi (Edit / Hapus)</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredServices.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-8 text-center text-slate-400">
                    Tidak ada standar layanan yang sesuai dengan pencarian Anda.
                  </td>
                </tr>
              ) : (
                filteredServices.map((s, idx) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition-colors">
                    <td className="py-3 px-4 font-mono font-bold text-slate-500">#{idx + 1}</td>
                    <td className="py-3 px-4 max-w-md">
                      <div className="font-bold text-slate-900">{s.name}</div>
                      <div className="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{s.summary}</div>
                      {s.brands && s.brands.length > 0 && (
                        <div className="flex flex-wrap gap-1 mt-1">
                          {s.brands.map((b, i) => (
                            <span
                              key={i}
                              className="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-medium"
                            >
                              {b}
                            </span>
                          ))}
                        </div>
                      )}
                    </td>
                    <td className="py-3 px-4 font-semibold text-slate-700">{s.categoryName}</td>
                    <td className="py-3 px-4">
                      <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-md text-[10px] font-bold inline-flex items-center gap-1">
                        <CheckCircle2 className="w-3 h-3 text-emerald-600" />
                        <span>
                          {s.verificationStatus === 'Terverifikasi dari PDF'
                            ? 'Standar Resmi KGS'
                            : s.verificationStatus || 'Standar Resmi KGS'}
                        </span>
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        type="button"
                        onClick={() => handleTogglePublish(s)}
                        className="cursor-pointer inline-flex items-center gap-1 px-2 py-1 rounded-md hover:bg-slate-100 transition"
                        title="Klik untuk mengubah status tayang"
                      >
                        {s.isPublished ? (
                          <span className="inline-flex items-center gap-1 text-emerald-600 font-bold text-[11px]">
                            <Eye className="w-3.5 h-3.5" /> Aktif
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 text-slate-400 font-semibold text-[11px]">
                            <EyeOff className="w-3.5 h-3.5" /> Draft
                          </span>
                        )}
                      </button>
                    </td>
                    <td className="py-3 px-4 text-right">
                      {deleteConfirmId === s.id ? (
                        <div className="inline-flex items-center justify-end gap-1.5 bg-rose-50 border border-rose-200 px-2 py-1 rounded-lg">
                          <span className="text-[10px] font-bold text-rose-800">Yakin hapus?</span>
                          <button
                            type="button"
                            onClick={() => handleConfirmDelete(s.id, s.name)}
                            className="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[10px] font-bold cursor-pointer"
                          >
                            Ya, Hapus
                          </button>
                          <button
                            type="button"
                            onClick={() => setDeleteConfirmId(null)}
                            className="px-2 py-1 bg-white border border-slate-300 text-slate-600 rounded text-[10px] font-semibold cursor-pointer"
                          >
                            Batal
                          </button>
                        </div>
                      ) : (
                        <div className="inline-flex items-center justify-end gap-1.5">
                          <button
                            type="button"
                            onClick={() => handleOpenEdit(s)}
                            className="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer"
                            title="Edit Standar Layanan"
                          >
                            <Edit3 className="w-3.5 h-3.5" />
                            <span>Edit</span>
                          </button>
                          <button
                            type="button"
                            onClick={() => setDeleteConfirmId(s.id)}
                            className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer"
                            title="Hapus Standar Layanan"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                            <span>Hapus</span>
                          </button>
                        </div>
                      )}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Compliance Rule Summary */}
      <div className="bg-white p-6 rounded-2xl border border-slate-200 space-y-4 shadow-xs">
        <h3 className="text-sm font-bold text-slate-900">
          Standarisasi Kualitas & Otorisasi Layanan Resmi PT. Kreatif Global Solusindo
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
          <div className="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-emerald-950">
            <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold block">5 Segmen Solusi Terintegrasi:</span>
              Computer & Networking, Telecommunication, Video Surveillance / CCTV, Internet Service Provider (KGSNet), dan Infrastruktur Pendukung.
            </div>
          </div>

          <div className="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-emerald-950">
            <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold block">Lisensi Resmi ISP KGSNet & APJII:</span>
              Beroperasi di bawah izin penyelenggaraan ISP resmi serta anggota aktif Asosiasi Penyelenggara Jasa Internet Indonesia (APJII).
            </div>
          </div>

          <div className="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-emerald-950">
            <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold block">Workshop Pabrikasi & Grounding &lt; 2 Ohm:</span>
              Pabrikasi mandiri Triangle Guywire Tower serta instalasi penangkal petir dengan uji resistansi Earth Tester terkalibrasi.
            </div>
          </div>

          <div className="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-emerald-950">
            <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold block">Prinsipal & Kemitraan Resmi:</span>
              Sony Authorized Dealer, Panasonic Authorized Dealer, dan Reseller Resmi NETVIEL Fiber Optic.
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
