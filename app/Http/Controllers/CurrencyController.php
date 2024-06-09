<?php

namespace App\Http\Controllers;

use App\Domain\Currency\Contracts\CurrencyRepository;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function index(CurrencyRepository $repository): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $repository->getCurrencies(),
        ]);
    }
}
