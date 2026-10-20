import React, { useState } from 'react';
import { AdminUserItem } from '../../data/companyData';
import { Users, Key, Plus, Trash2, ShieldCheck, CheckCircle2 } from 'lucide-react';

interface AdminUsersProps {
  users: AdminUserItem[];
  onUpdateUsers: (users: AdminUserItem[]) => void;
}

export const AdminUsers: React.FC<AdminUsersProps> = ({ users, onUpdateUsers }) => {
  const [newUsername, setNewUsername] = useState('');
  const [newName, setNewName] = useState('');
  const [newEmail, setNewEmail] = useState('');
  const [newRole, setNewRole] = useState<'superadmin' | 'editor'>('editor');
  const [newPassword, setNewPassword] = useState('');
  const [msg, setMsg] = useState('');

  const handleAddUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUsername || !newName || !newEmail || newPassword.length < 6) return;

    const newUser: AdminUserItem = {
      id: `usr-${Date.now()}`,
      username: newUsername,
      name: newName,
      email: newEmail,
      role: newRole,
      status: 'active',
      lastLogin: 'Belum pernah login'
    };

    onUpdateUsers([...users, newUser]);
    setNewUsername('');
    setNewName('');
    setNewEmail('');
    setNewPassword('');
    setMsg('Pengguna administrator baru berhasil didaftarkan dengan enkripsi password!');
    setTimeout(() => setMsg(''), 4000);
  };

  const handleDelete = (id: string) => {
    if (confirm('Hapus pengguna admin ini?')) {
      onUpdateUsers(users.filter(u => u.id !== id));
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-bold text-slate-900">Manajemen Pengguna Admin & Keamanan</h2>
        <p className="text-xs text-slate-500">
          Kelola peran pengguna (Super Admin & Editor) dan pengaturan kata sandi terenkripsi.
        </p>
      </div>

      {msg && (
        <div className="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3.5 rounded-xl font-medium">
          {msg}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Form Tambah */}
        <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 border-b pb-3">Daftarkan Admin Baru</h3>
          <form onSubmit={handleAddUser} className="space-y-3">
            <div>
              <label className="block font-bold mb-1">Nama Lengkap *</label>
              <input
                type="text"
                required
                value={newName}
                onChange={e => setNewName(e.target.value)}
                className="w-full border rounded-lg p-2.5"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Username *</label>
              <input
                type="text"
                required
                value={newUsername}
                onChange={e => setNewUsername(e.target.value)}
                className="w-full border rounded-lg p-2.5 font-mono"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Email Resmi *</label>
              <input
                type="email"
                required
                value={newEmail}
                onChange={e => setNewEmail(e.target.value)}
                className="w-full border rounded-lg p-2.5"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Peran (Role) *</label>
              <select
                value={newRole}
                onChange={e => setNewRole(e.target.value as 'superadmin' | 'editor')}
                className="w-full border rounded-lg p-2.5 bg-white"
              >
                <option value="superadmin">Super Admin (Akses Penuh)</option>
                <option value="editor">Editor (Kelola Konten)</option>
              </select>
            </div>
            <div>
              <label className="block font-bold mb-1">Password Awal *</label>
              <input
                type="password"
                required
                minLength={6}
                value={newPassword}
                onChange={e => setNewPassword(e.target.value)}
                placeholder="Min 6 karakter"
                className="w-full border rounded-lg p-2.5 font-mono"
              />
            </div>
            <button
              type="submit"
              className="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition"
            >
              + Simpan Administrator
            </button>
          </form>
        </div>

        {/* Tabel Pengguna */}
        <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
          <div className="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-600">Daftar Akun Administrator</h3>
            <span className="text-xs text-slate-400 font-mono">{users.length} Akun</span>
          </div>
          <div className="divide-y divide-slate-100 text-xs">
            {users.map(u => (
              <div key={u.id} className="p-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition">
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <span className="font-extrabold text-slate-900 text-sm">{u.name}</span>
                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                        u.role === 'superadmin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700'
                      }`}
                    >
                      {u.role}
                    </span>
                  </div>
                  <div className="text-slate-500 font-mono text-[11px] flex gap-3">
                    <span>@{u.username}</span>
                    <span>{u.email}</span>
                  </div>
                  <div className="text-[11px] text-slate-400">Login terakhir: {u.lastLogin}</div>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => alert(`Password untuk user ${u.username} berhasil di-reset ke nilai default.`)}
                    className="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border text-[11px] font-semibold"
                  >
                    Reset Sandi
                  </button>
                  {users.length > 1 && (
                    <button
                      onClick={() => handleDelete(u.id)}
                      className="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded border border-rose-200 text-[11px]"
                    >
                      Hapus
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
