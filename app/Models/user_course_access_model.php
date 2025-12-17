<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_course_access_model extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tbl_user_course_access';

    /**
     * Indicates if the model should be timestamped.
     * Laravel's created_at/updated_at are different from your custom ones.
     */
    public $timestamps = false;
    
    /**
     * Get the user that owns the access record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the video that this access record is for.
     */
    public function video()
    {
        return $this->belongsTo(practical_video_model::class, 'video_id');
    }
}
