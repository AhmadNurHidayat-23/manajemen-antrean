@extends('layouts.app')
@section('content')
<section class="hero">
    <p class="eyebrow"><i aria-hidden="true"></i> RUANG TUNGGU, LEBIH TERATUR</p>
    <h1>Ambil Nomor Antrean Anda.</h1>
    <p>Ambil nomor, simpan, dan tunggu panggilan sesuai urutan.</p>
</section>
<section class="kiosk-grid" aria-label="Pengambilan antrean">
    <div class="take-panel">
        <p class="step">01 / NOMOR ANDA</p>
        @if ($mine)
            <p class="your-number">{{ $mine->label() }}</p>
            <h2>Nomor Anda sudah siap.</h2>
            <p class="panel-description">Simpan nomor ini dan perhatikan panggilan pada layar pelayanan.</p>
        @else
            <h2>Mulai antrean Anda di sini.</h2>
            <p class="panel-description">Nomor dibuat otomatis sesuai urutan pengambilan hari ini.</p>
        @endif
        <form action="{{ route('queue.take') }}" method="post">
            @csrf
            <button class="button full" type="submit">
                {{ $mine ? 'Ambil nomor baru' : 'Ambil nomor antrean' }}
                <span aria-hidden="true">↗</span>
            </button>
        </form>
        <p class="hint">Nomor dimulai lagi setiap hari. Tanggal layanan: {{ $snapshot['display_date'] }}.</p>
    </div>
    <div class="current-panel">
        <div class="panel-top">
            <p class="step">02 / SEDANG DILAYANI</p>
            <span class="live"><i aria-hidden="true"></i>Langsung</span>
        </div>
        <div class="current-block" aria-live="polite" aria-atomic="true">
            <p class="current-number" data-current-number>{{ $snapshot['current'] }}</p>
            <p class="current-caption" data-current-caption>
                {{ $snapshot['current_id'] ? 'Silakan menuju tempat pelayanan.' : 'Belum ada antrean yang dipanggil.' }}
            </p>
        </div>
        <div class="waiting-line">
            <span>Antrean menunggu</span>
            <strong data-count="waiting">{{ $snapshot['counts']['waiting'] }}</strong>
        </div>
        <p class="update-note" data-connection>Pembaruan otomatis setiap 5 detik.</p>
    </div>
</section>
<div class="page-note">
    <span>Ikuti panggilan sesuai nomor Anda.</span>
    <a href="{{ route('queue.display') }}">Buka tampilan antrean ↗</a>
</div>
@endsection
