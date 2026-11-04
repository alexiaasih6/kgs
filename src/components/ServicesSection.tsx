import React, { useState } from 'react';
import {
  Server,
  Wifi,
  Camera,
  Shield,
  Radio,
  Bell,
  PhoneCall,
  Check,
  ArrowUpRight,
  Layers,
  X,
  Image as ImageIcon,
  MapPin,
  Handshake,
  ChevronRight,
  Edit3,
} from 'lucide-react';
import {
  ServiceItem,
  ServiceCategory,
  ProjectPhoto,
  StaticPageItem,
  PROJECT_DOCUMENTARY,
  SERVICE_CATEGORIES,
} from '../data/companyData';
import { ServiceDetailModal } from './ServiceDetailModal';

interface ServicesSectionProps {
  services: ServiceItem[];
  categories: ServiceCategory[];
  projects?: ProjectPhoto[];
  onRequestQuote: (serviceName: string) => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

const categoryIconMap: Record<string, React.ReactNode> = {
  cat_networking: <Server className="w-6 h-6" />,
  cat_radio: <Radio className="w-6 h-6" />,
  cat_pabx: <PhoneCall className="w-6 h-6" />,
  cat_cctv: <Camera className="w-6 h-6" />,
  cat_access: <Shield className="w-6 h-6" />,
  cat_fire: <Bell className="w-6 h-6" />,
  cat_isp: <Wifi className="w-6 h-6" />,
};

const serviceIconMap: Record<string, React.ReactNode> = {
  cat_networking: <Server className="w-5 h-5" />,
  cat_radio: <Radio className="w-5 h-5" />,
  cat_pabx: <PhoneCall className="w-5 h-5" />,
  cat_cctv: <Camera className="w-5 h-5" />,
  cat_access: <Shield className="w-5 h-5" />,
  cat_fire: <Bell className="w-5 h-5" />,
  cat_isp: <Wifi className="w-5 h-5" />,
};

// Match ProjectPhoto to one of the 7 Bidang Pekerjaan
const matchesBidangProject = (
  project: ProjectPhoto,
  categoryId: string,
  categoryName: string
): boolean => {
  const pCat = (project.category || '').toLowerCase();
  const id = categoryId.toLowerCase();
  if (id === 'cat_networking') {
    return pCat.includes('networking') || pCat.includes('jaringan') || pCat.includes('fiber');
  }
  if (id === 'cat_radio') {
    return (
      pCat.includes('radio') ||
      pCat.includes('repeater') ||
      pCat.includes('ht') ||
      pCat.includes('rig') ||
      pCat.includes('tower')
    );
  }
  if (id === 'cat_pabx') {
    return pCat.includes('pabx') || pCat.includes('ip pbx') || pCat.includes('telepon');
  }
  if (id === 'cat_cctv') {
    return pCat.includes('cctv') || pCat.includes('surveillance') || pCat.includes('kamera');
  }
  if (id === 'cat_access') {
    return (
      pCat.includes('access') ||
      pCat.includes('door') ||
      pCat.includes('turnstile') ||
      pCat.includes('barrier')
    );
  }
  if (id === 'cat_fire') {
    return (
      pCat.includes('fire') ||
      pCat.includes('public address') ||
      pCat.includes('nursecall') ||
      pCat.includes('alarm')
    );
  }
  if (id === 'cat_isp') {
    return (
      pCat.includes('internet') ||
      pCat.includes('isp') ||
      pCat.includes('rt/rw') ||
      pCat.includes('wireless')
    );
  }
  return pCat.includes(categoryName.toLowerCase());
};

export const ServicesSection: React.FC<ServicesSectionProps> = ({
  services,
  categories,
  projects = PROJECT_DOCUMENTARY,
  onRequestQuote,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection,
}) => {
  const [activeCategoryId, setActiveCategoryId] = useState<string>('ALL');
  const [selectedBidangModal, setSelectedBidangModal] = useState<ServiceCategory | null>(null);
  const [selectedModalService, setSelectedModalService] = useState<ServiceItem | null>(null);
  const [lightboxImage, setLightboxImage] = useState<{
    url: string;
    title: string;
    caption?: string;
  } | null>(null);

  const publishedServices = services
    .filter(s => s.isPublished !== false)
    .sort((a, b) => (a.order || 0) - (b.order || 0));

  const activeCategories = (
    categories && categories.length > 0 ? categories : SERVICE_CATEGORIES
  ).sort((a, b) => (a.segmentNumber || 0) - (b.segmentNumber || 0));

  const publishedProjects =
    projects && projects.length > 0 ? projects : PROJECT_DOCUMENTARY;

  const filteredServices =
    activeCategoryId === 'ALL'
      ? publishedServices
      : publishedServices.filter(s => s.categoryId === activeCategoryId);

  const filteredProjects =
    activeCategoryId === 'ALL'
      ? publishedProjects
      : publishedProjects.filter(p => {
          const catObj = activeCategories.find(c => c.id === activeCategoryId);
          return matchesBidangProject(p, activeCategoryId, catObj?.name || '');
        });

  const handleSelectBidang = (cat: ServiceCategory) => {
    setSelectedBidangModal(cat);
  };

  return (
    <section id="layanan" className="py-20 bg-white border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {isLiveEditMode && onOpenAdminSection && (
          <div
            className="mb-8 flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-blue-50 border border-blue-200"
            data-live-editor-ui="true"
          >
            <div className="text-xs font-semibold text-blue-900">
              💡 <strong>Mode Edit Langsung Produk & Layanan:</strong> Kelola 7 Bidang Pekerjaan, Spesifikasi Layanan, dan Foto Dokumentasi di Admin CMS.
            </div>
            <button
              type="button"
              onClick={() => onOpenAdminSection('services')}
              className="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>Kelola Produk & Layanan di Admin CMS</span>
            </button>
          </div>
        )}

        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
          <div>
            <div className="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold uppercase tracking-wider mb-3">
              <span>Produk & Layanan • 7 Bidang Pekerjaan Utama</span>
            </div>
            <h2 className="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Produk, Layanan & Dokumentasi Bidang Pekerjaan'}
            </h2>
          </div>
          <p className="text-slate-600 text-sm max-w-md">
            {pageContent?.summary ||
              'Klik pada setiap bidang di bawah ini untuk melihat penjelasan lengkap jenis bidang pekerjaan beserta foto-foto dokumentasi proyek PT. Kreatif Global Solusindo di bidang tersebut.'}
          </p>
        </div>

        {/* 7 BIDANG PRODUK & LAYANAN (INTERACTIVE CLICKABLE CARDS) */}
        <div className="mb-12">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
              <Layers className="w-4 h-4 text-blue-600" />
              <span>Pilih Bidang Pekerjaan (Klik untuk Penjelasan & Foto Dokumentasi)</span>
            </h3>
            <span className="text-xs font-semibold text-blue-600 hidden sm:inline">
              7 Bidang Spesialisasi KGS
            </span>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            {activeCategories.map((cat, index) => {
              const catServices = publishedServices.filter(s => s.categoryId === cat.id);
              const catProjects = publishedProjects.filter(p =>
                matchesBidangProject(p, cat.id, cat.name)
              );
              const previewPhotos: string[] = [];
              catProjects.forEach(p => {
                if (p.imageUrl && !previewPhotos.includes(p.imageUrl)) {
                  previewPhotos.push(p.imageUrl);
                }
                if (Array.isArray(p.images)) {
                  p.images.forEach(img => {
                    if (img.imageUrl && !previewPhotos.includes(img.imageUrl)) {
                      previewPhotos.push(img.imageUrl);
                    }
                  });
                }
              });

              const isISP = cat.id === 'cat_isp';

              return (
                <div
                  key={cat.id}
                  role="button"
                  tabIndex={0}
                  onClick={() => handleSelectBidang(cat)}
                  onKeyDown={e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                      e.preventDefault();
                      handleSelectBidang(cat);
                    }
                  }}
                  className={`rounded-2xl border p-5 transition-all cursor-pointer flex flex-col justify-between group hover:-translate-y-1 hover:shadow-xl ${
                    isISP
                      ? 'md:col-span-2 lg:col-span-2 xl:col-span-2 bg-gradient-to-br from-slate-900 via-slate-900 to-blue-950 text-white border-blue-500/40 shadow-lg'
                      : 'bg-slate-50/90 hover:bg-white text-slate-900 border-slate-200/90 hover:border-blue-500 shadow-xs'
                  }`}
                >
                  <div>
                    <div className="flex items-start justify-between gap-3 mb-3">
                      <div
                        className={`w-12 h-12 rounded-2xl flex items-center justify-center transition shadow-xs ${
                          isISP
                            ? 'bg-blue-600 text-white group-hover:bg-blue-500'
                            : 'bg-blue-600/10 text-blue-600 border border-blue-200 group-hover:bg-blue-600 group-hover:text-white'
                        }`}
                      >
                        {categoryIconMap[cat.id] || <Server className="w-6 h-6" />}
                      </div>
                      <div className="flex items-center gap-1.5">
                        <span
                          className={`text-[11px] font-bold px-2.5 py-1 rounded-full border ${
                            isISP
                              ? 'bg-blue-500/20 text-blue-300 border-blue-400/30'
                              : 'bg-white text-slate-600 border-slate-200'
                          }`}
                        >
                          Bidang #{index + 1}
                        </span>
                        <span
                          className={`text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1 ${
                            isISP
                              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30'
                              : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                          }`}
                        >
                          <ImageIcon className="w-3 h-3" />
                          <span>{previewPhotos.length || catProjects.length} Foto</span>
                        </span>
                      </div>
                    </div>

                    <h4
                      className={`text-lg font-extrabold mb-2 flex items-center justify-between gap-2 transition-colors ${
                        isISP
                          ? 'text-white group-hover:text-blue-300'
                          : 'text-slate-900 group-hover:text-blue-600'
                      }`}
                    >
                      <span>{cat.name}</span>
                      <ArrowUpRight className="w-4 h-4 shrink-0 opacity-75 group-hover:opacity-100 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                    </h4>

                    <p
                      className={`text-xs leading-relaxed mb-4 ${
                        isISP ? 'text-slate-300' : 'text-slate-600'
                      }`}
                    >
                      {cat.description}
                    </p>

                    {/* Highlight Reseller Notice inside ISP card */}
                    {isISP && (
                      <div className="mb-4 p-3 rounded-xl bg-amber-500/15 border border-amber-400/40 flex items-center gap-2.5">
                        <Handshake className="w-5 h-5 text-amber-400 shrink-0" />
                        <p className="text-xs font-extrabold text-amber-300 leading-snug">
                          &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;
                        </p>
                      </div>
                    )}

                    {/* Sub-services bullets */}
                    {catServices.length > 0 && (
                      <div className="space-y-1.5 mb-4">
                        <div
                          className={`text-[10px] font-bold uppercase tracking-wider ${
                            isISP ? 'text-slate-400' : 'text-slate-400'
                          }`}
                        >
                          Cakupan Pekerjaan & Layanan:
                        </div>
                        {catServices.slice(0, 3).map(srv => (
                          <div
                            key={srv.id}
                            className={`flex items-center gap-1.5 text-xs font-semibold ${
                              isISP ? 'text-slate-200' : 'text-slate-700'
                            }`}
                          >
                            <Check
                              className={`w-3.5 h-3.5 shrink-0 ${
                                isISP ? 'text-emerald-400' : 'text-blue-600'
                              }`}
                            />
                            <span className="truncate">{srv.name}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {/* Preview Thumbnails Strip + Action Footer */}
                  <div>
                    {previewPhotos.length > 0 && (
                      <div className="grid grid-cols-3 gap-1.5 mb-3">
                        {previewPhotos.slice(0, 3).map((imgUrl, idx) => (
                          <div
                            key={idx}
                            className="relative h-14 rounded-lg overflow-hidden border border-slate-200/40 bg-slate-800"
                          >
                            <img
                              src={imgUrl}
                              alt={`${cat.name} dok ${idx + 1}`}
                              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                              loading="lazy"
                            />
                            {idx === 2 && previewPhotos.length > 3 && (
                              <div className="absolute inset-0 bg-slate-950/70 flex items-center justify-center text-[10px] font-extrabold text-white">
                                +{previewPhotos.length - 3} Foto
                              </div>
                            )}
                          </div>
                        ))}
                      </div>
                    )}

                    <div
                      className={`pt-3 border-t flex items-center justify-between text-xs font-bold ${
                        isISP
                          ? 'border-slate-800 text-blue-300 group-hover:text-white'
                          : 'border-slate-200/80 text-blue-600 group-hover:text-blue-700'
                      }`}
                    >
                      <span>Klik untuk Penjelasan & Foto Dokumentasi</span>
                      <ChevronRight className="w-4 h-4" />
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Category Filter Tabs */}
        <div
          id="katalog-item-layanan"
          className="flex items-center gap-2 overflow-x-auto pb-4 mb-8 border-b border-slate-200 scroll-mt-28"
        >
          <button
            type="button"
            onClick={() => setActiveCategoryId('ALL')}
            className={`px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 ${
              activeCategoryId === 'ALL'
                ? 'bg-blue-600 text-white shadow-xs'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            }`}
          >
            <Layers className="w-3.5 h-3.5" />
            <span>Semua Bidang ({publishedServices.length})</span>
          </button>
          {activeCategories.map(cat => {
            const count = publishedServices.filter(s => s.categoryId === cat.id).length;
            return (
              <button
                key={cat.id}
                type="button"
                onClick={() => setActiveCategoryId(cat.id)}
                className={`px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer ${
                  activeCategoryId === cat.id
                    ? 'bg-blue-600 text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                {cat.name} ({count})
              </button>
            );
          })}
        </div>

        {/* Active Category Explanation Banner when a specific category is selected */}
        {activeCategoryId !== 'ALL' && (() => {
          const currentCat = activeCategories.find(c => c.id === activeCategoryId);
          if (!currentCat) return null;
          return (
            <div className="mb-8 p-6 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50/60 to-white border border-blue-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
              <div>
                <span className="text-[11px] font-extrabold uppercase tracking-wider text-blue-600">
                  Bidang Pekerjaan Terpilih
                </span>
                <h3 className="text-xl font-extrabold text-slate-900 mt-0.5">
                  {currentCat.name}
                </h3>
                <p className="text-xs sm:text-sm text-slate-600 mt-1 max-w-3xl leading-relaxed">
                  {currentCat.description}
                </p>
              </div>
              <div className="flex flex-wrap gap-2 shrink-0">
                <button
                  type="button"
                  onClick={() => handleSelectBidang(currentCat)}
                  className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition cursor-pointer shadow-xs"
                >
                  Lihat Galeri Foto Dokumentasi ({filteredProjects.length})
                </button>
                <button
                  type="button"
                  onClick={() => onRequestQuote(currentCat.name)}
                  className="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold transition cursor-pointer"
                >
                  Minta Penawaran
                </button>
              </div>
            </div>
          );
        })()}

        {/* Services Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-14">
          {filteredServices.map(service => {
            const parentCat = activeCategories.find(c => c.id === service.categoryId);
            return (
              <div
                key={service.id}
                role="button"
                tabIndex={0}
                onClick={() => {
                  if (parentCat) {
                    handleSelectBidang(parentCat);
                  } else {
                    setSelectedModalService(service);
                  }
                }}
                onKeyDown={e => {
                  if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (parentCat) {
                      handleSelectBidang(parentCat);
                    } else {
                      setSelectedModalService(service);
                    }
                  }
                }}
                className="bg-white rounded-2xl border border-slate-200 hover:border-blue-500 p-6 shadow-xs hover:shadow-lg hover:-translate-y-0.5 transition-all flex flex-col justify-between group cursor-pointer"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-4">
                    <div className="p-3 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 group-hover:bg-blue-600 group-hover:text-white transition">
                      {serviceIconMap[service.categoryId] || <Server className="w-5 h-5" />}
                    </div>
                    <span className="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                      {service.categoryName}
                    </span>
                  </div>

                  <h3 className="text-lg font-extrabold text-slate-900 group-hover:text-blue-600 transition mb-2">
                    {service.name}
                  </h3>

                  <p className="text-xs text-slate-600 leading-relaxed mb-4">
                    {service.description}
                  </p>

                  {/* Features List */}
                  <div className="space-y-1.5 mb-5">
                    <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                      Cakupan Teknis:
                    </div>
                    {service.features.map((feat, idx) => (
                      <div key={idx} className="flex items-start gap-2 text-xs text-slate-700">
                        <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                        <span>{feat}</span>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="pt-4 border-t border-slate-100 space-y-3">
                  {/* Brands */}
                  <div className="flex flex-wrap gap-1.5">
                    {service.brands.map((brand, idx) => (
                      <span
                        key={idx}
                        className="text-[10px] font-semibold px-2 py-0.5 bg-slate-100 text-slate-700 rounded border border-slate-200"
                      >
                        {brand}
                      </span>
                    ))}
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      onClick={e => {
                        e.stopPropagation();
                        if (parentCat) {
                          handleSelectBidang(parentCat);
                        } else {
                          setSelectedModalService(service);
                        }
                      }}
                      className="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1 cursor-pointer"
                    >
                      <span>Detail & Foto</span>
                      <ArrowUpRight className="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      onClick={e => {
                        e.stopPropagation();
                        onRequestQuote(service.name);
                      }}
                      className="py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1 cursor-pointer shadow-2xs"
                    >
                      <span>Minta Penawaran</span>
                    </button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* FOTO / DOKUMENTASI PEKERJAAN DIKELOMPOKKAN SESUAI BIDANG PEKERJAAN */}
        <div className="pt-10 border-t border-slate-200">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold uppercase tracking-wider mb-2">
                <ImageIcon className="w-3.5 h-3.5" />
                <span>Galeri Dokumentasi Lapangan per Bidang Pekerjaan</span>
              </span>
              <h3 className="text-xl sm:text-2xl font-extrabold text-slate-900">
                Foto & Dokumentasi Pekerjaan (
                {activeCategoryId === 'ALL'
                  ? 'Semua 7 Bidang'
                  : activeCategories.find(c => c.id === activeCategoryId)?.name}
                )
              </h3>
            </div>
            <p className="text-xs sm:text-sm text-slate-600 max-w-md">
              Dokumentasi instalasi, terminasi, dan commissioning di lapangan yang dikelompokkan sesuai 7 bidang pekerjaan utama PT. Kreatif Global Solusindo.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredProjects.map(project => {
              const coverImg =
                project.imageUrl ||
                (project.images && project.images[0]?.imageUrl) ||
                'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=900&q=80';
              return (
                <div
                  key={project.id}
                  className="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden hover:border-blue-400 hover:shadow-md transition flex flex-col justify-between group"
                >
                  <div>
                    <div
                      onClick={() =>
                        setLightboxImage({
                          url: coverImg,
                          title: project.title,
                          caption: project.description,
                        })
                      }
                      className="relative h-52 bg-slate-900 overflow-hidden cursor-pointer"
                    >
                      <img
                        src={coverImg}
                        alt={project.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        loading="lazy"
                      />
                      <div className="absolute top-3 left-3">
                        <span className="px-2.5 py-1 rounded-full bg-slate-900/85 text-white text-[10px] font-bold border border-white/15 backdrop-blur-xs">
                          {project.category}
                        </span>
                      </div>
                    </div>

                    <div className="p-5">
                      <h4 className="text-base font-extrabold text-slate-900 mb-1.5">
                        {project.title}
                      </h4>
                      <p className="text-xs text-slate-600 leading-relaxed mb-3">
                        {project.description}
                      </p>

                      {project.clientOrLocation && (
                        <div className="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 font-medium">
                          <MapPin className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                          <span>{project.clientOrLocation}</span>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Additional thumbnails inside project card */}
                  {Array.isArray(project.images) && project.images.length > 1 && (
                    <div className="px-5 pb-5 pt-2 border-t border-slate-200/70">
                      <div className="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                        Foto Dokumentasi Terkait ({project.images.length} Foto):
                      </div>
                      <div className="grid grid-cols-3 gap-2">
                        {project.images.slice(0, 3).map((imgItem, gIdx) => (
                          <button
                            key={imgItem.id || gIdx}
                            type="button"
                            onClick={() =>
                              setLightboxImage({
                                url: imgItem.imageUrl,
                                title: `${project.title} (Foto ${gIdx + 1})`,
                                caption: imgItem.caption || project.description,
                              })
                            }
                            className="h-14 rounded-lg overflow-hidden border border-slate-200 hover:border-blue-500 transition cursor-pointer"
                          >
                            <img
                              src={imgItem.imageUrl}
                              alt={imgItem.caption || `${project.title} ${gIdx + 1}`}
                              className="w-full h-full object-cover"
                              loading="lazy"
                            />
                          </button>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* MODAL DETAIL BIDANG & FOTO DOKUMENTASI PEKERJAAN */}
      {selectedBidangModal && (() => {
        const bidangServices = publishedServices.filter(
          s => s.categoryId === selectedBidangModal.id
        );
        const bidangProjects = publishedProjects.filter(p =>
          matchesBidangProject(p, selectedBidangModal.id, selectedBidangModal.name)
        );
        const isISP = selectedBidangModal.id === 'cat_isp';

        return (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overflow-y-auto"
            onClick={() => setSelectedBidangModal(null)}
          >
            <div
              className="bg-white rounded-3xl max-w-5xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200 my-8"
              onClick={e => e.stopPropagation()}
            >
              {/* Modal Header */}
              <div className="sticky top-0 z-20 bg-slate-900 text-white px-6 py-5 flex items-center justify-between border-b border-slate-800">
                <div className="flex items-center gap-3.5">
                  <div className="w-11 h-11 rounded-2xl bg-blue-600 flex items-center justify-center text-white shrink-0">
                    {categoryIconMap[selectedBidangModal.id] || <Server className="w-6 h-6" />}
                  </div>
                  <div>
                    <span className="text-[10px] font-bold uppercase tracking-widest text-blue-400 block">
                      Detail Bidang Pekerjaan & Dokumentasi Proyek
                    </span>
                    <h3 className="text-lg sm:text-2xl font-extrabold text-white">
                      {selectedBidangModal.name}
                    </h3>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setSelectedBidangModal(null)}
                  className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
                  aria-label="Tutup"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              {/* Modal Body */}
              <div className="p-6 sm:p-8 space-y-8">
                {/* 1. Penjelasan Jenis Bidang Pekerjaan */}
                <div className="bg-blue-50/70 rounded-2xl p-6 border border-blue-200">
                  <h4 className="text-xs font-extrabold uppercase tracking-wider text-blue-700 mb-2">
                    Penjelasan Bidang {selectedBidangModal.name}
                  </h4>
                  <p className="text-sm sm:text-base text-slate-800 leading-relaxed font-medium">
                    {selectedBidangModal.description}
                  </p>

                  {isISP && (
                    <div className="mt-4 p-4 rounded-xl bg-slate-900 text-white border border-blue-500/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                      <div className="flex items-center gap-3">
                        <Handshake className="w-6 h-6 text-amber-400 shrink-0" />
                        <p className="text-xs sm:text-sm font-extrabold text-amber-300">
                          &ldquo;KGSNet menerima kerjasama kemitraan reseller layanan internet di seluruh Indonesia&rdquo;
                        </p>
                      </div>
                      <button
                        type="button"
                        onClick={() => {
                          setSelectedBidangModal(null);
                          onRequestQuote('Kemitraan Reseller Internet KGSNet');
                        }}
                        className="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs shrink-0 cursor-pointer"
                      >
                        Daftar Mitra KGSNet
                      </button>
                    </div>
                  )}
                </div>

                {/* 2. Rincian Sub-Layanan & Spesifikasi Teknis */}
                {bidangServices.length > 0 && (
                  <div>
                    <h4 className="text-sm font-extrabold uppercase tracking-wider text-slate-500 mb-4">
                      Lingkup Pekerjaan & Spesifikasi Teknis ({bidangServices.length} Layanan)
                    </h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                      {bidangServices.map(srv => (
                        <div
                          key={srv.id}
                          className="rounded-2xl border border-slate-200 p-5 bg-white shadow-2xs"
                        >
                          <h5 className="text-base font-extrabold text-slate-900 mb-2">
                            {srv.name}
                          </h5>
                          <p className="text-xs text-slate-600 leading-relaxed mb-4">
                            {srv.description}
                          </p>
                          <div className="space-y-1.5 mb-4">
                            {srv.features.map((feat, i) => (
                              <div key={i} className="flex items-start gap-2 text-xs text-slate-700">
                                <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                <span>{feat}</span>
                              </div>
                            ))}
                          </div>
                          <div className="flex flex-wrap gap-1.5 pt-3 border-t border-slate-100">
                            {srv.brands.map((b, i) => (
                              <span
                                key={i}
                                className="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200"
                              >
                                {b}
                              </span>
                            ))}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* 3. Foto-Foto Dokumentasi Pekerjaan di Bidang Tersebut */}
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <h4 className="text-sm font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                      <ImageIcon className="w-4 h-4 text-emerald-600" />
                      <span>
                        Foto-Foto Dokumentasi Pekerjaan Bidang {selectedBidangModal.name}
                      </span>
                    </h4>
                    <span className="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                      {bidangProjects.length} Proyek Terdokumentasi
                    </span>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {bidangProjects.map(proj => {
                      const cover =
                        proj.imageUrl ||
                        (proj.images && proj.images[0]?.imageUrl) ||
                        'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=900&q=80';
                      return (
                        <div
                          key={proj.id}
                          className="rounded-2xl border border-slate-200 overflow-hidden bg-slate-50"
                        >
                          <div
                            onClick={() =>
                              setLightboxImage({
                                url: cover,
                                title: proj.title,
                                caption: proj.description,
                              })
                            }
                            className="relative h-56 bg-slate-900 cursor-pointer group"
                          >
                            <img
                              src={cover}
                              alt={proj.title}
                              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div className="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-slate-900/80 text-white text-[10px] font-bold">
                              Klik Perbesar Foto
                            </div>
                          </div>
                          <div className="p-4">
                            <h5 className="font-extrabold text-slate-900 text-sm mb-1">
                              {proj.title}
                            </h5>
                            <p className="text-xs text-slate-600 leading-relaxed mb-3">
                              {proj.description}
                            </p>
                            {proj.clientOrLocation && (
                              <div className="flex flex-wrap gap-2 text-[11px] text-slate-500 font-semibold mb-3">
                                <MapPin className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                                <span>{proj.clientOrLocation}</span>
                              </div>
                            )}

                            {/* Multi-photo documentation gallery */}
                            {Array.isArray(proj.images) && proj.images.length > 0 && (
                              <div className="grid grid-cols-3 gap-2 pt-3 border-t border-slate-200">
                                {proj.images.map((imgItem, idx) => (
                                  <button
                                    key={imgItem.id || idx}
                                    type="button"
                                    onClick={() =>
                                      setLightboxImage({
                                        url: imgItem.imageUrl,
                                        title: `${proj.title} - Dokumentasi ${idx + 1}`,
                                        caption: imgItem.caption || proj.description,
                                      })
                                    }
                                    className="h-16 rounded-lg overflow-hidden border border-slate-200 hover:border-blue-500 transition cursor-pointer"
                                  >
                                    <img
                                      src={imgItem.imageUrl}
                                      alt={imgItem.caption || `${proj.title} ${idx + 1}`}
                                      className="w-full h-full object-cover"
                                    />
                                  </button>
                                ))}
                              </div>
                            )}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>

                {/* Modal Action Footer */}
                <div className="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                  <p className="text-xs text-slate-600">
                    Butuh survei lokasi, konsultasi arsitektur sistem, atau penawaran harga untuk bidang{' '}
                    <strong className="text-slate-900">{selectedBidangModal.name}</strong>?
                  </p>
                  <div className="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button
                      type="button"
                      onClick={() => setSelectedBidangModal(null)}
                      className="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                      Tutup
                    </button>
                    <button
                      type="button"
                      onClick={() => {
                        const name = selectedBidangModal.name;
                        setSelectedBidangModal(null);
                        onRequestQuote(name);
                      }}
                      className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition cursor-pointer shadow-sm"
                    >
                      Minta Penawaran Bidang Ini &rarr;
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        );
      })()}

      {/* Lightbox Image Preview */}
      {lightboxImage && (
        <div
          className="fixed inset-0 z-[60] bg-slate-950/90 backdrop-blur-xs flex items-center justify-center p-4"
          onClick={() => setLightboxImage(null)}
        >
          <div
            className="max-w-4xl w-full bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 shadow-2xl"
            onClick={e => e.stopPropagation()}
          >
            <div className="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 text-white">
              <h4 className="text-sm font-bold truncate">{lightboxImage.title}</h4>
              <button
                type="button"
                onClick={() => setLightboxImage(null)}
                className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
            <div className="bg-black flex items-center justify-center max-h-[75vh] overflow-hidden">
              <img
                src={lightboxImage.url}
                alt={lightboxImage.title}
                className="max-h-[75vh] w-auto object-contain"
              />
            </div>
            {lightboxImage.caption && (
              <div className="p-4 text-xs text-slate-300 bg-slate-900">
                {lightboxImage.caption}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Service Technical Detail Modal */}
      <ServiceDetailModal
        service={selectedModalService}
        onClose={() => setSelectedModalService(null)}
        onRequestQuote={srvName => onRequestQuote(srvName)}
      />
    </section>
  );
};
