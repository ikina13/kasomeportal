<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class video_comments_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_video_comments';

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
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public function PracticleVideoClips()
    {
        return $this->hasMany('App\Models\practical_video_clips_model', 'practical_video_id');
    }

     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public function replies()
    {
        return $this->hasMany('App\Models\practical_video_clips_model', 'practical_video_id');
    }


    /**
     * Get the module that owns the video.
     */
    public function course()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }

    /**
     * Get the module that owns the video.
     */
    public function video()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }


}
