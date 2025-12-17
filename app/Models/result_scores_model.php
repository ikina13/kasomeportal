<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class result_scores_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_result_scores';

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
     * Get the more details for course modules enrolled.
     */
    public function enrolled_course_module()
    {
        return $this->belongsTo('App\Model\enrolled_course_modules_model', 'enrolled_course_module_id');
    }

    /**
     * Get the user that owns the entry
     */
    public function quiz()
    {
        return $this->belongsTo('App\Model\test_model', 'quiz_id');
    }

}
