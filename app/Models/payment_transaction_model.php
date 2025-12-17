<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_transaction_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_purchased_tokens';

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
     * Get the more details for user.
     */
    public function payment()
    {
        return $this->belongsTo('App\Model\payment_model', 'payment_id');
    }


    /**
     * Get the Models activated At.
     *
     * @param  string  $value
     * @return string
     */
    public function getActivatedAtAttribute($value)
    {
        $moment = new \MomentPHP\MomentPHP($value);
        return !is_null($value) ? $moment->format(parent::format) : $value;
    }

    /**
     * Get the Models expire time.
     *
     * @param  string  $value
     * @return string
     */
    public function getExpireTimeAttribute($value)
    {
        $moment = new \MomentPHP\MomentPHP($value);
        return !is_null($value) ? $moment->format(parent::format) : $value;
    }

}
