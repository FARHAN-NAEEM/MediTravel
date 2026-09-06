<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\User;
use App\Services\AdminManager;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?string $navigationLabel = 'Admin Management';

    protected static ?string $modelLabel = 'Admin';

    protected static ?string $pluralModelLabel = 'Admins';

    protected static ?int $navigationSort = 1;

    protected static string | array $routeMiddleware = [EnsureOwner::class];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Admin profile')
                ->description('Role and ownership are protected by the system and cannot be submitted through this form.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->autocomplete('name'),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->autocomplete('email'),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(32)
                        ->rule('regex:/^\+?[0-9][0-9\s().-]{6,30}$/')
                        ->autocomplete('tel'),
                    Forms\Components\Placeholder::make('role_label')
                        ->label('Role')
                        ->content(fn (?User $record): string => $record?->role_label ?? 'User Admin'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Admin')
                    ->description(fn (User $record): string => $record->email)
                    ->icon(fn (User $record): string => $record->isOwner() ? 'heroicon-m-shield-check' : 'heroicon-m-user')
                    ->color(fn (User $record): string => $record->isOwner() ? 'warning' : 'primary')
                    ->searchable(['name', 'email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('Not provided')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role_label')
                    ->label('Role')
                    ->badge()
                    ->color(fn (User $record): string => $record->isOwner() ? 'warning' : 'info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Never')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Account status'),
                Tables\Filters\SelectFilter::make('role')
                    ->relationship('roles', 'name')
                    ->options([
                        AdminAccess::OWNER_ROLE => 'Owner / Super Admin',
                        AdminAccess::USER_ADMIN_ROLE => 'User Admin',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Profile'),
                Tables\Actions\Action::make('sendPasswordReset')
                    ->label('Send reset link')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->isOwner() && $record->is_active)
                    ->action(function (User $record): void {
                        $status = app(AdminManager::class)->sendPasswordResetLink(auth()->user(), $record);
                        Notification::make()
                            ->title($status === Password::RESET_LINK_SENT ? 'Reset link sent' : __($status))
                            ->{$status === Password::RESET_LINK_SENT ? 'success' : 'danger'}()
                            ->send();
                    }),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->isOwner() && ! $record->is_active)
                    ->action(fn (User $record) => app(AdminManager::class)->activate(auth()->user(), $record)),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->form([self::currentPasswordField()])
                    ->modalHeading(fn (User $record): string => "Deactivate {$record->name}?")
                    ->modalDescription('The admin will be signed out and will not be able to log in until reactivated.')
                    ->visible(fn (User $record): bool => ! $record->isOwner() && $record->is_active)
                    ->action(fn (User $record) => app(AdminManager::class)->deactivate(auth()->user(), $record)),
                Tables\Actions\Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->form([self::currentPasswordField()])
                    ->modalHeading(fn (User $record): string => "Remove {$record->name}?")
                    ->modalDescription('This account will be archived. Historical records and audit logs will remain available.')
                    ->modalSubmitActionLabel('Remove admin')
                    ->visible(fn (User $record): bool => ! $record->isOwner())
                    ->action(fn (User $record) => app(AdminManager::class)->remove(auth()->user(), $record)),
            ])
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('No user admins yet')
            ->emptyStateDescription('Create an admin and send a secure account setup link.')
            ->defaultSort('created_at', 'desc');
    }

    private static function currentPasswordField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('current_password')
            ->label('Confirm your current password')
            ->password()
            ->revealable()
            ->currentPassword()
            ->required();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::ownerCanManage();
    }

    public static function canViewAny(): bool
    {
        return static::ownerCanManage();
    }

    public static function canCreate(): bool
    {
        return static::ownerCanManage();
    }

    public static function canEdit($record): bool
    {
        return static::ownerCanManage();
    }

    public static function canDelete($record): bool
    {
        return static::ownerCanManage() && $record instanceof User && ! $record->isOwner();
    }

    private static function ownerCanManage(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->isOwner()
            && $user->can(AdminAccess::MANAGE_ADMINS_PERMISSION);
    }

    public static function getRelations(): array
    {
        return [
            AdminResource\RelationManagers\ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
