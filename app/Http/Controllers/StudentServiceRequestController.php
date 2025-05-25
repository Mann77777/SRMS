<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ServiceRequestReceived;
use App\Notifications\RequestCancelledNotification;
use App\Notifications\AdminRequestCancelledNotification; // Added this line
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\RequestSubmitted;
use App\Utilities\DateChecker;
use App\Helpers\ServiceHelper; // Ensure helper is imported

class StudentServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to submit a request.');
        }

        // Check if the user is verified by admin
        if (!$user->admin_verified || $user->verification_status !== 'verified') {
            return redirect()->back()->with('error', 'Your account is not yet verified by the administrator. You cannot submit service requests until your account is verified.')->withInput();
        }

        // Base validation rules
        $rules = [
            'service_category' => 'required|string',
            'agreeTerms' => 'accepted',
            'additional_notes' => 'nullable|string|max:1000', // Validate optional notes
        ];

        // Add category-specific validation rules
        $serviceCategory = $request->input('service_category');
        switch ($serviceCategory) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
            case 'reset_ers_password': // Added missing ERS password reset case
                $rules['account_email'] = 'required|email|max:255';
                break;
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $rules['data_type'] = 'required|string|max:255';
                $rules['new_data'] = 'required|string|max:1000';
                $rules['supporting_document'] = 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'; // Made required as per form
                break;
            case 'request_led_screen':
                $rules['preferred_date'] = 'required|date|after_or_equal:today';
                $rules['preferred_time'] = 'required|string'; // Consider time format validation if needed
                break;
            case 'others':
                $rules['description'] = 'required|string|max:1000';
                break;
            // Add case for 'create' if it requires specific fields, otherwise it only needs base validation
            case 'create':
                 // No additional fields needed for 'create' based on the form structure
                 break;
        }

        // Validate the request data
        $validatedData = $request->validate($rules);

        // Create a new student service request (only after validation passes)
        $studentRequest = new StudentServiceRequest();
        $studentRequest->user_id = $user->id;
        $studentRequest->service_category = $validatedData['service_category']; // Use validated data
        // Assign user details from Auth using the new first_name and last_name attributes
        $studentRequest->first_name = $user->first_name; // Use the first_name field from User model
        $studentRequest->last_name = $user->last_name;   // Use the last_name field from User model
        // Ensure student_id exists on the user model before assigning
        $studentRequest->student_id = $user->student_id ?? 'N/A'; // Provide default if potentially missing
        $studentRequest->status = 'Pending';

        // Assign validated optional fields based on service category
        switch ($serviceCategory) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
            case 'reset_ers_password':
                $studentRequest->account_email = $validatedData['account_email'];
                break;
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $studentRequest->data_type = $validatedData['data_type'];
                $studentRequest->new_data = $validatedData['new_data'];
                // Handle file upload (already validated)
                if ($request->hasFile('supporting_document')) {
                    $file = $request->file('supporting_document');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('supporting_documents', $filename, 'public');
                    $studentRequest->supporting_document = $path;
                }
                break;
            case 'request_led_screen':
                $studentRequest->preferred_date = $validatedData['preferred_date'];
                $studentRequest->preferred_time = $validatedData['preferred_time'];
                break;
            case 'others':
                $studentRequest->description = $validatedData['description'];
                break;
        }

        // Assign validated optional additional notes
        $studentRequest->additional_notes = $validatedData['additional_notes'] ?? null;

        // Add logging before save
         \Log::info('Attempting to save student request with data:', [
             'user_id' => $studentRequest->user_id,
             'service_category' => $studentRequest->service_category, // Log the value being saved
             'first_name' => $studentRequest->first_name, // From Auth::user()->first_name
             'last_name' => $studentRequest->last_name,   // From Auth::user()->last_name
             'student_id' => $studentRequest->student_id, // From Auth::user()
             'status' => $studentRequest->status,
              'additional_notes' => $studentRequest->additional_notes, // Log this too
          ]);
  
        // Save the request
        $saveSuccess = false;
        try {
            $saveSuccess = $studentRequest->save();
            if ($saveSuccess) {
                \Log::info('Student request SAVED successfully.', ['request_id' => $studentRequest->id]);

                // Generate display ID only on successful save
                $displayId = 'SSR-' . date('Ymd') . '-' . str_pad($studentRequest->id, 4, '0', STR_PAD_LEFT);

                // Check if today is a non-working day (weekend or holiday)
                $nonWorkingDayInfo = DateChecker::isNonWorkingDay(Carbon::today());

                // Send email notification to the user
                Notification::route('mail', $user->email)
                    ->notify(new ServiceRequestReceived(
                        $displayId,
                        $studentRequest->service_category,
                        $user->first_name . ' ' . $user->last_name, // Combine first and last name
                        $nonWorkingDayInfo
                    ));

                // Notify admin users about the new request
                try {
                    $admins = \App\Models\Admin::where('role', 'Admin')->get();
                    \Log::info('Notifying admins about new student request', [
                        'request_id' => $studentRequest->id,
                        'admin_count' => $admins->count()
                    ]);
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\RequestSubmitted($studentRequest));
                        \Log::info('Notification sent to admin', [
                            'admin_id' => $admin->id,
                            'admin_name' => $admin->name
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to notify admins about new student request', [
                        'request_id' => $studentRequest->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue even if admin notification fails, but log it.
                }

                // Redirect back with success modal data
                return redirect()->back()->with([
                    'showSuccessModal' => true,
                    'requestId' => $displayId,
                    'serviceCategory' => $studentRequest->service_category,
                    'nonWorkingDayInfo' => $nonWorkingDayInfo
                ]);

            } else {
                \Log::error('Student request save() method returned false.', ['request_data' => $studentRequest->toArray()]);
                return redirect()->back()->with('error', 'Failed to save the service request. Please try again.')->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Exception during Student request save.', [
                'error_message' => $e->getMessage(),
                'request_data' => $studentRequest->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while saving the request: ' . $e->getMessage())->withInput();
        }
    } // This closes the store method

    // Removed duplicated notification block from here

    public function create()
    {
        $today = Carbon::today(); // Use today() for date-only comparisons if time isn't relevant
        $nonWorkingDayInfo = DateChecker::isNonWorkingDay($today);
        $holidayDetails = null;

        if ($nonWorkingDayInfo['isNonWorkingDay'] && $nonWorkingDayInfo['type'] === 'holiday') {
            $holidayDetails = DateChecker::getHolidayDetailsForDate($today);
        }

        $isWeekend = $nonWorkingDayInfo['isNonWorkingDay'] && $nonWorkingDayInfo['type'] === 'weekend';
        $isHoliday = $nonWorkingDayInfo['isNonWorkingDay'] && $nonWorkingDayInfo['type'] === 'holiday';
        
        // For academic periods, we'd need to check the holiday name if it's stored that way
        // This assumes 'Semestral Break' or 'Exam Week' would be the name of a holiday entry.
        $isSemestralBreak = $isHoliday && $holidayDetails && stripos($holidayDetails->name, 'semestral break') !== false;
        $isExamWeek = $isHoliday && $holidayDetails && stripos($holidayDetails->name, 'exam week') !== false;

        // Construct appropriate message
        $statusMessage = null;
        if ($isSemestralBreak) { // Check specific academic periods first
            $statusMessage = "Note: We are currently on semestral break. Response times may be longer than usual.";
        } elseif ($isExamWeek) {
            $statusMessage = "Note: It's exam week. Priority will be given to academic system issues.";
        } elseif ($isWeekend) {
            $statusMessage = "Note: Today is a weekend. Your request will be processed on the next business day.";
        } elseif ($isHoliday) { // General holiday if not an academic period
            $statusMessage = "Note: Today is a holiday ({$holidayDetails->name}). Your request will be processed on the next business day.";
        }

        // The view path seems to be 'users.student-request' based on other methods,
        // but the original code had 'user.service_requests.create'. Confirming 'users.student-request'
        // or if 'user.service_requests.create' is indeed the correct path for the form.
        // Assuming 'users.student-request' is the main form view.
        // If 'user.service_requests.create' is a different view, adjust the view name.
        // For now, keeping the original view name from the provided code.
        return view('users.student-request', compact('statusMessage'));
    }

    public function myRequests(Request $request)
    {
        $user = Auth::user();

        if($user->role === "Student")
        {
            \Log::info('Request parameters for student requests:', [
                'status' => $request->status,
                'search' => $request->search,
                'page' => $request->page
            ]);

            $query = StudentServiceRequest::where('user_id', Auth::id());

            if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
                \Log::info('Filtering by status: ' . $request->status);
                $query->where('status', $request->status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                \Log::info('Searching for: ' . $search);
                $extractedId = null;
                if (preg_match('/^SSR-\d{8}-(\d+)$/i', $search, $matches)) {
                    $extractedId = (int) $matches[1];
                    \Log::info('Extracted Request ID from search term: ' . $extractedId);
                }
                $query->where(function($q) use ($search, $extractedId) {
                    $q->where('service_category', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                    if ($extractedId !== null) {
                        $q->orWhere('id', '=', $extractedId);
                    }
                });
            }

            $totalRecords = $query->count();
            \Log::info('Total filtered records: ' . $totalRecords);
            $requests = $query->orderBy('created_at', 'asc')->paginate(10);
            $requests->appends($request->except('page'));
            \Log::info('Paginated results count: ' . $requests->count());

            // Log the actual data being passed to the view
            \Log::info('Requests data being passed to view for user ' . Auth::id() . ':', $requests->toArray());


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
        if ($request->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        if ($request->status !== 'Completed') {
            return redirect()->back()->with('error', 'Survey is only available for completed requests');
        }
        return view('users.customer-satisfaction', compact('request'));
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
        if ($serviceRequest->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        $serviceRequest->survey_rating = $validatedData['rating'];
        $serviceRequest->survey_comments = $validatedData['comments'];
        $serviceRequest->survey_issue_resolved = $validatedData['issue_resolved'];
        $serviceRequest->save();
        return redirect()->route('request.history')->with('success', 'Thank you for your feedback!');
    }

    public function getRequestDetails($id)
    {
        try {
            $request = StudentServiceRequest::with('assignedUITCStaff')->findOrFail($id);
            if ($request->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $responseData = $request->toArray();
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

    public function cancelRequest($id)
    {
        try {
            $request = StudentServiceRequest::findOrFail($id);
            if ($request->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            if ($request->status === 'Completed' || $request->status === 'Rejected' || $request->status === 'Cancelled') {
                return response()->json(['error' => 'This request cannot be cancelled because it is already ' . $request->status], 400);
            }
            $request->status = 'Cancelled';
            $request->save();

            // Send cancellation notification to the user
            $user = Auth::user();
            $user->notify(new \App\Notifications\RequestCancelledNotification(
                $request->id,
                $request->service_category,
                $user->first_name . ' ' . $user->last_name,
                null, // Pass cancellation reason if available
                $request->created_at
            ));

            // Notify all admins
            $admins = Admin::where('role', 'Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminRequestCancelledNotification(
                    $request->id,
                    $request->service_category,
                    $user->first_name . ' ' . $user->last_name,
                    $user->role, // Should be 'Student'
                    Carbon::now() // Timestamp of cancellation
                ));
            }

            return response()->json(['message' => 'Request cancelled successfully']);
        } catch (\Exception $e) {
            \Log::error('Error cancelling request: ' . $e->getMessage());
            return response()->json(['error' => 'Request not found'], 404);
        }
    }

    public function edit($id)
    {
        $request = StudentServiceRequest::findOrFail($id);
        if ($request->user_id !== Auth::id()) {
            return redirect()->route('myrequests')->with('error', 'Unauthorized action.');
        }
        if ($request->status !== 'Pending') {
             return redirect()->route('myrequests')->with('error', 'This request cannot be edited as it is no longer pending.');
        }
        // Use the static helper method
        $formattedServiceName = ServiceHelper::formatServiceCategory($request->service_category, $request->description);
        return view('users.edit-request', compact('request', 'formattedServiceName'));
    }

    public function update(Request $requestData, $id)
    {
        $serviceRequest = StudentServiceRequest::findOrFail($id);
        if ($serviceRequest->user_id !== Auth::id()) {
            return redirect()->route('myrequests')->with('error', 'Unauthorized action.');
        }
        if ($serviceRequest->status !== 'Pending') {
             return redirect()->route('myrequests')->with('error', 'This request cannot be edited as it is no longer pending.');
        }

        // Removed validation for first_name, last_name, student_id
        $rules = [
             // 'additional_notes' => 'nullable|string|max:1000', // Removed validation
             'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
         ];
         switch ($serviceRequest->service_category) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
                $rules['account_email'] = 'required|email|max:255';
                break;
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $rules['data_type'] = 'required|string|max:255';
                $rules['new_data'] = 'required|string|max:1000';
                break;
            case 'request_led_screen':
                $rules['preferred_date'] = 'required|date';
                $rules['preferred_time'] = 'required|string';
                break;
            case 'others':
                $rules['description'] = 'required|string|max:1000';
                break;
        }
        $validatedData = $requestData->validate($rules);

         try {
              // Removed assignment for first_name, last_name, student_id
              // $serviceRequest->first_name = $validatedData['first_name'];
              // $serviceRequest->last_name = $validatedData['last_name'];
              // $serviceRequest->student_id = $validatedData['student_id'];

              switch ($serviceRequest->service_category) {
                case 'reset_email_password':
                case 'reset_tup_web_password':
                    $serviceRequest->account_email = $validatedData['account_email'];
                    break;
                case 'change_of_data_ms':
                case 'change_of_data_portal':
                    $serviceRequest->data_type = $validatedData['data_type'];
                    $serviceRequest->new_data = $validatedData['new_data'];
                    break;
                case 'request_led_screen':
                    $serviceRequest->preferred_date = $validatedData['preferred_date'];
                    $serviceRequest->preferred_time = $validatedData['preferred_time'];
                    break;
                case 'others':
                     $serviceRequest->description = $validatedData['description'];
                     break;
             }
 
             // Save additional notes if provided
             $serviceRequest->additional_notes = $requestData->input('additional_notes'); // Added this line
 
             if ($requestData->hasFile('supporting_document')) {
                 if ($serviceRequest->supporting_document) {
                    Storage::disk('public')->delete($serviceRequest->supporting_document);
                }
                $file = $requestData->file('supporting_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('supporting_documents', $filename, 'public');
                $serviceRequest->supporting_document = $path;
            }

            $serviceRequest->save();
            return redirect()->route('myrequests')->with('success', 'Request updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating student service request:', [
                'request_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the request. Please try again.')->withInput();
        }
    }
}
