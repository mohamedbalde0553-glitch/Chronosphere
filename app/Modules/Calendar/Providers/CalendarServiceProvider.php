<?php

namespace App\Modules\Calendar\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('calendar')
            ->name('calendar.')
            ->group(base_path('routes/modules/calendar.php'));
    }
}
