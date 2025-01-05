<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Auth;

class UITCStaffController extends Controller
{
    public function getAssignedRequests()
    {
        // Get the currently logged-in UITC staff member's ID
        $uitcStaffId = Auth::guard('admin')->user()->id;

        // Fetch requests assigned to this UITC staff member with user role
        $assignedRequests = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
            ->join('users', 'student_service_requests.user_id', '=', 'users.id')
            ->select('student_service_requests.*', 'users.role as user_role')
            ->get();

        // Return view with assigned requests
        return view('uitc_staff.assign-request', compact('assignedRequests'));
    }

}