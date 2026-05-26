<?php

namespace App\Modules\Project\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('project')
            ->name('project.')
            ->group(base_path('routes/modules/project.php'));
    }
}
