import React, { useState } from 'react';
import { ProjectPhoto } from '../../data/companyData';
import { Camera, Plus, Trash2, MapPin, Search, X, Edit3, Check, ShieldCheck, Upload, Image as ImageIcon } from 'lucide-react';

interface AdminPortfolioProps {
  projects: ProjectPhoto[];
  onUpdateProjects: (projects: ProjectPhoto[]) => void;
}

export const AdminPortfolio: React.FC<AdminPortfolioProps> = ({ projects, onUpdateProjects }) => {
  const [search, setSearch] = useState('');
  const [isCreating, setIsCreating] = useState(false);
  const [editingProject, setEditingProject] = useState<ProjectPhoto | null>(null);

  // Form state for creating
  const [newTitle, setNewTitle] = useState('');
  const [newClient, setNewClient] = useState('');
  const [newCategory, setNewCategory] = useState('Fabrikasi Tower');
  const [newDesc, setNewDesc] = useState('');
  const [newImageUrl, setNewImageUrl] = useState('');

  // Form state for editing
  const [editTitle, setEditTitle] = useState('');
  const [editClient, setEditClient] = useState('');
  const [editCategory, setEditCategory] = useState('');
  const [editDesc, setEditDesc] = useState('');
  const [editImageUrl, setEditImageUrl] = useState('');

  const filtered = projects.filter(
    p =>
      p.title.toLowerCase().includes(search.toLowerCase()) ||
      p.clientOrLocation.toLowerCase().includes(search.toLowerCase()) ||
      p.category.toLowerCase().includes(search.toLowerCase())
  );

  const handleOpenEdit = (p: ProjectPhoto) => {
    setEditingProject(p);
    setEditTitle(p.title);
    setEditClient(p.clientOrLocation);
    setEditCategory(p.category);
    setEditDesc(p.description);
    setEditImageUrl(p.imageUrl || '');
  };

  const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>, isEdit: boolean) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      alert('File harus berupa berkas gambar (JPG, PNG, WebP)!');
      return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
      const result = event.target?.result as string;
      if (isEdit) {
        setEditImageUrl(result);
      } else {
        setNewImageUrl(result);
      }
    };
    reader.readAsDataURL(file);
  };

  const handleSaveEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingProject || !editTitle || !editClient) return;

    const updatedProjects = projects.map(p => {
      if (p.id === editingProject.id) {
        return {
          ...p,
          title: editTitle,
          clientOrLocation: editClient,
          category: editCategory,
          description: editDesc,
          imageUrl: editImageUrl || undefined
        };
      }
      return p;
    });

    onUpdateProjects(updatedProjects);
    setEditingProject(null);
  };

  const handleAdd = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTitle || !newClient) return;

    const newProj: ProjectPhoto = {
      id: `proj-${Date.now()}`,
      title: newTitle,
      clientOrLocation: newClient,
      category: newCategory,
      description: newDesc,
      imageUrl: newImageUrl || undefined
    };

    onUpdateProjects([newProj, ...projects]);
    setIsCreating(false);
    setNewTitle('');
    setNewClient('');
    setNewDesc('');
    setNewImageUrl('');
  };

  const handleDelete = (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus dokumentasi proyek ini?')) {
      onUpdateProjects(projects.filter(p => p.id !== id));
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">Kelola & Edit Dokumentasi Proyek</h2>
          <p className="text-xs text-slate-500">
            Kelola, tambah baru, dan edit informasi pekerjaan teknis lapangan, lokasi, kategori, serta foto dokumentasi proyek.
          </p>
        </div>
        <button
          onClick={() => {
            setNewTitle('');
            setNewClient('');
            setNewCategory('Fabrikasi Tower');
            setNewDesc('');
            setNewImageUrl('');
            setIsCreating(true);
          }}
          className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5 shadow-xs transition cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Dokumentasi Proyek</span>
        </button>
      </div>

      <div className="bg-white p-4 rounded-xl border border-slate-200">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
          <input
            type="text"
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari judul pekerjaan, lokasi proyek, atau kategori..."
            className="w-full text-xs pl-9 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filtered.map(p => (
          <div
            key={p.id}
            className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all group"
          >
            <div>
              {p.imageUrl ? (
                <div className="h-40 w-full bg-slate-900 overflow-hidden relative">
                  <img
                    src={p.imageUrl}
                    alt={p.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <span className="absolute top-3 left-3 text-[10px] font-bold uppercase text-white bg-blue-600/90 px-2 py-0.5 rounded shadow">
                    {p.category}
                  </span>
                </div>
              ) : (
                <div className="p-4 bg-gradient-to-r from-slate-900 to-blue-950 text-white">
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] font-bold uppercase text-blue-300 bg-blue-900/60 px-2 py-0.5 rounded border border-blue-800">
                      {p.category}
                    </span>
                    <span className="text-[10px] text-emerald-400 font-semibold flex items-center gap-1">
                      <ShieldCheck className="w-3.5 h-3.5" />
                      <span>Aktif</span>
                    </span>
                  </div>
                </div>
              )}

              <div className="p-5">
                <h3 className="font-extrabold text-slate-900 text-sm leading-snug">{p.title}</h3>
                <p className="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                  <MapPin className="w-3.5 h-3.5 text-blue-500 shrink-0" />
                  <span>{p.clientOrLocation}</span>
                </p>
                <p className="text-xs text-slate-600 mt-3 leading-relaxed line-clamp-3">{p.description}</p>
              </div>
            </div>

            <div className="p-4 border-t border-slate-100 flex items-center justify-between text-xs bg-slate-50/50">
              <button
                onClick={() => handleOpenEdit(p)}
                className="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 transition cursor-pointer"
              >
                <Edit3 className="w-3.5 h-3.5" />
                <span>Edit Proyek</span>
              </button>

              <button
                onClick={() => handleDelete(p.id)}
                className="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1 cursor-pointer p-1"
              >
                <Trash2 className="w-3.5 h-3.5" />
                <span>Hapus</span>
              </button>
            </div>
          </div>
        ))}
      </div>

      {filtered.length === 0 && (
        <div className="p-12 text-center text-slate-500 text-xs bg-white rounded-2xl border border-dashed border-slate-200">
          Tidak ada data dokumentasi proyek yang sesuai dengan kata kunci pencarian.
        </div>
      )}

      {/* Modal Edit Proyek */}
      {editingProject && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs animate-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b pb-3">
              <div>
                <h3 className="text-base font-bold text-slate-900">Edit Dokumentasi Proyek</h3>
                <p className="text-slate-500 text-[11px]">Perbarui informasi pekerjaan teknis, lokasi, kategori, dan foto.</p>
              </div>
              <button onClick={() => setEditingProject(null)} className="text-slate-400 hover:text-slate-700 cursor-pointer">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveEdit} className="space-y-4">
              <div>
                <label className="block font-bold mb-1 text-slate-700">Judul Pekerjaan / Proyek *</label>
                <input
                  type="text"
                  required
                  value={editTitle}
                  onChange={e => setEditTitle(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 font-semibold text-slate-900"
                />
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Klien / Lokasi Proyek *</label>
                <input
                  type="text"
                  required
                  value={editClient}
                  onChange={e => setEditClient(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Kategori Layanan *</label>
                <select
                  value={editCategory}
                  onChange={e => setEditCategory(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 bg-white"
                >
                  <option value="Fabrikasi Tower">Fabrikasi Tower</option>
                  <option value="Sistem Grounding">Sistem Grounding & Penangkal Petir</option>
                  <option value="Surveillance & CCTV">Surveillance & CCTV</option>
                  <option value="Solar Cell & Power">Solar Cell & Power</option>
                  <option value="Radio Komunikasi">Radio Komunikasi</option>
                  <option value="Sistem Maritim / AIS">Sistem Maritim / AIS</option>
                  <option value="Networking & Fiber Optic">Networking & Fiber Optic</option>
                </select>
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Deskripsi Teknis Pekerjaan *</label>
                <textarea
                  rows={4}
                  required
                  value={editDesc}
                  onChange={e => setEditDesc(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 leading-relaxed"
                />
              </div>

              {/* Upload Foto Dokumentasi */}
              <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                <label className="block font-bold text-slate-800">Foto / Gambar Dokumentasi Proyek</label>
                {editImageUrl && (
                  <div className="h-32 w-full rounded-lg overflow-hidden border border-slate-300 relative mb-2">
                    <img src={editImageUrl} alt="Preview" className="w-full h-full object-cover" />
                    <button
                      type="button"
                      onClick={() => setEditImageUrl('')}
                      className="absolute top-2 right-2 p-1 bg-rose-600 text-white rounded text-xs cursor-pointer shadow"
                    >
                      Hapus Foto
                    </button>
                  </div>
                )}
                <div className="flex items-center gap-2">
                  <label className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-xs cursor-pointer flex items-center gap-1.5 transition">
                    <Upload className="w-3.5 h-3.5" />
                    <span>Upload Foto</span>
                    <input
                      type="file"
                      accept="image/*"
                      onChange={e => handleImageUpload(e, true)}
                      className="hidden"
                    />
                  </label>
                  <input
                    type="url"
                    placeholder="Atau masukkan URL gambar..."
                    value={editImageUrl.startsWith('data:') ? '' : editImageUrl}
                    onChange={e => setEditImageUrl(e.target.value)}
                    className="flex-1 border border-slate-300 rounded-lg p-2 text-xs"
                  />
                </div>
              </div>

              <div className="pt-3 border-t flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setEditingProject(null)}
                  className="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm cursor-pointer flex items-center gap-1.5"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Perubahan</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Tambah Proyek */}
      {isCreating && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs animate-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b pb-3">
              <h3 className="text-base font-bold text-slate-900">Tambah Dokumentasi Proyek Baru</h3>
              <button onClick={() => setIsCreating(false)} className="text-slate-400 hover:text-slate-700 cursor-pointer">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleAdd} className="space-y-4">
              <div>
                <label className="block font-bold mb-1 text-slate-700">Judul Pekerjaan *</label>
                <input
                  type="text"
                  required
                  value={newTitle}
                  onChange={e => setNewTitle(e.target.value)}
                  placeholder="Contoh: Instalasi Tower Guywire 42m & Solar Cell"
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Klien / Lokasi Lapangan *</label>
                <input
                  type="text"
                  required
                  value={newClient}
                  onChange={e => setNewClient(e.target.value)}
                  placeholder="Contoh: PT. Semen Tonasa / Pangkep"
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Kategori Pekerjaan *</label>
                <select
                  value={newCategory}
                  onChange={e => setNewCategory(e.target.value)}
                  className="w-full border border-slate-300 rounded-lg p-2.5 bg-white"
                >
                  <option value="Fabrikasi Tower">Fabrikasi Tower</option>
                  <option value="Sistem Grounding">Sistem Grounding & Penangkal Petir</option>
                  <option value="Surveillance & CCTV">Surveillance & CCTV</option>
                  <option value="Solar Cell & Power">Solar Cell & Power</option>
                  <option value="Radio Komunikasi">Radio Komunikasi</option>
                  <option value="Sistem Maritim / AIS">Sistem Maritim / AIS</option>
                  <option value="Networking & Fiber Optic">Networking & Fiber Optic</option>
                </select>
              </div>

              <div>
                <label className="block font-bold mb-1 text-slate-700">Deskripsi Teknis Pekerjaan *</label>
                <textarea
                  rows={3}
                  required
                  value={newDesc}
                  onChange={e => setNewDesc(e.target.value)}
                  placeholder="Jelaskan spesifikasi teknis, standar pengerjaan, atau alat uji kalibrasi..."
                  className="w-full border border-slate-300 rounded-lg p-2.5"
                />
              </div>

              {/* Upload Foto Dokumentasi Baru */}
              <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                <label className="block font-bold text-slate-800">Foto / Gambar Dokumentasi (Opsional)</label>
                {newImageUrl && (
                  <div className="h-32 w-full rounded-lg overflow-hidden border border-slate-300 relative mb-2">
                    <img src={newImageUrl} alt="Preview" className="w-full h-full object-cover" />
                    <button
                      type="button"
                      onClick={() => setNewImageUrl('')}
                      className="absolute top-2 right-2 p-1 bg-rose-600 text-white rounded text-xs cursor-pointer shadow"
                    >
                      Hapus Foto
                    </button>
                  </div>
                )}
                <div className="flex items-center gap-2">
                  <label className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-xs cursor-pointer flex items-center gap-1.5 transition">
                    <Upload className="w-3.5 h-3.5" />
                    <span>Upload Foto</span>
                    <input
                      type="file"
                      accept="image/*"
                      onChange={e => handleImageUpload(e, false)}
                      className="hidden"
                    />
                  </label>
                  <input
                    type="url"
                    placeholder="Atau masukkan URL gambar..."
                    value={newImageUrl.startsWith('data:') ? '' : newImageUrl}
                    onChange={e => setNewImageUrl(e.target.value)}
                    className="flex-1 border border-slate-300 rounded-lg p-2 text-xs"
                  />
                </div>
              </div>

              <div className="pt-3 border-t flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setIsCreating(false)}
                  className="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm cursor-pointer"
                >
                  Tambah Proyek
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
