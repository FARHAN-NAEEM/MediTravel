<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HospitalResource\Pages;
use App\Models\City;
use App\Models\Hospital;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalResource extends Resource
{
    protected static ?string $model = Hospital::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_bn')->label('Name (Bangla)')->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('hospital_group_id')->relationship('group', 'name')->searchable()->preload()->label('Hospital group'),
            Forms\Components\Select::make('country_id')
                ->relationship('country', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                ->required(),
            Forms\Components\Select::make('city_id')
                ->label('City')
                ->options(fn (Get $get): array => City::query()
                    ->when(
                        $get('country_id'),
                        fn ($query, $countryId) => $query->where('country_id', $countryId),
                        fn ($query) => $query->whereRaw('1 = 0')
                    )
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->disabled(fn (Get $get): bool => blank($get('country_id')))
                ->placeholder('আগে country নির্বাচন করুন')
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('accreditation')->maxLength(255),
            Forms\Components\Select::make('care_type')->options(collect(Hospital::CARE_TYPES)->mapWithKeys(fn ($type) => [$type => __('hospitals.care_types.'.$type)]))->default('multi-specialty')->required(),
            Forms\Components\FileUpload::make('logo')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->disk('public')->directory('hospitals/logos')->maxSize(2048),
            Forms\Components\FileUpload::make('images')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->disk('public')->directory('hospitals/photos')->multiple()->reorderable()->maxFiles(5)->maxSize(5120),
            Forms\Components\TextInput::make('source_url')->label('Official hospital page')->url()->rules(['nullable', 'url:http,https'])->maxLength(500),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\Textarea::make('description_bn')->label('Description (Bangla)')->columnSpanFull(),
            Forms\Components\Textarea::make('card_highlight_bn')
                ->label('Card Highlight (Bangla)')
                ->rows(2)
                ->maxLength(180)
                ->helperText('Optional short specialty note shown on the hospital card. Maximum 180 characters.'),
            Forms\Components\Textarea::make('card_highlight_en')
                ->label('Card Highlight (English)')
                ->rows(2)
                ->maxLength(180)
                ->helperText('Optional English version. The other language is used as a fallback when this is empty.'),
            Forms\Components\Toggle::make('is_featured'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('group.name')->label('Group')->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label('City')->sortable(),
                Tables\Columns\TextColumn::make('card_highlight_bn')
                    ->label('Card Highlight')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\SelectFilter::make('hospital_group_id')->relationship('group', 'name')->label('Group'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListHospitals::route('/'),
            'create' => Pages\CreateHospital::route('/create'),
            'edit' => Pages\EditHospital::route('/{record}/edit'),
        ];
    }
}
