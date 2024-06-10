<?php

namespace App\Http\Controllers;

use App\Domain\Currency\Contracts\CurrencyRepository;
use Illuminate\Http\JsonResponse;

class DenominationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CurrencyRepository $repository, string $currency): JsonResponse
    {
        // dd($repository->getDenominations($currency));
        return response()->json([
            'success' => true,
            'data' => $repository->getDenominations($currency),
        ]);
    }
}
