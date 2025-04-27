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
    <script src="{{ asset('js/notifications.js') }}"></script>
    <title>Dashboard</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
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
                <!-- First row -->
                <div class="row">
                    <!-- Total/New Requests Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            New Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $requestReceive ?? 0 }}</div>
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
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignRequest ?? 0 }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress Requests Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            In Progress Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inProgressRequests ?? 0 }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-spinner fa-2x text-gray-300"></i>
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
                                            Completed Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $servicesCompleted ?? 0 }}</div>
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
                                            Rejected Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rejectedRequests ?? 0 }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active UITC Staff Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-secondary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                            Active UITC Staff</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignStaff ?? 0 }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
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

        <!-- CHARTS SECTION -->
        <section class="charts-section">
            <div class="container">
                <div class="row">
                    <!-- Requests Over Time Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Requests per Month</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="requestsOverTimeChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request Statistics Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Request Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie">
                                    <canvas id="requestStatisticsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Appointments by UITC Staff Chart -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Request Assigned per UITC Staff</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="appointmentsByStaffChart" height="300"></canvas>
                                </div>
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRequests ?? [] as $request)
                            <tr>
                                <td>
                                    <span class="clickable-request-id" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                        {{ $request['id'] }}
                                    </span>
                                </td>
                                <td>{{ $request['service_type'] }}</td>
                                <td>{{ $request['user_name'] }}</td>
                                <td>{{ $request['created_at']->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if($request['status'] == 'Pending')
                                        <span class="custom-badge custom-badge-warning">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'In Progress')
                                        <span class="custom-badge custom-badge-info">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Completed')
                                        <span class="custom-badge custom-badge-success">{{ $request['status'] }}</span>
                                    @elseif($request['status'] == 'Cancelled')
                                        <span class="custom-badge custom-badge-danger">{{ $request['status'] }}</span>
                                    @else
                                        <span class="custom-badge custom-badge-secondary">{{ $request['status'] }}</span>
                                    @endif
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

        @elseif(Auth::guard('admin')->user()->role === 'UITC Staff')
        <!-- TECHNICIAN STATUS OVERVIEW -->
        <section class="status-overview">
            <div class="container">
                <div class="row">
                    <!-- Assigned Requests Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Assigned Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignedRequests ?? 0 }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Requests Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Completed Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $servicesCompleted ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Average Rating Card -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Average Rating</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($surveyRatings ?? 0, 1) }}</div>
                                        <div class="rating-stars mt-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= ($surveyRatings ?? 0) ? 'text-warning' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-star fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- UITC STAFF QUICK ACTIONS -->
        <section class="quick-actions">
            <div class="container">
                <div class="action-buttons">
                    <a href="{{ url('/assign-request') }}" class="action-button">
                        <i class="fas fa-tasks"></i>
                        <span>Assigned Request</span>
                    </a>
                    <a href="{{ url('/assign-history') }}" class="action-button">
                        <i class="fas fa-history"></i>
                        <span>Assigened History</span>
                    </a>
                    <a href="{{ url('/uitc-staff/reports') }}" class="action-button">
                        <i class="fas fa-chart-bar"></i>
                        <span>My Report</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- RECENT ASSIGNED REQUESTS -->
        <section class="recent-requests">
            <div class="section-header">
                <h2>Recent Assigned Requests</h2>
                <a href="{{ url('/assign-request') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeRequests ?? [] as $request)
                        <tr>
                            <td>
                                <span class="clickable-request-id" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                    {{ $request['id'] }}
                                </span>
                            </td>
                            <td>{{ $request['service_type'] }}</td>
                            <td>{{ $request['user_name'] }}</td>
                            <td>{{ $request['created_at']->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="custom-badge custom-badge-info">In Progress</span>
                            </td>                          
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No active assigned requests found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/chatbot.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set data attributes programmatically for all charts
            
            // Request Stats Chart
            const statsChart = document.getElementById('requestStatisticsChart');
            if (statsChart) {
                statsChart.dataset.totalRequests = "{{ $totalRequests ?? 0 }}";
                statsChart.dataset.weekRequests = "{{ $weekRequests ?? 0 }}";
                statsChart.dataset.monthRequests = "{{ $monthRequests ?? 0 }}";
                statsChart.dataset.yearRequests = "{{ $yearRequests ?? 0 }}";
                
                console.log("Request Stats Data:", {
                    totalRequests: statsChart.dataset.totalRequests,
                    weekRequests: statsChart.dataset.weekRequests,
                    monthRequests: statsChart.dataset.monthRequests,
                    yearRequests: statsChart.dataset.yearRequests
                });
            }
            
            // Requests Over Time Chart
            const timeChart = document.getElementById('requestsOverTimeChart');
            if (timeChart) {
                timeChart.dataset.labels = JSON.stringify(@json($requestsOverTime['labels'] ?? []));
                timeChart.dataset.values = JSON.stringify(@json($requestsOverTime['data'] ?? []));
                
                try {
                    console.log("Requests Over Time Data:", {
                        labels: JSON.parse(timeChart.dataset.labels || '[]'),
                        values: JSON.parse(timeChart.dataset.values || '[]')
                    });
                } catch (e) {
                    console.error("Error parsing time chart data:", e);
                    console.log("Raw labels:", timeChart.dataset.labels);
                    console.log("Raw values:", timeChart.dataset.values);
                }
            }
            
            // Staff Appointments Chart
            const staffChart = document.getElementById('appointmentsByStaffChart');
            if (staffChart) {
                staffChart.dataset.staffNames = JSON.stringify(@json($appointmentsByStaff['labels'] ?? []));
                staffChart.dataset.assignedCounts = JSON.stringify(@json($appointmentsByStaff['assigned'] ?? []));
                staffChart.dataset.completedCounts = JSON.stringify(@json($appointmentsByStaff['completed'] ?? []));
                
                try {
                    console.log("Staff Appointments Data:", {
                        staffNames: JSON.parse(staffChart.dataset.staffNames || '[]'),
                        assignedCounts: JSON.parse(staffChart.dataset.assignedCounts || '[]'),
                        completedCounts: JSON.parse(staffChart.dataset.completedCounts || '[]')
                    });
                } catch (e) {
                    console.error("Error parsing staff chart data:", e);
                    console.log("Raw staff names:", staffChart.dataset.staffNames);
                    console.log("Raw assigned counts:", staffChart.dataset.assignedCounts);
                    console.log("Raw completed counts:", staffChart.dataset.completedCounts);
                }
            }
        });
    </script>

    <!-- Load chart initialization script after setting data attributes -->
    <script src="{{ asset('js/admin-dashboard-charts.js') }}"></script>
</body>
</html>