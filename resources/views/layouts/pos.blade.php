<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <!-- PWA -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pizza Pich">
    <link rel="apple-touch-icon" href="/pwa-icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#dc2626">

    <title>{{ $title ?? "Pizza Pich' POS" }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            touch-action: manipulation;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-press:active {
            transform: scale(0.96);
            transition: transform 0.1s;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @livewireStyles
</head>

<body class="bg-[#050505] text-white h-screen overflow-hidden">
    {{ $slot }}
    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered:', registration);
                }).catch(error => {
                    console.log('SW registration failed:', error);
                });
            });
        }
    </script>
</body>

</html>
