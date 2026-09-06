<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\SocialLinkResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\SocialLink;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Social Links';

    protected static ?string $modelLabel = 'Social link';

    protected static ?int $navigationSort = 3;

    protected static string | array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Social profile')
                ->description('The public footer displays the profile name, never the raw URL.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('platform')
                        ->options(SocialLink::PLATFORMS)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('name')
                        ->placeholder('Asian Health Connect')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('url')
                        ->label('Page URL')
                        ->url()
                        ->required()
                        ->maxLength(2048)
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('platform')->badge()->formatStateUsing(fn (string $state): string => SocialLink::PLATFORMS[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('url')->label('Destination')->limit(48)->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('platform')->options(SocialLink::PLATFORMS),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->emptyStateIcon('heroicon-o-share')
            ->emptyStateHeading('No social links yet')
            ->emptyStateDescription('Add Facebook now and other platforms whenever needed.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
