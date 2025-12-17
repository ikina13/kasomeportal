<?php

namespace App\Filament\Resources\ObjectivesResource\Pages;

use App\Filament\Resources\ObjectivesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewObjectives extends ViewRecord
{
    protected static string $resource = ObjectivesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
