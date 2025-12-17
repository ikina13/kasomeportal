<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class module_model extends Model{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_modules';

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
     * Get the more details for course.
     */
    public function course()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }



    /**
     * Get the notes record associated with the module.
     */
    public function notes()
    {
        return $this->hasOne('App\Models\notes_model', 'module_id');
    }

    /**
     * Get the tests for the module.
     */
    public function tests()
    {
        return $this->hasMany('App\Models\test_model', 'module_id')->orderBy('id');
    }

    /**
     * Get the objectives record associated with the module.
     */
    public function objectives()
    {
        return $this->hasOne('App\Models\module_objective_model', 'module_id');
    }


    /**
     * Get the objectives record associated with the module.
     */
    public function ObjectivesResourse()
    {
        return $this->hasMany('App\Models\module_objective_model', 'module_id');
    }




    /**
     * Get the video record associated with the module.
     */
    public function video()
    {
        return $this->hasOne('App\Models\video_courses_model', 'module_id');
    }

    /**
     * Get the video record associated with the module.
     */
    public function podcast()
    {
        return $this->hasOne('App\Models\podcast_courses_model', 'module_id');
    }


    /**
     * Get the video record associated with the module.
     */
    public function ContentResaource()
    {
        return $this->hasMany('App\Models\content_model', 'module_id');
    }

}

