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
    <style>
        .custom-badge-overdue {
            background-color: #ff9800; /* Orange color */
            color: white;
        }
        .info-tooltip {
            display: inline-block;
            vertical-align: middle;
            margin-left: 4px;
        }
    </style>
</head>
<body data-user-role="UITC Staff">
    @inject('serviceHelper', 'App\Helpers\ServiceHelper') {{-- Inject ServiceHelper --}}

    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Assigned Requests</h1>

        <div class="dropdown-container">
            <!-- Status Filter -->
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Overdue">Overdue</option>
                <option value="Unresolvable">Unresolvable</option>
            </select>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" name="search" id="search-input" placeholder="Search...">
                <button class="search-btn" type="button">Search</button>
            </div>

            <!-- Transaction Filter 
            <select name="transaction_type" id="transaction_type">
                <option value="all">All Transaction</option>
                <option value="simple">Simple Transaction</option>
                <option value="complex">Complex Transaction</option>
                <option value="highly technical">Highly Technical Transaction</option>
            </select> -->
        </div>

        <div class="assignreq-table-container">
            <h4>Assigned Request List</h4>
            <div class="assignreq-table-wrapper">
                <table class="assignreq-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Details</th>
                            <th>Validity Period</th> {{-- Added Validity Period Header --}}
                            <th>Role</th>
                            <th>Date & Time Submitted</th>
                            <th>Date & Time Completed</th>
                            <!-- <th>Transaction Type</th> -->
                            <th>Status</th>
                            <th>Remaining Days</th>
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
                                    {{-- Use ServiceHelper for consistent formatting --}}
                                    @php
                                        $requesterName = ($request->requester_first_name && $request->requester_last_name) 
                                            ? $request->requester_first_name . ' ' . $request->requester_last_name 
                                            : ($request->first_name && $request->last_name ? $request->first_name . ' ' . $request->last_name : 'N/A');
                                        
                                        $idField = '';
                                        if ($request->request_type == 'student' && isset($request->student_id)) {
                                            $idField = '<strong>Student ID:</strong> ' . $request->student_id . '<br>';
                                        } elseif ($request->request_type == 'faculty' && isset($request->faculty_id)) {
                                            $idField = '<strong>Faculty ID:</strong> ' . $request->faculty_id . '<br>';
                                        }
                                        
                                        $serviceName = $serviceHelper::formatServiceCategory($request->service_category, $request->description);
                                        $descriptionField = (isset($request->description) && $request->service_category != 'others') 
                                            ? '<br><strong>Description:</strong> ' . $request->description 
                                            : '';
                                    @endphp
                                    {!! 
                                        '<strong>Name:</strong> ' . $requesterName . '<br>' .
                                        $idField .
                                        '<strong>Service:</strong> ' . $serviceName .
                                        $descriptionField
                                    !!}
                                @endif
                            </td>
                            <td>
                                {{-- Use ServiceHelper to get validity days --}}
                                @php
                                    $validityDays = $serviceHelper::getServiceValidityDays($request->service_category);
                                    $validityText = match($validityDays) {
                                        3 => 'Simple (3 days)',
                                        7 => 'Complex (7 days)',
                                        20 => 'Highly Technical (20 days)',
                                        default => $validityDays . ' days',
                                    };
                                @endphp
                                {{ $validityText }}
                            </td>
                            <td>{{ $request->user_role ?? ($request->request_type == 'faculty' ? 'Faculty & Staff' : 'Student') }}</td>
                            <td>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span><br>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('h:i A') }}</span>
                            </td>
                            <td>
                                @if($request->status == 'Completed')
                                    @if($request->completed_at)
                                        <span>{{ \Carbon\Carbon::parse($request->completed_at)->format('M d, Y') }}</span><br>
                                        <span>{{ \Carbon\Carbon::parse($request->completed_at)->format('h:i A') }}</span>
                                    @else
                                        <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('M d, Y') }}</span><br>
                                        <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('h:i A') }}</span>
                                    @endif
                                @else
                                    –
                                @endif
                            </td>
                            <!-- <td>
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
                        </td> -->
                            <td>
                                @if($request->status == 'Pending')
                                    <span class="custom-badge custom-badge-warning">{{ $request->status }}</span>
                                @elseif($request->status == 'In Progress')
                                    <span class="custom-badge custom-badge-info">{{ $request->status }}</span>
                                @elseif($request->status == 'Completed')
                                    <span class="custom-badge custom-badge-success">{{ $request->status }}</span>
                                @elseif($request->status == 'Overdue')
                                    <span class="custom-badge custom-badge-overdue">{{ $request->status }}</span>
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
                                            <span style="display: inline-flex; align-items: center;">
                                                {{ $remainingDays }} days left
                                                <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px; vertical-align: middle;">
                                                    <i class="fas fa-info-circle"></i>
                                                </span>
                                            </span>
                                            <br>
                                            <small style="color:#888;">Overdue on: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
                                        </span>
                                    @else
                                        <span class="remaining-days negative">
                                            <span style="display: inline-flex; align-items: center;">
                                                Overdue by {{ abs($remainingDays) }} days
                                                <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px; vertical-align: middle;">
                                                    <i class="fas fa-info-circle"></i>
                                                </span>
                                            </span>
                                            <br>
                                            <small style="color:#888;">Was due: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
                                        </span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="btns">
                                 @if($request->status != 'Completed' && $request->status != 'Cancelled' && $request->status != 'Rejected' && $request->status != 'Unresolvable')
                                <button class="btn-complete" data-request-id="{{ $request->id }}" data-request-type="{{ $request->request_type }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                    </svg> Complete
                                </button>
                                <button class="btn-unresolvable btn-danger" data-request-id="{{ $request->id }}" data-request-type="{{ $request->request_type }}" style="margin-left: 5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                    </svg> Unresolvable
                                </button>
                                <button class="btn-return btn-warning" data-request-id="{{ $request->id }}" data-request-type="{{ $request->request_type }}" style="margin-left: 5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14.5 1.5a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v4a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L12.293 5H1.5v-3h13a.5.5 0 0 0 .5.5z"/>
                                    </svg> Return to Admin
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

    <!-- Unresolvable Request Modal -->
    <div class="modal fade" id="unresolvableRequestModal" tabindex="-1" role="dialog" aria-labelledby="unresolvableRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unresolvableRequestModalLabel">Mark Request as Unresolvable</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="unresolvableRequestForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="unresolvableRequestId" name="request_id">
                        <input type="hidden" id="unresolvableRequestType" name="request_type" value="">
                        
                        <div class="form-group">
                            <label for="unresolvableReason">Reason for Unresolvable <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control" 
                                id="unresolvableReason" 
                                name="unresolvable_reason" 
                                rows="5" 
                                placeholder="Enter detailed reason why the request is unresolvable" 
                                required
                            ></textarea>
                            <small class="form-text text-muted">Please provide a comprehensive reason.</small>
                        </div>

                        <div class="form-group">
                            <label for="unresolvableActionsTaken">Actions Taken (Optional)</label>
                            <textarea 
                                class="form-control" 
                                id="unresolvableActionsTaken" 
                                name="unresolvable_actions_taken" 
                                rows="3" 
                                placeholder="Describe any actions taken before marking as unresolvable"
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Mark as Unresolvable</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Return Request Modal -->
    <div class="modal fade" id="returnRequestModal" tabindex="-1" role="dialog" aria-labelledby="returnRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnRequestModalLabel">Return Request to Admin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="returnRequestForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="returnRequestId" name="request_id">
                        <input type="hidden" id="returnRequestType" name="request_type" value="">
                        
                        <div class="form-group">
                            <label for="returnReason">Reason for Returning <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control" 
                                id="returnReason" 
                                name="return_reason" 
                                rows="5" 
                                placeholder="Enter detailed reason why the request is being returned to admin" 
                                required
                            ></textarea>
                            <small class="form-text text-muted">Please provide a comprehensive reason.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Return to Admin</button>
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
    <script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
    </script>

</body>
</html>
