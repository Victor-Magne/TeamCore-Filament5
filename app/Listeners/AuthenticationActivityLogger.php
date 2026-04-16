<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;

class AuthenticationActivityLogger
{
    /**
     * Log user login
     */
    public function onLogin(Login $event): void
    {
        if ($event->user instanceof Model) {
            /** @noinspection PhpUndefinedMethodInspection */
            activity()
                ->useLog('authentication')
                ->performedOn($event->user)
                ->causedBy($event->user)
                ->log('User logged in');
        }
    }

    /**
     * Log user logout
     */
    public function onLogout(Logout $event): void
    {
        if ($event->user instanceof Model) {
            /** @noinspection PhpUndefinedMethodInspection */
            activity()
                ->useLog('authentication')
                ->causedBy($event->user)
                ->log('User logged out');
        }
    }

    /**
     * Log failed authentication attempts
     */
    public function onFailed(Failed $event): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        activity()
            ->useLog('authentication')
            ->withProperties(['email' => $event->credentials['email'] ?? null])
            ->log('Failed login attempt');
    }
}
