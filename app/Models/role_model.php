<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role_model extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_roles';

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
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Models\permission_model',  'tbl_role_permissions', 'role_id', 'permission_id');
    }

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User','permission_ids');
    }

}
