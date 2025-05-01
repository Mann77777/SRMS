<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>UITC Staff Report Summary</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section-title { font-weight: bold; margin: 15px 0 5px; font-size: 14px; }
        .critical { color: #cc0000; font-weight: bold; }
        .warning { color: #ff6600; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 15px;">
        <h1 style="margin: 0; font-size: 16px;">UITC STAFF SERVICE REQUEST SUMMARY</h1>
        <p>{{ $startDate }} to {{ $endDate }} | Generated: {{ date('F d, Y') }}</p>
        <p>Staff Member: {{ $staffName }}</p>
    </div>

    <!-- Key Metrics Section -->
    <div class="section-title">KEY PERFORMANCE METRICS</div>
    <table>
        <tr>
            <th>Total Requests</th>
            <th>Completed</th>
            <th>In Progress</th>
            <th>Overdue</th>
            <th>Completion Rate</th>
        </tr>
        <tr>
            <td>{{ $stats['total_requests'] }}</td>
            <td>{{ $stats['completed_requests'] }}</td>
            <td>{{ $stats['in_progress_requests'] }}</td>
            <td class="{{ $stats['overdue_requests'] > 0 ? 'critical' : '' }}">{{ $stats['overdue_requests'] }}</td>
            <td>{{ $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100) : 0 }}%</td>
        </tr>
    </table>

    <!-- SLA Performance -->
    <div class="section-title">SLA PERFORMANCE</div>
    <table>
        <tr>
            <th>Met Deadline</th>
            <th>Missed Deadline</th>
            <th>Avg Response Time</th>
            <th>Avg Overdue Days</th>
        </tr>
        <tr>
            <td>{{ $slaStats['met_percentage'] }}%</td>
            <td class="{{ $slaStats['missed_percentage'] > 20 ? 'critical' : ($slaStats['missed_percentage'] > 10 ? 'warning' : '') }}">
                {{ $slaStats['missed_percentage'] }}%
            </td>
            <td>{{ $slaStats['avg_response_time'] }} hrs</td>
            <td>{{ $slaStats['avg_overdue_days'] }} days</td>
        </tr>
    </table>

    <!-- Overdue Requests (Limited) -->
    @if(count($overdueRequests) > 0)
        <div class="section-title">TOP PRIORITY OVERDUE REQUESTS</div>
        <table>
            <tr>
                <th>Request ID</th>
                <th>Service</th>
                <th>Requester</th>
                <th>Transaction Type</th>
                <th>Days Overdue</th>
                <th>Expected Days</th>
            </tr>
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
                    <td class="{{ $request->days_overdue > 5 ? 'critical' : 'warning' }}">{{ $request->days_overdue }}</td>
                    <td>{{ $request->business_days_limit ?? 'N/A' }} days</td>
                </tr>
            @endforeach
        </table>
    @endif

    <!-- Improvement Recommendations -->
    @if(count($improvementRecommendations) > 0)
        <div class="section-title">IMPROVEMENT RECOMMENDATIONS</div>
        <table>
            <tr>
                <th>Area</th>
                <th>Current</th>
                <th>Target</th>
                <th>Recommendation</th>
            </tr>
            @foreach($improvementRecommendations as $recommendation)
                <tr>
                    <td>{{ $recommendation['area'] }}</td>
                    <td class="{{ $recommendation['priority'] == 'high' ? 'critical' : ($recommendation['priority'] == 'medium' ? 'warning' : '') }}">
                        {{ $recommendation['metric'] }}
                    </td>
                    <td>{{ $recommendation['target'] }}</td>
                    <td>{{ $recommendation['recommendation'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <!-- Top Service Categories -->
    <div class="section-title">TOP SERVICE CATEGORIES</div>
    <table>
        <tr>
            <th>Service Category</th>
            <th>Total</th>
            <th>Completed</th>
            <th>In Progress</th>
            <th>Overdue</th>
            <th>Completion Rate</th>
        </tr>
        @php $counter = 0; @endphp
        @foreach($categoryStats as $category => $data)
            @if($counter < 8)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['total'] }}</td>
                    <td>{{ $data['completed'] }}</td>
                    <td>{{ $data['in_progress'] }}</td>
                    <td>{{ $data['overdue'] ?? 0 }}</td>
                    <td>{{ $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0 }}%</td>
                </tr>
                @php $counter++; @endphp
            @endif
        @endforeach
    </table>
    
    <div style="text-align: center; margin-top: 30px; font-size: 10px;">
        <p>This is a simplified report summary. For detailed analysis, please refer to the online dashboard.</p>
        <p>Report Reference: UITC-{{ date('Ymd') }}-{{ rand(1000, 9999) }}</p>
    </div>
</body>
</html>