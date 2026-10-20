import React from 'react';
import { ServiceItem } from '../data/companyData';
import { X, CheckCircle, Tag, Building2, ShieldCheck, ArrowRight } from 'lucide-react';

interface ServiceDetailModalProps {
  service: ServiceItem | null;
  onClose: () => void;
  onRequestQuote: (serviceName: string) => void;
}

export const ServiceDetailModal: React.FC<ServiceDetailModalProps> = ({
  service,
  onClose,
  onRequestQuote
}) => {
  if (!service) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs overflow-y-auto">
      <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        
        {/* Modal Header */}
        <div className="p-6 border-b border-slate-100 flex items-start justify-between gap-4 sticky top-0 bg-white z-10">
          <div>
            <div className="flex items-center gap-2 text-xs text-blue-600 font-semibold mb-1">
              <span>{service.categoryName}</span>
              <span aria-hidden="true">·</span>
              <span className="text-emerald-700 font-medium flex items-center gap-1">
                <ShieldCheck className="w-3.5 h-3.5" />
                <span>Layanan Resmi PT. KGS</span>
              </span>
            </div>
            <h2 className="text-xl sm:text-2xl font-bold text-slate-900 leading-snug">
              {service.name}
            </h2>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Modal Content */}
        <div className="p-6 space-y-6 text-sm text-slate-700">
          
          {/* Description */}
          <div>
            <h3 className="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Deskripsi Layanan</h3>
            <p className="text-slate-700 leading-relaxed text-sm">
              {service.description}
            </p>
          </div>

          {/* Features / Spesifikasi */}
          {service.features && service.features.length > 0 && (
            <div>
              <h3 className="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2.5">
                Cakupan & Fitur Solusi
              </h3>
              <ul className="space-y-2">
                {service.features.map((feature, idx) => (
                  <li key={idx} className="flex items-start gap-2.5 text-xs text-slate-600">
                    <CheckCircle className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                    <span>{feature}</span>
                  </li>
                ))}
              </ul>
            </div>
          )}

          {/* Brands & Technologies */}
          {service.brands && service.brands.length > 0 && (
            <div>
              <h3 className="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">
                Brand & Ekosistem Perangkat
              </h3>
              <div className="flex flex-wrap gap-1.5">
                {service.brands.map((brand, idx) => (
                  <span
                    key={idx}
                    className="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded text-xs font-medium text-slate-700"
                  >
                    {brand}
                  </span>
                ))}
              </div>
            </div>
          )}

          {/* Target Sectors */}
          {service.targetSectors && service.targetSectors.length > 0 && (
            <div>
              <h3 className="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">
                Segmen Industri Pengguna
              </h3>
              <div className="flex flex-wrap gap-2 text-xs text-slate-600">
                {service.targetSectors.map((sector, idx) => (
                  <span key={idx} className="flex items-center gap-1">
                    <Building2 className="w-3.5 h-3.5 text-slate-400" />
                    <span>{sector}</span>
                    {idx < service.targetSectors.length - 1 && <span className="text-slate-300">·</span>}
                  </span>
                ))}
              </div>
            </div>
          )}

          {/* Standar Implementasi */}
          <div className="p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 space-y-1">
            <span className="font-bold flex items-center gap-1">
              <ShieldCheck className="w-4 h-4 text-blue-600" />
              <span>Jaminan Kualitas & Standar Pengerjaan:</span>
            </span>
            <p className="text-blue-800 leading-relaxed">
              Dikerjakan langsung oleh teknisi internal berpengalaman bersertifikasi K3 dengan dukungan alat ukur industri berkalibrasi resmi serta garansi suku cadang dan pemeliharaan berkala.
            </p>
          </div>

        </div>

        {/* Modal Footer */}
        <div className="p-6 border-t border-slate-100 flex items-center justify-between gap-4 bg-slate-50 rounded-b-2xl">
          <button
            onClick={onClose}
            className="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer"
          >
            Tutup
          </button>

          <button
            onClick={() => {
              onClose();
              onRequestQuote(service.name);
            }}
            className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-2 cursor-pointer"
          >
            <span>Konsultasi & Minta Penawaran</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>

      </div>
    </div>
  );
};
