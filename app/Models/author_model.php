<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class author_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_author';

    /**
     * The id associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['updated_at','deleted_at'];

    /**
     * Get all courses by this author.
     */
    public function courses()
    {
        return $this->hasMany('App\Models\practical_video_model', 'author_id');
    }

    /**
     * Get all books by this author.
     */
    public function books()
    {
        // Check if author_id column exists
        if (\Schema::hasColumn('tbl_books', 'author_id')) {
            return $this->hasMany('App\Models\Book', 'author_id');
        } else {
            // Fallback: return empty query builder if column doesn't exist
            // This prevents errors until migration is run
            return $this->hasMany('App\Models\Book', 'id')
                ->whereRaw('1 = 0'); // Always returns empty until migration is run
        }
    }

    /**
     * Get total books published by this author.
     */
    public function getTotalBooksAttribute(): int
    {
        return $this->books()->where('is_active', true)->count();
    }

    /**
     * Get total book sales count.
     */
    public function getTotalBookSalesCountAttribute(): int
    {
        return \App\Models\BookPurchase::whereHas('book', function($query) {
            $query->where('author_id', $this->id);
        })->where('status', 'completed')->count();
    }

    /**
     * Get total book revenue.
     */
    public function getTotalBookRevenueAttribute(): float
    {
        return \App\Models\BookPayment::whereHas('book', function($query) {
            $query->where('author_id', $this->id);
        })->where('status', 'settled')->sum('amount') ?? 0;
    }

}
