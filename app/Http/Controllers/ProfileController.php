<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Method to show the user profile
    public function show()
    {
        $user = Auth::user(); // Get the authenticated user
        return view('home', compact('user')); // Return the home view with user data
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . Auth::id(),
            'phone' => 'required|string|max:15',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('dashboard.blade.php'); // Redirect to the dashboard page
    }
}
