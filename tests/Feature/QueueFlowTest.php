<?php

namespace Tests\Feature;

use App\Models\QueueTicket;
use App\Services\QueueManager;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_and_empty_snapshot_are_available(): void
    {
        foreach (['/', '/operator', '/tampilan'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->getJson('/antrean/status')->assertOk()->assertJsonPath('current', '—')
            ->assertJsonPath('counts.total', 0)->assertHeader('Cache-Control', 'no-store, private')
            ->assertCookieMissing(config('session.cookie'));
    }

    public function test_tickets_are_sequential_and_kept_in_the_session(): void
    {
        $this->post('/antrean')->assertRedirect('/')->assertSessionHas('ticket_id', 1);
        $this->post('/antrean')->assertRedirect('/')->assertSessionHas('ticket_id', 2);
        $this->get('/')->assertSee('A002');
        $this->assertDatabaseCount('queue_tickets', 2);
        $this->assertSame(['A001', 'A002'], QueueTicket::all()->map->label()->all());
        $this->assertSame('A1000', QueueTicket::formatNumber(1000));
    }

    public function test_client_cannot_choose_number_date_or_status(): void
    {
        $this->post('/antrean', ['sequence' => 88, 'service_date' => '2020-01-01', 'status' => 'serving'])
            ->assertSessionHasErrors(['sequence', 'service_date', 'status']);
        $this->assertDatabaseCount('queue_tickets', 0);
    }

    public function test_operator_follows_fifo_and_finishes_or_skips_tickets(): void
    {
        $manager = app(QueueManager::class);
        $first = $manager->issue();
        $second = $manager->issue();
        $third = $manager->issue();
        $this->post('/operator/berikutnya')->assertRedirect('/operator');
        $this->assertSame('serving', $first->fresh()->status);
        $this->post('/operator/berikutnya')->assertSessionHasErrors('queue');
        $this->post("/operator/{$second->id}/selesai")->assertSessionHasErrors('queue');
        $this->post("/operator/{$first->id}/selesai")->assertRedirect('/operator');
        $this->assertNotNull($first->fresh()->finished_at);
        $this->post('/operator/berikutnya')->assertRedirect('/operator');
        $this->assertSame('serving', $second->fresh()->status);
        $this->post("/operator/{$second->id}/lewati")->assertRedirect('/operator');
        $this->post("/operator/{$third->id}/lewati")->assertRedirect('/operator');
        $this->post('/operator/berikutnya')->assertSessionHasErrors('queue');
        $this->getJson('/antrean/status')->assertJsonPath('counts.completed', 1)
            ->assertJsonPath('counts.skipped', 2)->assertJsonPath('counts.waiting', 0)
            ->assertJsonPath('current', '—');
    }

    public function test_terminal_tickets_cannot_be_changed_again(): void
    {
        $ticket = app(QueueManager::class)->issue();
        $this->post("/operator/{$ticket->id}/lewati")->assertRedirect('/operator');
        $this->post("/operator/{$ticket->id}/lewati")->assertSessionHasErrors('queue');
        $this->post("/operator/{$ticket->id}/selesai")->assertSessionHasErrors('queue');
        $this->assertSame('skipped', $ticket->fresh()->status);
        $this->post('/operator/9999/lewati')->assertNotFound();
    }

    public function test_numbers_restart_at_jakarta_midnight_and_old_tickets_stay_untouched(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 23:59:59', 'Asia/Jakarta'));
        $manager = app(QueueManager::class);
        $yesterday = $manager->issue();
        $manager->callNext();
        $manager->issue();
        $this->withSession(['ticket_id' => $yesterday->id]);
        $this->travelTo(Carbon::parse('2026-11-04 00:00:00', 'Asia/Jakarta'));
        $this->getJson('/antrean/status')->assertJsonPath('date', '2026-11-04')
            ->assertJsonPath('counts.total', 0)->assertJsonPath('current', '—');
        $this->get('/')->assertSessionMissing('ticket_id');
        $today = $manager->issue();
        $this->assertSame('A001', $today->label());
        $this->assertSame('2026-11-04', $today->service_date);
        $this->post("/operator/{$yesterday->id}/selesai")->assertSessionHasErrors('queue');
        $this->post('/operator/berikutnya')->assertRedirect('/operator');
        $this->assertSame('serving', $today->fresh()->status);
        $this->assertSame('serving', $yesterday->fresh()->status);
        $this->assertDatabaseCount('queue_days', 2);
        $this->travelBack();
    }

    public function test_snapshot_and_paginated_list_reflect_today_only(): void
    {
        $this->freezeSecond();
        QueueTicket::factory()->create(['service_date' => now()->subDay()->toDateString()]);
        foreach (range(1, 21) as $number) {
            app(QueueManager::class)->issue();
        }
        $manager = app(QueueManager::class);
        $manager->callNext();
        $this->getJson('/antrean/status')->assertJsonPath('current', 'A001')
            ->assertJsonPath('counts.waiting', 20)->assertJsonPath('counts.serving', 1)
            ->assertJsonPath('counts.total', 21);
        $this->get('/operator')->assertSee('A020')->assertDontSee('A021');
        $this->get('/operator?page=2')->assertSee('A021')->assertDontSee('A020');
    }

    public function test_database_rejects_duplicate_numbers_for_the_same_day(): void
    {
        $ticket = app(QueueManager::class)->issue();
        $this->expectException(QueryException::class);
        QueueTicket::factory()->create(['service_date' => $ticket->service_date, 'sequence' => $ticket->sequence]);
    }

    public function test_database_rejects_two_current_calls_on_one_day(): void
    {
        $manager = app(QueueManager::class);
        $manager->issue();
        $second = $manager->issue();
        $manager->callNext();
        $this->expectException(QueryException::class);
        DB::table('queue_tickets')->where('id', $second->id)->update(['status' => 'serving']);
    }
}
