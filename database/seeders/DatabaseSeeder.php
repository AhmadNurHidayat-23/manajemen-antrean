<?php

namespace Database\Seeders;

use App\Models\QueueTicket;
use App\Services\QueueManager;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (QueueTicket::where('service_date', now()->toDateString())->exists()) {
            return;
        }
        $queue = app(QueueManager::class);
        for ($number = 0; $number < 6; $number++) {
            $queue->issue();
        }
        $queue->finish($queue->callNext()->id);
        $queue->skip($queue->callNext()->id);
        $queue->callNext();
    }
}
