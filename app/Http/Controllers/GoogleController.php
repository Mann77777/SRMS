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
    
            // After validating the domain, check if the user exists
            $is_user = User::where('email', $user->getEmail())->first();
    
            if (!$is_user) {
                // Create a new user if not exists
                $saveUser = User::updateOrCreate(
                    [
                        'google_id' => $user->getId()
                    ],
                    [
                        'username' => $user->getName(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'password' => Hash::make($user->getName() . '@' . $user->getId()),
                    ]
                );
            } else {
                // Update existing user
                $saveUser = User::where('email', $user->getEmail())->update([
                    'google_id' => $user->getId(),
                ]);
                $saveUser = User::where('email', $user->getEmail())->first();
            }
    
            // Log the user in
            Auth::loginUsingId($saveUser->id);
            return redirect()->route('home');
    
        } catch (\Throwable $th) {
            return redirect()->route('login')->with('error', 'There was an error logging in with Google. Please try again.');
        }
    }
}