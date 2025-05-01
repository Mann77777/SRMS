<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">  <!-- Add this line -->
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body class="{{ Auth::check() ? 'user-authenticated' : '' }}" data-user-role="{{ Auth::user()->role }}">
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="main-content"> {{-- Added main-content wrapper --}}
        <!-- HERO SECTION -->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                <h1>
                    <?php
                    date_default_timezone_set('Asia/Manila');
                    $hour = date('H');
                    $greeting = ($hour >= 5 && $hour < 12) ? "Good Morning" :
                               (($hour >= 12 && $hour < 18) ? "Good Afternoon" : "Good Evening");
                    echo $greeting . ", " . Auth::user()->username . "!";
                    ?>
                </h1>
                <p class="lead">Track and manage your service requests</p>
            </div>
        </div>
    </section>


    <!-- STATUS OVERVIEW -->
    <section class="status-overview">
        <div class="container">
            <!-- First row -->
            <div class="row">
                <!-- Total Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Requests</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        In Progress</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inprogressRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cog fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Second row -->
            <div class="row">
                <!-- Completed Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Completed</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejected Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Rejected</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rejectedRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Requests Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Overdue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueRequests ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- QUICK ACTIONS -->
    <section class="quick-actions">
        <div class="container">
            <div class="action-buttons">
            @if(Auth::user()->role === 'Student')
                <a href="{{ url('/student-request') }}" class="action-button">
                    <i class="fas fa-plus-circle"></i>
                    <span>New Request</span>
                </a>
              @else
                 <a href="{{ url('/faculty-service') }}" class="action-button">
                    <i class="fas fa-plus-circle"></i>
                    <span>New Faculty Request</span>
                </a>
              @endif

                <a href="{{ url('/myrequests') }}" class="action-button">
                    <i class="fas fa-list-alt"></i>
                    <span>My Requests</span>
                </a>
                <a href="{{ url('/help') }}" class="action-button">
                    <i class="fas fa-question-circle"></i>
                    <span>Help Guide</span>
                </a>
            </div>
        </div>
    </section>

  

    <!-- RECENT REQUESTS -->
    <section class="recent-requests">
        <div class="container">
            <div class="section-header">
                <h2>Recent Requests</h2>
                <a href="{{ url('/myrequests') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="request-table-wrapper">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Service Type</th>
                            <th>Date Submitted</th>
                            <th>Last Update</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recentRequests as $request)
                        <tr>
                            <td>
                                <span class="request-id-text">
                                    @if(Auth::user()->role == "Student")
                                        {{ 'SSR-' . date('Ymd', strtotime($request['created_at'])) . '-' . str_pad($request['id'], 4, '0', STR_PAD_LEFT) }}
                                    @elseif(Auth::user()->role == "Faculty & Staff")
                                        {{ 'FSR-' . date('Ymd', strtotime($request['created_at'])) . '-' . str_pad($request['id'], 4, '0', STR_PAD_LEFT) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $request['service_type'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($request['created_at'])->format('M d, Y h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($request['updated_at'])->format('M d, Y h:i A') }}</td>
                            <td>
                                @if($request['status'] == 'Pending')
                                    <span class="custom-badge custom-badge-warning">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'In Progress')
                                    <span class="custom-badge custom-badge-info">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'Completed')
                                    <span class="custom-badge custom-badge-success">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'Overdue')
                                    <span class="custom-badge custom-badge-overdue">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'Cancelled')
                                    <span class="custom-badge custom-badge-danger">{{ $request['status'] }}</span>
                                @else
                                    <span class="custom-badge custom-badge-secondary">{{ $request['status'] }}</span>
                                @endif
                                </td>
                                               
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center"> No recent requests found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- SERVICE CATEGORIES -->
    <section class="service-categories">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2>Available Services</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="category-grid service-list">
                                <!-- Services will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
    </div> {{-- End of main-content wrapper --}}


    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/chatbot.js') }}"></script>
    <script src="{{ asset('js/service-dashboard.js') }}"></script>
    <script src="{{ asset('js/dashboard-requests.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
</body>
</html>
