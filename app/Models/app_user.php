<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class app_user extends Model {

    use HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_users';

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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password','reset_password_token'];

    /**
     * Get the role that belongs to the user.
     */
    public function role()
    {
        return $this->belongsTo('App\Models\role_model', 'role_id');
    }

    /**
     * Get the region that the user is from.
     */
    public function user_region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\region_model', 'region_id');
    }

    /**
     * Get the region that the user is from.
     */
    public function user_district(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\district_model', 'district_id');
    }

    /**
     * Get the more details for app user.
     */
    public function app(): HasOne
    {
        return $this->hasOne(app_user::class,'id', 'id');
    }

    /**
     * Get the more details for staff user.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(staff_model::class,'id');
    }

    /**
     * Get the messages for the blog post.
     */
    public function sms(): HasMany
    {
        return $this->hasMany(sms_model::class,'user_id');
    }

    /**
     * Get the downloads for the user.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(course_download_model::class, 'user_id');
    }

    /**
     * Get the bookings dates for the  user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(booking_model::class, 'user_id');
    }

    /**
     * Get the assessment results record associated with the user.
     */
    public function assessment_results(): HasMany
    {
        return $this->hasMany(assessment_result_model::class, 'user_id');
    }

    /**
     * Get the grant apps record associated with the user.
     */
    public function grants(): HasMany
    {
        return $this->hasMany(grant_application_model::class, 'user_id');
    }

    /**
     * Get the study history associated with the user.
     */
    public function study_history(): HasMany
    {
        return $this->hasMany(user_study_history_model::class, 'user_id');
    }


    /**
     * Get the course ratings associated with the user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(rating_model::class, 'user_id');
    }

    /**
     * Get the payments associated with the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(payments_model::class, 'user_id');
    }

   /**
     * Get the more details for user.
     */
    public function Views()
    {
        return $this->belongsTo('App\Models\video_views_model', 'id','user_id');
    }  

    public function subscriptions(): HasMany
    {
    // The foreign key on the subscriptions table is 'user_id'
    // The local key on this (users) table is 'id'
    return $this->hasMany(Subscription::class, 'user_id', 'id');
   }

    public function getSubscriptionStatus(): string
    {
    // Get the user's most recent 'active' subscription
    $latestSubscription = $this->subscriptions()
                               ->where('status', 'active')
                               ->latest('end_date')
                               ->first();

    if (!$latestSubscription) {
        return 'No Subscription';
    }
    
    // Check if the current time is between the start and end dates
    $now = Carbon::now();
    // Dates are already Carbon instances due to datetime casting
    $startDate = $latestSubscription->start_date;
    $endDate = $latestSubscription->end_date;

    // Ensure we have Carbon instances
    if (!$startDate instanceof Carbon) {
        $startDate = Carbon::make($startDate);
    }
    if (!$endDate instanceof Carbon) {
        $endDate = Carbon::make($endDate);
    }

    if ($now->between($startDate, $endDate)) {
        return 'Active';
    }

    // --- NEW LOGIC ADDED HERE ---
    // If the subscription is no longer in the date range,
    // update its status in the database to 'expired'.
    $latestSubscription->status = 'expired';
    $latestSubscription->save();
    // --- END OF NEW LOGIC ---

    return 'Expired';
   }


    /**
     * Formats the phone number to the international standard.
     *
     * @param string $phone
     * @return string
     */
    public static function getformatted(string $phone): string
    {
        $initial = substr($phone, 0, 1);
        switch ($initial) {
            case '+':
                return $phone;

            case '2':
                return '+' . $phone;

            case '0':
                return "+255" . substr($phone, 1);

            default:
                return "+255" . $phone;
        }
    }


   /**
     * Get all of the class subscriptions for the User.
     */
    public function classSubscriptions(): HasMany
    {
        // This links to the ClassSubscription model, using 'user_id'
        return $this->hasMany(ClassSubscription::class, 'user_id');
    }

    /**
     * Check if user has access to a specific course.
     * Checks in order: individual payments, class subscriptions, general subscriptions
     *
     * @param int $courseId
     * @return bool
     */
    public function hasCourseAccess($courseId): bool
    {
        // 1. Check if course is free (price is 0 or null)
        $course = practical_video_model::find($courseId);
        if ($course && ($course->price == null || $course->price == 0)) {
            return true;
        }

        // 2. Check individual payments (payments_model with video_id = courseId and status = 'settled')
        $hasPaid = $this->payments()
            ->where('video_id', $courseId)
            ->where('status', 'settled')
            ->exists();

        if ($hasPaid) {
            return true;
        }

        // 3. Check class subscriptions
        $classSubscriptions = $this->classSubscriptions()
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->get();

        foreach ($classSubscriptions as $classSub) {
            // Check if this class subscription grants access to the course
            // Either all courses in the class, or specific courses if subscription has course selection
            $courseInClass = practical_video_model::where('id', $courseId)
                ->where('class_id', $classSub->class_id)
                ->exists();

            if ($courseInClass) {
                // Check if subscription has specific courses selected
                $hasSpecificCourses = $classSub->courses()->exists();
                
                if (!$hasSpecificCourses) {
                    // No specific courses selected, so all courses in the class are accessible
                    return true;
                } else {
                    // Check if this specific course is in the subscription
                    if ($classSub->courses()->where('id', $courseId)->exists()) {
                        return true;
                    }
                }
            }
        }

        // 4. Check general subscriptions (all_courses or specific_courses)
        $activeSubscriptions = $this->subscriptions()
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->get();

        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->hasCourseAccess($courseId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all course IDs that the user has access to.
     *
     * @return array
     */
    public function getAccessibleCourseIds(): array
    {
        $accessibleIds = [];

        // Get all free courses
        $freeCourses = practical_video_model::where(function($q) {
            $q->whereNull('price')->orWhere('price', 0);
        })->pluck('id')->toArray();
        $accessibleIds = array_merge($accessibleIds, $freeCourses);

        // Get courses from individual payments
        $paidCourses = $this->payments()
            ->where('status', 'settled')
            ->whereNotNull('video_id')
            ->pluck('video_id')
            ->unique()
            ->toArray();
        $accessibleIds = array_merge($accessibleIds, $paidCourses);

        // Get courses from class subscriptions
        $classSubscriptions = $this->classSubscriptions()
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->get();

        foreach ($classSubscriptions as $classSub) {
            $hasSpecificCourses = $classSub->courses()->exists();
            
            if (!$hasSpecificCourses) {
                // All courses in the class
                $classCourses = practical_video_model::where('class_id', $classSub->class_id)
                    ->pluck('id')
                    ->toArray();
                $accessibleIds = array_merge($accessibleIds, $classCourses);
            } else {
                // Specific courses only
                $specificCourses = $classSub->courses()->pluck('id')->toArray();
                $accessibleIds = array_merge($accessibleIds, $specificCourses);
            }
        }

        // Get courses from general subscriptions
        $activeSubscriptions = $this->subscriptions()
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->get();

        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->subscription_type === 'all_courses') {
                // All courses
                $allCourses = practical_video_model::pluck('id')->toArray();
                $accessibleIds = array_merge($accessibleIds, $allCourses);
            } elseif ($subscription->subscription_type === 'specific_courses') {
                // Specific courses only
                $specificCourses = $subscription->courses()->pluck('id')->toArray();
                $accessibleIds = array_merge($accessibleIds, $specificCourses);
            }
        }

        return array_unique($accessibleIds);
    }

    /**
     * Check if user has an active class subscription for a specific class.
     *
     * @param int $classId
     * @return bool
     */
    public function hasActiveClassSubscription($classId): bool
    {
        return $this->classSubscriptions()
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->exists();
    }

    /**
     * Get active class subscription for a specific class.
     *
     * @param int $classId
     * @return ClassSubscription|null
     */
    public function getActiveClassSubscription($classId)
    {
        return $this->classSubscriptions()
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
    }

}
