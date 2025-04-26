<?php

namespace App\Http\Controllers;

use App\Models\FacultyServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Notifications\ServiceRequestReceived;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\RequestSubmitted;
use Carbon\Carbon;
use App\Utilities\DateChecker;

class FacultyServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Incoming request data:', $request->all());
    
            // Basic validation for common required fields
            $request->validate([
                'service_category' => 'required',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
            ]);
    
            // Get the ACTUAL database columns, not just what's in the model
            $tableColumns = Schema::getColumnListing('faculty_service_requests');
            
            // Get all input data
            $inputData = $request->all();
            
            // Initialize filtered data array
            $filteredData = [];
            
            // Only include fields that exist in the database
            foreach ($tableColumns as $column) {
                if (isset($inputData[$column])) {
                    $filteredData[$column] = $inputData[$column];
                }
            }
            
            // Handle special field mapping
            if (isset($inputData['problems_encountered']) && in_array('problem_encountered', $tableColumns)) {
                $filteredData['problem_encountered'] = $inputData['problems_encountered'];
            }
            
            // Add user_id if authenticated
            if (Auth::check()) {
                $filteredData['user_id'] = Auth::id();
            }
    
            // Add default status
            $filteredData['status'] = 'Pending';
    
            // Handle file upload
            if ($request->hasFile('supporting_document') && in_array('supporting_document', $tableColumns)) {
                $path = $request->file('supporting_document')->store('documents', 'public');
                $filteredData['supporting_document'] = $path;
            }
    
            // Handle DTR specific fields
            if ($request->input('service_category') === 'dtr') {
                if (in_array('dtr_months', $tableColumns)) {
                    $filteredData['dtr_months'] = $request->input('dtr_months');
                }
                if (in_array('dtr_with_details', $tableColumns)) {
                    $filteredData['dtr_with_details'] = $request->has('dtr_with_details') ? 1 : 0;
                }
            }
    
            // Log filtered data
            Log::info('Filtered data for submission:', $filteredData);
    
            // Create the request with filtered data
            $serviceRequest = FacultyServiceRequest::create($filteredData);
            $admins = Admin::where('role', 'Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new RequestSubmitted($serviceRequest));
}
    
            Log::info('Service request created:', ['id' => $serviceRequest->id]);
    
            // Generate a unique display ID with FSR prefix
            $displayId = 'FSR-' . date('Ymd') . '-' . str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT);
    
            // Check if today is a non-working day (weekend or holiday)
            $nonWorkingDayInfo = DateChecker::isNonWorkingDay();
    
            if (Auth::check() && $request->user()->email) {
                Notification::route('mail', $request->user()->email)
                    ->notify(new ServiceRequestReceived(
                        $displayId, // Formatted display ID
                        $request->input('service_category'),
                        $filteredData['first_name'] . ' ' . $filteredData['last_name'],
                        $nonWorkingDayInfo // Pass the non-working day info
                    ));
                    
                Log::info('Email notification sent to: ' . $request->user()->email);
            }
            
            // Redirect back with success modal data
            return redirect()->back()->with([
                'showSuccessModal' => true,
                'requestId' => $displayId, // Formatted display ID
                'serviceCategory' => $request->input('service_category'),
                'nonWorkingDayInfo' => $nonWorkingDayInfo // Add the non-working day info to the session
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error creating service request:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()->back()
                ->with('error', 'Error submitting request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Check if today is a weekend (Saturday or Sunday)
     *
     * @return bool
     */
    private function isWeekend()
    {
        $dayOfWeek = Carbon::now()->dayOfWeek;
        return $dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY;
    }
    
    // Update signature to require Request object
    public function myRequests(Request $request)
    {
        $user = Auth::user();

        if($user->role === "Faculty & Staff")
        {
            // Remove the null check and creation of new Request object

            // Debug the incoming request parameters
            \Log::info('Request parameters for faculty requests:', [
                'status' => $request->status,
                'search' => $request->search,
                'page' => $request->page
            ]);
            
            // Start building the query
            $query = FacultyServiceRequest::where('user_id', Auth::id());
            
            // Apply status filter if provided - exact match with database value
            if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
                \Log::info('Filtering by status: ' . $request->status);
                $query->where('status', $request->status);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                \Log::info('Searching for: ' . $search);

                // Check if the search term looks like a formatted Request ID (FSR-YYYYMMDD-ID)
                $extractedId = null;
                if (preg_match('/^FSR-\d{8}-(\d+)$/i', $search, $matches)) {
                    $extractedId = (int) $matches[1];
                    \Log::info('Extracted Request ID from search term: ' . $extractedId);
                }

                $query->where(function($q) use ($search, $extractedId) {
                    // Search service category or description
                    $q->where('service_category', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');

                    // If a numeric ID was extracted from the search term, search by that exact ID
                    if ($extractedId !== null) {
                        $q->orWhere('id', '=', $extractedId);
                    }
                    // Optionally, keep the original broad ID search as a fallback if no specific ID was extracted
                    // else {
                    //    $q->orWhere('id', 'like', '%' . $search . '%');
                    // }
                    // For now, let's only search the exact ID if the format matches.
                });
            }
            
            // Count the total records after filtering (before pagination)
            $totalRecords = $query->count();
            \Log::info('Total filtered records: ' . $totalRecords);
            
            // Get the requests with pagination after filtering
            $requests = $query->orderBy('created_at', 'desc')->paginate(10);
            
            // Append query parameters to pagination links
            $requests->appends($request->except('page'));
            
            \Log::info('Paginated results count: ' . $requests->count());
            
            return view('users.myrequests', compact('requests'));
        }
        
        return redirect()->back()->with('error', 'Unauthorized access');
    }

    public function requestHistory()
    {
        $user = Auth::user();
    
        if($user->role === "Faculty & Staff")
        {
            $requests = FacultyServiceRequest::where('user_id', Auth::id())
                ->where('status', 'Completed')
                ->with('assignedUITCStaff')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
    
            return view('users.request-history', compact('requests'));
        }
    
        return redirect()->back()->with('error', 'Unauthorized access');
    }

    public function getRequestDetails($id)
    {
        try {
            // Fetch the request with the assigned_uitc_staff relationship
            $request = FacultyServiceRequest::with('assignedUITCStaff')
                ->findOrFail($id);
                
            // Check if the request belongs to the current user
            if ($request->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Structure the response to include staff information properly
            $responseData = $request->toArray();
            
            // Make sure the assigned staff data is properly included
            if ($request->assignedUITCStaff) {
                $responseData['assigned_uitc_staff'] = [
                    'id' => $request->assignedUITCStaff->id,
                    'name' => $request->assignedUITCStaff->name
                ];
            }
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Error getting request details: ' . $e->getMessage());
            return response()->json(['error' => 'Request not found'], 404);
        }
    }
    public function show($id)
    {
        $request = FacultyServiceRequest::findOrFail($id);
        return view('users.faculty-service-view', ['request' => $request]);
    }

    public function updateRequest(Request $request, $id)
    {
        try {
            $serviceRequest = FacultyServiceRequest::findOrFail($id);
            
            if ($serviceRequest->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            // Get the ACTUAL database columns
            $tableColumns = Schema::getColumnListing('faculty_service_requests');
            
            // Get all input data
            $inputData = $request->all();
            
            // Initialize filtered data array
            $filteredData = [];
            
            // Only include fields that exist in the database
            foreach ($tableColumns as $column) {
                if (isset($inputData[$column])) {
                    $filteredData[$column] = $inputData[$column];
                }
            }
            
            // Handle special field mapping
            if (isset($inputData['problems_encountered']) && in_array('problem_encountered', $tableColumns)) {
                $filteredData['problem_encountered'] = $inputData['problems_encountered'];
            }

            $serviceRequest->update($filteredData);
            return redirect()->back()->with('success', 'Request updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating request:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error updating request');
        }
    }

    public function deleteRequest($id)
    {
        try {
            $serviceRequest = FacultyServiceRequest::findOrFail($id);
            
            if ($serviceRequest->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            $serviceRequest->delete();
            return redirect()->back()->with('success', 'Request deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting request:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error deleting request');
        }
    }

    public function submit(Request $request)
    {
        // Redirect to store method
        return $this->store($request);
    }

    public function showServiceSurvey($requestId)
    {
        $request = FacultyServiceRequest::findOrFail($requestId);
        
        // Ensure only the request owner can access the survey
        if ($request->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Ensure only completed requests can be surveyed
        if ($request->status !== 'Completed') {
            return redirect()->back()->with('error', 'Survey is only available for completed requests');
        }

        return view('users.service-survey', compact('request'));
    }
    
    public function cancelRequest($id)
    {
        try {
            $request = FacultyServiceRequest::findOrFail($id);
            
            // Check if the request belongs to the current user
            if ($request->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Check if the request can be cancelled (not completed or already cancelled)
            if ($request->status === 'Completed' || $request->status === 'Rejected' || $request->status === 'Cancelled') {
                return response()->json([
                    'error' => 'This request cannot be cancelled because it is already ' . $request->status
                ], 400);
            }
            
            // Update the request status to Cancelled
            $request->status = 'Cancelled';
            $request->save();
            
            return response()->json(['message' => 'Request cancelled successfully']);
        } catch (\Exception $e) {
            \Log::error('Error cancelling request: ' . $e->getMessage());
            return response()->json(['error' => 'Request not found'], 404);
        }
    }

    public function create()
    {
        $today = Carbon::now();
        $isWeekend = $today->isWeekend();
        $isHoliday = Holiday::isHoliday($today);
        $isSemestralBreak = Holiday::isAcademicPeriod($today, 'semestral_break');
        $isExamWeek = Holiday::isAcademicPeriod($today, 'exam_week');
        
        // Construct appropriate message
        $statusMessage = null;
        if ($isWeekend) {
            $statusMessage = "Note: Today is a weekend. Your request will be processed on the next business day.";
        } elseif ($isHoliday) {
            $statusMessage = "Note: Today is a holiday. Your request will be processed on the next business day.";
        } elseif ($isSemestralBreak) {
            $statusMessage = "Note: We are currently on semestral break. Response times may be longer than usual.";
        } elseif ($isExamWeek) {
            $statusMessage = "Note: It's exam week. Priority will be given to academic system issues.";
        }
        
        return view('user.service_requests.create', compact('statusMessage'));
    }
}
