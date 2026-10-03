import React from 'react';
import { EmptyState } from './StateFeedback';

export default function PendingApprovalsTable({ approvals = [] }) {
    return (
        <div className="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
            {/* Header */}
            <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <h3 className="text-sm font-extrabold text-slate-800">
                        Antrean Approval Masuk ({approvals.length})
                    </h3>
                </div>
                <a
                    href="/approvals"
                    className="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1"
                >
                    <span>Buka Semua</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {/* Table or Empty State */}
            {approvals.length === 0 ? (
                <EmptyState
                    title="Tidak Ada Antrean Approval"
                    description="Seluruh pengajuan pembelian pada periode/divisi ini telah selesai diproses atau disetujui."
                />
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-slate-700 divide-y divide-slate-100">
                        <thead className="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th className="px-5 py-3">No. PR</th>
                                <th className="px-5 py-3">Judul Pengajuan</th>
                                <th className="px-5 py-3">Pemohon</th>
                                <th className="px-5 py-3">Divisi</th>
                                <th className="px-5 py-3 text-right">Nilai Estimasi</th>
                                <th className="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {approvals.map((item) => (
                                <tr key={item.id} className="hover:bg-slate-50/70 transition">
                                    <td className="px-5 py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                        {item.pr_number}
                                    </td>
                                    <td className="px-5 py-3 max-w-[200px] truncate font-semibold text-slate-800">
                                        {item.title}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-slate-600">
                                        {item.requester_name}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-slate-500">
                                        {item.department_name}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                                        {item.formatted_total}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-center">
                                        <a
                                            href={`/purchase-requests/${item.id}`}
                                            className="inline-flex items-center px-2.5 py-1 bg-slate-900 hover:bg-blue-600 text-white rounded-lg text-[11px] font-bold transition shadow-2xs"
                                        >
                                            Periksa &rarr;
                                        </a>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
