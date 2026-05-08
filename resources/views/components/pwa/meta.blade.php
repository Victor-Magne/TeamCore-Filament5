@php
    $manifestPath = public_path('build/manifest.json');
    $manifest = [];
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
    }
    $swPath = asset('build/registerSW.js');
@endphp

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="TeamCore">
<meta name="theme-color" content="#582f0e">

<link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('images/Document.svg') }}">

<script src="{{ $swPath }}"></script>
