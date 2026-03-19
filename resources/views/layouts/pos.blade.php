<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <!-- PWA -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pizza Pich">
    <link rel="apple-touch-icon" href="/pwa-icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#dc2626">

    <title>{{ $title ?? "Pizza Pich' POS" }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Outfit', sans-serif; -webkit-tap-highlight-color: transparent; user-select: none; touch-action: manipulation; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .btn-press:active { transform: scale(0.96); transition: transform 0.1s; }
        [x-cloak] { display: none !important; }

        /* PORTRAIT OVERLAY DETECTION */
        #orientation-overlay { display: none; }
        @media screen and (orientation: portrait) {
            #orientation-overlay { display: flex !important; }
        }
        @keyframes rotate-phone { 0% { transform: rotate(0deg); } 25% { transform: rotate(90deg); } 100% { transform: rotate(90deg); } }
        .animate-rotate { animation: rotate-phone 2s infinite ease-in-out; }
    </style>
    @livewireStyles
</head>

<body class="bg-[#050505] text-white h-screen overflow-hidden">
    
    <!-- Orientation Overlay -->
    <div id="orientation-overlay" class="fixed inset-0 z-[9999] bg-[#050505]/95 backdrop-blur-xl flex flex-col items-center justify-center p-12 text-center">
        <div class="relative w-24 h-48 border-4 border-white/20 rounded-[2rem] bg-zinc-950 flex items-center justify-center animate-rotate">
            <div class="w-1.5 h-1.5 rounded-full bg-white/40 absolute bottom-4"></div>
            <span class="text-3xl">🍕</span>
        </div>
        <h2 class="text-2xl font-black uppercase tracking-tighter mt-12 mb-2 italic">Orientation paysage requise</h2>
        <p class="text-[0.6rem] font-black uppercase tracking-[0.3em] text-white/40 leading-relaxed max-w-[240px]">Veuillez tourner votre appareil pour utiliser la caisse</p>
    </div>

    {{ $slot }}
    @livewireScripts
    <script>
        // SERVICE WORKER
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered:', registration);
                }).catch(error => {
                    console.log('SW registration failed:', error);
                });
            });
        }

        // PWA Check (Standalone mode)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        // Push Notifications
        async function subscribeUser() {
            if ('serviceWorker' in navigator && 'PushManager' in window) {
                try {
                    const registration = await navigator.serviceWorker.ready;
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array('{{ env("VAPID_PUBLIC_KEY") }}')
                    });

                    await fetch('{{ route("push.subscriptions.store") }}', {
                        method: 'POST',
                        body: JSON.stringify(subscription),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    console.log('User is subscribed.');
                } catch (err) {
                    console.log('Failed to subscribe the user: ', err);
                }
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // Ask for permission and subscribe if granted
        window.addEventListener('load', () => {
            if ('Notification' in window && Notification.permission !== 'granted') {
                // If it's iOS, we can only ask if in standalone mode
                if (isIOS && !isStandalone) {
                    console.warn('Notifications on iOS require the app to be added to the home screen.');
                    return;
                }
                
                // Show a custom UI or just ask
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        subscribeUser();
                    }
                });
            } else if ('Notification' in window && Notification.permission === 'granted') {
                subscribeUser();
            }
        });

        // ORIENTATION LOCK ATTEMPT
        async function lockOrientation() {
            if (screen.orientation && screen.orientation.lock) {
                try {
                    await screen.orientation.lock('landscape');
                } catch (err) {
                    console.warn("Orientation lock denied:", err);
                }
            }
        }
        window.addEventListener('load', lockOrientation);
        document.addEventListener('click', lockOrientation, { once: true });
    </script>
</body>

</html>
