<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\CollectionScheduleService;
use App\Services\StockAlertService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
      public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('setting', cache()->remember('app.setting', 3600, fn () => Setting::first()));
        });

        View::composer('layouts.dashboard.app', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $schedule = app(CollectionScheduleService::class);
            $stockAlerts = app(StockAlertService::class);

            $view->with([
                'collectionAlerts' => $schedule->dashboardAlerts(15),
                'collectionAlertsCount' => $schedule->dashboardAlertsCount(),
                'collectionDueToday' => $schedule->dueTodayAlerts(),
                'stockAlerts' => $stockAlerts->alerts(15),
                'stockAlertsCount' => $stockAlerts->alertsCount(),
            ]);
        });
    }
}