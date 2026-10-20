import React, { useState } from 'react';
import { SERVICES_DATA, SERVICE_CATEGORIES } from '../data/companyData';
import { X, ShieldCheck, FileText, CheckCircle2, Search, Download, ExternalLink } from 'lucide-react';

interface PdfAuditModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const PdfAuditModal: React.FC<PdfAuditModalProps> = ({ isOpen, onClose }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCat, setSelectedCat] = useState<string>('all');

  if (!isOpen) return null;

  const filteredServices = SERVICES_DATA.filter((srv) => {
    const matchCat = selectedCat === 'all' || srv.categoryId === selectedCat;
    const matchSearch =
      searchTerm === '' ||
      srv.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      srv.categoryName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      srv.summary.toLowerCase().includes(searchTerm.toLowerCase()) ||
      `hal ${srv.pdfPage}`.includes(searchTerm.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/80 backdrop-blur-xs overflow-y-auto">
      <div className="bg-white rounded-2xl max-w-6xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
        
        {/* Modal Header */}
        <div className="p-6 bg-slate-900 text-white flex items-start justify-between gap-4">
          <div>
            <div className="flex items-center gap-2 text-xs font-semibold text-blue-400 mb-1">
              <ShieldCheck className="w-4 h-4 text-emerald-400" />
              <span>KATALOG STANDAR LAYANAN RESMI PT. KGS</span>
            </div>
            <h2 className="text-xl sm:text-2xl font-bold text-white tracking-tight">
              Tabel Katalog Layanan & Solusi Terintegrasi
            </h2>
            <p className="text-xs text-slate-300 mt-1 max-w-2xl">
              Daftar lengkap 22 standar layanan resmi PT. Kreatif Global Solusindo pada 5 segmen bisnis utama.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Filter and Summary Toolbar */}
        <div className="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
          <div className="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
            <button
              onClick={() => setSelectedCat('all')}
              className={`px-3 py-1.5 rounded font-semibold cursor-pointer transition ${
                selectedCat === 'all'
                  ? 'bg-slate-900 text-white'
                  : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'
              }`}
            >
              Semua ({SERVICES_DATA.length})
            </button>
            {SERVICE_CATEGORIES.map((c) => (
              <button
                key={c.id}
                onClick={() => setSelectedCat(c.id)}
                className={`px-3 py-1.5 rounded font-semibold cursor-pointer transition ${
                  selectedCat === c.id
                    ? 'bg-slate-900 text-white'
                    : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'
                }`}
              >
                Segmen {c.segmentNumber}
              </button>
            ))}
          </div>

          <div className="relative w-full sm:w-72">
            <Search className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Cari nama layanan atau ringkasan..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600"
            />
          </div>
        </div>

        {/* Table Body */}
        <div className="flex-1 overflow-y-auto p-4 sm:p-6">
          <div className="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
            <table className="w-full text-left text-xs text-slate-700 border-collapse">
              <thead className="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-bold uppercase text-[11px]">
                <tr>
                  <th className="p-3 w-12 text-center">No.</th>
                  <th className="p-3 w-48">Segmen Layanan</th>
                  <th className="p-3 w-64">Nama Layanan Resmi</th>
                  <th className="p-3">Ringkasan Spesifikasi</th>
                  <th className="p-3 w-36 text-center">Status Standar</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {filteredServices.map((srv, idx) => (
                  <tr key={srv.id} className="hover:bg-slate-50 transition-colors">
                    <td className="p-3 text-center font-mono text-slate-500 font-semibold">
                      {idx + 1}
                    </td>
                    <td className="p-3 font-semibold text-slate-900">
                      {srv.categoryName}
                    </td>
                    <td className="p-3">
                      <div className="font-bold text-blue-700">{srv.name}</div>
                      {srv.brands.length > 0 && (
                        <div className="text-[10px] text-slate-500 mt-0.5">
                          Brand: {srv.brands.join(', ')}
                        </div>
                      )}
                    </td>
                    <td className="p-3 text-slate-600 leading-relaxed max-w-md">
                      {srv.summary}
                    </td>
                    <td className="p-3 text-center">
                      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                        <CheckCircle2 className="w-3 h-3 text-emerald-600" />
                        <span>Standar Resmi KGS</span>
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Sitemap Structure */}
          <div className="mt-8 p-6 bg-slate-50 rounded-xl border border-slate-200">
            <h3 className="text-sm font-bold text-slate-900 mb-2">
              Struktur Navigasi Portal Website PT. KGS:
            </h3>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs text-slate-600">
              <div className="p-3 bg-white rounded-lg border border-slate-200">
                <span className="font-bold text-slate-900 block mb-1">1. Halaman Beranda</span>
                <p className="text-[11px] text-slate-500">Hero, 5 Segmen, Metrik Pengalaman 1997, Klien Terverifikasi, CTA Penawaran.</p>
              </div>
              <div className="p-3 bg-white rounded-lg border border-slate-200">
                <span className="font-bold text-slate-900 block mb-1">2. Katalog Layanan</span>
                <p className="text-[11px] text-slate-500">Filter 5 Segmen Resmi, Detail Spesifikasi per Layanan, Brand Terkait.</p>
              </div>
              <div className="p-3 bg-white rounded-lg border border-slate-200">
                <span className="font-bold text-slate-900 block mb-1">3. Portofolio & Klien</span>
                <p className="text-[11px] text-slate-500">Daftar 100+ Klien Nyata & Dokumentasi Proyek Lapangan.</p>
              </div>
              <div className="p-3 bg-white rounded-lg border border-slate-200">
                <span className="font-bold text-slate-900 block mb-1">4. Tentang & Kontak</span>
                <p className="text-[11px] text-slate-500">Sejarah 1997-2020, Visi Misi, Mitra ISP, Sertifikasi Resmi, Kantor Palembang & Jakarta.</p>
              </div>
            </div>
          </div>

        </div>

        {/* Modal Footer */}
        <div className="p-4 bg-slate-100 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
          <span>Total Layanan Terstandar: <strong>{SERVICES_DATA.length} Layanan</strong> PT. Kreatif Global Solusindo</span>
          <button
            onClick={onClose}
            className="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-lg cursor-pointer"
          >
            Tutup Jendela
          </button>
        </div>

      </div>
    </div>
  );
};
