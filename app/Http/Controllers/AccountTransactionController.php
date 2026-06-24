<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Services\AccountTransactionService;
use Illuminate\Http\JsonResponse;

final class AccountTransactionController extends Controller
{
    public function __construct(
        private readonly AccountTransactionService $transactions,
    ) {}

    public function store(StoreTransactionRequest $request, int $userId): JsonResponse
    {
        $state = $this->transactions->process($userId, $request->validated('amount'));

        return response()->json($state->toArray());
    }
}
