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
    // Removed constructor applying guest middleware, rely on route definition

    public function loginWithGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle()
    {
        try {
            // Remove stateless() to use default session-based handling
            $user = Socialite::driver('google')->user(); 
            
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
            // Split name from Google into first and last name
            $nameParts = explode(' ', $user->getName(), 2); // Limit to 2 parts
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? ''; // Use the second part as last name, or empty if not present

            // Generate username: first initial of first name + last name (lowercase)
            // Ensure lastName is not empty before generating username to avoid errors
            $generatedUsername = !empty($lastName) ? strtolower(substr($firstName, 0, 1) . $lastName) : strtolower($firstName);
            // Handle cases where username might still be empty if firstName was also empty (unlikely with Google)
            if (empty($generatedUsername)) {
                 $generatedUsername = strtolower(Str::random(8)); // Fallback username
            }
                        
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
                // Update existing user: include first_name and last_name
                $existingUser->update([
                    'google_id' => $user->getId(),
                    'first_name' => $firstName, // Update first_name
                    'last_name' => $lastName,   // Update last_name
                    'username' => $generatedUsername, // Update username as well
                    // Do NOT update 'name' field anymore if it's deprecated/removed
                ]);
                $user = $existingUser; // Use the updated existing user
            } else {
                // Create new user with pending verification, using first_name and last_name
                $user = User::create([
                    'email' => $email,
                    'first_name' => $firstName, // Use first_name
                    'last_name' => $lastName,   // Use last_name
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
