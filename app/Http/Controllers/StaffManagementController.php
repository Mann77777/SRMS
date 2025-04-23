<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StaffManagementController extends Controller
{
    public function index()
    {
        // Fetch only active staff members from the admins table by default
        $staff = Admin::where('role', 'UITC Staff')
            ->where('availability_status', 'active')
            ->get();

        return view('admin.staff-management', [
        'staff' => $staff,
        'showInactive' => false
        ]);
    }

    public function showAll()
    {
        // Fetch all staff members including inactive ones
        $staff = Admin::where('role', 'UITC Staff')->get();
        
        return view('admin.staff-management', [
            'staff' => $staff,
            'showInactive' => true
        ]);
    }

    public function saveNewStaff(Request $request)
    {
      $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|unique:admins,username|max:255',
        'password' => 'required|string|confirmed|min:8',
      ]);

      Admin::create([
          'name' => $request->name,
          'username' => $request->username,
          'password' => Hash::make($request->password),
          'role' => 'UITC Staff', // Explicitly set role as UITC Staff
          'availability_status' => 'active', // Set as active by default
      ]);

      return redirect()->back()->with('success', 'UITC Staff member added successfully.');
    }

    public function saveEditedStaff(Request $request, $id)
    {
        $staff = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username,' . $id,
            'profile_image' => 'nullable|image|max:2048', // Optional image upload
            'availability_status' => 'required|in:active,inactive', // Validate status
        ]);
            
        $staff->name = $request->input('name');
        $staff->username = $request->input('username');
        $staff->availability_status = $request->input('availability_status');

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($staff->profile_image) {
                Storage::delete('public/' . $staff->profile_image);
            }

            // Store new image
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $staff->profile_image = $imagePath;
        }

        $staff->save();

        return redirect()->back()->with('success', 'Staff member updated successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $staff = Admin::findOrFail($id);
            
            // Toggle the availability status
            $staff->availability_status = ($staff->availability_status === 'active') ? 'inactive' : 'active';
            $staff->save();
            
            $statusMessage = ($staff->availability_status === 'active') ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true, 
                'message' => "Staff member {$statusMessage} successfully.",
                'new_status' => $staff->availability_status
            ]);
        } catch (\Exception $e) {
            Log::error("Error changing staff status: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to change staff status.'
            ], 500);
        }
    }

    public function deleteStaff(Request $request, $id)
    {
        try {
            $staff = Admin::findOrFail($id);
            
            // Delete profile image if exists
            if ($staff->profile_image) {
                Storage::delete('public/' . $staff->profile_image);
            }
            
            $staff->delete();

            return response()->json([
                'success' => true, 
                'message' => 'UITC Staff permanently deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting staff member: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete UITC Staff.'
            ], 500);
        }
    }
}