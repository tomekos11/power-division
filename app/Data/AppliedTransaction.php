<?php

namespace App\Data;

use Carbon\CarbonInterface;

final readonly class AppliedTransaction
{
    public function __construct(
        public string $balance,
        public CarbonInterface $createdAt,
    ) {}
}
