<?php

namespace App\Filament\Resources\BookPurchaseResource\Pages;

use App\Filament\Resources\BookPurchaseResource;
use Filament\Resources\Pages\EditRecord;

class EditBookPurchase extends EditRecord
{
    protected static string $resource = BookPurchaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}

