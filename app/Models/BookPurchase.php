<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookPurchase extends Model
{
    use HasFactory;

    protected $table = 'tbl_book_purchases';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'book_id',
        'payment_id',
        'status',
        'delivery_method',
        'delivery_address',
        'download_count',
        'last_downloaded_at',
        'max_downloads',
        'purchased_at',
        'download_token',
        'token_expires_at',
        'purchase_type', // Will be null if column doesn't exist
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'purchased_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(app_user::class, 'user_id');
    }

    /**
     * Get the book that was purchased.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the payment for this purchase.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(BookPayment::class, 'payment_id');
    }

    /**
     * Get all downloads for this purchase.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(BookDownload::class, 'book_purchase_id');
    }

    /**
     * Check if the user can still download.
     */
    public function canDownload(): bool
    {
        // Check if status is completed
        if ($this->status !== 'completed') {
            return false;
        }

        // Check if download limit has been reached
        if ($this->download_count >= $this->max_downloads) {
            return false;
        }

        // Check if token is expired
        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Generate a secure download token.
     */
    public function generateDownloadToken($expiryHours = 24): string
    {
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours($expiryHours);

        $this->update([
            'download_token' => $token,
            'token_expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Record a download event.
     */
    public function recordDownload($ipAddress = null, $userAgent = null, $fileSize = null)
    {
        // Increment download count
        $this->increment('download_count');
        $this->update([
            'last_downloaded_at' => Carbon::now(),
        ]);

        // Record in downloads table
        BookDownload::create([
            'book_purchase_id' => $this->id,
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'download_token' => $this->download_token,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'file_size' => $fileSize ?? $this->book->file_size,
            'download_status' => 'success',
        ]);

        return $this;
    }

    /**
     * Get remaining downloads count.
     */
    public function getRemainingDownloads(): int
    {
        return max(0, $this->max_downloads - $this->download_count);
    }

    /**
     * Check if token is valid.
     */
    public function isTokenValid($token): bool
    {
        if ($this->download_token !== $token) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Regenerate download token.
     */
    public function regenerateToken($expiryHours = 24): string
    {
        return $this->generateDownloadToken($expiryHours);
    }
}

