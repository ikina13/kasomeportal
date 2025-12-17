<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class module_objective_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_module_objectives';

    /**
     * The id associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['updated_at','deleted_at'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * Get the module that owns the objectives.
     */
    public function module()
    {
        return $this->belongsTo('App\Models\module_model', 'module_id');
    }

    /**
     * Get the objectives for the module.
     */
    public function objectives()
    {
        return $this->hasMany('App\Models\objective_model', 'objective_id')->orderBy('id');
    }

    /**
     * Get the objectives record associated with the module.
     */
    public function image()
    {
        return $this->hasOne('App\Models\image_model', 'objective_id');
    }



}
