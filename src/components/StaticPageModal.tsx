import React from 'react';
import { StaticPageItem } from '../data/companyData';
import { X, ShieldCheck } from 'lucide-react';

interface StaticPageModalProps {
  page: StaticPageItem | null;
  onClose: () => void;
  onRequestQuote: () => void;
}

export const StaticPageModal: React.FC<StaticPageModalProps> = ({
  page,
  onClose,
  onRequestQuote
}) => {
  if (!page) return null;

  return (
    <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-10 shadow-2xl max-h-[90vh] overflow-y-auto space-y-6 text-xs text-slate-700">
        <div className="flex items-center justify-between border-b pb-4">
          <span className="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider">
            Informasi Resmi PT. KGS
          </span>
          <button onClick={onClose} className="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 cursor-pointer">
            <X className="w-5 h-5" />
          </button>
        </div>

        <div className="space-y-2">
          <h2 className="text-2xl font-black text-slate-900">{page.title}</h2>
          {page.summary && <p className="text-slate-500 italic text-xs">{page.summary}</p>}
        </div>

        <div className="prose max-w-none text-slate-700 leading-relaxed text-xs whitespace-pre-line space-y-3">
          {page.content}
        </div>

        <div className="pt-6 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
          <span>Diperbarui pada: {page.updatedAt}</span>
          <button
            onClick={() => {
              onClose();
              onRequestQuote();
            }}
            className="text-blue-600 font-bold hover:underline cursor-pointer"
          >
            Hubungi Perusahaan &rarr;
          </button>
        </div>
      </div>
    </div>
  );
};
