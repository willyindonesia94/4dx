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
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('cascading.wig.index')" :active="request()->routeIs('cascading.wig.*')">
                        {{ __('Cascading WIG') }}
                    </x-nav-link>
                    <x-nav-link :href="route('cascading.lm.index')" :active="request()->routeIs('cascading.lm.*')">
                        {{ __('Cascading LM') }}
                    </x-nav-link>
                    <x-nav-link :href="route('realisasi-wig.index')" :active="request()->routeIs('realisasi-wig.*')">
                        {{ __('Realisasi WIG') }}
                    </x-nav-link>
                    <x-nav-link :href="route('realisasis.index')" :active="request()->routeIs('realisasis.*')">
                        {{ __('Realisasi LM') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('sesi-wigs.index')" :active="request()->routeIs('sesi-wigs.*')">
                        {{ __('Sesi WIG') }}
                    </x-nav-link>

                    @role('Super Admin')
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
                                <x-dropdown-link :href="route('users.index')">
                                    {{ __('Pengguna') }}
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
                        {{ __('Laporan Bulanan') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
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
                            <p class="text-xs font-medium text-gray-500 truncate">{{ Auth::user()->email }}</p>
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
            
            <x-responsive-nav-link :href="route('cascading.wig.index')" :active="request()->routeIs('cascading.wig.*')">
                {{ __('Cascading WIG') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('cascading.lm.index')" :active="request()->routeIs('cascading.lm.*')">
                {{ __('Cascading LM') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('realisasi-wig.index')" :active="request()->routeIs('realisasi-wig.*')">
                {{ __('Realisasi WIG') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('realisasis.index')" :active="request()->routeIs('realisasis.*')">
                {{ __('Realisasi LM') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('sesi-wigs.index')" :active="request()->routeIs('sesi-wigs.*')">
                {{ __('Sesi WIG') }}
            </x-responsive-nav-link>

            @role('Super Admin')
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
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('Pengguna') }}
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
                         <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->email }}</p>
                         <span class="mt-3 px-4 py-1 bg-blue-50 text-blue-700 text-xs font-black uppercase tracking-wider rounded-full border border-blue-100 shadow-sm">
                             {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                         </span>
                     </div>

                     <div class="space-y-3 pb-2">
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
