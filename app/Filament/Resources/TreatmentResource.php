<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatmentResource\Pages;
use App\Models\Treatment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TreatmentResource extends Resource
{
    protected static ?string $model = Treatment::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Medical Content';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Name (English)')->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),
            Forms\Components\TextInput::make('name_bn')->label('Name (Bangla)')->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('department_id')->relationship('department', 'name')->searchable()->preload()->required(),
            Forms\Components\Textarea::make('description')->label('Description (English)')->rows(4),
            Forms\Components\Textarea::make('description_bn')->label('Description (Bangla)')->rows(4),
            Forms\Components\Select::make('illustration')->label('Catalogue illustration')
                ->options(config('treatments.illustrations'))->default('general')
                ->in(array_keys(config('treatments.illustrations'))),
            Forms\Components\FileUpload::make('image_path')->label('Custom image')
                ->image()->disk('public')->directory('treatments')->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            Forms\Components\TextInput::make('sort_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            Forms\Components\Toggle::make('is_featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('catalogue_image')->label('Image')
                    ->getStateUsing(fn (Treatment $record): string => $record->imageUrl())->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name_bn')->label('Bangla name')->searchable(),
                Tables\Columns\TextColumn::make('department.name')->label('Specialty')->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')->relationship('department', 'name')->label('Specialty'),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreatments::route('/'),
            'create' => Pages\CreateTreatment::route('/create'),
            'edit' => Pages\EditTreatment::route('/{record}/edit'),
        ];
    }
}
