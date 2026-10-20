import React, { useState } from 'react';
import { ArticleItem, StaticPageItem } from '../data/companyData';
import { BookOpen, Calendar, ArrowRight, User, Tag, Clock, Edit3 } from 'lucide-react';
import { ArticleDetailModal } from './ArticleDetailModal';

interface ArticlesSectionProps {
  articles: ArticleItem[];
  onRequestQuote: () => void;
  pageContent?: StaticPageItem;
  isLiveEditMode?: boolean;
  onOpenAdminSection?: (tab: string) => void;
}

export const ArticlesSection: React.FC<ArticlesSectionProps> = ({
  articles,
  onRequestQuote,
  pageContent,
  isLiveEditMode = false,
  onOpenAdminSection
}) => {
  const [selectedArticle, setSelectedArticle] = useState<ArticleItem | null>(null);

  const publishedArticles = articles.filter(a => a.status === 'published');

  return (
    <section id="artikel" className="py-20 bg-slate-50 border-t border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
          <div className="space-y-2">
            <span className="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full uppercase tracking-wider">
              Edukasi & Wawasan Teknis
            </span>
            <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
              {pageContent?.title || 'Artikel Solusi Teknologi & Praktik Terbaik Industri'}
            </h2>
            <p className="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
              {pageContent?.summary || 'Panduan implementasi jaringan backbone fiber optik pabrik, standar pengujian grounding di bawah 2 Ohm, sistem identifikasi kapal AIS, dan keandalan ISP KGSNet.'}
            </p>
            {pageContent?.content && (
              <div className="mt-2 text-xs text-slate-600 max-w-3xl leading-relaxed whitespace-pre-line">
                {pageContent.content}
              </div>
            )}
          </div>

          {onOpenAdminSection && isLiveEditMode && (
            <button
              type="button"
              onClick={() => onOpenAdminSection('articles')}
              className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <Edit3 className="w-3.5 h-3.5" />
              <span>+ Tambah / Edit Artikel & Blog di Admin CMS</span>
            </button>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {publishedArticles.map(art => (
            <article
              key={art.id}
              onClick={() => setSelectedArticle(art)}
              className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group cursor-pointer"
            >
              <div>
                {/* Cover Image */}
                {art.imageUrl && (
                  <div className="relative h-48 w-full bg-slate-900 overflow-hidden">
                    <img
                      src={art.imageUrl}
                      alt={art.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                    <div className="absolute top-3 left-3">
                      <span className="font-bold text-white text-[11px] bg-blue-600/90 backdrop-blur-xs px-2.5 py-1 rounded-md shadow-xs">
                        {art.categoryName}
                      </span>
                    </div>
                    {art.readTime && (
                      <div className="absolute bottom-3 right-3 text-white text-[10px] font-semibold bg-slate-900/80 px-2 py-0.5 rounded backdrop-blur-xs flex items-center gap-1">
                        <Clock className="w-3 h-3 text-blue-400" />
                        <span>{art.readTime}</span>
                      </div>
                    )}
                  </div>
                )}

                <div className="p-6 sm:p-7 space-y-3.5">
                  {!art.imageUrl && (
                    <div className="flex items-center justify-between text-xs">
                      <span className="font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded">
                        {art.categoryName}
                      </span>
                      <span className="text-slate-400 font-mono flex items-center gap-1">
                        <Calendar className="w-3.5 h-3.5" />
                        <span>{art.createdAt}</span>
                      </span>
                    </div>
                  )}

                  <h3 className="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition leading-snug">
                    {art.title}
                  </h3>

                  <p className="text-base text-slate-600 leading-relaxed">
                    {art.summary}
                  </p>

                  {/* Labels / Tags */}
                  {art.labels && art.labels.length > 0 && (
                    <div className="flex flex-wrap items-center gap-1.5 pt-1">
                      {art.labels.map((lbl, idx) => (
                        <span
                          key={idx}
                          className="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded text-xs font-semibold"
                        >
                          #{lbl}
                        </span>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              <div className="bg-slate-50 px-6 sm:px-7 py-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span className="text-slate-600 text-xs flex items-center gap-1.5 font-medium">
                  <User className="w-3.5 h-3.5 text-slate-400" />
                  <span>{art.author}</span>
                </span>
                <span className="font-bold text-blue-600 group-hover:translate-x-0.5 transition flex items-center gap-1">
                  <span>Baca Selengkapnya</span>
                  <ArrowRight className="w-4 h-4" />
                </span>
              </div>
            </article>
          ))}
        </div>
      </div>

      <ArticleDetailModal
        article={selectedArticle}
        onClose={() => setSelectedArticle(null)}
        onRequestQuote={onRequestQuote}
      />
    </section>
  );
};
