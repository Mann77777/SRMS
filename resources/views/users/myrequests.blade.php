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
    <script>
$(document).ready(function() {
    // View request details
    $('.btn-view').click(function() {
        const id = $(this).data('id');
        $.get(`/faculty/request/${id}`, function(data) {
            $('#viewServiceName').text(data.service_category);
            $('#viewServiceStatus').text(data.status);
              $('#viewServiceSubmittedDate').text(new Date(data.created_at).toLocaleString());
        $('#viewServiceCompletedDate').text(data.status == 'Completed' ? new Date(data.updated_at).toLocaleString() : '-')
            
            // Add more fields as needed
            const modalBody = $('#viewServiceModal .modal-body');
            modalBody.html(`
                <p><strong>Request ID:</strong> ${data.id}</p>
                <p><strong>Service:</strong> ${data.service_category}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>First Name:</strong> ${data.first_name}</p>
                <p><strong>Last Name:</strong> ${data.last_name}</p>
                <p><strong>Date Submitted:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                <p><strong>Date Completed:</strong> ${data.status == 'Completed' ? new Date(data.updated_at).toLocaleString() : '-'}</p>
                <p><strong>Date Submitted:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                ${data.description ? `<p><strong>Description:</strong> ${data.description}</p>` : ''}
            `);
            
            $('#viewServiceModal').modal('show');
        });
    });

    // Delete request
    $('.btn-delete').click(function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this request?')) {
            $.ajax({
                url: `/faculty/request/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error deleting request');
                }
            });
        }
    });

    // Filter by status
    $('#status-filter').change(function() {
        const status = $(this).val().toLowerCase();
        $('.request-table tbody tr').each(function() {
            const rowStatus = $(this).find('td:eq(3)').text().toLowerCase();
            $(this).toggle(status === '' || rowStatus.includes(status));
        });
    });

    // Search functionality
    $('#search-input').keyup(function() {
        const searchText = $(this).val().toLowerCase();
        $('.request-table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchText));
        });
    });
});
</script>
    <title>My Requests</title>
</head>
<body class="{{ Auth::check() ? 'user-authenticated' : '' }}" data-user-role="{{ Auth::user()->role }}">

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="content">
        <h1>My Request</h1>
        <div class="form-container">
            <div class="dropdown-container">
                <select name="status" id="status">
                    <option value="all">All</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
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
                                @switch($request->service_category)
                                    @case('create')
                                        Create MS Office/TUP Email Account
                                        @break
                                    @case('reset_email_password')
                                        Reset MS Office/TUP Email Password
                                        @break
                                    @case('change_of_data_ms')
                                        Change of Data (MS Office)
                                        @break
                                    @case('reset_tup_web_password')
                                        Reset TUP Web Password
                                        @break
                                    @case('reset_ers_password')
                                        Reset ERS Password
                                        @break
                                    @case('change_of_data_portal')
                                        Change of Data (Portal)
                                        @break
                                    @case('dtr')
                                        Daily Time Record
                                        @break
                                    @case('biometric_record')
                                        Biometric Record
                                        @break
                                    @case('biometrics_enrollement')
                                        Biometrics Enrollment
                                        @break
                                    @case('new_internet')
                                        New Internet Connection
                                        @break
                                    @case('new_telephone')
                                        New Telephone Connection
                                        @break
                                    @case('repair_and_maintenance')
                                        Internet/Telephone Repair and Maintenance
                                        @break
                                    @case('computer_repair_maintenance')
                                        Computer Repair and Maintenance
                                        @break
                                    @case('printer_repair_maintenance')
                                        Printer Repair and Maintenance
                                        @break
                                    @case('request_led_screen')
                                        LED Screen Request
                                        @break
                                    @case('install_application')
                                        Install Application/Information System/Software
                                        @break
                                    @case('post_publication')
                                        Post Publication/Update of Information Website
                                        @break
                                    @case('data_docs_reports')
                                        Data, Documents and Reports
                                        @break
                                    @case('others')
                                        Other Service
                                        @break
                                    @default
                                        {{ $request->service_category }}
                                @endswitch
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
                                        <span class="badge 
                                            @if($request->status == 'Pending') badge-warning
                                            @elseif($request->status == 'In Progress') badge-info
                                            @elseif($request->status == 'Completed') badge-success
                                            @elseif($request->status == 'Rejected') badge-danger
                                            @else badge-secondary
                                            @endif">
                                        {{ $request->status }}
                                            </span>
                                    </td>
                                    <td>
                                    @if($request->status != 'Completed')
                                        <button type="button" class="btn-edit" data-id="{{ $request->id }}">Edit</button>
                                        <button type="button" class="btn-delete" data-id="{{ $request->id }}">Cancel</button>
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
<div class="modal fade" id="requestDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
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
                <button type="button" class="btn btn-primary" onclick="saveEditedService()">Save Changes</button>
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

<!-- Delete Service Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Service</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this service?</p>
                <input type="hidden" id="deleteServiceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteService()">Delete</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>
   <script>
      $(document).ready(function() {
            // Edit Button: Populate Edit Modal
            $('.request-table').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const service = $(this).closest('tr').find('td:nth-child(2)').text();
                
                $('#editServiceId').val(id);
                $('#editServiceName').val(service);
                $('#editServiceDescription').val('');
                $('#editServiceModal').modal('show');
            });

            // View Button: Populate View Modal
            $('.request-table').on('click', '.btn-view', function() {
                const service = $(this).closest('tr').find('td:nth-child(2)').text();
                const status = $(this).closest('tr').find('td:nth-child(4)').text();
                
                $('#viewServiceName').text(service);
                $('#viewServiceStatus').text(status);
                $('#viewServiceModal').modal('show');
            });

            // Delete Button: Populate Delete Modal
            $('.request-table').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                $('#deleteServiceId').val(id);
                $('#deleteServiceModal').modal('show');
            });

            // Save Edited Service (Function Example)
            function saveEditedService() {
                const id = $('#editServiceId').val();
                const serviceName = $('#editServiceName').val();
                const description = $('#editServiceDescription').val();

                // AJAX or form submission logic here
                window.location.href = `/editrequest/${id}?service=${encodeURIComponent(serviceName)}&description=${encodeURIComponent(description)}`;
            }

            // Confirm Delete Service (Function Example)
            function confirmDeleteService() {
                const id = $('#deleteServiceId').val();

                // AJAX or deletion logic here
                window.location.href = `/deleterequest/${id}`;
            }

            // Attach click handlers to modal action buttons
            $('#editServiceModal').on('click', '.btn-primary', saveEditedService);
            $('#deleteServiceModal').on('click', '.btn-danger', confirmDeleteService);
        });
        
        $(document).ready(function() {
    console.log('Document ready - initializing filters');
    
    // Set the dropdown and search input values based on URL parameters
    initializeFiltersFromURL();
    
    // Filter by Status (Dropdown)
    $('#status').on('change', function() {
        console.log('Status changed to:', $(this).val());
        applyFilters();
    });
    
    // Search button click
    $('.search-btn').on('click', function() {
        console.log('Search button clicked');
        applyFilters();
    });
    
    // Enter key in search input
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) {
            console.log('Enter key pressed in search');
            e.preventDefault();
            applyFilters();
        }
    });
    
    // Function to apply filters
    function applyFilters() {
        const selectedStatus = $('#status').val();
        const searchTerm = $('#search-input').val().trim();
        
        console.log('Applying filters:', {
            status: selectedStatus,
            search: searchTerm
        });
        
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
        
        console.log('Navigating to:', url.toString());
        window.location.href = url.toString();
    }
    
    // Function to initialize filters from URL
    function initializeFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Set status dropdown
        const statusParam = urlParams.get('status');
        if (statusParam) {
            console.log('Setting status dropdown to:', statusParam);
            $('#status').val(statusParam);
        }
        
        // Set search input
        const searchParam = urlParams.get('search');
        if (searchParam) {
            console.log('Setting search input to:', searchParam);
            $('#search-input').val(searchParam);
        }
    }
});

    $(document).ready(function() {
        console.log('Enhanced request ID click handler with complete user submission data');
        
        // Click event for request ID
        $(document).on('click', '.clickable-request-id', function() {
            const id = $(this).data('id');
            console.log('Request ID clicked:', id);
            
            // Get the CSRF token
            const token = $('meta[name="csrf-token"]').attr('content');
            
            // Determine the current user role from the body data attribute
            const userRole = $('body').data('user-role') || '';
            console.log('User role:', userRole);
            
            // Set the correct endpoint based on role
            let requestUrl;
            if (userRole === 'Student') {
                requestUrl = `/student/request/${id}`;
            } else {
                requestUrl = `/faculty/request/${id}`;
            }
            
            console.log('Fetching details from:', requestUrl);
            
            // Fetch request details
            $.ajax({
                url: requestUrl,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                success: function(response) {
                    console.log('Response received:', response);
                    
                    // Check if response contains an error
                    if (response.error) {
                        console.error('Error in response:', response.error);
                        alert('Error: ' + response.error);
                        return;
                    }
                    
                    // Format request ID based on role
                    const requestPrefix = userRole === 'Student' ? 'SSR-' : 'FSR-';
                    const dateString = new Date(response.created_at)
                        .toISOString()
                        .slice(0, 10)
                        .replace(/-/g, '');
                    const formattedId = String(response.id).padStart(4, '0');
                    const displayId = `${requestPrefix}${dateString}-${formattedId}`;
                    
                    // Update the modal
                    $('#detailsRequestId').text(displayId);
                    
                    // Format service name based on service_category
                    const serviceName = formatServiceCategory(response.service_category);
                    $('#detailsService').text(serviceName);
                    
                    // Update status with badge
                    const statusBadge = $('#detailsStatus');
                    statusBadge.text(response.status);
                    statusBadge.removeClass().addClass('badge');
                    
                    if (response.status === 'Pending') {
                        statusBadge.addClass('badge-warning');
                    } else if (response.status === 'In Progress') {
                        statusBadge.addClass('badge-info');
                    } else if (response.status === 'Completed') {
                        statusBadge.addClass('badge-success');
                    } else if (response.status === 'Rejected') {
                        statusBadge.addClass('badge-danger');
                    } else {
                        statusBadge.addClass('badge-secondary');
                    }
                    
                    // Format dates
                    const submittedDate = new Date(response.created_at).toLocaleString();
                    const completedDate = response.status === 'Completed' && response.updated_at 
                        ? new Date(response.updated_at).toLocaleString() 
                        : '-';
                    
                    $('#detailsSubmitted').text(submittedDate);
                    $('#detailsCompleted').text(completedDate);
                    
                    // Build detailed request information with ALL submitted fields
                    let infoHtml = buildDetailedRequestInfo(response, userRole);
                    
                    // Update the information section
                    $('#detailsInformation').html(infoHtml);
                    
                    // Assignment information (show if in progress or completed)
                    if (response.status === 'In Progress' || response.status === 'Completed') {
                        $('#assignmentSection').show();
                        
                        // Check if assigned_uitc_staff exists and has data
                        console.log('Assigned staff data:', response.assigned_uitc_staff);
                        
                        // Properly handle the assigned staff name
                        let staffName = '-';
                        
                        if (response.assigned_uitc_staff && response.assigned_uitc_staff.name) {
                            // If the response has nested assigned_uitc_staff object with name
                            staffName = response.assigned_uitc_staff.name;
                        } else if (response.assigned_uitc_staff_id) {
                            // We have an ID but not the name - could display ID or fetch name separately
                            staffName = `Staff ID: ${response.assigned_uitc_staff_id}`;
                        }
                        
                        $('#detailsAssignedTo').text(staffName);
                        $('#detailsTransactionType').text(response.transaction_type || '-');
                        $('#detailsAdminNotes').text(response.admin_notes || 'No notes');
                    } else {
                        $('#assignmentSection').hide();
                    }
                    
                    // Rejection information (show if rejected)
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
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status code:', xhr.status);
                    
                    let errorMessage = 'Could not load request details. ';
                    
                    // Add more specific error details based on status code
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
        
        // Function to build detailed request information based on service category
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
                
            case 'dtr':
                if (response.dtr_months) {
                    infoHtml += `<p><strong>DTR Months:</strong> ${response.dtr_months}</p>`;
                }
                if (response.dtr_with_details !== undefined) {
                    infoHtml += `<p><strong>Include In/Out Details:</strong> ${response.dtr_with_details ? 'Yes' : 'No'}</p>`;
                }
                break;
                
            case 'biometrics_enrollement':
                // Add specific biometrics enrollment fields - FIXED VERSION
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
            case 'repair_and_maintenance':
            case 'computer_repair_maintenance':
            case 'printer_repair_maintenance':
                if (response.location) {
                    infoHtml += `<p><strong>Location:</strong> ${response.location}</p>`;
                }
                if (response.problem_encountered) {
                    infoHtml += `<p><strong>Problems Encountered:</strong> ${response.problem_encountered}</p>`;
                }
                break;
                
            case 'request_led_screen':
                if (response.preferred_date) {
                    infoHtml += `<p><strong>Preferred Date:</strong> ${response.preferred_date}</p>`;
                }
                if (response.preferred_time) {
                    infoHtml += `<p><strong>Preferred Time:</strong> ${response.preferred_time}</p>`;
                }
                if (response.led_screen_details) {
                    infoHtml += `<p><strong>Additional Details:</strong> ${response.led_screen_details}</p>`;
                }
                break;
                
            case 'install_application':
                if (response.application_name) {
                    infoHtml += `<p><strong>Application Name:</strong> ${response.application_name}</p>`;
                }
                if (response.installation_purpose) {
                    infoHtml += `<p><strong>Purpose of Installation:</strong> ${response.installation_purpose}</p>`;
                }
                if (response.installation_notes) {
                    infoHtml += `<p><strong>Additional Requirements:</strong> ${response.installation_notes}</p>`;
                }
                break;
                
            case 'post_publication':
                if (response.publication_author) {
                    infoHtml += `<p><strong>Author:</strong> ${response.publication_author}</p>`;
                }
                if (response.publication_editor) {
                    infoHtml += `<p><strong>Editor:</strong> ${response.publication_editor}</p>`;
                }
                if (response.publication_start_date) {
                    infoHtml += `<p><strong>Date of Publication:</strong> ${response.publication_start_date}</p>`;
                }
                if (response.publication_end_date) {
                    infoHtml += `<p><strong>End of Publication:</strong> ${response.publication_end_date}</p>`;
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
        
        // Add any other fields in the response that might be relevant
        // This ensures we don't miss any fields that are present but not explicitly handled above
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
                            'dtr_months', 'dtr_with_details'];
        
            for (const key in response) {
                // Skip if this is a common field we've already handled or if the value is null/undefined/object
                if (commonFields.includes(key) || response[key] === null || response[key] === undefined || typeof response[key] === 'object') {
                    continue;
                }
                
                // Format the field name for display (capitalize and add spaces)
                const fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                // Add the field to the HTML
                infoHtml += `<p><strong>${fieldName}:</strong> ${response[key]}</p>`;
            }
            
            return infoHtml;
        }
            
            // Helper function to format service category
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
        });
    </script>
</body>
</html>