<?php

namespace App\Filament\Resources\PracticleVideoResource\Pages;

use App\Filament\Resources\PracticleVideoResource;
use App\Filament\Resources\PracticleVideoResource\Widgets\PaymentWidget;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracticleVideos extends ListRecords
{
    protected static string $resource = PracticleVideoResource::class;

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
