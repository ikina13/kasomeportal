<?php

namespace App\Filament\Resources\SettledBillsResource\Pages;

use App\Filament\Resources\SettledBillsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettledBills extends EditRecord
{
    protected static string $resource = SettledBillsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
