import React, { useState, useMemo } from 'react';
import { VERIFIED_CLIENTS, ClientItem } from '../data/companyData';
import { Search, Building, MapPin, ShieldCheck, CheckCircle2 } from 'lucide-react';

export const ClientsSection: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');

  const categories = useMemo(() => {
    const list = Array.from(new Set(VERIFIED_CLIENTS.map(c => c.serviceCategory)));
    return ['all', ...list];
  }, []);

  const filteredClients = useMemo(() => {
    return VERIFIED_CLIENTS.filter(client => {
      const matchCat = selectedCategory === 'all' || client.serviceCategory === selectedCategory;
      const matchSearch = searchTerm === '' ||
        client.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        client.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (client.location && client.location.toLowerCase().includes(searchTerm.toLowerCase()));
      return matchCat && matchSearch;
    });
  }, [searchTerm, selectedCategory]);

  return (
    <section id="klien" className="py-20 bg-white border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
          <div className="max-w-2xl">
            <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">
              Portofolio & Pengalaman Kerja Nyata
            </div>
            <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
              Daftar Klien Berdasarkan Solusi Pekerjaan
            </h2>
            <p className="text-sm text-slate-600 mt-2 leading-relaxed">
              Daftar instansi pemerintah, BUMN, perbankan, perkebunan kelapa sawit, pertambangan batubara, rumah sakit, dan sektor industri yang telah dipercayakan kepada PT. KGS.
            </p>
          </div>

          {/* Search Box */}
          <div className="relative w-full md:w-80">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Cari nama klien atau lokasi..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 transition"
            />
          </div>
        </div>

        {/* Category Filter Buttons */}
        <div className="flex flex-wrap items-center gap-1.5 mb-8">
          <button
            onClick={() => setSelectedCategory('all')}
            className={`px-3 py-1.5 text-xs font-medium rounded-md transition-colors cursor-pointer whitespace-nowrap ${
              selectedCategory === 'all'
                ? 'bg-slate-900 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            }`}
          >
            Semua Solusi ({VERIFIED_CLIENTS.length})
          </button>
          {categories.filter(c => c !== 'all').map((cat) => (
            <button
              key={cat}
              onClick={() => setSelectedCategory(cat)}
              className={`px-3 py-1.5 text-xs font-medium rounded-md transition-colors cursor-pointer whitespace-nowrap ${
                selectedCategory === cat
                  ? 'bg-slate-900 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Clients Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredClients.map((client) => (
            <div
              key={client.id}
              className="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                  <span className="font-medium text-blue-600">{client.serviceCategory}</span>
                  <span className="text-emerald-600 font-medium">✓ Terverifikasi</span>
                </div>
                <h4 className="text-sm font-bold text-slate-900 leading-snug">
                  {client.name}
                </h4>
              </div>

              <div className="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                <span className="text-[11px] text-slate-600">{client.category}</span>
                {client.location && (
                  <span className="flex items-center gap-1 text-[11px] text-slate-500">
                    <MapPin className="w-3 h-3 text-slate-400" />
                    <span>{client.location}</span>
                  </span>
                )}
              </div>
            </div>
          ))}
        </div>

        {filteredClients.length === 0 && (
          <div className="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500">
            Tidak ditemukan klien dengan kata kunci "{searchTerm}". Silakan periksa kembali ejaan pencarian.
          </div>
        )}

      </div>
    </section>
  );
};
