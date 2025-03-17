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

            if (Auth::check() && $request->user()->email) {
                Notification::route('mail', $request->user()->email)
                    ->notify(new ServiceRequestReceived(
                        $serviceRequest->id, 
                        $request->input('service_category'),
                        $filteredData['first_name'] . ' ' . $filteredData['last_name']
                    ));
                    
                Log::info('Email notification sent to: ' . $request->user()->email);
            }
            // Redirect back with success modal data
            return redirect()->back()->with([
                'showSuccessModal' => true,
                'requestId' => $serviceRequest->id,
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

    public function myRequests()
    {
       $user = Auth::user();

       if($user->role === "Faculty & Staff")
       {
            $requests = FacultyServiceRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            return view('users.myrequests', compact('requests'));
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