<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/myrequest.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <style>
       
        .remaining-days.positive {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .remaining-days.negative {
            background-color: #ffebee;
            color: #c62828;
        }
        /* Add loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <title>My Requests</title>
</head>
<body class="{{ Auth::check() ? 'user-authenticated' : '' }}" data-user-role="{{ Auth::user()->role }}">

    @inject('serviceHelper', 'App\Helpers\ServiceHelper') {{-- Inject ServiceHelper --}}

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="main-content"> {{-- Changed class from content to main-content --}}
        <h1>My Request</h1>
        <div class="form-container">
            <div class="dropdown-container">
                <select name="status" id="status">
                    <option value="all">All</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Overdue">Overdue</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Unresolvable">Unresolvable</option>
                </select>
                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" name="search" id="search-input" placeholder="Search...">
                    <button class="search-btn" type="button">Search</button>
                </div>
            </div>
            
            <div class="request-table-container">
                <form action="">
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Service</th>
                                <th>Date & Time Submitted</th>
                                <th>Date & Time Completed</th>
                                <th>Status</th>
                                <th>Remaining Days</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                            {{-- @dd($request) --}} {{-- Removed dd() --}}
                            <tr>
                                <td>
                                    <span class="clickable-request-id" data-id="{{ $request->id }}" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                        @if(Auth::user()->role == "Student")
                                            {{ 'SSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                        @else
                                            {{ 'FSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    {{-- Use ServiceHelper to format the service name --}}
                                    {{ $serviceHelper::formatServiceCategory($request->service_category, $request->description) }}
                                </td>
                                <td>
                                    <span>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span><br>
                                    <span>{{ \Carbon\Carbon::parse($request->created_at)->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @if($request->status == 'Completed')
                                    <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('M d, Y') }}</span><br>
                                    <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('h:i A') }}</span>
                                    @else
                                        –
                                    @endif
                                </td>
                                <td>
                                    @if($request->status == 'Pending')
                                        <span class="custom-badge custom-badge-warning">{{ $request->status }}</span>
                                    @elseif($request->status == 'In Progress')
                                        <span class="custom-badge custom-badge-info">{{ $request->status }}</span>
                                    @elseif($request->status == 'Completed')
                                        <span class="custom-badge custom-badge-success">{{ $request->status }}</span>
                                    @elseif($request->status == 'Overdue')
                                        <span class="custom-badge custom-badge-overdue">{{ $request->status }}</span>
                                    @elseif($request->status == 'Rejected')
                                        <span class="custom-badge custom-badge-danger">{{ $request->status }}</span>
                                    @elseif($request->status == 'Unresolvable')
                                        <span class="custom-badge custom-badge-gray">{{ $request->status }}</span>
                                    @else
                                        <span class="custom-badge custom-badge-secondary">{{ $request->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->status == 'In Progress' && isset($request->transaction_type))
                                        @php
                                            $transactionLimits = [
                                                'Simple Transaction' => 3,
                                                'Complex Transaction' => 7,
                                                'Highly Technical Transaction' => 20,
                                            ];
                                            $assignedDate = \Carbon\Carbon::parse($request->updated_at)->startOfDay();
                                            $today = \Carbon\Carbon::today();

                                            // 1. Find the first business day after assignment
                                            $firstBusinessDay = $assignedDate->copy();
                                            while (true) {
                                                $dayOfWeek = $firstBusinessDay->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                $isHoliday = \App\Models\Holiday::isHoliday($firstBusinessDay);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($firstBusinessDay, 'semestral_break');
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    break;
                                                }
                                                $firstBusinessDay->addDay();
                                            }

                                            // 2. Calculate last allowed business day
                                            $limit = $transactionLimits[$request->transaction_type] ?? 0;
                                            $lastAllowedDay = $firstBusinessDay->copy();
                                            $businessDaysCounted = 0;
                                            while ($businessDaysCounted < $limit) {
                                                $dayOfWeek = $lastAllowedDay->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                $isHoliday = \App\Models\Holiday::isHoliday($lastAllowedDay);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($lastAllowedDay, 'semestral_break');
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    $businessDaysCounted++;
                                                }
                                                if ($businessDaysCounted < $limit) {
                                                    $lastAllowedDay->addDay();
                                                }
                                            }

                                            // 3. Find the next business day after the last allowed day (for overdue)
                                            $overdueDate = $lastAllowedDay->copy()->addDay();
                                            while (true) {
                                                $dayOfWeek = $overdueDate->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                $isHoliday = \App\Models\Holiday::isHoliday($overdueDate);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($overdueDate, 'semestral_break');
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    break;
                                                }
                                                $overdueDate->addDay();
                                            }

                                            // 4. Calculate business days elapsed (from first business day)
                                            $businessDaysElapsed = 0;
                                            $currentDate = $firstBusinessDay->copy();
                                            while ($currentDate->lte($today)) {
                                                $dayOfWeek = $currentDate->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                $isHoliday = \App\Models\Holiday::isHoliday($currentDate);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($currentDate, 'semestral_break');
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    $businessDaysElapsed++;
                                                }
                                                $currentDate->addDay();
                                            }

                                            $remainingDays = $limit - $businessDaysElapsed;
                                        @endphp
                                        @if($remainingDays > 0)
                                            <span class="remaining-days positive">
                                                Overdue in {{ $remainingDays }} days
                                                <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                                                    <i class="fas fa-info-circle"></i>
                                                </span>
                                                <br>
                                                <small style="color:#888;">Overdue on: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
                                            </span>
                                        @else
                                            <span class="remaining-days negative">
                                                Overdue by {{ abs($remainingDays) }} days
                                                <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                                                    <i class="fas fa-info-circle"></i>
                                                </span>
                                                <br>
                                                <small style="color:#888;">Was due: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($request->status != 'Completed' && $request->status != 'Rejected' && $request->status != 'Cancelled' && $request->status != 'In Progress' && $request->status != 'Unresolvable')
                                    <button type="button" class="btn-edit" data-id="{{ $request->id }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn-cancel" data-id="{{ $request->id }}">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
                <div class="pagination-container">
                    {{ $requests->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" role="dialog" aria-labelledby="requestDetailsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Request ID:</strong> <span id="detailsRequestId"></span></p>
                        <p><strong>Service:</strong> <span id="detailsService"></span></p>
                        <p><strong>Status:</strong> <span id="detailsStatus" class="badge"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date Submitted:</strong> <span id="detailsSubmitted"></span></p>
                        <p><strong>Date Completed:</strong> <span id="detailsCompleted"></span></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h6>Request Information</h6>
                        <div id="detailsInformation"></div>
                    </div>
                </div>
                
                <!-- Assignment Information (shown for In Progress or Completed) -->
                <div id="assignmentSection" class="mt-3" style="display: none;">
                    <hr>
                    <h6>Assignment Information</h6>
                    <p><strong>Assigned To:</strong> <span id="detailsAssignedTo"></span></p>
                    <p><strong>Transaction Type:</strong> <span id="detailsTransactionType"></span></p>
                    <p><strong>Admin Notes:</strong> <span id="detailsAdminNotes"></span></p>
                </div>
                
                <!-- Rejection Information (shown if rejected) -->
                <div id="rejectionSection" class="mt-3" style="display: none;">
                    <hr>
                    <h6>Rejection Information</h6>
                    <p><strong>Reason:</strong> <span id="detailsRejectionReason"></span></p>
                    <p><strong>Notes:</strong> <span id="detailsRejectionNotes"></span></p>
                    <p><strong>Rejected Date:</strong> <span id="detailsRejectedDate"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm">
                    <input type="hidden" id="editServiceId">
                    <div class="form-group">
                        <label>Service Name</label>
                        <input type="text" class="form-control" id="editServiceName" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="editServiceDescription" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditedServiceBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- View Service Modal -->
<div class="modal fade" id="viewServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Service</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Service:</strong> <span id="viewServiceName"></span></p>
                <p><strong>Status:</strong> <span id="viewServiceStatus"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Import JS files only once at the end -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>

    <script>
        // Service category formatter - moved to a global function
        function formatServiceCategory(category) {
            const categoryMap = {
                'create': 'Create MS Office/TUP Email Account',
                'reset_email_password': 'Reset MS Office/TUP Email Password',
                'change_of_data_ms': 'Change of Data (MS Office)',
                'reset_tup_web_password': 'Reset TUP Web Password',
                'reset_ers_password': 'Reset ERS Password',
                'change_of_data_portal': 'Change of Data (Portal)',
                'dtr': 'Daily Time Record',
                'biometric_record': 'Biometric Record',
                'biometrics_enrollement': 'Biometrics Enrollment',
                'new_internet': 'New Internet Connection',
                'new_telephone': 'New Telephone Connection',
                'repair_and_maintenance': 'Internet/Telephone Repair and Maintenance',
                'computer_repair_maintenance': 'Computer Repair and Maintenance',
                'printer_repair_maintenance': 'Printer Repair and Maintenance',
                'request_led_screen': 'LED Screen Request',
                'install_application': 'Install Application/Information System/Software',
                'post_publication': 'Post Publication/Update of Information Website',
                'data_docs_reports': 'Data, Documents and Reports',
                'others': 'Other Service'
            };
            
            return categoryMap[category] || category;
        }

        // Helper function to build detailed request information
        function buildDetailedRequestInfo(response, userRole) {
            // Start with basic user information
            let infoHtml = `
                <p><strong>First Name:</strong> ${response.first_name}</p>
                <p><strong>Last Name:</strong> ${response.last_name}</p>
            `;
            
            // Add student-specific fields
            if (userRole === 'Student') {
                infoHtml += `<p><strong>Student ID:</strong> ${response.student_id || '-'}</p>`;
            }
            
            // Add service-specific fields based on service category
            switch(response.service_category) {
                case 'reset_email_password':
                case 'reset_tup_web_password':
                case 'reset_ers_password':
                    if (response.account_email) {
                        infoHtml += `<p><strong>Account Email:</strong> ${response.account_email}</p>`;
                    }
                    break;
                    
                case 'change_of_data_ms':
                case 'change_of_data_portal':
                    if (response.data_type) {
                        infoHtml += `<p><strong>Data Type:</strong> ${response.data_type}</p>`;
                    }
                    if (response.new_data) {
                        infoHtml += `<p><strong>New Data:</strong> ${response.new_data}</p>`;
                    }
                    if (response.supporting_document) {
                        infoHtml += `<p><strong>Supporting Document:</strong> Submitted</p>`;
                    }
                    break;
                    
                case 'biometric_record': // Added this case
                case 'dtr':
                    // Display fields for both DTR and Biometric Record
                    // Always display the label, show 'N/A' if the value is null, undefined, or empty
                    infoHtml += `<p><strong>Month(s) of Record:</strong> ${response.dtr_months || 'N/A'}</p>`; // Generic label
                    if (response.dtr_with_details !== undefined) {
                        infoHtml += `<p><strong>Include In/Out Details:</strong> ${response.dtr_with_details ? 'Yes' : 'No'}</p>`; // Display this for both cases
                    }
                    break;
                    
                case 'biometrics_enrollement':
                    // Add specific biometrics enrollment fields
                    if (response.middle_name) {
                        infoHtml += `<p><strong>Middle Name:</strong> ${response.middle_name}</p>`;
                    }
                    if (response.college) {
                        infoHtml += `<p><strong>College:</strong> ${response.college}</p>`;
                    }
                    if (response.department) {
                        infoHtml += `<p><strong>Department:</strong> ${response.department}</p>`;
                    }
                    if (response.plantilla_position) {
                        infoHtml += `<p><strong>Plantilla Position:</strong> ${response.plantilla_position}</p>`;
                    }
                    if (response.date_of_birth) {
                        infoHtml += `<p><strong>Date of Birth:</strong> ${response.date_of_birth}</p>`;
                    }
                    if (response.phone_number) {
                        infoHtml += `<p><strong>Phone Number:</strong> ${response.phone_number}</p>`;
                    }
                    if (response.address) {
                        infoHtml += `<p><strong>Address:</strong> ${response.address}</p>`;
                    }
                    if (response.blood_type) {
                        infoHtml += `<p><strong>Blood Type:</strong> ${response.blood_type}</p>`;
                    }
                    if (response.emergency_contact_person) {
                        infoHtml += `<p><strong>Emergency Contact Person:</strong> ${response.emergency_contact_person}</p>`;
                    }
                    if (response.emergency_contact_number) {
                        infoHtml += `<p><strong>Emergency Contact Number:</strong> ${response.emergency_contact_number}</p>`;
                    }
                    break;
                    
                case 'new_internet':
                case 'new_telephone':
                case 'new_internet':
                case 'new_telephone':
                case 'repair_and_maintenance':
                case 'computer_repair_maintenance':
                case 'printer_repair_maintenance':
                    // Show location for all these types
                    if (response.location) {
                        infoHtml += `<p><strong>Location:</strong> ${response.location}</p>`;
                    }
                    // Show problems encountered for all these types (will show N/A if null)
                    infoHtml += `<p><strong>Problems Encountered:</strong> ${response.problem_encountered || 'N/A'}</p>`;
                    break;
                    
                case 'request_led_screen':
                    if (response.preferred_date) {
                        const formattedDate = new Date(response.preferred_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        infoHtml += `<p><strong>Preferred Date:</strong> ${formattedDate}</p>`;
                    }
                    if (response.preferred_time) {
                        let formattedTime;
                        // Check if preferred_time is a full ISO datetime string
                        if (response.preferred_time.includes('T')) {
                            const dateTime = new Date(response.preferred_time);
                            formattedTime = dateTime.toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true,
                                timeZone: 'Asia/Manila'
                            });
                        } else {
                            // Assume it's a simple time string (HH:mm:ss)
                            const timeParts = response.preferred_time.split(':');
                            const hours = parseInt(timeParts[0], 10);
                            const minutes = timeParts[1];
                            const ampm = hours >= 12 ? 'PM' : 'AM';
                            const displayHours = hours % 12 || 12;
                            formattedTime = `${displayHours}:${minutes} ${ampm}`;
                        }
                        infoHtml += `<p><strong>Preferred Time:</strong> ${formattedTime}</p>`;
                    }
                    if (response.led_screen_details) {
                        infoHtml += `<p><strong>Additional Details:</strong> ${response.led_screen_details}</p>`;
                    }
                    break;
                    
                case 'install_application':
                    // Always display the label, show 'N/A' if the value is null, undefined, or empty
                    infoHtml += `<p><strong>Application Name:</strong> ${response.application_name || 'N/A'}</p>`;
                    infoHtml += `<p><strong>Purpose of Installation:</strong> ${response.installation_purpose || 'N/A'}</p>`;
                    infoHtml += `<p><strong>Additional Requirements:</strong> ${response.installation_notes || 'N/A'}</p>`;
                    break;
                    
                case 'post_publication':
                    if (response.publication_author) {
                        infoHtml += `<p><strong>Author:</strong> ${response.publication_author}</p>`;
                    }
                    if (response.publication_editor) {
                        infoHtml += `<p><strong>Editor:</strong> ${response.publication_editor}</p>`;
                    }
                    if (response.publication_start_date) {
                        const pubStart = new Date(response.publication_start_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                        infoHtml += `<p><strong>Date of Publication:</strong> ${pubStart}</p>`;
                    }
                    if (response.publication_end_date) {
                        const pubEnd = new Date(response.publication_end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                        infoHtml += `<p><strong>End of Publication:</strong> ${pubEnd}</p>`;
                    }
                    break;
                    
                case 'data_docs_reports':
                    if (response.data_documents_details) {
                        infoHtml += `<p><strong>Details:</strong> ${response.data_documents_details}</p>`;
                    }
                    break;
            }
            
            // Add description for all services if available
            if (response.description) {
                infoHtml += `<p><strong>Description:</strong> ${response.description}</p>`;
            }
            
            // Add additional notes if available
            if (response.additional_notes) {
                infoHtml += `<p><strong>Additional Notes:</strong> ${response.additional_notes}</p>`;
            }
            
            // Add survey information if available
            if (response.status === 'Completed') {
                if (response.survey_rating) {
                    infoHtml += `<p><strong>Survey Rating:</strong> ${response.survey_rating}/5</p>`;
                }
                
                if (response.survey_comments) {
                    infoHtml += `<p><strong>Survey Comments:</strong> ${response.survey_comments}</p>`;
                }
                
                // Convert boolean/numeric survey_issue_resolved to Yes/No
                if (response.survey_issue_resolved !== undefined && response.survey_issue_resolved !== null) {
                    // Check if it's a string "yes"/"no" or a boolean/numeric 1/0
                    let issueResolved;
                    if (typeof response.survey_issue_resolved === 'string') {
                        issueResolved = response.survey_issue_resolved.toLowerCase() === 'yes' ? 'Yes' : 'No';
                    } else {
                        issueResolved = response.survey_issue_resolved ? 'Yes' : 'No';
                    }
                    infoHtml += `<p><strong>Issue Resolved:</strong> ${issueResolved}</p>`;
                }
            }
            
            // Add any other fields in the response that might be relevant
            const commonFields = ['id', 'user_id', 'service_category', 'first_name', 'last_name', 'student_id', 
                                'account_email', 'data_type', 'new_data', 'description', 'additional_notes', 
                                'created_at', 'updated_at', 'status', 'assigned_uitc_staff_id', 'transaction_type', 
                                'admin_notes', 'rejection_reason', 'supporting_document', 'assigned_uitc_staff',
                                'middle_name', 'college', 'department', 'plantilla_position', 'date_of_birth',
                                'phone_number', 'address', 'blood_type', 'emergency_contact_person',
                                'emergency_contact_number', 'location', 'problem_encountered', 'preferred_date',
                                'preferred_time', 'led_screen_details', 'application_name', 'installation_purpose',
                                'installation_notes', 'publication_author', 'publication_editor', 
                                'publication_start_date', 'publication_end_date', 'data_documents_details', 
                                'dtr_months', 'dtr_with_details', 'survey_rating', 'survey_comments', 'survey_issue_resolved'];
            
            for (const key in response) {
                // Skip if this is a common field we've already handled or if the value is null/undefined/object
                if (commonFields.includes(key) || response[key] === null || response[key] === undefined || typeof response[key] === 'object') {
                    continue;
                }
                
                // Format the field name for display (capitalize and add spaces)
                const fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                // For boolean values, convert to Yes/No
                let fieldValue = response[key];
                if (typeof fieldValue === 'boolean' || (typeof fieldValue === 'number' && (fieldValue === 0 || fieldValue === 1))) {
                    fieldValue = fieldValue ? 'Yes' : 'No';
                }
                
                // Add the field to the HTML
                infoHtml += `<p><strong>${fieldName}:</strong> ${fieldValue}</p>`;
            }
            
            return infoHtml;
        }

        // Document Ready - Main initialization
        $(document).ready(function() {
            // Initialize filters from URL
            initializeFiltersFromURL();
            
            // ======= VIEW REQUEST DETAILS =======
            $(document).on('click', '.clickable-request-id', function() {
                const id = $(this).data('id');
                const userRole = $('body').data('user-role') || '';
                const token = $('meta[name="csrf-token"]').attr('content');
                
                // Show loading state using SweetAlert2
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Set the correct endpoint based on role
                const requestUrl = userRole === 'Student' ? `/student/request/${id}` : `/faculty/request/${id}`;
                
                // Fetch request details
                $.ajax({
                    url: requestUrl,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': token },
                    success: function(response) {
                        // Close loading state
                        Swal.close();

                        if (response.error) {
                            alert('Error: ' + response.error);
                            return;
                        }
                        
                        // Format request ID
                        const requestPrefix = userRole === 'Student' ? 'SSR-' : 'FSR-';
                        const dateString = new Date(response.created_at).toISOString().slice(0, 10).replace(/-/g, '');
                        const formattedId = String(response.id).padStart(4, '0');
                        const displayId = `${requestPrefix}${dateString}-${formattedId}`;
                        
                        // Update the modal with basic info
                        $('#detailsRequestId').text(displayId);
                        $('#detailsService').text(formatServiceCategory(response.service_category));
                        
                        // Update status with badge
                        const statusBadge = $('#detailsStatus');
                        statusBadge.text(response.status);
                        statusBadge.removeClass().addClass('custom-badge');

                        if (response.status === 'Pending') {
                            statusBadge.addClass('custom-badge-warning');
                        } else if (response.status === 'In Progress') {
                            statusBadge.addClass('custom-badge-info');
                        } else if (response.status === 'Completed') {
                            statusBadge.addClass('custom-badge-success');
                        } else if (response.status === 'Rejected') {
                            statusBadge.addClass('custom-badge-danger');
                        } else {
                            statusBadge.addClass('custom-badge-secondary');
                        }
                        
                        // Format dates
                        const submittedDate = new Date(response.created_at).toLocaleString();
                        const completedDate = response.status === 'Completed' && response.updated_at 
                            ? new Date(response.updated_at).toLocaleString() 
                            : '-';
                        
                        $('#detailsSubmitted').text(submittedDate);
                        $('#detailsCompleted').text(completedDate);
                        
                        // Build detailed request information
                        let infoHtml = buildDetailedRequestInfo(response, userRole);
                        $('#detailsInformation').html(infoHtml);
                        
                        // Show/hide assignment information as needed
                        if (response.status === 'In Progress' || response.status === 'Completed') {
                            $('#assignmentSection').show();
                            
                            // Handle assigned staff name
                            let staffName = '-';
                            if (response.assigned_uitc_staff && response.assigned_uitc_staff.name) {
                                staffName = response.assigned_uitc_staff.name;
                            } else if (response.assigned_uitc_staff_id) {
                                staffName = `Staff ID: ${response.assigned_uitc_staff_id}`;
                            }
                            
                            $('#detailsAssignedTo').text(staffName);
                            $('#detailsTransactionType').text(response.transaction_type || '-');
                            $('#detailsAdminNotes').text(response.admin_notes || 'No notes');
                        } else {
                            $('#assignmentSection').hide();
                        }
                        
                        // Show/hide rejection information as needed
                        if (response.status === 'Rejected') {
                            $('#rejectionSection').show();
                            $('#detailsRejectionReason').text(response.rejection_reason || '-');
                            $('#detailsRejectionNotes').text(response.admin_notes || 'No notes');
                            $('#detailsRejectedDate').text(response.updated_at ? new Date(response.updated_at).toLocaleString() : '-');
                        } else {
                            $('#rejectionSection').hide();
                        }
                        
                        // Show the modal
                        $('#requestDetailsModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        // Close loading state
                        Swal.close();
                        
                        let errorMessage = 'Could not load request details. ';
                        
                        if (xhr.status === 404) {
                            errorMessage += 'Request not found.';
                        } else if (xhr.status === 403) {
                            errorMessage += 'You do not have permission to view this request.';
                        } else if (xhr.status === 500) {
                            errorMessage += 'Server error occurred.';
                        } else {
                            errorMessage += 'Please try again later.';
                        }
                        
                        alert(errorMessage);
                    }
                });
            });

            // ======= EDIT REQUEST =======
            // Redirect to a dedicated edit page when the edit button is clicked
            $('.request-table').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                // Determine the correct edit URL based on user role (optional, could be handled by a single route)
                const userRole = $('body').data('user-role') || '';
                // For simplicity, let's use a single route structure handled by RequestsController
                window.location.href = `/requests/${id}/edit`; 
            });

            // Remove the old modal save logic as it's no longer needed
            // $('#saveEditedServiceBtn').on('click', function() { ... }); // REMOVED

            // ======= CANCEL REQUEST =======
            $('.request-table').on('click', '.btn-cancel', function() {
                const id = $(this).data('id');
                const requestRow = $(this).closest('tr');
                const serviceName = requestRow.find('td:nth-child(2)').text().trim();
                
                Swal.fire({
                    title: 'Cancel Request',
                    html: `
                        <p>Are you sure you want to cancel this request?</p>
                        <p class="text-muted">This action cannot be undone.</p>
                        <p><strong>Service:</strong> ${serviceName}</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Cancel Request',
                    cancelButtonText: 'No, Keep Request'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const token = $('meta[name="csrf-token"]').attr('content');
                        const userRole = $('body').data('user-role') || '';
                        const cancelUrl = userRole === 'Student' ? `/student/cancel-request/${id}` : `/faculty/cancel-request/${id}`;
                        
                        // Send AJAX request to cancel
                        $.ajax({
                            url: cancelUrl,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            success: function(response) {
                                // Show success message
                                Swal.fire({
                                    title: 'Request Cancelled',
                                    text: 'Your request has been cancelled successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Reload the page to reflect the changes
                                    window.location.reload();
                                });
                            },
                            error: function(xhr, status, error) {
                                // Show error message
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error cancelling request: ' + (xhr.responseJSON?.message || 'Unknown error'),
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });


            // ======= FILTER AND SEARCH =======
            // Filter by Status
            $('#status').on('change', function() {
                applyFilters();
            });
            
            // Search button click
            $('.search-btn').on('click', function() {
                applyFilters();
            });
            
            // Enter key in search input
            $('#search-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    applyFilters();
                }
            });
            
            // Function to apply filters
            function applyFilters() {
                const selectedStatus = $('#status').val();
                const searchTerm = $('#search-input').val().trim();
                
                // Create URL with query parameters
                const url = new URL(window.location.href);
                
                // Clear existing parameters
                url.search = '';
                
                // Add status parameter if not "all"
                if (selectedStatus && selectedStatus !== 'all') {
                    url.searchParams.set('status', selectedStatus);
                }
                
                // Add search parameter if not empty
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                }
                
                window.location.href = url.toString();
            }
            
            // Function to initialize filters from URL
            function initializeFiltersFromURL() {
                const urlParams = new URLSearchParams(window.location.search);
                
                // Set status dropdown
                const statusParam = urlParams.get('status');
                if (statusParam) {
                    $('#status').val(statusParam);
                }
                
                // Set search input
                const searchParam = urlParams.get('search');
                if (searchParam) {
                    $('#search-input').val(searchParam);
                }
            }

            // Handle modal focus management
            $('#requestDetailsModal').on('shown.bs.modal', function () {
                // Focus the first focusable element in the modal
                $(this).find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first().focus();
            });

            // When modal is hidden, return focus to the trigger element
            $('#requestDetailsModal').on('hidden.bs.modal', function () {
                $('.clickable-request-id:focus').focus();
            });
        });

        $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>
</environment_details>
