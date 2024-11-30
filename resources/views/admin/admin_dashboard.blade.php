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
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Admin Dashboard</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

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
                    echo $greeting . ", " . Auth::guard('admin')->user()->username . "!";
                    ?>
                </h1>
                <p class="lead">Manage service requests and staff assignments</p>
            </div>
        </div>
    </section>

    @if(Auth::guard('admin')->user()->role === 'Admin')
    <!-- STATUS OVERVIEW -->
    <section class="status-overview">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="status-card total">
                        <div class="icon-wrapper">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $requestReceive ?? 0 }}</h3>
                            <p>New Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card pending">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $assignRequest ?? 0 }}</h3>
                            <p>Pending Assignments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card completed">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $servicesCompleted ?? 0 }}</h3>
                            <p>Completed Services</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-card staff">
                        <div class="icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $assignStaff ?? 0 }}</h3>
                            <p>Active Staff</p>
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
                <a href="{{ url('/admin/requests/new') }}" class="action-button">
                    <i class="fas fa-plus-circle"></i>
                    <span>Process Requests</span>
                </a>
                <a href="{{ url('/assign-management') }}" class="action-button">
                    <i class="fas fa-user-plus"></i>
                    <span>Assign Staff</span>
                </a>
                <a href="{{ url('/admin_report') }}" class="action-button">
                    <i class="fas fa-chart-bar"></i>
                    <span>View Reports</span>
                </a>
                <a href="{{ url('/settings') }}" class="action-button">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
    </section>

    <!-- RECENT REQUESTS -->
    <section class="recent-requests">
        <div class="container">
            <div class="section-header">
                <h2>Recent Requests</h2>
                <a href="{{ url('/service-request') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="request-table-wrapper">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Service Type</th>
                            <th>Requester</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests ?? [] as $request)
                        <tr>
                            <td>#{{ $request->id }}</td>
                            <td>{{ $request->service_type }}</td>
                            <td>{{ $request->user_name }}</td>
                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="status-badge {{ strtolower($request->status) }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="window.location.href='{{ url('/admin/request/'.$request->id) }}'">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-edit" onclick="window.location.href='{{ url('/admin/request/'.$request->id.'/assign') }}'">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No recent requests found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @elseif(Auth::guard('admin')->user()->role === 'Technician')
    <!-- TECHNICIAN STATUS OVERVIEW -->
    <section class="status-overview">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="status-card assigned">
                        <div class="icon-wrapper">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $assignedRequests ?? 0 }}</h3>
                            <p>Assigned Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="status-card completed">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $servicesCompleted ?? 0 }}</h3>
                            <p>Completed Services</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="status-card rating">
                        <div class="icon-wrapper">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ number_format($surveyRatings ?? 0, 1) }}</h3>
                            <p>Average Rating</p>
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= ($surveyRatings ?? 0) ? 'active' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TECHNICIAN QUICK ACTIONS -->
    <section class="quick-actions">
        <div class="container">
            <div class="action-buttons">
                <a href="{{ url('/technician/tasks') }}" class="action-button">
                    <i class="fas fa-tasks"></i>
                    <span>View Tasks</span>
                </a>
                <a href="{{ url('/technician/completed') }}" class="action-button">
                    <i class="fas fa-history"></i>
                    <span>Service History</span>
                </a>
                <a href="{{ url('/technician/profile') }}" class="action-button">
                    <i class="fas fa-user-cog"></i>
                    <span>My Profile</span>
                </a>
            </div>
        </div>
    </section>
    @endif

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/chatbot.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
