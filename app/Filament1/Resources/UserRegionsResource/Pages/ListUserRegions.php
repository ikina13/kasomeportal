<?php

namespace App\Filament\Resources\UserRegionsResource\Pages;

use App\Filament\Resources\UserRegionsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserRegions extends ListRecords
{
    protected static string $resource = UserRegionsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
