<?php

namespace App\Providers;

use App\Http\Middleware\EnsureRole;
use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('role', EnsureRole::class);

        Gate::policy(Order::class, OrderPolicy::class);
    }
}
