<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="{{ asset('css/reports_pdf.css') }}" rel="stylesheet">

    <title>UITC Staff Report</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            src: url(data:font/truetype;base64,[BASE64_FONT_DATA]) format('truetype');
        }
        
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            src: url(data:font/truetype;base64,[BASE64_FONT_DATA_BOLD]) format('truetype');
        }
        
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .logo-header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 70px;
            height: auto;
            margin: 0 auto;
        }
        
        .header-text {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .header-text h1 {
            font-size: 18px;
            text-transform: uppercase;
            margin: 5px 0;
            font-weight: 700;
        }
        
        .header-text h2 {
            font-size: 16px;
            margin: 5px 0;
            font-weight: 700;
        }
        
        .header-text p {
            margin: 5px 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: 700;
        }
        
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .stat-box {
            width: 23%;
            margin: 1%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .stat-box h3 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 700;
        }
        
        .stat-box p {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            font-weight: 500;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .rating-box {
            text-align: center;
            margin: 10px 0;
        }
        
        .rating-value {
            font-size: 24px;
            font-weight: 700;
            color: #be1e2d;
        }
        
        /* Styles for the Student Rating Slip heading */
        .report-heading {
            text-align: center;
            text-transform: uppercase;
            font-size: 16px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        /* SLA Performance */
        .sla-metrics {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 15px;
        }

        .sla-metric {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            flex: 1;
        }
                
        .metric-value {
            font-size: 18px;
            font-weight: 700;
            margin: 5px 0;
        }
        
        .metric-label {
            font-size: 12px;
            font-weight: 600;
        }
        
        .on-time {
            background-color: #d4edda;
            color: #155724;
        }
        
        .delayed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .avg-response {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        /* Category Activity Timeline */
        .timeline {
            margin-top: 20px;
            position: relative;
            border-left: 2px solid #ddd;
            padding-left: 20px;
            margin-left: 10px;
        }
        
        .timeline-item {
            margin-bottom: 15px;
            position: relative;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -26px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #be1e2d;
        }
        
        .timeline-date {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .timeline-content {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .overdue-warning {
            background-color: #fff3cd;
            color: #856404;
            border-radius: 5px;
            padding: 5px 10px;
            margin-top: 5px;
            font-weight: 600;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="logo-header">
        @if(isset($logoData) && $logoData)
            <img src="{{ $logoData }}" alt="TUP Logo" class="logo">
        @else
            <!-- Fallback if logo data isn't available -->
            <div style="width: 70px; height: 70px; background-color: #f0f0f0; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                <span>Logo</span>
            </div>
        @endif
    </div>
    
    <div class="header-text">
        <p>Republic of the Philippines</p>
        <p>Technological University of the Philippines Manila</p>
    </div>
    
    <div style="clear: both; overflow: auto; margin-bottom: 20px;">
        <div class="report-heading">UITC STAFF SERVICE REQUEST REPORT</div>
    </div>
    
    <div class="alert">
        <strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}<br>
        <strong>Staff Member:</strong> {{ $staffName }}<br>
        <strong>Report Period:</strong> {{ $startDate }} to {{ $endDate }}
    </div>
    
    <!-- SLA Performance Section -->
    <div class="section-title">UITC Staff Performance</div>
    
    <div class="sla-performance">
        <div class="sla-metrics">
            <div class="sla-metric on-time">
                <div class="metric-value">{{ $slaStats['met_percentage'] }}%</div>
                <div class="metric-label">Met Deadline</div>
            </div>
            <div class="sla-metric delayed">
                <div class="metric-value">{{ $slaStats['missed_percentage'] }}%</div>
                <div class="metric-label">Missed Deadline</div>
            </div>
            <div class="sla-metric avg-response">
                <div class="metric-value">{{ $slaStats['avg_response_time'] }} hrs</div>
                <div class="metric-label">Avg. Response Time</div>
            </div>
        </div>
        
        <table style="margin-top: 15px;">
            <tr>
                <th>Total Completed</th>
                <th>Met Deadline</th>
                <th>Missed Deadline</th>
                <th>Avg. Overdue Days</th>
                <th>Max Overdue Days</th>
            </tr>
            <tr>
                <td>{{ $slaStats['met'] + $slaStats['missed'] }}</td>
                <td>{{ $slaStats['met'] }}</td>
                <td>{{ $slaStats['missed'] }}</td>
                <td>{{ $slaStats['avg_overdue_days'] }}</td>
                <td>{{ $slaStats['max_overdue_days'] }}</td>
            </tr>
        </table>
    </div>
    
    <div class="section-title">Requests Statistics</div>
    
    <table>
        <tr>
            <th>Total Requests</th>
            <th>Completed</th>
            <th>In Progress</th>
            <th>Cancelled</th>
        </tr>
        <tr>
            <td>{{ $stats['total_requests'] }}</td>
            <td>{{ $stats['completed_requests'] }} ({{ $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100) : 0 }}%)</td>
            <td>{{ $stats['in_progress_requests'] }}</td>
            <td>{{ $stats['cancelled_requests'] }}</td>
        </tr>
    </table>
    
    <div class="section-title">Resolution Time</div>
    
    <table>
        <tr>
            <th>Average Resolution Time</th>
            <th>Median Resolution Time</th>
            <th>Fastest Resolution</th>
            <th>Slowest Resolution</th>
        </tr>
        <tr>
            <td>{{ number_format($stats['avg_resolution_time'], 2) }} days</td>
            <td>{{ number_format($stats['median_resolution_time'], 2) }} days</td>
            <td>{{ number_format($timeStats['fastest_resolution'] ?? 0, 2) }} days</td>
            <td>{{ number_format($timeStats['slowest_resolution'] ?? 0, 2) }} days</td>
        </tr>
    </table>
    
    <!-- Daily Activity Summary -->
    <div class="section-title">Daily Activity Summary</div>
    
    <table>
        <tr>
            <th>Date</th>
            <th>Number of Requests</th>
            <th>Number of Completed Requests</th>
            <th>Completion Rate</th>
        </tr>
        @forelse($dailyActivity ?? [] as $date => $activity)
        <tr>
            <td>{{ $date }}</td>
            <td>{{ $activity['new'] }}</td>
            <td>{{ $activity['completed'] }}</td>
            <td>{{ $activity['new'] > 0 ? round(($activity['completed'] / $activity['new']) * 100) : 0 }}%</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align: center;">No daily activity data available</td>
        </tr>
        @endforelse
    </table>
    
    <div class="section-title">Customer Satisfaction ({{ $stats['satisfaction_count'] }} responses)</div>
    
    @if($stats['satisfaction_count'] > 0)
        <div class="rating-box">
            <strong>Overall Rating:</strong> <span class="rating-value">{{ $stats['avg_overall_rating'] }}/5</span>
        </div>
        
        <table>
            <tr>
                <th>Responsiveness</th>
                <th>Reliability</th>
                <th>Access & Facilities</th>
                <th>Communication</th>
            </tr>
            <tr>
                <td>{{ $stats['avg_responsiveness'] }}/5</td>
                <td>{{ $stats['avg_reliability'] }}/5</td>
                <td>{{ $stats['avg_access_facilities'] }}/5</td>
                <td>{{ $stats['avg_communication'] }}/5</td>
            </tr>
        </table>
        
        <table>
            <tr>
                <th>Costs</th>
                <th>Integrity</th>
                <th>Assurance</th>
                <th>Outcome</th>
            </tr>
            <tr>
                <td>{{ $stats['avg_costs'] }}/5</td>
                <td>{{ $stats['avg_integrity'] }}/5</td>
                <td>{{ $stats['avg_assurance'] }}/5</td>
                <td>{{ $stats['avg_outcome'] }}/5</td>
            </tr>
        </table>
    @else
        <p>No customer satisfaction data available for the selected time period.</p>
    @endif
    
    <div class="page-break"></div>
    
    <div class="section-title">Service Category Analysis</div>
    
    <table>
        <thead>
            <tr>
                <th>Service Category</th>
                <th>Total</th>
                <th>Completed</th>
                <th>In Progress</th>
                <th>Cancelled</th>
                <th>Completion Rate</th>
                <th>Avg. Resolution (days)</th>
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
                        @if(is_numeric($categoryAvgResolution[$category] ?? null))
                            {{ number_format($categoryAvgResolution[$category], 2) }} days
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No data available for the selected period</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Recent Activity Timeline -->
    <div class="section-title">Recent Activity Timeline</div>
    
    <div class="timeline">
        @forelse($recentActivity ?? [] as $activity)
            <div class="timeline-item">
                <div class="timeline-date">{{ $activity['date'] }}</div>
                <div class="timeline-content">
                    <strong>Request #{{ $activity['id'] }}:</strong> {{ $activity['action'] }}<br>
                    <small>Service: {{ $activity['category'] }} | User: {{ $activity['user'] }}</small>
                    
                    @if($activity['is_overdue'] ?? false)
                        <div class="overdue-warning">
                            <i class="fas fa-exclamation-triangle"></i> This request exceeded SLA by {{ $activity['overdue_days'] }} days
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p>No recent activity data available.</p>
        @endforelse
    </div>

    <!-- Add this to reports_pdf.blade.php before the footer section -->

    <!-- Areas for Improvement Section -->
    <div class="section-title">Areas for Improvement</div>

    @if(count($improvementRecommendations ?? []) > 0)
        <table>
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Current</th>
                    <th>Target</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($improvementRecommendations as $recommendation)
                    <tr>
                        <td>{{ $recommendation['area'] }}</td>
                        <td>{{ $recommendation['metric'] }}</td>
                        <td>{{ $recommendation['target'] }}</td>
                        <td>{{ $recommendation['recommendation'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    
    @else
        <p style="text-align: center; padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px;">
            Great job! No significant areas for improvement identified.
        </p>
    @endif
    
    <div class="footer">
        <p>This report is automatically generated and reflects the Service Requests assigned to {{ $staffName }}.</p>
        <!-- <p>Technological University of the Philippines - University Information Technology Center</p> --> 
        <p>Report Reference: UITC-{{ date('Ymd') }}-{{ rand(1000, 9999) }}</p>
        <p>XXXXXXXX Nothing Follows XXXXXXXX</p>
    </div>
</body>
</html>