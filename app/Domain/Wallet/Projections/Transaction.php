<?php

namespace App\Domain\Wallet\Projections;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EventSourcing\Projections\Projection;

class Transaction extends Projection
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'happened_at' => 'datetime'
    ];
}
