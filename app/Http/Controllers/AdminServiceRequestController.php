<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Admin;
use App\Models\FacultyServiceRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Support\Facades\Log;

class AdminServiceRequestController extends Controller
{
 
    public function index()
    {
        $requests = [];

        try {
            // Fetch all student requests
            $studentRequests = ServiceRequest::with('user')->get();
        foreach($studentRequests as $request) {
            $user = $request->user;
            $requests[] = [
                'id' => $request->id,
                'user_id' => $request->user_id,
                'role' => $user ? $user->role : 'Student',
                'service' => $this->getServiceName($request, 'student'),
                'request_data' => $this->getRequestData($request),
                'date' => $request->created_at,
                'status' => $request->status,
                'type' => 'student',
            ];
        }

        // Fetch new student service requests
        $newStudentRequests = StudentServiceRequest::with('user')->get();
        foreach($newStudentRequests as $request) {
            $user = $request->user;
            $requests[] = [
                'id' => $request->id,
                'user_id' => $request->user_id,
                'role' => $user ? $user->role : 'Student',
                'service' => $request->service_category,
                'request_data' => $this->formatStudentServiceRequestData($request),
                'date' => $request->created_at,
                'status' => $request->status ?? 'Pending',
                'type' => 'new_student_service',
            ];
        }

        // Fetch faculty requests
        $facultyRequests = FacultyServiceRequest::with('user')->get();
        foreach($facultyRequests as $request) {
            $user = $request->user;
            $requests[] = [
                'id' => $request->id,
                'user_id' => $request->user_id,
                'role' => $user ? $user->role : 'Faculty',
                'service' => $this->getServiceName($request, 'faculty'),
                'request_data' => $this->getRequestData($request),
                'date' => $request->created_at,
                'status' => $request->status,
                'type' => 'faculty',
            ];
        }

    } catch (\Exception $e) {
        Log::error('Error fetching service requests: ' . $e->getMessage());
    }

    // Sort requests by date
    $allRequests = collect($requests)->sortByDesc('date');

    return view('admin.service-request', ['requests' => $allRequests]);
}
    private function getServiceName($request, $type)
{
    switch ($type) {
        case 'student':
            return $request->service_category ?? 'Unspecified Service';
            
        case 'faculty':
            return $request->service_type ?? 'Unspecified Service';
            
        default:
            return 'Unknown Service';
    }
}

private function getRequestData($request)
{
    $output = [];
    
    if ($request->user) {
        $output[] = '<strong>Name:</strong> ' . htmlspecialchars($request->user->name) . '<br>';
    }
    
    if (isset($request->service_category)) {
        $output[] = '<strong>Service:</strong> ' . htmlspecialchars($request->service_category) . '<br>';
    }
    
    if (isset($request->description)) {
        $output[] = '<strong>Description:</strong> ' . htmlspecialchars($request->description) . '<br>';
    }
    
    // Add additional data based on request type
    if (method_exists($request, 'getAdditionalData')) {
        $additionalData = $request->getAdditionalData();
        foreach ($additionalData as $key => $value) {
            $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
        }
    }
    
    return implode('', $output);
}

    /**
     * Format student service request data for display
     * 
     * @param StudentServiceRequest $request
     * @return string
     */
    private function formatStudentServiceRequestData($request)
    {
        $data = [
            'Name' => $request->first_name . ' ' . $request->last_name,
            'Student ID' => $request->student_id,
            'Service' => $request->service_category,

        ];
    
        // Add additional details based on service category
        switch($request->service_category) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
                $data['Account Email'] = $request->account_email ?? 'N/A';
                break;
            
            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $data['Data to be updated'] = $request->data_type ?? 'N/A';
                $data['New Data'] = $request->new_data ?? 'N/A';
    
                // Add supporting document link if exists
                if ($request->supporting_document) {
                    $data['Supporting Document'] = 'Available';
                }
                break;
            
            case 'request_led_screen':
                $data['Preferred Date'] = $request->preferred_date ?? 'N/A';
                $data['Preferred Time'] = $request->preferred_time ?? 'N/A';
                break;
            
            case 'others':
                $data['Description'] = $request->description ?? 'N/A';
                break;
        }
    
        // Convert data to HTML format
        $output = [];
        foreach($data as $key => $value){
            if ($key === 'Supporting Document' && $value === 'Available') {
                $output[] = '<strong>Supporting Document:</strong> ' . 
                    sprintf('<a href="%s" target="_blank" class="document-link">View Document</a>', 
                    route('admin.view-supporting-document', ['requestId' => $request->id])) . '<br>';
            } else {
                $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
            }
        }
        return implode('', $output);
    }
    
    public function viewSupportingDocument($requestId)
    {
        // Find the student service request
        $request = StudentServiceRequest::findOrFail($requestId);
    
        // Check if supporting document exists
        if (!$request->supporting_document) {
            return back()->with('error', 'No supporting document found.');
        }
    
        // Get the full path to the file
        $filePath = storage_path('app/public/' . $request->supporting_document);
    
        // Check if file exists
        if (!file_exists($filePath)) {
            return back()->with('error', 'Supporting document file not found.');
        }
    
        // Determine file type
        $mimeType = mime_content_type($filePath);
    
        // Return file for download or preview
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }


      // Method to fetch available UITC Staff
      public function getAvailableTechnicians()
      {
          // Fetch only UITC Staff from admins table who are available
          $availableUITCStaff = Admin::where('role', 'UITC Staff')
                ->where('availability_status', 'available') 
                ->select('id', 'name')
                ->get();
    
          return response()->json($availableUITCStaff);
      }


   // Method to assign UITC Staff to a student service request
   public function assignUITCStaff(Request $request)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'request_id' => 'required',
        'uitcstaff_id' => 'required|exists:admins,id',
        'transaction_type' => 'required|in:simple,complex,highly_technical',
        'notes' => 'nullable|string',
        'request_type' => 'required|in:student,faculty,new_student_service'
    ]);

    try {
        // Find the appropriate service request based on type
        switch ($validatedData['request_type']) {
            case 'new_student_service':
                $serviceRequest = StudentServiceRequest::findOrFail($validatedData['request_id']);
                break;
            case 'faculty':
                $serviceRequest = FacultyServiceRequest::findOrFail($validatedData['request_id']);
                break;
            case 'student':
                $serviceRequest = ServiceRequest::findOrFail($validatedData['request_id']);
                break;
            default:
                throw new \Exception('Invalid request type');
        }

        // Update the service request
        $serviceRequest->update([
            'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
            'transaction_type' => $validatedData['transaction_type'],
            'admin_notes' => $validatedData['notes'],
            'status' => 'In Progress'
        ]);

        // Update the staff's availability status
        $uitcStaff = Admin::findOrFail($validatedData['uitcstaff_id']);
        $uitcStaff->update(['availability_status' => 'available']);

        // Optional: Send notification to the assigned staff
        // Notification::send($uitcStaff, new ServiceRequestAssignedNotification($serviceRequest));

        return response()->json([
            'success' => true,
            'message' => 'UITC Staff assigned successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('UITC Staff Assignment Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to assign UITC Staff: ' . $e->getMessage()
        ], 500);
    }
    
}
public function deleteServiceRequests(Request $request)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'request_ids' => 'required|array',
        'request_ids.*' => 'required|integer'
    ]);

    try {
        // Start a database transaction
        DB::beginTransaction();

        foreach ($validatedData['request_ids'] as $requestId) {
            // Try to find and delete from each possible table
            $deleted = false;

            // Try StudentServiceRequest
            $studentRequest = StudentServiceRequest::find($requestId);
            if ($studentRequest) {
                $studentRequest->delete();
                $deleted = true;
            }

            // Try FacultyServiceRequest
            if (!$deleted) {
                $facultyRequest = FacultyServiceRequest::find($requestId);
                if ($facultyRequest) {
                    $facultyRequest->delete();
                    $deleted = true;
                }
            }

            // Try ServiceRequest
            if (!$deleted) {
                $serviceRequest = ServiceRequest::find($requestId);
                if ($serviceRequest) {
                    $serviceRequest->delete();
                    $deleted = true;
                }
            }

            if (!$deleted) {
                throw new \Exception("Request ID {$requestId} not found in any table");
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Selected requests deleted successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Service Request Deletion Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete requests: ' . $e->getMessage()
        ], 500);
    }
}

public function rejectServiceRequest(Request $request)
{
    // Validate the request
    $validatedData = $request->validate([
        'request_id' => 'required',
        'request_type' => 'required|in:student,faculty,new_student_service',
        'rejection_reason' => 'required|string',
        'notes' => 'nullable|string'
    ]);

    try {
        // Handle different request types
        switch ($validatedData['request_type']) {
            case 'new_student_service':
                $serviceRequest = StudentServiceRequest::findOrFail($validatedData['request_id']);
                break;
            case 'faculty':
                $serviceRequest = FacultyServiceRequest::findOrFail($validatedData['request_id']);
                break;
            case 'student':
                $serviceRequest = ServiceRequest::findOrFail($validatedData['request_id']);
                break;
            default:
                throw new \Exception('Invalid request type');
        }

        // Update the service request
        $serviceRequest->update([
            'status' => 'Rejected',
            'rejection_reason' => $validatedData['rejection_reason'],
            'admin_notes' => $validatedData['notes'],
            'rejected_at' => now()
        ]);

        // You might want to send a notification to the user here
        // Notification::send($serviceRequest->user, new ServiceRequestRejectedNotification($serviceRequest));

        return response()->json([
            'success' => true,
            'message' => 'Service request rejected successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Service Request Rejection Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to reject service request: ' . $e->getMessage()
        ], 500);
    }
}
}