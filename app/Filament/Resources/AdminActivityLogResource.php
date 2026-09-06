<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminActivityLogResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\AdminAccess;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdminActivityLogResource extends Resource
{
    protected static ?string $model = AdminActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?int $navigationSort = 2;

    protected static string | array $routeMiddleware = [EnsureOwner::class];

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Time')->dateTime('M j, Y g:i:s A')->sortable(),
                Tables\Columns\TextColumn::make('action')->badge()->searchable(),
                Tables\Columns\TextColumn::make('actor.name')->label('Performed by')->placeholder('System')->searchable(),
                Tables\Columns\TextColumn::make('targetUser.name')->label('Target admin')->placeholder('None')->searchable(),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP address')->placeholder('Unavailable'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(fn (): array => AdminActivityLog::query()->distinct()->orderBy('action')->pluck('action', 'action')->all()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->isOwner()
            && $user->can(AdminAccess::VIEW_ACTIVITY_PERMISSION);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminActivityLogs::route('/'),
        ];
    }
}
