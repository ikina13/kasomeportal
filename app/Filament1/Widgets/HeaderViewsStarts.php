<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\video_views_model as VideoViews;
use Illuminate\Support\Facades\Request;


class HeaderViewsStarts extends BaseWidget

{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {

        $startDate = request('tableFilters.created_at.start');
        $endDate = request('tableFilters.created_at.end');

        $totalUsers = VideoViews::query()
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();

       

     /*   $paidUsers = AppUser::query()
            ->where('status', 'paid')
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();
     */

        return [
            Card::make('Total Views',   $totalUsers)
                 
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success')
            
              
        ];
    }
}
