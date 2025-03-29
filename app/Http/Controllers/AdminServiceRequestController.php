<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Admin;
use App\Models\FacultyServiceRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ServiceRequestAssigned;
use App\Notifications\ServiceRequestRejected;

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
        
        // Get current page from request query string
        $page = request()->get('page', 1);
        $perPage = 10;
        
        // Paginate the collection manually
        $items = $allRequests->forPage($page, $perPage);
        
        // Create a new paginator instance
        $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allRequests->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.service-request', ['requests' => $paginatedRequests]);
    }


    /**
     * Get formatted request data for display
     * 
     * @param object $request The request object
     * @return string HTML output
     */
    private function getRequestData($request)
    {
        $output = [];
        
        if ($request->user) {
            $output[] = '<strong>Name:</strong> ' . htmlspecialchars($request->user->name) . '<br>';
        }
        
        if (isset($request->service_category)) {
            // Format the service category name
            $formattedServiceName = $this->formatServiceCategory($request->service_category, $request->description);
            $output[] = '<strong>Service:</strong> ' . htmlspecialchars($formattedServiceName) . '<br>';
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
     * Format service category to human-readable name
     * 
     * @param string $category The service category code
     * @param string|null $description Optional description for "others" category
     * @return string The formatted service name
     */
    private function formatServiceCategory($category, $description = null)
    {
        switch ($category) {
            case 'create':
                return 'Create MS Office/TUP Email Account';
            case 'reset_email_password':
                return 'Reset MS Office/TUP Email Password';
            case 'change_of_data_ms':
                return 'Change of Data (MS Office)';
            case 'reset_tup_web_password':
                return 'Reset TUP Web Password';
            case 'reset_ers_password':
                return 'Reset ERS Password';
            case 'change_of_data_portal':
                return 'Change of Data (Portal)';
            case 'dtr':
                return 'Daily Time Record';
            case 'biometric_record':
                return 'Biometric Record';
            case 'biometrics_enrollement':
                return 'Biometrics Enrollment';
            case 'new_internet':
                return 'New Internet Connection';
            case 'new_telephone':
                return 'New Telephone Connection';
            case 'repair_and_maintenance':
                return 'Internet/Telephone Repair and Maintenance';
            case 'computer_repair_maintenance':
                return 'Computer Repair and Maintenance';
            case 'printer_repair_maintenance':
                return 'Printer Repair and Maintenance';
            case 'request_led_screen':
                return 'LED Screen Request';
            case 'install_application':
                return 'Install Application/Information System/Software';
            case 'post_publication':
                return 'Post Publication/Update of Information Website';
            case 'data_docs_reports':
                return 'Data, Documents and Reports';
            case 'others':
                return $description ?? 'Other Service';
            default:
                return $category;
        }
    }


    /**
     * Get formatted service name based on request type
     * 
     * @param object $request The request object
     * @param string $type The type of request (student, faculty)
     * @return string Formatted service name
     */
    private function getServiceName($request, $type)
    {
        switch ($type) {
            case 'student':
                return $this->formatServiceCategory(
                    $request->service_category ?? 'Unspecified Service',
                    $request->description ?? null
                );
                
            case 'faculty':
                return $this->formatServiceCategory(
                    $request->service_type ?? 'Unspecified Service',
                    $request->description ?? null
                );
                
            default:
                return 'Unknown Service';
        }
    }


    /**
     * Format student service request data for display
     * 
     * @param StudentServiceRequest $request
     * @return string
     */
    private function formatStudentServiceRequestData($request)
    {
        // Format the service category name
        $formattedServiceName = $this->formatServiceCategory($request->service_category, $request->description);
        
        $data = [
            'Name' => $request->first_name . ' ' . $request->last_name,
            'Student ID' => $request->student_id,
            'Service' => $formattedServiceName,
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
          $availableUITCStaff = Admin::where('department', 'UITC')
                ->select('id', 'name')
                ->get();
    
          return response()->json($availableUITCStaff);
      }
      public function getUITCStaff()
      {
          try {
              // Fetch all UITC Staff
              $uitcStaff = Admin::where('role', 'UITC Staff')->get();
              
              return response()->json([
                  'success' => true,
                  'staff' => $uitcStaff
              ]);
          } catch (\Exception $e) {
              Log::error('Error fetching UITC Staff: ' . $e->getMessage());
              return response()->json([
                  'success' => false,
                  'message' => 'Failed to fetch UITC Staff'
              ], 500);
          }
      }


    // Method to assign UITC Staff to a student service request
    public function assignUitcStaff(Request $request)
    {
        // Log the incoming request data for debugging
        Log::info('Assign UITC Staff Request Data:', $request->all());
    
        // Basic validation without type-specific checks
        $validatedData = $request->validate([
            'request_id' => 'required|integer',
            'request_type' => 'required|string',
            'uitcstaff_id' => 'required|exists:admins,id',
            'transaction_type' => 'required',
            'notes' => 'nullable|string'
        ]);
    
        try {
            // Get the UITC staff name for notification
            $uitcStaff = Admin::find($validatedData['uitcstaff_id']);
            $uitcStaffName = $uitcStaff ? $uitcStaff->name : 'UITC Staff';
            
            // Variable to store service request object
            $serviceRequest = null;
            $requestorName = '';
            $serviceCategory = '';
            $userEmail = '';
            
            // Handle different request types
            switch ($validatedData['request_type']) {
                case 'student':
                case 'new_student_service':
                    // Verify student request exists
                    $serviceRequest = StudentServiceRequest::with('user')->find($validatedData['request_id']);
                    
                    if (!$serviceRequest) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Student Service Request not found.'
                        ], 404);
                    }
                    
                    // Get requestor details
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    $userEmail = $serviceRequest->user ? $serviceRequest->user->email : null;
                    
                    // Update student request
                    $serviceRequest->update([
                        'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
                        'status' => 'In Progress',
                        'transaction_type' => $validatedData['transaction_type'],
                        'admin_notes' => $validatedData['notes'] ?? null,
                        'updated_at' => now()
                    ]);
                    break;
                    
                case 'faculty':
                    // Verify faculty request exists
                    $serviceRequest = FacultyServiceRequest::with('user')->find($validatedData['request_id']);
                    
                    if (!$serviceRequest) {
                        Log::error('Faculty Service Request not found', [
                            'request_id' => $validatedData['request_id']
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Faculty Service Request not found.'
                        ], 404);
                    }
                    
                    // Get requestor details
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    $userEmail = $serviceRequest->user ? $serviceRequest->user->email : null;
                    
                    // Log before update
                    Log::info('Updating faculty service request', [
                        'request_id' => $validatedData['request_id'],
                        'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
                        'status' => 'In Progress'
                    ]);
                    
                    // Update faculty request
                    $serviceRequest->update([
                        'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
                        'status' => 'In Progress',
                        'transaction_type' => $validatedData['transaction_type'],
                        'admin_notes' => $validatedData['notes'] ?? null,
                        'updated_at' => now()
                    ]);
                    
                    // Log update result
                    Log::info('Faculty update result', ['updated' => $serviceRequest->wasChanged()]);
                    
                    break;
                    
                default:
                    Log::warning('Unknown request type', ['type' => $validatedData['request_type']]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid request type: ' . $validatedData['request_type']
                    ], 400);
            }
            
            // Send notification to user if email is available
            if ($userEmail) {
               
                
                // Send the notification
                \Illuminate\Support\Facades\Notification::route('mail', $userEmail)
                    ->notify(new ServiceRequestAssigned(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $uitcStaffName,
                        $validatedData['transaction_type'],
                        $validatedData['notes'] ?? ''
                    ));
                    
                Log::info('Assignment notification sent to: ' . $userEmail, [
                    'request_id' => $serviceRequest->id,
                    'staff_id' => $validatedData['uitcstaff_id'],
                    'staff_name' => $uitcStaffName
                ]);
            } else {
                Log::warning('Unable to send assignment notification - user email not found for request ID: ' . $validatedData['request_id']);
            }
    
            Log::info('UITC Staff assigned successfully', [
                'request_id' => $validatedData['request_id'],
                'request_type' => $validatedData['request_type'],
                'assigned_uitc_staff_id' => $validatedData['uitcstaff_id']
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'UITC Staff assigned successfully',
                'request_type' => $validatedData['request_type']
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning UITC Staff: ' . $e->getMessage(), [
                'request_id' => $validatedData['request_id'],
                'request_type' => $validatedData['request_type'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
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
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    break;
                case 'faculty':
                    $serviceRequest = FacultyServiceRequest::findOrFail($validatedData['request_id']);
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    break;
                case 'student':
                    $serviceRequest = ServiceRequest::findOrFail($validatedData['request_id']);
                    $requestorName = $serviceRequest->user ? $serviceRequest->user->name : 'Student';
                    $serviceCategory = $serviceRequest->service_category;
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

            // Send a notification to the user
            //Notification::send($serviceRequest->user, new RequestRejectedNotification($serviceRequest));

               // Send a notification to the user if we have the user associated
        if (isset($serviceRequest->user) && $serviceRequest->user) {
            $user = $serviceRequest->user;
            
            // Send the notification
            Notification::route('mail', $user->email)
                ->notify(new ServiceRequestRejected(
                    $serviceRequest->id,
                    $serviceCategory,
                    $requestorName,
                    $validatedData['rejection_reason'],
                    $validatedData['notes']
                ));
                
            Log::info('Rejection notification sent to: ' . $user->email);
        } else {
            Log::warning('Unable to send rejection notification - user not found for request ID: ' . $validatedData['request_id']);
        }


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