<?php

namespace App\Filament\Resources\PracticleVideoClipsResource\Pages;

use App\Filament\Resources\PracticleVideoClipsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPracticleVideoClips extends EditRecord
{
    protected static string $resource = PracticleVideoClipsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
