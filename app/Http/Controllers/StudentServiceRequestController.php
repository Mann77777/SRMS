<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ServiceRequestReceived;
use Illuminate\Support\Facades\Notification;
class StudentServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        // Validate basic required fields
        $validatedData = $request->validate([
            'service_category' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'student_id' => 'required|string',
            'agreeTerms' => 'accepted',
         
        ]);
    
        // Create a new student service request
        $studentRequest = new StudentServiceRequest();
        $studentRequest->user_id = Auth::id();
        $studentRequest->service_category = $request->input('service_category');
        $studentRequest->first_name = $request->input('first_name');
        $studentRequest->last_name = $request->input('last_name');
        $studentRequest->student_id = $request->input('student_id');
        
        // Handle optional fields based on service category
        switch($request->input('service_category')) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
                $studentRequest->account_email = $request->input('account_email');
                break;
            
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $studentRequest->data_type = $request->input('data_type');
                $studentRequest->new_data = $request->input('new_data');
                
                // Handle file upload for supporting document
                if ($request->hasFile('supporting_document')) {
                    $file = $request->file('supporting_document');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('supporting_documents', $filename, 'public');
                    $studentRequest->supporting_document = $path;
                }
                break;
            
            case 'request_led_screen':
                $studentRequest->preferred_date = $request->input('preferred_date');
                $studentRequest->preferred_time = $request->input('preferred_time');
                break;
            
            case 'others':
                $studentRequest->description = $request->input('description');
                break;
        }
    
        // Optional additional notes
        $studentRequest->additional_notes = $request->input('additional_notes');
        
        // Save the request
        $studentRequest->save();
    
        // Generate a unique display ID with SSR prefix
        $displayId = 'SSR-' . date('Ymd') . '-' . str_pad($studentRequest->id, 4, '0', STR_PAD_LEFT);
    
        // Send email notification
        Notification::route('mail', $request->user()->email)
            ->notify(new ServiceRequestReceived(
                $displayId, // Use the formatted display ID instead of raw database ID
                $studentRequest->service_category,
                $studentRequest->first_name . ' ' . $studentRequest->last_name
        ));
         
        // Redirect back with success modal data
        return redirect()->back()->with([
            'showSuccessModal' => true,
            'requestId' => $displayId, // Use the formatted display ID
            'serviceCategory' => $studentRequest->service_category
        ]);
    }



    // New method to show student's requestspublic function myRequests(Request $request = null)
    public function myRequests(Request $request = null)
    {
        $user = Auth::user();
        
        if($user->role === "Student")
        {
            // If $request is null, initialize it to get an empty request object
            if ($request === null) {
                $request = new Request();
            }
            
            // Debug the incoming request parameters
            \Log::info('Request parameters for student requests:', [
                'status' => $request->status,
                'search' => $request->search,
                'page' => $request->page
            ]);
            
            // Start building the query
            $query = StudentServiceRequest::where('user_id', Auth::id());
            
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

       public function show($id)
    {
        $request = StudentServiceRequest::findOrFail($id);
       return view('users.student-request-view', ['request' => $request]);
    }

    public function requestHistory()
    {
        $user = Auth::user();

        if($user->role === "Student")
        {
            $requests = StudentServiceRequest::where('user_id', Auth::id())
                ->where('status', 'Completed')
                ->with('assignedUITCStaff')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            return view('users.request-history', compact('requests'));
        }

        return redirect()->back()->with('error', 'Unauthorized access');
    }

    public function showServiceSurvey($requestId)
    {
        $request = StudentServiceRequest::findOrFail($requestId);
        
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

    public function submitServiceSurvey(Request $request)
    {
        $validatedData = $request->validate([
            'request_id' => 'required|exists:student_service_requests,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:500',
            'issue_resolved' => 'required|in:yes,no'
        ]);

        $serviceRequest = StudentServiceRequest::findOrFail($validatedData['request_id']);
        
        // Ensure only the request owner can submit the survey
        if ($serviceRequest->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Save survey results (you might want to create a separate Survey model)
        $serviceRequest->survey_rating = $validatedData['rating'];
        $serviceRequest->survey_comments = $validatedData['comments'];
        $serviceRequest->survey_issue_resolved = $validatedData['issue_resolved'];
        $serviceRequest->save();

        return redirect()->route('request.history')->with('success', 'Thank you for your feedback!');
    }
}