<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-bold text-white">Masuk ke Portal Karyawan</h2>
        <p class="text-xs text-slate-400 mt-0.5">Gunakan akun email resmi perusahaan Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                Email Perusahaan
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="nama.karyawan@orderflow.com"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Kata Sandi
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-indigo-400 hover:text-indigo-300 transition" href="{{ route('password.request') }}">
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
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900" name="remember">
                <span class="ms-2 text-xs text-slate-400">Ingat perangkat ini</span>
            </label>
        </div>

        <button type="submit" class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/25 transition duration-200">
            Masuk ke Akun
        </button>
    </form>
</x-guest-layout>
