import React from 'react';
import { EmptyState } from './StateFeedback';

export default function OverduePoTable({ overduePos = [] }) {
    return (
        <div className="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
            {/* Header */}
            <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <h3 className="text-sm font-extrabold text-slate-800">
                        Monitoring PO Terlambat ({overduePos.length})
                    </h3>
                </div>
                <a
                    href="/purchase-orders?status=overdue"
                    className="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1"
                >
                    <span>Detail Wanprestasi</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {/* Table or Empty State */}
            {overduePos.length === 0 ? (
                <EmptyState
                    title="Kinerja Pengiriman Optimal"
                    description="Tidak ada Purchase Order yang melewati batas target waktu pengiriman vendor."
                />
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-slate-700 divide-y divide-slate-100">
                        <thead className="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th className="px-5 py-3">No. PO</th>
                                <th className="px-5 py-3">Vendor / Rekanan</th>
                                <th className="px-5 py-3">Target Terima</th>
                                <th className="px-5 py-3 text-center">Keterlambatan</th>
                                <th className="px-5 py-3 text-right">Nilai PO</th>
                                <th className="px-5 py-3 text-right">Estimasi Denda</th>
                                <th className="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {overduePos.map((item) => (
                                <tr key={item.id} className="hover:bg-rose-50/30 transition">
                                    <td className="px-5 py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                        {item.po_number}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap font-medium text-slate-800">
                                        {item.vendor_name}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-slate-500">
                                        {item.delivery_target_date}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-center">
                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            +{item.overdue_days} Hari
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                                        {item.formatted_grand_total}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-right font-mono font-semibold text-rose-600">
                                        {item.formatted_penalty}
                                    </td>
                                    <td className="px-5 py-3 whitespace-nowrap text-center">
                                        <a
                                            href={`/purchase-orders/${item.id}`}
                                            className="inline-flex items-center px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[11px] font-bold transition shadow-2xs"
                                        >
                                            Surat Teguran &rarr;
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
