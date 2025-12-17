<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payments_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_payment';

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
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

   /**
     * Get the more details for user.
     */
    public function users()
    {
        return $this->hasOne('App\Models\app_user', 'id','user_id');
    }


    /**
     * Get the more details for payment.
     */
    public function video()
    {
        return $this->belongsTo('App\Models\practical_video_model', 'video_id');
    }


}

