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
<body class="{{ Auth::check() ? 'user-authenticated' : '' }}">
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

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

    <!-- STATUS OVERVIEW -->
    <section class="status-overview">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="status-card total">
                        <div class="icon-wrapper">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $totalRequests ?? 0 }}</h3>
                            <p>Total Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card pending">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $pendingRequests ?? 0 }}</h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card processing">
                        <div class="icon-wrapper">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $inprogressRequests ?? 0 }}</h3>
                            <p>In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card completed">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $completedRequests ?? 0 }}</h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
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
                             <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recentRequests as $request)
                        <tr>
                             <td>{{ $request['id'] }}</td>
                             <td>{{ $request['service_type'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($request['created_at'])->format('M d, Y h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($request['updated_at'])->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="badge
                                    @if($request['status'] == 'Pending') badge-warning
                                    @elseif($request['status'] == 'In Progress') badge-info
                                    @elseif($request['status'] == 'Completed') badge-success
                                     @elseif($request['status'] == 'Rejected') badge-danger
                                    @else badge-secondary
                                    @endif">
                                    {{ $request['status'] }}
                                </span>
                            </td>
                             <td>
                                 <a href="@if($request['type'] === 'student') {{ route('student.myrequests.show', $request['id']) }} @else {{ route('faculty.myrequests.show', $request['id']) }} @endif" class="btn-view">View</a>
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
    </section>


    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/chatbot.js') }}"></script>
    <script src="{{ asset('js/service-dashboard.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
</body>
</html>