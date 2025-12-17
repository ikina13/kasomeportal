<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class practical_video_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_practical_video';

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
     * Get the module that owns the video.
     */
    public function course()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }

     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public function payment()
    {
        return $this->hasOne('App\Models\payments_model', 'video_id','id');
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public function subject()
    {
        return $this->hasOne('App\Models\subject_model','id', 'subject_id');
    }

    /**
        * Get all course access records.
    */
   public function userAccess()
   {
        return $this->hasMany('App\Models\user_course_access_model','id', 'video_id');
   }

    /**
     * Get the author of this course.
     */
    public function author()
    {
        return $this->belongsTo('App\Models\author_model', 'author_id');
    }

}
