<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

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
        // مشاركة بيانات الإعدادات مع كل الصفحات
        View::composer('*', function ($view) {
            $view->with('setting', Setting::first());
        });
    }
}