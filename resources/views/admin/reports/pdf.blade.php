<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>UITC Service Request Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .page-break {
            page-break-after: always;
        }
        h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a237e;
        }
        h2 {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #283593;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #303f9f;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .filter-info {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }
        .filter-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-info td {
            padding: 5px;
        }
        .filter-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            justify-content: space-between;
        }
        .stat-card {
            width: 22%;
            background-color: #f5f5f5;
            border-left: 4px solid #3f51b5;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 20px;
            color: #3f51b5;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 12px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #e8eaf6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #c5cae9;
        }
        table.data-table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        .completed {
            background-color: #e8f5e9 !important;
        }
        .in-progress {
            background-color: #e3f2fd !important;
        }
        .pending {
            background-color: #fff8e1 !important;
        }
        .rejected {
            background-color: #ffebee !important;
        }
        .staff-performance {
            width: 100%;
        }
        .performance-bar {
            height: 15px;
            background-color: #eee;
            margin-top: 3px;
            border-radius: 2px;
            overflow: hidden;
        }
        .performance-value {
            height: 100%;
            background-color: #4caf50;
        }
        .performance-low {
            background-color: #f44336;
        }
        .performance-medium {
            background-color: #ffeb3b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UITC SERVICE REQUEST REPORT</h1>
        <p>Technological University of the Philippines</p>
    </div>
    
    <div class="filter-info">
        <h3>Report Filters</h3>
        <table>
            <tr>
                <td>Date Range:</td>
                <td>{{ $filters['dateFilter'] }} ({{ $filters['startDate'] }} to {{ $filters['endDate'] }})</td>
            </tr>
            <tr>
                <td>Staff:</td>
                <td>{{ $filters['staffName'] }}</td>
            </tr>
            <tr>
                <td>Service Category:</td>
                <td>{{ $filters['serviceCategory'] }}</td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>{{ $filters['status'] }}</td>
            </tr>
            <tr>
                <td>Generated on:</td>
                <td>{{ date('Y-m-d H:i:s') }}</td>
            </tr>
        </table>
    </div>
    
    <h2>Summary Statistics</h2>
    <div class="stats-cards">
        <div class="stat-card">
            <h3>{{ $stats['total_requests'] }}</h3>
            <p>Total Requests</p>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['completed_requests'] }}</h3>
            <p>Completed Requests</p>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['avg_response_time'] }}h</h3>
            <p>Avg. Response Time</p>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['completion_rate'] }}%</h3>
            <p>Completion Rate</p>
        </div>
    </div>
    
    <h2>Staff Performance</h2>
    <table class="data-table staff-performance">
        <thead>
            <tr>
                <th width="30%">Staff Name</th>
                <th width="15%">Assigned</th>
                <th width="15%">Completed</th>
                <th width="40%">Performance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffPerformance as $staff)
                <tr>
                    <td>{{ $staff['name'] }}</td>
                    <td>{{ $staff['assigned'] }}</td>
                    <td>{{ $staff['completed'] }}</td>
                    <td>
                        {{ $staff['performance'] }}%
                        <div class="performance-bar">
                            <div class="performance-value {{ $staff['performance'] < 70 ? 'performance-low' : ($staff['performance'] < 85 ? 'performance-medium' : '') }}" style="width: {{ $staff['performance'] }}%"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    <h2>Detailed Request Data</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Date</th>
                <th>Service</th>
                <th>Requester</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Response Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($detailedRequests, 0, 50) as $request)
                <tr class="{{ strtolower($request['status']) }}">
                    <td>{{ $request['id'] }}</td>
                    <td>{{ $request['created_at'] }}</td>
                    <td>{{ $request['service_name'] }}</td>
                    <td>{{ $request['requester_name'] }}</td>
                    <td>{{ $request['staff_name'] ?? 'Unassigned' }}</td>
                    <td>{{ $request['status'] }}</td>
                    <td>{{ $request['duration_hours'] ?? '-' }}</td>
                </tr>
            @endforeach
            @if(count($detailedRequests) > 50)
                <tr>
                    <td colspan="7" style="text-align: center; font-style: italic;">
                        ... and {{ count($detailedRequests) - 50 }} more requests (first 50 shown)
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <div class="footer">
        <p>UITC Service Request Report - Generated on {{ date('Y-m-d H:i:s') }}</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>