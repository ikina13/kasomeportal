<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notes_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_notes';

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
     * Get the pages for the  notes.
     */
    public function pages()
    {
        return $this->hasMany('App\Model\page_model', 'notes_id')->orderBy('id');
    }


    /**
     * Get the module that owns the notes.
     */
    public function module()
    {
        return $this->belongsTo('App\Model\module_model', 'module_id');
    }



}
