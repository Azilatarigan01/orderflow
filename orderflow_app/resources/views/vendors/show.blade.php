<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-slate-900 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                    {{ substr($vendor->code, -3) }}
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight leading-tight">{{ $vendor->name }}</h2>
                    <p class="text-xs text-slate-500 font-mono">{{ $vendor->code }} &bull; {{ $vendor->category }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('vendors.index') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition">
                    Kembali
                </a>
                @if(Auth::user()->hasRole(['procurement', 'admin']))
                    <a href="{{ route('vendors.edit', $vendor) }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition">
                        Ubah Data Profil
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Summary Metric Tiles -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Rekanan</span>
                    <div class="mt-2">
                        @if($vendor->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Skor Kinerja (Rating)</span>
                    <div class="text-xl font-bold text-slate-900 mt-1 font-mono">
                        {{ number_format($vendor->rating, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.00</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Person in Charge (PIC)</span>
                    <div class="text-sm font-semibold text-slate-900 mt-1">{{ $vendor->contact_person }}</div>
                    <div class="text-xs text-slate-500">{{ $vendor->phone }}</div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Email Korespondensi</span>
                    <div class="text-sm font-semibold text-slate-900 mt-1 truncate font-mono text-xs">{{ $vendor->email }}</div>
                    <div class="text-xs text-slate-400">Resmi Korporat</div>
                </div>
            </div>

            <!-- Detail Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Operasional & Legalitas -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <h3 class="font-bold text-slate-900 text-sm mb-4 pb-3 border-b border-slate-100 uppercase tracking-wider text-xs">
                        Legalitas & Alamat Operasional
                    </h3>
                    <dl class="space-y-4 text-xs">
                        <div>
                            <dt class="font-semibold text-slate-400 uppercase text-[11px]">Alamat Domisili Perusahaan</dt>
                            <dd class="text-slate-800 mt-1 leading-relaxed text-sm">{{ $vendor->address }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-400 uppercase text-[11px]">Nomor Pokok Wajib Pajak (NPWP)</dt>
                            <dd class="text-slate-800 mt-1 font-mono text-sm font-semibold">{{ $vendor->tax_number ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Rekening Pembayaran -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <h3 class="font-bold text-slate-900 text-sm mb-4 pb-3 border-b border-slate-100 uppercase tracking-wider text-xs">
                        Instruksi Rekening Perbankan (Disbursement)
                    </h3>
                    <dl class="space-y-4 text-xs">
                        <div>
                            <dt class="font-semibold text-slate-400 uppercase text-[11px]">Lembaga Perbankan</dt>
                            <dd class="text-slate-900 font-semibold mt-1 text-sm">{{ $vendor->bank_name ?? 'Belum terdaftar' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-400 uppercase text-[11px]">Nomor Rekening</dt>
                            <dd class="text-slate-900 font-mono text-base font-bold mt-1 tracking-wider">{{ $vendor->bank_account_no ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-400 uppercase text-[11px]">Atas Nama Rekening</dt>
                            <dd class="text-slate-800 mt-1 text-sm">{{ $vendor->bank_account_name ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
