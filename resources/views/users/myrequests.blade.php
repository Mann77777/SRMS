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
        $('#viewServiceCompletedDate').text(data.status == 'Completed' ? new Date(data.updated_at).toLocaleString() : 'N/A')
            
            // Add more fields as needed
            const modalBody = $('#viewServiceModal .modal-body');
            modalBody.html(`
                <p><strong>Request ID:</strong> ${data.id}</p>
                <p><strong>Service:</strong> ${data.service_category}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>First Name:</strong> ${data.first_name}</p>
                <p><strong>Last Name:</strong> ${data.last_name}</p>
                <p><strong>Date Submitted:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                <p><strong>Date Completed:</strong> ${data.status == 'Completed' ? new Date(data.updated_at).toLocaleString() : 'N/A'}</p>
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
<body>

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
                                @if(Auth::user()->role == "Student")
                                    {{ 'SSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                @else
                                    {{ 'FSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                @endif
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
                                        {{ $request->description ?? 'Other Service' }}
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
                                    <button type="button" class="btn-view" data-id="{{ $request->id }}">View</button>
                                    @if($request->status != 'Completed')
                                        <button type="button" class="btn-edit" data-id="{{ $request->id }}">Edit</button>
                                        <button type="button" class="btn-delete" data-id="{{ $request->id }}">Delete</button>
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
    </script>
</body>
</html>