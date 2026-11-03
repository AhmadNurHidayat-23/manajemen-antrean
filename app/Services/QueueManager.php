<?php

namespace App\Services;

use App\Models\QueueTicket;
use DomainException;
use Illuminate\Support\Facades\DB;

class QueueManager
{
    public function issue(): QueueTicket
    {
        return DB::transaction(function (): QueueTicket {
            $day = $this->lockDay();
            DB::table('queue_days')->where('service_date', $day)->increment('last_number');
            $sequence = (int) DB::table('queue_days')->where('service_date', $day)->value('last_number');

            return QueueTicket::create(['service_date' => $day, 'sequence' => $sequence, 'status' => 'waiting']);
        }, 5);
    }

    public function callNext(): QueueTicket
    {
        return DB::transaction(function (): QueueTicket {
            $day = $this->lockDay();
            if (QueueTicket::where('service_date', $day)->where('status', 'serving')->exists()) {
                throw new DomainException('Selesaikan atau lewati antrean yang sedang dilayani terlebih dahulu.');
            }
            $ticket = QueueTicket::where('service_date', $day)->where('status', 'waiting')->orderBy('sequence')->first();
            if (! $ticket) {
                throw new DomainException('Belum ada antrean yang menunggu.');
            }
            $ticket->update(['status' => 'serving', 'called_at' => now()]);

            return $ticket;
        }, 5);
    }

    public function finish(int $id): QueueTicket
    {
        return $this->changeStatus($id, 'completed', ['serving']);
    }

    public function skip(int $id): QueueTicket
    {
        return $this->changeStatus($id, 'skipped', ['waiting', 'serving']);
    }

    private function changeStatus(int $id, string $target, array $allowed): QueueTicket
    {
        return DB::transaction(function () use ($id, $target, $allowed): QueueTicket {
            $day = $this->lockDay();
            $ticket = QueueTicket::findOrFail($id);
            if ($ticket->service_date !== $day) {
                throw new DomainException('Antrean tersebut bukan antrean hari ini.');
            }
            if (! in_array($ticket->status, $allowed, true)) {
                throw new DomainException('Status antrean sudah berubah atau telah selesai diproses. Muat ulang halaman.');
            }
            $ticket->update(['status' => $target, 'finished_at' => now()]);

            return $ticket;
        }, 5);
    }

    /**
     * Acquire SQLite's write lock before reading, including on PHP 8.3 PDO.
     */
    private function lockDay(): string
    {
        $day = now()->toDateString();
        DB::statement('INSERT INTO queue_days (service_date, last_number) VALUES (?, 0) ON CONFLICT(service_date) DO UPDATE SET last_number = last_number', [$day]);
        $currentDay = now()->toDateString();
        if ($currentDay !== $day) {
            DB::table('queue_days')->insertOrIgnore(['service_date' => $currentDay, 'last_number' => 0]);
        }

        return $currentDay;
    }

    public function snapshot(): array
    {
        $now = now();
        $day = $now->toDateString();
        $row = QueueTicket::where('service_date', $day)->selectRaw("COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END), 0) AS waiting,
            COALESCE(SUM(CASE WHEN status = 'serving' THEN 1 ELSE 0 END), 0) AS serving,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed,
            COALESCE(SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END), 0) AS skipped,
            MAX(CASE WHEN status = 'serving' THEN sequence END) AS current_number,
            MAX(CASE WHEN status = 'serving' THEN id END) AS current_id")->first();
        $counts = [];
        foreach (['total', 'waiting', 'serving', 'completed', 'skipped'] as $key) {
            $counts[$key] = (int) $row->{$key};
        }
        $currentId = $row->current_id ? (int) $row->current_id : null;

        return [
            'date' => $day, 'display_date' => $now->format('d/m/Y'),
            'current' => $currentId ? QueueTicket::formatNumber((int) $row->current_number) : '—',
            'current_id' => $currentId, 'counts' => $counts, 'updated_at' => $now->toIso8601String(),
            'version' => hash('sha256', json_encode([$day, $currentId, $counts])),
        ];
    }
}
