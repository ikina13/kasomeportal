<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;


class StatsOverview extends BaseWidget

{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
        return [
            Card::make('Total App users', '91,019')
                 
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Card::make('TOTAL PAID APP USERS', '54,890
            ')
              
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->color('danger'),
            Card::make('TOTAL CERTIFIED USERS', '0')
               
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('#00ff00'),
            Card::make('TOTAL PRACTICAL BOOKINGS', '11,321')
                
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('warning'),
        ];
    }
}
