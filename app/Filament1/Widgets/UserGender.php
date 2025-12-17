<?php

namespace App\Filament\Widgets;

use Filament\Widgets\PieChartWidget;

class UserGender extends PieChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?int $sort = 6;

    protected static ?string $maxHeight = '270px';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => [4344, 5676],
                ],
            ],
            'backgroundColor'=> [
                'rgba(255, 99, 132,0.2)',
                'rgba(54, 162, 235,0.2)',

            ],
            'labels' => ['Male', 'Female'],
            ];
    }
}
