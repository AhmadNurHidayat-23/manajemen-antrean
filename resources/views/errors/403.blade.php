@extends('layouts.app')
@section('title', '403 — Manajemen Antrean')
@section('content')
<section class="empty-state"><p class="eyebrow">403</p><h1>Operator hanya untuk demo lokal.</h1><p>Gunakan localhost pada lingkungan lokal. Tambahkan autentikasi dan otorisasi sebelum memublikasikan akses operator.</p><a class="button" href="{{ route('queue.index') }}">Kembali ke pengambilan nomor</a></section>
@endsection
