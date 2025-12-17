<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\clip_comments_model as Comments;
use App\Models\clip_replies_model as Reply;


class HeaderClipCommentsStarts extends BaseWidget

{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
    	 $totalComments = Comments::count();
    	 $totalReply = Reply::count();

        return [
            Card::make('Total Comments', $totalComments)
                 
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Card::make('Total Replies',  $totalReply)
              
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->color('danger'),
            
        ];
    }
}
