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
                'ms_options' => 'nullable|array',
                'months' => 'nullable|array',
                'year' => 'nullable|string',
                'supporting_document' => 'nullable|file|max:2048',
                'description' => 'nullable|string',
                'problem_encountered' => 'nullable|string',
                'repair_maintenance' => 'nullable|string',
                'preferred_date' => 'nullable|date',
                'preferred_time' => 'nullable',
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