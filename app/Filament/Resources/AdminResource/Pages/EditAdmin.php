<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\User;
use App\Services\AdminManager;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        return app(AdminManager::class)->updateAdmin(auth()->user(), $record, $data);
    }

    protected function getHeaderActions(): array
    {
        /** @var User $record */
        $record = $this->record;

        if ($record->isOwner()) {
            return [];
        }

        return [
            Actions\Action::make('sendPasswordReset')
                ->label('Send reset link')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => $record->is_active)
                ->action(function () use ($record): void {
                    $status = app(AdminManager::class)->sendPasswordResetLink(auth()->user(), $record);
                    Notification::make()
                        ->title($status === Password::RESET_LINK_SENT ? 'Reset link sent' : __($status))
                        ->{$status === Password::RESET_LINK_SENT ? 'success' : 'danger'}()
                        ->send();
                }),
            Actions\Action::make('activate')
                ->label('Activate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! $record->is_active)
                ->action(fn () => app(AdminManager::class)->activate(auth()->user(), $record)),
            Actions\Action::make('deactivate')
                ->label('Deactivate')
                ->icon('heroicon-o-no-symbol')
                ->color('warning')
                ->form([$this->currentPasswordField()])
                ->modalHeading("Deactivate {$record->name}?")
                ->modalDescription('The admin will be signed out and blocked until reactivated.')
                ->visible(fn (): bool => $record->is_active)
                ->action(fn () => app(AdminManager::class)->deactivate(auth()->user(), $record)),
            Actions\Action::make('remove')
                ->label('Remove')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->form([$this->currentPasswordField()])
                ->modalHeading("Remove {$record->name}?")
                ->modalDescription('The account will be archived while its audit history remains intact.')
                ->modalSubmitActionLabel('Remove admin')
                ->action(function () use ($record): void {
                    app(AdminManager::class)->remove(auth()->user(), $record);
                    $this->redirect(AdminResource::getUrl('index'));
                }),
        ];
    }

    private function currentPasswordField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('current_password')
            ->label('Confirm your current password')
            ->password()
            ->revealable()
            ->currentPassword()
            ->required();
    }
}
