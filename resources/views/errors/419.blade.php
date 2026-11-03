@extends('layouts.app')
@section('title', '419 — Manajemen Antrean')
@section('content')
<section class="empty-state"><p class="eyebrow">419</p><h1>Sesi telah berakhir.</h1><p>Muat ulang halaman sebelum mengirim tindakan kembali.</p><a class="button" href="{{ route('queue.index') }}">Kembali ke pengambilan nomor</a></section>
@endsection
