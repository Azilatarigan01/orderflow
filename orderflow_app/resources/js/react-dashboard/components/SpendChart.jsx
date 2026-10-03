import React, { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

export default function SpendChart({ trend = [] }) {
    const canvasRef = useRef(null);
    const chartInstanceRef = useRef(null);

    useEffect(() => {
        if (!canvasRef.current) return;

        // Clean up previous chart instance
        if (chartInstanceRef.current) {
            chartInstanceRef.current.destroy();
            chartInstanceRef.current = null;
        }

        const labels = trend.map((t) => t.month);
        const prTotals = trend.map((t) => t.pr_total);
        const poTotals = trend.map((t) => t.po_total);

        const formatRupiah = (val) => {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
        };

        const ctx = canvasRef.current.getContext('2d');
        chartInstanceRef.current = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pengajuan PR',
                        data: prTotals,
                        borderColor: '#FF7A45', // Orange Coral
                        backgroundColor: 'rgba(255, 122, 69, 0.08)',
                        borderWidth: 3,
                        pointBackgroundColor: '#FF7A45',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Realisasi PO',
                        data: poTotals,
                        borderColor: '#0284C7', // Sky Cyan Blue
                        backgroundColor: 'rgba(2, 132, 199, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#0284C7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatRupiah(context.raw);
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#94A3B8',
                        },
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#94A3B8',
                            callback: function (value) {
                                if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'M';
                                if (value >= 1000000) return (value / 1000000).toFixed(0) + 'Jt';
                                return value;
                            },
                        },
                    },
                },
            },
        });

        return () => {
            if (chartInstanceRef.current) {
                chartInstanceRef.current.destroy();
                chartInstanceRef.current = null;
            }
        };
    }, [trend]);

    return (
        <div className="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                <div>
                    <h3 className="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <span>Tren Pengeluaran &amp; Anggaran</span>
                        <span className="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                            Realtime
                        </span>
                    </h3>
                    <p className="text-xs text-slate-400 mt-0.5">
                        Komparasi antara estimasi usulan belanja PR dan realisasi penerbitan PO
                    </p>
                </div>

                {/* Legend indicator */}
                <div className="flex items-center gap-4 text-xs font-semibold">
                    <span className="flex items-center gap-1.5 text-slate-700">
                        <span className="w-2.5 h-2.5 rounded-full bg-[#FF7A45]"></span>
                        <span>Usulan PR</span>
                    </span>
                    <span className="flex items-center gap-1.5 text-slate-700">
                        <span className="w-2.5 h-2.5 rounded-full bg-[#0284C7]"></span>
                        <span>Realisasi PO</span>
                    </span>
                </div>
            </div>

            {/* Canvas Container */}
            <div className="pt-4 pb-2 relative" style={{ height: '270px' }}>
                <canvas ref={canvasRef}></canvas>
            </div>

            {/* Chart Footer */}
            <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Nilai nominal dalam Rupiah (IDR)</span>
                <span>Otomatis disinkronkan sesuai filter</span>
            </div>
        </div>
    );
}
