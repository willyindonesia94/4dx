<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 border-t-4  shadow-sm sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php
                        $userRole = auth()->user()->role_name ?? (auth()->user()->roles->pluck('name')->first() ?? '');
                        $isUlp = str_contains(strtoupper($userRole), 'ULP') || (auth()->user()->unit && strtoupper(auth()->user()->unit->type) === 'ULP');
                        $isUp3 = str_contains(strtoupper($userRole), 'UP3') || str_contains(strtoupper($userRole), 'UP2D') || str_contains(strtoupper($userRole), 'UP2K') || (auth()->user()->unit && in_array(strtoupper(auth()->user()->unit->type), ['UP3', 'UP2D', 'UP2K']));
                        $isSrmPerencanaan = str_contains(strtoupper($userRole), 'SRM PERENCANAAN');
                    @endphp

                    @if(!$isSrmPerencanaan)
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(!$isUlp && !$isUp3)
                    <x-nav-link :href="route('cascading.wig.index')" :active="request()->routeIs('cascading.wig.*')">
                        {{ __('Cascading WIG') }}
                    </x-nav-link>
                    @endif

                    @if(!$isUlp)
                    <x-nav-link :href="route('cascading.lm.index')" :active="request()->routeIs('cascading.lm.*')">
                        {{ __('Cascading LM') }}
                    </x-nav-link>
                    <x-nav-link :href="route('realisasi-wig.index')" :active="request()->routeIs('realisasi-wig.*')">
                        {{ __('Realisasi WIG') }}
                    </x-nav-link>
                    @endif
                    <x-nav-link :href="route('realisasis.index')" :active="request()->routeIs('realisasis.*')">
                        {{ __('Realisasi LM') }}
                    </x-nav-link>
                    @endif
                    
                    <x-nav-link :href="route('sesi-wigs.index')" :active="request()->routeIs('sesi-wigs.*')">
                        {{ __('Sesi WIG') }}
                    </x-nav-link>

                    @if(!$isSrmPerencanaan)
                    @hasanyrole('Super Admin|Perencanaan UID')
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out h-full {{ request()->routeIs('master-*') || request()->routeIs('users.*') ? 'border-blue-600 text-gray-900 focus:border-blue-700' : '' }}">
                                    <div>Master Data</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('master-wigs.index')">
                                    {{ __('Master WIG') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('master-lms.index')">
                                    {{ __('Master LM') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('master-periodes.index')">
                                    {{ __('Master Periode') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('users.index')">
                                    {{ __('Pengguna') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('master-bidangs.index')">
                                    {{ __('Master Bidang') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('master-units.index')">
                                    {{ __('Master Unit') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('master-satuans.index')">
                                    {{ __('Master Satuan') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endrole

                    <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        {{ __('Laporan') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown & Notifications -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                
                <!-- Notification Bell -->
                <style>
                    .notif-dropdown-width {
                        width: 380px !important;
                        min-width: 380px !important;
                    }
                    @media (max-width: 640px) {
                        .notif-dropdown-width {
                            width: 300px !important;
                            min-width: 300px !important;
                        }
                    }
                </style>
                <x-dropdown align="right" width="notif-dropdown-width">
                    <x-slot name="trigger">
                        <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <!-- Badge Unread -->
                                @php
                                    $validDbNotifications = Auth::user()->unreadNotifications->filter(function($notification) {
                                        return !str_contains($notification->data['title'] ?? '', 'Persetujuan');
                                    })->count();
                                    
                                    $pendingWigs = collect();
                                    $pendingLms = collect();
                                    $pendingBreakdownWigs = collect();
                                    $pendingBreakdownLms = collect();
                                    
                                    $isSuperAdmin = auth()->user()->role_name === 'Super Admin' || auth()->user()->hasRole('Super Admin');
                                    $isSubBidang = auth()->user()->role_name === 'Sub Bidang UID' || auth()->user()->hasRole('Sub Bidang UID');
                                    $isManagerUp3 = in_array(auth()->user()->role_name, ['Manager UP3', 'UP2K', 'UP2D']) || auth()->user()->hasAnyRole(['Manager UP3', 'UP2K', 'UP2D']);
                                    
                                    if ($isSuperAdmin) {
                                        $pendingWigs = \App\Models\MasterWig::where('is_approved', false)->get();
                                        $pendingLms = \App\Models\MasterLm::with('wig')->whereHas('wig')->where('is_approved', false)->get();
                                        $pendingBreakdownWigs = \App\Models\BreakdownWig::with(['wig', 'unit'])->whereHas('wig', function($q) { $q->where('is_approved', true); })->whereHas('unit')->where('is_approved', false)->get()->unique('unit_id');
                                        $pendingBreakdownLms = \App\Models\BreakdownLm::with(['lm', 'unit'])->whereHas('lm', function($q) { $q->where('is_approved', true)->whereHas('wig', function($q2) { $q2->where('is_approved', true); }); })->whereHas('unit')->where('is_approved', false)->get()->unique('unit_id');
                                    } elseif ($isSubBidang) {
                                        $userDivisi = (string)auth()->user()->matrix_group_id;
                                        
                                        $pendingWigs = \App\Models\MasterWig::where('is_approved', false)
                                            ->where(function($q) use ($userDivisi) {
                                                $q->where('divisi', $userDivisi)
                                                  ->orWhere('divisi', 'like', '%"'.$userDivisi.'"%');
                                            })->get();
                                            
                                        $pendingLms = \App\Models\MasterLm::with('wig')->where('is_approved', false)
                                            ->whereHas('wig', function($q) use ($userDivisi) {
                                                $q->where('divisi', $userDivisi)
                                                  ->orWhere('divisi', 'like', '%"'.$userDivisi.'"%');
                                            })->get();
                                        
                                        $pendingBreakdownWigs = \App\Models\BreakdownWig::with(['wig', 'unit'])->whereHas('unit')->where('is_approved', false)
                                            ->whereHas('wig', function($q) use ($userDivisi) {
                                                $q->where('is_approved', true)->where(function($q2) use ($userDivisi) {
                                                    $q2->where('divisi', $userDivisi)->orWhere('divisi', 'like', '%"'.$userDivisi.'"%');
                                                });
                                            })->get()->unique('unit_id');
                                        
                                        $pendingBreakdownLms = \App\Models\BreakdownLm::with(['lm.wig', 'unit'])->whereHas('unit')->where('is_approved', false)
                                            ->whereHas('lm', function($q) use ($userDivisi) {
                                                $q->where('is_approved', true)->whereHas('wig', function($qw) use ($userDivisi) {
                                                    $qw->where('is_approved', true)->where(function($q2) use ($userDivisi) {
                                                        $q2->where('divisi', $userDivisi)->orWhere('divisi', 'like', '%"'.$userDivisi.'"%');
                                                    });
                                                });
                                            })->get()->unique('unit_id');
                                    } elseif ($isManagerUp3) {
                                        $userUnitId = auth()->user()->unit_id;
                                        
                                        $pendingBreakdownWigs = \App\Models\BreakdownWig::with(['wig', 'unit'])
                                            ->whereHas('wig', function($q) { $q->where('is_approved', true); })->whereHas('unit')
                                            ->where('is_approved', false)
                                            ->where('unit_id', $userUnitId)
                                            ->get()->unique('unit_id');

                                        $pendingBreakdownLms = \App\Models\BreakdownLm::with(['lm', 'unit'])->where('is_approved', false)
                                            ->whereHas('lm', function($q) { $q->where('is_approved', true)->whereHas('wig', function($q2) { $q2->where('is_approved', true); }); })
                                            ->whereHas('unit', function($q) use ($userUnitId) {
                                                // Only show breakdowns for ULP under this UP3, or for the UP3 itself
                                                $q->where('parent_id', $userUnitId)->orWhere('id', $userUnitId);
                                            })->get()->unique('unit_id');
                                    }
                                    $unreadCount = $validDbNotifications + $pendingWigs->count() + $pendingLms->count() + $pendingBreakdownWigs->count() + $pendingBreakdownLms->count();
                                    
                                    $historyNotifications = Auth::user()->notifications->filter(function($notification) {
                                        return !(isset($notification->data['title']) && str_contains($notification->data['title'], 'Persetujuan'));
                                    })->take(3);
                                @endphp
                                <span id="notification-counter" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full {{ $unreadCount > 0 ? '' : 'hidden' }}">
                                    {{ $unreadCount }}
                                </span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-5 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-xl">
                            <span class="text-sm font-bold text-gray-800">Notifikasi Terbaru</span>
                            <div class="flex items-center gap-3">
                                @if(Auth::user()->notifications()->count() > 0)
                                <form action="{{ route('notifications.clearAll') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat notifikasi?');">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-red-600 hover:text-red-800 transition-colors bg-red-50 hover:bg-red-100 px-2 py-1 rounded border border-red-100">
                                        Bersihkan
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @foreach($pendingWigs as $wig)
                                <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/80 hover:bg-orange-100/80 transition-colors duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-0.5 p-2 bg-orange-100 rounded-full text-orange-600 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900 font-bold mb-1">Persetujuan WIG Baru</p>
                                            <p class="text-xs text-gray-600 leading-relaxed mb-3">WIG <span class="font-bold text-gray-800">"{{ $wig->judul }}"</span> sedang menunggu persetujuan Anda.</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400 font-medium">{{ $wig->created_at->diffForHumans() }}</span>
                                                <a href="{{ route('master-wigs.index', ['highlight_wig' => $wig->id, 'status' => 'draft']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs text-orange-600 hover:text-orange-700 font-bold rounded-lg border border-orange-200 shadow-sm hover:shadow transition-all">
                                                    Lihat Detail
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @foreach($pendingLms as $lm)
                                <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/80 hover:bg-orange-100/80 transition-colors duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-0.5 p-2 bg-orange-100 rounded-full text-orange-600 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900 font-bold mb-1">Persetujuan LM Baru</p>
                                            <p class="text-xs text-gray-600 leading-relaxed mb-3">LM <span class="font-bold text-gray-800">"{{ $lm->judul_lm }}"</span> sedang menunggu persetujuan Anda.</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400 font-medium">{{ $lm->created_at->diffForHumans() }}</span>
                                                <a href="{{ route('master-lms.index', ['highlight_lm' => $lm->id, 'status' => 'draft']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs text-orange-600 hover:text-orange-700 font-bold rounded-lg border border-orange-200 shadow-sm hover:shadow transition-all">
                                                    Lihat Detail
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($pendingBreakdownWigs as $bw)
                                <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/80 hover:bg-orange-100/80 transition-colors duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-0.5 p-2 bg-orange-100 rounded-full text-orange-600 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900 font-bold mb-1">Persetujuan Cascading WIG</p>
                                            <p class="text-xs text-gray-600 leading-relaxed mb-3">Cascading WIG untuk Unit <span class="font-bold text-gray-800">"{{ $bw->unit->name ?? '-' }}"</span> sedang menunggu persetujuan Anda.</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400 font-medium">{{ $bw->created_at->diffForHumans() }}</span>
                                                <a href="{{ route('cascading.wig.index', ['highlight_unit' => $bw->unit_id, 'status' => 'draft']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs text-orange-600 hover:text-orange-700 font-bold rounded-lg border border-orange-200 shadow-sm hover:shadow transition-all">
                                                    Lihat Detail
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($pendingBreakdownLms as $bl)
                                <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/80 hover:bg-orange-100/80 transition-colors duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-0.5 p-2 bg-orange-100 rounded-full text-orange-600 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900 font-bold mb-1">Persetujuan Cascading LM</p>
                                            <p class="text-xs text-gray-600 leading-relaxed mb-3">Cascading LM untuk Unit <span class="font-bold text-gray-800">"{{ $bl->unit->name ?? '-' }}"</span> sedang menunggu persetujuan Anda.</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400 font-medium">{{ $bl->created_at->diffForHumans() }}</span>
                                                <a href="{{ route('cascading.lm.index', ['highlight_unit' => $bl->unit_id, 'status' => 'draft']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-xs text-orange-600 hover:text-orange-700 font-bold rounded-lg border border-orange-200 shadow-sm hover:shadow transition-all">
                                                    Lihat Detail
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @forelse($historyNotifications as $notification)
                                <div class="px-5 py-4 border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/50' }} hover:bg-gray-50 transition-colors">
                                    <div class="flex flex-col">
                                        <p class="text-sm text-gray-900 font-bold">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                                        <div class="mt-3 flex justify-between items-center">
                                            <span class="text-[10px] text-gray-500 font-medium">{{ $notification->created_at->diffForHumans() }}</span>
                                            <div class="flex items-center gap-2">
                                                @php
                                                    $notifUrl = null;
                                                    $notifType = $notification->data['type_item'] ?? '';
                                                    $notifTitle = $notification->data['title'] ?? '';
                                                    if (str_contains($notifType, 'Cascading LM') || str_contains($notifTitle, 'Cascading LM')) {
                                                        $notifUrl = route('cascading.lm.index', ['status' => 'draft']);
                                                    } elseif (str_contains($notifType, 'Cascading WIG') || str_contains($notifTitle, 'Cascading WIG')) {
                                                        $notifUrl = route('cascading.wig.index', ['status' => 'draft']);
                                                    } elseif (str_contains($notifType, 'Master LM') || str_contains($notifTitle, 'Master LM')) {
                                                        $notifUrl = route('master-lms.index', ['status' => 'draft']);
                                                    } elseif (str_contains($notifType, 'Master WIG') || str_contains($notifTitle, 'Master WIG')) {
                                                        $notifUrl = route('master-wigs.index', ['status' => 'draft']);
                                                    }
                                                @endphp
                                                @if($notifUrl && !$notification->read_at)
                                                    <a href="{{ $notifUrl }}" class="text-[11px] text-orange-600 hover:text-orange-800 font-bold bg-white border border-gray-200 px-3 py-1.5 rounded-md hover:shadow-sm transition-all">Lihat Detail</a>
                                                @endif
                                                @if(!$notification->read_at)
                                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-[11px] text-blue-600 hover:text-blue-800 font-bold bg-white border border-gray-200 px-3 py-1.5 rounded-md hover:shadow-sm transition-all">Tandai Dibaca</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @if($pendingWigs->isEmpty() && $pendingLms->isEmpty() && $pendingBreakdownWigs->isEmpty() && $pendingBreakdownLms->isEmpty())
                                    <div class="px-5 py-6 text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                        <p class="text-sm font-semibold text-gray-500">Belum ada notifikasi.</p>
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>

                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 p-1.5 border border-transparent font-medium rounded-full text-gray-500 bg-white hover:bg-gray-50 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200" title="{{ Auth::user()->name }}">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center overflow-hidden border border-gray-200">
                                @if (Auth::user()->profile_photo)
                                    <img src="{{ Storage::url(Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-full h-full text-gray-400 bg-gray-100" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                @endif
                            </div>
                            <!-- Caret -->
                            <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile Header -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs font-medium text-gray-500 truncate">{{ Auth::user()->username }}</p>
                            <span class="inline-block mt-2 px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-md border border-blue-100">
                                {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                            </span>
                        </div>
                        
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ __('Profil') }}
                                </div>
                            </x-dropdown-link>

                            @hasanyrole('Super Admin|Perencanaan UID|General Manager UID|Manager UP3|UP2K|UP2D|Manager ULP|Perencanaan UP3|Staff ULP')
                            <x-dropdown-link :href="route('audit-logs.index')">
                                <div class="flex items-center text-gray-700">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    {{ __('Audit Log') }}
                                </div>
                            </x-dropdown-link>
                            @endhasanyrole

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    <div class="flex items-center text-red-600">
                                        <svg class="w-4 h-4 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        {{ __('Keluar') }}
                                    </div>
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="open" 
         x-transition:enter="transition-opacity ease-linear duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-800 bg-opacity-50 z-40 sm:hidden" 
         @click="open = false" style="display: none;"></div>

    <!-- Responsive Navigation Menu (Sidebar) -->
    <div :class="{'translate-x-0': open, '-translate-x-full': ! open}" 
         class="fixed inset-y-0 left-0 w-64 bg-white shadow-2xl z-50 transform transition-transform duration-300 ease-in-out sm:hidden overflow-y-auto border-t-4  flex flex-col">
        
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <x-application-logo class="block h-8 w-auto" />
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links Container (Scrollable) -->
        <div class="pt-2 pb-3 space-y-1 flex-1 overflow-y-auto">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @if(!$isUlp && !$isUp3)
            <x-responsive-nav-link :href="route('cascading.wig.index')" :active="request()->routeIs('cascading.wig.*')">
                {{ __('Cascading WIG') }}
            </x-responsive-nav-link>
            @endif
            
            @if(!$isUlp)
            <x-responsive-nav-link :href="route('cascading.lm.index')" :active="request()->routeIs('cascading.lm.*')">
                {{ __('Cascading LM') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('realisasi-wig.index')" :active="request()->routeIs('realisasi-wig.*')">
                {{ __('Realisasi WIG') }}
            </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('realisasis.index')" :active="request()->routeIs('realisasis.*')">
                {{ __('Realisasi LM') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('sesi-wigs.index')" :active="request()->routeIs('sesi-wigs.*')">
                {{ __('Sesi WIG') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                {{ __('Laporan') }}
            </x-responsive-nav-link>

            @hasanyrole('Super Admin|Perencanaan UID')
            <div class="border-t border-gray-100 mt-2 pt-2">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Master Data
                </div>
                <x-responsive-nav-link :href="route('master-wigs.index')" :active="request()->routeIs('master-wigs.*')">
                    {{ __('Master WIG') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('master-lms.index')" :active="request()->routeIs('master-lms.*')">
                    {{ __('Master LM') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('master-periodes.index')" :active="request()->routeIs('master-periodes.*')">
                    {{ __('Master Periode') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('Pengguna') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('master-bidangs.index')" :active="request()->routeIs('master-bidangs.*')">
                    {{ __('Master Bidang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('master-units.index')" :active="request()->routeIs('master-units.*')">
                    {{ __('Master Unit') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('master-satuans.index')" :active="request()->routeIs('master-satuans.*')">
                    {{ __('Master Satuan') }}
                </x-responsive-nav-link>
            </div>
            @endrole

        </div>

        <!-- Responsive Settings Options (Bottom User Profile) -->
        <div class="p-4 border-t border-gray-100 mt-auto bg-gray-50/80" x-data="{ profileModal: false }">
            <button @click="profileModal = true" class="w-full flex items-center justify-between p-2 rounded-xl hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden border border-gray-200">
                        @if (Auth::user()->profile_photo)
                            <img src="{{ Storage::url(Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-full h-full text-gray-400 bg-gray-100" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="text-left w-28">
                        <div class="font-bold text-sm text-gray-800 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] font-semibold text-blue-500 uppercase truncate">
                            {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                        </div>
                    </div>
                </div>
                <div class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </button>

            <!-- Profile Pop Up Modal -->
            <div x-show="profileModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="profileModal = false"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                
                <!-- Modal Content -->
                <div class="bg-white rounded-3xl shadow-2xl transform transition-all p-5 mx-4 w-full max-w-sm relative z-[101]"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                     
                     <div class="flex justify-end">
                         <button @click="profileModal = false" class="p-2 text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-200 rounded-full transition-colors focus:outline-none">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                         </button>
                     </div>

                     <div class="flex flex-col items-center mb-6 mt-[-10px]">
                         <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4 shadow-md overflow-hidden border border-gray-200">
                             @if (Auth::user()->profile_photo)
                                 <img src="{{ Storage::url(Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                             @else
                                 <svg class="w-full h-full text-gray-400 bg-gray-100" fill="currentColor" viewBox="0 0 24 24">
                                     <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                 </svg>
                             @endif
                         </div>
                         <h3 class="text-xl font-extrabold text-gray-900 text-center leading-tight">{{ Auth::user()->name }}</h3>
                         <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->username }}</p>
                         <span class="mt-3 px-4 py-1 bg-blue-50 text-blue-700 text-xs font-black uppercase tracking-wider rounded-full border border-blue-100 shadow-sm">
                             {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                         </span>
                     </div>

                     <div class="space-y-3 pb-2">
                         @hasanyrole('Super Admin|Perencanaan UID|General Manager UID|Manager UP3|UP2K|UP2D|Manager ULP|Perencanaan UP3|Staff ULP')
                         <a href="{{ route('audit-logs.index') }}" class="flex items-center justify-center w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-100 hover:border-gray-300 transition-all shadow-sm">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Audit Log
                         </a>
                         @endhasanyrole
                         <a href="{{ route('profile.edit') }}" class="flex items-center justify-center w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-100 hover:border-gray-300 transition-all shadow-sm">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Edit Profil
                         </a>
                         <form method="POST" action="{{ route('logout') }}">
                             @csrf
                             <button type="submit" class="flex items-center justify-center w-full px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm font-bold text-red-600 hover:bg-red-100 hover:border-red-200 transition-all shadow-sm">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                             </button>
                         </form>
                     </div>
                </div>
            </div>
        </div>
    </div>
</nav>
