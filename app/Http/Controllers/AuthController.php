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
            'first_name' => 'required|string|max:255', // Changed from name
            'last_name' => 'required|string|max:255',  // Added last_name
            'username' => 'required|string|max:255|unique:users|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Student,Faculty & Staff', // Add role selection
        ]);
    
        $user = User::create([
            'first_name' => $validatedData['first_name'], // Changed from name
            'last_name' => $validatedData['last_name'],   // Added last_name
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

    // Removed redundant Google auth methods (handled by GoogleController)
    // public function redirectToGoogle() { ... }
    // public function handleGoogleCallback() { ... }

    // Handle the logout process
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Explicitly forget the session cookie in the response
        $response = redirect('/login'); // Redirect to your login or home page
        return $response->withCookie(cookie()->forget(session()->getName()));
    }

    /**
     * Show the form for the user to select their role.
     */
    public function showSelectRoleForm()
    {
        // Ensure the user is authenticated and their role is not already set
        if (!Auth::check() || !is_null(Auth::user()->role)) {
            // If role is already set or user not logged in, redirect away
            return redirect()->route('users.dashboard');
        }

        return view('auth.select-role'); // Assuming the view is in resources/views/auth/select-role.blade.php
    }

    /**
     * Store the selected role for the user.
     */
    public function storeSelectedRole(Request $request)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Double-check if the role is already set
        if (!is_null($user->role)) {
            return redirect()->route('users.dashboard')->with('info', 'Your role is already set.');
        }

        // Validate the selected role
        $validatedData = $request->validate([
            'role' => 'required|in:Student,Faculty & Staff',
        ]);

        // Update the user's role
        $user->role = $validatedData['role'];
        $user->save();

        // Redirect based on the newly set role
        if ($user->role === 'Student') {
            // Redirect to student details form if needed, otherwise dashboard
            if (!$user->student_id || !$user->course) {
                 return redirect()->route('student.details.form')->with('success', 'Role selected successfully! Please complete your details.');
            }
        }
        
        // For Faculty & Staff or Students with details already filled
        return redirect()->route('users.dashboard')->with('success', 'Role selected successfully!');
    }
}
