<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class test_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_tests';

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
     * Get the more details for module.
     */
    public function module()
    {
        return $this->belongsTo('App\Models\module_model', 'module_id');
    }

    /**
     * Get the more details for module.
     */
    public function course()
    {
        return $this->belongsTo('App\Models\course_model', 'course_id');
    }


    /**
     * Get the questions for the test.
     */
    public function questions()
    {
        return $this->hasMany('App\Models\question_model', 'test_id')->orderBy('question_number');
    }

    public function QuestionResaource()
    {
        return $this->hasMany('App\Models\question_model', 'test_id');
    }



}
