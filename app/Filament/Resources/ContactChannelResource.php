<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\ContactChannelResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\ContactChannel;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class ContactChannelResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = ContactChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Contact Channels';

    protected static ?string $modelLabel = 'Contact channel';

    protected static ?int $navigationSort = 1;

    protected static string | array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Contact details')
                ->description('Manage phone, WhatsApp, and email destinations shown across the website.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options(ContactChannel::TYPES)
                        ->required()
                        ->native(false)
                        ->live(),
                    Forms\Components\TextInput::make('label')
                        ->placeholder('Main Office, Support, Visa Help')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('value')
                        ->label(fn (Get $get): string => match ($get('type')) {
                            'email' => 'Email address',
                            'whatsapp' => 'WhatsApp number',
                            default => 'Phone number',
                        })
                        ->email(fn (Get $get): bool => $get('type') === 'email')
                        ->tel(fn (Get $get): bool => in_array($get('type'), ['phone', 'whatsapp'], true))
                        ->rules(fn (Get $get): array => $get('type') === 'email'
                            ? ['email:rfc']
                            : ['regex:/^\+?[0-9][0-9\s().-]{6,30}$/'])
                        ->rule(fn (Get $get, ?ContactChannel $record) => Rule::unique('contact_channels', 'value')
                            ->where('type', $get('type'))
                            ->ignore($record))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Forms\Components\Toggle::make('is_primary')
                        ->label('Primary for this type')
                        ->helperText('Selecting this automatically clears the previous primary channel of the same type.'),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Inactive channels are hidden from the website.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(fn (string $state): string => ContactChannel::TYPES[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('value')->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_primary')->label('Primary')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(ContactChannel::TYPES),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->emptyStateIcon('heroicon-o-phone')
            ->emptyStateHeading('No contact channels yet')
            ->emptyStateDescription('Add a phone, WhatsApp number, or email address.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactChannels::route('/'),
            'create' => Pages\CreateContactChannel::route('/create'),
            'edit' => Pages\EditContactChannel::route('/{record}/edit'),
        ];
    }
}
