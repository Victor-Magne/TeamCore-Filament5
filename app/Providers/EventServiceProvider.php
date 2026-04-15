<?php

namespace App\Providers;

use App\Listeners\AuthenticationActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            AuthenticationActivityLogger::class.'@onLogin',
        ],
        Logout::class => [
            AuthenticationActivityLogger::class.'@onLogout',
        ],
        Failed::class => [
            AuthenticationActivityLogger::class.'@onFailed',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
