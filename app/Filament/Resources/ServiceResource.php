<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\ServiceResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\Service;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Site Content';

    protected static string|array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('name')->label('Name (English)')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_bn')->label('Name (Bangla)')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('short_desc')->label('Summary (English)')->required()->rows(3),
            Forms\Components\Textarea::make('short_desc_bn')->label('Summary (Bangla)')->required()->rows(3),
            Forms\Components\Textarea::make('body')->label('Body (English)')->required()->rows(6),
            Forms\Components\Textarea::make('body_bn')->label('Body (Bangla)')->required()->rows(6),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('English')->searchable()->limit(70),
            Tables\Columns\TextColumn::make('name_bn')->label('Bangla')->searchable()->limit(70),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}
