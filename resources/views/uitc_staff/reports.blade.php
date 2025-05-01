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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/reports.css') }}" rel="stylesheet">
    <title>UITC Staff Report</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')
    
    <div class="content">
        <h1>my report</h1>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form action="{{ route('uitc-staff.reports') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="period" class="mr-2">Time Period:</label>
                    <select name="period" id="period" class="form-control" onchange="toggleCustomDateInputs()">
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Last 12 Months</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>
                
                <div id="customDateInputs" class="custom-date-inputs {{ $period == 'custom' ? 'd-flex' : '' }} align-items-center">
                    <div class="form-group mr-2">
                        <label for="custom_start_date" class="mr-2">From:</label>
                        <input type="date" name="custom_start_date" id="custom_start_date" class="form-control" value="{{ $customStartDate ?? '' }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="custom_end_date" class="mr-2">To:</label>
                        <input type="date" name="custom_end_date" id="custom_end_date" class="form-control" value="{{ $customEndDate ?? '' }}">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </form>
        </div>
        
        <!-- Date Range Display -->
        <div class="alert alert-info mb-4 d-flex justify-content-between align-items-center">
            <div>
                <strong>Showing data from:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </div>
            <a href="{{ route('uitc-staff.export-reports', [
                'period' => $period,
                'custom_start_date' => $customStartDate,
                'custom_end_date' => $customEndDate
            ]) }}" class="btn btn-primary">
                <i class="fas fa-file-pdf mr-2"></i> Export to PDF
            </a>
        </div>

        <!-- NEW: SLA Performance Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-tachometer-alt mr-2"></i> UITC Staff Performance</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Met Deadline</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $slaStats['met_percentage'] }}%</div>
                                        <div class="small text-muted">{{ $slaStats['met'] }} of {{ $slaStats['met'] + $slaStats['missed'] }} completed requests</div>
                                        <div class="progress progress-sm mt-2">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $slaStats['met_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Missed Deadline</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $slaStats['missed_percentage'] }}%</div>
                                        <div class="small text-muted">{{ $slaStats['missed'] }} of {{ $slaStats['met'] + $slaStats['missed'] }} completed requests</div>
                                        <div class="progress progress-sm mt-2">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $slaStats['missed_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Average Response Time</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $slaStats['avg_response_time'] }} hrs</div>
                                        <div class="small d-flex justify-content-between mt-2">
                                            <span>Avg Overdue Time:</span>
                                            <span>{{ $slaStats['avg_overdue_days'] }} days</span>
                                        </div>
                                        <div class="small d-flex justify-content-between">
                                            <span>Max Overdue:</span>
                                            <span>{{ $slaStats['max_overdue_days'] }} days</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- First Row: Overview Stats -->
        <!-- First Row: Overview Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Completed</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_requests'] }}</div>
                                <div class="small text-muted">{{ $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100) : 0 }}% completion rate</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
                <!-- Overdue Requests Card -->
            <div class="col-md-3">
                <div class="card border-left-overdue shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Overdue Requests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_requests'] ?? 0 }}</div>
                                <div class="small text-muted">
                                    @if(isset($stats['total_requests']) && $stats['total_requests'] > 0 && isset($stats['overdue_requests']))
                                        {{ round(($stats['overdue_requests'] / $stats['total_requests']) * 100) }}% of total requests
                                    @else
                                        0% of total requests
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Resolution Time</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['avg_resolution_time'], 2) }} days</div>
                                <div class="small d-flex justify-content-between mt-2">
                                    <span>Median: {{ number_format($stats['median_resolution_time'], 2) }}</span>
                                    <span>Fastest: {{ number_format($timeStats['fastest_resolution'], 2) }}</span>
                                    <span>Slowest: {{ number_format($timeStats['slowest_resolution'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        
        <!-- Second Row: Charts -->
        <div class="row">
            <!-- Request Status Distribution Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie mr-2"></i> Request Status Distribution
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Trends Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line mr-2"></i> Monthly Trends
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

        <!-- Add this code to reports.blade.php before the Daily Activity Chart section -->
        <div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line mr-2"></i> Areas for Improvement
            </div>
            <div class="card-body">
                @if(count($improvementRecommendations) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped improvement-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Area</th>
                                    <th style="width: 20%;">Current Metric</th>
                                    <th style="width: 15%;">Target</th>
                                    <th style="width: 40%;">Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($improvementRecommendations as $recommendation)
                                    <tr>
                                        <td>{{ $recommendation['area'] }}</td>
                                        <td class="text-center font-weight-bold">
                                            @if($recommendation['metric'] != 'N/A')
                                                <span class="
                                                    @if($recommendation['priority'] == 'high') text-danger
                                                    @elseif($recommendation['priority'] == 'medium') text-warning
                                                    @else text-info @endif
                                                ">{{ $recommendation['metric'] }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $recommendation['target'] }}</td>
                                        <td>{{ $recommendation['recommendation'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i> Great job! No significant areas for improvement identified based on current metrics.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
        
        <!-- NEW: Daily Activity Chart -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-day mr-2"></i> Daily Activity
                    </div>
                    <div class="card-body">
                        <div class="daily-chart-container">
                            <canvas id="dailyActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Third Row: Customer Satisfaction & Categories -->
        <div class="row mt-4">
            <!-- Service Categories -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tags mr-2"></i> Service Categories
                    </div>
                    <div class="card-body">
                        @if(count($categoryStats) > 0)
                            <div class="chart-container">
                                <canvas id="categoriesChart"></canvas>
                            </div>
                            
                            <!-- <h5 class="mt-4">All Requested Services</h5>
                            <ul class="list-group">
                                @foreach($categoryStats as $category => $data)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $category }}
                                        <span class="badge badge-primary badge-pill">{{ $data['total'] }}</span>
                                    </li>
                                @endforeach
                            </ul> -->
                        @else
                            <div class="alert alert-warning">
                                No service category data available for the selected time period.
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Customer Satisfaction -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-smile mr-2"></i> Customer Satisfaction ({{ $stats['satisfaction_count'] }} responses)
                    </div>
                    <div class="card-body">
                        @if($stats['satisfaction_count'] > 0)
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <h4>Overall Rating: {{ $stats['avg_overall_rating'] }}/5</h4>
                                    <div class="star-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($stats['avg_overall_rating']))
                                                <i class="fas fa-star"></i>
                                            @elseif($i - 0.5 <= $stats['avg_overall_rating'])
                                                <i class="fas fa-star-half-alt"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <canvas id="satisfactionChart"></canvas>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No customer satisfaction data available for the selected time period.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle mr-2"></i> Overdue Requests
            </div>
            <div class="card-body">
                @if(isset($overdueRequests) && count($overdueRequests) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Service</th>
                                    <th>Requester</th>
                                    <th>Transaction Type</th>
                                    <th>Days Overdue</th>
                                    <th>Expected Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            @php
                                                $categoryName = '';
                                                if (isset($request->service_category)) {
                                                    switch ($request->service_category) {
                                                        case 'create': $categoryName = 'Create MS Office/TUP Email Account'; break;
                                                        case 'reset_email_password': $categoryName = 'Reset MS Office/TUP Email Password'; break;
                                                        case 'change_of_data_ms': $categoryName = 'Change of Data (MS Office)'; break;
                                                        case 'reset_tup_web_password': $categoryName = 'Reset TUP Web Password'; break;
                                                        case 'reset_ers_password': $categoryName = 'Reset ERS Password'; break;
                                                        case 'change_of_data_portal': $categoryName = 'Change of Data (Portal)'; break;
                                                        case 'dtr': $categoryName = 'Daily Time Record'; break;
                                                        case 'biometric_record': $categoryName = 'Biometric Record'; break;
                                                        case 'biometrics_enrollement': $categoryName = 'Biometrics Enrollment'; break;
                                                        case 'new_internet': $categoryName = 'New Internet Connection'; break;
                                                        case 'new_telephone': $categoryName = 'New Telephone Connection'; break;
                                                        case 'repair_and_maintenance': $categoryName = 'Internet/Telephone Repair and Maintenance'; break;
                                                        case 'computer_repair_maintenance': $categoryName = 'Computer Repair and Maintenance'; break;
                                                        case 'printer_repair_maintenance': $categoryName = 'Printer Repair and Maintenance'; break;
                                                        case 'request_led_screen': $categoryName = 'LED Screen Request'; break;
                                                        case 'install_application': $categoryName = 'Install Application/Information System/Software'; break;
                                                        case 'post_publication': $categoryName = 'Post Publication/Update of Information Website'; break;
                                                        case 'data_docs_reports': $categoryName = 'Data, Documents and Reports'; break;
                                                        case 'others': $categoryName = 'Other Service'; break;
                                                        default: $categoryName = $request->service_category; break;
                                                    }
                                                }
                                            @endphp
                                            {{ $categoryName }}
                                        </td>
                                        <td>{{ $request->first_name }} {{ $request->last_name }}</td>
                                        <td>{{ $request->transaction_type ?? 'Standard' }}</td>
                                        <td class="text-danger font-weight-bold">{{ $request->days_overdue }}</td>
                                        <td>{{ $request->business_days_limit ?? 'N/A' }} days</td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                  
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i> No overdue requests found for the selected time period.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
        
        <!-- NEW: Recent Activity Timeline -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history mr-2"></i> Recent Activity Timeline
                    </div>
                    <div class="card-body">
                        <div class="timeline-container">
                            @forelse($recentActivity as $activity)
                                <div class="timeline-item {{ $activity['is_overdue'] ? 'timeline-overdue' : '' }}">
                                    <div class="timeline-date">
                                        {{ $activity['date'] }}
                                        @if($activity['is_overdue'])
                                            <span class="overdue-badge">
                                                <i class="fas fa-exclamation-triangle"></i> Overdue by {{ $activity['overdue_days'] }} day(s)
                                            </span>
                                        @endif
                                    </div>
                                    <div class="timeline-content">
                                        <strong>Request ID {{ $activity['id'] }}:</strong> {{ $activity['action'] }}<br>
                                        <small>Service: {{ $activity['category'] }} | User: {{ $activity['user'] }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-warning">
                                    No recent activity data available for the selected time period.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fourth Row: Detailed Category Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-table mr-2"></i> Detailed Service Category Statistics
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Service Category</th>
                                        <th>Total</th>
                                        <th>Completed</th>
                                        <th>In Progress</th>
                                        <th>Overdue</th>
                                        <th>Completion Rate</th>
                                        <th>Avg. Resolution Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryStats as $category => $data)
                                        <tr>
                                            <td>{{ $category }}</td>
                                            <td>{{ $data['total'] }}</td>
                                            <td>{{ $data['completed'] }}</td>
                                            <td>{{ $data['in_progress'] }}</td>
                                            <td>{{ $data['overdue'] }}</td>
                                            <td>
                                                {{ $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0 }}%
                                            </td>
                                            <td>
                                                {{ $categoryAvgResolution[$category] ?? 'N/A' }} days
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No data available for the selected period</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    <!-- Include necessary JS files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
        // Toggle custom date inputs when period changes
        function toggleCustomDateInputs() {
            var period = document.getElementById('period').value;
            var customDateInputs = document.getElementById('customDateInputs');
            
            if (period === 'custom') {
                customDateInputs.style.display = 'flex';
            } else {
                customDateInputs.style.display = 'none';
            }
        }
        
        // Initialize all charts when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Status Distribution Chart
            var statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
            var statusDistributionChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Completed', 'In Progress', 'Overdue'],
                    datasets: [{
                        data: [
                            {{ $stats['completed_requests'] }}, 
                            {{ $stats['in_progress_requests'] }}, 
                            {{ $stats['overdue_requests'] ?? 0 }},
                        ],
                        backgroundColor: [
                            '#28a745',  // Green for Completed
                            '#17a2b8',  // Blue for In Progress
                            '#ff9800',  // Orange for Overdue
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Monthly Trends Chart
            var trendsCtx = document.getElementById('monthlyTrendsChart');
                if (trendsCtx) {
                    trendsCtx = trendsCtx.getContext('2d');
                    var monthlyTrendsChart = new Chart(trendsCtx, {
                        type: 'line',
                        data: {
                            labels: [
                                @foreach($monthlyTrends as $month => $data)
                                    '{{ $month }}',
                                @endforeach
                            ],
                            datasets: [{
                                label: 'Total Requests',
                                data: [
                                    @foreach($monthlyTrends as $data)
                                        {{ $data['total'] ?? 0 }},
                                    @endforeach
                                ],
                                borderColor: '#6c757d',
                                backgroundColor: 'rgba(108, 117, 125, 0.1)',
                                borderWidth: 2,
                                fill: true
                            }, {
                                label: 'Completed Requests',
                                data: [
                                    @foreach($monthlyTrends as $data)
                                        {{ $data['completed'] ?? 0 }},
                                    @endforeach
                                ],
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                borderWidth: 2,
                                fill: true
                            }, {
                                label: 'Overdue Requests',
                                data: [
                                    @foreach($monthlyTrends as $data)
                                        {{ $data['overdue'] ?? 0 }},
                                    @endforeach
                                ],
                                borderColor: '#ff9800',
                                backgroundColor: 'rgba(255, 152, 0, 0.1)',
                                borderWidth: 2,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            
            // NEW: Daily Activity Chart
            var dailyCtx = document.getElementById('dailyActivityChart');
            if (dailyCtx) {
                dailyCtx = dailyCtx.getContext('2d');
                var dailyActivityChart = new Chart(dailyCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            @foreach($dailyActivity as $date => $data)
                                '{{ $date }}',
                            @endforeach
                        ],
                        datasets: [{
                            label: 'New Requests',
                            data: [
                                @foreach($dailyActivity as $data)
                                    {{ $data['new'] ?? 0 }},
                                @endforeach
                            ],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }, {
                            label: 'Completed Requests',
                            data: [
                                @foreach($dailyActivity as $data)
                                    {{ $data['completed'] ?? 0 }},
                                @endforeach
                            ],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgb(75, 192, 192)',
                            borderWidth: 1
                        }, {
                            label: 'Overdue Requests',
                            data: [
                                @foreach($dailyActivity as $data)
                                    {{ $data['overdue'] ?? 0 }},
                                @endforeach
                            ],
                            backgroundColor: 'rgba(255, 152, 0, 0.2)',
                            borderColor: 'rgb(255, 152, 0)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // Satisfaction Metrics Chart
            @if($stats['satisfaction_count'] > 0)
                var satisfactionCtx = document.getElementById('satisfactionChart').getContext('2d');
                var satisfactionChart = new Chart(satisfactionCtx, {
                    type: 'radar',
                    data: {
                        labels: [
                            'Responsiveness', 
                            'Reliability', 
                            'Access & Facilities',
                            'Communication',
                            'Costs',
                            'Integrity',
                            'Assurance',
                            'Outcome'
                        ],
                        datasets: [{
                            label: 'Average Rating',
                            data: [
                                {{ $stats['avg_responsiveness'] }},
                                {{ $stats['avg_reliability'] }},
                                {{ $stats['avg_access_facilities'] }},
                                {{ $stats['avg_communication'] }},
                                {{ $stats['avg_costs'] }},
                                {{ $stats['avg_integrity'] }},
                                {{ $stats['avg_assurance'] }},
                                {{ $stats['avg_outcome'] }}
                            ],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgb(54, 162, 235)',
                            pointBackgroundColor: 'rgb(54, 162, 235)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(54, 162, 235)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 5
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            @endif
            
            // Service Categories Chart
            @if(count($categoryStats) > 0)
                var categoryLabels = [];
                var categoryData = [];
                var categoryOverdueData = [];
                
                @foreach($categoryStats as $category => $data)
                    categoryLabels.push('{{ $category }}');
                    categoryData.push({{ $data['total'] }});
                    categoryOverdueData.push({{ $data['overdue'] ?? 0 }}); // Add overdue data
                @endforeach
                
                var categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
                var categoriesChart = new Chart(categoriesCtx, {
                    type: 'bar',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            label: 'Number of Requests',
                            data: categoryData,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgb(75, 192, 192)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            x: {
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 90,
                                    minRotation: 45
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            @endif
        });
    </script>
</body>
</html>