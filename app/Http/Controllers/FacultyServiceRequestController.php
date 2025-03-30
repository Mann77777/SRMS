<?php

namespace App\Http\Controllers;

use App\Models\FacultyServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Notifications\ServiceRequestReceived;
use Illuminate\Support\Facades\Notification;

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
    
            Log::info('Service request created:', ['id' => $serviceRequest->id]);
    
            // Generate a unique display ID with FSR prefix
            $displayId = 'FSR-' . date('Ymd') . '-' . str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT);
    
            if (Auth::check() && $request->user()->email) {
                Notification::route('mail', $request->user()->email)
                    ->notify(new ServiceRequestReceived(
                        $displayId, // Use the formatted display ID instead of raw database ID
                        $request->input('service_category'),
                        $filteredData['first_name'] . ' ' . $filteredData['last_name']
                    ));
                    
                Log::info('Email notification sent to: ' . $request->user()->email);
            }
            
            // Redirect back with success modal data
            return redirect()->back()->with([
                'showSuccessModal' => true,
                'requestId' => $displayId, // Use the formatted display ID
                'serviceCategory' => $request->input('service_category')
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
    public function myRequests(Request $request = null)
    {
        $user = Auth::user();
    
        if($user->role === "Faculty & Staff")
        {
            // If $request is null, initialize it to get an empty request object
            if ($request === null) {
                $request = new Request();
            }
            
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
                
                $query->where(function($q) use ($search) {
                    $q->where('service_category', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('id', 'like', '%' . $search . '%');
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
}