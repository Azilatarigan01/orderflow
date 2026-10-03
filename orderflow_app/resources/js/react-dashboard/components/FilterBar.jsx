import React from 'react';

export default function FilterBar({
    period,
    onPeriodChange,
    departmentId,
    onDepartmentChange,
    departments = [],
    onRefresh,
    isRefreshing = false,
    lastUpdated = null,
    onReset,
}) {
    const isFiltered = period !== 'this_month' || departmentId !== '';

    return (
        <div className="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3.5">
            {/* Left: Filter Controls */}
            <div className="flex flex-wrap items-center gap-2.5 sm:gap-3">
                {/* Period Selector */}
                <div className="flex items-center space-x-2">
                    <label htmlFor="filter-period" className="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                        <svg
                            className="w-3.5 h-3.5 text-slate-400 flex-shrink-0"
                            width="14"
                            height="14"
                            style={{ width: '14px', height: '14px', minWidth: '14px' }}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Periode:</span>
                    </label>
                    <select
                        id="filter-period"
                        value={period}
                        onChange={(e) => onPeriodChange(e.target.value)}
                        className="bg-slate-50 hover:bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200/80 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer shadow-2xs"
                    >
                        <option value="this_month">Bulan Ini</option>
                        <option value="last_month">Bulan Lalu</option>
                        <option value="this_quarter">Triwulan Ini</option>
                        <option value="this_year">Tahun Ini ({new Date().getFullYear()})</option>
                        <option value="all">Semua Data (1 Tahun)</option>
                    </select>
                </div>

                {/* Department Selector */}
                <div className="flex items-center space-x-2">
                    <label htmlFor="filter-dept" className="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                        <svg
                            className="w-3.5 h-3.5 text-slate-400 flex-shrink-0"
                            width="14"
                            height="14"
                            style={{ width: '14px', height: '14px', minWidth: '14px' }}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Divisi:</span>
                    </label>
                    <select
                        id="filter-dept"
                        value={departmentId}
                        onChange={(e) => onDepartmentChange(e.target.value)}
                        className="bg-slate-50 hover:bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200/80 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer shadow-2xs max-w-xs truncate"
                    >
                        <option value="">Semua Divisi / Unit Kerja</option>
                        {departments.map((dept) => (
                            <option key={dept.id} value={dept.id}>
                                {dept.name} ({dept.code})
                            </option>
                        ))}
                    </select>
                </div>

                {/* Reset Filter Button */}
                {isFiltered && (
                    <button
                        onClick={onReset}
                        className="text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200 transition flex items-center gap-1 cursor-pointer"
                        title="Kembalikan filter"
                    >
                        <span>✕</span>
                        <span>Reset</span>
                    </button>
                )}
            </div>

            {/* Right: Refresh Status & Trigger */}
            <div className="flex items-center justify-between sm:justify-end gap-3 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                {lastUpdated && (
                    <div className="text-[11px] text-slate-400 font-medium">
                        Sinkron: <span className="font-mono text-slate-600 font-semibold">{lastUpdated}</span>
                    </div>
                )}

                <button
                    onClick={onRefresh}
                    disabled={isRefreshing}
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 rounded-xl text-xs font-bold transition shadow-xs disabled:opacity-50 cursor-pointer active:scale-95"
                    title="Segarkan data"
                >
                    <svg
                        className={`w-3.5 h-3.5 text-slate-500 flex-shrink-0 ${isRefreshing ? 'animate-spin' : ''}`}
                        width="14"
                        height="14"
                        style={{ width: '14px', height: '14px', minWidth: '14px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{isRefreshing ? 'Memuat...' : 'Segarkan'}</span>
                </button>
            </div>
        </div>
    );
}
