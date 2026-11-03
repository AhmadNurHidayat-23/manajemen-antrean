<?php

namespace Tests\Feature;

use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class QueueConcurrencyTest extends TestCase
{
    public function test_parallel_clients_receive_unique_numbers_and_only_one_call_becomes_active(): void
    {
        $database = tempnam(sys_get_temp_dir(), 'queue-test-');
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database,
            'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $processes = [];
        try {
            $migration = new Process([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], base_path(), $environment);
            $migration->mustRun();
            $bootstrap = 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; '
                .'$app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); '
                .'\\Illuminate\\Support\\Carbon::setTestNow("2026-11-03 10:00:00"); '
                .'$queue = $app->make(\\App\\Services\\QueueManager::class); ';
            $issue = $bootstrap.'$numbers = []; for ($i=0; $i<4; $i++) {$numbers[] = $queue->issue()->sequence;} echo json_encode($numbers);';
            foreach (range(1, 6) as $worker) {
                $process = new Process([PHP_BINARY, '-r', $issue], base_path(), $environment, timeout: 30);
                $process->start();
                $processes[] = $process;
            }
            $numbers = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $numbers = array_merge($numbers, json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR));
            }
            sort($numbers);
            $this->assertSame(range(1, 24), $numbers);
            $connection = new PDO('sqlite:'.$database);
            $this->assertSame(24, (int) $connection->query('SELECT COUNT(*) FROM queue_tickets')->fetchColumn());
            $this->assertSame(24, (int) $connection->query('SELECT last_number FROM queue_days')->fetchColumn());
            $processes = [];
            $call = $bootstrap.'try {$queue->callNext(); echo "called";} catch (\\DomainException $e) {echo "busy";}';
            foreach (range(1, 4) as $worker) {
                $process = new Process([PHP_BINARY, '-r', $call], base_path(), $environment, timeout: 30);
                $process->start();
                $processes[] = $process;
            }
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $results[] = $process->getOutput();
            }
            $this->assertSame(1, count(array_filter($results, fn ($result) => $result === 'called')));
            $this->assertSame(3, count(array_filter($results, fn ($result) => $result === 'busy')));
            $this->assertSame(1, (int) $connection->query("SELECT sequence FROM queue_tickets WHERE status = 'serving'")->fetchColumn());
            $this->assertSame(23, (int) $connection->query("SELECT COUNT(*) FROM queue_tickets WHERE status = 'waiting'")->fetchColumn());
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ([$database, $database.'-wal', $database.'-shm', $database.'-journal'] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
