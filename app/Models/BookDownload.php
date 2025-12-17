<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookDownload extends Model
{
    use HasFactory;

    protected $table = 'tbl_book_downloads';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'book_purchase_id',
        'user_id',
        'book_id',
        'download_token',
        'ip_address',
        'user_agent',
        'downloaded_at',
        'file_size',
        'download_status',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the purchase this download belongs to.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(BookPurchase::class, 'book_purchase_id');
    }

    /**
     * Get the user who downloaded.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(app_user::class, 'user_id');
    }

    /**
     * Get the book that was downloaded.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

