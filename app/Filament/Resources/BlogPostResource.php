<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Filament\Resources\Concerns\OwnerOnlyResource;
use App\Http\Middleware\EnsureOwner;
use App\Models\BlogPost;
use App\Support\AdminAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    use OwnerOnlyResource;

    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Site Content';

    protected static string|array $routeMiddleware = [
        EnsureOwner::class.':'.AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('title')->label('Title (English)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_bn')->label('Title (Bangla)')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true),
            Forms\Components\Select::make('blog_category_id')->relationship('category', 'name')->searchable(),
            Forms\Components\Textarea::make('body')->label('Body (English)')->required()->rows(10),
            Forms\Components\Textarea::make('body_bn')->label('Body (Bangla)')->required()->rows(10),
            Forms\Components\TextInput::make('meta_title')->label('SEO title (English)')->maxLength(255),
            Forms\Components\TextInput::make('meta_title_bn')->label('SEO title (Bangla)')->maxLength(255),
            Forms\Components\Textarea::make('meta_description')->label('Summary / SEO description (English)')->required()->rows(3),
            Forms\Components\Textarea::make('meta_description_bn')->label('Summary / SEO description (Bangla)')->required()->rows(3),
            Forms\Components\DateTimePicker::make('published_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('English')->searchable()->limit(70),
            Tables\Columns\TextColumn::make('title_bn')->label('Bangla')->searchable()->limit(70),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBlogPosts::route('/'),
        ];
    }
}
