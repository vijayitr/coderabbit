<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\TimeEntry;
use App\Services\GoToConnectService;
use Illuminate\Support\Facades\URL;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

        $this->app->singleton(GoToConnectService::class, function ($app) {
            return new GoToConnectService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
            // \URL::forceScheme('https');
        }
            
        View::composer('*', function ($view) {
            $authUser = Auth::guard('web')->user();
            
            $isIdle = $authUser ? (new TimeEntry)->isIdle() : false;
            $isBrake = $authUser ? (new TimeEntry)->isBreak() : false;
            
            $view->with([
                'authUser' => $authUser,
                'isIdle' => $isIdle,
                'isBrake' => $isBrake,
            ]);
        });
    }
}
