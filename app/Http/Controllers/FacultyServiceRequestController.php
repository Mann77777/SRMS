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

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                // This case might be less likely if middleware protects the route, but good practice
                return redirect()->route('login')->with('error', 'You must be logged in to submit a request.');
            }

            // Check if the user is verified by admin
            if (!$user->admin_verified || $user->verification_status !== 'verified') {
                return redirect()->back()->with('error', 'Your account is not yet verified by the administrator. You cannot submit service requests until your account is verified.')->withInput();
            }
    
            // Define base validation rules
            $rules = [
                'service_category' => 'required',
                // 'first_name' and 'last_name' removed - will be fetched from Auth user
                // Add other common fields if needed
            ];

            // Add specific validation rules based on the selected service category
            $serviceCategory = $request->input('service_category');
            switch ($serviceCategory) {
                // (Add cases for other categories as needed for robustness, mirroring the update method)
                // 'account_email' validation removed - will be fetched from Auth user for relevant categories
                case 'reset_email_password':
                case 'reset_tup_web_password':
                case 'reset_ers_password':
                     // No specific validation needed here anymore for account_email
                     break;
                case 'change_of_data_ms':
                case 'change_of_data_portal':
                     $rules['data_type'] = 'required|string|max:255';
                     $rules['new_data'] = 'required|string|max:1000';
                     // Supporting document is optional on create, required validation might be too strict here unless intended
                     $rules['supporting_document'] = 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048';
                     break;
                case 'biometric_record': // Added this case
                case 'dtr':
                     $rules['dtr_months'] = 'required|string|max:255';
                     $rules['dtr_with_details'] = 'sometimes|boolean'; // Keep this for DTR, might not apply to biometric_record but harmless if not submitted
                     break;
                case 'biometrics_enrollement':
                     $rules['middle_name'] = 'nullable|string|max:255'; // Often optional
                     $rules['college'] = 'required|string|max:255';
                     $rules['department'] = 'required|string|max:255';
                     $rules['plantilla_position'] = 'required|string|max:255';
                     $rules['date_of_birth'] = 'required|date';
                     $rules['phone_number'] = 'required|string|max:20'; // Consider adding regex validation
                     $rules['address'] = 'required|string|max:500';
                     $rules['blood_type'] = 'nullable|string|max:10';
                     $rules['emergency_contact_person'] = 'required|string|max:255';
                     $rules['emergency_contact_number'] = 'required|string|max:20'; // Consider adding regex validation
                     break;
                 case 'new_internet':
                 case 'new_telephone':
                 case 'repair_and_maintenance':
                 case 'computer_repair_maintenance':
                 case 'printer_repair_maintenance':
                      $rules['location'] = 'required|string|max:255';
                      // problem_encountered is only required for repair/maintenance types
                      if (in_array($serviceCategory, ['repair_and_maintenance', 'computer_repair_maintenance', 'printer_repair_maintenance'])) {
                          $rules['problem_encountered'] = 'required|string|max:1000';
                      } else {
                          $rules['problem_encountered'] = 'nullable|string|max:1000';
                      }
                      break;
                 case 'request_led_screen':
                      $rules['preferred_date'] = 'required|date|after_or_equal:today'; // Ensure date is not in the past
                      $rules['preferred_time'] = 'required|string'; // Consider time format validation
                      $rules['led_screen_details'] = 'nullable|string|max:1000';
                      break;
                 case 'install_application': // <<< --- ADDED THIS CASE ---
                      $rules['application_name'] = 'required|string|max:255';
                      $rules['installation_purpose'] = 'required|string|max:1000';
                      $rules['installation_notes'] = 'nullable|string|max:1000';
                      $rules['location'] = 'required|string|max:255'; // Location is also shown in the form for this type
                      break;
                 case 'post_publication':
                      $rules['publication_author'] = 'required|string|max:255';
                      $rules['publication_editor'] = 'required|string|max:255';
                      $rules['publication_start_date'] = 'required|date';
                      $rules['publication_end_date'] = 'required|date|after_or_equal:publication_start_date';
                      // Add validation for publication image and file
                      $rules['publication_image_path'] = 'nullable|image|mimes:png,jpg,jpeg|max:25600'; // Max 25MB
                      $rules['publication_file_path'] = 'nullable|file|mimes:pdf,doc,docx,zip|max:25600'; // Max 25MB
                      // TODO: Implement copyright check for publication_image_path
                      break;
                 case 'data_docs_reports':
                      $rules['data_documents_details'] = 'required|string|max:1000';
                      break;
                 case 'others':
                      $rules['description'] = 'required|string|max:1000';
                      break;
            }

            // Validate the request with the combined rules
            // Validate the request with the combined rules and get validated data
            $validatedData = $request->validate($rules);

            // Get the ACTUAL database columns, not just what's in the model
            $tableColumns = Schema::getColumnListing('faculty_service_requests');
            
            // Initialize filtered data array using only validated data
            $filteredData = [];
            
            // Only include validated fields that exist as actual database columns
            // This prevents attempting to save fields that passed validation but don't have a corresponding column
            foreach ($validatedData as $key => $value) {
                if (in_array($key, $tableColumns)) {
                    $filteredData[$key] = $value;
                }
            }
            
            // Handle special field mapping
            if (isset($inputData['problems_encountered']) && in_array('problem_encountered', $tableColumns)) {
                $filteredData['problem_encountered'] = $inputData['problems_encountered'];
            }
            
            // Add user_id and fetch names if authenticated
            if (Auth::check()) {
                $user = Auth::user();
                $filteredData['user_id'] = $user->id;
                // Fetch first_name and last_name from the authenticated user
                if (in_array('first_name', $tableColumns)) {
                    $filteredData['first_name'] = $user->first_name;
                }
                if (in_array('last_name', $tableColumns)) {
                    $filteredData['last_name'] = $user->last_name;
                }
                // Fetch email for password reset categories
                if (in_array($serviceCategory, ['reset_email_password', 'reset_tup_web_password', 'reset_ers_password']) && in_array('account_email', $tableColumns)) {
                    $filteredData['account_email'] = $user->email;
                }
            }

            // Add default status
            $filteredData['status'] = 'Pending';
    
            // Handle file upload
            if ($request->hasFile('supporting_document') && in_array('supporting_document', $tableColumns)) {
                $path = $request->file('supporting_document')->store('documents', 'public');
                $filteredData['supporting_document'] = $path;
            }

            // Handle publication image upload
            if ($request->hasFile('publication_image_path') && in_array('publication_image_path', $tableColumns)) {
                $imagePath = $request->file('publication_image_path')->store('publications/images', 'public');
                $filteredData['publication_image_path'] = $imagePath;
            }

            // Handle publication file upload
            if ($request->hasFile('publication_file_path') && in_array('publication_file_path', $tableColumns)) {
                $filePath = $request->file('publication_file_path')->store('publications/files', 'public');
                $filteredData['publication_file_path'] = $filePath;
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
            $requests = $query->orderBy('created_at', 'asc')->paginate(10);
            
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

            // Return the full request object directly. Laravel will serialize it correctly.
            // This ensures all attributes, including those specific to certain service types,
            // and loaded relationships (like assignedUITCStaff) are included.
            return response()->json($request);
        } catch (\Exception $e) {
            \Log::error('Error getting request details: ' . $e->getMessage());
            // Return a more specific error for not found vs. other errors if possible
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                 return response()->json(['error' => 'Request not found'], 404);
            }
            return response()->json(['error' => 'An error occurred while fetching request details.'], 500); // General server error
        }
    }
    public function show($id)
    {
        $request = FacultyServiceRequest::findOrFail($id);
        return view('users.faculty-request-view', ['request' => $request]);
    }

    /**
     * Show the form for editing the specified faculty service request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $request = FacultyServiceRequest::findOrFail($id);

        // Authorization: Ensure the logged-in user owns this request
        if ($request->user_id !== Auth::id()) {
            return redirect()->route('myrequests')->with('error', 'Unauthorized action.');
        }

        // Prevent editing if the request is not in a pending state
        if ($request->status !== 'Pending') {
             return redirect()->route('myrequests')->with('error', 'This request cannot be edited as it is no longer pending.');
        }

        // Format the service category name before passing to the view
        // Format the service category name using the static helper method
        $formattedServiceName = \App\Helpers\ServiceHelper::formatServiceCategory($request->service_category, $request->description);

        // Pass the request data and the formatted name to the edit view
        return view('users.edit-request', compact('request', 'formattedServiceName'));
    }

    /**
     * Update the specified faculty service request in storage.
     * Renamed from updateRequest for consistency.
     *
     * @param  \Illuminate\Http\Request  $requestData
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $requestData, $id) // Renamed parameter and method
    {
        $serviceRequest = FacultyServiceRequest::findOrFail($id); // Renamed variable

        // Authorization: Ensure the logged-in user owns this request
        if ($serviceRequest->user_id !== Auth::id()) {
            return redirect()->route('myrequests')->with('error', 'Unauthorized action.');
        }

        // Prevent editing if the request is not in a pending state
        if ($serviceRequest->status !== 'Pending') {
             return redirect()->route('myrequests')->with('error', 'This request cannot be edited as it is no longer pending.');
        }

        // --- Validation ---
        // Define validation rules based on FacultyServiceRequest fields and service category
         $rules = [
             // 'first_name' and 'last_name' removed - should not be updated from form
             'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
             'remove_supporting_document' => 'nullable|in:1', // For checkbox to remove file
             'publication_image_path' => 'nullable|image|mimes:png,jpg,jpeg|max:25600',
             'remove_publication_image' => 'nullable|in:1',
             'publication_file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:25600',
             'remove_publication_file' => 'nullable|in:1',
            // Add other common faculty fields if necessary
        ];

        // Add specific validation based on the *original* service category
        // (Similar logic as Student controller, adjust fields for Faculty model)
        switch ($serviceRequest->service_category) {
            // 'account_email' validation removed - should not be updated from form
            case 'reset_email_password':
            case 'reset_tup_web_password':
            case 'reset_ers_password':
                 // No specific validation needed here anymore for account_email
                 break;
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                 $rules['data_type'] = 'required|string|max:255';
                 $rules['new_data'] = 'required|string|max:1000';
                 break;
            case 'biometric_record': // Added this case
            case 'dtr':
                 $rules['dtr_months'] = 'required|string|max:255';
                 $rules['dtr_with_details'] = 'sometimes|boolean'; // Use boolean validation
                 break;
            case 'biometrics_enrollement':
                 // Add validation for all relevant biometrics fields
                 $rules['middle_name'] = 'nullable|string|max:255';
                 $rules['college'] = 'required|string|max:255';
                 $rules['department'] = 'required|string|max:255';
                 $rules['plantilla_position'] = 'required|string|max:255';
                 $rules['date_of_birth'] = 'required|date';
                 $rules['phone_number'] = 'required|string|max:20';
                 $rules['address'] = 'required|string|max:500';
                 $rules['blood_type'] = 'nullable|string|max:10';
                 $rules['emergency_contact_person'] = 'required|string|max:255';
                 $rules['emergency_contact_number'] = 'required|string|max:20';
                 break;
            case 'new_internet':
            case 'new_telephone':
            case 'repair_and_maintenance':
            case 'computer_repair_maintenance':
            case 'printer_repair_maintenance':
                 $rules['location'] = 'required|string|max:255';
                 $rules['problem_encountered'] = 'required|string|max:1000'; // Mapped from problems_encountered
                 break;
            case 'request_led_screen':
                 $rules['preferred_date'] = 'required|date';
                 $rules['preferred_time'] = 'required|string';
                 $rules['led_screen_details'] = 'nullable|string|max:1000';
                 break;
            case 'install_application':
                 $rules['application_name'] = 'required|string|max:255';
                 $rules['installation_purpose'] = 'required|string|max:1000';
                 $rules['installation_notes'] = 'nullable|string|max:1000';
                 break;
            case 'post_publication':
                 $rules['publication_author'] = 'required|string|max:255';
                 $rules['publication_editor'] = 'required|string|max:255';
                 $rules['publication_start_date'] = 'required|date';
                 $rules['publication_end_date'] = 'required|date|after_or_equal:publication_start_date';
                 // TODO: Implement copyright check for publication_image_path update
                 break;
            case 'data_docs_reports':
                 $rules['data_documents_details'] = 'required|string|max:1000';
                 break;
            case 'others':
                 $rules['description'] = 'required|string|max:1000';
                 break;
        }

        $validatedData = $requestData->validate($rules);

        // --- Update Logic ---
        try {
            // Get the ACTUAL database columns to ensure we only update existing fields
            $tableColumns = Schema::getColumnListing('faculty_service_requests');
            
            // Prepare data for update, only including validated fields that exist in the table
             $updateData = [];
             foreach ($validatedData as $key => $value) {
                 // Skip file input, removal flag, names, and account email
                 if (in_array($key, ['supporting_document', 'remove_supporting_document', 'first_name', 'last_name', 'account_email'])) {
                     continue;
                 }

                // Handle special mapping for problem_encountered
                if ($key === 'problem_encountered' && in_array('problem_encountered', $tableColumns)) {
                     $updateData['problem_encountered'] = $value;
                } elseif (in_array($key, $tableColumns)) {
                    // Handle boolean conversion for dtr_with_details
                    if ($key === 'dtr_with_details') {
                        // Ensure value is explicitly true/false (or 1/0) based on presence
                        $updateData[$key] = $requestData->has('dtr_with_details');
                    } else {
                        $updateData[$key] = $value;
                    }
                }
            }

             // Handle file upload if a new one is provided
            if ($requestData->hasFile('supporting_document') && in_array('supporting_document', $tableColumns)) {
                // Delete old file if it exists
                if ($serviceRequest->supporting_document) {
                    Storage::disk('public')->delete($serviceRequest->supporting_document);
                }
                // Store new file
                $file = $requestData->file('supporting_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('documents', $filename, 'public'); // Use 'documents' folder
                $updateData['supporting_document'] = $path;
            } elseif ($requestData->has('remove_supporting_document') && $requestData->input('remove_supporting_document') == '1' && in_array('supporting_document', $tableColumns)) {
                 // Handle removal of existing document
                 if ($serviceRequest->supporting_document) {
                     Storage::disk('public')->delete($serviceRequest->supporting_document);
                     $updateData['supporting_document'] = null;
                 }
            }

            // Handle publication image update/removal
            if ($requestData->hasFile('publication_image_path') && in_array('publication_image_path', $tableColumns)) {
                if ($serviceRequest->publication_image_path) {
                    Storage::disk('public')->delete($serviceRequest->publication_image_path);
                }
                $imagePath = $requestData->file('publication_image_path')->store('publications/images', 'public');
                $updateData['publication_image_path'] = $imagePath;
            } elseif ($requestData->has('remove_publication_image') && $requestData->input('remove_publication_image') == '1' && in_array('publication_image_path', $tableColumns)) {
                if ($serviceRequest->publication_image_path) {
                    Storage::disk('public')->delete($serviceRequest->publication_image_path);
                    $updateData['publication_image_path'] = null;
                }
            }

            // Handle publication file update/removal
            if ($requestData->hasFile('publication_file_path') && in_array('publication_file_path', $tableColumns)) {
                if ($serviceRequest->publication_file_path) {
                    Storage::disk('public')->delete($serviceRequest->publication_file_path);
                }
                $filePath = $requestData->file('publication_file_path')->store('publications/files', 'public');
                $updateData['publication_file_path'] = $filePath;
            } elseif ($requestData->has('remove_publication_file') && $requestData->input('remove_publication_file') == '1' && in_array('publication_file_path', $tableColumns)) {
                if ($serviceRequest->publication_file_path) {
                    Storage::disk('public')->delete($serviceRequest->publication_file_path);
                    $updateData['publication_file_path'] = null;
                }
            }

            // Update the request
            $serviceRequest->update($updateData);

            return redirect()->route('myrequests')->with('success', 'Request updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating faculty service request:', [
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

    // Removed private formatServiceCategory method - Use App\Helpers\ServiceHelper::formatServiceCategory instead
}
