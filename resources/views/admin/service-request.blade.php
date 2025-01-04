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

    <!-- Assign Technician Modal -->
    <div class="modal fade" id="assignTechnicianModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Technician</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignTechnicianForm">
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
                            <label>Select Technician</label>
                            <select class="form-control" name="technician_id" required>
                                <option value="">Choose Technician</option>
                                <!--{{-- Populate technicians dynamically --}}
                                {{-- @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                @endforeach --}} -->
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
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes for the technician"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveAssignTechnician()">Assign Technician</button>
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
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
        document.querySelectorAll('.btn-approve').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the form from submitting
                
                var row = this.closest('tr');
                var requestId = row.querySelector('td:nth-child(2)').textContent;
                var services = row.querySelector('td:nth-child(4)').textContent;
                
                // Populate modal with request details
                document.getElementById('modalRequestId').textContent = requestId;
                document.getElementById('modalRequestServices').textContent = services;
                
                // Show the modal
                $('#assignTechnicianModal').modal('show');
            });
        });
        
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