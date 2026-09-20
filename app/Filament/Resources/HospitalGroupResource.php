<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HospitalGroupResource\Pages\ManageHospitalGroups;
use App\Models\HospitalGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalGroupResource extends Resource
{
    protected static ?string $model = HospitalGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\TextInput::make('website')->url()->rules(['nullable', 'url:http,https'])->maxLength(500),
            Forms\Components\FileUpload::make('logo')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->disk('public')->directory('hospital-groups')->maxSize(2048),
            Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('logo_preview')->state(fn (HospitalGroup $record) => $record->logoUrl())->label('Logo'),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('hospitals_count')->counts('hospitals')->label('Hospitals'),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHospitalGroups::route('/')];
    }
}
