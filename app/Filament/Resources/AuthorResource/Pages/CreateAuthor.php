<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Resources\AuthorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\author_model;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function afterCreate(): void
    {
        // Set created_by and updated_by fields
        $author = author_model::latest('id')->first();
        
        if ($author) {
            author_model::where('id', $author->id)->update([
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }
}
