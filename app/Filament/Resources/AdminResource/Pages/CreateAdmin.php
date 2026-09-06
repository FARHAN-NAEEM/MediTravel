<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\User;
use App\Services\AdminManager;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Throwable;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AdminManager::class)->createUserAdmin(auth()->user(), $data);
    }

    protected function afterCreate(): void
    {
        /** @var User $admin */
        $admin = $this->record;

        try {
            $status = app(AdminManager::class)->sendPasswordResetLink(auth()->user(), $admin);

            if ($status !== Password::RESET_LINK_SENT) {
                Notification::make()->title(__($status))->danger()->send();
            }
        } catch (Throwable) {
            Notification::make()
                ->title('Admin created, but email could not be sent')
                ->body('Configure production mail, then use “Send reset link” from the admin list.')
                ->warning()
                ->send();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User admin created';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
