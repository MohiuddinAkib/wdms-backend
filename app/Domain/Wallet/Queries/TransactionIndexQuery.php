<?php

namespace App\Domain\Wallet\Queries;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        /** @var User */
        $user = $request->user();
        $query = $user->transactions()->getQuery();

        parent::__construct($query, $request);

        $this->allowedFilters(
            'type',
            'wallet_currency',
            AllowedFilter::exact('wallet_id'),
            AllowedFilter::scope('happened_at_between')
        );
    }
}
