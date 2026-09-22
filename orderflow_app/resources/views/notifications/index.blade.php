<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Notifikasi Sistem & Pengadaan
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Pembaruan status pengajuan, permintaan persetujuan, dan catatan reviewer.</p>
            </div>
            <div>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                        Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
                @forelse($notifications as $notif)
                    <div class="p-5 flex items-start justify-between gap-4 {{ $notif->is_read ? 'bg-white' : 'bg-indigo-50/40' }} hover:bg-slate-50 transition">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 {{ $notif->is_read ? 'bg-slate-100 text-slate-500' : 'bg-indigo-600 text-white shadow-sm' }}">
                                @if($notif->type === 'approval_needed')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($notif->type === 'pr_approved')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($notif->type === 'pr_rejected')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @elseif($notif->type === 'pr_revision')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-slate-900">{{ $notif->title }}</h4>
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->message }}</p>
                                <span class="text-[10px] text-slate-400 block">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            @if($notif->link)
                                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition">
                                        Buka &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-400 space-y-2">
                        <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p class="text-xs font-medium text-slate-600">Belum ada notifikasi baru untuk Anda.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="p-4 bg-white rounded-xl border border-slate-200">
                    {{ $notifications->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
