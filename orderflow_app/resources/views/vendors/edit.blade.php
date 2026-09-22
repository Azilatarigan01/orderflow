<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">Perbarui Data Rekanan: {{ $vendor->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Pemutakhiran profil, legalitas, dan rekening perbankan mitra vendor.</p>
            </div>
            <a href="{{ route('vendors.index') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 md:p-8">
                <form method="POST" action="{{ route('vendors.update', $vendor) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Identitas Badan Usaha & Narahubung</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kode Rekanan <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $vendor->code) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Legal Badan Usaha <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Klasifikasi Bidang Usaha <span class="text-rose-500">*</span></label>
                            <input type="text" name="category" value="{{ old('category', $vendor->category) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('category') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Person in Charge (PIC) <span class="text-rose-500">*</span></label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('contact_person') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Resmi Korporat <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $vendor->email) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Telepon Kantor / Narahubung <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Alamat Domisili Kantor / Gudang <span class="text-rose-500">*</span></label>
                        <textarea name="address" rows="2" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">{{ old('address', $vendor->address) }}</textarea>
                        @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-b border-slate-100 pt-4 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Data Perpajakan & Perbankan (Disbursement)</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">NPWP Badan Usaha</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number', $vendor->tax_number) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Lembaga Perbankan</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $vendor->bank_account_no) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Atas Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $vendor->bank_account_name) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $vendor->is_active ? 'checked' : '' }} class="w-4 h-4 text-slate-900 rounded border-slate-300 focus:ring-slate-900">
                        <label for="is_active" class="text-xs font-medium text-slate-700">Status Aktif Rekanan</label>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-slate-100">
                        <button type="button" onclick="if(confirm('Konfirmasi penghapusan data rekanan vendor ini?')) document.getElementById('delete-vendor-form').submit();" class="text-rose-600 hover:text-rose-700 text-xs font-semibold">
                            Hapus Data Rekanan
                        </button>

                        <div class="flex gap-3">
                            <a href="{{ route('vendors.index') }}" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-vendor-form" action="{{ route('vendors.destroy', $vendor) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
