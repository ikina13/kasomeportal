<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPayment extends Model
{
    use HasFactory;

    protected $table = 'tbl_books_payment';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'book_id',
        'book_purchase_id',
        'amount',
        'status',
        'transactiontoken',
        'transref',
        'pnrid',
        'ccdapproval',
        'transid',
        'payment_type',
        'donation_type',
        'donation_title',
        'is_anonymous',
        'donor_name',
        'donor_message',
        'expired_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'expired_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(app_user::class, 'user_id');
    }

    /**
     * Get the book this payment is for.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the purchase associated with this payment.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(BookPurchase::class, 'book_purchase_id');
    }

    /**
     * Check if payment is settled/completed.
     */
    public function isSettled(): bool
    {
        return $this->status === 'settled';
    }
}

