<?php

namespace App\Filament\Resources\ContactChannelResource\Pages;

use App\Filament\Resources\ContactChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactChannels extends ListRecords
{
    protected static string $resource = ContactChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add contact channel')->icon('heroicon-o-plus')];
    }
}
