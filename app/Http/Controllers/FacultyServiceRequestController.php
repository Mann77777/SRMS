<?php

namespace App\Http\Controllers;

use App\Models\FacultyServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacultyServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Incoming request data:', $request->all());

            // Validate the request
            $validatedData = $request->validate([
                'service_category' => 'required',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'account_email' => 'nullable|email',
                'ms_options' => 'nullable|array',
                'months' => 'nullable|array',
                'year' => 'nullable|string',
                'supporting_document' => 'nullable|file|max:2048',
                'description' => 'nullable|string',
                'problem_encountered' => 'nullable|string',
                'repair_maintenance' => 'nullable|string',
                'preferred_date' => 'nullable|date',
                'preferred_time' => 'nullable',
                'dtr_months' => 'nullable|string',
                'dtr_with_details' => 'nullable|boolean',
                'data_type' => 'nullable|in:name,email,contact_number,address,others',
                'new_data' => 'nullable|string|max:255',
                'supporting_document' => 'nullable|file|max:2048',
                'description' => 'nullable|string',
                'middle_name' => 'nullable|string|max:255',
                'college' => 'nullable|in:CEIT,CAS,COED,COET,COBA,OTHER',
                'department' => 'nullable|string|max:255',
                'plantilla_position' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'emergency_contact_person' => 'nullable|string|max:255',
                'emergency_contact_number' => 'nullable|string|max:20',
                'location' => 'nullable|string|max:500',
                'preferred_date' => 'nullable|date|after_or_equal:today',
                'preferred_time' => 'nullable|date_format:H:i',
                'led_screen_details' => 'nullable|string|max:500',
                'application_name' => 'nullable|string|max:255',
                'installation_purpose' => 'nullable|string|max:1000',
                'installation_notes' => 'nullable|string|max:500',
                'publication_author' => 'nullable|string|max:255',
                'publication_editor' => 'nullable|string|max:255',
                'publication_start_date' => 'nullable|date',
                'publication_end_date' => 'nullable|date|after_or_equal:publication_start_date',
                'data_documents_details' => 'nullable|string|max:2000',
            ]);

            // Add user_id if authenticated
            if (Auth::check()) {
                $validatedData['user_id'] = Auth::id();
            }

            // Add default status
            $validatedData['status'] = 'Pending';

            // Handle file upload
            if ($request->hasFile('supporting_document')) {
                $path = $request->file('supporting_document')->store('documents', 'public');
                $validatedData['supporting_document'] = $path;
            }

            // Handle array fields
            foreach (['ms_options', 'months'] as $field) {
                if (isset($validatedData[$field]) && is_array($validatedData[$field])) {
                    $validatedData[$field] = json_encode($validatedData[$field]);
                }
            }

            // Handle DTR specific fields
            if ($validatedData['service_category'] === 'dtr') {
                $validatedData['dtr_months'] = $request->input('dtr_months');
                $validatedData['dtr_with_details'] = $request->has('dtr_with_details') ? 1 : 0;
            }
            
               // Handle 'other' data type
               if ($validatedData['data_type'] === 'other') {
                $validatedData['data_type'] = $request->input('other_data_type');
            }
            
        

            Log::info('Validated data:', $validatedData);

            // Create the request
            $serviceRequest = FacultyServiceRequest::create($validatedData);

            Log::info('Service request created:', ['id' => $serviceRequest->id]);

            //return redirect()->back()->with('success', 'Service request submitted successfully!');

            // Redirect back with success modal data
            return redirect()->back()->with([
                'showSuccessModal' => true,
                'requestId' => $serviceRequest->id,
                'serviceCategory' => $validatedData['service_category']
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

    public function myRequests()
    {
        try {
            // Get all requests for the current user
            $requests = FacultyServiceRequest::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            // Log for debugging
            Log::info('Fetched requests:', [
                'user_id' => Auth::id(),
                'count' => $requests->count(),
                'requests' => $requests->toArray()
            ]);

            return view('users.myrequest', ['requests' => $requests]);
        } catch (\Exception $e) {
            Log::error('Error fetching requests:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error fetching requests');
        }
    }

    public function updateRequest(Request $request, $id)
    {
        try {
            $serviceRequest = FacultyServiceRequest::findOrFail($id);
            
            if ($serviceRequest->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            $serviceRequest->update($request->all());
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