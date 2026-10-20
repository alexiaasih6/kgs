import React, { useState } from 'react';
import { ServiceCategory } from '../../data/companyData';
import { Plus, Check, X, ShieldCheck } from 'lucide-react';

interface AdminCategoriesProps {
  categories: ServiceCategory[];
  onUpdateCategories: (categories: ServiceCategory[]) => void;
  serviceCounts: Record<string, number>;
}

export const AdminCategories: React.FC<AdminCategoriesProps> = ({
  categories,
  onUpdateCategories,
  serviceCounts
}) => {
  const [isAdding, setIsAdding] = useState(false);
  const [newCat, setNewCat] = useState({
    name: '',
    segmentNumber: categories.length + 1,
    description: '',
    pdfPage: 4
  });

  const handleAdd = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newCat.name) return;

    const id = `cat_custom_${Date.now()}`;
    const added: ServiceCategory = {
      id,
      name: newCat.name,
      segmentNumber: Number(newCat.segmentNumber) || categories.length + 1,
      description: newCat.description,
      pdfPage: Number(newCat.pdfPage) || 4
    };

    onUpdateCategories([...categories, added]);
    setIsAdding(false);
    setNewCat({
      name: '',
      segmentNumber: categories.length + 2,
      description: '',
      pdfPage: 4
    });
  };

  const handleDelete = (id: string) => {
    if (confirm('Hapus kategori segmen ini? Layanan yang terkait harus dipindahkan terlebih dahulu.')) {
      onUpdateCategories(categories.filter(c => c.id !== id));
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-slate-900">Kelola 5 Segmen Kategori Layanan</h2>
          <p className="text-xs text-slate-500 mt-1">Struktur resmi kategori layanan utama PT. Kreatif Global Solusindo.</p>
        </div>
        <button
          onClick={() => setIsAdding(true)}
          className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 transition cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Kategori Baru</span>
        </button>
      </div>

      {isAdding && (
        <form onSubmit={handleAdd} className="bg-white p-5 rounded-xl border border-blue-300 shadow-sm space-y-4 text-xs">
          <div className="flex items-center justify-between border-b pb-2">
            <span className="font-bold text-slate-900">Tambah Kategori Baru</span>
            <button type="button" onClick={() => setIsAdding(false)} className="text-slate-400 hover:text-slate-600">
              <X className="w-4 h-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold mb-1">Nama Kategori *</label>
              <input
                type="text"
                required
                value={newCat.name}
                onChange={(e) => setNewCat({ ...newCat, name: e.target.value })}
                className="w-full border rounded-lg p-2"
                placeholder="Contoh: Cloud & Cybersecurity"
              />
            </div>
            <div>
              <label className="block font-semibold mb-1">Nomor Segmen *</label>
              <input
                type="number"
                min={1}
                value={newCat.segmentNumber}
                onChange={(e) => setNewCat({ ...newCat, segmentNumber: Number(e.target.value) })}
                className="w-full border rounded-lg p-2"
              />
            </div>
          </div>
          <div>
            <label className="block font-semibold mb-1">Deskripsi Ringkas</label>
            <textarea
              rows={2}
              value={newCat.description}
              onChange={(e) => setNewCat({ ...newCat, description: e.target.value })}
              className="w-full border rounded-lg p-2"
              placeholder="Deskripsi fungsi kategori..."
            ></textarea>
          </div>
          <div className="flex justify-end gap-2">
            <button type="button" onClick={() => setIsAdding(false)} className="px-3 py-1.5 border rounded-lg">
              Batal
            </button>
            <button type="submit" className="px-4 py-1.5 bg-blue-600 text-white font-semibold rounded-lg">
              Simpan Kategori
            </button>
          </div>
        </form>
      )}

      <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <table className="w-full text-left text-xs text-slate-700">
          <thead className="bg-slate-50 border-b uppercase font-semibold text-[10px] text-slate-500">
            <tr>
              <th className="p-3 text-center w-24">Segmen</th>
              <th className="p-3 w-56">Nama Kategori</th>
              <th className="p-3">Deskripsi</th>
              <th className="p-3 text-center">Layanan Terkait</th>
              <th className="p-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {categories.map((c) => (
              <tr key={c.id} className="hover:bg-slate-50">
                <td className="p-3 text-center font-bold text-blue-700 font-mono">
                  Segmen {c.segmentNumber}
                </td>
                <td className="p-3 font-bold text-slate-900">{c.name}</td>
                <td className="p-3 text-slate-600 leading-relaxed max-w-md">{c.description}</td>
                <td className="p-3 text-center font-bold text-slate-800">
                  {serviceCounts[c.id] || 0} Layanan
                </td>
                <td className="p-3 text-right">
                  {c.segmentNumber > 5 && (
                    <button
                      onClick={() => handleDelete(c.id)}
                      className="text-rose-600 hover:underline cursor-pointer"
                    >
                      Hapus
                    </button>
                  )}
                  {c.segmentNumber <= 5 && (
                    <span className="text-[10px] text-slate-400 font-medium italic">Kategori Utama KGS</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
