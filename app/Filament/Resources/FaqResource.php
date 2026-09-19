<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Filament\Resources\FaqResource\Pages;
use App\Http\Middleware\EnsureOwner;
use App\Models\Faq;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Site Content';

    protected static string|array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('question')->label('Question (English)')->required()->maxLength(255),
            Forms\Components\TextInput::make('question_bn')->label('Question (Bangla)')->required()->maxLength(255),
            Forms\Components\Textarea::make('answer')->label('Answer (English)')->required()->rows(5),
            Forms\Components\Textarea::make('answer_bn')->label('Answer (Bangla)')->required()->rows(5),
            Forms\Components\TextInput::make('category')->default('general')->required()->maxLength(255),
            Forms\Components\TextInput::make('sort_order')->numeric()->integer()->minValue(0)->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('question')->label('English')->searchable()->limit(70),
            Tables\Columns\TextColumn::make('question_bn')->label('Bangla')->searchable()->limit(70),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFaqs::route('/'),
        ];
    }
}
