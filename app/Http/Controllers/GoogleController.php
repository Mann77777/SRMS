<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
                return redirect()->route('login')
                    ->with('error', 'Only TUP email addresses are allowed.');
            }
    
            // Determine user role based on email pattern
            $email = $user->getEmail();
            if (strpos($email, '.') !== false && strpos($email, '_') === false) {
                $role = 'Student';
            } elseif (strpos($email, '_') !== false && strpos($email, '.') === false) {
                $role = 'Faculty & Staff';
            } else {
                return redirect()->route('login')
                    ->with('error', 'Invalid email format.');
            }

            // Generate username based on first initial and last name
            $nameParts = explode(' ', $user->getName());
            $firstName = $nameParts[0] ?? '';
            $lastName = end($nameParts) ?? '';
                        
            // Generate username: first initial of first name + last name (lowercase)
            $generatedUsername = strtolower(substr($firstName, 0, 1) . $lastName);
                        
            // Ensure username is unique
            $baseUsername = $generatedUsername;
            $counter = 1;
                while (User::where('username', $generatedUsername)->exists()) {
                    $generatedUsername = $baseUsername . $counter;
                    $counter++;
            }
            
    
            // Find or create user
            $existingUser = User::where('email', $email)->first();
            
            if ($existingUser) {
                // Update only Google-related fields for existing users
                $existingUser->update([
                    'google_id' => $user->getId(),
                    'name' => $user->getName(),
                    'username' => $generatedUsername,
                ]);
                $user = $existingUser;
            } else {
                // Create new user with pending verification
                $user = User::create([
                    'email' => $email,
                    'name' => $user->getName(),
                    'username' => $generatedUsername,
                    'google_id' => $user->getId(),
                    'role' => $role,
                    'password' => Hash::make(Str::random(16)),
                    'verification_status' => 'pending_admin',
                    'admin_verified' => false,
                    'status' => 'active'
                ]);
            }
    
            Auth::login($user);
    
            // If email not verified, send verification email
            if (!$user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                    return redirect()->route('verification.notice')
                        ->with('message', 'Please verify your email address to continue.');
                } catch (\Exception $e) {
                    \Log::error('Email verification error: ' . $e->getMessage());
                    return redirect()->route('login')
                        ->with('error', 'Failed to send verification email: ' . $e->getMessage());
                }
            }
            
            // If email verified but student details not submitted
            if ($role === 'Student' && empty($user->student_id)) {
                return redirect()->route('student.details.form')
                    ->with('message', 'Please complete your student details.');
            }
            
            // If pending admin verification
            if ($user->verification_status === 'pending_admin') {
                return redirect()->route('login')
                    ->with('message', 'Your account is pending admin verification.');
            }
            
            // Only allow access if account is active and verified
            if ($user->status === 'active' && $user->verification_status === 'verified') {
                return redirect()->route('users.dashboard');
            }
            
            // Default redirect for other cases
            return redirect()->route('login')
                ->with('message', 'Please complete the verification process to access your account.');
    
        } catch (\Throwable $th) {
            \Log::error('Google authentication error: ' . $th->getMessage());
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $th->getMessage());
        }
    }
}    