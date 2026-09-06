<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\AdminActivityLogger;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        $email = (string) ($this->data['email'] ?? '');

        try {
            $response = parent::authenticate();
        } catch (ValidationException $exception) {
            $target = User::withTrashed()->where('email', $email)->first();
            app(AdminActivityLogger::class)->log(
                'login_failed',
                'An administrator login attempt failed.',
                $target,
                metadata: ['email_provided' => $email !== ''],
            );

            throw $exception;
        }

        $user = Filament::auth()->user();
        if ($user instanceof User) {
            $user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ])->saveQuietly();
            session()->put('admin_session_version', $user->session_version);

            app(AdminActivityLogger::class)->log(
                'login_success',
                "{$user->name} signed in to the admin panel.",
                $user,
                $user,
            );
        }

        return $response;
    }
}
