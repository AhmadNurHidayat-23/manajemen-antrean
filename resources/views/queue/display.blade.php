@extends('layouts.app')
@section('title', 'Tampilan Pelayanan — Manajemen Antrean')
@section('content')
<section class="hero monitor-heading">
    <p class="eyebrow">LAYAR PELAYANAN / {{ $snapshot['display_date'] }}</p>
    <h1>Antrean yang Sedang Dilayani.</h1>
    <p>Perhatikan nomor panggilan dan bersiap saat giliran Anda tiba.</p>
</section>
<section class="monitor-panel">
    <div class="monitor-current" aria-live="polite" aria-atomic="true">
        <p class="step">NOMOR YANG DIPANGGIL</p>
        <p class="monitor-number" data-current-number>{{ $snapshot['current'] }}</p>
        <p class="current-caption" data-current-caption>
            {{ $snapshot['current_id'] ? 'Silakan menuju tempat pelayanan.' : 'Belum ada antrean yang dipanggil.' }}
        </p>
    </div>
    <div class="monitor-bottom">
        <div>
            <span>Antrean menunggu</span>
            <strong data-count="waiting">{{ $snapshot['counts']['waiting'] }}</strong>
        </div>
        <p data-connection>Pembaruan otomatis setiap 5 detik.</p>
    </div>
</section>
@endsection
