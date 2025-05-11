<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\CustomerSatisfaction;
use App\Models\User;
use App\Notifications\ServiceRequestCompleted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RequestCompletedNotification;
use App\Notifications\AdminRequestCompletedNotification;
use App\Notifications\RequestUnresolvableNotification;

use Carbon\Carbon; 

class UITCStaffController extends Controller
{
    public function getAssignedRequests(Request $httpRequest)
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
                    // Select first_name and last_name separately
                    'users.first_name as requester_first_name', 
                    'users.last_name as requester_last_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'student' as request_type")
                );
                
            // 2. Fetch Faculty service requests assigned to this UITC staff member
            $facultyQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'faculty_service_requests.user_id', '=', 'users.id')
                ->select(
                    'faculty_service_requests.*',
                     // Select first_name and last_name separately
                    'users.first_name as requester_first_name',
                    'users.last_name as requester_last_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'faculty' as request_type")
                );
            
            // Add filtering options (apply to both queries)
            if ($httpRequest->has('status') && $httpRequest->input('status') !== 'all') {
                $studentQuery->where('student_service_requests.status', $httpRequest->input('status'));
                $facultyQuery->where('faculty_service_requests.status', $httpRequest->input('status'));
            }
            
            if ($httpRequest->has('transaction_type') && $httpRequest->input('transaction_type') !== 'all') {
                $studentQuery->where('student_service_requests.transaction_type', $httpRequest->input('transaction_type'));
                $facultyQuery->where('faculty_service_requests.transaction_type', $httpRequest->input('transaction_type'));
            }
            
            // Search functionality
            if ($httpRequest->has('search') && !empty($httpRequest->input('search'))) {
                $search = $httpRequest->input('search');
                
                $studentQuery->where(function($q) use ($search) {
                    // Search first_name OR last_name in users table
                    $q->where('student_service_requests.first_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.last_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.service_category', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%") 
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.id', 'like', "%{$search}%");
                });
                
                $facultyQuery->where(function($q) use ($search) {
                    // Search first_name OR last_name in users table
                    $q->where('faculty_service_requests.first_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.last_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.service_category', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.id', 'like', "%{$search}%");
                });
            }
            
            // Add sorting (apply to both queries)
            $sortBy = $httpRequest->input('sort_by', 'created_at');
            $sortOrder = $httpRequest->input('sort_order', 'desc');
            
            $studentQuery->orderBy($sortBy, $sortOrder);
            $facultyQuery->orderBy($sortBy, $sortOrder);
            
            // Get student requests and faculty requests
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Sort the combined collection by created_at
            $sortedRequests = $allRequests->sortByDesc('created_at');
            
            // Format the request_data for each request to be consistent with service-request
            $formattedRequests = collect();
            foreach ($sortedRequests as $serviceRequest) {
                // Add a formatted request_data field to match the service-request format
                $serviceRequest->request_data = $this->formatRequestData($serviceRequest);
                $formattedRequests->push($serviceRequest);
            }
            
            // Use the formatted requests for pagination
            $sortedRequests = $formattedRequests;
            
            // Paginate the results
            $perPage = 10;
            $page = $httpRequest->input('page', 1);
            $offset = ($page - 1) * $perPage;
            $total = $sortedRequests->count();
            
            $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedRequests->slice($offset, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $httpRequest->url(), 'query' => $httpRequest->query()]
            );
            
            // If it's an AJAX request, return JSON
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $paginatedRequests
                ]);
            }
            
            // Restore original return view statement
            return view('uitc_staff.assign-request', [
                'assignedRequests' => $paginatedRequests,
                'totalRequests' => $total,
                'currentPage' => $page
            ]);
            // Removed temporary test code // Ensure this comment is removed if the test code was actually removed previously
            
        } catch (\Exception $e) {
            // Log the error with more context
            Log::error('EXCEPTION CAUGHT in getAssignedRequests: ' . $e->getMessage(), [ // Added specific marker
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'currentPage' => $page
            ]);
            
        } catch (\Exception $e) {
            // Log the error with more context
            Log::error('EXCEPTION CAUGHT in getAssignedRequests: ' . $e->getMessage(), [ // Added specific marker
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If it's an AJAX request, return JSON error
            if ($httpRequest->ajax()) {
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

    /**
     * Format request data to be consistent with service-request display
     */
    private function formatRequestData($serviceRequest)
    {
        $html = '';
        
        // Name information
        $name = '';
        if (isset($serviceRequest->first_name) && isset($serviceRequest->last_name) && 
            // Use requester_first_name and requester_last_name from the query
            !empty($serviceRequest->requester_first_name)) {
            $name = trim($serviceRequest->requester_first_name . ' ' . $serviceRequest->requester_last_name);
        // Fallback to request's own name fields if user join failed (less likely now)
        } elseif (isset($serviceRequest->first_name) && !empty($serviceRequest->first_name)) {
             $name = trim($serviceRequest->first_name . ' ' . $serviceRequest->last_name);
        } else {
             $name = 'N/A';
        }
        $html .= '<strong>Name:</strong> ' . $name . '<br>';
        
        // ID information
        if (isset($serviceRequest->request_type) && $serviceRequest->request_type == 'student' && isset($serviceRequest->student_id)) {
            $html .= '<strong>Student ID:</strong> ' . $serviceRequest->student_id . '<br>';
        } elseif (isset($serviceRequest->request_type) && $serviceRequest->request_type == 'faculty' && isset($serviceRequest->faculty_id)) {
            $html .= '<strong>Faculty ID:</strong> ' . $serviceRequest->faculty_id . '<br>';
        }
        
        // Service information
        if (isset($serviceRequest->service_category)) {
            $html .= '<strong>Service:</strong> ' . $this->getServiceName($serviceRequest->service_category) . '<br>';
            
            // Description (if provided and not for 'others' category)
            if (isset($serviceRequest->description) && !empty($serviceRequest->description)) {
                if ($serviceRequest->service_category != 'others') {
                    $html .= '<strong>Description:</strong> ' . $serviceRequest->description;
                } elseif ($serviceRequest->service_category == 'others') {
                    // For 'others', the description is the service itself
                    $html .= '<strong>Service Details:</strong> ' . $serviceRequest->description;
                }
            }
        }
        
        return $html;
    }
    
    /**
     * Get human-readable service name from category code
     */
    private function getServiceName($category)
    {
        $services = [
            'create' => 'Create MS Office/TUP Email Account',
            'reset_email_password' => 'Reset MS Office/TUP Email Password',
            'change_of_data_ms' => 'Change of Data (MS Office)',
            'reset_tup_web_password' => 'Reset TUP Web Password',
            'reset_ers_password' => 'Reset ERS Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            'dtr' => 'Daily Time Record',
            'biometric_record' => 'Biometric Record',
            'biometrics_enrollement' => 'Biometrics Enrollment',
            'new_internet' => 'New Internet Connection',
            'new_telephone' => 'New Telephone Connection',
            'repair_and_maintenance' => 'Internet/Telephone Repair and Maintenance',
            'computer_repair_maintenance' => 'Computer Repair and Maintenance',
            'printer_repair_maintenance' => 'Printer Repair and Maintenance',
            'request_led_screen' => 'LED Screen Request',
            'install_application' => 'Install Application/Information System/Software',
            'post_publication' => 'Post Publication/Update of Information Website',
            'data_docs_reports' => 'Data, Documents and Reports',
            'others' => 'Other Service',
        ];
        
        return isset($services[$category]) ? $services[$category] : $category;
    }

    /**
     * Get detailed information about a specific request
     *
     * @param int $id The ID of the request
     * @param Request $request The HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestDetailsById($id, Request $request)
    {
        try {
            // Determine request type (student or faculty)
            $requestType = $request->query('type', 'student');
            
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Get the request based on type
            if ($requestType === 'student') {
                $serviceRequest = StudentServiceRequest::where('id', $id)
                    ->where('assigned_uitc_staff_id', $uitcStaffId)
                    ->first();
                    
                if (!$serviceRequest) {
                    return response()->json([
                        'error' => 'Request not found or not assigned to you'
                    ], 404);
                }
                
                // Load relationships separately
                if ($serviceRequest) {
                    $serviceRequest->load(['user', 'assignedUITCStaff']);
                }
            } else {
                $serviceRequest = FacultyServiceRequest::where('id', $id)
                    ->where('assigned_uitc_staff_id', $uitcStaffId)
                    ->first();
                    
                if (!$serviceRequest) {
                    return response()->json([
                        'error' => 'Request not found or not assigned to you'
                    ], 404);
                }
                
                // Load relationships separately
                if ($serviceRequest) {
                    $serviceRequest->load(['user', 'assignedUITCStaff']);
                }
            }
            
            // Add requester name if available from relationship
            if ($serviceRequest->user) {
                $serviceRequest->email = $serviceRequest->user->email;
            }
            
            // Format display ID if needed
            if ($requestType === 'student') {
                $serviceRequest->display_id = 'SSR-' . date('Ymd', strtotime($serviceRequest->created_at)) . '-' . 
                    str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT);
            } else {
                $serviceRequest->display_id = 'FSR-' . date('Ymd', strtotime($serviceRequest->created_at)) . '-' . 
                    str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT);
            }
            
            // Return the request details
            return response()->json($serviceRequest);
            
        } catch (\Exception $e) {
            Log::error('Error fetching request details: ' . $e->getMessage(), [
                'request_id' => $id,
                'staff_id' => Auth::guard('admin')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch request details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function completeRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required',
            'request_type' => 'required|in:student,faculty',
            'actions_taken' => 'required|string|max:1000',
            'completion_report' => 'required|string|max:2000',
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
                    'completion_report' => $request->completion_report,
                    'actions_taken' => $request->actions_taken,
                    'completed_at' => now()
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
                $transactionType = $serviceRequest->transaction_type ?? '';
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
                    'completion_report' => $request->completion_report,
                    'actions_taken' => $request->actions_taken,
                    'completed_at' => now()
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
                $transactionType = $serviceRequest->transaction_type ?? '';
            }
    
            // 1. Notify the user who submitted the request (if available)
            if (isset($user) && $user) {
                try {
                    // Send the notification to the user
                    $user->notify(new RequestCompletedNotification(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $request->completion_report,
                        $request->actions_taken,
                        $staffName,
                        $transactionType
                    ));
                    
                    Log::info('Completion notification sent to user: ' . $user->email, [
                        'request_id' => $serviceRequest->id,
                        'staff_id' => $currentStaffId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send completion notification to user', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('Unable to send completion notification - user not found for request ID: ' . $request->request_id);
            }
            
            // 2. Notify all admins
            try {
                // Get all admin users (with role 'Admin')
                $admins = \App\Models\Admin::where('role', 'Admin')->get();
                
                foreach ($admins as $admin) {
                    // Skip if admin is the current UITC staff (to avoid duplicate notifications)
                    if ($admin->id === $currentStaffId) {
                        continue;
                    }
                    
                    // Send admin notification
                    $admin->notify(new AdminRequestCompletedNotification(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $request->completion_report,
                        $request->actions_taken,
                        $staffName,
                        $transactionType
                    ));
                    
                    Log::info('Completion notification sent to admin: ' . $admin->name, [
                        'admin_id' => $admin->id,
                        'request_id' => $serviceRequest->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send completion notifications to admins', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw the exception - continue processing
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


    public function getCompletedRequests(Request $httpRequest)
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Initialize collection for all requests
            $completedRequests = collect();
            
            // Get search and filter parameters
            $search = $httpRequest->input('search', '');
            $dateFrom = $httpRequest->input('date_from');
            $dateTo = $httpRequest->input('date_to');
            $status = $httpRequest->input('status', 'all');
            
            // 1. Query for Student service requests
            $studentQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'student_service_requests.user_id', '=', 'users.id')
                ->select(
                    'student_service_requests.*',
                    // Select first_name and last_name separately
                    'users.first_name as requester_first_name', 
                    'users.last_name as requester_last_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'student' as request_type")
                );
                
            // 2. Query for Faculty service requests
            $facultyQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'faculty_service_requests.user_id', '=', 'users.id')
                ->select(
                    'faculty_service_requests.*',
                    // Select first_name and last_name separately
                    'users.first_name as requester_first_name', 
                    'users.last_name as requester_last_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'faculty' as request_type")
                );
            
            // Filter by status (Completed, Cancelled or Unresolvable)
            if ($status === 'all') {
                $studentQuery->whereIn('student_service_requests.status', ['Completed', 'Cancelled', 'Unresolvable']);
                $facultyQuery->whereIn('faculty_service_requests.status', ['Completed', 'Cancelled', 'Unresolvable']);
            } else {
                $studentQuery->where('student_service_requests.status', $status);
                $facultyQuery->where('faculty_service_requests.status', $status);
            }
            
            // Apply date range filter if provided
            if ($dateFrom && $dateTo) {
                $dateFrom = Carbon::parse($dateFrom)->startOfDay();
                $dateTo = Carbon::parse($dateTo)->endOfDay();
                
                $studentQuery->whereBetween('student_service_requests.created_at', [$dateFrom, $dateTo]);
                $facultyQuery->whereBetween('faculty_service_requests.created_at', [$dateFrom, $dateTo]);
            }
            
            // Apply search filter if provided
            if (!empty($search)) {
                $studentQuery->where(function($q) use ($search) {
                    // Search first_name OR last_name in users table
                    $q->where('student_service_requests.first_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.last_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.service_category', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%") 
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('student_service_requests.id', 'like', "%{$search}%");
                });
                
                $facultyQuery->where(function($q) use ($search) {
                    // Search first_name OR last_name in users table
                    $q->where('faculty_service_requests.first_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.last_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.service_category', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%") 
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('faculty_service_requests.id', 'like', "%{$search}%");
                });
            }
            
            // Sort by completion date (most recent first)
            $studentQuery->orderBy('student_service_requests.completed_at', 'desc')
                        ->orderBy('student_service_requests.updated_at', 'desc');
            $facultyQuery->orderBy('faculty_service_requests.completed_at', 'desc')
                        ->orderBy('faculty_service_requests.updated_at', 'desc');
            
            // Get student requests and faculty requests
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Sort the combined collection
            $sortedRequests = $allRequests->sortByDesc(function($request) {
                // First by completed_at date if available
                if ($request->completed_at) {
                    return Carbon::parse($request->completed_at)->timestamp;
                }
                // Fall back to updated_at date
                return Carbon::parse($request->updated_at)->timestamp;
            });
            
            // Format the request_data for each request
            $formattedRequests = collect();
            foreach ($sortedRequests as $serviceRequest) {
                // Add a formatted request_data field
                $serviceRequest->request_data = $this->formatRequestData($serviceRequest);
                $formattedRequests->push($serviceRequest);
            }
            
            // Paginate the results
            $perPage = 10;
            $page = $httpRequest->input('page', 1);
            $offset = ($page - 1) * $perPage;
            $total = $formattedRequests->count();
            
            $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                $formattedRequests->slice($offset, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $httpRequest->url(), 'query' => $httpRequest->query()]
            );
            
            // If it's an AJAX request, return JSON
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $paginatedRequests
                ]);
            }
            
            // Return view with completed requests
            return view('uitc_staff.assign-history', [
                'completedRequests' => $paginatedRequests,
                'totalRequests' => $total,
                'currentPage' => $page
            ]);
            
        } catch (\Exception $e) {
            // Log the error with more context
            Log::error('EXCEPTION CAUGHT in getCompletedRequests: ' . $e->getMessage(), [ // Added specific marker
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If it's an AJAX request, return JSON error
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch completed requests',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // For non-AJAX requests, redirect with error
            return redirect()->back()->with('error', 'Unable to fetch completed requests: ' . $e->getMessage());
        }
    }

    /**
     * Get active requests for the UITC Staff dashboard
     * 
     * @param bool $isUitcStaff
     * @param int $staffId
     * @return Collection
     */
    private function getActiveRequests($staffId)
    {
        // Start queries
        $studentQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $staffId)
            ->where('status', 'In Progress')
            ->orderBy('created_at', 'desc');
        
        $facultyQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $staffId)
            ->where('status', 'In Progress')
            ->orderBy('created_at', 'desc');
        
        // Get student requests
        $studentRequests = $studentQuery->take(5)
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'service_type' => $this->getServiceName($request, 'student'),
                    'user_name' => $request->first_name . ' ' . $request->last_name,
                    'created_at' => $request->created_at,
                    'status' => $request->status,
                    'type' => 'student'
                ];
            });

        // Get faculty requests
        $facultyRequests = $facultyQuery->take(5)
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'service_type' => $this->getServiceName($request, 'faculty'),
                    'user_name' => $request->first_name . ' ' . $request->last_name,
                    'created_at' => $request->created_at,
                    'status' => $request->status,
                    'type' => 'faculty'
                ];
            });

        // Merge and sort by date
        return $studentRequests->concat($facultyRequests)
            ->sortByDesc('created_at')
            ->take(5);
    }


    public function getReports(Request $request)
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Get the period filter (default to current month)
            $period = $request->input('period', 'month');
            $customStartDate = $request->input('custom_start_date');
            $customEndDate = $request->input('custom_end_date');
            
            // Set date range based on period
            $startDate = null;
            $endDate = Carbon::now();
            
            switch ($period) {
                case 'week':
                    $startDate = Carbon::now()->subWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->subMonths(3);
                    break;
                case 'year':
                    $startDate = Carbon::now()->subYear();
                    break;
                case 'custom':
                    if ($customStartDate && $customEndDate) {
                        $startDate = Carbon::parse($customStartDate);
                        $endDate = Carbon::parse($customEndDate)->endOfDay();
                    } else {
                        $startDate = Carbon::now()->startOfMonth();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->startOfMonth();
            }
            
            // 1. Get student requests assigned to this staff
            $studentRequests = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 2. Get faculty requests assigned to this staff
            $facultyRequests = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 3. Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            $overdueRequests = $this->getOverdueRequestsDetails($allRequests);

            // 4. Prepare statistics
            $stats = [
                'total_requests' => $allRequests->count(),
                'completed_requests' => $allRequests->where('status', 'Completed')->count(),
                'in_progress_requests' => $allRequests->where('status', 'In Progress')->count(),
                'cancelled_requests' => $allRequests->where('status', 'Cancelled')->count(),
                'overdue_requests' => $allRequests->where('status', 'Overdue')->count(), // Add this line
            ];
            $overdueRequests = $this->getOverdueRequestsDetails($allRequests);
            
            // 5. Calculate average resolution time for completed requests
            $completedRequests = $allRequests->where('status', 'Completed');
            
            $totalResolutionDays = 0;
            $resolutionTimes = [];
            $fastestResolution = PHP_INT_MAX;
            $slowestResolution = 0;
            
            foreach ($completedRequests as $request) {
                $createdAt = Carbon::parse($request->created_at);
                $completedAt = Carbon::parse($request->updated_at);
                $daysToResolve = $createdAt->diffInDays($completedAt);
                $hoursToResolve = $createdAt->diffInHours($completedAt);
                
                // Track fastest and slowest resolution times
                if ($daysToResolve < $fastestResolution) {
                    $fastestResolution = $daysToResolve;
                }
                
                if ($daysToResolve > $slowestResolution) {
                    $slowestResolution = $daysToResolve;
                }
                
                $totalResolutionDays += $daysToResolve;
                $resolutionTimes[] = $daysToResolve;
                
                // Store the resolution time on the request object for later use
                $request->resolution_time_days = $daysToResolve;
                $request->resolution_time_hours = $hoursToResolve;
            }
            
            $stats['avg_resolution_time'] = $completedRequests->count() > 0 
                ? round($totalResolutionDays / $completedRequests->count(), 1) 
                : 0;
                
            // Calculate median resolution time
            if (count($resolutionTimes) > 0) {
                sort($resolutionTimes);
                $middle = floor(count($resolutionTimes) / 2);
                $stats['median_resolution_time'] = count($resolutionTimes) % 2 == 0 
                    ? ($resolutionTimes[$middle - 1] + $resolutionTimes[$middle]) / 2 
                    : $resolutionTimes[$middle];
            } else {
                $stats['median_resolution_time'] = 0;
            }
            
            // Store fastest and slowest resolution times
            $timeStats = [
                'fastest_resolution' => $fastestResolution != PHP_INT_MAX ? $fastestResolution : 0,
                'slowest_resolution' => $slowestResolution
            ];
            
            // 6. Get customer satisfaction data
            $customerSatisfactionData = CustomerSatisfaction::whereIn('request_id', $allRequests->pluck('id'))
                ->where(function($query) {
                    $query->where('request_type', 'Student')
                          ->orWhere('request_type', 'Faculty & Staff');
                })
                ->get();
                
            // Calculate average ratings
            $stats['satisfaction_count'] = $customerSatisfactionData->count();
            
            if ($customerSatisfactionData->count() > 0) {
                $stats['avg_responsiveness'] = round($customerSatisfactionData->avg('responsiveness'), 1);
                $stats['avg_reliability'] = round($customerSatisfactionData->avg('reliability'), 1);
                $stats['avg_access_facilities'] = round($customerSatisfactionData->avg('access_facilities'), 1);
                $stats['avg_communication'] = round($customerSatisfactionData->avg('communication'), 1);
                $stats['avg_costs'] = round($customerSatisfactionData->avg('costs'), 1);
                $stats['avg_integrity'] = round($customerSatisfactionData->avg('integrity'), 1);
                $stats['avg_assurance'] = round($customerSatisfactionData->avg('assurance'), 1);
                $stats['avg_outcome'] = round($customerSatisfactionData->avg('outcome'), 1);
                $stats['avg_overall_rating'] = round($customerSatisfactionData->avg('average_rating'), 1);
            } else {
                $stats['avg_responsiveness'] = 0;
                $stats['avg_reliability'] = 0;
                $stats['avg_access_facilities'] = 0;
                $stats['avg_communication'] = 0;
                $stats['avg_costs'] = 0;
                $stats['avg_integrity'] = 0;
                $stats['avg_assurance'] = 0;
                $stats['avg_outcome'] = 0;
                $stats['avg_overall_rating'] = 0;
            }
            
            // 7. Get request counts by service category
            $categoryStats = [];
            $categoryAvgResolution = [];
            
            foreach ($allRequests as $request) {
                $category = $request->service_category;
                $formattedCategory = $this->formatServiceCategory($category);
                
                if (!isset($categoryStats[$formattedCategory])) {
                    $categoryStats[$formattedCategory] = [
                        'total' => 0,
                        'completed' => 0,
                        'pending' => 0,
                        'in_progress' => 0,
                        'cancelled' => 0,
                        'overdue' => 0,
                        'resolution_times' => [],
                    ];
                }
                
                $categoryStats[$formattedCategory]['total']++;
                
                switch ($request->status) {
                    case 'Completed':
                        $categoryStats[$formattedCategory]['completed']++;
                        if (isset($request->resolution_time_days)) {
                            $categoryStats[$formattedCategory]['resolution_times'][] = $request->resolution_time_days;
                        }
                        break;
                    case 'Pending':
                        $categoryStats[$formattedCategory]['pending']++;
                        break;
                    case 'In Progress':
                        $categoryStats[$formattedCategory]['in_progress']++;
                        break;
                    case 'Cancelled':
                        $categoryStats[$formattedCategory]['cancelled']++;
                        break;
                    case 'Overdue':  // Add this case to correctly count overdue requests
                        $categoryStats[$formattedCategory]['overdue']++;
                        break;

                }
            }
            
            // Calculate average resolution time per category
            foreach ($categoryStats as $category => $data) {
                if (!empty($data['resolution_times'])) {
                    $categoryAvgResolution[$category] = round(array_sum($data['resolution_times']) / count($data['resolution_times']), 1);
                } else {
                    $categoryAvgResolution[$category] = 'N/A';
                }
            }
            
            // 8. Get monthly trends data with overdue counts
            $monthlyTrends = [];
            $startMonth = $startDate->copy();
            $endMonth = $endDate->copy();

            while ($startMonth->lt($endMonth)) {
                $monthKey = $startMonth->format('M Y');
                $monthStart = $startMonth->copy()->startOfMonth();
                $monthEnd = $startMonth->copy()->endOfMonth();
                
                // Get all requests created in this month
                $monthRequests = $allRequests->filter(function($request) use ($monthStart, $monthEnd) {
                    $requestDate = Carbon::parse($request->created_at);
                    return $requestDate->gte($monthStart) && $requestDate->lte($monthEnd);
                });
                
                // Get completed requests that were completed in this month
                $monthCompleted = $allRequests->filter(function($request) use ($monthStart, $monthEnd) {
                    if ($request->status !== 'Completed') return false;
                    $completedDate = Carbon::parse($request->updated_at);
                    return $completedDate->gte($monthStart) && $completedDate->lte($monthEnd);
                });
                
                // Get overdue requests that were marked as overdue in this month
                $monthOverdue = $allRequests->filter(function($request) use ($monthStart, $monthEnd) {
                    if ($request->status !== 'Overdue') return false;
                    $overdueDate = Carbon::parse($request->updated_at);
                    return $overdueDate->gte($monthStart) && $overdueDate->lte($monthEnd);
                });
                
                // Store the data for this month
                $monthlyTrends[$monthKey] = [
                    'total' => $monthRequests->count(),
                    'completed' => $monthCompleted->count(),
                    'overdue' => $monthOverdue->count(), // Add overdue count
                    'month_number' => $startMonth->month,
                    'year' => $startMonth->year,
                ];
                
                $startMonth->addMonth();
            }

            // Sort by month and year
            ksort($monthlyTrends);
            
            // 9. Get trending service categories
            $trendingCategories = array_keys($categoryStats);
            usort($trendingCategories, function($a, $b) use ($categoryStats) {
                return $categoryStats[$b]['total'] - $categoryStats[$a]['total'];
            });
            
            // Only keep top 5
            $trendingCategories = array_slice($trendingCategories, 0, 5);
            
            // 10. Get daily activity data
            $dailyActivity = $this->getDailyActivityData($startDate, $endDate, $allRequests);
            
            // 11. Get SLA performance data
            $slaStats = $this->getSLAPerformanceData($allRequests);
            
            // 12. Get recent activity timeline
            $recentActivity = $this->getRecentActivityData($allRequests, 10); // Get the 10 most recent activities
            
            // NEW: 13. Generate improvement recommendations
            $improvementRecommendations = $this->generateImprovementRecommendations(
                $stats, 
                $slaStats, 
                $categoryStats,
                [
                    'avg_responsiveness' => $stats['avg_responsiveness'] ?? 0,
                    'avg_reliability' => $stats['avg_reliability'] ?? 0,
                    'avg_access_facilities' => $stats['avg_access_facilities'] ?? 0,
                    'avg_communication' => $stats['avg_communication'] ?? 0,
                    'avg_costs' => $stats['avg_costs'] ?? 0,
                    'avg_integrity' => $stats['avg_integrity'] ?? 0,
                    'avg_assurance' => $stats['avg_assurance'] ?? 0,
                    'avg_outcome' => $stats['avg_outcome'] ?? 0,
                ]
            );

            // Load view with all stats
            return view('uitc_staff.reports', [
                'stats' => $stats,
                'timeStats' => $timeStats,
                'period' => $period,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'categoryStats' => $categoryStats,
                'categoryAvgResolution' => $categoryAvgResolution,
                'monthlyTrends' => $monthlyTrends,
                'trendingCategories' => $trendingCategories,
                'customStartDate' => $customStartDate,
                'customEndDate' => $customEndDate,
                'dailyActivity' => $dailyActivity,
                'slaStats' => $slaStats,
                'recentActivity' => $recentActivity,
                'improvementRecommendations' => $improvementRecommendations,
                'overdueRequests' => $overdueRequests, // Add this line
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error generating reports: ' . $e->getMessage(), [
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Redirect with error
            return redirect()->back()->with('error', 'Unable to generate reports: ' . $e->getMessage());
        }
    }
    
    // Helper method to get daily activity data
    private function getDailyActivityData($startDate, $endDate, $allRequests) {
        $dailyActivity = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCopy = Carbon::parse($endDate);
        
        while ($currentDate->lte($endDateCopy)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dateFormatted = $currentDate->format('M d, Y');
            
            $dailyRequests = $allRequests->filter(function($request) use ($currentDate) {
                $requestDate = Carbon::parse($request->created_at)->startOfDay();
                return $requestDate->eq($currentDate->startOfDay());
            });
            
            $dailyCompleted = $allRequests->filter(function($request) use ($currentDate) {
                if ($request->status !== 'Completed') return false;
                $completedDate = Carbon::parse($request->updated_at)->startOfDay();
                return $completedDate->eq($currentDate->startOfDay());
            });

            // NEW: Overdue requests on this day
            // Get requests that were marked as overdue on this day
            $dailyOverdue = $allRequests->filter(function($request) use ($currentDate) {
                if ($request->status !== 'Overdue') return false;
                $overdueDate = Carbon::parse($request->updated_at)->startOfDay();
                return $overdueDate->eq($currentDate->startOfDay());
            });
            
            $dailyActivity[$dateFormatted] = [
                'new' => $dailyRequests->count(),
                'completed' => $dailyCompleted->count(),
                'overdue' => $dailyOverdue->count(), // Add overdue count
            ];
            
            $currentDate->addDay();
        }
        
        return $dailyActivity;
    }

    /**
     * Get detailed information about overdue requests
     * 
     * @param Collection $allRequests Collection of service requests
     * @param int $limit Maximum number of requests to return (0 for all)
     * @return Collection Collection of overdue requests with additional information
     */
    private function getOverdueRequestsDetails($allRequests, $limit = 5) 
    {
        // Get requests with 'Overdue' status
        $overdueRequests = $allRequests->where('status', 'Overdue');
        
        // If no overdue requests, return empty collection
        if ($overdueRequests->isEmpty()) {
            return collect([]);
        }
        
        // Define transaction type time limits (in business days)
        $transactionLimits = [
            'Simple Transaction' => 3,
            'Complex Transaction' => 7,
            'Highly Technical Transaction' => 20,
            'default' => 5 // Default if transaction type is not specified
        ];
        
        // Add additional information to each overdue request
        $overdueRequests = $overdueRequests->map(function($request) use ($transactionLimits) {
            // Get the transaction type (with default fallback)
            $transactionType = $request->transaction_type ?? 'default';
            
            // Get the time limit for this transaction type
            $businessDaysLimit = $transactionLimits[$transactionType] ?? $transactionLimits['default'];
            
            // Calculate how long the request has been assigned
            $assignedDate = Carbon::parse($request->updated_at);
            $currentDate = Carbon::now();
            $daysElapsed = $assignedDate->diffInDays($currentDate);
            
            // Calculate approximate days overdue (without business day calculation)
            // In a real implementation, you might want to use a more accurate business day calculation
            $daysOverdue = max(0, $daysElapsed - $businessDaysLimit);
            
            // Store these values with the request
            $request->days_elapsed = $daysElapsed;
            $request->days_overdue = $daysOverdue;
            $request->business_days_limit = $businessDaysLimit;
            
            // Calculate a priority score (higher = more urgent)
            // This formula prioritizes requests that are more overdue and have shorter time limits
            $priorityScore = ($daysOverdue / max(1, $businessDaysLimit)) * 100;
            $request->priority_score = round($priorityScore);
            
            return $request;
        })
        ->sortByDesc('priority_score'); // Sort by priority (most urgent first)
        
        // Limit the number of requests if specified
        if ($limit > 0) {
            $overdueRequests = $overdueRequests->take($limit);
        }
        
        return $overdueRequests;
    }
        
    // Helper method to get SLA performance data
    private function getSLAPerformanceData($allRequests) {
        // Define SLA thresholds in hours for different service categories
        $slaThresholds = [
            'create' => 24, // hours
            'reset_email_password' => 2,
            'change_of_data_ms' => 24,
            'reset_tup_web_password' => 2,
            'reset_ers_password' => 2,
            'change_of_data_portal' => 24,
            'dtr' => 48,
            'biometric_record' => 24,
            'biometrics_enrollement' => 24,
            'new_internet' => 72,
            'new_telephone' => 72,
            'repair_and_maintenance' => 48,
            'computer_repair_maintenance' => 48,
            'printer_repair_maintenance' => 48,
            'request_led_screen' => 24,
            'install_application' => 48,
            'post_publication' => 24,
            'data_docs_reports' => 48,
            'default' => 24 // Default threshold
        ];
        
        $metSLA = 0;
        $missedSLA = 0;
        $totalResponseTime = 0;
        $overdueDays = [];
        
        foreach ($allRequests->where('status', 'Completed') as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $completedAt = Carbon::parse($request->updated_at);
            $responseTimeHours = $createdAt->diffInHours($completedAt);
            $totalResponseTime += $responseTimeHours;
            
            $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
            
            if ($responseTimeHours <= $threshold) {
                $metSLA++;
            } else {
                $missedSLA++;
                $overdueDays[] = ceil(($responseTimeHours - $threshold) / 24); // Convert to days
            }
        }
        
        $totalCompletedRequests = $metSLA + $missedSLA;
        
        return [
            'met' => $metSLA,
            'missed' => $missedSLA,
            'met_percentage' => $totalCompletedRequests > 0 ? 
                round(($metSLA / $totalCompletedRequests) * 100) : 0,
            'missed_percentage' => $totalCompletedRequests > 0 ? 
                round(($missedSLA / $totalCompletedRequests) * 100) : 0,
            'avg_response_time' => $totalCompletedRequests > 0 ? 
                round($totalResponseTime / $totalCompletedRequests, 1) : 0,
            'avg_overdue_days' => !empty($overdueDays) ? 
                round(array_sum($overdueDays) / count($overdueDays), 1) : 0,
            'max_overdue_days' => !empty($overdueDays) ? max($overdueDays) : 0
        ];
    }
    
    // Helper method to get recent activity data
    private function getRecentActivityData($allRequests, $limit = 10) {
        $activities = [];
        
        // Sort requests by most recent activity (either created or updated)
        $sortedRequests = $allRequests->sortByDesc(function($request) {
            $lastActivityDate = max(
                Carbon::parse($request->created_at)->timestamp,
                Carbon::parse($request->updated_at)->timestamp
            );
            return $lastActivityDate;
        })->take($limit);
        
        foreach ($sortedRequests as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $updatedAt = Carbon::parse($request->updated_at);
            
            // Determine if it's a new request or status change
            if ($createdAt->diffInHours($updatedAt) < 1) {
                $action = "New request submitted";
                $date = $createdAt->format('M d, Y h:i A');
            } else {
                $action = "Status changed to " . $request->status;
                $date = $updatedAt->format('M d, Y h:i A');
            }
            
            // Check if overdue based on SLA
            $isOverdue = false;
            $overdueDays = 0;
            
            if ($request->status === 'Completed') {
                // Use the same SLA thresholds as in getSLAPerformanceData
                $slaThresholds = [
                    'create' => 24,
                    'reset_email_password' => 2,
                    // ... other thresholds
                    'default' => 24
                ];
                
                $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
                $responseTimeHours = $createdAt->diffInHours($updatedAt);
                
                if ($responseTimeHours > $threshold) {
                    $isOverdue = true;
                    $overdueDays = ceil(($responseTimeHours - $threshold) / 24);
                }
            }
            
            $activities[] = [
                'id' => $request->id,
                'date' => $date,
                'action' => $action,
                'category' => $this->formatServiceCategory($request->service_category),
                'user' => $request->requester_name ?? ($request->first_name . ' ' . $request->last_name),
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays
            ];
        }
        
        return $activities;
    }
    
    // Update exportReports method to include new data
    public function exportReports(Request $request)
    {
        try {

            // Set a longer execution time for this specific request
            ini_set('max_execution_time', 300);
            
            // Increase memory limit if needed
            ini_set('memory_limit', '512M');

            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            $staffName = Auth::guard('admin')->user()->name;
            
            // Get the period filter (default to current month)
            $period = $request->input('period', 'month');
            $customStartDate = $request->input('custom_start_date');
            $customEndDate = $request->input('custom_end_date');
            
            // Set date range based on period
            $startDate = null;
            $endDate = Carbon::now();
            
            switch ($period) {
                case 'week':
                    $startDate = Carbon::now()->subWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->subMonths(3);
                    break;
                case 'year':
                    $startDate = Carbon::now()->subYear();
                    break;
                case 'custom':
                    if ($customStartDate && $customEndDate) {
                        $startDate = Carbon::parse($customStartDate);
                        $endDate = Carbon::parse($customEndDate)->endOfDay();
                    } else {
                        $startDate = Carbon::now()->startOfMonth();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->startOfMonth();
            }
            
            // 1. Get student requests assigned to this staff
            $studentRequests = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 2. Get faculty requests assigned to this staff
            $facultyRequests = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 3. Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Get overdue requests - important to define this before using it
            $overdueRequests = $this->getOverdueRequestsDetails($allRequests);
            
            // 4. Prepare statistics (same as in getReports method)
            $stats = [
                'total_requests' => $allRequests->count(),
                'completed_requests' => $allRequests->where('status', 'Completed')->count(),
                'pending_requests' => $allRequests->where('status', 'Pending')->count(),
                'in_progress_requests' => $allRequests->where('status', 'In Progress')->count(),
                'cancelled_requests' => $allRequests->where('status', 'Cancelled')->count(),
                'overdue_requests' => $allRequests->where('status', 'Overdue')->count(), // Make sure this is defined
            ];
            
            // 5. Calculate resolution time statistics
            $completedRequests = $allRequests->where('status', 'Completed');
            
            $totalResolutionDays = 0;
            $resolutionTimes = [];
            $fastestResolution = PHP_INT_MAX;
            $slowestResolution = 0;
            
            foreach ($completedRequests as $request) {
                $createdAt = Carbon::parse($request->created_at);
                $completedAt = Carbon::parse($request->updated_at);
                $daysToResolve = $createdAt->diffInDays($completedAt);
                
                if ($daysToResolve < $fastestResolution) {
                    $fastestResolution = $daysToResolve;
                }
                
                if ($daysToResolve > $slowestResolution) {
                    $slowestResolution = $daysToResolve;
                }
                
                $totalResolutionDays += $daysToResolve;
                $resolutionTimes[] = $daysToResolve;
            }
            
            $stats['avg_resolution_time'] = $completedRequests->count() > 0 
                ? round($totalResolutionDays / $completedRequests->count(), 1) 
                : 0;
                
            // Calculate median resolution time
            if (count($resolutionTimes) > 0) {
                sort($resolutionTimes);
                $middle = floor(count($resolutionTimes) / 2);
                $stats['median_resolution_time'] = count($resolutionTimes) % 2 == 0 
                    ? ($resolutionTimes[$middle - 1] + $resolutionTimes[$middle]) / 2 
                    : $resolutionTimes[$middle];
            } else {
                $stats['median_resolution_time'] = 0;
            }
            
            $timeStats = [
                'fastest_resolution' => $fastestResolution != PHP_INT_MAX ? $fastestResolution : 0,
                'slowest_resolution' => $slowestResolution
            ];
            
            // 6. Get customer satisfaction data
            $customerSatisfactionData = CustomerSatisfaction::whereIn('request_id', $allRequests->pluck('id'))
                ->where(function($query) {
                    $query->where('request_type', 'Student')
                          ->orWhere('request_type', 'Faculty & Staff');
                })
                ->get();
                
            // Calculate average ratings
            $stats['satisfaction_count'] = $customerSatisfactionData->count();
            
            if ($customerSatisfactionData->count() > 0) {
                $stats['avg_responsiveness'] = round($customerSatisfactionData->avg('responsiveness'), 1);
                $stats['avg_reliability'] = round($customerSatisfactionData->avg('reliability'), 1);
                $stats['avg_access_facilities'] = round($customerSatisfactionData->avg('access_facilities'), 1);
                $stats['avg_communication'] = round($customerSatisfactionData->avg('communication'), 1);
                $stats['avg_costs'] = round($customerSatisfactionData->avg('costs'), 1);
                $stats['avg_integrity'] = round($customerSatisfactionData->avg('integrity'), 1);
                $stats['avg_assurance'] = round($customerSatisfactionData->avg('assurance'), 1);
                $stats['avg_outcome'] = round($customerSatisfactionData->avg('outcome'), 1);
                $stats['avg_overall_rating'] = round($customerSatisfactionData->avg('average_rating'), 1);
            } else {
                $stats['avg_responsiveness'] = 0;
                $stats['avg_reliability'] = 0;
                $stats['avg_access_facilities'] = 0;
                $stats['avg_communication'] = 0;
                $stats['avg_costs'] = 0;
                $stats['avg_integrity'] = 0;
                $stats['avg_assurance'] = 0;
                $stats['avg_outcome'] = 0;
                $stats['avg_overall_rating'] = 0;
            }
            
            // 7. Get request counts by service category and calculate avg resolution time
            $categoryStats = [];
            $categoryAvgResolution = [];
            
            foreach ($allRequests as $request) {
                $category = $request->service_category;
                $formattedCategory = $this->formatServiceCategory($category);
                
                if (!isset($categoryStats[$formattedCategory])) {
                    $categoryStats[$formattedCategory] = [
                        'total' => 0,
                        'completed' => 0,
                        'pending' => 0,
                        'in_progress' => 0,
                        'cancelled' => 0,
                        'overdue' => 0,
                        'resolution_times' => [],
                    ];
                }
                
                $categoryStats[$formattedCategory]['total']++;
                
                switch ($request->status) {
                    case 'Completed':
                        $categoryStats[$formattedCategory]['completed']++;
                        $createdAt = Carbon::parse($request->created_at);
                        $completedAt = Carbon::parse($request->updated_at);
                        $daysToResolve = $createdAt->diffInDays($completedAt);
                        $categoryStats[$formattedCategory]['resolution_times'][] = $daysToResolve;
                        break;
                    case 'Pending':
                        $categoryStats[$formattedCategory]['pending']++;
                        break;
                    case 'In Progress':
                        $categoryStats[$formattedCategory]['in_progress']++;
                        break;
                    case 'Cancelled':
                        $categoryStats[$formattedCategory]['cancelled']++;
                        break;
                    case 'Overdue':
                        $categoryStats[$formattedCategory]['overdue']++;
                        break;
                }
            }
            
            // Calculate average resolution time per category
            foreach ($categoryStats as $category => $data) {
                if (!empty($data['resolution_times'])) {
                    $categoryAvgResolution[$category] = round(array_sum($data['resolution_times']) / count($data['resolution_times']), 1);
                } else {
                    $categoryAvgResolution[$category] = 'N/A';
                }
            }
            
            // 8. Get daily activity data
            $dailyActivity = $this->getDailyActivityData($startDate, $endDate, $allRequests);
            
            // 9. Get SLA performance data
            $slaStats = $this->getSLAPerformanceData($allRequests);
            
            // 10. Get recent activity timeline
            $recentActivity = $this->getRecentActivityData($allRequests, 15); // Get the 15 most recent activities
            
            // 11. Generate improvement recommendations - MOVED AFTER slaStats and categoryStats are defined
            $improvementRecommendations = $this->generateImprovementRecommendations(
                $stats, 
                $slaStats,
                $categoryStats,
                [
                    'avg_responsiveness' => $stats['avg_responsiveness'] ?? 0,
                    'avg_reliability' => $stats['avg_reliability'] ?? 0,
                    'avg_access_facilities' => $stats['avg_access_facilities'] ?? 0,
                    'avg_communication' => $stats['avg_communication'] ?? 0,
                    'avg_costs' => $stats['avg_costs'] ?? 0,
                    'avg_integrity' => $stats['avg_integrity'] ?? 0,
                    'avg_assurance' => $stats['avg_assurance'] ?? 0,
                    'avg_outcome' => $stats['avg_outcome'] ?? 0,
                ]
            );
            
            // Add this code to encode the logo
            $logoPath = public_path('images/tuplogo.png');
            $logoData = null;
            
            if (file_exists($logoPath)) {
                $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
            
            // Create a function to format service categories in the view
            $formatServiceCategory = function($category) {
                return $this->formatServiceCategory($category);
            };

            // Sort the categoryStats by total requests (descending)
            uasort($categoryStats, function ($a, $b) {
                return $b['total'] - $a['total'];
            });
            
            // Take only the top 5 overdueRequests
            $limitedOverdueRequests = $overdueRequests->take(5);
            
            // Generate PDF using DomPDF
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadView('uitc_staff.reports_pdf_simplified', [
                'stats' => $stats,
                'timeStats' => $timeStats,
                'startDate' => $startDate->format('M d, Y'),
                'endDate' => $endDate->format('M d, Y'),
                'staffName' => $staffName,
                'categoryStats' => $categoryStats, // Already sorted above
                'slaStats' => $slaStats,
                'overdueRequests' => $limitedOverdueRequests,
                'improvementRecommendations' => $improvementRecommendations
            ]);
            
            // Set PDF options if needed
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
            
            // Return the PDF for download with a dynamic filename
            return $pdf->download('UITC_Staff_Report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf');
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error exporting reports: ' . $e->getMessage(), [
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Redirect with error
            return redirect()->back()->with('error', 'Unable to export reports: ' . $e->getMessage());
        }
    }


        /**
     * Generate improvement recommendations based on report metrics
     * 
     * @param array $stats Overall statistics
     * @param array $slaStats SLA performance statistics
     * @param array $categoryStats Service category statistics
     * @param array $customerSatisfaction Customer satisfaction data
     * @return array Array of improvement recommendations with priority levels
     */
    private function generateImprovementRecommendations($stats, $slaStats, $categoryStats, $customerSatisfaction)
    {
        $recommendations = [];
        
        // 1. Check completion rate
        if ($stats['total_requests'] > 0) {
            $completionRate = ($stats['completed_requests'] / $stats['total_requests']) * 100;
            if ($completionRate < 50) {
                $recommendations[] = [
                    'area' => 'Completion Rate',
                    'issue' => 'Low overall completion rate (' . round($completionRate) . '%)',
                    'recommendation' => 'Focus on completing In Progress requests. Consider implementing a backlog reduction plan.',
                    'priority' => 'high',
                    'metric' => round($completionRate) . '%',
                    'target' => '100%+'
                ];
            } elseif ($completionRate < 70) {
                $recommendations[] = [
                    'area' => 'Completion Rate',
                    'issue' => 'Moderate completion rate (' . round($completionRate) . '%)',
                    'recommendation' => 'Review in-progress requests to identify and address bottlenecks.',
                    'priority' => 'medium',
                    'metric' => round($completionRate) . '%',
                    'target' => '80 %+'
                ];
            }
        }
        
        // 2. Check SLA performance
        if ($slaStats['met'] + $slaStats['missed'] > 0) {
            if ($slaStats['missed_percentage'] > 30) {
                $recommendations[] = [
                    'area' => 'Service Level Agreement',
                    'issue' => 'High rate of missed deadlines (' . $slaStats['missed_percentage'] . '%)',
                    'recommendation' => 'Review and prioritize tasks accordingly. Consider setting up reminders for upcoming deadlines.',
                    'priority' => 'high',
                    'metric' => $slaStats['missed_percentage'] . '%',
                    'target' => 'Less than 10%'
                ];
            } elseif ($slaStats['missed_percentage'] > 10) {
                $recommendations[] = [
                    'area' => 'Service Level Agreement',
                    'issue' => 'Moderate rate of missed deadlines (' . $slaStats['missed_percentage'] . '%)',
                    'recommendation' => 'Monitor upcoming deadlines more closely to ensure timely completion.',
                    'priority' => 'medium',
                    'metric' => $slaStats['missed_percentage'] . '%',
                    'target' => 'Less than 10%'
                ];
            }
            
            if ($slaStats['max_overdue_days'] > 3) {
                $recommendations[] = [
                    'area' => 'Resolution Time',
                    'issue' => 'Maximum overdue days (' . $slaStats['max_overdue_days'] . ' days) is concerning',
                    'recommendation' => 'Identify causes for significantly delayed requests and implement preventive measures.',
                    'priority' => 'high',
                    'metric' => $slaStats['max_overdue_days'] . ' days',
                    'target' => 'Less than 1 day'
                ];
            }
        }
        
        // 3. Check customer satisfaction
        if ($stats['satisfaction_count'] > 0) {
            // Find the lowest rated areas
            $satisfactionMetrics = [
                'Responsiveness' => $stats['avg_responsiveness'] ?? 0,
                'Reliability' => $stats['avg_reliability'] ?? 0,
                'Access & Facilities' => $stats['avg_access_facilities'] ?? 0,
                'Communication' => $stats['avg_communication'] ?? 0,
                'Costs' => $stats['avg_costs'] ?? 0,
                'Integrity' => $stats['avg_integrity'] ?? 0,
                'Assurance' => $stats['avg_assurance'] ?? 0,
                'Outcome' => $stats['avg_outcome'] ?? 0
            ];
            
            // Sort by lowest rating
            asort($satisfactionMetrics);
            $lowestRated = array_slice($satisfactionMetrics, 0, 2, true);
            
            foreach ($lowestRated as $metric => $rating) {
                if ($rating < 3) {
                    $priority = 'high';
                } elseif ($rating < 4) {
                    $priority = 'medium';
                } else {
                    continue; // Skip ratings that are 4+
                }
                
                $recommendationText = '';
                switch ($metric) {
                    case 'Responsiveness':
                        $recommendationText = 'Improve initial response time to service requests. Consider implementing automated acknowledgements.';
                        break;
                    case 'Reliability':
                        $recommendationText = 'Work on consistency in service delivery. Develop standard procedures for common request types.';
                        break;
                    case 'Communication':
                        $recommendationText = 'Enhance regular updates to requestors. Consider template-based status notifications.';
                        break;
                    case 'Costs':
                        $recommendationText = 'Review resource allocation and cost efficiency. Consider optimizing processes to reduce time/resource costs.';
                        break;
                    case 'Integrity':
                        $recommendationText = 'Improve transparency in handling requests. Document actions taken more thoroughly.';
                        break;
                    case 'Assurance':
                        $recommendationText = 'Develop expertise in handling complex requests. Consider additional training in problem areas.';
                        break;
                    case 'Outcome':
                        $recommendationText = 'Focus on quality of resolutions. Review completed requests for opportunities to improve outcomes.';
                        break;
                    default:
                        $recommendationText = 'Work on improving ' . $metric . ' rating.';
                }
                
                $recommendations[] = [
                    'area' => 'Customer Satisfaction: ' . $metric,
                    'issue' => 'Low rating for ' . $metric . ' (' . $rating . '/5)',
                    'recommendation' => $recommendationText,
                    'priority' => $priority,
                    'metric' => $rating . '/5',
                    'target' => '4+/5'
                ];
            }
        }
        
        // 4. Check service categories with low completion rates or high volume
        if (count($categoryStats) > 0) {
            foreach ($categoryStats as $category => $data) {
                if ($data['total'] >= 3) { // Only consider categories with enough requests
                    $categoryCompletionRate = $data['total'] > 0 ? ($data['completed'] / $data['total']) * 100 : 0;
                    
                    if ($categoryCompletionRate < 30 && $data['in_progress'] > 0) {
                        $recommendations[] = [
                            'area' => 'Service Category: ' . $category,
                            'issue' => 'Very low completion rate (' . round($categoryCompletionRate) . '%) with ' . $data['in_progress'] . ' requests in progress',
                            'recommendation' => 'Prioritize completing In Progress ' . $category . ' requests.',
                            'priority' => 'high',
                            'metric' => round($categoryCompletionRate) . '%',
                            'target' => '80%+'
                        ];
                    } elseif ($categoryCompletionRate < 50 && $data['in_progress'] > 0) {
                        $recommendations[] = [
                            'area' => 'Service Category: ' . $category,
                            'issue' => 'Low completion rate (' . round($categoryCompletionRate) . '%) with ' . $data['in_progress'] . ' requests in progress',
                            'recommendation' => 'Allocate more time to ' . $category . ' requests.',
                            'priority' => 'medium',
                            'metric' => round($categoryCompletionRate) . '%',
                            'target' => '80%+'
                        ];
                    }
                }
            }
        }
        
        // 5. Add general improvement if we don't have many specific ones
        /*if (count($recommendations) == 0) {
            $recommendations[] = [
                'area' => 'General Performance',
                'issue' => 'Maintaining current performance levels',
                'recommendation' => 'Continue current practices while looking for optimization opportunities.',
                'priority' => 'low',
                'metric' => 'N/A',
                'target' => 'N/A'
            ];
        } */
        
        // Sort recommendations by priority (high, medium, low)
        usort($recommendations, function($a, $b) {
            $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
        });
        
        return $recommendations;
    }

    public function markAsUnresolvable(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required',
            'request_type' => 'required|in:student,faculty',
            'unresolvable_reason' => 'required|string|max:2000',
            'unresolvable_actions_taken' => 'nullable|string|max:1000',
        ]);

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Get the currently logged in UITC staff ID
            $currentStaffId = Auth::guard('admin')->user()->id;
            $staffName = Auth::guard('admin')->user()->name;
            
            $serviceRequest = null;
            $user = null;

            // Based on request type, find the appropriate request
            if ($validatedData['request_type'] === 'student') {
                $serviceRequest = StudentServiceRequest::findOrFail($validatedData['request_id']);
            } else { // faculty
                $serviceRequest = FacultyServiceRequest::findOrFail($validatedData['request_id']);
            }
            
            // Ensure the request is assigned to the current UITC staff
            if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Unauthorized to mark this request as unresolvable.',
                    'error' => 'Request not assigned to current staff'
                ], 403);
            }

            // Update the request status and add unresolvable details
            $serviceRequest->status = 'Unresolvable'; // New status
            $serviceRequest->completion_report = $validatedData['unresolvable_reason']; // Re-using this field
            $serviceRequest->actions_taken = $validatedData['unresolvable_actions_taken'] ?? null; // Re-using this field
            $serviceRequest->completed_at = now(); // Marking the time it was closed
            $serviceRequest->save();
            
            $user = $serviceRequest->user; // Get the user associated with the request for notifications

            if ($user) {
                // Notify the user
                $user->notify(new \App\Notifications\RequestUnresolvableNotification($serviceRequest, $staffName));
            }

            // Notify all admins
            $admins = \App\Models\Admin::where('role', 'Admin')->get();
            $requestingUserName = $user ? ($user->first_name . ' ' . $user->last_name) : 'Unknown User';
            $serviceName = $this->getServiceName($serviceRequest->service_category); // Get formatted service name

            foreach ($admins as $admin) {
                // Optionally, skip notifying the staff member if they are also an admin
                // if ($admin->id === $currentStaffId) {
                // continue;
                // }
                $admin->notify(new \App\Notifications\AdminRequestUnresolvableNotification($serviceRequest, $staffName, $requestingUserName, $serviceName));
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Request marked as unresolvable successfully.',
                'request' => $serviceRequest
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Error marking request as unresolvable: Request not found.', [
                'request_id' => $validatedData['request_id'],
                'request_type' => $validatedData['request_type'],
                'staff_id' => $currentStaffId ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error('Error marking request as unresolvable: ' . $e->getMessage(), [
                'request_id' => $validatedData['request_id'],
                'request_type' => $validatedData['request_type'],
                'staff_id' => $currentStaffId ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to mark request as unresolvable: ' . $e->getMessage()
            ], 500);
        }
    }
}
