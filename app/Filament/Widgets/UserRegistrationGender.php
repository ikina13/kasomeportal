<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\LineChartWidget;

class UserRegistrationGender extends LineChartWidget
{
    protected static ?string $heading = 'User Registration by Month';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        // Get the current year dynamically
        $currentYear = now()->year;

        // Fetch user counts grouped by month for the current year
        $monthlyCounts = DB::table('tbl_users')
        ->selectRaw('EXTRACT(MONTH FROM created_at::timestamp) AS month, COUNT(*) AS count')
        ->whereRaw('EXTRACT(YEAR FROM created_at::timestamp) = ?', [now()->year])
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

        // Generate an array of 12 months with counts, defaulting to 0 for missing months
        $data = array_fill(1, 12, 0);
        foreach ($monthlyCounts as $month => $count) {
            $data[$month] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => array_values($data), // Extract the counts in order
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
