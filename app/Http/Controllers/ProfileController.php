<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    // Method to show the user profile
    public function show()
    {
        $user = Auth::user(); // Get the authenticated user
        return view('home', compact('user')); // Return the home view with user data
    }

    // Method to update user details
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

        return redirect()->route('dashboard'); // Redirect to the dashboard page
    }

    // Method to upload profile image
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete existing profile image if it exists
        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
        }

        // Store new profile image
        $path = $request->file('profile_image')->store('profile_images', 'public');
        $user->profile_image = $path;
        $user->save();

        return redirect()->back()->with('success', 'Profile image updated successfully.');
    }

    // Method to remove profile image
    public function removeProfileImage()
    {
        $user = Auth::user();

        // Delete profile image if it exists
        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
            $user->profile_image = null;
            $user->save();
        }

        return redirect()->back()->with('success', 'Profile image removed successfully.');
    }
}
