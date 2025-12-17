<?php

namespace App\Filament\Resources\PracticleVideoClipsResource\Pages;

use App\Filament\Resources\PracticleVideoClipsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracticleVideoClips extends ListRecords
{
    protected static string $resource = PracticleVideoClipsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
