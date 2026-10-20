import React, { useState } from 'react';
import { AuditLogItem } from '../../data/companyData';
import { ShieldCheck, Search, Clock } from 'lucide-react';

interface AdminAuditLogsProps {
  logs: AuditLogItem[];
}

export const AdminAuditLogs: React.FC<AdminAuditLogsProps> = ({ logs }) => {
  const [search, setSearch] = useState('');

  const filtered = logs.filter(
    l =>
      l.action.toLowerCase().includes(search.toLowerCase()) ||
      l.details.toLowerCase().includes(search.toLowerCase()) ||
      l.username.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-bold text-slate-900">Log Aktivitas & Jejak Audit Keamanan</h2>
        <p className="text-xs text-slate-500">
          Catatan riwayat aktivitas login admin, modifikasi data layanan, dan perubahan status leads.
        </p>
      </div>

      <div className="bg-white p-4 rounded-xl border border-slate-200">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
          <input
            type="text"
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari aktivitas, pengguna, atau rincian..."
            className="w-full text-xs pl-9 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <table className="w-full text-left text-xs text-slate-700">
          <thead className="bg-slate-50 uppercase text-[10px] text-slate-500 border-b">
            <tr>
              <th className="p-3.5 w-16">ID</th>
              <th className="p-3.5 w-36">Pengguna</th>
              <th className="p-3.5 w-44">Aktivitas</th>
              <th className="p-3.5">Detail Rincian Perubahan</th>
              <th className="p-3.5 w-32 font-mono">IP Address</th>
              <th className="p-3.5 w-40 text-right">Waktu</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {filtered.map(l => (
              <tr key={l.id} className="hover:bg-slate-50">
                <td className="p-3.5 font-mono text-slate-400">#{l.id}</td>
                <td className="p-3.5 font-bold text-slate-900">{l.username}</td>
                <td className="p-3.5">
                  <span className="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    {l.action}
                  </span>
                </td>
                <td className="p-3.5 text-slate-600 leading-relaxed">{l.details}</td>
                <td className="p-3.5 font-mono text-[11px] text-slate-500">{l.ipAddress}</td>
                <td className="p-3.5 text-right font-mono text-[11px] text-slate-400 whitespace-nowrap">
                  {l.createdAt}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
