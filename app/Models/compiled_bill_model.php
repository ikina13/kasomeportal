<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\PendingScope;

class compiled_bill_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_compiled_bill';

    /**
     * The id associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bill_id';

    /**
     * Get the transactions for the payment.
     */
    public function controlNumber()
    {
        return $this->hasOne('App\Models\compiled_bill_cno_model', 'bill_id');
    }


    /**
     * Get the transactions for the payment.
     */
    public function course()
    {
        return $this->hasOne('App\Model\course_model', 'id','course_id');
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope(new PendingScope);
        
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['updated_at','deleted_at'];
}

