import React, { useState, useMemo } from 'react';
import { PROJECT_DOCUMENTARY, VERIFIED_CLIENTS, ProjectPhoto, ClientItem, StaticPageItem } from '../data/companyData';
import { Camera, MapPin, ShieldCheck, Search, Building2, CheckCircle2, Eye, Edit3 } from 'lucide-react';

interface PortfolioSectionProps {
  onRequestQuote: (projectName?: string) => void;
  projects?: ProjectPhoto[];
  clients?: ClientItem[];
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const PortfolioSection: React.FC<PortfolioSectionProps> = ({
  onRequestQuote,
  projects = PROJECT_DOCUMENTARY,
  clients = VERIFIED_CLIENTS,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [activeTab, setActiveTab] = useState<'projects' | 'clients'>('projects');
  const [searchTerm, setSearchTerm] = useState('');
  const [projectCategory, setProjectCategory] = useState<string>('all');
  const [clientCategory, setClientCategory] = useState<string>('all');
  const [selectedPhoto, setSelectedPhoto] = useState<ProjectPhoto | null>(null);

  // Project categories
  const projectCategories = useMemo(() => {
    const list = Array.from(new Set(projects.map(p => p.category)));
    return ['all', ...list];
  }, [projects]);

  // Client categories
  const clientCategories = useMemo(() => {
    const list = Array.from(new Set(clients.map(c => c.serviceCategory)));
    return ['all', ...list];
  }, [clients]);

  // Filtered projects
  const filteredProjects = useMemo(() => {
    return projects.filter(p => {
      const matchCat = projectCategory === 'all' || p.category === projectCategory;
      const matchSearch = searchTerm === '' ||
        p.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.clientOrLocation.toLowerCase().includes(searchTerm.toLowerCase());
      return matchCat && matchSearch;
    });
  }, [projects, projectCategory, searchTerm]);

  // Filtered clients
  const filteredClients = useMemo(() => {
    return clients.filter(c => {
      const matchCat = clientCategory === 'all' || c.serviceCategory === clientCategory;
      const matchSearch = searchTerm === '' ||
        c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        c.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (c.location && c.location.toLowerCase().includes(searchTerm.toLowerCase()));
      return matchCat && matchSearch;
    });
  }, [clients, clientCategory, searchTerm]);

  return (
    <section id="portofolio" className="py-24 bg-white border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Section Header */}
        <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
          <div className="max-w-3xl">
            <div className="flex items-center gap-2 text-xs font-semibold text-blue-700 uppercase tracking-wider mb-2">
              <span>Portofolio & Pengalaman Lapangan</span>
              <span aria-hidden="true">·</span>
              <span>Rekam Jejak Nyata & Klien Terdaftar</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Portofolio Proyek & Klien Korporasi'}
            </h2>
            <p className="text-base text-slate-600 mt-3 leading-relaxed">
              {pageContent?.summary || 'Bukti nyata dedikasi teknis PT. KGS: dari pabrikasi mandiri tower guywire dan pengujian grounding di bawah 2 Ohm, hingga integrasi jaringan dan sistem keamanan untuk 100+ mitra BUMN dan korporasi.'}
            </p>
            {pageContent?.content && (
              <div className="mt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                {pageContent.content}
              </div>
            )}
            {onOpenAdminSection && isLiveEditMode && (
              <div className="flex flex-wrap gap-2 mt-4">
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('portfolio')}
                  className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>+ Tambah / Edit Foto Proyek di Admin CMS</span>
                </button>
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('clients')}
                  className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>+ Tambah / Edit Daftar Klien di Admin CMS</span>
                </button>
              </div>
            )}
          </div>

          {/* Search Box */}
          <div className="relative w-full lg:w-80">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder={activeTab === 'projects' ? 'Cari dokumentasi proyek...' : 'Cari nama klien / instansi...'}
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white transition"
            />
          </div>
        </div>

        {/* Primary View Switcher: Proyek Lapangan vs Klien Korporasi */}
        <div className="flex items-center gap-3 p-1.5 bg-slate-100 rounded-xl max-w-md mb-8" data-live-nav="true">
          <button
            onClick={() => {
              setActiveTab('projects');
              setSearchTerm('');
            }}
            className={`flex-1 py-2.5 px-4 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer ${
              activeTab === 'projects'
                ? 'bg-white text-blue-700 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <Camera className="w-4 h-4" />
            <span>Dokumentasi Lapangan ({projects.length})</span>
          </button>
          <button
            onClick={() => {
              setActiveTab('clients');
              setSearchTerm('');
            }}
            className={`flex-1 py-2.5 px-4 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer ${
              activeTab === 'clients'
                ? 'bg-white text-blue-700 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <Building2 className="w-4 h-4" />
            <span>Daftar Klien B2B ({clients.length})</span>
          </button>
        </div>

        {/* Tab 1: Project Documentary Grid */}
        {activeTab === 'projects' && (
          <div>
            {/* Category Filter Pills */}
            <div className="flex flex-wrap items-center gap-2 mb-8 text-xs" data-live-nav="true">
              {projectCategories.map((cat) => (
                <button
                  key={cat}
                  onClick={() => setProjectCategory(cat)}
                  className={`px-3.5 py-1.5 rounded-lg font-medium transition-colors cursor-pointer ${
                    projectCategory === cat
                      ? 'bg-slate-900 text-white shadow-xs'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  }`}
                >
                  {cat === 'all' ? `Semua Kategori (${projects.length})` : cat}
                </button>
              ))}
            </div>

            {/* Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredProjects.map((item) => (
                <div
                  key={item.id}
                  className="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group"
                >
                  {/* Card Visual Header or Image */}
                  {item.imageUrl ? (
                    <div className="relative h-48 w-full bg-slate-900 overflow-hidden">
                      <img
                        src={item.imageUrl}
                        alt={item.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                      <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                      <div className="absolute bottom-3 left-4 right-4 text-white">
                        <span className="text-xs uppercase font-bold text-blue-300 bg-blue-950/85 px-2.5 py-0.5 rounded border border-blue-800">
                          {item.category}
                        </span>
                        <h3 className="text-lg font-bold text-white mt-1.5 leading-snug">
                          {item.title}
                        </h3>
                      </div>
                    </div>
                  ) : (
                    <div className="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white relative">
                      <div className="flex items-center justify-between text-xs text-blue-400 font-mono mb-2">
                        <span className="flex items-center gap-1.5 font-sans font-medium">
                          <Camera className="w-3.5 h-3.5" />
                          <span>Dokumentasi Pekerjaan</span>
                        </span>
                        <span className="text-slate-400 text-[11px] font-sans">Spesifikasi Nyata</span>
                      </div>

                      <h3 className="text-base font-bold text-white leading-snug group-hover:text-blue-300 transition-colors">
                        {item.title}
                      </h3>

                      <div className="flex items-center gap-1.5 text-xs text-slate-300 mt-2.5">
                        <MapPin className="w-3.5 h-3.5 text-blue-400 shrink-0" />
                        <span>{item.clientOrLocation}</span>
                      </div>
                    </div>
                  )}

                  {/* Body description */}
                  <div className="p-6 flex-1 flex flex-col justify-between">
                    <p className="text-xs text-slate-600 leading-relaxed mb-6">
                      {item.description}
                    </p>

                    <div className="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                      <span className="text-slate-500 font-medium">{item.category}</span>
                      <button
                        onClick={() => setSelectedPhoto(item)}
                        className="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 cursor-pointer"
                      >
                        <Eye className="w-3.5 h-3.5" />
                        <span>Detail & Catatan</span>
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {filteredProjects.length === 0 && (
              <div className="p-12 text-center text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                Tidak ada dokumentasi proyek yang sesuai dengan kriteria pencarian.
              </div>
            )}
          </div>
        )}

        {/* Tab 2: Verified Corporate Clients */}
        {activeTab === 'clients' && (
          <div>
            {/* Category Filter Pills */}
            <div className="flex flex-wrap items-center gap-2 mb-8 text-xs">
              <button
                onClick={() => setClientCategory('all')}
                className={`px-3.5 py-1.5 rounded-lg font-medium transition-colors cursor-pointer ${
                  clientCategory === 'all'
                    ? 'bg-slate-900 text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                Semua Klien ({clients.length})
              </button>
              {clientCategories.filter(c => c !== 'all').map((cat) => (
                <button
                  key={cat}
                  onClick={() => setClientCategory(cat)}
                  className={`px-3.5 py-1.5 rounded-lg font-medium transition-colors cursor-pointer ${
                    clientCategory === cat
                      ? 'bg-slate-900 text-white shadow-xs'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  }`}
                >
                  {cat}
                </button>
              ))}
            </div>

            {/* Clients Cards Table / Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {filteredClients.map((client) => (
                <div
                  key={client.id}
                  className="p-5 rounded-xl border border-slate-200 bg-white hover:border-blue-300 hover:shadow-xs transition flex flex-col justify-between"
                >
                  <div>
                    <div className="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                      <span className="font-semibold text-blue-600">{client.category}</span>
                      <span className="text-slate-400">Mitra Resmi</span>
                    </div>
                    <h4 className="text-sm font-bold text-slate-900 leading-snug">
                      {client.name}
                    </h4>
                    {client.location && (
                      <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                        <MapPin className="w-3 h-3 text-slate-400 shrink-0" />
                        <span>{client.location}</span>
                      </div>
                    )}
                  </div>

                  <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span className="text-slate-600 font-medium">
                      Solusi: {client.serviceCategory}
                    </span>
                    <span className="text-emerald-600 font-medium flex items-center gap-0.5">
                      <CheckCircle2 className="w-3.5 h-3.5" />
                      <span>Terverifikasi</span>
                    </span>
                  </div>
                </div>
              ))}
            </div>

            {filteredClients.length === 0 && (
              <div className="p-12 text-center text-slate-500 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                Tidak ada data klien yang cocok dengan pencarian "{searchTerm}".
              </div>
            )}
          </div>
        )}

        {/* Modal for Project Detail */}
        {selectedPhoto && (
          <div className="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
              <div className="p-6 bg-slate-900 text-white">
                <div className="flex items-center justify-between text-xs text-blue-400 mb-2">
                  <span>Dokumentasi Pelaksanaan Proyek</span>
                  <span className="text-emerald-400 font-semibold">Standar KGS</span>
                </div>
                <h3 className="text-lg font-bold">{selectedPhoto.title}</h3>
                <div className="flex items-center gap-1.5 text-xs text-slate-300 mt-1">
                  <MapPin className="w-3.5 h-3.5 text-blue-400" />
                  <span>{selectedPhoto.clientOrLocation}</span>
                </div>
              </div>

              <div className="p-6 space-y-4">
                <div>
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
                    Deskripsi Pekerjaan
                  </label>
                  <p className="text-xs sm:text-sm text-slate-700 leading-relaxed">
                    {selectedPhoto.description}
                  </p>
                </div>

                <div className="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 space-y-1">
                  <div className="font-bold flex items-center gap-1">
                    <ShieldCheck className="w-4 h-4 text-blue-600" />
                    <span>Standar Pelaksanaan Proyek PT. KGS:</span>
                  </div>
                  <div>
                    Pengerjaan dilakukan oleh tim teknisi internal berpengalaman dengan prosedur keselamatan kerja K3 dan peralatan bersertifikasi kalibrasi resmi.
                  </div>
                </div>

                <div className="pt-2 flex items-center justify-end gap-3">
                  <button
                    onClick={() => setSelectedPhoto(null)}
                    className="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 cursor-pointer"
                  >
                    Tutup
                  </button>
                  <button
                    onClick={() => {
                      const title = selectedPhoto.title;
                      setSelectedPhoto(null);
                      onRequestQuote(`Kebutuhan seperti proyek: ${title}`);
                    }}
                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold cursor-pointer"
                  >
                    Minta Penawaran Serupa
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}

      </div>
    </section>
  );
};
