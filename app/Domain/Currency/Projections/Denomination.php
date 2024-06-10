<?php

namespace App\Domain\Currency\Projections;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EventSourcing\Projections\Projection;

class Denomination extends Projection
{
    use HasFactory;

    protected $guarded = [];
}
