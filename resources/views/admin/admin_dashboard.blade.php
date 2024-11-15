<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/admin_dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
    <div class="sidebar">
        @if(Auth::guard('admin')->user()->role === 'Admin')
            <a href="#">Dashboard</a>
            <a href="#">Service Request</a>
            <a href="#">User Management</a>
            <a href="#">Assign Management</a>
            <a href="#">Report</a>
            <a href="#">Settings</a>
        @elseif(Auth::guard('admin')->user()->role === 'Technician')
            <a href="#">Dashboard</a>
            <a href="#">Assign Request</a>
            <a href="#">Assign History</a>
            <a href="#">Report</a>
        @endif
    </div>
    
    <div class="content">
        <div class="dashboard-title">DASHBOARD</div>
        
        <div class="button-container">
            @if(Auth::guard('admin')->user()->role === 'Admin')
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
            @endif
        </div>

        <div class="stats-container">
            @if(Auth::guard('admin')->user()->role === 'Admin')
                <div class="stat-box">
                    <h3>Request Received</h3>
                    <div class="stat-value">5</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Assigned Requests</h3>
                    <div class="stat-value">1</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Services Completed</h3>
                    <div class="stat-value">0</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Number of Assign Staff</h3>
                    <div class="stat-value">4</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Survey Ratings</h3>
                    <div class="stat-value">4</div>
                    <a href="#">View</a>
                </div>
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
                <div class="stat-box">
                    <h3>Assigned Requests</h3>
                    <div class="stat-value">2</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Services Completed</h3>
                    <div class="stat-value">0</div>
                    <a href="#">View</a>
                </div>
                <div class="stat-box">
                    <h3>Survey Ratings</h3>
                    <div class="stat-value">0</div>
                    <a href="#">View</a>
                </div>
            @endif
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
