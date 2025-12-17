<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class region_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_regions';

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
     * Get the bookings for the date.
     */
    public function users()
    {
        return $this->hasMany('App\Model\User', 'region_id');
    }

    /**
     * Get the districts for the region.
     */
    public function districts()
    {
        return $this->hasMany('App\Model\district_model', 'region_id');
    }




}
