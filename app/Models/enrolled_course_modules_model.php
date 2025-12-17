<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class enrolled_course_modules_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_enrolled_course_modules';

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
    public function module()
    {
        return $this->belongsTo('App\Model\module_model', 'module_id');
    }

    /**
     * Get the user that owns the entry
     */
    public function enrollment()
    {
        return $this->belongsTo('App\Model\enrollment_model', 'enrollement_id');
    }

    /**
     * Get the enrolled modules results for the enrollment course module.
     */
    public function results()
    {
        return $this->hasMany('App\Model\result_scores_model', 'enrolled_course_module_id')->orderBy('id');
    }

}
