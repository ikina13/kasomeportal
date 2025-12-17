<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentChart extends LineChartWidget
{
    protected static ?string $heading = 'Last 30 Days Views';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get today's date (ensure it's at the start of the day)
        $today = Carbon::today();

        // Calculate the date 30 days ago from today
        $thirtyDaysAgo = $today->copy()->subDays(29);

        // Fetch view counts grouped by day for the last 30 days
        $dailyCounts = DB::table('tbl_video_views')
            ->selectRaw("DATE(created_at) AS date, COUNT(*) AS count")
            ->whereBetween('created_at', [$thirtyDaysAgo->toDateTimeString(), $today->endOfDay()->toDateTimeString()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date') // Pluck count with date as key
            ->toArray();

        // Generate labels for the last 30 days
        $labels = [];
        $data = [];

        // Loop through each day in the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $currentDate = $thirtyDaysAgo->copy()->addDays($i)->toDateString(); // Ensure consistent formatting
            $labels[] = $currentDate; // Add the date to the labels

            // Explicitly check for today's date to ensure it's included
            if ($currentDate === $today->toDateString()) {
                $data[] = $dailyCounts[$currentDate] ?? 0; // Default to 0 if no data exists
            } else {
                $data[] = $dailyCounts[$currentDate] ?? 0; // Add the count or default to 0
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Video Views',
                    'data' => $data,
                    'borderColor' => 'rgb(75, 192, 192)', // Optional: Set a color for the line
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Optional: Set a background color for the area under the line
                ],
            ],
            'labels' => $labels,
        ];
    }
}
