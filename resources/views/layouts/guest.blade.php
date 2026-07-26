<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/2/20/Logo_PLN.svg">

        <!-- PWA Meta Tags -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#4f46e5">
        <link rel="apple-touch-icon" href="/pwa/icon.png">
        <link rel="apple-touch-startup-image" href="/pwa/splash.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="4DX UID JABAR">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- PWA Splash Screen Overlay -->
        <div id="pwa-splash"></div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        <!-- Splash & PWA Script -->
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

            // Register Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js');
                });
            }
        </script>
    </body>
</html>
