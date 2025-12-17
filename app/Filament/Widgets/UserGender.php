<?php

namespace App\Filament\Widgets;

use Filament\Widgets\PieChartWidget;
use App\Models\app_user as AppUser;

class UserGender extends PieChartWidget
{
    protected static ?string $heading = 'Registration by Gender';

    protected static ?int $sort = 6;

    protected static ?string $maxHeight = '270px';

    protected function getData(): array
    {
        $totalUsers = AppUser::count(); // Get total users

        $totalUsersMale = AppUser::where('sex', 'male')->count();
        $totalUsersFemale = AppUser::where('sex', 'female')->count();

        // Avoid division by zero
        $malePercentage = $totalUsers > 0 ? round(($totalUsersMale / $totalUsers) * 100, 2) : 0;
        $femalePercentage = $totalUsers > 0 ? round(($totalUsersFemale / $totalUsers) * 100, 2) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => [$malePercentage, $femalePercentage],
                    'backgroundColor' => [
                        'rgba(89, 93, 109, 0.4)', // Color for Male
                        'rgba(223, 130, 14, 0.4)', // Color for Female
                    ],
                ],
            ],
            'labels' => [
                'Male (' . $malePercentage . '%)', 
                'Female (' . $femalePercentage . '%)'
            ],
        ];
    }
}
