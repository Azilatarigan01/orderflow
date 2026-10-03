<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Masuk ke Portal Karyawan</h2>
        <p class="text-xs text-slate-500 mt-1">Gunakan akun email resmi perusahaan Anda untuk melanjutkan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Alamat Email Korporat
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="nama.karyawan@orderflow.com"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition shadow-2xs">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-600" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Kata Sandi Keamanan
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition" href="{{ route('password.request') }}">
                        Lupa Sandi?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition shadow-2xs">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="remember">
                <span class="ms-2 text-xs font-medium text-slate-600">Ingat sesi kerja saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button (Vibrant Coral/Orange from Image 2) -->
        <button type="submit" class="w-full mt-2 py-3 px-4 bg-[#FF7A45] hover:bg-[#F9652B] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
            <span>Masuk ke Akun Terotorisasi</span>
            <svg class="w-4 h-4 text-orange-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>

        <!-- Enterprise Single Sign-On / Role Fast-Track Selector -->
        <div class="pt-5 mt-6 border-t border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Akses Cepat Akun Demo (1-Klik)
                </span>
                <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Pilih peran:</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <!-- Requester -->
                <button type="button" onclick="fillRole('requester@orderflow.demo', 'Requester (Pemohon)', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-sky-700">Requester</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">requester@orderflow.demo</div>
                </button>

                <!-- Manager -->
                <button type="button" onclick="fillRole('manager@orderflow.demo', 'Manager Divisi', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-amber-50 border border-slate-200/80 hover:border-amber-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-amber-700">Manager Divisi</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">manager@orderflow.demo</div>
                </button>

                <!-- Procurement -->
                <button type="button" onclick="fillRole('procurement@orderflow.demo', 'Procurement Officer', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-blue-700">Procurement</span>
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">procurement@orderflow.demo</div>
                </button>

                <!-- Finance -->
                <button type="button" onclick="fillRole('finance@orderflow.demo', 'Finance Officer', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200/80 hover:border-emerald-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-emerald-700">Finance</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">finance@orderflow.demo</div>
                </button>

                <!-- Admin -->
                <button type="button" onclick="fillRole('admin@orderflow.demo', 'Super Administrator', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-rose-50 border border-slate-200/80 hover:border-rose-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-rose-700">Super Admin</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">admin@orderflow.demo</div>
                </button>

                <!-- Warehouse / Gudang -->
                <button type="button" onclick="fillRole('warehouse@orderflow.demo', 'Staff Gudang & Logistik', this)" class="role-btn p-2.5 bg-slate-50 hover:bg-teal-50 border border-slate-200/80 hover:border-teal-300 rounded-xl text-left transition duration-150 group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-teal-700">Staff Gudang</span>
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5 truncate">warehouse@orderflow.demo</div>
                </button>
            </div>
            
            <div id="selection-hint" class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600 text-center transition-all duration-200">
                <span>Klik salah satu peran di atas untuk pengisian otomatis.</span>
                <span class="block mt-0.5 font-mono text-[10px] text-slate-400">Password semua akun demo: <strong>password</strong></span>
            </div>
        </div>
    </form>

    <script>
        function fillRole(email, roleLabel, btn) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const hint = document.getElementById('selection-hint');
            
            emailInput.value = email;
            passwordInput.value = 'password';

            // Highlight button
            document.querySelectorAll('.role-btn').forEach(b => {
                b.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
            });
            if (btn) {
                btn.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
            }
            
            if (hint) {
                hint.className = 'mt-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 text-center font-medium transition-all duration-200';
                hint.innerHTML = `&check; Terpilih: <strong>${roleLabel}</strong> (${email}). Klik tombol Masuk di atas.`;
            }

            // Visual feedback on input
            emailInput.classList.add('ring-2', 'ring-blue-500');
            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-blue-500');
            }, 700);
        }
    </script>
</x-guest-layout>
