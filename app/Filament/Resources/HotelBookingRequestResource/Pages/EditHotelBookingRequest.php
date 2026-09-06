<?php

namespace App\Filament\Resources\HotelBookingRequestResource\Pages;

use App\Filament\Resources\HotelBookingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHotelBookingRequest extends EditRecord
{
    protected static string $resource = HotelBookingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
