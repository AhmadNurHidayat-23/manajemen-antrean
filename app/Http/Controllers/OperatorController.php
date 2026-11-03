<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Services\QueueManager;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class OperatorController extends Controller
{
    public function index(QueueManager $queue): View
    {
        $snapshot = $queue->snapshot();
        $tickets = QueueTicket::where('service_date', $snapshot['date'])->orderBy('sequence')->paginate(20);
        $current = QueueTicket::whereKey($snapshot['current_id'])->first();
        $operatorVersion = $snapshot['version'];

        return view('operator.index', compact('snapshot', 'tickets', 'current', 'operatorVersion'));
    }

    public function next(QueueManager $queue): RedirectResponse
    {
        return $this->action(fn () => 'Antrean '.$queue->callNext()->label().' dipanggil.');
    }

    public function complete(QueueTicket $ticket, QueueManager $queue): RedirectResponse
    {
        return $this->action(fn () => 'Antrean '.$queue->finish($ticket->id)->label().' selesai dilayani.');
    }

    public function skip(QueueTicket $ticket, QueueManager $queue): RedirectResponse
    {
        return $this->action(fn () => 'Antrean '.$queue->skip($ticket->id)->label().' dilewati.');
    }

    private function action(callable $callback): RedirectResponse
    {
        try {
            $message = $callback();
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['queue' => $error->getMessage()]);
        }

        return to_route('operator.index')->with('success', $message);
    }
}
