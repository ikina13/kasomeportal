<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class image_model extends Model{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_images';

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
     * Get the more details for page.
     */
    public function page()
    {
        return $this->belongsTo('App\Model\page_model', 'page_id');
    }

    /**
     * Get the module that owns the objectives.
     */
    public function objective()
    {
        return $this->belongsTo('App\Model\module_objective_model', 'objective_id');
    }

    /**
     * Get the course that owns the image.
     */
    public function course()
    {
        return $this->belongsTo('App\Model\course_model', 'course_id');
    }



}
