<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function loginWithGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
            
            // Check if the email domain is @tup.edu.ph
            if (!str_ends_with($user->getEmail(), '@tup.edu.ph')) {
                return redirect()->route('login')->with('error', 'Only TUP email addresses are allowed.');
            }
    
            // Determine the user role based on email pattern
            $email = $user->getEmail();
            if (strpos($email, '.') !== false && strpos($email, '_') === false) {
                $role = 'Student';
            } elseif (strpos($email, '_') !== false && strpos($email, '.') === false) {
                $role = 'Faculty & Staff';
            } else {
                $role = 'User'; // Default role if pattern doesn't match
            }
    
            // Check if user exists
            $is_user = User::where('email', $email)->first();
    
            if (!$is_user) {
                // Create a new user if not exists
                $saveUser = User::updateOrCreate(
                    [
                        'google_id' => $user->getId()
                    ],
                    [
                        'username' => $user->getName(),
                        'name' => $user->getName(),
                        'email' => $email,
                        'password' => Hash::make($user->getName() . '@' . $user->getId()),
                        'role' => $role, // Assign the determined role
                    ]
                );
            } else {
                // Check if user is inactive
                if ($is_user->status === 'inactive' || $is_user->status === 0) {
                    return redirect()->route('login')->with('error', 'Your account is inactive. Please contact the administrator.');
                }
                
                // Update existing user
                $is_user->update([
                    'google_id' => $user->getId(),
                    'role' => $role, // Update role if necessary
                ]);
                $saveUser = $is_user;
            }
    
            // Log the user in
            Auth::loginUsingId($saveUser->id);
            return redirect('/dashboard');
    
        } catch (\Throwable $th) {
            return redirect()->route('login')->with('error', 'There was an error logging in with Google. Please try again.');
        }
    }
}    