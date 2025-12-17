<?php

namespace App\Filament\Resources\SettledBillsResource\Pages;

use App\Filament\Resources\SettledBillResource\Widgets\PaymentWidget;
use App\Filament\Resources\SettledBillsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettledBills extends ListRecords
{
    protected static string $resource = SettledBillsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
          PaymentWidget::class
        ];
    }
}
