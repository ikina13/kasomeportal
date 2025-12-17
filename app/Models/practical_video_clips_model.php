<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class practical_video_clips_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_practical_video_clips';

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
     * Get the module that owns the video.
     */
    public function practical_video()
    {
        return $this->belongsTo('App\Models\practical_video_model', 'id');

    }
    

    /**
     * Get the module that owns the video.
     */
    public function payment()
    {
        return $this->belongsTo('App\Models\payments_model', 'video_id','practical_video_id');

    }
}

