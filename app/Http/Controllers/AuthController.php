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
            return redirect()->intended('dashboard');
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
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:Student,Faculty & Staff,Admin,Technician',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Auth::login($user);
        return redirect()->intended('dashboard')->with('success', 'Registration successful!');
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
            'role' => 'user', // Assign a default role or however you want to manage roles
        ]);

        // Check if user is inactive before logging in
        if ($user->status === 'inactive' || $user->status === 0) {
            return redirect()->route('login')
                ->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // Log the user in
        Auth::login($user);

        return redirect()->intended('dashboard');
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
