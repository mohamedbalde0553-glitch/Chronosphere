<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Shifts\Models\Employee;
use App\Policies\EmployeePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
