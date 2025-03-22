<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin_servicerequest.css') }}" rel="stylesheet">

    <title>Admin - Service Request</title>
</head>
<body>
    @include('layouts.admin-navbar')
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Service Request</h1>
        <div class="dropdown-container">
            <select id="status" name="status_id">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <div class="requests-btn">
                <button type="button" class="delete-button" id="delete-btn">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>

        <div class="request-table-container">
            <h4>Request List</h4>
            <form action="" id="delete-form">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th class="left"><input type="checkbox" id="select-all"></th>
                            <th>Request ID</th>
                            <th>Request Details</th>
                            <th>Role</th>
                            <th>Date & Time Submitted</th>
                            <th>Date & Time Completed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td><input type="checkbox" name="selected_requests[]" value="{{ $request['id'] }}"></td>
                                <td>
                                    <span class="clickable-request-id" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                        {{ $request['id'] }}
                                    </span>
                                </td>
                                <td>{!! $request['request_data'] !!}</td>
                                <td>{{ $request['role'] }}</td>
                                <td>
                                    <span>{{ \Carbon\Carbon::parse($request['date'])->format('M d, Y') }}</span><br>
                                    <span>{{ \Carbon\Carbon::parse($request['date'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @if($request['status'] == 'Completed' && isset($request['updated_at']))
                                        <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('M d, Y') }}</span><br>
                                        <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('h:i A') }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($request['status'] == 'Pending') badge-warning
                                        @elseif($request['status'] == 'In Progress') badge-info
                                        @elseif($request['status'] == 'Completed') badge-success
                                        @elseif($request['status'] == 'Approved') badge-success
                                        @elseif($request['status'] == 'Rejected') badge-danger
                                        @else badge-secondary
                                        @endif">
                                        {{ $request['status'] }}
                                    </span>
                                </td>
                               
                                <td class="btns">
                                    @if($request['status'] == 'Pending')
                                        <button type="button" class="btn-approve" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                                            Approve
                                        </button>
                                        <button type="button" class="btn-reject" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                                            Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-inbox fa-3x"></i>
                                    <p>No requests found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
            <div class="pagination-container">
                {{ $requests->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

</div>
    


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @include('admin.modal.servicerequest-modal')


    <script>
        $(document).ready(function() {
            // Function to fetch and populate available UITC Staff
            function fetchUITCStaff() {
                fetch('/get-uitc-staff')
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                        const staffSelect = $('select[name="uitcstaff_id"]');
                        staffSelect.empty();
                        staffSelect.append('<option value="">Choose UITC Staff</option>');
                        
                        data.staff.forEach(staff => {
                            staffSelect.append(
                                `<option value="${staff.id}">${staff.name || staff.username}</option>`
                            );
                        });
                    } else {
                        console.error('Failed to fetch UITC Staff');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch available UITC Staff',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch UITC Staff',
                        confirmButtonText: 'OK'
                    });
                });
            }

            // Approve button click event
            $('.btn-approve').on('click', function() {
                const requestId = $(this).data('id');
                const requestType = $(this).data('type');
                const requestDetails = $(this).data('details');

                console.log('Approve Button Clicked');
                console.log('Button Data - Request ID:', requestId);
                console.log('Button Data - Request Type:', requestType);
                console.log('Button Data - Request Details:', requestDetails);

                // Reset form
                $('#assignStaffForm')[0].reset();
                
                // Set modal values
                $('#requestIdInput').val(requestId);
                $('#requestTypeInput').val(requestType);
                $('#modalRequestId').text(requestId);
                $('#modalRequestServices').html(requestDetails);

                // Verify values are set correctly
                console.log('Modal Input - Request ID:', $('#requestIdInput').val());
                console.log('Modal Input - Request Type:', $('#requestTypeInput').val());

                // Fetch and populate UITC Staff dropdown
                fetchUITCStaff();

                // Show the modal
                $('#assignStaffModal').modal('show');
            });


            // Handle Save Assign Staff
            $('#saveAssignStaffBtn').on('click', function() {
                // Get hidden input values
                const requestId = $('#requestIdInput').val();
                const requestType = $('#requestTypeInput').val();
                const uitcStaffId = $('select[name="uitcstaff_id"]').val();
                const transactionType = $('select[name="transaction_type"]').val();
                const notes = $('textarea[name="notes"]').val();

                // EXTREMELY DETAILED console logging
                console.log('SAVE BUTTON CLICKED - FULL DEBUG:');
                console.log('Hidden Inputs:');
                console.log('Request ID Input Element:', $('#requestIdInput'));
                console.log('Request Type Input Element:', $('#requestTypeInput'));
                
                console.log('Extracted Values:');
                console.log('Request ID:', requestId);
                console.log('Request Type:', requestType);
                console.log('UITC Staff ID:', uitcStaffId);
                console.log('Transaction Type:', transactionType);
                console.log('Notes:', notes);

                // Validate required fields with more detailed error messages
                let errorMessage = '';
                if (!requestId) {
                    errorMessage += 'Request ID is missing. ';
                    console.error('REQUEST ID IS EMPTY OR UNDEFINED');
                }
                if (!requestType) {
                    errorMessage += 'Request Type is missing. ';
                    console.error('REQUEST TYPE IS EMPTY OR UNDEFINED');
                }
                if (!uitcStaffId) {
                    errorMessage += 'UITC Staff is not selected. ';
                    console.error('UITC STAFF ID IS EMPTY OR UNDEFINED');
                }
                if (!transactionType) {
                    errorMessage += 'Transaction Type is not selected. ';
                    console.error('TRANSACTION TYPE IS EMPTY OR UNDEFINED');
                }

                if (errorMessage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: `Please fill in all required fields:<br>${errorMessage}`,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Prepare form data manually to ensure all fields are included
                const formData = {
                    request_id: requestId,
                    request_type: requestType,
                    uitcstaff_id: uitcStaffId,
                    transaction_type: transactionType,
                    notes: notes,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                console.log('Final Form Data:', formData);

                // Send AJAX request to assign staff
                $.ajax({
                    url: '/assign-uitc-staff',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'UITC Staff assigned successfully',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#assignStaffModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Full error response:', xhr);
                        console.error('Response JSON:', xhr.responseJSON);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Assignment Error',
                            text: xhr.responseJSON?.message || 'Failed to assign UITC Staff',
                            confirmButtonText: 'OK'
                        }); 
                    }
                });
            });
            // Handle Reject Button Click
            $(document).on('click', '.btn-reject', function(e) {
                e.preventDefault();
                const requestId = $(this).data('id');
                const requestType = $(this).data('type');
                const requestDetails = $(this).data('details');

                // Reset form
                $('#rejectServiceRequestForm')[0].reset();

                // Set form values
                $('#rejectServiceRequestForm input[name="request_id"]').val(requestId);
                $('#rejectServiceRequestForm input[name="request_type"]').val(requestType);
                $('#modalRejectRequestId').text(requestId);
                $('#modalRejectRequestServices').html(requestDetails);

                $('#rejectServiceRequestModal').modal('show');
            });
            // Handle Reject Confirmation
            $('#confirmRejectBtn').on('click', function() {
                const formData = {
                    request_id: $('#rejectServiceRequestForm input[name="request_id"]').val(),
                    request_type: $('#rejectServiceRequestForm input[name="request_type"]').val(),
                    rejection_reason: $('#rejectionReason').val(),
                    notes: $('#rejectionNotes').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                if (!formData.rejection_reason) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Field Missing',
                        text: 'Please select a rejection reason',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.reject.service.request") }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Request for Rejection Submitted',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#rejectServiceRequestModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to reject request',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Rejection error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while rejecting the request',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Handle Status Filter
            $('#status').on('change', function() {
                const selectedStatus = $(this).val().toLowerCase();
                const rows = $('.request-table tbody tr:not(.empty-state)');

                if (selectedStatus === 'all') {
                    rows.show();
                } else {
                    rows.each(function() {
                        const statusText = $(this).find('td:nth-child(6) .badge').text().trim().toLowerCase();
                        $(this).toggle(statusText === selectedStatus);
                    });
                }

                // Update empty state visibility
                const visibleRows = rows.filter(':visible');
                const emptyStateRow = $('.empty-state').closest('tr');
                
                if (visibleRows.length === 0) {
                    if (emptyStateRow.length === 0) {
                        $('.request-table tbody').append(`
                            <tr class="empty-state-row">
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-inbox fa-3x"></i>
                                    <p>No requests found with status: ${selectedStatus}</p>
                                </td>
                            </tr>
                        `);
                    } else {
                        emptyStateRow.show();
                    }
                } else {
                    $('.empty-state-row').remove();
                }
            });

            // Handle Delete Selected
            $('#delete-btn').on('click', function() {
                const selectedRequests = $('input[name="selected_requests[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedRequests.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Requests Selected',
                        text: 'Please select at least one request to delete',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Deletion',
                    text: `Are you sure you want to delete ${selectedRequests.length} selected request(s)?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("admin.delete.service.requests") }}',
                            method: 'POST',
                            data: {
                                request_ids: selectedRequests,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted Successfully',
                                        text: 'The selected requests have been deleted',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Delete Failed',
                                        text: response.message || 'Failed to delete selected requests',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error('Delete error:', xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the requests',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });

            // Handle Select All Checkbox
            $('#select-all').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('input[name="selected_requests[]"]').prop('checked', isChecked);
            });

            // Update Select All state when individual checkboxes change
            $(document).on('change', 'input[name="selected_requests[]"]', function() {
                const totalCheckboxes = $('input[name="selected_requests[]"]').length;
                const checkedCheckboxes = $('input[name="selected_requests[]"]:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });
        });


    // Extract service information from the request details HTML
    function extractServiceFromDetails(detailsHtml) {
        // Create a temporary DOM element to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = detailsHtml;
        
        // Look for service information
        const serviceElements = tempDiv.querySelectorAll('strong');
        let serviceCategory = '';
        let serviceDescription = '';
        
        // Search for service name in the HTML
        for (let i = 0; i < serviceElements.length; i++) {
            if (serviceElements[i].textContent === 'Service:') {
                // Get the text after this strong element
                let nextNode = serviceElements[i].nextSibling;
                if (nextNode) {
                    serviceCategory = nextNode.textContent.trim();
                }
            }
            
            if (serviceElements[i].textContent === 'Description:') {
                // Get the text after this strong element
                let nextNode = serviceElements[i].nextSibling;
                if (nextNode) {
                    serviceDescription = nextNode.textContent.trim();
                }
            }
        }
        
        return {
            category: serviceCategory,
            description: serviceDescription
        };
    }

    // Update the details modal with request information
    function updateRequestDetailsModal(requestData) {
        // Set basic request information
        $('#detailsRequestId').text(requestData.id);
        $('#detailsRequestRole').text(requestData.role);
        $('#detailsRequestDate').text(moment(requestData.date).format('MMM D, YYYY h:mm A'));
        
        // Format the data in request_data field
        $('#detailsRequestData').html(requestData.request_data);
    
        // Set status with appropriate color
        // Set status with appropriate color
        const $statusBadge = $('#detailsRequestStatus');
        // Trim and normalize the status text
        const statusText = requestData.status.trim().toLowerCase();
        $statusBadge.text(requestData.status);
        $statusBadge.removeClass().addClass('badge');

        // Match the status badge styling with the table using more flexible matching
        if (statusText.includes('pending')) {
            $statusBadge.addClass('badge-warning');
        } else if (statusText.includes('in progress')) {
            $statusBadge.addClass('badge-info');
        } else if (statusText.includes('completed') || statusText.includes('approved')) {
            $statusBadge.addClass('badge-success');
        } else if (statusText.includes('rejected')) {
            $statusBadge.addClass('badge-danger');
        } else {
            $statusBadge.addClass('badge-secondary');
        }
            // Handle completed date
            if (requestData.status === 'Completed' && requestData.updated_at) {
                $('#detailsRequestCompleted').text(moment(requestData.updated_at).format('MMM D, YYYY h:mm A'));
            } else {
                $('#detailsRequestCompleted').text('N/A');
            }
            
            // Show/hide action buttons based on status
            if (requestData.status === 'Pending') {
                $('#pendingActionsContainer').show();
                
                // Set up modal action buttons
                $('.modal-approve-btn').data('id', requestData.id);
                $('.modal-approve-btn').data('type', requestData.type);
                $('.modal-approve-btn').data('details', requestData.request_data);
                
                $('.modal-reject-btn').data('id', requestData.id);
                $('.modal-reject-btn').data('type', requestData.type);
                $('.modal-reject-btn').data('details', requestData.request_data);
            } else {
                $('#pendingActionsContainer').hide();
            }
            
            // Show assignment information if available
            if (requestData.assigned_uitc_staff) {
                $('#assignmentInfoSection').show();
                $('#detailsAssignedTo').text(requestData.assigned_uitc_staff);
                $('#detailsTransactionType').text(requestData.transaction_type || '-');
                $('#detailsAdminNotes').text(requestData.admin_notes || 'No notes');
            } else {
                $('#assignmentInfoSection').hide();
            }
            
            // Show rejection information if rejected
            if (requestData.status === 'Rejected') {
                $('#rejectionInfoSection').show();
                $('#detailsRejectionReason').text(requestData.rejection_reason || '-');
                $('#detailsRejectionNotes').text(requestData.notes || 'No notes');
                $('#detailsRejectedDate').text(requestData.updated_at ? 
                    moment(requestData.updated_at).format('MMM D, YYYY h:mm A') : '-');
            } else {
                $('#rejectionInfoSection').hide();
            }
        }

    // Document ready function for request detail modal
    $(document).on('click', '.clickable-request-id', function() {
    const row = $(this).closest('tr');
    const requestId = $(this).text();
    
    // Extract data from the current row
    const statusText = row.find('td:eq(6) .badge').text().trim();
    
    // Get completed date differently based on the cell content
    let completedDate = null;
    const completedDateCell = row.find('td:eq(5)');
    
    // Check if the status is completed and the cell doesn't just contain "N/A"
    if (statusText === 'Completed' && !completedDateCell.text().trim().includes('N/A')) {
        // Get both date and time spans if they exist
        if (completedDateCell.find('span').length > 0) {
            const completedDateValue = completedDateCell.find('span:first').text();
            const completedTimeValue = completedDateCell.find('span:last').text();
            completedDate = completedDateValue + ' ' + completedTimeValue;
        } else {
            // Handle case where there might just be text without spans
            completedDate = completedDateCell.text().trim();
        }
    }
    
    // Build the request data object
    const requestData = {
        id: requestId,
        role: row.find('td:eq(3)').text(),
        request_data: row.find('td:eq(2)').html(),
        date: row.find('td:eq(4)').find('span:first').text() + ' ' + row.find('td:eq(4)').find('span:last').text(),
        status: statusText,
        updated_at: completedDate
    };
    
    // Update and show the modal
    updateRequestDetailsModal(requestData);
    $('#requestDetailsModal').modal('show');
});
    </script>
</body>
</html>