<?php

namespace App\Filament\Pages\Auth;

use Filament\Notifications\Notification;

class RequestPasswordReset extends \Filament\Pages\Auth\PasswordReset\RequestPasswordReset
{
    protected function getFailureNotification(string $status): ?Notification
    {
        return $this->genericNotification();
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return $this->genericNotification();
    }

    private function genericNotification(): Notification
    {
        return Notification::make()
            ->title('Check your email')
            ->body('If an active administrator account matches that email, a secure reset link has been sent.')
            ->success();
    }
}
