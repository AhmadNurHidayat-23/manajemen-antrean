<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Services\QueueManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request, QueueManager $queue): View
    {
        $snapshot = $queue->snapshot();
        $mine = QueueTicket::whereKey($request->session()->get('ticket_id'))
            ->where('service_date', $snapshot['date'])->first();
        if (! $mine) {
            $request->session()->forget('ticket_id');
        }

        return view('queue.index', compact('snapshot', 'mine'));
    }

    public function take(Request $request, QueueManager $queue): RedirectResponse
    {
        $request->validate(['sequence' => ['prohibited'], 'service_date' => ['prohibited'], 'status' => ['prohibited']], [
            'prohibited' => 'Nomor, tanggal, dan status antrean ditentukan otomatis oleh sistem.',
        ]);
        $ticket = $queue->issue();
        $request->session()->put('ticket_id', $ticket->id);

        return to_route('queue.index')->with('success', 'Nomor '.$ticket->label().' berhasil diambil. Simpan nomor Anda dan tunggu panggilan.');
    }

    public function display(QueueManager $queue): View
    {
        return view('queue.display', ['snapshot' => $queue->snapshot()]);
    }

    public function status(QueueManager $queue): JsonResponse
    {
        return response()->json($queue->snapshot())->header('Cache-Control', 'no-store, private');
    }
}
