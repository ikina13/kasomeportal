<?php

namespace App\Filament\Resources\UserRegionsResource\Pages;

use App\Filament\Resources\UserRegionsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserRegions extends EditRecord
{
    protected static string $resource = UserRegionsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
