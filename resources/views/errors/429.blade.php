@extends('layouts.app')
@section('title', '429 — Manajemen Antrean')
@section('content')
<section class="empty-state"><p class="eyebrow">429</p><h1>Terlalu banyak permintaan.</h1><p>Tunggu sebentar sebelum mengambil nomor lagi.</p><a class="button" href="{{ route('queue.index') }}">Kembali ke pengambilan nomor</a></section>
@endsection
