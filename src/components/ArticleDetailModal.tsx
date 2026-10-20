import React from 'react';
import { ArticleItem } from '../data/companyData';
import { X, Calendar, User, Eye, ArrowRight, Clock, Tag } from 'lucide-react';

interface ArticleDetailModalProps {
  article: ArticleItem | null;
  onClose: () => void;
  onRequestQuote: () => void;
}

export const ArticleDetailModal: React.FC<ArticleDetailModalProps> = ({
  article,
  onClose,
  onRequestQuote
}) => {
  if (!article) return null;

  return (
    <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div className="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl max-h-[92vh] overflow-y-auto space-y-6 text-xs text-slate-700 animate-in zoom-in-95 duration-150">
        
        {/* Top Header */}
        <div className="flex items-center justify-between border-b border-slate-100 pb-4">
          <div className="flex items-center gap-2">
            <span className="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">
              {article.categoryName}
            </span>
            {article.readTime && (
              <span className="text-slate-400 text-xs flex items-center gap-1">
                <Clock className="w-3.5 h-3.5" />
                <span>{article.readTime}</span>
              </span>
            )}
          </div>
          <button
            onClick={onClose}
            className="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Featured Cover Image */}
        {article.imageUrl && (
          <div className="relative h-64 sm:h-80 w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 shadow-xs">
            <img
              src={article.imageUrl}
              alt={article.title}
              className="w-full h-full object-cover"
            />
          </div>
        )}

        {/* Title & Metadata */}
        <div className="space-y-3">
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 leading-tight">
            {article.title}
          </h2>

          <div className="flex flex-wrap items-center gap-4 text-slate-500 font-medium text-[11px] pb-2 border-b border-slate-100">
            <span className="flex items-center gap-1.5">
              <User className="w-3.5 h-3.5 text-slate-400" />
              <span>{article.author}</span>
            </span>
            <span className="flex items-center gap-1.5">
              <Calendar className="w-3.5 h-3.5 text-slate-400" />
              <span>{article.createdAt}</span>
            </span>
            <span className="flex items-center gap-1.5">
              <Eye className="w-3.5 h-3.5 text-slate-400" />
              <span>{article.views + 1} Pembaca</span>
            </span>
          </div>

          {/* Tags */}
          {article.labels && article.labels.length > 0 && (
            <div className="flex flex-wrap items-center gap-1.5 pt-1">
              <Tag className="w-3 h-3 text-slate-400" />
              {article.labels.map((lbl, idx) => (
                <span
                  key={idx}
                  className="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold"
                >
                  #{lbl}
                </span>
              ))}
            </div>
          )}
        </div>

        {/* Excerpt Summary */}
        <div className="p-4 bg-slate-50 border-l-4 border-blue-600 rounded-r-xl italic leading-relaxed text-slate-700 text-xs sm:text-sm">
          {article.summary}
        </div>

        {/* Full Content */}
        <div className="space-y-3 text-slate-700 leading-relaxed text-xs sm:text-sm whitespace-pre-line">
          {article.content}
        </div>

        {/* CTA Footer inside modal */}
        <div className="bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
          <div>
            <h4 className="font-bold text-sm">Konsultasi Solusi Terkait Topik Ini</h4>
            <p className="text-[11px] text-blue-100">Diskusikan kebutuhan teknis perusahaan Anda dengan tim engineer PT. Kreatif Global Solusindo.</p>
          </div>
          <button
            onClick={() => {
              onClose();
              onRequestQuote();
            }}
            className="px-5 py-2.5 bg-white text-blue-800 hover:bg-blue-50 font-bold rounded-xl text-xs shadow-md transition whitespace-nowrap cursor-pointer flex items-center gap-1.5"
          >
            <span>Minta Penawaran</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>
  );
};
