<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('delegations.index') }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 transition text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Formulir Pelimpahan Wewenang (Plt)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tunjuk pejabat pengganti sementara selama masa cuti atau dinas luar kota.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-8 space-y-6">

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                        <div class="font-bold">Terdapat kesalahan pengisian formulir:</div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('delegations.store') }}" class="space-y-6">
                    @csrf

                    <!-- Pejabat Pemberi Wewenang Info -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Pemberi Wewenang Asli (Delegator)</span>
                            <span class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="text-xs text-slate-500">({{ auth()->user()->role_label }} &bull; {{ auth()->user()->department?->name ?? 'Head Office' }})</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-bold bg-indigo-100 text-indigo-800">
                            NIP: {{ auth()->user()->id }}
                        </span>
                    </div>

                    <!-- Pilih Pegawai Pengganti (Delegatee) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Pejabat Pelaksana Tugas (Plt) <span class="text-rose-500">*</span>
                        </label>
                        <select name="delegatee_user_id" required class="w-full text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Pilih Rekan Kerja / Pejabat Pengganti --</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}" {{ old('delegatee_user_id') == $candidate->id ? 'selected' : '' }}>
                                    {{ $candidate->name }} ({{ $candidate->role_label }} &bull; {{ $candidate->department?->code ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pegawai yang dipilih akan memiliki hak akses sementara untuk menyetujui transaksi.</p>
                    </div>

                    <!-- Role yang Didelegasikan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kewenangan Peran yang Diberikan <span class="text-rose-500">*</span>
                        </label>
                        <select name="role_delegated" required class="w-full text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="manager" {{ old('role_delegated', auth()->user()->role === 'manager' ? 'manager' : '') === 'manager' ? 'selected' : '' }}>
                                Manager Divisi (Tier 1 Persetujuan PR)
                            </option>
                            <option value="finance" {{ old('role_delegated', auth()->user()->role === 'finance' ? 'finance' : '') === 'finance' ? 'selected' : '' }}>
                                Tim Keuangan / Finance (Tier 2 Persetujuan PR)
                            </option>
                            <option value="hod" {{ old('role_delegated', auth()->user()->role === 'hod' ? 'hod' : '') === 'hod' ? 'selected' : '' }}>
                                Direksi / Head of Department (Tier 3 Persetujuan PR)
                            </option>
                        </select>
                    </div>

                    <!-- Rentang Tanggal Efektif -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tanggal Mulai Berlaku <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="start_date" required
                                value="{{ old('start_date', date('Y-m-d')) }}"
                                class="w-full text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tanggal Berakhir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="end_date" required
                                value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}"
                                class="w-full text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Alasan Penugasan Plt -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Pelimpahan Wewenang (Dasar Penugasan) <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="reason" rows="3" required placeholder="Cth: Cuti tahunan selama 1 minggu di luar negeri / Dinas kunjungan kerja cabang Surabaya."
                            class="w-full text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-1 focus:ring-indigo-500">{{ old('reason') }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Alasan ini akan tercatat dalam log audit resmi dan sertifikat persetujuan dokumen.</p>
                    </div>

                    <!-- Submit & Cancel -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('delegations.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-xs transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Terbitkan Delegasi Plt
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
