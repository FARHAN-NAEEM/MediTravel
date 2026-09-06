<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HotelResource\Pages;
use App\Models\City;
use App\Models\Hotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Hotel Booking';

    protected static ?string $navigationLabel = 'Hotels';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, Set $set, Get $get) => blank($get('slug')) ? $set('slug', Str::slug($state ?? '')) : null),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Select::make('country_id')
                        ->relationship('country', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('city_id', null);
                            $set('hospitals', []);
                        })
                        ->required(),
                    Forms\Components\Select::make('city_id')
                        ->label('City')
                        ->options(fn (Get $get): array => City::query()
                            ->when(
                                $get('country_id'),
                                fn (Builder $query, $countryId) => $query->where('country_id', $countryId),
                                fn (Builder $query) => $query->whereRaw('1 = 0')
                            )
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->disabled(fn (Get $get): bool => blank($get('country_id')))
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('hospitals', []))
                        ->placeholder('Select country first')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('hospitals')
                        ->label('Nearby Hospitals / Medical Areas')
                        ->relationship(
                            name: 'hospitals',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                ->when($get('country_id'), fn (Builder $query, $countryId) => $query->where('country_id', $countryId))
                                ->when($get('city_id'), fn (Builder $query, $cityId) => $query->where('city_id', $cityId))
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->disabled(fn (Get $get): bool => blank($get('city_id')))
                        ->helperText('Only hospitals from the selected country and city are available.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('area')->maxLength(255),
                    Forms\Components\TextInput::make('hospital_distance_note')
                        ->label('Hospital distance / proximity note')
                        ->maxLength(255)
                        ->placeholder('e.g. 500 metres from the hospital'),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('special_notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Gallery')
                ->schema([
                    Forms\Components\FileUpload::make('images')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->directory('hotels')
                        ->maxSize(10240)
                        ->maxFiles(12)
                        ->reorderable()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Upload JPG, PNG, or WebP. Maximum 10MB per image, up to 12 images.')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Estimated Cost')
                ->description('Displayed as an estimate only, never as a fixed booking price.')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('cost_min')
                        ->label('Minimum estimated cost')
                        ->numeric()
                        ->minValue(0),
                    Forms\Components\TextInput::make('cost_max')
                        ->label('Maximum estimated cost')
                        ->numeric()
                        ->minValue(0)
                        ->gte('cost_min'),
                    Forms\Components\TextInput::make('currency')
                        ->required()
                        ->default('USD')
                        ->maxLength(8),
                    Forms\Components\Select::make('pricing_unit')
                        ->options([
                            'night' => 'Per night',
                            'week' => 'Per week',
                            'month' => 'Per month',
                        ])
                        ->required()
                        ->default('night'),
                ]),
            Forms\Components\Section::make('Rooms')
                ->description('Add flexible room types and facilities for this hotel.')
                ->schema([
                    Forms\Components\Repeater::make('rooms')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->collapsed()
                        ->cloneable()
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('name')->label('Room type')->required()->maxLength(255),
                            Forms\Components\TextInput::make('bed_count')->numeric()->minValue(1)->default(1)->required(),
                            Forms\Components\TextInput::make('room_size')->maxLength(100),
                            Forms\Components\TextInput::make('bathroom')->maxLength(100)->placeholder('Private / Shared'),
                            Forms\Components\Toggle::make('has_ac')->label('AC'),
                            Forms\Components\Toggle::make('has_wifi')->label('Wi-Fi'),
                            Forms\Components\TagsInput::make('facilities')
                                ->placeholder('Add a facility')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                            Forms\Components\Toggle::make('is_active')->default(true),
                        ])
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Publishing')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Hotel Information')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('name'),
                    Infolists\Components\TextEntry::make('country.name')->label('Country'),
                    Infolists\Components\TextEntry::make('city.name')->label('City'),
                    Infolists\Components\TextEntry::make('address')->columnSpan(2),
                    Infolists\Components\IconEntry::make('is_active')->boolean(),
                    Infolists\Components\TextEntry::make('hospitals.name')->label('Nearby hospitals')->listWithLineBreaks(),
                    Infolists\Components\TextEntry::make('hospital_distance_note'),
                    Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('special_notes')->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('Estimated Cost')
                ->schema([
                    Infolists\Components\TextEntry::make('estimated_cost')
                        ->state(fn (Hotel $record): string => $record->estimatedCostLabel() ?? 'Not provided'),
                ]),
            Infolists\Components\Section::make('Gallery')
                ->schema([
                    Infolists\Components\ImageEntry::make('images')->disk('public')->height(160)->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('Rooms')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('rooms')
                        ->schema([
                            Infolists\Components\TextEntry::make('name'),
                            Infolists\Components\TextEntry::make('bed_count'),
                            Infolists\Components\IconEntry::make('has_ac')->boolean()->label('AC'),
                            Infolists\Components\IconEntry::make('has_wifi')->boolean()->label('Wi-Fi'),
                            Infolists\Components\TextEntry::make('facilities')->badge(),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name')->label('Country')->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label('City')->sortable(),
                Tables\Columns\TextColumn::make('hospitals.name')->label('Related hospitals')->listWithLineBreaks()->limitList(2),
                Tables\Columns\TextColumn::make('estimated_cost')
                    ->label('Estimated cost')
                    ->state(fn (Hotel $record): string => $record->estimatedCostLabel() ?? 'Not provided'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')->relationship('country', 'name')->label('Country'),
                Tables\Filters\SelectFilter::make('city_id')->relationship('city', 'name')->label('City'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHotels::route('/'),
            'create' => Pages\CreateHotel::route('/create'),
            'view' => Pages\ViewHotel::route('/{record}'),
            'edit' => Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}
