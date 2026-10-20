import React, { useState, useMemo } from 'react';
import JSZip from 'jszip';
import { PHP_CODEBASE } from '../data/phpCodebase';
import {
  ExportStatePayload,
  buildDynamicSqlPatch,
  buildLivePreviewJson,
  computeExportBuildId,
  syncLiveStateToServer
} from '../utils/exportSync';
import {
  X,
  Copy,
  Check,
  Database,
  Code,
  ShieldCheck,
  Terminal,
  Download,
  Loader2,
  FolderArchive,
  RefreshCw
} from 'lucide-react';

interface PhpExportModalProps {
  isOpen: boolean;
  onClose: () => void;
  onOpenAdmin: () => void;
  exportState?: ExportStatePayload;
}

export const PhpExportModal: React.FC<PhpExportModalProps> = ({
  isOpen,
  onClose,
  onOpenAdmin,
  exportState
}) => {
  const [selectedFile, setSelectedFile] = useState<string>('database.sql');
  const [copied, setCopied] = useState(false);
  const [isGeneratingZip, setIsGeneratingZip] = useState(false);

  const buildId = useMemo(
    () => (exportState ? computeExportBuildId(exportState) : 'KGS-DEFAULT'),
    [exportState]
  );

  // Merge live preview state into PHP_CODEBASE dynamically so viewer + ZIP always match preview 1:1
  const effectivePhpFiles = useMemo(() => {
    const merged: Record<string, string> = {};
    PHP_CODEBASE.forEach((entry) => {
      merged[entry.path] = entry.code;
    });

    if (exportState) {
      const sqlPatch = buildDynamicSqlPatch(exportState);
      if (merged['database.sql']) {
        merged['database.sql'] = merged['database.sql'] + '\n' + sqlPatch;
      }
      const liveJson = buildLivePreviewJson(exportState);
      merged['config/live-preview-state.json'] = liveJson;
      if (exportState.inlineOverrides && Object.keys(exportState.inlineOverrides).length > 0) {
        merged['config/live-overrides.json'] = JSON.stringify(
          exportState.inlineOverrides,
          null,
          2
        );
      }
    }
    return merged;
  }, [exportState]);

  const effectiveFileList = useMemo(() => {
    const list = PHP_CODEBASE.map((entry) => ({
      name: entry.path,
      desc: entry.description,
      icon: entry.category === 'database' ? 'db' : 'code'
    }));
    if (!list.some((f) => f.name === 'config/live-preview-state.json')) {
      list.splice(3, 0, {
        name: 'config/live-preview-state.json',
        desc: 'Snapshot Sinkronisasi Live Edit & Admin CMS (Identik dengan Preview)',
        icon: 'code'
      });
    }
    return list;
  }, []);

  if (!isOpen) return null;

  const handleCopy = () => {
    const content = effectivePhpFiles[selectedFile] || '';
    navigator.clipboard.writeText(content);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handleDownloadSingleFile = (filename: string, content: string) => {
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename.replace(/\//g, '_');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  const handleDownloadSqlOnly = () => {
    if (exportState) {
      syncLiveStateToServer(exportState);
    }
    const sqlContent = effectivePhpFiles['database.sql'] || '';
    const blob = new Blob([sqlContent], { type: 'application/sql;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'database.sql';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  const handleDownloadZip = async () => {
    setIsGeneratingZip(true);
    try {
      if (exportState) {
        await syncLiveStateToServer(exportState);
      }

      let zip = new JSZip();
      try {
        const res = await fetch(`/KGS-WEBSITE-HOSTING.zip?v=${encodeURIComponent(buildId)}&t=${Date.now()}`);
        if (res.ok) {
          const buf = await res.arrayBuffer();
          zip = await JSZip.loadAsync(buf);
        } else {
          Object.entries(effectivePhpFiles).forEach(([filePath, content]) => {
            zip.file(filePath, content);
          });
        }
      } catch {
        Object.entries(effectivePhpFiles).forEach(([filePath, content]) => {
          zip.file(filePath, content);
        });
      }

      // Always inject the latest Live Edit & Admin CMS state into the ZIP package
      if (effectivePhpFiles['database.sql']) {
        zip.file('database.sql', effectivePhpFiles['database.sql']);
      }
      if (effectivePhpFiles['config/live-preview-state.json']) {
        zip.file('config/live-preview-state.json', effectivePhpFiles['config/live-preview-state.json']);
      }
      if (effectivePhpFiles['config/live-overrides.json']) {
        zip.file('config/live-overrides.json', effectivePhpFiles['config/live-overrides.json']);
      }

      const blob = await zip.generateAsync({
        type: 'blob',
        compression: 'DEFLATE',
        compressionOptions: { level: 6 }
      });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'KGS-WEBSITE-HOSTING.zip';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Failed to generate ZIP:', err);
    } finally {
      setIsGeneratingZip(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-sm">
      <div className="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-6xl h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        {/* Top Header */}
        <div className="px-5 py-4 bg-slate-950 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-blue-400">
              <FolderArchive className="w-5 h-5" />
            </div>
            <div>
              <div className="flex items-center gap-2 flex-wrap">
                <h2 className="text-white font-extrabold text-base sm:text-lg">
                  Paket Source Code PHP Native + MySQL (Siap Upload Hosting)
                </h2>
                <span className="px-2.5 py-0.5 rounded-md bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-mono font-bold flex items-center gap-1">
                  <RefreshCw className="w-3 h-3" />
                  Build Sync: {buildId}
                </span>
              </div>
              <p className="text-slate-400 text-xs mt-0.5">
                Seluruh hasil Live Edit &amp; Admin CMS pada preview ini otomatis tersinkron ke dalam{' '}
                <code className="text-emerald-300">database.sql</code> &amp;{' '}
                <code className="text-emerald-300">config/live-preview-state.json</code> di dalam ZIP.
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={handleDownloadSqlOnly}
              className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
            >
              <Database className="w-3.5 h-3.5 text-amber-400" />
              Download database.sql
            </button>
            <button
              onClick={handleDownloadZip}
              disabled={isGeneratingZip}
              className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold flex items-center gap-2 shadow-lg shadow-emerald-900/30 transition cursor-pointer disabled:opacity-60"
            >
              {isGeneratingZip ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" />
                  Menyiapkan ZIP...
                </>
              ) : (
                <>
                  <Download className="w-4 h-4" />
                  Download KGS-WEBSITE-HOSTING.zip
                </>
              )}
            </button>
            <button
              onClick={onClose}
              className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer"
              title="Tutup"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Info Banner */}
        <div className="px-5 py-2.5 bg-blue-950/50 border-b border-blue-900/50 flex flex-wrap items-center justify-between gap-2 text-xs text-blue-200">
          <div className="flex items-center gap-2">
            <ShieldCheck className="w-4 h-4 text-emerald-400 shrink-0" />
            <span>
              <strong>Sinkronisasi Preview &harr; Import Hosting Aktif:</strong> Paket ZIP di atas sudah
              menyertakan perubahan Live Edit terbaru, tema Dark/Light Mode, label footer{' '}
              <strong>KGSNET</strong>, dan pengaturan <strong>Alamat Kantor</strong>.
            </span>
          </div>
          <button
            onClick={() => {
              onClose();
              onOpenAdmin();
            }}
            className="text-amber-300 hover:text-amber-200 font-bold underline cursor-pointer"
          >
            Buka Admin CMS &rarr;
          </button>
        </div>

        {/* Body */}
        <div className="flex-1 flex flex-col md:flex-row overflow-hidden">
          {/* Left File List */}
          <div className="w-full md:w-80 bg-slate-950 border-b md:border-b-0 md:border-r border-slate-800 overflow-y-auto p-3 space-y-1">
            <div className="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-2.5 py-1.5 flex items-center justify-between">
              <span>Daftar File ({effectiveFileList.length})</span>
              <span className="text-emerald-400 font-mono">{buildId}</span>
            </div>
            {effectiveFileList.map((file) => {
              const active = selectedFile === file.name;
              return (
                <button
                  key={file.name}
                  onClick={() => setSelectedFile(file.name)}
                  className={`w-full text-left px-3 py-2.5 rounded-xl transition flex items-start gap-2.5 cursor-pointer ${
                    active
                      ? 'bg-blue-600 text-white shadow-sm'
                      : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                  }`}
                >
                  {file.icon === 'db' ? (
                    <Database
                      className={`w-4 h-4 mt-0.5 shrink-0 ${
                        active ? 'text-white' : 'text-amber-400'
                      }`}
                    />
                  ) : (
                    <Code
                      className={`w-4 h-4 mt-0.5 shrink-0 ${
                        active ? 'text-white' : 'text-blue-400'
                      }`}
                    />
                  )}
                  <div className="min-w-0 flex-1">
                    <div className="text-xs font-bold truncate font-mono">{file.name}</div>
                    <div
                      className={`text-[11px] truncate ${
                        active ? 'text-blue-100' : 'text-slate-400'
                      }`}
                    >
                      {file.desc}
                    </div>
                  </div>
                </button>
              );
            })}
          </div>

          {/* Right Code Viewer */}
          <div className="flex-1 flex flex-col bg-slate-900 overflow-hidden">
            <div className="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between gap-2">
              <div className="flex items-center gap-2 min-w-0">
                <Terminal className="w-4 h-4 text-blue-400 shrink-0" />
                <span className="text-xs font-mono font-bold text-white truncate">
                  {selectedFile}
                </span>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() =>
                    handleDownloadSingleFile(selectedFile, effectivePhpFiles[selectedFile] || '')
                  }
                  className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                >
                  <Download className="w-3.5 h-3.5" />
                  Unduh File Ini
                </button>
                <button
                  onClick={handleCopy}
                  className="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                >
                  {copied ? (
                    <>
                      <Check className="w-3.5 h-3.5" />
                      Tersalin!
                    </>
                  ) : (
                    <>
                      <Copy className="w-3.5 h-3.5" />
                      Salin Kode
                    </>
                  )}
                </button>
              </div>
            </div>

            <div className="flex-1 overflow-auto p-4 font-mono text-xs text-slate-200 leading-relaxed select-all">
              <pre className="whitespace-pre-wrap break-words">
                {effectivePhpFiles[selectedFile] || '// File tidak ditemukan'}
              </pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
