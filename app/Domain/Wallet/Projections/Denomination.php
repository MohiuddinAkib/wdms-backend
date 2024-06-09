<?php

namespace App\Domain\Wallet\Projections;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EventSourcing\Projections\Projection;

class Denomination extends Projection
{
    use HasFactory;
}
