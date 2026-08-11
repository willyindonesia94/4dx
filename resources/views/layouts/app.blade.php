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
        <link rel="apple-touch-icon" href="/pwa/icon.png">
        <link rel="apple-touch-startup-image" href="/pwa/splash.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="4DX UID JABAR">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        <script>

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
                    
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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
