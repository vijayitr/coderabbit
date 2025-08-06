<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogLoginTime;
use App\Listeners\LogLogoutTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Add login event listener
        Login::class => [
            LogLoginTime::class,
        ],
        // Add logout event listener
        Logout::class => [
            LogLogoutTime::class,
        ],
    ];



    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Register the Microsoft provider with Socialite
        $this->app->make('events')->listen(
            SocialiteWasCalled::class,
            MicrosoftExtendSocialite::class
        );
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
