<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffManagementController extends Controller
{
  public function index()
    {
       $staff = Staff::all();
        return view('admin.staff-management', ['staff' => $staff]);
    }

    public function saveNewStaff(Request $request)
    {
      $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|unique:staff,username|max:255',
        'password' => 'required|string|confirmed|min:8',
    ]);

         Staff::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'availability_status' => 'available',
         ]);

          return redirect()->back()->with('success', 'Staff member added successfully.');
    }

     public function saveEditedStaff(Request $request)
     {
         $request->validate([
          'name' => 'required|string|max:255',
          'username' => 'required|string|max:255|unique:staff,username,' . $request->input('staff_id'),
           'availability_status' => 'required|in:available,busy,on_leave',
          ]);
             
         $staff = Staff::findOrFail($request->input('staff_id'));
         $staff->name = $request->input('name');
         $staff->username = $request->input('username');
          $staff->availability_status = $request->input('availability_status');
          $staff->save();

          return redirect()->back()->with('success', 'Staff member edited successfully.');
     }

     public function deleteStaff(Request $request)
    {
          try {
            $staffId = $request->input('staff_id');

            $staff = Staff::findOrFail($staffId);

            $staff->delete();

          return response()->json(['success' => true, 'message' => 'Staff deleted successfully.']);
            } catch (\Exception $e) {
            Log::error("Error deleting staff member: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete staff member.'], 500);
            }
    }

}