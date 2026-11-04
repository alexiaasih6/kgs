import React, { useState, useMemo, useEffect } from 'react';
import {
  COMPANY_INFO,
  PROJECT_DOCUMENTARY,
  VERIFIED_CLIENTS,
  ProjectPhoto,
  PortfolioImageItem,
  ClientItem,
  StaticPageItem
} from '../data/companyData';
import {
  Camera,
  MapPin,
  ShieldCheck,
  Search,
  Building2,
  CheckCircle2,
  Edit3,
  ArrowLeft,
  ArrowRight,
  ChevronLeft,
  ChevronRight,
  Images,
  Maximize2,
  Play,
  Pause,
  Plus,
  Trash2,
  Upload,
  X,
  Star
} from 'lucide-react';

interface PortfolioSectionProps {
  onRequestQuote: (projectName?: string) => void;
  projects?: ProjectPhoto[];
  onUpdateProjects?: (projects: ProjectPhoto[]) => void;
  clients?: ClientItem[];
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

const DEFAULT_FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=1200&q=80';

function getProjectGallery(project: ProjectPhoto): PortfolioImageItem[] {
  if (project.images && project.images.length > 0) {
    return [...project.images].sort((a, b) => (a.sortOrder || 0) - (b.sortOrder || 0));
  }
  const cover = project.imageUrl || DEFAULT_FALLBACK_IMAGE;
  return [
    {
      id: `${project.id}-img-default`,
      imageUrl: cover,
      caption: `${project.title} — ${project.clientOrLocation}`,
      sortOrder: 1,
      isPrimary: true
    }
  ];
}

export const PortfolioSection: React.FC<PortfolioSectionProps> = ({
  onRequestQuote,
  projects = PROJECT_DOCUMENTARY,
  onUpdateProjects,
  clients = VERIFIED_CLIENTS,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [activeTab, setActiveTab] = useState<'projects' | 'clients'>('clients');
  const [searchTerm, setSearchTerm] = useState('');
  const [projectCategory, setProjectCategory] = useState<string>('all');
  const [clientCategory, setClientCategory] = useState<string>('all');

  // Detail View State (like Slideshow Detail Page)
  const [selectedProjectId, setSelectedProjectId] = useState<string | null>(null);
  const [currentSlideIdx, setCurrentSlideIdx] = useState<number>(0);
  const [isAutoPlay, setIsAutoPlay] = useState<boolean>(false);
  const [isLightboxOpen, setIsLightboxOpen] = useState<boolean>(false);

  // Add Multiple Images State (inside Detail View)
  const [isAddImagesOpen, setIsAddImagesOpen] = useState<boolean>(false);
  const [multiUrlInput, setMultiUrlInput] = useState<string>('');
  const [defaultCaptionInput, setDefaultCaptionInput] = useState<string>('');
  const [uploadSuccessMsg, setUploadSuccessMsg] = useState<string>('');

  // Create New Portfolio Project Modal State
  const [isNewProjectModalOpen, setIsNewProjectModalOpen] = useState<boolean>(false);
  const [newProjTitle, setNewProjTitle] = useState<string>('');
  const [newProjClient, setNewProjClient] = useState<string>('');
  const [newProjCategory, setNewProjCategory] = useState<string>('Data Networking');
  const [newProjDesc, setNewProjDesc] = useState<string>('');
  const [newProjUrls, setNewProjUrls] = useState<string>('');
  const [newProjUploadedFiles, setNewProjUploadedFiles] = useState<
    { url: string; name: string }[]
  >([]);

  const selectedProject = useMemo(
    () => projects.find(p => p.id === selectedProjectId) || null,
    [projects, selectedProjectId]
  );

  const activeSlides = useMemo(
    () => (selectedProject ? getProjectGallery(selectedProject) : []),
    [selectedProject]
  );

  const totalPortfolioImages = useMemo(
    () => projects.reduce((acc, p) => acc + getProjectGallery(p).length, 0),
    [projects]
  );

  // Reset slide index when project changes
  useEffect(() => {
    setCurrentSlideIdx(0);
    setIsAutoPlay(false);
    setIsLightboxOpen(false);
    setUploadSuccessMsg('');
  }, [selectedProjectId]);

  // AutoPlay timer
  useEffect(() => {
    if (!isAutoPlay || activeSlides.length <= 1) return;
    const timer = window.setInterval(() => {
      setCurrentSlideIdx(prev => (prev + 1) % activeSlides.length);
    }, 4000);
    return () => window.clearInterval(timer);
  }, [isAutoPlay, activeSlides.length]);

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
      const matchSearch =
        searchTerm === '' ||
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
      const matchSearch =
        searchTerm === '' ||
        c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        c.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (c.location && c.location.toLowerCase().includes(searchTerm.toLowerCase()));
      return matchCat && matchSearch;
    });
  }, [clients, clientCategory, searchTerm]);

  const currentSlide = activeSlides[currentSlideIdx] || activeSlides[0];

  const handleOpenProjectDetail = (projectId: string, openAddPanel = false) => {
    setSelectedProjectId(projectId);
    setIsAddImagesOpen(openAddPanel);
    const el = document.getElementById('portofolio');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  const handlePrevSlide = () => {
    if (activeSlides.length === 0) return;
    setCurrentSlideIdx(prev => (prev - 1 + activeSlides.length) % activeSlides.length);
  };

  const handleNextSlide = () => {
    if (activeSlides.length === 0) return;
    setCurrentSlideIdx(prev => (prev + 1) % activeSlides.length);
  };

  // Add multiple images via URLs (1 URL per line, optional ":: caption")
  const handleAddUrlsToProject = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedProject || !onUpdateProjects) return;
    const lines = multiUrlInput
      .split(/\r?\n/)
      .map(l => l.trim())
      .filter(Boolean);
    if (lines.length === 0) return;

    const existing = getProjectGallery(selectedProject);
    const addedSlides: PortfolioImageItem[] = lines.map((line, idx) => {
      const parts = line.split('::').map(s => s.trim());
      const url = parts[0];
      const cap =
        parts[1] ||
        defaultCaptionInput.trim() ||
        `${selectedProject.title} — Foto ${existing.length + idx + 1}`;
      return {
        id: `${selectedProject.id}-img-${Date.now()}-${idx}`,
        imageUrl: url,
        caption: cap,
        sortOrder: existing.length + idx + 1,
        isPrimary: existing.length === 0 && idx === 0
      };
    });

    const updatedGallery = [...existing, ...addedSlides];
    const updatedProjects = projects.map(p =>
      p.id === selectedProject.id
        ? {
            ...p,
            imageUrl: p.imageUrl || updatedGallery[0]?.imageUrl,
            images: updatedGallery
          }
        : p
    );

    onUpdateProjects(updatedProjects);
    setMultiUrlInput('');
    setDefaultCaptionInput('');
    setCurrentSlideIdx(updatedGallery.length - 1);
    setUploadSuccessMsg(`${addedSlides.length} gambar berhasil ditambahkan ke proyek ini!`);
  };

  // Add multiple images at once via Multi-File Input
  const handleMultiFileUploadToProject = (e: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = e.target.files;
    if (!fileList || fileList.length === 0 || !selectedProject || !onUpdateProjects) return;

    const files: File[] = Array.from(fileList);
    const promises = files.map(
      file =>
        new Promise<{ dataUrl: string; name: string }>((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () =>
            resolve({
              dataUrl: typeof reader.result === 'string' ? reader.result : '',
              name: file.name.replace(/\.[^/.]+$/, '').replace(/[-_]+/g, ' ')
            });
          reader.onerror = () => reject(new Error('Failed to read file'));
          reader.readAsDataURL(file);
        })
    );

    Promise.all(promises).then(results => {
      const valid = results.filter(r => r.dataUrl);
      if (valid.length === 0) return;

      const existing = getProjectGallery(selectedProject);
      const newSlides: PortfolioImageItem[] = valid.map((item, idx) => ({
        id: `${selectedProject.id}-file-${Date.now()}-${idx}`,
        imageUrl: item.dataUrl,
        caption:
          defaultCaptionInput.trim() ||
          `${selectedProject.title} — ${item.name || `Foto ${existing.length + idx + 1}`}`,
        sortOrder: existing.length + idx + 1,
        isPrimary: existing.length === 0 && idx === 0
      }));

      const updatedGallery = [...existing, ...newSlides];
      const updatedProjects = projects.map(p =>
        p.id === selectedProject.id
          ? {
              ...p,
              imageUrl: p.imageUrl || updatedGallery[0]?.imageUrl,
              images: updatedGallery
            }
          : p
      );

      onUpdateProjects(updatedProjects);
      setCurrentSlideIdx(updatedGallery.length - 1);
      setUploadSuccessMsg(`${newSlides.length} file foto berhasil diunggah ke proyek ini!`);
    });

    e.target.value = '';
  };

  const handleSetPrimaryProjectImage = (slideId: string) => {
    if (!selectedProject || !onUpdateProjects) return;
    const existing = getProjectGallery(selectedProject);
    const target = existing.find(img => img.id === slideId);
    if (!target) return;

    const updatedGallery = existing.map(img => ({
      ...img,
      isPrimary: img.id === slideId
    }));

    const updatedProjects = projects.map(p =>
      p.id === selectedProject.id
        ? {
            ...p,
            imageUrl: target.imageUrl,
            images: updatedGallery
          }
        : p
    );
    onUpdateProjects(updatedProjects);
  };

  const handleDeleteProjectImage = (slideId: string) => {
    if (!selectedProject || !onUpdateProjects) return;
    const existing = getProjectGallery(selectedProject);
    if (existing.length <= 1) return;

    const filtered = existing
      .filter(img => img.id !== slideId)
      .map((img, idx) => ({ ...img, sortOrder: idx + 1 }));

    if (!filtered.some(img => img.isPrimary) && filtered[0]) {
      filtered[0].isPrimary = true;
    }
    const primaryImg = filtered.find(img => img.isPrimary) || filtered[0];

    const updatedProjects = projects.map(p =>
      p.id === selectedProject.id
        ? {
            ...p,
            imageUrl: primaryImg?.imageUrl || p.imageUrl,
            images: filtered
          }
        : p
    );
    onUpdateProjects(updatedProjects);
    setCurrentSlideIdx(prev => Math.min(prev, filtered.length - 1));
  };

  // Multi-file upload inside "Buat Proyek Baru" modal
  const handleNewProjectFilesChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = e.target.files;
    if (!fileList || fileList.length === 0) return;
    const files: File[] = Array.from(fileList);
    Promise.all(
      files.map(
        f =>
          new Promise<{ url: string; name: string }>(resolve => {
            const reader = new FileReader();
            reader.onload = () =>
              resolve({
                url: typeof reader.result === 'string' ? reader.result : '',
                name: f.name.replace(/\.[^/.]+$/, '').replace(/[-_]+/g, ' ')
              });
            reader.readAsDataURL(f);
          })
      )
    ).then(items => {
      setNewProjUploadedFiles(prev => [...prev, ...items.filter(i => i.url)]);
    });
    e.target.value = '';
  };

  const handleCreateNewProject = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newProjTitle.trim() || !newProjClient.trim() || !onUpdateProjects) return;

    const newId = `doc-${Date.now()}`;
    const urlLines = newProjUrls
      .split(/\r?\n/)
      .map(l => l.trim())
      .filter(Boolean);

    const slides: PortfolioImageItem[] = [];
    newProjUploadedFiles.forEach((f, idx) => {
      slides.push({
        id: `${newId}-file-${idx + 1}`,
        imageUrl: f.url,
        caption: `${newProjTitle.trim()} — ${f.name || `Foto ${idx + 1}`}`,
        sortOrder: slides.length + 1,
        isPrimary: slides.length === 0
      });
    });

    urlLines.forEach((line, idx) => {
      const parts = line.split('::').map(s => s.trim());
      slides.push({
        id: `${newId}-url-${idx + 1}`,
        imageUrl: parts[0],
        caption: parts[1] || `${newProjTitle.trim()} — Foto ${slides.length + 1}`,
        sortOrder: slides.length + 1,
        isPrimary: slides.length === 0
      });
    });

    if (slides.length === 0) {
      slides.push({
        id: `${newId}-default-1`,
        imageUrl: DEFAULT_FALLBACK_IMAGE,
        caption: `${newProjTitle.trim()} — ${newProjClient.trim()}`,
        sortOrder: 1,
        isPrimary: true
      });
    }

    const newProject: ProjectPhoto = {
      id: newId,
      title: newProjTitle.trim(),
      clientOrLocation: newProjClient.trim(),
      category: newProjCategory.trim() || 'Solusi Terintegrasi',
      description:
        newProjDesc.trim() ||
        `Dokumentasi pekerjaan ${newProjTitle.trim()} di ${newProjClient.trim()}.`,
      imageUrl: slides[0].imageUrl,
      images: slides
    };

    onUpdateProjects([newProject, ...projects]);
    setIsNewProjectModalOpen(false);
    setNewProjTitle('');
    setNewProjClient('');
    setNewProjDesc('');
    setNewProjUrls('');
    setNewProjUploadedFiles([]);
    setSelectedProjectId(newId);
  };

  return (
    <section id="portofolio" className="py-24 bg-slate-50 border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
          <div className="max-w-4xl">
            <div className="flex flex-wrap items-center gap-2 text-xs font-extrabold text-blue-700 uppercase tracking-wider mb-2">
              <span>CLIENT Portofolio & Pengalaman Lapangan</span>
              <span aria-hidden="true">·</span>
              <span>Rekam Jejak Nyata & Klien Terdaftar</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Portofolio Proyek & Klien Korporasi'}
            </h2>
            <div className="mt-4 space-y-3 text-sm sm:text-base text-slate-700 leading-relaxed">
              {pageContent?.content ? (
                <div className="whitespace-pre-line">{pageContent.content}</div>
              ) : (
                <>
                  <p>{COMPANY_INFO.clientPortfolioIntro1}</p>
                  <p>{COMPANY_INFO.clientPortfolioIntro2}</p>
                </>
              )}
            </div>
          </div>

          {/* Stats & Action Buttons (Like slideshow.php Header) */}
          <div className="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end gap-3 shrink-0">
            <div className="flex items-center gap-3">
              <div className="px-4 py-3 rounded-2xl bg-slate-900 text-white text-center min-w-[115px] shadow-xs">
                <div className="text-2xl font-extrabold">{projects.length}</div>
                <div className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">
                  Proyek
                </div>
              </div>
              <div className="px-4 py-3 rounded-2xl bg-blue-600 text-white text-center min-w-[125px] shadow-xs">
                <div className="text-2xl font-extrabold">{totalPortfolioImages}</div>
                <div className="text-[11px] font-bold uppercase tracking-wider text-blue-100 mt-0.5">
                  Total Slide Foto
                </div>
              </div>
            </div>

            <div className="flex flex-wrap gap-2">
              {onUpdateProjects && (
                <button
                  type="button"
                  onClick={() => setIsNewProjectModalOpen(true)}
                  className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5 cursor-pointer transition"
                >
                  <Plus className="w-4 h-4" />
                  <span>+ Proyek & Banyak Gambar</span>
                </button>
              )}
              {onOpenAdminSection && isLiveEditMode && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('portfolio')}
                  className="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>Kelola di Admin CMS</span>
                </button>
              )}
            </div>
          </div>
        </div>

        {/* Top Switcher & Search Bar */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-8">
          <div
            className="flex items-center gap-2 p-1.5 bg-slate-200/80 rounded-xl max-w-lg"
            data-live-nav="true"
          >
            <button
              onClick={() => {
                setActiveTab('clients');
                setSelectedProjectId(null);
                setSearchTerm('');
              }}
              className={`flex-1 py-2.5 px-4 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer ${
                activeTab === 'clients'
                  ? 'bg-white text-blue-700 shadow-xs'
                  : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <Building2 className="w-4 h-4" />
              <span>Daftar Pelanggan / Client ({clients.length})</span>
            </button>
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
              <span>Foto Dokumentasi Pekerjaan ({projects.length})</span>
            </button>
          </div>

          {!selectedProject && (
            <div className="relative w-full sm:w-80">
              <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                placeholder={
                  activeTab === 'projects'
                    ? 'Cari dokumentasi proyek...'
                    : 'Cari nama klien / instansi...'
                }
                value={searchTerm}
                onChange={e => setSearchTerm(e.target.value)}
                className="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 transition"
              />
            </div>
          )}
        </div>

        {/* ===================================================================== */}
        {/* TAB 1: DOKUMENTASI PROYEK LAPANGAN (TAMPILAN SEPERTI SLIDESHOW.PHP) */}
        {/* ===================================================================== */}
        {activeTab === 'projects' && !selectedProject && (
          <div>
            {/* Category Filter Pills */}
            <div
              className="flex flex-wrap items-center gap-2 mb-6 pb-4 border-b border-slate-200 text-xs"
              data-live-nav="true"
            >
              <span className="font-extrabold uppercase tracking-wider text-slate-400 mr-1">
                Kategori Pekerjaan:
              </span>
              {projectCategories.map(cat => (
                <button
                  key={cat}
                  onClick={() => setProjectCategory(cat)}
                  className={`px-3.5 py-2 rounded-xl font-bold transition-colors cursor-pointer ${
                    projectCategory === cat
                      ? 'bg-blue-600 text-white shadow-xs'
                      : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                  }`}
                >
                  {cat === 'all' ? `Semua Dokumentasi (${projects.length})` : cat}
                </button>
              ))}
            </div>

            {/* Grid Kartu Portofolio (Tampilan Seperti slideshow.php) */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
              {filteredProjects.map((item, idx) => {
                const slides = getProjectGallery(item);
                const imgCount = slides.length;
                const primarySlide = slides.find(s => s.isPrimary) || slides[0];
                const cover = primarySlide?.imageUrl || item.imageUrl || DEFAULT_FALLBACK_IMAGE;

                return (
                  <article
                    key={item.id}
                    className="group bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl hover:border-blue-500/60 transition-all duration-300 flex flex-col"
                  >
                    {/* Cover Image Link (16:10 like slideshow.php) */}
                    <div
                      onClick={() => handleOpenProjectDetail(item.id)}
                      className="relative aspect-16/10 bg-slate-900 overflow-hidden cursor-pointer"
                    >
                      <img
                        src={cover}
                        alt={item.title}
                        loading="lazy"
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                      <div className="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent" />

                      {/* Badge Nomor Urut & Kategori */}
                      <div className="absolute top-3.5 left-3.5 flex items-center gap-1.5">
                        <span className="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold border border-white/15">
                          #{String(idx + 1).padStart(2, '0')}
                        </span>
                        <span className="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-950/85 backdrop-blur-md text-blue-300 text-[11px] font-bold border border-blue-400/30">
                          {item.category}
                        </span>
                      </div>

                      {/* Badge Jumlah Slide Gambar */}
                      <div className="absolute top-3.5 right-3.5">
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-600/95 backdrop-blur-md text-white text-xs font-extrabold shadow-md">
                          <Images className="w-3.5 h-3.5" />
                          <span>{imgCount} Gambar</span>
                        </span>
                      </div>

                      {/* Judul & Lokasi Proyek di Atas Cover */}
                      <div className="absolute bottom-3.5 left-4 right-4">
                        <h3 className="text-lg font-extrabold text-white tracking-tight leading-snug group-hover:text-blue-300 transition-colors">
                          {item.title}
                        </h3>
                        <div className="flex items-center gap-1.5 text-xs text-slate-300 mt-1 font-medium">
                          <MapPin className="w-3.5 h-3.5 text-blue-400 shrink-0" />
                          <span className="truncate">{item.clientOrLocation}</span>
                        </div>
                      </div>
                    </div>

                    {/* Konten Kartu */}
                    <div className="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                      <div>
                        <p className="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3 mb-4">
                          {item.description}
                        </p>

                        {/* Preview Mini Thumbnail Strip (Like slideshow.php) */}
                        <div className="mb-5">
                          <div className="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            <span>Pratinjau Slide Foto:</span>
                            {onUpdateProjects && (
                              <button
                                type="button"
                                onClick={() => handleOpenProjectDetail(item.id, true)}
                                className="text-blue-600 hover:text-blue-800 font-extrabold normal-case inline-flex items-center gap-1 cursor-pointer"
                              >
                                <Plus className="w-3 h-3" />
                                <span>Tambah Gambar</span>
                              </button>
                            )}
                          </div>
                          <div className="flex items-center gap-1.5">
                            {slides.slice(0, 4).map(thumb => (
                              <button
                                key={thumb.id}
                                type="button"
                                onClick={() => handleOpenProjectDetail(item.id)}
                                title={thumb.caption}
                                className="w-12 h-9 rounded-lg overflow-hidden border border-slate-200 shrink-0 hover:opacity-90 transition cursor-pointer"
                              >
                                <img
                                  src={thumb.imageUrl}
                                  alt={thumb.caption}
                                  loading="lazy"
                                  className="w-full h-full object-cover"
                                />
                              </button>
                            ))}
                            {imgCount > 4 && (
                              <button
                                type="button"
                                onClick={() => handleOpenProjectDetail(item.id)}
                                className="w-12 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-extrabold text-blue-600 shrink-0 cursor-pointer"
                              >
                                +{imgCount - 4}
                              </button>
                            )}
                          </div>
                        </div>
                      </div>

                      {/* Tombol Read More Halaman Detail & Galeri */}
                      <div className="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <span className="text-xs font-semibold text-slate-500">
                          Halaman Detail & Galeri
                        </span>
                        <button
                          type="button"
                          onClick={() => handleOpenProjectDetail(item.id)}
                          className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                        >
                          <span>Read More</span>
                          <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </article>
                );
              })}
            </div>

            {filteredProjects.length === 0 && (
              <div className="p-12 text-center text-slate-500 text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                Tidak ada dokumentasi proyek yang sesuai dengan kriteria pencarian.
              </div>
            )}
          </div>
        )}

        {/* ===================================================================== */}
        {/* HALAMAN DETAIL PORTOFOLIO MULTI-GAMBAR (SEPERTI SLIDESHOW-DETAIL.PHP) */}
        {/* ===================================================================== */}
        {activeTab === 'projects' && selectedProject && (
          <div className="space-y-8">
            {/* Top Navigation Bar */}
            <div className="bg-slate-900 text-white rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-lg">
              <div className="flex flex-wrap items-center justify-between gap-4 mb-5">
                <button
                  type="button"
                  onClick={() => setSelectedProjectId(null)}
                  className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 text-xs font-bold border border-slate-700 transition cursor-pointer"
                >
                  <ArrowLeft className="w-4 h-4" />
                  <span>Kembali ke Daftar Portofolio</span>
                </button>

                <div className="flex flex-wrap items-center gap-2">
                  <span className="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold">
                    {selectedProject.category}
                  </span>
                  <span className="px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold">
                    🖼️ {activeSlides.length} Slide Foto
                  </span>
                </div>
              </div>

              <h3 className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                {selectedProject.title}
              </h3>
              <div className="flex items-center gap-2 text-sm text-slate-300 mt-2 font-medium">
                <MapPin className="w-4 h-4 text-blue-400 shrink-0" />
                <span>{selectedProject.clientOrLocation}</span>
              </div>

              {/* Quick Switcher Antar Proyek */}
              <div className="mt-6 pt-5 border-t border-slate-800 flex items-center gap-2 overflow-x-auto pb-1">
                <span className="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 shrink-0 mr-1">
                  Pilih Proyek:
                </span>
                {projects.map(proj => {
                  const isCurr = proj.id === selectedProject.id;
                  return (
                    <button
                      key={proj.id}
                      type="button"
                      onClick={() => setSelectedProjectId(proj.id)}
                      className={`px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer ${
                        isCurr
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700'
                      }`}
                    >
                      {proj.title}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Konten Utama: Kolom Kiri (Carousel Multi-Gambar + Tambah Gambar) & Kolom Kanan (Deskripsi) */}
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
              {/* Kolom Kiri (7 Kolom): Penampil Slide Show Utama + Thumbnail Strip + Tambah Banyak Gambar */}
              <div className="lg:col-span-7 space-y-5">
                <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-md">
                  {/* Toolbar Atas Viewer */}
                  <div className="px-4 sm:px-6 py-3.5 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2 text-white">
                    <div className="flex items-center gap-2.5">
                      <span className="px-3 py-1 rounded-full bg-blue-600 text-white text-xs font-extrabold">
                        Slide {currentSlideIdx + 1} / {activeSlides.length}
                      </span>
                      <span className="text-xs text-slate-300 font-semibold hidden sm:inline">
                        Klik gambar untuk mode layar penuh (Lightbox)
                      </span>
                    </div>

                    <div className="flex items-center gap-2">
                      {activeSlides.length > 1 && (
                        <button
                          type="button"
                          onClick={() => setIsAutoPlay(prev => !prev)}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition cursor-pointer"
                        >
                          {isAutoPlay ? (
                            <>
                              <Pause className="w-3.5 h-3.5 text-amber-400" />
                              <span>Jeda Otomatis</span>
                            </>
                          ) : (
                            <>
                              <Play className="w-3.5 h-3.5 text-emerald-400" />
                              <span>Putar Otomatis</span>
                            </>
                          )}
                        </button>
                      )}
                      <button
                        type="button"
                        onClick={() => setIsLightboxOpen(true)}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 text-xs font-bold border border-blue-500/30 transition cursor-pointer"
                      >
                        <Maximize2 className="w-3.5 h-3.5" />
                        <span>Perbesar</span>
                      </button>
                    </div>
                  </div>

                  {/* Area Gambar Utama */}
                  <div className="relative aspect-16/10 bg-slate-950 overflow-hidden group select-none">
                    {currentSlide && (
                      <img
                        src={currentSlide.imageUrl}
                        alt={currentSlide.caption || selectedProject.title}
                        onClick={() => setIsLightboxOpen(true)}
                        className="w-full h-full object-cover cursor-zoom-in transition-all duration-300"
                      />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent pointer-events-none" />

                    {activeSlides.length > 1 && (
                      <>
                        <button
                          type="button"
                          onClick={handlePrevSlide}
                          aria-label="Gambar Sebelumnya"
                          className="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer"
                        >
                          <ChevronLeft className="w-5 h-5" />
                        </button>
                        <button
                          type="button"
                          onClick={handleNextSlide}
                          aria-label="Gambar Berikutnya"
                          className="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer"
                        >
                          <ChevronRight className="w-5 h-5" />
                        </button>
                      </>
                    )}

                    {/* Caption Bar */}
                    <div className="absolute bottom-0 inset-x-0 p-4 sm:p-5 text-white pointer-events-none">
                      <p className="text-sm sm:text-base font-bold text-white leading-snug drop-shadow">
                        {currentSlide?.caption || selectedProject.title}
                      </p>
                    </div>
                  </div>

                  {/* Strip Thumbnail Multi-Gambar */}
                  <div className="p-4 sm:p-5 bg-slate-50 border-t border-slate-200">
                    <div className="flex flex-wrap items-center justify-between gap-2 mb-3">
                      <span className="text-xs font-extrabold uppercase tracking-wider text-slate-600">
                        Daftar Gambar Pada Proyek Ini ({activeSlides.length} Slide)
                      </span>
                      {onUpdateProjects && (
                        <button
                          type="button"
                          onClick={() => setIsAddImagesOpen(prev => !prev)}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                        >
                          <Plus className="w-3.5 h-3.5" />
                          <span>
                            {isAddImagesOpen
                              ? 'Tutup Form Tambah Gambar'
                              : '+ Tambah Banyak Gambar'}
                          </span>
                        </button>
                      )}
                    </div>

                    <div className="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2.5">
                      {activeSlides.map((slide, idx) => (
                        <div key={slide.id} className="relative group/thumb">
                          <button
                            type="button"
                            onClick={() => setCurrentSlideIdx(idx)}
                            title={slide.caption}
                            className={`w-full relative aspect-16/10 rounded-xl overflow-hidden border-2 transition cursor-pointer focus:outline-none ${
                              idx === currentSlideIdx
                                ? 'border-blue-600 ring-2 ring-blue-500/40 opacity-100'
                                : 'border-slate-200 opacity-75 hover:opacity-100'
                            }`}
                          >
                            <img
                              src={slide.imageUrl}
                              alt={slide.caption}
                              loading="lazy"
                              className="w-full h-full object-cover"
                            />
                            <span className="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/80 text-white text-[10px] font-bold">
                              {idx + 1}
                            </span>
                            {slide.isPrimary && (
                              <span
                                title="Cover Utama"
                                className="absolute bottom-1 left-1 px-1 py-0.5 rounded bg-amber-500 text-slate-950 text-[9px] font-extrabold"
                              >
                                Cover
                              </span>
                            )}
                          </button>

                          {onUpdateProjects && (
                            <div className="absolute top-1 right-1 hidden group-hover/thumb:flex items-center gap-1">
                              {!slide.isPrimary && (
                                <button
                                  type="button"
                                  onClick={() => handleSetPrimaryProjectImage(slide.id)}
                                  title="Jadikan Cover Utama"
                                  className="w-5 h-5 rounded bg-amber-500 hover:bg-amber-400 text-slate-950 flex items-center justify-center shadow cursor-pointer"
                                >
                                  <Star className="w-3 h-3" />
                                </button>
                              )}
                              {activeSlides.length > 1 && (
                                <button
                                  type="button"
                                  onClick={() => handleDeleteProjectImage(slide.id)}
                                  title="Hapus Gambar Ini"
                                  className="w-5 h-5 rounded bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center shadow cursor-pointer"
                                >
                                  <Trash2 className="w-3 h-3" />
                                </button>
                              )}
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                </div>

                {/* Panel Tambah Banyak Gambar Sekaligus */}
                {onUpdateProjects && isAddImagesOpen && (
                  <div className="bg-white rounded-3xl border-2 border-blue-500/40 p-6 shadow-md space-y-5">
                    <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                      <div>
                        <span className="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 block">
                          Galeri Multi-Gambar Portofolio
                        </span>
                        <h4 className="text-base font-extrabold text-slate-900">
                          Tambah Banyak Gambar ke "{selectedProject.title}"
                        </h4>
                      </div>
                      <button
                        type="button"
                        onClick={() => setIsAddImagesOpen(false)}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer"
                      >
                        <X className="w-4 h-4" />
                      </button>
                    </div>

                    {uploadSuccessMsg && (
                      <div className="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        ✓ {uploadSuccessMsg}
                      </div>
                    )}

                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">
                        Keterangan / Caption Default (Opsional)
                      </label>
                      <input
                        type="text"
                        value={defaultCaptionInput}
                        onChange={e => setDefaultCaptionInput(e.target.value)}
                        placeholder={`Contoh: Dokumentasi lapangan ${selectedProject.title}`}
                        className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-600"
                      />
                    </div>

                    {/* Opsi 1: Upload Banyak File Sekaligus */}
                    <div className="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 space-y-2">
                      <label className="block text-xs font-extrabold text-blue-950">
                        1. Upload Banyak File Foto Sekaligus dari Perangkat
                      </label>
                      <p className="text-[11px] text-slate-600">
                        Anda dapat memilih banyak file gambar sekaligus (JPG, PNG, WEBP) untuk langsung ditambahkan ke slide proyek ini.
                      </p>
                      <label className="mt-2 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs cursor-pointer transition">
                        <Upload className="w-4 h-4" />
                        <span>Pilih Banyak File Gambar...</span>
                        <input
                          type="file"
                          accept="image/*"
                          multiple
                          onChange={handleMultiFileUploadToProject}
                          className="hidden"
                        />
                      </label>
                    </div>

                    {/* Opsi 2: Tambah Banyak URL Gambar Sekaligus */}
                    <form onSubmit={handleAddUrlsToProject} className="space-y-3">
                      <div>
                        <label className="block text-xs font-extrabold text-slate-800 mb-1">
                          2. Atau Masukkan Banyak URL Gambar (Satu Baris Satu URL)
                        </label>
                        <p className="text-[11px] text-slate-500 mb-2">
                          Format opsional dengan caption: <code>https://.../foto.jpg :: Keterangan Foto</code>
                        </p>
                        <textarea
                          rows={3}
                          value={multiUrlInput}
                          onChange={e => setMultiUrlInput(e.target.value)}
                          placeholder={`https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?w=1200 :: Foto Instalasi 1\nhttps://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200 :: Foto Instalasi 2`}
                          className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:border-blue-600"
                        />
                      </div>
                      <button
                        type="submit"
                        className="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold transition cursor-pointer"
                      >
                        + Tambahkan Semua URL Gambar ke Slide
                      </button>
                    </form>
                  </div>
                )}
              </div>

              {/* Kolom Kanan (5 Kolom): Rincian Proyek & Standar Pelaksanaan */}
              <div className="lg:col-span-5 space-y-6">
                <div className="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                  <div>
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-extrabold uppercase tracking-wider mb-3">
                      📘 Rincian Dokumentasi Proyek
                    </span>
                    <h4 className="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                      {selectedProject.title}
                    </h4>
                    <div className="flex items-center gap-2 text-xs font-semibold text-blue-700 mt-2">
                      <MapPin className="w-4 h-4 shrink-0" />
                      <span>Lokasi / Klien: {selectedProject.clientOrLocation}</span>
                    </div>
                  </div>

                  <div className="pt-4 border-t border-slate-100">
                    <div className="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                      Lingkup & Deskripsi Pekerjaan:
                    </div>
                    <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                      {selectedProject.description}
                    </p>
                  </div>

                  <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1.5">
                    <div className="font-extrabold text-slate-900 flex items-center gap-1.5">
                      <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0" />
                      <span>Standar Pelaksanaan Proyek PT. KGS</span>
                    </div>
                    <p className="leading-relaxed text-slate-600">
                      Dikerjakan oleh tim teknisi berpengalaman PT. Kreatif Global Solusindo dengan instrumen ukur terkalibrasi (OTDR, Earth Resistance Tester &lt; 2 Ohm, Fluke Cable Analyzer) dan standar keselamatan kerja (K3).
                    </p>
                  </div>

                  {/* Box Konsultasi Cepat */}
                  <div className="p-5 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50/70 border border-blue-200/80 space-y-3">
                    <h5 className="text-sm font-extrabold text-slate-900">
                      Butuh Implementasi Serupa untuk Instansi / Perusahaan Anda?
                    </h5>
                    <p className="text-xs text-slate-600 leading-relaxed">
                      Tim engineer PT. Kreatif Global Solusindo siap menjadwalkan survei lokasi dan menyusun RAB / BoQ sesuai kebutuhan lapangan.
                    </p>
                    <div className="flex flex-wrap gap-2 pt-1">
                      <button
                        type="button"
                        onClick={() =>
                          onRequestQuote(`Proyek Serupa: ${selectedProject.title}`)
                        }
                        className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                      >
                        Minta Penawaran Proyek Serupa →
                      </button>
                      <button
                        type="button"
                        onClick={() => setSelectedProjectId(null)}
                        className="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200 transition cursor-pointer"
                      >
                        Lihat Proyek Lainnya
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Bagian Bawah: Portofolio Proyek Lainnya */}
            <div className="pt-10 border-t border-slate-200">
              <div className="flex items-center justify-between mb-6">
                <div>
                  <span className="text-xs font-extrabold uppercase tracking-wider text-blue-600 block">
                    Eksplorasi Dokumentasi Lainnya
                  </span>
                  <h4 className="text-xl sm:text-2xl font-extrabold text-slate-900">
                    Portofolio Proyek Lainnya
                  </h4>
                </div>
                <button
                  type="button"
                  onClick={() => setSelectedProjectId(null)}
                  className="text-xs font-bold text-blue-600 hover:underline cursor-pointer"
                >
                  Lihat Semua ({projects.length}) →
                </button>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {projects
                  .filter(p => p.id !== selectedProject.id)
                  .slice(0, 6)
                  .map(rel => {
                    const relSlides = getProjectGallery(rel);
                    const relCover = relSlides[0]?.imageUrl || rel.imageUrl || DEFAULT_FALLBACK_IMAGE;
                    return (
                      <div
                        key={rel.id}
                        onClick={() => handleOpenProjectDetail(rel.id)}
                        className="group bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg hover:border-blue-500/50 transition flex flex-col cursor-pointer"
                      >
                        <div className="relative aspect-16/9 bg-slate-900 overflow-hidden">
                          <img
                            src={relCover}
                            alt={rel.title}
                            loading="lazy"
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                          />
                          <div className="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-transparent" />
                          <span className="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-blue-600/95 text-white text-[11px] font-bold">
                            🖼️ {relSlides.length} Gambar
                          </span>
                          <div className="absolute bottom-3 left-3.5 right-3.5">
                            <h5 className="text-sm sm:text-base font-extrabold text-white group-hover:text-blue-300 transition">
                              {rel.title}
                            </h5>
                          </div>
                        </div>
                        <div className="p-4 flex-1 flex items-center justify-between gap-3">
                          <span className="text-xs text-slate-600 line-clamp-1">
                            📍 {rel.clientOrLocation}
                          </span>
                          <span className="text-xs font-extrabold text-blue-600 shrink-0">
                            Read More →
                          </span>
                        </div>
                      </div>
                    );
                  })}
              </div>
            </div>
          </div>
        )}

        {/* ===================================================================== */}
        {/* TAB 2: DAFTAR KLIEN KORPORASI (B2B CLIENTS)                           */}
        {/* ===================================================================== */}
        {activeTab === 'clients' && (
          <div className="space-y-8">
            <div className="p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
              <div>
                <span className="text-xs font-extrabold uppercase tracking-wider text-blue-400">
                  Kepercayaan Lintas Industri Sejak 1997
                </span>
                <h3 className="text-xl font-extrabold mt-1">
                  Direktori Mitra BUMN, Perbankan, Rumah Sakit, Perkebunan & Instansi
                </h3>
                <p className="text-xs text-slate-300 mt-1 max-w-2xl">
                  Setiap entitas klien di bawah merupakan mitra implementasi nyata layanan jaringan, telekomunikasi PABX, CCTV, fabrikasi tower, penangkal petir, dan ISP KGSNet.
                </p>
              </div>
              {onOpenAdminSection && (
                <button
                  type="button"
                  onClick={() => onOpenAdminSection('clients')}
                  className="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5 shrink-0 cursor-pointer transition"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>+ Tambah Klien & Upload Foto</span>
                </button>
              )}
            </div>

            <div className="flex flex-wrap items-center gap-2 text-xs">
              <button
                onClick={() => setClientCategory('all')}
                className={`px-3.5 py-2 rounded-xl font-bold transition-colors cursor-pointer ${
                  clientCategory === 'all'
                    ? 'bg-blue-600 text-white shadow-xs'
                    : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                }`}
              >
                Semua Klien ({clients.length})
              </button>
              {clientCategories
                .filter(c => c !== 'all')
                .map(cat => (
                  <button
                    key={cat}
                    onClick={() => setClientCategory(cat)}
                    className={`px-3.5 py-2 rounded-xl font-semibold transition-colors cursor-pointer ${
                      clientCategory === cat
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                    }`}
                  >
                    {cat}
                  </button>
                ))}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {filteredClients.map(client => {
                const clientImg = client.imageUrl || client.logoUrl;
                const cleanName = client.name
                  .replace(/^(PT\.|CV\.|RS\.|RSUD|BANK|PERUMDA)\s*/i, '')
                  .trim();
                const words = cleanName.split(/\s+/);
                const monogram = (
                  (words[0]?.[0] || 'K') + (words[1]?.[0] || words[0]?.[1] || 'G')
                ).toUpperCase();

                return (
                  <div
                    key={client.id}
                    className="group rounded-2xl border border-slate-200 bg-white hover:border-blue-400 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between"
                  >
                    {clientImg && (
                      <div className="relative h-44 w-full bg-slate-100 border-b border-slate-100 flex items-center justify-center p-4 overflow-hidden">
                        <img
                          src={clientImg}
                          alt={client.name}
                          className="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"
                        />
                        <span className="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-slate-950 font-extrabold text-[10px] shadow-xs">
                          ✓ Mitra Terverifikasi
                        </span>
                      </div>
                    )}

                    <div className="p-5 flex-1 flex flex-col justify-between space-y-4">
                      <div className="flex items-start gap-3.5">
                        {!clientImg && (
                          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-slate-900 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs">
                            {monogram}
                          </div>
                        )}
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center justify-between gap-2 mb-1">
                            <span className="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[11px] truncate">
                              {client.category || 'Korporasi & Instansi'}
                            </span>
                            {!clientImg && (
                              <span className="text-emerald-600 font-bold text-[11px] flex items-center gap-0.5 shrink-0">
                                <CheckCircle2 className="w-3.5 h-3.5" />
                                <span>Terverifikasi</span>
                              </span>
                            )}
                          </div>
                          <h4 className="text-base font-extrabold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">
                            {client.name}
                          </h4>
                          {client.location && (
                            <div className="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                              <MapPin className="w-3.5 h-3.5 text-blue-500 shrink-0" />
                              <span>{client.location}</span>
                            </div>
                          )}
                        </div>
                      </div>

                      <div className="pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                        <div className="min-w-0">
                          <span className="text-[10px] uppercase tracking-wider font-bold text-slate-400 block">
                            Implementasi Solusi KGS
                          </span>
                          <span className="text-slate-800 font-bold truncate block">
                            {client.serviceCategory}
                          </span>
                        </div>
                        {client.websiteUrl && (
                          <a
                            href={client.websiteUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-blue-600 font-bold text-xs shrink-0 transition"
                          >
                            Detail ↗
                          </a>
                        )}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>

            {filteredClients.length === 0 && (
              <div className="p-12 text-center text-slate-500 text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                Tidak ada data klien yang cocok dengan pencarian "{searchTerm}".
              </div>
            )}
          </div>
        )}

        {/* Lightbox Fullscreen Modal */}
        {isLightboxOpen && selectedProject && currentSlide && (
          <div className="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md flex flex-col justify-between p-4 sm:p-6 select-none">
            <div className="flex items-center justify-between gap-4 text-white max-w-6xl w-full mx-auto">
              <div className="flex items-center gap-3">
                <span className="px-3 py-1 rounded-full bg-blue-600 text-xs font-extrabold">
                  {currentSlideIdx + 1} / {activeSlides.length}
                </span>
                <span className="text-sm font-bold text-slate-200 truncate max-w-md">
                  {selectedProject.title}
                </span>
              </div>
              <button
                type="button"
                onClick={() => setIsLightboxOpen(false)}
                className="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-red-600 text-white text-xs font-bold border border-slate-700 transition cursor-pointer"
              >
                ✕ Tutup
              </button>
            </div>

            <div className="relative flex-1 flex items-center justify-center my-4 max-w-6xl w-full mx-auto overflow-hidden">
              {activeSlides.length > 1 && (
                <button
                  type="button"
                  onClick={handlePrevSlide}
                  className="absolute left-2 sm:left-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer"
                >
                  <ChevronLeft className="w-6 h-6" />
                </button>
              )}

              <img
                src={currentSlide.imageUrl}
                alt={currentSlide.caption}
                className="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl"
              />

              {activeSlides.length > 1 && (
                <button
                  type="button"
                  onClick={handleNextSlide}
                  className="absolute right-2 sm:right-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-blue-600 text-white border border-white/20 flex items-center justify-center shadow-lg transition cursor-pointer"
                >
                  <ChevronRight className="w-6 h-6" />
                </button>
              )}
            </div>

            <div className="max-w-4xl w-full mx-auto text-center pb-2">
              <p className="text-sm sm:text-base font-semibold text-slate-200">
                {currentSlide.caption}
              </p>
            </div>
          </div>
        )}

        {/* Modal Buat Proyek Portofolio Baru + Banyak Gambar */}
        {isNewProjectModalOpen && (
          <div className="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
            <div className="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 my-8">
              <div className="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                  <span className="text-[11px] font-extrabold uppercase tracking-wider text-blue-400 block">
                    Portofolio Multi-Gambar
                  </span>
                  <h3 className="text-lg font-extrabold">
                    Tambah Proyek Portofolio & Banyak Gambar
                  </h3>
                </div>
                <button
                  type="button"
                  onClick={() => setIsNewProjectModalOpen(false)}
                  className="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white cursor-pointer"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleCreateNewProject} className="p-6 space-y-4 text-xs">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">
                      Judul Proyek / Instalasi *
                    </label>
                    <input
                      type="text"
                      required
                      value={newProjTitle}
                      onChange={e => setNewProjTitle(e.target.value)}
                      placeholder="Contoh: Instalasi Fiber Optik & Tower"
                      className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-600"
                    />
                  </div>
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">
                      Nama Klien / Lokasi Site *
                    </label>
                    <input
                      type="text"
                      required
                      value={newProjClient}
                      onChange={e => setNewProjClient(e.target.value)}
                      placeholder="Contoh: PT. Bukit Asam / Palembang"
                      className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-600"
                    />
                  </div>
                </div>

                <div>
                  <label className="block font-bold text-slate-700 mb-1">
                    Kategori Pekerjaan
                  </label>
                  <input
                    type="text"
                    value={newProjCategory}
                    onChange={e => setNewProjCategory(e.target.value)}
                    placeholder="Contoh: Networking & Tower"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-600"
                  />
                </div>

                <div>
                  <label className="block font-bold text-slate-700 mb-1">
                    Deskripsi Teknis Pekerjaan
                  </label>
                  <textarea
                    rows={3}
                    value={newProjDesc}
                    onChange={e => setNewProjDesc(e.target.value)}
                    placeholder="Jelaskan rincian teknis pekerjaan yang dilaksanakan..."
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-600"
                  />
                </div>

                {/* Upload Banyak File Gambar */}
                <div className="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-2">
                  <label className="block font-extrabold text-blue-950">
                    Upload Banyak File Foto Sekaligus
                  </label>
                  <div className="flex flex-wrap items-center gap-3">
                    <label className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold cursor-pointer transition">
                      <Upload className="w-3.5 h-3.5" />
                      <span>Pilih File Gambar (Bisa Banyak)...</span>
                      <input
                        type="file"
                        accept="image/*"
                        multiple
                        onChange={handleNewProjectFilesChange}
                        className="hidden"
                      />
                    </label>
                    {newProjUploadedFiles.length > 0 && (
                      <span className="font-bold text-emerald-700">
                        ✓ {newProjUploadedFiles.length} file dipilih
                      </span>
                    )}
                  </div>
                </div>

                <div>
                  <label className="block font-bold text-slate-700 mb-1">
                    Atau Masukkan Banyak URL Gambar (Satu URL per Baris)
                  </label>
                  <textarea
                    rows={3}
                    value={newProjUrls}
                    onChange={e => setNewProjUrls(e.target.value)}
                    placeholder={`https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?w=1200 :: Foto 1\nhttps://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200 :: Foto 2`}
                    className="w-full px-3.5 py-2 rounded-xl border border-slate-200 font-mono focus:outline-none focus:border-blue-600"
                  />
                </div>

                <div className="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => setIsNewProjectModalOpen(false)}
                    className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 cursor-pointer"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-sm cursor-pointer"
                  >
                    Simpan Proyek & Galeri
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </section>
  );
};
