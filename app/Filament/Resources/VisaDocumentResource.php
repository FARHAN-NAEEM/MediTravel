<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisaDocumentResource\Pages;
use App\Models\VisaDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VisaDocumentResource extends Resource
{
    protected static ?string $model = VisaDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Medical Content';

    protected static ?string $navigationLabel = 'Required Documents';

    protected static ?string $modelLabel = 'required document';

    protected static ?string $pluralModelLabel = 'Required Documents';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title_bn')
                ->label('Title Bangla')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Title English')
                ->maxLength(255),
            Forms\Components\Textarea::make('description_bn')
                ->label('Description Bangla')
                ->rows(3),
            Forms\Components\Textarea::make('description_en')
                ->label('Description English')
                ->rows(3),
            Forms\Components\Select::make('service_id')
                ->label('Checklist target')
                ->relationship('service', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Medical Visa Support')
                ->helperText('Leave empty for the Medical Visa Support page, or select a service for its details page.'),
            Forms\Components\Hidden::make('category')
                ->default('medical_visa'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_required')
                ->default(true),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')->label('Bangla')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title_en')->label('English')->searchable(),
                Tables\Columns\TextColumn::make('target')
                    ->label('Checklist target')
                    ->getStateUsing(fn (VisaDocument $record): string => $record->service?->name ?? 'Medical Visa Support')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_required')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Target service')
                    ->relationship('service', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_required'),
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
            'index' => Pages\ListVisaDocuments::route('/'),
            'create' => Pages\CreateVisaDocument::route('/create'),
            'edit' => Pages\EditVisaDocument::route('/{record}/edit'),
        ];
    }
}
