<?php

namespace App\Filament\Resources\ObjectivesResource\Pages;

use App\Filament\Resources\ObjectivesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObjectives extends ListRecords
{
    protected static string $resource = ObjectivesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
