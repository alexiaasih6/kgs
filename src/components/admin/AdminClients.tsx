import React, { useState } from 'react';
import { ClientItem } from '../../data/companyData';
import { Plus, Trash2, Search, Edit3, X, MapPin } from 'lucide-react';

interface AdminClientsProps {
  clients: ClientItem[];
  onUpdateClients: (clients: ClientItem[]) => void;
}

export const AdminClients: React.FC<AdminClientsProps> = ({ clients, onUpdateClients }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [isAdding, setIsAdding] = useState(false);
  const [newClient, setNewClient] = useState<Partial<ClientItem>>({
    name: '',
    category: '',
    location: '',
    serviceCategory: 'PABX & Billing System',
    pdfPage: 9
  });

  const handleAdd = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newClient.name) return;

    const item: ClientItem = {
      id: `c-custom-${Date.now()}`,
      name: newClient.name,
      category: newClient.category || 'Perusahaan',
      location: newClient.location || 'Palembang',
      serviceCategory: newClient.serviceCategory || 'PABX & Billing System',
      pdfPage: Number(newClient.pdfPage) || 9
    };

    onUpdateClients([item, ...clients]);
    setIsAdding(false);
    setNewClient({
      name: '',
      category: '',
      location: '',
      serviceCategory: 'PABX & Billing System',
      pdfPage: 9
    });
  };

  const handleDelete = (id: string) => {
    if (confirm('Hapus klien ini dari database portofolio?')) {
      onUpdateClients(clients.filter(c => c.id !== id));
    }
  };

  const filtered = clients.filter(c =>
    c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    c.serviceCategory.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (c.location && c.location.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari nama klien atau solusi..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600"
          />
        </div>

        <button
          onClick={() => setIsAdding(true)}
          className="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg flex items-center justify-center gap-2 cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Klien Proyek</span>
        </button>
      </div>

      {isAdding && (
        <form onSubmit={handleAdd} className="bg-white p-5 rounded-xl border border-blue-300 shadow-sm space-y-4 text-xs">
          <div className="flex items-center justify-between border-b pb-2">
            <span className="font-bold text-slate-900">Tambah Klien Baru</span>
            <button type="button" onClick={() => setIsAdding(false)} className="text-slate-400 hover:text-slate-600">
              <X className="w-4 h-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold mb-1">Nama Perusahaan / Klien *</label>
              <input
                type="text"
                required
                value={newClient.name}
                onChange={(e) => setNewClient({ ...newClient, name: e.target.value })}
                className="w-full border rounded-lg p-2"
                placeholder="Contoh: PT. Sawit Sejahtera"
              />
            </div>
            <div>
              <label className="block font-semibold mb-1">Kategori Industri</label>
              <input
                type="text"
                value={newClient.category}
                onChange={(e) => setNewClient({ ...newClient, category: e.target.value })}
                className="w-full border rounded-lg p-2"
                placeholder="Contoh: Perkebunan Sawit"
              />
            </div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold mb-1">Solusi Pekerjaan</label>
              <input
                type="text"
                value={newClient.serviceCategory}
                onChange={(e) => setNewClient({ ...newClient, serviceCategory: e.target.value })}
                className="w-full border rounded-lg p-2"
                placeholder="Contoh: Radiolink & Radio Komunikasi"
              />
            </div>
            <div>
              <label className="block font-semibold mb-1">Lokasi Proyek</label>
              <input
                type="text"
                value={newClient.location}
                onChange={(e) => setNewClient({ ...newClient, location: e.target.value })}
                className="w-full border rounded-lg p-2"
                placeholder="Contoh: Musi Banyuasin"
              />
            </div>
          </div>
          <div className="flex justify-end gap-2 pt-2">
            <button type="button" onClick={() => setIsAdding(false)} className="px-3 py-1.5 border rounded-lg">
              Batal
            </button>
            <button type="submit" className="px-4 py-1.5 bg-blue-600 text-white font-semibold rounded-lg">
              Simpan Klien
            </button>
          </div>
        </form>
      )}

      <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table className="w-full text-left text-xs text-slate-700">
          <thead className="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-[10px] text-slate-500">
            <tr>
              <th className="p-3">Nama Klien</th>
              <th className="p-3">Kategori Bisnis</th>
              <th className="p-3">Solusi Pekerjaan</th>
              <th className="p-3">Lokasi</th>
              <th className="p-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {filtered.slice(0, 30).map((c) => (
              <tr key={c.id} className="hover:bg-slate-50">
                <td className="p-3 font-bold text-slate-900">{c.name}</td>
                <td className="p-3 text-slate-600">{c.category}</td>
                <td className="p-3 text-blue-600 font-medium">{c.serviceCategory}</td>
                <td className="p-3 text-slate-500">{c.location || '-'}</td>
                <td className="p-3 text-right">
                  <button
                    onClick={() => handleDelete(c.id)}
                    className="p-1 text-rose-500 hover:text-rose-700 cursor-pointer"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {filtered.length > 30 && (
          <div className="p-3 bg-slate-50 border-t text-center text-xs text-slate-500">
            Menampilkan 30 dari total {filtered.length} klien terverifikasi. Gunakan kolom pencarian di atas untuk melihat klien spesifik.
          </div>
        )}
      </div>
    </div>
  );
};
