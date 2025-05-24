<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/request-history.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Request History</title>
    <style>
        /* Additional styles for survey status */
        .custom-badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .custom-badge-success {
            color: #fff;
            background-color: #28a745;
        }
        .btn-view-survey {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="main-content"> {{-- Changed class from content to main-content --}}
        <h1>Request History</h1>
    <!--    <p>Welcome, <strong>{{ Auth::user()->username }}!</strong></p> -->
       
        <div class="history-table-container">
            <form action="">
                <table class="history-table">
                    <thead>
                       <tr>
                            <th>Request ID</th>
                            <th>Service</th>
                            <th>Assigned Staff</th>
                            <th>Date Submitted</th>
                            <th>Date Completed</th>
                            <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>
                                @if(Auth::user()->role == "Student")
                                    {{ 'SSR-' . date('Ymd', strtotime($request['created_at'])) . '-' . str_pad($request['id'], 4, '0', STR_PAD_LEFT) }}
                                @else
                                    {{ 'FSR-' . date('Ymd', strtotime($request['created_at'])) . '-' . str_pad($request['id'], 4, '0', STR_PAD_LEFT) }}
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
                                {{ $request->assignedUITCStaff ? $request->assignedUITCStaff->name : 'N/A' }}
                            </td>                            
                            <td>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span><br>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('h:i A') }}</span>
                            </td>
                            <td>
                               <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('M d, Y') }}</span><br>
                                <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('h:i A') }}</span>
                            </td>
                            
                            <td>
                                @if($request->status === 'Completed' && !$request->is_surveyed)
                                    <a href="{{ route('show.customer.satisfaction', $request->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-star"></i> Rate Service
                                    </a>
                                @elseif($request->is_surveyed)
                                    <div>
                                        <span class="custom-badge custom-badge-success">Feedback Submitted</span>
                                        <a href="{{ route('view.survey', $request->id) }}" class="btn btn-sm btn-info btn-view-survey">
                                            <i class="fas fa-eye"></i> View Feedback
                                        </a>
                                    </div>
                                @else
                                    <!-- No action needed -->
                                @endif
                            </td>                   
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No completed requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
            
            <!-- Pagination Container -->
            <div class="pagination-container">
                {{ $requests->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @stack('scripts')
</body>
</html>
