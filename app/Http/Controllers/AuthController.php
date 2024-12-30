<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('login');
    }

    // Handle login process
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // First check if the user exists
        $user = User::where('username', $request->username)->first();

        // Check if user exists and is inactive (using strict comparison)
        if ($user && ($user->status === 'inactive' || $user->status === 0)) {
            return back()->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            // Double-check status after authentication
            if (Auth::user()->status === 'inactive' || Auth::user()->status === 0) {
                Auth::logout();
                return back()->with('error', 'Your account is inactive. Please contact the administrator.');
            }
            // Check if email is verified
           /* if (!Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            // Check if student details are filled
            if (Auth::user()->role === 'Student' && (!Auth::user()->student_id || !Auth::user()->course)) {
                return redirect()->route('student.details.form');
            }*/
            return redirect()->route('users.dashboard');
        }

        return back()->with('error', 'Invalid username or password.');
    }

    // Show the registration form
    public function showRegisterForm()
    {
        return view('users.register');
    }

    // Handle the registration process
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Student,Faculty & Staff', // Add role selection
        ]);
    
        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);
    
        // Send verification email
        $user->sendEmailVerificationNotification();
    
        // Log the user in
        Auth::login($user);
    
        // Redirect to verification notice
        return redirect()->route('verification.notice')
            ->with('message', 'Registration successful! Please verify your email.');
    }

    // Redirect to Google for authentication
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle the callback from Google
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Find or create the user
        $user = User::firstOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'username' => $googleUser->getName(),
            'name' => $googleUser->getName(),
            'password' => Hash::make(uniqid()), // Generate a random password
            'role' => 'Student', // Default role for Google login
            'email_verified_at' => now(), // Google accounts are pre-verified
        ]);

        // Check if user is inactive before logging in
        if ($user->status === 'inactive' || $user->status === 0) {
            return redirect()->route('login')
                ->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // Log the user in
        Auth::login($user);

        // If student details are not filled, redirect to details form
        if ($user->role === 'Student' && (!$user->student_id || !$user->course)) {
            return redirect()->route('student.details.form');
        }

        return redirect()->route('users.dashboard');
    }

    // Handle the logout process
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // Redirect to your login or home page
    }
}