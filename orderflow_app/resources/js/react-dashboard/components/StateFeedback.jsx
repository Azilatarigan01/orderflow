import React from 'react';

/**
 * Skeleton Loader for Dashboard Initial Load & Refetching
 */
export function SkeletonLoader() {
    return (
        <div className="space-y-6 animate-pulse">
            {/* Filter Bar Skeleton */}
            <div className="h-16 bg-white/70 rounded-2xl border border-slate-200/80 p-4 flex items-center justify-between">
                <div className="flex gap-3">
                    <div className="h-8 w-32 bg-slate-200 rounded-xl"></div>
                    <div className="h-8 w-44 bg-slate-200 rounded-xl"></div>
                </div>
                <div className="h-8 w-24 bg-slate-200 rounded-xl"></div>
            </div>

            {/* KPI Cards Skeleton */}
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
                {[1, 2, 3, 4, 5].map((i) => (
                    <div key={i} className="card-soft-pill p-4 h-20 flex items-center space-x-3">
                        <div className="w-11 h-11 rounded-full bg-slate-200 flex-shrink-0"></div>
                        <div className="space-y-2 flex-1">
                            <div className="h-5 bg-slate-200 rounded w-16"></div>
                            <div className="h-3 bg-slate-200 rounded w-20"></div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Main Content Skeleton */}
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div className="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-6 h-80">
                    <div className="h-5 w-48 bg-slate-200 rounded mb-4"></div>
                    <div className="h-56 bg-slate-100 rounded-xl"></div>
                </div>
                <div className="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-6 h-80">
                    <div className="h-5 w-36 bg-slate-200 rounded mb-4"></div>
                    <div className="space-y-3">
                        <div className="h-12 bg-slate-100 rounded-xl"></div>
                        <div className="h-12 bg-slate-100 rounded-xl"></div>
                        <div className="h-12 bg-slate-100 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    );
}

/**
 * Friendly Error State with Retry Button
 */
export function ErrorState({ message, onRetry }) {
    return (
        <div className="bg-rose-50 border border-rose-200 rounded-2xl p-6 sm:p-8 text-center my-6 shadow-xs">
            <div
                className="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-xs"
                style={{ width: '48px', height: '48px', minWidth: '48px' }}
            >
                <svg
                    className="w-6 h-6 flex-shrink-0"
                    width="24"
                    height="24"
                    style={{ width: '24px', height: '24px' }}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 className="text-sm font-extrabold text-rose-900 mb-1">
                Gagal Menampilkan Data
            </h3>
            <p className="text-xs text-rose-700 max-w-md mx-auto mb-4 leading-relaxed">
                {message || 'Koneksi ke backend terputus atau sesi telah kedaluwarsa. Silakan muat ulang halaman.'}
            </p>
            {onRetry && (
                <button
                    onClick={onRetry}
                    className="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all cursor-pointer"
                >
                    <svg
                        className="w-3.5 h-3.5 flex-shrink-0"
                        width="14"
                        height="14"
                        style={{ width: '14px', height: '14px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Coba Muat Ulang</span>
                </button>
            )}
        </div>
    );
}

/**
 * Reusable Empty State Card with clean SVG icon
 */
export function EmptyState({ title, description }) {
    return (
        <div className="text-center py-10 px-4">
            <div
                className="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-2.5 shadow-2xs"
                style={{ width: '40px', height: '40px', minWidth: '40px' }}
            >
                <svg
                    className="w-5 h-5 flex-shrink-0"
                    width="20"
                    height="20"
                    style={{ width: '20px', height: '20px' }}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h4 className="text-xs font-bold text-slate-700 mb-0.5">{title}</h4>
            <p className="text-[11px] text-slate-400 max-w-sm mx-auto">{description}</p>
        </div>
    );
}
