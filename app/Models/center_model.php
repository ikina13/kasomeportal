<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class center_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_centers';

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
     * Get the courses for the center.
     */
    public function courses()
    {
        return $this->belongsToMany('App\Model\course_model', 'tbl_course_centers', 'center_id', 'course_id');
    }


    /**
     * Get the bookings for the center.
     */
    public function bookings()
    {
        return $this->hasMany('App\Model\booking_model', 'center_id');
    }



}
