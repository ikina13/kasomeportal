<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AncientScope;
use App\Models\Scopes\CancelScope;
use App\Models\Scopes\PendingScope;

class cancel_bill_model extends Model
{
    use HasFactory;

    protected $table = 'tbl_compiled_bill';

    /**
     * The id associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bill_id';

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

    protected static function booted()
    {
        
        static::addGlobalScope(new CancelScope);
    }
}
