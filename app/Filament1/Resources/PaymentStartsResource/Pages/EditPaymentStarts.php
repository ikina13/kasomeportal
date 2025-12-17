<?php

namespace App\Filament\Resources\PaymentStartsResource\Pages;

use App\Filament\Resources\PaymentStartsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentStarts extends EditRecord
{
    protected static string $resource = PaymentStartsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
