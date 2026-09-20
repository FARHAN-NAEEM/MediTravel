<?php

namespace App\Filament\Resources\HospitalGroupResource\Pages;

use App\Filament\Resources\HospitalGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHospitalGroups extends ManageRecords
{
    protected static string $resource = HospitalGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
