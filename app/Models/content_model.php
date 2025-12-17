<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class content_model extends Model
{
    use HasFactory;

    protected $table = 'tbl_content';

    protected $fillable = [
        'page_no','contents'

    ];

    protected $casts = [
        'contents' => 'array'

    ];
}
