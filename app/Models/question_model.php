<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class question_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_question';

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
     * Get the more details for the test.
     */
    public function test()
    {
        return $this->belongsTo('App\Model\test_model', 'test_id');
    }

    protected $casts = [
        'multiple_questions' => 'array',
        'true_false_questions' => 'array'

    ];

}
