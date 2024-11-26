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
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        
        // Query users table
        $usersQuery = User::query();
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
                
            }
        }

        // Apply status filter
        if ($status !== 'all') {
            $usersQuery->where('status', $status);
            $techniciansQuery->where('status', $status);
        }
        
        // Apply search if provided
        if ($search) {
            $searchLower = strtolower($search);
            $usersQuery->where(function($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"]);
            });
            
            $techniciansQuery->where(function($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"]);
            });
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

    // Function to delete user
    public function deleteUser($id)
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

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Bulk delete users
    public function bulkDelete(Request $request)
    {
        $userIds = $request->input('ids');
        $successCount = 0;
        $errorCount = 0;

        foreach ($userIds as $id) {
            // Try to find user in Users table
            $user = User::find($id);
            $isAdmin = false;
            
            // If not found in Users table, check Admins table
            if (!$user) {
                $user = Admin::find($id);
                $isAdmin = true;
            }
            
            if ($user) {
                try {
                    $user->delete();
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        return response()->json([
            'message' => "$successCount users deleted successfully" . 
                        ($errorCount > 0 ? ", $errorCount deletions failed" : "")
        ]);
    }

    // Export users to CSV
    public function exportCSV(Request $request)
    {
        // Get filtered users based on search, role, and status
        $role = $request->input('role', 'all');
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        
        // Query users table
        $usersQuery = User::query();
        $techniciansQuery = Admin::query()->where('role', 'Technician');
        
        // Apply role filter
        if ($role !== 'all') {
            if ($role === 'faculty') {
                $usersQuery->where('role', 'Faculty & Staff');
                $techniciansQuery->whereNull('id');
            } elseif ($role === 'student') {
                $usersQuery->where('role', 'Student');
                $techniciansQuery->whereNull('id');
            } elseif ($role === 'technician') {
                $usersQuery->whereNull('id');
            }
        }

        // Apply status filter
        if ($status !== 'all') {
            $usersQuery->where('status', $status);
            $techniciansQuery->where('status', $status);
        }
        
        // Apply search
        if ($search) {
            $searchLower = strtolower($search);
            $usersQuery->where(function($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"]);
            });
            
            $techniciansQuery->where(function($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $users = $usersQuery->get();
        $technicians = $techniciansQuery->get();
        $allUsers = $users->concat($technicians);

        // Create CSV
        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Name', 'Username', 'Email', 'Role', 'Status', 'Created At'];

        $callback = function() use ($allUsers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($allUsers as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->username,
                    $user->email ?? $user->username,
                    $user->role,
                    $user->status ?? 'Active',
                    $user->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
