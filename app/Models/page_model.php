<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class page_model extends Model{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_notes_pages';

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
     * Get the more details for notes.
     */
    public function notes()
    {
        return $this->belongsTo('App\Models\notes_model', 'notes_id');
    }

    /**
     * Get the paragraphs for the  page.
     */
    public function paragraphs()
    {
        return $this->hasMany('App\Models\paragraph_model', 'page_id');
    }


    /**
     * Get the headings for the  page.
     */
    public function headings()
    {
        return $this->hasMany('App\Models\heading_model', 'page_id');
    }

    /**
     * Get the images for the  page.
     */
    public function images()
    {
        return $this->hasMany('App\Models\image_model', 'page_id');
    }


}

