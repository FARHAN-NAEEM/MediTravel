<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function afterSave(): void
    {
        $this->record->logs()->create([
            'status' => $this->record->status,
            'note' => 'Status updated from admin panel.',
            'user_id' => auth()->id(),
        ]);
    }
}
