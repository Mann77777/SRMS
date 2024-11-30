<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->input('role', 'all');
        
        // Get regular users
        $usersQuery = User::query();
        
        // Get technicians from admins table
        $techniciansQuery = Admin::query()->where('role', 'Technician');
        
        if ($role !== 'all') {
            if ($role === 'faculty') {
                $usersQuery->where('role', 'Faculty & Staff');
                $techniciansQuery->whereNull('id'); // Don't include technicians
            } elseif ($role === 'student') {
                $usersQuery->where('role', 'Student');
                $techniciansQuery->whereNull('id'); // Don't include technicians
            } elseif ($role === 'technician') {
                $usersQuery->whereNull('id'); // Don't include regular users
                // techniciansQuery already filtered to Technician role
            }
        }
        
        // Get results
        $users = $usersQuery->get();
        $technicians = $techniciansQuery->get();
        
        // Combine results
        $allUsers = $users->concat($technicians);
        
        if ($request->ajax()) {
            return response()->json([
                'users' => $allUsers
            ]);
        }
        
        return view('admin.user-management', ['users' => $allUsers]);
    }

    public function getUser($id)
    {
        // Try to find user in Users table
        $user = User::find($id);
        
        // If not found in Users table, check Admins table
        if (!$user) {
            $user = Admin::find($id);
        }
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string'
        ]);

        // Try to find user in Users table
        $user = User::find($id);
        $isAdmin = false;
        
        // If not found in Users table, check Admins table
        if (!$user) {
            $user = Admin::find($id);
            $isAdmin = true;
        }
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->name = $request->name;
        if ($isAdmin) {
            $user->username = $request->email;
        } else {
            $user->email = $request->email;
        }
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function toggleStatus($id)
    {
        // Try to find user in Users table
        $user = User::find($id);
        $isAdmin = false;
        
        // If not found in Users table, check Admins table
        if (!$user) {
            $user = Admin::find($id);
            $isAdmin = true;
        }
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Toggle status between 'active' and 'inactive'
        $user->status = ($user->status === 'active' || $user->status === null) ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $user->status
        ]);
    }

    public function resetPassword($id)
    {
        try {
            // Try to find user in Users table
            $user = User::find($id);
            $isAdmin = false;
            
            // If not found in Users table, check Admins table
            if (!$user) {
                $user = Admin::find($id);
                $isAdmin = true;
            }
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Reset password to default
            $defaultPassword = 'SRMS2023';
            $user->password = bcrypt($defaultPassword);
            $user->save();

            return response()->json([
                'message' => 'Password has been reset successfully',
                'default_password' => $defaultPassword
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error resetting password',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}