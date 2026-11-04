import React, { useState } from 'react';
import { ClientItem } from '../../data/companyData';
import { Plus, Trash2, Search, Edit3, X, Upload, ExternalLink, Check } from 'lucide-react';

interface AdminClientsProps {
  clients: ClientItem[];
  onUpdateClients: (clients: ClientItem[]) => void;
}

export const AdminClients: React.FC<AdminClientsProps> = ({ clients, onUpdateClients }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingClient, setEditingClient] = useState<ClientItem | null>(null);
  const [toast, setToast] = useState('');
  const [formData, setFormData] = useState<Partial<ClientItem>>({
    name: '',
    category: '',
    location: '',
    serviceCategory: 'PABX & Billing System',
    imageUrl: '',
    websiteUrl: '',
    pdfPage: 9
  });

  const showToast = (msg: string) => {
    setToast(msg);
    setTimeout(() => setToast(''), 3500);
  };

  const handlePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      if (typeof reader.result === 'string') {
        setFormData(prev => ({
          ...prev,
          imageUrl: reader.result as string,
          logoUrl: reader.result as string
        }));
      }
    };
    reader.readAsDataURL(file);
  };

  const openAddForm = () => {
    setEditingClient(null);
    setFormData({
      name: '',
      category: 'Korporasi & Instansi',
      location: 'Palembang, Sumatera Selatan',
      serviceCategory: 'Dedicated Internet KGSNet & Infrastruktur',
      imageUrl: '',
      websiteUrl: '',
      pdfPage: 9
    });
    setIsFormOpen(true);
  };

  const openEditForm = (client: ClientItem) => {
    setEditingClient(client);
    setFormData({
      name: client.name,
      category: client.category || 'Korporasi & Instansi',
      location: client.location || 'Palembang, Sumatera Selatan',
      serviceCategory: client.serviceCategory,
      imageUrl: client.imageUrl || client.logoUrl || '',
      websiteUrl: client.websiteUrl || '',
      pdfPage: client.pdfPage || 9
    });
    setIsFormOpen(true);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name?.trim()) return;

    if (editingClient) {
      const updated = clients.map(c =>
        c.id === editingClient.id
          ? {
              ...c,
              name: formData.name!.trim(),
              category: (formData.category || 'Korporasi & Instansi').trim(),
              location: (formData.location || 'Palembang').trim(),
              serviceCategory: (formData.serviceCategory || 'Infrastruktur IT & Telekomunikasi').trim(),
              imageUrl: formData.imageUrl?.trim() || undefined,
              logoUrl: formData.imageUrl?.trim() || undefined,
              websiteUrl: formData.websiteUrl?.trim() || undefined
            }
          : c
      );
      onUpdateClients(updated);
      showToast(`Data & foto klien "${formData.name}" berhasil diperbarui!`);
    } else {
      const item: ClientItem = {
        id: `c-custom-${Date.now()}`,
        name: formData.name.trim(),
        category: (formData.category || 'Korporasi & Instansi').trim(),
        location: (formData.location || 'Palembang').trim(),
        serviceCategory: (formData.serviceCategory || 'Infrastruktur IT & Telekomunikasi').trim(),
        imageUrl: formData.imageUrl?.trim() || undefined,
        logoUrl: formData.imageUrl?.trim() || undefined,
        websiteUrl: formData.websiteUrl?.trim() || undefined,
        pdfPage: Number(formData.pdfPage) || 9
      };
      onUpdateClients([item, ...clients]);
      showToast(`Klien baru "${item.name}" berhasil ditambahkan!`);
    }

    setIsFormOpen(false);
    setEditingClient(null);
  };

  const handleDelete = (id: string) => {
    if (confirm('Hapus klien ini dari daftar klien terverifikasi?')) {
      onUpdateClients(clients.filter(c => c.id !== id));
      showToast('Data klien berhasil dihapus.');
    }
  };

  const filtered = clients.filter(
    c =>
      c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      c.serviceCategory.toLowerCase().includes(searchTerm.toLowerCase()) ||
      c.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (c.location && c.location.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
          <span className="text-[11px] font-bold uppercase tracking-wider text-blue-600">
            Manajemen Direktori Klien B2B
          </span>
          <h2 className="text-lg font-extrabold text-slate-900">
            Daftar Klien & Upload Foto / Logo Mitra ({clients.length} Entitas)
          </h2>
          <p className="text-xs text-slate-500 mt-0.5">
            Unggah foto dokumentasi proyek atau logo instansi klien untuk ditampilkan secara profesional di halaman depan.
          </p>
        </div>

        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
          <div className="relative w-full sm:w-64">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Cari nama klien, sektor, solusi..."
              value={searchTerm}
              onChange={e => setSearchTerm(e.target.value)}
              className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600"
            />
          </div>

          <button
            type="button"
            onClick={openAddForm}
            className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 cursor-pointer shrink-0 shadow-xs"
          >
            <Plus className="w-4 h-4" />
            <span>+ Tambah Klien & Upload Foto</span>
          </button>
        </div>
      </div>

      {toast && (
        <div className="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center justify-between">
          <span className="flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-600" />
            {toast}
          </span>
          <button onClick={() => setToast('')} className="cursor-pointer">
            ✕
          </button>
        </div>
      )}

      {isFormOpen && (
        <form
          onSubmit={handleSave}
          className="bg-white p-6 rounded-2xl border-2 border-blue-200 shadow-sm space-y-4 text-xs"
        >
          <div className="flex items-center justify-between border-b border-slate-200 pb-3">
            <div>
              <span className="font-extrabold text-slate-900 text-sm">
                {editingClient ? `Edit Data & Foto Klien: ${editingClient.name}` : 'Tambah Klien B2B Baru'}
              </span>
              <p className="text-[11px] text-slate-500">
                Mendukung upload foto/logo klien (PNG, JPG, WebP) serta link website referensi.
              </p>
            </div>
            <button
              type="button"
              onClick={() => {
                setIsFormOpen(false);
                setEditingClient(null);
              }}
              className="text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Nama Perusahaan / Instansi Klien *
              </label>
              <input
                type="text"
                required
                value={formData.name || ''}
                onChange={e => setFormData({ ...formData, name: e.target.value })}
                className="w-full border border-slate-300 rounded-xl p-2.5 font-bold"
                placeholder="Contoh: PT. Bukit Asam / RSUD"
              />
            </div>
            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Bidang Industri / Sektor
              </label>
              <input
                type="text"
                value={formData.category || ''}
                onChange={e => setFormData({ ...formData, category: e.target.value })}
                className="w-full border border-slate-300 rounded-xl p-2.5"
                placeholder="Contoh: BUMN / Perbankan / Rumah Sakit"
              />
            </div>
            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Solusi Pekerjaan KGS *
              </label>
              <input
                type="text"
                required
                value={formData.serviceCategory || ''}
                onChange={e => setFormData({ ...formData, serviceCategory: e.target.value })}
                className="w-full border border-slate-300 rounded-xl p-2.5"
                placeholder="Contoh: Dedicated Internet / PABX / Tower"
              />
            </div>
            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Lokasi Proyek / Wilayah
              </label>
              <input
                type="text"
                value={formData.location || ''}
                onChange={e => setFormData({ ...formData, location: e.target.value })}
                className="w-full border border-slate-300 rounded-xl p-2.5"
                placeholder="Contoh: Palembang, Sumatera Selatan"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Upload Foto / Logo Klien (Dari Komputer/HP)
              </label>
              <label className="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-dashed border-blue-400 bg-blue-50/50 hover:bg-blue-50 text-blue-700 font-bold cursor-pointer transition">
                <Upload className="w-4 h-4" />
                <span>Pilih File Foto / Logo</span>
                <input
                  type="file"
                  accept="image/*"
                  onChange={handlePhotoUpload}
                  className="hidden"
                />
              </label>
              {formData.imageUrl && (
                <div className="mt-2 flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200">
                  <img
                    src={formData.imageUrl}
                    alt="Preview"
                    className="h-10 w-16 object-contain rounded bg-white border p-0.5"
                  />
                  <button
                    type="button"
                    onClick={() => setFormData({ ...formData, imageUrl: '', logoUrl: '' })}
                    className="text-[11px] text-rose-600 font-bold hover:underline cursor-pointer"
                  >
                    Hapus Foto
                  </button>
                </div>
              )}
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Atau URL Foto / Logo Klien (Opsional)
              </label>
              <input
                type="text"
                value={formData.imageUrl || ''}
                onChange={e =>
                  setFormData({ ...formData, imageUrl: e.target.value, logoUrl: e.target.value })
                }
                className="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                placeholder="https://... atau uploads/portfolio/..."
              />
            </div>

            <div>
              <label className="block font-bold text-slate-700 mb-1">
                Link Website Klien / Referensi (Opsional)
              </label>
              <input
                type="text"
                value={formData.websiteUrl || ''}
                onChange={e => setFormData({ ...formData, websiteUrl: e.target.value })}
                className="w-full border border-slate-300 rounded-xl p-2.5 font-mono"
                placeholder="https://..."
              />
            </div>
          </div>

          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              onClick={() => {
                setIsFormOpen(false);
                setEditingClient(null);
              }}
              className="px-4 py-2 border border-slate-300 rounded-xl font-semibold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer shadow-xs"
            >
              {editingClient ? 'Simpan Perubahan Klien' : 'Simpan Klien Baru'}
            </button>
          </div>
        </form>
      )}

      <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-50 border-b border-slate-200 uppercase font-bold text-[10px] text-slate-500">
              <tr>
                <th className="p-3.5">Foto / Logo</th>
                <th className="p-3.5">Nama Klien / Instansi</th>
                <th className="p-3.5">Bidang Industri</th>
                <th className="p-3.5">Solusi Pekerjaan KGS</th>
                <th className="p-3.5">Lokasi</th>
                <th className="p-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.slice(0, 40).map(c => {
                const img = c.imageUrl || c.logoUrl;
                const cleanName = c.name.replace(/^(PT\.|CV\.|RS\.|RSUD|BANK)\s*/i, '').trim();
                const initials = cleanName.slice(0, 2).toUpperCase() || 'KG';
                return (
                  <tr key={c.id} className="hover:bg-slate-50 transition">
                    <td className="p-3.5 w-20">
                      {img ? (
                        <img
                          src={img}
                          alt={c.name}
                          className="w-14 h-10 object-contain rounded-lg bg-white border border-slate-200 p-1"
                        />
                      ) : (
                        <div className="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-xs flex items-center justify-center">
                          {initials}
                        </div>
                      )}
                    </td>
                    <td className="p-3.5">
                      <div className="font-bold text-slate-900">{c.name}</div>
                      {c.websiteUrl && (
                        <a
                          href={c.websiteUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-[11px] text-blue-600 hover:underline inline-flex items-center gap-1 mt-0.5"
                        >
                          <span>{c.websiteUrl}</span>
                          <ExternalLink className="w-3 h-3" />
                        </a>
                      )}
                    </td>
                    <td className="p-3.5 text-slate-600">{c.category}</td>
                    <td className="p-3.5 text-blue-600 font-semibold">{c.serviceCategory}</td>
                    <td className="p-3.5 text-slate-500">{c.location || 'Sumatera Selatan'}</td>
                    <td className="p-3.5 text-right whitespace-nowrap space-x-1.5">
                      <button
                        type="button"
                        onClick={() => openEditForm(c)}
                        className="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg font-bold inline-flex items-center gap-1 cursor-pointer"
                      >
                        <Edit3 className="w-3.5 h-3.5" />
                        <span>Edit / Foto</span>
                      </button>
                      <button
                        type="button"
                        onClick={() => handleDelete(c.id)}
                        className="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold inline-flex items-center gap-1 cursor-pointer"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                        <span>Hapus</span>
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
        {filtered.length > 40 && (
          <div className="p-3 bg-slate-50 border-t text-center text-xs text-slate-500">
            Menampilkan 40 dari total {filtered.length} klien terverifikasi. Gunakan kolom pencarian di atas untuk memfilter klien spesifik.
          </div>
        )}
      </div>
    </div>
  );
};
