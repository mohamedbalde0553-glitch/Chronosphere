<?php

namespace App\Modules\Booking\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'verified', 'module.access:booking'])
            ->prefix('booking')
            ->name('booking.')
            ->group(base_path('routes/modules/booking.php'));
    }
}
