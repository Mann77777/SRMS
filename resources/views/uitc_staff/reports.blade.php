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
    <title>UITC Staff Report</title>
    <style>
        .report-container {
            padding: 40px;
            margin-left: 250px;
        }

        .content {
            margin-top: 8%;
            margin-left: 20%;
            padding: 40px;
        }
        .content h1{
            font-size: 2rem;
            font-weight: 650;
            text-transform: uppercase;
        }
                
        
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stat-box {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stat-box h3 {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-box p {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .bg-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .bg-progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .bg-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .bg-total {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .rating-box {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .star-rating {
            color: #ffc107;
            font-size: 24px;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .custom-date-inputs {
            display: none;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .report-container {
                margin-left: 0;
                padding: 10px;
            }
        }
        
        /* New styles for SLA monitoring */
        .sla-stat-box {
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .sla-stat-box h3 {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .sla-stat-box p {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }
        
        .sla-stat-box small {
            position: relative;
            z-index: 1;
        }
        
        .bg-sla-met {
            background-color: #d4edda;
            color: #155724;
        }
        
        .bg-sla-missed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .progress-bar-container {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
        }
        
        .gauge-container {
            position: relative;
            width: 100%;
            height: 6px;
            background-color: #e9ecef;
            margin-top: 10px;
            border-radius: 3px;
        }
        
        .gauge-fill {
            position: absolute;
            height: 100%;
            left: 0;
            border-radius: 3px;
        }
        
        .timeline-container {
            border-left: 2px solid #dee2e6;
            padding: 0 0 0 20px;
            margin-left: 10px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
        }
        
        .timeline-date {
            font-weight: bold;
            color: #6c757d;
        }
        
        .timeline-content {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        .timeline-overdue {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }
        
        .timeline-overdue::before {
            background-color: #ffc107;
        }
        
        .overdue-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: bold;
            background-color: #ffc107;
            color: #212529;
            margin-left: 5px;
            vertical-align: middle;
        }
        
        /* Daily activity chart styles */
        .daily-chart-container {
            height: 300px;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }
        

        /* Add these styles to the existing <style> block in your reports.blade.php file */

        /* Styles for Areas for Improvement section */
        .improvement-table td {
            vertical-align: middle;
        }

        .priority-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }

        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .action-plan {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .action-plan h6 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .action-plan ol {
            padding-left: 20px;
        }

        .action-plan li {
            margin-bottom: 5px;
        }

        /* Tooltip for improvement recommendations */
        .recommendation-cell {
            position: relative;
        }

        .recommendation-tooltip {
            visibility: hidden;
            position: absolute;
            z-index: 1;
            width: 300px;
            background-color: #333;
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 10px;
            bottom: 100%;
            left: 50%;
            margin-left: -150px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .recommendation-cell:hover .recommendation-tooltip {
            visibility: visible;
            opacity: 1;
        }
    </style>
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
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tachometer-alt mr-2"></i> UITC Staff Performance
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="sla-stat-box bg-sla-met">
                            <h3>Met Dealine</h3>
                            <p>{{ $slaStats['met_percentage'] }}%</p>
                            <small>{{ $slaStats['met'] }} of {{ $slaStats['met'] + $slaStats['missed'] }} completed requests</small>
                            <div class="gauge-container">
                                <div class="gauge-fill bg-success" style="width: {{ $slaStats['met_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sla-stat-box bg-sla-missed">
                            <h3>Missed Deadline</h3>
                            <p>{{ $slaStats['missed_percentage'] }}%</p>
                            <small>{{ $slaStats['missed'] }} of {{ $slaStats['met'] + $slaStats['missed'] }} completed requests</small>
                            <div class="gauge-container">
                                <div class="gauge-fill bg-danger" style="width: {{ $slaStats['missed_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sla-stat-box">
                            <h3>Average Response Time</h3>
                            <p>{{ $slaStats['avg_response_time'] }} hrs</p>
                            <div class="d-flex justify-content-between mt-3">
                                <span class="text-muted">Avg Overdue Time:</span>
                                <span class="font-weight-bold">{{ $slaStats['avg_overdue_days'] }} days</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Max Overdue:</span>
                                <span class="font-weight-bold">{{ $slaStats['max_overdue_days'] }} days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- First Row: Overview Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-box bg-total">
                    <h3>Total Requests</h3>
                    <p>{{ $stats['total_requests'] }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box bg-completed">
                    <h3>Completed</h3>
                    <p>{{ $stats['completed_requests'] }}</p>
                    <small>{{ $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100) : 0 }}% completion rate</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="background-color: #e9ecef;">
                    <h3>Resolution Time</h3>
                    <p>{{ number_format($stats['avg_resolution_time'], 2) }} days</p>
                    <div class="d-flex justify-content-between mt-2">
                        <small>Median: {{ number_format($stats['median_resolution_time'], 2) }} days</small>
                        <small>Fastest: {{ number_format($timeStats['fastest_resolution'], 2) }} days</small>
                        <small>Slowest: {{ number_format($timeStats['slowest_resolution'], 2) }} days</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-box bg-progress">
                    <h3>In Progress</h3>
                    <p>{{ $stats['in_progress_requests'] }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-box bg-cancelled">
                    <h3>Cancelled</h3>
                    <p>{{ $stats['cancelled_requests'] }}</p>
                </div>
            </div>
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
                            
                            <h5 class="mt-4">All Requested Services</h5>
                            <ul class="list-group">
                                @foreach($categoryStats as $category => $data)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $category }}
                                        <span class="badge badge-primary badge-pill">{{ $data['total'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
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
                                        <th>Cancelled</th>
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
                                            <td>{{ $data['cancelled'] }}</td>
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
                    labels: ['Completed', 'In Progress', 'Cancelled'],
                    datasets: [{
                        data: [
                            {{ $stats['completed_requests'] }}, 
                            {{ $stats['in_progress_requests'] }}, 
                            {{ $stats['cancelled_requests'] }}
                        ],
                        backgroundColor: [
                            '#28a745',  // Green for Completed
                            '#17a2b8',  // Blue for In Progress
                            '#dc3545'   // Red for Cancelled
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
            var trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
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
                                {{ $data['total'] }},
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
                                {{ $data['completed'] }},
                            @endforeach
                        ],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
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
            
            // NEW: Daily Activity Chart
            var dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
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
                                {{ $data['new'] }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }, {
                        label: 'Completed Requests',
                        data: [
                            @foreach($dailyActivity as $data)
                                {{ $data['completed'] }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgb(75, 192, 192)',
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
                
                @foreach($categoryStats as $category => $data)
                    categoryLabels.push('{{ $category }}');
                    categoryData.push({{ $data['total'] }});
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