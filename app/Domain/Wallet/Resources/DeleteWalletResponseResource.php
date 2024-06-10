<?php

namespace App\Domain\Wallet\Resources;

use Illuminate\Http\Request;
use Spatie\LaravelData\Resource;
use Symfony\Component\HttpFoundation\Response;

class DeleteWalletResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message,
    ) {}

    protected function calculateResponseStatus(Request $request): int
    {
      return $this->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
    }
}
