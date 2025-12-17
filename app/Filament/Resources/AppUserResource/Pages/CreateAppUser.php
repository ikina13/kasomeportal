<?php

namespace App\Filament\Resources\AppUserResource\Pages;

use App\Filament\Resources\AppUserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateAppUser extends CreateRecord
{
    protected static string $resource = AppUserResource::class;

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash the password before creating the user
        $data['password'] = Hash::make($data['password']);
 
        return $data;
    }
}
