<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityController;

Route::middleware('api')
    ->prefix('v1')
    ->group(function (): void {
        Route::post('/activities', [ActivityController::class, 'store']);
        Route::get('/activities/{custId}/{mobile}', [ActivityController::class, 'show'])
            ->whereNumber('custId');
    });

