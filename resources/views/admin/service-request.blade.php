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
    <style>
        .remaining-days {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }
        .remaining-days.positive {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .remaining-days.negative {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Loading Spinner Styles */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
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

        .loading-spinner.active {
            display: flex;
        }

        /* Button Spinner Styles */
        .btn-spinner {
            display: none;
        }

        .btn-spinner.active {
            display: inline-block;
        }

        .button-text {
            display: inline-block;
        }

        .button-text.hidden {
            display: none;
        }
    </style>
    <title>Admin - Service Request</title>
</head>
<body data-user-role="Admin">
    <!-- Add Loading Spinner HTML -->
    <div class="loading-spinner">
        <div class="spinner"></div>
    </div>

    @inject('serviceHelper', 'App\Helpers\ServiceHelper') {{-- Inject ServiceHelper --}}

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
                <option value="overdue">Overdue</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" name="search" id="search-input" placeholder="Search...">
                <button class="search-btn" type="button">Search</button>
            </div>

            <!-- <div class="requests-btn">
                <button type="button" class="delete-button" id="delete-btn">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div> -->
        </div>

        <div class="request-table-container">
            <h4>Request List</h4>
            <form action="" id="delete-form">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Details</th>
                            <th>Role</th>
                            <th>Date & Time Submitted</th>
                            <th>Date & Time Completed</th>
                            <th>Status</th>
                            <th>Remaining Days</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
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
                                    @if($request['status'] == 'Completed')
                                        @if(isset($request['updated_at']))
                                            <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('M d, Y') }}</span><br>
                                            <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('h:i A') }}</span>
                                        @else
                                            <!-- If no updated_at field, display the date field or a placeholder -->
                                            <span>{{ \Carbon\Carbon::parse($request['date'])->format('M d, Y') }}</span><br>
                                            <span>{{ \Carbon\Carbon::parse($request['date'])->format('h:i A') }}</span>
                                            <!-- Add a note or styling to indicate this is estimated -->
                                        @endif
                                    @else
                                        –
                                    @endif
                                </td>
                                <td>
                                    @if($request['status'] == 'Pending')
                                        <span class="custom-badge custom-badge-warning">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'In Progress')
                                        <span class="custom-badge custom-badge-info">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Completed')
                                        <span class="custom-badge custom-badge-success">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Overdue')
                                        <span class="custom-badge custom-badge-overdue">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Rejected')
                                        <span class="custom-badge custom-badge-danger">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Unresolvable')
                                        <span class="custom-badge custom-badge-gray">{{ $request['status'] }}</span>
                                    @else
                                        <span class="custom-badge custom-badge-secondary">{{ $request['status'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request['status'] == 'In Progress' && isset($request['transaction_type']))
                                        @php
                                            $transactionLimits = [
                                                'Simple Transaction' => 3,
                                                'Complex Transaction' => 7,
                                                'Highly Technical Transaction' => 20,
                                            ];
                                            $assignedDate = \Carbon\Carbon::parse($request['updated_at'])->startOfDay();
                                            $today = \Carbon\Carbon::today();

                                            // 1. Find the first business day on or after assignment
                                            $firstBusinessDay = $assignedDate->copy();
                                            while (true) {
                                                $dayOfWeek = $firstBusinessDay->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6); // Sunday or Saturday
                                                $isHoliday = \App\Models\Holiday::isHoliday($firstBusinessDay);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($firstBusinessDay, 'semestral_break'); // Assuming 'semestral_break' is a type
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    break;
                                                }
                                                $firstBusinessDay->addDay();
                                            }

                                            // 2. Calculate last allowed business day
                                            $limit = $transactionLimits[$request['transaction_type']] ?? 0;
                                            $lastAllowedDay = $firstBusinessDay->copy();
                                            if ($limit > 0) {
                                                $businessDaysCounted = 0;
                                                while ($businessDaysCounted < $limit) {
                                                    // Check if $lastAllowedDay itself is a business day before incrementing count for it
                                                    $dayOfWeek = $lastAllowedDay->dayOfWeek;
                                                    $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                    $isHoliday = \App\Models\Holiday::isHoliday($lastAllowedDay);
                                                    $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($lastAllowedDay, 'semestral_break');

                                                    if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                        $businessDaysCounted++;
                                                    }
                                                    // If we haven't reached the limit yet, move to the next day
                                                    if ($businessDaysCounted < $limit) {
                                                        $lastAllowedDay->addDay();
                                                    } else if ($businessDaysCounted == $limit && ($isWeekend || $isHoliday || $isAcademicPeriod)) {
                                                        // If the limit lands on a non-business day, keep skipping until a business day.
                                                        // This ensures the $lastAllowedDay is a business day.
                                                        // However, the loop condition $businessDaysCounted < $limit ensures we add days until limit is met on a business day.
                                                        // This part might need refinement if limit means "N business days from start" vs "ends on Nth business day"
                                                        // Current logic: finds the Nth business day.
                                                        while(true){
                                                            $dayOfWeekCheck = $lastAllowedDay->dayOfWeek;
                                                            $isWeekendCheck = ($dayOfWeekCheck === 0 || $dayOfWeekCheck === 6);
                                                            $isHolidayCheck = \App\Models\Holiday::isHoliday($lastAllowedDay);
                                                            $isAcademicPeriodCheck = \App\Models\Holiday::isAcademicPeriod($lastAllowedDay, 'semestral_break');
                                                            if (!$isWeekendCheck && !$isHolidayCheck && !$isAcademicPeriodCheck) break;
                                                            $lastAllowedDay->addDay();
                                                        }
                                                    }
                                                }
                                            }


                                            // 3. Find the next business day after the last allowed day (this is the overdueDate)
                                            $overdueDate = $lastAllowedDay->copy()->addDay();
                                            while (true) {
                                                $dayOfWeek = $overdueDate->dayOfWeek;
                                                $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                $isHoliday = \App\Models\Holiday::isHoliday($overdueDate);
                                                $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($overdueDate, 'semestral_break');
                                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                    break; // Found the first business day after lastAllowedDay
                                                }
                                                $overdueDate->addDay();
                                            }

                                            // 4. Determine if the request is actually overdue
                                            $isActuallyOverdue = $today->gte($overdueDate);

                                            // 5. Update status in DB if it's overdue and not already marked as Completed, Overdue, Cancelled, or Rejected
                                            if ($isActuallyOverdue && !in_array($request['status'], ['Completed', 'Overdue', 'Cancelled', 'Rejected'])) {
                                                if ($request['type'] === 'new_student_service') {
                                                    \App\Models\StudentServiceRequest::where('id', $request['id'])->update(['status' => 'Overdue']);
                                                } elseif ($request['type'] === 'faculty') {
                                                    \App\Models\FacultyServiceRequest::where('id', $request['id'])->update(['status' => 'Overdue']);
                                                }
                                                $request['status'] = 'Overdue'; // Reflect change for current view
                                            }

                                            // 6. Display logic
                                            if ($request['status'] == 'Overdue') {
                                                $daysOverdueCount = 0;
                                                $tempDate = $overdueDate->copy();
                                                while($tempDate->lte($today)) {
                                                    $dayOfWeek = $tempDate->dayOfWeek;
                                                    $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                    $isHoliday = \App\Models\Holiday::isHoliday($tempDate);
                                                    $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($tempDate, 'semestral_break');
                                                    if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                        $daysOverdueCount++;
                                                    }
                                                    if ($tempDate->isSameDay($today)) break;
                                                    $tempDate->addDay();
                                                }
                                                if ($daysOverdueCount == 0 && $isActuallyOverdue) $daysOverdueCount = 1; // Ensure at least 1 if overdue
                                        @endphp
                                        <span class="remaining-days negative">
                                            Overdue by {{ $daysOverdueCount }} day{{ $daysOverdueCount != 1 ? 's' : '' }}
                                            <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                            <br>
                                            <small style="color:#888;">Was due: {{ $lastAllowedDay->format('M d, Y') }}</small>
                                        </span>
                                        @php
                                            } else { // Not 'Overdue' status
                                                // Calculate business days elapsed from firstBusinessDay up to today
                                                $businessDaysElapsed = 0;
                                                $currentProcessingDate = $firstBusinessDay->copy();
                                                while ($currentProcessingDate->lte($today)) {
                                                    $dayOfWeek = $currentProcessingDate->dayOfWeek;
                                                    $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                                                    $isHoliday = \App\Models\Holiday::isHoliday($currentProcessingDate);
                                                    $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($currentProcessingDate, 'semestral_break');
                                                    if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                                        $businessDaysElapsed++;
                                                    }
                                                    if ($currentProcessingDate->isSameDay($today)) break;
                                                    $currentProcessingDate->addDay();
                                                }

                                                $calculatedRemainingDays = $limit - $businessDaysElapsed;

                                                if ($calculatedRemainingDays == 0 && $today->isSameDay($lastAllowedDay) && !$isActuallyOverdue) { // Due Today
                                        @endphp
                                        <span class="remaining-days positive"> {{-- Consider a neutral class like .remaining-days-due --}}
                                            Due today
                                            <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                            <br>
                                            <small style="color:#888;">Overdue on: {{ $overdueDate->format('M d, Y') }}</small>
                                        </span>
                                        @php
                                                } elseif ($calculatedRemainingDays > 0 && !$isActuallyOverdue) { // Days Left
                                        @endphp
                                        <span class="remaining-days positive">
                                            {{ $calculatedRemainingDays }} day{{ $calculatedRemainingDays != 1 ? 's' : '' }} left
                                            <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                            <br>
                                            <small style="color:#888;">Overdue on: {{ $overdueDate->format('M d, Y') }}</small>
                                        </span>
                                        @php
                                                } else { // Fallback for other cases (e.g., weekend/holiday before due date but after last allowed day)
                                        @endphp
                                        -
                                        @php
                                                }
                                            } // End else for not 'Overdue' status
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="btns">
                                    @if($request['status'] == 'Pending')
                                    <button type="button" class="btn-approve" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                        </svg> Approve
                                    </button>

                                    <button type="button" class="btn-reject" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg> Reject
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
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

            // Approve button click event (using event delegation)
            $(document).on('click', '.btn-approve', function() {
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

                // Show loading spinner in button
                const $button = $(this);
                const $buttonText = $button.find('.button-text');
                const $spinner = $button.find('.btn-spinner');
                
                $button.prop('disabled', true);
                $buttonText.addClass('hidden');
                $spinner.addClass('active');

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
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false);
                        $buttonText.removeClass('hidden');
                        $spinner.removeClass('active');
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

                // Show loading spinner in button
                const $button = $(this);
                const $buttonText = $button.find('.button-text');
                const $spinner = $button.find('.btn-spinner');
                
                $button.prop('disabled', true);
                $buttonText.addClass('hidden');
                $spinner.addClass('active');

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
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false);
                        $buttonText.removeClass('hidden');
                        $spinner.removeClass('active');
                    }
                });
            });

            // Handle Status Filter
            $('#status').on('change', function() {
                const selectedStatus = $(this).val();
                const searchTerm = $('#search-input').val(); // Get current search term
                fetchRequests(selectedStatus, searchTerm); // Pass search term
            });

            // Handle Search Input (Enter Key)
            $('#search-input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission if it's inside a form
                    const selectedStatus = $('#status').val();
                    const searchTerm = $(this).val();
                    fetchRequests(selectedStatus, searchTerm);
                }
            });

            // Handle Search Button Click
            $('.search-btn').on('click', function() {
                const selectedStatus = $('#status').val();
                const searchTerm = $('#search-input').val();
                fetchRequests(selectedStatus, searchTerm);
            });

            function fetchRequests(status, searchTerm = '', page = 1) { // Add searchTerm parameter
                // Add a loading indicator (optional)
                $('.request-table tbody').html('<tr><td colspan="8" style="text-align: center;">Loading...</td></tr>');

                $.ajax({
                    url: '{{ route("admin.service.requests.filter") }}', // We will define this route next
                    method: 'GET',
                    data: {
                        status: status,
                        search: searchTerm, // Add search term to data
                        page: page // Pass the page number for pagination
                    },
                    success: function(response) {
                        // Replace table body content
                        $('.request-table tbody').html(response.table_body);
                        // Replace pagination links
                        $('.pagination-container').html(response.pagination);
                        // Re-attach event listeners for dynamically added elements if necessary
                        // (e.g., approve/reject buttons, clickable IDs) - Consider moving event bindings to use delegation
                        rebindEventListeners(); 
                    },
                    error: function(xhr) {
                        console.error('Error fetching requests:', xhr);
                        $('.request-table tbody').html('<tr><td colspan="8" style="text-align: center; color: red;">Failed to load requests.</td></tr>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to filter requests. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            // Handle pagination clicks using event delegation
            $(document).on('click', '.pagination a', function(event) {
                event.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const selectedStatus = $('#status').val();
                const searchTerm = $('#search-input').val(); // Get current search term for pagination
                fetchRequests(selectedStatus, searchTerm, page); // Pass search term for pagination
            });

            // Function to rebind event listeners after AJAX update
            // Note: It's better practice to use event delegation from a static parent element
            // like $(document).on('click', '.btn-approve', function() { ... });
            // The current implementation already uses delegation for some buttons, which is good.
            // We'll add a placeholder function here, but ensure your existing delegated events cover the dynamic content.
            function rebindEventListeners() {
                // Example: If you had non-delegated events, rebind them here.
                // $('.some-dynamic-element').off('click').on('click', function() { ... });
                
                // Re-initialize Select All checkbox logic if needed, although it might be fine
                $('#select-all').prop('checked', false); // Reset select-all on filter/page change
                
                // Ensure existing delegated events still work (they should if properly set up)
                console.log("Event listeners rebound (or rely on delegation).");
            }


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
        const $statusBadge = $('#detailsRequestStatus');
        // Trim and normalize the status text
        const statusText = requestData.status.trim();
        $statusBadge.text(statusText);
        $statusBadge.removeClass().addClass('custom-badge');

        // Match the status badge styling with the table
        if (statusText === 'Pending') {
            $statusBadge.addClass('custom-badge-warning');
        } else if (statusText === 'In Progress') {
            $statusBadge.addClass('custom-badge-info');
        } else if (statusText === 'Completed') {
            $statusBadge.addClass('custom-badge-success');
        } else if (statusText === 'Rejected') {
            $statusBadge.addClass('custom-badge-danger');
        } else {
            $statusBadge.addClass('custom-badge-secondary');
        }

        // Handle completed date
        if (requestData.status === 'Completed' && requestData.updated_at) {
            $('#detailsRequestCompleted').text(moment(requestData.updated_at).format('MMM D, YYYY h:mm A'));
        } else {
            $('#detailsRequestCompleted').text('-');
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
        const requestId = $(this).text().trim();
        
        // Extract data from the current row
        const statusCell = row.find('td:eq(6)');
        const statusText = statusCell.find('.custom-badge').text().trim();
        
        // Get completed date from the completed date cell directly
        let completedDate = null;
        const completedDateCell = row.find('td:eq(5)');
        
        // Check if the status is completed and the cell doesn't just contain "-"
        if (statusText === 'Completed' && !completedDateCell.text().trim().includes('–')) {
            // Try to combine date and time from spans
            const dateSpan = completedDateCell.find('span:first').text().trim();
            const timeSpan = completedDateCell.find('span:last').text().trim();
            
            if (dateSpan && timeSpan) {
                completedDate = dateSpan + ' ' + timeSpan;
            }
        }
        
        // Build the request data object
        const requestData = {
            id: requestId,
            role: row.find('td:eq(3)').text().trim(),
            request_data: row.find('td:eq(2)').html(),
            date: row.find('td:eq(4) span:first').text().trim() + ' ' + 
                row.find('td:eq(4) span:last').text().trim(),
            status: statusText,
            updated_at: completedDate
        };
        
        console.log('Request data for modal:', requestData);
        
        // Update and show the modal
        updateRequestDetailsModal(requestData);
        $('#requestDetailsModal').modal('show');
    });

    // Add JS to enable Bootstrap tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
    </script>
</body>
</html>
