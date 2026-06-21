<?php

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * API response DTO for account state after a transaction.
 */
class AccountStateData extends Data
{
    public function __construct(
        public int $user_id,
        public string $balance,
        #[MapOutputName('last_transaction_at')]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s\Z')]
        public CarbonInterface $lastTransactionAt,
    ) {}
}
