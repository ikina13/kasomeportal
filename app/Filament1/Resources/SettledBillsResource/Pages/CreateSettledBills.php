<?php

namespace App\Filament\Resources\SettledBillsResource\Pages;

use App\Filament\Resources\SettledBillsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSettledBills extends CreateRecord
{
    protected static string $resource = SettledBillsResource::class;
}
