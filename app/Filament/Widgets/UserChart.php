<?php

namespace App\Filament\Widgets;
use Illuminate\Support\Facades\DB;

use Filament\Widgets\BarChartWidget;

class UserChart extends BarChartWidget
{
    protected static ?string $heading = 'Registered Students';

    protected static ?int $sort = 4;
    
     public function getData(): array
{
    $currentDate = now();
    $startDate = $currentDate->copy()->subMonths(11)->startOfMonth()->format('Y-m-d H:i:s');
    $endDate = $currentDate->endOfMonth()->format('Y-m-d H:i:s');

    // Fetch user counts grouped by month-year
    $monthlyCounts = DB::table('tbl_users')
    ->selectRaw("TO_CHAR(CAST(created_at AS TIMESTAMP), 'YYYY-MM') AS month_year, COUNT(*) AS count")
    ->whereBetween(DB::raw("CAST(created_at AS TIMESTAMP)"), [$startDate, $endDate])
    ->groupBy(DB::raw("TO_CHAR(CAST(created_at AS TIMESTAMP), 'YYYY-MM')"))
    ->orderBy(DB::raw("TO_CHAR(CAST(created_at AS TIMESTAMP), 'YYYY-MM')"))
    ->pluck('count', 'month_year')
    ->toArray();

  //  dd($monthlyCounts);

    // Debug the $monthlyCounts array
    //dd($monthlyCounts);

 

 // Use the existing array directly for labels and data
    $labels = array_keys($monthlyCounts); // Get the keys (e.g., '2024-02', '2024-03', etc.)
    $data = array_values($monthlyCounts); // Get the values (counts)

// Debug the results
 

 
    return [
        'datasets' => [
            [
                'label' => 'User Registrations',
                'data' => $data,
                'backgroundColor' => '#595d6d', 
            ],
        ],
        'labels' => $labels,
    ];
}
      

}
