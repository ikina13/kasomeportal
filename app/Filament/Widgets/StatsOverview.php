<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\app_user as AppUser;
use App\Models\payments_model as SettledBills;

class StatsOverview extends BaseWidget

{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {

        $totalUsers = AppUser::count();
        $totalUsersPaid = SettledBills::where('status','settled')->count();
        $totalUsersMale = AppUser::where('sex','male')->count();
        $totalUsersFemale = AppUser::where('sex','female')->count();
         
        return [
            Card::make('Total App users', $totalUsers)
                 
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Card::make('TOTAL PAID APP USERS',  $totalUsersPaid)
              
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->color('danger'),
            Card::make('TOTAL MALE USERS',  $totalUsersMale)
               
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('#00ff00'),
            Card::make('TOTAL FEMALE USERS', $totalUsersFemale)
                
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('warning'),
        ];
    }
}
