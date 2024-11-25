<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function index()
    {
        // Fetch all users
        $users = User::all();

        // Pass users data to the view
        return view('admin.user-management', compact('users'));
    }
}
