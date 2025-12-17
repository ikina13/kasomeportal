<?php

namespace App\Filament\Resources\BookPurchaseResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\BookPurchase;
use App\Models\BookPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookPurchaseStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        // Total purchases
        $totalPurchases = BookPurchase::count();
        
        // Completed purchases
        $completedPurchases = BookPurchase::where('status', 'completed')->count();
        
        // Pending purchases
        $pendingPurchases = BookPurchase::where('status', 'pending')->count();
        
        // Total revenue from settled payments (amount is in payments table, not purchases)
        $totalRevenue = BookPayment::where('status', 'settled')->sum('amount') ?? 0;
        $formattedRevenue = number_format($totalRevenue, 0) . ' TZS';
        
        // Purchase type breakdown (only if column exists)
        $regularPurchases = 0;
        $donationPurchases = 0;
        $typeDescription = 'Revenue from all purchases';
        
        if (Schema::hasColumn('tbl_book_purchases', 'purchase_type')) {
            $regularPurchases = BookPurchase::where('status', 'completed')
                ->where('purchase_type', 'purchase')
                ->count();
            
            $donationPurchases = BookPurchase::where('status', 'completed')
                ->where('purchase_type', 'donation')
                ->count();
            
            $typeDescription = $regularPurchases . ' regular, ' . $donationPurchases . ' donations';
        }
        
        // Get last 7 days data for charts
        $recentPurchases = BookPurchase::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
        
        // Pad array to 7 elements
        while (count($recentPurchases) < 7) {
            array_unshift($recentPurchases, 0);
        }

        return [
            Card::make('Total Purchases', $totalPurchases)
                ->chart($recentPurchases)
                ->color('success')
                ->description('All purchase records'),
            
            Card::make('Completed Purchases', $completedPurchases)
                ->chart($recentPurchases)
                ->color('info')
                ->description('Successfully completed'),
            
            Card::make('Pending Purchases', $pendingPurchases)
                ->chart($recentPurchases)
                ->color('warning')
                ->description('Awaiting completion'),
            
            Card::make('Total Revenue', $formattedRevenue)
                ->chart($recentPurchases)
                ->color('danger')
                ->description($typeDescription)
                ->descriptionIcon('heroicon-s-trending-up'),
        ];
    }
}

