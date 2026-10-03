import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import FilterBar from './components/FilterBar';
import KpiCards from './components/KpiCards';
import SpendChart from './components/SpendChart';
import PendingApprovalsTable from './components/PendingApprovalsTable';
import OverduePoTable from './components/OverduePoTable';
import RecentPrTable from './components/RecentPrTable';
import { SkeletonLoader, ErrorState } from './components/StateFeedback';

export default function ManagementDashboard({ initialUser }) {
    // 1. State definitions
    const [period, setPeriod] = useState('this_month');
    const [departmentId, setDepartmentId] = useState('');
    const [activeTab, setActiveTab] = useState('overview'); // 'overview' | 'approvals' | 'overdue' | 'recent_prs'

    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);
    const [lastUpdated, setLastUpdated] = useState(null);

    // 2. Fetch Data with Axios (useEffect trigger on filter change)
    const fetchDashboardData = useCallback(async (isBackground = false) => {
        if (!isBackground) {
            setLoading(true);
        } else {
            setRefreshing(true);
        }
        setError(null);

        try {
            const params = {
                period,
                ...(departmentId ? { department_id: departmentId } : {}),
            };

            const response = await axios.get('/web-api/management-dashboard-data', {
                params,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.data && response.data.success) {
                setDashboardData(response.data.data);
                const now = new Date();
                setLastUpdated(now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            } else {
                throw new Error(response.data.message || 'Format data API tidak sesuai');
            }
        } catch (err) {
            console.error('Error fetching management dashboard data:', err);
            const errMsg = err.response?.data?.message || err.message || 'Koneksi ke REST API gagal.';
            setError(errMsg);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [period, departmentId]);

    // 3. Trigger fetch whenever filters change
    useEffect(() => {
        fetchDashboardData(false);
    }, [fetchDashboardData]);

    // 4. Handle Filter Reset
    const handleResetFilters = () => {
        setPeriod('this_month');
        setDepartmentId('');
    };

    // 5. Render Loading State
    if (loading && !dashboardData) {
        return (
            <div className="max-w-7xl mx-auto space-y-6">
                <SkeletonLoader />
            </div>
        );
    }

    // 6. Render Error State
    if (error && !dashboardData) {
        return (
            <div className="max-w-7xl mx-auto space-y-6">
                <ErrorState message={error} onRetry={() => fetchDashboardData(false)} />
            </div>
        );
    }

    const {
        departments = [],
        kpis = {},
        trend = [],
        pending_approvals = [],
        overdue_pos = [],
        recent_prs = [],
        user = initialUser,
    } = dashboardData || {};

    return (
        <div className="max-w-7xl mx-auto space-y-6">
            {/* Top Filter Bar Component with Props */}
            <FilterBar
                period={period}
                onPeriodChange={setPeriod}
                departmentId={departmentId}
                onDepartmentChange={setDepartmentId}
                departments={departments}
                onRefresh={() => fetchDashboardData(true)}
                isRefreshing={refreshing}
                lastUpdated={lastUpdated}
                onReset={handleResetFilters}
            />

            {/* Error banner if background refresh fails */}
            {error && dashboardData && (
                <div className="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-center justify-between shadow-2xs">
                    <span>Gagal menyinkronkan data terbaru: {error}</span>
                    <button
                        onClick={() => fetchDashboardData(true)}
                        className="text-rose-900 font-bold hover:underline cursor-pointer"
                    >
                        Coba lagi
                    </button>
                </div>
            )}

            {/* KPI Cards Component with Props */}
            <KpiCards kpis={kpis} />

            {/* Section Filter / View Tabs */}
            <div className="flex border-b border-slate-200 gap-2 overflow-x-auto pb-px">
                <button
                    onClick={() => setActiveTab('overview')}
                    className={`px-4 py-2.5 text-xs font-bold transition-all border-b-2 whitespace-nowrap cursor-pointer flex items-center gap-2 ${
                        activeTab === 'overview'
                            ? 'border-blue-600 text-blue-700 bg-blue-50/50 rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'
                    }`}
                >
                    <svg
                        className="w-4 h-4 flex-shrink-0"
                        width="16"
                        height="16"
                        style={{ width: '16px', height: '16px', minWidth: '16px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Semua Modul (Terpadu)</span>
                </button>

                <button
                    onClick={() => setActiveTab('approvals')}
                    className={`px-4 py-2.5 text-xs font-bold transition-all border-b-2 whitespace-nowrap cursor-pointer flex items-center gap-2 ${
                        activeTab === 'approvals'
                            ? 'border-blue-600 text-blue-700 bg-blue-50/50 rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'
                    }`}
                >
                    <svg
                        className="w-4 h-4 flex-shrink-0"
                        width="16"
                        height="16"
                        style={{ width: '16px', height: '16px', minWidth: '16px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Antrean Approval</span>
                    {pending_approvals.length > 0 && (
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            {pending_approvals.length}
                        </span>
                    )}
                </button>

                <button
                    onClick={() => setActiveTab('overdue')}
                    className={`px-4 py-2.5 text-xs font-bold transition-all border-b-2 whitespace-nowrap cursor-pointer flex items-center gap-2 ${
                        activeTab === 'overdue'
                            ? 'border-rose-600 text-rose-700 bg-rose-50/50 rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'
                    }`}
                >
                    <svg
                        className="w-4 h-4 flex-shrink-0"
                        width="16"
                        height="16"
                        style={{ width: '16px', height: '16px', minWidth: '16px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>PO Terlambat</span>
                    {overdue_pos.length > 0 && (
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            {overdue_pos.length}
                        </span>
                    )}
                </button>

                <button
                    onClick={() => setActiveTab('recent_prs')}
                    className={`px-4 py-2.5 text-xs font-bold transition-all border-b-2 whitespace-nowrap cursor-pointer flex items-center gap-2 ${
                        activeTab === 'recent_prs'
                            ? 'border-blue-600 text-blue-700 bg-blue-50/50 rounded-t-xl'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'
                    }`}
                >
                    <svg
                        className="w-4 h-4 flex-shrink-0"
                        width="16"
                        height="16"
                        style={{ width: '16px', height: '16px', minWidth: '16px' }}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Aktivitas PR</span>
                    {recent_prs.length > 0 && (
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            {recent_prs.length}
                        </span>
                    )}
                </button>
            </div>

            {/* Tab 1: Overview & Unified Dashboard (All in One Screen) */}
            {activeTab === 'overview' && (
                <div className="space-y-6">
                    {/* Interactive Chart Component */}
                    <SpendChart trend={trend} />

                    {/* Dual Tables Preview Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <PendingApprovalsTable approvals={pending_approvals} />
                        <OverduePoTable overduePos={overdue_pos} />
                    </div>

                    {/* Unified Document Activity: Recent PRs */}
                    <RecentPrTable recentPrs={recent_prs} />
                </div>
            )}

            {/* Tab 2: Full View Approvals */}
            {activeTab === 'approvals' && (
                <div className="space-y-4">
                    <PendingApprovalsTable approvals={pending_approvals} />
                </div>
            )}

            {/* Tab 3: Full View Overdue POs */}
            {activeTab === 'overdue' && (
                <div className="space-y-4">
                    <OverduePoTable overduePos={overdue_pos} />
                </div>
            )}

            {/* Tab 4: Full View Recent PRs */}
            {activeTab === 'recent_prs' && (
                <div className="space-y-4">
                    <RecentPrTable recentPrs={recent_prs} />
                </div>
            )}
        </div>
    );
}
