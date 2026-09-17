<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroImageResource\Pages;
use App\Models\HeroImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroImageResource extends Resource
{
    protected static ?string $model = HeroImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?string $navigationLabel = 'Homepage Slider';

    protected static ?string $modelLabel = 'Slide';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->maxLength(255)
                ->helperText('Internal title for admin reference.'),
            Forms\Components\FileUpload::make('image_path')
                ->label('Slide image')
                ->image()
                ->disk('public')
                ->directory('hero-images')
                ->maxSize(10240)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->imageEditor()
                ->required()
                ->helperText('Upload JPG, PNG, or WebP. Maximum size: 10MB per image.'),
            Forms\Components\Select::make('image_fit')
                ->label('Image display')
                ->options(['cover' => 'Fill frame', 'contain' => 'Show full image'])
                ->default('cover')
                ->required()
                ->in(['cover', 'contain']),
            Forms\Components\Section::make('Slide text')
                ->description('Add a heading and paragraph in either language. The other language is optional and falls back to the available translation.')
                ->schema([
                    Forms\Components\TextInput::make('heading_bn')->label('Heading (Bangla)')->maxLength(160)->requiredWithout('heading_en'),
                    Forms\Components\TextInput::make('heading_en')->label('Heading (English)')->maxLength(160)->requiredWithout('heading_bn'),
                    Forms\Components\Textarea::make('body_bn')->label('Paragraph (Bangla)')->rows(4)->maxLength(600)->requiredWithout('body_en'),
                    Forms\Components\Textarea::make('body_en')->label('Paragraph (English)')->rows(4)->maxLength(600)->requiredWithout('body_bn'),
                ])->columns(2),
            Forms\Components\TextInput::make('alt_text')
                ->maxLength(255)
                ->helperText('Short description for accessibility and SEO.'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->required()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('title')->label('Internal title')->searchable(),
                Tables\Columns\TextColumn::make('heading_bn')->label('Heading (Bangla)')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('heading_en')->label('Heading (English)')->limit(40)->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListHeroImages::route('/'),
            'create' => Pages\CreateHeroImage::route('/create'),
            'edit' => Pages\EditHeroImage::route('/{record}/edit'),
        ];
    }
}
