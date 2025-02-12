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
                            <th>Request Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td><input type="checkbox" name="selected_requests[]" value="{{ $request['id'] }}"></td>
                                <td>{{ $request['id'] }}</td>
                                <td>{!! $request['request_data'] !!}</td>
                                <td>{{ $request['role'] }}</td>
                                <td>
                                    <strong>Date: </strong><span>{{ $request['date']->format('Y-m-d') }}</span><br>
                                    <strong>Time: </strong><span>{{ $request['date']->format('g:i A') }}</span>
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
        </div>
    </div>

    <!-- Assign UITC Staff Modal -->
    <div class="modal fade" id="assignStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign UITC Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignStaffForm">
                        @csrf
                        <input type="hidden" id="requestIdInput" name="request_id">
                        <input type="hidden" id="requestTypeInput" name="request_type">
                        
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRequestId"></span></p>
                                <div id="modalRequestServices"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Select UITC Staff</label>
                            <select class="form-control" name="uitcstaff_id" required>
                                <option value="">Choose UITC Staff</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select class="form-control" name="transaction_type" required>
                                <option value="">Select Transaction Type</option>
                                <option value="simple">Simple Transaction</option>
                                <option value="complex">Complex Transaction</option>
                                <option value="highly_technical">Highly Technical Transaction</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes for the UITC Staff"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAssignStaffBtn">Assign UITC Staff</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Service Request Modal -->
    <div class="modal fade" id="rejectServiceRequestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Service Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rejectServiceRequestForm">
                        <input type="hidden" name="request_id">
                        <input type="hidden" name="request_type">
                        
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRejectRequestId"></span></p>
                                <div id="modalRejectRequestServices"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Reason for Rejection <span class="text-danger">*</span></label>
                            <select class="form-control" id="rejectionReason" name="rejection_reason" required>
                                <option value="">Select Rejection Reason</option>
                                <option value="incomplete_information">Incomplete Information</option>
                                <option value="out_of_scope">Service Out of Scope</option>
                                <option value="resource_unavailable">Resources Unavailable</option>
                                <option value="duplicate_request">Duplicate Request</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea class="form-control" id="rejectionNotes" name="notes" rows="4" placeholder="Provide additional details about the rejection (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Function to fetch and populate available UITC Staff
            function fetchAvailableUITCStaff() {
                return $.ajax({
                    url: '{{ route("get.available.technicians") }}',
                    method: 'GET',
                    success: function(uitcStaff) {
                        const uitcStaffSelect = $('select[name="uitcstaff_id"]');
                        uitcStaffSelect.empty();
                        uitcStaffSelect.append('<option value="">Choose Available UITC Staff</option>');
                        
                        if (uitcStaff.length === 0) {
                            uitcStaffSelect.append('<option value="">No available UITC Staff</option>');
                        } else {
                            uitcStaff.forEach(function(staff) {
                                uitcStaffSelect.append(
                                    `<option value="${staff.id}">${staff.name}</option>`
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to fetch available UITC Staff:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch available UITC Staff',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            // Handle Approve Button Click
            $(document).on('click', '.btn-approve', function(e) {
                e.preventDefault();
                const requestId = $(this).data('id');
                const requestType = $(this).data('type');
                const requestDetails = $(this).data('details');

                // Reset form
                $('#assignStaffForm')[0].reset();
                
                // Set modal values
                $('#requestIdInput').val(requestId);
                $('#requestTypeInput').val(requestType);
                $('#modalRequestId').text(requestId);
                $('#modalRequestServices').html(requestDetails);

                // Fetch available staff and show modal
                fetchAvailableUITCStaff().then(() => {
                    $('#assignStaffModal').modal('show');
                });
            });

            // Handle Save Assign Staff
            $('#saveAssignStaffBtn').on('click', function() {
                const formData = {
                    request_id: $('#requestIdInput').val(),
                    request_type: $('#requestTypeInput').val(),
                    uitcstaff_id: $('select[name="uitcstaff_id"]').val(),
                    transaction_type: $('select[name="transaction_type"]').val(),
                    notes: $('textarea[name="notes"]').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                if (!formData.uitcstaff_id || !formData.transaction_type) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Fields Missing',
                        text: 'Please select both UITC Staff and Transaction Type',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.assign.uitc.staff") }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'UITC Staff assigned successfully',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#assignStaffModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to assign UITC staff',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Assignment error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while assigning UITC staff',
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
    </script>
</body>
</html>