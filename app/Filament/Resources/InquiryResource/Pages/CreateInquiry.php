<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use Filament\Resources\Pages\CreateRecord;

class CreateInquiry extends CreateRecord
{
    protected static string $resource = InquiryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ref_number'] = $data['ref_number'] ?: Inquiry::nextReference();
        $data['status'] = $data['status'] ?: 'new';
        $data['source'] = $data['source'] ?? 'admin';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->logs()->create([
            'status' => $this->record->status,
            'note' => 'Inquiry created from admin panel.',
            'user_id' => auth()->id(),
        ]);
    }
}
