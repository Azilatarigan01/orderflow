import React from 'react';
import { EmptyState } from './StateFeedback';

export default function RecentPrTable({ recentPrs = [] }) {
    const getBadgeStyle = (status) => {
        switch (status) {
            case 'approved':
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            case 'submitted':
                return 'bg-amber-50 text-amber-700 border-amber-200';
            case 'rejected':
                return 'bg-rose-50 text-rose-700 border-rose-200';
            case 'revision_required':
                return 'bg-orange-50 text-orange-700 border-orange-200';
            case 'draft':
                return 'bg-slate-50 text-slate-700 border-slate-200';
            default:
                return 'bg-blue-50 text-blue-700 border-blue-200';
        }
    };

    return (
        <div className="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
            {/* Card Header */}
            <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 className="text-sm font-extrabold text-slate-800">
                            Aktivitas Pengajuan Pembelian (PR) Terbaru
                        </h3>
                        <p className="text-[11px] text-slate-400 font-medium">
                            Dokumen PR yang baru diajukan atau diperbarui lintas unit kerja
                        </p>
                    </div>
                </div>
                <a
                    href="/purchase-requests"
                    className="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1"
                >
                    <span>Lihat Semua PR</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {/* Table / Empty State */}
            {recentPrs.length === 0 ? (
                <EmptyState
                    title="Belum Ada Pengajuan PR"
                    description="Belum ada transaksi pengajuan pembelian (PR) yang tercatat untuk filter ini."
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
                                <th className="px-5 py-3">Tanggal</th>
                                <th className="px-5 py-3 text-right">Nilai Estimasi</th>
                                <th className="px-5 py-3 text-center">Status</th>
                                <th className="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {recentPrs.map((item) => (
                                <tr key={item.id} className="hover:bg-slate-50/70 transition">
                                    <td className="px-5 py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                        {item.pr_number}
                                    </td>
                                    <td className="px-5 py-3 max-w-[220px] truncate font-semibold text-slate-800" title={item.title}>
                                        {item.title}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-slate-600">
                                        {item.requester_name}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap">
                                        <span className="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            {item.department_code}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-slate-500 text-[11px]">
                                        {item.created_at}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                                        {item.formatted_total}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-center">
                                        <span className={`inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border ${getBadgeStyle(item.status)}`}>
                                            {item.status_label || item.status}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-center">
                                        <a
                                            href={`/purchase-requests/${item.id}`}
                                            className="px-3 py-1 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg text-[11px] font-bold transition cursor-pointer inline-block"
                                        >
                                            Detail &rarr;
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
