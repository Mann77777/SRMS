<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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


    public function completeRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required|exists:student_service_requests,id',
            'actions_taken' => 'required|string|max:1000',
            'completion_report' => 'required|string|max:2000',
            'completion_status' => 'required|in:fully_completed,partially_completed,requires_follow_up'
        ]);

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Find the request
            $serviceRequest = StudentServiceRequest::findOrFail($request->request_id);

            // Update the request status and add completion details
            $serviceRequest->update([
                'status' => 'Completed', // Explicitly set status to Completed
                'admin_notes' => $request->completion_report,
                'completion_status' => $request->completion_status,
                'actions_taken' => $request->actions_taken
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Request completed successfully',
                'request' => $serviceRequest
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to complete request: ' . $e->getMessage()
            ], 500);
        }
    }
}