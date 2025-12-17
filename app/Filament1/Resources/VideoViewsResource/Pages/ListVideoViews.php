<?php

namespace App\Filament\Resources\VideoViewsResource\Pages;

use App\Filament\Resources\VideoViewsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\HeaderViewsStarts;

class ListVideoViews extends ListRecords
{
    protected static string $resource = VideoViewsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
           HeaderViewsStarts::class
        ];
    }
}
