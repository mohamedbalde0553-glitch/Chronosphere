<?php

namespace App\Modules\Timetable\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TimetableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('timetable')
            ->name('timetable.')
            ->group(base_path('routes/modules/timetable.php'));
    }
}
