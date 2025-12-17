<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\class_model as ClassModel;

class ClassSubscription extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_class_subscriptions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'class_id',
        'amount',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the class that this subscription belongs to.
     */
    public function class(): BelongsTo
    {
        // Assumes you have a 'Classe' model for 'tbl_class'
        // Use 'App\Models\Class' if that is your model name
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the user that this subscription belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the courses associated with this class subscription.
     */
    public function courses()
    {
        return $this->belongsToMany(
            practical_video_model::class,
            'tbl_class_subscription_courses',
            'class_subscription_id',
            'course_id'
        );
    }
}
