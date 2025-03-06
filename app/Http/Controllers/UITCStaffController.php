<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this import

class UITCStaffController extends Controller
{
    public function getAssignedRequests(Request $request) // Add Request parameter
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
    
            // Fetch requests assigned to this UITC staff member with detailed information
            $query = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->join('users', 'student_service_requests.user_id', '=', 'users.id')
                ->select(
                    'student_service_requests.*', 
                    'users.name as requester_name', 
                    'users.role as user_role',
                    'users.email as requester_email',
                    
                );
    
            // Add filtering options
            if ($request->has('status')) {
                $query->where('student_service_requests.status', $request->input('status'));
            }
    
            if ($request->has('transaction_type')) {
                $query->where('transaction_type', $request->input('transaction_type'));
            }
    
            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
    
            // Paginate results
            $assignedRequests = $query->paginate(10);
    
            // If it's an AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $assignedRequests
                ]);
            }
    
            // Return view with assigned requests
            return view('uitc_staff.assign-request', [
                'assignedRequests' => $assignedRequests,
                'totalRequests' => $assignedRequests->total(),
                'currentPage' => $assignedRequests->currentPage()
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching assigned requests: ' . $e->getMessage(), [
                'staff_id' => $uitcStaffId ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
    
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch assigned requests',
                    'error' => $e->getMessage()
                ], 500);
            }
    
            // For non-AJAX requests, redirect with error
            return redirect()->back()->with('error', 'Unable to fetch assigned requests');
        }
    }


    public function completeRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required|exists:student_service_requests,id',
            'actions_taken' => 'nullable|string|max:1000',
            'completion_report' => 'nullable|string|max:2000',
            'completion_status' => 'required|in:fully_completed,partially_completed,requires_follow_up'
        ]);

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Find the request
            $serviceRequest = StudentServiceRequest::findOrFail($request->request_id);

            /* Additional validation to prevent automatic completion
            if (!$request->has('actions_taken') || !$request->has('completion_report')) {
                return response()->json([
                    'message' => 'Cannot complete request without documenting actions and report',
                    'error' => 'Incomplete request details'
                ], 400);
            } */

            // Ensure the request is assigned to the current UITC staff
            $currentStaffId = Auth::guard('admin')->user()->id;
            if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                return response()->json([
                    'message' => 'Unauthorized to complete this request',
                    'error' => 'Request not assigned to current staff'
                ], 403);
            }

            // Update the request status and add completion details
            $serviceRequest->update([
                'status' => 'Completed', // Explicitly set status to Completed
                'admin_notes' => $request->completion_report ?? 'Request completed',
                'completion_status' => $request->completion_status,
                'actions_taken' => $request->actions_taken ?? 'Standard request completion'
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

            // Log the error
            Log::error('Error completing request: ' . $e->getMessage(), [
                'request_id' => $request->request_id,
                'staff_id' => $currentStaffId ?? 'Unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to complete request: ' . $e->getMessage()
            ], 500);
        }
    }
}