<?php

namespace App\Filament\Resources\AuthorResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\author_model as Author;
use App\Models\Book;
use App\Models\practical_video_model;
use Illuminate\Support\Facades\Schema;

class AuthorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        // Total authors
        $totalAuthors = Author::count();
        
        // Authors with courses
        $authorsWithCourses = Author::has('courses')->count();
        
        // Authors with books (only if author_id column exists)
        $authorsWithBooks = 0;
        if (Schema::hasColumn('tbl_books', 'author_id')) {
            $authorsWithBooks = Author::has('books')->count();
        }
        
        // Total courses
        $totalCourses = practical_video_model::count();
        
        // Total books (if author_id column exists)
        $totalBooks = 0;
        if (Schema::hasColumn('tbl_books', 'author_id')) {
            $totalBooks = Book::count();
        }
        
        // Generate chart data (dummy data since authors don't have timestamps)
        // Using consistent chart data based on total count
        $chartData = array_fill(0, 7, (int)($totalAuthors / 7));
        if ($totalAuthors % 7 > 0) {
            $chartData[6] += ($totalAuthors % 7);
        }

        $cards = [
            Card::make('Total Authors', $totalAuthors)
                ->chart($chartData)
                ->color('success')
                ->description('All authors in the system'),
            
            Card::make('Authors with Courses', $authorsWithCourses)
                ->chart($chartData)
                ->color('info')
                ->description('Authors who have created courses'),
            
            Card::make('Total Courses', $totalCourses)
                ->chart($chartData)
                ->color('warning')
                ->description('All courses available'),
        ];
        
        // Only add books card if author_id column exists
        if (Schema::hasColumn('tbl_books', 'author_id')) {
            $cards[] = Card::make('Authors with Books', $authorsWithBooks)
                ->chart($chartData)
                ->color('danger')
                ->description('Total books: ' . $totalBooks);
        }
        
        return $cards;
    }
}

