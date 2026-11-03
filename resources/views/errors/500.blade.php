@extends('layouts.app')
@section('title', '500 — Manajemen Antrean')
@section('content')
<section class="empty-state"><p class="eyebrow">500</p><h1>Terjadi kendala.</h1><p>Permintaan belum dapat diproses. Silakan coba kembali.</p><a class="button" href="{{ route('queue.index') }}">Kembali ke pengambilan nomor</a></section>
@endsection
