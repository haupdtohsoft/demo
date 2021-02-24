<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Events\EventHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->events->subscribe(new EventHandler());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
