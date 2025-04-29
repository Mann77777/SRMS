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
use App\Models\Admin;
use App\Notifications\RequestSubmitted;
use App\Utilities\DateChecker;
use App\Helpers\ServiceHelper; // Ensure helper is imported

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
        $studentRequest->status = 'Pending'; // Make sure status is explicitly set

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
 
         // Add logging before save
         \Log::info('Attempting to save student request with data:', [
             'user_id' => $studentRequest->user_id,
             'service_category' => $studentRequest->service_category, // Log the value being saved
             'first_name' => $studentRequest->first_name,
             'last_name' => $studentRequest->last_name,
             'student_id' => $studentRequest->student_id,
             'status' => $studentRequest->status,
              'additional_notes' => $studentRequest->additional_notes, // Log this too
          ]);
  
          // Save the request with explicit success/failure logging
          $saveSuccess = false;
          try {
              $saveSuccess = $studentRequest->save();
              if ($saveSuccess) {
                  \Log::info('Student request SAVED successfully.', ['request_id' => $studentRequest->id]);
              } else {
                  \Log::error('Student request save() method returned false.', ['request_data' => $studentRequest->toArray()]);
              }
          } catch (\Exception $e) {
              \Log::error('Exception during Student request save.', [
                  'error_message' => $e->getMessage(),
                  'request_data' => $studentRequest->toArray(),
                  'trace' => $e->getTraceAsString() // Optional: include stack trace for deep debug
              ]);
              // Optionally rethrow or handle the exception, e.g., return error to user
              // For now, we just log it and let the process continue to see if redirect happens
          }
  
          // Log after save attempt (indicates code reached this point)
          \Log::info('Student request save block finished.', ['save_success' => $saveSuccess, 'request_id' => $studentRequest->id ?? null]);
  
          // Generate a unique display ID with SSR prefix (only if save was successful)
          $displayId = $saveSuccess ? ('SSR-' . date('Ymd') . '-' . str_pad($studentRequest->id, 4, '0', STR_PAD_LEFT)) : 'SAVE_FAILED';
        $displayId = 'SSR-' . date('Ymd') . '-' . str_pad($studentRequest->id, 4, '0', STR_PAD_LEFT);

        // Check if today is a non-working day (weekend or holiday)
        $nonWorkingDayInfo = DateChecker::isNonWorkingDay();

        // Send email notification to the user
        Notification::route('mail', $request->user()->email)
            ->notify(new ServiceRequestReceived(
                $displayId, // Formatted display ID
                $studentRequest->service_category,
                $studentRequest->first_name . ' ' . $studentRequest->last_name,
                $nonWorkingDayInfo // Pass the non-working day info
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
        }

        // Redirect back with success modal data
        return redirect()->back()->with([
            'showSuccessModal' => true,
            'requestId' => $displayId, // Use the formatted display ID
            'serviceCategory' => $studentRequest->service_category,
            'nonWorkingDayInfo' => $nonWorkingDayInfo // Add the non-working day info to the session
        ]);
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
            $requests = $query->orderBy('created_at', 'desc')->paginate(10);
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

        $rules = [
             'first_name' => 'required|string|max:255',
             'last_name' => 'required|string|max:255',
             'student_id' => 'required|string|max:50',
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
              $serviceRequest->first_name = $validatedData['first_name'];
              $serviceRequest->last_name = $validatedData['last_name'];
              $serviceRequest->student_id = $validatedData['student_id'];

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
