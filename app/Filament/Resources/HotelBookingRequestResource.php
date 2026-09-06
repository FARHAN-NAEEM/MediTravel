<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HotelBookingRequestResource\Pages;
use App\Models\HotelBookingRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HotelBookingRequestResource extends Resource
{
    protected static ?string $model = HotelBookingRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Hotel Booking';

    protected static ?string $navigationLabel = 'Booking Requests';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('ref_number')->disabled(),
                    Forms\Components\Select::make('status')->options(HotelBookingRequest::statusOptions())->required(),
                    Forms\Components\Placeholder::make('hotel_display')
                        ->label('Hotel')
                        ->content(fn (?HotelBookingRequest $record): string => $record?->hotel?->name ?? 'Not available'),
                    Forms\Components\Placeholder::make('room_display')
                        ->label('Room type')
                        ->content(fn (?HotelBookingRequest $record): string => $record?->room?->name ?? 'Not selected'),
                    Forms\Components\Placeholder::make('country_display')
                        ->label('Country')
                        ->content(fn (?HotelBookingRequest $record): string => $record?->country?->name ?? 'Not available'),
                    Forms\Components\Placeholder::make('city_display')
                        ->label('City')
                        ->content(fn (?HotelBookingRequest $record): string => $record?->city?->name ?? 'Not available'),
                    Forms\Components\Placeholder::make('hospital_display')
                        ->label('Related hospital')
                        ->content(fn (?HotelBookingRequest $record): string => $record?->hospital?->name ?? 'Not selected'),
                    Forms\Components\TextInput::make('name')->label('Guest / Patient name')->disabled(),
                    Forms\Components\TextInput::make('phone')->disabled(),
                    Forms\Components\TextInput::make('whatsapp')->disabled(),
                    Forms\Components\DatePicker::make('check_in')->disabled(),
                    Forms\Components\DatePicker::make('check_out')->disabled(),
                    Forms\Components\TextInput::make('room_count')->disabled(),
                    Forms\Components\TextInput::make('guest_count')->disabled(),
                    Forms\Components\Textarea::make('additional_note')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Booking Request')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('ref_number')->copyable(),
                    Infolists\Components\TextEntry::make('status')->badge(),
                    Infolists\Components\TextEntry::make('created_at')->dateTime(),
                    Infolists\Components\TextEntry::make('hotel.name')->label('Hotel'),
                    Infolists\Components\TextEntry::make('room.name')->label('Room type'),
                    Infolists\Components\TextEntry::make('hospital.name')->label('Related hospital'),
                    Infolists\Components\TextEntry::make('country.name')->label('Country'),
                    Infolists\Components\TextEntry::make('city.name')->label('City'),
                    Infolists\Components\TextEntry::make('check_in')->date(),
                    Infolists\Components\TextEntry::make('check_out')->date(),
                    Infolists\Components\TextEntry::make('room_count'),
                    Infolists\Components\TextEntry::make('guest_count'),
                ]),
            Infolists\Components\Section::make('Guest / Patient')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('name'),
                    Infolists\Components\TextEntry::make('phone')->copyable(),
                    Infolists\Components\TextEntry::make('whatsapp')->copyable(),
                    Infolists\Components\TextEntry::make('additional_note')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ref_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('hotel.name')->label('Hotel')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Guest / Patient')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('check_in')->date()->sortable(),
                Tables\Columns\TextColumn::make('check_out')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(HotelBookingRequest::statusOptions()),
                Tables\Filters\SelectFilter::make('hotel_id')->relationship('hotel', 'name')->label('Hotel'),
                Tables\Filters\SelectFilter::make('country_id')->relationship('country', 'name')->label('Country'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHotelBookingRequests::route('/'),
            'view' => Pages\ViewHotelBookingRequest::route('/{record}'),
            'edit' => Pages\EditHotelBookingRequest::route('/{record}/edit'),
        ];
    }
}
