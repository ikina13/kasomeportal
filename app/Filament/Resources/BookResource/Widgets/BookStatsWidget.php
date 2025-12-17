<?php

namespace App\Filament\Resources\BookResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\BookPayment;
use Illuminate\Support\Facades\DB;

class BookStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        // Total books
        $totalBooks = Book::count();
        
        // Active books
        $activeBooks = Book::where('is_active', true)->count();
        
        // Total sales (completed purchases)
        $totalSales = BookPurchase::where('status', 'completed')->count();
        
        // Total revenue from settled payments
        $totalRevenue = BookPayment::where('status', 'settled')->sum('amount') ?? 0;
        
        // Format revenue as currency
        $formattedRevenue = number_format($totalRevenue, 0) . ' TZS';
        
        // Get last 7 days data for charts
        $recentBooks = Book::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
        
        $recentSales = BookPurchase::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
        
        // Pad arrays to 7 elements for consistent chart display
        while (count($recentBooks) < 7) {
            array_unshift($recentBooks, 0);
        }
        while (count($recentSales) < 7) {
            array_unshift($recentSales, 0);
        }

        return [
            Card::make('Total Books', $totalBooks)
                ->chart($recentBooks)
                ->color('success')
                ->description('All books in the system'),
            
            Card::make('Active Books', $activeBooks)
                ->chart($recentBooks)
                ->color('info')
                ->description('Currently available for purchase'),
            
            Card::make('Total Sales', $totalSales)
                ->chart($recentSales)
                ->color('warning')
                ->description('Completed book purchases'),
            
            Card::make('Total Revenue', $formattedRevenue)
                ->chart($recentSales)
                ->color('danger')
                ->description('From settled payments')
                ->descriptionIcon('heroicon-s-trending-up'),
        ];
    }
}

