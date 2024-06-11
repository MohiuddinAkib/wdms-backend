<?php

namespace App\Domain\Wallet\Projections;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EventSourcing\Projections\Projection;

class Transaction extends Projection
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'happened_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'uuid');
    }

    public function denomination(): BelongsTo
    {
        return $this->belongsTo(Denomination::class, 'denomination_id', 'uuid');
    }

    public function scopeHappenedAtBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query
            ->whereDate('happened_at', '>=', Carbon::parse($startDate))
            ->whereDate('happened_at', '<=', Carbon::parse($endDate));
    }
}
