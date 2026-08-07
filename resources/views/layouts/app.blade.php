<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

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

        <!-- Splash Screen CSS -->
        <style>
            #pwa-splash {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background-color: #ffffff;
                background-image: url('/pwa/splash.png');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                z-index: 999999;
                transition: opacity 0.5s ease;
            }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- PWA Splash Screen Overlay -->
        <div id="pwa-splash"></div>

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

        <!-- PWA Service Worker Registration & Splash Script -->
        <script>
            // Hide splash screen after a short delay
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const splash = document.getElementById('pwa-splash');
                    if(splash) {
                        splash.style.opacity = '0';
                        setTimeout(() => splash.remove(), 500);
                    }
                }, 1500); // 1.5 seconds visible delay
            });

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
        </script>
    </body>
</html>
