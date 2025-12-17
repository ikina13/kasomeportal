<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_bookings';

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
     * Get the Models booking date.
     *
     * @param  string  $value
     * @return string
     */
    public function getBookingDateAttribute($value)
    {
        $moment = new \MomentPHP\MomentPHP($value);
        return !is_null($value) ? $moment->format(self::format) : $value;
    }

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }

    /**
     * Get the more details for booking date.
     */
    public function practical_date()
    {
        return $this->belongsTo('App\Model\booking_date_model', 'selected_practical_date');
    }

    /**
     * Get the more details for center.
     */
    public function center()
    {
        return $this->belongsTo('App\Model\center_model', 'center_id');
    }

    /**
     * Get the more details for course.
     */
    public function course()
    {
        return $this->belongsTo('App\Model\course_model', 'course_id');
    }

}
