@extends('layouts.app')
@section('title', 'Operator — Manajemen Antrean')
@section('content')
<section class="hero operator-heading">
    <p class="eyebrow">OPERATOR / DEMO LOKAL TANPA LOGIN</p>
    <h1>Kelola Antrean Hari Ini.</h1>
    <p>Akses operator dibatasi ke localhost. Tambahkan autentikasi sebelum dipublikasikan.</p>
</section>
<section class="operator-controls">
    <div>
        <p class="step">SEDANG DILAYANI</p>
        <p class="operator-number">{{ $snapshot['current'] }}</p>
        <p class="hint">{{ $current ? 'Panggilan pukul '.$current->called_at->format('H:i').' WIB' : 'Belum ada antrean aktif.' }}</p>
    </div>
    <div class="operator-actions">
        @if ($current)
            <form action="{{ route('operator.complete', $current) }}" method="post">
                @csrf
                <button class="button" type="submit">Tandai selesai</button>
            </form>
            <form action="{{ route('operator.skip', $current) }}" method="post">
                @csrf
                <button class="button outline" type="submit">Lewati antrean</button>
            </form>
        @else
            <form action="{{ route('operator.next') }}" method="post">
                @csrf
                <button class="button" type="submit" @disabled($snapshot['counts']['waiting'] === 0)>Panggil antrean berikutnya ↗</button>
            </form>
        @endif
        <p class="hint">{{ $current ? 'Selesaikan atau lewati sebelum memanggil nomor berikutnya.' : 'Nomor dipanggil menurut urutan pengambilan.' }}</p>
    </div>
</section>
<dl class="status-summary">
    @foreach (\App\Models\QueueTicket::STATUSES as $key => $label)
        <div><dt>{{ $label }}</dt><dd>{{ $snapshot['counts'][$key] }}</dd></div>
    @endforeach
</dl>
<div class="listing-heading">
    <h2>Antrean hari ini</h2>
    <span>{{ $snapshot['display_date'] }} · {{ $snapshot['counts']['total'] }} nomor</span>
</div>
@if ($tickets->isEmpty())
    <section class="empty-state">
        <h2>Belum ada nomor yang diambil.</h2>
        <p>Antrean baru akan muncul setelah pengunjung mengambil nomor.</p>
        <a href="{{ route('queue.index') }}" class="button">Buka pengambilan nomor</a>
    </section>
@else
    <div class="queue-table-wrap">
        <table class="queue-table">
            <thead>
                <tr>
                    <th scope="col">NOMOR</th>
                    <th scope="col">DIAMBIL</th>
                    <th scope="col">STATUS</th>
                    <th scope="col">TINDAKAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="table-number">{{ $ticket->label() }}</td>
                        <td>{{ $ticket->created_at->format('H:i:s') }}</td>
                        <td><span class="status-tag" data-status="{{ $ticket->status }}">{{ \App\Models\QueueTicket::STATUSES[$ticket->status] }}</span></td>
                        <td>
                            @if ($ticket->status === 'waiting')
                                <form action="{{ route('operator.skip', $ticket) }}" method="post">
                                    @csrf
                                    <button type="submit" class="text-button" aria-label="Lewati {{ $ticket->label() }}">Lewati ↗</button>
                                </form>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $tickets])
@endif
<p class="update-note" data-connection>Pembaruan otomatis setiap 5 detik.</p>
@endsection
