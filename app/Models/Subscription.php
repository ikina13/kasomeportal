<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_subscriptions';

    public $incrementing = true;

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
     * The attributes that are mass assignable.
     * Using $fillable is often clearer than $guarded.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'start_date',
        'end_date',
        'status',
        'subscription_type', // NEW: 'all_courses' or 'specific_courses'
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo("App\Models\app_user",'user_id');
    }

    /**
     * Get the courses associated with this subscription.
     * Only used when subscription_type = 'specific_courses'
     */
    public function courses()
    {
        return $this->belongsToMany(
            practical_video_model::class,
            'tbl_course_subscriptions',
            'subscription_id',
            'course_id'
        );
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Check if subscription is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        // Dates are already Carbon instances due to datetime casting
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Ensure we have Carbon instances
        if (!$startDate instanceof Carbon) {
            $startDate = Carbon::make($startDate);
        }
        if (!$endDate instanceof Carbon) {
            $endDate = Carbon::make($endDate);
        }

        return $now->between($startDate, $endDate);
    }

    /**
     * Check if this subscription grants access to a specific course.
     *
     * @param int $courseId
     * @return bool
     */
    public function hasCourseAccess($courseId): bool
    {
        // If subscription is not active, no access
        if (!$this->isActive()) {
            return false;
        }

        // If subscription type is 'all_courses', grant access to all
        if ($this->subscription_type === 'all_courses') {
            return true;
        }

        // If subscription type is 'specific_courses', check if course is included
        if ($this->subscription_type === 'specific_courses') {
            return $this->courses()->where('id', $courseId)->exists();
        }

        return false;
    }

}
