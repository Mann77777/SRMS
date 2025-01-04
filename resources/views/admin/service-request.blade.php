<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
        
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Service Request</h1>
        <div class="dropdown-container">
            <select id="status" name="status_id">
                <option value="all">All Status</option>
                <option value="peding">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <div class="requests-btn">
                <button type="button" class="delete-button" id="delete-btn">
                    <i class="fas fa-trash"></i>  Delete Selected
                </button>
             <!--   <button type="button" class="cancel-btn" id="cancel-btn">Cancel</button>
                <button type="button" class="confirm-btn" id="confirm-btn">Confirm</button> -->
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
                            <th>Request Data</th>
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
                                <td>
                                      {!!  $request['request_data'] !!}
                                </td>
                                <td>
                                    {{ $request['role'] }}
                                </td>
                                <td>
                                    <strong>Date: </strong><span>{{ $request['date']->format('Y-m-d') }}</span><br>
                                    <strong>Time: </strong><span>{{ $request['date']->format('g:i A') }}</span>
                                </td>
                                 <td>
                                    <span class="badge 
                                        @if($request['status'] == 'Pending') badge-warning
                                        @elseif($request['status'] == 'Approved') badge-success
                                        @elseif($request['status'] == 'Rejected') badge-danger
                                        @else badge-secondary
                                        @endif">
                                    {{ $request['status'] }}
                                    </span>
                                 </td>
                                 <td class="btns"> 
                                  <button class="btn-approve" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}">Approve</button>
                                  <button class="btn-reject" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}">Reject</button>
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
                        
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRequestId"></span></p>
                                <p><strong>Services:</strong> <span id="modalRequestServices"></span></p>
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
                    <button type="button" class="btn btn-primary" onclick="saveAssignStaff()">Assign UITC Staff</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Service Request Modal -->
    <div class="modal fade" id="rejectServiceRequestModal" tabindex="-1" role="dialog" aria-labelledby="rejectServiceRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectServiceRequestModalLabel">Reject Service Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rejectServiceRequestForm">
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRejectRequestId"></span></p>
                                <p><strong>Services:</strong> <span id="modalRejectRequestServices"></span></p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="rejectionReason">Reason for Rejection <span class="text-danger">*</span></label>
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
                            <label for="rejectionNotes">Additional Notes</label>
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

    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
   // Function to fetch and populate available UITC Staff
   function fetchAvailableUITCStaff() {
        return $.ajax({
            url: '{{ route("get.available.technicians") }}',
            method: 'GET',
            success: function(uitcStaff) {
                const uitcStaffSelect = $('select[name="uitcstaff_id"]');
                uitcStaffSelect.empty();
                uitcStaffSelect.append('<option value="">Choose Available UITC Staff</option>');
                
                // Only append available UITC Staff
                if (uitcStaff.length === 0) {
                    uitcStaffSelect.append('<option value="">No available UITC Staff</option>');
                } else {
                    uitcStaff.forEach(function(staff) {
                        uitcStaffSelect.append(
                            `<option value="${staff.id}">
                                ${staff.name}
                            </option>`
                        );
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch available UITC Staff:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: 'Failed to fetch available UITC Staff. ' + error,
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Event listener for approve button to trigger UITC Staff selection
    $('.btn-approve').on('click', function(e) {
        e.preventDefault(); // Prevent any default action
        
        const requestId = $(this).data('id');
        const services = $(this).closest('tr').find('.service-details').text().trim();
        
        // Populate modal details
        $('#requestIdInput').val(requestId);
        $('#modalRequestId').text(requestId);
        $('#modalRequestServices').text(services);
        
        // Fetch and populate available UITC Staff
        fetchAvailableUITCStaff().always(function() {
            // Ensure modal is shown after AJAX call completes
            $('#assignStaffModal').modal('show');
        });
    });

      // Function to save UITC Staff assignment
      function saveAssignStaff() {
        const formData = $('#assignStaffForm').serialize();
        
        $.ajax({
            url: '{{ route("admin.assign.uitc.staff") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'UITC Staff Assigned',
                        text: 'UITC Staff assigned successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#assignStaffModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Assignment Failed',
                        text: response.message,
                        confirmButtonText: 'Try Again'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error assigning UITC Staff. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
        
    document.querySelectorAll('.btn-reject').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the form from submitting
                var row = this.closest('tr');
                var requestId = row.querySelector('td:nth-child(2)').textContent.trim();
                var services = row.querySelector('td:nth-child(4)').textContent.trim();
                
                document.getElementById('modalRejectRequestId').textContent = requestId;
                document.getElementById('modalRejectRequestServices').textContent = services;
                
                $('#rejectServiceRequestModal').modal('show');
            });
        });

    </script>
</body>
</html>