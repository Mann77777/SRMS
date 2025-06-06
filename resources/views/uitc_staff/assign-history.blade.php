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
    <link href="{{ asset('css/assign-history.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Assigned History</title>
</head>
<body>
     <!-- Include Navbar -->
     @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Assigned History</h1>

        <!-- <div class="dropdown-container">
             Status Filter 
            <select name="status" id="status-history-filter" class="form-control" style="width: auto; display: inline-block;">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="Unresolvable" {{ request('status') == 'Unresolvable' ? 'selected' : '' }}>Unresolvable</option>
            </select>

             Search Bar
            <div class="search-container" style="display: inline-block; margin-left: 10px;">
                <input type="text" name="search" id="search-input-history" class="form-control" placeholder="Search..." value="{{ request('search') }}" style="width: 250px; display: inline-block;">
                <button class="btn btn-primary search-btn-history" type="button" style="display: inline-block;">Search</button>
            </div>
        </div> -->

        <div class="assignhistory-table-container">
            <!-- <h4>Assigned Request History</h4> -->
            <div class="assignhistory-table-wrapper">
                <table class="assignhistory-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Data</th>
                            <th>Role</th>
                            <th>Date Submitted</th>
                            <th>Date Closed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body">
                        @if(isset($completedRequests) && count($completedRequests) > 0)
                            @foreach($completedRequests as $request)
                                <tr>
                                    <td>
                                        {{ $request->id }}
                                    </td>
                                    <td>{!! $request->request_data !!}</td>
                                    <td>{{ $request->user_role ?? ($request->request_type == 'faculty' ? 'Faculty & Staff' : 'Student') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y h:i A') }}</td>
                                    <td>
                                        @if($request->completed_at)
                                            {{ \Carbon\Carbon::parse($request->completed_at)->format('M d, Y h:i A') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($request->updated_at)->format('M d, Y h:i A') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->status == 'Completed')
                                            <span class="custom-badge custom-badge-success">{{ $request->status }}</span>
                                        @elseif($request->status == 'Cancelled')
                                            <span class="custom-badge custom-badge-secondary">{{ $request->status }}</span>
                                        @elseif($request->status == 'Unresolvable')
                                            <span class="custom-badge custom-badge-danger">{{ $request->status }}</span>
                                        @else
                                            <span class="custom-badge">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $satisfaction = App\Models\CustomerSatisfaction::where('request_id', $request->id)
                                                ->where('request_type', $request->request_type == 'student' ? 'Student' : 'Faculty & Staff')
                                                ->first();
                                        @endphp
                                        
                                        @if($satisfaction)
                                            <button class="view-satisfaction-btn btn btn-sm btn-info" 
                                                    data-toggle="modal" 
                                                    data-target="#satisfactionModal" 
                                                    data-id="{{ $request->id }}"
                                                    data-type="{{ $request->request_type }}"
                                                    data-responsiveness="{{ $satisfaction->responsiveness }}"
                                                    data-reliability="{{ $satisfaction->reliability }}"
                                                    data-access="{{ $satisfaction->access_facilities }}"
                                                    data-communication="{{ $satisfaction->communication }}"
                                                    data-costs="{{ $satisfaction->costs }}"
                                                    data-integrity="{{ $satisfaction->integrity }}"
                                                    data-assurance="{{ $satisfaction->assurance }}"
                                                    data-outcome="{{ $satisfaction->outcome }}"
                                                    data-average="{{ $satisfaction->average_rating }}"
                                                    data-comments="{{ $satisfaction->additional_comments }}">
                                                <i class="fas fa-eye"></i> View Feedback
                                            </button>
                                        @else
                                            <span class="text-muted">No feedback</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">No completed requests found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $completedRequests->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
    
    @include('admin.modal.customersatisfaction-modal')


    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Set CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        
            // View satisfaction button click event
            $('#satisfactionModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                
                // Get data attributes
                const responsiveness = button.data('responsiveness');
                const reliability = button.data('reliability');
                const access = button.data('access');
                const communication = button.data('communication');
                const costs = button.data('costs');
                const integrity = button.data('integrity');
                const assurance = button.data('assurance');
                const outcome = button.data('outcome');
                const average = button.data('average');
                const comments = button.data('comments');
                
                // Reset all radio buttons first
                $('.resp-radio, .rel-radio, .acc-radio, .com-radio, .cost-radio, .int-radio, .ass-radio, .out-radio').prop('checked', false);
                
                // Set the checked radio buttons for each criteria
                $(`#resp-${responsiveness}`).prop('checked', true);
                $(`#rel-${reliability}`).prop('checked', true);
                $(`#acc-${access}`).prop('checked', true);
                $(`#com-${communication}`).prop('checked', true);
                $(`#cost-${costs}`).prop('checked', true);
                $(`#int-${integrity}`).prop('checked', true);
                $(`#ass-${assurance}`).prop('checked', true);
                $(`#out-${outcome}`).prop('checked', true);
                
                // Format average rating with stars
                const avgRating = parseFloat(average).toFixed(1);
                let starsHtml = '';
                
                for (let i = 1; i <= 5; i++) {
                    if (i <= Math.floor(avgRating)) {
                        starsHtml += '<i class="fas fa-star"></i>';
                    } else if (i - 0.5 <= avgRating) {
                        starsHtml += '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star"></i>';
                    }
                }
                
                $('.rating-number').text(avgRating);
                $('.rating-stars').html(starsHtml);
                
                // Set comments or hide comments section if none
                if (comments && comments.trim() !== '') {
                    $('#modal-comments').text(comments);
                    $('#comments-container').show();
                } else {
                    $('#comments-container').hide();
                }
            });


            // Format service category
            function formatServiceCategory(category) {
                const categories = {
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
                
                return categories[category] || category;
            }

            // Function to apply filters for history page
            function applyHistoryFilters() {
                const status = $('#status-history-filter').val();
                const searchTerm = $('#search-input-history').val();
                
                let queryParams = [];
                
                if (status && status !== 'all') {
                    queryParams.push(`status=${encodeURIComponent(status)}`);
                }
                
                if (searchTerm) {
                    queryParams.push(`search=${encodeURIComponent(searchTerm)}`);
                }
                
                let url = window.location.pathname;
                if (queryParams.length > 0) {
                    url += '?' + queryParams.join('&');
                }
                
                window.location.href = url;
            }

            // Add event listeners to filters
            $('#status-history-filter').on('change', applyHistoryFilters);
            $('.search-btn-history').on('click', applyHistoryFilters);
            $('#search-input-history').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyHistoryFilters();
                }
            });

        });
    </script>
</body>
</html>
