import React, { useState } from 'react';
import { ServiceItem, SERVICE_CATEGORIES, SERVICES_DATA } from '../../data/companyData';
import { Plus, Edit3, Trash2, Eye, EyeOff, Check, X, Search, RotateCcw } from 'lucide-react';

interface AdminServicesProps {
  services: ServiceItem[];
  onUpdateServices: (services: ServiceItem[]) => void;
}

export const AdminServices: React.FC<AdminServicesProps> = ({ services, onUpdateServices }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [editingService, setEditingService] = useState<ServiceItem | null>(null);
  const [isAddingNew, setIsAddingNew] = useState(false);
  const [deleteConfirmId, setDeleteConfirmId] = useState<string | null>(null);

  const [formData, setFormData] = useState<Partial<ServiceItem>>({
    name: '',
    categoryId: SERVICE_CATEGORIES[0].id,
    summary: '',
    description: '',
    features: [],
    brands: [],
    pdfPage: 4,
    verificationStatus: 'Standar Resmi KGS',
    adminNotes: '',
    isPublished: true,
    order: services.length + 1
  });

  const handleEditClick = (srv: ServiceItem) => {
    setEditingService(srv);
    setFormData({
      ...srv,
      verificationStatus:
        srv.verificationStatus === 'Terverifikasi dari PDF'
          ? 'Standar Resmi KGS'
          : srv.verificationStatus || 'Standar Resmi KGS'
    });
    setIsAddingNew(false);
  };

  const handleAddNewClick = () => {
    setEditingService(null);
    setFormData({
      name: '',
      categoryId: SERVICE_CATEGORIES[0].id,
      summary: '',
      description: '',
      features: ['Instalasi terstandar'],
      brands: [],
      pdfPage: 4,
      verificationStatus: 'Standar Resmi KGS',
      adminNotes: 'Standar Layanan Resmi PT. KGS',
      isPublished: true,
      order: services.length + 1
    });
    setIsAddingNew(true);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.summary) return;

    const catObj = SERVICE_CATEGORIES.find(c => c.id === formData.categoryId) || SERVICE_CATEGORIES[0];

    if (isAddingNew) {
      const newSrv: ServiceItem = {
        id: `srv-custom-${Date.now()}`,
        categoryId: formData.categoryId || catObj.id,
        categoryName: catObj.name,
        name: formData.name || '',
        slug: (formData.name || '').toLowerCase().replace(/[^a-z0-9]+/g, '-'),
        summary: formData.summary || '',
        description: formData.description || formData.summary || '',
        features: formData.features || [],
        brands: formData.brands || [],
        targetSectors: formData.targetSectors || ['Korporasi', 'Industri'],
        pdfPage: Number(formData.pdfPage) || 4,
        verificationStatus: (formData.verificationStatus as any) || 'Standar Resmi KGS',
        adminNotes: formData.adminNotes || 'Standar Layanan Resmi PT. KGS',
        isPublished: formData.isPublished ?? true,
        order: Number(formData.order) || services.length + 1
      };
      onUpdateServices([...services, newSrv]);
      setIsAddingNew(false);
    } else if (editingService) {
      const updated = services.map(s => {
        if (s.id === editingService.id) {
          return {
            ...s,
            name: formData.name || s.name,
            categoryId: formData.categoryId || s.categoryId,
            categoryName: catObj.name,
            summary: formData.summary || s.summary,
            description: formData.description || s.description,
            features: formData.features || s.features,
            brands: formData.brands || s.brands,
            pdfPage: Number(formData.pdfPage) || s.pdfPage,
            verificationStatus: (formData.verificationStatus as any) || s.verificationStatus,
            adminNotes: formData.adminNotes || s.adminNotes,
            isPublished: formData.isPublished ?? s.isPublished,
            order: Number(formData.order) || s.order
          };
        }
        return s;
      });
      onUpdateServices(updated);
      setEditingService(null);
    }
  };

  const handleConfirmDelete = (id: string) => {
    onUpdateServices(services.filter(s => s.id !== id));
    setDeleteConfirmId(null);
    if (editingService?.id === id) {
      setEditingService(null);
    }
  };

  const handleTogglePublish = (id: string) => {
    onUpdateServices(
      services.map(s => (s.id === id ? { ...s, isPublished: !s.isPublished } : s))
    );
  };

  const filtered = services.filter(
    s =>
      s.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.categoryName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.summary.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      {/* Top Action Bar */}
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari layanan atau kategori..."
            value={searchTerm}
            onChange={e => setSearchTerm(e.target.value)}
            className="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600"
          />
        </div>

        <div className="flex items-center gap-2 w-full sm:w-auto">
          <button
            type="button"
            onClick={() => onUpdateServices(SERVICES_DATA)}
            className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer transition"
            title="Kembalikan 22 Layanan Standar Default"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Reset Default</span>
          </button>

          <button
            onClick={handleAddNewClick}
            className="flex-1 sm:flex-initial px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-2 cursor-pointer shadow-xs transition"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Layanan Baru</span>
          </button>
        </div>
      </div>

      {/* Edit / Add Modal or Form */}
      {(isAddingNew || editingService) && (
        <div className="bg-white border-2 border-blue-500 rounded-2xl p-6 shadow-lg">
          <div className="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <h3 className="text-sm font-bold text-slate-900">
              {isAddingNew ? 'Tambah Layanan Baru' : `Edit Layanan: ${editingService?.name}`}
            </h3>
            <button
              onClick={() => {
                setIsAddingNew(false);
                setEditingService(null);
              }}
              className="text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <form onSubmit={handleSave} className="space-y-4 text-xs">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Nama Layanan *</label>
                <input
                  type="text"
                  required
                  value={formData.name || ''}
                  onChange={e => setFormData({ ...formData, name: e.target.value })}
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Kategori Segmen Layanan *</label>
                <select
                  value={formData.categoryId || ''}
                  onChange={e => setFormData({ ...formData, categoryId: e.target.value })}
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                >
                  {SERVICE_CATEGORIES.map(c => (
                    <option key={c.id} value={c.id}>
                      Segmen {c.segmentNumber}: {c.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Nomor Urut Katalog *</label>
                <input
                  type="number"
                  min={1}
                  value={formData.order || 1}
                  onChange={e => setFormData({ ...formData, order: Number(e.target.value) })}
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Status Standarisasi *</label>
                <select
                  value={formData.verificationStatus || 'Standar Resmi KGS'}
                  onChange={e =>
                    setFormData({ ...formData, verificationStatus: e.target.value as any })
                  }
                  className="w-full border border-slate-300 rounded-lg p-2.5 font-medium"
                >
                  <option value="Standar Resmi KGS">Standar Resmi KGS</option>
                  <option value="Perlu Konfirmasi">Perlu Konfirmasi</option>
                  <option value="Tambahan Disetujui Admin">Tambahan Disetujui Admin</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Status Publikasi</label>
                <div className="flex items-center gap-4 mt-2">
                  <label className="flex items-center gap-1.5 cursor-pointer">
                    <input
                      type="radio"
                      name="isPub"
                      checked={formData.isPublished === true}
                      onChange={() => setFormData({ ...formData, isPublished: true })}
                    />
                    <span>Terbitkan (Publik)</span>
                  </label>
                  <label className="flex items-center gap-1.5 cursor-pointer">
                    <input
                      type="radio"
                      name="isPub"
                      checked={formData.isPublished === false}
                      onChange={() => setFormData({ ...formData, isPublished: false })}
                    />
                    <span>Sembunyikan (Draft)</span>
                  </label>
                </div>
              </div>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Ringkasan Layanan *</label>
              <textarea
                rows={2}
                required
                value={formData.summary || ''}
                onChange={e => setFormData({ ...formData, summary: e.target.value })}
                className="w-full border border-slate-300 rounded-lg p-2.5"
              ></textarea>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Deskripsi Lengkap</label>
              <textarea
                rows={3}
                value={formData.description || ''}
                onChange={e => setFormData({ ...formData, description: e.target.value })}
                className="w-full border border-slate-300 rounded-lg p-2.5"
              ></textarea>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Catatan Teknis Internal</label>
              <input
                type="text"
                value={formData.adminNotes || ''}
                onChange={e => setFormData({ ...formData, adminNotes: e.target.value })}
                placeholder="Contoh: Standar Layanan Resmi PT. KGS"
                className="w-full border border-slate-300 rounded-lg p-2.5"
              />
            </div>

            <div className="flex justify-end gap-3 pt-3 border-t border-slate-200">
              <button
                type="button"
                onClick={() => {
                  setIsAddingNew(false);
                  setEditingService(null);
                }}
                className="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg cursor-pointer flex items-center gap-1.5"
              >
                <Check className="w-4 h-4" />
                <span>Simpan Perubahan</span>
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Services Table */}
      <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-[10px] text-slate-500">
              <tr>
                <th className="p-3.5">Nama Layanan</th>
                <th className="p-3.5">Kategori Segmen</th>
                <th className="p-3.5">Status Standar</th>
                <th className="p-3.5 text-center">Publikasi</th>
                <th className="p-3.5 text-right">Aksi (Edit / Hapus)</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.map(srv => (
                <tr key={srv.id} className="hover:bg-slate-50 transition-colors">
                  <td className="p-3.5">
                    <div className="font-bold text-slate-900">{srv.name}</div>
                    <div className="text-[11px] text-slate-500 truncate max-w-sm">{srv.summary}</div>
                  </td>
                  <td className="p-3.5 text-slate-600 font-medium">{srv.categoryName}</td>
                  <td className="p-3.5">
                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                        srv.verificationStatus === 'Terverifikasi dari PDF' ||
                        srv.verificationStatus === 'Standar Resmi KGS'
                          ? 'bg-emerald-100 text-emerald-800'
                          : srv.verificationStatus === 'Perlu Konfirmasi'
                          ? 'bg-amber-100 text-amber-800'
                          : 'bg-blue-100 text-blue-800'
                      }`}
                    >
                      {srv.verificationStatus === 'Terverifikasi dari PDF'
                        ? 'Standar Resmi KGS'
                        : srv.verificationStatus}
                    </span>
                  </td>
                  <td className="p-3.5 text-center">
                    <button
                      onClick={() => handleTogglePublish(srv.id)}
                      className="cursor-pointer"
                      title={
                        srv.isPublished
                          ? 'Terbit (Klik untuk sembunyikan)'
                          : 'Draft (Klik untuk terbitkan)'
                      }
                    >
                      {srv.isPublished ? (
                        <span className="inline-flex items-center gap-1 text-emerald-600 font-semibold text-[11px]">
                          <Eye className="w-3.5 h-3.5" /> Terbit
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1 text-slate-400 font-semibold text-[11px]">
                          <EyeOff className="w-3.5 h-3.5" /> Draft
                        </span>
                      )}
                    </button>
                  </td>
                  <td className="p-3.5 text-right">
                    {deleteConfirmId === srv.id ? (
                      <div className="inline-flex items-center justify-end gap-1.5 bg-rose-50 border border-rose-200 px-2 py-1 rounded-lg">
                        <span className="text-[10px] font-bold text-rose-800">Hapus?</span>
                        <button
                          type="button"
                          onClick={() => handleConfirmDelete(srv.id)}
                          className="px-2 py-0.5 bg-rose-600 hover:bg-rose-700 text-white rounded text-[10px] font-bold cursor-pointer"
                        >
                          Ya
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteConfirmId(null)}
                          className="px-2 py-0.5 bg-white border border-slate-300 text-slate-600 rounded text-[10px] cursor-pointer"
                        >
                          Batal
                        </button>
                      </div>
                    ) : (
                      <div className="flex items-center justify-end gap-1.5">
                        <button
                          onClick={() => handleEditClick(srv)}
                          className="px-2.5 py-1.5 text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg font-semibold text-[11px] inline-flex items-center gap-1 cursor-pointer"
                          title="Edit Layanan"
                        >
                          <Edit3 className="w-3.5 h-3.5" />
                          <span>Edit</span>
                        </button>
                        <button
                          onClick={() => setDeleteConfirmId(srv.id)}
                          className="px-2.5 py-1.5 text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg font-semibold text-[11px] inline-flex items-center gap-1 cursor-pointer"
                          title="Hapus Layanan"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          <span>Hapus</span>
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
