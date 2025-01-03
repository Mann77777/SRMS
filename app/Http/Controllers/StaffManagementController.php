<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffManagementController extends Controller
{
    public function index()
    {
        // Fetch only staff members from the admins table
        $staff = Admin::where('role', 'UITC Staff')->get();
        return view('admin.staff-management', ['staff' => $staff]);
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
          'availability_status' => 'available', // Always set to available when added via Staff Management
      ]);

      return redirect()->back()->with('success', 'UITC Staff member added successfully.');
    }

    public function saveEditedStaff(Request $request, $id)
{
    $staff = Admin::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:admins,username,' . $id,
        'availability_status' => 'required|in:available,busy,on_leave',
        'profile_image' => 'nullable|image|max:2048', // Optional image upload
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

    public function deleteStaff(Request $request)
    {
        try {
            $staffId = $request->input('staff_id');

            $staff = Admin::findOrFail($staffId);
            $staff->delete();

            return response()->json(['success' => true, 'message' => 'UITC Staff deleted successfully.']);
        } catch (\Exception $e) {
            Log::error("Error deleting staff member: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete UITC Staff.'], 500);
        }
    }
}