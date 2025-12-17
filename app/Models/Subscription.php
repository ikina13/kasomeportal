<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_subscriptions';

    public $incrementing = true;

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
     * The attributes that are mass assignable.
     * Using $fillable is often clearer than $guarded.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the more details for user.
     */
    public function user()
    {
        return $this->belongsTo("App\Models\app_user",'user_id');
    }



}
