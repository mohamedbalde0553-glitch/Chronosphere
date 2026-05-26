<?php

namespace App\Modules\Shifts\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ShiftsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('shifts')
            ->name('shifts.')
            ->group(base_path('routes/modules/shifts.php'));
    }
}
