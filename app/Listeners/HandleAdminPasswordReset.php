<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Services\AdminActivityLogger;
use Illuminate\Auth\Events\PasswordReset;

class HandleAdminPasswordReset
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function handle(PasswordReset $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $event->user->forceFill([
            'password_changed_at' => now(),
            'must_change_password' => false,
            'session_version' => $event->user->session_version + 1,
        ])->saveQuietly();

        $this->activityLogger->log(
            'password_reset_completed',
            "Password reset was completed for {$event->user->name}.",
            $event->user,
            $event->user,
        );

        $event->user->notify(new PasswordChangedNotification);
    }
}
