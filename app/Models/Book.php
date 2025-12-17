<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $table = 'tbl_books';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'author',
        'author_id',
        'description',
        'price',
        'original_price',
        'language',
        'level',
        'image_url',
        'rating',
        'review_count',
        'stock_quantity',
        'is_active',
        'file_name',
        'download_url',
        'file_size',
        'file_type',
        'is_donation_enabled',
        'donation_min_amount',
        'preview_url',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'stock_quantity' => 'integer',
        'review_count' => 'integer',
        'is_active' => 'boolean',
        'is_donation_enabled' => 'boolean',
        'donation_min_amount' => 'decimal:2',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the author of the book.
     */
    public function authorModel(): BelongsTo
    {
        return $this->belongsTo(author_model::class, 'author_id');
    }

    /**
     * Get all purchases of this book.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(BookPurchase::class, 'book_id');
    }

    /**
     * Get all payments for this book.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookPayment::class, 'book_id');
    }

    /**
     * Check if a user has purchased this book.
     */
    public function hasUserPurchased($userId): bool
    {
        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get user's purchase for this book.
     */
    public function getUserPurchase($userId)
    {
        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->first();
    }

    /**
     * Get download URL for a user (if they have access).
     */
    public function getDownloadUrl($userId, $purchaseId = null)
    {
        if (!$this->hasUserPurchased($userId)) {
            return null;
        }

        $purchase = $purchaseId 
            ? $this->purchases()->find($purchaseId)
            : $this->getUserPurchase($userId);

        if (!$purchase || !$purchase->canDownload()) {
            return null;
        }

        return $this->download_url;
    }

    /**
     * Get total sales count.
     */
    public function getTotalSalesCountAttribute(): int
    {
        return $this->purchases()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Get total revenue.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->payments()
            ->where('status', 'settled')
            ->sum('amount') ?? 0;
    }
}

