<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\User;
use App\Notifications\ServiceRequestCompleted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class UITCStaffController extends Controller
{
    public function getAssignedRequests(Request $request)
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Initialize collection for all requests
            $assignedRequests = collect();
            
            // 1. Fetch Student service requests assigned to this UITC staff member
            $studentQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'student_service_requests.user_id', '=', 'users.id')
                ->select(
                    'student_service_requests.*',
                    'users.name as requester_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'student' as request_type")
                );
                
            // 2. Fetch Faculty service requests assigned to this UITC staff member
            $facultyQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'faculty_service_requests.user_id', '=', 'users.id')
                ->select(
                    'faculty_service_requests.*',
                    'users.name as requester_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'faculty' as request_type")
                );
            
            // Add filtering options (apply to both queries)
            if ($request->has('status') && $request->input('status') !== 'all') {
                $studentQuery->where('student_service_requests.status', $request->input('status'));
                $facultyQuery->where('faculty_service_requests.status', $request->input('status'));
            }
            
            if ($request->has('transaction_type') && $request->input('transaction_type') !== 'all') {
                $studentQuery->where('student_service_requests.transaction_type', $request->input('transaction_type'));
                $facultyQuery->where('faculty_service_requests.transaction_type', $request->input('transaction_type'));
            }
            
            // Search functionality
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                
                $studentQuery->where(function($q) use ($search) {
                    $q->where('student_service_requests.first_name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.last_name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.service_category', 'like', "%{$search}%")
                      ->orWhere('users.name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.id', 'like', "%{$search}%");
                });
                
                $facultyQuery->where(function($q) use ($search) {
                    $q->where('faculty_service_requests.first_name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.last_name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.service_category', 'like', "%{$search}%")
                      ->orWhere('users.name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.id', 'like', "%{$search}%");
                });
            }
            
            // Add sorting (apply to both queries)
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            $studentQuery->orderBy($sortBy, $sortOrder);
            $facultyQuery->orderBy($sortBy, $sortOrder);
            
            // Get student requests and faculty requests
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Sort the combined collection by created_at
            $sortedRequests = $allRequests->sortByDesc('created_at');
            
            // Paginate the results
            $perPage = 10;
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
            $total = $sortedRequests->count();
            
            $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedRequests->slice($offset, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            // If it's an AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $paginatedRequests
                ]);
            }
            
            // Return view with assigned requests
            return view('uitc_staff.assign-request', [
                'assignedRequests' => $paginatedRequests,
                'totalRequests' => $total,
                'currentPage' => $page
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching assigned requests: ' . $e->getMessage(), [
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            return redirect()->back()->with('error', 'Unable to fetch assigned requests: ' . $e->getMessage());
        }
    }

    public function completeRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required',
            'request_type' => 'required|in:student,faculty',
            'actions_taken' => 'nullable|string|max:1000',
            'completion_report' => 'nullable|string|max:2000',
            'completion_status' => 'required|in:fully_completed,partially_completed,requires_follow_up'
        ]);

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Get the currently logged in UITC staff ID
            $currentStaffId = Auth::guard('admin')->user()->id;
            $staffName = Auth::guard('admin')->user()->name;
            
            // Based on request type, find the appropriate request
            if ($validatedData['request_type'] === 'student') {
                // Find the student service request
                $serviceRequest = StudentServiceRequest::findOrFail($request->request_id);
                
                // Ensure the request is assigned to the current UITC staff
                if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                    return response()->json([
                        'message' => 'Unauthorized to complete this request',
                        'error' => 'Request not assigned to current staff'
                    ], 403);
                }
                
                // Update the request status and add completion details
                $serviceRequest->update([
                    'status' => 'Completed',
                    'admin_notes' => $request->completion_report ?? 'Request completed',
                    'completion_status' => $request->completion_status,
                    'actions_taken' => $request->actions_taken ?? 'Standard request completion'
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
            } else {
                // Find the faculty service request
                $serviceRequest = FacultyServiceRequest::findOrFail($request->request_id);
                
                // Ensure the request is assigned to the current UITC staff
                if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                    return response()->json([
                        'message' => 'Unauthorized to complete this request',
                        'error' => 'Request not assigned to current staff'
                    ], 403);
                }
                
                // Update the request status and add completion details
                $serviceRequest->update([
                    'status' => 'Completed',
                    'admin_notes' => $request->completion_report ?? 'Request completed',
                    'completion_status' => $request->completion_status,
                    'actions_taken' => $request->actions_taken ?? 'Standard request completion'
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
            }

            // Send notification if user exists
            if (isset($user) && $user) {
                // Send the notification
                Notification::route('mail', $user->email)
                    ->notify(new ServiceRequestCompleted(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $request->completion_status,
                        $request->actions_taken,
                        $request->completion_report
                    ));
                    
                Log::info('Completion notification sent to: ' . $user->email, [
                    'request_id' => $serviceRequest->id,
                    'staff_id' => $currentStaffId,
                    'completion_status' => $request->completion_status
                ]);
            } else {
                Log::warning('Unable to send completion notification - user not found for request ID: ' . $request->request_id);
            }

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
                'request_type' => $validatedData['request_type'],
                'staff_id' => $currentStaffId ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to complete request: ' . $e->getMessage()
            ], 500);
        }
    }


    private function formatServiceCategory($category)
    {
        switch ($category) {
            case 'create':
                return 'Create MS Office/TUP Email Account';
            case 'reset_email_password':
                return 'Reset MS Office/TUP Email Password';
            case 'change_of_data_ms':
                return 'Change of Data (MS Office)';
            case 'reset_tup_web_password':
                return 'Reset TUP Web Password';
            case 'reset_ers_password':
                return 'Reset ERS Password';
            case 'change_of_data_portal':
                return 'Change of Data (Portal)';
            case 'dtr':
                return 'Daily Time Record';
            case 'biometric_record':
                return 'Biometric Record';
            case 'biometrics_enrollement':
                return 'Biometrics Enrollment';
            case 'new_internet':
                return 'New Internet Connection';
            case 'new_telephone':
                return 'New Telephone Connection';
            case 'repair_and_maintenance':
                return 'Internet/Telephone Repair and Maintenance';
            case 'computer_repair_maintenance':
                return 'Computer Repair and Maintenance';
            case 'printer_repair_maintenance':
                return 'Printer Repair and Maintenance';
            case 'request_led_screen':
                return 'LED Screen Request';
            case 'install_application':
                return 'Install Application/Information System/Software';
            case 'post_publication':
                return 'Post Publication/Update of Information Website';
            case 'data_docs_reports':
                return 'Data, Documents and Reports';
            case 'others':
                return $category;
            default:
                return $category;
        }
    }
    
    
    // You can add other methods like getAssignHistoryRequests here
}