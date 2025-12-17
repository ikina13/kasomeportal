<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class course_model extends Model
{
    
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_courses';

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
     * Get the modules for the  course.
     */
    public function modules()
    {
        return $this->hasMany('App\Models\module_model', 'course_id')->orderBy('id');
    }

    /**
     * Get the downloads for the  course.
     */
    public function downloads()
    {
        return $this->hasMany('App\Models\course_download_model', 'course_id');
    }

    /**
     * Get the enrollments for the  course.
     */
    public function enrollments()
    {
        return $this->hasMany('App\Models\enrollment_model', 'course_id');
    }

    /**
     * Get the bookings dates for the  course.
     */
    public function booking_dates()
    {
        return $this->hasMany('App\Models\booking_date_model', 'course_id');
    }


    /**
     * Get the centers for the course.
     */
    public function centers()
    {
        return $this->belongsToMany('App\Models\center_model', 'tbl_course_centers', 'course_id', 'center_id');
    }

    /**
     * Get the skills for the course.
     */
    public function skills()
    {
        return $this->hasMany('App\Model\skill_model', 'course_id');
    }


    /**
     * Get the assessment results record associated with the course.
     */
    public function assessment_results()
    {
        return $this->hasMany('App\Model\assessment_result_model', 'course_id');
    }

    /**
     * Get the study history associated with the course.
     */
    public function study_history()
    {
        return $this->hasMany('App\Model\user_study_history_model', 'course_id');
    }

    /**
     * Get the ratings associated with the course.
     */
    public function ratings()
    {
        return $this->hasMany('App\Model\rating_model', 'course_id');
    }

    /**
     * Get the user enrolled to the course.
     */
    public function users()
    {
        return $this->belongsToMany('App\Model\app_user', 'tbl_enrollments', 'course_id', 'user_id');
    }

    /**
     * Get the bookings for the course.
     */
    public function bookings()
    {
        return $this->hasMany('App\Model\booking_model', 'course_id');
    }

    /**
     * Get the image record associated with the course.
     */
    public function image()
    {
        return $this->hasOne('App\Models\image_model', 'course_id');
    }

    public function PracticleVideoResource()
    {
        return $this->hasOne('App\Models\practical_video_model', 'course_id');
    }

    protected $casts = [
        'is_admin' => 'boolean',
        'skills' => 'array',
    ];




}
