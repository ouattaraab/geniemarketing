<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API
|--------------------------------------------------------------------------
|
| Endpoints techniques / intégrations futures (webhooks CinetPay, etc.).
| Les routes métier passent par routes/web.php (SSR Blade + Livewire).
|
*/

Route::prefix('v1')->group(function (): void {

    Route::get('health', fn () => response()->json([
        'status' => 'ok',
        'version' => config('app.version', '0.1.0'),
        'time' => now()->toIso8601String(),
    ]));
});
