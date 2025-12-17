<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class assessment_result_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_assessment_results';

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
     * Get the user that owns the assessment result.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\app_user', 'user_id');
    }

    /**
     * Get the course for the assessment result.
     */
    public function course()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }



}
