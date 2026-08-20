<?php

namespace App\Filament\Resources\TreatmentCostResource\Pages;

use App\Filament\Resources\TreatmentCostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentCost extends EditRecord
{
    protected static string $resource = TreatmentCostResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
