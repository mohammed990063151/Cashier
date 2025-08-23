<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapDashboardRoutes();
        // $this->mapApiRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    protected function mapDashboardRoutes(): void
    {
        $dashboardPath = base_path('routes/dashboard');

        foreach (glob($dashboardPath.'/*.php') as $routeFile) {
            Route::middleware(['web', 'auth'])
                ->namespace($this->namespace)
                ->group($routeFile);
        }
    }

    // protected function mapApiRoutes(): void
    // {
    //     Route::prefix('api')
    //         ->middleware('api')
    //         ->namespace($this->namespace)
    //         ->group(base_path('routes/api.php'));
    // }
}
