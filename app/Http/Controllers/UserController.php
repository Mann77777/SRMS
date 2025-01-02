<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = strtolower($request->input('role', 'all'));
        
        // Get regular users, explicitly excluding technicians
        $usersQuery = User::query()->whereNotIn('role', ['Technician']);
        
        // Completely remove technicians from the query
        $techniciansQuery = Admin::query()->where('id', null);
        
        if ($role !== 'all') {
            if ($role === 'faculty') {
                $usersQuery->where('role', 'Faculty & Staff');
            } elseif ($role === 'student') {
                $usersQuery->where('role', 'Student');
            }
        }
        
        // Get results
        $users = $usersQuery->get();
        $technicians = $techniciansQuery->get();
        
        // Combine results without transforming to array
        $allUsers = $users->concat($technicians);
        
        if ($request->ajax()) {
            return response()->json([
                'users' => $allUsers
            ]);
        }
        
        return view('admin.user-management', ['users' => $allUsers]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|in:Student,Faculty & Staff',
                'college' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
                'course' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
                'student_id' => $request->role === 'Student' ? 'required|string|unique:users' : 'nullable|string',
                'year_level' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'active',
                'email_verified_at' => now(), // Since admin is creating the account
                'verification_status' => 'verified',
                'admin_verified' => true,
                'college' => $request->role === 'Student' ? $request->college : null,
                'course' => $request->role === 'Student' ? $request->course : null,
                'student_id' => $request->role === 'Student' ? $request->student_id : null,
                'year_level' => $request->role === 'Student' ? $request->year_level : null,
                'admin_verification_notes' => 'User created directly by admin ',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('User Creation Error: ' . $e->getMessage());
            \Log::error('Request Data: ' . json_encode($request->all()));
            return response()->json([
                'success' => false,
                'error' => 'Error creating user: ' . $e->getMessage(),
                'details' => $request->all()
            ], 500);
        }
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

    
    public function bulkDelete(Request $request)
    {
        try {
            $userIds = $request->input('users', []);
            
            if (empty($userIds)) {
                return response()->json(['error' => 'No users selected'], 400);
            }

            $deletedCount = 0;
            $errors = [];

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
                        $deletedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Failed to delete user {$id}: {$e->getMessage()}";
                    }
                } else {
                    $errors[] = "User {$id} not found";
                }
            }

            $message = "{$deletedCount} users deleted successfully";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(", ", $errors);
            }

            return response()->json([
                'message' => $message,
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete users: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|string|in:Student,Faculty & Staff',
            'college' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
            'course' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
            'student_id' => $request->role === 'Student' ? 'required|string|unique:users,student_id,'.$user->id : 'nullable|string',
            'year_level' => $request->role === 'Student' ? 'required|string' : 'nullable|string',
        ]);
    
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'college' => $request->role === 'Student' ? $request->college : null,
            'course' => $request->role === 'Student' ? $request->course : null,
            'student_id' => $request->role === 'Student' ? $request->student_id : null,
            'year_level' => $request->role === 'Student' ? $request->year_level : null,
            'admin_verification_notes' => 'User updated by admin',
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
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
            'success' => true,
            'message' => "User status has been changed to {$user->status}",
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
            $defaultPassword = 'SRMS2024';
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

    public function deleteUser($id)
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

            // Delete the user
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPendingVerifications()
    {
        $pendingUsers = User::where('verification_status', 'pending_admin')
            ->where('role', 'Student')
            ->get();
            
        return view('admin.verify-students', ['students' => $pendingUsers]);
    }

    public function verifyStudent(Request $request, $id)
    {
        try {
            $request->validate([
                'decision' => 'required|in:approve,reject',
                'notes' => 'required_if:decision,reject|string|nullable'
            ]);

            // Try to find user in Users table
            $user = User::find($id);
            
            // If not found in Users table, check Admins table
            if (!$user) {
                $user = Admin::find($id);
            }
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
            }
            
            if ($request->decision === 'approve') {
                $user->update([
                    'admin_verified' => true,
                    'verification_status' => 'verified',
                    'status' => 'active',
                    'admin_verification_notes' => $request->notes ?? 'Account verified by admin'
                ]);
                $message = 'User account has been verified and activated successfully';
            } else {
                if (empty($request->notes)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Rejection notes are required when rejecting a verification'
                    ], 422);
                }
                
                $user->update([
                    'admin_verified' => false,
                    'verification_status' => 'rejected',
                    'status' => 'inactive',
                    'admin_verification_notes' => $request->notes
                ]);
                $message = 'User account verification has been rejected';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Error verifying user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error processing verification. Please try again.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudentDetails($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
    

    public function verifyFacultyStaff(Request $request, $userId)
    {
        \Log::info('Verification Request Data:', [
            'userId' => $userId,
            'decision' => $request->input('decision'),
            'notes' => $request->input('notes')
        ]);
    
        try {
            $user = User::findOrFail($userId);
            
            // Ensure only Faculty & Staff can be verified
            if ($user->role !== 'Faculty & Staff') {
                return response()->json([
                    'success' => false, 
                    'error' => 'Only Faculty & Staff can be verified'
                ], 400);
            }
    
            $decision = $request->input('decision');
            $notes = $request->input('notes', null);
        
            if ($decision === 'reject' && empty($notes)) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Rejection notes are required'
                ], 400);
            }
        
            if ($decision === 'approve') {
                $user->admin_verified = 1;
                $user->verification_status = 'verified';
                $user->admin_verification_notes = 'Account verified by admin';
            } else {
                $user->admin_verified = 0;
                $user->verification_status = 'rejected';
                $user->admin_verification_notes = $notes;
            }
            
            $user->save();
        
            return response()->json([
                'success' => true,
                'message' => 'Faculty & Staff verification updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getFacultyStaffDetails($id)
    {
        try {
            $user = User::where('role', 'Faculty & Staff')
                        ->where('id', $id)
                        ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'verification_status' => $user->verification_status ?? 'Pending'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty/Staff user not found.'
            ], 404);
        }
    }
}