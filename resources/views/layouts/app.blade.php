<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ambil nomor antrean, kelola panggilan, dan pantau pelayanan secara langsung. Manajemen Antrean dengan pembaruan otomatis.">
    <meta name="theme-color" content="#F5F3EB">
    <title>@yield('title', 'Manajemen Antrean')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="shell site-header">
    <a class="brand" href="{{ route('queue.index') }}">Manajemen Antrean<span>.</span></a>
    <nav aria-label="Navigasi utama">
        <a href="{{ route('queue.index') }}" @if (request()->routeIs('queue.index')) aria-current="page" @endif>Ambil nomor</a>
        <a href="{{ route('operator.index') }}" @if (request()->routeIs('operator.*')) aria-current="page" @endif>Operator</a>
        <a href="{{ route('queue.display') }}" @if (request()->routeIs('queue.display')) aria-current="page" @endif>Monitor ↗</a>
    </nav>
</header>
<main class="shell main-content"
    @isset($snapshot) data-live-endpoint="{{ route('queue.status') }}" data-live-day="{{ $snapshot['date'] }}" @endisset
    @isset($operatorVersion) data-reload-version="{{ $operatorVersion }}" @endisset>
    @if (session('success'))
        <p class="notice" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="errors" role="alert">
            <p>Tindakan belum dapat diproses.</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
<footer class="shell site-footer">
    <p>Manajemen Antrean.</p>
    <span>Pelayanan lebih tertata, satu nomor pada waktunya.</span>
</footer>
</body>
</html>
