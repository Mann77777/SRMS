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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-report.css') }}" rel="stylesheet">
    <title>Report</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="content">

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="report-header">
                <h1>Report</h1>
            </div>
            </div>
            <div class="col-md-4 text-right">
                <form id="exportForm" action="{{ route('admin.reports.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="staff_id" value="{{ $selectedStaffId }}">
                    <input type="hidden" name="service_category" value="{{ $selectedCategory }}">
                    <input type="hidden" name="custom_start_date" value="{{ $customStartDate }}">
                    <input type="hidden" name="custom_end_date" value="{{ $customEndDate }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                </form>
            </div>
        </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period">Time Period</label>
                            <select class="form-control" id="period" name="period" onchange="toggleCustomDateFields()">
                                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Last 3 Months</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Last 12 Months</option>
                                <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="staff_id">UITC Staff</label>
                            <select class="form-control" id="staff_id" name="staff_id">
                                <option value="all">All Staff</option>
                                @foreach($uitcStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ $selectedStaffId == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service_category">Service Category</label>
                            <select class="form-control" id="service_category" name="service_category">
                                <option value="all">All Categories</option>
                                @foreach($serviceCategories as $key => $category)
                                    <option value="{{ $key }}" {{ $selectedCategory == $key ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
                
                <div class="row" id="customDateFields" style="{{ $period == 'custom' ? '' : 'display: none;' }}">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="custom_start_date">Start Date</label>
                            <input type="date" class="form-control" id="custom_start_date" name="custom_start_date" value="{{ $customStartDate }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="custom_end_date">End Date</label>
                            <input type="date" class="form-control" id="custom_end_date" name="custom_end_date" value="{{ $customEndDate }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <!-- First Row of Stats Cards with Total, Completed, In Progress, Pending -->
<div class="row">
    <!-- Total Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_requests'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_requests'] }} 
                            <span class="text-sm text-muted">
                                ({{ $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100) : 0 }}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- In Progress Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            In Progress Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress_requests'] }}
                            <span class="text-sm text-muted">
                                ({{ $stats['total_requests'] > 0 ? round(($stats['in_progress_requests'] / $stats['total_requests']) * 100) : 0 }}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-spinner fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pending Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_requests'] }}
                            <span class="text-sm text-muted">
                                ({{ $stats['total_requests'] > 0 ? round(($stats['pending_requests'] / $stats['total_requests']) * 100) : 0 }}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row of Stats Cards with Cancelled, Rejected, Resolution Time -->
<div class="row">
    <!-- Cancelled Requests Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Cancelled Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelled_requests'] }}
                            <span class="text-sm text-muted">
                                ({{ $stats['total_requests'] > 0 ? round(($stats['cancelled_requests'] / $stats['total_requests']) * 100) : 0 }}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ban fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejected Requests Card (if you have this data available) -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Rejected Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected_requests'] ?? 0 }}
                            <span class="text-sm text-muted">
                                ({{ $stats['total_requests'] > 0 && isset($stats['rejected_requests']) ? round(($stats['rejected_requests'] / $stats['total_requests']) * 100) : 0 }}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolution Time Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Average Resolution Time</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['avg_resolution_time'] }} days</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Status Distribution Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Request Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLA Performance Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">SLA Performance</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="slaChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Met Deadline ({{ $slaStats['met_percentage'] }}%)
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Missed Deadline ({{ $slaStats['missed_percentage'] }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Category Breakdown -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Service Category Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="categoryTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Service Category</th>
                                    <th>Total Requests</th>
                                    <th>Completed</th>
                                    <th>In Progress</th>
                                    <th>Pending</th>
                                    <th>Cancelled</th>
                                    <th>Completion Rate</th>
                                    <th>Avg Resolution (days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryStats as $category => $data)
                                <tr>
                                    <td>{{ $category }}</td>
                                    <td>{{ $data['total'] }}</td>
                                    <td>{{ $data['completed'] }}</td>
                                    <td>{{ $data['in_progress'] }}</td>
                                    <td>{{ $data['pending'] }}</td>
                                    <td>{{ $data['cancelled'] }}</td>
                                    <td>
                                        {{ $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) : 0 }}%
                                    </td>
                                    <td>{{ is_numeric($data['avg_resolution']) ? $data['avg_resolution'] : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Monthly Request Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">UITC Staff Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="staffTable" width="100%" cellspacing="0">
                            <thead> 
                                <tr>
                                    <th>Staff Name</th>
                                    <th>Total Assigned</th>
                                    <th>Completed</th>
                                    <th>In Progress</th>
                                    <th>Pending</th>
                                    <th>Completion Rate</th>
                                    <th>Avg Resolution (days)</th>
                                    <th>SLA Met Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffStats as $staffId => $data)
                                <tr>
                                    <td>{{ $data['name'] }}</td>
                                    <td>{{ $data['total'] }}</td>
                                    <td>{{ $data['completed'] }}</td>
                                    <td>{{ $data['in_progress'] }}</td>
                                    <td>{{ $data['pending'] }}</td>
                                    <td>
                                        {{ $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) : 0 }}%
                                    </td>
                                    <td>{{ $data['avg_resolution'] }}</td>
                                    <td>{{ $data['sla_met_rate'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle custom date fields based on period selection
        function toggleCustomDateFields() {
            var period = document.getElementById('period').value;
            var customDateFields = document.getElementById('customDateFields');
            
            if (period === 'custom') {
                customDateFields.style.display = 'flex';
            } else {
                customDateFields.style.display = 'none';
            }
        }
        
        // Status Distribution Chart
        var statusCtx = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Pending', 'Cancelled', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $stats['completed_requests'] }},
                        {{ $stats['in_progress_requests'] }},
                        {{ $stats['pending_requests'] }},
                        {{ $stats['cancelled_requests'] }},
                        {{ $stats['rejected_requests'] ?? 0 }} // Added rejected requests
                    ],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#e74a3b', '#858796'],
                    hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf', '#e02d1b', '#6e707e'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 70,
            },
        });
        
        // SLA Performance Chart
        var slaCtx = document.getElementById('slaChart').getContext('2d');
        var slaChart = new Chart(slaCtx, {
            type: 'doughnut',
            data: {
                labels: ['Met Dealine', 'Missed Deadline'],
                datasets: [{
                    data: [
                        {{ $slaStats['met'] }},
                        {{ $slaStats['missed'] }}
                    ],
                    backgroundColor: ['#1cc88a', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#e02d1b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 70,
            },
        });
        
        // Monthly Trends Chart
        var trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        var trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($monthlyTrends as $month => $data)
                        '{{ $month }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'New Requests',
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [
                        @foreach($monthlyTrends as $data)
                            {{ $data['total'] }},
                        @endforeach
                    ],
                },
                {
                    label: 'Completed Requests',
                    lineTension: 0.3,
                    backgroundColor: "rgba(28, 200, 138, 0.05)",
                    borderColor: "rgba(28, 200, 138, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointBorderColor: "rgba(28, 200, 138, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [
                        @foreach($monthlyTrends as $data)
                            {{ $data['completed'] }},
                        @endforeach
                    ],
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'month'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 12
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        });
        
        // DataTables initialization
        $(document).ready(function() {
            $('#categoryTable').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 10
            });
            
            $('#staffTable').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 10
            });
        });
    </script>

    
</body>
</html>