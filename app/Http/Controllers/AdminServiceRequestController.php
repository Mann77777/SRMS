<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Admin;
use App\Models\FacultyServiceRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ServiceRequestAssigned; // Keep if used elsewhere (e.g., rejection potentially)
use App\Notifications\ServiceRequestRejected;
use App\Notifications\StaffAssignedToRequest;
use App\Notifications\ServiceRequestAssignedToUser; // Import the new notification class

class AdminServiceRequestController extends Controller
{

    public function index()
    {
        $requests = [];

        try {
            // Fetch all student requests (Assuming this is an older model or type)
            // Consider if this fetch is still necessary if StudentServiceRequest covers all student cases
            // $studentRequests = ServiceRequest::with('user')->get();
            // foreach($studentRequests as $request) {
            //     $user = $request->user;
            //     $requests[] = [
            //         'id' => $request->id,
            //         'user_id' => $request->user_id,
            //         'role' => $user ? $user->role : 'Student', // Might need refinement based on actual user setup
            //         'service' => $this->getServiceName($request, 'student'),
            //         'request_data' => $this->getRequestData($request),
            //         'date' => $request->created_at,
            //         'status' => $request->status,
            //         'type' => 'student', // Legacy type?
            //         'updated_at' => $request->updated_at,
            //     ];
            // }

            // Fetch new student service requests
            $newStudentRequests = StudentServiceRequest::with('user')->get();
            foreach($newStudentRequests as $request) {
                $user = $request->user;
                $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'role' => $user ? $user->role : 'Student', // Assumes user relationship exists and has role
                    'service' => $request->service_category, // Use raw category here, format later if needed
                    'request_data' => $this->formatStudentServiceRequestData($request),
                    'date' => $request->created_at,
                    'status' => $request->status ?? 'Pending',
                    'type' => 'new_student_service', // Specific type
                    'updated_at' => $request->updated_at,
                    'rejection_reason' => $request->rejection_reason ?? null,
                    'notes' => $request->admin_notes ?? null,
                ];
            }

            // Fetch faculty requests
            $facultyRequests = FacultyServiceRequest::with('user')->get();
            foreach($facultyRequests as $request) {
                $user = $request->user;
                $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'role' => $user ? $user->role : 'Faculty', // Assumes user relationship exists and has role
                    'service' => $request->service_category, // Use raw category here, format later if needed
                    'request_data' => $this->formatFacultyServiceRequestData($request),
                    'date' => $request->created_at,
                    'status' => $request->status ?? 'Pending', // Default status if null
                    'rejection_reason' => $request->rejection_reason ?? null,
                    'notes' => $request->admin_notes ?? null,
                    'type' => 'faculty', // Specific type
                    'updated_at' => $request->updated_at,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error fetching service requests: ' . $e->getMessage());
            // Optionally, return an error view or message
        }

        // Sort requests by date (latest first)
        $allRequests = collect($requests)->sortByDesc('date');

        // Get current page from request query string
        $page = request()->get('page', 1);
        $perPage = 10; // Or get from config/request

        // Paginate the collection manually
        $items = $allRequests->forPage($page, $perPage);

        // Create a new paginator instance
        $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->values(), // Ensure it's a non-associative array
            $allRequests->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.service-request', ['requests' => $paginatedRequests]);
    }


    /**
     * Get formatted request data for display (Potentially legacy for ServiceRequest model)
     * Consider removing if ServiceRequest model is not used for active display
     *
     * @param object $request The request object
     * @return string HTML output
     */
    private function getRequestData($request)
    {
        $output = [];

        if ($request->user) {
            // Combine first_name and last_name
            $userName = trim(htmlspecialchars($request->user->first_name ?? '') . ' ' . htmlspecialchars($request->user->last_name ?? ''));
            $output[] = '<strong>Name:</strong> ' . ($userName ?: 'N/A') . '<br>';
        }

        if (isset($request->service_category)) {
            // Format the service category name
            $formattedServiceName = $this->formatServiceCategory($request->service_category, $request->description ?? null);
            $output[] = '<strong>Service:</strong> ' . htmlspecialchars($formattedServiceName) . '<br>';
        }

        if (isset($request->description)) {
            $output[] = '<strong>Description:</strong> ' . htmlspecialchars($request->description) . '<br>';
        }

        // Add additional data based on request type (if applicable to this model)
        if (method_exists($request, 'getAdditionalData')) {
            $additionalData = $request->getAdditionalData();
            foreach ($additionalData as $key => $value) {
                $output[] = '<strong>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . htmlspecialchars($value) . '<br>';
            }
        }

        return implode('', $output);
    }

    /**
     * Format service category code to human-readable name
     *
     * @param string $category The service category code
     * @param string|null $description Optional description for "others" category
     * @return string The formatted service name
     */
    private function formatServiceCategory($category, $description = null)
    {
        // Consider moving this mapping to a config file or a dedicated helper/service class
        // for better organization and maintainability
        $mapping = [
            'create' => 'Create MS Office/TUP Email Account',
            'reset_email_password' => 'Reset MS Office/TUP Email Password',
            'change_of_data_ms' => 'Change of Data (MS Office)',
            'reset_tup_web_password' => 'Reset TUP Web Password',
            'reset_ers_password' => 'Reset ERS Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            'dtr' => 'Daily Time Record',
            'biometric_record' => 'Biometric Record',
            'biometrics_enrollment' => 'Biometrics Enrollment', // Corrected typo
            'new_internet' => 'New Internet Connection',
            'new_telephone' => 'New Telephone Connection',
            'repair_and_maintenance' => 'Internet/Telephone Repair and Maintenance',
            'computer_repair_maintenance' => 'Computer Repair and Maintenance',
            'printer_repair_maintenance' => 'Printer Repair and Maintenance',
            'request_led_screen' => 'LED Screen Request',
            'install_application' => 'Install Application/Information System/Software',
            'post_publication' => 'Post Publication/Update of Information Website',
            'data_docs_reports' => 'Data, Documents and Reports',
            'others' => $description ?: 'Other Service',
        ];

        return $mapping[$category] ?? ucfirst(str_replace('_', ' ', $category)); // Fallback for unknown categories
    }


    /**
     * Get formatted service name based on request object and type.
     * This seems redundant if the index method directly uses service_category
     * and formatStudent/FacultyServiceRequestData already format the name.
     * Consider simplifying or removing if not strictly needed.
     *
     * @param object $request The request object
     * @param string $type The type of request (student, faculty)
     * @return string Formatted service name
     */
    private function getServiceName($request, $type)
    {
        $category = null;
        $description = null;

        // Determine category and description based on type and potential fields
        switch ($type) {
            case 'student': // Legacy ServiceRequest?
            case 'new_student_service': // StudentServiceRequest
                $category = $request->service_category ?? null;
                $description = $request->description ?? null;
                break;

            case 'faculty': // FacultyServiceRequest
                 // Assuming faculty requests also use 'service_category' now based on formatFacultyServiceRequestData
                $category = $request->service_category ?? ($request->service_type ?? null);
                $description = $request->description ?? null;
                break;
        }

        if ($category) {
            return $this->formatServiceCategory($category, $description);
        }

        return 'Unspecified Service';
    }


    /**
     * Format student service request data for display
     *
     * @param StudentServiceRequest $request
     * @return string HTML string
     */
    private function formatStudentServiceRequestData(StudentServiceRequest $request)
    {
        $formattedServiceName = $this->formatServiceCategory($request->service_category, $request->description);

        // Generate a formatted display ID (optional, but good for user reference)
        // $displayId = 'SSR-' . $request->created_at->format('Ymd') . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT);

        $data = [
            'Name' => $request->first_name . ' ' . $request->last_name,
            'Student ID' => $request->student_id,
            'Service' => $formattedServiceName,
        ];

        // Add fields based on service category
        switch ($request->service_category) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
            case 'reset_ers_password':
                $data['Account Email'] = $request->account_email ?? 'N/A';
                break;

            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $data['Data to be updated'] = $request->data_type ?? 'N/A';
                $data['New Data'] = $request->new_data ?? 'N/A';
                if ($request->additional_notes) {
                    $data['Additional Notes'] = $request->additional_notes;
                }
                break;

            case 'request_led_screen':
                $data['Preferred Date'] = $request->preferred_date ? date('Y-m-d', strtotime($request->preferred_date)) : 'N/A';
                $data['Preferred Time'] = $request->preferred_time ?? 'N/A';
                break;

            case 'others':
                if ($request->description) { // Only show description if provided for 'others'
                   $data['Description'] = $request->description;
                }
                break;
            // Add cases for other student-specific services if needed
        }

        // Add supporting document link if it exists
        if ($request->supporting_document) {
             $data['Supporting Document'] = sprintf(
                '<a href="%s" target="_blank" class="document-link btn btn-sm btn-outline-primary">View Document</a>',
                route('admin.view-supporting-document', ['requestId' => $request->id, 'type' => 'student']) // Add type hint for routing
             );
        }

        // Add status information and other metadata
        if ($request->assigned_uitc_staff_id) {
            // Eager load staff name if possible during initial query for performance
            $staffName = Admin::find($request->assigned_uitc_staff_id)->name ?? 'Unknown Staff';
            $data['Assigned To'] = $staffName;
        }

        if ($request->transaction_type) {
            $data['Transaction Type'] = ucfirst($request->transaction_type); // Capitalize first letter
        }

        if ($request->admin_notes) {
            $data['Admin Notes'] = $request->admin_notes;
        }

        if ($request->status === 'Rejected' && $request->rejection_reason) {
            $data['Rejection Reason'] = $request->rejection_reason;
        }
        // Removed redundant check for admin_notes when status is Rejected

        // Convert data to HTML format
        $output = [];
        foreach ($data as $key => $value) {
            // Handle the HTML link for the document directly
            if ($key === 'Supporting Document') {
                 $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . $value . '<br>';
            } else {
                $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
            }
        }
        return implode('', $output);
    }

    /**
     * Format faculty service request data for display
     *
     * @param FacultyServiceRequest $request
     * @return string HTML string
     */
    private function formatFacultyServiceRequestData(FacultyServiceRequest $request)
    {
        $formattedServiceName = $this->formatServiceCategory($request->service_category, $request->description);

        // Generate a formatted display ID (optional)
        // $displayId = 'FSR-' . $request->created_at->format('Ymd') . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT);

        $data = [
            'Name' => $request->first_name . ' ' . $request->last_name,
            'Service' => $formattedServiceName,
        ];

        // Add fields based on service category
        switch ($request->service_category) {
            case 'reset_email_password':
            case 'reset_tup_web_password':
            case 'reset_ers_password':
                $data['Account Email'] = $request->account_email ?? 'N/A';
                break;

            case 'change_of_data_ms':
            case 'change_of_data_portal':
                $data['Data to be updated'] = $request->data_type ?? 'N/A';
                $data['New Data'] = $request->new_data ?? 'N/A';
                if ($request->additional_notes) {
                    $data['Additional Notes'] = $request->additional_notes;
                }
                break;

            case 'dtr':
                $data['DTR Months'] = $request->dtr_months ?? 'N/A';
                $data['Include In/Out Details'] = isset($request->dtr_with_details) ? ($request->dtr_with_details ? 'Yes' : 'No') : 'N/A';
                break;

            case 'biometrics_enrollment': // Corrected typo
                // List all expected fields for clarity
                $bioFields = [
                    'middle_name' => 'Middle Name',
                    'college' => 'College',
                    'department' => 'Department',
                    'plantilla_position' => 'Plantilla Position',
                    'date_of_birth' => 'Date of Birth',
                    'phone_number' => 'Phone Number',
                    'address' => 'Address',
                    'blood_type' => 'Blood Type',
                    'emergency_contact_person' => 'Emergency Contact Person',
                    'emergency_contact_number' => 'Emergency Contact Number'
                ];
                // Removed redundant individual isset checks

                foreach ($bioFields as $field => $label) {
                    // Include the field even if it's null, showing "Not provided" for empty values
                    $data[$label] = isset($request->$field) && !empty($request->$field)
                        ? ($field === 'date_of_birth' ? $this->formatDate($request->$field) : $request->$field) // Format date specifically
                        : 'Not provided';
                }
                break;

            case 'new_internet':
            case 'new_telephone':
            case 'repair_and_maintenance':
            case 'computer_repair_maintenance':
            case 'printer_repair_maintenance':
                $data['Location'] = $request->location ?? 'N/A';
                $data['Problems Encountered'] = $request->problem_encountered ?? 'N/A';
                break;

            case 'request_led_screen':
                if (isset($request->preferred_date)) {
                    $data['Preferred Date'] = $this->formatDate($request->preferred_date);
                }
                if (isset($request->preferred_time)) {
                    $data['Preferred Time'] = $request->preferred_time;
                }
                if (isset($request->led_screen_details)) {
                    $data['Additional Details'] = $request->led_screen_details;
                }
                break;

            case 'install_application':
                $data['Application Name'] = $request->application_name ?? 'N/A';
                $data['Purpose of Installation'] = $request->installation_purpose ?? 'N/A';
                $data['Additional Requirements'] = $request->installation_notes ?? 'N/A';
                break;

            case 'post_publication':
                if (isset($request->publication_author)) {
                    $data['Author'] = $request->publication_author;
                }
                if (isset($request->publication_editor)) {
                    $data['Editor'] = $request->publication_editor;
                }
                if (isset($request->publication_start_date)) {
                    $data['Date of Publication'] = $this->formatDate($request->publication_start_date);
                }
                if (isset($request->publication_end_date)) {
                    $data['End of Publication'] = $this->formatDate($request->publication_end_date);
                }
                break;

            case 'data_docs_reports':
                $data['Details'] = $request->data_documents_details ?? 'N/A';
                break;

             case 'others':
                if ($request->description) { // Only show description if provided for 'others'
                   $data['Description'] = $request->description;
                }
                break;
        }

        // Add supporting document link if it exists
        if ($request->supporting_document) {
            $data['Supporting Document'] = sprintf(
               '<a href="%s" target="_blank" class="document-link btn btn-sm btn-outline-primary">View Document</a>',
               route('admin.view-supporting-document', ['requestId' => $request->id, 'type' => 'faculty']) // Add type hint
            );
        }

        // Add status information and other metadata
        if ($request->assigned_uitc_staff_id) {
            $staffName = Admin::find($request->assigned_uitc_staff_id)->name ?? 'Unknown Staff';
            $data['Assigned To'] = $staffName;
        }

        if ($request->transaction_type) {
            $data['Transaction Type'] = ucfirst($request->transaction_type);
        }

        if ($request->admin_notes) {
            $data['Admin Notes'] = $request->admin_notes;
        }

        if ($request->status === 'Rejected' && $request->rejection_reason) {
            $data['Rejection Reason'] = $request->rejection_reason;
        }
        // Removed the second, redundant check for admin_notes

        // Convert data to HTML format
        $output = [];
        foreach ($data as $key => $value) {
            // Handle the HTML link for the document directly
            if ($key === 'Supporting Document') {
                 $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . $value . '<br>';
            } else {
                $output[] = '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
            }
        }
        return implode('', $output);
      }

      /**
 * View the supporting document attached to a service request
 *
 * @param int $requestId The ID of the request
 * @param string $type The type of request (student/faculty), passed as a query parameter
 * @return \Illuminate\Http\Response
 */
public function viewSupportingDocument(Request $request, $requestId)
{
    // Get the type from query string or default to checking both types
    $type = $request->query('type', null);
    
    try {
        $documentPath = null;
        $fileName = null;

        // Check for the document based on request type
        if ($type == 'student' || $type === null) {
            $studentRequest = StudentServiceRequest::find($requestId);
            if ($studentRequest && $studentRequest->supporting_document) {
                $documentPath = storage_path('app/public/' . $studentRequest->supporting_document);
                $fileName = basename($studentRequest->supporting_document);
            }
        }
        
        if (($type == 'faculty' || $type === null) && $documentPath === null) {
            $facultyRequest = FacultyServiceRequest::find($requestId);
            if ($facultyRequest && $facultyRequest->supporting_document) {
                $documentPath = storage_path('app/public/' . $facultyRequest->supporting_document);
                $fileName = basename($facultyRequest->supporting_document);
            }
        }

        if (!$documentPath || !file_exists($documentPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Supporting document not found'
            ], 404);
        }

        // Determine file type and proper content type header
        $extension = pathinfo($documentPath, PATHINFO_EXTENSION);
        $contentType = $this->getContentType($extension);

        // Return the file with appropriate headers
        return response()->file($documentPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    } catch (\Exception $e) {
        Log::error('Error viewing supporting document: ' . $e->getMessage(), [
            'request_id' => $requestId,
            'type' => $type,
            'error_trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error retrieving the supporting document'
        ], 500);
    }
}

    /**
     * Helper method to determine content type based on file extension
     *
     * @param string $extension File extension
     * @return string Content type
     */
    private function getContentType($extension)
    {
        $contentTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];

        return $contentTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

      // Method to fetch all UITC Staff (likely for assignment dropdowns)
      public function getUITCStaff()
      {
          try {
              // Fetch all Admins with role 'UITC Staff'
              $uitcStaff = Admin::where('role', 'UITC Staff')
                ->where('availability_status', 'active') // Only get active staff
                ->select('id', 'name') // Only select needed fields
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'staff' => $uitcStaff
            ]);
          } catch (\Exception $e) {
              Log::error('Error fetching UITC Staff: ' . $e->getMessage());
              return response()->json([
                  'success' => false,
                  'message' => 'Failed to fetch UITC Staff'
              ], 500); // Internal Server Error
          }
      }


    /**
     * Assign a UITC Staff member to a specific service request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUitcStaff(Request $request)
    {
        // Log the incoming request data for debugging
        Log::info('Assign UITC Staff Request Data:', $request->all());

        // Validate the request data
        $validatedData = $request->validate([
            'request_id' => 'required|integer',
            'request_type' => 'required|string|in:student,faculty,new_student_service', // Enforce valid types
            'uitcstaff_id' => 'required|exists:admins,id', // Ensure staff exists in admins table
            'transaction_type' => 'required|string', // Basic string validation, consider enum if applicable
            'notes' => 'nullable|string|max:1000' // Add max length for notes
        ]);

        try {
            // Find the UITC staff member to get their name
            $uitcStaff = Admin::find($validatedData['uitcstaff_id']);
            if (!$uitcStaff) {
                // This should technically be caught by 'exists:admins,id' validation, but double-check
                return response()->json(['success' => false, 'message' => 'Assigned UITC Staff not found.'], 404);
            }
            $uitcStaffName = $uitcStaff->name;

            // Variables to hold details for notifications and logging
            $serviceRequest = null;
            $requestorName = '';
            $serviceCategory = '';
            //$userEmail = null; // We'll rely on the user relationship

            DB::beginTransaction(); // Start transaction for reliable update + notification sending

            // Handle different request types
            switch ($validatedData['request_type']) {
                case 'student': // Legacy type? Handle if still needed
                case 'new_student_service':
                    $serviceRequest = StudentServiceRequest::with('user')->find($validatedData['request_id']);
                    if (!$serviceRequest) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Student Service Request not found.'], 404);
                    }
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    //$userEmail = optional($serviceRequest->user)->email;
                    break;

                case 'faculty':
                    $serviceRequest = FacultyServiceRequest::with('user')->find($validatedData['request_id']);
                    if (!$serviceRequest) {
                        DB::rollBack();
                        Log::error('Faculty Service Request not found on assignment', ['request_id' => $validatedData['request_id']]);
                        return response()->json(['success' => false, 'message' => 'Faculty Service Request not found.'], 404);
                    }
                    $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                    $serviceCategory = $serviceRequest->service_category;
                    //$userEmail = optional($serviceRequest->user)->email;
                    break;

                default:
                    // Should not happen due to validation, but good practice
                    DB::rollBack();
                    Log::warning('Unknown request type during assignment', ['type' => $validatedData['request_type']]);
                    return response()->json(['success' => false, 'message' => 'Invalid request type provided.'], 400);
            }

            // Update the service request
            $serviceRequest->update([
                'assigned_uitc_staff_id' => $validatedData['uitcstaff_id'],
                'status' => 'In Progress', // Set status consistently
                'transaction_type' => $validatedData['transaction_type'],
                'admin_notes' => $validatedData['notes'] // Use 'notes' from validation
            ]);
             // updated_at is handled automatically by Eloquent

            // --- Notification Section ---

            // 1. Send notification to the assigned UITC staff
            try {
                // Format the service category to a human-readable name for the notification
                $formattedServiceCategory = $this->formatServiceCategory($serviceCategory);

                $uitcStaff->notify(new StaffAssignedToRequest(
                    $serviceRequest->id,
                    $formattedServiceCategory, // Send formatted name
                    $requestorName,
                    $validatedData['transaction_type'],
                    $validatedData['notes'] // Pass notes to staff notification
                ));

                Log::info('Staff assignment notification sent to UITC staff', [
                    'staff_id' => $uitcStaff->id,
                    'staff_name' => $uitcStaffName,
                    'request_id' => $serviceRequest->id,
                    'request_type' => $validatedData['request_type']
                ]);
            } catch (\Exception $e) {
                // Log the error but don't necessarily roll back the assignment
                // The assignment itself succeeded, only notification failed.
                Log::error('Failed to send notification to UITC staff: ' . $e->getMessage(), [
                    'staff_id' => $uitcStaff->id,
                    'request_id' => $serviceRequest->id,
                    'error_trace' => $e->getTraceAsString() // Log trace for debugging notification issues
                ]);
                // Optionally: Add a flag to the response indicating notification failure
            }


            // *** INTEGRATED CODE BLOCK STARTS HERE ***
            // Send notification to the user if the user exists and has an email
            if ($serviceRequest->user) {
                $user = $serviceRequest->user;

                // Ensure the user object has the Notifiable trait
                if (method_exists($user, 'notify')) {
                     try {
                        // Format category name for user notification
                        $formattedServiceCategoryUser = $this->formatServiceCategory($serviceCategory);

                        // Send the notification to the user about assignment
                        $user->notify(new ServiceRequestAssignedToUser(
                            $serviceRequest->id,
                            $formattedServiceCategoryUser, // Use formatted name
                            $uitcStaffName, // Staff Name
                            $validatedData['transaction_type'],
                            $validatedData['notes'] // Pass admin notes to user notification? Or keep separate? Decide based on requirements.
                        ));

                        Log::info('Assignment notification sent to user: ' . $user->email, [
                            'user_id' => $user->id,
                            'request_id' => $serviceRequest->id,
                            'staff_id' => $validatedData['uitcstaff_id'],
                            'staff_name' => $uitcStaffName
                        ]);
                     } catch (\Exception $e) {
                        // Log notification failure for the user
                        Log::error('Failed to send assignment notification to user: ' . $e->getMessage(), [
                           'user_id' => $user->id,
                           'request_id' => $serviceRequest->id,
                           'error_trace' => $e->getTraceAsString()
                        ]);
                     }
                } else {
                    Log::warning('User object found but does not use the Notifiable trait.', [
                        'user_id' => $user->id,
                        'request_id' => $serviceRequest->id
                    ]);
                }
            } else {
                Log::warning('Unable to send assignment notification - User relationship not loaded or does not exist for request.', [
                    'request_id' => $validatedData['request_id'],
                    'request_type' => $validatedData['request_type']
                ]);
            }
            // *** INTEGRATED CODE BLOCK ENDS HERE ***


            // If everything went well, commit the transaction
            DB::commit();

            Log::info('UITC Staff assigned successfully and notifications attempted.', [
                'request_id' => $validatedData['request_id'],
                'request_type' => $validatedData['request_type'],
                'assigned_uitc_staff_id' => $validatedData['uitcstaff_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'UITC Staff assigned successfully.',
                'request_type' => $validatedData['request_type'], // Useful for frontend updates
                'new_status' => 'In Progress' // Send back the new status
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically if needed, though Laravel handles this by default
             Log::error('Validation failed during staff assignment: ', $e->errors());
             return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Rollback transaction in case of any other error during the process
            DB::rollBack();
            Log::error('Error assigning UITC Staff: ' . $e->getMessage(), [
                'request_id' => $validatedData['request_id'] ?? 'unknown',
                'request_type' => $validatedData['request_type'] ?? 'unknown',
                'error_trace' => $e->getTraceAsString() // Log stack trace for detailed debugging
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while assigning UITC Staff. Please try again.'
                // Avoid exposing raw error messages like $e->getMessage() to the client in production
            ], 500); // Internal Server Error
        }
    }

    /**
     * Delete one or more service requests based on IDs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteServiceRequests(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'required|integer|distinct' // Ensure IDs are integers and unique
        ]);

        $deletedCount = 0;
        $notFoundCount = 0;
        $errors = [];

        // Use a transaction to ensure atomicity if needed, although simple deletion might not strictly require it
        // DB::beginTransaction(); // Uncomment if other related actions need atomicity

        try {
            foreach ($validatedData['request_ids'] as $requestId) {
                $deleted = false;

                // Prioritize specific models first
                if (StudentServiceRequest::destroy($requestId)) {
                    $deleted = true;
                } elseif (FacultyServiceRequest::destroy($requestId)) {
                    $deleted = true;
                }
                 // Uncomment if the generic ServiceRequest model is still in use for deletable records
                 // elseif (ServiceRequest::destroy($requestId)) {
                 //     $deleted = true;
                 // }

                if ($deleted) {
                    $deletedCount++;
                    Log::info('Service Request deleted', ['request_id' => $requestId]);
                } else {
                    $notFoundCount++;
                    Log::warning('Attempted to delete non-existent request ID', ['request_id' => $requestId]);
                    // Optionally collect not found IDs
                    // $errors[] = "Request ID {$requestId} not found.";
                }
            }

            // DB::commit(); // Uncomment if using transaction

            $message = "{$deletedCount} request(s) deleted successfully.";
            if ($notFoundCount > 0) {
                $message .= " {$notFoundCount} request ID(s) were not found.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'not_found_count' => $notFoundCount
            ]);

        } catch (\Exception $e) {
            // DB::rollBack(); // Uncomment if using transaction
            Log::error('Service Request Deletion Error: ' . $e->getMessage(), [
                 'request_ids' => $validatedData['request_ids'],
                 'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during deletion. Please try again.'
                // Avoid exposing raw error messages to the client
            ], 500);
        }
    }

    /**
     * Reject a specific service request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectServiceRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required|integer',
            'request_type' => 'required|string|in:student,faculty,new_student_service',
            'rejection_reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000' // Optional admin notes for rejection context
        ]);

        try {
            $serviceRequest = null;
            $requestorName = 'User'; // Default
            $serviceCategory = 'Service'; // Default

            DB::beginTransaction(); // Use transaction for update + notification

            // Find the correct request model based on type
            switch ($validatedData['request_type']) {
                case 'new_student_service':
                    $serviceRequest = StudentServiceRequest::with('user')->find($validatedData['request_id']);
                    break;
                case 'faculty':
                    $serviceRequest = FacultyServiceRequest::with('user')->find($validatedData['request_id']);
                    break;
                case 'student': // Legacy?
                    $serviceRequest = ServiceRequest::with('user')->find($validatedData['request_id']);
                    break;
            }

            if (!$serviceRequest) {
                 DB::rollBack();
                 return response()->json(['success' => false, 'message' => 'Service Request not found.'], 404);
            }

            // Get details for notification
            $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name; // Assumes these fields exist
            $serviceCategory = $serviceRequest->service_category ?? 'Unspecified Service';
            // For legacy 'student' type, adjust name source if needed:
            // if ($validatedData['request_type'] === 'student') {
            //     $requestorName = optional($serviceRequest->user)->name ?? 'Student';
            // }


            // Update the service request status and reason
            $serviceRequest->update([
                'status' => 'Rejected',
                'rejection_reason' => $validatedData['rejection_reason'],
                'admin_notes' => $validatedData['notes'], // Store admin notes if provided
                'rejected_at' => now(), // Record rejection timestamp
                'assigned_uitc_staff_id' => null // Unassign staff if any was assigned previously
            ]);

            // --- Send Notification to User ---
            if ($serviceRequest->user) {
                $user = $serviceRequest->user;
                 if (method_exists($user, 'notify')) {
                    try {
                        // Format category for notification
                        $formattedServiceCategory = $this->formatServiceCategory($serviceCategory);

                        // Send the rejection notification
                        // Pass the rejectionReason and notes to the notification
                        $user->notify(new ServiceRequestRejected(
                            $serviceRequest->id,
                            $formattedServiceCategory,
                            $requestorName, // Pass requestor name if needed by notification template
                            $validatedData['rejection_reason'],
                            $validatedData['notes'] // Pass admin notes to notification?
                        ));

                        Log::info('Rejection notification sent to user', [
                            'user_id' => $user->id,
                            'user_email' => $user->email,
                            'request_id' => $serviceRequest->id
                        ]);
                    } catch (\Exception $e) {
                         Log::error('Failed to send rejection notification to user: ' . $e->getMessage(), [
                             'user_id' => $user->id,
                             'request_id' => $serviceRequest->id,
                             'error_trace' => $e->getTraceAsString()
                         ]);
                         // Don't rollback the rejection itself, just log the notification error
                    }
                 } else {
                     Log::warning('User object found for rejected request but does not use Notifiable trait.', [
                        'user_id' => $user->id,
                        'request_id' => $serviceRequest->id
                    ]);
                 }
            } else {
                Log::warning('Unable to send rejection notification - User relationship not loaded or does not exist for request.', [
                     'request_id' => $validatedData['request_id'],
                     'request_type' => $validatedData['request_type']
                ]);
            }

            DB::commit(); // Commit the rejection update

            return response()->json([
                'success' => true,
                'message' => 'Service request rejected successfully.',
                'new_status' => 'Rejected' // Send back new status
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
             Log::error('Validation failed during request rejection: ', $e->errors());
             return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on any other error
            Log::error('Service Request Rejection Error: ' . $e->getMessage(), [
                 'request_id' => $validatedData['request_id'] ?? 'unknown',
                 'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the service request.'
                // Avoid exposing raw error messages
            ], 500);
        }
    }

        /**
     * Format a date to remove time component
     *
     * @param string|null $date The date to format
     * @return string Formatted date or default text if null
     */
    private function formatDate($date)
    {
        if (!$date) {
            return 'Not provided';
        }
        
        // Convert to Carbon instance if not already
        $carbonDate = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        
        // Format only as date without time
        return $carbonDate->format('M d, Y');
    }

    /**
     * Filter service requests based on status via AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterRequests(Request $request)
    {
        $status = $request->input('status');
        $searchTerm = $request->input('search'); // Get search term
        $page = $request->input('page', 1);
        $perPage = 10; // Number of items per page

        $requests = [];

        try {
            // Fetch student requests
            $studentQuery = StudentServiceRequest::with('user');
            if ($status && $status !== 'all') {
                $studentQuery->where('status', $status);
            }

            // Apply search filter if search term exists
            if ($searchTerm) {
                $studentQuery->where(function ($q) use ($searchTerm) {
                    $q->where('id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('service_category', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            $newStudentRequests = $studentQuery->get();
            foreach($newStudentRequests as $req) { // Use different variable name to avoid conflict
                $user = $req->user;
                $requests[] = [
                    'id' => $req->id,
                    'user_id' => $req->user_id,
                    'role' => $user ? $user->role : 'Student',
                    'service' => $req->service_category,
                    'request_data' => $this->formatStudentServiceRequestData($req),
                    'date' => $req->created_at,
                    'status' => $req->status ?? 'Pending',
                    'type' => 'new_student_service',
                    'updated_at' => $req->updated_at,
                    'rejection_reason' => $req->rejection_reason ?? null,
                    'notes' => $req->admin_notes ?? null,
                ];
            }

            // Fetch faculty requests
            $facultyQuery = FacultyServiceRequest::with('user');
            if ($status && $status !== 'all') {
                $facultyQuery->where('status', $status);
            }

            // Apply search filter if search term exists
            if ($searchTerm) {
                $facultyQuery->where(function ($q) use ($searchTerm) {
                    $q->where('id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      // Add faculty_id search if that field exists on the model
                      // ->orWhere('faculty_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('service_category', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            $facultyRequests = $facultyQuery->get();
            foreach($facultyRequests as $req) { // Use different variable name
                $user = $req->user;
                $requests[] = [
                    'id' => $req->id,
                    'user_id' => $req->user_id,
                    'role' => $user ? $user->role : 'Faculty',
                    'service' => $req->service_category,
                    'request_data' => $this->formatFacultyServiceRequestData($req),
                    'date' => $req->created_at,
                    'status' => $req->status ?? 'Pending',
                    'rejection_reason' => $req->rejection_reason ?? null,
                    'notes' => $req->admin_notes ?? null,
                    'type' => 'faculty',
                    'updated_at' => $req->updated_at,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error filtering service requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load requests.'], 500);
        }

        // Sort combined requests by date (latest first)
        $allRequests = collect($requests)->sortByDesc('date');

        // Paginate the collection manually
        $items = $allRequests->forPage($page, $perPage);

        // Create a new paginator instance
        $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->values(), // Ensure it's a non-associative array
            $allRequests->count(),
            $perPage,
            $page,
            // Important: Set the path for pagination links to the filter route itself
            ['path' => route('admin.service.requests.filter'), 'query' => $request->query()]
        );

        // Render the partial view for the table body
        $tableBodyHtml = view('admin.partials.service-request-rows', ['requests' => $paginatedRequests])->render();

        // Render the pagination links
        $paginationHtml = $paginatedRequests->links('vendor.pagination.custom')->toHtml();

        return response()->json([
            'table_body' => $tableBodyHtml,
            'pagination' => $paginationHtml
        ]);
    }
}
