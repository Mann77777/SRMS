<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
            'agreeTerms' => 'accepted'
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

        // Redirect to my requests page
        return redirect()->route('users.myrequests')->with('success', 'Service request submitted successfully!');
    }

    // New method to show student's requests
    public function myRequests()
    {
        // Fetch requests with description for 'others' category
        $requests = DB::table('student_service_requests')
        ->select('id', 'service_category', 'status', 'created_at', 'description')
        ->where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('users.myrequests', compact('requests'));
    }
}