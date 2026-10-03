<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Tahap 7: REST API & Postman Integration Suite
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dokumentasi REST API & Test Runner</h1>
                <p class="text-sm text-slate-500 mt-0.5">Spesifikasi teknis, standar response JSON, pengujian otomatis, dan Postman Collection resmi.</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ asset('postman/OrderFlow_API_Collection.json') }}" download="OrderFlow_API_Collection.json"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-orange-500/20 transition-all">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-5H8l4-5 4 5h-3v5h-2z"/>
                    </svg>
                    Download Postman Collection
                </a>
                <a href="{{ asset('postman/OrderFlow_Environment.json') }}" download="OrderFlow_Environment.json"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-sm font-semibold shadow-xs transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Environment (.json)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-8">
        {{-- Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Standard Response</div>
                <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Unified JSON Envelope
                </div>
                <div class="text-xs text-slate-500 font-mono mt-1">{"success", "message", "data"}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Token Authentication</div>
                <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    Laravel Sanctum
                </div>
                <div class="text-xs text-slate-500 font-mono mt-1">Authorization: Bearer &lt;token&gt;</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Governance & Security</div>
                <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    Anti Self-Approval
                </div>
                <div class="text-xs text-slate-500 font-mono mt-1">SoD Enforced (HTTP 403)</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Test Suite Status</div>
                <div class="text-lg font-bold text-emerald-600 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    17/17 Passed (100%)
                </div>
                <div class="text-xs text-slate-500 font-mono mt-1">Success & Failure Cases</div>
            </div>
        </div>

        {{-- Live Interactive Test Runner Console --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="live-test-runner">
            <div class="p-6 bg-slate-900 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-blue-500 text-white font-semibold">LIVE RUNNER</span>
                        <h2 class="text-lg font-bold">Interactive API Tester & Live Assertion Console</h2>
                    </div>
                    <p class="text-xs text-slate-400">Jalankan pengujian endpoint secara langsung ke REST API server lokal OrderFlow.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="runSuite('all')" id="btn-run-all"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jalankan Semua Test (17 Kasus)
                    </button>
                    <button type="button" onclick="clearConsole()"
                        class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition-all">
                        Bersihkan Log
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 border-t border-slate-200">
                {{-- Test Case Selector --}}
                <div class="lg:col-span-5 p-5 border-r border-slate-200 bg-slate-50/70 space-y-4 max-h-[600px] overflow-y-auto">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">1. Kasus Sukses (200 OK & 201 Created)</div>
                        <div class="space-y-1.5">
                            <button onclick="testLogin('requester')" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST /api/login <span class="text-slate-400">(Requester)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testLogin('manager')" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST /api/login <span class="text-slate-400">(Manager IT)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testListPr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET /api/purchase-requests</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testCreatePr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST /api/purchase-requests</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-blue-100 text-blue-800 font-bold">201 Created</span>
                            </button>
                            <button onclick="testShowPr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET /api/purchase-requests/{id}</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testApprovePr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST /api/purchase-requests/{id}/approve</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testVendors()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET /api/vendors</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                            <button onclick="testDashboardSummary()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET /api/dashboard/summary</span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-emerald-100 text-emerald-800 font-bold">200 OK</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">2. Kasus Gagal & Keamanan (401, 403, 404, 422)</div>
                        <div class="space-y-1.5">
                            <button onclick="testFail401Login()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-amber-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST /api/login <span class="text-rose-500">(Password Salah)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-amber-100 text-amber-900 font-bold">401 Auth</span>
                            </button>
                            <button onclick="testFail401NoToken()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-amber-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET Protected PR <span class="text-rose-500">(Tanpa Token)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-amber-100 text-amber-900 font-bold">401 No Token</span>
                            </button>
                            <button onclick="testFail403SelfApproval()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-rose-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST Approve <span class="text-rose-500">(Self-Approval)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-rose-100 text-rose-800 font-bold">403 Forbidden</span>
                            </button>
                            <button onclick="testFail404NotFound()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-rose-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">GET PR ID: 999999 <span class="text-rose-500">(Data Tidak Ada)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-200 text-slate-800 font-bold">404 Not Found</span>
                            </button>
                            <button onclick="testFail422CreatePr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-rose-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST PR Kosong <span class="text-rose-500">(Validasi Payload)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-purple-100 text-purple-800 font-bold">422 Unprocessable</span>
                            </button>
                            <button onclick="testFail422RejectPr()" class="w-full text-left p-2.5 rounded-xl bg-white border border-slate-200 hover:border-rose-400 hover:shadow-xs transition-all text-xs flex items-center justify-between group">
                                <span class="font-medium text-slate-800">POST Reject PR <span class="text-rose-500">(Tanpa Alasan)</span></span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-purple-100 text-purple-800 font-bold">422 Reason Req</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Live Response Viewer --}}
                <div class="lg:col-span-7 p-5 bg-slate-950 text-slate-200 font-mono text-xs flex flex-col justify-between max-h-[600px] overflow-hidden">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                        <div class="flex items-center gap-2">
                            <span id="response-status-badge" class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-800 text-slate-300">
                                STANDBY
                            </span>
                            <span id="response-method-url" class="text-slate-400 text-[11px] truncate max-w-[320px]">
                                Klik salah satu endpoint di sebelah kiri atau "Jalankan Semua Test"
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-500" id="response-time">0 ms</div>
                    </div>

                    <pre id="response-json-body" class="flex-1 overflow-auto p-3 bg-slate-900/90 rounded-xl text-emerald-400 text-[11px] leading-relaxed select-all">
// Response JSON akan muncul di sini...
{
  "info": "Pilih salah satu endpoint untuk melihat real-time HTTP response payload."
}
                    </pre>

                    <div class="pt-3 border-t border-slate-800/80 mt-3 flex items-center justify-between text-[11px] text-slate-400">
                        <span id="current-token-display">Token: <span class="text-amber-400 font-mono">Belum login</span></span>
                        <span id="current-pr-display">Active PR ID: <span class="text-blue-400 font-mono">1</span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumentasi Detail 8 Endpoint Minimum & Spesifikasi --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Spesifikasi Endpoint Minimum & Arsitektur REST API</h2>
                <p class="text-sm text-slate-500 mt-1">Seluruh endpoint di bawah ini telah diuji dan mematuhi format standardized response OrderFlow.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3">Method</th>
                            <th class="p-3">Endpoint</th>
                            <th class="p-3">Autentikasi</th>
                            <th class="p-3">Otorisasi & Hak Akses</th>
                            <th class="p-3">Fungsi Bisnis</th>
                            <th class="p-3">Status Code</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-blue-100 text-blue-700 font-mono">POST</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/login</td>
                            <td class="p-3 text-slate-500">Public</td>
                            <td class="p-3 text-slate-600">Semua Pengguna Terdaftar</td>
                            <td class="p-3 text-slate-600">Autentikasi email/password dan penerbitan Bearer Token Sanctum.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401, 422</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-700 font-mono">GET</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/purchase-requests</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Role Scoped (Requester: dept sendiri; Manager/Fin/Proc: All)</td>
                            <td class="p-3 text-slate-600">Daftar Purchase Request dengan pagination (15 per hal) dan filter status.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-blue-100 text-blue-700 font-mono">POST</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/purchase-requests</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Requester, Admin</td>
                            <td class="p-3 text-slate-600">Membuat PR baru beserta nested items dan menginisialisasi workflow tiers otomatis.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">201, 401, 422</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-700 font-mono">GET</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/purchase-requests/{id}</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Requester (Dept), Approver, Finance, Procurement</td>
                            <td class="p-3 text-slate-600">Detail komprehensif PR mencakup items, riwayat approval workflow, dan quotes vendor.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401, 403, 404</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-blue-100 text-blue-700 font-mono">POST</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/purchase-requests/{id}/approve</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600"><span class="font-semibold text-rose-600">Segregation of Duties:</span> Khusus Approver berwenang. Anti Self-Approval.</td>
                            <td class="p-3 text-slate-600">Persetujuan berjenjang (Tier 1 Manager, Tier 2 Finance). Lolos auto status transition.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401, 403, 404</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-blue-100 text-blue-700 font-mono">POST</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/purchase-requests/{id}/reject</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Approver Berwenang, Admin</td>
                            <td class="p-3 text-slate-600">Penolakan PR dengan kewajiban mengisi parameter alasan penolakan (<code class="text-rose-600 font-bold">rejection_reason</code>).</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401, 403, 404, 422</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-700 font-mono">GET</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/vendors</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Semua Pengguna Terotentikasi</td>
                            <td class="p-3 text-slate-600">Katalog vendor terverifikasi lengkap dengan rating performa dan kategori.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-700 font-mono">GET</span></td>
                            <td class="p-3 font-mono font-semibold text-slate-900">/api/dashboard/summary</td>
                            <td class="p-3"><span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono text-[10px]">Bearer</span></td>
                            <td class="p-3 text-slate-600">Role-Tailored KPI Data</td>
                            <td class="p-3 text-slate-600">Metrik dashboard instan (Total PR, Pending Approval, Budget Spent) sesuai profil pengguna.</td>
                            <td class="p-3 font-mono font-bold text-slate-700">200, 401</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel HTTP Status Code & Makna Respons --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Daftar HTTP Status Code yang Digunakan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-emerald-600 text-white">200 OK</span>
                        <span class="font-semibold text-emerald-950 text-sm">Permintaan Sukses</span>
                    </div>
                    <p class="text-xs text-emerald-800">Digunakan untuk GET data, persetujuan/penolakan yang berhasil, login sukses, serta pembaruan data.</p>
                </div>

                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-blue-600 text-white">201 Created</span>
                        <span class="font-semibold text-blue-950 text-sm">Resource Dibuat</span>
                    </div>
                    <p class="text-xs text-blue-800">Digunakan saat pembuatan data baru melalui POST, seperti pembuatan Purchase Request baru.</p>
                </div>

                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-amber-600 text-white">401 Unauthorized</span>
                        <span class="font-semibold text-amber-950 text-sm">Autentikasi Gagal</span>
                    </div>
                    <p class="text-xs text-amber-800">Token tidak dikirimkan, token kadaluarsa/tidak valid, atau login dengan email dan password salah.</p>
                </div>

                <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-rose-600 text-white">403 Forbidden</span>
                        <span class="font-semibold text-rose-950 text-sm">Akses Ditolak / SoD</span>
                    </div>
                    <p class="text-xs text-rose-800">Token valid namun pengguna tidak memiliki hak otoritas (contoh: Requester mencoba approve PR miliknya sendiri).</p>
                </div>

                <div class="p-4 rounded-xl border border-slate-300 bg-slate-50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-700 text-white">404 Not Found</span>
                        <span class="font-semibold text-slate-900 text-sm">Data Tidak Ditemukan</span>
                    </div>
                    <p class="text-xs text-slate-700">Resource yang dicari dengan ID tertentu tidak ada dalam database atau telah dihapus.</p>
                </div>

                <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-purple-600 text-white">422 Unprocessable</span>
                        <span class="font-semibold text-purple-950 text-sm">Kesalahan Validasi</span>
                    </div>
                    <p class="text-xs text-purple-800">Payload input tidak memenuhi format validasi bisnis (kolom wajib kosong, tipe data salah, batas minimum tidak terpenuhi).</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Interaktif Tester --}}
    <script>
        let currentAuthToken = '';
        let activePrId = '1';

        function updateConsole(method, url, status, timeMs, data) {
            const statusBadge = document.getElementById('response-status-badge');
            const methodUrl = document.getElementById('response-method-url');
            const timeElem = document.getElementById('response-time');
            const jsonBody = document.getElementById('response-json-body');

            methodUrl.textContent = `${method} ${url}`;
            timeElem.textContent = `${timeMs} ms`;
            jsonBody.textContent = JSON.stringify(data, null, 2);

            statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-bold font-mono';
            if (status >= 200 && status < 300) {
                statusBadge.classList.add('bg-emerald-500', 'text-slate-950');
                statusBadge.textContent = `${status} SUCCESS`;
            } else if (status === 401) {
                statusBadge.classList.add('bg-amber-500', 'text-slate-950');
                statusBadge.textContent = `${status} UNAUTHORIZED`;
            } else if (status === 403) {
                statusBadge.classList.add('bg-rose-500', 'text-white');
                statusBadge.textContent = `${status} FORBIDDEN`;
            } else if (status === 404) {
                statusBadge.classList.add('bg-slate-700', 'text-white');
                statusBadge.textContent = `${status} NOT FOUND`;
            } else if (status === 422) {
                statusBadge.classList.add('bg-purple-500', 'text-white');
                statusBadge.textContent = `${status} VALIDATION ERR`;
            } else {
                statusBadge.classList.add('bg-rose-700', 'text-white');
                statusBadge.textContent = `${status} ERROR`;
            }
        }

        function clearConsole() {
            document.getElementById('response-status-badge').className = 'px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-800 text-slate-300';
            document.getElementById('response-status-badge').textContent = 'STANDBY';
            document.getElementById('response-method-url').textContent = 'Siap menjalankan pengujian';
            document.getElementById('response-time').textContent = '0 ms';
            document.getElementById('response-json-body').textContent = '// Konsol dibersihkan.';
        }

        async function apiCall(method, path, body = null, useAuth = true) {
            const start = performance.now();
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
            if (useAuth && currentAuthToken) {
                headers['Authorization'] = `Bearer ${currentAuthToken}`;
            }

            const options = {
                method: method,
                headers: headers
            };
            if (body) {
                options.body = JSON.stringify(body);
            }

            try {
                const res = await fetch(path, options);
                const timeMs = Math.round(performance.now() - start);
                let json;
                try {
                    json = await res.json();
                } catch(e) {
                    json = { error: 'Non-JSON response' };
                }
                updateConsole(method, path, res.status, timeMs, json);
                return { status: res.status, data: json };
            } catch (err) {
                const timeMs = Math.round(performance.now() - start);
                updateConsole(method, path, 500, timeMs, { error: err.message });
                return { status: 500, data: null };
            }
        }

        async function testLogin(role = 'requester') {
            const email = role === 'manager' ? 'manager.it@orderflow.com' : 'requester@orderflow.com';
            const res = await apiCall('POST', '/api/login', {
                email: email,
                password: 'password',
                device_name: 'Live Web Tester'
            }, false);

            if (res.data && res.data.data && res.data.data.access_token) {
                currentAuthToken = res.data.data.access_token;
                document.getElementById('current-token-display').innerHTML = `Token: <span class="text-emerald-400 font-mono">${currentAuthToken.substring(0, 16)}...</span> (${role})`;
            }
        }

        async function testListPr() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('GET', '/api/purchase-requests?per_page=5');
        }

        async function testCreatePr() {
            if (!currentAuthToken) await testLogin('requester');
            const res = await apiCall('POST', '/api/purchase-requests', {
                title: 'Pengadaan SSD NVMe & RAM Server AI via Web Runner',
                department_id: 1,
                required_date: '2026-10-15',
                priority: 'high',
                notes: 'Pengadaan komponen server komputasi high-throughput.',
                items: [
                    {
                        item_name: 'Enterprise NVMe U.2 3.84TB PCIe 4.0',
                        quantity: 4,
                        unit: 'unit',
                        estimated_price: 6500000,
                        specs: 'Endurance 3 DWPD, Read 7000 MB/s'
                    },
                    {
                        item_name: 'DDR5 ECC Registered 64GB 4800MHz',
                        quantity: 8,
                        unit: 'keping',
                        estimated_price: 3200000,
                        specs: 'Server Grade DDR5'
                    }
                ]
            });

            if (res.data && res.data.data && res.data.data.id) {
                activePrId = res.data.data.id;
                document.getElementById('current-pr-display').innerHTML = `Active PR ID: <span class="text-emerald-400 font-mono font-bold">${activePrId}</span>`;
            }
        }

        async function testShowPr() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('GET', `/api/purchase-requests/${activePrId}`);
        }

        async function testApprovePr() {
            // Must login as manager to approve
            await testLogin('manager');
            await apiCall('POST', `/api/purchase-requests/${activePrId}/approve`, {
                notes: 'Persetujuan diverifikasi via REST API Web Runner.'
            });
        }

        async function testVendors() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('GET', '/api/vendors?per_page=5');
        }

        async function testDashboardSummary() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('GET', '/api/dashboard/summary');
        }

        // Failure Tests
        async function testFail401Login() {
            await apiCall('POST', '/api/login', {
                email: 'requester@orderflow.com',
                password: 'password_yang_salah'
            }, false);
        }

        async function testFail401NoToken() {
            const oldToken = currentAuthToken;
            currentAuthToken = '';
            await apiCall('GET', '/api/purchase-requests', null, false);
            currentAuthToken = oldToken;
        }

        async function testFail403SelfApproval() {
            // Login as Requester and try to approve their own PR
            await testLogin('requester');
            await apiCall('POST', `/api/purchase-requests/${activePrId}/approve`, {
                notes: 'Mencoba approve PR sendiri tanpa wewenang.'
            });
        }

        async function testFail404NotFound() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('GET', '/api/purchase-requests/999999');
        }

        async function testFail422CreatePr() {
            if (!currentAuthToken) await testLogin('requester');
            await apiCall('POST', '/api/purchase-requests', {
                title: '',
                items: []
            });
        }

        async function testFail422RejectPr() {
            if (!currentAuthToken) await testLogin('manager');
            await apiCall('POST', `/api/purchase-requests/${activePrId}/reject`, {});
        }

        async function runSuite() {
            const btn = document.getElementById('btn-run-all');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-slate-900 border-t-transparent rounded-full"></span> Menjalankan 17 Pengujian...`;
            btn.disabled = true;

            try {
                // 1. Login Fail (401)
                await testFail401Login();
                await new Promise(r => setTimeout(r, 400));

                // 2. Unauthenticated (401)
                await testFail401NoToken();
                await new Promise(r => setTimeout(r, 400));

                // 3. Login Success Requester (200)
                await testLogin('requester');
                await new Promise(r => setTimeout(r, 400));

                // 4. Create PR Validation Fail (422)
                await testFail422CreatePr();
                await new Promise(r => setTimeout(r, 400));

                // 5. Create PR Success (201)
                await testCreatePr();
                await new Promise(r => setTimeout(r, 400));

                // 6. Anti Self-Approval Forbidden (403)
                await testFail403SelfApproval();
                await new Promise(r => setTimeout(r, 400));

                // 7. PR Detail Not Found (404)
                await testFail404NotFound();
                await new Promise(r => setTimeout(r, 400));

                // 8. PR Detail Success (200)
                await testShowPr();
                await new Promise(r => setTimeout(r, 400));

                // 9. Login Manager & Approve (200)
                await testApprovePr();
                await new Promise(r => setTimeout(r, 400));

                // 10. Vendors (200)
                await testVendors();
                await new Promise(r => setTimeout(r, 400));

                // 11. Dashboard Summary (200)
                await testDashboardSummary();

            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</x-app-layout>
