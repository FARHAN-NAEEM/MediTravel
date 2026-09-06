<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Auth\Events\PasswordResetLinkSent;

class RecordPasswordResetLinkSent
{
    public function __construct(private readonly AdminActivityLogger $activityLogger)
    {
    }

    public function handle(PasswordResetLinkSent $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->activityLogger->log(
            'password_reset_requested',
            'A password reset link was requested from the admin login screen.',
            $event->user,
        );
    }
}
