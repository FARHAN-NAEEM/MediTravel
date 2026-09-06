<?php

namespace App\Filament\Resources\HotelBookingRequestResource\Pages;

use App\Filament\Resources\HotelBookingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHotelBookingRequest extends ViewRecord
{
    protected static string $resource = HotelBookingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
