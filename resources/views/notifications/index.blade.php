<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Semua Notifikasi') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Riwayat Notifikasi Anda</h3>
                        <div class="flex items-center gap-4">
                            @if($notifications->count() > 0)
                            <form action="{{ route('notifications.clearAll') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat notifikasi Anda? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Bersihkan Semua
                                </button>
                            </form>
                            @endif
                            <div class="text-sm text-gray-500">
                                Total: <span class="font-bold text-gray-700">{{ $notifications->total() }}</span> Notifikasi
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($notifications as $notification)
                            <div class="p-5 rounded-xl border transition-all duration-200 {{ $notification->read_at ? 'bg-gray-50 border-gray-100' : 'bg-blue-50/50 border-blue-100 hover:bg-blue-50 hover:shadow-sm' }}">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex gap-4">
                                        <div class="mt-1 flex-shrink-0">
                                            @if($notification->read_at)
                                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-base {{ $notification->read_at ? 'text-gray-600' : 'text-blue-900' }}">{{ $notification->data['title'] ?? 'Notifikasi' }}</h4>
                                            <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                                            <div class="flex items-center gap-3 mt-3">
                                                <span class="text-xs font-medium px-2.5 py-1 rounded-md {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                <span class="text-xs text-gray-400">{{ $notification->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold bg-white text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-300 px-4 py-2 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-blue-500/20">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <h3 class="text-base font-bold text-gray-700">Tidak ada notifikasi</h3>
                                <p class="text-sm text-gray-500 mt-1">Saat ini Anda tidak memiliki riwayat notifikasi apapun.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($notifications->hasPages())
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
