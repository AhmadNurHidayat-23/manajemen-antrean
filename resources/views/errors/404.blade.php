@extends('layouts.app')
@section('title', '404 — Manajemen Antrean')
@section('content')
<section class="empty-state"><p class="eyebrow">404</p><h1>Halaman atau antrean tidak ditemukan.</h1><p>Nomor yang diminta tidak tersedia.</p><a class="button" href="{{ route('queue.index') }}">Kembali ke pengambilan nomor</a></section>
@endsection
