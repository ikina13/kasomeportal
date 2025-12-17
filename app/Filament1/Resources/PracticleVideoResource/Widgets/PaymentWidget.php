<?php

namespace App\Filament\Resources\PracticleVideoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class PaymentWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Enrolled Students', '1340')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('warning'),
        Card::make('Total Courses', '3543')
            
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        Card::make('Total Modules', '3543')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->descriptionIcon('heroicon-s-trending-up')
            ->color('danger')
        ];
    }
}
