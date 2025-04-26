<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="{{ asset('css/assign-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Assigned Request</title>
</head>
<body data-user-role="UITC Staff">
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Assigned Requests</h1>

        <div class="dropdown-container">
            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="user-search" name="user-search" placeholder="Search users...">
                    <i class="fas fa-search search-icon"></i>
                </div>            
            </div>

            <!-- Status Filter -->
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <!-- Transaction Filter -->
            <select name="transaction_type" id="transaction_type">
                <option value="all">All Transaction</option>
                <option value="simple">Simple Transaction</option>
                <option value="complex">Complex Transaction</option>
                <option value="highly technical">Highly Technical Transaction</option>
            </select>
        </div>

        <div class="assignreq-table-container">
            <h4>Assigned Request List</h4>
            <div class="assignreq-table-wrapper">
                <table class="assignreq-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Details</th>
                            <th>Role</th>
                            <th>Date & Time Submitted</th>
                            <th>Date & Time Completed</th>
                            <th>Transaction Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedRequests as $request)
                        <tr>
                            <td>
                                <span class="clickable-request-id" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                    {{ $request->id }}
                                </span>
                            </td>
                            <td>
                                @if(isset($request->request_data))
                                    {!! $request->request_data !!}
                                @else
                                    {!! 
                                        '<strong>Name:</strong> ' . ($request->first_name && $request->last_name ? 
                                            $request->first_name . ' ' . $request->last_name : 
                                            ($request->requester_name ?? 'N/A')) . '<br>' .
                                        
                                        ($request->request_type == 'student' && isset($request->student_id) ? 
                                            '<strong>Student ID:</strong> ' . $request->student_id . '<br>' : 
                                            ($request->request_type == 'faculty' && isset($request->faculty_id) ? 
                                                '<strong>Faculty ID:</strong> ' . $request->faculty_id . '<br>' : '')) .
                                        
                                        '<strong>Service:</strong> ' . 
                                        (function($category) {
                                            switch($category) {
                                                case 'create': return 'Create MS Office/TUP Email Account';
                                                case 'reset_email_password': return 'Reset MS Office/TUP Email Password';
                                                case 'change_of_data_ms': return 'Change of Data (MS Office)';
                                                case 'reset_tup_web_password': return 'Reset TUP Web Password';
                                                case 'reset_ers_password': return 'Reset ERS Password';
                                                case 'change_of_data_portal': return 'Change of Data (Portal)';
                                                case 'dtr': return 'Daily Time Record';
                                                case 'biometric_record': return 'Biometric Record';
                                                case 'biometrics_enrollement': return 'Biometrics Enrollment';
                                                case 'new_internet': return 'New Internet Connection';
                                                case 'new_telephone': return 'New Telephone Connection';
                                                case 'repair_and_maintenance': return 'Internet/Telephone Repair and Maintenance';
                                                case 'computer_repair_maintenance': return 'Computer Repair and Maintenance';
                                                case 'printer_repair_maintenance': return 'Printer Repair and Maintenance';
                                                case 'request_led_screen': return 'LED Screen Request';
                                                case 'install_application': return 'Install Application/Information System/Software';
                                                case 'post_publication': return 'Post Publication/Update of Information Website';
                                                case 'data_docs_reports': return 'Data, Documents and Reports';
                                                case 'others': return isset($request->description) && $request->description ? $request->description : 'Other Service';
                                                default: return $category;
                                            }
                                        })($request->service_category) . 
                                        
                                        (isset($request->description) && $request->service_category != 'others' ? 
                                            '<br><strong>Description:</strong> ' . $request->description : '')
                                    !!}
                                @endif
                            </td>
                            <td>{{ $request->user_role ?? ($request->request_type == 'faculty' ? 'Faculty & Staff' : 'Student') }}</td>
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
                                @if(isset($request->transaction_type))
                                    @switch(strtolower($request->transaction_type))
                                        @case('simple')
                                            <span class="transaction-badge transaction-simple">Simple</span>
                                            @break
                                        @case('complex')
                                            <span class="transaction-badge transaction-complex">Complex</span>
                                            @break
                                        @case('highly technical')
                                            <span class="transaction-badge transaction-technical">Highly Technical</span>
                                            @break
                                        @default
                                            <span class="transaction-badge">{{ ucfirst($request->transaction_type) }}</span>
                                    @endswitch
                                @else
                                    <span class="transaction-badge transaction-simple">Simple</span>
                                @endif
                        </td>
                            <td>
                                @if($request->status == 'Pending')
                                    <span class="custom-badge custom-badge-warning">{{ $request->status }}</span>
                                @elseif($request->status == 'In Progress')
                                    <span class="custom-badge custom-badge-info">{{ $request->status }}</span>
                                @elseif($request->status == 'Completed')
                                    <span class="custom-badge custom-badge-success">{{ $request->status }}</span>
                                @elseif($request->status == 'Cancelled' || $request->status == 'Rejected')
                                    <span class="custom-badge custom-badge-danger">{{ $request->status }}</span>
                                @else
                                    <span class="custom-badge custom-badge-secondary">{{ $request->status }}</span>
                                @endif
                            </td>
                            <td class="btns">
                                @if($request->status != 'Completed' && $request->status != 'Cancelled' && $request->status != 'Rejected')
                                    <button class="btn-complete" data-request-id="{{ $request->id }}" data-request-type="{{ $request->request_type }}">
                                        Complete
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No assigned requests found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-container">
                    {{ $assignedRequests->links('vendor.pagination.custom') }}
                </div>
            </div> 
        </div>
    </div>

    <!-- Complete Request Modal -->
    <div class="modal fade" id="completeRequestModal" tabindex="-1" role="dialog" aria-labelledby="completeRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeRequestModalLabel">Complete Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="completeRequestForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="completeRequestId" name="request_id">
                        <input type="hidden" id="completeRequestType" name="request_type" value="">
                        
                        <div class="form-group">
                            <label for="completionReport">Completion Report <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control" 
                                id="completionReport" 
                                name="completion_report" 
                                rows="5" 
                                placeholder="Enter detailed report about the completed request" 
                                required
                            ></textarea>
                            <small class="form-text text-muted">Please provide a comprehensive report of the completed request.</small>
                        </div>

                        <div class="form-group">
                            <label for="actionsTaken">Actions Taken <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control" 
                                id="actionsTaken" 
                                name="actions_taken" 
                                rows="3" 
                                placeholder="Describe the specific actions taken to complete the request" 
                                required
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit Completion</button>
                    </div>
                </form>
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
                            <p><strong>Role:</strong> <span id="detailsRequestRole"></span></p>
                            <p><strong>Status:</strong> <span id="detailsRequestStatus" class="custom-badge"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Submitted:</strong> <span id="detailsRequestDate"></span></p>
                            <p><strong>Completed:</strong> <span id="detailsRequestCompleted"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Request Information</h6>
                            <div id="detailsRequestData"></div>
                        </div>
                    </div>
                    
                    <!-- Assignment Information -->
                    <div id="assignmentInfoSection" class="mt-3" style="display: none;">
                        <hr>
                        <h6>Assignment Information</h6>
                        <p><strong>Assigned To:</strong> <span id="detailsAssignedTo"></span></p>
                        <p><strong>Transaction Type:</strong> <span id="detailsTransactionType"></span></p>
                        <p><strong>Admin Notes:</strong> <span id="detailsAdminNotes"></span></p>
                    </div>
                    
                    <!-- Rejection Information -->
                    <div id="rejectionInfoSection" class="mt-3" style="display: none;">
                        <hr>
                        <h6>Rejection Information</h6>
                        <p><strong>Reason:</strong> <span id="detailsRejectionReason"></span></p>
                        <p><strong>Notes:</strong> <span id="detailsRejectionNotes"></span></p>
                        <p><strong>Rejected Date:</strong> <span id="detailsRejectedDate"></span></p>
                    </div>
                    
                    <!-- Pending Actions -->
                    <div id="pendingActionsContainer" class="mt-3" style="display: none;">
                        <hr>
                        <h6>Actions</h6>
                        <button type="button" class="btn btn-success modal-approve-btn">Approve</button>
                        <button type="button" class="btn btn-danger modal-reject-btn">Reject</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/assign-request.js') }}"></script>


</body>
</html>