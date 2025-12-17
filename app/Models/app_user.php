<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    $startDate = Carbon::parse($latestSubscription->start_date);
    $endDate = Carbon::parse($latestSubscription->end_date);

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

}
