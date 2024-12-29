<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    // Method to show the user profile
    public function show()
    {
        // Check if user is admin or regular user
        $user = auth('admin')->check() ? auth('admin')->user() : auth()->user();

        // Return the appropriate view based on user type
        if (auth('admin')->check()) {
            return view('admin.admin_myprofile', compact('user'));
        }
        return view('users.myprofile', compact('user'));
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

    // Update username
    public function updateUsername(Request $request)
    {
        // Validate the request data
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
        ]);
        
        $user = auth()->user();
        $user->username = $request->username;
        $user->save();
        
        // Return with success message for username update
        return response()->json(['success' => true]);
    }

    // Method to upload profile image
    public function uploadProfileImage(Request $request)
    { 
        $request->validate([
        'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Determine the user based on the guard
        $user = auth('admin')->check() ? auth('admin')->user() : auth()->user();

        // Delete existing profile image if it exists
        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
        }

        // Store new profile image
        $path = $request->file('profile_image')->store('profile_images', 'public');
        $user->profile_image = $path;
        $user->save();

        // Add debugging log
        \Log::info('Profile Image Uploaded', [
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'image_path' => $path,
            'full_storage_path' => storage_path('app/public/' . $path)
        ]);

        return redirect()->back()->with('upload_success', true);
    }
    
    // Method to remove profile image
    public function removeProfileImage(Request $request)
    {
        // Determine the user based on the guard
        $user = auth('admin')->check() ? auth('admin')->user() : auth()->user();

        // Delete profile image if it exists
        if ($user->profile_image) {
            // Add detailed logging
            \Log::info('Attempting to remove profile image', [
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'current_image_path' => $user->profile_image,
                'storage_path' => 'public/' . $user->profile_image
            ]);

            try {
                // Delete from storage
                if (Storage::exists('public/' . $user->profile_image)) {
                    Storage::delete('public/' . $user->profile_image);
                    
                    // Set profile image to null
                    $user->profile_image = null;
                    $user->save();

                    \Log::info('Profile image successfully removed', [
                        'user_id' => $user->id,
                        'user_type' => get_class($user)
                    ]);

                    // Redirect back and trigger the modal
                    return redirect()->back()->with('image_removed', true);
                } else {
                    \Log::warning('Profile image file does not exist', [
                        'user_id' => $user->id,
                        'user_type' => get_class($user),
                        'image_path' => $user->profile_image
                    ]);

                    // If file doesn't exist, just update the database
                    $user->profile_image = null;
                    $user->save();

                    return redirect()->back()->with('image_removed', true);
                }
            } catch (\Exception $e) {
                // Log any errors
                \Log::error('Error removing profile image', [
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'error_message' => $e->getMessage()
                ]);

                return redirect()->back()->withErrors(['error' => 'Failed to remove profile image.']);
            }
        }
        
        return redirect()->back()->withErrors(['error' => 'No profile image to remove.']);
    }

        
    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ], [
            'password.confirmed' => 'The password and confirmation password do not match.',
            'password.min' => 'The password must be at least 8 characters long.',
        ]);

        $user = auth()->user();
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->back()->with('password_changed', true);
    }
}
