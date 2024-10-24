<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function index()
    {
        return view('users.aboutus'); // Assuming you have a view file named 'about.blade.php'
    }
}
