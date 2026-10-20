import React from 'react';
import { PROJECT_DOCUMENTARY } from '../data/companyData';
import { Camera, MapPin, ShieldCheck, Wrench, Radio, Sun, Zap } from 'lucide-react';

export const DocumentarySection: React.FC = () => {
  return (
    <section id="dokumentasi" className="py-20 bg-slate-50 border-b border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="max-w-2xl mb-12">
          <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">
            Rekam Jejak Pekerjaan Lapangan
          </div>
          <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">
            Dokumentasi Pekerjaan Lapangan & Fabrikasi
          </h2>
          <p className="text-sm text-slate-600 mt-2 leading-relaxed">
            Rekam jejak instalasi fisik teknisi PT. KGS di berbagai lokasi proyek industri, perkebunan sawit, pembangkit listrik, dan rumah sakit.
          </p>
        </div>

        {/* Documentary Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {PROJECT_DOCUMENTARY.map((doc) => (
            <div
              key={doc.id}
              className="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition-all flex flex-col justify-between"
            >
              {/* Visual Carrier / Header with Icon */}
              <div className="p-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative">
                <div className="flex items-center justify-between text-xs text-blue-400 font-mono mb-2">
                  <span className="flex items-center gap-1">
                    <Camera className="w-3.5 h-3.5" />
                    <span>Dokumentasi Proyek</span>
                  </span>
                  <span className="text-slate-400">Standar KGS</span>
                </div>
                <h4 className="text-base font-bold text-white leading-snug">
                  {doc.title}
                </h4>
                <div className="flex items-center gap-1.5 text-xs text-slate-300 mt-2">
                  <MapPin className="w-3.5 h-3.5 text-blue-400 shrink-0" />
                  <span>{doc.clientOrLocation}</span>
                </div>
              </div>

              {/* Description Body */}
              <div className="p-5 flex-1 flex flex-col justify-between">
                <p className="text-xs text-slate-600 leading-relaxed mb-4">
                  {doc.description}
                </p>

                <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                  <span className="font-medium text-slate-700">{doc.category}</span>
                  <div className="flex items-center gap-1 text-emerald-600 font-medium text-[11px]">
                    <ShieldCheck className="w-3.5 h-3.5" />
                    <span>Proyek Terverifikasi</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Highlight Banner: Workshop Fabrikasi Sendiri */}
        <div className="mt-12 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="space-y-1">
            <div className="text-xs font-semibold text-blue-600 uppercase tracking-wider">
              Keunggulan Fasilitas Mandiri
            </div>
            <h3 className="text-lg font-bold text-slate-900">
              Workshop Fabrikasi Menara Guywire Tower Internal
            </h3>
            <p className="text-xs text-slate-600 max-w-2xl leading-relaxed">
              PT. KGS memiliki workshop pabrikasi mandiri di Palembang untuk perakitan menara Guywire Tower, pengelasan, dan uji ketahanan tanah (Earth Resistance &lt; 2 Ohm) sebelum ereksi di site klien.
            </p>
          </div>
          <div className="shrink-0 flex items-center gap-2 text-xs font-semibold px-4 py-2 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
            <Wrench className="w-4 h-4 text-blue-600" />
            <span>Workshop Creative Com Palembang</span>
          </div>
        </div>

      </div>
    </section>
  );
};
