<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load settings after database is available
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            // Set app configuration from database settings
            Config::set('app.name', Setting::get('app_name', config('app.name')));
            Config::set('app.timezone', Setting::get('timezone', config('app.timezone')));

            // Make settings available to all views
            View::composer('*', function ($view) {
                $view->with('clinicSettings', [
                    'name' => Setting::get('clinic_name'),
                    'address' => Setting::get('clinic_address'),
                    'phone' => Setting::get('clinic_phone'),
                    'email' => Setting::get('clinic_email'),
                    'website' => Setting::get('clinic_website'),
                    'logo' => Setting::get('clinic_logo_path'),
                ]);
            });
        } catch (\Exception $e) {
            // Handle case when database is not yet migrated
            // Log error but don't break the application
        }
    }
}
