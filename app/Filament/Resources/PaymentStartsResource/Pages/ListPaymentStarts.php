<?php

namespace App\Filament\Resources\PaymentStartsResource\Pages;

use App\Filament\Resources\PaymentStartsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentStarts extends ListRecords
{
    protected static string $resource = PaymentStartsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
