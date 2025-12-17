<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackController extends Controller
{
    //
     public function __construct()
    {
    $this->middleware('web'); // Adjust as needed
    }
    //
    public function payurl()
    {
        return response()->file(public_path('backurl.php'));
    }
}
