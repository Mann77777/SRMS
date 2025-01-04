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
            $studentRequests = ServiceRequest::all();
            foreach($studentRequests as $request){
                $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'role' => $request->user ? $request->user->role : 'Unknown', // Fetch user role
                    'service' => $this->getServiceName($request, 'student'),
                    'request_data' => $this->getRequestData($request),
                    'date' => $request->created_at,
                    'status' => $request->status,
                    'type' => 'student',
                ];
            }

            // Fetch all new student service requests
            $newStudentRequests = StudentServiceRequest::all();
            foreach($newStudentRequests as $request){
                $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'role' => $request->user ? $request->user->role : 'Student', // Default to 'Student' if no user found
                    'service' => $request->service_category,
                    'request_data' => $this->formatStudentServiceRequestData($request),
                    'date' => $request->created_at,
                    'status' => $request->status ?? 'Pending',
                    'type' => 'new_student_service',
                ];
            }

            // Fetch all faculty requests
            $facultyRequests = FacultyServiceRequest::all();
            foreach($facultyRequests as $request){
                $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
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

        // Sort by date
        $allRequests = collect($requests)->sortByDesc('date');

        return view('admin.service-request', ['requests' => $allRequests]);
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
       // Validate the request
       $validatedData = $request->validate([
           'request_id' => 'required|exists:student_service_requests,id',
           'uitcstaff_id' => 'required|exists:admins,id',
           'transaction_type' => 'required|in:simple,complex,highly_technical',
           'notes' => 'nullable|string'
       ]);
 
       try {
           // Find the student service request
           $studentServiceRequest = StudentServiceRequest::findOrFail($validatedData['request_id']);
 
           // Update student service request with assigned UITC Staff
           $studentServiceRequest->update([
               'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
               'transaction_type' => $validatedData['transaction_type'],
               'admin_notes' => $validatedData['notes'] ?? null,
               'status' => 'Assigned' // Change status to assigned
           ]);
 
           // Update staff availability
           $uitcStaff = Admin::findOrFail($validatedData['uitcstaff_id']);
           $uitcStaff->update(['availability_status' => 'busy']);
 
           return response()->json([
               'success' => true, 
               'message' => 'UITC Staff assigned to student service request successfully'
           ]);
 
       } catch (\Exception $e) {
           // Log the error for debugging
           Log::error('UITC Staff Assignment Error: ' . $e->getMessage());
 
           return response()->json([
               'success' => false, 
               'message' => 'Failed to assign UITC Staff: ' . $e->getMessage()
           ], 500);
       }
   }
}