<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['service_date', 'sequence', 'status', 'called_at', 'finished_at'])]
class QueueTicket extends Model
{
    use HasFactory;

    public const STATUSES = ['waiting' => 'Menunggu', 'serving' => 'Dilayani', 'completed' => 'Selesai', 'skipped' => 'Dilewati'];

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'called_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function label(): string
    {
        return self::formatNumber($this->sequence);
    }

    public static function formatNumber(int $sequence): string
    {
        return 'A'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
