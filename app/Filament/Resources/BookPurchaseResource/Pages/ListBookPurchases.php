<?php

namespace App\Filament\Resources\BookPurchaseResource\Pages;

use App\Filament\Resources\BookPurchaseResource;
use App\Filament\Resources\BookPurchaseResource\Widgets\BookPurchaseStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListBookPurchases extends ListRecords
{
    protected static string $resource = BookPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Disable create action - purchases should be created through payment flow
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookPurchaseStatsWidget::class,
        ];
    }
}

