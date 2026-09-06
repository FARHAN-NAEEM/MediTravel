<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Asian Health Connect admin password was changed')
            ->greeting("Hello {$notifiable->name},")
            ->line('The password for your Asian Health Connect administrator account was changed successfully.')
            ->line('All older administrator sessions have been invalidated for your security.')
            ->line('If you did not make this change, contact the account owner immediately.');
    }
}
