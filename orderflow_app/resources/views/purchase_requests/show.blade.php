<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-mono font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                        {{ $purchaseRequest->pr_number }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $purchaseRequest->status_badge_class }}">
                        {{ $purchaseRequest->status_label }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Dibuat pada {{ $purchaseRequest->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-requests.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Kembali ke Daftar
                </a>
                @if(in_array($purchaseRequest->status, ['approved', 'processing', 'completed']))
                    <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Matriks Vendor (RFQ)</span>
                    </a>
                @endif
                @if($purchaseRequest->canBeEditedBy(Auth::user()))
                    <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                        ✏️ Edit Pengajuan
                    </a>
                @endif
                @if($purchaseRequest->canBeSubmittedBy(Auth::user()))
                    <form method="POST" action="{{ route('purchase-requests.submit', $purchaseRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan Purchase Request ini ke atasan divisi?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            🚀 Ajukan Persetujuan (Submit)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
                    <div class="flex items-center gap-2 font-bold text-rose-900">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mohon periksa kembali tindakan Anda:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-700 pl-7 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Interactive Approval Action Box (For authorized approvers of active tier) -->
            @if($canApprove && $activeTier)
                <div x-data="{ modalAction: null, notes: '' }" class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-indigo-500/30">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    Tindakan Otorisasi Anda Diperlukan
                                </span>
                                <span class="text-xs text-slate-300">
                                    Tahapan Anda: <strong class="text-white">{{ $activeTier->tier_label }}</strong>
                                </span>
                            </div>
                            <h3 class="text-lg font-extrabold text-white tracking-tight">Keputusan Persetujuan Pengadaan</h3>
                            <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                                Anda berwenang mengambil keputusan pada tahapan ini. Mohon verifikasi rincian barang, harga, dan justifikasi bisnis sebelum memberikan otorisasi.
                            </p>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2.5 flex-shrink-0">
                            <!-- Request Revision Button -->
                            <button type="button" @click="modalAction = 'revision'; notes = ''" class="px-4 py-2.5 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/50 text-amber-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Minta Revisi
                            </button>

                            <!-- Reject Button -->
                            <button type="button" @click="modalAction = 'reject'; notes = ''" class="px-4 py-2.5 bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/50 text-rose-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak PR
                            </button>

                            <!-- Approve Button -->
                            <button type="button" @click="modalAction = 'approve'; notes = ''" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/25 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Setujui (Approve)
                            </button>
                        </div>
                    </div>

                    <!-- Modal Confirmation & Notes Dialog -->
                    <div x-show="modalAction !== null"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4"
                         style="display: none;">
                        
                        <div @click.away="modalAction = null" class="bg-white rounded-2xl max-w-lg w-full p-6 text-slate-900 shadow-2xl space-y-4">
                            <!-- Approve Modal Header -->
                            <template x-if="modalAction === 'approve'">
                                <div>
                                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">Konfirmasi Persetujuan (Approve)</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Anda akan menyetujui pengajuan PR <strong>{{ $purchaseRequest->pr_number }}</strong> pada tahapan <strong>{{ $activeTier->tier_label }}</strong>.
                                    </p>
                                </div>
                            </template>

                            <!-- Request Revision Modal Header -->
                            <template x-if="modalAction === 'revision'">
                                <div>
                                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">Minta Revisi ke Pemohon</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Status PR akan dikembalikan menjadi <strong>Revisi Diperlukan (Revision Required)</strong>. Berikan instruksi perbaikan yang jelas kepada pemohon.
                                    </p>
                                </div>
                            </template>

                            <!-- Reject Modal Header -->
                            <template x-if="modalAction === 'reject'">
                                <div>
                                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">Konfirmasi Penolakan Pengadaan (Reject)</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Pengajuan ini akan ditolak secara permanen dan tidak dapat dilanjutkan ke bagian Procurement. Alasan penolakan wajib dicatat dalam jejak audit.
                                    </p>
                                </div>
                            </template>

                            <!-- Form Action -->
                            <form :action="modalAction === 'approve'
                                    ? '{{ route('approvals.approve', $purchaseRequest) }}'
                                    : (modalAction === 'revision'
                                        ? '{{ route('approvals.revision', $purchaseRequest) }}'
                                        : '{{ route('approvals.reject', $purchaseRequest) }}')"
                                  method="POST" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        <span x-text="modalAction === 'approve' ? 'Catatan Persetujuan (Opsional)' : (modalAction === 'revision' ? 'Instruksi Perbaikan / Revisi (Wajib, Min 5 Karakter)' : 'Alasan Penolakan (Wajib, Min 5 Karakter)')"></span>
                                        <span x-show="modalAction !== 'approve'" class="text-rose-600">*</span>
                                    </label>
                                    <textarea name="notes" x-model="notes" rows="3"
                                        :required="modalAction !== 'approve'"
                                        :placeholder="modalAction === 'approve'
                                            ? 'Tambahkan catatan jika diperlukan (opsional)...'
                                            : (modalAction === 'revision'
                                                ? 'Contoh: Mohon kurangi jumlah item atau lampirkan perbandingan 3 penawaran vendor...'
                                                : 'Contoh: Anggaran divisi tidak mencukupi untuk kuartal ini...')"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-xs"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" @click="modalAction = null" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                                        Batal
                                    </button>
                                    
                                    <template x-if="modalAction === 'approve'">
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                            Ya, Setujui Pengajuan
                                        </button>
                                    </template>

                                    <template x-if="modalAction === 'revision'">
                                        <button type="submit" :disabled="notes.trim().length < 5" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                            Kirim Permintaan Revisi
                                        </button>
                                    </template>

                                    <template x-if="modalAction === 'reject'">
                                        <button type="submit" :disabled="notes.trim().length < 5" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                            Tolak Pengadaan
                                        </button>
                                    </template>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($purchaseRequest->user_id === Auth::id() && $purchaseRequest->status === 'submitted')
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-bold text-amber-950">Pengajuan Sedang Dalam Proses Penelaahan (In Approval Queue)</p>
                        <p class="text-amber-800 mt-0.5 leading-relaxed">
                            Pengajuan Anda telah diserahkan dan saat ini menunggu peninjauan oleh <strong>{{ $activeTier?->tier_label ?? 'Atasan Terkait' }}</strong>. Sesuai prinsip <em>Segregation of Duties (SoD)</em>, pemohon tidak dapat menyetujui pengajuannya sendiri.
                        </p>
                    </div>
                </div>
            @elseif($purchaseRequest->status === 'revision_required')
                <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-amber-900">Perbaikan / Revisi Diperlukan</h4>
                            <p class="text-xs text-amber-800 mt-1">
                                Approver meminta revisi pada pengajuan ini. Silakan periksa catatan revisi di bawah dan perbarui informasi atau rincian item pengajuan.
                            </p>
                            @php
                                $lastRevisionHistory = $purchaseRequest->histories->where('to_status', 'revision_required')->last();
                            @endphp
                            @if($lastRevisionHistory && $lastRevisionHistory->notes)
                                <div class="mt-2.5 p-3 bg-white/80 rounded-xl border border-amber-200 text-xs text-amber-900 font-medium">
                                    <span class="font-bold text-amber-800">Catatan Revisi dari Atasan:</span> "{{ $lastRevisionHistory->notes }}"
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($purchaseRequest->canBeEditedBy(Auth::user()))
                        <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-600/20 transition flex items-center justify-center gap-2 flex-shrink-0">
                            ✏️ Perbaiki Pengajuan Sekarang
                        </a>
                    @endif
                </div>
            @endif

            <!-- RFQ & Vendor Procurement Status Banner (Visible when PR approved / processing) -->
            @if(in_array($purchaseRequest->status, ['approved', 'processing', 'completed']))
                @php
                    $selectedVendorQ = $purchaseRequest->selectedQuotation();
                    $rfqCount = $purchaseRequest->quotations()->count();
                @endphp
                <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white shadow-xl border border-indigo-500/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-500/30 text-indigo-300 border border-indigo-400/30">
                                Tahap Pengadaan Rekanan (RFQ)
                            </span>
                            @if($selectedVendorQ)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    ✓ Vendor Telah Ditetapkan
                                </span>
                            @endif
                        </div>
                        <h4 class="text-base font-extrabold text-white tracking-tight">
                            @if($selectedVendorQ)
                                Rekanan Terpilih: <span class="text-emerald-400">{{ $selectedVendorQ->vendor?->name }}</span>
                            @else
                                Proses Pengumpulan Surat Penawaran (RFQ)
                            @endif
                        </h4>
                        <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                            @if($selectedVendorQ)
                                Penetapan senilai <strong>{{ $selectedVendorQ->formatted_grand_total }}</strong> (#{{ $selectedVendorQ->quotation_number ?? '-' }}). Alasan: "{{ $selectedVendorQ->selection_reason }}".
                            @else
                                PR telah disetujui penuh. Terdapat <strong>{{ $rfqCount }} surat penawaran</strong> yang masuk dari vendor rekanan.
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Buka Matriks Perbandingan</span>
                        </a>
                        @if(Auth::user()->hasRole(['procurement', 'admin']))
                            <a href="{{ route('quotations.create', $purchaseRequest) }}" class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl text-xs font-semibold transition">
                                + Input Penawaran
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Multi-Tier Approval Workflow Stepper Tracker -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 gap-2">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span>Alur Persetujuan Bertingkat (Approval Workflow)</span>
                            @if($purchaseRequest->status === 'approved')
                                <span class="text-emerald-600 font-bold text-xs">✓ Selesai & Disetujui Penuh</span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Jalur persetujuan ditentukan otomatis berdasarkan nilai anggaran: <strong>Rp {{ number_format($purchaseRequest->estimated_total, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div>
                        @if($purchaseRequest->approvals->count() > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-mono text-[11px] font-semibold">
                                Total {{ $purchaseRequest->approvals->count() }} Tahapan
                            </span>
                        @endif
                    </div>
                </div>

                @if($purchaseRequest->approvals->count() === 0)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-bold text-slate-800">Draf Belum Memiliki Antrean Persetujuan</p>
                            <p class="text-slate-500 mt-0.5">Daftar approver akan dibentuk otomatis oleh sistem berdasarkan total anggaran saat Anda menekan tombol <strong>Ajukan Persetujuan (Submit)</strong> di kanan atas.</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-{{ $purchaseRequest->approvals->count() }} gap-4 relative pt-1">
                        @foreach($purchaseRequest->approvals->sortBy('tier_level') as $approval)
                            @php
                                $isCurrent = ($approval->status === 'pending' && $activeTier && $activeTier->id === $approval->id);
                            @endphp
                            <div class="relative flex flex-col p-4 rounded-xl border transition-all duration-200
                                {{ $isCurrent ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/40 shadow-sm' : '' }}
                                {{ $approval->status === 'approved' ? 'bg-emerald-50/50 border-emerald-200' : '' }}
                                {{ $approval->status === 'rejected' ? 'bg-rose-50/50 border-rose-200' : '' }}
                                {{ $approval->status === 'revision_required' ? 'bg-orange-50/50 border-orange-200' : '' }}
                                {{ $approval->status === 'pending' && !$isCurrent ? 'bg-slate-50/70 border-slate-200 opacity-60' : '' }}">
                                
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold font-mono
                                        {{ $approval->status === 'approved' ? 'bg-emerald-600 text-white' : '' }}
                                        {{ $approval->status === 'rejected' ? 'bg-rose-600 text-white' : '' }}
                                        {{ $approval->status === 'revision_required' ? 'bg-orange-600 text-white' : '' }}
                                        {{ $isCurrent ? 'bg-amber-500 text-white animate-pulse' : '' }}
                                        {{ $approval->status === 'pending' && !$isCurrent ? 'bg-slate-200 text-slate-600' : '' }}">
                                        @if($approval->status === 'approved')
                                            ✓
                                        @elseif($approval->status === 'rejected')
                                            ✕
                                        @elseif($approval->status === 'revision_required')
                                            ✎
                                        @else
                                            {{ $approval->tier_level }}
                                        @endif
                                    </span>

                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $approval->status_badge_class }}">
                                        @if($isCurrent)
                                            Menunggu Giliran Ini
                                        @else
                                            {{ $approval->status_label }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                            {{ $approval->tier_label }}
                                        </h4>
                                        
                                        @if($approval->approver)
                                            <p class="text-[11px] text-slate-700 font-medium mt-1">
                                                Oleh: <strong class="text-slate-900">{{ $approval->approver->name }}</strong>
                                            </p>
                                            @if($approval->acted_at)
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    {{ $approval->acted_at->format('d M Y, H:i') }} WIB
                                                </p>
                                            @endif
                                        @elseif($isCurrent)
                                            <p class="text-[11px] text-amber-700 font-medium mt-1">
                                                Sedang menunggu keputusan...
                                            </p>
                                        @else
                                            <p class="text-[11px] text-slate-400 italic mt-1">
                                                Menunggu tahap sebelumnya
                                            </p>
                                        @endif
                                    </div>

                                    @if($approval->notes)
                                        <div class="mt-2.5 p-2 rounded-lg bg-white border border-slate-200 text-[11px] text-slate-700 italic">
                                            "{{ $approval->notes }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- PR Header Document Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $purchaseRequest->title }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Dokumen Resmi Pengajuan Pembelian Korporat</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Pemohon (Requester)</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $purchaseRequest->user?->name ?? '-' }}</p>
                        <p class="text-slate-500">{{ $purchaseRequest->user?->email ?? '-' }}</p>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Divisi / Departemen</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $purchaseRequest->department?->name ?? 'Lintas Divisi' }}</p>
                        <p class="text-slate-500">Kode Divisi: <span class="font-mono font-bold">{{ $purchaseRequest->department?->code ?? '-' }}</span></p>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Target Kedatangan Barang</span>
                        <p class="font-bold text-indigo-700 text-sm mt-0.5">{{ $purchaseRequest->required_date->format('d F Y') }}</p>
                        <p class="text-slate-500">({{ $purchaseRequest->required_date->diffForHumans() }})</p>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Alasan & Justifikasi Kebutuhan Bisnis:</h4>
                    <div class="p-3.5 bg-slate-50/80 rounded-lg border border-slate-200 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                        {{ $purchaseRequest->description }}
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                        Rincian Barang / Jasa ({{ $purchaseRequest->items->count() }} Item)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">#</th>
                                <th class="px-4 py-3">Nama Barang / Jasa</th>
                                <th class="px-4 py-3">Spesifikasi Teknis</th>
                                <th class="px-4 py-3 text-center w-24">Jumlah</th>
                                <th class="px-4 py-3 text-center w-24">Satuan</th>
                                <th class="px-4 py-3 text-right w-36">Harga Satuan</th>
                                <th class="px-4 py-3 text-right w-40">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            @foreach($purchaseRequest->items as $index => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item->specification ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700">Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                            <tr>
                                <td colspan="6" class="px-4 py-3.5 text-right font-bold text-slate-700 uppercase tracking-wider text-xs">
                                    Total Estimasi Anggaran:
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-extrabold text-lg text-indigo-700">
                                    Rp {{ number_format($purchaseRequest->estimated_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Attachments & Documents -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
                    Berkas Lampiran Pendukung
                </h3>

                @if($purchaseRequest->attachments->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($purchaseRequest->attachments as $att)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <div class="truncate">
                                        <p class="text-xs font-semibold text-slate-900 truncate">{{ $att->file_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $att->formatted_size }} &bull; Diunggah oleh {{ $att->uploader?->name ?? 'Pengguna' }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-medium rounded-md flex-shrink-0 ml-2">
                                    Buka File
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas lampiran pendukung yang diunggah.</p>
                @endif
            </div>

            <!-- Status History Audit Trail (Timeline) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
                    Linimasa Jejak Audit & Riwayat Status (Status History)
                </h3>

                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($purchaseRequest->histories as $index => $history)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center ring-8 ring-white text-xs font-bold font-mono">
                                                {{ $purchaseRequest->histories->count() - $index }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-xs font-bold text-slate-900">
                                                    Status: <span class="text-indigo-600">{{ $history->to_status_label }}</span>
                                                    @if($history->from_status)
                                                        <span class="text-slate-400 font-normal">(sebelumnya: {{ $history->from_status }})</span>
                                                    @endif
                                                </p>
                                                @if($history->notes)
                                                    <p class="text-xs text-slate-600 mt-1 bg-slate-50 p-2 rounded-lg border border-slate-200">
                                                        {{ $history->notes }}
                                                    </p>
                                                @endif
                                                <p class="text-[11px] text-slate-400 mt-1">
                                                    Oleh: <strong class="text-slate-600">{{ $history->user?->name ?? 'Sistem' }}</strong> ({{ $history->user?->role_label ?? '-' }})
                                                </p>
                                            </div>
                                            <div class="text-right text-[11px] whitespace-nowrap text-slate-400">
                                                {{ $history->created_at->format('d M Y, H:i') }} WIB
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Danger Zone / Delete Draft Option -->
            @if($purchaseRequest->status === 'draft' && ($purchaseRequest->user_id === Auth::id() || Auth::user()->hasRole('admin')))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-rose-800">Hapus Draf Pengajuan</h4>
                        <p class="text-[11px] text-rose-600">Draf ini belum diserahkan ke atasan dan dapat dihapus secara permanen.</p>
                    </div>
                    <form method="POST" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf PR ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                            Hapus Draf
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
