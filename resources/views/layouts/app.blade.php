<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Auto refresh saat session expired -->
        <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA & Chart -->
        <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/2/20/Logo_PLN.svg">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#4f46e5">
        <link rel="apple-touch-icon" href="/pwa/apple-touch-icon.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="4DX UID JABAR">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

        <!-- Pustaka Flatpickr (Global) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @if(session()->has('impersonator_id'))
            <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white px-4 py-2.5 shadow-md sticky top-0 z-[100] border-b border-amber-700">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 relative flex-shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                        </span>
                        <span>
                            <strong>Mode Akses Pengguna:</strong> Anda sedang masuk sebagai
                            <span class="underline font-bold">{{ auth()->user()->name }}</span>
                            <span class="bg-white/20 px-2 py-0.5 rounded text-xs ml-1 font-medium">{{ auth()->user()->role_name ?? 'User' }}</span>
                            @if(auth()->user()->unit)
                                <span class="text-amber-100 text-xs">({{ auth()->user()->unit->name }})</span>
                            @endif
                        </span>
                    </div>
                    <form action="{{ route('impersonate.leave', [], false) }}" method="POST" class="inline m-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-white text-amber-900 hover:bg-amber-50 font-bold px-3 py-1.5 rounded-md text-xs shadow-sm transition-all transform hover:scale-105">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Kembali ke Akun Utama
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- PWA Service Worker Registration -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Notification Polling Logic
            let lastNotificationCount = 0;
            let notifiedIds = new Set();
            
            function checkNotifications() {
                fetch('{{ route("notifications.check") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        // Update bell counter if exists
                        const bellCounter = document.getElementById('notification-counter');
                        if (bellCounter) {
                            bellCounter.innerText = data.notifications.length;
                            bellCounter.classList.remove('hidden');
                        }

                        // Show SweetAlert popup for newly arrived notifications
                        data.notifications.forEach(notif => {
                            if (!notifiedIds.has(notif.id)) {
                                notifiedIds.add(notif.id);
                                
                                Swal.fire({
                                    title: notif.data.title || 'Pemberitahuan Baru',
                                    text: notif.data.message,
                                    icon: notif.data.type || 'info',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                                    }
                                });
                            }
                        });
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
            }

            @auth
            // Check notifications on page load
            checkNotifications();
            // Polling every 1 minute
            setInterval(checkNotifications, 60000);
            @endauth

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js').then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }

            // Convert native date inputs to Flatpickr with Indonesian locale
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof flatpickr !== 'undefined') {
                    flatpickr('input[type="date"]', {
                        locale: 'id',
                        dateFormat: 'Y-m-d'
                    });
                }
            });

            window.performAjaxSearch = function(inputElement, containerId) {
                clearTimeout(inputElement.timer);
                inputElement.timer = setTimeout(() => {
                    const form = inputElement.closest('form');
                    const url = new URL(form.action);
                    const formData = new FormData(form);
                    for (const [key, value] of formData.entries()) {
                        url.searchParams.set(key, value);
                    }
                    
                    const container = document.getElementById(containerId);
                    if (container) container.style.opacity = '0.5';
                    
                    fetch(url, { 
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' },
                        cache: 'no-store'
                    })
                        .then(res => res.text())
                        .then(html => {
                            const doc = new DOMParser().parseFromString(html, 'text/html');
                            const newContainer = doc.getElementById(containerId);
                            if (newContainer && container) {
                                container.innerHTML = newContainer.innerHTML;
                                container.style.opacity = '1';
                            }
                        });
                }, 500);
            }
        </script>
    </body>
</html>
