import React from 'react';

export default function KpiCards({ kpis = {} }) {
    const {
        formatted_spend = 'Rp 0',
        total_pr = 0,
        pending_approval_pr = 0,
        active_po = 0,
        overdue_po = 0,
    } = kpis;

    const cards = [
        {
            id: 'pr_total',
            title: 'Total PR',
            value: total_pr.toString(),
            ringBorder: 'border-orange-400 bg-orange-50/70 text-orange-500',
            symbol: '↗',
        },
        {
            id: 'pending_appr',
            title: 'Perlu Approval',
            value: pending_approval_pr.toString(),
            ringBorder: 'border-sky-400 bg-sky-50/70 text-sky-500',
            symbol: '↙',
        },
        {
            id: 'active_po',
            title: 'PO Berjalan',
            value: active_po.toString(),
            ringBorder: 'border-amber-400 bg-amber-50/70 text-amber-500',
            symbol: '↗',
        },
        {
            id: 'spend',
            title: 'Realisasi PO',
            value: formatted_spend,
            ringBorder: 'border-emerald-400 bg-emerald-50/70 text-emerald-500',
            symbol: '↙',
        },
        {
            id: 'overdue_po',
            title: 'PO Terlambat',
            value: overdue_po.toString(),
            ringBorder: overdue_po > 0
                ? 'border-rose-500 bg-rose-50 text-rose-600 animate-pulse'
                : 'border-purple-400 bg-purple-50/70 text-purple-500',
            symbol: overdue_po > 0 ? '!' : '✓',
            isAlert: overdue_po > 0,
        },
    ];

    return (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
            {cards.map((card) => (
                <div
                    key={card.id}
                    className={`card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition ${
                        card.isAlert ? 'ring-2 ring-rose-300/80 bg-rose-50/20' : ''
                    }`}
                >
                    {/* Ring Icon */}
                    <div
                        className={`w-11 h-11 sm:w-12 sm:h-12 rounded-full border-[3px] flex items-center justify-center flex-shrink-0 font-bold text-base sm:text-lg shadow-2xs ${card.ringBorder}`}
                        style={{ minWidth: '44px', minHeight: '44px' }}
                    >
                        <span className="transform -rotate-12 leading-none">{card.symbol}</span>
                    </div>

                    {/* Metric Information */}
                    <div className="min-w-0 flex-1">
                        <div className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none font-mono truncate">
                            {card.value}
                        </div>
                        <div className="text-xs text-slate-500 font-semibold mt-1 truncate">
                            {card.title}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
