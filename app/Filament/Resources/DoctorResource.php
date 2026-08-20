<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('hospital_id')->relationship('hospital', 'name')->required(),
            Forms\Components\Select::make('department_id')->relationship('department', 'name')->required(),
            Forms\Components\TextInput::make('designation')->maxLength(255),
            Forms\Components\TextInput::make('qualifications')->maxLength(255),
            Forms\Components\TextInput::make('experience_years')->numeric()->default(0),
            Forms\Components\Textarea::make('bio')->columnSpanFull(),
            Forms\Components\Toggle::make('is_featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('hospital.name')->searchable(),
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
