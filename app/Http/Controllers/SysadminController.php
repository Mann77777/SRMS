<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SysadminController extends Controller
{
    // Show the login form
    public function showAdminLoginForm()
    {
        return view('sysadmin_login'); // Adjust the view path if necessary
    }

    // Handle the login form submission
    public function sysadmin_login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Log credentials for debugging
        \Log::info('Attempting admin/staff login with credentials: ', $credentials);

        // Attempt login using the 'admin' guard
        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            \Log::info('Login attempt successful for user: ' . $user->username . ' with role: ' . $user->role);

            // Check if the logged-in user has the 'Admin' or 'UITC Staff' role
            if ($user->role === 'Admin' || $user->role === 'UITC Staff') {
                \Log::info('Redirecting user ' . $user->username . ' to admin dashboard.');
                // Redirect to the intended staff/admin dashboard
                return redirect()->intended(route('admin.dashboard')); 
            } else {
                // If role is not allowed, log them out and show error
                Auth::guard('admin')->logout();
                \Log::warning('Login successful but role is not Admin or UITC Staff for user: ' . $user->username);
                return redirect()->back()->with('error', 'Access denied. You do not have the required role.');
            }
        }

        \Log::warning('Admin/Staff login failed for username: ' . $credentials['username']);
        return redirect()->back()->with('error', 'Invalid credentials');
    }

    // Show the registration form
    public function showAdminRegisterForm()
    {
        return view('admin.admin_register');
    }

    // Handle the registration form submission
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username|max:255',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'required|in:Admin,Technician',
        ]);

        // Create a new admin with hashed password
        Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Save the normalized role
        ]);

        // Redirect to login with a success message
        return redirect()->route('sysadmin_login')->with('success', 'Admin registered successfully!');
    }

    // Removed showStaffLoginForm and staff_login methods as they are consolidated into sysadmin_login

    // Show the admin dashboard
    public function showAdminDashboard()
    {
        return view('admin.admin_dashboard'); // Make sure this matches your view file name
    }

    public function myProfile()
    {
        // Logic to show the admin profile
        return view('admin.admin_myprofile');
    }

    public function saveAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',  // Add validation for name
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'username.unique' => 'The username has already been taken. Please choose another one.',
            'password.min' => 'The password must be at least 8 characters long.',
        ]);


        // Set the role to "Admin" directly since it's always going to be "Admin"
        $role = 'Admin';

        
        Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $role,  // Directly assigning "Admin"
        ]);

        return redirect()->route('admin.settings')->with('success', 'Administrator added successfully!');
    }
}
