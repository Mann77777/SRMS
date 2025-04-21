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
            <!-- First row -->
            <div class="row">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="status-card pending">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $assignRequest ?? 0 }}</h3>
                            <p>Pending Requests</p>
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
                            <p>Completed Requests</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Second row -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="status-card rejected">
                        <div class="icon-wrapper">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $rejectedRequests ?? 0 }}</h3>
                            <p>Rejected Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="status-card staff">
                        <div class="icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="status-details">
                            <h3>{{ $assignStaff ?? 0 }}</h3>
                            <p>Active UITC Staff</p>
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
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Requests per Month</h3>
                            <!-- <div class="chart-actions">
                                <button class="time-filter active" data-period="6months">6 Months</button>
                                <button class="time-filter" data-period="year">1 Year</button>
                            </div> -->
                        </div>
                        <canvas id="requestsOverTimeChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Request Statistics Chart (Keep existing) -->
                <div class="col-lg-4 mb-4">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Request Statistics</h3>
                        </div>
                        <canvas id="requestStatisticsChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Appointments by UITC Staff Chart -->
                <div class="col-lg-12 mb-4">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Request Assigned per UITC Staff</h3>
                        </div>
                        <canvas id="appointmentsByStaffChart" height="300"></canvas>
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
                            <td>{{ $request['created_at']->format('M d, Y h:i: A') }}</td>
                            <td>
                                @if($request['status'] == 'Pending')
                                    <span class="custom-badge custom-badge-warning">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'In Progress')
                                    <span class="custom-badge custom-badge-info">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'Completed')
                                    <span class="custom-badge custom-badge-success">{{ $request['status'] }}</span>
                                @elseif($request['status'] == 'Rejected')
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
                            <p>Completed Requests</p>
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
                <a href="{{ url('/assign-request') }}" class="action-button">
                    <i class="fas fa-tasks"></i>
                    <span>View Tasks</span>
                </a>
                <a href="{{ url('/assign-history') }}" class="action-button">
                    <i class="fas fa-history"></i>
                    <span>Service History</span>
                </a>
                <a href="{{ url('/uitc-staff/reports') }}" class="action-button">
                    <i class="bx bxs-chat"></i>
                    <span>View Reports</span>
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
            
            // Event listeners for time filter buttons
            document.querySelectorAll('.time-filter').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.time-filter').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    const period = this.dataset.period;
                    console.log('Time period selected:', period);
                    
                    // Call the fetchTimeSeriesData function from the external JS
                    if (typeof fetchTimeSeriesData === 'function') {
                        fetchTimeSeriesData(period);
                    } else {
                        console.error('fetchTimeSeriesData function not available');
                        alert('This would fetch data for the ' + period + ' period');
                    }
                });
            });
        });


        
    </script>

    <!-- Load chart initialization script after setting data attributes -->
    <script src="{{ asset('js/admin-dashboard-charts.js') }}"></script>
</body>
</html>