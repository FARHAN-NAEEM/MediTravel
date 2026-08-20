<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatmentCostResource\Pages;
use App\Models\TreatmentCost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TreatmentCostResource extends Resource
{
    protected static ?string $model = TreatmentCost::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Medical Content';

    protected static ?string $navigationLabel = 'Cost Ranges';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('treatment_id')
                ->relationship('treatment', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('hospital_id')
                ->relationship('hospital', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('cost_min')
                ->numeric()
                ->minValue(0)
                ->required(),
            Forms\Components\TextInput::make('cost_max')
                ->numeric()
                ->minValue(0)
                ->required(),
            Forms\Components\TextInput::make('currency')
                ->required()
                ->maxLength(8)
                ->default('INR'),
            Forms\Components\Textarea::make('notes')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('treatment.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('hospital.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cost_min')
                    ->formatStateUsing(fn (TreatmentCost $record): string => $record->currency.' '.number_format((float) $record->cost_min))
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_max')
                    ->formatStateUsing(fn (TreatmentCost $record): string => $record->currency.' '.number_format((float) $record->cost_max))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('treatment_id')->relationship('treatment', 'name')->label('Treatment'),
                Tables\Filters\SelectFilter::make('hospital_id')->relationship('hospital', 'name')->label('Hospital'),
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
            'index' => Pages\ListTreatmentCosts::route('/'),
            'create' => Pages\CreateTreatmentCost::route('/create'),
            'edit' => Pages\EditTreatmentCost::route('/{record}/edit'),
        ];
    }
}
