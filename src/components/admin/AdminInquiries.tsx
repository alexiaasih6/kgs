import React, { useState } from 'react';
import { InquiryMessage, ServiceItem } from '../../data/companyData';
import { Trash2, CheckCircle2, Clock, Mail, Phone, MapPin, MessageSquare, Send, Plus, ChevronDown, ChevronUp } from 'lucide-react';

interface AdminInquiriesProps {
  inquiries: InquiryMessage[];
  services: ServiceItem[];
  onUpdateInquiries: (inquiries: InquiryMessage[]) => void;
}

export const AdminInquiries: React.FC<AdminInquiriesProps> = ({
  inquiries,
  services,
  onUpdateInquiries
}) => {
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [search, setSearch] = useState('');
  const [activeNoteInquiryId, setActiveNoteInquiryId] = useState<string | null>(null);
  const [newNoteText, setNewNoteText] = useState('');

  const handleStatusChange = (id: string, newStatus: 'Baru' | 'Diproses' | 'Selesai') => {
    onUpdateInquiries(
      inquiries.map(inq => (inq.id === id ? { ...inq, status: newStatus } : inq))
    );
  };

  const handleDelete = (id: string) => {
    if (confirm('Hapus permintaan penawaran ini?')) {
      onUpdateInquiries(inquiries.filter(inq => inq.id !== id));
    }
  };

  const handleAddNote = (inquiryId: string) => {
    if (!newNoteText.trim()) return;

    const note = {
      id: `note-${Date.now()}`,
      note: newNoteText.trim(),
      createdAt: new Date().toISOString().replace('T', ' ').slice(0, 16)
    };

    onUpdateInquiries(
      inquiries.map(inq =>
        inq.id === inquiryId
          ? { ...inq, notes: [note, ...(inq.notes || [])] }
          : inq
      )
    );
    setNewNoteText('');
  };

  const getServiceName = (serviceId: string) => {
    const s = services.find(srv => srv.id === serviceId);
    return s ? s.name : serviceId;
  };

  const filtered = inquiries.filter(inq => {
    const matchStatus = filterStatus === 'all' || inq.status === filterStatus;
    const matchSearch =
      inq.name.toLowerCase().includes(search.toLowerCase()) ||
      inq.company.toLowerCase().includes(search.toLowerCase()) ||
      inq.email.toLowerCase().includes(search.toLowerCase()) ||
      inq.phone.includes(search);
    return matchStatus && matchSearch;
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">
            Manajemen Permintaan Penawaran & Leads ({inquiries.length})
          </h2>
          <p className="text-xs text-slate-500 mt-0.5">
            Tindak lanjuti calon klien via WhatsApp direct link, kirim proposal, dan catat histori negosiasi.
          </p>
        </div>
      </div>

      {/* Filter & Search Bar */}
      <div className="bg-white p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          {['all', 'Baru', 'Diproses', 'Selesai'].map(st => (
            <button
              key={st}
              onClick={() => setFilterStatus(st)}
              className={`px-3 py-1.5 rounded-lg font-semibold transition ${
                filterStatus === st
                  ? 'bg-slate-900 text-white'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              }`}
            >
              {st === 'all' ? `Semua (${inquiries.length})` : `${st} (${inquiries.filter(i => i.status === st).length})`}
            </button>
          ))}
        </div>

        <input
          type="text"
          value={search}
          onChange={e => setSearch(e.target.value)}
          placeholder="Cari nama, perusahaan, telepon..."
          className="w-full sm:w-64 text-xs px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div className="space-y-4">
        {filtered.length === 0 ? (
          <div className="bg-white p-12 text-center rounded-2xl border text-slate-400 text-xs">
            Tidak ada permintaan penawaran yang sesuai filter.
          </div>
        ) : (
          filtered.map(inq => {
            const cleanPhone = inq.phone.replace(/[^0-9]/g, '');
            const waNumber = cleanPhone.startsWith('0') ? '62' + cleanPhone.slice(1) : cleanPhone;
            const waLink = `https://wa.me/${waNumber}?text=${encodeURIComponent(
              `Halo Bapak/Ibu ${inq.name} (${inq.company}), terima kasih telah menghubungi PT. Kreatif Global Solusindo terkait permintaan ${getServiceName(
                inq.serviceId
              )}.`
            )}`;
            const emailLink = `mailto:${inq.email}?subject=${encodeURIComponent(
              `Penawaran Solusi: ${getServiceName(inq.serviceId)} - PT. KGS`
            )}`;
            const isNoteOpen = activeNoteInquiryId === inq.id;

            return (
              <div
                key={inq.id}
                className="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 hover:border-slate-300 transition"
              >
                <div className="space-y-3">
                  <div className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div className="flex items-center gap-3">
                      <span className="font-extrabold text-base text-slate-900">{inq.name}</span>
                      <span className="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded">
                        {inq.company || 'Perorangan / Instansi'}
                      </span>
                    </div>
                    <span className="text-[11px] text-slate-400 font-mono">{inq.createdAt}</span>
                  </div>

                  <div className="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                    <span className="flex items-center gap-1.5 font-medium">
                      <Mail className="w-3.5 h-3.5 text-slate-400" />
                      <span>{inq.email}</span>
                    </span>
                    <span className="flex items-center gap-1.5 font-medium">
                      <Phone className="w-3.5 h-3.5 text-slate-400" />
                      <span>{inq.phone}</span>
                    </span>
                    <span className="flex items-center gap-1.5 font-medium">
                      <MapPin className="w-3.5 h-3.5 text-slate-400" />
                      <span>Lokasi: {inq.location}</span>
                    </span>
                  </div>

                  <div className="text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1">
                    <div className="text-blue-700 font-bold">
                      Layanan Diminta: {getServiceName(inq.serviceId)}
                    </div>
                    <p className="text-slate-700 leading-relaxed whitespace-pre-line">{inq.message}</p>
                  </div>
                </div>

                {/* Communication and Follow-up Actions */}
                <div className="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                  <div className="flex items-center gap-2">
                    <a
                      href={waLink}
                      target="_blank"
                      rel="noreferrer"
                      className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-2xs flex items-center gap-1.5 transition"
                    >
                      <span>💬 Balas WhatsApp</span>
                    </a>
                    <a
                      href={emailLink}
                      className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-2xs flex items-center gap-1.5 transition"
                    >
                      <span>📧 Email</span>
                    </a>
                    <button
                      onClick={() => setActiveNoteInquiryId(isNoteOpen ? null : inq.id)}
                      className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border transition flex items-center gap-1"
                    >
                      <MessageSquare className="w-3.5 h-3.5" />
                      <span>Catatan Tindak Lanjut ({inq.notes?.length || 0})</span>
                      {isNoteOpen ? <ChevronUp className="w-3 h-3" /> : <ChevronDown className="w-3 h-3" />}
                    </button>
                  </div>

                  <div className="flex items-center gap-3">
                    <select
                      value={inq.status}
                      onChange={e => handleStatusChange(inq.id, e.target.value as any)}
                      className={`text-xs font-bold px-3 py-1.5 rounded-lg border cursor-pointer ${
                        inq.status === 'Baru'
                          ? 'bg-amber-50 text-amber-800 border-amber-300'
                          : inq.status === 'Diproses'
                          ? 'bg-blue-50 text-blue-800 border-blue-300'
                          : 'bg-emerald-50 text-emerald-800 border-emerald-300'
                      }`}
                    >
                      <option value="Baru">Status: Baru</option>
                      <option value="Diproses">Status: Diproses</option>
                      <option value="Selesai">Status: Selesai</option>
                    </select>

                    <button
                      onClick={() => handleDelete(inq.id)}
                      className="text-rose-600 hover:underline text-xs"
                    >
                      Hapus
                    </button>
                  </div>
                </div>

                {/* Follow-up Notes Timeline */}
                {isNoteOpen && (
                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3 text-xs">
                    <div className="font-bold text-slate-800">Riwayat Catatan Negosiasi / Tindak Lanjut:</div>

                    <div className="flex gap-2">
                      <input
                        type="text"
                        value={newNoteText}
                        onChange={e => setNewNoteText(e.target.value)}
                        placeholder="Tulis catatan (misal: Sudah telepon PIC, penawaran harga dikirim via email)..."
                        className="flex-1 text-xs px-3 py-2 border rounded-lg bg-white"
                        onKeyDown={e => {
                          if (e.key === 'Enter') {
                            e.preventDefault();
                            handleAddNote(inq.id);
                          }
                        }}
                      />
                      <button
                        onClick={() => handleAddNote(inq.id)}
                        className="px-4 py-2 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800"
                      >
                        Tambah Catatan
                      </button>
                    </div>

                    <div className="space-y-2 pt-2">
                      {!inq.notes || inq.notes.length === 0 ? (
                        <p className="text-slate-400 italic">Belum ada catatan tindak lanjut.</p>
                      ) : (
                        inq.notes.map(n => (
                          <div key={n.id} className="p-3 bg-white rounded-lg border border-slate-200 space-y-1">
                            <div className="flex justify-between text-[10px] text-slate-400 font-mono">
                              <span className="font-bold text-slate-700">Internal Admin Note</span>
                              <span>{n.createdAt}</span>
                            </div>
                            <p className="text-slate-800">{n.note}</p>
                          </div>
                        ))
                      )}
                    </div>
                  </div>
                )}
              </div>
            );
          })
        )}
      </div>
    </div>
  );
};
