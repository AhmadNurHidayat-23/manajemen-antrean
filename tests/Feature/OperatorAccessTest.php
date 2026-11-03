<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_remote_clients_cannot_use_operator_pages_or_actions(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.4']);
        $this->get('/operator')->assertForbidden();
        $this->post('/operator/berikutnya')->assertForbidden();
        $this->get('/')->assertOk();
    }

    public function test_non_local_hosts_cannot_use_operator(): void
    {
        $this->get('http://example.com/operator')->assertForbidden();
    }

    public function test_operator_is_disabled_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/operator')->assertForbidden();
        $this->get('/')->assertOk();
    }

    public function test_csrf_is_required_for_taking_and_changing_tickets(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->post('/antrean')->assertStatus(419);
        $this->post('/operator/berikutnya')->assertStatus(419);
        $this->assertDatabaseCount('queue_tickets', 0);
    }
}
