<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\ReviewResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\Review;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Site Content';

    protected static string|array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('patient_name')->label('Patient name (English)')->required()->maxLength(255),
            Forms\Components\TextInput::make('patient_name_bn')->label('Patient name (Bangla)')->required()->maxLength(255),
            Forms\Components\Textarea::make('body')->label('Review (English)')->required()->rows(5),
            Forms\Components\Textarea::make('body_bn')->label('Review (Bangla)')->required()->rows(5),
            Forms\Components\TextInput::make('treatment')->label('Treatment (English)')->maxLength(255),
            Forms\Components\TextInput::make('treatment_bn')->label('Treatment (Bangla)')->maxLength(255),
            Forms\Components\Select::make('hospital_id')->relationship('hospital', 'name')->searchable(),
            Forms\Components\TextInput::make('rating')->numeric()->integer()->minValue(1)->maxValue(5)->default(5)->required(),
            Forms\Components\Toggle::make('is_verified')->default(false),
            Forms\Components\Toggle::make('is_published')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('patient_name')->label('English')->searchable()->limit(70),
            Tables\Columns\TextColumn::make('patient_name_bn')->label('Bangla')->searchable()->limit(70),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReviews::route('/'),
        ];
    }
}
