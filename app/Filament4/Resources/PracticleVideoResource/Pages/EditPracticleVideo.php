<?php

namespace App\Filament\Resources\PracticleVideoResource\Pages;

use App\Filament\Resources\PracticleVideoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPracticleVideo extends EditRecord
{
    protected static string $resource = PracticleVideoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
