<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AgentPasswordReset extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->subject('Asian Health Connect - Agent password')
            ->line('Set or reset your Asian Health Connect agent portal password.')
            ->action('Set password', rtrim(config('app.url'), '/').route('agent.password.reset', ['token' => $this->token, 'email' => $notifiable->email], false))
            ->line('This one-time link expires in 60 minutes. If you did not request it, ignore this email.');
    }
}
