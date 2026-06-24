<?php

use App\Http\Controllers\AccountTransactionController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

Route::get('health', HealthCheckJsonResultsController::class);

Route::post('users/{user}/transactions', [AccountTransactionController::class, 'store']);
