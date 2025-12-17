<?php

namespace App\Filament\Resources\ClipCommentResource\Pages;

use App\Filament\Resources\ClipCommentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\HeaderClipCommentsStarts;

class ListClipComments extends ListRecords
{
    protected static string $resource = ClipCommentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
           HeaderClipCommentsStarts::class
        ];
    }
}
