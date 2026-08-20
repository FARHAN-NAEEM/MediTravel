<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('ref_number')
                ->label('Reference number')
                ->default(fn (): string => Inquiry::nextReference())
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Auto-generated from website inquiries. Super Admin can edit it if a custom WhatsApp reference is needed.'),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone')->required(),
            Forms\Components\TextInput::make('whatsapp'),
            Forms\Components\Select::make('type')->options([
                'appointment' => 'Appointment',
                'visa' => 'Visa',
                'cost' => 'Cost',
                'hospital' => 'Hospital',
                'contact' => 'Contact',
            ]),
            Forms\Components\Select::make('status')->options(Inquiry::statusOptions())->required(),
            Forms\Components\Textarea::make('message')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ref_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (?string $state): string => Inquiry::statusLabel($state))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Inquiry::statusOptions()),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'create' => Pages\CreateInquiry::route('/create'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
