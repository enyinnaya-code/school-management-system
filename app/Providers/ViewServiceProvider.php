<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SchoolSetting;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $schoolSettings = SchoolSetting::first();
            $view->with('schoolSettings', $schoolSettings);
        });
    }

    public function register()
    {
        //
    }
}