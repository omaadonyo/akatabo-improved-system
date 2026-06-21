<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class ActivitySubscriber
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        session(['active_business_id' => $user->business_id]);

        ActivityLog::log('login', __('Logged in from :ip', ['ip' => request()->ip()]), [
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) return;

        ActivityLog::log('logout', __('Logged out'), [
            'ip' => request()->ip(),
        ]);
    }

    public function subscribe(): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
