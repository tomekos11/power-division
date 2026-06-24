<?php

namespace App\Exceptions;

use App\Models\User;

final class AccountNotFoundException extends BusinessException
{
    public function __construct(public readonly User $user)
    {
        parent::__construct("No active account found for user [{$user->id}].");
    }
}
