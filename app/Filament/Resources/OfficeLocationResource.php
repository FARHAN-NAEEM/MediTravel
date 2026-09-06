<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\OfficeLocationResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\OfficeLocation;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeLocationResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = OfficeLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Office Locations';

    protected static ?string $modelLabel = 'Office location';

    protected static ?int $navigationSort = 2;

    protected static string | array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Office details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('district')->label('District / City')->required()->maxLength(255),
                    Forms\Components\Textarea::make('address')->required()->rows(3)->columnSpanFull(),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->rule('regex:/^\+?[0-9][0-9\s().-]{6,30}$/')
                        ->maxLength(40),
                    Forms\Components\TextInput::make('map_url')
                        ->label('Google Maps link')
                        ->url()
                        ->maxLength(2048),
                    Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->default(0)->required(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('district')->label('District / City')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('address')->limit(55)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('phone')->placeholder('Not provided'),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Active status')])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('No office locations yet')
            ->emptyStateDescription('Add head office and district office addresses.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficeLocations::route('/'),
            'create' => Pages\CreateOfficeLocation::route('/create'),
            'edit' => Pages\EditOfficeLocation::route('/{record}/edit'),
        ];
    }
}
