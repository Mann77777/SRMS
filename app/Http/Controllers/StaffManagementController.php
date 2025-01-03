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
        $staff = Admin::where('role', 'Staff')->get();
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

    public function saveEditedStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username,' . $request->input('staff_id'),
            'status' => 'required|in:available,busy,on_leave',
        ]);
             
        $staff = Admin::findOrFail($request->input('staff_id'));
        $staff->name = $request->input('name');
        $staff->username = $request->input('username');
        $staff->status = $request->input('status');
        $staff->save();

        return redirect()->back()->with('success', 'Staff member edited successfully.');
    }

    public function deleteStaff(Request $request)
    {
        try {
            $staffId = $request->input('staff_id');

            $staff = Admin::findOrFail($staffId);
            $staff->delete();

            return response()->json(['success' => true, 'message' => 'Staff deleted successfully.']);
        } catch (\Exception $e) {
            Log::error("Error deleting staff member: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete staff member.'], 500);
        }
    }
}