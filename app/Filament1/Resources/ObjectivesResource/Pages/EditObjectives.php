<?php

namespace App\Filament\Resources\ObjectivesResource\Pages;

use App\Filament\Resources\ObjectivesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObjectives extends EditRecord
{
    protected static string $resource = ObjectivesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
