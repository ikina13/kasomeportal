<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking_date_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_booking_dates';

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
     * Get the course that owns the download.
     */
    public function course()
    {
        return $this->belongsTo('App\Model\course_model', 'course_id');
    }

    /**
     * Get the bookings for the date.
     */
    public function bookings()
    {
        return $this->hasMany('App\Model\booking_model', 'selected_practical_date');
    }




}
