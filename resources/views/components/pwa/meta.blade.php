<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="TeamCore">
<meta name="theme-color" content="#582f0e">

<link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('images/Document.svg') }}">
<link rel="icon" href="{{ asset('images/pwa-192.png') }}" sizes="192x192" type="image/png">
<link rel="icon" href="{{ asset('images/pwa-512.png') }}" sizes="512x512" type="image/png">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function (error) {
                console.error('Service worker registration failed:', error);
            });
        });
    }
</script>
