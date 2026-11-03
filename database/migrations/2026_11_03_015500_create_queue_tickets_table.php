<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_days', function (Blueprint $table): void {
            $table->date('service_date')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });
        Schema::create('queue_tickets', function (Blueprint $table): void {
            $table->id();
            $table->date('service_date');
            $table->unsignedInteger('sequence');
            $table->enum('status', ['waiting', 'serving', 'completed', 'skipped'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->unique(['service_date', 'sequence']);
            $table->index(['service_date', 'status', 'sequence']);
        });
        DB::statement("CREATE UNIQUE INDEX one_serving_per_day ON queue_tickets (service_date) WHERE status = 'serving'");
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
        Schema::dropIfExists('queue_days');
    }
};
