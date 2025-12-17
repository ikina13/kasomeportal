<?php

namespace App\Filament\Resources\ClipCommentResource\Pages;

use App\Filament\Resources\ClipCommentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClipComment extends EditRecord
{
    protected static string $resource = ClipCommentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
